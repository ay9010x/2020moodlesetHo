<?php


defined('MOODLE_INTERNAL') || die();



function xmldb_lti_install() {
    global $DB;

        $DB->set_field('modules', 'visible', '0', array('name'=>'lti')); 
    
    return true;
}


