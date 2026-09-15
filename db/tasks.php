<?php
defined('MOODLE_INTERNAL') || die();

$tasks = [
    [
        'classname' => 'local_telegramlogger\task\send_logs',
        'blocking'  => 0,
        'minute'    => '*/5',
        'hour'      => '*',
        'day'       => '*',
        'dayofweek' => '*',
        'month'     => '*',
    ],
];