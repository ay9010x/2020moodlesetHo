<?php



namespace mod_assign\event;

defined('MOODLE_INTERNAL') || die();


class all_submissions_downloaded extends base {
    
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
        return "The user with id '$this->userid' has downloaded all the submissions for the assignment " .
            "with course module id '$this->contextinstanceid'.";
    }

    
    public static function get_name() {
        return get_string('eventallsubmissionsdownloaded', 'mod_assign');
    }

    
    protected function init() {
        $this->data['crud'] = 'r';
        $this->data['edulevel'] = self::LEVEL_TEACHING;
        $this->data['objecttable'] = 'assign';
    }

    
    protected function get_legacy_logdata() {
        $this->set_legacy_logdata('download all submissions', get_string('downloadall', 'assign'));
        return parent::get_legacy_logdata();
    }

    
    protected function validate_data() {
        if (self::$preventcreatecall) {
            throw new \coding_exception('cannot call all_submissions_downloaded::create() directly, use all_submissions_downloaded::create_from_assign() instead.');
        }

        parent::validate_data();
    }

    public static function get_objectid_mapping() {
        return array('db' => 'assign', 'restore' => 'assign');
    }
}
