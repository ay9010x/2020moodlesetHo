<?php



require_once('../config.php');
require_once($CFG->libdir.'/adminlib.php');

$confirm = optional_param('confirm', 0, PARAM_BOOL);
$returnurl = optional_param('returnurl', null, PARAM_LOCALURL);

if ($confirm && isloggedin() && confirm_sesskey()) {
    require_capability('moodle/site:config', context_system::instance());

        purge_all_caches();

    if ($returnurl) {
        $returnurl = $CFG->wwwroot . $returnurl;
    } else {
        $returnurl = new moodle_url('/admin/purgecaches.php');
    }
    redirect($returnurl, get_string('purgecachesfinished', 'admin'));
}

admin_externalpage_setup('purgecaches');

$actionurl = new moodle_url('/admin/purgecaches.php', array('sesskey'=>sesskey(), 'confirm'=>1));
if ($returnurl) {
    $actionurl->param('returnurl', $returnurl);
}

echo $OUTPUT->header();
echo $OUTPUT->heading(get_string('purgecaches', 'admin'));

echo $OUTPUT->box_start('generalbox', 'notice');
echo html_writer::tag('p', get_string('purgecachesconfirm', 'admin'));
echo $OUTPUT->single_button($actionurl, get_string('purgecaches', 'admin'), 'post');
echo $OUTPUT->box_end();

echo $OUTPUT->footer();
