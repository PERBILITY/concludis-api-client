<?php

require __DIR__ . '/../../vendor/autoload.php';
require __DIR__ . '/../../config/config.php';

use Concludis\ApiClient\Config\Baseconfig;

$ats_source_id = 'v2.example.source';
$project_id = 3;
$location_ids = [4];

try {
    $source = Baseconfig::getSourceById($ats_source_id);
    $response = $source->client()->fetchDataPrivacyStatement($project_id, $location_ids);

    if($response->success()) {
        echo "Company IDs of companies which may access your application if you apply: \n";
        echo json_encode($response->dp_companies, JSON_THROW_ON_ERROR | JSON_PRETTY_PRINT);
        echo "\n\n";

        echo "The dynamic generated dataprivacy statement depending on your apply-locations: \n";
        echo json_encode($response->dataprivacy_statement, JSON_THROW_ON_ERROR | JSON_PRETTY_PRINT);
        echo "\n\n";
    } else {
        echo json_encode($response->error(), JSON_THROW_ON_ERROR | JSON_PRETTY_PRINT);
    }

} catch (Exception $e) {
    echo "Error: " . $e->getMessage();
}

