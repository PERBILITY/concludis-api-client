<?php


namespace Concludis\ApiClient\V2\Endpoints;


use Concludis\ApiClient\Common\AbstractEndpoint;
use Concludis\ApiClient\Common\TraitParamsEndpoint;
use Concludis\ApiClient\V2\Client\Client;
use Concludis\ApiClient\V2\Responses\CompaniesCompanyGetResponse;

class CompaniesCompanyGetEndpoint extends AbstractEndpoint {

    use TraitParamsEndpoint;

    public const PARAM_KEY_IDENTIFIER = 'identifier';
    public const PARAM_KEY_SHOW = 'show';

    private string $locale = 'de_DE';


    public function __construct(Client $client) {
        parent::__construct($client);
    }


    public function paramsDefinition(): array {
        return [
            self::PARAM_KEY_IDENTIFIER => ['cast' => 'string'],
            self::PARAM_KEY_SHOW => ['cast' => 'string']
        ];
    }


    public function locale(string $locale): self {
        $this->locale = $locale;
        return $this;
    }

    /**
     * @return CompaniesCompanyGetResponse
     */
    public function call(): CompaniesCompanyGetResponse {

        $url_params = [
            '{locale}' => $this->locale
        ];

        $endpoint = '/{locale}/companies/company';

        $endpoint = strtr($endpoint, $url_params);

        $response = $this->client->call($endpoint, $this->params, 'GET');

        return new CompaniesCompanyGetResponse($this, $response);

    }

}