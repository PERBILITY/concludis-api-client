<?php


namespace Concludis\ApiClient\Storage;


use Concludis\ApiClient\Config\Baseconfig;
use Concludis\ApiClient\Database\PDO;
use Concludis\ApiClient\Resources\Schedule;
use Exception;

class ScheduleRepository {

    /**
     * @param Schedule $element
     * @return bool
     * @throws Exception
     */
    public static function save(Schedule $element): bool {

        if (self::exists($element)) {
            return self::update($element);
        }

        return self::insert($element);
    }

    /**
     * @param Schedule $element
     * @return bool
     * @throws Exception
     */
    private static function insert(Schedule $element): bool {

        $pdo = PDO::getInstance();

        $sql = 'INSERT INTO `'.CONCLUDIS_TABLE_LOCAL_SCHEDULE.'` SET 
        `source_id` = :source_id, 
        `schedule_id` = :schedule_id, 
        `global_schedule_id` = :global_schedule_id,
        `locale` = :locale,
        `name` = :name';

        $ph = [
            ':source_id' => $element->source_id,
            ':schedule_id' => $element->id,
            ':global_schedule_id' => $element->global_id,
            ':name' => $element->name,
            ':locale' => $element->locale
        ];

        return $pdo->insert($sql, $ph);
    }

    /**
     * @param Schedule $element
     * @return bool
     * @throws Exception
     */
    private static function update(Schedule $element): bool {

        $pdo = PDO::getInstance();

        $sql = 'UPDATE `'.CONCLUDIS_TABLE_LOCAL_SCHEDULE.'` SET 
        `name` = :name,
        `locale` = :locale,
        `global_schedule_id` = :global_schedule_id                                      
        WHERE `source_id` = :source_id AND `schedule_id` = :schedule_id';

        $ph = [
            ':source_id' => $element->source_id,
            ':schedule_id' => $element->id,
            ':global_schedule_id' => $element->global_id,
            ':name' => $element->name,
            ':locale' => $element->locale
        ];

        return $pdo->update($sql, $ph);
    }

    /**
     * @param Schedule $element
     * @return bool|null
     * @throws Exception
     */
    public static function exists(Schedule $element): ?bool {

        $pdo = PDO::getInstance();

        $sql = 'SELECT COUNT(*) AS `cnt` FROM `'.CONCLUDIS_TABLE_LOCAL_SCHEDULE.'` 
        WHERE `source_id` = :source_id AND `schedule_id` = :schedule_id';

        $res = $pdo->selectOne($sql, [
            ':source_id' => $element->source_id,
            ':schedule_id' => $element->id
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

        $sql = 'SELECT DISTINCT `source_id` FROM `'.CONCLUDIS_TABLE_LOCAL_SCHEDULE.'`';

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

        $sql = 'DELETE FROM `'.CONCLUDIS_TABLE_LOCAL_SCHEDULE.'` WHERE `source_id` = :source_id';

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
        $sql = 'SELECT `l`.`source_id`, `l`.`schedule_id` FROM `'.CONCLUDIS_TABLE_LOCAL_SCHEDULE.'` `l` 
        LEFT JOIN `'.CONCLUDIS_TABLE_PROJECT_SCHEDULE.'` `p`  ON (`l`.`schedule_id` = `p`.`schedule_id` AND `l`.`source_id` = `p`.`source_id`)        
        WHERE `p`.`project_id` IS NULL';

        if($source_id !== null) {
            $sql .= ' AND `l`.`source_id` = :source_id';
            $ph = [':source_id' => $source_id];
        }

        $res = $pdo->select($sql, $ph);

        $to_delete = [];
        foreach($res as $r){
            $key = $r['source_id'] . '::' . $r['schedule_id'];
            $to_delete[$key] = [
                'source_id' => $r['source_id'],
                'schedule_id' => $r['schedule_id']
            ];
        }

        foreach($to_delete as $v){
            $sql = 'DELETE FROM `'.CONCLUDIS_TABLE_LOCAL_SCHEDULE.'` WHERE `source_id` = :source_id AND `schedule_id` = :schedule_id';
            $pdo->delete($sql, [
                ':source_id' => $v['source_id'],
                ':schedule_id' => $v['schedule_id']
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

        $sql = 'SELECT `name` FROM `'.CONCLUDIS_TABLE_GLOBAL_SCHEDULE.'` WHERE `global_id` = :id';

        $res = $pdo->selectOne($sql, [':id' => $id]);

        if($res === false) {
            return null;
        }

        return (string)$res['name'];
    }

}