<?php


namespace Concludis\ApiClient\Exception;


use Exception;
use Throwable;

class HttpException extends Exception {
    /**
     * @var string
     */
    public string $response_body;

    public function __construct($message = '', $code = 0, $response_body = '', Throwable $previous = null) {
        parent::__construct($message, $code, $previous);
        $this->response_body = (string)$response_body;
    }
}