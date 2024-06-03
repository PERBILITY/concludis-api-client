<?php

namespace Concludis\ApiClient\V2\Responses;

use Concludis\ApiClient\Common\ApiResponse;
use Concludis\ApiClient\Common\EndpointResponse;
use Concludis\ApiClient\V2\Endpoints\ApplicationApplyPostEndpoint;

class ApplicationApplyPostResponse extends EndpointResponse {


    public array $messages = [];

    public array $qa_validation_errors = [];

    public ?int $candidate_id = null;

    public ?int $application_id = null;

    public ?int $project_id = null;

    public ?int $campaign_transaction_id = null;



    /**
     * @param ApplicationApplyPostEndpoint $endpoint
     * @param ApiResponse $response
     */
    public function __construct(ApplicationApplyPostEndpoint $endpoint, ApiResponse $response) {
        parent::__construct($endpoint, $response);

        if($response->success) {

            if(isset($response->data['qa_validation_errors'])){
                $this->qa_validation_errors = (array)$response->data['qa_validation_errors'];
            }

            if(isset($response->data['messages'])){
                $this->messages = (array)$response->data['messages'];
            }

            if(isset($response->data['candidate_id']) && (int)$response->data['candidate_id'] > 0){
                $this->candidate_id = (int)$response->data['candidate_id'];
            }

            if(isset($response->data['application_id']) && (int)$response->data['application_id'] > 0){
                $this->application_id = (int)$response->data['application_id'];
            }

            if(isset($response->data['project_id']) && (int)$response->data['project_id'] > 0){
                $this->project_id = (int)$response->data['project_id'];
            }

            if(isset($response->data['campaign_transaction_id']) && (int)$response->data['campaign_transaction_id'] > 0){
                $this->campaign_transaction_id = (int)$response->data['campaign_transaction_id'];
            }
        }

    }
}