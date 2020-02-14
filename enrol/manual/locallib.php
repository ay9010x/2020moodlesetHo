<?php



defined('MOODLE_INTERNAL') || die();

require_once($CFG->dirroot . '/user/selector/lib.php');
require_once($CFG->dirroot . '/enrol/locallib.php');



class enrol_manual_potential_participant extends user_selector_base {
    protected $enrolid;

    public function __construct($name, $options) {
        $this->enrolid  = $options['enrolid'];
        parent::__construct($name, $options);
    }

    
    public function find_users($search) {
        global $DB;
                list($wherecondition, $params) = $this->search_sql($search, 'u');
        $params['enrolid'] = $this->enrolid;

        $fields      = 'SELECT ' . $this->required_fields_sql('u');
        $countfields = 'SELECT COUNT(1)';

        $sql = " FROM {user} u
            LEFT JOIN {user_enrolments} ue ON (ue.userid = u.id AND ue.enrolid = :enrolid)
                WHERE $wherecondition
                      AND ue.id IS NULL";

        list($sort, $sortparams) = users_order_by_sql('u', $search, $this->accesscontext);
        $order = ' ORDER BY ' . $sort;

        if (!$this->is_validating()) {
            $potentialmemberscount = $DB->count_records_sql($countfields . $sql, $params);
            if ($potentialmemberscount > $this->maxusersperpage) {
                return $this->too_many_results($search, $potentialmemberscount);
            }
        }

        $availableusers = $DB->get_records_sql($fields . $sql . $order, array_merge($params, $sortparams));

        if (empty($availableusers)) {
            return array();
        }


        if ($search) {
            $groupname = get_string('enrolcandidatesmatching', 'enrol', $search);
        } else {
            $groupname = get_string('enrolcandidates', 'enrol');
        }

        return array($groupname => $availableusers);
    }

    protected function get_options() {
        $options = parent::get_options();
        $options['enrolid'] = $this->enrolid;
        $options['file']    = 'enrol/manual/locallib.php';
        return $options;
    }
}


class enrol_manual_current_participant extends user_selector_base {
    protected $courseid;
    protected $enrolid;

    public function __construct($name, $options) {
        $this->enrolid  = $options['enrolid'];
        parent::__construct($name, $options);
    }

    
    public function find_users($search) {
        global $DB;
                list($wherecondition, $params) = $this->search_sql($search, 'u');
        $params['enrolid'] = $this->enrolid;

        $fields      = 'SELECT ' . $this->required_fields_sql('u');
        $countfields = 'SELECT COUNT(1)';

        $sql = " FROM {user} u
                 JOIN {user_enrolments} ue ON (ue.userid = u.id AND ue.enrolid = :enrolid)
                WHERE $wherecondition";

        list($sort, $sortparams) = users_order_by_sql('u', $search, $this->accesscontext);
        $order = ' ORDER BY ' . $sort;

        if (!$this->is_validating()) {
            $potentialmemberscount = $DB->count_records_sql($countfields . $sql, $params);
            if ($potentialmemberscount > $this->maxusersperpage) {
                return $this->too_many_results($search, $potentialmemberscount);
            }
        }

        $availableusers = $DB->get_records_sql($fields . $sql . $order, array_merge($params, $sortparams));

        if (empty($availableusers)) {
            return array();
        }


        if ($search) {
            $groupname = get_string('enrolledusersmatching', 'enrol', $search);
        } else {
            $groupname = get_string('enrolledusers', 'enrol');
        }

        return array($groupname => $availableusers);
    }

    protected function get_options() {
        $options = parent::get_options();
        $options['enrolid'] = $this->enrolid;
        $options['file']    = 'enrol/manual/locallib.php';
        return $options;
    }
}


