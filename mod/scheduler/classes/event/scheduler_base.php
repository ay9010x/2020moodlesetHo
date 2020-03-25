<?php



namespace mod_scheduler\event;

defined('MOODLE_INTERNAL') || die();


abstract class scheduler_base extends \core\event\base {

    protected $scheduler;

    
    protected $legacylogdata;

    protected static function base_data(\scheduler_instance $scheduler) {
        return array(
            'context' => $scheduler->get_context(),
            'objectid' => $scheduler->id
        );
    }

    protected function set_scheduler(\scheduler_instance $scheduler) {
        $this->add_record_snapshot('scheduler', $scheduler->data);
        $this->scheduler = $scheduler;
        $this->data['objecttable'] = 'scheduler';
    }

    
    public function get_scheduler() {
        if ($this->is_restored()) {
            throw new \coding_exception('get_scheduler() is intended for event observers only');
        }
        if (!isset($this->scheduler)) {
            debugging('scheduler property should be initialised in each event', DEBUG_DEVELOPER);
            global $CFG;
            require_once($CFG->dirroot . '/mod/scheduler/locallib.php');
            $this->scheduler = \scheduler_instance::load_by_coursemodule_id($this->contextinstanceid);
        }
        return $this->scheduler;
    }


    
    public function get_url() {
        return new \moodle_url('/mod/scheduler/view.php', array('id' => $this->contextinstanceid));
    }

    
    protected function init() {
        $this->data['objecttable'] = 'scheduler';
    }

    
    protected function validate_data() {
        parent::validate_data();

        if ($this->contextlevel != CONTEXT_MODULE) {
            throw new \coding_exception('Context level must be CONTEXT_MODULE.');
        }
    }
}
