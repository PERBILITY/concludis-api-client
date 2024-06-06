<?php
/**
 * concludis - InstallService.php.
 * @author: Alex Agaltsev
 * created on: 12.06.2019
 */

namespace Concludis\ApiClient\Service;


use Concludis\ApiClient\Config\Baseconfig;
use Concludis\ApiClient\Config\Setupitem;
use Concludis\ApiClient\Database\PDO;
use Concludis\ApiClient\Util\CliUtil;
use DirectoryIterator;
use Exception;
use RuntimeException;
use ZipArchive;

class InstallService {

    private static function getCredentials(): array {

        $errors = [];

        $db_prefix = Baseconfig::$db_prefix;
        $db_host = Baseconfig::$db_host;
        $db_name = Baseconfig::$db_name;
        $db_user = Baseconfig::$db_user;
        $db_pass = Baseconfig::$db_pass;

        if($db_prefix === ''){
            $errors['prefix'] = 'db_prefix is empty';
        }
        if($db_host === ''){
            $errors['host'] = 'db_host is empty';
        }
        if($db_name === ''){
            $errors['name'] = 'db_name is empty';
        }
        if($db_user === ''){
            $errors['user'] = 'db_user is empty';
        }
        if($db_pass === ''){
            $errors['pass'] = 'db_pass is empty';
        }

        if(empty($errors)){
            return [
                'prefix' => $db_prefix,
                'host'   => $db_host,
                'name'   => $db_name,
                'user'   => $db_user,
                'pass'   => $db_pass
            ];
        }

        return [
            'errors' => $errors
        ];

    }

    private static function getTablePairs($prefix): array {
        return [
            '`tbl_global_category`' => '`' . $prefix . 'global_category`',
            '`tbl_global_classification`' => '`' . $prefix . 'global_classification`',
            '`tbl_global_geo`' => '`' . $prefix . 'global_geo`',
            '`tbl_global_occupation`' => '`' . $prefix . 'global_occupation`',
            '`tbl_global_schedule`' => '`' . $prefix . 'global_schedule`',
            '`tbl_global_seniority`' => '`' . $prefix . 'global_seniority`',
            '`tbl_local_category`' => '`' . $prefix . 'local_category`',
            '`tbl_local_classification`' => '`' . $prefix . 'local_classification`',
            '`tbl_local_location`' => '`' . $prefix . 'local_location`',
            '`tbl_local_occupation`' => '`' . $prefix . 'local_occupation`',
            '`tbl_local_region`' => '`' . $prefix . 'local_region`',
            '`tbl_local_schedule`' => '`' . $prefix . 'local_schedule`',
            '`tbl_local_seniority`' => '`' . $prefix . 'local_seniority`',
            '`tbl_local_company`' => '`' . $prefix . 'local_company`',
            '`tbl_local_group`' => '`' . $prefix . 'local_group`',
            '`tbl_local_board`' => '`' . $prefix . 'local_board`',
            '`tbl_project`' => '`' . $prefix . 'project`',
            '`tbl_project_ad_container`' => '`' . $prefix . 'project_ad_container`',
            '`tbl_project_category`' => '`' . $prefix . 'project_category`',
            '`tbl_project_classification`' => '`' . $prefix . 'project_classification`',
            '`tbl_project_location`' => '`' . $prefix . 'project_location`',
            '`tbl_project_schedule`' => '`' . $prefix . 'project_schedule`',
            '`tbl_project_seniority`' => '`' . $prefix . 'project_seniority`',
            '`tbl_project_company`' => '`' . $prefix . 'project_company`',
            '`tbl_project_group`' => '`' . $prefix . 'project_group`',
            '`tbl_project_board`' => '`' . $prefix . 'project_board`',
            '`tbl_setup`' => '`' . $prefix . 'setup`',
            '`tbl_i18n`' => '`' . $prefix . 'i18n`',
            '`tbl_cache`' => '`' . $prefix . 'cache`'
        ];
    }

    /**
     * @param array $credentials
     * @throws Exception
     */
    private static function handleCredentialsErrors(array $credentials): void {

        if(array_key_exists('errors', $credentials)){
            CliUtil::output('Errors occurred...');
            $errors = 'Errors occurred...' . "\n";
            foreach ($credentials['errors'] as $key => $error) {
                CliUtil::output('-> ' . $key . ': ' . $error);
                $errors .= '-> ' . $key . ': ' . $error . "\n";
            }

            throw new RuntimeException($errors);
        }
    }

    private static function getFixedQueries(string $file_path, $pairs): string {
        return strtr(file_get_contents($file_path), $pairs);
    }

