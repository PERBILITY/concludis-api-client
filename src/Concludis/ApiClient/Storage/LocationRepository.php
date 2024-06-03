<?php


namespace Concludis\ApiClient\Storage;


use Concludis\ApiClient\Config\Baseconfig;
use Concludis\ApiClient\Database\PDO;
use Concludis\ApiClient\Resources\Location;
use Exception;

class LocationRepository {

    /**
     * @param Location $location
     * @return bool
     * @throws Exception
     */
    public static function save(Location $location): bool {

        if (self::exists($location)) {
            return self::update($location);
        }

        return self::insert($location);
    }

    /**
     * @param Location $location
     * @return bool
     * @throws Exception
     */
    private static function insert(Location $location): bool {

        $pdo = PDO::getInstance();

        $sql = 'INSERT INTO `'.CONCLUDIS_TABLE_LOCAL_LOCATION.'` SET 
        `source_id` = :source_id, 
        `location_id` = :location_id, 
        `name` = :name, 
        `country_code` = :country_code, 
        `postal_code` = :postal_code, 
        `locality` = :locality, 
        `external_id` = :external_id, 
        `region_id` = :region_id, 
        `custom_text1` = :custom1, 
        `custom_text2` = :custom2, 
        `custom_text3` = :custom3, 
        `lat` = :lat, 
        `lon` = :lon';

        $ph = [
            ':source_id' => $location->source_id,
            ':location_id' => $location->id,
            ':name' => $location->name,
            ':country_code' => $location->country_code,
            ':postal_code' => $location->postal_code,
            ':locality' => $location->locality,
            ':external_id' => $location->external_id,
            ':region_id' => $location->region?->id,
            ':custom1' => $location->custom1,
            ':custom2' => $location->custom2,
            ':custom3' => $location->custom3,
            ':lat' => $location->lat,
            ':lon' => $location->lon
        ];

        if($pdo->insert($sql, $ph)) {

            if($location->region !== null) {
                RegionRepository::save($location->region);
            }
            return true;
        }
        return false;
    }

    /**
     * @param Location $location
     * @return bool
     * @throws Exception
     */
    private static function update(Location $location): bool {

        $pdo = PDO::getInstance();

        $sql = 'UPDATE `'.CONCLUDIS_TABLE_LOCAL_LOCATION.'` SET 
        `name` = :name, 
        `country_code` = :country_code, 
        `postal_code` = :postal_code, 
        `locality` = :locality, 
        `external_id` = :external_id, 
        `region_id` = :region_id, 
        `custom_text1` = :custom1, 
        `custom_text2` = :custom2, 
        `custom_text3` = :custom3 , 
        `lat` = :lat, 
        `lon` = :lon                                    
        WHERE `source_id` = :source_id AND `location_id` = :location_id';

        $ph = [
            ':source_id' => $location->source_id,
            ':location_id' => $location->id,
            ':name' => $location->name,
            ':country_code' => $location->country_code,
            ':postal_code' => $location->postal_code,
            ':locality' => $location->locality,
            ':external_id' => $location->external_id,
            ':region_id' => $location->region?->id,
            ':custom1' => $location->custom1,
            ':custom2' => $location->custom2,
            ':custom3' => $location->custom3,
            ':lat' => $location->lat,
            ':lon' => $location->lon
        ];

        if($pdo->update($sql, $ph)) {

            if($location->region !== null) {
                RegionRepository::save($location->region);
            }

            return true;
        }
        return false;
    }

    /**
     * @param Location $location
     * @return bool|null
     * @throws Exception
     */
    public static function exists(Location $location): ?bool {

        $pdo = PDO::getInstance();

        $sql = 'SELECT COUNT(*) AS `cnt` FROM `'.CONCLUDIS_TABLE_LOCAL_LOCATION.'` 
        WHERE `source_id` = :source_id AND `location_id` = :location_id';

        $res = $pdo->selectOne($sql, [
            ':source_id' => $location->source_id,
            ':location_id' => $location->id
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

        $sql = 'SELECT DISTINCT `source_id` FROM `'.CONCLUDIS_TABLE_LOCAL_LOCATION.'`';

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

        $sql = 'DELETE FROM `'.CONCLUDIS_TABLE_LOCAL_LOCATION.'` WHERE `source_id` = :source_id';

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
        $sql = 'SELECT `l`.`source_id`, `l`.`location_id` FROM `'.CONCLUDIS_TABLE_LOCAL_LOCATION.'` `l` 
        LEFT JOIN `'.CONCLUDIS_TABLE_PROJECT_LOCATION.'` `p`  ON (`l`.`location_id` = `p`.`location_id` AND `l`.`source_id` = `p`.`source_id`)        
        WHERE `p`.`project_id` IS NULL';

        if($source_id !== null) {
            $sql .= ' AND `l`.`source_id` = :source_id';
            $ph = [':source_id' => $source_id];
        }

        $res = $pdo->select($sql, $ph);

        $to_delete = [];
        foreach($res as $r){
            $key = $r['source_id'] . '::' . $r['location_id'];
            $to_delete[$key] = [
                'source_id' => $r['source_id'],
                'location_id' => $r['location_id']
            ];
        }

        foreach($to_delete as $v){
            $sql = 'DELETE FROM `'.CONCLUDIS_TABLE_LOCAL_LOCATION.'` WHERE `source_id` = :source_id AND `location_id` = :location_id';
            $pdo->delete($sql, [
                ':source_id' => $v['source_id'],
                ':location_id' => $v['location_id']
            ]);
        }
    }

}