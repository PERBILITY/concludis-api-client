<?php

namespace Concludis\ApiClient\Storage;

use Concludis\ApiClient\Database\PDO;
use Exception;

class I18nRepository {


    /**
     * @param string $model
     * @param string $key
     * @param array $arr_fields
     * @param bool $fill_non_existing
     * @return array
     * @throws Exception
     */
    public static function fetchTranslationsByKeys(string $model, string $key, array $arr_fields, bool $fill_non_existing = true): array {

        $pdo = PDO::getInstance();

        $sql = 'SELECT `model`, `field`, `key`, `locale`, `translation` 
        FROM `'.CONCLUDIS_TABLE_I18N.'`  
        WHERE `model`= :imodel 
        AND `key` = :ifk';

        $result = $pdo->select($sql, [
            ':imodel' => $model,
            ':ifk' => $key,
        ]);

        $r = [];
        foreach ($result as $v) {
            $locale = $v['locale'];
            $field = $v['field'];
            $translation = $v['translation'];

            if (!isset($r[$locale])) {
                $r[$locale] = array();
                if ($fill_non_existing) {
                    foreach ($arr_fields as $fld) {
                        $r[$locale][$fld] = '';
                    }
                }
            }

            if (in_array($field, $arr_fields, true)) {
                $r[$locale][$field] = $translation;
            }
        }

        return $r;
    }

    /**
     * @param string $model
     * @param string $key
     * @param array $data
     * @param bool $delete_non_present
     * @param bool $save_empty_values
     * @return void
     * @throws Exception
     */
    public static function saveTranslations(string $model, string $key, array $data, bool $delete_non_present = false, bool $save_empty_values = false): void {

        // We run into problems if we always delete non-present translations.
        // The reason is that each translatable child object holds only the translations of parents objects locales-available.
        // This deletes translations of others. Workaround: only update translations

        if($delete_non_present) {
            self::deleteTranslations(
                $model,
                $key
            );
        }

        foreach ($data as $locale => $fields) {
            foreach ($fields as $field => $translation) {
                self::saveTranslation(
                    $model,
                    $key,
                    $field,
                    $locale,
                    $translation,
                    $save_empty_values
                );
            }
        }

    }

    /**
     * @param string $model
     * @param string $key
     * @return bool
     * @throws Exception
     */
    public static function deleteTranslations(string $model, string $key): bool {

        $pdo = PDO::getInstance();

        $sql = 'DELETE FROM `'.CONCLUDIS_TABLE_I18N.'` WHERE `model` = :imodel AND `key`= :ifk';

        return $pdo->delete($sql, [
            ':imodel' => $model,
            ':ifk' => $key
        ]);
    }


    /**
     * @param string $model
     * @param string $key
     * @param string $field
     * @param string $locale
     * @param string $translation
     * @param bool $save_empty_values
     * @return bool
     * @throws Exception
     */
    public static function saveTranslation(string $model, string $key, string $field, string $locale, string $translation, bool $save_empty_values = false): bool {

        $pdo = PDO::getInstance();

        if (empty($translation) && !$save_empty_values) {

            $sql = 'DELETE FROM `'.CONCLUDIS_TABLE_I18N.'` WHERE `model` = :imodel AND `key`= :ifk AND `field` = :ifield AND `locale` = :locale';

            return $pdo->delete($sql, [
                ':imodel' => $model,
                ':ifk' => $key,
                ':ifield' => $field,
                ':locale' => $locale
            ]);
        }


        $sql = 'INSERT INTO `'.CONCLUDIS_TABLE_I18N.'` (`model`, `key`, `field`, `locale`, `translation`)
        VALUES (:imodel, :ifk, :ifield, :locale, :itrans)
        ON DUPLICATE KEY UPDATE `translation` = :utrans';

        return $pdo->insert($sql, [
            ':imodel' => $model,
            ':ifk' => $key,
            ':ifield' => $field,
            ':locale' => $locale,
            ':itrans' => $translation,
            ':utrans' => $translation
        ]);
    }
}