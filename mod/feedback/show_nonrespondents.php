<?php



require_once("../../config.php");
require_once("lib.php");
require_once($CFG->libdir.'/tablelib.php');

$id = required_param('id', PARAM_INT);
$subject = optional_param('subject', '', PARAM_CLEANHTML);
$message = optional_param('message', '', PARAM_CLEANHTML);
$format = optional_param('format', FORMAT_MOODLE, PARAM_INT);
$messageuser = optional_param_array('messageuser', false, PARAM_INT);
$action = optional_param('action', '', PARAM_ALPHA);
$perpage = optional_param('perpage', FEEDBACK_DEFAULT_PAGE_COUNT, PARAM_INT);  $showall = optional_param('showall', false, PARAM_INT);  $current_tab = 'nonrespondents';


list ($course, $cm) = get_course_and_cm_from_cmid($id, 'feedback');
if (! $feedback = $DB->get_record("feedback", array("id"=>$cm->instance))) {
    print_error('invalidcoursemodule');
}

if ($feedback->anonymous != FEEDBACK_ANONYMOUS_NO OR $feedback->course == SITEID) {
    print_error('error');
}

$url = new moodle_url('/mod/feedback/show_nonrespondents.php', array('id'=>$cm->id));

$PAGE->set_url($url);

$context = context_module::instance($cm->id);

$coursecontext = context_course::instance($course->id);

require_login($course, true, $cm);

if (($formdata = data_submitted()) AND !confirm_sesskey()) {
    print_error('invalidsesskey');
}

require_capability('mod/feedback:viewreports', $context);

if ($action == 'sendmessage' AND has_capability('moodle/course:bulkmessaging', $coursecontext)) {
    $shortname = format_string($course->shortname,
                            true,
                            array('context' => $coursecontext));
    $strfeedbacks = get_string("modulenameplural", "feedback");

    $htmlmessage = "<body id=\"email\">";

    $link1 = $CFG->wwwroot.'/course/view.php?id='.$course->id;
    $link2 = $CFG->wwwroot.'/mod/feedback/index.php?id='.$course->id;
    $link3 = $CFG->wwwroot.'/mod/feedback/view.php?id='.$cm->id;

    $htmlmessage .= '<div class="navbar">'.
    '<a target="_blank" href="'.$link1.'">'.$shortname.'</a> &raquo; '.
    '<a target="_blank" href="'.$link2.'">'.$strfeedbacks.'</a> &raquo; '.
    '<a target="_blank" href="'.$link3.'">'.format_string($feedback->name, true).'</a>'.
    '</div>';

    $htmlmessage .= $message;
    $htmlmessage .= '</body>';

    $good = 1;
    if (is_array($messageuser)) {
        foreach ($messageuser as $userid) {
            $senduser = $DB->get_record('user', array('id'=>$userid));
            $eventdata = new stdClass();
            $eventdata->name             = 'message';
            $eventdata->component        = 'mod_feedback';
            $eventdata->userfrom         = $USER;
            $eventdata->userto           = $senduser;
            $eventdata->subject          = $subject;
            $eventdata->fullmessage      = html_to_text($htmlmessage);
            $eventdata->fullmessageformat = FORMAT_PLAIN;
            $eventdata->fullmessagehtml  = $htmlmessage;
            $eventdata->smallmessage     = '';
            $good = $good && message_send($eventdata);
        }
        if (!empty($good)) {
            $msg = $OUTPUT->heading(get_string('messagedselectedusers'));
        } else {
            $msg = $OUTPUT->heading(get_string('messagedselectedusersfailed'));
        }
        redirect($url, $msg, 4);
        exit;
    }
}


$PAGE->set_heading($course->fullname);
$PAGE->set_title($feedback->name);
echo $OUTPUT->header();
echo $OUTPUT->heading(format_string($feedback->name));

require('tabs.php');


if (isset($cm->groupmode) && empty($course->groupmodeforce)) {
    $groupmode =  $cm->groupmode;
} else {
    $groupmode = $course->groupmode;
}

$groupselect = groups_print_activity_menu($cm, $url->out(), true);
$mygroupid = groups_get_activity_group($cm);

$baseurl = new moodle_url('/mod/feedback/show_nonrespondents.php');
$baseurl->params(array('id'=>$id, 'showall'=>$showall));

$tablecolumns = array('userpic', 'fullname', 'status');
$tableheaders = array(get_string('userpic'), get_string('fullnameuser'), get_string('status'));

if (has_capability('moodle/course:bulkmessaging', $coursecontext)) {
    $tablecolumns[] = 'select';
    $tableheaders[] = get_string('select');
}

$table = new flexible_table('feedback-shownonrespondents-'.$course->id);

$table->define_columns($tablecolumns);
$table->define_headers($tableheaders);
$table->define_baseurl($baseurl);

