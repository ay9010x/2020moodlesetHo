<?php


defined('MOODLE_INTERNAL') || die();



function xmldb_lesson_install() {
    global $DB;

        $DB->set_field('modules', 'visible', '0', array('name'=>'lesson')); 
    
    return true;
}


