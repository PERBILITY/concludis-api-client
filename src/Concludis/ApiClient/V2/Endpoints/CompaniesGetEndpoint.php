<?php


namespace Concludis\ApiClient\V2\Endpoints;


use Concludis\ApiClient\Common\AbstractEndpoint;
use Concludis\ApiClient\V2\Client\Client;
use Concludis\ApiClient\V2\Responses\CompaniesGetResponse;

class CompaniesGetEndpoint extends AbstractEndpoint {

    private const FILTER_TYPE_PAGINATION = 'pagination';
    private const FILTER_TYPE_KEYWORD = 'keyword';
    private const FILTER_TYPE_NAME = 'name';
    private const FILTER_TYPE_ADDRESS = 'address';
    private const FILTER_TYPE_POSTAL_CODE = 'postal_code';
    private const FILTER_TYPE_COUNTRY_CODE = 'country_code';
    private const FILTER_TYPE_EXTERNAL_ID = 'external_id';
    private const FILTER_TYPE_PARENT_ID = 'parent_id';
    private const FILTER_TYPE_LOCATION_ID = 'location_id';

    private array $filter = [];

    public function __construct(Client $client) {
        parent::__construct($client);
    }


    public function paginate(int $page, int $items_per_page = 5): self {
        $this->filter[self::FILTER_TYPE_PAGINATION] = [
            'page' => $page,
            'ipp' => $items_per_page
        ];
        return $this;
    }

    public function addFilter(string $filter_type,  $value): self {

        $this->filter[$filter_type] = $value;

        return $this;
    }


    /**
     * @return CompaniesGetResponse
     */
    public function call(): CompaniesGetResponse {

        $url_params = [
            '{locale}' => $this->locale
        ];

        $endpoint = '/{locale}/companies';

        $endpoint = strtr($endpoint, $url_params);

        $data = [];

        if(array_key_exists(self::FILTER_TYPE_PAGINATION, $this->filter)) {
            $data['page'] = $this->filter[self::FILTER_TYPE_PAGINATION]['page'];
            $data['ipp'] = $this->filter[self::FILTER_TYPE_PAGINATION]['ipp'];
        }
        if(array_key_exists(self::FILTER_TYPE_KEYWORD, $this->filter)) {
            $data['keyword'] = (string)$this->filter[self::FILTER_TYPE_KEYWORD];
        }
        if(array_key_exists(self::FILTER_TYPE_NAME, $this->filter)) {
            $data['name'] = (string)$this->filter[self::FILTER_TYPE_NAME];
        }
        if(array_key_exists(self::FILTER_TYPE_ADDRESS, $this->filter)) {
            $data['address'] = (string)$this->filter[self::FILTER_TYPE_ADDRESS];
        }
        if(array_key_exists(self::FILTER_TYPE_POSTAL_CODE, $this->filter)) {
            $data['postal_code'] = (string)$this->filter[self::FILTER_TYPE_POSTAL_CODE];
        }
        if(array_key_exists(self::FILTER_TYPE_COUNTRY_CODE, $this->filter)) {
            $data['country_code'] = (string)$this->filter[self::FILTER_TYPE_COUNTRY_CODE];
        }
        if(array_key_exists(self::FILTER_TYPE_EXTERNAL_ID, $this->filter)) {
            $data['external_id'] = (int)$this->filter[self::FILTER_TYPE_EXTERNAL_ID];
        }
        if(array_key_exists(self::FILTER_TYPE_PARENT_ID, $this->filter)) {
            $data['parent_id'] = (int)$this->filter[self::FILTER_TYPE_PARENT_ID];
        }
        if(array_key_exists(self::FILTER_TYPE_LOCATION_ID, $this->filter)) {
            $data['location_id'] = (int)$this->filter[self::FILTER_TYPE_LOCATION_ID];
        }

        $response = $this->client->call($endpoint, $data, 'GET');

        return new CompaniesGetResponse($this, $response);

    }

}