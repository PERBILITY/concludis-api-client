<?php
/**
 * concludis - SetupRepository.php.
 * @author: Alex Agaltsev
 * created on: 13.06.2019
 */

namespace Concludis\ApiClient\Storage;


use Concludis\ApiClient\Config\Setupitem;
use Concludis\ApiClient\Database\PDO;
use Exception;

class SetupRepository {

    /**
     * @param string $key
     * @return string|null
     * @throws Exception
     */
    public static function fetchValue(string $key): ?string {

        $pdo = PDO::getInstance();

        $sql = 'SELECT `value` FROM `'.CONCLUDIS_TABLE_SETUP.'` WHERE `key` = :key';

        $res = $pdo->selectOne($sql, [':key' => $key]);

        if($res === false) {
            return null;
        }

        return (string)$res['value'];
    }

    /**
     * @param Setupitem $value
     * @return bool
     * @throws Exception
     */
    public static function saveSetupitem(Setupitem $value): bool {

        $pdo = PDO::getInstance();

        $sql = 'INSERT INTO `'.CONCLUDIS_TABLE_SETUP.'` (`key`, `value`) VALUES(:mykey, :myvalue) 
                ON DUPLICATE KEY UPDATE `key` = :mykey1, `value` = :myvalue1';

        $ph = [
            ':mykey' => $value->key,
            ':myvalue' => json_encode($value->getValue(), JSON_THROW_ON_ERROR),
            ':mykey1' => $value->key,
            ':myvalue1' => json_encode($value->getValue(), JSON_THROW_ON_ERROR),
        ];

        return $pdo->insert($sql, $ph);

    }

    /**
     * @param string $key
     * @return bool
     * @throws Exception
     */
    public static function deleteValue(string $key): bool {

        $pdo = PDO::getInstance();

        $sql = 'DELETE FROM `'.CONCLUDIS_TABLE_SETUP.'` WHERE `key` = :key AND `key` <> \'db_version\'';

        return $pdo->delete($sql, [':key' => $key]);

    }

}