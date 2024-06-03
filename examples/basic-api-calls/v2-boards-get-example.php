<?php

require __DIR__ . '/../../vendor/autoload.php';
//require __DIR__ . '/config.example.php';
require __DIR__ . '/../../config/config.php';

use Concludis\ApiClient\V2\Api;


$api = new Api('tm.dev.api2');

$response = $api
    ->BoardsGetEndpoint()
    ->paginate(1,30)
    ->call();

if($response->success()) {
    var_export($response->boards);
} else {
    var_export($response->error());
}