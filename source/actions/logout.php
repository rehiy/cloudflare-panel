<?php
/*
 * Logout action
 */

if (!isset($adapter)) {
    exit;
}

setcookie('cloudflare_email', null, -1);
setcookie('cloudflare_key', null, -1);

header('Location: ./');
