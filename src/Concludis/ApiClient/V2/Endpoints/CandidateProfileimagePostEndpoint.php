<?php

namespace Concludis\ApiClient\V2\Endpoints;

use Concludis\ApiClient\Common\AbstractEndpoint;
use Concludis\ApiClient\Common\TraitParamsEndpoint;
use Concludis\ApiClient\V2\Responses\CandidateProfileimagePostResponse;

class CandidateProfileimagePostEndpoint extends AbstractEndpoint
{

    use TraitParamsEndpoint;
    public const PARAM_KEY_CANDIDATE_ID = 'candidate_id';
    public const PARAM_KEY_FILE_MIME = 'file_mime';
    public const PARAM_KEY_FILE_CONTENT = 'file_content';

    public function paramsDefinition(): array {
        return [
            self::PARAM_KEY_CANDIDATE_ID => ['cast' => 'int'],
            self::PARAM_KEY_FILE_MIME => ['cast' => 'string'],
            self::PARAM_KEY_FILE_CONTENT => ['cast' => 'string'],
        ];
    }

    /**
     * @return CandidateProfileimagePostResponse
     */
    public function call(): CandidateProfileimagePostResponse {

        $url_params = [
            '{locale}' => $this->locale
        ];

        $endpoint = '/{locale}/candidate/profileimage';

        $endpoint = strtr($endpoint, $url_params);

        $response = $this->client->call($endpoint, $this->params, 'POST');

        return new CandidateProfileimagePostResponse($this, $response);
    }


}