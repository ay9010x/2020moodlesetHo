<?php




defined('MOODLE_INTERNAL') || die();

$logs = array(
    array('module'=>'glossary', 'action'=>'add', 'mtable'=>'glossary', 'field'=>'name'),
    array('module'=>'glossary', 'action'=>'update', 'mtable'=>'glossary', 'field'=>'name'),
    array('module'=>'glossary', 'action'=>'view', 'mtable'=>'glossary', 'field'=>'name'),
    array('module'=>'glossary', 'action'=>'view all', 'mtable'=>'glossary', 'field'=>'name'),
    array('module'=>'glossary', 'action'=>'add entry', 'mtable'=>'glossary', 'field'=>'name'),
    array('module'=>'glossary', 'action'=>'update entry', 'mtable'=>'glossary', 'field'=>'name'),
    array('module'=>'glossary', 'action'=>'add category', 'mtable'=>'glossary', 'field'=>'name'),
    array('module'=>'glossary', 'action'=>'update category', 'mtable'=>'glossary', 'field'=>'name'),
    array('module'=>'glossary', 'action'=>'delete category', 'mtable'=>'glossary', 'field'=>'name'),
    array('module'=>'glossary', 'action'=>'approve entry', 'mtable'=>'glossary', 'field'=>'name'),
    array('module'=>'glossary', 'action'=>'disapprove entry', 'mtable'=>'glossary', 'field'=>'name'),
    array('module'=>'glossary', 'action'=>'view entry', 'mtable'=>'glossary_entries', 'field'=>'concept'),
);