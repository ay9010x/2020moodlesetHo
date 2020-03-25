<?php



namespace mod_assign\event;

defined('MOODLE_INTERNAL') || die();


class identities_revealed extends base {
    
    protected static $preventcreatecall = true;

    
    public static function create_from_assign(\assign $assign) {
        $data = array(
            'context' => $assign->get_context(),
            'objectid' => $assign->get_instance()->id
        );
        self::$preventcreatecall = false;
        
        $event = self::create($data);
        self::$preventcreatecall = true;
        $event->set_assign($assign);
        return $event;
    }

    
    public function get_description() {
        return "The user with id '$this->userid' has revealed identities in the assignment with course module " .
            "id '$this->contextinstanceid'.";
    }

    
    public static function get_name() {
        return get_string('eventidentitiesrevealed', 'mod_assign');
    }

    
    protected function init() {
        $this->data['crud'] = 'u';
        $this->data['edulevel'] = self::LEVEL_TEACHING;
        $this->data['objecttable'] = 'assign';
    }

    
    protected function get_legacy_logdata() {
        $this->set_legacy_logdata('reveal identities', get_string('revealidentities', 'assign'));
        return parent::get_legacy_logdata();
    }

    
    protected function validate_data() {
        if (self::$preventcreatecall) {
            throw new \coding_exception('cannot call identities_revealed::create() directly, use identities_revealed::create_from_assign() instead.');
        }

        parent::validate_data();
    }

    public static function get_objectid_mapping() {
        return array('db' => 'assign', 'restore' => 'assign');
    }
}
