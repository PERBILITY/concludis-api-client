<?php

namespace Concludis\ApiClient\V2\Endpoints;

use Concludis\ApiClient\Common\AbstractEndpoint;
use Concludis\ApiClient\Common\TraitParamsEndpoint;
use Concludis\ApiClient\V2\Responses\CandidateHrjsonPostResponse;

class CandidateHrjsonPostEndpoint extends AbstractEndpoint
{

    use TraitParamsEndpoint;
    public const PARAM_KEY_CANDIDATE = 'candidate';

    public function paramsDefinition(): array {
        return [
            self::PARAM_KEY_CANDIDATE => ['cast' => 'array']
        ];
    }

    /**
     * @return CandidateHrjsonPostResponse
     */
    public function call(): CandidateHrjsonPostResponse {

        $url_params = [
            '{locale}' => $this->locale
        ];

        $endpoint = '/{locale}/candidate/hrjson';

        $endpoint = strtr($endpoint, $url_params);

        $response = $this->client->call($endpoint, $this->params, 'POST');

        return new CandidateHrjsonPostResponse($this, $response);
    }


}