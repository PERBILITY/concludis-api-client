<?php
require __DIR__ . '/../vendor/autoload.php';
require __DIR__ . '/../config/config.php';

use Concludis\ApiClient\Common\ProjectSaveHandler;
use Concludis\ApiClient\Config\Baseconfig;

$source = Baseconfig::getSourceById('tm.dev.api2');


$saveHandler = new ProjectSaveHandler();
$source->client()->pullProject($source, 76433, $saveHandler, true);