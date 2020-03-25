<?php



namespace mod_scheduler\event;

defined('MOODLE_INTERNAL') || die();


abstract class slot_base extends \core\event\base {

    protected $slot;

    protected static function base_data(\scheduler_slot $slot) {
        return array(
            'context' => $slot->get_scheduler()->get_context(),
            'objectid' => $slot->id,
            'relateduserid' => $slot->teacherid
        );
    }

    protected function set_slot(\scheduler_slot $slot) {
        $this->add_record_snapshot('scheduler_slots', $slot->data);
        $this->add_record_snapshot('scheduler', $slot->get_scheduler()->data);
        $this->slot = $slot;
        $this->data['objecttable'] = 'scheduler_slots';
    }

    
    public function get_slot() {
        if ($this->is_restored()) {
            throw new \coding_exception('get_slot() is intended for event observers only');
        }
        return $this->slot;
    }

    
    public function get_url() {
        return new \moodle_url('/mod/scheduler/view.php', array('id' => $this->contextinstanceid));
    }

    
    protected function validate_data() {
        parent::validate_data();

        if ($this->contextlevel != CONTEXT_MODULE) {
            throw new \coding_exception('Context level must be CONTEXT_MODULE.');
        }
    }
}
