<?php

$translates = [];

function l($k)
{
    global $translates;
    return $translates[$k] ?? $k;
}

if (preg_match('/^zh/i', $_SERVER['HTTP_ACCEPT_LANGUAGE'])) {
    include __DIR__ . '/translate/zh-cn.php';
}

$ttl_translate = [
    1 => l('Automatic'),
    120 => l('2 mins'),
    300 => l('5 mins'),
    600 => l('10 mins'),
    900 => l('15 mins'),
    1800 => l('30 mins'),
    3600 => l('1 hour'),
    7200 => l('2 hours'),
    18000 => l('5 hours'),
    43200 => l('12 hours'),
    86400 => l('1 day'),
];

$status_translate = [
    'active' => '<span class="badge badge-success">' . l('Active') . '</span>',
    'pending' => '<span class="badge badge-warning">' . l('Pending') . '</span>',
    'initializing' => '<span class="badge badge-light">' . l('Initializing') . '</span>',
    'moved' => '<span class="badge badge-dark">' . l('Moved') . '</span>',
    'deleted' => '<span class="badge badge-danger">' . ('Deleted') . '</span>',
    'deactivated' => '<span class="badge badge-light">' . l('Deactivated') . '</span>',
];

$action_translate = [
    'logout' => l('Logout'),
    'security' => l('Security'),
    'add_record' => l('Add Record'),
    'edit_record' => l('Edit Record'),
    'delete_record' => l('Delete Record'),
    'zone' => l('Manage Zone'),
    'dnssec' => l('DNSSEC'),
    'login' => l('Login'),
];
