<?php



namespace core\event;

defined('MOODLE_INTERNAL') || die();


class user_updated extends base {

    
    protected function init() {
        $this->data['objecttable'] = 'user';
        $this->data['crud'] = 'u';
        $this->data['edulevel'] = self::LEVEL_OTHER;
    }

    
    public static function get_name() {
        return get_string('eventuserupdated');
    }

    
    public function get_description() {
        return "The user with id '$this->userid' updated the profile for the user with id '$this->objectid'.";
    }

    
    public function get_url() {
        return new \moodle_url('/user/view.php', array('id' => $this->objectid));
    }

    
    public static function get_legacy_eventname() {
        return 'user_updated';
    }

    
    protected function get_legacy_eventdata () {
        return $this->get_record_snapshot('user', $this->objectid);
    }

    
    protected function get_legacy_logdata() {
        return array(SITEID, 'user', 'update', 'view.php?id='.$this->objectid, '');
    }

    
    protected function validate_data() {
        parent::validate_data();

        if (!isset($this->relateduserid)) {
            debugging('The \'relateduserid\' value must be specified in the event.', DEBUG_DEVELOPER);
            $this->relateduserid = $this->objectid;
        }
    }

    
    public static function create_from_userid($userid) {
        $data = array(
            'objectid' => $userid,
            'relateduserid' => $userid,
            'context' => \context_user::instance($userid)
        );

                $event = self::create($data);
        return $event;
    }

    public static function get_objectid_mapping() {
        return array('db' => 'user', 'restore' => 'user');
    }
}
