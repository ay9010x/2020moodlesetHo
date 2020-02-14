<?php



namespace gradereport_singleview\local\ui;

defined('MOODLE_INTERNAL') || die;


abstract class grade_attribute_format extends attribute_format implements unique_name {

    
    public $name;

    
    public $label;

    
    public $grade;

    
    public function __construct($grade = 0) {
        $this->grade = $grade;
    }

    
    public function get_name() {
        return "{$this->name}_{$this->grade->itemid}_{$this->grade->userid}";
    }

    
    public abstract function set($value);
}
