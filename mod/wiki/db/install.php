<?php


defined('MOODLE_INTERNAL') || die();



function xmldb_wiki_install() {
    global $DB;

        $DB->set_field('modules', 'visible', '0', array('name'=>'wiki')); 
    
    return true;
}


