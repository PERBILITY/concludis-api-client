<?php


namespace Concludis\ApiClient\V2\Responses;


use Concludis\ApiClient\Common\ApiResponse;
use Concludis\ApiClient\Common\EndpointResponse;
use Concludis\ApiClient\V2\Endpoints\CompaniesCompanyPatchEndpoint;

class CompaniesCompanyPatchResponse extends EndpointResponse {

    /**
     * @var int|null
     */
    public ?int $id = null;

    /**
     * GetCompaniesResponse constructor.
     * @param CompaniesCompanyPatchEndpoint $endpoint
     * @param ApiResponse $response
     */
    public function __construct(CompaniesCompanyPatchEndpoint $endpoint, ApiResponse $response) {
        parent::__construct($endpoint, $response);

        if($response->success) {
            $this->id = (int)$response->data['id'];
        }

    }
}