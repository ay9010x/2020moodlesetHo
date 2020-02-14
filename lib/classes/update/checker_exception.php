<?php


namespace core\update;

defined('MOODLE_INTERNAL') || die();


class checker_exception extends \moodle_exception {

    
    public function __construct($errorcode, $debuginfo=null) {
        parent::__construct($errorcode, 'core_plugin', '', null, print_r($debuginfo, true));
    }
}
