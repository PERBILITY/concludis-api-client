<?php
/**
 * Fetch projects from local database in "locationInflated"-style.
 * This means that we'll get one result for each assinged location.
 * If there is only one project with 3 assigned locations, we will
 * receive 3 project objects, each of them will hold the same jobad
 * content but one different location.
 *
 * In contrast to a normal fetch which will produce in a single
 * project object with 3 locations inside.
 */

require __DIR__ . '/../vendor/autoload.php';
require __DIR__ . '/../config/config.php';


use Concludis\ApiClient\Storage\ProjectRepository;

$repos = ProjectRepository::factory();

$count = 0;

$data = $repos
//    ->addFilter(ProjectRepository::FILTER_TYPE_GLOBAL_CLASSIFICATION, [1])
//    ->radius('DE', '51147', 'Köln', 8)
//    ->addFilter(ProjectRepository::FILTER_TYPE_CLASSIFICATION, [10])
//    ->paginate(10, 0)
    ->fetchLocationInflated($count)
;


//
//
echo PHP_EOL;
echo count($data) . PHP_EOL;
var_dump($data);
echo PHP_EOL;
//
//foreach($data as $d) {
//    echo PHP_EOL;
//    echo $d->id . PHP_EOL;
//    echo $d->position_title . PHP_EOL;
//    echo count($d->locations);
//    var_export($d->locations);
//    echo PHP_EOL;
//}
//var_dump($data);
//echo PHP_EOL;
//echo PHP_EOL;



//var_dump($data5);