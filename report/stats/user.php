<?php



require('../../config.php');
require_once($CFG->dirroot.'/report/stats/locallib.php');

$userid   = required_param('id', PARAM_INT);
$courseid = required_param('course', PARAM_INT);

$user = $DB->get_record('user', array('id'=>$userid, 'deleted'=>0), '*', MUST_EXIST);
$course = $DB->get_record('course', array('id'=>$courseid), '*', MUST_EXIST);

$coursecontext   = context_course::instance($course->id);
$personalcontext = context_user::instance($user->id);

$pageheading = $course->fullname;
$userfullname = fullname($user);
if ($courseid == SITEID) {
    $PAGE->set_context($personalcontext);
    $pageheading = $userfullname;
}

if ($USER->id != $user->id and has_capability('moodle/user:viewuseractivitiesreport', $personalcontext)
        and !is_enrolled($coursecontext, $USER) and is_enrolled($coursecontext, $user)) {
        require_login();
    $PAGE->set_course($course);
} else {
    require_login($course);
}

if (!report_stats_can_access_user_report($user, $course, true)) {
        print_error('nocapability', 'report_stats');
}

$stractivityreport = get_string('activityreport');

$PAGE->set_pagelayout('report');
$PAGE->set_url('/report/stats/user.php', array('id'=>$user->id, 'course'=>$course->id));
$PAGE->navigation->extend_for_user($user);
$PAGE->navigation->set_userid_for_parent_checks($user->id); $navigationnode = array(
        'name' => get_string('stats'),
        'url' => new moodle_url('/report/stats/user.php', array('id' => $user->id, 'course' => $course->id))
    );
$PAGE->add_report_nodes($user->id, $navigationnode);

$PAGE->set_title("$course->shortname: $stractivityreport");
$PAGE->set_heading($pageheading);
echo $OUTPUT->header();
if ($courseid != SITEID) {
    echo $OUTPUT->context_header(
            array(
            'heading' => $userfullname,
            'user' => $user,
            'usercontext' => $personalcontext
        ), 2);
}

$event = \report_stats\event\user_report_viewed::create(array('context' => $coursecontext, 'relateduserid' => $user->id));
$event->trigger();

if (empty($CFG->enablestats)) {
    print_error('statsdisable', 'error');
}

$statsstatus = stats_check_uptodate($course->id);
if ($statsstatus !== NULL) {
    echo $OUTPUT->notification($statsstatus);
}

$earliestday   = $DB->get_field_sql('SELECT MIN(timeend) FROM {stats_user_daily}');
$earliestweek  = $DB->get_field_sql('SELECT MIN(timeend) FROM {stats_user_weekly}');
$earliestmonth = $DB->get_field_sql('SELECT MIN(timeend) FROM {stats_user_monthly}');

if (empty($earliestday)) {
    $earliestday = time();
}
if (empty($earliestweek)) {
    $earliestweek = time();
}
if (empty($earliestmonth)) {
    $earliestmonth = time();
}

$now = stats_get_base_daily();
$lastweekend = stats_get_base_weekly();
$lastmonthend = stats_get_base_monthly();

$timeoptions = stats_get_time_options($now,$lastweekend,$lastmonthend,$earliestday,$earliestweek,$earliestmonth);

if (empty($timeoptions)) {
    print_error('nostatstodisplay', '', $CFG->wwwroot.'/course/user.php?id='.$course->id.'&user='.$user->id.'&mode=outline');
}

$timekeys = array_keys($timeoptions);
$time = array_pop($timekeys);

$param = stats_get_parameters($time,STATS_REPORT_USER_VIEW,$course->id,STATS_MODE_DETAILED);
$params = $param->params;

$param->table = 'user_'.$param->table;

$sql = 'SELECT id, timeend,'.$param->fields.' FROM {stats_'.$param->table.'} WHERE '
.(($course->id == SITEID) ? '' : ' courseid = '.$course->id.' AND ')
    .' userid = '.$user->id.' AND timeend >= '.$param->timeafter .$param->extras
    .' ORDER BY timeend DESC';
$stats = $DB->get_records_sql($sql, $params); 
if (empty($stats)) {
    print_error('nostatstodisplay', '', $CFG->wwwroot.'/course/user.php?id='.$course->id.'&user='.$user->id.'&mode=outline');
}

echo '<center><img src="'.$CFG->wwwroot.'/report/stats/graph.php?mode='.STATS_MODE_DETAILED.'&course='.$course->id.'&time='.$time.'&report='.STATS_REPORT_USER_VIEW.'&userid='.$user->id.'" alt="'.get_string('statisticsgraph').'" /></center>';

$stats = stats_fix_zeros($stats,$param->timeafter,$param->table,(!empty($param->line2)),(!empty($param->line3)));

$table = new html_table();
$table->align = array('left','center','center','center');
$param->table = str_replace('user_','',$param->table);
switch ($param->table) {
    case 'daily'  : $period = get_string('day'); break;
    case 'weekly' : $period = get_string('week'); break;
    case 'monthly': $period = get_string('month', 'form'); break;
    default : $period = '';
}
$table->head = array(get_string('periodending','moodle',$period),$param->line1,$param->line2,$param->line3);
foreach ($stats as $stat) {
    if (!empty($stat->zerofixed)) {          continue;
    }
    $a = array(userdate($stat->timeend,get_string('strftimedate'),$CFG->timezone),$stat->line1);
    $a[] = $stat->line2;
    $a[] = $stat->line3;
    $table->data[] = $a;
}
echo html_writer::table($table);


echo $OUTPUT->footer();
