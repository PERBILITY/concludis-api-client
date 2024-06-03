<?php

require __DIR__ . '/../../vendor/autoload.php';
require __DIR__ . '/config.example.php';

use Concludis\ApiClient\V2\Api;
use Concludis\ApiClient\V2\Endpoints\CompaniesCompanyGetEndpoint;

/**
 * Fetches the company with the external_id "concludis".
 * In addition to the master data, the extended properties "dataprivacy_statament"
 * and "assigned_locations" will also be loaded
 */

$api = new Api('v2.example.source');

$response = $api
    ->CompaniesCompanyGetEndpoint()
    ->addParam(CompaniesCompanyGetEndpoint::PARAM_KEY_IDENTIFIER, 'external_id:concludis')
    ->addParam(CompaniesCompanyGetEndpoint::PARAM_KEY_SHOW, 'dataprivacy_statement:assigned_locations')
    ->call();

if ($response->success()) {

    echo 'Yes, the company has been succesfully fetched from API.' . PHP_EOL .
        'Here is the json representation of fetched company object: ' . PHP_EOL .
    json_encode($response->company, JSON_PRETTY_PRINT);

} else {
    echo 'Uups, an error occurred. Here is some debugging info: ' . PHP_EOL;
    echo print_r($response->error(), true);
}