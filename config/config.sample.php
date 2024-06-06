<?php

use Concludis\ApiClient\Config\Baseconfig;
use Concludis\ApiClient\Config\Source;

Baseconfig::$db_host = 'db-host';
Baseconfig::$db_user = 'db-user';
Baseconfig::$db_pass = 'db-pass';
Baseconfig::$db_name = 'db-name';
Baseconfig::$db_prefix = 'tbl_';

Baseconfig::addSource([
    'id' => 'a-unique-source-id',
    'api' => Source::API_VERSION_V2,
    'baseurl' => 'https://your-concludis-domain/api/2.0',
    'filters' => ['boards' => [1,2,3]],
    'username' => 'api-username',
    'password' => 'api-password'
]);

Baseconfig::init();