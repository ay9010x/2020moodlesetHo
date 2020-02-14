<?php



namespace core\event;

defined('MOODLE_INTERNAL') || die();


class role_unassigned extends base {
    protected function init() {
        $this->data['objecttable'] = 'role';
        $this->data['crud'] = 'd';
        $this->data['edulevel'] = self::LEVEL_OTHER;
    }

    
    public static function get_name() {
        return get_string('eventroleunassigned', 'role');
    }

    
    public function get_description() {
        return "The user with id '$this->userid' unassigned the role with id '$this->objectid' from the user with " .
            "id '$this->relateduserid'.";
    }

    
    public function get_url() {
        return new \moodle_url('/admin/roles/assign.php', array('contextid' => $this->contextid, 'roleid' => $this->objectid));
    }

    
    public static function get_legacy_eventname() {
        return 'role_unassigned';
    }

    
    protected function get_legacy_eventdata() {
        return $this->get_record_snapshot('role_assignments', $this->other['id']);
    }

    
    protected function get_legacy_logdata() {
        $roles = get_all_roles();
        $rolenames = role_fix_names($roles, $this->get_context(), ROLENAME_ORIGINAL, true);
        return array($this->courseid, 'role', 'unassign', 'admin/roles/assign.php?contextid='.$this->contextid.'&roleid='.$this->objectid,
                $rolenames[$this->objectid], '', $this->userid);
    }

    
    protected function validate_data() {
        parent::validate_data();

        if (!isset($this->relateduserid)) {
            throw new \coding_exception('The \'relateduserid\' must be set.');
        }

        if (!isset($this->other['id'])) {
            throw new \coding_exception('The \'id\' value must be set in other.');
        }

        if (!isset($this->other['component'])) {
            throw new \coding_exception('The \'component\' value must be set in other.');
        }
    }

    public static function get_objectid_mapping() {
        return array('db' => 'role', 'restore' => 'role');
    }

    public static function get_other_mapping() {
        $othermapped = array();
        $othermapped['id'] = array('db' => 'role_assignments', 'restore' => base::NOT_MAPPED);
        $othermapped['itemid'] = base::NOT_MAPPED;

        return $othermapped;
    }
}
