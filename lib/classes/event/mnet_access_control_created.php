<?php



namespace core\event;

defined('MOODLE_INTERNAL') || die();


class mnet_access_control_created extends base {

    
    protected function init() {
        $this->data['objecttable'] = 'mnet_sso_access_control';
        $this->data['crud'] = 'c';
        $this->data['edulevel'] = self::LEVEL_OTHER;
    }

    
    public static function get_name() {
        return get_string('eventaccesscontrolcreated', 'mnet');
    }

    
    public function get_url() {
        return new \moodle_url('/admin/mnet/access_control.php');
    }

    
    public function get_description() {
        return "The user with id '$this->userid' created access control for the user with username '{$this->other['username']}' " .
            "belonging to mnet host '{$this->other['hostname']}'.";
    }

    
    protected function get_legacy_logdata() {
        return array(SITEID, 'admin/mnet', 'add', 'admin/mnet/access_control.php', 'SSO ACL: ' . $this->other['accessctrl'] .
            ' user \'' . $this->other['username'] . '\' from ' . $this->other['hostname']);
    }

    
    protected function validate_data() {
        parent::validate_data();

        if (!isset($this->other['username'])) {
            throw new \coding_exception('The \'username\' value must be set in other.');
        }

        if (!isset($this->other['hostname'])) {
            throw new \coding_exception('The \'hostname\' value must be set in other.');
        }

        if (!isset($this->other['accessctrl'])) {
            throw new \coding_exception('The \'accessctrl\' value must be set in other.');
        }
    }

    public static function get_objectid_mapping() {
                return array('db' => 'mnet_sso_access_control', 'restore' => base::NOT_MAPPED);
    }

    public static function get_other_mapping() {
                return false;
    }
}
