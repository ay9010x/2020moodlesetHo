<?php



require_once($CFG->libdir.'/gradelib.php');


abstract class grade_report {
    
    public $courseid;

    
    public $course;

    
    public $gpr;

    
    public $context;

    
    public $gtree;

    
    public $prefs = array();

    
    public $gradebookroles;

    
    public $baseurl;

    
    public $pbarurl;

    
    public $page;

    
    public $lang_strings = array();

    
    
    public $currentgroup;

    
    public $currentgroupname;

    
    public $groupmode;

    
    public $group_selector;

    
    protected $groupsql;

    
    protected $groupwheresql;

    
    protected $groupwheresql_params = array();

    
    
    protected $userwheresql;

    
    protected $userwheresql_params = array();

    
    public function __construct($courseid, $gpr, $context, $page=null) {
        global $CFG, $COURSE, $DB;

        if (empty($CFG->gradebookroles)) {
            print_error('norolesdefined', 'grades');
        }

        $this->courseid  = $courseid;
        if ($this->courseid == $COURSE->id) {
            $this->course = $COURSE;
        } else {
            $this->course = $DB->get_record('course', array('id' => $this->courseid));
        }

        $this->gpr       = $gpr;
        $this->context   = $context;
        $this->page      = $page;

                $this->gradebookroles = $CFG->gradebookroles;

                $this->preferences_page = $CFG->wwwroot.'/grade/report/grader/preferences.php?id='.$courseid;

            }

    
    public function get_pref($pref, $objectid=null) {
        global $CFG;
        $fullprefname = 'grade_report_' . $pref;
        $shortprefname = 'grade_' . $pref;

        $retval = null;

        if (!isset($this) OR get_class($this) != 'grade_report') {
            if (!empty($objectid)) {
                $retval = get_user_preferences($fullprefname . $objectid, self::get_pref($pref));
            } else if (isset($CFG->$fullprefname)) {
                $retval = get_user_preferences($fullprefname, $CFG->$fullprefname);
            } else if (isset($CFG->$shortprefname)) {
                $retval = get_user_preferences($fullprefname, $CFG->$shortprefname);
            } else {
                $retval = null;
            }
        } else {
            if (empty($this->prefs[$pref.$objectid])) {

                if (!empty($objectid)) {
                    $retval = get_user_preferences($fullprefname . $objectid);
                    if (empty($retval)) {
                                                $retval = $this->get_pref($pref);
                        $objectid = null;
                    }
                } else {
                    $retval = get_user_preferences($fullprefname, $CFG->$fullprefname);
                }
                $this->prefs[$pref.$objectid] = $retval;
            } else {
                $retval = $this->prefs[$pref.$objectid];
            }
        }

        return $retval;
    }

    
    public function set_pref($pref, $pref_value='default', $itemid=null) {
        $fullprefname = 'grade_report_' . $pref;
        if ($pref_value == 'default') {
            return unset_user_preference($fullprefname.$itemid);
        } else {
            return set_user_preference($fullprefname.$itemid, $pref_value);
        }
    }

    
    abstract public function process_data($data);

    
    abstract public function process_action($target, $action);

    
    public function get_lang_string($strcode, $section=null) {
        if (empty($this->lang_strings[$strcode])) {
            $this->lang_strings[$strcode] = get_string($strcode, $section);
        }
        return $this->lang_strings[$strcode];
    }

    
    public function get_numusers($groups = true, $users = false) {
        global $CFG, $DB;
        $userwheresql = "";
        $groupsql      = "";
        $groupwheresql = "";

                list($gradebookrolessql, $gradebookrolesparams) = $DB->get_in_or_equal(explode(',', $this->gradebookroles), SQL_PARAMS_NAMED, 'grbr0');

                list($enrolledsql, $enrolledparams) = get_enrolled_sql($this->context);

                list($relatedctxsql, $relatedctxparams) = $DB->get_in_or_equal($this->context->get_parent_context_ids(true), SQL_PARAMS_NAMED, 'relatedctx');

        $params = array_merge($gradebookrolesparams, $enrolledparams, $relatedctxparams);

        if ($users) {
            $userwheresql = $this->userwheresql;
            $params       = array_merge($params, $this->userwheresql_params);
        }

        if ($groups) {
            $groupsql      = $this->groupsql;
            $groupwheresql = $this->groupwheresql;
            $params        = array_merge($params, $this->groupwheresql_params);
        }

        $sql = "SELECT DISTINCT u.id
                       FROM {user} u
                       JOIN ($enrolledsql) je
                            ON je.id = u.id
                       JOIN {role_assignments} ra
                            ON u.id = ra.userid
                       $groupsql
                      WHERE ra.roleid $gradebookrolessql
                            AND u.deleted = 0
                            $userwheresql
                            $groupwheresql
                            AND ra.contextid $relatedctxsql";
        $selectedusers = $DB->get_records_sql($sql, $params);

        $count = 0;
                if (!empty($selectedusers)) {
            $coursecontext = $this->context->get_course_context(true);

            $defaultgradeshowactiveenrol = !empty($CFG->grade_report_showonlyactiveenrol);
            $showonlyactiveenrol = get_user_preferences('grade_report_showonlyactiveenrol', $defaultgradeshowactiveenrol);
            $showonlyactiveenrol = $showonlyactiveenrol || !has_capability('moodle/course:viewsuspendedusers', $coursecontext);

            if ($showonlyactiveenrol) {
                $useractiveenrolments = get_enrolled_users($coursecontext, '', 0, 'u.id',  null, 0, 0, true);
            }

            foreach ($selectedusers as $id => $value) {
                if (!$showonlyactiveenrol || ($showonlyactiveenrol && array_key_exists($id, $useractiveenrolments))) {
                    $count++;
                }
            }
        }
        return $count;
    }

    
    public static function supports_mygrades() {
        return false;
    }

    
    protected function setup_groups() {
                if ($this->groupmode = groups_get_course_groupmode($this->course)) {
            $this->currentgroup = groups_get_course_group($this->course, true);
            $this->group_selector = groups_print_course_menu($this->course, $this->pbarurl, true);

            if ($this->groupmode == SEPARATEGROUPS and !$this->currentgroup and !has_capability('moodle/site:accessallgroups', $this->context)) {
                $this->currentgroup = -2;             }

            if ($this->currentgroup) {
                $group = groups_get_group($this->currentgroup);
                $this->currentgroupname     = $group->name;
                $this->groupsql             = " JOIN {groups_members} gm ON gm.userid = u.id ";
                $this->groupwheresql        = " AND gm.groupid = :gr_grpid ";
                $this->groupwheresql_params = array('gr_grpid'=>$this->currentgroup);
            }
        }
    }

    
    public function setup_users() {
        global $SESSION, $DB;

        $this->userwheresql = "";
        $this->userwheresql_params = array();
        if (isset($SESSION->gradereport['filterfirstname']) && !empty($SESSION->gradereport['filterfirstname'])) {
            $this->userwheresql .= ' AND '.$DB->sql_like('u.firstname', ':firstname', false, false);
            $this->userwheresql_params['firstname'] = $SESSION->gradereport['filterfirstname'].'%';
        }
        if (isset($SESSION->gradereport['filtersurname']) && !empty($SESSION->gradereport['filtersurname'])) {
            $this->userwheresql .= ' AND '.$DB->sql_like('u.lastname', ':lastname', false, false);
            $this->userwheresql_params['lastname'] = $SESSION->gradereport['filtersurname'].'%';
        }
    }

    
    protected function get_sort_arrow($direction='move', $sortlink=null) {
        global $OUTPUT;
        $pix = array('up' => 't/sort_desc', 'down' => 't/sort_asc', 'move' => 't/sort');
        $matrix = array('up' => 'desc', 'down' => 'asc', 'move' => 'desc');
        $strsort = $this->get_lang_string('sort' . $matrix[$direction]);

        $arrow = $OUTPUT->pix_icon($pix[$direction], $strsort, '', array('class' => 'sorticon'));
        return html_writer::link($sortlink, $arrow, array('title'=>$strsort));
    }

    
    protected function blank_hidden_total_and_adjust_bounds($courseid, $course_item, $finalgrade) {
        global $CFG, $DB;
        static $hiding_affected = null;
                static $previous_userid = null;

                static $previous_courseid = null;

        $coursegradegrade = grade_grade::fetch(array('userid'=>$this->user->id, 'itemid'=>$course_item->id));
        $grademin = $course_item->grademin;
        $grademax = $course_item->grademax;
        if ($coursegradegrade) {
            $grademin = $coursegradegrade->get_grade_min();
            $grademax = $coursegradegrade->get_grade_max();
        } else {
            $coursegradegrade = new grade_grade(array('userid'=>$this->user->id, 'itemid'=>$course_item->id), false);
        }
        $hint = $coursegradegrade->get_aggregation_hint();
        $aggregationstatus = $hint['status'];
        $aggregationweight = $hint['weight'];

        if (!is_array($this->showtotalsifcontainhidden)) {
            debugging('showtotalsifcontainhidden should be an array', DEBUG_DEVELOPER);
            $this->showtotalsifcontainhidden = array($courseid => $this->showtotalsifcontainhidden);
        }

        if ($this->showtotalsifcontainhidden[$courseid] == GRADE_REPORT_SHOW_REAL_TOTAL_IF_CONTAINS_HIDDEN) {
            return array('grade' => $finalgrade,
                         'grademin' => $grademin,
                         'grademax' => $grademax,
                         'aggregationstatus' => $aggregationstatus,
                         'aggregationweight' => $aggregationweight);
        }

                if ($previous_userid != $this->user->id || $previous_courseid != $courseid) {
            $hiding_affected = null;
            $previous_userid = $this->user->id;
            $previous_courseid = $courseid;
        }

        if (!$hiding_affected) {
            $items = grade_item::fetch_all(array('courseid'=>$courseid));
            $grades = array();
            $sql = "SELECT g.*
                      FROM {grade_grades} g
                      JOIN {grade_items} gi ON gi.id = g.itemid
                     WHERE g.userid = {$this->user->id} AND gi.courseid = {$courseid}";
            if ($gradesrecords = $DB->get_records_sql($sql)) {
                foreach ($gradesrecords as $grade) {
                    $grades[$grade->itemid] = new grade_grade($grade, false);
                }
                unset($gradesrecords);
            }
            foreach ($items as $itemid => $unused) {
                if (!isset($grades[$itemid])) {
                    $grade_grade = new grade_grade();
                    $grade_grade->userid = $this->user->id;
                    $grade_grade->itemid = $items[$itemid]->id;
                    $grades[$itemid] = $grade_grade;
                }
                $grades[$itemid]->grade_item =& $items[$itemid];
            }
            $hiding_affected = grade_grade::get_hiding_affected($grades, $items);
        }

                if (array_key_exists($course_item->id, $hiding_affected['altered']) ||
                array_key_exists($course_item->id, $hiding_affected['alteredgrademin']) ||
                array_key_exists($course_item->id, $hiding_affected['alteredgrademax']) ||
                array_key_exists($course_item->id, $hiding_affected['alteredaggregationstatus']) ||
                array_key_exists($course_item->id, $hiding_affected['alteredaggregationweight'])) {
            if (!$this->showtotalsifcontainhidden[$courseid] && array_key_exists($course_item->id, $hiding_affected['altered'])) {
                                $finalgrade = null;
            } else {
                                if (array_key_exists($course_item->id, $hiding_affected['altered'])) {
                    $finalgrade = $hiding_affected['altered'][$course_item->id];
                }
                if (array_key_exists($course_item->id, $hiding_affected['alteredgrademin'])) {
                    $grademin = $hiding_affected['alteredgrademin'][$course_item->id];
                }
                if (array_key_exists($course_item->id, $hiding_affected['alteredgrademax'])) {
                    $grademax = $hiding_affected['alteredgrademax'][$course_item->id];
                }
                if (array_key_exists($course_item->id, $hiding_affected['alteredaggregationstatus'])) {
                    $aggregationstatus = $hiding_affected['alteredaggregationstatus'][$course_item->id];
                }
                if (array_key_exists($course_item->id, $hiding_affected['alteredaggregationweight'])) {
                    $aggregationweight = $hiding_affected['alteredaggregationweight'][$course_item->id];
                }

                if (!$this->showtotalsifcontainhidden[$courseid]) {
                                                            $aggregationstatus = 'unknown';
                    $aggregationweight = null;
                }
            }
        } else if (!empty($hiding_affected['unknown'][$course_item->id])) {
                        if (!$this->showtotalsifcontainhidden[$courseid]) {
                                $finalgrade = null;
            } else {
                                $finalgrade = $hiding_affected['unknown'][$course_item->id];

                if (array_key_exists($course_item->id, $hiding_affected['alteredgrademin'])) {
                    $grademin = $hiding_affected['alteredgrademin'][$course_item->id];
                }
                if (array_key_exists($course_item->id, $hiding_affected['alteredgrademax'])) {
                    $grademax = $hiding_affected['alteredgrademax'][$course_item->id];
                }
                if (array_key_exists($course_item->id, $hiding_affected['alteredaggregationstatus'])) {
                    $aggregationstatus = $hiding_affected['alteredaggregationstatus'][$course_item->id];
                }
                if (array_key_exists($course_item->id, $hiding_affected['alteredaggregationweight'])) {
                    $aggregationweight = $hiding_affected['alteredaggregationweight'][$course_item->id];
                }
            }
        }

        return array('grade' => $finalgrade, 'grademin' => $grademin, 'grademax' => $grademax, 'aggregationstatus'=>$aggregationstatus, 'aggregationweight'=>$aggregationweight);
    }

    
    protected function blank_hidden_total($courseid, $course_item, $finalgrade) {
                
        debugging('grade_report::blank_hidden_total() is deprecated.
                   Call grade_report::blank_hidden_total_and_adjust_bounds instead.', DEBUG_DEVELOPER);
        $result = $this->blank_hidden_total_and_adjust_bounds($courseid, $course_item, $finalgrade);
        return $result['grade'];
    }
}

