<?php




namespace mod_scheduler\event;
defined('MOODLE_INTERNAL') || die();

class booking_form_viewed extends scheduler_base {

    public static function create_from_scheduler(\scheduler_instance $scheduler) {
        $event = self::create(self::base_data($scheduler));
        $event->set_scheduler($scheduler);
        return $event;
    }

    
    protected function init() {
        $this->data['crud'] = 'r';
        $this->data['edulevel'] = self::LEVEL_PARTICIPATING;
    }

    
    public static function get_name() {
        return get_string('event_bookingformviewed', 'scheduler');
    }

    
    public function get_description() {
        return "The user with id '$this->userid' has viewed the booking form in the scheduler with course module id '$this->contextinstanceid'.";
    }
}
