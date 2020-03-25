<?php



defined('MOODLE_INTERNAL') || die();

require_once($CFG->dirroot.'/user/selector/lib.php');


class core_role_check_users_selector extends user_selector_base {
    
    protected $onlyenrolled;

    
    public function __construct($name, $options) {
        if (!isset($options['multiselect'])) {
            $options['multiselect'] = false;
        }
        parent::__construct($name, $options);

        $coursecontext = $this->accesscontext->get_course_context(false);
        if ($coursecontext and $coursecontext->id != SITEID and !has_capability('moodle/role:manage', $coursecontext)) {
                        $this->onlyenrolled = true;
        } else {
            $this->onlyenrolled = false;
        }
    }

    public function find_users($search) {
        global $DB;

        list($wherecondition, $params) = $this->search_sql($search, 'u');

        $fields      = 'SELECT ' . $this->required_fields_sql('u');
        $countfields = 'SELECT COUNT(1)';

        $coursecontext = $this->accesscontext->get_course_context(false);

        if ($coursecontext and $coursecontext != SITEID) {
            $sql1 = " FROM {user} u
                      JOIN {user_enrolments} ue ON (ue.userid = u.id)
                      JOIN {enrol} e ON (e.id = ue.enrolid AND e.courseid = :courseid1)
                     WHERE $wherecondition";
            $params['courseid1'] = $coursecontext->instanceid;

            if ($this->onlyenrolled) {
                $sql2 = null;
            } else {
                $sql2 = " FROM {user} u
                     LEFT JOIN ({user_enrolments} ue
                                JOIN {enrol} e ON (e.id = ue.enrolid AND e.courseid = :courseid2)) ON (ue.userid = u.id)
                         WHERE $wherecondition
                               AND ue.id IS NULL";
                $params['courseid2'] = $coursecontext->instanceid;
            }

        } else {
            if ($this->onlyenrolled) {
                                return array();
            }
            $sql1 = null;
            $sql2 = " FROM {user} u
                     WHERE $wherecondition";
        }

        $params['contextid'] = $this->accesscontext->id;

        list($sort, $sortparams) = users_order_by_sql('u', $search, $this->accesscontext);
        $order = ' ORDER BY ' . $sort;

        $result = array();

        if ($search) {
            $groupname1 = get_string('enrolledusersmatching', 'enrol', $search);
            $groupname2 = get_string('potusersmatching', 'core_role', $search);
        } else {
            $groupname1 = get_string('enrolledusers', 'enrol');
            $groupname2 = get_string('potusers', 'core_role');
        }

        if ($sql1) {
            $enrolleduserscount = $DB->count_records_sql($countfields . $sql1, $params);
            if (!$this->is_validating() and $enrolleduserscount > $this->maxusersperpage) {
                $result[$groupname1] = array();
                $toomany = $this->too_many_results($search, $enrolleduserscount);
                $result[implode(' - ', array_keys($toomany))] = array();

            } else {
                $enrolledusers = $DB->get_records_sql($fields . $sql1 . $order, array_merge($params, $sortparams));
                if ($enrolledusers) {
                    $result[$groupname1] = $enrolledusers;
                }
            }
            if ($sql2) {
                $result[''] = array();
            }
        }
        if ($sql2) {
            $otheruserscount = $DB->count_records_sql($countfields . $sql2, $params);
            if (!$this->is_validating() and $otheruserscount > $this->maxusersperpage) {
                $result[$groupname2] = array();
                $toomany = $this->too_many_results($search, $otheruserscount);
                $result[implode(' - ', array_keys($toomany))] = array();
            } else {
                $otherusers = $DB->get_records_sql($fields . $sql2 . $order, array_merge($params, $sortparams));
                if ($otherusers) {
                    $result[$groupname2] = $otherusers;
                }
            }
        }

        return $result;
    }

    protected function get_options() {
        global $CFG;
        $options = parent::get_options();
        $options['file'] = $CFG->admin . '/roles/lib.php';
        return $options;
    }
}
