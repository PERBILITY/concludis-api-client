<?php


namespace Concludis\ApiClient\Storage;


use Concludis\ApiClient\Config\Baseconfig;
use Concludis\ApiClient\Database\PDO;
use Concludis\ApiClient\Resources\Company;
use Concludis\ApiClient\Util\ArrayUtil;
use Exception;

class CompanyRepository {


    public const FILTER_TYPE_SOURCE = 'source';
    public const FILTER_TYPE_ID = 'id';
    public const FILTER_TYPE_MERGE_HASH = 'merge_hash';
    public const FILTER_TYPE_PAGINATION = 'pagination';

    /**
     * @param Company $company
     * @return bool
     * @throws Exception
     */
    public static function save(Company $company): bool {

        if (self::exists($company)) {
            return self::update($company);
        }

        return self::insert($company);
    }

    /**
     * @param Company $company
     * @return bool
     * @throws Exception
     */
    private static function insert(Company $company): bool {

        $pdo = PDO::getInstance();

        $sql = 'INSERT INTO `'.CONCLUDIS_TABLE_LOCAL_COMPANY.'` SET 
        `source_id` = :source_id, 
        `company_id` = :company_id, 
        `data` = :data';

        $ph = [
            ':source_id' => $company->source_id,
            ':company_id' => $company->id,
            ':data' => json_encode($company, JSON_THROW_ON_ERROR),
        ];

        return $pdo->insert($sql, $ph);
    }

    /**
     * @param Company $company
     * @return bool
     * @throws Exception
     */
    private static function update(Company $company): bool {

        $pdo = PDO::getInstance();

        $sql = 'UPDATE `'.CONCLUDIS_TABLE_LOCAL_COMPANY.'` SET `data` = :data                             
        WHERE `source_id` = :source_id AND `company_id` = :company_id';

        $ph = [
            ':source_id' => $company->source_id,
            ':company_id' => $company->id,
            ':data' => json_encode($company, JSON_THROW_ON_ERROR),
        ];

        return $pdo->update($sql, $ph);
    }

    /**
     * @param Company $company
     * @return bool|null
     * @throws Exception
     */
    public static function exists(Company $company): ?bool {

        $pdo = PDO::getInstance();

        $sql = 'SELECT COUNT(*) AS `cnt` FROM `'.CONCLUDIS_TABLE_LOCAL_COMPANY.'` 
        WHERE `source_id` = :source_id AND `company_id` = :company_id';

        $res = $pdo->selectOne($sql, [
            ':source_id' => $company->source_id,
            ':company_id' => $company->id
        ]);

        if ($res === false) {
            return null;
        }

        return $res['cnt'] > 0;
    }

    /**
     * Fetches a company by its source ID and company ID
     * @param string $source_id
     * @param int $company_id
     * @return Company|null
     * @throws Exception
     */
    public static function fetchCompanyById(string $source_id, int $company_id): ?Company {
        $pdo = PDO::getInstance();

        $sql = 'SELECT `data` FROM `'.CONCLUDIS_TABLE_LOCAL_COMPANY.'` 
        WHERE `source_id` = :source_id AND `company_id` = :company_id';

        $res = $pdo->selectOne($sql, [
            ':source_id' => $source_id,
            ':company_id' => $company_id
        ]);

        if(!is_array($res)) {
            return null;
        }

        $data = json_decode($res['data'], true, 512, JSON_THROW_ON_ERROR);
        $data['source_id'] = $source_id;
        $data['id'] = $company_id;
        return new Company($data);
    }

    /**
     * @return void
     * @throws Exception
     */
    public static function purgeDeprecatedSources(): void {

        $sources = self::fetchDistinctSources();
        foreach($sources as $source_id) {
            $source = Baseconfig::getSourceById($source_id);
            if($source === null) {
                self::purgeBySource($source_id);
            }
        }
    }

    /**
     * @return array
     * @throws Exception
     */
    public static function fetchDistinctSources(): array {

        $pdo = PDO::getInstance();

        $sql = 'SELECT DISTINCT `source_id` FROM `'.CONCLUDIS_TABLE_LOCAL_COMPANY.'`';

        $res =  $pdo->select($sql);

        $data = [];
        foreach($res as $r){
            $data[] = $r['source_id'];
        }
        return $data;
    }

