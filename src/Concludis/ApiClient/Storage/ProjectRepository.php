<?php


namespace Concludis\ApiClient\Storage;


use Concludis\ApiClient\Config\Baseconfig;
use Concludis\ApiClient\Database\PDO;
use Concludis\ApiClient\Resources\Element;
use Concludis\ApiClient\Resources\JobadContainer;
use Concludis\ApiClient\Resources\Location;
use Concludis\ApiClient\Resources\Place;
use Concludis\ApiClient\Resources\Project;
use Concludis\ApiClient\Resources\Schedule;
use Concludis\ApiClient\Util\ArrayUtil;
use DateTime;
use Exception;
use PDOStatement;
use RuntimeException;
use TypeError;

use function in_array;

class ProjectRepository {

    public const FILTER_TYPE_SOURCE = 'source';
    /**
     * Filter by "internal publication" or "public publication"
     * Possible values: one of the INT_PUB_* constants
     */
    public const FILTER_TYPE_INT_PUB = 'int_pub';
    public const FILTER_TYPE_KEYWORD = 'keyword';
    public const FILTER_TYPE_SENIORITY = 'seniority';
    public const FILTER_TYPE_SCHEDULE = 'schedule';
    public const FILTER_TYPE_CLASSIFICATION = 'classification';
    public const FILTER_TYPE_CATEGORY = 'category';
    public const FILTER_TYPE_COMPANY = 'company';
    public const FILTER_TYPE_LOCATION = 'location';
    public const FILTER_TYPE_GROUP1 = 'group1';
    public const FILTER_TYPE_GROUP2 = 'group2';
    public const FILTER_TYPE_GROUP3 = 'group3';
    public const FILTER_TYPE_BOARD = 'board';
    public const FILTER_TYPE_DATE_FROM_PUBLIC = 'date_from_public';
    public const FILTER_TYPE_DATE_FROM_INTERNAL = 'date_from_internal';

    public const FILTER_TYPE_MERGED_COMPANY = 'merged_company';
    public const FILTER_TYPE_MERGED_GROUP1 = 'merged_group1';
    public const FILTER_TYPE_MERGED_GROUP2 = 'merged_group2';
    public const FILTER_TYPE_MERGED_GROUP3 = 'merged_group3';

    public const FILTER_TYPE_GLOBAL_SENIORITY = 'global_seniority';
    public const FILTER_TYPE_GLOBAL_SCHEDULE = 'global_schedule';
    public const FILTER_TYPE_GLOBAL_CLASSIFICATION = 'global_classification';
    public const FILTER_TYPE_GLOBAL_CATEGORY = 'global_category';

    public const FILTER_TYPE_INDEED_ENABLED = 'indeed_enabled';
    public const FILTER_TYPE_CUSTOM = 'custom';

    private const FILTER_TYPE_PAGINATION = 'pagination';
    private const FILTER_TYPE_RADIUS = 'radius';

    public const ORDER_FIELD_SOURCE_ID = '`project`.`source_id`';
    public const ORDER_FIELD_PROJECT_ID = '`project`.`project_id`';
    public const ORDER_FIELD_PROJECT_DATE_FROM_PUBLIC = '`project`.`date_from_public`';
    public const ORDER_FIELD_PROJECT_DATE_FROM_INTERNAL = '`project`.`date_from_internal`';
    public const ORDER_FIELD_LOCATION_LOCALITY = '`local_location`.`locality`';
    public const ORDER_FIELD_GLOBAL_SENIORITY_NAME = '`seniority`.`name`';
    public const ORDER_FIELD_LOCAL_SENIORITY_NAME = '`local_seniority`.`name`';
    public const ORDER_FIELD_GLOBAL_SCHEDULE_NAME = '`schedule`.`name`';
    public const ORDER_FIELD_LOCAL_SCHEDULE_NAME = '`local_schedule`.`name`';
    public const ORDER_FIELD_GLOBAL_CLASSIFICATION_NAME = '`classification`.`name`';
    public const ORDER_FIELD_LOCAL_CLASSIFICATION_NAME = '`local_classification`.`name`';
    public const ORDER_FIELD_GLOBAL_CATEGORY_NAME = '`category`.`name`';
    public const ORDER_FIELD_LOCAL_CATEGORY_NAME = '`local_category`.`name`';

    public const INT_PUB_PUBLIC = 1;
    public const INT_PUB_INTERNAL = 2;

    private array $filter = [];
    private array $order = [];

    private static array $joins = [

        'location' => 'LEFT JOIN `'.CONCLUDIS_TABLE_PROJECT_LOCATION.'` `location` ' .
            'ON (`location`.`project_id` = `project`.`project_id` AND `location`.`source_id` = `project`.`source_id`)',

        'local_location' => 'LEFT JOIN `'.CONCLUDIS_TABLE_LOCAL_LOCATION.'` `local_location` ' .
            'ON (`location`.`location_id` = `local_location`.`location_id` AND `location`.`source_id` = `local_location`.`source_id`)',

        'company' => 'LEFT JOIN `'.CONCLUDIS_TABLE_PROJECT_COMPANY.'` `company` ' .
            'ON (`company`.`project_id` = `project`.`project_id` AND `company`.`source_id` = `project`.`source_id`)',

        'local_company' => 'LEFT JOIN `'.CONCLUDIS_TABLE_LOCAL_COMPANY.'` `local_company` ' .
            'ON (`company`.`source_id` = `local_company`.`source_id` AND `company`.`company_id` = `local_company`.`company_id`)',

        'group' => 'LEFT JOIN `'.CONCLUDIS_TABLE_PROJECT_GROUP.'` `group` ' .
            'ON (`group`.`project_id` = `project`.`project_id` AND `group`.`source_id` = `project`.`source_id`)',

        'local_group' => 'LEFT JOIN `'.CONCLUDIS_TABLE_LOCAL_GROUP.'` `local_group` ' .
            'ON (`group`.`source_id` = `local_group`.`source_id` AND `group`.`group_id` = `local_group`.`group_id` AND `group`.`group_key` = `local_group`.`group_key`)',

        'seniority' => 'LEFT JOIN `'.CONCLUDIS_TABLE_PROJECT_SENIORITY.'` `seniority` ' .
            'ON (`seniority`.`project_id` = `project`.`project_id` AND `seniority`.`source_id` = `project`.`source_id`)',

        'local_seniority' => 'LEFT JOIN `'.CONCLUDIS_TABLE_LOCAL_SENIORITY.'` `local_seniority` ' .
            'ON (`seniority`.`source_id` = `local_seniority`.`source_id` AND `seniority`.`seniority_id` = `local_seniority`.`seniority_id`)',

        'global_seniority' => 'LEFT JOIN `'.CONCLUDIS_TABLE_GLOBAL_SENIORITY.'` `global_seniority` ' .
            'ON (`local_seniority`.`source_id` = `global_seniority`.`source_id` AND `local_seniority`.`global_seniority_id` = `global_seniority`.`global_id`)',

        'schedule' => 'LEFT JOIN `'.CONCLUDIS_TABLE_PROJECT_SCHEDULE.'` `schedule` ' .
            'ON (`schedule`.`project_id` = `project`.`project_id` AND `schedule`.`source_id` = `project`.`source_id`)',

        'local_schedule' => 'LEFT JOIN `'.CONCLUDIS_TABLE_LOCAL_SCHEDULE.'` `local_schedule` ' .
            'ON (`schedule`.`source_id` = `local_schedule`.`source_id` AND `schedule`.`schedule_id` = `local_schedule`.`schedule_id`)',

        'global_schedule' => 'LEFT JOIN `'.CONCLUDIS_TABLE_GLOBAL_SCHEDULE.'` `global_schedule` ' .
            'ON (`local_schedule`.`source_id` = `global_schedule`.`source_id` AND `local_schedule`.`global_schedule_id` = `global_schedule`.`global_id`)',

        'classification' => 'LEFT JOIN `'.CONCLUDIS_TABLE_PROJECT_CLASSIFICATION.'` `classification` ' .
            'ON (`classification`.`project_id` = `project`.`project_id` AND `classification`.`source_id` = `project`.`source_id`)',

        'local_classification' => 'LEFT JOIN `'.CONCLUDIS_TABLE_LOCAL_CLASSIFICATION.'` `local_classification` ' .
            'ON (`classification`.`source_id` = `local_classification`.`source_id` AND `classification`.`classification_id` = `local_classification`.`classification_id`)',

        'global_classification' => 'LEFT JOIN `'.CONCLUDIS_TABLE_GLOBAL_CLASSIFICATION.'` `global_classification` ' .
            'ON (`local_classification`.`source_id` = `global_classification`.`source_id` AND `local_classification`.`global_classification_id` = `global_classification`.`global_id`)',

        'category' => 'LEFT JOIN `'.CONCLUDIS_TABLE_PROJECT_CATEGORY.'` `category` ' .
            'ON (`category`.`project_id` = `project`.`project_id` AND `category`.`source_id` = `project`.`source_id`)',

        'category_with_distinct_occupation' => 'LEFT JOIN `'.CONCLUDIS_TABLE_PROJECT_CATEGORY.'` `category` ' .
            'ON (`category`.`project_id` = `project`.`project_id` AND `category`.`source_id` = `project`.`source_id` AND `category`.`occupation_id` = (' .
            'SELECT occupation_id FROM tbl_project_category WHERE source_id = `project`.`source_id` AND project_id = `project`.`project_id` LIMIT 1' .
            '))',

        'local_category' => 'LEFT JOIN `'.CONCLUDIS_TABLE_LOCAL_CATEGORY.'` `local_category` ' .
            'ON (`category`.`source_id` = `local_category`.`source_id` AND `category`.`category_id` = `local_category`.`category_id`)',

        'global_category' => 'LEFT JOIN `'.CONCLUDIS_TABLE_GLOBAL_CATEGORY.'` `global_category` ' .
            'ON (`local_category`.`source_id` = `global_category`.`source_id` AND `local_category`.`global_category_id` = `global_category`.`global_id`)',

        'ad_container' => 'LEFT JOIN `'.CONCLUDIS_TABLE_PROJECT_AD_CONTAINER.'` `ad_container` ' .
            'ON (`ad_container`.`project_id` = `project`.`project_id` AND `ad_container`.`source_id` = `project`.`source_id`)',

        'board' => 'LEFT JOIN `'.CONCLUDIS_TABLE_PROJECT_BOARD.'` `board` ' .
            'ON (`board`.`project_id` = `project`.`project_id` AND `board`.`source_id` = `project`.`source_id`)',

        'local_board' => 'LEFT JOIN `'.CONCLUDIS_TABLE_LOCAL_BOARD.'` `local_board` ' .
            'ON (`board`.`source_id` = `local_board`.`source_id` AND `board`.`board_id` = `local_board`.`board_id`)',
    ];


    public function addFilter(string $filter_type, $value): ProjectRepository {

        $this->filter[$filter_type] = $value;

        return $this;
    }

    /**
     * @param string $field
     * @param bool $asc
     *
     * @return ProjectRepository
     */
    public function addOrder(string $field, bool $asc = true): ProjectRepository{

        $this->order[$field] = $asc;

        return $this;
    }

    /**
     * @param string $country_code
     * @param string $postal_code
     * @param string $place_name
     * @param float $distance_km
     * @param Place|null $place
     * @return $this
     * @throws Exception
     */
    public function radius(string $country_code, string $postal_code, string $place_name, float $distance_km, ?Place &$place = null): ProjectRepository {

        $place = PlaceRepository::factory()
            ->addFilter(PlaceRepository::FILTER_TYPE_COUNTRY_CODE, $country_code)
            ->addFilter(PlaceRepository::FILTER_TYPE_POSTAL_CODE, $postal_code)
            ->addFilter(PlaceRepository::FILTER_TYPE_PLACE_NAME, $place_name)
            ->fetchOne();

        if($place !== null) {
            $this->filter[self::FILTER_TYPE_RADIUS] = [
                'lat' => $place->lat,
                'lon' => $place->lon,
                'km' => $distance_km
            ];
        }


        return $this;
    }

    public function paginate(int $limit, int $offset): ProjectRepository {
        $this->filter[self::FILTER_TYPE_PAGINATION] = [
            'limit' => $limit,
            'offset' => $offset
        ];
        return $this;
    }

    /**
     * @param int $count
     * @param bool $count_only
     * @return Project[]
     * @throws Exception
     */
    public function fetch(int &$count = -1, bool $count_only = false): array {

        $query_parts = $this->createQueryParts();
        $query = $this->prepareQueryParts($query_parts);

        if(array_key_exists('radius', $query_parts)) {
            $this->addOrder('distance');
        }

        if (array_key_exists(self::FILTER_TYPE_PAGINATION, $this->filter)) {
            $tmp_filter = $this->filter[self::FILTER_TYPE_PAGINATION];
            $limit = array_key_exists('limit', $tmp_filter) ? (int)$tmp_filter['limit'] : 0;
            $offset = array_key_exists('offset', $tmp_filter) ? (int)$tmp_filter['offset'] : 0;
            $query['limit'] = 'LIMIT ' . $offset . ',' . $limit;
        }

        if(!empty($this->order)){
            $order_parts = [];
            foreach ($this->order as $k => $v) {
                $order_parts[] = $k . ' ' . ($v ? 'ASC' : 'DESC');
            }
            $query['order'] = 'ORDER BY ' . implode(',', $order_parts);
        }

        $pdo = PDO::getInstance();

        if($count === 0) {
            $query_count = 'SELECT COUNT(*) AS `cnt`
                FROM `'.CONCLUDIS_TABLE_PROJECT.'` `project` ' .
                (!empty($query['join']) ? "\n" . implode(" \n", $query['join']) . " \n" : '') .
                'WHERE 1 ' .
                (!empty($query['where']) ? "\n" . 'AND ' . implode(' AND ', $query['where']) . " \n" : '');

            $res_count = $pdo->selectOne($query_count, $query['ph']);

            if($res_count !== false) {
                $count = $res_count['cnt'];
            }
        }

        if ($count_only) {
            return [];
        }

        $query_select = 'SELECT `project`.`source_id`, `project`.`project_id`, project.data ' .
            (!empty($query['addons']) ? ",\n" . implode(" ,\n", $query['addons']) . " \n" : '') .
            ' FROM `'.CONCLUDIS_TABLE_PROJECT.'` `project` ' .
            (!empty($query['join']) ? "\n" . implode(" \n", $query['join']) . " \n" : '') .
            'WHERE 1 ' .
            (!empty($query['where']) ? "\n" . 'AND ' . implode(' AND ', $query['where']) . " \n" : '') .
//            'GROUP BY `project`.`project_id`, `project`.`source_id` ' .
            (!empty($query['order']) ? $query['order'] . " \n" : '') .
            (!empty($query['limit']) ? $query['limit'] . " \n" : '');

        //         echo $query_select; exit();
//          var_export( $query['ph']); exit();
        $query_phs = array_merge($query['ph'], $query['ph_addons']);
        $res = $pdo->select($query_select, $query_phs);

        $data = [];
        foreach ($res as $d) {
            $data[] = new Project(json_decode($d['data'], true, 512, JSON_THROW_ON_ERROR));
        }

        return $data;
    }

