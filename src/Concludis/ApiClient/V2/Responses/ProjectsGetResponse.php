<?php

namespace Concludis\ApiClient\V2\Responses;

use Concludis\ApiClient\Common\ApiResponse;
use Concludis\ApiClient\Common\EndpointResponse;
use Concludis\ApiClient\Resources\Project;
use Concludis\ApiClient\V2\Endpoints\ProjectsGetEndpoint;
use Concludis\ApiClient\V2\Factory\ProjectFactory;
use Exception;

class ProjectsGetResponse extends EndpointResponse {

    /**
     * @var Project[]|null
     */
    public ?array $projects = [];

    /**
     * @var int
     */
    public int $count = -1;

    /**
     * @var int
     */
    public int $page = -1;

    /**
     * @var int
     */
    public int $items_per_page = -1;


    /**
     * @param ProjectsGetEndpoint $endpoint
     * @param ApiResponse $response
     * @throws Exception
     */
    public function __construct(ProjectsGetEndpoint $endpoint, ApiResponse $response) {
        parent::__construct($endpoint, $response);

        if($response->success) {

            $source_id = $this->endpoint->client()->source()->id;
            $this->count = (int)$response->data['count'];
            $this->page = (int)$response->data['page'];
            $this->items_per_page = (int)$response->data['items_per_page'];

            $projects = $response->data['projects'];
            $this->projects = [];
            foreach($projects as $project) {
                $this->projects[] = ProjectFactory::createFromResponseObject($source_id, $project);
            }
        }
    }

}