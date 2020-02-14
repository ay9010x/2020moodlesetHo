<?php



namespace mod_scorm\event;
defined('MOODLE_INTERNAL') || die();


class sco_launched extends \core\event\base {

    
    protected function init() {
        $this->data['crud'] = 'r';
        $this->data['edulevel'] = self::LEVEL_PARTICIPATING;
        $this->data['objecttable'] = 'scorm_scoes';
    }

    
    public function get_description() {
        return "The user with id '$this->userid' launched the sco with id '$this->objectid' for the scorm with " .
            "course module id '$this->contextinstanceid'.";
    }

    
    public static function get_name() {
        return get_string('eventscolaunched', 'mod_scorm');
    }

    
    public function get_url() {
        return new \moodle_url('/mod/scorm/player.php', array('id' => $this->contextinstanceid, 'scoid' => $this->objectid));
    }

    
    protected function get_legacy_logdata() {
        return array($this->courseid, 'scorm', 'launch', 'view.php?id=' . $this->contextinstanceid,
                $this->other['loadedcontent'], $this->contextinstanceid);
    }

    
    protected function validate_data() {
        parent::validate_data();

        if (empty($this->other['loadedcontent'])) {
            throw new \coding_exception('The \'loadedcontent\' value must be set in other.');
        }
    }

    public static function get_objectid_mapping() {
        return array('db' => 'scorm_scoes', 'restore' => 'scorm_sco');
    }

    public static function get_other_mapping() {
        $othermapped = array();
        $othermapped['instanceid'] = array('db' => 'scorm', 'restore' => 'scorm');

        return $othermapped;
    }
}
