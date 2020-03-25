<?php



require_once("../../config.php");
require_once("lib.php");

$current_tab = 'analysis';

$id = required_param('id', PARAM_INT);  $courseitemfilter = optional_param('courseitemfilter', '0', PARAM_INT);
$courseitemfiltertyp = optional_param('courseitemfiltertyp', '0', PARAM_ALPHANUM);
$courseid = optional_param('courseid', false, PARAM_INT);

$url = new moodle_url('/mod/feedback/analysis_course.php', array('id'=>$id));
navigation_node::override_active_url($url);
if ($courseid !== false) {
    $url->param('courseid', $courseid);
}
if ($courseitemfilter !== '0') {
    $url->param('courseitemfilter', $courseitemfilter);
}
if ($courseitemfiltertyp !== '0') {
    $url->param('courseitemfiltertyp', $courseitemfiltertyp);
}
$PAGE->set_url($url);

list($course, $cm) = get_course_and_cm_from_cmid($id, 'feedback');
$context = context_module::instance($cm->id);

require_course_login($course, true, $cm);

$feedback = $PAGE->activityrecord;

if (!($feedback->publish_stats OR has_capability('mod/feedback:viewreports', $context))) {
    print_error('error');
}

$feedbackstructure = new mod_feedback_structure($feedback, $PAGE->cm, $courseid);

$courseselectform = new mod_feedback_course_select_form($url, $feedbackstructure);
if ($data = $courseselectform->get_data()) {
    redirect(new moodle_url($url, ['courseid' => $data->courseid]));
}

$strfeedbacks = get_string("modulenameplural", "feedback");
$strfeedback  = get_string("modulename", "feedback");

$PAGE->set_heading($course->fullname);
$PAGE->set_title($feedback->name);
echo $OUTPUT->header();
echo $OUTPUT->heading(format_string($feedback->name));

require('tabs.php');

$mygroupid = false;

$courseselectform->display();

$summary = new mod_feedback\output\summary($feedbackstructure);
echo $OUTPUT->render_from_template('mod_feedback/summary', $summary->export_for_template($OUTPUT));

$items = $feedbackstructure->get_items(true);

if ($courseitemfilter > 0) {
    $sumvalue = 'SUM(' . $DB->sql_cast_char2real('value', true) . ')';
    $sql = "SELECT fv.course_id, c.shortname, $sumvalue AS sumvalue, COUNT(value) as countvalue
            FROM {feedback_value} fv, {course} c, {feedback_item} fi
            WHERE fv.course_id = c.id AND fi.id = fv.item AND fi.typ = ? AND fv.item = ?
            GROUP BY course_id, shortname
            ORDER BY sumvalue desc";

    if ($courses = $DB->get_records_sql($sql, array($courseitemfiltertyp, $courseitemfilter))) {
        $item = $DB->get_record('feedback_item', array('id'=>$courseitemfilter));
        echo '<h4>'.$item->name.'</h4>';
        echo '<div class="clearfix">';
        echo '<table>';
        echo '<tr><th>Course</th><th>Average</th></tr>';

        foreach ($courses as $c) {
            $coursecontext = context_course::instance($c->course_id);
            $shortname = format_string($c->shortname, true, array('context' => $coursecontext));

            echo '<tr>';
            echo '<td>'.$shortname.'</td>';
            echo '<td align="right">';
            echo format_float(($c->sumvalue / $c->countvalue), 2);
            echo '</td>';
            echo '</tr>';
        }
        echo '</table>';
    } else {
        echo '<p>'.get_string('noresults').'</p>';
    }
    echo '<p><a href="analysis_course.php?id=' . $id . '">';
    echo get_string('back');
    echo '</a></p>';
} else {

        foreach ($items as $item) {
        echo '<table class="analysis">';
        $itemobj = feedback_get_item_class($item->typ);
        $printnr = ($feedback->autonumbering && $item->itemnr) ? ($item->itemnr . '.') : '';
        $itemobj->print_analysed($item, $printnr, $mygroupid, $feedbackstructure->get_courseid());
        if (preg_match('/rated$/i', $item->typ)) {
            $url = new moodle_url('/mod/feedback/analysis_course.php', array('id' => $id,
                'courseitemfilter' => $item->id, 'courseitemfiltertyp' => $item->typ));
            $anker = html_writer::link($url, get_string('sort_by_course', 'feedback'));

            echo '<tr><td colspan="2">'.$anker.'</td></tr>';
        }
        echo '</table>';
    }
}

echo $OUTPUT->footer();

