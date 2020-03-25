<?php



require_once(__DIR__ . '/../../../config.php');

$id = required_param('courseid', PARAM_INT);

$params = array('id' => $id);
$course = $DB->get_record('course', $params, '*', MUST_EXIST);

require_login($course);
\core_competency\api::require_enabled();

$context = context_course::instance($course->id);
$urlparams = array('courseid' => $id);

$url = new moodle_url('/admin/tool/lp/coursecompetencies.php', $urlparams);

list($title, $subtitle) = \tool_lp\page_helper::setup_for_course($url, $course);

$output = $PAGE->get_renderer('tool_lp');
$page = new \tool_lp\output\course_competencies_page($course->id);

echo $output->header();
echo $output->heading($title);

echo $output->render($page);

echo $output->footer();
