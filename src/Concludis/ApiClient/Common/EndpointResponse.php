<?php


namespace Concludis\ApiClient\Common;


class EndpointResponse {

    /**
     * @var ApiResponse
     */
    protected ApiResponse $response;

    /**
     * @var AbstractEndpoint
     */
    protected AbstractEndpoint $endpoint;

    public function __construct(AbstractEndpoint $endpoint, ApiResponse $response) {
        $this->response = $response;
        $this->endpoint = $endpoint;
    }

    public function response(): ApiResponse {
        return $this->response;
    }

    public function endpoint(): AbstractEndpoint {
        return $this->endpoint;
    }

    public function success(): bool {
        return $this->response->success;
    }

    public function error(): ?ApiError {
        return $this->response->error;
    }
}