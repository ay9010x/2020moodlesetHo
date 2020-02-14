<?php




$capabilities = array(

    'webservice/rest:use' => array(
        'captype' => 'read',         'contextlevel' => CONTEXT_COURSE,         'archetypes' => array(
            'moodleset' => CAP_ALLOW,
        ),
    ),

);
