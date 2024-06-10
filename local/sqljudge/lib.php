<?php

defined('MOODLE_INTERNAL') || die();

/**
 * add the sqljudge plugin into navigation
 */
function sqljudge_extends_navigation(global_navigation $navigation) {
    $sqljudge = $navigation->add(
        get_string('pluginname', 'local_sqljudge'), 
        new moodle_url('/local/sqljudge/'));
}