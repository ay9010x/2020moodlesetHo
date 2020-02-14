<?php



namespace core\event;
defined('MOODLE_INTERNAL') || die();


class webservice_token_created extends base {

    
    public function get_description() {
        return "The user with id '$this->userid' created a web service token for the user with id '$this->relateduserid'.";
    }

    
    protected function get_legacy_logdata() {
        if (!empty($this->other['auto'])) {
                        return array(SITEID, 'webservice', 'automatically create user token', '' , 'User ID: ' . $this->relateduserid);
        }
    }

    
    public static function get_name() {
        return get_string('eventwebservicetokencreated', 'webservice');
    }

    
    public function get_url() {
        return new \moodle_url('/admin/settings.php', array('section' => 'webservicetokens'));
    }

    
    protected function init() {
        $this->context = \context_system::instance();
        $this->data['crud'] = 'c';
        $this->data['edulevel'] = self::LEVEL_OTHER;
        $this->data['objecttable'] = 'external_tokens';
    }

    
    protected function validate_data() {
        parent::validate_data();
        if (!isset($this->relateduserid)) {
           throw new \coding_exception('The \'relateduserid\' must be set.');
        }

        if (!isset($this->other['auto'])) {
            throw new \coding_exception('The \'auto\' value must be set in other.');
        }
    }

    public static function get_objectid_mapping() {
                return array('db' => 'external_tokens', 'restore' => base::NOT_MAPPED);
    }

    public static function get_other_mapping() {
        return false;
    }
}
