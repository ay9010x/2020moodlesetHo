<?php



define('CHAT_MAX_CLIENT_UPDATES', 1000);
define('NO_MOODLE_COOKIES', true); define('NO_OUTPUT_BUFFERING', true);

require('../../../config.php');
require('../lib.php');

core_php_time_limit::raise();

$chatsid      = required_param('chat_sid',          PARAM_ALPHANUM);
$chatlasttime = optional_param('chat_lasttime',  0, PARAM_INT);
$chatlastrow  = optional_param('chat_lastrow',   1, PARAM_INT);
$chatlastid   = optional_param('chat_lastid',    0, PARAM_INT);

$url = new moodle_url('/mod/chat/gui_header_js/jsupdated.php', array('chat_sid' => $chatsid));
if ($chatlasttime !== 0) {
    $url->param('chat_lasttime', $chatlasttime);
}
if ($chatlastrow !== 1) {
    $url->param('chat_lastrow', $chatlastrow);
}
if ($chatlastid !== 1) {
    $url->param('chat_lastid', $chatlastid);
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

header('Expires: Sun, 28 Dec 1997 09:32:45 GMT');
header('Last-Modified: '.gmdate('D, d M Y H:i:s').' GMT');
header('Cache-Control: no-cache, must-revalidate');
header('Pragma: no-cache');
header('Content-Type: text/html; charset=utf-8');

$refreshurl = "{$CFG->wwwroot}/mod/chat/gui_header_js/jsupdated.php?".
              "chat_sid=$chatsid&chat_lasttime=$chatlasttime&chat_lastrow=$chatnewrow&chat_lastid=$chatlastid";
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html>
    <head>
        <meta http-equiv="content-type" content="text/html; charset=utf-8" />
        <script type="text/javascript">
        //<![CDATA[
        if (parent.msg.document.getElementById("msgStarted") == null) {
            parent.msg.document.close();
            parent.msg.document.open("text/html","replace");
            parent.msg.document.write("<!DOCTYPE html PUBLIC \"-//W3C//DTD XHTML 1.0 Transitional//EN\" \"http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd\">");
            parent.msg.document.write("<html><head>");
            parent.msg.document.write("<meta http-equiv=\"content-type\" content=\"text/html; charset=utf-8\" />");
            parent.msg.document.write("<base target=\"_blank\" />");
            parent.msg.document.write("</head><body class=\"mod-chat-gui_header_js course-<?php echo $chatuser->course ?>\" id=\"mod-chat-gui_header_js-jsupdate\"><div style=\"display: none\" id=\"msgStarted\">&nbsp;</div>");
        }
        //]]>
        </script>
    </head>
    <body>

<?php

echo $CHAT_DUMMY_DATA;

for ($n = 0; $n <= CHAT_MAX_CLIENT_UPDATES; $n++) {

        $chatuser->lastping = time();
    $DB->set_field('chat_users', 'lastping', $chatuser->lastping, array('id' => $chatuser->id));

    if ($message = chat_get_latest_message($chatuser->chatid, $chatuser->groupid)) {
        $chatnewlasttime = $message->timestamp;
        $chatnewlastid   = $message->id;
    } else {
        $chatnewlasttime = 0;
        $chatnewlastid   = 0;
        print " \n";
        print $CHAT_DUMMY_DATA;
        sleep($CFG->chat_refresh_room);
        continue;
    }

    $timenow    = time();

    $params = array('groupid' => $chatuser->groupid,
                    'lastid' => $chatlastid,
                    'lasttime' => $chatlasttime,
                    'chatid' => $chatuser->chatid);
    $groupselect = $chatuser->groupid ? " AND (groupid=:groupid OR groupid=0) " : "";

    $newcriteria = '';
    if ($chatlastid > 0) {
        $newcriteria = "id > :lastid";
    } else {
        if ($chatlasttime == 0) {             $chatlasttime = $timenow - $CFG->chat_old_ping;         }
        $newcriteria = "timestamp > :lasttime";
    }

    $messages = $DB->get_records_select("chat_messages_current",
                                   "chatid = :chatid AND $newcriteria $groupselect", $params,
                                   "timestamp ASC");

    if ($messages) {
        $num = count($messages);
    } else {
        print " \n";
        print $CHAT_DUMMY_DATA;
        sleep($CFG->chat_refresh_room);
        continue;
    }

    print '<script type="text/javascript">' . "\n";
    print "//<![CDATA[\n\n";

    $chatnewrow = ($chatlastrow + $num) % 2;

    $refreshusers = false;
    $us = array ();
    if (($chatlasttime != $chatnewlasttime) and $messages) {

        $beep         = false;
        $refreshusers = false;
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
            echo "parent.msg.document.write('".addslashes_js($formatmessage->html )."\\n');\n";

        }
                        $chatlasttime = $message->timestamp;
        $chatlastid   = $message->id;
    }

    if ($refreshusers) {
        echo "if (parent.users.document.anchors[0] != null) {" .
            "parent.users.location.href = parent.users.document.anchors[0].href;}\n";
    } else {
        foreach ($us as $uid => $lastping) {
            $min = (int) ($lastping / 60);
            $sec = $lastping - ($min * 60);
            $min = $min < 10 ? '0'.$min : $min;
            $sec = $sec < 10 ? '0'.$sec : $sec;
            $idle = $min.':'.$sec;
            echo "if (parent.users.document.getElementById('uidle{$uid}') != null) {".
                    "parent.users.document.getElementById('uidle{$uid}').innerHTML = '$idle';}\n";
        }
    }

    print <<<EOD
    if(parent.input){
        var autoscroll = parent.input.document.getElementById('auto');
        if(parent.msg && autoscroll && autoscroll.checked){
            parent.msg.scroll(1,5000000);
        }
    }
EOD;
    print "//]]>\n";
    print '</script>' . "\n\n";
    if ($beep) {
        print '<embed src="../beep.wav" autostart="true" hidden="true" name="beep" />';
    }
    print $CHAT_DUMMY_DATA;
    sleep($CFG->chat_refresh_room);
} 
$refreshurl = "{$CFG->wwwroot}/mod/chat/gui_header_js/jsupdated.php?";
$refreshurl .= "chat_sid=$chatsid&chat_lasttime=$chatlasttime&chat_lastrow=$chatnewrow&chat_lastid=$chatlastid";

print '<script type="text/javascript">' . "\n";
print "//<![CDATA[ \n\n";
print "location.href = '$refreshurl';\n";
print "//]]>\n";
print '</script>' . "\n\n";
?>

    </body>
</html>
