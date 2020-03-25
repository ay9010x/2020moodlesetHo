<?php



defined('MOODLE_INTERNAL') || die;

$functions = array(

    'mod_folder_view_folder' => array(
        'classname'     => 'mod_folder_external',
        'methodname'    => 'view_folder',
        'description'   => 'Simulate the view.php web interface folder: trigger events, completion, etc...',
        'type'          => 'write',
        'capabilities'  => 'mod/folder:view',
        'services'      => array(MOODLE_OFFICIAL_MOBILE_SERVICE)
    ),

);
