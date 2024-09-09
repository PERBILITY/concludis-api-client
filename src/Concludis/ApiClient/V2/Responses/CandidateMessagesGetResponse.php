<?php

namespace Concludis\ApiClient\V2\Responses;

use Concludis\ApiClient\Common\ApiResponse;
use Concludis\ApiClient\Common\EndpointResponse;
use Concludis\ApiClient\V2\Endpoints\CandidateMessagesGetEndpoint;

class CandidateMessagesGetResponse extends EndpointResponse {

    public ?array $messages = null;

    public function __construct(CandidateMessagesGetEndpoint $endpoint, ApiResponse $response) {
        parent::__construct($endpoint, $response);

        if($response->success && ($response->data['success'] ?? false)) {
            $messages = $response->data['messages'] ?? null;
            if(is_array($messages)) {
                $this->messages = $messages;
            }
        }
    }

}