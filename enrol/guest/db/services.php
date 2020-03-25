<?php



$functions = array(

    'enrol_guest_get_instance_info' => array(
        'classname'   => 'enrol_guest_external',
        'methodname'  => 'get_instance_info',
        'description' => 'Return guest enrolment instance information.',
        'type'        => 'read',
        'services'    => array(MOODLE_OFFICIAL_MOBILE_SERVICE),
    ),
);
