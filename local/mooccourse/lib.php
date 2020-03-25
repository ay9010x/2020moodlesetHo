<?php

function local_mooccourse_information_standard_log_update($course) {
    $eventdata = array();
    $eventdata['objectid'] = $course->id;
    $eventdata['context'] = context_course::instance($course->id);
    $eventdata['other'] = array('shortname' => $course->shortname);
    $event = \local_mooccourse\event\information_updated::create($eventdata);
    $event->trigger();
}

function local_mooccourse_automatic_create_activity($course){
    
    local_mooccourse_create_newforum($course->id);
    local_mooccourse_create_forum($course->id);
    local_mooccourse_create_attendance($course->id);
    local_mooccourse_create_quiz($course->id);
}

function local_mooccourse_create_newforum($courseid){
    global $CFG, $DB;
    if ($module = $DB->get_record("modules", array("name" => "forum"))) {
        require_once($CFG->dirroot.'/mod/forum/lib.php');
        $forum = forum_get_course_forum($courseid, 'news');
    }
}

function local_mooccourse_create_forum($courseid){
    global $CFG, $DB;
    include_once($CFG->dirroot.'/course/lib.php');
    
    if ($module = $DB->get_record("modules", array("name" => "forum"))) {
        $forum = new stdClass();
        $forum->course = $courseid;
        $forum->type = "general";
        $forum->name = '課程內容討論區';
        $forum->intro = '課程內容討論區';
        $forum->timemodified = time();
        $forum->id = $DB->insert_record("forum", $forum);
        
        $mod = new stdClass();
        $mod->course = $courseid;
        $mod->module = $module->id;
        $mod->instance = $forum->id;
        $mod->section = 0;
        if ($mod->coursemodule = add_course_module($mod) ) {
            $sectionid = course_add_cm_to_section($courseid, $mod->coursemodule, 0);
        }        
    }
}

function local_mooccourse_create_attendance($courseid){
    global $CFG, $DB;
    include_once($CFG->dirroot.'/course/lib.php');
    include_once($CFG->dirroot.'/mod/attendance/lib.php');

    if ($module = $DB->get_record("modules", array("name" => "attendance"))) {
        $att = new stdClass();
        $att->course = $courseid;
        $att->name = '出缺席';
        $att->cmidnumber = '';
        $att->grade = 100;
        $att->gradecat = '1';
        $att->timemodified = time();        
        
        $att->id = $DB->insert_record('attendance', $att);
        att_add_default_statuses($att->id);
        attendance_grade_item_update($att);
        
        $mod = new stdClass();
        $mod->course = $courseid;
        $mod->module = $module->id;
        $mod->instance = $att->id;
        $mod->section = 0;
        if ($mod->coursemodule = add_course_module($mod) ) {
            $sectionid = course_add_cm_to_section($courseid, $mod->coursemodule, 0);
        }
    }
}

function local_mooccourse_create_quiz($courseid){
    global $CFG, $DB;
    include_once($CFG->dirroot.'/course/lib.php');
    include_once($CFG->dirroot.'/mod/quiz/lib.php');

    if ($module = $DB->get_record("modules", array("name" => "quiz"))) {
        $quizconfig = get_config('quiz');
        $option = array('期中考','期末考');
        foreach($option as $name){
            $quiz = new stdClass();
            $quiz->course = $courseid;
            $quiz->name = $name;
            $quiz->intro = $name;
            $quiz->timeopen = 0;
            $quiz->timeclose = 0;
            $quiz->introformat = FORMAT_HTML;
            $quiz->timelimit = $quizconfig->timelimit;
            $quiz->overduehandling = $quizconfig->overduehandling;
            $quiz->graceperiod = $quizconfig->graceperiod;
            $quiz->grade = $quizconfig->maximumgrade;
            $quiz->attempts = $quizconfig->attempts;
            $quiz->grademethod = $quizconfig->grademethod;
            $quiz->questionsperpage = $quizconfig->questionsperpage;
            $quiz->navmethod = $quizconfig->navmethod;
            $quiz->shuffleanswers = $quizconfig->shuffleanswers;
            $quiz->preferredbehaviour = $quizconfig->preferredbehaviour;
            $quiz->canredoquestions = $quizconfig->canredoquestions;
            $quiz->attemptonlast = $quizconfig->attemptonlast;
            $quiz->showuserpicture = $quizconfig->showuserpicture;
            $quiz->decimalpoints = $quizconfig->decimalpoints;
            $quiz->questiondecimalpoints = $quizconfig->questiondecimalpoints;
            $quiz->showblocks = $quizconfig->showblocks;
            $quiz->quizpassword = $quizconfig->password;
            $quiz->subnet = $quizconfig->subnet;
            $quiz->delay1 = $quizconfig->delay1;
            $quiz->delay2 = $quizconfig->delay2;
            $quiz->browsersecurity = $quizconfig->browsersecurity;
            $quiz->feedbackboundarycount = 0;
            $quiz->created = time();
            
            quiz_process_options($quiz);
            $quiz->id = $DB->insert_record('quiz', $quiz);
            $DB->insert_record('quiz_sections', array('quizid' => $quiz->id, 'firstslot' => 1, 'heading' => '', 'shufflequestions' => 0));
            
            $mod = new stdClass();
            $mod->course = $courseid;
            $mod->module = $module->id;
            $mod->instance = $quiz->id;
            $mod->section = 0;
            if ($mod->coursemodule = add_course_module($mod) ) {
                $sectionid = course_add_cm_to_section($courseid, $mod->coursemodule, 0);
            }
            $quiz->coursemodule = $mod->coursemodule;
            quiz_after_add_or_update($quiz);
        }
    }
}