<?php
/**
 * Fetch projects from local database
 */

require __DIR__ . '/../vendor/autoload.php';
require __DIR__ . '/../config/config.php';


use Concludis\ApiClient\Storage\ProjectRepository;

$repos = ProjectRepository::factory();

$data = $repos
//    ->addFilter(ProjectRepository::FILTER_TYPE_KEYWORD, '140')
//    ->addFilter(ProjectRepository::FILTER_TYPE_INT_PUB, ProjectRepository::INT_PUB_INTERNAL) // internal jobs only
    ->addFilter(ProjectRepository::FILTER_TYPE_INT_PUB, ProjectRepository::INT_PUB_PUBLIC) // public jobs only
//    ->radius('DE', '51145', 'Köln', 8)
//    ->addFilter(ProjectRepository::FILTER_TYPE_CLASSIFICATION, [10])
//    ->paginate(10, 0)
    ->addOrder('date_from_public',true)
    ->fetch()
;
echo '<pre>' . json_encode($data, JSON_PRETTY_PRINT) . '</pre>';
//
//$data2 = $repos->fetchSeniorityQuantity();
//
//$data3 = $repos->fetchGlobalSeniorityQuantity();
//
//$data4 = $repos->fetchScheduleQuantity();
//
//$data5 = $repos->fetchGlobalScheduleQuantity();
//
//$data6 = $repos->fetchClassificationQuantity();
//
//$data7 = $repos->fetchGlobalClassificationQuantity();
//
//
//$data8 = $repos->fetchCategoryQuantity();
//
//$data9 = $repos->fetchGlobalCategoryQuantity();
//
//$data10 = $repos->fetchGeoStateQuantity();
//
//
//
//
echo PHP_EOL;
echo count($data) . PHP_EOL;
echo PHP_EOL;

/*foreach($data as $d) {
    echo PHP_EOL;
    echo $d->id . PHP_EOL;
    echo $d->position_title . PHP_EOL;
    echo count($d->locations);
    var_export($d->locations);
    echo PHP_EOL;
}*/
//var_dump($data);
echo PHP_EOL;
echo PHP_EOL;



//var_dump($data5);