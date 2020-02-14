<?php



defined('MOODLE_INTERNAL') || die();

$logs = array(
    array('module' => 'book', 'action' => 'add', 'mtable' => 'book', 'field' => 'name'),
    array('module' => 'book', 'action' => 'update', 'mtable' => 'book', 'field' => 'name'),
    array('module' => 'book', 'action' => 'view', 'mtable' => 'book', 'field' => 'name'),
    array('module' => 'book', 'action' => 'add chapter', 'mtable' => 'book_chapters', 'field' => 'title'),
    array('module' => 'book', 'action' => 'update chapter', 'mtable'=> 'book_chapters', 'field' => 'title'),
    array('module' => 'book', 'action' => 'view chapter', 'mtable' => 'book_chapters', 'field' => 'title')
);