    /**
     * @param string $source_id
     * @param array $except_ids
     * @return bool
     * @throws Exception
     */
    public static function purgeBySource(string $source_id, array $except_ids = []): bool {

        $pdo = PDO::getInstance();

        $ph = [':source_id' => $source_id];

        $sql = 'DELETE FROM `'.CONCLUDIS_TABLE_LOCAL_COMPANY.'` WHERE `source_id` = :source_id';

        if(!empty($except_ids)) {
            $sql .= ' AND `company_id` NOT IN (:except_ids)';
            $ph[':except_ids'] = $except_ids;
        }

        return $pdo->delete($sql,$ph);
    }


    /**
     * @param string|null $source_id
     * @return void
     * @throws Exception
     */
    public static function purgeUnused(?string $source_id = null): void {

        $pdo = PDO::getInstance();

        $ph = [];
        $sql = 'SELECT `l`.`source_id`, `l`.`company_id` FROM `'.CONCLUDIS_TABLE_LOCAL_COMPANY.'` `l` 
        LEFT JOIN `'.CONCLUDIS_TABLE_PROJECT_COMPANY.'` `p`  ON (`l`.`company_id` = `p`.`company_id` AND `l`.`source_id` = `p`.`source_id`)        
        WHERE `p`.`project_id` IS NULL';

        if($source_id !== null) {
            $sql .= ' AND `l`.`source_id` = :source_id';
            $ph = [':source_id' => $source_id];
        }

        $res = $pdo->select($sql, $ph);

        $to_delete = [];
        foreach($res as $r){
            $key = $r['source_id'] . '::' . $r['company_id'];
            $to_delete[$key] = [
                'source_id' => $r['source_id'],
                'company_id' => $r['company_id']
            ];
        }

        foreach($to_delete as $v){
            $sql = 'DELETE FROM `'.CONCLUDIS_TABLE_LOCAL_COMPANY.'` WHERE `source_id` = :source_id AND `company_id` = :company_id';
            $pdo->delete($sql, [
                ':source_id' => $v['source_id'],
                ':company_id' => $v['company_id']
            ]);
        }
    }

