<?php

/**
 * The Gradebook setup page.
 *
 * @package   core_grades
 *
 * by YCJ 2017.12.12
 *
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once '../../../config.php';
require_once $CFG->dirroot.'/grade/lib.php';
require_once $CFG->dirroot.'/grade/report/lib.php'; // for preferences
require_once $CFG->dirroot.'/grade/edit/moodleset/lib.php';

$courseid        = required_param('id', PARAM_INT);
$eid             = optional_param('eid', 0, PARAM_ALPHANUM);
$weightsadjusted = optional_param('weightsadjusted', 0, PARAM_INT);

$url = new moodle_url('/grade/edit/moodleset/index.php', array('id' => $courseid));
$PAGE->set_url($url);
$PAGE->set_pagelayout('admin');

/// Make sure they can even access this course
if (!$course = $DB->get_record('course', array('id' => $courseid))) {
    print_error('nocourseid');
}

require_login($course);
$context = context_course::instance($course->id);
require_capability('moodle/grade:manage', $context);

/// return tracking object
$gpr = new grade_plugin_return(array('type'=>'edit', 'plugin'=>'moodleset', 'courseid'=>$courseid));
$returnurl = $gpr->get_return_url(null);

// get the grading moodleset object
// note: total must be first for moving to work correctly, if you want it last moving code must be rewritten!
$gtree = new grade_moodleset($courseid, false, false);

if (empty($eid)) {
    $element = null;
    $object  = null;

} else {
    if (!$element = $gtree->locate_element($eid)) {
        print_error('invalidelementid', '', $returnurl);
    }
    $object = $element['object'];
}

$switch = grade_get_setting($course->id, 'aggregationposition', $CFG->grade_aggregationposition);

$strgrades             = get_string('grades');
$strgraderreport       = get_string('graderreport', 'grades');

$grade_edit_moodleset = new grade_edit_moodleset($gtree, false, $gpr);

//if we go straight to the db to update an element we need to recreate the moodleset as
// $grade_edit_moodleset has already been constructed.
//Ideally we could do the updates through $grade_edit_moodleset to avoid recreating it
$recreatetree = false;

if ($data = data_submitted() and confirm_sesskey()) {
	  // Reset the latest entry of grade setup page
    $DB->set_field('grade_categories_setuppage', 'setuppage', 0, array('courseid' => $courseid));

    // Perform bulk actions first
    if (!empty($data->bulkmove)) {
        $elements = array();

        foreach ($data as $key => $value) {
            if (preg_match('/select_(ig[0-9]*)/', $key, $matches)) {
                $elements[] = $matches[1];
            }
        }

        $grade_edit_moodleset->move_elements($elements, $returnurl);
    }

    // Update weights (extra credits) on categories and items.
    foreach ($data as $key => $value) {
        if (preg_match('/^weight_([0-9]+)$/', $key, $matches)) {
            $aid   = $matches[1];

            $value = unformat_float($value);
            $value = clean_param($value, PARAM_FLOAT);

            $grade_item = grade_item::fetch(array('id' => $aid, 'courseid' => $courseid));

            // Convert weight to aggregation coef2.
            $aggcoef = $grade_item->get_coefstring();
            if ($aggcoef == 'aggregationcoefextraweightsum') {
                // The field 'weight' should only be sent when the checkbox 'weighoverride' is checked,
                // so there is not need to set weightoverride here, it is done below.
                $value = $value / 100.0;
                $grade_item->aggregationcoef2 = $value;
            } else if ($aggcoef == 'aggregationcoefweight' || $aggcoef == 'aggregationcoefextraweight') {
                $grade_item->aggregationcoef = $value;
            }

            $grade_item->update();

            $recreatetree = true;

        // Grade item checkbox inputs.
        } elseif (preg_match('/^(weightoverride)_([0-9]+)$/', $key, $matches)) {
            $param   = $matches[1];
            $aid     = $matches[2];
            $value   = clean_param($value, PARAM_BOOL);

            $grade_item = grade_item::fetch(array('id' => $aid, 'courseid' => $courseid));
            $grade_item->$param = $value;

            $grade_item->update();

            $recreatetree = true;
        }
    }
}

$originalweights = grade_helper::fetch_all_natural_weights_for_course($courseid);

/**
 * Callback function to adjust the URL if weights changed after the
 * regrade.
 *
 * @param int $courseid The course ID
 * @param array $originalweights The weights before the regrade
 * @param int $weightsadjusted Whether weights have been adjusted
 * @return moodle_url A URL to redirect to after regrading when a progress bar is displayed.
 */
$grade_edit_moodleset_index_checkweights = function() use ($courseid, $originalweights, &$weightsadjusted) {
    global $PAGE;

    $alteredweights = grade_helper::fetch_all_natural_weights_for_course($courseid);
    if (array_diff($originalweights, $alteredweights)) {
        $weightsadjusted = 1;
        return new moodle_url($PAGE->url, array('weightsadjusted' => $weightsadjusted));
    }
    return $PAGE->url;
};

if (grade_regrade_final_grades_if_required($course, $grade_edit_moodleset_index_checkweights)) {
    $recreatetree = true;
}

print_grade_page_head($courseid, 'settings', 'moodleset', 'MoodleSET Setup', '', '', false);   // disable the selector

// Change mode
echo $OUTPUT->single_button(new moodle_url('../tree/index.php', array('id'=>$course->id)), get_string('gradebook_originalsetup', 'grades'), 'get');

// Print Table of categories and items
echo $OUTPUT->box_start('gradetreebox generalbox');

echo '<form id="gradetreeform" method="post" action="'.$returnurl.'">';
echo '<div>';
echo '<input type="hidden" name="sesskey" value="'.sesskey().'" />';

//did we update something in the db and thus invalidate $grade_edit_moodleset?
if ($recreatetree) {
    $grade_edit_moodleset = new grade_edit_moodleset($gtree, false, $gpr);
}

// Check to see if we have a normalisation message to send.
if ($weightsadjusted) {
    echo $OUTPUT->notification(get_string('weightsadjusted', 'grades'), 'notifymessage');
}

//JK: render table
echo html_writer::table($grade_edit_moodleset->table);

echo '<div id="gradetreesubmit">';

echo '<input class="advanced" type="submit" value="'.get_string('savechanges').'" />';

echo '</div>';
echo '</div></form>';

echo $OUTPUT->box_end();

$PAGE->requires->yui_module('moodle-core-formchangechecker',
    'M.core_formchangechecker.init',
    array(array(
        'formid' => 'gradetreeform'
    ))
);
$PAGE->requires->string_for_js('changesmadereallygoaway', 'moodle');

$PAGE->requires->js('/grade/edit/moodleset/extrafunc.js');   // to check the sum of all weights to be 100%


echo $OUTPUT->footer();
die;


