<?php



require_once(dirname(dirname(dirname(__FILE__))).'/config.php');
require_once(dirname(__FILE__).'/imageadd_form.php');
require_once(dirname(__FILE__).'/imageclass.php');

$id = required_param('id', PARAM_INT);

list($course, $cm) = get_course_and_cm_from_cmid($id, 'lightboxgallery');
$gallery = $DB->get_record('lightboxgallery', array('id' => $cm->instance), '*', MUST_EXIST);

require_login($course, true, $cm);

$context = context_module::instance($cm->id);
require_capability('mod/lightboxgallery:addimage', $context);

$PAGE->set_cm($cm);
$PAGE->set_url('/mod/lightboxgallery/view.php', array('id' => $cm->id));
$PAGE->set_title($gallery->name);
$PAGE->set_heading($course->shortname);

$mform = new mod_lightboxgallery_imageadd_form(null, array('id' => $cm->id));

if ($mform->is_cancelled()) {
    redirect($CFG->wwwroot.'/mod/lightboxgallery/view.php?id='.$cm->id);

} else if (($formdata = $mform->get_data()) && confirm_sesskey()) {
    require_once($CFG->dirroot . '/lib/uploadlib.php');

    $fs = get_file_storage();
    $draftid = file_get_submitted_draft_itemid('image');
    if (!$files = $fs->get_area_files(
        context_user::instance($USER->id)->id, 'user', 'draft', $draftid, 'id DESC', false)) {
        redirect($PAGE->url);
    }

    if ($gallery->autoresize == AUTO_RESIZE_UPLOAD || $gallery->autoresize == AUTO_RESIZE_BOTH) {
        $resize = $gallery->resize;
    } else if (isset($formdata->resize)) {
        $resize = $formdata->resize;
    } else {
        $resize = 0;     }

    lightboxgallery_add_images($files, $context, $cm, $gallery, $resize);
    redirect($CFG->wwwroot.'/mod/lightboxgallery/view.php?id='.$cm->id);
}

echo $OUTPUT->header();
$mform->display();
echo $OUTPUT->footer();
