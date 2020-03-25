<?php




namespace tool_lp\form;

use coding_exception;
use MoodleQuickForm_autocomplete;
use \core_competency\competency_framework;

global $CFG;
require_once($CFG->libdir . '/form/autocomplete.php');



class framework_autocomplete extends MoodleQuickForm_autocomplete {

    
    protected $onlyvisible = false;

    
    public function __construct($elementName = null, $elementLabel = null, $options = array()) {
        $contextid = null;
        if (!empty($options['contextid'])) {
            $contextid = $options['contextid'];
        } else if (!empty($options['context'])) {
            $contextid = $options['context']->id;
        }

        $this->onlyvisible = !empty($options['onlyvisible']);

        $validattributes = array(
            'ajax' => 'tool_lp/frameworks_datasource',
            'data-contextid' => $contextid,
            'data-onlyvisible' => $this->onlyvisible ? '1' : '0',
        );
        if (!empty($options['multiple'])) {
            $validattributes['multiple'] = 'multiple';
        }

        parent::__construct($elementName, $elementLabel, array(), $validattributes);
    }

    
    public function setValue($value) {
        global $DB;
        $values = (array) $value;
        $ids = array();

        foreach ($values as $onevalue) {
            if (!empty($onevalue) && (!$this->optionExists($onevalue)) &&
                    ($onevalue !== '_qf__force_multiselect_submission')) {
                array_push($ids, $onevalue);
            }
        }

        if (empty($ids)) {
            return $this->setSelected(array());
        }

                $toselect = array();
        list($insql, $inparams) = $DB->get_in_or_equal($ids, SQL_PARAMS_NAMED, 'param');
        $frameworks = competency_framework::get_records_select("id $insql", $inparams, 'shortname');
        foreach ($frameworks as $framework) {
            if (!has_any_capability(array('moodle/competency:competencyview', 'moodle/competency:competencymanage'),
                    $framework->get_context())) {
                continue;
            } else if ($this->onlyvisible && !$framework->get_visible()) {
                continue;
            }
            $this->addOption($framework->get_shortname() . ' ' . $framework->get_idnumber(), $framework->get_id());
            array_push($toselect, $framework->get_id());
        }

        return $this->setSelected($toselect);
    }
}