    /**
     * @throws Exception
     */
    public static function install(): void {

        $credentials = self::getCredentials();
        self::handleCredentialsErrors($credentials);

        if (file_exists(CONCLUDIS_PATH . '/database/create.sql')){

            $file = file_get_contents(CONCLUDIS_PATH . '/database/create.sql');
            $prefix = $credentials['prefix'];
            $pairs = self::getTablePairs($prefix);

            $q = strtr($file, $pairs);

            $pdo = PDO::getInstance();
            $pdo->exec($q);

            self::extractGlobalData($pairs);

        } else {
            throw new RuntimeException('Can\'t retrieve decreatelete.sql...');
        }

    }

    /**
     * @return bool
     * @throws Exception
     */
    public static function update(): bool {

        $credentials = self::getCredentials();
        self::handleCredentialsErrors($credentials);

        $patches = [];
        $latest_patch = 0;

        foreach (new DirectoryIterator(CONCLUDIS_PATH . '/database') as $file) {
            if ($file->isFile()) {

                $fn = $file->getFilename();

                if(preg_match('/patch-(\d{5})\.sql/', $fn, $matches)) {
                    $idx = (int)$matches[1];
                    $patches[$idx] = CONCLUDIS_PATH . '/database/' . $matches[0];
                }
            }
        }

        if(!empty($patches)) {
            $latest_patch = (int)max(array_keys($patches));
        }

        $dbVersion = self::getCurrentDbVersion();
        $revision = (int)$dbVersion->getValue()->revision;

        for($i = $revision; $i <= $latest_patch; $i++) {

            if(array_key_exists($i, $patches)) {
                self::execPatch($i, $patches[$i]);
                continue;
            }

            throw new RuntimeException(sprintf("Patch %s not found", $i));
        }

        return true;
    }

    /**
     * @throws Exception
     */
    public static function uninstall(): void {

        $credentials = self::getCredentials();
        self::handleCredentialsErrors($credentials);

        if (file_exists(CONCLUDIS_PATH . '/database/delete.sql')){

            $file = file_get_contents(CONCLUDIS_PATH . '/database/delete.sql');
            $prefix = $credentials['prefix'];
            $pairs = self::getTablePairs($prefix);

            $q = strtr($file, $pairs);

            $pdo = PDO::getInstance();
            $pdo->exec($q);

        } else {
            throw new RuntimeException('Can\'t retrieve delete.sql...');
        }

    }

    /**
     * @param int $patch_revision
     * @param string $patch_file
     * @return bool
     * @throws Exception
     */
    public static function execPatch(int $patch_revision, string $patch_file): bool {

        $dbVersion = self::getCurrentDbVersion();
        $revision = (int)$dbVersion->getValue()->revision;

        if(!($revision === $patch_revision)) {
            throw new RuntimeException(sprintf('Patch %s should not be executed for current revision %s.', $patch_revision, $revision));
        }


        $credentials = self::getCredentials();

        if(array_key_exists('errors', $credentials)){
            throw new RuntimeException('Invalid credentials');
        }
        $prefix = $credentials['prefix'];
        $pairs = self::getTablePairs($prefix);


        $pdo = PDO::getInstance();
        $q = self::getFixedQueries($patch_file, $pairs);
        if($pdo->exec($q) === false) {
            throw new RuntimeException('Execution failed: ' . "\n" . $q);
        }

        $dbVersion->value->revision++;
        if($dbVersion->save()) {
            CliUtil::output(sprintf('successfully patched to revision %s', $dbVersion->value->revision));
            return true;
        }

        return false;
    }

    /**
     * @return Setupitem
     * @throws Exception
     */
    public static function getCurrentDbVersion(): Setupitem {

        $dbVersion = new Setupitem(['key' => 'db_version']);

        $value = $dbVersion->getValue();

        if(!property_exists($value, 'revision')) {
            throw new RuntimeException('Revision not found.');
        }

        return $dbVersion;
    }

    /**
     * @throws Exception
     */
    private static function extractGlobalData(array $pairs): void {

        $zip = new ZipArchive();
        $target_dir = CONCLUDIS_PATH . '/database';
        $path_zipfile = $target_dir . '/global-data.zip';
        $res = $zip->open($path_zipfile);

        if($res === true){
            $zip->extractTo($target_dir);
            $zip->close();

            $pdo = PDO::getInstance();

            for ($i=0; $i < 11; $i++){
                $fn = CONCLUDIS_PATH . '/database/global-data-' . $i . '.sql';
                $q1 = self::getFixedQueries($fn, $pairs);
                $pdo->exec( $q1);
                unlink($fn);
            }

            return;
        }

        throw new RuntimeException('global data extraction failed');

    }

}