<?php



namespace core\event;

defined('MOODLE_INTERNAL') || die();


class role_allow_assign_updated extends base {
    
    protected function init() {
        $this->data['crud'] = 'u';
        $this->data['edulevel'] = self::LEVEL_OTHER;
    }

    
    public static function get_name() {
        return get_string('eventroleallowassignupdated', 'role');
    }

    
    public function get_description() {
        return "The user with id '$this->userid' updated Allow role assignments.";
    }

    
    public function get_url() {
        return new \moodle_url('/admin/roles/allow.php', array('mode' => 'assign'));
    }

    
    protected function get_legacy_logdata() {
        return array(SITEID, 'role', 'edit allow assign', 'admin/roles/allow.php?mode=assign');
    }
}
