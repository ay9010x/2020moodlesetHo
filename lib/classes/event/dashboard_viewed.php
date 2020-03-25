<?php



namespace core\event;
defined('MOODLE_INTERNAL') || die();


class dashboard_viewed extends base {

    
    protected function init() {
        $this->data['crud'] = 'r';
        $this->data['edulevel'] = self::LEVEL_OTHER;
    }

    
    public function get_description() {
        return "The user with id '$this->userid' has viewed their dashboard";
    }

    
    public static function get_name() {
        return get_string('eventdashboardviewed', 'core');
    }

}
