<?php

namespace Concludis\ApiClient\V2\Responses;

use Concludis\ApiClient\Common\ApiResponse;
use Concludis\ApiClient\Common\EndpointResponse;
use Concludis\ApiClient\V2\Endpoints\ApplicationSetupGetEndpoint;

class ApplicationSetupGetResponse extends EndpointResponse {

    /**
     * the full application setup as array
     * @var array|null
     */
    public ?array $setup = null;

    /**
     * @param ApplicationSetupGetEndpoint $endpoint
     * @param ApiResponse $response
     */
    public function __construct(ApplicationSetupGetEndpoint $endpoint, ApiResponse $response) {
        parent::__construct($endpoint, $response);

        if ($response->success) {
            $this->setup = (array)$response->data['setup'];
        }
    }
}