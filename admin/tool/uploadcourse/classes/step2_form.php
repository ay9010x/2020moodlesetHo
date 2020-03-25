<?php



defined('MOODLE_INTERNAL') || die();


class tool_uploadcourse_step2_form extends tool_uploadcourse_base_form {

    
    public function definition () {
        global $CFG;

        $mform   = $this->_form;
        $data    = $this->_customdata['data'];
        $courseconfig = get_config('moodlecourse');

                $this->add_import_options();

                $mform->addElement('header', 'courseoptionshdr', get_string('courseprocess', 'tool_uploadcourse'));
        $mform->setExpanded('courseoptionshdr', true);

        $mform->addElement('text', 'options[shortnametemplate]', get_string('shortnametemplate', 'tool_uploadcourse'),
            'maxlength="100" size="20"');
        $mform->setType('options[shortnametemplate]', PARAM_RAW);
        $mform->addHelpButton('options[shortnametemplate]', 'shortnametemplate', 'tool_uploadcourse');
        $mform->disabledIf('options[shortnametemplate]', 'options[mode]', 'eq', tool_uploadcourse_processor::MODE_CREATE_OR_UPDATE);
        $mform->disabledIf('options[shortnametemplate]', 'options[mode]', 'eq', tool_uploadcourse_processor::MODE_UPDATE_ONLY);

                $contextid = $this->_customdata['contextid'];
        $mform->addElement('hidden', 'contextid', $contextid);
        $mform->setType('contextid', PARAM_INT);
        $mform->addElement('filepicker', 'restorefile', get_string('templatefile', 'tool_uploadcourse'));
        $mform->addHelpButton('restorefile', 'templatefile', 'tool_uploadcourse');

        $mform->addElement('text', 'options[templatecourse]', get_string('coursetemplatename', 'tool_uploadcourse'));
        $mform->setType('options[templatecourse]', PARAM_TEXT);
        $mform->addHelpButton('options[templatecourse]', 'coursetemplatename', 'tool_uploadcourse');

        $mform->addElement('selectyesno', 'options[reset]', get_string('reset', 'tool_uploadcourse'));
        $mform->setDefault('options[reset]', 0);
        $mform->disabledIf('options[reset]', 'options[mode]', 'eq', tool_uploadcourse_processor::MODE_CREATE_NEW);
        $mform->disabledIf('options[reset]', 'options[mode]', 'eq', tool_uploadcourse_processor::MODE_CREATE_ALL);
        $mform->disabledIf('options[reset]', 'options[allowresets]', 'eq', 0);
        $mform->addHelpButton('options[reset]', 'reset', 'tool_uploadcourse');

                $mform->addElement('header', 'defaultheader', get_string('defaultvalues', 'tool_uploadcourse'));
        $mform->setExpanded('defaultheader', true);

        $displaylist = coursecat::make_categories_list('moodle/course:create');
        $mform->addElement('select', 'defaults[category]', get_string('coursecategory'), $displaylist);
        $mform->addHelpButton('defaults[category]', 'coursecategory');

        $choices = array();
        $choices['0'] = get_string('hide');
        $choices['1'] = get_string('show');
        $mform->addElement('select', 'defaults[visible]', get_string('visible'), $choices);
        $mform->addHelpButton('defaults[visible]', 'visible');
        $mform->setDefault('defaults[visible]', $courseconfig->visible);

        $mform->addElement('date_selector', 'defaults[startdate]', get_string('startdate'));
        $mform->addHelpButton('defaults[startdate]', 'startdate');
        $mform->setDefault('defaults[startdate]', time() + 3600 * 24);

        $courseformats = get_sorted_course_formats(true);
        $formcourseformats = array();
        foreach ($courseformats as $courseformat) {
            $formcourseformats[$courseformat] = get_string('pluginname', "format_$courseformat");
        }
        $mform->addElement('select', 'defaults[format]', get_string('format'), $formcourseformats);
        $mform->addHelpButton('defaults[format]', 'format');
        $mform->setDefault('defaults[format]', $courseconfig->format);

        if (!empty($CFG->allowcoursethemes)) {
            $themeobjects = get_list_of_themes();
            $themes=array();
            $themes[''] = get_string('forceno');
            foreach ($themeobjects as $key => $theme) {
                if (empty($theme->hidefromselector)) {
                    $themes[$key] = get_string('pluginname', 'theme_'.$theme->name);
                }
            }
            $mform->addElement('select', 'defaults[theme]', get_string('forcetheme'), $themes);
        }

        $languages = array();
        $languages[''] = get_string('forceno');
        $languages += get_string_manager()->get_list_of_translations();
        $mform->addElement('select', 'defaults[lang]', get_string('forcelanguage'), $languages);
        $mform->setDefault('defaults[lang]', $courseconfig->lang);

        $options = range(0, 10);
        $mform->addElement('select', 'defaults[newsitems]', get_string('newsitemsnumber'), $options);
        $mform->addHelpButton('defaults[newsitems]', 'newsitemsnumber');
        $mform->setDefault('defaults[newsitems]', $courseconfig->newsitems);

        $mform->addElement('selectyesno', 'defaults[showgrades]', get_string('showgrades'));
        $mform->addHelpButton('defaults[showgrades]', 'showgrades');
        $mform->setDefault('defaults[showgrades]', $courseconfig->showgrades);

        $mform->addElement('selectyesno', 'defaults[showreports]', get_string('showreports'));
        $mform->addHelpButton('defaults[showreports]', 'showreports');
        $mform->setDefault('defaults[showreports]', $courseconfig->showreports);

        if (!empty($CFG->legacyfilesinnewcourses)) {
            $mform->addElement('select', 'defaults[legacyfiles]', get_string('courselegacyfiles'), $choices);
            $mform->addHelpButton('defaults[legacyfiles]', 'courselegacyfiles');
            if (!isset($courseconfig->legacyfiles)) {
                $courseconfig->legacyfiles = 0;
            }
            $mform->setDefault('defaults[legacyfiles]', $courseconfig->legacyfiles);
        }

        $choices = get_max_upload_sizes($CFG->maxbytes);
        $mform->addElement('select', 'defaults[maxbytes]', get_string('maximumupload'), $choices);
        $mform->addHelpButton('defaults[maxbytes]', 'maximumupload');
        $mform->setDefault('defaults[maxbytes]', $courseconfig->maxbytes);

        $choices = array();
        $choices[NOGROUPS] = get_string('groupsnone', 'group');
        $choices[SEPARATEGROUPS] = get_string('groupsseparate', 'group');
        $choices[VISIBLEGROUPS] = get_string('groupsvisible', 'group');
        $mform->addElement('select', 'defaults[groupmode]', get_string('groupmode', 'group'), $choices);
        $mform->addHelpButton('defaults[groupmode]', 'groupmode', 'group');
        $mform->setDefault('defaults[groupmode]', $courseconfig->groupmode);

        $mform->addElement('selectyesno', 'defaults[groupmodeforce]', get_string('groupmodeforce', 'group'));
        $mform->addHelpButton('defaults[groupmodeforce]', 'groupmodeforce', 'group');
        $mform->setDefault('defaults[groupmodeforce]', $courseconfig->groupmodeforce);

                $mform->addElement('hidden', 'importid');
        $mform->setType('importid', PARAM_INT);

        $mform->addElement('hidden', 'previewrows');
        $mform->setType('previewrows', PARAM_INT);

        $this->add_action_buttons(true, get_string('uploadcourses', 'tool_uploadcourse'));

        $this->set_data($data);
    }

    
    public function add_action_buttons($cancel = true, $submitlabel = null) {
        $mform =& $this->_form;
        $buttonarray = array();
        $buttonarray[] = &$mform->createElement('submit', 'showpreview', get_string('preview', 'tool_uploadcourse'));
        $buttonarray[] = &$mform->createElement('submit', 'submitbutton', $submitlabel);
        $buttonarray[] = &$mform->createElement('cancel');
        $mform->addGroup($buttonarray, 'buttonar', '', array(' '), false);
        $mform->closeHeaderBefore('buttonar');
    }

}
