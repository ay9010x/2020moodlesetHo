<?php



require(__DIR__ . '/../../../config.php');

$id = required_param('id', PARAM_INT);

require_login(null, false);
if (isguestuser()) {
    throw new require_login_exception('Guests are not allowed here.');
}
\core_competency\api::require_enabled();

$plan = \core_competency\api::read_plan($id);
$url = new moodle_url('/admin/tool/lp/plan.php', array('id' => $id));

list($title, $subtitle) = \tool_lp\page_helper::setup_for_plan($plan->get_userid(), $url, $plan);

$output = $PAGE->get_renderer('tool_lp');
echo $output->header();

$page = new \tool_lp\output\plan_page($plan);
echo $output->render($page);

\core_competency\api::plan_viewed($plan);

echo $output->footer();
