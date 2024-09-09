<?php

namespace Concludis\ApiClient\V2\Endpoints;

use Concludis\ApiClient\Common\AbstractEndpoint;
use Concludis\ApiClient\Common\TraitParamsEndpoint;
use Concludis\ApiClient\V2\Responses\CandidateMessagesGetResponse;

class CandidateMessagesGetEndpoint extends AbstractEndpoint
{

    use TraitParamsEndpoint;
    public const PARAM_KEY_CANDIDATE_ID = 'candidate_id';

    public function paramsDefinition(): array {
        return [
            self::PARAM_KEY_CANDIDATE_ID => ['cast' => 'int']
        ];
    }

    /**
     * @return CandidateMessagesGetResponse
     */
    public function call(): CandidateMessagesGetResponse {

        $url_params = [
            '{locale}' => $this->locale
        ];

        $endpoint = '/{locale}/candidate/message';

        $endpoint = strtr($endpoint, $url_params);

        $response = $this->client->call($endpoint, $this->params, 'GET');

        return new CandidateMessagesGetResponse($this, $response);
    }

}