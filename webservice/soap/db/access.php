<?php




$capabilities = array(

    'webservice/soap:use' => array(
        'captype' => 'read',         'contextlevel' => CONTEXT_COURSE,         'archetypes' => array(
            'moodleset' => CAP_ALLOW,
        ),
    ),

);
