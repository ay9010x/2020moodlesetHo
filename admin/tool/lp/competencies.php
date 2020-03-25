<?php



require_once(__DIR__ . '/../../../config.php');
require_once($CFG->libdir.'/adminlib.php');

$id = required_param('competencyframeworkid', PARAM_INT);
$pagecontextid = required_param('pagecontextid', PARAM_INT);  $search = optional_param('search', '', PARAM_RAW);

require_login();
\core_competency\api::require_enabled();

$pagecontext = context::instance_by_id($pagecontextid);
$framework = \core_competency\api::read_framework($id);
$context = $framework->get_context();

if (!\core_competency\competency_framework::can_read_context($context)) {
    throw new required_capability_exception($context, 'moodle/competency:competencyview', 'nopermissions', '');
}

$title = get_string('competencies', 'core_competency');
$pagetitle = get_string('competenciesforframework', 'tool_lp', $framework->get_shortname());

$url = new moodle_url("/admin/tool/lp/competencies.php", array('competencyframeworkid' => $framework->get_id(),
    'pagecontextid' => $pagecontextid));
$frameworksurl = new moodle_url('/admin/tool/lp/competencyframeworks.php', array('pagecontextid' => $pagecontextid));

$PAGE->navigation->override_active_url($frameworksurl);
$PAGE->set_context($pagecontext);
$PAGE->set_pagelayout('admin');
$PAGE->set_url($url);
$PAGE->navbar->add($framework->get_shortname(), $url);
$PAGE->set_title($title);
$PAGE->set_heading($title);
$output = $PAGE->get_renderer('tool_lp');
echo $output->header();

$page = new \tool_lp\output\manage_competencies_page($framework, $search, $pagecontext);
echo $output->render($page);

\core_competency\api::competency_framework_viewed($framework);

echo $output->footer();
