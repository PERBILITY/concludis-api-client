<?php


namespace Concludis\ApiClient\Service;


use Concludis\ApiClient\Common\ProjectSaveHandler;
use Concludis\ApiClient\Config\Baseconfig;
use Concludis\ApiClient\Config\Source;
use Concludis\ApiClient\Storage\BoardRepository;
use Concludis\ApiClient\Storage\CategoryRepository;
use Concludis\ApiClient\Storage\ClassificationRepository;
use Concludis\ApiClient\Storage\CompanyRepository;
use Concludis\ApiClient\Storage\GroupRepository;
use Concludis\ApiClient\Storage\LocationRepository;
use Concludis\ApiClient\Storage\OccupationRepository;
use Concludis\ApiClient\Storage\ProjectRepository;
use Concludis\ApiClient\Storage\RegionRepository;
use Concludis\ApiClient\Storage\ScheduleRepository;
use Concludis\ApiClient\Storage\SeniorityRepository;
use Concludis\ApiClient\Util\CliUtil;
use Exception;

class ApiService {

    /**
     * @param bool $cli
     * @return void
     * @throws Exception
     */
    public static function pullBoards(bool $cli): void {
        foreach(Baseconfig::$sources as $source) {
            self::pullBoardsFromSource($source, $cli);
        }
    }

    /**
     * @param bool $cli
     * @return void
     * @throws Exception
     */
    public static function pullProjects(bool $cli): void {
        self::purgeDeprecatedSources();
        foreach(Baseconfig::$sources as $source) {
            self::pullProjectsFromSource($source, $cli);
        }
        self::purgeUnused();
        try {
            ProjectRepository::optimizeAdContainerTable();
        } catch (Exception) {
        }
    }

    /**
     * @return void
     * @throws Exception
     */
    public static function purgeDeprecatedSources(): void {
        CompanyRepository::purgeDeprecatedSources();
        BoardRepository::purgeDeprecatedSources();
        GroupRepository::purgeDeprecatedSources();
        ProjectRepository::purgeDeprecatedSources();
        CategoryRepository::purgeDeprecatedSources();
        OccupationRepository::purgeDeprecatedSources();
        LocationRepository::purgeDeprecatedSources();
        RegionRepository::purgeDeprecatedSources();
        ClassificationRepository::purgeDeprecatedSources();
        ScheduleRepository::purgeDeprecatedSources();
        SeniorityRepository::purgeDeprecatedSources();
    }

    /**
     * @param string|null $source_id
     * @return void
     * @throws Exception
     */
    private static function purgeUnused(?string $source_id = null): void {
        CompanyRepository::purgeUnused($source_id);
//        Unused boards should not be deleted
//        BoardRepository::purgeUnused();
        GroupRepository::purgeUnused($source_id);
        CategoryRepository::purgeUnused($source_id);
        OccupationRepository::purgeUnused($source_id);
        LocationRepository::purgeUnused($source_id);
        RegionRepository::purgeUnused($source_id);
        ClassificationRepository::purgeUnused($source_id);
        ScheduleRepository::purgeUnused($source_id);
        SeniorityRepository::purgeUnused($source_id);
    }

    /**
     * @param Source $source
     * @param bool $cli
     * @return void
     * @throws Exception
     */
    public static function pullAllFromSource(Source $source, bool $cli): void {

        if($cli) {
            CliUtil::output('');
            CliUtil::output('Pull all from source...');
            CliUtil::output('id.............: ' . $source->id);
            CliUtil::output('baseurl........: ' . $source->baseurl);
            CliUtil::output('api............: ' . $source->api);
        }

        $source->client()->pullBoards($source, $cli);

        $source->client()->pullProjects($source, new ProjectSaveHandler(), $cli);

        self::purgeUnused($source->id);
    }

    /**
     * @param Source $source
     * @param bool $cli
     * @return void
     * @throws Exception
     */
    private static function pullBoardsFromSource(Source $source, bool $cli): void {

        if($cli) {
            CliUtil::output('');
            CliUtil::output('Update boards extended props from source...');
            CliUtil::output('id.............: ' . $source->id);
            CliUtil::output('baseurl........: ' . $source->baseurl);
            CliUtil::output('api............: ' . $source->api);
        }

        $source->client()->pullBoards($source, $cli);
    }

    /**
     * @param Source $source
     * @param bool $cli
     * @return void
     * @throws Exception
     */
    private static function pullProjectsFromSource(Source $source, bool $cli): void {

        if($cli) {
            CliUtil::output('');
            CliUtil::output('Fetch projects from source...');
            CliUtil::output('id.............: ' . $source->id);
            CliUtil::output('baseurl........: ' . $source->baseurl);
            CliUtil::output('api............: ' . $source->api);
        }

        $saveHandler = new ProjectSaveHandler();
        $source->client()->pullProjects($source, $saveHandler, $cli);

    }
}