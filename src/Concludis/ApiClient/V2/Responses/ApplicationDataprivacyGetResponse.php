<?php

namespace Concludis\ApiClient\V2\Responses;

use Concludis\ApiClient\Common\ApiResponse;
use Concludis\ApiClient\Common\EndpointResponse;
use Concludis\ApiClient\V2\Endpoints\ApplicationDataprivacyGetEndpoint;

class ApplicationDataprivacyGetResponse extends EndpointResponse {

    /**
     * The resulting dataprivacy statement, depending on the given input params
     * @var string|null
     */
    public ?string $dataprivacy_statement = null;

    /**
     * The company IDs wich may have access to the application
     * @var int[]|null
     */
    public ?array $dp_companies = null;

    /**
     * @param ApplicationDataprivacyGetEndpoint $endpoint
     * @param ApiResponse $response
     */
    public function __construct(ApplicationDataprivacyGetEndpoint $endpoint, ApiResponse $response) {
        parent::__construct($endpoint, $response);

        if ($response->success) {
            $this->dataprivacy_statement = (string)$response->data['dataprivacy_statement'];
            $this->dp_companies = (array)$response->data['dp_companies'];
        }
    }
}