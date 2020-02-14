<?php



namespace core\event;

defined('MOODLE_INTERNAL') || die();


class user_profile_viewed extends base {

    
    protected function init() {
        $this->data['objecttable'] = 'user';
        $this->data['crud'] = 'r';
        $this->data['edulevel'] = self::LEVEL_OTHER;
    }

    
    public static function get_name() {
        return get_string('eventuserprofileviewed');
    }

    
    public function get_description() {
        $desc = "The user with id '$this->userid' viewed the profile for the user with id '$this->relateduserid'";
        $desc .= ($this->contextlevel == CONTEXT_COURSE) ? " in the course with id '$this->courseid'." : ".";
        return $desc;
    }

    
    public function get_url() {
        if ($this->contextlevel == CONTEXT_COURSE) {
            return new \moodle_url('/user/view.php', array('id' => $this->relateduserid, 'course' => $this->courseid));
        }
        return new \moodle_url('/user/profile.php', array('id' => $this->relateduserid));
    }

    
    protected function get_legacy_logdata() {
        if ($this->contextlevel == CONTEXT_COURSE) {
            return array($this->courseid, 'user', 'view', 'view.php?id=' . $this->relateduserid . '&course=' .
                $this->courseid, $this->relateduserid);
        }
        return null;
    }

    
    protected function validate_data() {
        parent::validate_data();

        if (!isset($this->relateduserid)) {
            throw new \coding_exception('The \'relateduserid\' must be set.');
        }
    }

    public static function get_objectid_mapping() {
        return array('db' => 'user', 'restore' => 'user');
    }

    public static function get_other_mapping() {
        $othermapped = array();
        $othermapped['courseid'] = array('db' => 'course', 'restore' => 'course');

        return $othermapped;
    }
}
