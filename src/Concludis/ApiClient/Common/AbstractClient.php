<?php


namespace Concludis\ApiClient\Common;


use Concludis\ApiClient\Config\Source;
use Concludis\ApiClient\Resources\File;
use Concludis\ApiClient\Resources\Project;
use Concludis\ApiClient\V2\Responses\ApplicationApplyPostResponse;
use Concludis\ApiClient\V2\Responses\ApplicationDataprivacyGetResponse;
use Concludis\ApiClient\V2\Responses\ApplicationSetupGetResponse;
use Concludis\ApiClient\V2\Responses\CandidateAppicationsGetResponse;
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

    /**
     * @param int $project_id
     * @param bool $is_internal
     * @param int $jobboard_id
     * @param string|null $locale
     * @return ApplicationSetupGetResponse
     */
    abstract public function fetchApplicationSetup(int $project_id, bool $is_internal, int $jobboard_id = 0, ?string $locale = null): ApplicationSetupGetResponse;

    /**
     * @param int $project_id
     * @param array $location_ids
     * @param string|null $locale
     * @return ApplicationDataprivacyGetResponse
     */
    abstract public function fetchDataPrivacyStatement(int $project_id, array $location_ids, ?string $locale = null): ApplicationDataprivacyGetResponse;

    /**
     * @param int $candidate_id
     * @return CandidateAppicationsGetResponse
     */
    abstract public function fetchCandidateApplications(int $candidate_id): CandidateAppicationsGetResponse;

    /**
     * @param int $candidate_id
     * @param int $file_id
     * @param array|null $meta
     * @return void
     * @throws Exception
     */
    abstract public function deleteCandidateFile(int $candidate_id, int $file_id, ?array $meta = null): void;

    /**
     * @param int $candidate_id
     * @param int $file_id
     * @return File
     * @throws Exception
     */
    abstract public function getCandidateFile(int $candidate_id, int $file_id): File;

    /**
     * @param int $candidate_id
     * @param File $file
     * @param array|null $meta
     * @return File
     * @throws Exception
     */
    abstract public function postCandidateFile(int $candidate_id, File $file, ?array $meta = null): File;

    /**
     * @param int $candidate_id
     * @return array
     * @throws Exception
     */
    abstract public function getCandidateHrjson(int $candidate_id): array;

    /**
     * @param array $candidate The hropen candidate object as php-array
     * @return void
     * @throws Exception
     */
    abstract public function postCandidateHrjson(array $candidate): void;

    /**
     * @param int $candidate_id
     * @param string $message the base64-json encoded message object
     * @return void
     * @throws Exception
     */
    abstract public function postCandidateIncomingmessage(int $candidate_id, string $message): void;

    /**
     * @param int $candidate_id
     * @param int $message_id
     * @param string $action
     * @return void
     * @throws Exception
     */
    abstract public function patchCandidateMessage(int $candidate_id, int $message_id, string $action): void;

    /**
     * @param int $candidate_id
     * @return array[]
     * @throws Exception
     */
    abstract public function getCandidateMessages(int $candidate_id): array;

    /**
     * @param int $candidate_id
     * @return void
     * @throws Exception
     */
    abstract public function deleteCandidateProfileimage(int $candidate_id): void;

    /**
     * @param int $candidate_id
     * @param File $file
     * @return File
     * @throws Exception
     */
    abstract public function postCandidateProfileimage(int $candidate_id, File $file): File;


}