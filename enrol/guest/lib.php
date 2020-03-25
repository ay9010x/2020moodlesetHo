<?php



defined('MOODLE_INTERNAL') || die();


class enrol_guest_plugin extends enrol_plugin {

    
    public function get_info_icons(array $instances) {
        foreach ($instances as $instance) {
            if ($instance->password !== '') {
                return array(new pix_icon('withpassword', get_string('guestaccess_withpassword', 'enrol_guest'), 'enrol_guest'));
            } else {
                return array(new pix_icon('withoutpassword', get_string('guestaccess_withoutpassword', 'enrol_guest'), 'enrol_guest'));
            }
        }
    }

    
    public function enrol_user(stdClass $instance, $userid, $roleid = null, $timestart = 0, $timeend = 0, $status = null, $recovergrades = null) {
                return;
    }

    
    public function unenrol_user(stdClass $instance, $userid) {
                return;
    }

    
    public function try_guestaccess(stdClass $instance) {
        global $USER, $CFG;

        $allow = false;

        if ($instance->password === '') {
            $allow = true;

        } else if (isset($USER->enrol_guest_passwords[$instance->id])) {             if ($USER->enrol_guest_passwords[$instance->id] === $instance->password) {
                $allow = true;
            }
        }

        if ($allow) {
                        $context = context_course::instance($instance->courseid);
            load_temp_course_role($context, $CFG->guestroleid);
            return ENROL_MAX_TIMESTAMP;
        }

        return false;
    }

    
    public function can_add_instance($courseid) {
        global $DB;

        $context = context_course::instance($courseid, MUST_EXIST);

        if (!has_capability('moodle/course:enrolconfig', $context) or !has_capability('enrol/guest:config', $context)) {
            return false;
        }

        if ($DB->record_exists('enrol', array('courseid'=>$courseid, 'enrol'=>'guest'))) {
            return false;
        }

        return true;
    }

    
    public function enrol_page_hook(stdClass $instance) {
        global $CFG, $OUTPUT, $SESSION, $USER;

        if ($instance->password === '') {
            return null;
        }

        if (isset($USER->enrol['tempguest'][$instance->courseid]) and $USER->enrol['tempguest'][$instance->courseid] > time()) {
                        return null;
        }

        require_once("$CFG->dirroot/enrol/guest/locallib.php");
        $form = new enrol_guest_enrol_form(NULL, $instance);
        $instanceid = optional_param('instance', 0, PARAM_INT);

        if ($instance->id == $instanceid) {
            if ($data = $form->get_data()) {
                                $context = context_course::instance($instance->courseid);
                $USER->enrol_guest_passwords[$instance->id] = $data->guestpassword;                 if (isset($USER->enrol['tempguest'][$instance->courseid])) {
                    remove_temp_course_roles($context);
                }
                load_temp_course_role($context, $CFG->guestroleid);
                $USER->enrol['tempguest'][$instance->courseid] = ENROL_MAX_TIMESTAMP;

                                if (!empty($SESSION->wantsurl)) {
                    $destination = $SESSION->wantsurl;
                    unset($SESSION->wantsurl);
                } else {
                    $destination = "$CFG->wwwroot/course/view.php?id=$instance->courseid";
                }
                redirect($destination);
            }
        }

        ob_start();
        $form->display();
        $output = ob_get_clean();

        return $OUTPUT->box($output, 'generalbox');
    }

    
    public function course_updated($inserted, $course, $data) {
        global $DB;

        if ($inserted) {
            if (isset($data->enrol_guest_status_0)) {
                $fields = array('status'=>$data->enrol_guest_status_0);
                if ($fields['status'] == ENROL_INSTANCE_ENABLED) {
                    $fields['password'] = $data->enrol_guest_password_0;
                } else {
                    if ($this->get_config('requirepassword')) {
                        $fields['password'] = generate_password(20);
                    }
                }
                $this->add_instance($course, $fields);
            } else {
                if ($this->get_config('defaultenrol')) {
                    $this->add_default_instance($course);
                }
            }

        } else {
            $instances = $DB->get_records('enrol', array('courseid'=>$course->id, 'enrol'=>'guest'));
            foreach ($instances as $instance) {
                $i = $instance->id;

                if (isset($data->{'enrol_guest_status_'.$i})) {
                    $reset = ($instance->status != $data->{'enrol_guest_status_'.$i});

                    $instance->status       = $data->{'enrol_guest_status_'.$i};
                    $instance->timemodified = time();
                    if ($instance->status == ENROL_INSTANCE_ENABLED) {
                        if ($instance->password !== $data->{'enrol_guest_password_'.$i}) {
                            $reset = true;
                        }
                        $instance->password = $data->{'enrol_guest_password_'.$i};
                    }
                    $DB->update_record('enrol', $instance);
                    \core\event\enrol_instance_updated::create_from_record($instance)->trigger();

                    if ($reset) {
                        $context = context_course::instance($course->id);
                        $context->mark_dirty();
                    }
                }
            }
        }
    }

    
    public function add_instance($course, array $fields = NULL) {
        $fields = (array)$fields;

        if (!isset($fields['password'])) {
            $fields['password'] = '';
        }

        return parent::add_instance($course, $fields);
    }

    
    public function add_default_instance($course) {
        $fields = array('status'=>$this->get_config('status'));

        if ($this->get_config('requirepassword')) {
            $fields['password'] = generate_password(20);
        }

        return $this->add_instance($course, $fields);
    }

    
    public function restore_instance(restore_enrolments_structure_step $step, stdClass $data, $course, $oldid) {
        global $DB;

        if (!$DB->record_exists('enrol', array('courseid' => $data->courseid, 'enrol' => $this->get_name()))) {
            $this->add_instance($course, (array)$data);
        }

                $step->set_mapping('enrol', $oldid, 0);
    }

    
    public function can_delete_instance($instance) {
        $context = context_course::instance($instance->courseid);
        return has_capability('enrol/guest:config', $context);
    }

    
    public function can_hide_show_instance($instance) {
        $context = context_course::instance($instance->courseid);
        if (!has_capability('enrol/guest:config', $context)) {
            return false;
        }

                        if ($instance->status == ENROL_INSTANCE_DISABLED) {
            if ($this->get_config('requirepassword')) {
                if (empty($instance->password)) {
                    return false;
                }
            }

                        if (!empty($instance->password) && $this->get_config('usepasswordpolicy')) {
                if (!check_password_policy($instance->password, $errmsg)) {
                    return false;
                }
            }
        }

        return true;
    }

    
    public function get_instance_defaults() {
        $fields = array();
        $fields['status']          = $this->get_config('status');
        return $fields;
    }

    
    public function get_enrol_info(stdClass $instance) {

        $instanceinfo = new stdClass();
        $instanceinfo->id = $instance->id;
        $instanceinfo->courseid = $instance->courseid;
        $instanceinfo->type = $this->get_name();
        $instanceinfo->name = $this->get_instance_name($instance);
        $instanceinfo->status = $instance->status == ENROL_INSTANCE_ENABLED;

                $instanceinfo->requiredparam = new stdClass();
        $instanceinfo->requiredparam->passwordrequired = !empty($instance->password);

                if ($instanceinfo->status) {
            $instanceinfo->wsfunction = 'enrol_guest_get_instance_info';
        }
        return $instanceinfo;
    }

    
    protected function get_status_options() {
        $options = array(ENROL_INSTANCE_ENABLED  => get_string('yes'),
                         ENROL_INSTANCE_DISABLED => get_string('no'));
        return $options;
    }

    
    public function edit_instance_form($instance, MoodleQuickForm $mform, $context) {
        global $CFG;

        $options = $this->get_status_options();
        $mform->addElement('select', 'status', get_string('status', 'enrol_guest'), $options);
        $mform->addHelpButton('status', 'status', 'enrol_guest');
        $mform->setDefault('status', $this->get_config('status'));
        $mform->setAdvanced('status', $this->get_config('status_adv'));

        $mform->addElement('passwordunmask', 'password', get_string('password', 'enrol_guest'));
        $mform->addHelpButton('password', 'password', 'enrol_guest');

                                        if (empty($instance->id) && $this->get_config('requirepassword')) {
            $mform->addRule('password', get_string('required'), 'required', null);
        }
    }

    
    public function use_standard_editing_ui() {
        return true;
    }

    
    public function edit_instance_validation($data, $files, $instance, $context) {
        $errors = array();

        $checkpassword = false;

        if ($data['id']) {
                        if (($instance->status == ENROL_INSTANCE_DISABLED) && ($data['status'] == ENROL_INSTANCE_ENABLED)) {
                $checkpassword = true;
            }

                        if (($data['status'] == ENROL_INSTANCE_ENABLED) && ($instance->password !== $data['password'])) {
                $checkpassword = true;
            }
        } else {
            $checkpassword = true;
        }

        if ($checkpassword) {
            $require = $this->get_config('requirepassword');
            $policy  = $this->get_config('usepasswordpolicy');
            if ($require && trim($data['password']) === '') {
                $errors['password'] = get_string('required');
            } else if (!empty($data['password']) && $policy) {
                $errmsg = '';
                if (!check_password_policy($data['password'], $errmsg)) {
                    $errors['password'] = $errmsg;
                }
            }
        }

        $validstatus = array_keys($this->get_status_options());
        $tovalidate = array(
            'status' => $validstatus
        );
        $typeerrors = $this->validate_param_types($data, $tovalidate);
        $errors = array_merge($errors, $typeerrors);

        return $errors;
    }


}
