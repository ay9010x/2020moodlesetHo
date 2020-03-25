<?php



$publishes = array(
    'sso_idp' => array(
        'apiversion' => 1,
        'classname'  => 'auth_plugin_mnet',
        'filename'   => 'auth.php',
        'methods'    => array(
            'user_authorise',
            'keepalive_server',
            'kill_children',
            'refresh_log',
            'fetch_user_image',
            'fetch_theme_info',
            'update_enrolments',
        ),
    ),
    'sso_sp' => array(
        'apiversion' => 1,
        'classname'  => 'auth_plugin_mnet',
        'filename'   => 'auth.php',
        'methods'    => array(
            'keepalive_client',
            'kill_child'
        )
    )
);
$subscribes = array(
    'sso_idp' => array(
        'user_authorise'    => 'auth/mnet/auth.php/user_authorise',
        'keepalive_server'  => 'auth/mnet/auth.php/keepalive_server',
        'kill_children'     => 'auth/mnet/auth.php/kill_children',
        'refresh_log'       => 'auth/mnet/auth.php/refresh_log',
        'fetch_user_image'  => 'auth/mnet/auth.php/fetch_user_image',
        'fetch_theme_info'  => 'auth/mnet/auth.php/fetch_theme_info',
        'update_enrolments' => 'auth/mnet/auth.php/update_enrolments',
    ),
    'sso_sp' => array(
        'keepalive_client' => 'auth/mnet/auth.php/keepalive_client',
        'kill_child'       => 'auth/mnet/auth.php/kill_child',
    ),
);
