<?php
defined('MOODLE_INTERNAL') || die();

class sqljudge_exception extends moodle_exception {
    function __construct($errorcode, $a = NULL, $debuginfo = NULL) {
        parent::__construct($errorcode, 'local_sqljudge', '', $a, $debuginfo);
    }
}


