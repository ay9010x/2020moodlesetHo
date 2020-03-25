<?php



namespace core\event;

defined('MOODLE_INTERNAL') || die();


class message_deleted extends base {

    
    public static function create_from_ids($userfromid, $usertoid, $userdeleted, $messagetable, $messageid) {
                if ($userdeleted == $userfromid) {
            $relateduserid = $usertoid;
        } else {
            $relateduserid = $userfromid;
        }

                        $event = self::create(array(
            'userid' => $userdeleted,
            'context' => \context_system::instance(),
            'relateduserid' => $relateduserid,
            'other' => array(
                'messagetable' => $messagetable,
                'messageid' => $messageid,
                'useridfrom' => $userfromid,
                'useridto' => $usertoid
            )
        ));

        return $event;
    }

    
    protected function init() {
        $this->data['crud'] = 'u';
        $this->data['edulevel'] = self::LEVEL_OTHER;
    }

    
    public static function get_name() {
        return get_string('eventmessagedeleted', 'message');
    }

    
    public function get_description() {
                if ($this->userid == $this->other['useridto']) {
            $str = 'from';
        } else {
            $str = 'to';
        }

        return "The user with id '$this->userid' deleted a message sent $str the user with id '$this->relateduserid'.";
    }

    
    protected function validate_data() {
        parent::validate_data();

        if (!isset($this->relateduserid)) {
            throw new \coding_exception('The \'relateduserid\' must be set.');
        }

        if (!isset($this->other['messagetable'])) {
            throw new \coding_exception('The \'messagetable\' value must be set in other.');
        }

        if (!isset($this->other['messageid'])) {
            throw new \coding_exception('The \'messageid\' value must be set in other.');
        }

        if (!isset($this->other['useridfrom'])) {
            throw new \coding_exception('The \'useridfrom\' value must be set in other.');
        }

        if (!isset($this->other['useridto'])) {
            throw new \coding_exception('The \'useridto\' value must be set in other.');
        }
    }

    public static function get_other_mapping() {
                $othermapped = array();
                $othermapped['messageid'] = array('db' => base::NOT_MAPPED, 'restore' => base::NOT_MAPPED);
        $othermapped['useridfrom'] = array('db' => 'user', 'restore' => base::NOT_MAPPED);
        $othermapped['useridto'] = array('db' => 'user', 'restore' => base::NOT_MAPPED);
        return $othermapped;
    }
}
