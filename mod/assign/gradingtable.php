<?php



defined('MOODLE_INTERNAL') || die();

require_once($CFG->libdir.'/tablelib.php');
require_once($CFG->libdir.'/gradelib.php');
require_once($CFG->dirroot.'/mod/assign/locallib.php');


class assign_grading_table extends table_sql implements renderable {
    
    private $assignment = null;
    
    private $perpage = 10;
    
    private $rownum = -1;
    
    private $output = null;
    
    private $gradinginfo = null;
    
    private $tablemaxrows = 10000;
    
    private $quickgrading = false;
    
    private $hasgrantextension = false;
    
    private $hasgrade = false;
    
    private $groupsubmissions = array();
    
    private $submissiongroups = array();
    
    public $plugingradingbatchoperations = array();
    
    private $plugincache = array();
    
    private $scale = null;

    
    public function __construct(assign $assignment,
                                $perpage,
                                $filter,
                                $rowoffset,
                                $quickgrading,
                                $downloadfilename = null) {
        global $CFG, $PAGE, $DB, $USER;
        parent::__construct('mod_assign_grading');
        $this->is_persistent(true);
        $this->assignment = $assignment;

                $this->hasgrantextension = has_capability('mod/assign:grantextension',
                                                  $this->assignment->get_context());
        $this->hasgrade = $this->assignment->can_grade();

                $this->hasviewblind = has_capability('mod/assign:viewblinddetails',
                $this->assignment->get_context());

        foreach ($assignment->get_feedback_plugins() as $plugin) {
            if ($plugin->is_visible() && $plugin->is_enabled()) {
                foreach ($plugin->get_grading_batch_operations() as $action => $description) {
                    if (empty($this->plugingradingbatchoperations)) {
                        $this->plugingradingbatchoperations[$plugin->get_type()] = array();
                    }
                    $this->plugingradingbatchoperations[$plugin->get_type()][$action] = $description;
                }
            }
        }
        $this->perpage = $perpage;
        $this->quickgrading = $quickgrading && $this->hasgrade;
        $this->output = $PAGE->get_renderer('mod_assign');

        $urlparams = array('action'=>'grading', 'id'=>$assignment->get_course_module()->id);
        $url = new moodle_url($CFG->wwwroot . '/mod/assign/view.php', $urlparams);
        $this->define_baseurl($url);

                $currentgroup = groups_get_activity_group($assignment->get_course_module(), true);

        if ($rowoffset) {
            $this->rownum = $rowoffset - 1;
        }

        $users = array_keys( $assignment->list_participants($currentgroup, true));
        if (count($users) == 0) {
                        $users[] = -1;
        }

        $params = array();
        $params['assignmentid1'] = (int)$this->assignment->get_instance()->id;
        $params['assignmentid2'] = (int)$this->assignment->get_instance()->id;
        $params['assignmentid3'] = (int)$this->assignment->get_instance()->id;

        $extrauserfields = get_extra_user_fields($this->assignment->get_context());

        $fields = user_picture::fields('u', $extrauserfields) . ', ';
        $fields .= 'u.id as userid, ';
        $fields .= 's.status as status, ';
        $fields .= 's.id as submissionid, ';
        $fields .= 's.timecreated as firstsubmission, ';
        $fields .= 's.timemodified as timesubmitted, ';
        $fields .= 's.attemptnumber as attemptnumber, ';
        $fields .= 'g.id as gradeid, ';
        $fields .= 'g.grade as grade, ';
        $fields .= 'g.timemodified as timemarked, ';
        $fields .= 'g.timecreated as firstmarked, ';
        $fields .= 'uf.mailed as mailed, ';
        $fields .= 'uf.locked as locked, ';
        $fields .= 'uf.extensionduedate as extensionduedate, ';
        $fields .= 'uf.workflowstate as workflowstate, ';
        $fields .= 'uf.allocatedmarker as allocatedmarker ';
        
        $fields .= ', uf.patternstate as patternstate ';
        $from = '{user} u
                         LEFT JOIN {assign_submission} s
                                ON u.id = s.userid
                               AND s.assignment = :assignmentid1
                               AND s.latest = 1
                         LEFT JOIN {assign_grades} g
                                ON u.id = g.userid
                               AND g.assignment = :assignmentid2 ';

                        if ($this->assignment->get_instance()->teamsubmission) {
            $params['assignmentid4'] = (int) $this->assignment->get_instance()->id;
            $grademaxattempt = 'SELECT mxg.userid, MAX(mxg.attemptnumber) AS maxattempt
                                  FROM {assign_grades} mxg
                                 WHERE mxg.assignment = :assignmentid4
                              GROUP BY mxg.userid';
            $from .= 'LEFT JOIN (' . $grademaxattempt . ') gmx
                             ON u.id = gmx.userid
                            AND g.attemptnumber = gmx.maxattempt ';
        } else {
            $from .= 'AND g.attemptnumber = s.attemptnumber ';
        }

        $from .= 'LEFT JOIN {assign_user_flags} uf
                         ON u.id = uf.userid
                        AND uf.assignment = :assignmentid3 ';

        if (!empty($this->assignment->get_instance()->blindmarking)) {
            $from .= 'LEFT JOIN {assign_user_mapping} um
                             ON u.id = um.userid
                            AND um.assignment = :assignmentid5 ';
            $params['assignmentid5'] = (int)$this->assignment->get_instance()->id;
            $fields .= ', um.id as recordid ';
        }

        $userparams = array();
        $userindex = 0;

        list($userwhere, $userparams) = $DB->get_in_or_equal($users, SQL_PARAMS_NAMED, 'user');
        $where = 'u.id ' . $userwhere;
        $params = array_merge($params, $userparams);

                if ($this->assignment->is_any_submission_plugin_enabled()) {
            if ($filter == ASSIGN_FILTER_SUBMITTED) {
                $where .= ' AND (s.timemodified IS NOT NULL AND
                                 s.status = :submitted) ';
                $params['submitted'] = ASSIGN_SUBMISSION_STATUS_SUBMITTED;

            } else if ($filter == ASSIGN_FILTER_NOT_SUBMITTED) {
                $where .= ' AND (s.timemodified IS NULL OR s.status != :submitted) ';
                $params['submitted'] = ASSIGN_SUBMISSION_STATUS_SUBMITTED;
            } else if ($filter == ASSIGN_FILTER_REQUIRE_GRADING) {
                $where .= ' AND (s.timemodified IS NOT NULL AND
                                 s.status = :submitted AND
                                 (s.timemodified >= g.timemodified OR g.timemodified IS NULL OR g.grade IS NULL';

                if ($this->assignment->get_grade_item()->gradetype == GRADE_TYPE_SCALE) {
                                        $where .= ' OR g.grade = -1';
                }

                $where .= '))';
                $params['submitted'] = ASSIGN_SUBMISSION_STATUS_SUBMITTED;

            } else if (strpos($filter, ASSIGN_FILTER_SINGLE_USER) === 0) {
                $userfilter = (int) array_pop(explode('=', $filter));
                $where .= ' AND (u.id = :userid)';
                $params['userid'] = $userfilter;
            }
        }

        if ($this->assignment->get_instance()->markingworkflow &&
            $this->assignment->get_instance()->markingallocation) {
            if (has_capability('mod/assign:manageallocations', $this->assignment->get_context())) {
                                $markerfilter = (int)get_user_preferences('assign_markerfilter', '');
                if (!empty($markerfilter)) {
                    if ($markerfilter == ASSIGN_MARKER_FILTER_NO_MARKER) {
                        $where .= ' AND (uf.allocatedmarker IS NULL OR uf.allocatedmarker = 0)';
                    } else {
                        $where .= ' AND uf.allocatedmarker = :markerid';
                        $params['markerid'] = $markerfilter;
                    }
                }
            } else {                 $where .= ' AND uf.allocatedmarker = :markerid';
                $params['markerid'] = $USER->id;
            }
        }

        if ($this->assignment->get_instance()->markingworkflow) {
            $workflowstates = $this->assignment->get_marking_workflow_states_for_current_user();
            if (!empty($workflowstates)) {
                $workflowfilter = get_user_preferences('assign_workflowfilter', '');
                if ($workflowfilter == ASSIGN_MARKING_WORKFLOW_STATE_NOTMARKED) {
                    $where .= ' AND (uf.workflowstate = :workflowstate OR uf.workflowstate IS NULL OR '.
                        $DB->sql_isempty('assign_user_flags', 'workflowstate', true, true).')';
                    $params['workflowstate'] = $workflowfilter;
                } else if (array_key_exists($workflowfilter, $workflowstates)) {
                    $where .= ' AND uf.workflowstate = :workflowstate';
                    $params['workflowstate'] = $workflowfilter;
                }
            }
        }

        $this->set_sql($fields, $from, $where, $params);

        if ($downloadfilename) {
            $this->is_downloading('csv', $downloadfilename);
        }

        $columns = array();
        $headers = array();

                if (!$this->is_downloading() && $this->hasgrade) {
            $columns[] = 'select';
            $headers[] = get_string('select') .
                    '<div class="selectall"><label class="accesshide" for="selectall">' . get_string('selectall') . '</label>
                    <input type="checkbox" id="selectall" name="selectall" title="' . get_string('selectall') . '"/></div>';
        }

                if ($this->hasviewblind || !$this->assignment->is_blind_marking()) {
            if (!$this->is_downloading()) {
                $columns[] = 'picture';
                $headers[] = get_string('pictureofuser');
            } else {
                $columns[] = 'recordid';
                $headers[] = get_string('recordid', 'assign');
            }

                        $columns[] = 'fullname';
            $headers[] = get_string('fullname');

                        if ($this->assignment->is_blind_marking()) {
                if (!$this->is_downloading()) {
                    $columns[] = 'recordid';
                    $headers[] = get_string('recordid', 'assign');
                }
            }

            foreach ($extrauserfields as $extrafield) {
                $columns[] = $extrafield;
                $headers[] = get_user_field_name($extrafield);
            }
        } else {
                        $columns[] = 'recordid';
            $headers[] = get_string('recordid', 'assign');
        }

                $columns[] = 'status';
        $headers[] = get_string('status', 'assign');

                if ($assignment->get_instance()->teamsubmission) {
            $columns[] = 'team';
            $headers[] = get_string('submissionteam', 'assign');
        }
                if ($this->assignment->get_instance()->markingworkflow &&
            $this->assignment->get_instance()->markingallocation &&
            has_capability('mod/assign:manageallocations', $this->assignment->get_context())) {
                        $columns[] = 'allocatedmarker';
            $headers[] = get_string('marker', 'assign');
        }
                $columns[] = 'grade';
        $headers[] = get_string('grade');
        if ($this->is_downloading()) {
            if ($this->assignment->get_instance()->grade >= 0) {
                $columns[] = 'grademax';
                $headers[] = get_string('maxgrade', 'assign');
            } else {
                                $columns[] = 'scale';
                $headers[] = get_string('scale', 'assign');
            }

            if ($this->assignment->get_instance()->markingworkflow) {
                                $columns[] = 'workflowstate';
                $headers[] = get_string('markingworkflowstate', 'assign');
            }
                        $columns[] = 'gradecanbechanged';
            $headers[] = get_string('gradecanbechanged', 'assign');
        }
        if (!$this->is_downloading() && $this->hasgrade) {
                        $columns[] = 'userid';
            $headers[] = get_string('edit');
        }

                if ($assignment->is_any_submission_plugin_enabled()) {
            $columns[] = 'timesubmitted';
            $headers[] = get_string('lastmodifiedsubmission', 'assign');

            foreach ($this->assignment->get_submission_plugins() as $plugin) {
                if ($this->is_downloading()) {
                    if ($plugin->is_visible() && $plugin->is_enabled()) {
                        foreach ($plugin->get_editor_fields() as $field => $description) {
                            $index = 'plugin' . count($this->plugincache);
                            $this->plugincache[$index] = array($plugin, $field);
                            $columns[] = $index;
                            $headers[] = $plugin->get_name();
                        }
                    }
                } else {
                    if ($plugin->is_visible() && $plugin->is_enabled() && $plugin->has_user_summary()) {
                        $index = 'plugin' . count($this->plugincache);
                        $this->plugincache[$index] = array($plugin);
                        $columns[] = $index;
                        $headers[] = $plugin->get_name();
                    }
                }
            }
        }

                $columns[] = 'timemarked';
        $headers[] = get_string('lastmodifiedgrade', 'assign');

                foreach ($this->assignment->get_feedback_plugins() as $plugin) {
            if ($this->is_downloading()) {
                if ($plugin->is_visible() && $plugin->is_enabled()) {
                    foreach ($plugin->get_editor_fields() as $field => $description) {
                        $index = 'plugin' . count($this->plugincache);
                        $this->plugincache[$index] = array($plugin, $field);
                        $columns[] = $index;
                        $headers[] = $description;
                    }
                }
            } else if ($plugin->is_visible() && $plugin->is_enabled() && $plugin->has_user_summary()) {
                $index = 'plugin' . count($this->plugincache);
                $this->plugincache[$index] = array($plugin);
                $columns[] = $index;
                $headers[] = $plugin->get_name();
            }
        }

                if (!$this->is_downloading()) {
                        $columns[] = 'finalgrade';
            $headers[] = get_string('finalgrade', 'grades');
        }

                $this->gradinginfo = grade_get_grades($this->assignment->get_course()->id,
                                              'mod',
                                              'assign',
                                              $this->assignment->get_instance()->id,
                                              $users);

        if (!empty($CFG->enableoutcomes) && !empty($this->gradinginfo->outcomes)) {
            $columns[] = 'outcomes';
            $headers[] = get_string('outcomes', 'grades');
        }

                $this->define_columns($columns);
        $this->define_headers($headers);
        foreach ($extrauserfields as $extrafield) {
             $this->column_class($extrafield, $extrafield);
        }
        $this->no_sorting('recordid');
        $this->no_sorting('finalgrade');
        $this->no_sorting('userid');
        $this->no_sorting('select');
        $this->no_sorting('outcomes');

        if ($assignment->get_instance()->teamsubmission) {
            $this->no_sorting('team');
        }

        $plugincolumnindex = 0;
        foreach ($this->assignment->get_submission_plugins() as $plugin) {
            if ($plugin->is_visible() && $plugin->is_enabled() && $plugin->has_user_summary()) {
                $submissionpluginindex = 'plugin' . $plugincolumnindex++;
                $this->no_sorting($submissionpluginindex);
            }
        }
        foreach ($this->assignment->get_feedback_plugins() as $plugin) {
            if ($plugin->is_visible() && $plugin->is_enabled() && $plugin->has_user_summary()) {
                $feedbackpluginindex = 'plugin' . $plugincolumnindex++;
                $this->no_sorting($feedbackpluginindex);
            }
        }

                if ($this->is_downloading()) {
            $this->start_output();
        }
    }

    
    public function format_row($row) {
        if ($this->rownum < 0) {
            $this->rownum = $this->currpage * $this->pagesize;
        } else {
            $this->rownum += 1;
        }

        return parent::format_row($row);
    }

    
    public function col_recordid(stdClass $row) {
        if (empty($row->recordid)) {
            $row->recordid = $this->assignment->get_uniqueid_for_user($row->userid);
        }
        return get_string('hiddenuser', 'assign') . $row->recordid;
    }


    
    public function get_row_class($row) {
        return 'user' . $row->userid;
    }

    
    public function get_rows_per_page() {
        return $this->perpage;
    }

    
    public function col_workflowstatus(stdClass $row) {
        $o = '';

        $gradingdisabled = $this->assignment->grading_disabled($row->id);
                $workflowstates = $this->assignment->get_marking_workflow_states_for_current_user();
        $workflowstate = $row->workflowstate;
        if (empty($workflowstate)) {
            $workflowstate = ASSIGN_MARKING_WORKFLOW_STATE_NOTMARKED;
        }
        if ($this->quickgrading && !$gradingdisabled) {
            $notmarked = get_string('markingworkflowstatenotmarked', 'assign');
            $name = 'quickgrade_' . $row->id . '_workflowstate';
            $o .= html_writer::select($workflowstates, $name, $workflowstate, array('' => $notmarked));
                        if ($this->assignment->get_instance()->markingworkflow &&
                $this->assignment->get_instance()->markingallocation &&
                !has_capability('mod/assign:manageallocations', $this->assignment->get_context())) {

                $name = 'quickgrade_' . $row->id . '_allocatedmarker';
                $o .= html_writer::empty_tag('input', array('type' => 'hidden', 'name' => $name,
                        'value' => $row->allocatedmarker));
            }
        } else {
            $o .= $this->output->container(get_string('markingworkflowstate' . $workflowstate, 'assign'), $workflowstate);
        }
        return $o;
    }

    
    public function col_workflowstate($row) {
        $state = $row->workflowstate;
        if (empty($state)) {
            $state = ASSIGN_MARKING_WORKFLOW_STATE_NOTMARKED;
        }

        return get_string('markingworkflowstate' . $state, 'assign');
    }

    
    public function col_allocatedmarker(stdClass $row) {
        static $markers = null;
        static $markerlist = array();
        if ($markers === null) {
            list($sort, $params) = users_order_by_sql();
            $markers = get_users_by_capability($this->assignment->get_context(), 'mod/assign:grade', '', $sort);
            $markerlist[0] = get_string('choosemarker', 'assign');
            foreach ($markers as $marker) {
                $markerlist[$marker->id] = fullname($marker);
            }
        }
        if (empty($markerlist)) {
                        return '';
        }
        if ($this->is_downloading()) {
            if (isset($markers[$row->allocatedmarker])) {
                return fullname($markers[$row->allocatedmarker]);
            } else {
                return '';
            }
        }

        if ($this->quickgrading && has_capability('mod/assign:manageallocations', $this->assignment->get_context()) &&
            (empty($row->workflowstate) ||
             $row->workflowstate == ASSIGN_MARKING_WORKFLOW_STATE_INMARKING ||
             $row->workflowstate == ASSIGN_MARKING_WORKFLOW_STATE_NOTMARKED)) {

            $name = 'quickgrade_' . $row->id . '_allocatedmarker';
            return  html_writer::select($markerlist, $name, $row->allocatedmarker, false);
        } else if (!empty($row->allocatedmarker)) {
            $output = '';
            if ($this->quickgrading) {                 $name = 'quickgrade_' . $row->id . '_allocatedmarker';
                $output .= html_writer::empty_tag('input', array('type'=>'hidden', 'name'=>$name, 'value'=>$row->allocatedmarker));
            }
            $output .= $markerlist[$row->allocatedmarker];
            return $output;
        }
    }
    