    /**
     * @param array $filters
     * @param int|null $count
     * @return Company[]
     * @throws Exception
     */
    public static function fetch(array $filters, ?int &$count = null): array {
        $pdo = PDO::getInstance();

        $query = [
            'ph' =>  [],
            'where' =>  [],
        ];

        $query_parts = [];

        if(array_key_exists(self::FILTER_TYPE_SOURCE, $filters)) {
            $tmp_filter = $filters[self::FILTER_TYPE_SOURCE];

            $source_in = null;
            $source_not_in = null;
            if(array_key_exists('not_in', $tmp_filter)) {
                $source_not_in = [];
                if (is_array($tmp_filter['not_in'])) {
                    $source_not_in = ArrayUtil::toStringArray($tmp_filter['not_in']);
                } else if (is_string($tmp_filter['not_in'])) {
                    $source_not_in = ArrayUtil::toStringArray([$tmp_filter['not_in']]);
                }
            }
            if(array_key_exists('in', $tmp_filter)) {
                $source_in = [];
                if (is_array($tmp_filter['in'])) {
                    $source_in = ArrayUtil::toStringArray($tmp_filter['in']);
                } else if (is_string($tmp_filter['in'])) {
                    $source_in = ArrayUtil::toStringArray([$tmp_filter['in']]);
                }
            }

            // fallback to in-definition without special in-index
            if($source_in === null && $source_not_in === null) {
                $source_in = [];
                if (is_array($tmp_filter)) {
                    $source_in = ArrayUtil::toStringArray($tmp_filter);
                } else if (is_string($tmp_filter)) {
                    $source_in = ArrayUtil::toStringArray([$tmp_filter]);
                }
            }

            if (!empty($source_in)) {
                $query_parts['source_in'] = [
                    'where' => '`company`.`source_id` IN (:source_in)',
                    'ph' => [':source_in' => $source_in]
                ];
            }
            if (!empty($source_not_in)) {
                $query_parts['source_not_in'] = [
                    'where' => '`company`.`source_id` NOT IN (:source_not_in)',
                    'ph' => [':source_not_in' => $source_not_in]
                ];
            }
        }

        if(array_key_exists(self::FILTER_TYPE_ID, $filters)) {
            $tmp_filter = $filters[self::FILTER_TYPE_ID];
            $tmp_in = null;
            if(array_key_exists('in', $tmp_filter)) {
                $tmp_in = [];
                if (is_array($tmp_filter['in'])) {
                    $tmp_in = ArrayUtil::toIntArray($tmp_filter['in']);
                } else if (is_string($tmp_filter['in'])) {
                    $tmp_in = ArrayUtil::toIntArray([$tmp_filter['in']]);
                }
            }
            if (!empty($tmp_in)) {
                $query_parts['company_id_in'] = [
                    'where' => '`company`.`company_id` IN (:company_id_in)',
                    'ph' => [':company_id_in' => $tmp_in]
                ];
            }
        }

        if(array_key_exists(self::FILTER_TYPE_MERGE_HASH, $filters)) {
            $tmp_filter = $filters[self::FILTER_TYPE_MERGE_HASH];
            $tmp_in = null;
            if(array_key_exists('in', $tmp_filter)) {
                $tmp_in = [];
                if (is_array($tmp_filter['in'])) {
                    $tmp_in = ArrayUtil::toStringArray($tmp_filter['in']);
                } else if (is_string($tmp_filter['in'])) {
                    $tmp_in = ArrayUtil::toStringArray([$tmp_filter['in']]);
                }
            }
            if (!empty($tmp_in)) {
                $query_parts['merge_hash_in'] = [
                    'where' => 'SHA1(`company`.`name`) IN (:merge_hash_in)',
                    'ph' => [':merge_hash_in' => $tmp_in]
                ];
            }
        }

        if (array_key_exists(self::FILTER_TYPE_PAGINATION, $filters)) {
            $tmp_filter = $filters[self::FILTER_TYPE_PAGINATION];
            $limit = array_key_exists('limit', $tmp_filter) ? (int)$tmp_filter['limit'] : 0;
            $offset = array_key_exists('offset', $tmp_filter) ? (int)$tmp_filter['offset'] : 0;
            $query['limit'] = 'LIMIT ' . $offset . ',' . $limit;
        }

        $query['order'] = 'ORDER BY `name`';

        foreach ($query_parts as $v) {
            if (array_key_exists('where', $v)) {
                $query['where'][] = $v['where'];
            }

            if (array_key_exists('ph', $v)) {
                foreach ($v['ph'] as $ph_k => $ph_v) {
                    $query['ph'][$ph_k] = $ph_v;
                }
            }
        }

        $sql_count = "SELECT COUNT(*) AS `cnt` FROM `".CONCLUDIS_TABLE_LOCAL_COMPANY."` company WHERE 1" .
            (!empty($query['where'] ?? null) ? "\n" . 'AND ' . implode(' AND ', $query['where']) . " \n" : '');

        $res_count = $pdo->selectOne($sql_count, $query['ph']);
        if(!is_array($res_count)) {
            $count = null;
            return [];
        }

        $count = (int)($res_count['cnt'] ?? 0);


        $sql = "SELECT `source_id`, `company_id`, `data` FROM `".CONCLUDIS_TABLE_LOCAL_COMPANY."` company WHERE 1" .
            (!empty($query['where'] ?? null) ? "\n" . 'AND ' . implode(' AND ', $query['where']) . " \n" : '') .
            (!empty($query['order'] ?? null) ? $query['order'] . " \n" : '') .
            (!empty($query['limit'] ?? null) ? $query['limit'] . " \n" : '');


        $res = $pdo->select($sql, $query['ph']);

        return array_map(static function ($v) {
            $data = (array)json_decode($v['data'], true, 512, JSON_THROW_ON_ERROR);
            $data['source_id'] = $v['source_id'];
            $data['id'] = $v['company_id'];
            return new Company($data);
        }, $res);

    }

}