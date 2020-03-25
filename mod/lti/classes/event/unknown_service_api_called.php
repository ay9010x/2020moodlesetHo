<?php



namespace mod_lti\event;
defined('MOODLE_INTERNAL') || die();


class unknown_service_api_called extends \core\event\base {

    
    protected $eventdata;

    
    public function set_message_data(\stdClass $data) {
        $this->eventdata = $data;
    }

    
    public function get_message_data() {
        if ($this->is_restored()) {
            throw new \coding_exception('Function get_message_data() can not be used on restored events.');
        }
        return $this->eventdata;
    }

    
    protected function init() {
        $this->data['crud'] = 'r';
        $this->data['edulevel'] = self::LEVEL_OTHER;
        $this->context = \context_system::instance();
    }

    
    public function get_description() {
        return 'An unknown call to a service api was made.';
    }

    
    public static function get_name() {
        return get_string('ltiunknownserviceapicall', 'mod_lti');
    }

    
    public static function get_legacy_eventname() {
        return 'lti_unknown_service_api_call';
    }

    
    protected function get_legacy_eventdata() {
        return $this->eventdata;
    }

}
