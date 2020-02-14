<?php



defined('MOODLE_INTERNAL') || die('Direct access to this script is forbidden.');


require_once($CFG->libdir.'/formslib.php');
require_once($CFG->dirroot . '/mod/assign/locallib.php');


class mod_assign_extension_form extends moodleform {
    
    private $instance;

    
    public function definition() {
        global $DB;

        $mform = $this->_form;
        $params = $this->_customdata;

                $instance = $params['instance'];
        $this->instance = $instance;

                $assign = $params['assign'];
        $userlist = $params['userlist'];
        $usercount = 0;
        $usershtml = '';

        $extrauserfields = get_extra_user_fields($assign->get_context());
        foreach ($userlist as $userid) {
            if ($usercount >= 5) {
                $usershtml .= get_string('moreusers', 'assign', count($userlist) - 5);
                break;
            }
            $user = $DB->get_record('user', array('id' => $userid), '*', MUST_EXIST);

            $usershtml .= $assign->get_renderer()->render(new assign_user_summary($user,
                                                                    $assign->get_course()->id,
                                                                    has_capability('moodle/site:viewfullnames',
                                                                    $assign->get_course_context()),
                                                                    $assign->is_blind_marking(),
                                                                    $assign->get_uniqueid_for_user($user->id),
                                                                    $extrauserfields,
                                                                    !$assign->is_active_user($userid)));
            $usercount += 1;
        }

        $userscount = count($userlist);

        $listusersmessage = get_string('grantextensionforusers', 'assign', $userscount);
        $mform->addElement('header', 'general', $listusersmessage);
        $mform->addElement('static', 'userslist', get_string('selectedusers', 'assign'), $usershtml);

        if ($instance->allowsubmissionsfromdate) {
            $mform->addElement('static', 'allowsubmissionsfromdate', get_string('allowsubmissionsfromdate', 'assign'),
                               userdate($instance->allowsubmissionsfromdate));
        }

        $finaldate = 0;
        if ($instance->duedate) {
            $mform->addElement('static', 'duedate', get_string('duedate', 'assign'), userdate($instance->duedate));
            $finaldate = $instance->duedate;
        }
        if ($instance->cutoffdate) {
            $mform->addElement('static', 'cutoffdate', get_string('cutoffdate', 'assign'), userdate($instance->cutoffdate));
            $finaldate = $instance->cutoffdate;
        }
        $mform->addElement('date_time_selector', 'extensionduedate',
                           get_string('extensionduedate', 'assign'), array('optional'=>true));
        $mform->setDefault('extensionduedate', $finaldate);

        $mform->addElement('hidden', 'id');
        $mform->setType('id', PARAM_INT);
        $mform->addElement('hidden', 'userid');
        $mform->setType('userid', PARAM_INT);
        $mform->addElement('hidden', 'selectedusers');
        $mform->setType('selectedusers', PARAM_SEQUENCE);
        $mform->addElement('hidden', 'action', 'saveextension');
        $mform->setType('action', PARAM_ALPHA);

        $this->add_action_buttons(true, get_string('savechanges', 'assign'));
    }

    
    public function validation($data, $files) {
        $errors = parent::validation($data, $files);
        if ($this->instance->duedate && $data['extensionduedate']) {
            if ($this->instance->duedate > $data['extensionduedate']) {
                $errors['extensionduedate'] = get_string('extensionnotafterduedate', 'assign');
            }
        }
        if ($this->instance->allowsubmissionsfromdate && $data['extensionduedate']) {
            if ($this->instance->allowsubmissionsfromdate > $data['extensionduedate']) {
                $errors['extensionduedate'] = get_string('extensionnotafterfromdate', 'assign');
            }
        }

        return $errors;
    }
}
