<?php



namespace core\antivirus;

defined('MOODLE_INTERNAL') || die();


class scanner_exception extends \moodle_exception {
    
    public function __construct($errorcode, $link = '', $a = null, $debuginfo = null) {
        parent::__construct($errorcode, 'antivirus', $link, $a, $debuginfo);
    }
}
