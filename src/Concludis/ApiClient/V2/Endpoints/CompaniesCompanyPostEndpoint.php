<?php


namespace Concludis\ApiClient\V2\Endpoints;


use Concludis\ApiClient\Common\AbstractEndpoint;
use Concludis\ApiClient\Common\TraitParamsEndpoint;
use Concludis\ApiClient\V2\Client\Client;
use Concludis\ApiClient\V2\Responses\CompaniesCompanyPostResponse;

class CompaniesCompanyPostEndpoint extends AbstractEndpoint {

    use TraitParamsEndpoint;

    public const PARAM_KEY_PARENT_ID = 'parent_id';
    public const PARAM_KEY_NAME = 'name';
    public const PARAM_KEY_ADDRESS = 'address';
    public const PARAM_KEY_CITY = 'city';
    public const PARAM_KEY_POSTAL_CODE = 'postal_code';
    public const PARAM_KEY_COUNTRY_CODE = 'country_code';
    public const PARAM_KEY_COMMERCIALREGISTER = 'commercialregister';
    public const PARAM_KEY_EXTERNAL_ID = 'external_id';
    public const PARAM_KEY_EDU_AUTH = 'edu_auth';
    public const PARAM_KEY_PHONE_NUMBER = 'phone_number';
    public const PARAM_KEY_INVOICE_EMAIL = 'invoice_email';
    public const PARAM_KEY_WEBSITE_URL = 'website_url';
    public const PARAM_KEY_CAREER_SITE_URL = 'career_site_url';
    public const PARAM_KEY_XING_PROFILE_URL = 'xing_profile_url';
    public const PARAM_KEY_INDUSTRY_ID = 'industry_id';

    private string $locale = 'de_DE';

    public function __construct(Client $client) {
        parent::__construct($client);
    }

    public function paramsDefinition(): array {
        return [
            self::PARAM_KEY_PARENT_ID => ['cast' => 'int'],
            self::PARAM_KEY_NAME => ['cast' => 'string'],
            self::PARAM_KEY_ADDRESS => ['cast' => 'string'],
            self::PARAM_KEY_CITY => ['cast' => 'string'],
            self::PARAM_KEY_POSTAL_CODE => ['cast' => 'string'],
            self::PARAM_KEY_COUNTRY_CODE => ['cast' => 'string'],
            self::PARAM_KEY_COMMERCIALREGISTER => ['cast' => 'string'],
            self::PARAM_KEY_EXTERNAL_ID => ['cast' => 'string'],
            self::PARAM_KEY_EDU_AUTH => ['cast' => 'bool'],
            self::PARAM_KEY_PHONE_NUMBER => ['cast' => 'string'],
            self::PARAM_KEY_INVOICE_EMAIL => ['cast' => 'string'],
            self::PARAM_KEY_WEBSITE_URL => ['cast' => 'string'],
            self::PARAM_KEY_CAREER_SITE_URL => ['cast' => 'string'],
            self::PARAM_KEY_XING_PROFILE_URL => ['cast' => 'string'],
            self::PARAM_KEY_INDUSTRY_ID => ['cast' => 'int'],
        ];
    }

    /**
     * @return CompaniesCompanyPostResponse
     */
    public function call(): CompaniesCompanyPostResponse {

        $url_params = [
            '{locale}' => $this->locale
        ];

        $endpoint = '/{locale}/companies/company';

        $endpoint = strtr($endpoint, $url_params);

        $response = $this->client->call($endpoint, $this->params, 'POST');

        return new CompaniesCompanyPostResponse($this, $response);
    }

}