    public function col_scale($row) {
        global $DB;

        if (empty($this->scale)) {
            $dbparams = array('id'=>-($this->assignment->get_instance()->grade));
            $this->scale = $DB->get_record('scale', $dbparams);
        }

        if (!empty($this->scale->scale)) {
            return implode("\n", explode(',', $this->scale->scale));
        }
        return '';
    }

    
    public function display_grade($grade, $editable, $userid, $modified) {
        if ($this->is_downloading()) {
            if ($this->assignment->get_instance()->grade >= 0) {
                if ($grade == -1 || $grade === null) {
                    return '';
                }
                $gradeitem = $this->assignment->get_grade_item();
                return format_float($grade, $gradeitem->get_decimals());
            } else {
                                $scale = $this->assignment->display_grade($grade, false);
                if ($scale == '-') {
                    $scale = '';
                }
                return $scale;
            }
        }
        return $this->assignment->display_grade($grade, $editable, $userid, $modified);
    }

    
    public function col_team(stdClass $row) {
        $submission = false;
        $group = false;
        $this->get_group_and_submission($row->id, $group, $submission, -1);
        if ($group) {
            return $group->name;
        } else if ($this->assignment->get_instance()->preventsubmissionnotingroup) {
            $usergroups = $this->assignment->get_all_groups($row->id);
            if (count($usergroups) > 1) {
                return get_string('multipleteamsgrader', 'assign');
            } else {
                return get_string('noteamgrader', 'assign');
            }
        }
        return get_string('defaultteam', 'assign');
    }

    
    protected function get_group_and_submission($userid, &$group, &$submission, $attemptnumber) {
        $group = false;
        if (isset($this->submissiongroups[$userid])) {
            $group = $this->submissiongroups[$userid];
        } else {
            $group = $this->assignment->get_submission_group($userid, false);
            $this->submissiongroups[$userid] = $group;
        }

        $groupid = 0;
        if ($group) {
            $groupid = $group->id;
        }

                        if (isset($this->groupsubmissions[$groupid . ':' . $attemptnumber])) {
            $submission = $this->groupsubmissions[$groupid . ':' . $attemptnumber];
        } else {
            $submission = $this->assignment->get_group_submission($userid, $groupid, false, $attemptnumber);
            $this->groupsubmissions[$groupid . ':' . $attemptnumber] = $submission;
        }
    }

    
    public function col_outcomes(stdClass $row) {
        $outcomes = '';
        foreach ($this->gradinginfo->outcomes as $index => $outcome) {
            $options = make_grades_menu(-$outcome->scaleid);

            $options[0] = get_string('nooutcome', 'grades');
            if ($this->quickgrading && !($outcome->grades[$row->userid]->locked)) {
                $select = '<select name="outcome_' . $index . '_' . $row->userid . '" class="quickgrade">';
                foreach ($options as $optionindex => $optionvalue) {
                    $selected = '';
                    if ($outcome->grades[$row->userid]->grade == $optionindex) {
                        $selected = 'selected="selected"';
                    }
                    $select .= '<option value="' . $optionindex . '"' . $selected . '>' . $optionvalue . '</option>';
                }
                $select .= '</select>';
                $outcomes .= $this->output->container($outcome->name . ': ' . $select, 'outcome');
            } else {
                $name = $outcome->name . ': ' . $options[$outcome->grades[$row->userid]->grade];
                if ($this->is_downloading()) {
                    $outcomes .= $name;
                } else {
                    $outcomes .= $this->output->container($name, 'outcome');
                }
            }
        }

        return $outcomes;
    }


    
    public function col_picture(stdClass $row) {
        return $this->output->user_picture($row);
    }

    
    public function col_fullname($row) {
        if (!$this->is_downloading()) {
            $courseid = $this->assignment->get_course()->id;
            $link= new moodle_url('/user/view.php', array('id' =>$row->id, 'course'=>$courseid));
            $fullname = $this->output->action_link($link, $this->assignment->fullname($row));
        } else {
            $fullname = $this->assignment->fullname($row);
        }

        if (!$this->assignment->is_active_user($row->id)) {
            $suspendedstring = get_string('userenrolmentsuspended', 'grades');
            $fullname .= ' ' . html_writer::empty_tag('img', array('src' => $this->output->pix_url('i/enrolmentsuspended'),
                'title' => $suspendedstring, 'alt' => $suspendedstring, 'class' => 'usersuspendedicon'));
            $fullname = html_writer::tag('span', $fullname, array('class' => 'usersuspended'));
        }
        return $fullname;
    }

    
    public function col_select(stdClass $row) {
        $selectcol = '<label class="accesshide" for="selectuser_' . $row->userid . '">';
        $selectcol .= get_string('selectuser', 'assign', $this->assignment->fullname($row));
        $selectcol .= '</label>';
        $selectcol .= '<input type="checkbox"
                              id="selectuser_' . $row->userid . '"
                              name="selectedusers"
                              value="' . $row->userid . '"/>';
        $selectcol .= '<input type="hidden"
                              name="grademodified_' . $row->userid . '"
                              value="' . $row->timemarked . '"/>';
        $selectcol .= '<input type="hidden"
                              name="gradeattempt_' . $row->userid . '"
                              value="' . $row->attemptnumber . '"/>';
        return $selectcol;
    }

    
    private function get_gradebook_data_for_user($userid) {
        if (isset($this->gradinginfo->items[0]) && $this->gradinginfo->items[0]->grades[$userid]) {
            return $this->gradinginfo->items[0]->grades[$userid];
        }
        return false;
    }

    
    public function col_gradecanbechanged(stdClass $row) {
        $gradingdisabled = $this->assignment->grading_disabled($row->id);
        if ($gradingdisabled) {
            return get_string('no');
        } else {
            return get_string('yes');
        }
    }

    
    public function col_grademax(stdClass $row) {
        $gradeitem = $this->assignment->get_grade_item();
        return format_float($this->assignment->get_instance()->grade, $gradeitem->get_decimals());
    }

    
    public function col_grade(stdClass $row) {
        $o = '';

        $link = '';
        $separator = $this->output->spacer(array(), true);
        $grade = '';
        $gradingdisabled = $this->assignment->grading_disabled($row->id);

        if (!$this->is_downloading() && $this->hasgrade) {
            $urlparams = array('id' => $this->assignment->get_course_module()->id,
                               'rownum' => 0,
                               'action' => 'grader');

            if ($this->assignment->is_blind_marking()) {
                if (empty($row->recordid)) {
                    $row->recordid = $this->assignment->get_uniqueid_for_user($row->userid);
                }
                $urlparams['blindid'] = $row->recordid;
            } else {
                $urlparams['userid'] = $row->userid;
            }

            $url = new moodle_url('/mod/assign/view.php', $urlparams);
            $link = '<a href="' . $url . '" class="btn btn-primary">' . get_string('grade') . '</a>';
            $grade .= $link . $separator;
        }

        $grade .= $this->display_grade($row->grade,
                                       $this->quickgrading && !$gradingdisabled,
                                       $row->userid,
                                       $row->timemarked);

        return $grade;
    }

    
    public function col_finalgrade(stdClass $row) {
        $o = '';

        $grade = $this->get_gradebook_data_for_user($row->userid);
        if ($grade) {
            $o = $this->display_grade($grade->grade, false, $row->userid, $row->timemarked);
        }

        return $o;
    }

    
    public function col_timemarked(stdClass $row) {
        $o = '-';

        if ($row->timemarked && $row->grade !== null && $row->grade >= 0) {
            $o = userdate($row->timemarked);
        }
        if ($row->timemarked && $this->is_downloading()) {
                        $o = userdate($row->timemarked);
        }

        return $o;
    }

    
    public function col_timesubmitted(stdClass $row) {
        $o = '-';

        $group = false;
        $submission = false;
        $this->get_group_and_submission($row->id, $group, $submission, -1);
        if ($submission && $submission->timemodified && $submission->status != ASSIGN_SUBMISSION_STATUS_NEW) {
            $o = userdate($submission->timemodified);
        } else if ($row->timesubmitted && $row->status != ASSIGN_SUBMISSION_STATUS_NEW) {
            $o = userdate($row->timesubmitted);
        }

        return $o;
    }

    
    public function col_status(stdClass $row) {
        $o = '';

        $instance = $this->assignment->get_instance();

        $due = $instance->duedate;
        if ($row->extensionduedate) {
            $due = $row->extensionduedate;
        }

        $group = false;
        $submission = false;
        $this->get_group_and_submission($row->id, $group, $submission, -1);

        if ($instance->teamsubmission && !$group && !$instance->preventsubmissionnotingroup) {
            $group = true;
        }

        if ($group && $submission) {
            $timesubmitted = $submission->timemodified;
            $status = $submission->status;
        } else {
            $timesubmitted = $row->timesubmitted;
            $status = $row->status;
        }

        $displaystatus = $status;
        if ($displaystatus == 'new') {
            $displaystatus = '';
        }

        if ($this->assignment->is_any_submission_plugin_enabled()) {

            $o .= $this->output->container(get_string('submissionstatus_' . $displaystatus, 'assign'),
                                           array('class'=>'submissionstatus' .$displaystatus));
            if ($due && $timesubmitted > $due && $status != ASSIGN_SUBMISSION_STATUS_NEW) {
                $usertime = format_time($timesubmitted - $due);
                $latemessage = get_string('submittedlateshort',
                                          'assign',
                                          $usertime);
                $o .= $this->output->container($latemessage, 'latesubmission');
            }
            if ($row->locked) {
                $lockedstr = get_string('submissionslockedshort', 'assign');
                $o .= $this->output->container($lockedstr, 'lockedsubmission');
            }

                        if (!$instance->markingworkflow) {
                if ($row->grade !== null && $row->grade >= 0) {
                    $o .= $this->output->container(get_string('graded', 'assign'), 'submissiongraded');
                } else if (!$timesubmitted || $status == ASSIGN_SUBMISSION_STATUS_NEW) {
                    $now = time();
                    if ($due && ($now > $due)) {
                        $overduestr = get_string('overdue', 'assign', format_time($now - $due));
                        $o .= $this->output->container($overduestr, 'overduesubmission');
                    }
                }
            }
        }

        if ($instance->markingworkflow) {
            $o .= $this->col_workflowstatus($row);
        }
        if ($row->extensionduedate) {
            $userdate = userdate($row->extensionduedate);
            $extensionstr = get_string('userextensiondate', 'assign', $userdate);
            $o .= $this->output->container($extensionstr, 'extensiondate');
        }
        
        if ($row->patternstate) {
            $o .= $this->output->container(get_string('pattern_assign', 'assign'), 'patternstatus');
        }
        if ($this->is_downloading()) {
            $o = strip_tags(rtrim(str_replace('</div>', ' - ', $o), '- '));
        }

        return $o;
    }

    
    public function col_userid(stdClass $row) {
        global $USER;

        $edit = '';

        $actions = array();

        $urlparams = array('id' => $this->assignment->get_course_module()->id,
                               'rownum' => 0,
                               'action' => 'grader');

        if ($this->assignment->is_blind_marking()) {
            if (empty($row->recordid)) {
                $row->recordid = $this->assignment->get_uniqueid_for_user($row->userid);
            }
            $urlparams['blindid'] = $row->recordid;
        } else {
            $urlparams['userid'] = $row->userid;
        }
        $url = new moodle_url('/mod/assign/view.php', $urlparams);
        $noimage = null;

        if (!$row->grade) {
            $description = get_string('grade');
        } else {
            $description = get_string('updategrade', 'assign');
        }
        $actions['grade'] = new action_menu_link_secondary(
            $url,
            $noimage,
            $description
        );

                $submission = $row;
        $flags = $row;
        if ($this->assignment->get_instance()->teamsubmission) {
                        $submission = false;
            $group = false;
            $this->get_group_and_submission($row->id, $group, $submission, -1);
        }

        $submissionsopen = $this->assignment->submissions_open($row->id,
                                                               true,
                                                               $submission,
                                                               $flags,
                                                               $this->gradinginfo);
        $caneditsubmission = $this->assignment->can_edit_submission($row->id, $USER->id);

                if ($this->assignment->is_any_submission_plugin_enabled()) {
            if (!$row->status ||
                    $row->status == ASSIGN_SUBMISSION_STATUS_DRAFT ||
                    !$this->assignment->get_instance()->submissiondrafts) {

                if (!$row->locked) {
                    $urlparams = array('id' => $this->assignment->get_course_module()->id,
                                       'userid'=>$row->id,
                                       'action'=>'lock',
                                       'sesskey'=>sesskey(),
                                       'page'=>$this->currpage);
                    $url = new moodle_url('/mod/assign/view.php', $urlparams);

                    $description = get_string('preventsubmissionsshort', 'assign');
                    $actions['lock'] = new action_menu_link_secondary(
                        $url,
                        $noimage,
                        $description
                    );
                } else {
                    $urlparams = array('id' => $this->assignment->get_course_module()->id,
                                       'userid'=>$row->id,
                                       'action'=>'unlock',
                                       'sesskey'=>sesskey(),
                                       'page'=>$this->currpage);
                    $url = new moodle_url('/mod/assign/view.php', $urlparams);
                    $description = get_string('allowsubmissionsshort', 'assign');
                    $actions['unlock'] = new action_menu_link_secondary(
                        $url,
                        $noimage,
                        $description
                    );
                }
            }

            if ($submissionsopen &&
                    $USER->id != $row->id &&
                    $caneditsubmission) {
                $urlparams = array('id' => $this->assignment->get_course_module()->id,
                                   'userid'=>$row->id,
                                   'action'=>'editsubmission',
                                   'sesskey'=>sesskey(),
                                   'page'=>$this->currpage);
                $url = new moodle_url('/mod/assign/view.php', $urlparams);
                $description = get_string('editsubmission', 'assign');
                $actions['editsubmission'] = new action_menu_link_secondary(
                    $url,
                    $noimage,
                    $description
                );
            }
        }
        if (($this->assignment->get_instance()->duedate ||
                $this->assignment->get_instance()->cutoffdate) &&
                $this->hasgrantextension) {
             $urlparams = array('id' => $this->assignment->get_course_module()->id,
                                'userid' => $row->id,
                                'action' => 'grantextension',
                                'sesskey' => sesskey(),
                                'page' => $this->currpage);
             $url = new moodle_url('/mod/assign/view.php', $urlparams);
             $description = get_string('grantextension', 'assign');
             $actions['grantextension'] = new action_menu_link_secondary(
                 $url,
                 $noimage,
                 $description
             );
        }
        if ($row->status == ASSIGN_SUBMISSION_STATUS_SUBMITTED &&
                $this->assignment->get_instance()->submissiondrafts) {
            $urlparams = array('id' => $this->assignment->get_course_module()->id,
                               'userid'=>$row->id,
                               'action'=>'reverttodraft',
                               'sesskey'=>sesskey(),
                               'page'=>$this->currpage);
            $url = new moodle_url('/mod/assign/view.php', $urlparams);
            $description = get_string('reverttodraftshort', 'assign');
            $actions['reverttodraft'] = new action_menu_link_secondary(
                $url,
                $noimage,
                $description
            );
        }
        if ($row->status == ASSIGN_SUBMISSION_STATUS_DRAFT &&
                $this->assignment->get_instance()->submissiondrafts &&
                $caneditsubmission &&
                $submissionsopen &&
                $row->id != $USER->id) {
            $urlparams = array('id' => $this->assignment->get_course_module()->id,
                               'userid'=>$row->id,
                               'action'=>'submitotherforgrading',
                               'sesskey'=>sesskey(),
                               'page'=>$this->currpage);
            $url = new moodle_url('/mod/assign/view.php', $urlparams);
            $description = get_string('submitforgrading', 'assign');
            $actions['submitforgrading'] = new action_menu_link_secondary(
                $url,
                $noimage,
                $description
            );
        }

        $ismanual = $this->assignment->get_instance()->attemptreopenmethod == ASSIGN_ATTEMPT_REOPEN_METHOD_MANUAL;
        $hassubmission = !empty($row->status);
        $notreopened = $hassubmission && $row->status != ASSIGN_SUBMISSION_STATUS_REOPENED;
        $isunlimited = $this->assignment->get_instance()->maxattempts == ASSIGN_UNLIMITED_ATTEMPTS;
        $hasattempts = $isunlimited || $row->attemptnumber < $this->assignment->get_instance()->maxattempts - 1;

        if ($ismanual && $hassubmission && $notreopened && $hasattempts) {
            $urlparams = array('id' => $this->assignment->get_course_module()->id,
                               'userid'=>$row->id,
                               'action'=>'addattempt',
                               'sesskey'=>sesskey(),
                               'page'=>$this->currpage);
            $url = new moodle_url('/mod/assign/view.php', $urlparams);
            $description = get_string('addattempt', 'assign');
            $actions['addattempt'] = new action_menu_link_secondary(
                $url,
                $noimage,
                $description
            );
        }

        $menu = new action_menu();
        $menu->set_owner_selector('.gradingtable-actionmenu');
        $menu->set_alignment(action_menu::TL, action_menu::BL);
        $menu->set_constraint('.gradingtable > .no-overflow');
        $menu->set_menu_trigger(get_string('edit'));
        foreach ($actions as $action) {
            $menu->add($action);
        }

                $menu->prioritise = true;

        $edit .= $this->output->render($menu);

        return $edit;
    }

    
    private function format_plugin_summary_with_link(assign_plugin $plugin,
                                                     stdClass $item,
                                                     $returnaction,
                                                     $returnparams) {
        $link = '';
        $showviewlink = false;

        $summary = $plugin->view_summary($item, $showviewlink);
        $separator = '';
        if ($showviewlink) {
            $viewstr = get_string('view' . substr($plugin->get_subtype(), strlen('assign')), 'assign');
            $icon = $this->output->pix_icon('t/preview', $viewstr);
            $urlparams = array('id' => $this->assignment->get_course_module()->id,
                                                     'sid'=>$item->id,
                                                     'gid'=>$item->id,
                                                     'plugin'=>$plugin->get_type(),
                                                     'action'=>'viewplugin' . $plugin->get_subtype(),
                                                     'returnaction'=>$returnaction,
                                                     'returnparams'=>http_build_query($returnparams));
            $url = new moodle_url('/mod/assign/view.php', $urlparams);
            $link = $this->output->action_link($url, $icon);
            $separator = $this->output->spacer(array(), true);
        }

        return $link . $separator . $summary;
    }


    
    public function other_cols($colname, $row) {
                if (empty($this->plugincache[$colname])) {
            return $row->$colname;
        }

                $plugincache = $this->plugincache[$colname];

        $plugin = $plugincache[0];

        $field = null;
        if (isset($plugincache[1])) {
            $field = $plugincache[1];
        }

        if ($plugin->is_visible() && $plugin->is_enabled()) {
            if ($plugin->get_subtype() == 'assignsubmission') {
                if ($this->assignment->get_instance()->teamsubmission) {
                    $group = false;
                    $submission = false;

                    $this->get_group_and_submission($row->id, $group, $submission, -1);
                    if ($submission) {
                        if ($submission->status == ASSIGN_SUBMISSION_STATUS_REOPENED) {
                                                        $this->get_group_and_submission($row->id, $group, $submission, $submission->attemptnumber-1);
                        }
                        if (isset($field)) {
                            return $plugin->get_editor_text($field, $submission->id);
                        }
                        return $this->format_plugin_summary_with_link($plugin,
                                                                      $submission,
                                                                      'grading',
                                                                      array());
                    }
                } else if ($row->submissionid) {
                    if ($row->status == ASSIGN_SUBMISSION_STATUS_REOPENED) {
                                                $submission = $this->assignment->get_user_submission($row->userid, false, $row->attemptnumber - 1);
                    } else {
                        $submission = new stdClass();
                        $submission->id = $row->submissionid;
                        $submission->timecreated = $row->firstsubmission;
                        $submission->timemodified = $row->timesubmitted;
                        $submission->assignment = $this->assignment->get_instance()->id;
                        $submission->userid = $row->userid;
                        $submission->attemptnumber = $row->attemptnumber;
                    }
                                        if (isset($field)) {
                        return $plugin->get_editor_text($field, $submission->id);
                    }
                    return $this->format_plugin_summary_with_link($plugin,
                                                                  $submission,
                                                                  'grading',
                                                                  array());
                }
            } else {
                $grade = null;
                if (isset($field)) {
                    return $plugin->get_editor_text($field, $row->gradeid);
                }

                if ($row->gradeid) {
                    $grade = new stdClass();
                    $grade->id = $row->gradeid;
                    $grade->timecreated = $row->firstmarked;
                    $grade->timemodified = $row->timemarked;
                    $grade->assignment = $this->assignment->get_instance()->id;
                    $grade->userid = $row->userid;
                    $grade->grade = $row->grade;
                    $grade->mailed = $row->mailed;
                    $grade->attemptnumber = $row->attemptnumber;
                }
                if ($this->quickgrading && $plugin->supports_quickgrading()) {
                    return $plugin->get_quickgrading_html($row->userid, $grade);
                } else if ($grade) {
                    return $this->format_plugin_summary_with_link($plugin,
                                                                  $grade,
                                                                  'grading',
                                                                  array());
                }
            }
        }
        return '';
    }

    
    public function get_column_data($columnname) {
        $this->setup();
        $this->currpage = 0;
        $this->query_db($this->tablemaxrows);
        $result = array();
        foreach ($this->rawdata as $row) {
            $result[] = $row->$columnname;
        }
        return $result;
    }

    
    public function get_assignment_name() {
        return $this->assignment->get_instance()->name;
    }

    
    public function get_course_module_id() {
        return $this->assignment->get_course_module()->id;
    }

    
    public function get_course_id() {
        return $this->assignment->get_course()->id;
    }

    
    public function get_course_context() {
        return $this->assignment->get_course_context();
    }

    
    public function submissions_enabled() {
        return $this->assignment->is_any_submission_plugin_enabled();
    }

    
    public function can_view_all_grades() {
        $context = $this->assignment->get_course_context();
        return has_capability('gradereport/grader:view', $context) &&
               has_capability('moodle/grade:viewall', $context);
    }

    
    public function get_sort_columns() {
        $result = parent::get_sort_columns();
        $result = array_merge($result, array('userid' => SORT_ASC));
        return $result;
    }

    
    protected function show_hide_link($column, $index) {
        if ($index > 0 || !$this->hasgrade) {
            return parent::show_hide_link($column, $index);
        }
        return '';
    }

    
    public function setup() {
                        if (!empty($this->setup)) {
            return;
        }
        parent::setup();
    }
}
