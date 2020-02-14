<?php

class report_forumview_track {
    public static function report_forumview_analysis($course_id = null)
	{
        global $CFG, $DB;
        
        ini_set('display_errors', 'On');
        ini_set('error_log', $CFG->dataroot.'/report/forumview_'.date("Ymd").'.log');
        error_reporting(E_ALL | E_STRICT);
                $sql = "SELECT * FROM {course} WHERE startdate < :time AND visible = 1 AND id != :siteid ";
        if(!empty($course_id)){
            $sql .= " AND id = :courseid";
            
            $DB->delete_records('weekly_log_forum', array('courseid'=>$course_id));
        }
        $courses = $DB->get_records_sql($sql, array('time'=>time(), 'siteid'=>SITEID, 'courseid'=>$course_id));
        
		if(!empty($courses)) {
			foreach($courses as $course){
                                if(!$DB->record_exists('weekly_log_forum', array('courseid'=>$course->id, 'finally'=>1))){
                    error_log(get_string('fullname').':'.$course->fullname);
                    $sql = "SELECT max(id) FROM {weekly_log_forum} WHERE courseid = :courseid";
                    $id = $DB->get_field_sql($sql, array('courseid'=>$course->id));
                    if(!empty($id)){
                        $track = $DB->get_record('weekly_log_forum', array('id'=>$id));
                        if(($course->startdate != $track->startdate) || ($course->enddate != $track->enddate)){
                            error_log(get_string('datemodify', 'report_forumview', $course));
                        }else{
                            if($course->enddate <= time()){
                                report_forumview_track_analysis($course, $course->enddate, true);
                            }else if(($track->cycleend + WEEKSECS) <= time()) {
                                                                report_forumview_track_analysis($course, $track->cycleend);
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
                                                                report_forumview_track_analysis($course, $starttime);
                            }
                            if(time() >= $course->enddate){
                                                                $starttime = $course->startdate + ($week * WEEKSECS);
                                report_forumview_track_analysis($course, $starttime, true);
                            }
                        }
                    }
                }else{
                                        error_log(get_string('isdone', 'report_forumview', $course));
                }
            }
		}
        return true;
	}
}

function report_forumview_track_analysis($course, $starttime, $finally=false) {
    global $CFG, $DB;
    
    $numsections = $DB->get_field('course_format_options', 'value', array('courseid'=>$course->id, 'name'=>'numsections'));
    
    $data             = new stdClass();
    $data->courseid   = $course->id;
    $data->startdate  = $course->startdate;
    $data->enddate    = $course->enddate;
    $data->cyclebegin = $starttime;
    
    if($finally){
        $endtime = $course->enddate;
    }
    else{
        $endtime = $starttime + WEEKSECS - 1;
    }
    $data->cycleend = $endtime;

    $modulename = "'forum'";
    $sql = "SELECT cm.*,m.name as modulename FROM {course_modules} cm 
            LEFT JOIN {modules} m ON m.id = cm.module
            WHERE cm.course = :courseid AND m.name in ($modulename)";
    $modules = $DB->get_records_sql($sql, array('courseid'=>$course->id));
    
    foreach($modules as $mod){
        if($DB->get_field('forum', 'type', array('id'=>$mod->instance)) != 'news' ){
            $discussion = 0;
            $sql = "SELECT id FROM {forum_discussions} WHERE forum = :forum AND timemodified <= :endtime";
            $discussions = $DB->get_records_sql($sql, array('forum'=>$mod->instance, 'endtime'=>$endtime));
            $discussionuser = $view = $user = $post = $postuser = 0;
            
            $view = $view + report_forumview_track_log($course->id, $mod->id, $mod->instance, $starttime, $endtime, 'viewed');
            foreach($discussions as $d){
                $discussion++;
                $discussionuser = $discussionuser + report_forumview_track_log($course->id, $mod->id, $d->id, $starttime, $endtime, 'created', 'discussion', 'l.userid,l.courseid,l.component,l.contextinstanceid');
                                $view = $view + report_forumview_track_log($course->id, $mod->id, $d->id, $starttime, $endtime, 'viewed');
                $user = $user + report_forumview_track_log($course->id, $mod->id, $d->id, $starttime, $endtime, 'viewed', '', 'l.userid,l.courseid,l.component,l.contextinstanceid');
               
                                $post = $post+ report_forumview_track_get_view_post_log($course->id, $mod->id, $d->id, $starttime, $endtime);
                $postuser = $postuser + report_forumview_track_get_view_post_log($course->id, $mod->id, $d->id, $starttime, $endtime, 'l.userid,l.courseid,l.component,l.contextinstanceid');
            }

            $data->sectionid        = $mod->section;
            $data->cmid             = $mod->id;
            $data->module           = $mod->modulename;
            $data->instance         = $mod->instance;
            $data->view             = $view;
            $data->viewuser         = $user;
            $data->discussion       = $discussion;
            $data->discussionuser   = $discussionuser;
            $data->postview         = $post;
            $data->postuser         = $postuser;
            
            if($finally){
                $data->finally = 1;
            }
            $data->timecreated = time();

            $DB->insert_record('weekly_log_forum', $data);
            error_log(get_string('insertmessage', 'report_forumview', $data));
        }
    }
    return true;
}

function report_forumview_track_log($courseid, $cmid, $objectid, $starttime, $endtime, $action = 'viewed', $target = '', $groupby = ''){
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
            AND l.contextinstanceid = :cmid AND l.component = 'mod_forum' 
            AND (l.timecreated >= :starttime AND l.timecreated < :endtime)
            AND l.userid != :guestid";
    if(!empty($objectid)){
        $sql .= " AND l.objectid = :objectid";
        $params['objectid']    = $objectid;
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

function report_forumview_track_get_view_post_log($courseid, $cmid, $discussionid, $starttime, $endtime, $groupby = ''){
    global $CFG, $DB;
    $roleid = '5';
    
    $params['action']       = 'created';
    $params['target']       = 'post';
    $params['courseid']     = $courseid;
    $params['cmid']         = $cmid;
    $params['discussionid'] = $discussionid;
    $params['starttime']    = $starttime;
    $params['endtime']      = $endtime;
    $params['guestid']      = $CFG->siteguest;
    
    $sql = "SELECT l.* FROM {logstore_standard_log} l
            LEFT JOIN {context} ctx ON l.courseid = ctx.instanceid
            LEFT JOIN {role_assignments} ra ON l.userid = ra.userid AND ra.contextid = ctx.id
            LEFT JOIN {forum_posts} p ON l.objectid = p.id
            WHERE ctx.contextlevel = 50 AND ra.roleid in($roleid)
            AND l.action = :action AND l.target = :target AND l.courseid = :courseid
            AND l.contextinstanceid = :cmid AND p.discussion = :discussionid AND l.component = 'mod_forum'
            AND (l.timecreated >= :starttime AND l.timecreated < :endtime)
            AND l.userid != :guestid";

    if(!empty($groupby)){
        $sql .= " GROUP BY $groupby";
    }
    
    $records = $DB->get_records_sql($sql, $params);
    return sizeof($records);
}