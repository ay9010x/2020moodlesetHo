<?php


defined('MOODLE_INTERNAL') || die();



function xmldb_assignment_install() {
    global $DB;

        $DB->set_field('modules', 'visible', '0', array('name'=>'assignment')); 
    
    return true;
}


