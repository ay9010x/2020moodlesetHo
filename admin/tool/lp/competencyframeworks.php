<?php



require_once(__DIR__ . '/../../../config.php');
require_once($CFG->libdir.'/adminlib.php');

$pagecontextid = required_param('pagecontextid', PARAM_INT);
$context = context::instance_by_id($pagecontextid);

$url = new moodle_url("/admin/tool/lp/competencyframeworks.php");
$url->param('pagecontextid', $pagecontextid);

require_login();
\core_competency\api::require_enabled();

if (!\core_competency\competency_framework::can_read_context($context)) {
    throw new required_capability_exception($context, 'moodle/competency:competencyview', 'nopermissions', '');
}

$title = get_string('competencies', 'core_competency');
$pagetitle = get_string('competencyframeworks', 'tool_lp');

$PAGE->set_context($context);
$PAGE->set_pagelayout('admin');
$PAGE->set_url($url);
$PAGE->set_title($title);
$PAGE->set_heading($title);
$output = $PAGE->get_renderer('tool_lp');
echo $output->header();
echo $output->heading($pagetitle, 2);

$page = new \tool_lp\output\manage_competency_frameworks_page($context);
echo $output->render($page);

echo $output->footer();
