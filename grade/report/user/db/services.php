<?php



$functions = array(

    'gradereport_user_get_grades_table' => array(
        'classname' => 'gradereport_user_external',
        'methodname' => 'get_grades_table',
        'classpath' => 'grade/report/user/externallib.php',
        'description' => 'Get the user/s report grades table for a course',
        'type' => 'read',
        'capabilities' => 'gradereport/user:view',
        'services' => array(MOODLE_OFFICIAL_MOBILE_SERVICE),
    ),
    'gradereport_user_view_grade_report' => array(
        'classname' => 'gradereport_user_external',
        'methodname' => 'view_grade_report',
        'classpath' => 'grade/report/user/externallib.php',
        'description' => 'Trigger the report view event',
        'type' => 'write',
        'capabilities' => 'gradereport/user:view',
        'services' => array(MOODLE_OFFICIAL_MOBILE_SERVICE),
    )
);
