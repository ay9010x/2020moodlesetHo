<?php



defined('MOODLE_INTERNAL') || die();

require_once($CFG->dirroot.'/calendar/lib.php');

global $CHAT_HTMLHEAD;
$CHAT_HTMLHEAD = "<!DOCTYPE HTML PUBLIC \"-//W3C//DTD HTML 4.0 Transitional//EN\" \"http://www.w3.org/TR/REC-html40/loose.dtd\"><html><head></head>\n<body>\n\n".padding(200);

global $CHAT_HTMLHEAD_JS;
$CHAT_HTMLHEAD_JS = <<<EOD
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.0 Transitional//EN" "http://www.w3.org/TR/REC-html40/loose.dtd">
<html><head><script type="text/javascript">
//<![CDATA[
function move() {
    if (scroll_active)
        window.scroll(1,400000);
    window.setTimeout("move()",100);
}
var scroll_active = true;
move();
//]]>
</script>
</head>
<body onBlur="scroll_active = true" onFocus="scroll_active = false">
EOD;
global $CHAT_HTMLHEAD_JS;
$CHAT_HTMLHEAD_JS .= padding(200);

global $CHAT_HTMLHEAD_OUT;
$CHAT_HTMLHEAD_OUT = "<!DOCTYPE HTML PUBLIC \"-//W3C//DTD HTML 4.0 Transitional//EN\" \"http://www.w3.org/TR/REC-html40/loose.dtd\"><html><head><title>You are out!</title></head><body></body></html>";

global $CHAT_HTMLHEAD_MSGINPUT;
$CHAT_HTMLHEAD_MSGINPUT = "<!DOCTYPE HTML PUBLIC \"-//W3C//DTD HTML 4.0 Transitional//EN\" \"http://www.w3.org/TR/REC-html40/loose.dtd\"><html><head><title>Message Input</title></head><body>";

global $CHAT_HTMLHEAD_MSGINPUT_JS;
$CHAT_HTMLHEAD_MSGINPUT_JS = <<<EOD
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.0 Transitional//EN" "http://www.w3.org/TR/REC-html40/loose.dtd">
<html>
    <head><title>Message Input</title>
    <script type="text/javascript">
    //<![CDATA[
    scroll_active = true;
    function empty_field_and_submit() {
        document.fdummy.arsc_message.value=document.f.arsc_message.value;
        document.fdummy.submit();
        document.f.arsc_message.focus();
        document.f.arsc_message.select();
        return false;
    }
    //]]>
    </script>
    </head><body OnLoad="document.f.arsc_message.focus();document.f.arsc_message.select();">;
EOD;

global $CHAT_DUMMY_DATA;
$CHAT_DUMMY_DATA = padding(200);


function padding($n) {
    $str = '';
    for ($i = 0; $i < $n; $i++) {
        $str .= "<!-- nix -->\n";
    }
    return $str;
}


function chat_add_instance($chat) {
    global $DB;

    $chat->timemodified = time();

    $returnid = $DB->insert_record("chat", $chat);

    if ($chat->schedule > 0) {
        $event = new stdClass();
        $event->name        = $chat->name;
        $event->description = format_module_intro('chat', $chat, $chat->coursemodule);
        $event->courseid    = $chat->course;
        $event->groupid     = 0;
        $event->userid      = 0;
        $event->modulename  = 'chat';
        $event->instance    = $returnid;
        $event->eventtype   = 'chattime';
        $event->timestart   = $chat->chattime;
        $event->timeduration = 0;

        calendar_event::create($event);
    }
    return $returnid;
}


function chat_update_instance($chat) {
    global $DB;

    $chat->timemodified = time();
    $chat->id = $chat->instance;

    $DB->update_record("chat", $chat);

    $event = new stdClass();

    if ($event->id = $DB->get_field('event', 'id', array('modulename' => 'chat', 'instance' => $chat->id))) {

        if ($chat->schedule > 0) {
            $event->name        = $chat->name;
            $event->description = format_module_intro('chat', $chat, $chat->coursemodule);
            $event->timestart   = $chat->chattime;

            $calendarevent = calendar_event::load($event->id);
            $calendarevent->update($event);
        } else {
                        $calendarevent = calendar_event::load($event->id);
            $calendarevent->delete();
        }
    } else {
                if ($chat->schedule > 0) {
            $event = new stdClass();
            $event->name        = $chat->name;
            $event->description = format_module_intro('chat', $chat, $chat->coursemodule);
            $event->courseid    = $chat->course;
            $event->groupid     = 0;
            $event->userid      = 0;
            $event->modulename  = 'chat';
            $event->instance    = $chat->id;
            $event->eventtype   = 'chattime';
            $event->timestart   = $chat->chattime;
            $event->timeduration = 0;

            calendar_event::create($event);
        }
    }

    return true;
}


