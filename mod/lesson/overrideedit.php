<?php




require_once(dirname(__FILE__) . '/../../config.php');
require_once($CFG->dirroot.'/mod/lesson/lib.php');
require_once($CFG->dirroot.'/mod/lesson/locallib.php');
require_once($CFG->dirroot.'/mod/lesson/override_form.php');


$cmid = optional_param('cmid', 0, PARAM_INT);
$overrideid = optional_param('id', 0, PARAM_INT);
$action = optional_param('action', null, PARAM_ALPHA);
$reset = optional_param('reset', false, PARAM_BOOL);

$override = null;
if ($overrideid) {

    if (! $override = $DB->get_record('lesson_overrides', array('id' => $overrideid))) {
        print_error('invalidoverrideid', 'lesson');
    }

    $lesson = new lesson($DB->get_record('lesson', array('id' => $override->lessonid), '*',  MUST_EXIST));

    list($course, $cm) = get_course_and_cm_from_instance($lesson, 'lesson');

} else if ($cmid) {
    list($course, $cm) = get_course_and_cm_from_cmid($cmid, 'lesson');
    $lesson = new lesson($DB->get_record('lesson', array('id' => $cm->instance), '*', MUST_EXIST));

} else {
    print_error('invalidcoursemodule');
}
$course = $DB->get_record('course', array('id' => $cm->course), '*', MUST_EXIST);

$url = new moodle_url('/mod/lesson/overrideedit.php');
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

require_capability('mod/lesson:manageoverrides', $context);

if ($overrideid) {
        $data = clone $override;
} else {
        $data = new stdClass();
}

$keys = array('available', 'deadline', 'review', 'timelimit', 'maxattempts', 'retake', 'password');
foreach ($keys as $key) {
    if (!isset($data->{$key}) || $reset) {
        $data->{$key} = $lesson->{$key};
    }
}

$groupmode = !empty($data->groupid) || ($action === 'addgroup' && empty($overrideid));

if ($action === 'duplicate') {
    $override->id = $data->id = null;
    $override->userid = $data->userid = null;
    $override->groupid = $data->groupid = null;
}

$overridelisturl = new moodle_url('/mod/lesson/overrides.php', array('cmid' => $cm->id));
if (!$groupmode) {
    $overridelisturl->param('mode', 'user');
}

$mform = new lesson_override_form($url, $cm, $lesson, $context, $groupmode, $override);
$mform->set_data($data);

if ($mform->is_cancelled()) {
    redirect($overridelisturl);

} else if (optional_param('resetbutton', 0, PARAM_ALPHA)) {
    $url->param('reset', true);
    redirect($url);

} else if ($fromform = $mform->get_data()) {
        $fromform->lessonid = $lesson->id;

        foreach ($keys as $key) {
        if ($fromform->{$key} == $lesson->{$key}) {
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
                'lessonid' => $lesson->id,
                'userid' => empty($fromform->userid) ? null : $fromform->userid,
                'groupid' => empty($fromform->groupid) ? null : $fromform->groupid);
        if ($oldoverride = $DB->get_record('lesson_overrides', $conditions)) {
                                    foreach ($keys as $key) {
                if (is_null($fromform->{$key})) {
                    $fromform->{$key} = $oldoverride->{$key};
                }
            }

            $lesson->delete_override($oldoverride->id);
        }
    }

        $params = array(
        'context' => $context,
        'other' => array(
            'lessonid' => $lesson->id
        )
    );
    if (!empty($override->id)) {
        $fromform->id = $override->id;
        $DB->update_record('lesson_overrides', $fromform);

                $params['objectid'] = $override->id;
        if (!$groupmode) {
            $params['relateduserid'] = $fromform->userid;
            $event = \mod_lesson\event\user_override_updated::create($params);
        } else {
            $params['other']['groupid'] = $fromform->groupid;
            $event = \mod_lesson\event\group_override_updated::create($params);
        }

                $event->trigger();
    } else {
        unset($fromform->id);
        $fromform->id = $DB->insert_record('lesson_overrides', $fromform);

                $params['objectid'] = $fromform->id;
        if (!$groupmode) {
            $params['relateduserid'] = $fromform->userid;
            $event = \mod_lesson\event\user_override_created::create($params);
        } else {
            $params['other']['groupid'] = $fromform->groupid;
            $event = \mod_lesson\event\group_override_created::create($params);
        }

                $event->trigger();
    }

    lesson_update_events($lesson, $fromform);

    if (!empty($fromform->submitbutton)) {
        redirect($overridelisturl);
    }

        $url->remove_params('cmid');
    $url->param('action', 'duplicate');
    $url->param('id', $fromform->id);
    redirect($url);

}

$pagetitle = get_string('editoverride', 'lesson');
$PAGE->navbar->add($pagetitle);
$PAGE->set_pagelayout('admin');
$PAGE->set_title($pagetitle);
$PAGE->set_heading($course->fullname);
echo $OUTPUT->header();
echo $OUTPUT->heading(format_string($lesson->name, true, array('context' => $context)));

$mform->display();

echo $OUTPUT->footer();
