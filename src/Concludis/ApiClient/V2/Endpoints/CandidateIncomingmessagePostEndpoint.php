<?php

namespace Concludis\ApiClient\V2\Endpoints;

use Concludis\ApiClient\Common\AbstractEndpoint;
use Concludis\ApiClient\Common\TraitParamsEndpoint;
use Concludis\ApiClient\V2\Responses\CandidateIncomingmessagePostResponse;

class CandidateIncomingmessagePostEndpoint extends AbstractEndpoint
{

    use TraitParamsEndpoint;
    public const PARAM_KEY_CANDIDATE_ID = 'candidate_id';
    public const PARAM_KEY_MESSAGE = 'message';

    public function paramsDefinition(): array {
        return [
            self::PARAM_KEY_CANDIDATE_ID => ['cast' => 'int'],
            self::PARAM_KEY_MESSAGE => ['cast' => 'string']
        ];
    }

    /**
     * @return CandidateIncomingmessagePostResponse
     */
    public function call(): CandidateIncomingmessagePostResponse {

        $url_params = [
            '{locale}' => $this->locale
        ];

        $endpoint = '/{locale}/candidate/incomingmessage';

        $endpoint = strtr($endpoint, $url_params);

        $response = $this->client->call($endpoint, $this->params, 'POST');

        return new CandidateIncomingmessagePostResponse($this, $response);
    }


}