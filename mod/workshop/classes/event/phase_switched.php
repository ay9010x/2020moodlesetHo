<?php



namespace mod_workshop\event;
defined('MOODLE_INTERNAL') || die();


class phase_switched extends \core\event\base {

    
    protected function init() {
        $this->data['crud'] = 'u';
        $this->data['edulevel'] = self::LEVEL_TEACHING;
        $this->data['objecttable'] = 'workshop';
    }

    
    public function get_description() {
        return "The user with id '$this->userid' has switched the phase of the workshop with course module id " .
            "'$this->contextinstanceid' to '{$this->other['workshopphase']}'.";
    }

    
    protected function get_legacy_logdata() {
        return array($this->courseid, 'workshop', 'update switch phase', 'view.php?id=' . $this->contextinstanceid,
                $this->other['workshopphase'], $this->contextinstanceid);
    }

    
    public static function get_name() {
        return get_string('eventphaseswitched', 'mod_workshop');
    }

    
    public function get_url() {
        return new \moodle_url('/mod/workshop/view.php', array('id' => $this->contextinstanceid));
    }

    
    protected function validate_data() {
        parent::validate_data();

        if (!isset($this->other['workshopphase'])) {
            throw new \coding_exception('The \'workshopphase\' value must be set in other.');
        }
    }

    public static function get_objectid_mapping() {
        return array('db' => 'workshop', 'restore' => 'workshop');
    }

    public static function get_other_mapping() {
                return false;
    }
}
