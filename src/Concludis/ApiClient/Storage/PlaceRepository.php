<?php


namespace Concludis\ApiClient\Storage;


use Concludis\ApiClient\Database\PDO;
use Concludis\ApiClient\Resources\Place;
use Exception;


class PlaceRepository {

    public const FILTER_TYPE_PLACE_NAME = 'place_name';
    public const FILTER_TYPE_POSTAL_CODE = 'postal_code';
    public const FILTER_TYPE_COUNTRY_CODE = 'country_code';
    public const FILTER_TYPE_KEYWORD = 'keyword';

    private array $filter = [];

    public function addFilter(string $filter_type, $value): PlaceRepository {

        $this->filter[$filter_type] = $value;

        return $this;
    }

    /**
     * @return Place[]
     * @throws Exception
     */
    public function fetch(): array {

        $pdo = PDO::getInstance();

        $query = $this->createQuery();

        $order = [
            'name' => 'ASC'
        ];

        if(!empty($order)) {
            $order_parts = [];
            foreach ($order as $k => $v) {
                $order_parts[] = $k . ' ' . ($v ? 'ASC' : 'DESC');
            }
            $query['order'] = 'ORDER BY ' . implode(',', $order_parts);
        }

        $sql = 'SELECT `global_id` AS `id`, `place_name` AS `name`, 
       `country_code`, `postal_code`, 
       `state_code`, `state_name`, 
       `province_code`, `province_name`,  
       `community_code`, `community_name`,  
       `latitude`  AS `lat`, `longitude` AS `lon` 
        FROM `'.CONCLUDIS_TABLE_GLOBAL_GEO.'` 
        WHERE 1 ' .
            (!empty($query['where']) ? "\n" . 'AND ' . implode(' AND ', $query['where']) . " \n" : '') .
            (!empty($query['order']) ? $query['order'] . " \n" : '');

        $res = $pdo->select($sql, $query['ph']);

        $data = [];
        foreach($res as $r) {
            $data[] = new Place($r);
        }

        return $data;
    }

    /**
     * @return Place|null
     * @throws Exception
     */
    public function fetchOne(): ?Place {

        $pdo = PDO::getInstance();

        $query = $this->createQuery();

        $sql = 'SELECT `global_id` AS `id`, `place_name` AS `name`, 
       `country_code`, `postal_code`, 
       `state_code`, `state_name`, 
       `province_code`, `province_name`,  
       `community_code`, `community_name`,  
       `latitude`  AS `lat`, `longitude` AS `lon` 
        FROM `'.CONCLUDIS_TABLE_GLOBAL_GEO.'` 
        WHERE 1 ' .
            (!empty($query['where']) ? "\n" . 'AND ' . implode(' AND ', $query['where']) . " \n" : '') .
        'LIMIT 1';


        $res = $pdo->selectOne($sql, $query['ph']);

        if($res === false) {
            return null;
        }

        return new Place($res);
    }


    private function createQuery(): array {

        $query = [
            'where' => [],
            'ph' => []
        ];

        if(array_key_exists(self::FILTER_TYPE_KEYWORD, $this->filter)) {
            $tmp_filter = $this->filter[self::FILTER_TYPE_KEYWORD];

            if (is_string($tmp_filter) && !empty($tmp_filter)) {

                $replacements = [
                    '^' => '',
                    '%' => '',
                    '|' => ''
                ];

                $keyword = strtr(trim($tmp_filter), $replacements);

                //$keyword = str_replace('%', '', $tmp_filter);

                $ph_keyword_place_name = ':keyword_place_name';
                $query['ph'][$ph_keyword_place_name] = $keyword . '%';

                $ph_keyword_postal_code = ':keyword_postal_code';
                $query['ph'][$ph_keyword_postal_code] = $keyword . '%';

                $query['where'][] = '(
                    `place_name` LIKE ' . $ph_keyword_place_name . '
                    OR `postal_code` LIKE ' . $ph_keyword_postal_code . '
                    ) 
                    AND `country_code` IN (
                        SELECT DISTINCT `country_code` FROM `' . CONCLUDIS_TABLE_LOCAL_LOCATION . '` 
                        WHERE `country_code` != "")';
            }
        }

        if (array_key_exists(self::FILTER_TYPE_COUNTRY_CODE, $this->filter)) {
            $tmp_filter = $this->filter[self::FILTER_TYPE_COUNTRY_CODE];

            if (is_string($tmp_filter) && !empty($tmp_filter)) {
                if($tmp_filter === 'find_all_used_countries') {
                    $query['where'][] = '`country_code` IN (SELECT DISTINCT `country_code` FROM `' . CONCLUDIS_TABLE_LOCAL_LOCATION . '` WHERE `country_code` != "")';
                } else {
                    $query['where'][] = '`country_code` = :country_code';
                    $query['ph'][':country_code'] = $tmp_filter;
                }
            }
        }

        if (array_key_exists(self::FILTER_TYPE_POSTAL_CODE, $this->filter)) {
            $tmp_filter = $this->filter[self::FILTER_TYPE_POSTAL_CODE];

            if (is_string($tmp_filter) && !empty($tmp_filter)) {
                $query['where'][] = '`postal_code` LIKE :postal_code';
                $query['ph'][':postal_code'] = $tmp_filter;
            }
        }

        if (array_key_exists(self::FILTER_TYPE_PLACE_NAME, $this->filter)) {
            $tmp_filter = $this->filter[self::FILTER_TYPE_PLACE_NAME];

            if (is_string($tmp_filter) && !empty($tmp_filter)) {
                $query['where'][] = '`place_name` LIKE :place_name';
                $query['ph'][':place_name'] = $tmp_filter;
            }
        }

        return $query;
    }

    public static function factory(): PlaceRepository {
        return new self();
    }
}