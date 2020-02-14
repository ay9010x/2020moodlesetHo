<?php



defined('MOODLE_INTERNAL') || die();

global $DB;

$logs = array(
    array('module' => 'lightboxgallery', 'action' => 'update', 'mtable' => 'lightboxgallery', 'field' => 'name'),
    array('module' => 'lightboxgallery', 'action' => 'view', 'mtable' => 'lightboxgallery', 'field' => 'name'),
    array('module' => 'lightboxgallery', 'action' => 'comment', 'mtable' => 'lightboxgallery', 'field' => 'name')
);
