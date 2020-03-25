<?php



namespace core\event;

defined('MOODLE_INTERNAL') || die();


class role_deleted extends base {
    
    protected function init() {
        $this->data['objecttable'] = 'role';
        $this->data['crud'] = 'd';
        $this->data['edulevel'] = self::LEVEL_OTHER;
    }

    
    public static function get_name() {
        return get_string('eventroledeleted', 'role');
    }

    
    public function get_description() {
        return "The user with id '$this->userid' deleted the role with id '$this->objectid'.";
    }

    
    public function get_url() {
        return new \moodle_url('/admin/roles/manage.php');
    }

    
    protected function get_legacy_logdata() {
        return array(SITEID, 'role', 'delete', 'admin/roles/manage.php?action=delete&roleid=' . $this->objectid,
            $this->other['shortname'], '');
    }

    
    protected function validate_data() {
        parent::validate_data();

        if (!isset($this->other['shortname'])) {
            throw new \coding_exception('The \'shortname\' value must be set in other.');
        }
    }

    public static function get_objectid_mapping() {
        return array('db' => 'role', 'restore' => 'role');
    }

    public static function get_other_mapping() {
                return false;
    }
}
