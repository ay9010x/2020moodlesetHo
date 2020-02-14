<?php
namespace tool_monitor\task;
use tool_monitor\subscription;
use tool_monitor\subscription_manager;


class check_subscriptions extends \core\task\scheduled_task {

    
    protected $userssetupcache = array();

    
    protected $courseaccesscache = array();

    
    public function get_name() {
        return get_string('taskchecksubscriptions', 'tool_monitor');
    }

    
    public function execute() {
        global $DB;

        if (!get_config('tool_monitor', 'enablemonitor')) {
            return;         }

        $toactivate   = array();         $todeactivate = array(); 
                $sql = "SELECT u.id AS userid, u.firstname AS userfirstname, u.lastname AS userlastname, u.suspended AS usersuspended,
                       u.email AS useremail, c.visible as coursevisible, c.cacherev as coursecacherev, s.courseid AS subcourseid,
                       s.userid AS subuserid, s.cmid AS subcmid, s.inactivedate AS subinactivedate, s.id AS subid
                  FROM {user} u
                  JOIN {tool_monitor_subscriptions} s ON (s.userid = u.id)
             LEFT JOIN {course} c ON (c.id = s.courseid)
                 WHERE u.id = s.userid
              ORDER BY s.userid, s.courseid";
        $rs = $DB->get_recordset_sql($sql);

        foreach ($rs as $row) {
                        $sub = $this->get_subscription_from_rowdata($row);
            $sub = new subscription($sub);
            if (!isset($user) || $user->id != $sub->userid) {
                $user= $this->get_user_from_rowdata($row);
            }
            if ((!isset($course) || $course->id != $sub->courseid) && !empty($sub->courseid)) {
                $course = $this->get_course_from_rowdata($row);
            }

                        if ($user->suspended) {
                if (subscription_manager::subscription_is_active($sub)) {
                    $todeactivate[] = $sub->id;
                }
                continue;
            }

                        if (!$this->is_user_setup($user)) {
                if (subscription_manager::subscription_is_active($sub)) {
                    $todeactivate[] = $sub->id;
                }
                continue;
            }

                        $sitelevelsubscription = false;
            if (empty($sub->courseid)) {
                $context = \context_system::instance();
                $sitelevelsubscription = true;
            } else {
                $context = \context_course::instance($sub->courseid);
            }

                        if (!has_capability('tool/monitor:subscribe', $context, $user)) {
                if (subscription_manager::subscription_is_active($sub)) {
                    $todeactivate[] = $sub->id;
                }
                continue;
            }

                        if ($sitelevelsubscription) {
                if (!subscription_manager::subscription_is_active($sub)) {
                    $toactivate[] = $sub->id;
                }
                continue;
            }

                        if (!$this->user_can_access_course($user, $course, 'tool/monitor:subscribe')) {
                if (subscription_manager::subscription_is_active($sub)) {
                    $todeactivate[] = $sub->id;
                }
                continue;
            }

                        if (empty($sub->cmid)) {
                if (!subscription_manager::subscription_is_active($sub)) {
                    $toactivate[] = $sub->id;
                }
                continue;
            }

                        $modinfo = get_fast_modinfo($course, $sub->userid);
            $cm = $modinfo->get_cm($sub->cmid);
            if (!$cm || !$cm->uservisible || !$cm->available) {
                if (subscription_manager::subscription_is_active($sub)) {
                    $todeactivate[] = $sub->id;
                }
                continue;
            }

                        if (!subscription_manager::subscription_is_active($sub)) {
                $toactivate[] = $sub->id;
            }
        }
        $rs->close();

                subscription_manager::activate_subscriptions($toactivate);
        subscription_manager::deactivate_subscriptions($todeactivate);
        subscription_manager::delete_stale_subscriptions();
    }

    
    protected function is_user_setup($user) {
        if (!isset($this->userssetupcache[$user->id])) {
            $this->userssetupcache[$user->id] = !user_not_fully_set_up($user, true);
        }
        return $this->userssetupcache[$user->id];
    }

    
    protected function user_can_access_course($user, $course, $capability) {
        if (!isset($this->courseaccesscache[$course->id][$user->id][$capability])) {
            $this->courseaccesscache[$course->id][$user->id][$capability] = can_access_course($course, $user, $capability, true);
        }
        return $this->courseaccesscache[$course->id][$user->id][$capability];
    }

    
    protected function get_subscription_from_rowdata($rowdata) {
        $sub = new \stdClass();
        $sub->id = $rowdata->subid;
        $sub->userid = $rowdata->subuserid;
        $sub->courseid = $rowdata->subcourseid;
        $sub->cmid = $rowdata->subcmid;
        $sub->inactivedate = $rowdata->subinactivedate;
        return $sub;
    }

    
    protected function get_course_from_rowdata($rowdata) {
        $course = new \stdClass();
        $course->id = $rowdata->subcourseid;
        $course->visible = $rowdata->coursevisible;
        $course->cacherev = $rowdata->coursecacherev;
        return $course;
    }

    
    protected function get_user_from_rowdata($rowdata) {
        $user = new \stdClass();
        $user->id = $rowdata->userid;
        $user->firstname = $rowdata->userfirstname;
        $user->lastname = $rowdata->userlastname;
        $user->email = $rowdata->useremail;
        $user->suspended = $rowdata->usersuspended;
        return $user;
    }
}
