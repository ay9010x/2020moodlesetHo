<?php




require_once(dirname(__FILE__) . '/../../config.php');
require_once($CFG->dirroot.'/mod/quiz/lib.php');
require_once($CFG->dirroot.'/mod/quiz/locallib.php');
require_once($CFG->dirroot.'/mod/quiz/override_form.php');


$cmid = optional_param('cmid', 0, PARAM_INT);
$overrideid = optional_param('id', 0, PARAM_INT);
$action = optional_param('action', null, PARAM_ALPHA);
$reset = optional_param('reset', false, PARAM_BOOL);

$override = null;
if ($overrideid) {

    if (! $override = $DB->get_record('quiz_overrides', array('id' => $overrideid))) {
        print_error('invalidoverrideid', 'quiz');
    }
    if (! $quiz = $DB->get_record('quiz', array('id' => $override->quiz))) {
        print_error('invalidcoursemodule');
    }
    list($course, $cm) = get_course_and_cm_from_instance($quiz, 'quiz');

} else if ($cmid) {
    list($course, $cm) = get_course_and_cm_from_cmid($cmid, 'quiz');
    $quiz = $DB->get_record('quiz', array('id' => $cm->instance), '*', MUST_EXIST);

} else {
    print_error('invalidcoursemodule');
}
$course = $DB->get_record('course', array('id'=>$cm->course), '*', MUST_EXIST);

$url = new moodle_url('/mod/quiz/overrideedit.php');
if ($action) {
    $url->param('action', $action);
}
if ($overrideid) {
    $url->param('id', $overrideid);
} else {
    $url->param('cmid', $cmid);
}

$PAGE->set_url($url);

require_login($course, false, $cm);

$context = context_module::instance($cm->id);

require_capability('mod/quiz:manageoverrides', $context);

if ($overrideid) {
        $data = clone $override;
} else {
        $data = new stdClass();
}

$keys = array('timeopen', 'timeclose', 'timelimit', 'attempts', 'password');
foreach ($keys as $key) {
    if (!isset($data->{$key}) || $reset) {
        $data->{$key} = $quiz->{$key};
    }
}

if ($action === 'duplicate') {
    $override->id = null;
    $override->userid = null;
    $override->groupid = null;
}

$groupmode = !empty($data->groupid) || ($action === 'addgroup' && empty($overrideid));

$overridelisturl = new moodle_url('/mod/quiz/overrides.php', array('cmid'=>$cm->id));
if (!$groupmode) {
    $overridelisturl->param('mode', 'user');
}

$mform = new quiz_override_form($url, $cm, $quiz, $context, $groupmode, $override);
$mform->set_data($data);

if ($mform->is_cancelled()) {
    redirect($overridelisturl);

} else if (optional_param('resetbutton', 0, PARAM_ALPHA)) {
    $url->param('reset', true);
    redirect($url);

} else if ($fromform = $mform->get_data()) {
        $fromform->quiz = $quiz->id;

        foreach ($keys as $key) {
        if ($fromform->{$key} == $quiz->{$key}) {
            $fromform->{$key} = null;
        }
    }

        $userorgroupchanged = false;
    if (empty($override->id)) {
        $userorgroupchanged = true;
    } else if (!empty($fromform->userid)) {
        $userorgroupchanged = $fromform->userid !== $override->userid;
    } else {
        $userorgroupchanged = $fromform->groupid !== $override->groupid;
    }

    if ($userorgroupchanged) {
        $conditions = array(
                'quiz' => $quiz->id,
                'userid' => empty($fromform->userid)? null : $fromform->userid,
                'groupid' => empty($fromform->groupid)? null : $fromform->groupid);
        if ($oldoverride = $DB->get_record('quiz_overrides', $conditions)) {
                                    foreach ($keys as $key) {
                if (is_null($fromform->{$key})) {
                    $fromform->{$key} = $oldoverride->{$key};
                }
            }
                        $quiz->cmid = $cm->id;
            quiz_delete_override($quiz, $oldoverride->id);
        }
    }

        $params = array(
        'context' => $context,
        'other' => array(
            'quizid' => $quiz->id
        )
    );
    if (!empty($override->id)) {
        $fromform->id = $override->id;
        $DB->update_record('quiz_overrides', $fromform);

                $params['objectid'] = $override->id;
        if (!$groupmode) {
            $params['relateduserid'] = $fromform->userid;
            $event = \mod_quiz\event\user_override_updated::create($params);
        } else {
            $params['other']['groupid'] = $fromform->groupid;
            $event = \mod_quiz\event\group_override_updated::create($params);
        }

                $event->trigger();
    } else {
        unset($fromform->id);
        $fromform->id = $DB->insert_record('quiz_overrides', $fromform);

                $params['objectid'] = $fromform->id;
        if (!$groupmode) {
            $params['relateduserid'] = $fromform->userid;
            $event = \mod_quiz\event\user_override_created::create($params);
        } else {
            $params['other']['groupid'] = $fromform->groupid;
            $event = \mod_quiz\event\group_override_created::create($params);
        }

                $event->trigger();
    }

    quiz_update_open_attempts(array('quizid'=>$quiz->id));
    quiz_update_events($quiz, $fromform);

    if (!empty($fromform->submitbutton)) {
        redirect($overridelisturl);
    }

        $url->remove_params('cmid');
    $url->param('action', 'duplicate');
    $url->param('id', $fromform->id);
    redirect($url);

}

$pagetitle = get_string('editoverride', 'quiz');
$PAGE->navbar->add($pagetitle);
$PAGE->set_pagelayout('admin');
$PAGE->set_title($pagetitle);
$PAGE->set_heading($course->fullname);
echo $OUTPUT->header();
echo $OUTPUT->heading(format_string($quiz->name, true, array('context' => $context)));

$mform->display();

echo $OUTPUT->footer();
