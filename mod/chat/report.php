<?php


require_once('../../config.php');
require_once('lib.php');

$id            = required_param('id', PARAM_INT);
$start         = optional_param('start', 0, PARAM_INT);   $end           = optional_param('end', 0, PARAM_INT);     $deletesession = optional_param('deletesession', 0, PARAM_BOOL);
$confirmdelete = optional_param('confirmdelete', 0, PARAM_BOOL);
$showall      = optional_param('show_all', 0, PARAM_BOOL);

$url = new moodle_url('/mod/chat/report.php', array('id' => $id));
if ($start !== 0) {
    $url->param('start', $start);
}
if ($end !== 0) {
    $url->param('end', $end);
}
if ($deletesession !== 0) {
    $url->param('deletesession', $deletesession);
}
if ($confirmdelete !== 0) {
    $url->param('confirmdelete', $confirmdelete);
}
$PAGE->set_url($url);

if (! $cm = get_coursemodule_from_id('chat', $id)) {
    print_error('invalidcoursemodule');
}
if (! $chat = $DB->get_record('chat', array('id' => $cm->instance))) {
    print_error('invalidcoursemodule');
}
if (! $course = $DB->get_record('course', array('id' => $chat->course))) {
    print_error('coursemisconf');
}

$context = context_module::instance($cm->id);
$PAGE->set_context($context);
$PAGE->set_heading($course->fullname);

require_login($course, false, $cm);

if (empty($chat->studentlogs) && !has_capability('mod/chat:readlog', $context)) {
    notice(get_string('nopermissiontoseethechatlog', 'chat'));
}

$params = array(
    'context' => $context,
    'objectid' => $chat->id,
    'other' => array(
        'start' => $start,
        'end' => $end
    )
);
$event = \mod_chat\event\sessions_viewed::create($params);
$event->add_record_snapshot('chat', $chat);
$event->trigger();

$strchats         = get_string('modulenameplural', 'chat');
$strchat          = get_string('modulename', 'chat');
$strchatreport    = get_string('chatreport', 'chat');
$strseesession    = get_string('seesession', 'chat');
$strdeletesession = get_string('deletesession', 'chat');

$navlinks = array();

$canexportsess = has_capability('mod/chat:exportsession', $context);


if ($start and $end and !$confirmdelete) {       $PAGE->navbar->add($strchatreport);
    $PAGE->set_title(format_string($chat->name).": $strchatreport");
    echo $OUTPUT->header();
    echo $OUTPUT->heading(format_string($chat->name), 2);

        $groupmode = groups_get_activity_groupmode($cm);
    $currentgroup = groups_get_activity_group($cm, true);
    groups_print_activity_menu($cm, $CFG->wwwroot . "/mod/chat/report.php?id=$cm->id");

    $params = array('currentgroup' => $currentgroup, 'chatid' => $chat->id, 'start' => $start, 'end' => $end);

            if ($currentgroup) {
        $groupselect = " AND (groupid = :currentgroup OR groupid = 0)";
    } else {
        $groupselect = "";
    }

    if ($deletesession and has_capability('mod/chat:deletelog', $context)) {
        echo $OUTPUT->confirm(get_string('deletesessionsure', 'chat'),
                     "report.php?id=$cm->id&deletesession=1&confirmdelete=1&start=$start&end=$end",
                     "report.php?id=$cm->id");
    }

    if (!$messages = $DB->get_records_select('chat_messages',
                                             "chatid = :chatid AND timestamp >= :start AND timestamp <= :end $groupselect",
                                             $params,
                                             "timestamp ASC")) {

        echo $OUTPUT->heading(get_string('nomessages', 'chat'));

    } else {
        echo '<p class="boxaligncenter">'.userdate($start).' --> '. userdate($end).'</p>';

        echo $OUTPUT->box_start('center');
        $participates = array();
        foreach ($messages as $message) {              if (!isset($participates[$message->userid])) {
                $participates[$message->userid] = true;
            }
            $formatmessage = chat_format_message($message, $course->id, $USER);
            if (isset($formatmessage->html)) {
                echo $formatmessage->html;
            }
        }
        $participatedcap = array_key_exists($USER->id, $participates)
                           && has_capability('mod/chat:exportparticipatedsession', $context);

        if (!empty($CFG->enableportfolios) && ($canexportsess || $participatedcap)) {
            require_once($CFG->libdir . '/portfoliolib.php');
            $buttonoptions  = array(
                'id'    => $cm->id,
                'start' => $start,
                'end'   => $end,
            );
            $button = new portfolio_add_button();
            $button->set_callback_options('chat_portfolio_caller', $buttonoptions, 'mod_chat');
            $button->render();
        }
        echo $OUTPUT->box_end();
    }

    if (!$deletesession or !has_capability('mod/chat:deletelog', $context)) {
        echo $OUTPUT->continue_button("report.php?id=$cm->id");
    }

    echo $OUTPUT->footer();
    exit;
}


$PAGE->navbar->add($strchatreport);
$PAGE->set_title(format_string($chat->name).": $strchatreport");
echo $OUTPUT->header();

echo $OUTPUT->heading(format_string($chat->name).': '.get_string('sessions', 'chat'), 2);