class enrol_manual_editselectedusers_operation extends enrol_bulk_enrolment_operation {

    
    public function get_title() {
        return get_string('editselectedusers', 'enrol_manual');
    }

    
    public function get_identifier() {
        return 'editselectedusers';
    }

    
    public function process(course_enrolment_manager $manager, array $users, stdClass $properties) {
        global $DB, $USER;

        if (!has_capability("enrol/manual:manage", $manager->get_context())) {
            return false;
        }

                $ueids = array();
        $instances = array();
        foreach ($users as $user) {
            foreach ($user->enrolments as $enrolment) {
                $ueids[] = $enrolment->id;
                if (!array_key_exists($enrolment->id, $instances)) {
                    $instances[$enrolment->id] = $enrolment;
                }
            }
        }

                foreach ($instances as $instance) {
            if (!$this->plugin->allow_manage($instance)) {
                return false;
            }
        }

                $status = $properties->status;
        $timestart = $properties->timestart;
        $timeend = $properties->timeend;

        list($ueidsql, $params) = $DB->get_in_or_equal($ueids, SQL_PARAMS_NAMED);

        $updatesql = array();
        if ($status == ENROL_USER_ACTIVE || $status == ENROL_USER_SUSPENDED) {
            $updatesql[] = 'status = :status';
            $params['status'] = (int)$status;
        }
        if (!empty($timestart)) {
            $updatesql[] = 'timestart = :timestart';
            $params['timestart'] = (int)$timestart;
        }
        if (!empty($timeend)) {
            $updatesql[] = 'timeend = :timeend';
            $params['timeend'] = (int)$timeend;
        }
        if (empty($updatesql)) {
            return true;
        }

                $updatesql[] = 'modifierid = :modifierid';
        $params['modifierid'] = (int)$USER->id;

                $updatesql[] = 'timemodified = :timemodified';
        $params['timemodified'] = time();

                $updatesql = join(', ', $updatesql);
        $sql = "UPDATE {user_enrolments}
                   SET $updatesql
                 WHERE id $ueidsql";

        if ($DB->execute($sql, $params)) {
            foreach ($users as $user) {
                foreach ($user->enrolments as $enrolment) {
                    $enrolment->courseid  = $enrolment->enrolmentinstance->courseid;
                    $enrolment->enrol     = 'manual';
                                        $event = \core\event\user_enrolment_updated::create(
                            array(
                                'objectid' => $enrolment->id,
                                'courseid' => $enrolment->courseid,
                                'context' => context_course::instance($enrolment->courseid),
                                'relateduserid' => $user->id,
                                'other' => array('enrol' => 'manual')
                                )
                            );
                    $event->trigger();
                }
            }
                        cache::make('core', 'coursecontacts')->delete($manager->get_context()->instanceid);
            return true;
        }

        return false;
    }

    
    public function get_form($defaultaction = null, $defaultcustomdata = null) {
        global $CFG;
        require_once($CFG->dirroot.'/enrol/manual/bulkchangeforms.php');
        return new enrol_manual_editselectedusers_form($defaultaction, $defaultcustomdata);
    }
}



class enrol_manual_deleteselectedusers_operation extends enrol_bulk_enrolment_operation {

    
    public function get_identifier() {
        return 'deleteselectedusers';
    }

    
    public function get_title() {
        return get_string('deleteselectedusers', 'enrol_manual');
    }

    
    public function get_form($defaultaction = null, $defaultcustomdata = null) {
        global $CFG;
        require_once($CFG->dirroot.'/enrol/manual/bulkchangeforms.php');
        if (!array($defaultcustomdata)) {
            $defaultcustomdata = array();
        }
        $defaultcustomdata['title'] = $this->get_title();
        $defaultcustomdata['message'] = get_string('confirmbulkdeleteenrolment', 'enrol_manual');
        $defaultcustomdata['button'] = get_string('unenrolusers', 'enrol_manual');
        return new enrol_manual_deleteselectedusers_form($defaultaction, $defaultcustomdata);
    }

    
    public function process(course_enrolment_manager $manager, array $users, stdClass $properties) {
        global $DB;

        if (!has_capability("enrol/manual:unenrol", $manager->get_context())) {
            return false;
        }
        foreach ($users as $user) {
            foreach ($user->enrolments as $enrolment) {
                $plugin = $enrolment->enrolmentplugin;
                $instance = $enrolment->enrolmentinstance;
                if ($plugin->allow_unenrol_user($instance, $enrolment)) {
                    $plugin->unenrol_user($instance, $user->id);
                }
            }
        }
        return true;
    }
}


