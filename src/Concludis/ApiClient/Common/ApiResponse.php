<?php


namespace Concludis\ApiClient\Common;


use Concludis\ApiClient\Exception\ApiRuntimeException;
use Concludis\ApiClient\Exception\JsonException;

class ApiResponse {

    /**
     * @var bool
     */
    public bool $success;

    /**
     * @var array|null
     */
    public ?array $data;

    /**
     * @var ApiError|null
     */
    public ?ApiError $error;

    /**
     * Response constructor.
     * @param array|null $data
     * @param ApiError|null $error
     */
    public function __construct(?array $data, ?ApiError $error = null) {

        $this->success = $error === null;

        $this->data = $data;

        $this->error = $error;
    }

    public function toArray(): array {

        if($this->data !== null) {
            return $this->data;
        }

        if($this->error !== null) {

            if($this->error->exception instanceof ApiRuntimeException) {
                return [
                    'success' => false,
                    'errors' => $this->error->exception->suberrors
                ];
            }

            if($this->error->exception instanceof JsonException) {

                return [
                    'success' => false,
                    'errors' => [
                        [
                            'code' => $this->error->code,
                            'subcode' => $this->error->exception->getCode(),
                            'msg' => $this->error->msg . ': ' . $this->error->exception->getMessage()
                                . '; Response body begins with: ' . substr($this->error->exception->response_body, 0, 200)
                        ]
                    ]
                ];
            }

            return [
                'success' => false,
                'errors' => [
                    [
                        'code' => $this->error->code,
                        'subcode' => $this->error->exception->getCode(),
                        'msg' => $this->error->msg . ': ' . $this->error->exception->getMessage()
                    ]
                ]
            ];
        }

        return [
            'success' => $this->success
        ];
    }

}