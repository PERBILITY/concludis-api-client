<?php

namespace Concludis\ApiClient\V2\Endpoints;

use Concludis\ApiClient\Common\AbstractEndpoint;
use Concludis\ApiClient\Common\TraitParamsEndpoint;
use Concludis\ApiClient\V2\Client\Client;
use Concludis\ApiClient\V2\Responses\ApplicationSetupGetResponse;

class ApplicationSetupGetEndpoint extends AbstractEndpoint {

    use TraitParamsEndpoint;

    public const PARAM_KEY_PROJECT_ID = 'project_id';
    public const PARAM_KEY_JOBBOARD_ID = 'jobboard_id';
    public const PARAM_KEY_IS_INTERNAL = 'is_internal';

    private string $locale = 'de_DE';

    public function __construct(Client $client) {
        parent::__construct($client);
    }


    public function paramsDefinition(): array {
        return [
            self::PARAM_KEY_PROJECT_ID => ['cast' => 'int'],
            self::PARAM_KEY_JOBBOARD_ID => ['cast' => 'int'],
            self::PARAM_KEY_IS_INTERNAL => ['cast' => 'bool']
        ];
    }

    /**
     * @return ApplicationSetupGetResponse
     */
    public function call(): ApplicationSetupGetResponse {

        $url_params = [
            '{locale}' => $this->locale
        ];

        $endpoint = '/{locale}/application/setup';

        $endpoint = strtr($endpoint, $url_params);

        $response = $this->client->call($endpoint, $this->params, 'GET');

        return new ApplicationSetupGetResponse($this, $response);
    }
}