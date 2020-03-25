<?php

define('NO_MOODLE_COOKIES', true); 
require('../../../config.php');
require('../lib.php');

$chatsid      = required_param('chat_sid', PARAM_ALPHANUM);
$chatlasttime = optional_param('chat_lasttime', 0, PARAM_INT);
$chatlastrow  = optional_param('chat_lastrow', 1, PARAM_INT);

$url = new moodle_url('/mod/chat/gui_header_js/jsupdate.php', array('chat_sid' => $chatsid));
if ($chatlasttime !== 0) {
    $url->param('chat_lasttime', $chatlasttime);
}
if ($chatlastrow !== 1) {
    $url->param('chat_lastrow', $chatlastrow);
}
$PAGE->set_url($url);


if (!$chatuser = $DB->get_record('chat_users', array('sid' => $chatsid))) {
    print_error('notlogged', 'chat');
}

if (!$course = $DB->get_record('course', array('id' => $chatuser->course))) {
    print_error('invalidcourseid');
}

if (!$user = $DB->get_record('user', array('id' => $chatuser->userid, 'deleted' => 0, 'suspended' => 0))) {
    print_error('invaliduser');
}
\core\session\manager::set_user($user);

$PAGE->set_course($course);

if ((time() - $chatlasttime) > $CFG->chat_old_ping) {
        chat_delete_old_users();
}

if ($message = chat_get_latest_message($chatuser->chatid, $chatuser->groupid)) {
    $chatnewlasttime = $message->timestamp;
} else {
    $chatnewlasttime = 0;
}

if ($chatlasttime == 0) {     $chatlasttime = time() - $CFG->chat_old_ping; }

$timenow    = time();

$params = array('groupid' => $chatuser->groupid, 'chatid' => $chatuser->chatid, 'lasttime' => $chatlasttime);

$groupselect = $chatuser->groupid ? " AND (groupid=:groupid OR groupid=0) " : "";

$messages = $DB->get_records_select("chat_messages_current",
                    "chatid = :chatid AND timestamp > :lasttime $groupselect", $params,
                    "timestamp ASC");

if ($messages) {
    $num = count($messages);
} else {
    $num = 0;
}

$chatnewrow = ($chatlastrow + $num) % 2;

$baseurl = "{$CFG->wwwroot}/mod/chat/gui_header_js/jsupdate.php?";
$refreshurl = $baseurl . "chat_sid=$chatsid&chat_lasttime=$chatnewlasttime&chat_lastrow=$chatnewrow";
$refreshurlamp = $baseurl . "chat_sid=$chatsid&amp;chat_lasttime=$chatnewlasttime&amp;chat_lastrow=$chatnewrow";

header('Expires: Sun, 28 Dec 1997 09:32:45 GMT');
header('Last-Modified: '.gmdate('D, d M Y H:i:s').' GMT');
header('Cache-Control: no-cache, must-revalidate');
header('Pragma: no-cache');
header('Content-Type: text/html; charset=utf-8');
header("Refresh: $CFG->chat_refresh_room; url=$refreshurl");

ob_start();

?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html>
    <head>
        <meta http-equiv="content-type" content="text/html; charset=utf-8" />
        <script type="text/javascript">
        //<![CDATA[
        if (parent.msg && parent.msg.document.getElementById("msgStarted") == null) {
            parent.msg.document.close();
            parent.msg.document.open("text/html","replace");
            parent.msg.document.write("<!DOCTYPE html PUBLIC \"-//W3C//DTD XHTML 1.0 Transitional//EN\" \"http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd\">");
            parent.msg.document.write("<html><head>");
            parent.msg.document.write("<meta http-equiv=\"content-type\" content=\"text/html; charset=utf-8\" />");
            parent.msg.document.write("<base target=\"_blank\" />");
            parent.msg.document.write("<\/head><body class=\"mod-chat-gui_header_js course-<?php echo $chatuser->course ?>\" id=\"mod-chat-gui_header_js-jsupdate\"><div style=\"display: none\" id=\"msgStarted\">&nbsp;<\/div>");
        }
<?php
$beep = false;
$refreshusers = false;
$us = array ();
if (($chatlasttime != $chatnewlasttime) and $messages) {

    foreach ($messages as $message) {
        $chatlastrow = ($chatlastrow + 1) % 2;
        $formatmessage = chat_format_message($message, $chatuser->course, $USER, $chatlastrow);
        if ($formatmessage->beep) {
             $beep = true;
        }
        if ($formatmessage->refreshusers) {
             $refreshusers = true;
        }
        $us[$message->userid] = $timenow - $message->timestamp;
        echo "if(parent.msg)";
        echo "parent.msg.document.write('".addslashes_js($formatmessage->html)."\\n');\n";
    }
}

$chatuser->lastping = time();
$DB->set_field('chat_users', 'lastping', $chatuser->lastping, array('id' => $chatuser->id));

if ($refreshusers) {
?>
        var link = parent.users.document.getElementById('refreshLink');
        if (link != null) {
            parent.users.location.href = link.href;
        }
<?php
} else {
    foreach ($us as $uid => $lastping) {
        $min = (int) ($lastping / 60);
        $sec = $lastping - ($min * 60);
        $min = $min < 10 ? '0'.$min : $min;
        $sec = $sec < 10 ? '0'.$sec : $sec;
        $idle = $min.':'.$sec;
        echo "if (parent.users && parent.users.document.getElementById('uidle{$uid}') != null) {".
                "parent.users.document.getElementById('uidle{$uid}').innerHTML = '$idle';}\n";
    }
}
?>
        if (parent.input) {
            var autoscroll = parent.input.document.getElementById('auto');
            if (parent.msg && autoscroll && autoscroll.checked) {
                parent.msg.scroll(1,5000000);
            }
        }
        //]]>
        </script>
    </head>
    <body>
<?php
if ($beep) {
    echo '<embed src="../beep.wav" autostart="true" hidden="true" name="beep" />';
}
?>
       <a href="<?php echo $refreshurlamp ?>" name="refreshLink">Refresh link</a>
    </body>
</html>
<?php

header("Content-Length: " . ob_get_length() );
ob_end_flush();
exit;

