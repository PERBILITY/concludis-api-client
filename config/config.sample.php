<?php

use Concludis\ApiClient\Config\Baseconfig;

Baseconfig::$db_host = 'db-host';
Baseconfig::$db_user = 'db-user';
Baseconfig::$db_pass = 'db-pass';
Baseconfig::$db_name = 'db-name';
Baseconfig::$db_prefix = 'tbl_';

Baseconfig::addSource([
    'id' => 'a-unique-source-id',
    'baseurl' => 'https://your-concludis-domain/api/1.0',
    'filters' => ['boards' => [1,2,3]],
    'username' => 'api-username',
    'password' => 'api-password'
]);

Baseconfig::init();