<?php



namespace mod_workshop\event;
defined('MOODLE_INTERNAL') || die();


class assessments_reset extends \core\event\base {

    
    protected function init() {
        $this->data['crud'] = 'u';
        $this->data['edulevel'] = self::LEVEL_TEACHING;
    }

    
    public function get_description() {
        return "The user with id '$this->userid' has reset the assessments for the workshop with course module id " .
            "'$this->contextinstanceid'.";
    }

    
    protected function get_legacy_logdata() {
        return array($this->courseid, 'workshop', 'update clear assessments', 'view.php?id=' . $this->contextinstanceid,
            $this->other['workshopid'], $this->contextinstanceid);
    }

    
    public static function get_name() {
        return get_string('eventsubmissionassessmentsreset', 'mod_workshop');
    }

    
    public function get_url() {
        return new \moodle_url('/mod/workshop/view.php', array('id' => $this->contextinstanceid));
    }

    
    protected function validate_data() {
        parent::validate_data();

        if (!isset($this->other['workshopid'])) {
            throw new \coding_exception('The \'workshopid\' value must be set in other.');
        }
    }

    public static function get_other_mapping() {
        $othermapped = array();
        $othermapped['workshopid'] = array('db' => 'workshop', 'restore' => 'workshop');

        return $othermapped;
    }
}
