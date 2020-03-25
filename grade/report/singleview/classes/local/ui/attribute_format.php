<?php



namespace gradereport_singleview\local\ui;

defined('MOODLE_INTERNAL') || die;


abstract class attribute_format {

    
    abstract public function determine_format();

    
    public function __toString() {
        return $this->determine_format()->html();
    }
}

