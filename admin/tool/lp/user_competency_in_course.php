<?php



require(__DIR__ . '/../../../config.php');

$userid = optional_param('userid', 0, PARAM_INT);
$competencyid = required_param('competencyid', PARAM_INT);
$courseid = required_param('courseid', PARAM_INT);

require_login(null, false);
if (isguestuser()) {
    throw new require_login_exception('Guests are not allowed here.');
}
\core_competency\api::require_enabled();

$course = $DB->get_record('course', array('id' => $courseid));
$context = context_course::instance($courseid);
$currentgroup = groups_get_course_group($course, true);
if (empty($userid)) {
    $gradable = get_enrolled_users($context, 'moodle/competency:coursecompetencygradable', $currentgroup, 'u.id', null, 0, 1);
    if (empty($gradable)) {
        $userid = 0;
    } else {
        $userid = array_pop($gradable)->id;
    }
} else {
    $gradable = get_enrolled_users($context, 'moodle/competency:coursecompetencygradable', $currentgroup, 'u.id');
    if (count($gradable) == 0) {
        $userid = 0;
    } else if (!in_array($userid, array_keys($gradable))) {
        $userid = array_shift($gradable)->id;
    }
}

$params = array('userid' => $userid, 'competencyid' => $competencyid, 'courseid' => $courseid);
$url = new moodle_url('/admin/tool/lp/user_competency_in_course.php', $params);

if ($userid > 0) {
    $usercontext = context_user::instance($userid);
    $user = $DB->get_record('user', array('id' => $userid));
}
$competency = new \core_competency\competency($competencyid);

if ($userid > 0) {
    $usercompetencycourses = \core_competency\api::list_user_competencies_in_course($courseid, $userid);
}
$subtitle = $competency->get_shortname() . ' <em>' . $competency->get_idnumber() . '</em>';

list($title, $subtitle) = \tool_lp\page_helper::setup_for_course($url, $course, $subtitle);

$output = $PAGE->get_renderer('tool_lp');
if ($userid > 0) {
    $userheading = array(
        'heading' => fullname($user),
        'user' => $user,
        'usercontext' => $usercontext
    );
}
echo $output->header();
if ($userid > 0) {
    echo $OUTPUT->context_header($userheading, 3);
}

$baseurl = new moodle_url('/admin/tool/lp/user_competency_in_course.php');
$nav = new \tool_lp\output\user_competency_course_navigation($userid, $competencyid, $courseid, $baseurl);
echo $output->render($nav);
if ($userid > 0) {
    $page = new \tool_lp\output\user_competency_summary_in_course($userid, $competencyid, $courseid);
    echo $output->render($page);

        $uc = \core_competency\api::get_user_competency_in_course($courseid, $userid, $competencyid);
    \core_competency\api::user_competency_viewed_in_course($uc, $courseid);
} else {
    echo $output->container('', 'clearfix');
    echo $output->notify_problem(get_string('noparticipants', 'tool_lp'));
}

echo $output->footer();
