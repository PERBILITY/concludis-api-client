<?php

require __DIR__ . '/../../vendor/autoload.php';
require __DIR__ . '/../../config/config.php';

use Concludis\ApiClient\Config\Baseconfig;

$ats_source_id = 'v2.example.source';
$project_id = 3;
$is_internal = false;

try {
    $source = Baseconfig::getSourceById($ats_source_id);
    $response = $source->client()->fetchApplicationSetup($project_id, $is_internal);

    if($response->success()) {
        echo json_encode($response->setup, JSON_THROW_ON_ERROR | JSON_PRETTY_PRINT);
    } else {
        echo json_encode($response->error(), JSON_THROW_ON_ERROR | JSON_PRETTY_PRINT);
    }

} catch (Exception $e) {
    echo "Error: " . $e->getMessage();
}

