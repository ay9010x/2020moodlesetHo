<?php




$capabilities = array(

    'webservice/xmlrpc:use' => array(
        'captype' => 'read',         'contextlevel' => CONTEXT_COURSE,         'archetypes' => array(
            'moodleset' => CAP_ALLOW,
        ),
    ),

);
