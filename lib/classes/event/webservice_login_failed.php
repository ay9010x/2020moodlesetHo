<?php



namespace core\event;
defined('MOODLE_INTERNAL') || die();


class webservice_login_failed extends base {

    
    protected $legacylogdata;

    
    public function get_description() {
        return "Web service authentication failed with code: \"{$this->other['reason']}\".";
    }

    
    protected function get_legacy_logdata() {
        return $this->legacylogdata;
    }

    
    public static function get_name() {
        return get_string('eventwebserviceloginfailed', 'webservice');
    }

    
    protected function init() {
        $this->data['crud'] = 'r';
        $this->data['edulevel'] = self::LEVEL_OTHER;
        $this->context = \context_system::instance();
    }

    
    public function set_legacy_logdata($logdata) {
        $this->legacylogdata = $logdata;
    }

    
    protected function validate_data() {
        parent::validate_data();
        if (!isset($this->other['reason'])) {
           throw new \coding_exception('The \'reason\' value must be set in other.');
        } else if (!isset($this->other['method'])) {
           throw new \coding_exception('The \'method\' value must be set in other.');
        } else if (isset($this->other['token'])) {
           throw new \coding_exception('The \'token\' value must not be set in other.');
        }
    }

    public static function get_other_mapping() {
        return false;
    }
}
