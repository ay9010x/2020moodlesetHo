<?php



defined('MOODLE_INTERNAL') || die;

require_once($CFG->libdir.'/formslib.php');
require_once($CFG->dirroot.'/mod/lti/locallib.php');

class mod_lti_edit_types_form extends moodleform{
    public function definition() {
        global $CFG;

        $mform    =& $this->_form;

        $istool = $this->_customdata && $this->_customdata->istool;

                $mform->addElement('header', 'setup', get_string('tool_settings', 'lti'));

        $mform->addElement('text', 'lti_typename', get_string('typename', 'lti'));
        $mform->setType('lti_typename', PARAM_TEXT);
        $mform->addHelpButton('lti_typename', 'typename', 'lti');
        $mform->addRule('lti_typename', null, 'required', null, 'client');

        $mform->addElement('text', 'lti_toolurl', get_string('toolurl', 'lti'), array('size' => '64'));
        $mform->setType('lti_toolurl', PARAM_URL);
        $mform->addHelpButton('lti_toolurl', 'toolurl', 'lti');

        $mform->addElement('textarea', 'lti_description', get_string('tooldescription', 'lti'), array('rows' => 4, 'cols' => 60));
        $mform->setType('lti_description', PARAM_TEXT);
        $mform->addHelpButton('lti_description', 'tooldescription', 'lti');
        if (!$istool) {
            $mform->addRule('lti_toolurl', null, 'required', null, 'client');
        } else {
            $mform->disabledIf('lti_toolurl', null);
        }

        if (!$istool) {
            $mform->addElement('text', 'lti_resourcekey', get_string('resourcekey_admin', 'lti'));
            $mform->setType('lti_resourcekey', PARAM_TEXT);
            $mform->addHelpButton('lti_resourcekey', 'resourcekey_admin', 'lti');

            $mform->addElement('passwordunmask', 'lti_password', get_string('password_admin', 'lti'));
            $mform->setType('lti_password', PARAM_TEXT);
            $mform->addHelpButton('lti_password', 'password_admin', 'lti');
        }

        if ($istool) {
            $mform->addElement('textarea', 'lti_parameters', get_string('parameter', 'lti'), array('rows' => 4, 'cols' => 60));
            $mform->setType('lti_parameters', PARAM_TEXT);
            $mform->addHelpButton('lti_parameters', 'parameter', 'lti');
            $mform->disabledIf('lti_parameters', null);
        }

        $mform->addElement('textarea', 'lti_customparameters', get_string('custom', 'lti'), array('rows' => 4, 'cols' => 60));
        $mform->setType('lti_customparameters', PARAM_TEXT);
        $mform->addHelpButton('lti_customparameters', 'custom', 'lti');

        if (!empty($this->_customdata->isadmin)) {
            $options = array(
                LTI_COURSEVISIBLE_NO => get_string('show_in_course_no', 'lti'),
                LTI_COURSEVISIBLE_PRECONFIGURED => get_string('show_in_course_preconfigured', 'lti'),
                LTI_COURSEVISIBLE_ACTIVITYCHOOSER => get_string('show_in_course_activity_chooser', 'lti'),
            );
            if ($istool) {
                                unset($options[LTI_COURSEVISIBLE_NO]);
                $stringname = 'show_in_course_lti2';
            } else {
                $stringname = 'show_in_course_lti1';
            }
            $mform->addElement('select', 'lti_coursevisible', get_string($stringname, 'lti'), $options);
            $mform->addHelpButton('lti_coursevisible', $stringname, 'lti');
            $mform->setDefault('lti_coursevisible', '1');
        } else {
            $mform->addElement('hidden', 'lti_coursevisible', LTI_COURSEVISIBLE_PRECONFIGURED);
        }
        $mform->setType('lti_coursevisible', PARAM_INT);

        $mform->addElement('hidden', 'typeid');
        $mform->setType('typeid', PARAM_INT);

        $launchoptions = array();
        $launchoptions[LTI_LAUNCH_CONTAINER_EMBED] = get_string('embed', 'lti');
        $launchoptions[LTI_LAUNCH_CONTAINER_EMBED_NO_BLOCKS] = get_string('embed_no_blocks', 'lti');
        $launchoptions[LTI_LAUNCH_CONTAINER_REPLACE_MOODLE_WINDOW] = get_string('existing_window', 'lti');
        $launchoptions[LTI_LAUNCH_CONTAINER_WINDOW] = get_string('new_window', 'lti');

        $mform->addElement('select', 'lti_launchcontainer', get_string('default_launch_container', 'lti'), $launchoptions);
        $mform->setDefault('lti_launchcontainer', LTI_LAUNCH_CONTAINER_EMBED_NO_BLOCKS);
        $mform->addHelpButton('lti_launchcontainer', 'default_launch_container', 'lti');
        $mform->setType('lti_launchcontainer', PARAM_INT);

        $mform->addElement('hidden', 'oldicon');
        $mform->setType('oldicon', PARAM_URL);

        $mform->addElement('text', 'lti_icon', get_string('icon_url', 'lti'), array('size' => '64'));
        $mform->setType('lti_icon', PARAM_URL);
        $mform->setAdvanced('lti_icon');
        $mform->addHelpButton('lti_icon', 'icon_url', 'lti');

        $mform->addElement('text', 'lti_secureicon', get_string('secure_icon_url', 'lti'), array('size' => '64'));
        $mform->setType('lti_secureicon', PARAM_URL);
        $mform->setAdvanced('lti_secureicon');
        $mform->addHelpButton('lti_secureicon', 'secure_icon_url', 'lti');

        if (!$istool) {
                        $mform->addElement('header', 'privacy', get_string('privacy', 'lti'));

            $options = array();
            $options[0] = get_string('never', 'lti');
            $options[1] = get_string('always', 'lti');
            $options[2] = get_string('delegate', 'lti');

            $mform->addElement('select', 'lti_sendname', get_string('share_name_admin', 'lti'), $options);
            $mform->setType('lti_sendname', PARAM_INT);
            $mform->setDefault('lti_sendname', '2');
            $mform->addHelpButton('lti_sendname', 'share_name_admin', 'lti');

            $mform->addElement('select', 'lti_sendemailaddr', get_string('share_email_admin', 'lti'), $options);
            $mform->setType('lti_sendemailaddr', PARAM_INT);
            $mform->setDefault('lti_sendemailaddr', '2');
            $mform->addHelpButton('lti_sendemailaddr', 'share_email_admin', 'lti');

            
                        $mform->addElement('select', 'lti_acceptgrades', get_string('accept_grades_admin', 'lti'), $options);
            $mform->setType('lti_acceptgrades', PARAM_INT);
            $mform->setDefault('lti_acceptgrades', '2');
            $mform->addHelpButton('lti_acceptgrades', 'accept_grades_admin', 'lti');

            $mform->addElement('checkbox', 'lti_forcessl', '&nbsp;', ' ' . get_string('force_ssl', 'lti'), $options);
            $mform->setType('lti_forcessl', PARAM_BOOL);
            if (!empty($CFG->mod_lti_forcessl)) {
                $mform->setDefault('lti_forcessl', '1');
                $mform->freeze('lti_forcessl');
            } else {
                $mform->setDefault('lti_forcessl', '0');
            }
            $mform->addHelpButton('lti_forcessl', 'force_ssl', 'lti');

            if (!empty($this->_customdata->isadmin)) {
                                $mform->addElement('header', 'setupoptions', get_string('miscellaneous', 'lti'));

                                $idoptions = array();
                $idoptions[0] = get_string('id', 'lti');
                $idoptions[1] = get_string('courseid', 'lti');

                $mform->addElement('text', 'lti_organizationid', get_string('organizationid', 'lti'));
                $mform->setType('lti_organizationid', PARAM_TEXT);
                $mform->addHelpButton('lti_organizationid', 'organizationid', 'lti');

                $mform->addElement('text', 'lti_organizationurl', get_string('organizationurl', 'lti'));
                $mform->setType('lti_organizationurl', PARAM_URL);
                $mform->addHelpButton('lti_organizationurl', 'organizationurl', 'lti');
            }
        }

        

        

        $tab = optional_param('tab', '', PARAM_ALPHAEXT);
        $mform->addElement('hidden', 'tab', $tab);
        $mform->setType('tab', PARAM_ALPHAEXT);

        $courseid = optional_param('course', 1, PARAM_INT);
        $mform->addElement('hidden', 'course', $courseid);
        $mform->setType('course', PARAM_INT);

                $this->add_action_buttons();

    }
}
