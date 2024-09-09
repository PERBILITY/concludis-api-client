<?php

namespace Concludis\ApiClient\V2\Endpoints;

use Concludis\ApiClient\Common\AbstractEndpoint;
use Concludis\ApiClient\Common\TraitParamsEndpoint;
use Concludis\ApiClient\V2\Responses\CandidateMessagePatchResponse;

class CandidateMessagePatchEndpoint extends AbstractEndpoint
{

    use TraitParamsEndpoint;
    public const PARAM_KEY_CANDIDATE_ID = 'candidate_id';
    public const PARAM_KEY_MESSAGE_ID = 'message_id';
    public const PARAM_KEY_ACTION = 'action';

    public function paramsDefinition(): array {
        return [
            self::PARAM_KEY_CANDIDATE_ID => ['cast' => 'int'],
            self::PARAM_KEY_MESSAGE_ID => ['cast' => 'int'],
            self::PARAM_KEY_ACTION => ['cast' => 'string']
        ];
    }

    /**
     * @return CandidateMessagePatchResponse
     */
    public function call(): CandidateMessagePatchResponse {

        $url_params = [
            '{locale}' => $this->locale
        ];

        $endpoint = '/{locale}/candidate/message';

        $endpoint = strtr($endpoint, $url_params);

        $response = $this->client->call($endpoint, $this->params, 'PATCH');

        return new CandidateMessagePatchResponse($this, $response);
    }

}