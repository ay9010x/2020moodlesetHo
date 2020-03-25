<?php



defined('MOODLE_INTERNAL') || die;

$functions = array(

    'mod_survey_get_surveys_by_courses' => array(
        'classname'     => 'mod_survey_external',
        'methodname'    => 'get_surveys_by_courses',
        'description'   => 'Returns a list of survey instances in a provided set of courses,
                            if no courses are provided then all the survey instances the user has access to will be returned.',
        'type'          => 'read',
        'capabilities'  => '',
        'services'      => array(MOODLE_OFFICIAL_MOBILE_SERVICE)
    ),

    'mod_survey_view_survey' => array(
        'classname'     => 'mod_survey_external',
        'methodname'    => 'view_survey',
        'description'   => 'Trigger the course module viewed event and update the module completion status.',
        'type'          => 'write',
        'capabilities'  => 'mod/survey:participate',
        'services'      => array(MOODLE_OFFICIAL_MOBILE_SERVICE)
    ),

    'mod_survey_get_questions' => array(
        'classname'     => 'mod_survey_external',
        'methodname'    => 'get_questions',
        'description'   => 'Get the complete list of questions for the survey, including subquestions.',
        'type'          => 'read',
        'capabilities'  => 'mod/survey:participate',
        'services'      => array(MOODLE_OFFICIAL_MOBILE_SERVICE)
    ),

    'mod_survey_submit_answers' => array(
        'classname'     => 'mod_survey_external',
        'methodname'    => 'submit_answers',
        'description'   => 'Submit the answers for a given survey.',
        'type'          => 'write',
        'capabilities'  => 'mod/survey:participate',
        'services'      => array(MOODLE_OFFICIAL_MOBILE_SERVICE)
    ),

);
