<?php



namespace core\event;

defined('MOODLE_INTERNAL') || die();


class user_loggedinas extends base {

    
    protected function init() {
        $this->data['crud'] = 'r';
        $this->data['edulevel'] = self::LEVEL_OTHER;
        $this->data['objecttable'] = 'user';
    }

    
    public static function get_name() {
        return get_string('eventuserloggedinas', 'auth');
    }

    
    public function get_description() {
        return "The user with id '$this->userid' has logged in as the user with id '$this->relateduserid'.";
    }

    
    protected function get_legacy_logdata() {
        return array($this->courseid, 'course', 'loginas', '../user/view.php?id=' . $this->courseid . '&amp;user=' . $this->userid,
            $this->other['originalusername'] . ' -> ' . $this->other['loggedinasusername']);
    }

    
    public function get_url() {
        return new \moodle_url('/user/view.php', array('id' => $this->objectid));
    }

    
    protected function validate_data() {
        parent::validate_data();

        if (!isset($this->relateduserid)) {
            throw new \coding_exception('The \'relateduserid\' must be set.');
        }

        if (!isset($this->other['originalusername'])) {
            throw new \coding_exception('The \'originalusername\' value must be set in other.');
        }

        if (!isset($this->other['loggedinasusername'])) {
            throw new \coding_exception('The \'loggedinasusername\' value must be set in other.');
        }
    }

    public static function get_objectid_mapping() {
        return array('db' => 'user', 'restore' => 'user');
    }

    public static function get_other_mapping() {
        return false;
    }
}
