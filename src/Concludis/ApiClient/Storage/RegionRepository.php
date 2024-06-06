<?php


namespace Concludis\ApiClient\Storage;


use Concludis\ApiClient\Config\Baseconfig;
use Concludis\ApiClient\Database\PDO;
use Concludis\ApiClient\Resources\Element;
use Exception;

class RegionRepository {

    /**
     * @param string $source_id
     * @param int $region_id
     * @return Element|null
     * @throws Exception
     */
    public static function fetchById(string $source_id, int $region_id): ?Element {

        $pdo = PDO::getInstance();

        $sql = 'SELECT `source_id`, `region_id` AS `id`, `name` FROM `'.CONCLUDIS_TABLE_LOCAL_REGION.'` WHERE 
        `source_id` = :source_id AND `region_id` = :region_id';

        $res = $pdo->selectOne($sql, [
            ':source_id' => $source_id,
            ':region_id' => $region_id
        ]);

        if(!is_array($res)) {
            return null;
        }

        return new Element($res);
    }

    /**
     * @param Element $element
     * @return bool
     * @throws Exception
     */
    public static function save(Element $element): bool {

        if (self::exists($element)) {
            return self::update($element);
        }

        return self::insert($element);
    }

    /**
     * @param Element $element
     * @return bool
     * @throws Exception
     */
    private static function insert(Element $element): bool {

        $pdo = PDO::getInstance();

        $sql = 'INSERT INTO `'.CONCLUDIS_TABLE_LOCAL_REGION.'` SET 
        `source_id` = :source_id, 
        `region_id` = :region_id, 
        `name` = :name';

        $ph = [
            ':source_id' => $element->source_id,
            ':region_id' => $element->id,
            ':name' => $element->name
        ];

        return $pdo->insert($sql, $ph);
    }

    /**
     * @param Element $element
     * @return bool
     * @throws Exception
     */
    private static function update(Element $element): bool {

        $pdo = PDO::getInstance();

        $sql = 'UPDATE `'.CONCLUDIS_TABLE_LOCAL_REGION.'` SET 
        `name` = :name                             
        WHERE `source_id` = :source_id AND `region_id` = :region_id';

        $ph = [
            ':source_id' => $element->source_id,
            ':region_id' => $element->id,
            ':name' => $element->name
        ];

        return $pdo->update($sql, $ph);
    }

    /**
     * @param Element $element
     * @return bool|null
     * @throws Exception
     */
    public static function exists(Element $element): ?bool {

        $pdo = PDO::getInstance();

        $sql = 'SELECT COUNT(*) AS `cnt` FROM `'.CONCLUDIS_TABLE_LOCAL_REGION.'` 
        WHERE `source_id` = :source_id AND `region_id` = :region_id';

        $res = $pdo->selectOne($sql, [
            ':source_id' => $element->source_id,
            ':region_id' => $element->id
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

        $sql = 'SELECT DISTINCT `source_id` FROM `'.CONCLUDIS_TABLE_LOCAL_REGION.'`';

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

        $sql = 'DELETE FROM `'.CONCLUDIS_TABLE_LOCAL_REGION.'` WHERE `source_id` = :source_id';

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
        $sql = 'SELECT `l`.`source_id`, `l`.`region_id` FROM `'.CONCLUDIS_TABLE_LOCAL_REGION.'` `l` 
        LEFT JOIN `'.CONCLUDIS_TABLE_LOCAL_LOCATION.'` `p`  ON (`l`.`region_id` = `p`.`region_id` AND `l`.`source_id` = `p`.`source_id`)        
        WHERE `p`.`location_id` IS NULL';

        if($source_id !== null) {
            $sql .= ' AND `l`.`source_id` = :source_id';
            $ph = [':source_id' => $source_id];
        }

        $res = $pdo->select($sql, $ph);

        $to_delete = [];
        foreach($res as $r){
            $key = $r['source_id'] . '::' . $r['region_id'];
            $to_delete[$key] = [
                'source_id' => $r['source_id'],
                'region_id' => $r['region_id']
            ];
        }

        foreach($to_delete as $v){
            $sql = 'DELETE FROM `'.CONCLUDIS_TABLE_LOCAL_REGION.'` WHERE `source_id` = :source_id AND `region_id` = :region_id';
            $pdo->delete($sql, [
                ':source_id' => $v['source_id'],
                ':region_id' => $v['region_id']
            ]);
        }
    }

}