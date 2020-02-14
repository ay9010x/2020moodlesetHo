<?php



namespace tool_lp\form;
defined('MOODLE_INTERNAL') || die();

use stdClass;


class competency_framework extends persistent {

    protected static $persistentclass = 'core_competency\\competency_framework';

    
    public function definition() {
        global $PAGE;

        $mform = $this->_form;
        $context = $this->_customdata['context'];
        $framework = $this->get_persistent();

        $mform->addElement('hidden', 'contextid');
        $mform->setType('contextid', PARAM_INT);
        $mform->setConstant('contextid', $context->id);

        $mform->addElement('header', 'generalhdr', get_string('general'));

                $mform->addElement('text', 'shortname', get_string('shortname', 'tool_lp'), 'maxlength="100"');
        $mform->setType('shortname', PARAM_TEXT);
        $mform->addRule('shortname', null, 'required', null, 'client');
        $mform->addRule('shortname', get_string('maximumchars', '', 100), 'maxlength', 100, 'client');
                $mform->addElement('editor', 'description',
                           get_string('description', 'tool_lp'), array('rows' => 4));
        $mform->setType('description', PARAM_RAW);
                $mform->addElement('text', 'idnumber', get_string('idnumber', 'tool_lp'), 'maxlength="100"');
        $mform->setType('idnumber', PARAM_RAW);
        $mform->addRule('idnumber', null, 'required', null, 'client');
        $mform->addRule('idnumber', get_string('maximumchars', '', 100), 'maxlength', 100, 'client');

        $scales = get_scales_menu();
        $scaleid = $mform->addElement('select', 'scaleid', get_string('scale', 'tool_lp'), $scales);
        $mform->setType('scaleid', PARAM_INT);
        $mform->addHelpButton('scaleid', 'scale', 'tool_lp');
        $mform->addRule('scaleid', null, 'required', null, 'client');
        if ($framework && $framework->has_user_competencies()) {
                                                $scaleid->updateAttributes(array('readonly' => 'readonly'));
            $mform->setConstant('scaleid', $framework->get_scaleid());
        }

        $mform->addElement('button', 'scaleconfigbutton', get_string('configurescale', 'tool_lp'));
                $mform->addElement('hidden', 'scaleconfiguration', '', array('id' => 'tool_lp_scaleconfiguration'));
        $mform->setType('scaleconfiguration', PARAM_RAW);
        $PAGE->requires->js_call_amd('tool_lp/scaleconfig', 'init', array('#id_scaleid',
            '#tool_lp_scaleconfiguration', '#id_scaleconfigbutton'));

        $mform->addElement('selectyesno', 'visible',
                           get_string('visible', 'tool_lp'));
        $mform->setDefault('visible', true);
        $mform->addHelpButton('visible', 'visible', 'tool_lp');

        $mform->addElement('static', 'context', get_string('category', 'tool_lp'));
        $mform->setDefault('context', $context->get_context_name(false));

        $mform->addElement('header', 'taxonomyhdr', get_string('taxonomies', 'tool_lp'));
        $taxonomies = \core_competency\competency_framework::get_taxonomies_list();
        $taxdefaults = array();
        $taxcount = max($framework ? $framework->get_depth() : 4, 4);
        for ($i = 1; $i <= $taxcount; $i++) {
            $mform->addElement('select', "taxonomies[$i]", get_string('levela', 'tool_lp', $i), $taxonomies);
            $taxdefaults[$i] = \core_competency\competency_framework::TAXONOMY_COMPETENCY;
        }
                $mform->setDefault('taxonomies', $taxdefaults);

        $this->add_action_buttons(true, get_string('savechanges', 'tool_lp'));
    }

    
    protected static function convert_fields(stdClass $data) {
        $data = parent::convert_fields($data);
        $data->taxonomies = implode(',', $data->taxonomies);
        return $data;
    }

    
    protected function extra_validation($data, $files, array &$errors) {
        $newerrors = array();
                if (isset($errors['scaleconfiguration']) && !isset($errors['scaleid'])) {
            $newerrors['scaleid'] = $errors['scaleconfiguration'];
            unset($errors['scaleconfiguration']);
        }
        return $newerrors;
    }

    
    protected function get_default_data() {
        $data = parent::get_default_data();
        $data->taxonomies = $this->get_persistent()->get_taxonomies();
        return $data;
    }

}

