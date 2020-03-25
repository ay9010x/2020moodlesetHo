<?php



namespace gradereport_singleview\local\screen;

defined('MOODLE_INTERNAL') || die;


interface selectable_items {
    
    public function description();

    
    public function select_label();

    
    public function options();

    
    public function item_type();
}
