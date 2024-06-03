<?php
/**
 * concludis - uninstall.php.
 * @author: Alex Agaltsev
 * created on: 13.06.2019
 */

use Concludis\ApiClient\Service\InstallService;
use Concludis\ApiClient\Util\CliUtil;

require __DIR__ . '/../vendor/autoload.php';
require __DIR__ . '/../config/config.php';

error_reporting(E_ALL);
ini_set('display_errors',1);

try {
    InstallService::uninstall();
} catch (Exception $e) {
    CliUtil::output('Uninstall failed...') . "\n";
    CliUtil::output('-> ' . $e->getMessage());
}