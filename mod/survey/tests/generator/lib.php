<?php



defined('MOODLE_INTERNAL') || die();


class mod_survey_generator extends testing_module_generator {

    
    private $templates = null;

    public function reset() {
        $this->templates = null;
        parent::reset();
    }

    public function create_instance($record = null, array $options = null) {
        global $DB;

        if ($this->templates === null) {
            $this->templates = $DB->get_records_menu('survey', array('template' => 0), 'name', 'id, name');
        }
        if (empty($this->templates)) {
            throw new moodle_exception('cannotfindsurveytmpt', 'survey');
        }
        $record = (array)$record;
        if (isset($record['template']) && !is_number($record['template'])) {
                        $record['template'] = array_search($record['template'], $this->templates);
        }
        if (isset($record['template']) && !array_key_exists($record['template'], $this->templates)) {
            throw new moodle_exception('cannotfindsurveytmpt', 'survey');
        }

                if (!isset($record['template'])) {
            reset($this->templates);
            $record['template'] = key($this->templates);
        }

        return parent::create_instance($record, (array)$options);
    }
}
