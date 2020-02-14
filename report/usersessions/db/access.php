<?php



defined('MOODLE_INTERNAL') || die();

$capabilities = array(

    'report/usersessions:manageownsessions' => array(
        'captype' => 'write',
        'contextlevel' => CONTEXT_USER,
        'archetypes' => array(
            'user' => CAP_ALLOW,
        ),

                        'clonepermissionsfrom' => 'moodle/user:changeownpassword'
    ),
);


