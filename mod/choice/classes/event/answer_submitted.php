<?php



namespace mod_choice\event;

defined('MOODLE_INTERNAL') || die();


class answer_submitted extends \core\event\base {

    
    public function get_description() {
        return "The user with id '$this->userid' made the choice with id '$this->objectid' in the choice activity
            with course module id '$this->contextinstanceid'.";
    }

    
    protected function get_legacy_logdata() {
        $legacylogdata = array($this->courseid,
            'choice',
            'choose',
            'view.php?id=' . $this->contextinstanceid,
            $this->other['choiceid'],
            $this->contextinstanceid);

        return $legacylogdata;
    }

    
    public static function get_name() {
        return get_string('eventanswercreated', 'mod_choice');
    }

    
    public function get_url() {
        return new \moodle_url('/mod/choice/view.php', array('id' => $this->contextinstanceid));
    }

    
    protected function init() {
                                        $this->data['crud'] = 'c';
        $this->data['edulevel'] = self::LEVEL_PARTICIPATING;
        $this->data['objecttable'] = 'choice';
    }

    
    protected function validate_data() {
        parent::validate_data();

        if (!isset($this->other['choiceid'])) {
            throw new \coding_exception('The \'choiceid\' value must be set in other.');
        }
    }

    public static function get_objectid_mapping() {
        return array('db' => 'choice', 'restore' => 'choice');
    }

    public static function get_other_mapping() {
        $othermapped = array();
        $othermapped['choiceid'] = array('db' => 'choice', 'restore' => 'choice');

                                                $othermapped['optionid'] = \core\event\base::NOT_MAPPED;

        return $othermapped;
    }
}
