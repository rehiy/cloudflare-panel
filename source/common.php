<?php

// error_reporting(E_ALL);
// ini_set('display_errors', 1);
// ini_set('display_startup_errors', 1);

define('APP_NAME', 'CloudFlare');
define('APP_VERSION', '1');

require_once dirname(__DIR__) . '/vendor/autoload.php';
require_once __DIR__ . '/translate.php';

$action = $_GET['action'] ?? 'list_zones';

if (empty($_COOKIE['cf_email']) || empty($_COOKIE['cf_api_key'])) {
    $action = $_GET['action'] = 'login';
}

$cf_email = $_COOKIE['cf_email'] ?? $_POST['cf_email'] ?? '';
$cf_api_key = $_COOKIE['cf_api_key'] ??  $_POST['cf_api_key'] ?? '';

if (is_file(__DIR__ . '/vaccount/config.php')) {
    require_once __DIR__ . '/vaccount/caller.php';
}

$key = new \Cloudflare\API\Auth\APIKey($cf_email, $cf_api_key);
$adapter = new Cloudflare\API\Adapter\Guzzle($key);
