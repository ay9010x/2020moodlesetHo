<?php



namespace mod_assign\event;

defined('MOODLE_INTERNAL') || die();


class reveal_identities_confirmation_page_viewed extends base {
    
    protected static $preventcreatecall = true;

    
    public static function create_from_assign(\assign $assign) {
        $data = array(
            'context' => $assign->get_context(),
            'other' => array(
                'assignid' => $assign->get_instance()->id,
            ),
        );
        self::$preventcreatecall = false;
        
        $event = self::create($data);
        self::$preventcreatecall = true;
        $event->set_assign($assign);
        return $event;
    }

    
    protected function init() {
        $this->data['crud'] = 'r';
        $this->data['edulevel'] = self::LEVEL_TEACHING;
    }

    
    public static function get_name() {
        return get_string('eventrevealidentitiesconfirmationpageviewed', 'mod_assign');
    }

    
    public function get_description() {
        return "The user with id '$this->userid' viewed the confirmation page for revealing identities for the " .
            "assignment with course module id '$this->contextinstanceid'.";
    }


    
    protected function get_legacy_logdata() {
        $this->set_legacy_logdata('view', get_string('viewrevealidentitiesconfirm', 'assign'));
        return parent::get_legacy_logdata();
    }

    
    protected function validate_data() {
        if (self::$preventcreatecall) {
            throw new \coding_exception('cannot call reveal_identities_confirmation_page_viewed::create() directly, use reveal_identities_confirmation_page_viewed::create_from_grade() instead.');
        }

        parent::validate_data();

        if (!isset($this->other['assignid'])) {
            throw new \coding_exception('The \'assignid\' value must be set in other.');
        }
    }

    public static function get_other_mapping() {
        $othermapped = array();
        $othermapped['assignid'] = array('db' => 'assign', 'restore' => 'assign');

        return $othermapped;
    }
}