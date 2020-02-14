<?php


define('NO_MOODLE_COOKIES', true); require_once '../../../config.php';

$id = required_param('id', PARAM_INT); if (!$course = $DB->get_record('course', array('id'=>$id))) {
    print_error('nocourseid');
}

require_user_key_login('grade/import', $id); 
if (empty($CFG->gradepublishing)) {
    print_error('gradepubdisable');
}

$context = context_course::instance($id);
require_capability('gradeimport/xml:publish', $context);

require 'import.php';


