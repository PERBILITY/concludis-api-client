<?php

use Concludis\ApiClient\Config\Baseconfig;

Baseconfig::$db_host = 'localhost';
Baseconfig::$db_user = 'dbuser';
Baseconfig::$db_pass = 'dbpass';
Baseconfig::$db_name = 'dbname';
Baseconfig::$db_prefix = 'someprefix_concludis_';

Baseconfig::addSource([
    'id' => 'instance_id',
    'baseurl' => 'base_url',
    'username' => 'username',
    'password' => 'userpass',
    'api' => (new CustomApiClient())
]);

Baseconfig::init();
