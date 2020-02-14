<?php




require_once('../../config.php');
require_once($CFG->dirroot.'/mod/lesson/locallib.php');

$id = required_param('id', PARAM_INT);

$cm = get_coursemodule_from_id('lesson', $id, 0, false, MUST_EXIST);
$course = $DB->get_record('course', array('id' => $cm->course), '*', MUST_EXIST);
$lesson = new lesson($DB->get_record('lesson', array('id' => $cm->instance), '*', MUST_EXIST));

require_login($course, false, $cm);

$context = context_module::instance($cm->id);
require_capability('mod/lesson:manage', $context);

$mode    = optional_param('mode', get_user_preferences('lesson_view', 'collapsed'), PARAM_ALPHA);
if (!in_array($mode, array('single', 'full', 'collapsed'))) {
    $mode = 'collapsed';
}
$PAGE->set_url('/mod/lesson/edit.php', array('id'=>$cm->id,'mode'=>$mode));

if ($mode != get_user_preferences('lesson_view', 'collapsed') && $mode !== 'single') {
    set_user_preference('lesson_view', $mode);
}

$lessonoutput = $PAGE->get_renderer('mod_lesson');
$PAGE->navbar->add(get_string('edit'));
echo $lessonoutput->header($lesson, $cm, $mode, false, null, get_string('edit', 'lesson'));

if (!$lesson->has_pages()) {
        require_capability('mod/lesson:edit', $context);
    echo $lessonoutput->add_first_page_links($lesson);
} else {
    switch ($mode) {
        case 'collapsed':
            echo $lessonoutput->display_edit_collapsed($lesson, $lesson->firstpageid);
            break;
        case 'single':
            $pageid =  required_param('pageid', PARAM_INT);
            $PAGE->url->param('pageid', $pageid);
            $singlepage = $lesson->load_page($pageid);
            echo $lessonoutput->display_edit_full($lesson, $singlepage->id, $singlepage->prevpageid, true);
            break;
        case 'full':
            echo $lessonoutput->display_edit_full($lesson, $lesson->firstpageid, 0);
            break;
    }
}

echo $lessonoutput->footer();
