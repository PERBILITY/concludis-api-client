<?php

namespace Concludis\ApiClient\V2\Responses;

use Concludis\ApiClient\Common\ApiResponse;
use Concludis\ApiClient\Common\EndpointResponse;
use Concludis\ApiClient\V2\Endpoints\CandidateFileDeleteEndpoint;

class CandidateFileDeleteResponse extends EndpointResponse {
    public function __construct(CandidateFileDeleteEndpoint $endpoint, ApiResponse $response) {
        parent::__construct($endpoint, $response);
    }

}