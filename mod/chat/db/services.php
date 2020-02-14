<?php



defined('MOODLE_INTERNAL') || die;

$functions = array(

    'mod_chat_login_user' => array(
        'classname'     => 'mod_chat_external',
        'methodname'    => 'login_user',
        'description'   => 'Log a user into a chat room in the given chat.',
        'type'          => 'write',
        'capabilities'  => 'mod/chat:chat',
        'services'      => array(MOODLE_OFFICIAL_MOBILE_SERVICE)
    ),

    'mod_chat_get_chat_users' => array(
        'classname'     => 'mod_chat_external',
        'methodname'    => 'get_chat_users',
        'description'   => 'Get the list of users in the given chat session.',
        'type'          => 'read',
        'capabilities'  => 'mod/chat:chat',
        'services'      => array(MOODLE_OFFICIAL_MOBILE_SERVICE)
    ),

    'mod_chat_send_chat_message' => array(
        'classname'     => 'mod_chat_external',
        'methodname'    => 'send_chat_message',
        'description'   => 'Send a message on the given chat session.',
        'type'          => 'write',
        'capabilities'  => 'mod/chat:chat',
        'services'      => array(MOODLE_OFFICIAL_MOBILE_SERVICE)
    ),

    'mod_chat_get_chat_latest_messages' => array(
        'classname'     => 'mod_chat_external',
        'methodname'    => 'get_chat_latest_messages',
        'description'   => 'Get the latest messages from the given chat session.',
        'type'          => 'read',
        'capabilities'  => 'mod/chat:chat',
        'services'      => array(MOODLE_OFFICIAL_MOBILE_SERVICE)
    ),

    'mod_chat_view_chat' => array(
        'classname'     => 'mod_chat_external',
        'methodname'    => 'view_chat',
        'description'   => 'Trigger the course module viewed event and update the module completion status.',
        'type'          => 'write',
        'capabilities'  => 'mod/chat:chat',
        'services'      => array(MOODLE_OFFICIAL_MOBILE_SERVICE)
    ),

    'mod_chat_get_chats_by_courses' => array(
        'classname'     => 'mod_chat_external',
        'methodname'    => 'get_chats_by_courses',
        'description'   => 'Returns a list of chat instances in a provided set of courses,
                            if no courses are provided then all the chat instances the user has access to will be returned.',
        'type'          => 'read',
        'capabilities'  => '',
        'services'      => array(MOODLE_OFFICIAL_MOBILE_SERVICE)
    )
);
