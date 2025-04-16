<?php
/**
 * Fetch projects from local database
 */

require __DIR__ . '/../vendor/autoload.php';
require __DIR__ . '/../config/config.php';


use Concludis\ApiClient\Storage\ProjectRepository;

$repos = ProjectRepository::factory();

$repos
//    ->addFilter(ProjectRepository::FILTER_TYPE_KEYWORD, '140')
//    ->addFilter(ProjectRepository::FILTER_TYPE_INT_PUB, ProjectRepository::INT_PUB_INTERNAL) // internal jobs only
    ->addFilter(ProjectRepository::FILTER_TYPE_INT_PUB, ProjectRepository::INT_PUB_PUBLIC) // public jobs only
//    ->radius('DE', '51145', 'Köln', 8)
//    ->addFilter(ProjectRepository::FILTER_TYPE_CLASSIFICATION, [10])
//    ->paginate(10, 0)
        ;

echo "\n\n Fetch local schedule quantities based on filter criteria: \n";
echo json_encode($repos->fetchScheduleQuantity(), JSON_PRETTY_PRINT);


echo "\n\n Fetch global schedule quantities based on filter criteria: \n";
echo json_encode($repos->fetchGlobalScheduleQuantity(), JSON_PRETTY_PRINT);