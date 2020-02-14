<?php


defined('MOODLE_INTERNAL') || die();



function xmldb_choice_install() {
    global $DB;

        $DB->set_field('modules', 'visible', '0', array('name'=>'choice')); 
    
    return true;
}


