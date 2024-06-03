<?php


namespace Concludis\ApiClient\Config;


class Baseconfig {

    /**
     * @var string
     */
    public static string $db_host = '';

    /**
     * @var string
     */
    public static string $db_user = '';

    /**
     * @var string
     */
    public static string $db_pass = '';

    /**
     * @var string
     */
    public static string $db_name = '';

    /**
     * @var Source[]
     */
    public static array $sources = [];

    /**
     * @var string
     */
    public static string $db_prefix = '';

    /**
     * @var string
     */
    public static string $api_base_url = '';


    public static string $default_locale = 'de_DE';


    public static function init(): void {

        if(!defined('CONCLUDIS_PATH')) {
            define('CONCLUDIS_PATH',  dirname(__DIR__, 4));
        }

        if(!defined('CONCLUDIS_TABLE_PROJECT')) {
            define('CONCLUDIS_TABLE_PROJECT', self::$db_prefix . 'project');
        }

        if(!defined('CONCLUDIS_TABLE_PROJECT_AD_CONTAINER')) {
            define('CONCLUDIS_TABLE_PROJECT_AD_CONTAINER', self::$db_prefix . 'project_ad_container');
        }

        if(!defined('CONCLUDIS_TABLE_PROJECT_CATEGORY')) {
            define('CONCLUDIS_TABLE_PROJECT_CATEGORY', self::$db_prefix . 'project_category');
        }

        if(!defined('CONCLUDIS_TABLE_PROJECT_CLASSIFICATION')) {
            define('CONCLUDIS_TABLE_PROJECT_CLASSIFICATION', self::$db_prefix . 'project_classification');
        }

        if(!defined('CONCLUDIS_TABLE_PROJECT_GROUP')) {
            define('CONCLUDIS_TABLE_PROJECT_GROUP', self::$db_prefix . 'project_group');
        }

        if(!defined('CONCLUDIS_TABLE_PROJECT_COMPANY')) {
            define('CONCLUDIS_TABLE_PROJECT_COMPANY', self::$db_prefix . 'project_company');
        }

        if(!defined('CONCLUDIS_TABLE_PROJECT_LOCATION')) {
            define('CONCLUDIS_TABLE_PROJECT_LOCATION', self::$db_prefix . 'project_location');
        }

        if(!defined('CONCLUDIS_TABLE_PROJECT_SCHEDULE')) {
            define('CONCLUDIS_TABLE_PROJECT_SCHEDULE', self::$db_prefix . 'project_schedule');
        }

        if(!defined('CONCLUDIS_TABLE_PROJECT_SENIORITY')) {
            define('CONCLUDIS_TABLE_PROJECT_SENIORITY', self::$db_prefix . 'project_seniority');
        }

        if(!defined('CONCLUDIS_TABLE_GLOBAL_CATEGORY')) {
            define('CONCLUDIS_TABLE_GLOBAL_CATEGORY', self::$db_prefix . 'global_category');
        }

        if(!defined('CONCLUDIS_TABLE_GLOBAL_CLASSIFICATION')) {
            define('CONCLUDIS_TABLE_GLOBAL_CLASSIFICATION', self::$db_prefix . 'global_classification');
        }

        if(!defined('CONCLUDIS_TABLE_GLOBAL_GEO')) {
            define('CONCLUDIS_TABLE_GLOBAL_GEO', self::$db_prefix . 'global_geo');
        }

        if(!defined('CONCLUDIS_TABLE_GLOBAL_OCCUPATION')) {
            define('CONCLUDIS_TABLE_GLOBAL_OCCUPATION', self::$db_prefix . 'global_occupation');
        }

        if(!defined('CONCLUDIS_TABLE_GLOBAL_SCHEDULE')) {
            define('CONCLUDIS_TABLE_GLOBAL_SCHEDULE', self::$db_prefix . 'global_schedule');
        }

        if(!defined('CONCLUDIS_TABLE_PROJECT_BOARD')) {
            define('CONCLUDIS_TABLE_PROJECT_BOARD', self::$db_prefix . 'project_board');
        }

        if(!defined('CONCLUDIS_TABLE_GLOBAL_SENIORITY')) {
            define('CONCLUDIS_TABLE_GLOBAL_SENIORITY', self::$db_prefix . 'global_seniority');
        }

        if(!defined('CONCLUDIS_TABLE_LOCAL_CATEGORY')) {
            define('CONCLUDIS_TABLE_LOCAL_CATEGORY', self::$db_prefix . 'local_category');
        }
        if(!defined('CONCLUDIS_TABLE_LOCAL_COMPANY')) {
            define('CONCLUDIS_TABLE_LOCAL_COMPANY', self::$db_prefix . 'local_company');
        }

        if(!defined('CONCLUDIS_TABLE_LOCAL_GROUP')) {
            define('CONCLUDIS_TABLE_LOCAL_GROUP', self::$db_prefix . 'local_group');
        }

        if(!defined('CONCLUDIS_TABLE_LOCAL_CLASSIFICATION')) {
            define('CONCLUDIS_TABLE_LOCAL_CLASSIFICATION', self::$db_prefix . 'local_classification');
        }

        if(!defined('CONCLUDIS_TABLE_LOCAL_LOCATION')) {
            define('CONCLUDIS_TABLE_LOCAL_LOCATION', self::$db_prefix . 'local_location');
        }

        if(!defined('CONCLUDIS_TABLE_LOCAL_OCCUPATION')) {
            define('CONCLUDIS_TABLE_LOCAL_OCCUPATION', self::$db_prefix . 'local_occupation');
        }

        if(!defined('CONCLUDIS_TABLE_LOCAL_REGION')) {
            define('CONCLUDIS_TABLE_LOCAL_REGION', self::$db_prefix . 'local_region');
        }

        if(!defined('CONCLUDIS_TABLE_LOCAL_SCHEDULE')) {
            define('CONCLUDIS_TABLE_LOCAL_SCHEDULE', self::$db_prefix . 'local_schedule');
        }

        if(!defined('CONCLUDIS_TABLE_LOCAL_SENIORITY')) {
            define('CONCLUDIS_TABLE_LOCAL_SENIORITY', self::$db_prefix . 'local_seniority');
        }

        if(!defined('CONCLUDIS_TABLE_LOCAL_BOARD')) {
            define('CONCLUDIS_TABLE_LOCAL_BOARD', self::$db_prefix . 'local_board');
        }

        if(!defined('CONCLUDIS_TABLE_SETUP')) {
            define('CONCLUDIS_TABLE_SETUP', self::$db_prefix . 'setup');
        }

        if(!defined('CONCLUDIS_TABLE_I18N')) {
            define('CONCLUDIS_TABLE_I18N', self::$db_prefix . 'i18n');
        }

    }

    /**
     * @param array $data
     */
    public static function addSource(array $data = []): void {
        self::$sources[] = new Source($data);
    }

    /**
     * @param string $source_id
     * @return Source|null
     */
    public static function getSourceById(string $source_id): ?Source {
        foreach (self::$sources as $source) {
            if ($source->id === $source_id) {
                return $source;
            }
        }
        return null;
    }

}