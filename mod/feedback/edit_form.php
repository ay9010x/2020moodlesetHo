<?php



if (!defined('MOODLE_INTERNAL')) {
    die('Direct access to this script is forbidden.');
}

require_once($CFG->libdir.'/formslib.php');

class feedback_edit_use_template_form extends moodleform {

    
    public function definition() {
        $mform =& $this->_form;

        $course = $this->_customdata['course'];

        $elementgroup = array();
                $mform->addElement('header', 'using_templates', get_string('using_templates', 'feedback'));
                $mform->addElement('hidden', 'id');
        $mform->setType('id', PARAM_INT);

                $templates_options = array();
        $owntemplates = feedback_get_template_list($course, 'own');
        $publictemplates = feedback_get_template_list($course, 'public');

        $options = array();
        if ($owntemplates or $publictemplates) {
            $options[''] = array('' => get_string('choosedots'));

            if ($owntemplates) {
                $courseoptions = array();
                foreach ($owntemplates as $template) {
                    $courseoptions[$template->id] = format_string($template->name);
                }
                $options[get_string('course')] = $courseoptions;
            }

            if ($publictemplates) {
                $publicoptions = array();
                foreach ($publictemplates as $template) {
                    $publicoptions[$template->id] = format_string($template->name);
                }
                $options[get_string('public', 'feedback')] = $publicoptions;
            }

            $attributes = 'onChange="M.core_formchangechecker.set_form_submitted(); this.form.submit()"';
            $elementgroup[] = $mform->createElement('selectgroups',
                                                     'templateid',
                                                     get_string('using_templates', 'feedback'),
                                                     $options,
                                                     $attributes);

            $elementgroup[] = $mform->createElement('submit',
                                                     'use_template',
                                                     get_string('use_this_template', 'feedback'),
                                                     array('class' => 'hiddenifjs'));

            $mform->addGroup($elementgroup, 'elementgroup', '', array(' '), false);
        } else {
            $mform->addElement('static', 'info', get_string('no_templates_available_yet', 'feedback'));
        }

        $this->set_data(array('id' => $this->_customdata['id']));
    }
}

class feedback_edit_create_template_form extends moodleform {

    
    public function definition() {
        $mform =& $this->_form;

                $mform->addElement('hidden', 'id');
        $mform->setType('id', PARAM_INT);
        $mform->addElement('hidden', 'do_show');
        $mform->setType('do_show', PARAM_ALPHANUMEXT);
        $mform->setConstant('do_show', 'templates');

                $mform->addElement('header', 'creating_templates', get_string('creating_templates', 'feedback'));

                $elementgroup = array();

        $elementgroup[] = $mform->createElement('text',
                                                 'templatename',
                                                 get_string('name', 'feedback'),
                                                 array('size'=>'40', 'maxlength'=>'200'));

        if (has_capability('mod/feedback:createpublictemplate', context_system::instance())) {
            $elementgroup[] = $mform->createElement('checkbox',
                                                     'ispublic',
                                                     get_string('public', 'feedback'),
                                                     get_string('public', 'feedback'));
        }

                $elementgroup[] = $mform->createElement('submit',
                                                 'create_template',
                                                 get_string('save_as_new_template', 'feedback'));

        $mform->addGroup($elementgroup,
                         'elementgroup',
                         get_string('name', 'feedback'),
                         array(' '),
                         false);

        $mform->setType('templatename', PARAM_TEXT);

        $this->set_data(array('id' => $this->_customdata['id']));
    }

    
    public function validation($data, $files) {
        $errors = parent::validation($data, $files);
        if (!isset($data['templatename']) || trim(strval($data['templatename'])) === '') {
            $errors['elementgroup'] = get_string('name_required', 'feedback');
        }
        return $errors;
    }
}

