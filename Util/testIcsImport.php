<?php
/**
 * Created by PhpStorm.
 * User: willemv
 * Date: 2017/09/22
 * Time: 16:47
 */

require_once __DIR__ . '/../vendor/autoload.php';

if (count($argv) != 6) {
    echo "Usage: php testIcsImport.php {SOAP Server URL} {Admin User} {Admin Password} {REST Server URL} {Account Email Address}";

    exit(1);
}

$serverUrl = $argv[1];
$adminUser = $argv[2];
$adminPass = $argv[3];
$restServerUrl = $argv[4];
$account = $argv[5];

$client = new \Synaq\CurlBundle\Curl\Wrapper();
$connector = new \Synaq\ZasaBundle\Connector\ZimbraConnector(
    $client,
    $serverUrl,
    $adminUser,
    $adminPass,
    false,
    null,
    $restServerUrl
);

$stream = file_get_contents('php://stdin');

$connector->importCalendar($account, $stream);

exit(0);