<?php



define('AJAX_SCRIPT', true);
require_once('../../config.php');

$id      = optional_param('id', 0, PARAM_INT);
$page    = optional_param('page', 0, PARAM_INT);
$since    = optional_param('since', 0, PARAM_INT);
$logreader = optional_param('logreader', '', PARAM_COMPONENT); 
$PAGE->set_url('/report/loglive/loglive_ajax.php');

if (empty($id)) {
    require_login();
    $context = context_system::instance();
    $PAGE->set_context($context);
} else {
    $course = get_course($id);
    require_login($course);
    $context = context_course::instance($course->id);
}

require_capability('report/loglive:view', $context);

if (!$since) {
    echo $since = $since - report_loglive_renderable::CUTOFF;
}
$renderable = new report_loglive_renderable($logreader, $id, '', $since, $page);
$output = $PAGE->get_renderer('report_loglive');
echo $output->render($renderable);
