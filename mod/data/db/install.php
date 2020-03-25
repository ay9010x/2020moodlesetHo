<?php


defined('MOODLE_INTERNAL') || die();



function xmldb_data_install() {
    global $DB;

        $DB->set_field('modules', 'visible', '0', array('name'=>'data')); 
    
    return true;
}


