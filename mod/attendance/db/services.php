<?php



    
    $functions = array(
        'mod_wsattendance_get_courses_with_today_sessions' => array(
            'classname'   => 'mod_wsattendance_external',
            'methodname'  => 'get_courses_with_today_sessions',
            'classpath'   => 'mod/attendance/externallib.php',
            'description' => 'Method that retrieves courses with today sessions of a teacher.',
            'type'        => 'read',
        ),
        'mod_wsattendance_get_session' => array(
            'classname'   => 'mod_wsattendance_external',
            'methodname'  => 'get_session',
            'classpath'   => 'mod/attendance/externallib.php',
            'description' => 'Method that retrieves the session data',
            'type'        => 'read',
        ),

        'mod_wsattendance_update_user_status' => array(
            'classname'   => 'mod_wsattendance_external',
            'methodname'  => 'update_user_status',
            'classpath'   => 'mod/attendance/externallib.php',
            'description' => 'Method that updates the user status in a session.',
            'type'        => 'write',
        )
    );


        $services = array('Attendance' => array('functions' => array('mod_wsattendance_get_courses_with_today_sessions',
                   'mod_wsattendance_get_session',
                   'mod_wsattendance_update_user_status'),
                   'restrictedusers' => 0,
                   'enabled' => 1));
