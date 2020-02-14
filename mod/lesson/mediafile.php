<?php




require_once('../../config.php');
require_once($CFG->dirroot.'/mod/lesson/locallib.php');

$id = required_param('id', PARAM_INT);    $printclose = optional_param('printclose', 0, PARAM_INT);

$cm = get_coursemodule_from_id('lesson', $id, 0, false, MUST_EXIST);
$course = $DB->get_record('course', array('id' => $cm->course), '*', MUST_EXIST);
$lesson = new lesson($DB->get_record('lesson', array('id' => $cm->instance), '*', MUST_EXIST));

require_login($course, false, $cm);


$lesson->update_effective_access($USER->id);

$context = context_module::instance($cm->id);
$canmanage = has_capability('mod/lesson:manage', $context);

$url = new moodle_url('/mod/lesson/mediafile.php', array('id'=>$id));
if ($printclose !== '') {
    $url->param('printclose', $printclose);
}
$PAGE->set_url($url);
$PAGE->set_pagelayout('popup');
$PAGE->set_title($course->shortname);

$lessonoutput = $PAGE->get_renderer('mod_lesson');

$mimetype = mimeinfo("type", $lesson->mediafile);

if ($printclose) {      if ($lesson->mediaclose) {
        echo $lessonoutput->header($lesson, $cm);
        echo $OUTPUT->box('<form><div><input type="button" onclick="top.close();" value="'.get_string("closewindow").'" /></div></form>', 'lessonmediafilecontrol');
        echo $lessonoutput->footer();
    }
    exit();
}

echo $lessonoutput->header($lesson, $cm);

if (!$canmanage) {
    if (!$lesson->is_accessible()) {          echo $lessonoutput->header($lesson, $cm);
        if ($lesson->deadline != 0 && time() > $lesson->deadline) {
            echo $lessonoutput->lesson_inaccessible(get_string('lessonclosed', 'lesson', userdate($lesson->deadline)));
        } else {
            echo $lessonoutput->lesson_inaccessible(get_string('lessonopen', 'lesson', userdate($lesson->available)));
        }
        echo $lessonoutput->footer();
        exit();
    } else if ($lesson->usepassword && empty($USER->lessonloggedin[$lesson->id])) {         $correctpass = false;
        if (!empty($userpassword) && (($lesson->password == md5(trim($userpassword))) || ($lesson->password == trim($userpassword)))) {
            require_sesskey();
                        $USER->lessonloggedin[$lesson->id] = true;
            $correctpass = true;
        } else if (isset($lesson->extrapasswords)) {
                        foreach ($lesson->extrapasswords as $password) {
                if (strcmp($password, md5(trim($userpassword))) === 0 || strcmp($password, trim($userpassword)) === 0) {
                    require_sesskey();
                    $correctpass = true;
                    $USER->lessonloggedin[$lesson->id] = true;
                }
            }
        }
        if (!$correctpass) {
            echo $lessonoutput->header($lesson, $cm);
            echo $lessonoutput->login_prompt($lesson, $userpassword !== '');
            echo $lessonoutput->footer();
            exit();
        }
    }
}

echo $OUTPUT->box(lesson_get_media_html($lesson, $context));

if ($lesson->mediaclose) {
   echo '<div class="lessonmediafilecontrol">';
   echo $OUTPUT->close_window_button();
   echo '</div>';
}

echo $lessonoutput->footer();
