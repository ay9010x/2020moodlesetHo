<?php



require_once(__DIR__ . '/../../config.php');

require_login(null, false);
if (isguestuser()) {
    throw new require_login_exception('Guests are not allowed here.');
}

$toreviewstr = get_string('competenciestoreview', 'block_lp');

$url = new moodle_url('/blocks/lp/competencies_to_review.php');
$PAGE->set_context(context_user::instance($USER->id));
$PAGE->set_url($url);
$PAGE->set_title($toreviewstr);
$PAGE->set_pagelayout('standard');
$PAGE->navbar->add($toreviewstr, $url);

$output = $PAGE->get_renderer('block_lp');
echo $output->header();
echo $output->heading($toreviewstr);

$page = new \block_lp\output\competencies_to_review_page();
echo $output->render($page);

echo $output->footer();
