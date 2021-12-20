<?php

// error_reporting(E_ALL);
// ini_set('display_errors', 1);
// ini_set('display_startup_errors', 1);

define('APP_NAME', 'CloudFlare');
define('APP_VERSION', '1');

require_once dirname(__DIR__) . '/vendor/autoload.php';
require_once __DIR__ . '/translate.php';

$action = $_GET['action'] ?? 'list_zones';

if (empty($_COOKIE['cloudflare_email']) || empty($_COOKIE['cloudflare_key'])) {
    $action = $_GET['action'] = 'login';
} else {
    $key = new \Cloudflare\API\Auth\APIKey($_COOKIE['cloudflare_email'], $_COOKIE['cloudflare_key']);
    $adapter = new Cloudflare\API\Adapter\Guzzle($key);
}
