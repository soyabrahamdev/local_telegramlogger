<?php
defined('MOODLE_INTERNAL') || die();

if ($hassiteconfig) {
    $settings = new admin_settingpage('local_telegramlogger', get_string('pluginname', 'local_telegramlogger'));
    $ADMIN->add('localplugins', $settings);

    $settings->add(new admin_setting_configcheckbox(
        'local_telegramlogger/enabled',
        get_string('enabled', 'local_telegramlogger'), '', 1
    ));

    $settings->add(new admin_setting_configtext(
        'local_telegramlogger/bottoken',
        get_string('bottoken', 'local_telegramlogger'),
        get_string('bottoken_desc', 'local_telegramlogger'), '', PARAM_RAW
    ));

    $settings->add(new admin_setting_configtext(
        'local_telegramlogger/chatid',
        get_string('chatid', 'local_telegramlogger'),
        get_string('chatid_desc', 'local_telegramlogger'), '', PARAM_RAW
    ));
}