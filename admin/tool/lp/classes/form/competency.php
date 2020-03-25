<?php



namespace tool_lp\form;
defined('MOODLE_INTERNAL') || die();

use stdClass;


class competency extends persistent {

    
    protected static $persistentclass = 'core_competency\\competency';

    
    public function definition() {
        global $PAGE, $OUTPUT;

        $mform = $this->_form;
        $framework = $this->_customdata['competencyframework'];
        $parent = $this->_customdata['parent'];
        $pagecontextid = $this->_customdata['pagecontextid'];
        $competency = $this->get_persistent();

        $mform->addElement('hidden', 'competencyframeworkid');
        $mform->setType('competencyframeworkid', PARAM_INT);
        $mform->setConstant('competencyframeworkid', $framework->get_id());

        $mform->addElement('header', 'generalhdr', get_string('general'));

        $mform->addElement('static',
                           'frameworkdesc',
                           get_string('competencyframework', 'tool_lp'),
                           s($framework->get_shortname()));

        $mform->addElement('hidden', 'parentid', '', array('id' => 'tool_lp_parentcompetency'));

        $mform->setType('parentid', PARAM_INT);
        $mform->setConstant('parentid', ($parent) ? $parent->get_id() : 0);
        $parentlevel = ($parent) ? $parent->get_level() : 0;
        $parentname = ($parent) ? $parent->get_shortname() : get_string('competencyframeworkroot', 'tool_lp');
        $parentlabel = ($competency->get_id()) ?
            get_string('taxonomy_parent_' . $framework->get_taxonomy($parentlevel), 'tool_lp') :
            get_string('parentcompetency', 'tool_lp');
        $editaction = '';
        if (!$competency->get_id()) {
            $icon = $OUTPUT->pix_icon('t/editinline', get_string('parentcompetency_edit', 'tool_lp'));
            $editaction = $OUTPUT->action_link('#', $icon, null, array('id' => 'id_parentcompetencybutton'));
        }

        $mform->addElement('static',
                           'parentdesc',
                           $parentlabel,
                           "<span id='id_parentdesc'>$parentname</span>&nbsp;".$editaction);
                if (!$competency->get_id()) {
                        $PAGE->requires->js_call_amd('tool_lp/parentcompetency_form', 'init', array('#id_parentcompetencybutton',
                '#tool_lp_parentcompetency',
                '#id_parentdesc',
                $framework->get_id(),
                $pagecontextid));
        }

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

        $scales = array(null => get_string('inheritfromframework', 'tool_lp')) + get_scales_menu();
        $scaleid = $mform->addElement('select', 'scaleid', get_string('scale', 'tool_lp'), $scales);
        $mform->setType('scaleid', PARAM_INT);
        $mform->addHelpButton('scaleid', 'scale', 'tool_lp');

        $mform->addElement('hidden', 'scaleconfiguration', '', array('id' => 'tool_lp_scaleconfiguration'));
        $mform->setType('scaleconfiguration', PARAM_RAW);

        $mform->addElement('button', 'scaleconfigbutton', get_string('configurescale', 'tool_lp'));
        $PAGE->requires->js_call_amd('tool_lp/scaleconfig', 'init', array('#id_scaleid',
            '#tool_lp_scaleconfiguration', '#id_scaleconfigbutton'));

        if ($competency && $competency->has_user_competencies()) {
                                                $scaleid->updateAttributes(array('disabled' => 'disabled'));
            $mform->setConstant('scaleid', $competency->get_scaleid());
        }

                $mform->setDisableShortforms();
        $this->add_action_buttons(true, get_string('savechanges', 'tool_lp'));
    }

    
    protected static function convert_fields(stdClass $data) {
        $data = parent::convert_fields($data);
        if (empty($data->scaleid)) {
            $data->scaleid = null;
            $data->scaleconfiguration = null;
        }
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

}
