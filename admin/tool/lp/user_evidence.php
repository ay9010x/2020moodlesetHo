<?php



require_once(__DIR__ . '/../../../config.php');

require_login(null, false);
if (isguestuser()) {
    throw new require_login_exception('Guests are not allowed here.');
}
\core_competency\api::require_enabled();

$id = required_param('id', PARAM_INT);

$userevidence = \core_competency\api::read_user_evidence($id);
$url = new moodle_url('/admin/tool/lp/user_evidence.php', array('id' => $id));
list($title, $subtitle) = \tool_lp\page_helper::setup_for_user_evidence($userevidence->get_userid(), $url, $userevidence);

$output = $PAGE->get_renderer('tool_lp');
echo $output->header();

$page = new \tool_lp\output\user_evidence_page($userevidence);
echo $output->render($page);

echo $output->footer();
