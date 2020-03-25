<?php



$functions = array(

    'mod_data_get_databases_by_courses' => array(
        'classname' => 'mod_data_external',
        'methodname' => 'get_databases_by_courses',
        'description' => 'Returns a list of database instances in a provided set of courses, if
            no courses are provided then all the database instances the user has access to will be returned.',
        'type' => 'read',
        'capabilities' => 'mod/data:viewentry',
        'services' => array(MOODLE_OFFICIAL_MOBILE_SERVICE)
    )
);
