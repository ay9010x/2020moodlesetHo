<?php



$functions = array(
    'enrol_self_get_instance_info' => array(
        'classname'   => 'enrol_self_external',
        'methodname'  => 'get_instance_info',
        'classpath'   => 'enrol/self/externallib.php',
        'description' => 'self enrolment instance information.',
        'type'        => 'read',
        'services'    => array(MOODLE_OFFICIAL_MOBILE_SERVICE),
    ),

    'enrol_self_enrol_user' => array(
        'classname'   => 'enrol_self_external',
        'methodname'  => 'enrol_user',
        'classpath'   => 'enrol/self/externallib.php',
        'description' => 'Self enrol the current user in the given course.',
        'type'        => 'write',
        'services'    => array(MOODLE_OFFICIAL_MOBILE_SERVICE),
    )
);
