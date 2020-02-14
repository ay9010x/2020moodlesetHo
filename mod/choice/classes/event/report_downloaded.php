<?php



namespace mod_choice\event;
defined('MOODLE_INTERNAL') || die();


class report_downloaded extends \core\event\base {

    
    protected function init() {
        $this->data['crud'] = 'r';
        $this->data['edulevel'] = self::LEVEL_TEACHING;
    }

    
    public static function get_name() {
        return get_string('eventreportdownloaded', 'mod_choice');
    }

    
    public function get_description() {
        return "The user with id '$this->userid' has downloaded the report in the '".$this->other['format']."' format for
            the choice activity with course module id '$this->contextinstanceid'";
    }

    
    public function get_url() {
        return new \moodle_url('/mod/choice/report.php', array('id' => $this->contextinstanceid));
    }

    
    protected function validate_data() {
        parent::validate_data();

                if (!isset($this->other['content'])) {
            throw new \coding_exception('The \'content\' value must be set in other.');
        }
                if (!isset($this->other['format'])) {
            throw new \coding_exception('The \'format\' value must be set in other.');
        }
                if (!isset($this->other['choiceid'])) {
            throw new \coding_exception('The \'choiceid\' value must be set in other.');
        }
    }

    public static function get_objectid_mapping() {
        return false;
    }

    public static function get_other_mapping() {
        $othermapped = array();
        $othermapped['choiceid'] = array('db' => 'choice', 'restore' => 'choice');

        return $othermapped;
    }
}