$table->sortable(true, 'lastname', SORT_DESC);
$table->set_attribute('cellspacing', '0');
$table->set_attribute('id', 'showentrytable');
$table->set_attribute('class', 'generaltable generalbox');
$table->set_control_variables(array(
            TABLE_VAR_SORT    => 'ssort',
            TABLE_VAR_IFIRST  => 'sifirst',
            TABLE_VAR_ILAST   => 'silast',
            TABLE_VAR_PAGE    => 'spage'
            ));

$table->no_sorting('select');
$table->no_sorting('status');

$table->setup();

if ($table->get_sql_sort()) {
    $sort = $table->get_sql_sort();
} else {
    $sort = '';
}

if ($groupmode > 0) {
    if ($mygroupid > 0) {
        $usedgroupid = $mygroupid;
    } else {
        $usedgroupid = false;
    }
} else {
    $usedgroupid = false;
}

$matchcount = feedback_count_incomplete_users($cm, $usedgroupid);
$table->initialbars(false);

if ($showall) {
    $startpage = false;
    $pagecount = false;
} else {
    $table->pagesize($perpage, $matchcount);
    $startpage = $table->get_page_start();
    $pagecount = $table->get_page_size();
}

$students = feedback_get_incomplete_users($cm, $usedgroupid, $sort, $startpage, $pagecount);
echo $OUTPUT->heading(get_string('non_respondents_students', 'feedback', $matchcount), 4);
echo isset($groupselect) ? $groupselect : '';
echo '<div class="clearer"></div>';

if (!$students) {
    echo $OUTPUT->notification(get_string('noexistingparticipants', 'enrol'));
} else {

    if (has_capability('moodle/course:bulkmessaging', $coursecontext)) {
        echo '<form class="mform" action="show_nonrespondents.php" method="post" id="feedback_sendmessageform">';
    }
    foreach ($students as $student) {
        $user = $DB->get_record('user', array('id'=>$student));
                $profile_url = $CFG->wwwroot.'/user/view.php?id='.$user->id.'&amp;course='.$course->id;
        $profilelink = '<strong><a href="'.$profile_url.'">'.fullname($user).'</a></strong>';
        $data = array ($OUTPUT->user_picture($user, array('courseid'=>$course->id)), $profilelink);

        if ($DB->record_exists('feedback_completedtmp', array('userid' => $user->id, 'feedback' => $feedback->id))) {
            $data[] = get_string('started', 'feedback');
        } else {
            $data[] = get_string('not_started', 'feedback');
        }

                if (has_capability('moodle/course:bulkmessaging', $coursecontext)) {
            $data[] = '<input type="checkbox" class="usercheckbox" name="messageuser[]" value="'.$user->id.'" />';
        }
        $table->add_data($data);
    }
    $table->print_html();

    $allurl = new moodle_url($baseurl);

    if ($showall) {
        $allurl->param('showall', 0);
        echo $OUTPUT->container(html_writer::link($allurl, get_string('showperpage', '', FEEDBACK_DEFAULT_PAGE_COUNT)),
                                    array(), 'showall');

    } else if ($matchcount > 0 && $perpage < $matchcount) {
        $allurl->param('showall', 1);
        echo $OUTPUT->container(html_writer::link($allurl, get_string('showall', '', $matchcount)), array(), 'showall');
    }
    if (has_capability('moodle/course:bulkmessaging', $coursecontext)) {
        echo '<div class="buttons"><br />';
        echo '<input type="button" id="checkall" value="'.get_string('selectall').'" /> ';
        echo '<input type="button" id="checknone" value="'.get_string('deselectall').'" /> ';
        echo '</div>';
        echo '<fieldset class="clearfix">';
        echo '<legend class="ftoggler">'.get_string('send_message', 'feedback').'</legend>';
        echo '<div>';
        echo '<label for="feedback_subject">'.get_string('subject', 'feedback').'&nbsp;</label>';
        echo '<input type="text" id="feedback_subject" size="50" maxlength="255" name="subject" value="'.$subject.'" />';
        echo '</div>';
        print_textarea(true, 15, 25, 30, 10, "message", $message);
        print_string('formathtml');
        echo '<input type="hidden" name="format" value="'.FORMAT_HTML.'" />';
        echo '<br /><div class="buttons">';
        echo '<input type="submit" name="send_message" value="'.get_string('send', 'feedback').'" />';
        echo '</div>';
        echo '<input type="hidden" name="sesskey" value="'.sesskey().'" />';
        echo '<input type="hidden" name="action" value="sendmessage" />';
        echo '<input type="hidden" name="id" value="'.$id.'" />';
        echo '</fieldset>';
        echo '</form>';
                $module = array('name'=>'mod_feedback', 'fullpath'=>'/mod/feedback/feedback.js');
        $PAGE->requires->js_init_call('M.mod_feedback.init_sendmessage', null, false, $module);
    }
}


echo $OUTPUT->footer();

