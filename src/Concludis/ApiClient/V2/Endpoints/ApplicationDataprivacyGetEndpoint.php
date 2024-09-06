<?php

namespace Concludis\ApiClient\V2\Endpoints;

use Concludis\ApiClient\Common\AbstractEndpoint;
use Concludis\ApiClient\Common\TraitParamsEndpoint;
use Concludis\ApiClient\V2\Client\Client;
use Concludis\ApiClient\V2\Responses\ApplicationDataprivacyGetResponse;

class ApplicationDataprivacyGetEndpoint extends AbstractEndpoint {

    use TraitParamsEndpoint;

    public const PARAM_KEY_PROJECT_ID = 'project_id';
    public const PARAM_KEY_LOCATION_IDS = 'location_ids';

    public function __construct(Client $client) {
        parent::__construct($client);
    }


    public function paramsDefinition(): array {
        return [
            self::PARAM_KEY_PROJECT_ID => ['cast' => 'int'],
            self::PARAM_KEY_LOCATION_IDS => ['cast' => 'int[]']
        ];
    }

    /**
     * @return ApplicationDataprivacyGetResponse
     */
    public function call(): ApplicationDataprivacyGetResponse {

        $url_params = [
            '{locale}' => $this->locale
        ];

        $endpoint = '/{locale}/application/dataprivacy';

        $endpoint = strtr($endpoint, $url_params);

        $response = $this->client->call($endpoint, $this->params, 'GET');

        return new ApplicationDataprivacyGetResponse($this, $response);
    }


}