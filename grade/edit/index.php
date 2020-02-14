<?php
/**
 * Redirect to the last-time grade setup page
 *
 * by Y.C.J. 2107.12.13
 *
 */
 
require_once '../../config.php';
require_once $CFG->dirroot.'/grade/lib.php';

$id = required_param('id', PARAM_INT);

/// Make sure they can even access this course
if (!$course = $DB->get_record('course', array('id' => $id))) {
    print_error('nocourseid');
}

require_login($course);
$context = context_course::instance($course->id);
require_capability('moodle/grade:manage', $context);

$nowpage = $DB->get_record('grade_categories_setuppage', array('courseid' => $id));

if (!$nowpage) {
	$gtree = new grade_tree($id, false, false);
  $gtree_element = $gtree->top_element;
  $catid = $gtree_element['object']->id;
  $grade_category = grade_category::fetch(array('id'=>$catid, 'courseid'=>$course->id));
  
	$newitem = new stdClass();
	$newitem->courseid = $id;
	
	if ($grade_category->aggregation == GRADE_AGGREGATE_WEIGHTED_MEAN) {
	  $newitem->setuppage = 0;	
	  $url = new moodle_url('moodleset/index.php', array('id' => $id));
	} else {
	  $newitem->setuppage = 1;	
	  $url = new moodle_url('tree/index.php', array('id' => $id));		
  }
  
  $DB->insert_record('grade_categories_setuppage', $newitem, false);
  
}	else if ($nowpage->setuppage == 0) {
	$url = new moodle_url('moodleset/index.php', array('id' => $id));
  //redirect('moodleset/index.php?id='.$id);
} else {
	$url = new moodle_url('tree/index.php', array('id' => $id));
  //redirect('tree/index.php?id='.$id);
}

redirect($url);
die;