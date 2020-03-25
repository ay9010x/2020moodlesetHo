<?php



namespace mod_scorm\event;
defined('MOODLE_INTERNAL') || die();


class report_viewed extends \core\event\base {

    
    protected function init() {
        $this->data['crud'] = 'r';
        $this->data['edulevel'] = self::LEVEL_TEACHING;
    }

    
    public function get_description() {
        return "The user with id '$this->userid' viewed the scorm report '{$this->other['mode']}' for the scorm with " .
            "course module id '$this->contextinstanceid'.";
    }

    
    public static function get_name() {
        return get_string('eventreportviewed', 'mod_scorm');
    }

    
    public function get_url() {
        return new \moodle_url('/mod/scorm/report.php', array('id' => $this->contextinstanceid, 'mode' => $this->other['mode']));
    }

    
    protected function get_legacy_logdata() {
        return array($this->courseid, 'scorm', 'report', 'report.php?id=' . $this->contextinstanceid .
                '&mode=' . $this->other['mode'], $this->other['scormid'], $this->contextinstanceid);
    }

    
    protected function validate_data() {
        parent::validate_data();

        if (empty($this->other['scormid'])) {
            throw new \coding_exception('The \'scormid\' value must be set in other.');
        }

        if (empty($this->other['mode'])) {
            throw new \coding_exception('The \'mode\' value must be set in other.');
        }
    }

    public static function get_other_mapping() {
        $othermapped = array();
        $othermapped['scormid'] = array('db' => 'scorm', 'restore' => 'scorm');

        return $othermapped;
    }
}
