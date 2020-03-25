<?php

/**
 * Transfer back to the simple Gradebook setup page.
 * by YCJ 2017.12.11
 */

require_once '../../../config.php';
require_once $CFG->dirroot.'/grade/lib.php';

$courseid = required_param('id', PARAM_INT);
$gtree = new grade_tree($courseid, false, false);
$gtree_element = $gtree->top_element;
$catid    = $gtree_element['object']->id;      // grade_category->id
//$catid    = required_param('catid', PARAM_INT);  

$course = $DB->get_record('course', array('id' => $courseid));
 
require_login($course);
$context = context_course::instance($course->id);
require_capability('moodle/grade:manage', $context);

// get the current grade_category
$grade_category = grade_category::fetch(array('id'=>$catid, 'courseid'=>$course->id));

if ($grade_category->aggregation != GRADE_AGGREGATE_WEIGHTED_MEAN) {
		$grade_category->apply_forced_settings();
		
		// Reset the aggregation method & Update
		grade_category::set_properties($grade_category, array('aggregation' => GRADE_AGGREGATE_WEIGHTED_MEAN));
		$grade_category->update();
}

// Back to the simple Gradebook setup page
$url = new moodle_url('/grade/edit/moodleset/index.php', array('id' => $courseid));
redirect($url);

die;


