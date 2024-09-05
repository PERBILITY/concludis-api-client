<?php


namespace Concludis\ApiClient\Storage;


use Concludis\ApiClient\Config\Baseconfig;
use Concludis\ApiClient\Database\PDO;
use Concludis\ApiClient\Resources\Location;
use Concludis\ApiClient\Resources\Place;
use Exception;

class LocationRepository {

    /**
     * @param Location $location
     * @return bool
     * @throws Exception
     */
    public static function save(Location $location): bool {

        self::fulfillLatLonFromCache($location);

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
        `address` = :address, 
        `external_id` = :external_id, 
        `region_id` = :region_id, 
        `custom_text1` = :custom1, 
        `custom_text2` = :custom2, 
        `custom_text3` = :custom3, 
        `lat` = :lat, 
        `lon` = :lon, 
        `geocoding_source` = :geocoding_source ';

        if($pdo->insert($sql, [
            ':source_id' => $location->source_id,
            ':location_id' => $location->id,
            ':name' => $location->name,
            ':country_code' => $location->country_code,
            ':postal_code' => $location->postal_code,
            ':locality' => $location->locality,
            ':address' => $location->address,
            ':external_id' => $location->external_id,
            ':region_id' => $location->region?->id,
            ':custom1' => $location->custom1,
            ':custom2' => $location->custom2,
            ':custom3' => $location->custom3,
            ':lat' => $location->lat,
            ':lon' => $location->lon,
            ':geocoding_source' => $location->geocoding_source
        ])) {

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
        `address` = :address, 
        `external_id` = :external_id, 
        `region_id` = :region_id, 
        `custom_text1` = :custom1, 
        `custom_text2` = :custom2, 
        `custom_text3` = :custom3 , 
        `lat` = :lat, 
        `lon` = :lon, 
        `geocoding_source` = :geocoding_source                                    
        WHERE `source_id` = :source_id AND `location_id` = :location_id';

        if($pdo->update($sql, [
            ':source_id' => $location->source_id,
            ':location_id' => $location->id,
            ':name' => $location->name,
            ':country_code' => $location->country_code,
            ':postal_code' => $location->postal_code,
            ':locality' => $location->locality,
            ':address' => $location->address,
            ':external_id' => $location->external_id,
            ':region_id' => $location->region?->id,
            ':custom1' => $location->custom1,
            ':custom2' => $location->custom2,
            ':custom3' => $location->custom3,
            ':lat' => $location->lat,
            ':lon' => $location->lon,
            ':geocoding_source' => $location->geocoding_source
        ])) {

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

    /**
     * @param int $limit
     * @return Location[]
     * @throws Exception
     */
    public static function fetchGeocodableLocationsStack(int $limit = 50): array  {

        $pdo = PDO::getInstance();

        $sql = 'SELECT *  FROM `'.CONCLUDIS_TABLE_LOCAL_LOCATION.'` WHERE 
        ( (`lat` IS NULL AND `lon` IS NULL) OR `geocoding_source` = ' . Location::GEOCODING_SOURCE_FALLBACK . ')
        AND `country_code` != ""
        AND `postal_code` != ""
        AND `locality` != ""
        AND `address` != ""
        
        AND NOT EXISTS(
        SELECT 1 FROM `'.CONCLUDIS_TABLE_CACHE.'` WHERE `key` = CONCAT("geocode:", SHA1(CONCAT(`country_code`, "::", `postal_code`, "::", `locality`, "::", `address`)))
        ) LIMIT ' . $limit;

        $res =  $pdo->select($sql);

        return array_map(static function(array $data) {
            return new Location($data);
        }, $res);
    }

    /**
     * @param Location $location
     * @return void
     */
    public static function fulfillLatLonFromCache(Location $location): void  {

        $has_geocode = $location->lat !== null && $location->lon !== null;
        $source_fallback = $location->geocoding_source === Location::GEOCODING_SOURCE_FALLBACK;

        if($has_geocode && !$source_fallback) {
            return;
        }

        if(!empty($location->country_code)
            && !empty($location->postal_code)
            && !empty($location->locality)
            && !empty($location->address)) {

            $key = 'geocode:' . sha1(
            $location->country_code . '::' .
                $location->postal_code . '::' .
                $location->locality . '::' .
                $location->address
            );

            try {
                $data = CacheRepository::fetch($key);
                $result = $data['response'] ?? null;
                if(is_array($result)) {
                    $lat = $result['lat'] ?? null;
                    $lon = $result['lon'] ?? null;
                    if($lat !== null && $lon !== null) {
                        $location->lat = (float)$lat;
                        $location->lon = (float)$lon;
                        $location->geocoding_source = Location::GEOCODING_SOURCE_GOOGLE;
                        return;
                    }
                }
            } catch (Exception) {
                // do nothing on read error
            }
        }

        if(!empty($location->country_code)
            && !empty($location->postal_code)
            && !empty($location->locality)) {
            try {
                $place = PlaceRepository::factory()
                    ->addFilter(PlaceRepository::FILTER_TYPE_COUNTRY_CODE, $location->country_code)
                    ->addFilter(PlaceRepository::FILTER_TYPE_POSTAL_CODE, $location->postal_code)
                    ->addFilter(PlaceRepository::FILTER_TYPE_PLACE_NAME, $location->locality)
                    ->fetchOne();

                if ($place !== null) {
                    $location->lat = $place->lat;
                    $location->lon = $place->lon;
                    $location->geocoding_source = Location::GEOCODING_SOURCE_FALLBACK;
                }
            } catch (Exception) {
                // do nothing on read error
            }
        }

    }

    /**
     * @param Location $location
     * @return Place|null
     * @throws Exception
     */
    public static function fetchPlaceByLocation(Location $location): ? Place {

        return PlaceRepository::factory()
            ->addFilter(PlaceRepository::FILTER_TYPE_COUNTRY_CODE, $location->country_code)
            ->addFilter(PlaceRepository::FILTER_TYPE_POSTAL_CODE, $location->postal_code)
            ->addFilter(PlaceRepository::FILTER_TYPE_PLACE_NAME, $location->locality)
            ->fetchOne();

    }

}