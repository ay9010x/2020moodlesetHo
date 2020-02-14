<?php


namespace core\event;

defined('MOODLE_INTERNAL') || die();


class user_deleted extends base {

    
    protected function init() {
        $this->data['objecttable'] = 'user';
        $this->data['crud'] = 'd';
        $this->data['edulevel'] = self::LEVEL_OTHER;
    }

    
    public static function get_name() {
        return get_string('eventuserdeleted');
    }

    
    public function get_description() {
        return "The user with id '$this->userid' deleted the user with id '$this->objectid'.";
    }

    
    public static function get_legacy_eventname() {
        return 'user_deleted';
    }

    
    protected function get_legacy_eventdata() {
        $user = $this->get_record_snapshot('user', $this->objectid);
        $user->deleted = 0;
        $user->username = $this->other['username'];
        $user->email = $this->other['email'];
        $user->idnumber = $this->other['idnumber'];
        $user->picture = $this->other['picture'];

        return $user;
    }

    
    protected function get_legacy_logdata() {
        $user = $this->get_record_snapshot('user', $this->objectid);
        return array(SITEID, 'user', 'delete', 'view.php?id=' . $user->id, $user->firstname . ' ' . $user->lastname);
    }

    
    protected function validate_data() {
        parent::validate_data();

        if (!isset($this->relateduserid)) {
            debugging('The \'relateduserid\' value must be specified in the event.', DEBUG_DEVELOPER);
            $this->relateduserid = $this->objectid;
        }

        if (!isset($this->other['username'])) {
            throw new \coding_exception('The \'username\' value must be set in other.');
        }

        if (!isset($this->other['email'])) {
            throw new \coding_exception('The \'email\' value must be set in other.');
        }

        if (!isset($this->other['idnumber'])) {
            throw new \coding_exception('The \'idnumber\' value must be set in other.');
        }

        if (!isset($this->other['picture'])) {
            throw new \coding_exception('The \'picture\' value must be set in other.');
        }

        if (!isset($this->other['mnethostid'])) {
            throw new \coding_exception('The \'mnethostid\' value must be set in other.');
        }
    }

    public static function get_objectid_mapping() {
        return array('db' => 'user', 'restore' => 'user');
    }

    public static function get_other_mapping() {
        $othermapped = array();
        $othermapped['mnethostid'] = array('db' => 'mnet_host', 'restore' => base::NOT_MAPPED);

        return $othermapped;
    }
}
