<?php


defined('MOODLE_INTERNAL') || die();



function xmldb_workshop_install() {
    global $DB;

        $DB->set_field('modules', 'visible', '0', array('name'=>'workshop')); 
    
    return true;
}


