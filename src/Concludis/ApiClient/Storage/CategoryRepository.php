<?php


namespace Concludis\ApiClient\Storage;


use Concludis\ApiClient\Config\Baseconfig;
use Concludis\ApiClient\Database\PDO;
use Concludis\ApiClient\Resources\Category;
use Exception;

class CategoryRepository {

    /**
     * @param Category $element
     * @return bool
     * @throws Exception
     */
    public static function save(Category $element): bool {

        if (self::exists($element)) {
            return self::update($element);
        }

        return self::insert($element);
    }

    /**
     * @param Category $category
     * @return bool
     * @throws Exception
     */
    private static function insert(Category $category): bool {

        $pdo = PDO::getInstance();

        $sql = 'INSERT INTO `'.CONCLUDIS_TABLE_LOCAL_CATEGORY.'` SET 
        `source_id` = :source_id, 
        `category_id` = :category_id, 
        `global_category_id` = :global_category_id,
        `locale` = :locale,
        `name` = :name';

        $ph = [
            ':source_id' => $category->source_id,
            ':category_id' => $category->id,
            ':global_category_id' => $category->global_id,
            ':name' => $category->name,
            ':locale' => $category->locale
        ];

        if($pdo->insert($sql, $ph)) {

            foreach($category->occupations as $occupation) {
                OccupationRepository::save($occupation);
            }

            return true;
        }

        return false;
    }

    /**
     * @param Category $category
     * @return bool
     * @throws Exception
     */
    private static function update(Category $category): bool {

        $pdo = PDO::getInstance();

        $sql = 'UPDATE `'.CONCLUDIS_TABLE_LOCAL_CATEGORY.'` SET 
        `name` = :name,
        `locale` = :locale,
        `global_category_id` = :global_category_id                                      
        WHERE `source_id` = :source_id AND `category_id` = :category_id';

        $ph = [
            ':source_id' => $category->source_id,
            ':category_id' => $category->id,
            ':global_category_id' => $category->global_id,
            ':name' => $category->name,
            ':locale' => $category->locale
        ];

        if($pdo->update($sql, $ph)) {

            foreach($category->occupations as $occupation) {
                OccupationRepository::save($occupation);
            }

            return true;
        }

        return false;
    }

    /**
     * @param Category $category
     * @return bool|null
     * @throws Exception
     */
    public static function exists(Category $category): ?bool {

        $pdo = PDO::getInstance();

        $sql = 'SELECT COUNT(*) AS `cnt` FROM `'.CONCLUDIS_TABLE_LOCAL_CATEGORY.'` 
        WHERE `source_id` = :source_id AND `category_id` = :category_id';

        $res = $pdo->selectOne($sql, [
            ':source_id' => $category->source_id,
            ':category_id' => $category->id
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

        $sql = 'SELECT DISTINCT `source_id` FROM `'.CONCLUDIS_TABLE_LOCAL_CATEGORY.'`';

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

        $sql = 'DELETE FROM `'.CONCLUDIS_TABLE_LOCAL_CATEGORY.'` WHERE `source_id` = :source_id';

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
        $sql = 'SELECT `l`.`source_id`, `l`.`category_id` FROM `'.CONCLUDIS_TABLE_LOCAL_CATEGORY.'` `l` 
        LEFT JOIN `'.CONCLUDIS_TABLE_PROJECT_CATEGORY.'` `p`  ON (`l`.`category_id` = `p`.`category_id` AND `l`.`source_id` = `p`.`source_id`)        
        WHERE `p`.`project_id` IS NULL';

        if($source_id !== null) {
            $sql .= ' AND `l`.`source_id` = :source_id';
            $ph = [':source_id' => $source_id];
        }

        $res = $pdo->select($sql, $ph);

        $to_delete = [];
        foreach($res as $r){
            $key = $r['source_id'] . '::' . $r['category_id'];
            $to_delete[$key] = [
                'source_id' => $r['source_id'],
                'category_id' => $r['category_id']
            ];
        }

        foreach($to_delete as $v){
            $sql = 'DELETE FROM `'.CONCLUDIS_TABLE_LOCAL_CATEGORY.'` WHERE `source_id` = :source_id AND `category_id` = :category_id';
            $pdo->delete($sql, [
                ':source_id' => $v['source_id'],
                ':category_id' => $v['category_id']
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

        $sql = 'SELECT `name` FROM `'.CONCLUDIS_TABLE_GLOBAL_CATEGORY.'` WHERE `global_id` = :id';

        $res = $pdo->selectOne($sql, [':id' => $id]);

        if($res === false) {
            return null;
        }

        return (string)$res['name'];
    }

}