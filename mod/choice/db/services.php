<?php



defined('MOODLE_INTERNAL') || die;

$functions = array(

    'mod_choice_get_choice_results' => array(
        'classname'     => 'mod_choice_external',
        'methodname'    => 'get_choice_results',
        'description'   => 'Retrieve users results for a given choice.',
        'type'          => 'read',
        'capabilities'  => '',
        'services'      => array(MOODLE_OFFICIAL_MOBILE_SERVICE)
    ),

    'mod_choice_get_choice_options' => array(
        'classname'     => 'mod_choice_external',
        'methodname'    => 'get_choice_options',
        'description'   => 'Retrieve options for a specific choice.',
        'type'          => 'read',
        'capabilities'  => 'mod/choice:choose',
        'services'      => array(MOODLE_OFFICIAL_MOBILE_SERVICE)
    ),

    'mod_choice_submit_choice_response' => array(
        'classname'     => 'mod_choice_external',
        'methodname'    => 'submit_choice_response',
        'description'   => 'Submit responses to a specific choice item.',
        'type'          => 'write',
        'capabilities'  => 'mod/choice:choose',
        'services'      => array(MOODLE_OFFICIAL_MOBILE_SERVICE)
    ),

    'mod_choice_view_choice' => array(
        'classname'     => 'mod_choice_external',
        'methodname'    => 'view_choice',
        'description'   => 'Trigger the course module viewed event and update the module completion status.',
        'type'          => 'write',
        'capabilities'  => '',
        'services'      => array(MOODLE_OFFICIAL_MOBILE_SERVICE)
    ),

    'mod_choice_get_choices_by_courses' => array(
        'classname'     => 'mod_choice_external',
        'methodname'    => 'get_choices_by_courses',
        'description'   => 'Returns a list of choice instances in a provided set of courses,
                            if no courses are provided then all the choice instances the user has access to will be returned.',
        'type'          => 'read',
        'capabilities'  => '',
        'services'      => array(MOODLE_OFFICIAL_MOBILE_SERVICE)
    ),

    'mod_choice_delete_choice_responses' => array(
        'classname'     => 'mod_choice_external',
        'methodname'    => 'delete_choice_responses',
        'description'   => 'Delete the given submitted responses in a choice',
        'type'          => 'write',
        'capabilities'  => 'mod/choice:choose',
        'services'      => array(MOODLE_OFFICIAL_MOBILE_SERVICE)
    ),
);
