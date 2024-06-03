<?php

namespace Concludis\ApiClient\V2\Responses;

use Concludis\ApiClient\Common\ApiResponse;
use Concludis\ApiClient\Common\EndpointResponse;
use Concludis\ApiClient\Resources\Project;
use Concludis\ApiClient\V2\Endpoints\ProjectsGetEndpoint;
use Concludis\ApiClient\V2\Factory\ProjectFactory;
use Exception;

class ProjectsProjectGetResponse extends EndpointResponse {
    /**
     * @var Project|null
     */
    public ?Project $project = null;


    /**
     * @param ProjectsGetEndpoint $endpoint
     * @param ApiResponse $response
     * @throws Exception
     */
    public function __construct(ProjectsGetEndpoint $endpoint, ApiResponse $response) {
        parent::__construct($endpoint, $response);

        if($response->success) {
            $source_id = $this->endpoint->client()->source()->id;
            $project = $response->data['project'];
            $this->project = ProjectFactory::createFromResponseObject($source_id, $project);
        }
    }
}