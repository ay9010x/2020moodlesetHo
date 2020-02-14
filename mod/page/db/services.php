<?php



defined('MOODLE_INTERNAL') || die;

$functions = array(

    'mod_page_view_page' => array(
        'classname'     => 'mod_page_external',
        'methodname'    => 'view_page',
        'description'   => 'Simulate the view.php web interface page: trigger events, completion, etc...',
        'type'          => 'write',
        'capabilities'  => 'mod/page:view',
        'services'      => array(MOODLE_OFFICIAL_MOBILE_SERVICE)
    ),

);
