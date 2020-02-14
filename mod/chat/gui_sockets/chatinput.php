<?php

define('NO_MOODLE_COOKIES', true); 
require('../../../config.php');
require('../lib.php');

$chatsid = required_param('chat_sid', PARAM_ALPHANUM);

$PAGE->set_url('/mod/chat/gui_sockets/chatinput.php', array('chat_sid' => $chatsid));
$PAGE->set_popup_notification_allowed(false);

if (!$chatuser = $DB->get_record('chat_users', array('sid' => $chatsid))) {
    print_error('notlogged', 'chat');
}

$USER = $DB->get_record('user', array('id' => $chatuser->userid));

$PAGE->set_pagelayout('embedded');
$PAGE->set_course($DB->get_record('course', array('id' => $chatuser->course)));
$PAGE->requires->js('/mod/chat/gui_sockets/chat_gui_sockets.js', true);
$PAGE->requires->js_function_call('setfocus');
$PAGE->set_focuscontrol('chat_message');
$PAGE->set_cacheable(false);
echo $OUTPUT->header();

?>

    <form action="../empty.php" method="get" target="empty" id="inputform"
          onsubmit="return empty_field_and_submit();">
        <label class="accesshide" for="chat_message"><?php print_string('entermessage', 'chat'); ?></label>
        <input type="text" name="chat_message" id="chat_message" size="60" value="" />
    </form>

    <form action="<?php echo "http://$CFG->chat_serverhost:$CFG->chat_serverport/"; ?>" method="get" target="empty" id="sendform">
        <input type="hidden" name="win" value="message" />
        <input type="hidden" name="chat_message" value="" />
        <input type="hidden" name="chat_msgidnr" value="0" />
        <input type="hidden" name="chat_sid" value="<?php echo $chatsid ?>" />
    </form>
<?php
echo $OUTPUT->footer();

