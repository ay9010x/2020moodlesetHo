<?php



namespace mod_scheduler\event;

defined('MOODLE_INTERNAL') || die();

class slot_deleted extends slot_base {

    public static function create_from_slot(\scheduler_slot $slot, $action) {
        $data = self::base_data($slot);
        $data['other'] = array('action' => $action);
        $event = self::create($data);
        $event->set_slot($slot);
        return $event;
    }

    
    protected function init() {
        $this->data['crud'] = 'd';
        $this->data['edulevel'] = self::LEVEL_TEACHING;
    }

    
    public static function get_name() {
        return get_string('event_slotdeleted', 'scheduler');
    }

    
    public function get_description() {
        $desc = "The user with id '$this->userid' deleted the slot with id  '{$this->objectid}'"
                ." in the scheduler with course module id '$this->contextinstanceid'";
        if ($act = $this->other['action']) {
            $desc .= " during action '$act'";
        }
        $desc .= '.';
        return $desc;
    }
}
