<?php



namespace core\message\inbound;

defined('MOODLE_INTERNAL') || die();


class processing_failed_exception extends \moodle_exception {
    
    public function __construct($identifier, $component, \stdClass $data = null) {
        return parent::__construct($identifier, $component, '', $data);
    }
}
