<?php



namespace gradereport_singleview\local\ui;

defined('MOODLE_INTERNAL') || die;


abstract class element {

    
    public $name;

    
    public $value;

    
    public $label;

    
    public function __construct($name, $value, $label) {
        $this->name = $name;
        $this->value = $value;
        $this->label = $label;
    }

    
    public function is_checkbox() {
        return false;
    }

    
    public function is_textbox() {
        return false;
    }

    
    public function is_dropdown() {
        return false;
    }

    
    abstract public function html();
}
