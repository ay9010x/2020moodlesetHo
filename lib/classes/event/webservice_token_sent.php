<?php



namespace core\event;
defined('MOODLE_INTERNAL') || die();


class webservice_token_sent extends base {

    
    public function get_description() {
        return "The user with id '$this->userid' has been sent the web service token with id '$this->objectid'.";
    }

    
    protected function get_legacy_logdata() {
        return array(SITEID, 'webservice', 'sending requested user token', '' , 'User ID: ' . $this->userid);
    }

    
    public static function get_name() {
        return get_string('eventwebservicetokensent', 'webservice');
    }

    
    protected function init() {
        $this->context = \context_system::instance();
        $this->data['crud'] = 'r';
        $this->data['edulevel'] = self::LEVEL_OTHER;
        $this->data['objecttable'] = 'external_tokens';
    }

    public static function get_objectid_mapping() {
                return array('db' => 'external_tokens', 'restore' => base::NOT_MAPPED);
    }
}
