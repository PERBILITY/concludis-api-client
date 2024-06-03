<?php


namespace Concludis\ApiClient\Common;


use Concludis\ApiClient\Config\Source;
use Concludis\ApiClient\Resources\Project;
use Concludis\ApiClient\V2\Responses\ApplicationApplyPostResponse;
use Exception;

abstract class AbstractClient {

    /**
     * @var Source|null
     */
    protected ?Source $source = null;

    public function __construct(?Source $source = null) {
        if($source !== null) {
            $this->source = $source;
        }
    }

    /**
     * Returns the Source of the current client
     * @return Source
     */
    public function source(): Source {
        return $this->source;
    }

    /**
     * @param Source $source
     * @return void
     */
    public function setSource(Source $source): void {
        $this->source = $source;
    }

    /**
     * @param string $endpoint
     * @param array $data
     * @param string $method
     * @return ApiResponse
     */
    abstract public function call(string $endpoint, array $data, string $method): ApiResponse;

    /**
     * @param int $project_id
     * @return Project
     * @throws Exception
     */
    abstract public function fetchProject(int $project_id): Project;

    /**
     * @param Source $source
     * @param int $project_id
     * @param ProjectSaveHandler $saveHandler
     * @param bool $cli
     * @return void
     */
    abstract public function pullProject(Source $source, int $project_id, ProjectSaveHandler $saveHandler, bool $cli): void;

    /**
     * @param Source $source
     * @param ProjectSaveHandler $saveHandler
     * @param bool $cli
     * @return void
     */
    abstract public function pullProjects(Source $source, ProjectSaveHandler $saveHandler, bool $cli): void;

    /**
     * @param Source $source
     * @param bool $cli
     * @return void
     */
    abstract public function pullBoards(Source $source,bool $cli): void;

    /**
     * @param int $project_id
     * @param array $location_ids
     * @param int $source_id
     * @param bool $is_internal
     * @param array $candidate
     * @param array $options
     * @return ApplicationApplyPostResponse
     */
    abstract public function pushApplication(int $project_id, array $location_ids, int $source_id, bool $is_internal, array $candidate, array $options): ApplicationApplyPostResponse;



}