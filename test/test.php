<?php

require __DIR__ . '/../vendor/autoload.php';
require __DIR__ . '/../config/config.php';

use Concludis\ApiClient\Service\ApiService;
//use Concludis\ApiClient\Storage\ProjectRepository;
//use Concludis\ApiClient\V1\Endpoints\ProjectEndpoint;

error_reporting(E_ALL);
ini_set('display_errors',1);

ApiService::pullProjects(true);

//$url = 'https://me.concludis.de/api/1.0/DE/project';
//
//$result = Client::call($url, null);
//
//var_dump($result);


//$data = ProjectEndpoint::instance($client)
////    ->addFilter(ProjectEndpoint::FILTER_TYPE_BOARD, [1,2,3,4] )
////    ->addFilter(ProjectEndpoint::FILTER_TYPE_COMPANY, [36])
////    ->byId(71);
//    ->listFull();
//
//if($data !== null) {
//    foreach($data as $d) {
//        ProjectRepository::save($d);
//    }
//}

//var_export($data);