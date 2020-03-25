<?php



defined('MOODLE_INTERNAL') || die;

$functions = array(

    'mod_url_view_url' => array(
        'classname'     => 'mod_url_external',
        'methodname'    => 'view_url',
        'description'   => 'Trigger the course module viewed event and update the module completion status.',
        'type'          => 'write',
        'capabilities'  => 'mod/url:view',
        'services'      => array(MOODLE_OFFICIAL_MOBILE_SERVICE)
    ),

);
