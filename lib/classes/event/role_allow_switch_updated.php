<?php



namespace core\event;

defined('MOODLE_INTERNAL') || die();


class role_allow_switch_updated extends base {
    
    protected function init() {
        $this->data['crud'] = 'u';
        $this->data['edulevel'] = self::LEVEL_OTHER;
    }

    
    public static function get_name() {
        return get_string('eventroleallowswitchupdated', 'role');
    }

    
    public function get_description() {
        return "The user with id '$this->userid' updated Allow role switches.";
    }

    
    public function get_url() {
        return new \moodle_url('/admin/roles/allow.php', array('mode' => 'switch'));
    }

    
    protected function get_legacy_logdata() {
        return array(SITEID, 'role', 'edit allow switch', 'admin/roles/allow.php?mode=switch');
    }
}
