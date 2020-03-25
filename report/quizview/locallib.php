<?php

class report_quizview_track {
    public static function report_quizview_analysis($course_id = null)
	{
        global $CFG, $DB;
        
        ini_set('display_errors', 'On');
        ini_set('error_log', $CFG->dataroot.'/report/quizview_'.date("Ymd").'.log');
        error_reporting(E_ALL | E_STRICT);
                $sql = "SELECT * FROM {course} WHERE startdate < :time AND visible = 1 AND id != :siteid ";
        if(!empty($course_id)){
            $sql .= " AND id = :courseid";
            
            $DB->delete_records('weekly_log_quiz', array('courseid'=>$course_id));
        }
        $courses = $DB->get_records_sql($sql, array('time'=>time(), 'siteid'=>SITEID, 'courseid'=>$course_id));
		if(!empty($courses)) {
			foreach($courses as $course){
                                if(!$DB->record_exists('weekly_log_quiz', array('courseid'=>$course->id, 'finally'=>1))){
                    error_log(get_string('fullname').':'.$course->fullname);
                    $sql = "SELECT max(id) FROM {weekly_log_quiz} WHERE courseid = :courseid";
                    $id = $DB->get_field_sql($sql, array('courseid'=>$course->id));
                    if(!empty($id)){
                        $track = $DB->get_record('weekly_log_quiz', array('id'=>$id));
                        if(($course->startdate != $track->startdate) || ($course->enddate != $track->enddate)){
                            error_log(get_string('datemodify', 'report_quizview', $course));
                        }else{
                            if($course->enddate <= time()){
                                report_quizview_track_analysis($course, $course->enddate, true);
                            }else if(($track->cycleend + WEEKSECS) <= time()) {
                                                                report_quizview_track_analysis($course, $track->cycleend);
                            }
                        }
                    }else{
                                                if(($course->startdate + WEEKSECS) <= time() or (($course->enddate - $course->startdate) < WEEKSECS)) {
                            if($course->enddate <= time()){
                                $calculate = ($course->enddate - $course->startdate) / WEEKSECS;
                            }else{
                                $calculate = (time() - $course->startdate) / WEEKSECS;
                            }
                            $week = floor($calculate);
                            $starttime = 0;
                            for($i=0; $i < $week; $i++){
                                $starttime = $course->startdate + ($i * WEEKSECS);
                                                                report_quizview_track_analysis($course, $starttime);
                            }
                            if(time() >= $course->enddate){
                                                                $starttime = $course->startdate + ($week * WEEKSECS);
                                report_quizview_track_analysis($course, $starttime, true);
                            }
                        }
                    }
                }else{
                                        error_log(get_string('isdone', 'report_quizview', $course));
                }
            }
		}
        return true;
	}
}

function report_quizview_track_analysis($course, $starttime, $finally=false) {
    global $CFG, $DB;
    
    $numsections = $DB->get_field('course_format_options', 'value', array('courseid'=>$course->id, 'name'=>'numsections'));
    
    $data = new stdClass();
    $data->courseid = $course->id;
    $data->startdate = $course->startdate;
    $data->enddate = $course->enddate;
    $data->cyclebegin = $starttime;
    
    if($finally){
        $endtime = $course->enddate;
    }
    else{
        $endtime = $starttime + WEEKSECS - 1;
    }
    $data->cycleend = $endtime;
    
    $activitytypes = array('quiz', 'workshop');
    $modulename = "''";
    foreach($activitytypes as $at){
        $modulename .= ",'". $at ."'";
    }
    $sql = "SELECT cm.*,m.name as modulename FROM {course_modules} cm 
            LEFT JOIN {modules} m ON m.id = cm.module
            WHERE cm.course = :courseid AND m.name in ($modulename)";
    $modules = $DB->get_records_sql($sql, array('courseid'=>$course->id));
    
    foreach($modules as $mod){
                if($mod->modulename == 'quiz'){
                        $component = 'mod_quiz';
            $action = 'submitted';
            $target = 'attempt';
        }else if($mod->modulename == 'workshop'){
                                                $component = 'mod_workshop';
            $action = 'created';
            $target = 'submission';
        }
                                                                                                                             
        $data->cmid        = $mod->id;
        $data->module      = $mod->modulename;
        $data->instance    = $mod->instance;
        $data->view        = report_quizview_track_log($course->id, $starttime, $endtime, $mod->id, $component, 'viewed');;
        $data->viewuser    = report_quizview_track_log($course->id, $starttime, $endtime, $mod->id, $component, 'viewed', '', 'l.userid,l.courseid,l.component,l.contextinstanceid');;
        $data->attempt     = report_quizview_track_log($course->id, $starttime, $endtime, $mod->id, $component, $action, $target);
        $data->attemptuser = report_quizview_track_log($course->id, $starttime, $endtime, $mod->id, $component, $action, $target, 'l.userid,l.courseid,l.component,l.contextinstanceid');
        if($finally){
            $data->finally = 1;
        }
        $data->timecreated = time();

        $DB->insert_record('weekly_log_quiz', $data);
        error_log(get_string('insertmessage', 'report_quizview', $data));
    }
    return true;
}

function report_quizview_track_log($courseid, $starttime, $endtime, $cmid, $component = '', $action = 'viewed', $target = '', $groupby = ''){
    global $CFG, $DB;
    $role_archetype = "'student','auditor'";
    
    $params['action']    = $action;
    $params['courseid']  = $courseid;
    $params['cmid']      = $cmid;
    $params['starttime'] = $starttime;
    $params['endtime']   = $endtime;
    $params['guestid']   = $CFG->siteguest;
    
    $sql = "SELECT l.* FROM {logstore_standard_log} l
            LEFT JOIN {context} ctx ON l.courseid = ctx.instanceid
            LEFT JOIN {role_assignments} ra ON l.userid = ra.userid AND ra.contextid = ctx.id
            LEFT JOIN {role} r ON r.id = ra.roleid
            WHERE ctx.contextlevel = 50 AND r.archetype in($role_archetype)
            AND l.action = :action AND l.courseid = :courseid
            AND l.contextinstanceid = :cmid
            AND (l.timecreated >= :starttime AND l.timecreated < :endtime)
            AND l.userid != :guestid";
    if(!empty($component)){
        $sql .= " AND l.component = :component";
        $params['component']    = $component;
    }
    if(!empty($target)){
        $sql .= " AND l.target = :target";
        $params['target']    = $target;
    }
    if(!empty($groupby)){
        $sql .= " GROUP BY $groupby";
    }
    $records = $DB->get_records_sql($sql, $params);
    return sizeof($records);
}