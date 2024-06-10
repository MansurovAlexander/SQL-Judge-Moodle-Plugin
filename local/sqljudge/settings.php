<?php
defined('MOODLE_INTERNAL') || die;

if ($hassiteconfig) {
    $settings = new admin_settingpage('local_sqljudge', get_string('pluginname', 'local_sqljudge'));

    $settings->add(new admin_setting_configtext('local_sqljudge/backendaddress',
        get_string('backendaddress', 'local_sqljudge'),
        get_string('backendaddress_help', 'local_sqljudge'),
        '127.0.0.1:8080',
        PARAM_TEXT));

    $ADMIN->add('localplugins', $settings);
}
