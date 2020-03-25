<?php



require_once(__DIR__ . '/../../../config.php');
require_once($CFG->libdir.'/adminlib.php');

$pagecontextid = required_param('pagecontextid', PARAM_INT);
$context = context::instance_by_id($pagecontextid);

require_login(0, false);
\core_competency\api::require_enabled();
if (!\core_competency\template::can_read_context($context)) {
    throw new required_capability_exception($context, 'moodle/competency:templateview', 'nopermissions', '');
}

$url = new moodle_url('/admin/tool/lp/learningplans.php', array('pagecontextid' => $pagecontextid));
list($title, $subtitle) = \tool_lp\page_helper::setup_for_template($pagecontextid, $url);

$output = $PAGE->get_renderer('tool_lp');
echo $output->header();
echo $output->heading($title);
$page = new \tool_lp\output\manage_templates_page($context);
echo $output->render($page);
echo $output->footer();
