<?php



namespace mod_choice\event;

defined('MOODLE_INTERNAL') || die();


class answer_deleted extends \core\event\base {

    
    public function get_description() {
        return "The user with id '$this->userid' has deleted the option with id '" . $this->other['optionid'] . "' for the
            user with id '$this->relateduserid' from the choice activity with course module id '$this->contextinstanceid'.";
    }

    
    public static function get_name() {
        return get_string('eventanswerdeleted', 'mod_choice');
    }

    
    public function get_url() {
        return new \moodle_url('/mod/choice/view.php', array('id' => $this->contextinstanceid));
    }

    
    protected function init() {
        $this->data['objecttable'] = 'choice_answers';
        $this->data['crud'] = 'd';
        $this->data['edulevel'] = self::LEVEL_PARTICIPATING;
    }

    
    protected function validate_data() {
        parent::validate_data();

        if (!isset($this->other['choiceid'])) {
            throw new \coding_exception('The \'choiceid\' value must be set in other.');
        }

        if (!isset($this->other['optionid'])) {
            throw new \coding_exception('The \'optionid\' value must be set in other.');
        }
    }

    public static function get_objectid_mapping() {
        return array('db' => 'choice_answers', 'restore' => \core\event\base::NOT_MAPPED);
    }

    public static function get_other_mapping() {
        $othermapped = array();
        $othermapped['choiceid'] = array('db' => 'choice', 'restore' => 'choice');
        $othermapped['optionid'] = array('db' => 'choice_options', 'restore' => 'choice_option');

        return $othermapped;
    }
}