function chat_delete_instance($id) {
    global $DB;

    if (! $chat = $DB->get_record('chat', array('id' => $id))) {
        return false;
    }

    $result = true;

    
    if (! $DB->delete_records('chat', array('id' => $chat->id))) {
        $result = false;
    }
    if (! $DB->delete_records('chat_messages', array('chatid' => $chat->id))) {
        $result = false;
    }
    if (! $DB->delete_records('chat_messages_current', array('chatid' => $chat->id))) {
        $result = false;
    }
    if (! $DB->delete_records('chat_users', array('chatid' => $chat->id))) {
        $result = false;
    }

    if (! $DB->delete_records('event', array('modulename' => 'chat', 'instance' => $chat->id))) {
        $result = false;
    }

    return $result;
}


function chat_print_recent_activity($course, $viewfullnames, $timestart) {
    global $CFG, $USER, $DB, $OUTPUT;

        $timeout = $CFG->chat_old_ping * 10;

    if (!$mcms = $DB->get_records_sql("SELECT cm.id, MAX(chm.timestamp) AS lasttime
                                         FROM {course_modules} cm
                                         JOIN {modules} md        ON md.id = cm.module
                                         JOIN {chat} ch           ON ch.id = cm.instance
                                         JOIN {chat_messages} chm ON chm.chatid = ch.id
                                        WHERE chm.timestamp > ? AND ch.course = ? AND md.name = 'chat'
                                     GROUP BY cm.id
                                     ORDER BY lasttime ASC", array($timestart, $course->id))) {
         return false;
    }

    $past     = array();
    $current  = array();
    $modinfo = get_fast_modinfo($course); 
    foreach ($mcms as $cmid => $mcm) {
        if (!array_key_exists($cmid, $modinfo->cms)) {
            continue;
        }
        $cm = $modinfo->cms[$cmid];
        if (!$modinfo->cms[$cm->id]->uservisible) {
            continue;
        }

        if (groups_get_activity_groupmode($cm) != SEPARATEGROUPS
         or has_capability('moodle/site:accessallgroups', context_module::instance($cm->id))) {
            if ($timeout > time() - $mcm->lasttime) {
                $current[] = $cm;
            } else {
                $past[] = $cm;
            }

            continue;
        }

                if (!$mygroupids = $modinfo->get_groups($cm->groupingid)) {
            continue;
        }

                        $mygroupids = implode(',', $mygroupids);

        if (!$mcm = $DB->get_record_sql("SELECT cm.id, MAX(chm.timestamp) AS lasttime
                                           FROM {course_modules} cm
                                           JOIN {chat} ch           ON ch.id = cm.instance
                                           JOIN {chat_messages_current} chm ON chm.chatid = ch.id
                                          WHERE chm.timestamp > ? AND cm.id = ? AND
                                                (chm.groupid IN ($mygroupids) OR chm.groupid = 0)
                                       GROUP BY cm.id", array($timestart, $cm->id))) {
             continue;
        }

        $mcms[$cmid]->lasttime = $mcm->lasttime;
        if ($timeout > time() - $mcm->lasttime) {
            $current[] = $cm;
        } else {
            $past[] = $cm;
        }
    }

    if (!$past and !$current) {
        return false;
    }

    $strftimerecent = get_string('strftimerecent');

    if ($past) {
        echo $OUTPUT->heading(get_string("pastchats", 'chat').':', 3);

        foreach ($past as $cm) {
            $link = $CFG->wwwroot.'/mod/chat/view.php?id='.$cm->id;
            $date = userdate($mcms[$cm->id]->lasttime, $strftimerecent);
            echo '<div class="head"><div class="date">'.$date.'</div></div>';
            echo '<div class="info"><a href="'.$link.'">'.format_string($cm->name, true).'</a></div>';
        }
    }

    if ($current) {
        echo $OUTPUT->heading(get_string("currentchats", 'chat').':', 3);

        $oldest = floor((time() - $CFG->chat_old_ping) / 10) * 10;  
        $timeold    = time() - $CFG->chat_old_ping;
        $timeold    = floor($timeold / 10) * 10;          $timeoldext = time() - ($CFG->chat_old_ping * 10);         $timeoldext = floor($timeoldext / 10) * 10;  
        $params = array('timeold' => $timeold, 'timeoldext' => $timeoldext, 'cmid' => $cm->id);

        $timeout = "AND ((chu.version<>'basic' AND chu.lastping>:timeold) OR (chu.version='basic' AND chu.lastping>:timeoldext))";

        foreach ($current as $cm) {
                        $mygroupids = $modinfo->groups[$cm->groupingid];
            if (!empty($mygroupids)) {
                list($subquery, $subparams) = $DB->get_in_or_equal($mygroupids, SQL_PARAMS_NAMED, 'gid');
                $params += $subparams;
                $groupselect = "AND (chu.groupid $subquery OR chu.groupid = 0)";
            } else {
                $groupselect = "";
            }

            $userfields = user_picture::fields('u');
            if (!$users = $DB->get_records_sql("SELECT $userfields
                                                  FROM {course_modules} cm
                                                  JOIN {chat} ch        ON ch.id = cm.instance
                                                  JOIN {chat_users} chu ON chu.chatid = ch.id
                                                  JOIN {user} u         ON u.id = chu.userid
                                                 WHERE cm.id = :cmid $timeout $groupselect
                                              GROUP BY $userfields", $params)) {
            }

            $link = $CFG->wwwroot.'/mod/chat/view.php?id='.$cm->id;
            $date = userdate($mcms[$cm->id]->lasttime, $strftimerecent);

            echo '<div class="head"><div class="date">'.$date.'</div></div>';
            echo '<div class="info"><a href="'.$link.'">'.format_string($cm->name, true).'</a></div>';
            echo '<div class="userlist">';
            if ($users) {
                echo '<ul>';
                foreach ($users as $user) {
                    echo '<li>'.fullname($user, $viewfullnames).'</li>';
                }
                echo '</ul>';
            }
            echo '</div>';
        }
    }

    return true;
}


function chat_cron () {
    global $DB;

    chat_update_chat_times();

    chat_delete_old_users();

        $subselect = "SELECT c.keepdays
                    FROM {chat} c
                   WHERE c.id = {chat_messages}.chatid";

    $sql = "DELETE
              FROM {chat_messages}
             WHERE ($subselect) > 0 AND timestamp < ( ".time()." -($subselect) * 24 * 3600)";

    $DB->execute($sql);

    $sql = "DELETE
              FROM {chat_messages_current}
             WHERE timestamp < ( ".time()." - 8 * 3600)";

    $DB->execute($sql);

    return true;
}


function chat_refresh_events($courseid = 0) {
    global $DB;

    if ($courseid) {
        if (! $chats = $DB->get_records("chat", array("course" => $courseid))) {
            return true;
        }
    } else {
        if (! $chats = $DB->get_records("chat")) {
            return true;
        }
    }
    $moduleid = $DB->get_field('modules', 'id', array('name' => 'chat'));

    foreach ($chats as $chat) {
        $cm = get_coursemodule_from_instance('chat', $chat->id, $chat->course);
        $event = new stdClass();
        $event->name        = $chat->name;
        $event->description = format_module_intro('chat', $chat, $cm->id);
        $event->timestart   = $chat->chattime;

        if ($event->id = $DB->get_field('event', 'id', array('modulename' => 'chat', 'instance' => $chat->id))) {
            $calendarevent = calendar_event::load($event->id);
            $calendarevent->update($event);
        } else if ($chat->schedule > 0) {
                        $event->courseid    = $chat->course;
            $event->groupid     = 0;
            $event->userid      = 0;
            $event->modulename  = 'chat';
            $event->instance    = $chat->id;
            $event->eventtype   = 'chattime';
            $event->timeduration = 0;
            $event->visible = $DB->get_field('course_modules', 'visible', array('module' => $moduleid, 'instance' => $chat->id));

            calendar_event::create($event);
        }
    }
    return true;
}



function chat_get_users($chatid, $groupid=0, $groupingid=0) {
    global $DB;

    $params = array('chatid' => $chatid, 'groupid' => $groupid, 'groupingid' => $groupingid);

    if ($groupid) {
        $groupselect = " AND (c.groupid=:groupid OR c.groupid='0')";
    } else {
        $groupselect = "";
    }

    if (!empty($groupingid)) {
        $groupingjoin = "JOIN {groups_members} gm ON u.id = gm.userid
                         JOIN {groupings_groups} gg ON gm.groupid = gg.groupid AND gg.groupingid = :groupingid ";

    } else {
        $groupingjoin = '';
    }

    $ufields = user_picture::fields('u');
    return $DB->get_records_sql("SELECT DISTINCT $ufields, c.lastmessageping, c.firstping
                                   FROM {chat_users} c
                                   JOIN {user} u ON u.id = c.userid $groupingjoin
                                  WHERE c.chatid = :chatid $groupselect
                               ORDER BY c.firstping ASC", $params);
}


function chat_get_latest_message($chatid, $groupid=0) {
    global $DB;

    $params = array('chatid' => $chatid, 'groupid' => $groupid);

    if ($groupid) {
        $groupselect = "AND (groupid=:groupid OR groupid=0)";
    } else {
        $groupselect = "";
    }

    $sql = "SELECT *
        FROM {chat_messages_current} WHERE chatid = :chatid $groupselect
        ORDER BY timestamp DESC";

        return $DB->get_record_sql($sql, $params, true);
}


function chat_login_user($chatid, $version, $groupid, $course) {
    global $USER, $DB;

    if (($version != 'sockets') and $chatuser = $DB->get_record('chat_users', array('chatid' => $chatid,
                                                                                    'userid' => $USER->id,
                                                                                    'groupid' => $groupid))) {
                $chatuser->version  = $version;
        $chatuser->ip       = $USER->lastip;
        $chatuser->lastping = time();
        $chatuser->lang     = current_language();

                        if (empty($chatuser->ip)) {
            $chatuser->ip = getremoteaddr();
        }

        if (($chatuser->course != $course->id) or ($chatuser->userid != $USER->id)) {
            return false;
        }
        $DB->update_record('chat_users', $chatuser);

    } else {
        $chatuser = new stdClass();
        $chatuser->chatid   = $chatid;
        $chatuser->userid   = $USER->id;
        $chatuser->groupid  = $groupid;
        $chatuser->version  = $version;
        $chatuser->ip       = $USER->lastip;
        $chatuser->lastping = $chatuser->firstping = $chatuser->lastmessageping = time();
        $chatuser->sid      = random_string(32);
        $chatuser->course   = $course->id;         $chatuser->lang     = current_language(); 
                        if (empty($chatuser->ip)) {
            $chatuser->ip = getremoteaddr();
        }

        $DB->insert_record('chat_users', $chatuser);

        if ($version == 'sockets') {
                    } else {
            chat_send_chatmessage($chatuser, 'enter', true);
        }
    }

    return $chatuser->sid;
}


function chat_delete_old_users() {
        global $CFG, $DB;

    $timeold = time() - $CFG->chat_old_ping;
    $timeoldext = time() - ($CFG->chat_old_ping * 10); 
    $query = "(version<>'basic' AND lastping<?) OR (version='basic' AND lastping<?)";
    $params = array($timeold, $timeoldext);

    if ($oldusers = $DB->get_records_select('chat_users', $query, $params) ) {
        $DB->delete_records_select('chat_users', $query, $params);
        foreach ($oldusers as $olduser) {
            chat_send_chatmessage($olduser, 'exit', true);
        }
    }
}


function chat_update_chat_times($chatid=0) {
        global $DB;

    $timenow = time();

    $params = array('timenow' => $timenow, 'chatid' => $chatid);

    if ($chatid) {
        if (!$chats[] = $DB->get_record_select("chat", "id = :chatid AND chattime <= :timenow AND schedule > 0", $params)) {
            return;
        }
    } else {
        if (!$chats = $DB->get_records_select("chat", "chattime <= :timenow AND schedule > 0", $params)) {
            return;
        }
    }

    foreach ($chats as $chat) {
        switch ($chat->schedule) {
            case 1:                 $chat->chattime = 0;
                $chat->schedule = 0;
                break;
            case 2:                 while ($chat->chattime <= $timenow) {
                    $chat->chattime += 24 * 3600;
                }
                break;
            case 3:                 while ($chat->chattime <= $timenow) {
                    $chat->chattime += 7 * 24 * 3600;
                }
                break;
        }
        $DB->update_record("chat", $chat);

        $event = new stdClass(); 
        $cond = "modulename='chat' AND instance = :chatid AND timestart <> :chattime";
        $params = array('chattime' => $chat->chattime, 'chatid' => $chat->id);

        if ($event->id = $DB->get_field_select('event', 'id', $cond, $params)) {
            $event->timestart   = $chat->chattime;
            $calendarevent = calendar_event::load($event->id);
            $calendarevent->update($event, false);
        }
    }
}


function chat_send_chatmessage($chatuser, $messagetext, $system = false, $cm = null) {
    global $DB;

    $message = new stdClass();
    $message->chatid    = $chatuser->chatid;
    $message->userid    = $chatuser->userid;
    $message->groupid   = $chatuser->groupid;
    $message->message   = $messagetext;
    $message->system    = $system ? 1 : 0;
    $message->timestamp = time();

    $messageid = $DB->insert_record('chat_messages', $message);
    $DB->insert_record('chat_messages_current', $message);
    $message->id = $messageid;

    if (!$system) {

        if (empty($cm)) {
            $cm = get_coursemodule_from_instance('chat', $chatuser->chatid, $chatuser->course);
        }

        $params = array(
            'context' => context_module::instance($cm->id),
            'objectid' => $message->id,
                        'relateduserid' => $chatuser->userid
        );
        $event = \mod_chat\event\message_sent::create($params);
        $event->add_record_snapshot('chat_messages', $message);
        $event->trigger();
    }

    return $message->id;
}


function chat_format_message_manually($message, $courseid, $sender, $currentuser, $chatlastrow = null) {
    global $CFG, $USER, $OUTPUT;

    $output = new stdClass();
    $output->beep = false;           $output->refreshusers = false; 
        $tz = core_date::get_user_timezone($currentuser);

    $message->strtime = userdate($message->timestamp, get_string('strftimemessage', 'chat'), $tz);

    $message->picture = $OUTPUT->user_picture($sender, array('size' => false, 'courseid' => $courseid, 'link' => false));

    if ($courseid) {
        $message->picture = "<a onclick=\"window.open('$CFG->wwwroot/user/view.php?id=$sender->id&amp;course=$courseid')\"".
                            " href=\"$CFG->wwwroot/user/view.php?id=$sender->id&amp;course=$courseid\">$message->picture</a>";
    }

        if ($chatlastrow !== null) {
        $rowclass = ' class="r'.$chatlastrow.'" ';
    } else {
        $rowclass = '';
    }

    
    if (!empty($message->system)) {
                $output->text = $message->strtime.': '.get_string('message'.$message->message, 'chat', fullname($sender));
        $output->html  = '<table class="chat-event"><tr'.$rowclass.'><td class="picture">'.$message->picture.'</td>';
        $output->html .= '<td class="text"><span class="event">'.$output->text.'</span></td></tr></table>';
        $output->basic = '<tr class="r1">
                            <th scope="row" class="cell c1 title"></th>
                            <td class="cell c2 text">' . get_string('message'.$message->message, 'chat', fullname($sender)) . '</td>
                            <td class="cell c3">' . $message->strtime . '</td>
                          </tr>';
        if ($message->message == 'exit' or $message->message == 'enter') {
            $output->refreshusers = true;         }
        return $output;
    }

        $rawtext = trim($message->message);

                        $options = new stdClass();
    $options->para = false;
    $options->blanktarget = true;

        $patternto = '#^\s*To\s([^:]+):(.*)#';
    $special = false;

    if (substr($rawtext, 0, 5) == 'beep ') {
                $special = true;
        $beepwho = trim(substr($rawtext, 5));

        if ($beepwho == 'all') {               $outinfobasic = get_string('messagebeepseveryone', 'chat', fullname($sender));
            $outinfo = $message->strtime . ': ' . $outinfobasic;
            $outmain = '';

            $output->beep = true;  
        } else if ($beepwho == $currentuser->id) {              $outinfobasic = get_string('messagebeepsyou', 'chat', fullname($sender));
            $outinfo = $message->strtime . ': ' . $outinfobasic;
            $outmain = '';
            $output->beep = true;

        } else {              return false;
        }
    } else if (substr($rawtext, 0, 1) == '/') {             $special = true;
        $pattern = '#(^\/)(\w+).*#';
        preg_match($pattern, $rawtext, $matches);
        $command = isset($matches[2]) ? $matches[2] : false;
                switch ($command) {
            case 'me':
                $outinfo = $message->strtime;
                $text = '*** <b>'.$sender->firstname.' '.substr($rawtext, 4).'</b>';
                $outmain = format_text($text, FORMAT_MOODLE, $options, $courseid);
                break;
            default:
                                $special = false;
                break;
        }
    } else if (preg_match($patternto, $rawtext)) {
        $special = true;
        $matches = array();
        preg_match($patternto, $rawtext, $matches);
        if (isset($matches[1]) && isset($matches[2])) {
            $text = format_text($matches[2], FORMAT_MOODLE, $options, $courseid);
            $outinfo = $message->strtime;
            $outmain = $sender->firstname.' '.get_string('saidto', 'chat').' <i>'.$matches[1].'</i>: '.$text;
        } else {
                        $special = false;
        }
    }

    if (!$special) {
        $text = format_text($rawtext, FORMAT_MOODLE, $options, $courseid);
        $outinfo = $message->strtime.' '.$sender->firstname;
        $outmain = $text;
    }

    
    $output->text  = strip_tags($outinfo.': '.$outmain);

    $output->html  = "<table class=\"chat-message\"><tr$rowclass><td class=\"picture\" valign=\"top\">$message->picture</td>";
    $output->html .= "<td class=\"text\"><span class=\"title\">$outinfo</span>";
    if ($outmain) {
        $output->html .= ": $outmain";
        $output->basic = '<tr class="r0">
                            <th scope="row" class="cell c1 title">' . $sender->firstname . '</th>
                            <td class="cell c2 text">' . $outmain . '</td>
                            <td class="cell c3">' . $message->strtime . '</td>
                          </tr>';
    } else {
        $output->basic = '<tr class="r1">
                            <th scope="row" class="cell c1 title"></th>
                            <td class="cell c2 text">' . $outinfobasic . '</td>
                            <td class="cell c3">' . $message->strtime . '</td>
                          </tr>';
    }
    $output->html .= "</td></tr></table>";
    return $output;
}


function chat_format_message($message, $courseid, $currentuser, $chatlastrow=null) {
    global $DB;

    static $users;     
    if (isset($users[$message->userid])) {
        $user = $users[$message->userid];
    } else if ($user = $DB->get_record('user', array('id' => $message->userid), user_picture::fields())) {
        $users[$message->userid] = $user;
    } else {
        return null;
    }
    return chat_format_message_manually($message, $courseid, $user, $currentuser, $chatlastrow);
}


function chat_format_message_theme ($message, $chatuser, $currentuser, $groupingid, $theme = 'bubble') {
    global $CFG, $USER, $OUTPUT, $COURSE, $DB, $PAGE;
    require_once($CFG->dirroot.'/mod/chat/locallib.php');

    static $users;     
    $result = new stdClass();

    if (file_exists($CFG->dirroot . '/mod/chat/gui_ajax/theme/'.$theme.'/config.php')) {
        include($CFG->dirroot . '/mod/chat/gui_ajax/theme/'.$theme.'/config.php');
    }

    if (isset($users[$message->userid])) {
        $sender = $users[$message->userid];
    } else if ($sender = $DB->get_record('user', array('id' => $message->userid), user_picture::fields())) {
        $users[$message->userid] = $sender;
    } else {
        return null;
    }

        $tz = core_date::get_user_timezone($currentuser);

    if (empty($chatuser->course)) {
        $courseid = $COURSE->id;
    } else {
        $courseid = $chatuser->course;
    }

    $message->strtime = userdate($message->timestamp, get_string('strftimemessage', 'chat'), $tz);
    $message->picture = $OUTPUT->user_picture($sender, array('courseid' => $courseid));

    $message->picture = "<a target='_blank'".
                        " href=\"$CFG->wwwroot/user/view.php?id=$sender->id&amp;course=$courseid\">$message->picture</a>";

        if (!empty($message->system)) {
        $result->type = 'system';

        $senderprofile = $CFG->wwwroot.'/user/view.php?id='.$sender->id.'&amp;course='.$courseid;
        $event = get_string('message'.$message->message, 'chat', fullname($sender));
        $eventmessage = new event_message($senderprofile, fullname($sender), $message->strtime, $event, $theme);

        $output = $PAGE->get_renderer('mod_chat');
        $result->html = $output->render($eventmessage);

        return $result;
    }

        $rawtext = trim($message->message);

                        $options = new stdClass();
    $options->para = false;
    $options->blanktarget = true;

        $special = false;
    $outtime = $message->strtime;

        $outmain = '';
    $patternto = '#^\s*To\s([^:]+):(.*)#';

    if (substr($rawtext, 0, 5) == 'beep ') {
        $special = true;
                $result->type = 'beep';
        $beepwho = trim(substr($rawtext, 5));

        if ($beepwho == 'all') {               $outmain = get_string('messagebeepseveryone', 'chat', fullname($sender));
        } else if ($beepwho == $currentuser->id) {              $outmain = get_string('messagebeepsyou', 'chat', fullname($sender));
        } else if ($sender->id == $currentuser->id) {                          if (!empty($chatuser) && is_numeric($beepwho)) {
                $chatusers = chat_get_users($chatuser->chatid, $chatuser->groupid, $groupingid);
                if (array_key_exists($beepwho, $chatusers)) {
                    $outmain = get_string('messageyoubeep', 'chat', fullname($chatusers[$beepwho]));
                } else {
                    $outmain = get_string('messageyoubeep', 'chat', $beepwho);
                }
            } else {
                $outmain = get_string('messageyoubeep', 'chat', $beepwho);
            }
        }
    } else if (substr($rawtext, 0, 1) == '/') {             $special = true;
        $result->type = 'command';
        $pattern = '#(^\/)(\w+).*#';
        preg_match($pattern, $rawtext, $matches);
        $command = isset($matches[2]) ? $matches[2] : false;
                switch ($command) {
            case 'me':
                $text = '*** <b>'.$sender->firstname.' '.substr($rawtext, 4).'</b>';
                $outmain = format_text($text, FORMAT_MOODLE, $options, $courseid);
                break;
            default:
                                $special = false;
                break;
        }
    } else if (preg_match($patternto, $rawtext)) {
        $special = true;
        $result->type = 'dialogue';
        $matches = array();
        preg_match($patternto, $rawtext, $matches);
        if (isset($matches[1]) && isset($matches[2])) {
            $text = format_text($matches[2], FORMAT_MOODLE, $options, $courseid);
            $outmain = $sender->firstname.' <b>'.get_string('saidto', 'chat').'</b> <i>'.$matches[1].'</i>: '.$text;
        } else {
                        $special = false;
        }
    }

    if (!$special) {
        $text = format_text($rawtext, FORMAT_MOODLE, $options, $courseid);
        $outmain = $text;
    }

    $result->text = strip_tags($outtime.': '.$outmain);

    $mymessageclass = '';
    if ($sender->id == $USER->id) {
        $mymessageclass = 'chat-message-mymessage';
    }

    $senderprofile = $CFG->wwwroot.'/user/view.php?id='.$sender->id.'&amp;course='.$courseid;
    $usermessage = new user_message($senderprofile, fullname($sender), $message->picture,
                                    $mymessageclass, $outtime, $outmain, $theme);

    $output = $PAGE->get_renderer('mod_chat');
    $result->html = $output->render($usermessage);

        if (('' === $outmain) && $special) {
        return false;
    } else {
        return $result;
    }
}


function chat_format_userlist($users, $course) {
    global $CFG, $DB, $COURSE, $OUTPUT;
    $result = array();
    foreach ($users as $user) {
        $item = array();
        $item['name'] = fullname($user);
        $item['url'] = $CFG->wwwroot.'/user/view.php?id='.$user->id.'&amp;course='.$course->id;
        $item['picture'] = $OUTPUT->user_picture($user);
        $item['id'] = $user->id;
        $result[] = $item;
    }
    return $result;
}


function chat_print_error($level, $msg) {
    header('Content-Length: ' . ob_get_length() );
    $error = new stdClass();
    $error->level = $level;
    $error->msg   = $msg;
    $response['error'] = $error;
    echo json_encode($response);
    ob_end_flush();
    exit;
}


function chat_get_view_actions() {
    return array('view', 'view all', 'report');
}


function chat_get_post_actions() {
    return array('talk');
}


function chat_print_overview($courses, &$htmlarray) {
    global $USER, $CFG;

    if (empty($courses) || !is_array($courses) || count($courses) == 0) {
        return array();
    }

    if (!$chats = get_all_instances_in_courses('chat', $courses)) {
        return;
    }

    $strchat = get_string('modulename', 'chat');
    $strnextsession  = get_string('nextsession', 'chat');

    foreach ($chats as $chat) {
        if ($chat->chattime and $chat->schedule) {              $str = '<div class="chat overview"><div class="name">'.
                   $strchat.': <a '.($chat->visible ? '' : ' class="dimmed"').
                   ' href="'.$CFG->wwwroot.'/mod/chat/view.php?id='.$chat->coursemodule.'">'.
                   $chat->name.'</a></div>';
            $str .= '<div class="info">'.$strnextsession.': '.userdate($chat->chattime).'</div></div>';

            if (empty($htmlarray[$chat->course]['chat'])) {
                $htmlarray[$chat->course]['chat'] = $str;
            } else {
                $htmlarray[$chat->course]['chat'] .= $str;
            }
        }
    }
}



function chat_reset_course_form_definition(&$mform) {
    $mform->addElement('header', 'chatheader', get_string('modulenameplural', 'chat'));
    $mform->addElement('advcheckbox', 'reset_chat', get_string('removemessages', 'chat'));
}


function chat_reset_course_form_defaults($course) {
    return array('reset_chat' => 1);
}


function chat_reset_userdata($data) {
    global $CFG, $DB;

    $componentstr = get_string('modulenameplural', 'chat');
    $status = array();

    if (!empty($data->reset_chat)) {
        $chatessql = "SELECT ch.id
                        FROM {chat} ch
                       WHERE ch.course=?";
        $params = array($data->courseid);

        $DB->delete_records_select('chat_messages', "chatid IN ($chatessql)", $params);
        $DB->delete_records_select('chat_messages_current', "chatid IN ($chatessql)", $params);
        $DB->delete_records_select('chat_users', "chatid IN ($chatessql)", $params);
        $status[] = array('component' => $componentstr, 'item' => get_string('removemessages', 'chat'), 'error' => false);
    }

        if ($data->timeshift) {
        shift_course_mod_dates('chat', array('chattime'), $data->timeshift, $data->courseid);
        $status[] = array('component' => $componentstr, 'item' => get_string('datechanged'), 'error' => false);
    }

    return $status;
}


function chat_get_extra_capabilities() {
    return array('moodle/site:accessallgroups', 'moodle/site:viewfullnames');
}



function chat_supports($feature) {
    switch($feature) {
        case FEATURE_GROUPS:
            return true;
        case FEATURE_GROUPINGS:
            return true;
        case FEATURE_MOD_INTRO:
            return true;
        case FEATURE_BACKUP_MOODLE2:
            return true;
        case FEATURE_COMPLETION_TRACKS_VIEWS:
            return true;
        case FEATURE_GRADE_HAS_GRADE:
            return false;
        case FEATURE_GRADE_OUTCOMES:
            return true;
        case FEATURE_SHOW_DESCRIPTION:
            return true;
        default:
            return null;
    }
}

function chat_extend_navigation($navigation, $course, $module, $cm) {
    global $CFG;

    $currentgroup = groups_get_activity_group($cm, true);

    if (has_capability('mod/chat:chat', context_module::instance($cm->id))) {
        $strenterchat    = get_string('enterchat', 'chat');

        $target = $CFG->wwwroot.'/mod/chat/';
        $params = array('id' => $cm->instance);

        if ($currentgroup) {
            $params['groupid'] = $currentgroup;
        }

        $links = array();

        $url = new moodle_url($target.'gui_'.$CFG->chat_method.'/index.php', $params);
        $action = new popup_action('click', $url, 'chat'.$course->id.$cm->instance.$currentgroup,
                                   array('height' => 500, 'width' => 700));
        $links[] = new action_link($url, $strenterchat, $action);

        $url = new moodle_url($target.'gui_basic/index.php', $params);
        $action = new popup_action('click', $url, 'chat'.$course->id.$cm->instance.$currentgroup,
                                   array('height' => 500, 'width' => 700));
        $links[] = new action_link($url, get_string('noframesjs', 'message'), $action);

        foreach ($links as $link) {
            $navigation->add($link->text, $link, navigation_node::TYPE_SETTING, null , null, new pix_icon('i/group' , ''));
        }
    }

    $chatusers = chat_get_users($cm->instance, $currentgroup, $cm->groupingid);
    if (is_array($chatusers) && count($chatusers) > 0) {
        $users = $navigation->add(get_string('currentusers', 'chat'));
        foreach ($chatusers as $chatuser) {
            $userlink = new moodle_url('/user/view.php', array('id' => $chatuser->id, 'course' => $course->id));
            $users->add(fullname($chatuser).' '.format_time(time() - $chatuser->lastmessageping),
                        $userlink, navigation_node::TYPE_USER, null, null, new pix_icon('i/user', ''));
        }
    }
}


function chat_extend_settings_navigation(settings_navigation $settings, navigation_node $chatnode) {
    global $DB, $PAGE, $USER;
    $chat = $DB->get_record("chat", array("id" => $PAGE->cm->instance));

    if ($chat->chattime && $chat->schedule) {
        $nextsessionnode = $chatnode->add(get_string('nextsession', 'chat').
                                          ': '.userdate($chat->chattime).
                                          ' ('.usertimezone($USER->timezone).')');
        $nextsessionnode->add_class('note');
    }

    $currentgroup = groups_get_activity_group($PAGE->cm, true);
    if ($currentgroup) {
        $groupselect = " AND groupid = '$currentgroup'";
    } else {
        $groupselect = '';
    }

    if ($chat->studentlogs || has_capability('mod/chat:readlog', $PAGE->cm->context)) {
        if ($DB->get_records_select('chat_messages', "chatid = ? $groupselect", array($chat->id))) {
            $chatnode->add(get_string('viewreport', 'chat'), new moodle_url('/mod/chat/report.php', array('id' => $PAGE->cm->id)));
        }
    }
}


function chat_user_logout(\core\event\user_loggedout $event) {
    global $DB;
    $DB->delete_records('chat_users', array('userid' => $event->objectid));
}


function chat_page_type_list($pagetype, $parentcontext, $currentcontext) {
    $modulepagetype = array('mod-chat-*' => get_string('page-mod-chat-x', 'chat'));
    return $modulepagetype;
}


function chat_get_latest_messages($chatuser, $chatlasttime) {
    global $DB;

    $params = array('groupid' => $chatuser->groupid, 'chatid' => $chatuser->chatid, 'lasttime' => $chatlasttime);

    $groupselect = $chatuser->groupid ? " AND (groupid=" . $chatuser->groupid . " OR groupid=0) " : "";

    return $DB->get_records_select('chat_messages_current', 'chatid = :chatid AND timestamp > :lasttime ' . $groupselect,
                                    $params, 'timestamp ASC');
}


function chat_view($chat, $course, $cm, $context) {

        $params = array(
        'context' => $context,
        'objectid' => $chat->id
    );

    $event = \mod_chat\event\course_module_viewed::create($params);
    $event->add_record_snapshot('course_modules', $cm);
    $event->add_record_snapshot('course', $course);
    $event->add_record_snapshot('chat', $chat);
    $event->trigger();

        $completion = new completion_info($course);
    $completion->set_module_viewed($cm);
}
