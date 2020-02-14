<?php



namespace mod_scheduler\event;

defined('MOODLE_INTERNAL') || die();

class slot_added extends slot_base {

    public static function create_from_slot(\scheduler_slot $slot) {
        $event = self::create(self::base_data($slot));
        $event->set_slot($slot);
        return $event;
    }

    
    protected function init() {
        $this->data['crud'] = 'c';
        $this->data['edulevel'] = self::LEVEL_TEACHING;
    }

    
    public static function get_name() {
        return get_string('event_slotadded', 'scheduler');
    }

    
    public function get_description() {
        return "The user with id '$this->userid' created the slot with id  '{$this->objectid}'"
                ." in the scheduler with course module id '$this->contextinstanceid'.";
    }
}
