<?php



require_once("../../config.php");
require_once("lib.php");
require_once($CFG->libdir . '/completionlib.php');

feedback_init_feedback_session();

$id = required_param('id', PARAM_INT);
$courseid = optional_param('courseid', null, PARAM_INT);
$gopage = optional_param('gopage', 0, PARAM_INT);

list($course, $cm) = get_course_and_cm_from_cmid($id, 'feedback');
$feedback = $DB->get_record("feedback", array("id" => $cm->instance), '*', MUST_EXIST);

$urlparams = array('id' => $cm->id, 'gopage' => $gopage, 'courseid' => $courseid);
$PAGE->set_url('/mod/feedback/complete.php', $urlparams);

require_course_login($course, true, $cm);
$PAGE->set_activity_record($feedback);

$context = context_module::instance($cm->id);
$feedbackcompletion = new mod_feedback_completion($feedback, $cm, $courseid);

$courseid = $feedbackcompletion->get_courseid();

if (!has_capability('mod/feedback:edititems', $context) &&
        !$feedbackcompletion->check_course_is_mapped()) {
    echo $OUTPUT->header();
    echo $OUTPUT->notification(get_string('cannotaccess', 'mod_feedback'));
    echo $OUTPUT->footer();
    exit;
}

if ($courseid AND $courseid != SITEID) {
    require_course_login(get_course($courseid)); }

if (!$feedbackcompletion->can_complete()) {
    print_error('error');
}

$PAGE->navbar->add(get_string('feedback:complete', 'feedback'));
$PAGE->set_heading($course->fullname);
$PAGE->set_title($feedback->name);
$PAGE->set_pagelayout('incourse');

if (!$feedbackcompletion->is_open()) {
    echo $OUTPUT->header();
    echo $OUTPUT->heading(format_string($feedback->name));
    echo $OUTPUT->box_start('generalbox boxaligncenter');
    echo $OUTPUT->notification(get_string('feedback_is_not_open', 'feedback'));
    echo $OUTPUT->continue_button(course_get_url($courseid ?: $feedback->course));
    echo $OUTPUT->box_end();
    echo $OUTPUT->footer();
    exit;
}

$completion = new completion_info($course);
if (isloggedin() && !isguestuser()) {
    $completion->set_module_viewed($cm);
}

$cansubmit = $feedbackcompletion->can_submit();

if (!$feedbackcompletion->is_empty() && $cansubmit) {
    $form = new mod_feedback_complete_form(mod_feedback_complete_form::MODE_COMPLETE,
            $feedbackcompletion, 'feedback_complete_form', array('gopage' => $gopage));
    if ($form->is_cancelled()) {
                redirect(course_get_url($courseid ?: $course));
    } else if ($form->is_submitted() &&
            ($form->is_validated() || optional_param('gopreviouspage', null, PARAM_RAW))) {
                $data = $form->get_submitted_data();
        if (!isset($SESSION->feedback->is_started) OR !$SESSION->feedback->is_started == true) {
            print_error('error', '', $CFG->wwwroot.'/course/view.php?id='.$course->id);
        }
        $feedbackcompletion->save_response_tmp($data);
        if (!empty($data->savevalues) || !empty($data->gonextpage)) {
            if (($nextpage = $feedbackcompletion->get_next_page($gopage)) !== null) {
                redirect(new moodle_url($PAGE->url, array('gopage' => $nextpage)));
            } else {
                $feedbackcompletion->save_response();
                if (!$feedback->page_after_submit) {
                    \core\notification::success(get_string('entries_saved', 'feedback'));
                }
            }
        } else if (!empty($data->gopreviouspage)) {
            $prevpage = $feedbackcompletion->get_previous_page($gopage);
            redirect(new moodle_url($PAGE->url, array('gopage' => intval($prevpage))));
        }
    }
}

$strfeedbacks = get_string("modulenameplural", "feedback");
$strfeedback  = get_string("modulename", "feedback");

echo $OUTPUT->header();
echo $OUTPUT->heading(format_string($feedback->name));

if ($feedbackcompletion->is_empty()) {
    \core\notification::error(get_string('no_items_available_yet', 'feedback'));
} else if ($cansubmit) {
    if (!empty($data->savevalues) || !empty($data->gonextpage)) {
                if ($feedback->page_after_submit) {
            echo $OUTPUT->box($feedbackcompletion->page_after_submit(),
                    'generalbox boxaligncenter');
        }
        if ($feedbackcompletion->can_view_analysis()) {
            echo '<p align="center">';
            $analysisurl = new moodle_url('/mod/feedback/analysis.php', array('id' => $cm->id, 'courseid' => $courseid));
            echo html_writer::link($analysisurl, get_string('completed_feedbacks', 'feedback'));
            echo '</p>';
        }

        if ($feedback->site_after_submit) {
            $url = feedback_encode_target_url($feedback->site_after_submit);
        } else {
            $url = course_get_url($courseid ?: $course->id);
        }
        echo $OUTPUT->continue_button($url);
    } else {
                $SESSION->feedback->is_started = true;
        $form->display();
    }
} else {
    echo $OUTPUT->box_start('generalbox boxaligncenter');
    echo $OUTPUT->notification(get_string('this_feedback_is_already_submitted', 'feedback'));
    echo $OUTPUT->continue_button(course_get_url($courseid ?: $course->id));
    echo $OUTPUT->box_end();
}

echo $OUTPUT->footer();
