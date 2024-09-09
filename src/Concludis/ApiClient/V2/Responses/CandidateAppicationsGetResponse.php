<?php

namespace Concludis\ApiClient\V2\Responses;

use Concludis\ApiClient\Common\ApiResponse;
use Concludis\ApiClient\Common\EndpointResponse;
use Concludis\ApiClient\V2\Endpoints\CandidateApplicationsGetEndpoint;

class CandidateAppicationsGetResponse extends EndpointResponse {

    public ?array $applications = null;
    public function __construct(CandidateApplicationsGetEndpoint $endpoint, ApiResponse $response) {
        parent::__construct($endpoint, $response);

        if($response->success && is_array($response->data['applications'])) {
            $this->applications = $response->data['applications'];
        }
    }

}