<?php


namespace Concludis\ApiClient\Storage;


use Concludis\ApiClient\Config\Baseconfig;
use Concludis\ApiClient\Database\PDO;
use Concludis\ApiClient\Resources\Seniority;
use Exception;

class SeniorityRepository {

    /**
     * @param Seniority $element
     * @return bool
     * @throws Exception
     */
    public static function save(Seniority $element): bool {

        if (self::exists($element)) {
            return self::update($element);
        }

        return self::insert($element);
    }

    /**
     * @param Seniority $element
     * @return bool
     * @throws Exception
     */
    private static function insert(Seniority $element): bool {

        $pdo = PDO::getInstance();

        $sql = 'INSERT INTO `'.CONCLUDIS_TABLE_LOCAL_SENIORITY.'` SET 
        `source_id` = :source_id, 
        `seniority_id` = :seniority_id, 
        `global_seniority_id` = :global_seniority_id,
        `name` = :name,
        `locale` = :locale';

        $ph = [
            ':source_id' => $element->source_id,
            ':seniority_id' => $element->id,
            ':global_seniority_id' => $element->global_id,
            ':name' => $element->name,
            ':locale' => $element->locale
        ];

        return $pdo->insert($sql, $ph);
    }

    /**
     * @param Seniority $element
     * @return bool
     * @throws Exception
     */
    private static function update(Seniority $element): bool {

        $pdo = PDO::getInstance();

        $sql = 'UPDATE `'.CONCLUDIS_TABLE_LOCAL_SENIORITY.'` SET 
        `name` = :name,
        `locale` = :locale,
        `global_seniority_id` = :global_seniority_id                                      
        WHERE `source_id` = :source_id AND `seniority_id` = :seniority_id';

        $ph = [
            ':source_id' => $element->source_id,
            ':seniority_id' => $element->id,
            ':global_seniority_id' => $element->global_id,
            ':name' => $element->name,
            ':locale' => $element->locale
        ];

        return $pdo->update($sql, $ph);
    }

    /**
     * @param Seniority $element
     * @return bool|null
     * @throws Exception
     */
    public static function exists(Seniority $element): ?bool {

        $pdo = PDO::getInstance();

        $sql = 'SELECT COUNT(*) AS `cnt` FROM `'.CONCLUDIS_TABLE_LOCAL_SENIORITY.'` 
        WHERE `source_id` = :source_id AND `seniority_id` = :seniority_id';

        $res = $pdo->selectOne($sql, [
            ':source_id' => $element->source_id,
            ':seniority_id' => $element->id
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

        $sql = 'SELECT DISTINCT `source_id` FROM `'.CONCLUDIS_TABLE_LOCAL_SENIORITY.'`';

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

        $sql = 'DELETE FROM `'.CONCLUDIS_TABLE_LOCAL_SENIORITY.'` WHERE `source_id` = :source_id';

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
        $sql = 'SELECT `l`.`source_id`, `l`.`seniority_id` FROM `'.CONCLUDIS_TABLE_LOCAL_SENIORITY.'` `l` 
        LEFT JOIN `'.CONCLUDIS_TABLE_PROJECT_SENIORITY.'` `p`  ON (`l`.`seniority_id` = `p`.`seniority_id` AND `l`.`source_id` = `p`.`source_id`)        
        WHERE `p`.`project_id` IS NULL';

        if($source_id !== null) {
            $sql .= ' AND `l`.`source_id` = :source_id';
            $ph = [':source_id' => $source_id];
        }

        $res = $pdo->select($sql, $ph);

        $to_delete = [];
        foreach($res as $r){
            $key = $r['source_id'] . '::' . $r['seniority_id'];
            $to_delete[$key] = [
                'source_id' => $r['source_id'],
                'seniority_id' => $r['seniority_id']
            ];
        }

        foreach($to_delete as $v){
            $sql = 'DELETE FROM `'.CONCLUDIS_TABLE_LOCAL_SENIORITY.'` WHERE `source_id` = :source_id AND `seniority_id` = :seniority_id';
            $pdo->delete($sql, [
                ':source_id' => $v['source_id'],
                ':seniority_id' => $v['seniority_id']
            ]);
        }
    }

    /**
     * @param int $id
     * @return string|null
     * @throws Exception
     */
    public static function fetchGlobalNameById(int $id): ?string {

        $pdo = PDO::getInstance();

        $sql = 'SELECT `name` FROM `'.CONCLUDIS_TABLE_GLOBAL_SENIORITY.'` WHERE `global_id` = :id';

        $res = $pdo->selectOne($sql, [':id' => $id]);

        if($res === false) {
            return null;
        }

        return (string)$res['name'];
    }

}