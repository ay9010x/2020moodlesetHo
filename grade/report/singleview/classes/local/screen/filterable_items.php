<?php



namespace gradereport_singleview\local\screen;

defined('MOODLE_INTERNAL') || die;


interface filterable_items {

    
    public static function filter($item);
}
