<?php

namespace Concludis\ApiClient\V2\Responses;

use Concludis\ApiClient\Common\ApiResponse;
use Concludis\ApiClient\Common\EndpointResponse;
use Concludis\ApiClient\Resources\File;
use Concludis\ApiClient\V2\Endpoints\CandidateProfileimagePostEndpoint;

class CandidateProfileimagePostResponse extends EndpointResponse {

    public ?File $file = null;

    public function __construct(CandidateProfileimagePostEndpoint $endpoint, ApiResponse $response) {
        parent::__construct($endpoint, $response);

        if($response->success && ($response->data['success'] ?? false) && is_array($response->data['file'] ?? null)) {

            $file = $response->data['file'];

            $this->file = new File([
                'id' => (int)($file['id'] ?? 0),
                'candidate_id' => (int)$endpoint->getParam(CandidateProfileimagePostEndpoint::PARAM_KEY_CANDIDATE_ID),
                'name' => (string)($file['name'] ?? ''),
                'mime_type' => (int)($file['mime_type'] ?? 0),
                'local_file_type' => (int)($file['local_file_type'] ?? 0),
                'global_file_type' => (int)($file['global_file_type'] ?? 0),
                'size' => (int)($file['size'] ?? 0),
                'mktime' => (int)($file['mktime'] ?? 0),
                'checksum' => (string)($file['checksum'] ?? '')
            ]);
        }
    }

}