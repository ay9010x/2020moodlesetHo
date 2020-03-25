<?php



defined('MOODLE_INTERNAL') || die();


abstract class testing_block_generator extends component_generator_base {
    
    protected $instancecount = 0;

    
    public function reset() {
        $this->instancecount = 0;
    }

    
    public function get_blockname() {
        $matches = null;
        if (!preg_match('/^block_([a-z0-9_]+)_generator$/', get_class($this), $matches)) {
            throw new coding_exception('Invalid block generator class name: '.get_class($this));
        }

        if (empty($matches[1])) {
            throw new coding_exception('Invalid block generator class name: '.get_class($this));
        }
        return $matches[1];
    }

    
    protected function prepare_record(stdClass $record) {
        $record->blockname = $this->get_blockname();
        if (!isset($record->parentcontextid)) {
            $record->parentcontextid = context_system::instance()->id;
        }
        if (!isset($record->showinsubcontexts)) {
            $record->showinsubcontexts = 0;
        }
        if (!isset($record->pagetypepattern)) {
            $record->pagetypepattern = '*';
        }
        if (!isset($record->subpagepattern)) {
            $record->subpagepattern = null;
        }
        if (!isset($record->defaultregion)) {
            $record->defaultregion = 'side-pre';
        }
        if (!isset($record->defaultweight)) {
            $record->defaultweight = 5;
        }
        if (!isset($record->configdata)) {
            $record->configdata = null;
        }
        return $record;
    }

    
    public function create_instance($record = null, $options = array()) {
        global $DB;

        $this->instancecount++;

        $record = (object)(array)$record;
        $this->preprocess_record($record, $options);
        $record = $this->prepare_record($record);

        $id = $DB->insert_record('block_instances', $record);
        context_block::instance($id);

        $instance = $DB->get_record('block_instances', array('id' => $id), '*', MUST_EXIST);
        return $instance;
    }

    
    protected function preprocess_record(stdClass $record, array $options) {
    }
}
