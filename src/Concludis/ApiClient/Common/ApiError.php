<?php


namespace Concludis\ApiClient\Common;

use Exception;

class ApiError {

    public const CODE_CURL_ERROR = 1;
    public const CODE_HTTP_ERROR = 2;
    public const CODE_JSON_ERROR = 3;
    public const CODE_API_RUNTIME_ERROR = 4;

    /**
     * @var int
     */
    public int $code;

    /**
     * @var string
     */
    public string $msg;

    /**
     * @var Exception
     */
    public Exception $exception;

    public function __construct(int $code, string $msg, Exception $exception) {
        $this->code = $code;
        $this->msg = $msg;
        $this->exception = $exception;
    }

}