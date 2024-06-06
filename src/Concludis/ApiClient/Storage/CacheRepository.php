<?php

namespace Concludis\ApiClient\Storage;

use Exception;
use RuntimeException;
use Concludis\ApiClient\Database\PDO;

class CacheRepository {

    /**
     * @param string $key
     * @return mixed|null
     * @throws Exception
     */
    public static function fetch(string $key): mixed {

        $pdo = PDO::getInstance();

        $sql = "SELECT `data` FROM `".CONCLUDIS_TABLE_CACHE."` WHERE `key` = :k";

        $res = $pdo->selectOne($sql, [':k' => $key]);

        if(!is_array($res)) {
            return null;
        }

        return json_decode($res['data'], true, 512, JSON_THROW_ON_ERROR);
    }

    /**
     * @param string $key
     * @return bool
     * @throws Exception
     */
    public static function exists(string $key): bool {

        $pdo = PDO::getInstance();

        $sql = "SELECT COUNT(*) AS `cnt` FROM `".CONCLUDIS_TABLE_CACHE."` WHERE `key` = :k";

        $res = $pdo->selectOne($sql, [':k' => $key]);

        if(!is_array($res)) {
            throw new RuntimeException('strange result for exist (cache)');
        }

        return $res['cnt'] > 0;
    }

    /**
     * @param string $key
     * @return bool
     * @throws Exception
     */
    public static function delete(string $key): bool {

        $pdo = PDO::getInstance();

        $sql = "DELETE FROM `".CONCLUDIS_TABLE_CACHE."` WHERE `key` = :k";

        return $pdo->delete($sql, [':k' => $key]);
    }

    /**
     * @param string $key
     * @param $data
     * @return bool
     * @throws Exception
     */
    public static function cache(string $key, $data): bool
    {

        $pdo = PDO::getInstance();

        $pdo->beginTransaction();

        try {
            self::delete($key);

            $sql = "INSERT INTO `".CONCLUDIS_TABLE_CACHE."` SET `key` = :k, `data` = :d";

            $pdo->insert($sql, [':k' => $key, ':d' => json_encode($data, JSON_THROW_ON_ERROR | JSON_PRETTY_PRINT)]);

            return $pdo->commit();

        } catch (Exception $e) {
            $pdo->rollback();
            throw $e;
        }
    }

    /**
     * @param string $key
     * @param $data
     * @throws Exception
     */
    public static function update(string $key, $data): void {

        $pdo = PDO::getInstance();

        $sql = "UPDATE `".CONCLUDIS_TABLE_CACHE."` SET `data` = :d WHERE `key` = :k";

        $pdo->update($sql, [':k' => $key, ':d' => json_encode($data, JSON_THROW_ON_ERROR | JSON_PRETTY_PRINT)]);

    }

}