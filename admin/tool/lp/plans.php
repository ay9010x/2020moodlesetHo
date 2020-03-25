<?php



require_once(__DIR__ . '/../../../config.php');

require_login(null, false);
if (isguestuser()) {
    throw new require_login_exception('Guests are not allowed here.');
}
\core_competency\api::require_enabled();

$userid = optional_param('userid', $USER->id, PARAM_INT);

$url = new moodle_url('/admin/tool/lp/plans.php', array('userid' => $userid));
list($title, $subtitle) = \tool_lp\page_helper::setup_for_plan($userid, $url);

$output = $PAGE->get_renderer('tool_lp');
echo $output->header();
echo $output->heading($title);

$page = new \tool_lp\output\plans_page($userid);
echo $output->render($page);

echo $output->footer();
