<?php


namespace Concludis\ApiClient\V2\Responses;


use Concludis\ApiClient\Common\ApiResponse;
use Concludis\ApiClient\Common\EndpointResponse;
use Concludis\ApiClient\Resources\Company;
use Concludis\ApiClient\V2\Endpoints\CompaniesCompanyGetEndpoint;
use Concludis\ApiClient\V2\Factory\CompanyFactory;

class CompaniesCompanyGetResponse extends EndpointResponse {

    /**
     * @var Company|null
     */
    public ?Company $company;

    /**
     * GetCompaniesResponse constructor.
     * @param CompaniesCompanyGetEndpoint $endpoint
     * @param ApiResponse $response
     */
    public function __construct(CompaniesCompanyGetEndpoint $endpoint, ApiResponse $response) {
        parent::__construct($endpoint, $response);

        if($response->success) {

            $source_id = $this->endpoint->client()->source()->id;
            $company = $response->data['company'];

            $this->company = CompanyFactory::createFromResponseObject($source_id, $company);
        }

    }
}