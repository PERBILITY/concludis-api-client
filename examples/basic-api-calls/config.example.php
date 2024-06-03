<?php

use Concludis\ApiClient\Config\Baseconfig;
use Concludis\ApiClient\Config\Source;

Baseconfig::addSource([
    'id' => 'v2.example.source',
    'baseurl' => 'https://dev.local.dev.concludis.de/api/2.0',
    'filters' => ['boards' => [1,2,3]], //optional
    'username' => 'api-username',
    'password' => 'api-password',
    'api' => Source::API_VERSION_V2,
    'ssl_verify_peer' => false
]);
