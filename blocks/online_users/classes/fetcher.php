<?php



namespace block_online_users;

defined('MOODLE_INTERNAL') || die();


class fetcher {

    
    public $sql;
    
    public $csql;
    
    public $params;

    
    public function __construct($currentgroup, $now, $timetoshowusers, $context, $sitelevel = true, $courseid = null) {
        $this->set_sql($currentgroup, $now, $timetoshowusers, $context, $sitelevel, $courseid);
    }

    
    protected function set_sql($currentgroup, $now, $timetoshowusers, $context, $sitelevel, $courseid) {
        $timefrom = 100 * floor(($now - $timetoshowusers) / 100); 
        $groupmembers = "";
        $groupselect  = "";
        $groupby       = "";
        $lastaccess    = ", lastaccess";
        $timeaccess    = ", ul.timeaccess AS lastaccess";
        $params = array();

        $userfields = \user_picture::fields('u', array('username'));

                if ($currentgroup !== null) {
            $groupmembers = ", {groups_members} gm";
            $groupselect = "AND u.id = gm.userid AND gm.groupid = :currentgroup";
            $groupby = "GROUP BY $userfields";
            $lastaccess = ", MAX(u.lastaccess) AS lastaccess";
            $timeaccess = ", MAX(ul.timeaccess) AS lastaccess";
            $params['currentgroup'] = $currentgroup;
        }

        $params['now'] = $now;
        $params['timefrom'] = $timefrom;
        if ($sitelevel) {
            $sql = "SELECT $userfields $lastaccess
                      FROM {user} u $groupmembers
                     WHERE u.lastaccess > :timefrom
                           AND u.lastaccess <= :now
                           AND u.deleted = 0
                           $groupselect $groupby
                  ORDER BY lastaccess DESC ";

            $csql = "SELECT COUNT(u.id)
                      FROM {user} u $groupmembers
                     WHERE u.lastaccess > :timefrom
                           AND u.lastaccess <= :now
                           AND u.deleted = 0
                           $groupselect";

        } else {
                                    list($esqljoin, $eparams) = get_enrolled_sql($context);
            $params = array_merge($params, $eparams);

            $sql = "SELECT $userfields $timeaccess
                      FROM {user_lastaccess} ul $groupmembers, {user} u
                      JOIN ($esqljoin) euj ON euj.id = u.id
                     WHERE ul.timeaccess > :timefrom
                           AND u.id = ul.userid
                           AND ul.courseid = :courseid
                           AND ul.timeaccess <= :now
                           AND u.deleted = 0
                           $groupselect $groupby
                  ORDER BY lastaccess DESC";

            $csql = "SELECT COUNT(u.id)
                      FROM {user_lastaccess} ul $groupmembers, {user} u
                      JOIN ($esqljoin) euj ON euj.id = u.id
                     WHERE ul.timeaccess > :timefrom
                           AND u.id = ul.userid
                           AND ul.courseid = :courseid
                           AND ul.timeaccess <= :now
                           AND u.deleted = 0
                           $groupselect";

            $params['courseid'] = $courseid;
        }
        $this->sql = $sql;
        $this->csql = $csql;
        $this->params = $params;
    }

    
    public function get_users($userlimit = 0) {
        global $DB;
        $users = $DB->get_records_sql($this->sql, $this->params, 0, $userlimit);
        return $users;
    }

    
    public function count_users() {
        global $DB;
        return $DB->count_records_sql($this->csql, $this->params);
    }

}
