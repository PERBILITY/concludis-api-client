<?php

require __DIR__ . '/../vendor/autoload.php';
require __DIR__ . '/../config/config.php';

use Concludis\ApiClient\Resources\Project;
use Concludis\ApiClient\Storage\ProjectRepository;

$pr = new ProjectRepository();
$data = $pr->fetchGroupInflated(1);

$data = array_map(function(Project $item) {
    return [
        'id' => $item->id,
        'group1' => $item->group1,
    ];
}, $data);
echo json_encode($data, JSON_PRETTY_PRINT);