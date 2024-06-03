<?php

namespace Concludis\ApiClient\Storage;

use Concludis\ApiClient\Config\Baseconfig;
use Concludis\ApiClient\Database\PDO;
use Concludis\ApiClient\Resources\Board;
use Exception;

class BoardRepository {

    public const FILTER_TYPE_SOURCE = 'source';

    private array $filter = [];

    public function addFilter(string $filter_type, $value): BoardRepository {

        $this->filter[$filter_type] = $value;

        return $this;
    }

    private function createQuery(): array {

        $query = [
            'where' => [],
            'ph' => []
        ];

        if (array_key_exists(self::FILTER_TYPE_SOURCE, $this->filter)) {
            $tmp_filter = $this->filter[self::FILTER_TYPE_SOURCE];

            if (is_string($tmp_filter) && !empty($tmp_filter)) {
                $query['where'][] = '`source_id` = :source_id';
                $query['ph'][':source_id'] = $tmp_filter;
            } else if (is_array($tmp_filter) && !empty($tmp_filter)) {
                $query['where'][] = '`source_id` IN (:source_id)';
                $query['ph'][':source_id'] = $tmp_filter;
            }
        }

        return $query;
    }

    /**
     * @return Board[]
     * @throws Exception
     */
    public function fetch(): array {

        $pdo = PDO::getInstance();

        $query = $this->createQuery();

        $order = [
            'name' => 'ASC'
        ];

        if(!empty($order)){
            $order_parts = [];
            foreach ($order as $k => $v) {
                $order_parts[] = $k . ' ' . ($v ? 'ASC' : 'DESC');
            }
            $query['order'] = 'ORDER BY ' . implode(',', $order_parts);
        }

        $sql = 'SELECT `source_id`, `board_id` AS `id`, `name`, `external_id`, `extended_props`
        FROM `'.CONCLUDIS_TABLE_LOCAL_BOARD.'` 
        WHERE 1 ' .
            (!empty($query['where']) ? "\n" . 'AND ' . implode(' AND ', $query['where']) . " \n" : '') .
            (!empty($query['order']) ? $query['order'] . " \n" : '');

        $res = $pdo->select($sql, $query['ph']);

        $data = [];
        foreach($res as $r) {
            $data[] = new Board($r);
        }

        return $data;
    }

    /**
     * @param Board $board
     * @return bool
     * @throws Exception
     */
    public static function save(Board $board): bool {

        if (self::exists($board)) {
            return self::update($board);
        }

        return self::insert($board);
    }

    /**
     * @param Board $board
     * @return bool
     * @throws Exception
     */
    private static function insert(Board $board): bool {

        $pdo = PDO::getInstance();

        $extended_props = null;
        if($board->extended_props !== null) {
            try {
                $extended_props = json_encode($board->extended_props, JSON_THROW_ON_ERROR);
                if($extended_props === false) {
                    $extended_props = null;
                }
            } catch (Exception) {}
        }

        $sql = 'INSERT INTO `'.CONCLUDIS_TABLE_LOCAL_BOARD.'` SET 
        `source_id` = :source_id, 
        `board_id` = :board_id,
        `name` = :name, 
        `external_id` = :external_id';

        $ph = [
            ':source_id' => $board->source_id,
            ':board_id' => $board->id,
            ':name' => $board->name,
            ':external_id' => $board->external_id
        ];

        if($extended_props !== null) {
            $sql .= ', `extended_props` = :extended_props';
            $ph[':extended_props'] = $extended_props;
        }

        return $pdo->insert($sql, $ph);
    }

    /**
     * @param Board $board
     * @return bool
     * @throws Exception
     */
    private static function update(Board $board): bool {

        $pdo = PDO::getInstance();

        $extended_props = null;
        if($board->extended_props !== null) {
            try {
                $extended_props = json_encode($board->extended_props, JSON_THROW_ON_ERROR);
                if($extended_props === false) {
                    $extended_props = null;
                }
            } catch (Exception) {}
        }

        $sql = 'UPDATE `'.CONCLUDIS_TABLE_LOCAL_BOARD.'` SET 
        `name` = :name,
        `external_id` = :external_id 
        ' . ($extended_props !== null ? ',`extended_props` = :extended_props' : '') . '                                     
        WHERE `source_id` = :source_id AND `board_id` = :board_id';

        $ph = [
            ':source_id' => $board->source_id,
            ':board_id' => $board->id,
            ':external_id' => $board->external_id,
            ':name' => $board->name
        ];

        if($extended_props !== null) {
            $ph[':extended_props'] = $extended_props;
        }

        return $pdo->update($sql, $ph);
    }

