<?php




namespace mod_scheduler\event;
defined('MOODLE_INTERNAL') || die();

class booking_removed extends slot_base {

    public static function create_from_slot(\scheduler_slot $slot) {
        $event = self::create(self::base_data($slot));
        $event->set_slot($slot);
        return $event;
    }

    
    protected function init() {
        $this->data['crud'] = 'd';
        $this->data['edulevel'] = self::LEVEL_PARTICIPATING;
    }

    
    public static function get_name() {
        return get_string('event_bookingremoved', 'scheduler');
    }

    
    public function get_description() {
        return "The user with id '$this->userid' has removed their booking from the slot with id  '{$this->objectid}'"
                ." in the scheduler with course module id '$this->contextinstanceid'.";
    }
}
