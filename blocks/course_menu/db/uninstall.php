<?php


defined('MOODLE_INTERNAL') || die();

function xmldb_block_course_menu_uninstall($oldversion=0) {
    global $CFG, $DB;  

    $result = $DB->delete_records('block_instances', array('blockname'=>'course_menu'));
    
        $undeletableblocktypes = explode(',', $CFG->undeletableblocktypes);
    foreach($undeletableblocktypes as $key=>$val){
        if ($val == 'course_menu') {
            unset($undeletableblocktypes[$key]);
            set_config('undeletableblocktypes', implode(',', $undeletableblocktypes));
        }
    }
   
   return $result;   
}