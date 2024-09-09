<?php

namespace Concludis\ApiClient\V2\Endpoints;

use Concludis\ApiClient\Common\AbstractEndpoint;
use Concludis\ApiClient\Common\TraitParamsEndpoint;
use Concludis\ApiClient\V2\Responses\CandidateFilePostResponse;

class CandidateFilePostEndpoint extends AbstractEndpoint
{

    use TraitParamsEndpoint;
    public const PARAM_KEY_CANDIDATE_ID = 'candidate_id';
    public const PARAM_KEY_FILE_NAME = 'file_name';
    public const PARAM_KEY_FILE_TYPE = 'file_type';
    public const PARAM_KEY_FILE_CONTENT = 'file_content';
    public const PARAM_KEY_FILE_META = 'file_meta';

    public function paramsDefinition(): array {
        return [
            self::PARAM_KEY_CANDIDATE_ID => ['cast' => 'int'],
            self::PARAM_KEY_FILE_NAME => ['cast' => 'string'],
            self::PARAM_KEY_FILE_TYPE => ['cast' => 'int'],
            self::PARAM_KEY_FILE_CONTENT => ['cast' => 'string'],
            self::PARAM_KEY_FILE_META => ['cast' => 'array'],
        ];
    }

    /**
     * @return CandidateFilePostResponse
     */
    public function call(): CandidateFilePostResponse {

        $url_params = [
            '{locale}' => $this->locale
        ];

        $endpoint = '/{locale}/candidate/file';

        $endpoint = strtr($endpoint, $url_params);

        $response = $this->client->call($endpoint, $this->params, 'POST');

        return new CandidateFilePostResponse($this, $response);
    }

}