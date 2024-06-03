<?php


namespace Concludis\ApiClient\V2\Responses;

use Concludis\ApiClient\Common\ApiResponse;
use Concludis\ApiClient\Common\EndpointResponse;
use Concludis\ApiClient\Resources\Company;
use Concludis\ApiClient\V2\Endpoints\CompaniesGetEndpoint;
use Concludis\ApiClient\V2\Factory\CompanyFactory;

class CompaniesGetResponse extends EndpointResponse  {

    /**
     * @var Company[]|null
     */
    public ?array $companies = null;

    /**
     * @var int
     */
    public int $count = -1;

    /**
     * @var int
     */
    public int $page = -1;

    /**
     * @var int
     */
    public int $items_per_page = -1;


    /**
     * GetCompaniesResponse constructor.
     * @param CompaniesGetEndpoint $endpoint
     * @param ApiResponse $response
     */
    public function __construct(CompaniesGetEndpoint $endpoint, ApiResponse $response) {
        parent::__construct($endpoint, $response);

        if($response->success) {

            $source_id = $this->endpoint->client()->source()->id;
            $this->count = (int)$response->data['count'];
            $this->page = (int)$response->data['page'];
            $this->items_per_page = (int)$response->data['items_per_page'];

            $companies = $response->data['companies'];
            $this->companies = [];
            foreach($companies as $company) {
                $this->companies[] = CompanyFactory::createFromResponseObject($source_id, $company);
            }
        }
    }
}