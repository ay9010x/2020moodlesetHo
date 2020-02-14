<?php

class report_landview_track {
    public static function report_landview_analysis($course_id = null)
	{
        global $CFG, $DB;
        
        ini_set('display_errors', 'On');
        ini_set('error_log', $CFG->dataroot.'/report/landview_'.date("Ymd").'.log');
        error_reporting(E_ALL | E_STRICT);
                $sql = "SELECT * FROM {course} WHERE startdate < :time AND visible = 1 AND id != :siteid ";        if(!empty($course_id)){
            $sql .= " AND id = :courseid";
            
            $DB->delete_records('weekly_log_land', array('courseid'=>$course_id));
        }
        $courses = $DB->get_records_sql($sql, array('time'=>time(), 'siteid'=>SITEID, 'courseid'=>$course_id));
        
		if(!empty($courses)) {
			foreach($courses as $course){
                                if(!$DB->record_exists('weekly_log_land', array('courseid'=>$course->id, 'finally'=>1))){
                    error_log(get_string('fullname').':'.$course->fullname);
                    $sql = "SELECT max(id) FROM {weekly_log_land} WHERE courseid = :courseid";
                    $id = $DB->get_field_sql($sql, array('courseid'=>$course->id));
                    if(!empty($id)){
                        $track = $DB->get_record('weekly_log_land', array('id'=>$id));
                        if(($course->startdate != $track->startdate) || ($course->enddate != $track->enddate)){
                            error_log(get_string('datemodify', 'report_landview', $course));
                        }else{
                            if($course->enddate <= time()){
                                report_landview_track_analysis($course, $course->enddate, true);
                            }else if(($track->cycleend + WEEKSECS) <= time()) {
                                                                report_landview_track_analysis($course, $track->cycleend);
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
                                                                report_landview_track_analysis($course, $starttime);
                            }
                            if(time() >= $course->enddate){
                                                                $starttime = $course->startdate + ($week * WEEKSECS);
                                report_landview_track_analysis($course, $starttime, true);
                            }
                        }
                    }
                }else{
                                        error_log(get_string('isdone', 'report_landview', $course));
                }
            }
		}
        return true;
	}
}

function report_landview_track_analysis($course, $starttime, $finally=false) {
    global $CFG, $DB;

    $data = new stdClass();
    $data->courseid = $course->id;
    $data->startdate = $course->startdate;
    $data->enddate = $course->enddate;
    $data->cyclebegin = $starttime;
    
    if($finally){
        $endtime = $course->enddate;
    }else{
        $endtime = $starttime + WEEKSECS - 1;
    }
    $data->cycleend = $endtime;

    $view = report_landview_track_log($course->id, $starttime, $endtime);
    $user = report_landview_track_log($course->id, $starttime, $endtime, 'l.userid, l.courseid');

    $data->view = $view;
    $data->viewuser = $user;
    if($finally){
        $data->finally = 1;
    }
    $data->timecreated = time();
    
    $DB->insert_record('weekly_log_land', $data);
    error_log(get_string('insertmessage', 'report_landview', $data));
    
    return true;
}

function report_landview_track_log($courseid, $starttime, $endtime, $groupby = ''){
    global $CFG, $DB;
    $role_archetype = "'student','auditor'";
        $params['action']    = 'viewed';
    $params['target']    = 'course';
    $params['courseid']  = $courseid;
    $params['starttime'] = $starttime;
    $params['endtime']   = $endtime;
    $params['guestid']   = $CFG->siteguest;
    
    $sql = "SELECT l.* FROM {logstore_standard_log} l
            LEFT JOIN {context} ctx ON l.courseid = ctx.instanceid
            LEFT JOIN {role_assignments} ra ON l.userid = ra.userid AND ra.contextid = ctx.id
            LEFT JOIN {role} r ON r.id = ra.roleid
            WHERE ctx.contextlevel = 50 AND r.archetype in($role_archetype)
            AND l.courseid = :courseid AND l.action = :action AND l.target = :target
            AND (l.timecreated >= :starttime AND l.timecreated < :endtime)
            AND l.userid != :guestid";
    if(!empty($groupby)){
        $sql .= " GROUP BY $groupby";
    }
    $records = $DB->get_records_sql($sql, $params);
    return sizeof($records);
}