<?php



namespace core\event;

defined('MOODLE_INTERNAL') || die();


class role_allow_override_updated extends base {
    
    protected function init() {
        $this->data['crud'] = 'u';
        $this->data['edulevel'] = self::LEVEL_OTHER;
    }

    
    public static function get_name() {
        return get_string('eventroleallowoverrideupdated', 'role');
    }

    
    public function get_description() {
        return "The user with id '$this->userid' updated Allow role overrides.";
    }

    
    public function get_url() {
        return new \moodle_url('/admin/roles/allow.php', array('mode' => 'override'));
    }

    
    protected function get_legacy_logdata() {
        return array(SITEID, 'role', 'edit allow override', 'admin/roles/allow.php?mode=override');
    }
}
