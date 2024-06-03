<?php

require __DIR__ . '/../../vendor/autoload.php';
require __DIR__ . '/config.example.php';

use Concludis\ApiClient\V2\Api;
use Concludis\ApiClient\V2\Endpoints\CompaniesCompanyPostEndpoint;

/**
 * Creates a new company and returns the assigned ID.
 */

$api = new Api('v2.example.source');

$response = $api
    ->CompaniesCompanyPostEndpoint()
    ->addParam(CompaniesCompanyPostEndpoint::PARAM_KEY_NAME, 'Concludis GmbH')
    ->addParam(CompaniesCompanyPostEndpoint::PARAM_KEY_ADDRESS, 'Frankfurter Str. 561')
    ->addParam(CompaniesCompanyPostEndpoint::PARAM_KEY_CITY, 'Köln')
    ->addParam(CompaniesCompanyPostEndpoint::PARAM_KEY_POSTAL_CODE, '51145')
    ->addParam(CompaniesCompanyPostEndpoint::PARAM_KEY_COUNTRY_CODE, 'DE')
    ->addParam(CompaniesCompanyPostEndpoint::PARAM_KEY_EXTERNAL_ID, 'concludis')
    ->addParam(CompaniesCompanyPostEndpoint::PARAM_KEY_PHONE_NUMBER, '+49 2203 898560')
    ->addParam(CompaniesCompanyPostEndpoint::PARAM_KEY_INDUSTRY_ID, 68)
    ->call();

if($response->success()) {

    echo sprintf('The new company has been successfully created and got the ID %d assigned.', $response->id);

} else {
    echo 'Uups, an error occurred. Here is some debugging info: ' . PHP_EOL;
    echo print_r($response->error(), true);
}
