<?php



namespace core\event;
defined('MOODLE_INTERNAL') || die();


class dashboard_reset extends base {

    
    protected function init() {
        $this->data['crud'] = 'u';
        $this->data['edulevel'] = self::LEVEL_OTHER;

    }

    
    public function get_description() {
        return "The user with id '$this->userid' has reset their dashboard";
    }

    
    public static function get_name() {
        return get_string('eventdashboardreset', 'core');
    }

}
