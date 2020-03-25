<?php



namespace core\event;
defined('MOODLE_INTERNAL') || die();


class webservice_service_user_added extends base {

    
    public function get_description() {
        return "The user with id '$this->userid' added the user with id '$this->relateduserid' to the web service " .
            "with id '$this->objectid'.";
    }

    
    protected function get_legacy_logdata() {
        global $CFG;
        return array(SITEID, 'core', 'assign', $CFG->admin . '/webservice/service_users.php?id=' . $this->objectid, 'add', '',
            $this->relateduserid);
    }

    
    public static function get_name() {
        return get_string('eventwebserviceserviceuseradded', 'webservice');
    }

    
    public function get_url() {
        return new \moodle_url('/admin/webservice/service_users.php', array('id' => $this->objectid));
    }

    
    protected function init() {
        $this->context = \context_system::instance();
        $this->data['crud'] = 'c';
        $this->data['edulevel'] = self::LEVEL_OTHER;
        $this->data['objecttable'] = 'external_services';
    }

    
    protected function validate_data() {
        parent::validate_data();
        if (!isset($this->relateduserid)) {
            throw new \coding_exception('The \'relateduserid\' must be set.');
        }
    }

    public static function get_objectid_mapping() {
                return array('db' => 'external_services', 'restore' => base::NOT_MAPPED);
    }

}
