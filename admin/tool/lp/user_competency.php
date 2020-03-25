<?php



require(__DIR__ . '/../../../config.php');

$id = required_param('id', PARAM_INT);

require_login(null, false);
if (isguestuser()) {
    throw new require_login_exception('Guests are not allowed here.');
}
\core_competency\api::require_enabled();

$uc = \core_competency\api::get_user_competency_by_id($id);
$params = array('id' => $id);
$url = new moodle_url('/admin/tool/lp/user_competency.php', $params);

$user = core_user::get_user($uc->get_userid());
if (!$user || !core_user::is_real_user($user->id)) {
    throw new moodle_exception('invaliduser', 'error');
}
$iscurrentuser = ($USER->id == $user->id);

$competency = $uc->get_competency();
$compexporter = new \core_competency\external\competency_exporter($competency, array('context' => $competency->get_context()));

$PAGE->set_pagelayout('standard');
$PAGE->set_url($url);
$PAGE->navigation->override_active_url(new moodle_url('/admin/tool/lp/plans.php', array('userid' => $uc->get_userid())));
$PAGE->set_context($uc->get_context());
if (!$iscurrentuser) {
    $PAGE->navigation->extend_for_user($user);
    $PAGE->navigation->set_userid_for_parent_checks($user->id);
}
$output = $PAGE->get_renderer('tool_lp');
$compdata = $compexporter->export($output);
$PAGE->navbar->add($compdata->shortname, $url);
$PAGE->set_title($compdata->shortname);
$PAGE->set_heading($compdata->shortname);

echo $output->header();
$page = new \tool_lp\output\user_competency_summary($uc);
echo $output->render($page);
\core_competency\api::user_competency_viewed($uc);

echo $output->footer();
