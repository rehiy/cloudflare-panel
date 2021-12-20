<?php

$va_list = [];

include __DIR__ . '/config.php';

if (isset($va_list[$cf_email])) {
    $va_user = $va_list[$cf_email];

    if ($va_user['sub_key'] == md5($cf_api_key)) {
        $cf_email = $va_user['cf_email'];
        $cf_api_key = $va_user['cf_api_key'];
    }

    if ($va_user['sub_zone']) {
        if (isset($_GET['domain'])) {
            if (!key_exists($_GET['domain'], $va_user['sub_zone'])) {
                $_GET['domain'] = '123';
            }
        }
        if (isset($_GET['zoneid'])) {
            if (!in_array($_GET['zoneid'], $va_user['sub_zone'])) {
                $_GET['zoneid'] = '123';
            }
        }
        $_ENV['va.zonelist'] = array_keys($va_user['sub_zone']);
    }

    unset($va_user);
}

unset($va_list);
