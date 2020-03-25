<?php



require(__DIR__ . '/../../../config.php');

$userid = required_param('userid', PARAM_INT);
$competencyid = required_param('competencyid', PARAM_INT);
$planid = required_param('planid', PARAM_INT);

require_login(null, false);
if (isguestuser()) {
    throw new require_login_exception('Guests are not allowed here.');
}
\core_competency\api::require_enabled();

$params = array('userid' => $userid, 'competencyid' => $competencyid);
$params['planid'] = $planid;
$plan = \core_competency\api::read_plan($planid);
$url = new moodle_url('/admin/tool/lp/user_competency_in_plan.php', $params);
$competency = new \core_competency\competency($competencyid);
$framework = $competency->get_framework();

list($title, $subtitle) = \tool_lp\page_helper::setup_for_plan($userid, $url, $plan);

$output = $PAGE->get_renderer('tool_lp');
echo $output->header();
echo $output->heading($title);
$baseurl = new moodle_url('/admin/tool/lp/user_competency_in_plan.php');
$nav = new \tool_lp\output\competency_plan_navigation($userid, $competencyid, $planid, $baseurl);

echo $output->render($nav);
$page = new \tool_lp\output\user_competency_summary_in_plan($competencyid, $planid);
echo $output->render($page);
$pc = \core_competency\api::get_plan_competency($plan, $competency->get_id());
if ($plan->get_status() == \core_competency\plan::STATUS_COMPLETE) {
    $usercompetencyplan = $pc->usercompetencyplan;
    \core_competency\api::user_competency_plan_viewed($usercompetencyplan);
} else {
    $usercompetency = $pc->usercompetency;
    \core_competency\api::user_competency_viewed_in_plan($usercompetency, $plan->get_id());
}

echo $output->footer();
