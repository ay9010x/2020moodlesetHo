<?php



namespace core\event;
defined('MOODLE_INTERNAL') || die();


class webservice_service_created extends base {

    
    public function get_description() {
        return "The user with id '$this->userid' created the web service with id '$this->objectid'.";
    }

    
    protected function get_legacy_logdata() {
        global $CFG;
        $service = $this->get_record_snapshot('external_services', $this->objectid);
        return array(SITEID, 'webservice', 'add', $CFG->wwwroot . "/" . $CFG->admin . "/settings.php?section=externalservices",
            get_string('addservice', 'webservice', $service));
    }

    
    public static function get_name() {
        return get_string('eventwebserviceservicecreated', 'webservice');
    }

    
    public function get_url() {
        return new \moodle_url('/admin/settings.php', array('section' => 'externalservices'));
    }

    
    protected function init() {
        $this->context = \context_system::instance();
        $this->data['crud'] = 'c';
        $this->data['edulevel'] = self::LEVEL_OTHER;
        $this->data['objecttable'] = 'external_services';
    }

    public static function get_objectid_mapping() {
                return array('db' => 'external_services', 'restore' => NOT_MAPPED);
    }

    public static function get_other_mapping() {
        return false;
    }
}
