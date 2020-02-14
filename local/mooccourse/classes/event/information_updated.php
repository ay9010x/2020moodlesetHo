<?php

namespace local_mooccourse\event;
defined('MOODLE_INTERNAL') || die();

class information_updated extends \core\event\base {

    
    protected function init() {
        $this->data['crud'] = 'u';
        $this->data['edulevel'] = self::LEVEL_TEACHING;
        $this->data['objecttable'] = 'course';
    }

    
    public function get_description() {
        return 'User with id ' . $this->userid . ' updated course information with instanceid ' .
            $this->objectid;
    }

    
    public static function get_name() {
        return get_string('eventinformationupdated', 'local_mooccourse');
    }

    
    public function get_url() {
        return new \moodle_url('/local/mooccourse/editinfo.php', array('id' => $this->objectid));
    }

    
    protected function get_legacy_logdata() {
        return array($this->courseid, 'mooccourse', 'information updated', $this->get_url(),
            $this->other['shortname'], $this->contextinstanceid);
    }

    
    protected function validate_data() {
        if (empty($this->other['shortname'])) {
            throw new \coding_exception('The event local_mooccourse\\event\\information_updated must specify shortname.');
        }
        parent::validate_data();
    }
}