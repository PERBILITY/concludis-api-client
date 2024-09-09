<?php

namespace Concludis\ApiClient\V2\Responses;

use Concludis\ApiClient\Common\ApiResponse;
use Concludis\ApiClient\Common\EndpointResponse;
use Concludis\ApiClient\V2\Endpoints\CandidateHrjsonGetEndpoint;

class CandidateHrjsonGetResponse extends EndpointResponse {

    /**
     * @var array|null
     */
    public ?array $candidate = null;

    public function __construct(CandidateHrjsonGetEndpoint $endpoint, ApiResponse $response) {
        parent::__construct($endpoint, $response);

        if($response->success && ($response->data['success'] ?? false)) {
            $candidate = $response->data['candidate'] ?? null;
            if(is_array($candidate)) {
                $this->candidate = $candidate;

            }
        }
    }

}