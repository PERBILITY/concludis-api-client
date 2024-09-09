<?php

namespace Concludis\ApiClient\V2\Responses;

use Concludis\ApiClient\Common\ApiResponse;
use Concludis\ApiClient\Common\EndpointResponse;
use Concludis\ApiClient\V2\Endpoints\CandidateIncomingmessagePostEndpoint;

class CandidateIncomingmessagePostResponse extends EndpointResponse {

    public function __construct(CandidateIncomingmessagePostEndpoint $endpoint, ApiResponse $response) {
        parent::__construct($endpoint, $response);
    }

}