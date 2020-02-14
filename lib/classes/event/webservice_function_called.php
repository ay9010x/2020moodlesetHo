<?php



namespace core\event;
defined('MOODLE_INTERNAL') || die();


class webservice_function_called extends base {

    
    protected $legacylogdata;

    
    public function get_description() {
        return "The web service function '{$this->other['function']}' has been called.";
    }

    
    protected function get_legacy_logdata() {
        return $this->legacylogdata;
    }

    
    public static function get_name() {
        return get_string('eventwebservicefunctioncalled', 'webservice');
    }

    
    protected function init() {
        $this->data['crud'] = 'r';
        $this->data['edulevel'] = self::LEVEL_OTHER;
        $this->context = \context_system::instance();
    }

    
    public function set_legacy_logdata($legacydata) {
        $this->legacylogdata = $legacydata;
    }

    
    protected function validate_data() {
        parent::validate_data();
        if (!isset($this->other['function'])) {
           throw new \coding_exception('The \'function\' value must be set in other.');
        }
    }

    public static function get_other_mapping() {
        return false;
    }
}
