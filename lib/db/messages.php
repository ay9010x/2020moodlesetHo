<?php



defined('MOODLE_INTERNAL') || die();

$messageproviders = array (

        'notices' => array (
         'capability'  => 'moodle/site:config'
    ),

        'errors' => array (
         'capability'  => 'moodle/site:config'
    ),

        'availableupdate' => array(
        'capability' => 'moodle/site:config',
        'defaults' => array(
            'email' => MESSAGE_PERMITTED + MESSAGE_DEFAULT_LOGGEDIN + MESSAGE_DEFAULT_LOGGEDOFF
        ),

    ),

    'instantmessage' => array (
        'defaults' => array(
            'popup' => MESSAGE_PERMITTED + MESSAGE_DEFAULT_LOGGEDIN + MESSAGE_DEFAULT_LOGGEDOFF,
            'email' => MESSAGE_PERMITTED + MESSAGE_DEFAULT_LOGGEDOFF,
        ),
    ),

    'backup' => array (
        'capability'  => 'moodle/site:config'
    ),

        'courserequested' => array (
        'capability'  => 'moodle/site:approvecourse'
    ),

        'courserequestapproved' => array (
         'capability'  => 'moodle/course:request'
    ),

        'courserequestrejected' => array (
        'capability'  => 'moodle/course:request'
    ),

        'badgerecipientnotice' => array (
        'defaults' => array(
            'popup' => MESSAGE_PERMITTED + MESSAGE_DEFAULT_LOGGEDIN + MESSAGE_DEFAULT_LOGGEDOFF,
            'email' => MESSAGE_PERMITTED + MESSAGE_DEFAULT_LOGGEDOFF,
        ),
        'capability'  => 'moodle/badges:earnbadge'
    ),

        'badgecreatornotice' => array (
        'defaults' => array(
            'email' => MESSAGE_PERMITTED + MESSAGE_DEFAULT_LOGGEDOFF,
        )
    ),

        'competencyplancomment' => array(),

        'competencyusercompcomment' => array(),
);