    /**
     * @return array
     * @throws Exception
     */
    public function fetchMapData(): array {

        $query_parts = $this->createQueryParts(true);

        foreach ($query_parts as &$v) {
            if(!array_key_exists('join', $v)){
                continue;
            }
            $v['join'] = array_diff($v['join'], ['location']);
        }
        unset($v);

        $query = $this->prepareQueryParts($query_parts);

        $pdo = PDO::getInstance();

        $query_select = 'SELECT `location`.`map_data` ' .
            (!empty($query['addons']) ? ",\n" . implode(" ,\n", $query['addons']) . " \n" : '') .
            ' FROM `'.CONCLUDIS_TABLE_PROJECT_LOCATION.'` `location` ' .
            'LEFT JOIN `'.CONCLUDIS_TABLE_PROJECT.'` `project` ' .
            'ON (`location`.`project_id` = `project`.`project_id` AND `location`.`source_id` = `project`.`source_id`)' .
            (!empty($query['join']) ? "\n" . implode(" \n", $query['join']) . " \n" : '') .
            'WHERE 1 ' .
            (!empty($query['where']) ? "\n" . 'AND ' . implode(' AND ', $query['where']) . " \n" : '') .
            'GROUP BY `location`.`source_id`, `location`.`project_id`, `location`.`location_id`  ' .
            (!empty($query['limit']) ? $query['limit'] . " \n" : '');

        $query_phs = array_merge($query['ph'], $query['ph_addons']);

        $res = $pdo->select($query_select, $query_phs);

        $data = [];
        foreach ($res as $d) {
            $data[] = json_decode($d['map_data'], true, 512, JSON_THROW_ON_ERROR);
        }

        return $data;
    }

    /**
     * Fetches location-inflated projects.
     * That means that we return a single project multiple times! One result for each assigned
     * location as long as it matches the filter criteria.
     * Be careful. The project object is manipulated here. It holds max one location in locations property.
     *
     * @param int $count
     * @return Project[]
     * @throws Exception
     */
    public function fetchLocationInflated(int &$count = -1): array {

        $query_parts = $this->createQueryParts(true);

        $query_parts['location_inflation'] = [
            'join' => ['location', 'local_location']
        ];

        if(array_key_exists('radius', $query_parts)) {
            $this->addOrder('distance');
        }

        $query = $this->prepareQueryParts($query_parts);

        if (array_key_exists(self::FILTER_TYPE_PAGINATION, $this->filter)) {
            $tmp_filter = $this->filter[self::FILTER_TYPE_PAGINATION];
            $limit = array_key_exists('limit', $tmp_filter) ? (int)$tmp_filter['limit'] : 0;
            $offset = array_key_exists('offset', $tmp_filter) ? (int)$tmp_filter['offset'] : 0;
            $query['limit'] = 'LIMIT ' . $offset . ',' . $limit;
        }

        if(!empty($this->order)){
            $order_parts = [];
            foreach ($this->order as $k => $v) {
                $order_parts[] = $k . ' ' . ($v ? 'ASC' : 'DESC');
            }
            $query['order'] = 'ORDER BY ' . implode(',', $order_parts);
        }

        $pdo = PDO::getInstance();

        if($count === 0) {
            $query_count = 'SELECT COUNT(DISTINCT CONCAT(`project`.`source_id`, "-", `project`.`project_id`, "-", COALESCE(`location`.`location_id`, 0)) ) AS `cnt`
                FROM `'.CONCLUDIS_TABLE_PROJECT.'` `project` ' .
                (!empty($query['join']) ? "\n" . implode(" \n", $query['join']) . " \n" : '') .
                'WHERE 1 ' .
                (!empty($query['where']) ? "\n" . 'AND ' . implode(' AND ', $query['where']) . " \n" : '');

            $res_count = $pdo->selectOne($query_count, $query['ph']);

            if($res_count !== false) {
                $count = $res_count['cnt'];
            }
        }

        $query_select = 'SELECT `project`.*, `location`.*' .
            (!empty($query['addons']) ? ",\n" . implode(" ,\n", $query['addons']) . " \n" : '') .
            ' FROM `'.CONCLUDIS_TABLE_PROJECT.'` `project` ' .
            (!empty($query['join']) ? "\n" . implode(" \n", $query['join']) . " \n" : '') .
            'WHERE 1 ' .
            (!empty($query['where']) ? "\n" . 'AND ' . implode(' AND ', $query['where']) . " \n" : '') .
//            'GROUP BY `project`.`source_id`, `project`.`project_id`, `location`.`location_id` ' .
            (!empty($query['order']) ? $query['order'] . " \n" : '') .
            (!empty($query['limit']) ? $query['limit'] . " \n" : '');

//                 echo $query_select; exit();
//        var_export( $query['ph']); exit();

        $query_phs = array_merge($query['ph'], $query['ph_addons']);

        $res = $pdo->select($query_select, $query_phs);

        $data = [];
        foreach ($res as $d) {

            $location_id = $d['location_id'];
            $project = new Project(json_decode($d['data'], true, 512, JSON_THROW_ON_ERROR));

            $project->locations = array_values(array_filter($project->locations, static function(Location $loc) use ($location_id) {
                return $loc->id === $location_id;
            }));

            $data[] = $project;
        }

        return $data;
    }


    /**
     * Exec callback for location-inflated projects.
     * That means that we exec the given callback for a single project multiple times! One time for each assigned
     * location as long as it matches the filter criteria.
     * Be careful. The project object is manipulated here. It holds max one location in locations property.
     *
     * @throws Exception
     */
    public function execLocationInflated(callable $callback): void {

        $query_parts = $this->createQueryParts(true);

        $query_parts['location_inflation'] = [
            'join' => ['location', 'local_location']
        ];

        if(array_key_exists('radius', $query_parts)) {
            $this->addOrder('distance');
        }

        $query = $this->prepareQueryParts($query_parts);

        if (array_key_exists(self::FILTER_TYPE_PAGINATION, $this->filter)) {
            $tmp_filter = $this->filter[self::FILTER_TYPE_PAGINATION];
            $limit = array_key_exists('limit', $tmp_filter) ? (int)$tmp_filter['limit'] : 0;
            $offset = array_key_exists('offset', $tmp_filter) ? (int)$tmp_filter['offset'] : 0;
            $query['limit'] = 'LIMIT ' . $offset . ',' . $limit;
        }

        if(!empty($this->order)){
            $order_parts = [];
            foreach ($this->order as $k => $v) {
                $order_parts[] = $k . ' ' . ($v ? 'ASC' : 'DESC');
            }
            $query['order'] = 'ORDER BY ' . implode(',', $order_parts);
        }

        $pdo = PDO::getInstance();

        $query_select = 'SELECT `project`.*, `location`.*' .
            (!empty($query['addons']) ? ",\n" . implode(" ,\n", $query['addons']) . " \n" : '') .
            ' FROM `'.CONCLUDIS_TABLE_PROJECT.'` `project` ' .
            (!empty($query['join']) ? "\n" . implode(" \n", $query['join']) . " \n" : '') .
            'WHERE 1 ' .
            (!empty($query['where']) ? "\n" . 'AND ' . implode(' AND ', $query['where']) . " \n" : '') .
//            'GROUP BY `project`.`source_id`, `project`.`project_id`, `location`.`location_id`  ' .
            (!empty($query['order']) ? $query['order'] . " \n" : '') .
            (!empty($query['limit']) ? $query['limit'] . " \n" : '');

        $query_phs = array_merge($query['ph'], $query['ph_addons']);

        $pdo->select($query_select, $query_phs, static function(PDOStatement $stmt) use ($callback) {

            foreach($stmt as $d) {

                $location_id = $d['location_id'];
                $project = new Project(json_decode($d['data'], true, 512, JSON_THROW_ON_ERROR));

                $project->locations = array_values(array_filter($project->locations, static function(Location $loc) use ($location_id) {
                    return $loc->id === $location_id;
                }));

                try {
                    $callback($project);
                } catch (TypeError $e) {
                    throw new RuntimeException('TypeError occurred on callback', 0, $e);
                } catch (Exception $e) {
                    throw new RuntimeException('Exception occurred on callback', 0, $e);
                }

            }
        });
    }

    /**
     * @param $id
     * @return Project
     * @throws Exception
     */
    public static function fetchProjectById($id): Project {

        $parts = explode('-', $id);

        if(!$parts || count($parts) !== 2) {
            throw new RuntimeException('invalid project id');
        }

        $source_id = $parts[0] ?? '';
        $project_id = $parts[1] ?? '';

        $pdo = PDO::getInstance();

        $sql = 'SELECT `data` FROM `'.CONCLUDIS_TABLE_PROJECT.'` `p` WHERE `p`.`source_id` = :source_id AND `p`.`project_id` = :project_id';

        $res = $pdo->selectOne($sql, [
            'source_id' => $source_id,
            'project_id' => $project_id
        ]);

        if($res === false) {
            throw new RuntimeException('project not found');
        }

        return new Project(json_decode($res['data'], true, 512, JSON_THROW_ON_ERROR));
    }

