<?php

namespace Concludis\ApiClient\V2\Endpoints;

use Concludis\ApiClient\Common\AbstractEndpoint;
use Concludis\ApiClient\Common\TraitParamsEndpoint;
use Concludis\ApiClient\V2\Client\Client;
use Concludis\ApiClient\V2\Responses\ApplicationApplyPostResponse;

class ApplicationApplyPostEndpoint extends AbstractEndpoint {

    use TraitParamsEndpoint;

    public const PARAM_KEY_PROJECT_ID = 'project_id';
    public const PARAM_KEY_LOCATION_IDS = 'location_ids';
    public const PARAM_KEY_SOURCE_ID = 'source_id';
    public const PARAM_KEY_IS_INTERNAL = 'is_internal';
    public const PARAM_KEY_CANDIDATE = 'candidate';
    public const PARAM_KEY_OPTIONS = 'options';

    public function __construct(Client $client) {
        parent::__construct($client);
    }


    public function paramsDefinition(): array {
        return [
            self::PARAM_KEY_PROJECT_ID => ['cast' => 'int'],
            self::PARAM_KEY_LOCATION_IDS => ['cast' => 'int[]'],
            self::PARAM_KEY_SOURCE_ID => ['cast' => 'int'],
            self::PARAM_KEY_IS_INTERNAL => ['cast' => 'bool'],
            self::PARAM_KEY_CANDIDATE => ['cast' => 'array'],
            self::PARAM_KEY_OPTIONS => ['cast' => 'array']
        ];
    }

    /**
     * @return ApplicationApplyPostResponse
     */
    public function call(): ApplicationApplyPostResponse {

        $url_params = [
            '{locale}' => $this->locale
        ];

        $endpoint = '/{locale}/application/apply';

        $endpoint = strtr($endpoint, $url_params);

        $response = $this->client->call($endpoint, $this->params, 'POST');

        return new ApplicationApplyPostResponse($this, $response);
    }

}