function enrol_manual_migrate_plugin_enrolments($enrol) {
    global $DB;

    if ($enrol === 'manual') {
                return;
    }

    $manualplugin = enrol_get_plugin('manual');

    $params = array('enrol'=>$enrol);
    $sql = "SELECT e.id, e.courseid, e.status, MIN(me.id) AS mid, COUNT(ue.id) AS cu
              FROM {enrol} e
              JOIN {user_enrolments} ue ON (ue.enrolid = e.id)
              JOIN {course} c ON (c.id = e.courseid)
         LEFT JOIN {enrol} me ON (me.courseid = e.courseid AND me.enrol='manual')
             WHERE e.enrol = :enrol
          GROUP BY e.id, e.courseid, e.status
          ORDER BY e.id";
    $rs = $DB->get_recordset_sql($sql, $params);

    foreach($rs as $e) {
        $minstance = false;
        if (!$e->mid) {
                        $course = $DB->get_record('course', array('id'=>$e->courseid), '*', MUST_EXIST);
            if ($minstance = $DB->get_record('enrol', array('courseid'=>$course->id, 'enrol'=>'manual'))) {
                                $e->mid = $minstance->id;
            } else if ($e->mid = $manualplugin->add_default_instance($course)) {
                $minstance = $DB->get_record('enrol', array('id'=>$e->mid));
                if ($e->status != ENROL_INSTANCE_ENABLED) {
                    $DB->set_field('enrol', 'status', ENROL_INSTANCE_DISABLED, array('id'=>$e->mid));
                    $minstance->status = ENROL_INSTANCE_DISABLED;
                }
            }
        } else {
            $minstance = $DB->get_record('enrol', array('id'=>$e->mid));
        }

        if (!$minstance) {
                        debugging('Failed to find manual enrolment instance', DEBUG_DEVELOPER);
            continue;
        }

                $params = array('id'=>$e->id, 'component'=>'enrol_'.$enrol, 'empty'=>'');
        $sql = "SELECT ra.id
                  FROM {role_assignments} ra
                  JOIN {role_assignments} mra ON (mra.contextid = ra.contextid AND mra.userid = ra.userid AND mra.roleid = ra.roleid AND mra.component = :empty AND mra.itemid = 0)
                 WHERE ra.component = :component AND ra.itemid = :id";
        $ras = $DB->get_records_sql($sql, $params);
        $ras = array_keys($ras);
        $DB->delete_records_list('role_assignments', 'id', $ras);
        unset($ras);

                $sql = "UPDATE {role_assignments}
                   SET itemid = 0, component = :empty
                 WHERE itemid = :id AND component = :component";
        $params = array('empty'=>'', 'id'=>$e->id, 'component'=>'enrol_'.$enrol);
        $DB->execute($sql, $params);

                $params = array('id'=>$e->id, 'mid'=>$e->mid);
        $sql = "SELECT ue.id
                  FROM {user_enrolments} ue
                  JOIN {user_enrolments} mue ON (mue.userid = ue.userid AND mue.enrolid = :mid)
                 WHERE ue.enrolid = :id";
        $ues = $DB->get_records_sql($sql, $params);
        $ues = array_keys($ues);
        $DB->delete_records_list('user_enrolments', 'id', $ues);
        unset($ues);

                $params = array('id'=>$e->id, 'mid'=>$e->mid);
        if ($e->status != ENROL_INSTANCE_ENABLED and $minstance->status == ENROL_INSTANCE_ENABLED) {
            $status = ", status = :disabled";
            $params['disabled'] = ENROL_USER_SUSPENDED;
        } else {
            $status = "";
        }
        $sql = "UPDATE {user_enrolments}
                   SET enrolid = :mid $status
                 WHERE enrolid = :id";
        $DB->execute($sql, $params);
    }
    $rs->close();
}


function enrol_manual_get_potential_cohorts($context, $enrolid, $search = '', $page = 0, $perpage = 25, $addedenrollment = 0) {
    global $CFG;
    require_once($CFG->dirroot . '/cohort/lib.php');

    $allcohorts = cohort_get_available_cohorts($context, COHORT_WITH_NOTENROLLED_MEMBERS_ONLY, 0, 0, $search);
    $totalcohorts = count($allcohorts);
    $cohorts = array();
    $cnt = 0;
    foreach ($allcohorts as $c) {
        if ($cnt >= $page * $perpage && (!$perpage || $cnt < ($page+1)*$perpage)) {
            $cohorts[] = (object)array(
                'id' => $c->id,
                'name' => format_string($c->name, true, array('context' => $c->contextid)),
                'cnt' => $c->memberscnt - $c->enrolledcnt
            );
        }
        $cnt++;
    }
    return array('totalcohorts' => $totalcohorts, 'cohorts' => $cohorts);
}