    /**
     * @param bool $location_inflated
     * @return array
     */
    private function createQueryParts(bool $location_inflated = false): array {

        $ln = "\n";

        $query_parts = [];

        $query_parts['default'] = [];

        if (array_key_exists(self::ORDER_FIELD_GLOBAL_SENIORITY_NAME, $this->order)) {
            $query_parts['default']['join'][] = 'seniority';
            $query_parts['default']['join'][] = 'local_seniority';
            $query_parts['default']['join'][] = 'global_seniority';
        }
        if (array_key_exists(self::ORDER_FIELD_LOCAL_SENIORITY_NAME, $this->order)) {
            $query_parts['default']['join'][] = 'seniority';
            $query_parts['default']['join'][] = 'local_seniority';
        }
        if (array_key_exists(self::ORDER_FIELD_GLOBAL_SCHEDULE_NAME, $this->order)) {
            $query_parts['default']['join'][] = 'schedule';
            $query_parts['default']['join'][] = 'local_schedule';
            $query_parts['default']['join'][] = 'global_schedule';
        }
        if (array_key_exists(self::ORDER_FIELD_LOCAL_SCHEDULE_NAME, $this->order)) {
            $query_parts['default']['join'][] = 'schedule';
            $query_parts['default']['join'][] = 'local_schedule';
        }
        if (array_key_exists(self::ORDER_FIELD_GLOBAL_CLASSIFICATION_NAME, $this->order)) {
            $query_parts['default']['join'][] = 'classification';
            $query_parts['default']['join'][] = 'local_classification';
            $query_parts['default']['join'][] = 'global_classification';
        }
        if (array_key_exists(self::ORDER_FIELD_LOCAL_CLASSIFICATION_NAME, $this->order)) {
            $query_parts['default']['join'][] = 'classification';
            $query_parts['default']['join'][] = 'local_classification';
        }
        if (array_key_exists(self::ORDER_FIELD_GLOBAL_CATEGORY_NAME, $this->order)) {
            $query_parts['default']['join'][] = 'category_with_distinct_occupation';
            $query_parts['default']['join'][] = 'local_category';
            $query_parts['default']['join'][] = 'global_category';
        }
        if (array_key_exists(self::ORDER_FIELD_LOCAL_CATEGORY_NAME, $this->order)) {
            $query_parts['default']['join'][] = 'category_with_distinct_occupation';
            $query_parts['default']['join'][] = 'local_category';
        }

        if (array_key_exists(self::FILTER_TYPE_SOURCE, $this->filter)) {
            $tmp_filter = $this->filter[self::FILTER_TYPE_SOURCE];

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
                    'where' => '`project`.`source_id` IN (:source_in)',
                    'ph' => [':source_in' => $source_in]
                ];
            }
            if (!empty($source_not_in)) {
                $query_parts['source_not_in'] = [
                    'where' => '`project`.`source_id` NOT IN (:source_not_in)',
                    'ph' => [':source_not_in' => $source_not_in]
                ];
            }
        }

        if (array_key_exists(self::FILTER_TYPE_RADIUS, $this->filter)) {
            $tmp_filter = $this->filter[self::FILTER_TYPE_RADIUS];
            if (is_array($tmp_filter)
                && array_key_exists('lat', $tmp_filter)
                && array_key_exists('lon', $tmp_filter)
                && array_key_exists('km', $tmp_filter)
            ) {
                $query_parts['radius'] = [
                    'addons' => [
                        '(' . $ln .
                            'SELECT COALESCE(MIN(
                                ( 6371 * 
                                    acos( 
                                        cos( radians(:addon_lat_1) ) 
                                        * cos( radians( `local_location`.`lat` ) ) 
                                        * cos( radians( `local_location`.`lon` ) - radians(:addon_lon) )
                                        + sin( radians(:addon_lat_2) ) 
                                        * sin( radians( `local_location`.`lat` ) ) 
                                    ) 
                                )), 10000
                            ) FROM `'.CONCLUDIS_TABLE_PROJECT_LOCATION.'` `location1` ' . $ln .
                            ' JOIN `'.CONCLUDIS_TABLE_LOCAL_LOCATION.'` `local_location` ' . $ln .
                            '     ON (`location1`.`location_id` = `local_location`.`location_id` AND `location1`.`source_id` = `local_location`.`source_id`) ' . $ln .
                            ' WHERE `location1`.`project_id` = `project`.`project_id` AND `location1`.`source_id` = `project`.`source_id` ' . $ln .
                            ($location_inflated ? ' AND `location1`.`location_id` = `location`.`location_id` ' . $ln : '') .
                        ') as `distance`'
                    ],
                    'where' => 'EXISTS(' .
                        '   SELECT 1 FROM `'.CONCLUDIS_TABLE_PROJECT_LOCATION.'` `location1` ' . $ln .
                        '   JOIN `'.CONCLUDIS_TABLE_LOCAL_LOCATION.'` `local_location` ' . $ln .
                        '       ON (`location1`.`location_id` = `local_location`.`location_id` AND `location1`.`source_id` = `local_location`.`source_id`)' . $ln .
                        '   WHERE `location1`.`project_id` = `project`.`project_id` AND `location1`.`source_id` = `project`.`source_id` ' . $ln .
                        ($location_inflated ? '   AND `location1`.`location_id` = `location`.`location_id` ' . $ln : '') .
                        '   AND COALESCE(
                                ( 6371 * 
                                    acos( 
                                        cos( radians(:lat_1) ) 
                                        * cos( radians( `local_location`.`lat` ) ) 
                                        * cos( radians( `local_location`.`lon` ) - radians(:lon) )
                                        + sin( radians(:lat_2) ) 
                                        * sin( radians( `local_location`.`lat` ) ) 
                                    ) 
                                ), 10000
                            ) < :distance' . $ln .
                        ')',
                    'ph' => [
                        ':lat_1' => (float)$tmp_filter['lat'],
                        ':lat_2' => (float)$tmp_filter['lat'],
                        ':lon' => (float)$tmp_filter['lon'],
                        ':distance' => (float)$tmp_filter['km']
                    ],
                    'ph_addons' => [
                        ':addon_lat_1' => (float)$tmp_filter['lat'],
                        ':addon_lat_2' => (float)$tmp_filter['lat'],
                        ':addon_lon' => (float)$tmp_filter['lon']
                    ]
                ];
            }
        }

        if (array_key_exists(self::FILTER_TYPE_COMPANY, $this->filter)) {

            $tmp_filter = self::getFilter($this->filter[self::FILTER_TYPE_COMPANY], 'int');

            if (!empty($tmp_filter)) {

                $find_ids = array_values(array_diff($tmp_filter, [-1]));
                $find_others = in_array(-1, $tmp_filter, true);

                $tmp_query_parts = [];
                $placeholders = [];

                if (!empty($find_ids)) {
                    $tmp_query_parts[] = 'EXISTS(' . $ln .
                        '   SELECT 1 FROM `' . CONCLUDIS_TABLE_PROJECT_COMPANY . '` `company` ' . $ln .
                        '   WHERE `company`.`project_id` = `project`.`project_id` AND `company`.`source_id` = `project`.`source_id` ' . $ln .
                        '   AND `company`.`company_id` IN (:companies) ' . $ln .
                        ') ' . $ln;
                    $placeholders[':companies'] = $find_ids;
                }

                if($find_others) {
                    $tmp_query_parts[] = '(' . $ln .
                        '   SELECT 1 FROM `' . CONCLUDIS_TABLE_PROJECT_COMPANY . '` `company` ' . $ln .
                        '   WHERE `company`.`project_id` = `project`.`project_id` AND `company`.`source_id` = `project`.`source_id` ' . $ln .
                        '   LIMIT 1' . $ln .
                        ') IS NULL ' . $ln;
                }

                if(!empty($tmp_query_parts)) {
                    $query_parts['company'] = [
                        'where' => '(' . implode(' OR ', $tmp_query_parts) . ')',
                        'ph' => $placeholders
                    ];
                }
            }
        }
        else if (array_key_exists(self::FILTER_TYPE_MERGED_COMPANY, $this->filter)) {

            $tmp_filter = self::getFilter($this->filter[self::FILTER_TYPE_MERGED_COMPANY], 'string');

            if (!empty($tmp_filter)) {

                $find_ids = array_values(array_diff($tmp_filter, [sha1('-1')]));
                $find_others = in_array(sha1('-1'), $tmp_filter, true);

                $tmp_query_parts = [];
                $placeholders = [];

                if (!empty($find_ids)) {
                    $tmp_query_parts[] = 'EXISTS(' . $ln .
                        '   SELECT 1 FROM `' . CONCLUDIS_TABLE_PROJECT_COMPANY . '` `company` ' . $ln .
                        '      JOIN `'.CONCLUDIS_TABLE_LOCAL_COMPANY.'` `local_company` ' . $ln .
                        '       ON (`company`.`source_id` = `local_company`.`source_id` AND `company`.`company_id` = `local_company`.`company_id`) ' . $ln .
                        '   WHERE `company`.`project_id` = `project`.`project_id` AND `company`.`source_id` = `project`.`source_id` ' . $ln .
                        '   AND SHA1(`local_company`.`name`) IN (:companies) ' . $ln .
                        ') ' . $ln;
                    $placeholders[':companies'] = $find_ids;
                }

                if($find_others) {
                    $tmp_query_parts[] = '(' . $ln .
                        '   SELECT 1 FROM `' . CONCLUDIS_TABLE_PROJECT_COMPANY . '` `company` ' . $ln .
                        '   WHERE `company`.`project_id` = `project`.`project_id` AND `company`.`source_id` = `project`.`source_id` ' . $ln .
                        '   LIMIT 1' . $ln .
                        ') IS NULL ' . $ln;
                }

                if(!empty($tmp_query_parts)) {
                    $query_parts['company'] = [
                        'where' => '(' . implode(' OR ', $tmp_query_parts) . ')',
                        'ph' => $placeholders
                    ];
                }
            }
        }

        if (array_key_exists(self::FILTER_TYPE_LOCATION, $this->filter)) {

            $tmp_filter = self::getFilter($this->filter[self::FILTER_TYPE_LOCATION], 'string');

            if (!empty($tmp_filter)) {

                $find_ids = array_values(array_diff($tmp_filter, [sha1('-1')]));
                $find_others = in_array(sha1('-1'), $tmp_filter, true);

                $tmp_query_parts = [];
                $placeholders = [];

                if (!empty($find_ids)) {
                    $tmp_query_parts[] = 'EXISTS(' . $ln .
                        '   SELECT 1 FROM `' . CONCLUDIS_TABLE_PROJECT_LOCATION . '` `location` ' . $ln .
                        '      JOIN `'.CONCLUDIS_TABLE_LOCAL_LOCATION.'` `local_location` ' . $ln .
                        '       ON (`location`.`source_id` = `local_location`.`source_id` AND `location`.`location_id` = `local_location`.`location_id`) ' . $ln .
                        '   WHERE `location`.`project_id` = `project`.`project_id` AND `location`.`source_id` = `project`.`source_id` ' . $ln .
                        '   AND SHA1(`local_location`.`locality`) IN (:locations) ' . $ln .
                        ') ' . $ln;
                    $placeholders[':locations'] = $find_ids;
                }

                if($find_others) {
                    $tmp_query_parts[] = '(' . $ln .
                        '   SELECT 1 FROM `' . CONCLUDIS_TABLE_PROJECT_LOCATION . '` `location` ' . $ln .
                        '   WHERE `location`.`project_id` = `project`.`project_id` AND `location`.`source_id` = `project`.`source_id` ' . $ln .
                        '   LIMIT 1' . $ln .
                        ') IS NULL ' . $ln;
                }

                if(!empty($tmp_query_parts)) {
                    $query_parts['location'] = [
                        'where' => '(' . implode(' OR ', $tmp_query_parts) . ')',
                        'ph' => $placeholders
                    ];
                }
            }
        }

        if (array_key_exists(self::FILTER_TYPE_BOARD, $this->filter)) {

            $tmp_filter = self::getFilter($this->filter[self::FILTER_TYPE_BOARD], 'int');

            if (!empty($tmp_filter)) {

                $find_ids = array_values(array_diff($tmp_filter, [-1]));
                $find_others = in_array(-1, $tmp_filter, true);

                $tmp_query_parts = [];
                $placeholders = [];

                if (!empty($find_ids)) {
                    $tmp_query_parts[] = 'EXISTS(' . $ln .
                        '   SELECT 1 FROM `' . CONCLUDIS_TABLE_PROJECT_BOARD . '` `board` ' . $ln .
                        '   WHERE `board`.`project_id` = `project`.`project_id` AND `board`.`source_id` = `project`.`source_id` ' . $ln .
                        '   AND `board`.`board_id` IN (:boards) ' . $ln .
                        ') ' . $ln;
                    $placeholders[':boards'] = $find_ids;
                }

                if($find_others) {
                    $tmp_query_parts[] = '(' . $ln .
                        '   SELECT 1 FROM `' . CONCLUDIS_TABLE_PROJECT_BOARD . '` `board` ' . $ln .
                        '   WHERE `board`.`project_id` = `project`.`project_id` AND `board`.`source_id` = `project`.`source_id` ' . $ln .
                        '   LIMIT 1' . $ln .
                        ') IS NULL ' . $ln;
                }

                if(!empty($tmp_query_parts)) {
                    $query_parts['board'] = [
                        'where' => '(' . implode(' OR ', $tmp_query_parts) . ')',
                        'ph' => $placeholders
                    ];
                }
            }
        }

        if (array_key_exists(self::FILTER_TYPE_GROUP1, $this->filter)) {

            $tmp_filter = self::getFilter($this->filter[self::FILTER_TYPE_GROUP1], 'int');

            if (!empty($tmp_filter)) {

                $find_ids = array_values(array_diff($tmp_filter, [-1]));
                $find_others = in_array(-1, $tmp_filter, true);

                $tmp_query_parts = [];
                $placeholders = [];

                if (!empty($find_ids)) {
                    $tmp_query_parts[] = 'EXISTS(' . $ln .
                        '   SELECT 1 FROM `' . CONCLUDIS_TABLE_PROJECT_GROUP . '` `group` ' . $ln .
                        '   WHERE `group`.`project_id` = `project`.`project_id` AND `group`.`source_id` = `project`.`source_id` AND `group`.`group_key` = 1 ' . $ln .
                        '   AND `group`.`group_id` IN (:groups1) ' . $ln .
                        ') ' . $ln;
                    $placeholders[':groups1'] = $find_ids;
                }

                if($find_others) {
                    $tmp_query_parts[] = '(' . $ln .
                        '   SELECT 1 FROM `'.CONCLUDIS_TABLE_PROJECT_GROUP.'` `group` ' . $ln .
                        '   WHERE `group`.`project_id` = `project`.`project_id` AND `group`.`source_id` = `project`.`source_id` AND `group`.`group_key` = 1 ' . $ln .
                        '   LIMIT 1' . $ln .
                        ') IS NULL ' . $ln;
                }

                if(!empty($tmp_query_parts)) {
                    $query_parts['group1'] = [
                        'where' => '(' . implode(' OR ', $tmp_query_parts) . ')',
                        'ph' => $placeholders
                    ];
                }
            }
        }
        else if (array_key_exists(self::FILTER_TYPE_MERGED_GROUP1, $this->filter)) {

            $tmp_filter = self::getFilter($this->filter[self::FILTER_TYPE_MERGED_GROUP1], 'string');

            if (!empty($tmp_filter)) {

                $find_ids = array_values(array_diff($tmp_filter, [sha1('-1')]));
                $find_others = in_array(sha1('-1'), $tmp_filter, true);

                $tmp_query_parts = [];
                $placeholders = [];

                if (!empty($find_ids)) {
                    $tmp_query_parts[] = 'EXISTS(' . $ln .
                        '   SELECT 1 FROM `' . CONCLUDIS_TABLE_PROJECT_GROUP . '` `group` ' . $ln .
                        '      JOIN `'.CONCLUDIS_TABLE_LOCAL_GROUP.'` `local_group` ' . $ln .
                        '       ON (`group`.`source_id` = `local_group`.`source_id` AND `group`.`group_id` = `local_group`.`group_id` AND `group`.`group_key` = `local_group`.`group_key`) ' . $ln .
                        '   WHERE `group`.`project_id` = `project`.`project_id` AND `group`.`source_id` = `project`.`source_id` AND `group`.`group_key` = 1 ' . $ln .
                        '   AND SHA1(`local_group`.`name`) IN (:groups1) ' . $ln .
                        ') ' . $ln;
                    $placeholders[':groups1'] = $find_ids;
                }

                if($find_others) {
                    $tmp_query_parts[] = '(' . $ln .
                        '   SELECT 1 FROM `'.CONCLUDIS_TABLE_PROJECT_GROUP.'` `group` ' . $ln .
                        '   WHERE `group`.`project_id` = `project`.`project_id` AND `group`.`source_id` = `project`.`source_id` AND `group`.`group_key` = 1 ' . $ln .
                        '   LIMIT 1' . $ln .
                        ') IS NULL ' . $ln;
                }

                if(!empty($tmp_query_parts)) {
                    $query_parts['group1'] = [
                        'where' => '(' . implode(' OR ', $tmp_query_parts) . ')',
                        'ph' => $placeholders
                    ];
                }
            }
        }

        if (array_key_exists(self::FILTER_TYPE_GROUP2, $this->filter)) {

            $tmp_filter = self::getFilter($this->filter[self::FILTER_TYPE_GROUP2], 'int');

            if (!empty($tmp_filter)) {

                $find_ids = array_values(array_diff($tmp_filter, [-1]));
                $find_others = in_array(-1, $tmp_filter, true);

                $tmp_query_parts = [];
                $placeholders = [];

                if (!empty($find_ids)) {
                    $tmp_query_parts[] = 'EXISTS(' . $ln .
                        '   SELECT 1 FROM `' . CONCLUDIS_TABLE_PROJECT_GROUP . '` `group` ' . $ln .
                        '   WHERE `group`.`project_id` = `project`.`project_id` AND `group`.`source_id` = `project`.`source_id` AND `group`.`group_key` = 2 ' . $ln .
                        '   AND `group`.`group_id` IN (:groups2) ' . $ln .
                        ') ' . $ln;
                    $placeholders[':groups2'] = $find_ids;
                }

                if($find_others) {
                    $tmp_query_parts[] = '(' . $ln .
                        '   SELECT 1 FROM `'.CONCLUDIS_TABLE_PROJECT_GROUP.'` `group` ' . $ln .
                        '   WHERE `group`.`project_id` = `project`.`project_id` AND `group`.`source_id` = `project`.`source_id` AND `group`.`group_key` = 2 ' . $ln .
                        '   LIMIT 1' . $ln .
                        ') IS NULL ' . $ln;
                }

                if(!empty($tmp_query_parts)) {
                    $query_parts['group2'] = [
                        'where' => '(' . implode(' OR ', $tmp_query_parts) . ')',
                        'ph' => $placeholders
                    ];
                }
            }
        }
        else if (array_key_exists(self::FILTER_TYPE_MERGED_GROUP2, $this->filter)) {

            $tmp_filter = self::getFilter($this->filter[self::FILTER_TYPE_MERGED_GROUP2], 'string');

            if (!empty($tmp_filter)) {

                $find_ids = array_values(array_diff($tmp_filter, [sha1('-1')]));
                $find_others = in_array(sha1('-1'), $tmp_filter, true);

                $tmp_query_parts = [];
                $placeholders = [];

                if (!empty($find_ids)) {
                    $tmp_query_parts[] = 'EXISTS(' . $ln .
                        '   SELECT 1 FROM `' . CONCLUDIS_TABLE_PROJECT_GROUP . '` `group` ' . $ln .
                        '      JOIN `'.CONCLUDIS_TABLE_LOCAL_GROUP.'` `local_group` ' . $ln .
                        '       ON (`group`.`source_id` = `local_group`.`source_id` AND `group`.`group_id` = `local_group`.`group_id` AND `group`.`group_key` = `local_group`.`group_key`) ' . $ln .
                        '   WHERE `group`.`project_id` = `project`.`project_id` AND `group`.`source_id` = `project`.`source_id` AND `group`.`group_key` = 2 ' . $ln .
                        '   AND SHA1(`local_group`.`name`) IN (:groups2) ' . $ln .
                        ') ' . $ln;
                    $placeholders[':groups2'] = $find_ids;
                }

                if($find_others) {
                    $tmp_query_parts[] = '(' . $ln .
                        '   SELECT 1 FROM `'.CONCLUDIS_TABLE_PROJECT_GROUP.'` `group` ' . $ln .
                        '   WHERE `group`.`project_id` = `project`.`project_id` AND `group`.`source_id` = `project`.`source_id` AND `group`.`group_key` = 2 ' . $ln .
                        '   LIMIT 1' . $ln .
                        ') IS NULL ' . $ln;
                }

                if(!empty($tmp_query_parts)) {
                    $query_parts['group2'] = [
                        'where' => '(' . implode(' OR ', $tmp_query_parts) . ')',
                        'ph' => $placeholders
                    ];
                }
            }
        }

        if (array_key_exists(self::FILTER_TYPE_GROUP3, $this->filter)) {

            $tmp_filter = self::getFilter($this->filter[self::FILTER_TYPE_GROUP3], 'int');

            if (!empty($tmp_filter)) {

                $find_ids = array_values(array_diff($tmp_filter, [-1]));
                $find_others = in_array(-1, $tmp_filter, true);

                $tmp_query_parts = [];
                $placeholders = [];

                if (!empty($find_ids)) {
                    $tmp_query_parts[] = 'EXISTS(' . $ln .
                        '   SELECT 1 FROM `' . CONCLUDIS_TABLE_PROJECT_GROUP . '` `group` ' . $ln .
                        '   WHERE `group`.`project_id` = `project`.`project_id` AND `group`.`source_id` = `project`.`source_id` AND `group`.`group_key` = 3 ' . $ln .
                        '   AND `group`.`group_id` IN (:groups3) ' . $ln .
                        ') ' . $ln;
                    $placeholders[':groups3'] = $find_ids;
                }

                if($find_others) {
                    $tmp_query_parts[] = '(' . $ln .
                        '   SELECT 1 FROM `'.CONCLUDIS_TABLE_PROJECT_GROUP.'` `group` ' . $ln .
                        '   WHERE `group`.`project_id` = `project`.`project_id` AND `group`.`source_id` = `project`.`source_id` AND `group`.`group_key` = 3 ' . $ln .
                        '   LIMIT 1' . $ln .
                        ') IS NULL ' . $ln;
                }

                if(!empty($tmp_query_parts)) {
                    $query_parts['group3'] = [
                        'where' => '(' . implode(' OR ', $tmp_query_parts) . ')',
                        'ph' => $placeholders
                    ];
                }
            }
        }
        else if (array_key_exists(self::FILTER_TYPE_MERGED_GROUP3, $this->filter)) {

            $tmp_filter = self::getFilter($this->filter[self::FILTER_TYPE_MERGED_GROUP3], 'string');

            if (!empty($tmp_filter)) {

                $find_ids = array_values(array_diff($tmp_filter, [sha1('-1')]));
                $find_others = in_array(sha1('-1'), $tmp_filter, true);

                $tmp_query_parts = [];
                $placeholders = [];

                if (!empty($find_ids)) {
                    $tmp_query_parts[] = 'EXISTS(' . $ln .
                        '   SELECT 1 FROM `' . CONCLUDIS_TABLE_PROJECT_GROUP . '` `group` ' . $ln .
                        '      JOIN `'.CONCLUDIS_TABLE_LOCAL_GROUP.'` `local_group` ' . $ln .
                        '       ON (`group`.`source_id` = `local_group`.`source_id` AND `group`.`group_id` = `local_group`.`group_id` AND `group`.`group_key` = `local_group`.`group_key`) ' . $ln .
                        '   WHERE `group`.`project_id` = `project`.`project_id` AND `group`.`source_id` = `project`.`source_id` AND `group`.`group_key` = 3 ' . $ln .
                        '   AND SHA1(`local_group`.`name`) IN (:groups3) ' . $ln .
                        ') ' . $ln;
                    $placeholders[':groups3'] = $find_ids;
                }

                if($find_others) {
                    $tmp_query_parts[] = '(' . $ln .
                        '   SELECT 1 FROM `'.CONCLUDIS_TABLE_PROJECT_GROUP.'` `group` ' . $ln .
                        '   WHERE `group`.`project_id` = `project`.`project_id` AND `group`.`source_id` = `project`.`source_id` AND `group`.`group_key` = 3 ' . $ln .
                        '   LIMIT 1' . $ln .
                        ') IS NULL ' . $ln;
                }

                if(!empty($tmp_query_parts)) {
                    $query_parts['group3'] = [
                        'where' => '(' . implode(' OR ', $tmp_query_parts) . ')',
                        'ph' => $placeholders
                    ];
                }
            }
        }

        if (array_key_exists(self::FILTER_TYPE_SENIORITY, $this->filter)) {

            $tmp_filter = self::getFilter($this->filter[self::FILTER_TYPE_SENIORITY], 'string');

            if (!empty($tmp_filter)) {

                $find_ids = array_values(array_diff($tmp_filter, ['-1']));
                $find_others = in_array('-1', $tmp_filter, true);

                $tmp_query_parts = [];
                $placeholders = [];

                if (!empty($find_ids)) {
                    $tmp_query_parts[] = 'EXISTS(' . $ln .
                        '   SELECT 1 FROM `' . CONCLUDIS_TABLE_PROJECT_SENIORITY . '` `seniority` ' . $ln .
                        '   WHERE `seniority`.`project_id` = `project`.`project_id` AND `seniority`.`source_id` = `project`.`source_id` ' . $ln .
                        '   AND SHA1(CONCAT(`seniority`.`source_id` , \'-\', `seniority`.`seniority_id`)) IN (:seniorities) ' . $ln .
                        ') ' . $ln;
                    $placeholders[':seniorities'] = $find_ids;
                }

                if ($find_others) {
                    $tmp_query_parts[] = '(' . $ln .
                        '   SELECT 1 FROM `' . CONCLUDIS_TABLE_PROJECT_SENIORITY . '` `seniority` ' . $ln .
                        '   WHERE `seniority`.`project_id` = `project`.`project_id` AND `seniority`.`source_id` = `project`.`source_id` ' . $ln .
                        '   LIMIT 1' . $ln .
                        ') IS NULL ' . $ln;
                }

                if (!empty($tmp_query_parts)) {
                    $query_parts['seniority'] = [
                        'where' => '(' . implode(' OR ', $tmp_query_parts) . ')',
                        'ph' => $placeholders
                    ];
                }
            }
        }
        else if (array_key_exists(self::FILTER_TYPE_GLOBAL_SENIORITY, $this->filter)) {

            $tmp_filter = self::getFilter($this->filter[self::FILTER_TYPE_GLOBAL_SENIORITY], 'int');

            if (!empty($tmp_filter)) {

                $find_ids = array_values(array_diff($tmp_filter, [-1]));
                $find_others = in_array(-1, $tmp_filter, true);

                $tmp_query_parts = [];
                $placeholders = [];

                if(!empty($find_ids)) {
                    $tmp_query_parts[] = 'EXISTS(' . $ln .
                        '   SELECT 1 FROM `'.CONCLUDIS_TABLE_PROJECT_SENIORITY.'` `seniority` ' . $ln .
                        '      JOIN `'.CONCLUDIS_TABLE_LOCAL_SENIORITY.'` `local_seniority` ' . $ln .
                        '       ON (`seniority`.`source_id` = `local_seniority`.`source_id` AND `seniority`.`seniority_id` = `local_seniority`.`seniority_id`) ' . $ln .
                        '   WHERE `seniority`.`project_id` = `project`.`project_id` AND `seniority`.`source_id` = `project`.`source_id` ' . $ln .
                        '   AND `local_seniority`.`global_seniority_id` IN (:seniorities) ' . $ln .
                        ') ' . $ln;
                    $placeholders[':seniorities'] = $find_ids;
                }

                if($find_others) {
                    $tmp_query_parts[] = '(' . $ln .
                        '   SELECT 1 FROM `'.CONCLUDIS_TABLE_PROJECT_SENIORITY.'` `seniority` ' . $ln .
                        '   WHERE `seniority`.`project_id` = `project`.`project_id` AND `seniority`.`source_id` = `project`.`source_id` ' . $ln .
                        '   LIMIT 1' . $ln .
                        ') IS NULL ' . $ln;
                }

                if(!empty($tmp_query_parts)) {
                    $query_parts['seniority'] = [
                        'where' => '(' . implode(' OR ', $tmp_query_parts) . ')',
                        'ph' => $placeholders
                    ];
                }
            }
        }

        if (array_key_exists(self::FILTER_TYPE_SCHEDULE, $this->filter)) {

            $tmp_filter = self::getFilter($this->filter[self::FILTER_TYPE_SCHEDULE], 'string');

            if (!empty($tmp_filter)) {

                // apply merge mapping
                if (!empty($source_in)) {
                    foreach($source_in as $source_id) {
                        $map_k_array = [];
                        foreach (Schedule::$merge_map as $map_k => $map_v_array) {
                            try {
                                $local_v_array = ScheduleRepository::fetchLocalIdsByGlobalIds($map_v_array, $source_id);
                                if(!empty(array_intersect($tmp_filter, $local_v_array))) {
                                    $map_k_array[] = $map_k;
                                }
                            } catch (Exception) {
                                continue;
                            }
                        }

                        try {
                            $local_k_array = ScheduleRepository::fetchLocalIdsByGlobalIds($map_k_array, $source_id);
                            foreach ($local_k_array as $local_k) {
                                $tmp_filter[] = $local_k;
                            }
                        } catch (Exception) {
                            continue;
                        }
                    }
                    $tmp_filter = array_unique($tmp_filter);
                }

                $find_ids = array_values(array_diff($tmp_filter, ['-1']));
                $find_others = in_array('-1', $tmp_filter, true);

                $tmp_query_parts = [];
                $placeholders = [];

                if (!empty($find_ids)) {
                    $tmp_query_parts[] = 'EXISTS(' . $ln .
                        '   SELECT 1 FROM `' . CONCLUDIS_TABLE_PROJECT_SCHEDULE . '` `schedule` ' . $ln .
                        '   WHERE `schedule`.`project_id` = `project`.`project_id` AND `schedule`.`source_id` = `project`.`source_id` ' . $ln .
                        '   AND SHA1(CONCAT(`schedule`.`source_id` , \'-\', `schedule`.`schedule_id`)) IN (:schedules) ' . $ln .
                        ') ' . $ln;
                    $placeholders[':schedules'] = $find_ids;
                }

                if ($find_others) {
                    $tmp_query_parts[] = '(' . $ln .
                        '   SELECT 1 FROM `' . CONCLUDIS_TABLE_PROJECT_SCHEDULE . '` `schedule` ' . $ln .
                        '   WHERE `schedule`.`project_id` = `project`.`project_id` AND `schedule`.`source_id` = `project`.`source_id` ' . $ln .
                        '   LIMIT 1' . $ln .
                        ') IS NULL ' . $ln;
                }

                if (!empty($tmp_query_parts)) {
                    $query_parts['schedule'] = [
                        'where' => '(' . implode(' OR ', $tmp_query_parts) . ')',
                        'ph' => $placeholders
                    ];
                }
            }
        }
        else if (array_key_exists(self::FILTER_TYPE_GLOBAL_SCHEDULE, $this->filter)) {

            $tmp_filter = self::getFilter($this->filter[self::FILTER_TYPE_GLOBAL_SCHEDULE], 'int');

            if (!empty($tmp_filter)) {

                // apply merge mapping
                foreach (Schedule::$merge_map as $map_k => $map_v_array) {
                    if(!empty(array_intersect($tmp_filter, $map_v_array))) {
                        $tmp_filter[] = $map_k;
                    }
                }
                $tmp_filter = array_unique($tmp_filter);

                $find_ids = array_values(array_diff($tmp_filter, [-1]));
                $find_others = in_array(-1, $tmp_filter, true);

                $tmp_query_parts = [];
                $placeholders = [];

                if(!empty($find_ids)) {
                    $tmp_query_parts[] = 'EXISTS(' . $ln .
                        '   SELECT 1 FROM `'.CONCLUDIS_TABLE_PROJECT_SCHEDULE.'` `schedule` ' . $ln .
                        '      JOIN `'.CONCLUDIS_TABLE_LOCAL_SCHEDULE.'` `local_schedule` ' . $ln .
                        '       ON (`schedule`.`source_id` = `local_schedule`.`source_id` AND `schedule`.`schedule_id` = `local_schedule`.`schedule_id`) ' . $ln .
                        '   WHERE `schedule`.`project_id` = `project`.`project_id` AND `schedule`.`source_id` = `project`.`source_id` ' . $ln .
                        '   AND `local_schedule`.`global_schedule_id` IN (:schedules) ' . $ln .
                        ') ' . $ln;
                    $placeholders[':schedules'] = $find_ids;
                }

                if($find_others) {
                    $tmp_query_parts[] = '(' . $ln .
                        '   SELECT 1 FROM `'.CONCLUDIS_TABLE_PROJECT_SCHEDULE.'` `schedule` ' . $ln .
                        '   WHERE `schedule`.`project_id` = `project`.`project_id` AND `schedule`.`source_id` = `project`.`source_id` ' . $ln .
                        '   LIMIT 1' . $ln .
                        ') IS NULL ' . $ln;
                }

                if(!empty($tmp_query_parts)) {
                    $query_parts['schedule'] = [
                        'where' => '(' . implode(' OR ', $tmp_query_parts) . ')',
                        'ph' => $placeholders
                    ];
                }
            }
        }

        if (array_key_exists(self::FILTER_TYPE_CLASSIFICATION, $this->filter)) {

            $tmp_filter = self::getFilter($this->filter[self::FILTER_TYPE_CLASSIFICATION], 'string');

            if (!empty($tmp_filter)) {

                $find_ids = array_values(array_diff($tmp_filter, ['-1']));
                $find_others = in_array('-1', $tmp_filter, true);

                $tmp_query_parts = [];
                $placeholders = [];

                if(!empty($find_ids)) {
                    $tmp_query_parts[] = 'EXISTS(' . $ln .
                        '   SELECT 1 FROM `'.CONCLUDIS_TABLE_PROJECT_CLASSIFICATION.'` `classification` ' . $ln .
                        '   WHERE `classification`.`project_id` = `project`.`project_id` AND `classification`.`source_id` = `project`.`source_id` ' . $ln .
                        '   AND SHA1(CONCAT(`classification`.`source_id` , \'-\', `classification`.`classification_id`)) IN (:classifications) ' . $ln .
                        ') ' . $ln;
                    $placeholders[':classifications'] = $find_ids;
                }

                if($find_others) {
                    $tmp_query_parts[] = '(' . $ln .
                        '   SELECT 1 FROM `'.CONCLUDIS_TABLE_PROJECT_CLASSIFICATION.'` `classification` ' . $ln .
                        '   WHERE `classification`.`project_id` = `project`.`project_id` AND `classification`.`source_id` = `project`.`source_id` ' . $ln .
                        '   LIMIT 1' . $ln .
                        ') IS NULL ' . $ln;
                }

                if(!empty($tmp_query_parts)) {
                    $query_parts['category'] = [
                        'where' => '(' . implode(' OR ', $tmp_query_parts) . ')',
                        'ph' => $placeholders
                    ];
                }
            }
        }
        else if (array_key_exists(self::FILTER_TYPE_GLOBAL_CLASSIFICATION, $this->filter)) {

            $tmp_filter = self::getFilter($this->filter[self::FILTER_TYPE_GLOBAL_CLASSIFICATION], 'int');

            if (!empty($tmp_filter)) {

                $find_ids = array_values(array_diff($tmp_filter, [-1]));
                $find_others = in_array(-1, $tmp_filter, true);

                $tmp_query_parts = [];
                $placeholders = [];

                if(!empty($find_ids)) {
                    $tmp_query_parts[] = 'EXISTS(' . $ln .
                        '   SELECT 1 FROM `'.CONCLUDIS_TABLE_PROJECT_CLASSIFICATION.'` `classification` ' . $ln .
                        '      JOIN `'.CONCLUDIS_TABLE_LOCAL_CLASSIFICATION.'` `local_classification` ' . $ln .
                        '       ON (`classification`.`source_id` = `local_classification`.`source_id` AND `classification`.`classification_id` = `local_classification`.`classification_id`) ' . $ln .
                        '   WHERE `classification`.`project_id` = `project`.`project_id` AND `classification`.`source_id` = `project`.`source_id` ' . $ln .
                        '   AND `local_classification`.`global_classification_id` IN (:classifications) ' . $ln .
                        ') ' . $ln;
                    $placeholders[':classifications'] = $find_ids;
                }

                if($find_others) {
                    $tmp_query_parts[] = '(' . $ln .
                        '   SELECT 1 FROM `'.CONCLUDIS_TABLE_PROJECT_CLASSIFICATION.'` `classification` ' . $ln .
                        '   WHERE `classification`.`project_id` = `project`.`project_id` AND `classification`.`source_id` = `project`.`source_id` ' . $ln .
                        '   LIMIT 1' . $ln .
                        ') IS NULL ' . $ln;
                }

                if(!empty($tmp_query_parts)) {
                    $query_parts['classification'] = [
                        'where' => '(' . implode(' OR ', $tmp_query_parts) . ')',
                        'ph' => $placeholders
                    ];
                }
            }
        }

        if (array_key_exists(self::FILTER_TYPE_CATEGORY, $this->filter)) {

            $tmp_filter = self::getFilter($this->filter[self::FILTER_TYPE_CATEGORY], 'string');

            if (!empty($tmp_filter)) {

                $find_ids = array_values(array_diff($tmp_filter, ['-1']));
                $find_others = in_array('-1', $tmp_filter, true);

                $tmp_query_parts = [];
                $placeholders = [];

                if(!empty($find_ids)) {
                    $tmp_query_parts[] = 'EXISTS(' . $ln .
                        '   SELECT 1 FROM `'.CONCLUDIS_TABLE_PROJECT_CATEGORY.'` `category` ' . $ln .
                        '   WHERE `category`.`project_id` = `project`.`project_id` AND `category`.`source_id` = `project`.`source_id` ' . $ln .
                        '   AND SHA1(CONCAT(`category`.`source_id` , \'-\', `category`.`category_id`)) IN (:categories) ' . $ln .
                        ') ' . $ln;
                    $placeholders[':categories'] = $find_ids;
                }

                if($find_others) {
                    $tmp_query_parts[] = '(' . $ln .
                        '   SELECT 1 FROM `'.CONCLUDIS_TABLE_PROJECT_CATEGORY.'` `category` ' . $ln .
                        '   WHERE `category`.`project_id` = `project`.`project_id` AND `category`.`source_id` = `project`.`source_id` ' . $ln .
                        '   LIMIT 1' . $ln .
                        ') IS NULL ' . $ln;
                }

                if(!empty($tmp_query_parts)) {
                    $query_parts['category'] = [
                        'where' => '(' . implode(' OR ', $tmp_query_parts) . ')',
                        'ph' => $placeholders
                    ];
                }
            }
        }
        else if (array_key_exists(self::FILTER_TYPE_GLOBAL_CATEGORY, $this->filter)) {

            $tmp_filter = self::getFilter($this->filter[self::FILTER_TYPE_GLOBAL_CATEGORY], 'int');

            if (!empty($tmp_filter)) {

                $find_ids = array_values(array_diff($tmp_filter, [-1]));
                $find_others = in_array(-1, $tmp_filter, true);

                $tmp_query_parts = [];
                $placeholders = [];

                if(!empty($find_ids)) {
                    $tmp_query_parts[] = 'EXISTS(' . $ln .
                        '   SELECT 1 FROM `'.CONCLUDIS_TABLE_PROJECT_CATEGORY.'` `category` ' . $ln .
                        '      JOIN `'.CONCLUDIS_TABLE_LOCAL_CATEGORY.'` `local_category` ' . $ln .
                        '       ON (`category`.`source_id` = `local_category`.`source_id` AND `category`.`category_id` = `local_category`.`category_id`) ' . $ln .
                        '   WHERE `category`.`project_id` = `project`.`project_id` AND `category`.`source_id` = `project`.`source_id` ' . $ln .
                        '   AND `local_category`.`global_category_id` IN (:categories) ' . $ln .
                        ') ' . $ln;
                    $placeholders[':categories'] = $find_ids;
                }

                if($find_others) {
                    $tmp_query_parts[] = '(' . $ln .
                        '   SELECT 1 FROM `'.CONCLUDIS_TABLE_PROJECT_CATEGORY.'` `category` ' . $ln .
                        '   WHERE `category`.`project_id` = `project`.`project_id` AND `category`.`source_id` = `project`.`source_id` ' . $ln .
                        '   LIMIT 1' . $ln .
                        ') IS NULL ' . $ln;
                }

                if(!empty($tmp_query_parts)) {
                    $query_parts['category'] = [
                        'where' => '(' . implode(' OR ', $tmp_query_parts) . ')',
                        'ph' => $placeholders
                    ];
                }
            }
        }

        $filter_int_pub = $this->filter[self::FILTER_TYPE_INT_PUB] ?? self::INT_PUB_PUBLIC;

        if($filter_int_pub === self::INT_PUB_INTERNAL) {
            $query_parts['int_pub'] = [
                'where' => '`project`.`published_internal` = 1'
            ];
        } else {
            $query_parts['int_pub'] = [
                'where' => '`project`.`published_public` = 1'
            ];
        }

        if (array_key_exists(self::FILTER_TYPE_INDEED_ENABLED, $this->filter)) {
            $filter_indeed_enabled = (bool)$this->filter[self::FILTER_TYPE_INT_PUB];

            if($filter_indeed_enabled === true) {
                $query_parts['indeed_enabled'] = [
                    'where' => 'CAST(JSON_UNQUOTE(JSON_EXTRACT(`data`, "$.indeed_enabled")) AS UNSIGNED) > 0'
                ];
            } else {
                $query_parts['indeed_enabled'] = [
                    'where' => 'CAST(JSON_UNQUOTE(JSON_EXTRACT(`data`, "$.indeed_enabled")) AS UNSIGNED) = 0'
                ];
            }
        }
        if (array_key_exists(self::FILTER_TYPE_CUSTOM, $this->filter) ) {
            $filter_custom = $this->filter[self::FILTER_TYPE_CUSTOM];
            if(is_array($filter_custom)) {
                foreach($filter_custom as $k => $v) {
                    $query_parts['custom' . $k] = [
                        'where' => $v['where'] ?? null,
                        'ph' => $v['ph'] ?? [],
                    ];
                }
            }
        }

        if (array_key_exists(self::FILTER_TYPE_KEYWORD, $this->filter)) {
            $tmp_filter = $this->filter[self::FILTER_TYPE_KEYWORD];

            if ($tmp_filter !== '') {
                if($filter_int_pub === self::INT_PUB_INTERNAL) {
                    $query_parts['keyword'] = [
                        'where' => 'EXISTS(' .
                            '   SELECT 1 FROM `'.CONCLUDIS_TABLE_PROJECT_AD_CONTAINER.'` `ad_container` ' .
                            '   WHERE `ad_container`.`project_id` = `project`.`project_id` ' .
                            '   AND `ad_container`.`source_id` = `project`.`source_id` ' .
                            '   AND (' .
                            '           ( ' .
                            '               `ad_container`.`content_internal` = "" ' .
                            '               AND MATCH(`ad_container`.`content_external`) AGAINST (:ad_container) ' .
                            '           ) OR ( ' .
                            '               MATCH(`ad_container`.`content_internal`) AGAINST (:ad_container2) ' .
                            '           ) ' .
                            '   ) ' .
                            ')',
                        'ph' => [
                            ':ad_container' => $tmp_filter,
                            ':ad_container2' => $tmp_filter
                        ]
                    ];
                } else {
                    $query_parts['keyword'] = [
                        'where' => 'EXISTS(' .
                            '   SELECT 1 FROM `'.CONCLUDIS_TABLE_PROJECT_AD_CONTAINER.'` `ad_container` ' .
                            '   WHERE `ad_container`.`project_id` = `project`.`project_id` ' .
                            '   AND `ad_container`.`source_id` = `project`.`source_id` ' .
                            '   AND MATCH(`ad_container`.`content_external`) AGAINST (:ad_container)' .
                            ')',
                        'ph' => [':ad_container' => $tmp_filter]
                    ];
                }
            }
        }

        if (array_key_exists(self::FILTER_TYPE_DATE_FROM_PUBLIC, $this->filter)) {
            $tmp_filter = $this->filter[self::FILTER_TYPE_DATE_FROM_PUBLIC];

            $start = array_key_exists('start', $tmp_filter) ? (string)$tmp_filter['start'] : '';
            $end = array_key_exists('end', $tmp_filter) ? (string)$tmp_filter['end'] : '';

            if(!empty($start) && !empty($end)) {
                $query_parts['date_from_public'] = [
                    'where' => '`project`.`date_from_public` BETWEEN :start AND :end',
                    'ph' => [
                        ':start' => $start,
                        ':end' => $end
                    ]
                ];
            } else if (!empty($start)) {
                $query_parts['date_from_public'] = [
                    'where' => '`project`.`date_from_public` >= :start',
                    'ph' => [
                        ':start' => $start
                    ]
                ];
            } else if (!empty($end)) {
                $query_parts['date_from_public'] = [
                    'where' => '`project`.`date_from_public` <= :end',
                    'ph' => [
                        ':end' => $end
                    ]
                ];
            }
        }


        if (array_key_exists(self::FILTER_TYPE_DATE_FROM_INTERNAL, $this->filter)) {
            $tmp_filter = $this->filter[self::FILTER_TYPE_DATE_FROM_INTERNAL];

            $start = array_key_exists('start', $tmp_filter) ? (string)$tmp_filter['start'] : '';
            $end = array_key_exists('end', $tmp_filter) ? (string)$tmp_filter['end'] : '';

            if(!empty($start) && !empty($end)) {
                $query_parts['date_from_internal'] = [
                    'where' => '`project`.`date_from_internal` BETWEEN :start AND :end',
                    'ph' => [
                        ':start' => $start,
                        ':end' => $end
                    ]
                ];
            } else if (!empty($start)) {
                $query_parts['date_from_internal'] = [
                    'where' => '`project`.`date_from_internal` >= :start',
                    'ph' => [
                        ':start' => $start
                    ]
                ];
            } else if (!empty($end)) {
                $query_parts['date_from_internal'] = [
                    'where' => '`project`.`date_from_internal` <= :end',
                    'ph' => [
                        ':end' => $end
                    ]
                ];
            }
        }

        return $query_parts;
    }

    /**
     * @param array $query_parts
     * @return array
     */
    private function prepareQueryParts(array $query_parts): array {

        $ph = [];
        $ph_addons = [];
        $where = [];
        $join = [];
        $addons = [];
        foreach ($query_parts as $v) {

            if (array_key_exists('where', $v)) {
                $where[] = $v['where'];
            }

            if (array_key_exists('ph', $v)) {
                foreach ($v['ph'] as $ph_k => $ph_v) {
                    $ph[$ph_k] = $ph_v;
                }
            }

            if (array_key_exists('ph_addons', $v)) {
                foreach ($v['ph_addons'] as $ph_k => $ph_v) {
                    $ph_addons[$ph_k] = $ph_v;
                }
            }

            if (array_key_exists('join', $v)) {
                foreach ($v['join'] as $j) {
                    if (array_key_exists($j, self::$joins)) {
                        $join[] = $j;
                    }
                }
            }
            if (array_key_exists('addons', $v)) {
                foreach ($v['addons'] as $a) {
                    $addons[] = $a;
                }
            }
        }
        $join = array_unique($join);

        $join_sql = [];
        foreach (self::$joins as $k => $v) {
            if (in_array($k, $join, true)) {
                $join_sql[] = $v;
            }
        }

        return [
            'addons' => $addons,
            'join' => $join_sql,
            'where' => $where,
            'ph_addons' => $ph_addons,
            'ph' => $ph
        ];
    }

    public function fetchLocationQuantity(): array {

        $query_parts = $this->createQueryParts();

        if(array_key_exists('location', $query_parts)) {
            unset($query_parts['location']);
        }

        $query = $this->prepareQueryParts($query_parts);

        $query_select = 'SELECT 
    
        IF(`local_location`.`locality` IS NULL, NULL, SHA1(`local_location`.`locality`)) AS `id`,
        COUNT(DISTINCT `project`.`project_id`) AS `cnt`, `local_location`.`locality` AS `name`

        FROM `'.CONCLUDIS_TABLE_PROJECT.'` `project`  
            
        LEFT JOIN `'.CONCLUDIS_TABLE_PROJECT_LOCATION.'` `location` 
        ON (`location`.`project_id` = `project`.`project_id` AND `location`.`source_id` = `project`.`source_id`) 
            
        LEFT JOIN `'.CONCLUDIS_TABLE_LOCAL_LOCATION.'` `local_location` 
        ON (`location`.`location_id` = `local_location`.`location_id` AND `location`.`source_id` = `local_location`.`source_id`)
                    
        ' . (!empty($query['join']) ? implode(" \n", $query['join']) : '') . ' 
        WHERE 1 
        ' . (!empty($query['where']) ? 'AND ' . implode(' AND ', $query['where']) : '') . '
        
        GROUP BY COALESCE(SHA1(`local_location`.`locality`), SHA1(-1)), `local_location`.`locality` 
        ORDER BY `cnt` DESC, IF(`local_location`.`locality` IS NULL, 1, 0), `local_location`.`locality` DESC';

        return PDO::getInstance()->select($query_select, $query['ph']);

    }

    /**
     * @return array
     * @throws Exception
     */
    public function fetchCompanyQuantity(): array {

        $query_parts = $this->createQueryParts();

        if(array_key_exists('company', $query_parts)) {
            unset($query_parts['company']);
        }

        $query = $this->prepareQueryParts($query_parts);

        $query_select = 'SELECT 
        COALESCE(`company`.`company_id`, -1) AS `id`,
        COUNT(*) AS `cnt`, `local_company`.`name`

        FROM `'.CONCLUDIS_TABLE_PROJECT.'` `project`  
            
        LEFT JOIN `'.CONCLUDIS_TABLE_PROJECT_COMPANY.'` `company` 
        ON (`company`.`project_id` = `project`.`project_id` AND `company`.`source_id` = `project`.`source_id`) 
            
        LEFT JOIN `'.CONCLUDIS_TABLE_LOCAL_COMPANY.'` `local_company` 
        ON (`company`.`company_id` = `local_company`.`company_id` AND `company`.`source_id` = `local_company`.`source_id`)
                    
        ' . (!empty($query['join']) ? implode(" \n", $query['join']) : '') . ' 
        WHERE 1 
        ' . (!empty($query['where']) ? 'AND ' . implode(' AND ', $query['where']) : '') . '
        
        GROUP BY COALESCE(`local_company`.`company_id`, -1) 
        ORDER BY `cnt`, `local_company`.`name` DESC';

        return PDO::getInstance()->select($query_select, $query['ph']);
    }

    /**
     * @return array
     * @throws Exception
     */
    public function fetchMergedCompanyQuantity(): array {

        $query_parts = $this->createQueryParts();

        if(array_key_exists('company', $query_parts)) {
            unset($query_parts['company']);
        }

        $query = $this->prepareQueryParts($query_parts);

        $query_select = 'SELECT 
       
        COALESCE(SHA1(`local_company`.`name`), SHA1(-1)) AS `id`,
        COUNT(*) AS `cnt`,
        `local_company`.`name`

        FROM `'.CONCLUDIS_TABLE_PROJECT.'` `project`  
            
        LEFT JOIN `'.CONCLUDIS_TABLE_PROJECT_COMPANY.'` `company` 
        ON (`company`.`project_id` = `project`.`project_id` AND `company`.`source_id` = `project`.`source_id`) 
            
        LEFT JOIN `'.CONCLUDIS_TABLE_LOCAL_COMPANY.'` `local_company` 
        ON (`company`.`company_id` = `local_company`.`company_id` AND `company`.`source_id` = `local_company`.`source_id`)
                    
        ' . (!empty($query['join']) ? implode(" \n", $query['join']) : '') . ' 
        WHERE 1 
        ' . (!empty($query['where']) ? 'AND ' . implode(' AND ', $query['where']) : '') . '
        
        GROUP BY COALESCE(SHA1(`local_company`.`name`), SHA1(-1)) 
        ORDER BY `cnt`, `local_company`.`name` DESC';

        return PDO::getInstance()->select($query_select, $query['ph']);
    }

    /**
     * @param int $group_key
     * @return array
     * @throws Exception
     */
    public function fetchMergedGroupQuantity(int $group_key): array {

        $query_parts = $this->createQueryParts();

        if( array_key_exists('group' . $group_key, $query_parts)) {
            unset($query_parts['group' . $group_key]);
        }

        $query = $this->prepareQueryParts($query_parts);

        $query['ph'][':group_key'] = $group_key;

        $query_select = 'SELECT 
       
        COALESCE(SHA1(`local_group`.`name`), SHA1(-1)) AS `id`,
        COUNT(*) AS `cnt`,
        `local_group`.`name`

        FROM `'.CONCLUDIS_TABLE_PROJECT.'` `project`  
            
        LEFT JOIN `'.CONCLUDIS_TABLE_PROJECT_GROUP.'` `group` 
        ON (`group`.`project_id` = `project`.`project_id` AND `group`.`source_id` = `project`.`source_id`) 
            
        LEFT JOIN `'.CONCLUDIS_TABLE_LOCAL_GROUP.'` `local_group` 
        ON (`group`.`group_id` = `local_group`.`group_id` AND `group`.`group_key` = `local_group`.`group_key` AND `group`.`source_id` = `local_group`.`source_id`)
                    
        ' . (!empty($query['join']) ? implode(" \n", $query['join']) : '') . ' 
        WHERE `group`.`group_key` = :group_key 
        ' . (!empty($query['where']) ? 'AND ' . implode(' AND ', $query['where']) : '') . '
        
        GROUP BY COALESCE(SHA1(`local_group`.`name`), SHA1(-1)) 
        ORDER BY `cnt` DESC';

        return PDO::getInstance()->select($query_select, $query['ph']);
    }

    /**
     * @param bool $inflated
     * @param string $locale
     * @return array
     * @throws Exception
     */
    public function fetchSeniorityQuantity(bool $inflated = false, string $locale = 'de_DE'): array {

        $query_parts = $this->createQueryParts($inflated);

        if(array_key_exists('seniority', $query_parts)) {
            unset($query_parts['seniority']);
        }

        if($inflated) {
            $count_key = 'CONCAT(`project`.`source_id` , \'-\', `project`.`project_id`, \'-\', COALESCE(`location`.`location_id`,-1))';
            $query_parts['location_inflated'] = [
                'join' => ['location']
            ];
        } else {
            $count_key = 'CONCAT(`project`.`source_id` , \'-\', `project`.`project_id`)';
        }

        $query = $this->prepareQueryParts($query_parts);

        $query['ph'][':ilocale'] = $locale;

        $query_select = 'SELECT 
       
       IF(`local_seniority`.`source_id` IS NOT NULL, SHA1(CONCAT(`local_seniority`.`source_id` , \'-\', `local_seniority`.`seniority_id`)), NULL) AS `id`,
        COUNT(DISTINCT ' . $count_key . ') AS `cnt`, 
        `local_seniority`.`seniority_id`,
        `local_seniority`.`source_id`,
        COALESCE((
            SELECT `translation` FROM `'.CONCLUDIS_TABLE_I18N.'` 
            WHERE `model` = "local_seniority" AND `field` = "name" 
            AND `key` = CONCAT(`local_seniority`.`source_id`, "::",`local_seniority`.`seniority_id`) 
            AND `locale` = :ilocale
        ), `local_seniority`.`name`) AS `name`

        FROM `'.CONCLUDIS_TABLE_PROJECT.'` `project`  
            
        LEFT JOIN `'.CONCLUDIS_TABLE_PROJECT_SENIORITY.'` `seniority` 
        ON (`seniority`.`project_id` = `project`.`project_id` AND `seniority`.`source_id` = `project`.`source_id`) 
            
        LEFT JOIN `'.CONCLUDIS_TABLE_LOCAL_SENIORITY.'` `local_seniority` 
        ON (`seniority`.`seniority_id` = `local_seniority`.`seniority_id` AND `seniority`.`source_id` = `local_seniority`.`source_id`) 
        
        ' . (!empty($query['join']) ? implode(" \n", $query['join']) : '') . ' 
        WHERE 1 
        ' . (!empty($query['where']) ?  'AND ' . implode(' AND ', $query['where']) : '') . '
        
        GROUP BY `seniority`.`seniority_id`, `seniority`.`source_id` 
        ORDER BY `cnt` DESC';

        return PDO::getInstance()->select($query_select, $query['ph']);
    }

    /**
     * @param bool $inflated
     * @param string $locale
     * @return array
     * @throws Exception
     */
    public function fetchGlobalSeniorityQuantity(bool $inflated = false, string $locale = 'de_DE'): array {

        $query_parts = $this->createQueryParts($inflated);

        if(array_key_exists('seniority', $query_parts)) {
            unset($query_parts['seniority']);
        }

        if($inflated) {
            $count_key = 'CONCAT(`project`.`source_id` , \'-\', `project`.`project_id`, \'-\', COALESCE(`location`.`location_id`,-1))';
            $query_parts['location_inflated'] = [
                'join' => ['location']
            ];
        } else {
            $count_key = 'CONCAT(`project`.`source_id` , \'-\', `project`.`project_id`)';
        }

        $query = $this->prepareQueryParts($query_parts);

        $query['ph'][':ilocale'] = $locale;

        $query_select = 'SELECT 
       
        `global_seniority`.`global_id` AS `id`,
        COUNT(DISTINCT ' . $count_key . ') AS `cnt`,
        COALESCE((
            SELECT `translation` FROM `'.CONCLUDIS_TABLE_I18N.'` 
            WHERE `model` = "global_seniority" AND `field` = "name" 
            AND `key` = CONCAT("",`global_seniority`.`global_id`) 
            AND `locale` = :ilocale
        ), `global_seniority`.`name`) AS `name`

        FROM `'.CONCLUDIS_TABLE_PROJECT.'` `project`  
            
        LEFT JOIN `'.CONCLUDIS_TABLE_PROJECT_SENIORITY.'` `seniority` 
        ON (`seniority`.`project_id` = `project`.`project_id` AND `seniority`.`source_id` = `project`.`source_id`) 
            
        LEFT JOIN `'.CONCLUDIS_TABLE_LOCAL_SENIORITY.'` `local_seniority` 
        ON (`seniority`.`seniority_id` = `local_seniority`.`seniority_id` AND `seniority`.`source_id` = `local_seniority`.`source_id`)
            
        LEFT JOIN `'.CONCLUDIS_TABLE_GLOBAL_SENIORITY.'` `global_seniority` 
        ON (`local_seniority`.`global_seniority_id` = `global_seniority`.`global_id`) 
        
        ' . (!empty($query['join']) ? implode(" \n", $query['join']) : '') . ' 
        WHERE 1 
        ' . (!empty($query['where']) ? 'AND ' . implode(' AND ', $query['where']) : '') . '
        
        GROUP BY `local_seniority`.`global_seniority_id` 
        ORDER BY `cnt` DESC';

        return PDO::getInstance()->select($query_select, $query['ph']);
    }

    /**
     * @param bool $inflated
     * @param string $locale
     * @return array
     * @throws Exception
     */
    public function fetchScheduleQuantity(bool $inflated = false, string $locale = 'de_DE'): array {

        $query_parts = $this->createQueryParts($inflated);

        if(array_key_exists('schedule', $query_parts)) {
            unset($query_parts['schedule']);
        }

        if($inflated) {
            $count_key = 'CONCAT(`project`.`source_id` , \'-\', `project`.`project_id`, \'-\', COALESCE(`location`.`location_id`,-1))';
            $query_parts['location_inflated'] = [
              'join' => ['location']
            ];
        } else {
            $count_key = 'CONCAT(`project`.`source_id` , \'-\', `project`.`project_id`)';
        }

        $query = $this->prepareQueryParts($query_parts);

        $query['ph'][':ilocale'] = $locale;

        $query_select = 'SELECT 
       
        IF(`local_schedule`.`source_id` IS NOT NULL, SHA1(CONCAT(`local_schedule`.`source_id` , \'-\', `local_schedule`.`schedule_id`)), NULL) AS `id`,
        COUNT(DISTINCT ' . $count_key . ') AS `cnt`, 
        `local_schedule`.`schedule_id`,
        `local_schedule`.`source_id`,
        `local_schedule`.`global_schedule_id`,
        COALESCE((
            SELECT `translation` FROM `'.CONCLUDIS_TABLE_I18N.'` 
            WHERE `model` = "local_schedule" AND `field` = "name" 
            AND `key` = CONCAT(`local_schedule`.`source_id`, "::",`local_schedule`.`schedule_id`) 
            AND `locale` = :ilocale
        ), `local_schedule`.`name`) AS `name`

        FROM `'.CONCLUDIS_TABLE_PROJECT.'` `project`  
            
        LEFT JOIN `'.CONCLUDIS_TABLE_PROJECT_SCHEDULE.'` `schedule` 
        ON (`schedule`.`project_id` = `project`.`project_id` AND `schedule`.`source_id` = `project`.`source_id`) 
            
        LEFT JOIN `'.CONCLUDIS_TABLE_LOCAL_SCHEDULE.'` `local_schedule` 
        ON (`schedule`.`schedule_id` = `local_schedule`.`schedule_id` AND `schedule`.`source_id` = `local_schedule`.`source_id`) 
        
        ' . (!empty($query['join']) ? implode(" \n", $query['join']) : '') . ' 
        WHERE 1 
        ' . (!empty($query['where']) ?  'AND ' . implode(' AND ', $query['where']) : '') . '
        
        GROUP BY `schedule`.`schedule_id`, `schedule`.`source_id` 
        ORDER BY `cnt` DESC';

        return self::mergeQuantityResults(
            PDO::getInstance()->select($query_select, $query['ph']),
            Schedule::$merge_map,
            'global_schedule_id'
        );
    }

    /**
     * @param bool $inflated
     * @param string $locale
     * @return array
     * @throws Exception
     */
    public function fetchGlobalScheduleQuantity(bool $inflated = false, string $locale = 'de_DE'): array {

        $query_parts = $this->createQueryParts($inflated);

        if(array_key_exists('schedule', $query_parts)) {
            unset($query_parts['schedule']);
        }

        if($inflated) {
            $count_key = 'CONCAT(`project`.`source_id` , \'-\', `project`.`project_id`, \'-\', COALESCE(`location`.`location_id`,-1))';
            $query_parts['location_inflated'] = [
                'join' => ['location']
            ];
        } else {
            $count_key = 'CONCAT(`project`.`source_id` , \'-\', `project`.`project_id`)';
        }

        $query = $this->prepareQueryParts($query_parts);

        $query['ph'][':ilocale'] = $locale;

        $query_select = 'SELECT 
       
        `global_schedule`.`global_id` AS `id`,
        COUNT(DISTINCT ' . $count_key . ') AS `cnt`,
        COALESCE((
            SELECT `translation` FROM `'.CONCLUDIS_TABLE_I18N.'` 
            WHERE `model` = "global_schedule" AND `field` = "name" 
            AND `key` = CONCAT("",`global_schedule`.`global_id`) 
            AND `locale` = :ilocale
        ), `global_schedule`.`name`) AS `name`

        FROM `'.CONCLUDIS_TABLE_PROJECT.'` `project`  
            
        LEFT JOIN `'.CONCLUDIS_TABLE_PROJECT_SCHEDULE.'` `schedule` 
        ON (`schedule`.`project_id` = `project`.`project_id` AND `schedule`.`source_id` = `project`.`source_id`) 
            
        LEFT JOIN `'.CONCLUDIS_TABLE_LOCAL_SCHEDULE.'` `local_schedule` 
        ON (`schedule`.`schedule_id` = `local_schedule`.`schedule_id` AND `schedule`.`source_id` = `local_schedule`.`source_id`)
            
        LEFT JOIN `'.CONCLUDIS_TABLE_GLOBAL_SCHEDULE.'` `global_schedule` 
        ON (`local_schedule`.`global_schedule_id` = `global_schedule`.`global_id`) 
        
        ' . (!empty($query['join']) ? implode(" \n", $query['join']) : '') . ' 
        WHERE 1 
        ' . (!empty($query['where']) ? 'AND ' . implode(' AND ', $query['where']) : '') . '
        
        GROUP BY `local_schedule`.`global_schedule_id` 
        ORDER BY `cnt` DESC';

        return self::mergeQuantityResults(
            PDO::getInstance()->select($query_select, $query['ph']),
            Schedule::$merge_map,
            'id'
        );
    }


    private static function mergeQuantityResults(array $data, array $merge_mapping, string $global_id_key = 'global_id'): array {

        if(empty($merge_mapping)) {
            return $data;
        }

        $merge_into_tmp = [];
        $merged_data = [];

        foreach($data as $d) {
            $global_schedule_id = $d[$global_id_key] ?? '_undefined';
            if(array_key_exists($global_schedule_id, $merge_mapping)) {
                foreach($merge_mapping[$global_schedule_id] as $merge_into_global_schedule_id) {
                    $merge_into_tmp[] = [
                        'merge_from_global_id' => $global_schedule_id,
                        'merge_into_global_id' => $merge_into_global_schedule_id,
                        'cnt' => $d['cnt']
                    ];
                }
            } else {
                $merged_data[] = $d;
            }
        }

        foreach($merge_into_tmp as $m) {
            foreach($merged_data as &$d) {
                if($m['merge_into_global_id'] === $d[$global_id_key]) {
                    $d['cnt'] += $m['cnt'];
                }
            }
            unset($d);
        }

        return $merged_data;
    }

    /**
     * @param bool $inflated
     * @param string $locale
     * @return array
     * @throws Exception
     */
    public function fetchClassificationQuantity(bool $inflated = false, string $locale = 'de_DE'): array {

        $query_parts = $this->createQueryParts($inflated);

        if(array_key_exists('classification', $query_parts)) {
            unset($query_parts['classification']);
        }

        if($inflated) {
            $count_key = 'CONCAT(`project`.`source_id` , \'-\', `project`.`project_id`, \'-\', COALESCE(`location`.`location_id`,-1))';
            $query_parts['location_inflated'] = [
                'join' => ['location']
            ];
        } else {
            $count_key = 'CONCAT(`project`.`source_id` , \'-\', `project`.`project_id`)';
        }

        $query = $this->prepareQueryParts($query_parts);

        $query['ph'][':ilocale'] = $locale;

        $query_select = 'SELECT 
       
       IF(`local_classification`.`source_id` IS NOT NULL, SHA1(CONCAT(`local_classification`.`source_id` , \'-\', `local_classification`.`classification_id`)), NULL) AS `id`,
        COUNT(DISTINCT ' . $count_key . ') AS `cnt`, 
        `local_classification`.`classification_id`,
        `local_classification`.`source_id`,
        COALESCE((
            SELECT `translation` FROM `'.CONCLUDIS_TABLE_I18N.'` 
            WHERE `model` = "local_classification" AND `field` = "name" 
            AND `key` = CONCAT(`local_classification`.`source_id`, "::",`local_classification`.`classification_id`) 
            AND `locale` = :ilocale
        ), `local_classification`.`name`) AS `name`

        FROM `'.CONCLUDIS_TABLE_PROJECT.'` `project`  
            
        LEFT JOIN `'.CONCLUDIS_TABLE_PROJECT_CLASSIFICATION.'` `classification` 
        ON (`classification`.`project_id` = `project`.`project_id` AND `classification`.`source_id` = `project`.`source_id`) 
            
        LEFT JOIN `'.CONCLUDIS_TABLE_LOCAL_CLASSIFICATION.'` `local_classification` 
        ON (`classification`.`classification_id` = `local_classification`.`classification_id` AND `classification`.`source_id` = `local_classification`.`source_id`) 
        
        ' . (!empty($query['join']) ? implode(" \n", $query['join']) : '') . ' 
        WHERE 1 
        ' . (!empty($query['where']) ?  'AND ' . implode(' AND ', $query['where']) : '') . '
        
        GROUP BY `classification`.`classification_id`, `classification`.`source_id` 
        ORDER BY `cnt` DESC';

        return PDO::getInstance()->select($query_select, $query['ph']);
    }

    /**
     * @param bool $inflated
     * @param string $locale
     * @return array
     * @throws Exception
     */
    public function fetchGlobalClassificationQuantity(bool $inflated = false, string $locale = 'de_DE'): array {

        $query_parts = $this->createQueryParts($inflated);

        if(array_key_exists('classification', $query_parts)) {
            unset($query_parts['classification']);
        }

        if($inflated) {
            $count_key = 'CONCAT(`project`.`source_id` , \'-\', `project`.`project_id`, \'-\', COALESCE(`location`.`location_id`,-1))';
            $query_parts['location_inflated'] = [
                'join' => ['location']
            ];
        } else {
            $count_key = 'CONCAT(`project`.`source_id` , \'-\', `project`.`project_id`)';
        }

        $query = $this->prepareQueryParts($query_parts);

        $query['ph'][':ilocale'] = $locale;

        $query_select = 'SELECT 
       
        `global_classification`.`global_id` AS `id`,
        COUNT(DISTINCT ' . $count_key . ') AS `cnt`,
        COALESCE((
            SELECT `translation` FROM `'.CONCLUDIS_TABLE_I18N.'` 
            WHERE `model` = "global_classification" AND `field` = "name" 
            AND `key` = CONCAT("",`global_classification`.`global_id`) 
            AND `locale` = :ilocale
        ), `global_classification`.`name`) AS `name`

        FROM `'.CONCLUDIS_TABLE_PROJECT.'` `project`  
            
        LEFT JOIN `'.CONCLUDIS_TABLE_PROJECT_CLASSIFICATION.'` `classification` 
        ON (`classification`.`project_id` = `project`.`project_id` AND `classification`.`source_id` = `project`.`source_id`) 
            
        LEFT JOIN `'.CONCLUDIS_TABLE_LOCAL_CLASSIFICATION.'` `local_classification` 
        ON (`classification`.`classification_id` = `local_classification`.`classification_id` AND `classification`.`source_id` = `local_classification`.`source_id`)
            
        LEFT JOIN `'.CONCLUDIS_TABLE_GLOBAL_CLASSIFICATION.'` `global_classification` 
        ON (`local_classification`.`global_classification_id` = `global_classification`.`global_id`) 
        
        ' . (!empty($query['join']) ? implode(" \n", $query['join']) : '') . ' 
        WHERE 1 
        ' . (!empty($query['where']) ? 'AND ' . implode(' AND ', $query['where']) : '') . '
        
        GROUP BY `local_classification`.`global_classification_id` 
        ORDER BY `cnt` DESC';

        return PDO::getInstance()->select($query_select, $query['ph']);
    }

    /**
     * @param bool $inflated
     * @param string $locale
     * @return array
     * @throws Exception
     */
    public function fetchCategoryQuantity(bool $inflated = false, string $locale = 'de_DE'): array {

        $query_parts = $this->createQueryParts($inflated);

        if(array_key_exists('category', $query_parts)) {
            unset($query_parts['category']);
        }

        if($inflated) {
            $count_key = 'CONCAT(`project`.`source_id` , \'-\', `project`.`project_id`, \'-\', COALESCE(`location`.`location_id`,-1))';
            $query_parts['location_inflated'] = [
                'join' => ['location']
            ];
        } else {
            $count_key = 'CONCAT(`project`.`source_id` , \'-\', `project`.`project_id`)';
        }

        $query = $this->prepareQueryParts($query_parts);

        $query['ph'][':ilocale'] = $locale;

        $query_select = 'SELECT 
       
       IF(`local_category`.`source_id` IS NOT NULL, SHA1(CONCAT(`local_category`.`source_id` , \'-\', `local_category`.`category_id`)), NULL) AS `id`,
        COUNT(DISTINCT ' . $count_key . ') AS `cnt`, 
        `local_category`.`category_id`,
        `local_category`.`source_id`,
        COALESCE((
            SELECT `translation` FROM `'.CONCLUDIS_TABLE_I18N.'` 
            WHERE `model` = "local_category" AND `field` = "name" 
            AND `key` = CONCAT(`local_category`.`source_id`, "::",`local_category`.`category_id`) 
            AND `locale` = :ilocale
        ), `local_category`.`name`) AS `name`

        FROM `'.CONCLUDIS_TABLE_PROJECT.'` `project`  
            
        LEFT JOIN `'.CONCLUDIS_TABLE_PROJECT_CATEGORY.'` `category` 
        ON (`category`.`project_id` = `project`.`project_id` AND `category`.`source_id` = `project`.`source_id`) 
            
        LEFT JOIN `'.CONCLUDIS_TABLE_LOCAL_CATEGORY.'` `local_category` 
        ON (`category`.`category_id` = `local_category`.`category_id` AND `category`.`source_id` = `local_category`.`source_id`) 
        
        ' . (!empty($query['join']) ? implode(" \n", $query['join']) : '') . ' 
        WHERE 1 
        ' . (!empty($query['where']) ?  'AND ' . implode(' AND ', $query['where']) : '') . '
        
        GROUP BY `category`.`category_id`, `category`.`source_id` 
        ORDER BY `cnt` DESC';

        return PDO::getInstance()->select($query_select, $query['ph']);
    }

    /**
     * @param bool $inflated
     * @param string $locale
     * @return array
     * @throws Exception
     */
    public function fetchGlobalCategoryQuantity(bool $inflated = false, string $locale = 'de_DE'): array {

        $query_parts = $this->createQueryParts($inflated);

        if(array_key_exists('category', $query_parts)) {
            unset($query_parts['category']);
        }

        if($inflated) {
            $count_key = 'CONCAT(`project`.`source_id` , \'-\', `project`.`project_id`, \'-\', COALESCE(`location`.`location_id`,-1))';
            $query_parts['location_inflated'] = [
                'join' => ['location']
            ];
        } else {
            $count_key = 'CONCAT(`project`.`source_id` , \'-\', `project`.`project_id`)';
        }

        $query = $this->prepareQueryParts($query_parts);

        $query['ph'][':ilocale'] = $locale;

        $query_select = 'SELECT 
       
        `global_category`.`global_id` AS `id`,
        COUNT(DISTINCT ' . $count_key . ') AS `cnt`,
        COALESCE((
            SELECT `translation` FROM `'.CONCLUDIS_TABLE_I18N.'` 
            WHERE `model` = "global_category" AND `field` = "name" 
            AND `key` = CONCAT("",`global_category`.`global_id`) 
            AND `locale` = :ilocale
        ), `global_category`.`name`) AS `name`

        FROM `'.CONCLUDIS_TABLE_PROJECT.'` `project`  
            
        LEFT JOIN `'.CONCLUDIS_TABLE_PROJECT_CATEGORY.'` `category` 
        ON (`category`.`project_id` = `project`.`project_id` AND `category`.`source_id` = `project`.`source_id`) 
            
        LEFT JOIN `'.CONCLUDIS_TABLE_LOCAL_CATEGORY.'` `local_category` 
        ON (`category`.`category_id` = `local_category`.`category_id` AND `category`.`source_id` = `local_category`.`source_id`)
            
        LEFT JOIN `'.CONCLUDIS_TABLE_GLOBAL_CATEGORY.'` `global_category` 
        ON (`local_category`.`global_category_id` = `global_category`.`global_id`) 
        
        ' . (!empty($query['join']) ? implode(" \n", $query['join']) : '') . ' 
        WHERE 1 
        ' . (!empty($query['where']) ? 'AND ' . implode(' AND ', $query['where']) : '') . '
        
        GROUP BY `local_category`.`global_category_id` 
        ORDER BY `cnt` DESC';

        return PDO::getInstance()->select($query_select, $query['ph']);
    }

    /**
     * @return array
     * @throws Exception
     */
    public function fetchGeoStateQuantity(): array {

        $query_parts = $this->createQueryParts();

        if(array_key_exists('geo_state', $query_parts)) {
            unset($query_parts['geo_state']);
        }

        $query = $this->prepareQueryParts($query_parts);

        $query_select = 'SELECT 
       
       SHA1(CONCAT(`geo`.`country_code` , \'-\', `geo`.`state_code`)) AS `id`,
        `geo`.`country_code` AS `country_code`,
        `geo`.`state_code` AS `state_code`,
        COUNT(DISTINCT CONCAT(`project`.`source_id` , \'-\', `project`.`project_id`)) AS `cnt`,
        `geo`.`state_name` AS `name`

        FROM `'.CONCLUDIS_TABLE_PROJECT.'` `project`  
            
        LEFT JOIN `'.CONCLUDIS_TABLE_PROJECT_LOCATION.'` `ploc` 
        ON (`ploc`.`project_id` = `project`.`project_id` AND `ploc`.`source_id` = `project`.`source_id`) 
            
        LEFT JOIN `'.CONCLUDIS_TABLE_LOCAL_LOCATION.'` `loc` 
        ON (`loc`.`location_id` = `ploc`.`location_id` AND `loc`.`source_id` = `ploc`.`source_id`) 
            
        LEFT JOIN `'.CONCLUDIS_TABLE_GLOBAL_GEO.'` `geo` 
        ON (`loc`.`country_code` = `geo`.`country_code` AND `loc`.`postal_code` = `geo`.`postal_code`)
                    
        ' . (!empty($query['join']) ? implode(" \n", $query['join']) : '') . ' 
        WHERE 1 
        ' . (!empty($query['where']) ? 'AND ' . implode(' AND ', $query['where']) : '') . '
        
        GROUP BY `geo`.`state_code` 
        ORDER BY `cnt` DESC';

        return PDO::getInstance()->select($query_select, $query['ph']);
    }


    public static function factory(): ProjectRepository {
        return new self();
    }

    /**
     * @param Project $project
     * @return bool|null
     * @throws Exception
     */
    public static function exists(Project $project): ?bool {

        $pdo = PDO::getInstance();

        $sql = 'SELECT COUNT(*) AS `cnt` FROM `'.CONCLUDIS_TABLE_PROJECT.'` WHERE `source_id` = :source_id AND `project_id` = :project_id';

        $res = $pdo->selectOne($sql, [
            ':source_id' => $project->source_id,
            ':project_id' => $project->id
        ]);

        if ($res === false) {
            return null;
        }

        return $res['cnt'] > 0;
    }

    /**
     * @param Project $project
     * @return bool
     * @throws Exception
     */
    public static function save(Project $project): bool {

        if (self::exists($project)) {
            return self::update($project);
        }

        return self::insert($project);
    }

    /**
     * @param Project $project
     * @return bool
     * @throws Exception
     */
    private static function insert(Project $project): bool {

        $pdo = PDO::getInstance();

        try {

            $pdo->beginTransaction();

            $now = new DateTime();

            /**
             * Be sure to store always the "real" last-published date from our point of view.
             * Dates in project object may be in past or future but NOW is the time when it is going live.
             * So we store always the current datetime on first import!
             * @see self::update()
             */
            $project->date_from_public  = $project->is_published_public ? $now->format('Y-m-d') : null;
            $project->date_from_internal  = $project->is_published_internal ? $now->format('Y-m-d') : null;

            $sql = 'INSERT INTO `'.CONCLUDIS_TABLE_PROJECT.'` SET 
            `source_id` = :source_id, 
            `project_id` = :project_id, 
            `data` = :data, 
            `date_from_public` = :date_from_public, 
            `date_from_internal` = :date_from_internal, 
            `published_internal` = :published_internal, 
            `published_public` = :published_public, 
            `lastupdate` = :lastupdate';

            $ph = [
                ':source_id' => $project->source_id,
                ':project_id' => $project->id,
                ':data' => json_encode($project, JSON_THROW_ON_ERROR),
                ':date_from_public' => $project->getDateFromPublic()?->format('Y-m-d'),
                ':date_from_internal' => $project->getDateFromInternal()?->format('Y-m-d'),
                ':published_internal' => (int)$project->is_published_internal,
                ':published_public' => (int)$project->is_published_public,
                ':lastupdate' => $project->lastupdate
            ];

            if ($pdo->insert($sql, $ph)) {

                self::saveProjectCompany($project);
                self::saveProjectBoards($project);
                self::saveProjectGroups($project, 1);
                self::saveProjectGroups($project, 2);
                self::saveProjectGroups($project, 3);
                self::saveProjectClassification($project);
                self::saveProjectSeniority($project);
                self::saveProjectSchedule($project);
                self::saveProjectCategory($project);
                self::saveProjectLocations($project);
                self::saveProjectJobadContainers($project);
            }

            return $pdo->commit();

        } catch (Exception $e) {

            $pdo->rollBack();
            throw $e;
        }
    }

    /**
     * @param Project $project
     * @return bool
     * @throws Exception
     */
    private static function update(Project $project): bool {

        $eproject = self::fetchProjectById($project->source_id . '-' . $project->id);

        /**
         * Set from date to now in case of publishing within this update
         * As a result we have always the "real" last-published date in database.
         * @see self::insert()
         */

        if(!$eproject->is_published_internal && $project->is_published_internal) {
            $project->date_from_internal = (new DateTime())->format('Y-m-d');
        } else if($project->is_published_internal) {
            $project->date_from_internal = $eproject->date_from_internal;
        } else {
            $project->date_from_internal = null;
        }

        if(!$eproject->is_published_public && $project->is_published_public) {
            $project->date_from_public = (new DateTime())->format('Y-m-d');
        } else if($project->is_published_public) {
            $project->date_from_public = $eproject->date_from_public;
        } else {
            $project->date_from_public = null;
        }

        $pdo = PDO::getInstance();


        try {

            $pdo->beginTransaction();

            $sql = 'UPDATE `'.CONCLUDIS_TABLE_PROJECT.'` SET 
            `data` = :data, 
            `date_from_public` = :date_from_public, 
            `date_from_internal` = :date_from_internal, 
            `published_internal` = :published_internal, 
            `published_public` = :published_public, 
            `lastupdate` = :lastupdate
            WHERE `source_id` = :source_id AND `project_id` = :project_id';

            $ph = [
                ':source_id' => $project->source_id,
                ':project_id' => $project->id,
                ':data' => json_encode($project, JSON_THROW_ON_ERROR),
                ':date_from_public' => $project->getDateFromPublic()?->format('Y-m-d'),
                ':date_from_internal' => $project->getDateFromInternal()?->format('Y-m-d'),
                ':published_internal' => (int)$project->is_published_internal,
                ':published_public' => (int)$project->is_published_public,
                ':lastupdate' => $project->lastupdate
            ];

            if ($pdo->update($sql, $ph)) {

                self::saveProjectCompany($project);
                self::saveProjectBoards($project);
                self::saveProjectGroups($project, 1);
                self::saveProjectGroups($project, 2);
                self::saveProjectGroups($project, 3);
                self::saveProjectClassification($project);
                self::saveProjectSeniority($project);
                self::saveProjectSchedule($project);
                self::saveProjectCategory($project);
                self::saveProjectLocations($project);
                self::saveProjectJobadContainers($project);
            }

            return $pdo->commit();

        } catch (Exception $e) {

            $pdo->rollBack();
            throw $e;
        }
    }

    /**
     * @param Project $project
     * @return void
     * @throws Exception
     */
    private static function saveProjectBoards(Project $project): void {

        $pdo = PDO::getInstance();

        $sql = 'DELETE FROM `'.CONCLUDIS_TABLE_PROJECT_BOARD.'` 
        WHERE `source_id` = :source_id AND `project_id` = :project_id';

        $pdo->delete($sql, [
            ':source_id' => $project->source_id,
            ':project_id' => $project->id
        ]);

        if (empty($project->board)) {
            return;
        }

        foreach ($project->board as $board) {
            if (BoardRepository::save($board)) {

                $sql = 'INSERT INTO `' . CONCLUDIS_TABLE_PROJECT_BOARD . '` 
                SET `source_id` = :source_id, `project_id` = :project_id, `board_id` = :board_id';

                $pdo->insert($sql, [
                    ':source_id' => $board->source_id,
                    ':project_id' => $project->id,
                    ':board_id' => $board->id
                ]);
            }
        }

    }

    /**
     * @param Project $project
     * @param int $group_key
     * @return void
     * @throws Exception
     */
    private static function saveProjectGroups(Project $project, int $group_key): void {

        $pdo = PDO::getInstance();

        $sql = 'DELETE FROM `'.CONCLUDIS_TABLE_PROJECT_GROUP.'` 
        WHERE `source_id` = :source_id AND `project_id` = :project_id AND `group_key` = :group_key';

        $pdo->delete($sql, [
            ':source_id' => $project->source_id,
            ':project_id' => $project->id,
            ':group_key' => $group_key,
        ]);

        /**
         * @var $elem Element[]
         */
        $elem = [];

        if($group_key === 1) {
            $elem = $project->group1;
        } else if($group_key === 2) {
            $elem = $project->group2;
        } else if($group_key === 3) {
            $elem = $project->group3;
        }

        if (empty($elem)) {
            return;
        }

        foreach ($elem as $group) {
            if ($group->save($group_key)) {

                $sql = 'INSERT INTO `' . CONCLUDIS_TABLE_PROJECT_GROUP . '` 
                SET `source_id` = :source_id, `project_id` = :project_id, `group_id` = :group_id, `group_key` = :group_key';

                $pdo->insert($sql, [
                    ':source_id' => $group->source_id,
                    ':project_id' => $project->id,
                    ':group_id' => $group->id,
                    ':group_key' => $group_key
                ]);
            }
        }

    }

    /**
     * @param Project $project
     * @return void
     * @throws Exception
     */
    private static function saveProjectCompany(Project $project): void {

        $pdo = PDO::getInstance();

        $sql = 'DELETE FROM `'.CONCLUDIS_TABLE_PROJECT_COMPANY.'` 
        WHERE `source_id` = :source_id AND `project_id` = :project_id';

        $pdo->delete($sql, [
            ':source_id' => $project->source_id,
            ':project_id' => $project->id
        ]);

        if ($project->company === null) {
            return;
        }

        if (CompanyRepository::save($project->company)) {

            $sql = 'INSERT INTO `'.CONCLUDIS_TABLE_PROJECT_COMPANY.'` 
            SET `source_id` = :source_id, `project_id` = :project_id, `company_id` = :company_id';

            $pdo->insert($sql, [
                ':source_id' => $project->source_id,
                ':project_id' => $project->id,
                ':company_id' => $project->company->id
            ]);
        }

    }

    /**
     * @param Project $project
     * @return void
     * @throws Exception
     */
    private static function saveProjectClassification(Project $project): void {


        $pdo = PDO::getInstance();

        $sql = 'DELETE FROM `'.CONCLUDIS_TABLE_PROJECT_CLASSIFICATION.'` 
        WHERE `source_id` = :source_id AND `project_id` = :project_id';

        $pdo->delete($sql, [
            ':source_id' => $project->source_id,
            ':project_id' => $project->id
        ]);

        if ($project->classification === null) {
            return;
        }

        if ($project->classification->save()) {

            $sql = 'INSERT INTO `'.CONCLUDIS_TABLE_PROJECT_CLASSIFICATION.'` 
            SET `source_id` = :source_id, `project_id` = :project_id, `classification_id` = :classification_id';

            $pdo->insert($sql, [
                ':source_id' => $project->source_id,
                ':project_id' => $project->id,
                ':classification_id' => $project->classification->id
            ]);

        }
    }

    /**
     * @param Project $project
     * @return void
     * @throws Exception
     */
    private static function saveProjectSeniority(Project $project): void {

        $pdo = PDO::getInstance();

        $sql = 'DELETE FROM `'.CONCLUDIS_TABLE_PROJECT_SENIORITY.'` 
        WHERE `source_id` = :source_id AND `project_id` = :project_id';

        $pdo->delete($sql, [
            ':source_id' => $project->source_id,
            ':project_id' => $project->id
        ]);

        if ($project->seniority === null) {
            return;
        }

        if ($project->seniority->save()) {

            $sql = 'INSERT INTO `'.CONCLUDIS_TABLE_PROJECT_SENIORITY.'` 
            SET `source_id` = :source_id, `project_id` = :project_id, `seniority_id` = :seniority_id';

            $pdo->insert($sql, [
                ':source_id' => $project->source_id,
                ':project_id' => $project->id,
                ':seniority_id' => $project->seniority->id
            ]);
        }
    }

    /**
     * @param Project $project
     * @return void
     * @throws Exception
     */
    private static function saveProjectSchedule(Project $project): void {

        $pdo = PDO::getInstance();

        $sql = 'DELETE FROM `'.CONCLUDIS_TABLE_PROJECT_SCHEDULE.'` 
        WHERE `source_id` = :source_id AND `project_id` = :project_id';

        $pdo->delete($sql, [
            ':source_id' => $project->source_id,
            ':project_id' => $project->id
        ]);

        if ($project->schedule === null) {
            return;
        }

        if ($project->schedule->save()) {

            $sql = 'INSERT INTO `'.CONCLUDIS_TABLE_PROJECT_SCHEDULE.'` 
            SET `source_id` = :source_id, `project_id` = :project_id, `schedule_id` = :schedule_id';

            $pdo->insert($sql, [
                ':source_id' => $project->source_id,
                ':project_id' => $project->id,
                ':schedule_id' => $project->schedule->id
            ]);

        }
    }

    /**
     * @param Project $project
     * @return void
     * @throws Exception
     */
    private static function saveProjectCategory(Project $project): void {

        $pdo = PDO::getInstance();

        $sql = 'DELETE FROM `'.CONCLUDIS_TABLE_PROJECT_CATEGORY.'` 
        WHERE `source_id` = :source_id AND `project_id` = :project_id';

        $pdo->delete($sql, [
            ':source_id' => $project->source_id,
            ':project_id' => $project->id
        ]);

        if ($project->category === null) {
            return;
        }

        if ($project->category->save()) {

            foreach ($project->category->occupations as $occupation) {
                $sql = 'INSERT INTO `'.CONCLUDIS_TABLE_PROJECT_CATEGORY.'` SET 
                                   `source_id` = :source_id, 
                                   `project_id` = :project_id, 
                                   `category_id` = :category_id, 
                                   `occupation_id` = :occupation_id';

                $pdo->insert($sql, [
                    ':source_id' => $project->source_id,
                    ':project_id' => $project->id,
                    ':category_id' => $project->category->id,
                    ':occupation_id' => $occupation->id
                ]);
            }

        }
    }

    /**
     * @param Project $project
     * @return void
     * @throws Exception
     */
    private static function saveProjectLocations(Project $project): void {

        $pdo = PDO::getInstance();

        $sql = 'DELETE FROM `'.CONCLUDIS_TABLE_PROJECT_LOCATION.'` 
        WHERE `source_id` = :source_id AND `project_id` = :project_id';

        $pdo->delete($sql, [
            ':source_id' => $project->source_id,
            ':project_id' => $project->id
        ]);

        foreach ($project->locations as $location) {
            if (LocationRepository::save($location)) {

                $dateFrom = $project->getDateFromPublic();

                $company_logo = '';
                if(!empty($project->company->url_signet)) {
                    $company_logo = $project->company->url_signet;
                } else if(!empty($project->company->url_logo)) {
                    $company_logo = $project->company->url_logo;
                }

                $location_array_for_map = [
                    'coordinates' => [$location->lon, $location->lat],
                    'locationName' => $location->getDisplayName(),
                    'gpid' => $project->source_id . 'p' . $project->id,
                    'title' => $project->getTitle(),
                    'teaser' => strip_tags($project->getTeaser()),
                    'changed' => $dateFrom !== null ? $dateFrom->format('d.m.Y') : 'undefined',
                    'companyName' => $project->company->name ?? '',
                    'logo' => $company_logo
                ];

                $sql = 'INSERT INTO `'.CONCLUDIS_TABLE_PROJECT_LOCATION.'` SET 
                           `source_id` = :source_id, 
                           `project_id` = :project_id, 
                           `location_id` = :location_id,
                           `map_data` = :map_data';

                $pdo->insert($sql, [
                    ':source_id' => $project->source_id,
                    ':project_id' => $project->id,
                    ':location_id' => $location->id,
                    ':map_data' => json_encode($location_array_for_map, JSON_THROW_ON_ERROR),
                ]);
            }
        }
    }

    /**
     * @param Location $location
     * @return void
     * @throws Exception
     */
    public static function updateProjectLocationsLatLon(Location $location): void {

        $pdo = PDO::getInstance();

        $sql = 'UPDATE `'.CONCLUDIS_TABLE_PROJECT_LOCATION.'` 
        SET `map_data` = JSON_SET(`map_data`,
            "$.coordinates[0]", :lon,
            "$.coordinates[1]", :lat
        ) WHERE `source_id` = :source_id AND `location_id` = :location_id';

        $pdo->update($sql, [
            ':lon' => $location->lon,
            ':lat' => $location->lat,
            ':source_id' => $location->source_id,
            ':location_id' => $location->id,
        ]);

    }

    /**
     * @param Project $project
     * @return void
     * @throws Exception
     */
    private static function saveProjectJobadContainers(Project $project): void {

        $containers = $project->getJobadContainers(false);
        if($containers === null) {
            return;
        }

        $pdo = PDO::getInstance();

        $sql = 'DELETE FROM `'.CONCLUDIS_TABLE_PROJECT_AD_CONTAINER.'` 
        WHERE `source_id` = :source_id AND `project_id` = :project_id';

        $pdo->delete($sql, [
            ':source_id' => $project->source_id,
            ':project_id' => $project->id
        ]);

        foreach ($containers as $container) {
            $sql = 'INSERT INTO `'.CONCLUDIS_TABLE_PROJECT_AD_CONTAINER.'` SET 
                                   `source_id` = :source_id, 
                                   `project_id` = :project_id, 
                                   `datafield_id` = :datafield_id, 
                                   `locale` = :locale, 
                                   `type` = :atype, 
                                   `sortorder` = :sortorder, 
                                   `container_type` = :container_type, 
                                   `content_external` = :content_external, 
                                   `content_internal` = :content_internal';

            $pdo->insert($sql, [
                ':source_id' => $project->source_id,
                ':project_id' => $project->id,
                ':datafield_id' => $container->datafield_id,
                ':locale' => $container->locale,
                ':atype' => $container->type,
                ':sortorder' => $container->sortorder,
                ':container_type' => $container->container_type,
                ':content_external' => $container->content_external,
                ':content_internal' => $container->content_internal
            ]);
        }
    }

    /**
     * @param Project $project
     * @return JobadContainer[]
     * @throws Exception
     */
    public static function fetchJobadContainers(Project $project): array
    {

        $pdo = PDO::getInstance();

        $sql = 'SELECT * FROM  `'.CONCLUDIS_TABLE_PROJECT_AD_CONTAINER.'` 
        WHERE `source_id` = :source_id AND `project_id` = :project_id ORDER BY `sortorder`';

        $res = $pdo->select($sql, [
            ':source_id' => $project->source_id,
            ':project_id' => $project->id
        ]);
        $r = [];
        foreach($res as $d) {
            $r[] = new JobadContainer($d);
        }

        return $r;
    }

    /**
     * @param string $source_id
     * @param int $project_id
     * @return bool
     * @throws Exception
     */
    public static function purgeProjectById(string $source_id, int $project_id): bool {

        $pdo = PDO::getInstance();

        $sql = 'DELETE FROM `'.CONCLUDIS_TABLE_PROJECT.'` WHERE `source_id` = :source_id AND `project_id` = :project_id';

        return $pdo->delete($sql, [':source_id' => $source_id, ':project_id' => $project_id]);
    }

    /**
     * @param string $source_id
     * @param string $lastupdate
     * @return bool
     * @throws Exception
     */
    public static function purgeDeprecatedProjectsBySource(string $source_id, string $lastupdate): bool {

        $pdo = PDO::getInstance();

        $sql = 'DELETE FROM `'.CONCLUDIS_TABLE_PROJECT.'` WHERE `source_id` = :source_id AND `lastupdate` != :lastupdate';

        return $pdo->delete($sql, [':source_id' => $source_id, ':lastupdate' => $lastupdate]);
    }

    /**
     * @return void
     * @throws Exception
     */
    public static function purgeDeprecatedSources(): void {

        $sources = self::fetchDistinctSources();
        foreach ($sources as $source_id) {
            $source = Baseconfig::getSourceById($source_id);
            if ($source === null) {
                self::purgeBySource($source_id);
            }
        }
    }

    /**
     * @param string $source_id
     * @return bool
     * @throws Exception
     */
    public static function purgeBySource(string $source_id): bool {

        $pdo = PDO::getInstance();

        $sql = 'DELETE FROM `'.CONCLUDIS_TABLE_PROJECT.'` WHERE `source_id` = :source_id';

        return $pdo->delete($sql, [':source_id' => $source_id]);
    }

    /**
     * @return void
     * @throws Exception
     */
    public static function optimizeTable(): void {
        PDO::getInstance()->optimizeTable(CONCLUDIS_TABLE_PROJECT);
    }

    /**
     * @return void
     * @throws Exception
     */
    public static function optimizeAdContainerTable(): void {
        PDO::getInstance()->optimizeTable(CONCLUDIS_TABLE_PROJECT_AD_CONTAINER);
    }

    /**
     * @return array
     * @throws Exception
     */
    public static function fetchDistinctSources(): array {

        $pdo = PDO::getInstance();

        $sql = 'SELECT DISTINCT `source_id` FROM `'.CONCLUDIS_TABLE_PROJECT.'`';

        $res = $pdo->select($sql);

        $data = [];
        foreach ($res as $r) {
            $data[] = $r['source_id'];
        }
        return $data;
    }

    private static function getFilter($filter, string $type): array {

        if (is_array($filter)) {
            if($type === 'int') {
                $filter = ArrayUtil::toIntArray($filter);
            } else {
                $filter = ArrayUtil::toStringArray($filter);
            }
        } else if (is_int($filter) && $type === 'int') {
            $filter = ArrayUtil::toIntArray([$filter]);
        } else if (is_string($filter) && $type === 'string') {
            $filter = ArrayUtil::toStringArray([$filter]);
        } else {
            $filter = [];
        }
        return $filter;
    }

}