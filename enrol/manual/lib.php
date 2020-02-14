<?php



defined('MOODLE_INTERNAL') || die();

class enrol_manual_plugin extends enrol_plugin {

    protected $lasternoller = null;
    protected $lasternollerinstanceid = 0;

    public function roles_protected() {
                return false;
    }

    public function allow_enrol(stdClass $instance) {
                return true;
    }

    public function allow_unenrol(stdClass $instance) {
                return true;
    }

    public function allow_manage(stdClass $instance) {
                return true;
    }

    
    public function get_manual_enrol_link($instance) {
        $name = $this->get_name();
        if ($instance->enrol !== $name) {
            throw new coding_exception('invalid enrol instance!');
        }

        if (!enrol_is_enabled($name)) {
            return NULL;
        }

        $context = context_course::instance($instance->courseid, MUST_EXIST);

        if (!has_capability('enrol/manual:enrol', $context)) {
                                    return NULL;
        }

        return new moodle_url('/enrol/manual/manage.php', array('enrolid'=>$instance->id, 'id'=>$instance->courseid));
    }

    
    public function can_add_instance($courseid) {
        global $DB;

        $context = context_course::instance($courseid, MUST_EXIST);
        if (!has_capability('moodle/course:enrolconfig', $context) or !has_capability('enrol/manual:config', $context)) {
            return false;
        }

        if ($DB->record_exists('enrol', array('courseid'=>$courseid, 'enrol'=>'manual'))) {
                        return false;
        }

        return true;
    }

    
    public function get_action_icons(stdClass $instance) {
        global $OUTPUT;

        $context = context_course::instance($instance->courseid);

        $icons = array();
        if (has_capability('enrol/manual:enrol', $context) or has_capability('enrol/manual:unenrol', $context)) {
            $managelink = new moodle_url("/enrol/manual/manage.php", array('enrolid'=>$instance->id));
            $icons[] = $OUTPUT->action_icon($managelink, new pix_icon('t/enrolusers', get_string('enrolusers', 'enrol_manual'), 'core', array('class'=>'iconsmall')));
        }
        $parenticons = parent::get_action_icons($instance);
        $icons = array_merge($icons, $parenticons);

        return $icons;
    }

    
    public function add_default_instance($course) {
        $expirynotify = $this->get_config('expirynotify', 0);
        if ($expirynotify == 2) {
            $expirynotify = 1;
            $notifyall = 1;
        } else {
            $notifyall = 0;
        }
        $fields = array(
            'status'          => $this->get_config('status'),
            'roleid'          => $this->get_config('roleid', 0),
            'enrolperiod'     => $this->get_config('enrolperiod', 0),
            'expirynotify'    => $expirynotify,
            'notifyall'       => $notifyall,
            'expirythreshold' => $this->get_config('expirythreshold', 86400),
        );
        return $this->add_instance($course, $fields);
    }

    
    public function add_instance($course, array $fields = NULL) {
        global $DB;

        if ($DB->record_exists('enrol', array('courseid'=>$course->id, 'enrol'=>'manual'))) {
                        return NULL;
        }

        return parent::add_instance($course, $fields);
    }

    
    public function update_instance($instance, $data) {
        global $DB;

                if ($instances = $DB->get_records('enrol', array('courseid' => $instance->courseid, 'enrol' => 'manual'), 'id ASC')) {
            foreach ($instances as $anotherinstance) {
                if ($anotherinstance->id != $instance->id) {
                    $this->delete_instance($anotherinstance);
                }
            }
        }
        return parent::update_instance($instance, $data);
    }

    
    public function get_manual_enrol_button(course_enrolment_manager $manager) {
        global $CFG;
        require_once($CFG->dirroot.'/cohort/lib.php');

        $instance = null;
        $instances = array();
        foreach ($manager->get_enrolment_instances() as $tempinstance) {
            if ($tempinstance->enrol == 'manual') {
                if ($instance === null) {
                    $instance = $tempinstance;
                }
                $instances[] = array('id' => $tempinstance->id, 'name' => $this->get_instance_name($tempinstance));
            }
        }
        if (empty($instance)) {
            return false;
        }

        if (!$manuallink = $this->get_manual_enrol_link($instance)) {
            return false;
        }

        $button = new enrol_user_button($manuallink, get_string('enrolusers', 'enrol_manual'), 'get');
        $button->class .= ' enrol_manual_plugin';

        $startdate = $manager->get_course()->startdate;
        if (!$defaultstart = get_config('enrol_manual', 'enrolstart')) {
                        $defaultstart = 4;
        }
        $startdateoptions = array();
        $dateformat = get_string('strftimedatefullshort');
        if ($startdate > 0) {
            $startdateoptions[2] = get_string('coursestart') . ' (' . userdate($startdate, $dateformat) . ')';
        }
        $now = time();
        $today = make_timestamp(date('Y', $now), date('m', $now), date('d', $now), 0, 0, 0);
        $startdateoptions[3] = get_string('today') . ' (' . userdate($today, $dateformat) . ')';
        $startdateoptions[4] = get_string('now', 'enrol_manual') . ' (' . userdate($now, get_string('strftimedatetimeshort')) . ')';
        $defaultduration = $instance->enrolperiod > 0 ? $instance->enrolperiod / DAYSECS : '';

        $modules = array('moodle-enrol_manual-quickenrolment', 'moodle-enrol_manual-quickenrolment-skin');
        $arguments = array(
            'instances'           => $instances,
            'courseid'            => $instance->courseid,
            'ajaxurl'             => '/enrol/manual/ajax.php',
            'url'                 => $manager->get_moodlepage()->url->out(false),
            'optionsStartDate'    => $startdateoptions,
            'defaultRole'         => $instance->roleid,
            'defaultDuration'     => $defaultduration,
            'defaultStartDate'    => (int)$defaultstart,
            'disableGradeHistory' => $CFG->disablegradehistory,
            'recoverGradesDefault'=> '',
            'cohortsAvailable'    => cohort_get_available_cohorts($manager->get_context(), COHORT_WITH_NOTENROLLED_MEMBERS_ONLY, 0, 1) ? true : false
        );

        if ($CFG->recovergradesdefault) {
            $arguments['recoverGradesDefault'] = ' checked="checked"';
        }

        $function = 'M.enrol_manual.quickenrolment.init';
        $button->require_yui_module($modules, $function, array($arguments));
        $button->strings_for_js(array(
            'ajaxoneuserfound',
            'ajaxxusersfound',
            'ajaxnext25',
            'enrol',
            'enrolmentoptions',
            'enrolusers',
            'enrolxusers',
            'errajaxfailedenrol',
            'errajaxsearch',
            'foundxcohorts',
            'none',
            'usersearch',
            'unlimitedduration',
            'startdatetoday',
            'durationdays',
            'enrolperiod',
            'finishenrollingusers',
            'recovergrades'), 'enrol');
        $button->strings_for_js(array('browseusers', 'browsecohorts'), 'enrol_manual');
        $button->strings_for_js('assignroles', 'role');
        $button->strings_for_js('startingfrom', 'moodle');

        return $button;
    }

    
    public function cron() {
        $trace = new text_progress_trace();
        $this->sync($trace, null);
        $this->send_expiry_notifications($trace);
    }

    
    public function sync(progress_trace $trace, $courseid = null) {
        global $DB;

        if (!enrol_is_enabled('manual')) {
            $trace->finished();
            return 2;
        }

                core_php_time_limit::raise();
        raise_memory_limit(MEMORY_HUGE);

        $trace->output('Verifying manual enrolment expiration...');

        $params = array('now'=>time(), 'useractive'=>ENROL_USER_ACTIVE, 'courselevel'=>CONTEXT_COURSE);
        $coursesql = "";
        if ($courseid) {
            $coursesql = "AND e.courseid = :courseid";
            $params['courseid'] = $courseid;
        }

                $action = $this->get_config('expiredaction', ENROL_EXT_REMOVED_KEEP);

        if ($action == ENROL_EXT_REMOVED_UNENROL) {
            $instances = array();
            $sql = "SELECT ue.*, e.courseid, c.id AS contextid
                      FROM {user_enrolments} ue
                      JOIN {enrol} e ON (e.id = ue.enrolid AND e.enrol = 'manual')
                      JOIN {context} c ON (c.instanceid = e.courseid AND c.contextlevel = :courselevel)
                     WHERE ue.timeend > 0 AND ue.timeend < :now
                           $coursesql";
            $rs = $DB->get_recordset_sql($sql, $params);
            foreach ($rs as $ue) {
                if (empty($instances[$ue->enrolid])) {
                    $instances[$ue->enrolid] = $DB->get_record('enrol', array('id'=>$ue->enrolid));
                }
                $instance = $instances[$ue->enrolid];
                                role_unassign_all(array('userid'=>$ue->userid, 'contextid'=>$ue->contextid, 'component'=>'', 'itemid'=>0), true);
                $this->unenrol_user($instance, $ue->userid);
                $trace->output("unenrolling expired user $ue->userid from course $instance->courseid", 1);
            }
            $rs->close();
            unset($instances);

        } else if ($action == ENROL_EXT_REMOVED_SUSPENDNOROLES or $action == ENROL_EXT_REMOVED_SUSPEND) {
            $instances = array();
            $sql = "SELECT ue.*, e.courseid, c.id AS contextid
                      FROM {user_enrolments} ue
                      JOIN {enrol} e ON (e.id = ue.enrolid AND e.enrol = 'manual')
                      JOIN {context} c ON (c.instanceid = e.courseid AND c.contextlevel = :courselevel)
                     WHERE ue.timeend > 0 AND ue.timeend < :now
                           AND ue.status = :useractive
                           $coursesql";
            $rs = $DB->get_recordset_sql($sql, $params);
            foreach ($rs as $ue) {
                if (empty($instances[$ue->enrolid])) {
                    $instances[$ue->enrolid] = $DB->get_record('enrol', array('id'=>$ue->enrolid));
                }
                $instance = $instances[$ue->enrolid];
                if ($action == ENROL_EXT_REMOVED_SUSPENDNOROLES) {
                                        role_unassign_all(array('userid'=>$ue->userid, 'contextid'=>$ue->contextid, 'component'=>'', 'itemid'=>0), true);
                    $this->update_user_enrol($instance, $ue->userid, ENROL_USER_SUSPENDED);
                    $trace->output("suspending expired user $ue->userid in course $instance->courseid, roles unassigned", 1);
                } else {
                    $this->update_user_enrol($instance, $ue->userid, ENROL_USER_SUSPENDED);
                    $trace->output("suspending expired user $ue->userid in course $instance->courseid, roles kept", 1);
                }
            }
            $rs->close();
            unset($instances);

        } else {
                    }

        $trace->output('...manual enrolment updates finished.');
        $trace->finished();

        return 0;
    }

    
    protected function get_enroller($instanceid) {
        global $DB;

        if ($this->lasternollerinstanceid == $instanceid and $this->lasternoller) {
            return $this->lasternoller;
        }

        $instance = $DB->get_record('enrol', array('id'=>$instanceid, 'enrol'=>$this->get_name()), '*', MUST_EXIST);
        $context = context_course::instance($instance->courseid);

        if ($users = get_enrolled_users($context, 'enrol/manual:manage')) {
            $users = sort_by_roleassignment_authority($users, $context);
            $this->lasternoller = reset($users);
            unset($users);
        } else {
            $this->lasternoller = parent::get_enroller($instanceid);
        }

        $this->lasternollerinstanceid = $instanceid;

        return $this->lasternoller;
    }

    
    public function get_user_enrolment_actions(course_enrolment_manager $manager, $ue) {
        $actions = array();
        $context = $manager->get_context();
        $instance = $ue->enrolmentinstance;
        $params = $manager->get_moodlepage()->url->params();
        $params['ue'] = $ue->id;
        if ($this->allow_unenrol_user($instance, $ue) && has_capability("enrol/manual:unenrol", $context)) {
            $url = new moodle_url('/enrol/unenroluser.php', $params);
            $actions[] = new user_enrolment_action(new pix_icon('t/delete', ''), get_string('unenrol', 'enrol'), $url, array('class'=>'unenrollink', 'rel'=>$ue->id));
        }
        if ($this->allow_manage($instance) && has_capability("enrol/manual:manage", $context)) {
            $url = new moodle_url('/enrol/editenrolment.php', $params);
            $actions[] = new user_enrolment_action(new pix_icon('t/edit', ''), get_string('edit'), $url, array('class'=>'editenrollink', 'rel'=>$ue->id));
        }
        return $actions;
    }

    
    public function get_bulk_operations(course_enrolment_manager $manager) {
        global $CFG;
        require_once($CFG->dirroot.'/enrol/manual/locallib.php');
        $context = $manager->get_context();
        $bulkoperations = array();
        if (has_capability("enrol/manual:manage", $context)) {
            $bulkoperations['editselectedusers'] = new enrol_manual_editselectedusers_operation($manager, $this);
        }
        if (has_capability("enrol/manual:unenrol", $context)) {
            $bulkoperations['deleteselectedusers'] = new enrol_manual_deleteselectedusers_operation($manager, $this);
        }
        return $bulkoperations;
    }

    
    public function restore_instance(restore_enrolments_structure_step $step, stdClass $data, $course, $oldid) {
        global $DB;
                if ($instances = $DB->get_records('enrol', array('courseid'=>$data->courseid, 'enrol'=>'manual'), 'id')) {
            $instance = reset($instances);
            $instanceid = $instance->id;
        } else {
            $instanceid = $this->add_instance($course, (array)$data);
        }
        $step->set_mapping('enrol', $oldid, $instanceid);
    }

    
    public function restore_user_enrolment(restore_enrolments_structure_step $step, $data, $instance, $userid, $oldinstancestatus) {
        global $DB;

                
        $ue = $DB->get_record('user_enrolments', array('enrolid'=>$instance->id, 'userid'=>$userid));
        $enrol = false;
        if ($ue and $ue->status == ENROL_USER_ACTIVE) {
                                    if ($data->status == ENROL_USER_ACTIVE) {
                if ($data->timestart > $ue->timestart) {
                    $data->timestart = $ue->timestart;
                    $enrol = true;
                }

                if ($data->timeend == 0) {
                    if ($ue->timeend != 0) {
                        $enrol = true;
                    }
                } else if ($ue->timeend == 0) {
                    $data->timeend = 0;
                } else if ($data->timeend < $ue->timeend) {
                    $data->timeend = $ue->timeend;
                    $enrol = true;
                }
            }
        } else {
            if ($instance->status == ENROL_INSTANCE_ENABLED and $oldinstancestatus != ENROL_INSTANCE_ENABLED) {
                                                $data->status = ENROL_USER_SUSPENDED;
            }
            $enrol = true;
        }

        if ($enrol) {
            $this->enrol_user($instance, $userid, null, $data->timestart, $data->timeend, $data->status);
        }
    }

    
    public function restore_role_assignment($instance, $roleid, $userid, $contextid) {
                        role_assign($roleid, $userid, $contextid, '', 0);
    }

    
    public function restore_group_member($instance, $groupid, $userid) {
        global $CFG;
        require_once("$CFG->dirroot/group/lib.php");

        
        groups_add_member($groupid, $userid);
    }

    
    public function can_delete_instance($instance) {
        $context = context_course::instance($instance->courseid);
        return has_capability('enrol/manual:config', $context);
    }

    
    public function can_hide_show_instance($instance) {
        $context = context_course::instance($instance->courseid);
        return has_capability('enrol/manual:config', $context);
    }

    
    public function enrol_cohort(stdClass $instance, $cohortid, $roleid = null, $timestart = 0, $timeend = 0, $status = null, $recovergrades = null) {
        global $DB;
        $context = context_course::instance($instance->courseid);
        list($esql, $params) = get_enrolled_sql($context);
        $sql = "SELECT cm.userid FROM {cohort_members} cm LEFT JOIN ($esql) u ON u.id = cm.userid ".
            "WHERE cm.cohortid = :cohortid AND u.id IS NULL";
        $params['cohortid'] = $cohortid;
        $members = $DB->get_fieldset_sql($sql, $params);
        foreach ($members as $userid) {
            $this->enrol_user($instance, $userid, $roleid, $timestart, $timeend, $status, $recovergrades);
        }
    }

    
    public function use_standard_editing_ui() {
        return true;
    }

    
    protected function get_status_options() {
        $options = array(ENROL_INSTANCE_ENABLED  => get_string('yes'),
                         ENROL_INSTANCE_DISABLED => get_string('no'));
        return $options;
    }

    
    protected function get_roleid_options($instance, $context) {
        if ($instance->id) {
            $roles = get_default_enrol_roles($context, $instance->roleid);
        } else {
            $roles = get_default_enrol_roles($context, $this->get_config('roleid'));
        }
        return $roles;
    }

    
    protected function get_expirynotify_options() {
        $options = array(
            0 => get_string('no'),
            1 => get_string('expirynotifyenroller', 'core_enrol'),
            2 => get_string('expirynotifyall', 'core_enrol')
        );
        return $options;
    }

    
    public function edit_instance_form($instance, MoodleQuickForm $mform, $context) {

        $options = $this->get_status_options();
        $mform->addElement('select', 'status', get_string('status', 'enrol_manual'), $options);
        $mform->addHelpButton('status', 'status', 'enrol_manual');
        $mform->setDefault('status', $this->get_config('status'));

        $roles = $this->get_roleid_options($instance, $context);
        $mform->addElement('select', 'roleid', get_string('defaultrole', 'role'), $roles);
        $mform->setDefault('roleid', $this->get_config('roleid'));

        $options = array('optional' => true, 'defaultunit' => 86400);
        $mform->addElement('duration', 'enrolperiod', get_string('defaultperiod', 'enrol_manual'), $options);
        $mform->setDefault('enrolperiod', $this->get_config('enrolperiod'));
        $mform->addHelpButton('enrolperiod', 'defaultperiod', 'enrol_manual');

        $options = $this->get_expirynotify_options();
        $mform->addElement('select', 'expirynotify', get_string('expirynotify', 'core_enrol'), $options);
        $mform->addHelpButton('expirynotify', 'expirynotify', 'core_enrol');

        $options = array('optional' => false, 'defaultunit' => 86400);
        $mform->addElement('duration', 'expirythreshold', get_string('expirythreshold', 'core_enrol'), $options);
        $mform->addHelpButton('expirythreshold', 'expirythreshold', 'core_enrol');
        $mform->disabledIf('expirythreshold', 'expirynotify', 'eq', 0);

        if (enrol_accessing_via_instance($instance)) {
            $warntext = get_string('instanceeditselfwarningtext', 'core_enrol');
            $mform->addElement('static', 'selfwarn', get_string('instanceeditselfwarning', 'core_enrol'), $warntext);
        }
    }

    
    public function edit_instance_validation($data, $files, $instance, $context) {
        $errors = array();

        if ($data['expirynotify'] > 0 and $data['expirythreshold'] < 86400) {
            $errors['expirythreshold'] = get_string('errorthresholdlow', 'core_enrol');
        }

        $validstatus = array_keys($this->get_status_options());
        $validroles = array_keys($this->get_roleid_options($instance, $context));
        $validexpirynotify = array_keys($this->get_expirynotify_options());

        $tovalidate = array(
            'status' => $validstatus,
            'roleid' => $validroles,
            'enrolperiod' => PARAM_INT,
            'expirynotify' => $validexpirynotify,
            'expirythreshold' => PARAM_INT
        );

        $typeerrors = $this->validate_param_types($data, $tovalidate);
        $errors = array_merge($errors, $typeerrors);

        return $errors;
    }

}
