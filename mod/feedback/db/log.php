<?php



defined('MOODLE_INTERNAL') || die();

$logs = array(
    array('module'=>'feedback', 'action'=>'startcomplete', 'mtable'=>'feedback', 'field'=>'name'),
    array('module'=>'feedback', 'action'=>'submit', 'mtable'=>'feedback', 'field'=>'name'),
    array('module'=>'feedback', 'action'=>'delete', 'mtable'=>'feedback', 'field'=>'name'),
    array('module'=>'feedback', 'action'=>'view', 'mtable'=>'feedback', 'field'=>'name'),
    array('module'=>'feedback', 'action'=>'view all', 'mtable'=>'course', 'field'=>'shortname'),
);