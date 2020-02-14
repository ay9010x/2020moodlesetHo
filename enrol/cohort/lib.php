<?php



defined('MOODLE_INTERNAL') || die();


define('COHORT_CREATE_GROUP', -1);


class enrol_cohort_plugin extends enrol_plugin {

    
    public function can_delete_instance($instance) {
        $context = context_course::instance($instance->courseid);
        return has_capability('enrol/cohort:config', $context);
    }

    
    public function get_instance_name($instance) {
        global $DB;

        if (empty($instance)) {
            $enrol = $this->get_name();
            return get_string('pluginname', 'enrol_'.$enrol);

        } else if (empty($instance->name)) {
            $enrol = $this->get_name();
            $cohort = $DB->get_record('cohort', array('id'=>$instance->customint1));
            if (!$cohort) {
                return get_string('pluginname', 'enrol_'.$enrol);
            }
            $cohortname = format_string($cohort->name, true, array('context'=>context::instance_by_id($cohort->contextid)));
            if ($role = $DB->get_record('role', array('id'=>$instance->roleid))) {
                $role = role_get_name($role, context_course::instance($instance->courseid, IGNORE_MISSING));
                return get_string('pluginname', 'enrol_'.$enrol) . ' (' . $cohortname . ' - ' . $role .')';
            } else {
                return get_string('pluginname', 'enrol_'.$enrol) . ' (' . $cohortname . ')';
            }

        } else {
            return format_string($instance->name, true, array('context'=>context_course::instance($instance->courseid)));
        }
    }

    
    public function can_add_instance($courseid) {
        global $CFG;
        require_once($CFG->dirroot . '/cohort/lib.php');
        $coursecontext = context_course::instance($courseid);
        if (!has_capability('moodle/course:enrolconfig', $coursecontext) or !has_capability('enrol/cohort:config', $coursecontext)) {
            return false;
        }
        return cohort_get_available_cohorts($coursecontext, 0, 0, 1) ? true : false;
    }

    
    public function add_instance($course, array $fields = null) {
        global $CFG;

        if (!empty($fields['customint2']) && $fields['customint2'] == COHORT_CREATE_GROUP) {
                        $context = context_course::instance($course->id);
            require_capability('moodle/course:managegroups', $context);
            $groupid = enrol_cohort_create_new_group($course->id, $fields['customint1']);
            $fields['customint2'] = $groupid;
        }

        $result = parent::add_instance($course, $fields);

        require_once("$CFG->dirroot/enrol/cohort/locallib.php");
        $trace = new null_progress_trace();
        enrol_cohort_sync($trace, $course->id);
        $trace->finished();

        return $result;
    }

    
    public function update_instance($instance, $data) {
        global $CFG;

                $context = context_course::instance($instance->courseid);
        if ($data->roleid != $instance->roleid) {
                        $params = array(
                'contextid' => $context->id,
                'roleid' => $instance->roleid,
                'component' => 'enrol_cohort',
                'itemid' => $instance->id
            );
            role_unassign_all($params);
        }
                if ($data->customint2 == COHORT_CREATE_GROUP) {
            require_capability('moodle/course:managegroups', $context);
            $groupid = enrol_cohort_create_new_group($instance->courseid, $data->customint1);
            $data->customint2 = $groupid;
        }

        $result = parent::update_instance($instance, $data);

        require_once("$CFG->dirroot/enrol/cohort/locallib.php");
        $trace = new null_progress_trace();
        enrol_cohort_sync($trace, $instance->courseid);
        $trace->finished();

        return $result;
    }

    
    public function cron() {
        global $CFG;

        require_once("$CFG->dirroot/enrol/cohort/locallib.php");
        $trace = new null_progress_trace();
        enrol_cohort_sync($trace);
        $trace->finished();
    }

    
    public function course_updated($inserted, $course, $data) {
            }

    
    public function update_status($instance, $newstatus) {
        global $CFG;

        parent::update_status($instance, $newstatus);

        require_once("$CFG->dirroot/enrol/cohort/locallib.php");
        $trace = new null_progress_trace();
        enrol_cohort_sync($trace, $instance->courseid);
        $trace->finished();
    }

    
    public function allow_unenrol_user(stdClass $instance, stdClass $ue) {
        if ($ue->status == ENROL_USER_SUSPENDED) {
            return true;
        }

        return false;
    }

    
    public function get_user_enrolment_actions(course_enrolment_manager $manager, $ue) {
        $actions = array();
        $context = $manager->get_context();
        $instance = $ue->enrolmentinstance;
        $params = $manager->get_moodlepage()->url->params();
        $params['ue'] = $ue->id;
        if ($this->allow_unenrol_user($instance, $ue) && has_capability('enrol/cohort:unenrol', $context)) {
            $url = new moodle_url('/enrol/unenroluser.php', $params);
            $actions[] = new user_enrolment_action(new pix_icon('t/delete', ''), get_string('unenrol', 'enrol'), $url, array('class'=>'unenrollink', 'rel'=>$ue->id));
        }
        return $actions;
    }

    
    public function restore_instance(restore_enrolments_structure_step $step, stdClass $data, $course, $oldid) {
        global $DB, $CFG;

        if (!$step->get_task()->is_samesite()) {
                        $step->set_mapping('enrol', $oldid, 0);
            return;
        }

        if (!empty($data->customint2)) {
            $data->customint2 = $step->get_mappingid('group', $data->customint2);
        }

        if ($data->roleid and $DB->record_exists('cohort', array('id'=>$data->customint1))) {
            $instance = $DB->get_record('enrol', array('roleid'=>$data->roleid, 'customint1'=>$data->customint1, 'courseid'=>$course->id, 'enrol'=>$this->get_name()));
            if ($instance) {
                $instanceid = $instance->id;
            } else {
                $instanceid = $this->add_instance($course, (array)$data);
            }
            $step->set_mapping('enrol', $oldid, $instanceid);

            require_once("$CFG->dirroot/enrol/cohort/locallib.php");
            $trace = new null_progress_trace();
            enrol_cohort_sync($trace, $course->id);
            $trace->finished();

        } else if ($this->get_config('unenrolaction') == ENROL_EXT_REMOVED_SUSPENDNOROLES) {
            $data->customint1 = 0;
            $instance = $DB->get_record('enrol', array('roleid'=>$data->roleid, 'customint1'=>$data->customint1, 'courseid'=>$course->id, 'enrol'=>$this->get_name()));

            if ($instance) {
                $instanceid = $instance->id;
            } else {
                $data->status = ENROL_INSTANCE_DISABLED;
                $instanceid = $this->add_instance($course, (array)$data);
            }
            $step->set_mapping('enrol', $oldid, $instanceid);

            require_once("$CFG->dirroot/enrol/cohort/locallib.php");
            $trace = new null_progress_trace();
            enrol_cohort_sync($trace, $course->id);
            $trace->finished();

        } else {
            $step->set_mapping('enrol', $oldid, 0);
        }
    }

    
    public function restore_user_enrolment(restore_enrolments_structure_step $step, $data, $instance, $userid, $oldinstancestatus) {
        global $DB;

        if ($this->get_config('unenrolaction') != ENROL_EXT_REMOVED_SUSPENDNOROLES) {
                        return;
        }

                
        if (!$DB->record_exists('user_enrolments', array('enrolid'=>$instance->id, 'userid'=>$userid))) {
            $this->enrol_user($instance, $userid, null, $data->timestart, $data->timeend, ENROL_USER_SUSPENDED);
        }
    }

    
    public function restore_group_member($instance, $groupid, $userid) {
                return;
    }

    
    public function can_hide_show_instance($instance) {
        $context = context_course::instance($instance->courseid);
        return has_capability('enrol/cohort:config', $context);
    }

    
    protected function get_status_options() {
        $options = array(ENROL_INSTANCE_ENABLED  => get_string('yes'),
                         ENROL_INSTANCE_DISABLED => get_string('no'));
        return $options;
    }

    
    protected function get_cohort_options($instance, $context) {
        global $DB, $CFG;

        require_once($CFG->dirroot . '/cohort/lib.php');

        $cohorts = array();

        if ($instance->id) {
            if ($cohort = $DB->get_record('cohort', array('id' => $instance->customint1))) {
                $name = format_string($cohort->name, true, array('context' => context::instance_by_id($cohort->contextid)));
                $cohorts = array($instance->customint1 => $name);
            } else {
                $cohorts = array($instance->customint1 => get_string('error'));
            }
        } else {
            $cohorts = array('' => get_string('choosedots'));
            $allcohorts = cohort_get_available_cohorts($context, 0, 0, 0);
            foreach ($allcohorts as $c) {
                $cohorts[$c->id] = format_string($c->name);
            }
        }
        return $cohorts;
    }

    
    protected function get_role_options($instance, $coursecontext) {
        global $DB;

        $roles = get_assignable_roles($coursecontext);
        $roles[0] = get_string('none');
        $roles = array_reverse($roles, true);         if ($instance->id and !isset($roles[$instance->roleid])) {
            if ($role = $DB->get_record('role', array('id' => $instance->roleid))) {
                $roles = role_fix_names($roles, $coursecontext, ROLENAME_ALIAS, true);
                $roles[$instance->roleid] = role_get_name($role, $coursecontext);
            } else {
                $roles[$instance->roleid] = get_string('error');
            }
        }

        return $roles;
    }

    
    protected function get_group_options($coursecontext) {
        $groups = array(0 => get_string('none'));
        if (has_capability('moodle/course:managegroups', $coursecontext)) {
            $groups[COHORT_CREATE_GROUP] = get_string('creategroup', 'enrol_cohort');
        }

        foreach (groups_get_all_groups($coursecontext->instanceid) as $group) {
            $groups[$group->id] = format_string($group->name, true, array('context' => $coursecontext));
        }

        return $groups;
    }

    
    public function use_standard_editing_ui() {
        return true;
    }

    
    public function edit_instance_form($instance, MoodleQuickForm $mform, $coursecontext) {
        global $DB;

        $mform->addElement('text', 'name', get_string('custominstancename', 'enrol'));
        $mform->setType('name', PARAM_TEXT);

        $options = $this->get_status_options();
        $mform->addElement('select', 'status', get_string('status', 'enrol_cohort'), $options);

        $options = $this->get_cohort_options($instance, $coursecontext);
        $mform->addElement('select', 'customint1', get_string('cohort', 'cohort'), $options);
        if ($instance->id) {
            $mform->setConstant('customint1', $instance->customint1);
            $mform->hardFreeze('customint1', $instance->customint1);
        } else {
            $mform->addRule('customint1', get_string('required'), 'required', null, 'client');
        }

        $roles = $this->get_role_options($instance, $coursecontext);
        $mform->addElement('select', 'roleid', get_string('assignrole', 'enrol_cohort'), $roles);
        $mform->setDefault('roleid', $this->get_config('roleid'));
        $groups = $this->get_group_options($coursecontext);
        $mform->addElement('select', 'customint2', get_string('addgroup', 'enrol_cohort'), $groups);
    }

    
    public function edit_instance_validation($data, $files, $instance, $context) {
        global $DB;
        $errors = array();

        $params = array(
            'roleid' => $data['roleid'],
            'customint1' => $data['customint1'],
            'courseid' => $data['courseid'],
            'id' => $data['id']
        );
        $sql = "roleid = :roleid AND customint1 = :customint1 AND courseid = :courseid AND enrol = 'cohort' AND id <> :id";
        if ($DB->record_exists_select('enrol', $sql, $params)) {
            $errors['roleid'] = get_string('instanceexists', 'enrol_cohort');
        }
        $validstatus = array_keys($this->get_status_options());
        $validcohorts = array_keys($this->get_cohort_options($instance, $context));
        $validroles = array_keys($this->get_role_options($instance, $context));
        $validgroups = array_keys($this->get_group_options($context));
        $tovalidate = array(
            'name' => PARAM_TEXT,
            'status' => $validstatus,
            'customint1' => $validcohorts,
            'roleid' => $validroles,
            'customint2' => $validgroups
        );
        $typeerrors = $this->validate_param_types($data, $tovalidate);
        $errors = array_merge($errors, $typeerrors);

        return $errors;
    }
}


function enrol_cohort_allow_group_member_remove($itemid, $groupid, $userid) {
    return false;
}


function enrol_cohort_create_new_group($courseid, $cohortid) {
    global $DB, $CFG;

    require_once($CFG->dirroot . '/group/lib.php');

    $groupname = $DB->get_field('cohort', 'name', array('id' => $cohortid), MUST_EXIST);
    $a = new stdClass();
    $a->name = $groupname;
    $a->increment = '';
    $groupname = trim(get_string('defaultgroupnametext', 'enrol_cohort', $a));
    $inc = 1;
        while ($DB->record_exists('groups', array('name' => $groupname, 'courseid' => $courseid))) {
        $a->increment = '(' . (++$inc) . ')';
        $newshortname = trim(get_string('defaultgroupnametext', 'enrol_cohort', $a));
        $groupname = $newshortname;
    }
        $groupdata = new stdClass();
    $groupdata->courseid = $courseid;
    $groupdata->name = $groupname;
    $groupid = groups_create_group($groupdata);

    return $groupid;
}
