<?php



require_once($CFG->dirroot . '/mod/attendance/locallib.php');

class mod_attendance_summary {

    
    private $attendanceid;

    
    private $course;

    
    private $groupmode;

    
    private $userspoints;

    
    private $maxpointsbygroupsessions;

    
    public function __construct($attendanceid, $userids=array(), $startdate = '', $enddate = '') {
        $this->attendanceid = $attendanceid;

        $this->compute_users_points($userids, $startdate, $enddate);
    }

    
    public function has_taken_sessions($userid) {
        return isset($this->userspoints[$userid]);
    }

    
    public function with_groups() {
        return $this->groupmode > 0;
    }

    
    public function get_groupmode() {
        return $this->groupmode;
    }

    
    public function get_user_taken_sessions_percentages() {
        $percentages = array();

        foreach ($this->userspoints as $userid => $userpoints) {
            $percentages[$userid] = attendance_calc_fraction($userpoints->points, $userpoints->maxpoints);
        }

        return $percentages;
    }

    
    public function get_taken_sessions_summary_for($userid) {
        $usersummary = new stdClass();
        if ($this->has_taken_sessions($userid)) {
            $usersummary->numtakensessions = $this->userspoints[$userid]->numtakensessions;
            $usersummary->takensessionspoints = $this->userspoints[$userid]->points;
            $usersummary->takensessionsmaxpoints = $this->userspoints[$userid]->maxpoints;
        } else {
            $usersummary->numtakensessions = 0;
            $usersummary->takensessionspoints = 0;
            $usersummary->takensessionsmaxpoints = 0;
        }
        $usersummary->takensessionspercentage = attendance_calc_fraction($usersummary->takensessionspoints,
                                                                         $usersummary->takensessionsmaxpoints);

        return $usersummary;
    }

    
    public function get_all_sessions_summary_for($userid) {
        $usersummary = $this->get_taken_sessions_summary_for($userid);

        if (!isset($this->maxpointsbygroupsessions)) {
            $this->compute_maxpoints_by_group_session();
        }

        $usersummary->numallsessions = $this->maxpointsbygroupsessions[0]->numsessions;
        $usersummary->allsessionsmaxpoints = $this->maxpointsbygroupsessions[0]->maxpoints;

        if ($this->with_groups()) {
            $groupids = array_keys(groups_get_all_groups($this->course->id, $userid));
            foreach ($groupids as $gid) {
                if (isset($this->maxpointsbygroupsessions[$gid])) {
                    $usersummary->numallsessions += $this->maxpointsbygroupsessions[$gid]->numsessions;
                    $usersummary->allsessionsmaxpoints += $this->maxpointsbygroupsessions[$gid]->maxpoints;
                }
            }
        }
        $usersummary->allsessionspercentage = attendance_calc_fraction($usersummary->takensessionspoints,
                                                                       $usersummary->allsessionsmaxpoints);

        $deltapoints = $usersummary->allsessionsmaxpoints - $usersummary->takensessionsmaxpoints;
        $usersummary->maxpossiblepoints = $usersummary->takensessionspoints + $deltapoints;
        $usersummary->maxpossiblepercentage = attendance_calc_fraction($usersummary->maxpossiblepoints,
                                                                       $usersummary->allsessionsmaxpoints);

        return $usersummary;
    }

    
    private function compute_users_points($userids=array(), $startdate = '', $enddate = '') {
        global $DB;

        list($this->course, $cm) = get_course_and_cm_from_instance($this->attendanceid, 'attendance');
        $this->groupmode = $cm->effectivegroupmode;

        $params = array(
            'attid'      => $this->attendanceid,
            'attid2'     => $this->attendanceid,
            'cstartdate' => $this->course->startdate,
            );

        $where = '';
        if (!empty($userids)) {
            list($insql, $inparams) = $DB->get_in_or_equal($userids, SQL_PARAMS_NAMED);
            $where .= ' AND atl.studentid ' . $insql;
            $params = array_merge($params, $inparams);
        }
        if (!empty($startdate)) {
            $where .= ' AND ats.sessdate >= :startdate';
            $params['startdate'] = $startdate;
        }
        if (!empty($enddate)) {
            $where .= ' AND ats.sessdate < :enddate ';
            $params['enddate'] = $enddate;
        }

        $joingroup = '';
        if ($this->with_groups()) {
            $joingroup = 'LEFT JOIN {groups_members} gm ON (gm.userid = atl.studentid AND gm.groupid = ats.groupid)';
            $where .= ' AND (ats.groupid = 0 or gm.id is NOT NULL)';
        } else {
            $where .= ' AND ats.groupid = 0';
        }

        $sql = " SELECT atl.studentid AS userid, COUNT(DISTINCT ats.id) AS numtakensessions,
                        SUM(stg.grade) AS points, SUM(stm.maxgrade) AS maxpoints
                   FROM {attendance_sessions} ats
                   JOIN {attendance_log} atl ON (atl.sessionid = ats.id)
                   JOIN {attendance_statuses} stg ON (stg.id = atl.statusid AND stg.deleted = 0 AND stg.visible = 1)
                   JOIN (SELECT setnumber, MAX(grade) AS maxgrade
                           FROM {attendance_statuses}
                          WHERE attendanceid = :attid2
                            AND deleted = 0
                            AND visible = 1
                         GROUP BY setnumber) stm
                     ON (stm.setnumber = ats.statusset)
                   {$joingroup}
                  WHERE ats.attendanceid = :attid
                    AND ats.sessdate >= :cstartdate
                    AND ats.lasttakenby != 0
                    {$where}
                GROUP BY atl.studentid";
        $this->userspoints = $DB->get_records_sql($sql, $params);
    }

    
    private function compute_maxpoints_by_group_session() {
        global $DB;

        $params = array(
            'attid'      => $this->attendanceid,
            'attid2'     => $this->attendanceid,
            'cstartdate' => $this->course->startdate,
            );

        $where = '';
        if (!$this->with_groups()) {
            $where = 'AND sess.groupid = 0';
        }

        $sql = "SELECT sess.groupid, COUNT(*) AS numsessions, SUM(stamax.maxgrade) AS maxpoints
                  FROM {attendance_sessions} sess
                  JOIN (SELECT setnumber, MAX(grade) AS maxgrade
                                             FROM {attendance_statuses}
                                            WHERE attendanceid = :attid2
                                              AND deleted = 0
                                              AND visible = 1
                                           GROUP BY setnumber) stamax
                    ON (stamax.setnumber = sess.statusset)
                 WHERE sess.attendanceid = :attid
                   AND sess.sessdate >= :cstartdate
                   {$where}
              GROUP BY sess.groupid";
        $this->maxpointsbygroupsessions = $DB->get_records_sql($sql, $params);

        if (!isset($this->maxpointsbygroupsessions[0])) {
            $gpoints = new stdClass();
            $gpoints->numsessions = 0;
            $gpoints->maxpoints = 0;
            $this->maxpointsbygroupsessions[0] = $gpoints;
        }
    }
}
