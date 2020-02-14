<?php

require_once('../../../config.php');
require_once('../lib.php');

$chatsid     = required_param('chat_sid', PARAM_ALPHANUM);
$chatmessage = required_param('chat_message', PARAM_RAW);

$PAGE->set_url('/mod/chat/gui_header_js/insert.php', array('chat_sid' => $chatsid, 'chat_message' => $chatmessage));

if (!$chatuser = $DB->get_record('chat_users', array('sid' => $chatsid))) {
    print_error('notlogged', 'chat');
}

if (!$chat = $DB->get_record('chat', array('id' => $chatuser->chatid))) {
    print_error('nochat', 'chat');
}

if (!$course = $DB->get_record('course', array('id' => $chat->course))) {
    print_error('invalidcourseid');
}

if (!$cm = get_coursemodule_from_instance('chat', $chat->id, $course->id)) {
    print_error('invalidcoursemodule');
}

require_login($course, false, $cm);

if (isguestuser()) {
    print_error('noguests');
}

\core\session\manager::write_close();


chat_delete_old_users();


$chatmessage = clean_text($chatmessage, FORMAT_MOODLE);  

if (!empty($chatmessage)) {

    chat_send_chatmessage($chatuser, $chatmessage, 0, $cm);

    $chatuser->lastmessageping = time() - 2;
    $DB->update_record('chat_users', $chatuser);
}

if ($chatuser->version == 'header_js') {

    $forcerefreshasap = ($CFG->chat_normal_updatemode != 'jsupdated'); 
    $module = array(
        'name'      => 'mod_chat_header',
        'fullpath'  => '/mod/chat/gui_header_js/module.js'
    );
    $PAGE->requires->js_init_call('M.mod_chat_header.init_insert_nojsupdated', array($forcerefreshasap), true, $module);
}

redirect('../empty.php');