if ($groupmode = groups_get_activity_groupmode($cm)) {       $currentgroup = groups_get_activity_group($cm, true);
    groups_print_activity_menu($cm, $CFG->wwwroot . "/mod/chat/report.php?id=$cm->id");
} else {
    $currentgroup = false;
}

$params = array('currentgroup' => $currentgroup, 'chatid' => $chat->id, 'start' => $start, 'end' => $end);

if (!empty($currentgroup)) {
    $groupselect = " AND (groupid = :currentgroup OR groupid = 0)";
} else {
    $groupselect = "";
}


if ($deletesession and has_capability('mod/chat:deletelog', $context)
    and $confirmdelete and $start and $end and confirm_sesskey()) {

    $DB->delete_records_select('chat_messages', "chatid = :chatid AND timestamp >= :start AND
                                                 timestamp <= :end $groupselect", $params);
    $strdeleted  = get_string('deleted');
    echo $OUTPUT->notification("$strdeleted: ".userdate($start).' --> '. userdate($end));
    unset($deletesession);
}

if (empty($messages)) {       if (!$messages = $DB->get_records_select('chat_messages', "chatid = :chatid $groupselect", $params, "timestamp DESC")) {
        echo $OUTPUT->heading(get_string('nomessages', 'chat'), 3);
        echo $OUTPUT->footer();
        exit;
    }
}

if ($showall) {
    $headingstr = get_string('listing_all_sessions', 'chat') . '&nbsp;';
    $headingstr .= html_writer::link("report.php?id={$cm->id}&show_all=0", get_string('list_complete_sessions', 'chat'));
    echo  $OUTPUT->heading($headingstr, 3);
}


$sessiongap        = 5 * 60;    $sessionend        = 0;
$sessionstart      = 0;
$sessionusers      = array();
$lasttime          = 0;
$completesessions  = 0;

$messagesleft = count($messages);

foreach ($messages as $message) {  
    $messagesleft --;              
    if (!$lasttime) {
        $lasttime = $message->timestamp;
    }
    if (!$sessionend) {
        $sessionend = $message->timestamp;
    }
    if ((($lasttime - $message->timestamp) < $sessiongap) and $messagesleft) {          if ($message->userid and !$message->system) {                   if (empty($sessionusers[$message->userid])) {
                $sessionusers[$message->userid] = 1;
            } else {
                $sessionusers[$message->userid] ++;
            }
        }
    } else {
        $sessionstart = $lasttime;

        $iscomplete = ($sessionend - $sessionstart > 60 and count($sessionusers) > 1);
        if ($showall or $iscomplete) {

            echo '<p align="center">'.userdate($sessionstart).' --> '. userdate($sessionend).'</p>';

            echo $OUTPUT->box_start();

            arsort($sessionusers);
            foreach ($sessionusers as $sessionuser => $usermessagecount) {
                if ($user = $DB->get_record('user', array('id' => $sessionuser))) {
                    $OUTPUT->user_picture($user, array('courseid' => $course->id));
                    echo '&nbsp;'.fullname($user, true);                     echo "&nbsp;($usermessagecount)<br />";
                }
            }

            echo '<p align="right">';
            echo "<a href=\"report.php?id=$cm->id&amp;start=$sessionstart&amp;end=$sessionend\">$strseesession</a>";
            $participatedcap = (array_key_exists($USER->id, $sessionusers)
                               && has_capability('mod/chat:exportparticipatedsession', $context));
            if (!empty($CFG->enableportfolios) && ($canexportsess || $participatedcap)) {
                require_once($CFG->libdir . '/portfoliolib.php');
                $buttonoptions  = array(
                    'id'    => $cm->id,
                    'start' => $sessionstart,
                    'end'   => $sessionend,
                );
                $button = new portfolio_add_button();
                $button->set_callback_options('chat_portfolio_caller', $buttonoptions, 'mod_chat');
                $portfoliobutton = $button->to_html(PORTFOLIO_ADD_TEXT_LINK);
                if (!empty($portfoliobutton)) {
                    echo '<br />' . $portfoliobutton;
                }
            }
            if (has_capability('mod/chat:deletelog', $context)) {
                $deleteurl = "report.php?id=$cm->id&amp;start=$sessionstart&amp;end=$sessionend&amp;deletesession=1";
                echo "<br /><a href=\"$deleteurl\">$strdeletesession</a>";
            }
            echo '</p>';
            echo $OUTPUT->box_end();
        }
        if ($iscomplete) {
            $completesessions++;
        }

        $sessionend = $message->timestamp;
        $sessionusers = array();
        $sessionusers[$message->userid] = 1;
    }
    $lasttime = $message->timestamp;
}

if (!empty($CFG->enableportfolios) && $canexportsess) {
    require_once($CFG->libdir . '/portfoliolib.php');
    $button = new portfolio_add_button();
    $button->set_callback_options('chat_portfolio_caller', array('id' => $cm->id), 'mod_chat');
    $button->render(null, get_string('addalltoportfolio', 'portfolio'));
}


if (!$showall and $completesessions == 0) {
    echo html_writer::start_tag('p');
    echo get_string('no_complete_sessions_found', 'chat') . '&nbsp;';
    echo html_writer::link('report.php?id='.$cm->id.'&show_all=1', get_string('list_all_sessions', 'chat'));
    echo html_writer::end_tag('p');
}

echo $OUTPUT->footer();
