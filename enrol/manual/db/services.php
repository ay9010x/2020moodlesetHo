<?php



$functions = array(

        'enrol_manual_enrol_users' => array(
        'classname'   => 'enrol_manual_external',
        'methodname'  => 'enrol_users',
        'classpath'   => 'enrol/manual/externallib.php',
        'description' => 'Manual enrol users',
        'capabilities'=> 'enrol/manual:enrol',
        'type'        => 'write',
    ),

    'enrol_manual_unenrol_users' => array(
        'classname'   => 'enrol_manual_external',
        'methodname'  => 'unenrol_users',
        'classpath'   => 'enrol/manual/externallib.php',
        'description' => 'Manual unenrol users',
        'capabilities'=> 'enrol/manual:unenrol',
        'type'        => 'write',
    ),

);
