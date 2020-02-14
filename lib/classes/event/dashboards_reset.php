<?php



namespace core\event;
defined('MOODLE_INTERNAL') || die();


class dashboards_reset extends base {

    
    protected function init() {
        $this->data['crud'] = 'u';
        $this->data['edulevel'] = self::LEVEL_OTHER;
    }

    
    public function get_description() {
        return "The user with id '$this->userid' has reset all user dashboards";
    }

    
    public static function get_name() {
        return get_string('eventdashboardsreset', 'core');
    }

    
    public function get_url() {
        return new \moodle_url('/my/indexsys.php');
    }
}
