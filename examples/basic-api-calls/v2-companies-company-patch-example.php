<?php

require __DIR__ . '/../../vendor/autoload.php';
require __DIR__ . '/config.example.php';

use Concludis\ApiClient\V2\Api;
use Concludis\ApiClient\V2\Endpoints\CompaniesCompanyPatchEndpoint;

/**
 * Updates the value of field "website_url" of the company with the external_id "concludis".
 * Returns the ID of the company patched.
 */

$api = new Api('v2.example.source');

$response = $api
    ->CompaniesCompanyPatchEndpoint()
    ->addParam(CompaniesCompanyPatchEndpoint::PARAM_KEY_IDENTIFIER, 'external_id:concludis')
    ->addParam(CompaniesCompanyPatchEndpoint::PARAM_KEY_WEBSITE_URL, 'https://www.concludis.com')
    ->call();

if($response->success()) {

    echo sprintf('The company with id %d has been successfully patched.', $response->id);

} else {
    echo 'Uups, an error occurred. Here is some debugging info: ' . PHP_EOL;
    echo print_r($response->error(), true);
}
