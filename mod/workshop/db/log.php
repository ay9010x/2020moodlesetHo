<?php




defined('MOODLE_INTERNAL') || die();

$logs = array(
        array('module'=>'workshop', 'action'=>'add', 'mtable'=>'workshop', 'field'=>'name'),
    array('module'=>'workshop', 'action'=>'update', 'mtable'=>'workshop', 'field'=>'name'),
    array('module'=>'workshop', 'action'=>'view', 'mtable'=>'workshop', 'field'=>'name'),
    array('module'=>'workshop', 'action'=>'view all', 'mtable'=>'workshop', 'field'=>'name'),
        array('module'=>'workshop', 'action'=>'add submission', 'mtable'=>'workshop_submissions', 'field'=>'title'),
    array('module'=>'workshop', 'action'=>'update submission', 'mtable'=>'workshop_submissions', 'field'=>'title'),
    array('module'=>'workshop', 'action'=>'view submission', 'mtable'=>'workshop_submissions', 'field'=>'title'),
        array('module'=>'workshop', 'action'=>'add assessment', 'mtable'=>'workshop_submissions', 'field'=>'title'),
    array('module'=>'workshop', 'action'=>'update assessment', 'mtable'=>'workshop_submissions', 'field'=>'title'),
        array('module'=>'workshop', 'action'=>'add example', 'mtable'=>'workshop_submissions', 'field'=>'title'),
    array('module'=>'workshop', 'action'=>'update example', 'mtable'=>'workshop_submissions', 'field'=>'title'),
    array('module'=>'workshop', 'action'=>'view example', 'mtable'=>'workshop_submissions', 'field'=>'title'),
        array('module'=>'workshop', 'action'=>'add reference assessment', 'mtable'=>'workshop_submissions', 'field'=>'title'),
    array('module'=>'workshop', 'action'=>'update reference assessment', 'mtable'=>'workshop_submissions', 'field'=>'title'),
    array('module'=>'workshop', 'action'=>'add example assessment', 'mtable'=>'workshop_submissions', 'field'=>'title'),
    array('module'=>'workshop', 'action'=>'update example assessment', 'mtable'=>'workshop_submissions', 'field'=>'title'),
        array('module'=>'workshop', 'action'=>'update aggregate grades', 'mtable'=>'workshop', 'field'=>'name'),
    array('module'=>'workshop', 'action'=>'update clear aggregated grades', 'mtable'=>'workshop', 'field'=>'name'),
    array('module'=>'workshop', 'action'=>'update clear assessments', 'mtable'=>'workshop', 'field'=>'name'),
);