    /**
     * @param Board $board
     * @return bool|null
     * @throws Exception
     */
    public static function exists(Board $board): ?bool {

        $pdo = PDO::getInstance();

        $sql = 'SELECT COUNT(*) AS `cnt` FROM `'.CONCLUDIS_TABLE_LOCAL_BOARD.'` 
        WHERE `source_id` = :source_id AND `board_id` = :board_id';

        $res = $pdo->selectOne($sql, [
            ':source_id' => $board->source_id,
            ':board_id' => $board->id
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

        $sql = 'SELECT DISTINCT `source_id` FROM `'.CONCLUDIS_TABLE_LOCAL_BOARD.'`';

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

        $sql = 'DELETE FROM `'.CONCLUDIS_TABLE_LOCAL_BOARD.'` WHERE `source_id` = :source_id';

        if(!empty($except_ids)) {
            $sql .= ' AND `board_id` NOT IN (:except_ids)';
            $ph[':except_ids'] = $except_ids;
        }

        return $pdo->delete($sql,$ph);
    }

    /**
     * @return void
     * @throws Exception
     */
    public static function purgeUnused(): void {

        $pdo = PDO::getInstance();

        $pdo->delete('DELETE FROM `'.CONCLUDIS_TABLE_LOCAL_BOARD.'` WHERE CONCAT(`source_id`, "::", `board_id`) NOT IN (
                SELECT DISTINCT CONCAT(`source_id`, "::", `board_id`)  FROM `'.CONCLUDIS_TABLE_PROJECT_BOARD.'`
            )');
    }

    /**
     * @param string $source_id
     * @param string $identifier
     * @param bool $internal
     * @return Board|null
     * @throws Exception
     */
    public static function fetchBoardByIdentifier(string $source_id, string $identifier, bool $internal): ?Board {

        $index = $internal ? 'identifier_internal' : 'identifier';

        $pdo = PDO::getInstance();

        $sql = 'SELECT `source_id`, `board_id` AS `id`, `name`, `external_id`, `extended_props` FROM `'.CONCLUDIS_TABLE_LOCAL_BOARD.'`  
        WHERE `source_id` = :source_id AND  JSON_UNQUOTE(JSON_EXTRACT(`extended_props`, "$.' . $index . '")) = :identifier';

        $res = $pdo->selectOne($sql, [
            ':source_id' => $source_id,
            ':identifier' => $identifier
        ]);

        if(is_array($res)) {
            return new Board($res);
        }

        return null;
    }

    /**
     * @param string $source_id
     * @param int $board_id
     * @return Board|null
     * @throws Exception
     */
    public static function fetchBoardById(string $source_id, int $board_id): ?Board {

        $pdo = PDO::getInstance();

        $sql = 'SELECT `source_id`, `board_id` AS `id`, `name`, `external_id`, `extended_props` FROM `'.CONCLUDIS_TABLE_LOCAL_BOARD.'`  
        WHERE `source_id` = :source_id AND `board_id` = :board_id';

        $res = $pdo->selectOne($sql, [
            ':source_id' => $source_id,
            ':board_id' => $board_id
        ]);

        if(is_array($res)) {
            return new Board($res);
        }

        return null;
    }

    /**
     * @param string $source_id
     * @param string $board_external_id
     * @return Board|null
     * @throws Exception
     */
    public static function fetchBoardByExternalId(string $source_id, string $board_external_id): ?Board {

        $pdo = PDO::getInstance();

        $sql = 'SELECT `source_id`, `board_id` AS `id`, `name`, `external_id`, `extended_props` FROM `'.CONCLUDIS_TABLE_LOCAL_BOARD.'`  
        WHERE `source_id` = :source_id AND `external_id` = :board_external_id ORDER BY `board_id` LIMIT 1';

        $res = $pdo->selectOne($sql, [
            ':source_id' => $source_id,
            ':board_external_id' => $board_external_id
        ]);

        if(is_array($res)) {
            return new Board($res);
        }

        return null;
    }
}