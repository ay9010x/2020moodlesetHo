<?php



$functions = array(

    'tool_mobile_get_plugins_supporting_mobile' => array(
        'classname'   => 'tool_mobile\external',
        'methodname'  => 'get_plugins_supporting_mobile',
        'description' => 'Returns a list of Moodle plugins supporting the mobile app.',
        'type'        => 'read',
        'services'    => array(MOODLE_OFFICIAL_MOBILE_SERVICE)
    )

);

