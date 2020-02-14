<?php




defined('MOODLE_INTERNAL') || die();

$logs = array(
    array('module'=>'lesson', 'action'=>'start', 'mtable'=>'lesson', 'field'=>'name'),
    array('module'=>'lesson', 'action'=>'end', 'mtable'=>'lesson', 'field'=>'name'),
    array('module'=>'lesson', 'action'=>'view', 'mtable'=>'lesson_pages', 'field'=>'title'),
);