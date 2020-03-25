<?php



defined('MOODLE_INTERNAL') || die;

require_once($CFG->dirroot.'/course/moodleform_mod.php');
require_once($CFG->dirroot.'/mod/lti/locallib.php');

class mod_lti_mod_form extends moodleform_mod {

    public function definition() {
        global $DB, $PAGE, $OUTPUT, $USER, $COURSE;

        if ($type = optional_param('type', false, PARAM_ALPHA)) {
            component_callback("ltisource_$type", 'add_instance_hook');
        }

        $this->typeid = 0;

        $mform =& $this->_form;
                $mform->addElement('header', 'general', get_string('general', 'form'));
                $mform->addElement('text', 'name', get_string('basicltiname', 'lti'), array('size' => '64'));
        $mform->setType('name', PARAM_TEXT);
        $mform->addRule('name', null, 'required', null, 'client');
        $mform->addRule('name', get_string('maximumchars', '', 255), 'maxlength', 255, 'client');
                $this->standard_intro_elements(get_string('basicltiintro', 'lti'));
        $mform->setAdvanced('introeditor');

                if ($mform->elementExists('showdescription')) {
            $coursedesc = $mform->getElement('showdescription');
            if (!empty($coursedesc)) {
                $coursedesc->setText(' ' . $coursedesc->getLabel());
                $coursedesc->setLabel('&nbsp');
            }
        }

        $mform->setAdvanced('showdescription');

        $mform->addElement('checkbox', 'showtitlelaunch', '&nbsp;', ' ' . get_string('display_name', 'lti'));
        $mform->setAdvanced('showtitlelaunch');
        $mform->setDefault('showtitlelaunch', true);
        $mform->addHelpButton('showtitlelaunch', 'display_name', 'lti');

        $mform->addElement('checkbox', 'showdescriptionlaunch', '&nbsp;', ' ' . get_string('display_description', 'lti'));
        $mform->setAdvanced('showdescriptionlaunch');
        $mform->addHelpButton('showdescriptionlaunch', 'display_description', 'lti');

                $tooltypes = $mform->addElement('select', 'typeid', get_string('external_tool_type', 'lti'), array());
        $typeid = optional_param('typeid', false, PARAM_INT);
        $mform->getElement('typeid')->setValue($typeid);
        $mform->addHelpButton('typeid', 'external_tool_type', 'lti');
        $toolproxy = array();

        foreach (lti_get_types_for_add_instance() as $id => $type) {
            if (!empty($type->toolproxyid)) {
                $toolproxy[] = $type->id;
                $attributes = array( 'globalTool' => 1, 'toolproxy' => 1);
                $enabledcapabilities = explode("\n", $type->enabledcapability);
                if (!in_array('Result.autocreate', $enabledcapabilities)) {
                    $attributes['nogrades'] = 1;
                }
                if (!in_array('Person.name.full', $enabledcapabilities) && !in_array('Person.name.family', $enabledcapabilities) &&
                    !in_array('Person.name.given', $enabledcapabilities)) {
                    $attributes['noname'] = 1;
                }
                if (!in_array('Person.email.primary', $enabledcapabilities)) {
                    $attributes['noemail'] = 1;
                }
            } else if ($type->course == $COURSE->id) {
                $attributes = array( 'editable' => 1, 'courseTool' => 1, 'domain' => $type->tooldomain );
            } else if ($id != 0) {
                $attributes = array( 'globalTool' => 1, 'domain' => $type->tooldomain);
            } else {
                $attributes = array();
            }

            $tooltypes->addOption($type->name, $id, $attributes);
        }

        $mform->addElement('text', 'toolurl', get_string('launch_url', 'lti'), array('size' => '64'));
        $mform->setType('toolurl', PARAM_URL);
        $mform->addHelpButton('toolurl', 'launch_url', 'lti');
        $mform->disabledIf('toolurl', 'typeid', 'neq', '0');

        $mform->addElement('text', 'securetoolurl', get_string('secure_launch_url', 'lti'), array('size' => '64'));
        $mform->setType('securetoolurl', PARAM_URL);
        $mform->setAdvanced('securetoolurl');
        $mform->addHelpButton('securetoolurl', 'secure_launch_url', 'lti');
        $mform->disabledIf('securetoolurl', 'typeid', 'neq', '0');

        $mform->addElement('hidden', 'urlmatchedtypeid', '', array( 'id' => 'id_urlmatchedtypeid' ));
        $mform->setType('urlmatchedtypeid', PARAM_INT);

        $launchoptions = array();
        $launchoptions[LTI_LAUNCH_CONTAINER_DEFAULT] = get_string('default', 'lti');
        $launchoptions[LTI_LAUNCH_CONTAINER_EMBED] = get_string('embed', 'lti');
        $launchoptions[LTI_LAUNCH_CONTAINER_EMBED_NO_BLOCKS] = get_string('embed_no_blocks', 'lti');
        $launchoptions[LTI_LAUNCH_CONTAINER_REPLACE_MOODLE_WINDOW] = get_string('existing_window', 'lti');
        $launchoptions[LTI_LAUNCH_CONTAINER_WINDOW] = get_string('new_window', 'lti');

        $mform->addElement('select', 'launchcontainer', get_string('launchinpopup', 'lti'), $launchoptions);
        $mform->setDefault('launchcontainer', LTI_LAUNCH_CONTAINER_DEFAULT);
        $mform->addHelpButton('launchcontainer', 'launchinpopup', 'lti');
        $mform->setAdvanced('launchcontainer');

        $mform->addElement('text', 'resourcekey', get_string('resourcekey', 'lti'));
        $mform->setType('resourcekey', PARAM_TEXT);
        $mform->setAdvanced('resourcekey');
        $mform->addHelpButton('resourcekey', 'resourcekey', 'lti');
        $mform->disabledIf('resourcekey', 'typeid', 'neq', '0');

        $mform->addElement('passwordunmask', 'password', get_string('password', 'lti'));
        $mform->setType('password', PARAM_TEXT);
        $mform->setAdvanced('password');
        $mform->addHelpButton('password', 'password', 'lti');
        $mform->disabledIf('password', 'typeid', 'neq', '0');

        $mform->addElement('textarea', 'instructorcustomparameters', get_string('custom', 'lti'), array('rows' => 4, 'cols' => 60));
        $mform->setType('instructorcustomparameters', PARAM_TEXT);
        $mform->setAdvanced('instructorcustomparameters');
        $mform->addHelpButton('instructorcustomparameters', 'custom', 'lti');

        $mform->addElement('text', 'icon', get_string('icon_url', 'lti'), array('size' => '64'));
        $mform->setType('icon', PARAM_URL);
        $mform->setAdvanced('icon');
        $mform->addHelpButton('icon', 'icon_url', 'lti');
        $mform->disabledIf('icon', 'typeid', 'neq', '0');

        $mform->addElement('text', 'secureicon', get_string('secure_icon_url', 'lti'), array('size' => '64'));
        $mform->setType('secureicon', PARAM_URL);
        $mform->setAdvanced('secureicon');
        $mform->addHelpButton('secureicon', 'secure_icon_url', 'lti');
        $mform->disabledIf('secureicon', 'typeid', 'neq', '0');

                $mform->addElement('header', 'privacy', get_string('privacy', 'lti'));

        $mform->addElement('advcheckbox', 'instructorchoicesendname', '&nbsp;', ' ' . get_string('share_name', 'lti'));
        $mform->setDefault('instructorchoicesendname', '1');
        $mform->addHelpButton('instructorchoicesendname', 'share_name', 'lti');
        $mform->disabledIf('instructorchoicesendname', 'typeid', 'in', $toolproxy);

        $mform->addElement('advcheckbox', 'instructorchoicesendemailaddr', '&nbsp;', ' ' . get_string('share_email', 'lti'));
        $mform->setDefault('instructorchoicesendemailaddr', '1');
        $mform->addHelpButton('instructorchoicesendemailaddr', 'share_email', 'lti');
        $mform->disabledIf('instructorchoicesendemailaddr', 'typeid', 'in', $toolproxy);

        $mform->addElement('advcheckbox', 'instructorchoiceacceptgrades', '&nbsp;', ' ' . get_string('accept_grades', 'lti'));
        $mform->setDefault('instructorchoiceacceptgrades', '1');
        $mform->addHelpButton('instructorchoiceacceptgrades', 'accept_grades', 'lti');
        $mform->disabledIf('instructorchoiceacceptgrades', 'typeid', 'in', $toolproxy);

                $this->standard_grading_coursemodule_elements();

                $this->standard_coursemodule_elements();
        $mform->setAdvanced('cmidnumber');

                $this->add_action_buttons();

        $editurl = new moodle_url('/mod/lti/instructor_edit_tool_type.php',
                array('sesskey' => sesskey(), 'course' => $COURSE->id));
        $ajaxurl = new moodle_url('/mod/lti/ajax.php');

        $jsinfo = (object)array(
                        'edit_icon_url' => (string)$OUTPUT->pix_url('t/edit'),
                        'add_icon_url' => (string)$OUTPUT->pix_url('t/add'),
                        'delete_icon_url' => (string)$OUTPUT->pix_url('t/delete'),
                        'green_check_icon_url' => (string)$OUTPUT->pix_url('i/valid'),
                        'warning_icon_url' => (string)$OUTPUT->pix_url('warning', 'lti'),
                        'instructor_tool_type_edit_url' => $editurl->out(false),
                        'ajax_url' => $ajaxurl->out(true),
                        'courseId' => $COURSE->id
                  );

        $module = array(
            'name' => 'mod_lti_edit',
            'fullpath' => '/mod/lti/mod_form.js',
            'requires' => array('base', 'io', 'querystring-stringify-simple', 'node', 'event', 'json-parse'),
            'strings' => array(
                array('addtype', 'lti'),
                array('edittype', 'lti'),
                array('deletetype', 'lti'),
                array('delete_confirmation', 'lti'),
                array('cannot_edit', 'lti'),
                array('cannot_delete', 'lti'),
                array('global_tool_types', 'lti'),
                array('course_tool_types', 'lti'),
                array('using_tool_configuration', 'lti'),
                array('using_tool_cartridge', 'lti'),
                array('domain_mismatch', 'lti'),
                array('custom_config', 'lti'),
                array('tool_config_not_found', 'lti'),
                array('tooltypeadded', 'lti'),
                array('tooltypedeleted', 'lti'),
                array('tooltypenotdeleted', 'lti'),
                array('tooltypeupdated', 'lti'),
                array('forced_help', 'lti')
            ),
        );

        if (!empty($typeid)) {
            $mform->setAdvanced('typeid');
            $mform->setAdvanced('toolurl');
        }

        $PAGE->requires->js_init_call('M.mod_lti.editor.init', array(json_encode($jsinfo)), true, $module);
    }

}

