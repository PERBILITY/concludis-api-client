<?php
/**
 * @package:    concludis
 * @file:       SaveHandler.php
 *
 * @author:     concludis <aa@concludis.de>
 * @copyright:  concludis GmbH © 2007-2022
 * @created:    22.08.22
 * @link:       https://www.concludis.com
 */

namespace Concludis\ApiClient\Common;

use Concludis\ApiClient\Resources\Project;
use Concludis\ApiClient\Storage\ProjectRepository;
use Exception;

class ProjectSaveHandler {
    /**
     * @param string $client_source_id
     * @param int $project_id
     * @return void
     * @throws Exception
     */
    public function deleteProject(string $client_source_id, int $project_id): void {
        ProjectRepository::purgeProjectById($client_source_id, $project_id);
    }

    /**
     * @param Project $project
     * @return void
     * @throws Exception
     */
    public function saveProject(Project $project): void {
        $project->save();
    }

    /**
     * @param string $client_source_id
     * @param string $update_datetime
     * @return void
     * @throws Exception
     */
    public function purgeDeprecatedProjects(string $client_source_id, string $update_datetime): void {
        ProjectRepository::purgeDeprecatedProjectsBySource($client_source_id, $update_datetime);
    }

}