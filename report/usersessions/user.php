<?php



require(__DIR__ . '/../../config.php');
require_once(__DIR__ . '/locallib.php');

require_login(null, false);

if (isguestuser()) {
        redirect(new moodle_url('/'));
    die;
}
if (\core\session\manager::is_loggedinas()) {
        redirect(new moodle_url('/user/index.php'));
    die;
}

$context = context_user::instance($USER->id);
require_capability('report/usersessions:manageownsessions', $context);

$delete = optional_param('delete', 0, PARAM_INT);

$PAGE->set_url('/report/usersessions/user.php');
$PAGE->set_context($context);
$PAGE->set_title(get_string('navigationlink', 'report_usersessions'));
$PAGE->set_heading(fullname($USER));
$PAGE->set_pagelayout('admin');

if ($delete and confirm_sesskey()) {
    report_usersessions_kill_session($delete);
    redirect($PAGE->url);
}

$PAGE->add_report_nodes($USER->id, array(
        'name' => get_string('navigationlink', 'report_usersessions'),
        'url' => new moodle_url('/report/usersessions/user.php')
    ));

echo $OUTPUT->header();
echo $OUTPUT->heading(get_string('mysessions', 'report_usersessions'));

$data = array();
$sql = "SELECT id, timecreated, timemodified, firstip, lastip, sid
          FROM {sessions}
         WHERE userid = :userid
      ORDER BY timemodified DESC";
$params = array('userid' => $USER->id, 'sid' => session_id());

$sessions = $DB->get_records_sql($sql, $params);
foreach ($sessions as $session) {
    if ($session->sid === $params['sid']) {
        $lastaccess = get_string('thissession', 'report_usersessions');
        $deletelink = '';

    } else {
        $lastaccess = report_usersessions_format_duration(time() - $session->timemodified);
        $url = new moodle_url($PAGE->url, array('delete' => $session->id, 'sesskey' => sesskey()));
        $deletelink = html_writer::link($url, get_string('logout'));
    }
    $data[] = array(userdate($session->timecreated), $lastaccess, report_usersessions_format_ip($session->lastip), $deletelink);
}

$table = new html_table();
$table->head  = array(get_string('login'), get_string('lastaccess'), get_string('lastip'), get_string('action'));
$table->align = array('left', 'left', 'left', 'right');
$table->data  = $data;
echo html_writer::table($table);

echo $OUTPUT->footer();

