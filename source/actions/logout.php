<?php
/*
 * Logout action
 */

if (!isset($adapter)) {
    exit;
}

setcookie('cf_email', null, -1);
setcookie('cf_api_key', null, -1);

header('Location: ./');
