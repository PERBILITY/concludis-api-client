<?php


namespace Concludis\ApiClient\Exception;


use Exception;
use Throwable;

class ApiRuntimeException extends Exception {

    public array $suberrors;

    public function __construct($message = '', $code = 0, array $suberrors = [], Throwable $previous = null) {
        parent::__construct($message, $code, $previous);
        $this->suberrors = $suberrors;
    }


    public function getInnerMessage(): string {
        $msg = '';
        foreach($this->suberrors as $suberror) {
            $msg .= trim($suberror['msg']) . PHP_EOL;
        }
        if(empty($msg)) {
            $msg = $this->getMessage();
        }
        return $msg;
    }
}