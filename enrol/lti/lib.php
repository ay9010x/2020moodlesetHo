<?php



defined('MOODLE_INTERNAL') || die();


class enrol_lti_plugin extends enrol_plugin {

    
    public function can_add_instance($courseid) {
        $context = context_course::instance($courseid, MUST_EXIST);
        return has_capability('moodle/course:enrolconfig', $context) && has_capability('enrol/lti:config', $context);
    }

    
    public function can_delete_instance($instance) {
        $context = context_course::instance($instance->courseid);
        return has_capability('enrol/lti:config', $context);
    }

    
    public function can_hide_show_instance($instance) {
        $context = context_course::instance($instance->courseid);
        return has_capability('enrol/lti:config', $context);
    }

    
    public function allow_unenrol(stdClass $instance) {
        return true;
    }

    
    public function use_standard_editing_ui() {
        return true;
    }

    
    public function add_instance($course, array $fields = null) {
        global $DB;

        $instanceid = parent::add_instance($course, $fields);

                $data = new stdClass();
        $data->enrolid = $instanceid;
        $data->timecreated = time();
        $data->timemodified = $data->timecreated;
        foreach ($fields as $field => $value) {
            $data->$field = $value;
        }

        $DB->insert_record('enrol_lti_tools', $data);

        return $instanceid;
    }

    
    public function update_instance($instance, $data) {
        global $DB;

        parent::update_instance($instance, $data);

                unset($data->id);
        unset($data->timecreated);
        unset($data->timemodified);

                $fields = (array) $data;

                $tool = new stdClass();
        $tool->id = $data->toolid;
        $tool->timemodified = time();
        foreach ($fields as $field => $value) {
            $tool->$field = $value;
        }

        return $DB->update_record('enrol_lti_tools', $tool);
    }

    
    public function delete_instance($instance) {
        global $DB;

                $tool = $DB->get_record('enrol_lti_tools', array('enrolid' => $instance->id), 'id', MUST_EXIST);

                $DB->delete_records('enrol_lti_users', array('toolid' => $tool->id));

                $DB->delete_records('enrol_lti_tools', array('id' => $tool->id));

                parent::delete_instance($instance);
    }

    
    public function unenrol_user(stdClass $instance, $userid) {
        global $DB;

                        if ($tool = $DB->get_record('enrol_lti_tools', array('enrolid' => $instance->id), 'id')) {
                        $DB->delete_records('enrol_lti_users', array('userid' => $userid, 'toolid' => $tool->id));
        }

        parent::unenrol_user($instance, $userid);
    }

    
    public function edit_instance_form($instance, MoodleQuickForm $mform, $context) {
        global $DB;

        $nameattribs = array('size' => '20', 'maxlength' => '255');
        $mform->addElement('text', 'name', get_string('custominstancename', 'enrol'), $nameattribs);
        $mform->setType('name', PARAM_TEXT);
        $mform->addRule('name', get_string('maximumchars', '', 255), 'maxlength', 255, 'server');

        $tools = array();
        $tools[$context->id] = get_string('course');
        $modinfo = get_fast_modinfo($instance->courseid);
        $mods = $modinfo->get_cms();
        foreach ($mods as $mod) {
            $tools[$mod->context->id] = format_string($mod->name);
        }

        $mform->addElement('select', 'contextid', get_string('tooltobeprovided', 'enrol_lti'), $tools);
        $mform->setDefault('contextid', $context->id);

        $mform->addElement('duration', 'enrolperiod', get_string('enrolperiod', 'enrol_lti'),
            array('optional' => true, 'defaultunit' => DAYSECS));
        $mform->setDefault('enrolperiod', 0);
        $mform->addHelpButton('enrolperiod', 'enrolperiod', 'enrol_lti');

        $mform->addElement('date_time_selector', 'enrolstartdate', get_string('enrolstartdate', 'enrol_lti'),
            array('optional' => true));
        $mform->setDefault('enrolstartdate', 0);
        $mform->addHelpButton('enrolstartdate', 'enrolstartdate', 'enrol_lti');

        $mform->addElement('date_time_selector', 'enrolenddate', get_string('enrolenddate', 'enrol_lti'),
            array('optional' => true));
        $mform->setDefault('enrolenddate', 0);
        $mform->addHelpButton('enrolenddate', 'enrolenddate', 'enrol_lti');

        $mform->addElement('text', 'maxenrolled', get_string('maxenrolled', 'enrol_lti'));
        $mform->setDefault('maxenrolled', 0);
        $mform->addHelpButton('maxenrolled', 'maxenrolled', 'enrol_lti');
        $mform->setType('maxenrolled', PARAM_INT);

        $assignableroles = get_assignable_roles($context);

        $mform->addElement('select', 'roleinstructor', get_string('roleinstructor', 'enrol_lti'), $assignableroles);
        $mform->setDefault('roleinstructor', '3');
        $mform->addHelpButton('roleinstructor', 'roleinstructor', 'enrol_lti');

        $mform->addElement('select', 'rolelearner', get_string('rolelearner', 'enrol_lti'), $assignableroles);
        $mform->setDefault('rolelearner', '5');
        $mform->addHelpButton('rolelearner', 'rolelearner', 'enrol_lti');

        $mform->addElement('header', 'remotesystem', get_string('remotesystem', 'enrol_lti'));

        $mform->addElement('text', 'secret', get_string('secret', 'enrol_lti'), 'maxlength="64" size="25"');
        $mform->setType('secret', PARAM_ALPHANUM);
        $mform->setDefault('secret', random_string(32));
        $mform->addHelpButton('secret', 'secret', 'enrol_lti');
        $mform->addRule('secret', get_string('required'), 'required');

        $mform->addElement('selectyesno', 'gradesync', get_string('gradesync', 'enrol_lti'));
        $mform->setDefault('gradesync', 1);
        $mform->addHelpButton('gradesync', 'gradesync', 'enrol_lti');

        $mform->addElement('selectyesno', 'gradesynccompletion', get_string('requirecompletion', 'enrol_lti'));
        $mform->setDefault('gradesynccompletion', 0);
        $mform->disabledIf('gradesynccompletion', 'gradesync', 0);

        $mform->addElement('selectyesno', 'membersync', get_string('membersync', 'enrol_lti'));
        $mform->setDefault('membersync', 1);
        $mform->addHelpButton('membersync', 'membersync', 'enrol_lti');

        $options = array();
        $options[\enrol_lti\helper::MEMBER_SYNC_ENROL_AND_UNENROL] = get_string('membersyncmodeenrolandunenrol', 'enrol_lti');
        $options[\enrol_lti\helper::MEMBER_SYNC_ENROL_NEW] = get_string('membersyncmodeenrolnew', 'enrol_lti');
        $options[\enrol_lti\helper::MEMBER_SYNC_UNENROL_MISSING] = get_string('membersyncmodeunenrolmissing', 'enrol_lti');
        $mform->addElement('select', 'membersyncmode', get_string('membersyncmode', 'enrol_lti'), $options);
        $mform->setDefault('membersyncmode', \enrol_lti\helper::MEMBER_SYNC_ENROL_AND_UNENROL);
        $mform->addHelpButton('membersyncmode', 'membersyncmode', 'enrol_lti');
        $mform->disabledIf('membersyncmode', 'membersync', 0);

        $mform->addElement('header', 'defaultheader', get_string('userdefaultvalues', 'enrol_lti'));

        $emaildisplay = get_config('enrol_lti', 'emaildisplay');
        $choices = array(
            0 => get_string('emaildisplayno'),
            1 => get_string('emaildisplayyes'),
            2 => get_string('emaildisplaycourse')
        );
        $mform->addElement('select', 'maildisplay', get_string('emaildisplay'), $choices);
        $mform->setDefault('maildisplay', $emaildisplay);

        $city = get_config('enrol_lti', 'city');
        $mform->addElement('text', 'city', get_string('city'), 'maxlength="100" size="25"');
        $mform->setType('city', PARAM_TEXT);
        $mform->setDefault('city', $city);

        $country = get_config('enrol_lti', 'country');
        $countries = array('' => get_string('selectacountry') . '...') + get_string_manager()->get_list_of_countries();
        $mform->addElement('select', 'country', get_string('selectacountry'), $countries);
        $mform->setDefault('country', $country);
        $mform->setAdvanced('country');

        $timezone = get_config('enrol_lti', 'timezone');
        $choices = core_date::get_list_of_timezones(null, true);
        $mform->addElement('select', 'timezone', get_string('timezone'), $choices);
        $mform->setDefault('timezone', $timezone);
        $mform->setAdvanced('timezone');

        $lang = get_config('enrol_lti', 'lang');
        $mform->addElement('select', 'lang', get_string('preferredlanguage'), get_string_manager()->get_list_of_translations());
        $mform->setDefault('lang', $lang);
        $mform->setAdvanced('lang');

        $institution = get_config('enrol_lti', 'institution');
        $mform->addElement('text', 'institution', get_string('institution'), 'maxlength="40" size="25"');
        $mform->setType('institution', core_user::get_property_type('institution'));
        $mform->setDefault('institution', $institution);
        $mform->setAdvanced('institution');

                if (!empty($instance->id)) {
                        $ltitool = $DB->get_record('enrol_lti_tools', array('enrolid' => $instance->id), '*', MUST_EXIST);

            $mform->addElement('hidden', 'toolid');
            $mform->setType('toolid', PARAM_INT);
            $mform->setConstant('toolid', $ltitool->id);

            $mform->setDefaults((array) $ltitool);
        }
    }

    
    public function edit_instance_validation($data, $files, $instance, $context) {
        global $COURSE, $DB;

        $errors = array();

        if (!empty($data['enrolenddate']) && $data['enrolenddate'] < $data['enrolstartdate']) {
            $errors['enrolenddate'] = get_string('enrolenddateerror', 'enrol_lti');
        }

        if (!empty($data['requirecompletion'])) {
            $completion = new completion_info($COURSE);
            $moodlecontext = $DB->get_record('context', array('id' => $data['contextid']));
            if ($moodlecontext->contextlevel == CONTEXT_MODULE) {
                $cm = get_coursemodule_from_id(false, $moodlecontext->instanceid, 0, false, MUST_EXIST);
            } else {
                $cm = null;
            }

            if (!$completion->is_enabled($cm)) {
                $errors['requirecompletion'] = get_string('errorcompletionenabled', 'enrol_lti');
            }
        }

        return $errors;
    }

    
    public function get_user_enrolment_actions(course_enrolment_manager $manager, $ue) {
        $actions = array();
        $context = $manager->get_context();
        $instance = $ue->enrolmentinstance;
        $params = $manager->get_moodlepage()->url->params();
        $params['ue'] = $ue->id;
        if ($this->allow_unenrol_user($instance, $ue) && has_capability("enrol/lti:unenrol", $context)) {
            $url = new moodle_url('/enrol/unenroluser.php', $params);
            $actions[] = new user_enrolment_action(new pix_icon('t/delete', ''), get_string('unenrol', 'enrol'), $url,
                array('class' => 'unenrollink', 'rel' => $ue->id));
        }
        if ($this->allow_manage($instance) && has_capability("enrol/lti:manage", $context)) {
            $url = new moodle_url('/enrol/editenrolment.php', $params);
            $actions[] = new user_enrolment_action(new pix_icon('t/edit', ''), get_string('edit'), $url,
                array('class' => 'editenrollink', 'rel' => $ue->id));
        }
        return $actions;
    }

    
    public function restore_instance(restore_enrolments_structure_step $step, stdClass $data, $course, $oldid) {
                        $instanceid = parent::add_instance($course, (array)$data);
        $step->set_mapping('enrol', $oldid, $instanceid);
    }
}


function enrol_lti_extend_navigation_course($navigation, $course, $context) {
        if (enrol_is_enabled('lti')) {
                $ltiplugin = enrol_get_plugin('lti');
        if ($ltiplugin->can_add_instance($course->id)) {
            $url = new moodle_url('/enrol/lti/index.php', array('courseid' => $course->id));
            $settingsnode = navigation_node::create(get_string('sharedexternaltools', 'enrol_lti'), $url,
                navigation_node::TYPE_SETTING, null, null, new pix_icon('i/settings', ''));

            $navigation->add_node($settingsnode);
        }
    }
}
