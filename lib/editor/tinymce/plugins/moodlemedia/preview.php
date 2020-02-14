<?php



require(dirname(__FILE__) . '/../../../../../config.php');
require_once($CFG->libdir . '/filelib.php');

$media = required_param('media', PARAM_RAW);
$media = base64_decode($media);
$url = clean_param($media, PARAM_URL);
$url = new moodle_url($url);

$PAGE->set_pagelayout('embedded');
$PAGE->set_url(new moodle_url('/lib/editor/tinymce/plugins/moodlemedia/preview.php'));
$PAGE->set_context(context_system::instance());
$PAGE->add_body_class('core_media_preview');

echo $OUTPUT->header();

$mediarenderer = $PAGE->get_renderer('core', 'media');

if (isloggedin() and !isguestuser() and $mediarenderer->can_embed_url($url)) {
    require_sesskey();
    echo $mediarenderer->embed_url($url);
} else {
    print_string('nopreview', 'tinymce_moodlemedia');
}

echo $OUTPUT->footer();
