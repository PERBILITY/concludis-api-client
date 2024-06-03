<?php


namespace Concludis\ApiClient\Storage;


use Concludis\ApiClient\Config\Baseconfig;
use Concludis\ApiClient\Database\PDO;
use Concludis\ApiClient\Resources\Group;
use Exception;

class GroupRepository {

    /**
     * @param Group $element
     * @param int $group_key
     * @return bool
     * @throws Exception
     */
    public static function save(Group $element, int $group_key): bool {

        if (self::exists($element, $group_key)) {
            return self::update($element, $group_key);
        }

        return self::insert($element, $group_key);
    }

    /**
     * @param Group $element
     * @param int $group_key
     * @return bool
     * @throws Exception
     */
    private static function insert(Group $element, int $group_key): bool {

        $pdo = PDO::getInstance();

        $sql = 'INSERT INTO `'.CONCLUDIS_TABLE_LOCAL_GROUP.'` SET 
        `source_id` = :source_id, 
        `group_id` = :group_id, 
        `group_key` = :group_key, 
        `name` = :name, 
        `locale` = :locale';

        $ph = [
            ':source_id' => $element->source_id,
            ':group_id' => $element->id,
            ':group_key' => $group_key,
            ':name' => $element->name,
            ':locale' => $element->locale
        ];

        return $pdo->insert($sql, $ph);
    }

    /**
     * @param Group $element
     * @param int $group_key
     * @return bool
     * @throws Exception
     */
    private static function update(Group $element, int $group_key): bool {

        $pdo = PDO::getInstance();

        $sql = 'UPDATE `'.CONCLUDIS_TABLE_LOCAL_GROUP.'` SET 
        `name` = :name, `locale` = :locale                             
        WHERE `source_id` = :source_id AND `group_id` = :group_id AND `group_key` = :group_key';

        $ph = [
            ':source_id' => $element->source_id,
            ':group_id' => $element->id,
            ':group_key' => $group_key,
            ':name' => $element->name,
            ':locale' => $element->locale
        ];

        return $pdo->update($sql, $ph);
    }

    /**
     * @param Group $element
     * @param int $group_key
     * @return bool|null
     * @throws Exception
     */
    public static function exists(Group $element, int $group_key): ?bool {

        $pdo = PDO::getInstance();

        $sql = 'SELECT COUNT(*) AS `cnt` FROM `'.CONCLUDIS_TABLE_LOCAL_GROUP.'` 
        WHERE `source_id` = :source_id AND `group_id` = :group_id AND `group_key` = :group_key';

        $res = $pdo->selectOne($sql, [
            ':source_id' => $element->source_id,
            ':group_id' => $element->id,
            ':group_key' => $group_key
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

        $sql = 'SELECT DISTINCT `source_id` FROM `'.CONCLUDIS_TABLE_LOCAL_GROUP.'`';

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

        $sql = 'DELETE FROM `'.CONCLUDIS_TABLE_LOCAL_GROUP.'` WHERE `source_id` = :source_id';

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
        $sql = 'SELECT `l`.`source_id`, `l`.`group_id`, `l`.`group_key`  FROM `'.CONCLUDIS_TABLE_LOCAL_GROUP.'` `l` 
        LEFT JOIN `'.CONCLUDIS_TABLE_PROJECT_GROUP.'` `p`  ON (
            `l`.`group_id` = `p`.`group_id` AND `l`.`group_key` = `p`.`group_key` AND `l`.`source_id` = `p`.`source_id`
        )        
        WHERE `p`.`project_id` IS NULL';

        if($source_id !== null) {
            $sql .= ' AND `l`.`source_id` = :source_id';
            $ph = [':source_id' => $source_id];
        }

        $res = $pdo->select($sql, $ph);

        $to_delete = [];
        foreach($res as $r){
            $key = $r['source_id'] . '::' . $r['group_id'] . '::' . $r['group_key'];
            $to_delete[$key] = [
                'source_id' => $r['source_id'],
                'group_id' => $r['group_id'],
                'group_key' => $r['group_key']
            ];
        }

        foreach($to_delete as $v){
            $sql = 'DELETE FROM `'.CONCLUDIS_TABLE_LOCAL_GROUP.'` WHERE `source_id` = :source_id AND `group_id` = :group_id AND `group_key` = :group_key';
            $pdo->delete($sql, [
                ':source_id' => $v['source_id'],
                ':group_id' => $v['group_id'],
                ':group_key' => $v['group_key']
            ]);
        }
    }


}