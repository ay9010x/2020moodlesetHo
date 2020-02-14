<?php



defined('MOODLE_INTERNAL') || die();


define('ENROL_META_CREATE_GROUP', -1);


class enrol_meta_plugin extends enrol_plugin {

    
    public function get_instance_name($instance) {
        global $DB;

        if (empty($instance)) {
            $enrol = $this->get_name();
            return get_string('pluginname', 'enrol_'.$enrol);
        } else if (empty($instance->name)) {
            $enrol = $this->get_name();
            $course = $DB->get_record('course', array('id'=>$instance->customint1));
            if ($course) {
                $coursename = format_string(get_course_display_name_for_list($course));
            } else {
                                $coursename = $instance->customint1;
            }
            return get_string('pluginname', 'enrol_' . $enrol) . ' (' . $coursename . ')';
        } else {
            return format_string($instance->name);
        }
    }

    
    public function can_add_instance($courseid) {
        $context = context_course::instance($courseid, MUST_EXIST);
        if (!has_capability('moodle/course:enrolconfig', $context) or !has_capability('enrol/meta:config', $context)) {
            return false;
        }
                return true;
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
        if ($this->allow_unenrol_user($instance, $ue) && has_capability('enrol/meta:unenrol', $context)) {
            $url = new moodle_url('/enrol/unenroluser.php', $params);
            $actions[] = new user_enrolment_action(new pix_icon('t/delete', ''), get_string('unenrol', 'enrol'), $url, array('class'=>'unenrollink', 'rel'=>$ue->id));
        }
        return $actions;
    }

    
    public function course_updated($inserted, $course, $data) {
                    }

    
    public function add_instance($course, array $fields = null) {
        global $CFG;

        require_once("$CFG->dirroot/enrol/meta/locallib.php");

                if (is_array($fields['customint1'])) {
            $courses = array_unique($fields['customint1']);
        } else {
            $courses = array($fields['customint1']);
        }
        foreach ($courses as $courseid) {
            if (!empty($fields['customint2']) && $fields['customint2'] == ENROL_META_CREATE_GROUP) {
                $context = context_course::instance($course->id);
                require_capability('moodle/course:managegroups', $context);
                $groupid = enrol_meta_create_new_group($course->id, $courseid);
                $fields['customint2'] = $groupid;
            }

            $fields['customint1'] = $courseid;
            $result = parent::add_instance($course, $fields);
        }

        enrol_meta_sync($course->id);

        return $result;
    }

    
    public function update_instance($instance, $data) {
        global $CFG;

        require_once("$CFG->dirroot/enrol/meta/locallib.php");

        if (!empty($data->customint2) && $data->customint2 == ENROL_META_CREATE_GROUP) {
            $context = context_course::instance($instance->courseid);
            require_capability('moodle/course:managegroups', $context);
            $groupid = enrol_meta_create_new_group($instance->courseid, $data->customint1);
            $data->customint2 = $groupid;
        }

        $result = parent::update_instance($instance, $data);

        enrol_meta_sync($instance->courseid);

        return $result;
    }

    
    public function update_status($instance, $newstatus) {
        global $CFG;

        parent::update_status($instance, $newstatus);

        require_once("$CFG->dirroot/enrol/meta/locallib.php");
        enrol_meta_sync($instance->courseid);
    }

    
    public function cron() {
        global $CFG;

        require_once("$CFG->dirroot/enrol/meta/locallib.php");
        enrol_meta_sync();
    }

    
    public function can_delete_instance($instance) {
        $context = context_course::instance($instance->courseid);
        return has_capability('enrol/meta:config', $context);
    }

    
    public function can_hide_show_instance($instance) {
        $context = context_course::instance($instance->courseid);
        return has_capability('enrol/meta:config', $context);
    }

    
    public function use_standard_editing_ui() {
        return true;
    }

    
    protected function get_course_options($instance, $coursecontext) {
        global $DB;

        if ($instance->id) {
            $where = 'WHERE c.id = :courseid';
            $params = array('courseid' => $instance->customint1);
            $existing = array();
        } else {
            $where = '';
            $params = array();
            $instanceparams = array('enrol' => 'meta', 'courseid' => $instance->courseid);
            $existing = $DB->get_records('enrol', $instanceparams, '', 'customint1, id');
        }

                $courses = array();
        $select = ', ' . context_helper::get_preload_record_columns_sql('ctx');
        $join = "LEFT JOIN {context} ctx ON (ctx.instanceid = c.id AND ctx.contextlevel = :contextlevel)";

        $sortorder = 'c.' . $this->get_config('coursesort', 'sortorder') . ' ASC';

        $sql = "SELECT c.id, c.fullname, c.shortname, c.visible $select FROM {course} c $join $where ORDER BY $sortorder";
        $rs = $DB->get_recordset_sql($sql, array('contextlevel' => CONTEXT_COURSE) + $params);
        foreach ($rs as $c) {
            if ($c->id == SITEID or $c->id == $instance->courseid or isset($existing[$c->id])) {
                continue;
            }
            context_helper::preload_from_record($c);
            $coursecontext = context_course::instance($c->id);
            if (!$c->visible and !has_capability('moodle/course:viewhiddencourses', $coursecontext)) {
                continue;
            }
            if (!has_capability('enrol/meta:selectaslinked', $coursecontext)) {
                continue;
            }
            $courses[$c->id] = $coursecontext->get_context_name(false);
        }
        $rs->close();
        return $courses;
    }

    
    protected function get_group_options($coursecontext) {
        $groups = array(0 => get_string('none'));
        $courseid = $coursecontext->instanceid;
        if (has_capability('moodle/course:managegroups', $coursecontext)) {
            $groups[ENROL_META_CREATE_GROUP] = get_string('creategroup', 'enrol_meta');
        }
        foreach (groups_get_all_groups($courseid) as $group) {
            $groups[$group->id] = format_string($group->name, true, array('context' => $coursecontext));
        }
        return $groups;
    }

    
    public function edit_instance_form($instance, MoodleQuickForm $mform, $coursecontext) {
        global $DB;

        $groups = $this->get_group_options($coursecontext);
        $existing = $DB->get_records('enrol', array('enrol' => 'meta', 'courseid' => $coursecontext->instanceid), '', 'customint1, id');

        $excludelist = array($coursecontext->instanceid);
        foreach ($existing as $existinginstance) {
            $excludelist[] = $existinginstance->customint1;
        }

        $options = array(
            'requiredcapabilities' => array('enrol/meta:selectaslinked'),
            'multiple' => empty($instance->id),              'exclude' => $excludelist
        );
        $mform->addElement('course', 'customint1', get_string('linkedcourse', 'enrol_meta'), $options);
        $mform->addRule('customint1', get_string('required'), 'required', null, 'client');
        if (!empty($instance->id)) {
            $mform->freeze('customint1');
        }

        $mform->addElement('select', 'customint2', get_string('addgroup', 'enrol_meta'), $groups);
    }

    
    public function edit_instance_validation($data, $files, $instance, $context) {
        global $DB;
        $errors = array();
        $thiscourseid = $context->instanceid;
        $c = false;

        if (!empty($data['customint1'])) {
            $courses = is_array($data['customint1']) ? $data['customint1'] : [$data['customint1']];
            foreach ($courses as $courseid) {
                $c = $DB->get_record('course', array('id' => $courseid), '*', MUST_EXIST);
                $coursecontext = context_course::instance($c->id);

                $sqlexists = 'enrol = :meta AND courseid = :currentcourseid AND customint1 = :courseid AND id != :id';
                $existing = $DB->record_exists_select('enrol', $sqlexists, [
                    'meta' => 'meta',
                    'currentcourseid' => $thiscourseid,
                    'courseid' => $c->id,
                    'id' => $instance->id
                ]);

                if (!$c->visible and !has_capability('moodle/course:viewhiddencourses', $coursecontext)) {
                    $errors['customint1'] = get_string('error');
                } else if (!has_capability('enrol/meta:selectaslinked', $coursecontext)) {
                    $errors['customint1'] = get_string('error');
                } else if ($c->id == SITEID or $c->id == $thiscourseid or $existing) {
                    $errors['customint1'] = get_string('error');
                }
            }
        } else {
            $errors['customint1'] = get_string('required');
        }

        $validgroups = array_keys($this->get_group_options($context));

        $tovalidate = array(
            'customint2' => $validgroups
        );
        $typeerrors = $this->validate_param_types($data, $tovalidate);
        $errors = array_merge($errors, $typeerrors);

        return $errors;
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

        if ($DB->record_exists('course', array('id' => $data->customint1))) {
            $instance = $DB->get_record('enrol', array('roleid' => $data->roleid, 'customint1' => $data->customint1,
                'courseid' => $course->id, 'enrol' => $this->get_name()));
            if ($instance) {
                $instanceid = $instance->id;
            } else {
                $instanceid = $this->add_instance($course, (array)$data);
            }
            $step->set_mapping('enrol', $oldid, $instanceid);

            require_once("$CFG->dirroot/enrol/meta/locallib.php");
            enrol_meta_sync($data->customint1);

        } else {
            $step->set_mapping('enrol', $oldid, 0);
        }
    }

    
    public function restore_user_enrolment(restore_enrolments_structure_step $step, $data, $instance, $userid, $oldinstancestatus) {
        global $DB;

        if ($this->get_config('unenrolaction') != ENROL_EXT_REMOVED_SUSPENDNOROLES) {
                        return;
        }

                
        if (!$DB->record_exists('user_enrolments', array('enrolid' => $instance->id, 'userid' => $userid))) {
            $this->enrol_user($instance, $userid, null, $data->timestart, $data->timeend, ENROL_USER_SUSPENDED);
            if ($instance->customint2) {
                groups_add_member($instance->customint2, $userid, 'enrol_meta', $instance->id);
            }
        }
    }

    
    public function restore_group_member($instance, $groupid, $userid) {
                return;
    }

}
