<?php

class report_materialview_track {
    public static function report_materialview_analysis($course_id = null)
	{
        global $CFG, $DB;
        
        ini_set('display_errors', 'On');
        ini_set('error_log', $CFG->dataroot.'/report/materialview_'.date("Ymd").'.log');
        error_reporting(E_ALL | E_STRICT);
                $sql = "SELECT * FROM {course} WHERE startdate < :time AND visible = 1 AND id != :siteid ";
        if(!empty($course_id)){
            $sql .= " AND id = :courseid";
            
            $DB->delete_records('weekly_log_material', array('courseid'=>$course_id));
        }
        $courses = $DB->get_records_sql($sql, array('time'=>time(), 'siteid'=>SITEID, 'courseid'=>$course_id));

		if(!empty($courses)) {
			foreach($courses as $course){
                                if(!$DB->record_exists('weekly_log_material', array('courseid'=>$course->id, 'finally'=>1))){
                    error_log(get_string('fullname').':'.$course->fullname);
                    $sql = "SELECT max(id) FROM {weekly_log_material} WHERE courseid = :courseid";
                    $id = $DB->get_field_sql($sql, array('courseid'=>$course->id));
                    if(!empty($id)){
                                                $track = $DB->get_record('weekly_log_material', array('id'=>$id));
                        if(($course->startdate != $track->startdate) || ($course->enddate != $track->enddate)){
                            error_log(get_string('datemodify', 'report_materialview', $course));
                        }else{
                            if($course->enddate <= time()){
                                report_materialview_track_analysis($course, $course->enddate, true);
                            }else if(($track->cycleend + WEEKSECS) <= time()) {
                                                                report_materialview_track_analysis($course, $track->cycleend);
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
                                                                report_materialview_track_analysis($course, $starttime);
                            }
                            if(time() >= $course->enddate){
                                                                $starttime = $course->startdate + ($week * WEEKSECS);
                                report_materialview_track_analysis($course, $starttime, true);
                            }
                        }
                    }
                }else{
                                        error_log(get_string('isdone', 'report_materialview', $course));
                }
            }
		}
        return true;
	}
}

function report_materialview_track_analysis($course, $starttime, $finally=false) {
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

    $activitytypes = array('resource');
    $modulename = $componentname = "";
    foreach($activitytypes as $at){
        $modulename .= "'". $at ."'";
        $componentname = "'mod_". $at ."'";
    }

    $sql = "SELECT * FROM {course_sections} WHERE course = :courseid AND section != 0 AND section <= :sections ";
    $sections = $DB->get_records_sql($sql, array('courseid'=>$course->id, 'sections'=>$numsections));
    foreach($sections as $section){
        $data->sectionid = $section->id;
        if(!empty($section->sequence)){
            $sql = "SELECT count(cm.id) FROM {course_modules} cm
                    LEFT JOIN {modules} m ON cm.module = m.id
                    WHERE cm.course = :coursid AND cm.id in ($section->sequence)
                    AND m.name in ($modulename)
                    AND cm.added <= :endtime";
            $sequence = $DB->count_records_sql($sql, array('coursid'=>$course->id, 'endtime'=>$endtime));
        }else{
            $sequence = 0;
        }
        if($sequence !=0){
            $view = report_materialview_track_log($componentname, $course->id, $section->sequence, $starttime, $endtime, 'viewed');
            $user = report_materialview_track_log($componentname, $course->id, $section->sequence, $starttime, $endtime, 'viewed', 'l.userid,l.courseid,l.component,l.contextinstanceid');
            
            $download     = report_materialview_track_log($componentname, $course->id, $section->sequence, $starttime, $endtime, 'downloaded');
            $downloaduser = report_materialview_track_log($componentname, $course->id, $section->sequence, $starttime, $endtime, 'downloaded', 'l.userid,l.courseid,l.component,l.contextinstanceid');
        }else{
                    $sequence = 0;
            $view = $user = $download = $downloaduser = '0';
        }
        $data->modcount = $sequence;
        $data->view = $view;
        $data->viewuser = $user;
        $data->download = $view;
        $data->downloaduser = $user;
        if($finally){
            $data->finally = 1;
        }
        $data->timecreated = time();

        $DB->insert_record('weekly_log_material', $data);
        error_log(get_string('insertmessage', 'report_materialview', $data));
    }
    return true;
}

function report_materialview_track_log($modules, $courseid, $sequences, $starttime, $endtime, $action = 'viewed', $groupby = ''){
    global $CFG, $DB;
    $role_archetype = "'student','auditor'";
    
    $params['action']    = $action;
    $params['target']    = 'course_module';
    $params['courseid']  = $courseid;
    $params['starttime'] = $starttime;
    $params['endtime']   = $endtime;
    $params['guestid']   = $CFG->siteguest;
    
    $sql = "SELECT l.* FROM {logstore_standard_log} l
            LEFT JOIN {context} ctx ON l.courseid = ctx.instanceid
            LEFT JOIN {role_assignments} ra ON l.userid = ra.userid AND ra.contextid = ctx.id
            LEFT JOIN {role} r ON r.id = ra.roleid
            WHERE ctx.contextlevel = 50 AND r.archetype in($role_archetype)
            AND l.action = :action AND l.target = :target AND l.courseid = :courseid
            AND l.component in ($modules) 
            AND (l.timecreated >= :starttime AND l.timecreated < :endtime)
            AND l.userid != :guestid";
    if(!empty($sequences)){
        $sql .= " AND l.contextinstanceid in ($sequences)";
    }
    if(!empty($groupby)){
        $sql .= " GROUP BY $groupby";
    }
    $records = $DB->get_records_sql($sql, $params);
    return sizeof($records);
}