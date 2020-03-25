<?php



require_once("../../config.php");
require_once("lib.php");

$id = required_param('id', PARAM_INT);
$userid = optional_param('userid', false, PARAM_INT);
$showcompleted = optional_param('showcompleted', false, PARAM_INT);
$deleteid = optional_param('delete', null, PARAM_INT);
$courseid = optional_param('courseid', null, PARAM_INT);


list($course, $cm) = get_course_and_cm_from_cmid($id, 'feedback');

$baseurl = new moodle_url('/mod/feedback/show_entries.php', array('id' => $cm->id));
$PAGE->set_url(new moodle_url($baseurl, array('userid' => $userid, 'showcompleted' => $showcompleted,
        'delete' => $deleteid)));

$context = context_module::instance($cm->id);

require_login($course, true, $cm);
$feedback = $PAGE->activityrecord;

require_capability('mod/feedback:viewreports', $context);

if ($deleteid) {
        require_capability('mod/feedback:deletesubmissions', $context);
    require_sesskey();
    $feedbackstructure = new mod_feedback_completion($feedback, $cm, 0, true, $deleteid);
    feedback_delete_completed($feedbackstructure->get_completed(), $feedback, $cm);
    redirect($baseurl);
} else if ($showcompleted || $userid) {
        $feedbackstructure = new mod_feedback_completion($feedback, $cm, 0, true, $showcompleted, $userid);
} else {
        $feedbackstructure = new mod_feedback_structure($feedback, $cm, $courseid);
}

$responsestable = new mod_feedback_responses_table($feedbackstructure);
$anonresponsestable = new mod_feedback_responses_anon_table($feedbackstructure);

if ($responsestable->is_downloading()) {
    $responsestable->download();
}
if ($anonresponsestable->is_downloading()) {
    $anonresponsestable->download();
}

$courseselectform = new mod_feedback_course_select_form($baseurl, $feedbackstructure, $feedback->course == SITEID);
if ($data = $courseselectform->get_data()) {
    redirect(new moodle_url($baseurl, ['courseid' => $data->courseid]));
}
navigation_node::override_active_url($baseurl);
$PAGE->set_heading($course->fullname);
$PAGE->set_title($feedback->name);
echo $OUTPUT->header();
echo $OUTPUT->heading(format_string($feedback->name));

$current_tab = 'showentries';
require('tabs.php');


if ($userid || $showcompleted) {
        $completedrecord = $feedbackstructure->get_completed();

    if ($userid) {
        $usr = $DB->get_record('user', array('id' => $userid), '*', MUST_EXIST);
        $responsetitle = userdate($completedrecord->timemodified) . ' (' . fullname($usr) . ')';
    } else {
        $responsetitle = get_string('response_nr', 'feedback') . ': ' .
                $completedrecord->random_response . ' (' . get_string('anonymous', 'feedback') . ')';
    }

    echo $OUTPUT->heading($responsetitle, 4);

    $form = new mod_feedback_complete_form(mod_feedback_complete_form::MODE_VIEW_RESPONSE,
            $feedbackstructure, 'feedback_viewresponse_form');
    $form->display();

    list($prevresponseurl, $returnurl, $nextresponseurl) = $userid ?
            $responsestable->get_reponse_navigation_links($completedrecord) :
            $anonresponsestable->get_reponse_navigation_links($completedrecord);

    echo html_writer::start_div('response_navigation');
    echo $prevresponseurl ? html_writer::link($prevresponseurl, get_string('prev'), ['class' => 'prev_response']) : '';
    echo html_writer::link($returnurl, get_string('back'), ['class' => 'back_to_list']);
    echo $nextresponseurl ? html_writer::link($nextresponseurl, get_string('next'), ['class' => 'next_response']) : '';
    echo html_writer::end_div();
} else {
        $courseselectform->display();

        $totalrows = $responsestable->get_total_responses_count();
    if (!$feedbackstructure->is_anonymous() || $totalrows) {
        echo $OUTPUT->heading(get_string('non_anonymous_entries', 'feedback', $totalrows), 4);
        $responsestable->display();
    }

        $feedbackstructure->shuffle_anonym_responses();
    $totalrows = $anonresponsestable->get_total_responses_count();
    if ($feedbackstructure->is_anonymous() || $totalrows) {
        echo $OUTPUT->heading(get_string('anonymous_entries', 'feedback', $totalrows), 4);
        $anonresponsestable->display();
    }

}

echo $OUTPUT->footer();

