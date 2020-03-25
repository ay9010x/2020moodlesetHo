<?php



defined('MOODLE_INTERNAL') || die;

$functions = array(

    'mod_resource_view_resource' => array(
        'classname'     => 'mod_resource_external',
        'methodname'    => 'view_resource',
        'description'   => 'Simulate the view.php web interface resource: trigger events, completion, etc...',
        'type'          => 'write',
        'capabilities'  => 'mod/resource:view',
        'services'      => array(MOODLE_OFFICIAL_MOBILE_SERVICE)
    ),

);
