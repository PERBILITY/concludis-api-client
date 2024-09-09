<?php

namespace Concludis\ApiClient\V2\Responses;

use Concludis\ApiClient\Common\ApiResponse;
use Concludis\ApiClient\Common\EndpointResponse;
use Concludis\ApiClient\V2\Endpoints\CandidateProfileimageDeleteEndpoint;

class CandidateProfileimageDeleteResponse extends EndpointResponse {

    public function __construct(CandidateProfileimageDeleteEndpoint $endpoint, ApiResponse $response) {
        parent::__construct($endpoint, $response);
    }

}