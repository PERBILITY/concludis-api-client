<?php
require __DIR__ . '/../vendor/autoload.php';
require __DIR__ . '/../config/config.php';

use Concludis\ApiClient\Service\ApiService;

error_reporting(E_ALL);
ini_set('display_errors',1);

ApiService::pullBoards(true);