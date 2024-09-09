<?php

namespace Concludis\ApiClient\V2\Endpoints;

use Concludis\ApiClient\Common\AbstractEndpoint;
use Concludis\ApiClient\Common\TraitParamsEndpoint;
use Concludis\ApiClient\V2\Responses\CandidateFileGetResponse;

class CandidateFileGetEndpoint extends AbstractEndpoint
{

    use TraitParamsEndpoint;
    public const PARAM_KEY_CANDIDATE_ID = 'candidate_id';
    public const PARAM_KEY_FILE_ID = 'file_id';

    public function paramsDefinition(): array {
        return [
            self::PARAM_KEY_CANDIDATE_ID => ['cast' => 'int'],
            self::PARAM_KEY_FILE_ID => ['cast' => 'int'],
        ];
    }

    /**
     * @return CandidateFileGetResponse
     */
    public function call(): CandidateFileGetResponse {

        $url_params = [
            '{locale}' => $this->locale
        ];

        $endpoint = '/{locale}/candidate/file';

        $endpoint = strtr($endpoint, $url_params);

        $response = $this->client->call($endpoint, $this->params, 'GET');

        return new CandidateFileGetResponse($this, $response);
    }


}