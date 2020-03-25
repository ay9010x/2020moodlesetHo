<?php



defined('MOODLE_INTERNAL') || die();


function xmldb_imscp_install() {
    global $CFG, $DB;

        $DB->set_field('modules', 'visible', '0', array('name'=>'imscp')); 
}
