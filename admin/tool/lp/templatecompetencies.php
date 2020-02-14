<?php



require_once(__DIR__ . '/../../../config.php');
require_once($CFG->libdir.'/adminlib.php');

$templateid = required_param('templateid', PARAM_INT);
$pagecontextid = required_param('pagecontextid', PARAM_INT);  
require_login(0, false);
\core_competency\api::require_enabled();

$pagecontext = context::instance_by_id($pagecontextid);
$template = \core_competency\api::read_template($templateid);
$context = $template->get_context();
if (!$template->can_read()) {
    throw new required_capability_exception($context, 'moodle/competency:templateview', 'nopermissions', '');
}

\core_competency\api::template_viewed($template);

$url = new moodle_url('/admin/tool/lp/templatecompetencies.php', array('templateid' => $template->get_id(),
    'pagecontextid' => $pagecontextid));
list($title, $subtitle) = \tool_lp\page_helper::setup_for_template($pagecontextid, $url, $template);

$output = $PAGE->get_renderer('tool_lp');
echo $output->header();
$page = new \tool_lp\output\template_competencies_page($template, $pagecontext);
echo $output->render($page);
echo $output->footer();
