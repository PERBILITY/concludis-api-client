<?php


namespace Concludis\ApiClient\Storage;


use Concludis\ApiClient\Config\Baseconfig;
use Concludis\ApiClient\Database\PDO;
use Concludis\ApiClient\Resources\Company;
use Exception;

class CompanyRepository {

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
        `name` = :name';

        $ph = [
            ':source_id' => $company->source_id,
            ':company_id' => $company->id,
            ':name' => $company->name
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

        $sql = 'UPDATE `'.CONCLUDIS_TABLE_LOCAL_COMPANY.'` SET 
        `name` = :name                             
        WHERE `source_id` = :source_id AND `company_id` = :company_id';

        $ph = [
            ':source_id' => $company->source_id,
            ':company_id' => $company->id,
            ':name' => $company->name
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
     * @return bool
     * @throws Exception
     */
    public static function purgeBySource(string $source_id): bool {

        $pdo = PDO::getInstance();

        $sql = 'DELETE FROM `'.CONCLUDIS_TABLE_LOCAL_COMPANY.'` WHERE `source_id` = :source_id';

        return $pdo->delete($sql,[':source_id' => $source_id]);
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


}