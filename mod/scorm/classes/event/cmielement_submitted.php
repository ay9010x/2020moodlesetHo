<?php



namespace mod_scorm\event;
defined('MOODLE_INTERNAL') || die();


abstract class cmielement_submitted extends \core\event\base {

    
    protected function init() {
        $this->data['crud'] = 'u';
        $this->data['edulevel'] = self::LEVEL_PARTICIPATING;
        $this->data['objecttable'] = 'scorm_scoes_track';
    }

    
    public function get_description() {
        return "The user with the id '$this->userid' submitted the element '{$this->other['cmielement']}' " .
                "with the value of '{$this->other['cmivalue']}' " .
                "for the attempt with the id '{$this->other['attemptid']}' " .
                "for a scorm activity with the course module id '$this->contextinstanceid'.";
    }

    
    public function get_url() {
        return new \moodle_url('/mod/scorm/report/userreport.php',
                array('id' => $this->contextinstanceid, 'user' => $this->userid, 'attempt' => $this->other['attemptid']));
    }

    
    protected function validate_data() {
        parent::validate_data();

        if (empty($this->other['attemptid'])) {
            throw new \coding_exception("The 'attemptid' must be set in other.");
        }

        if (empty($this->other['cmielement'])) {
            throw new \coding_exception("The 'cmielement' must be set in other.");
        }
                        if (strpos($this->other['cmielement'], 'cmi.', 0) !== 0) {
            throw new \coding_exception(
                "A valid 'cmielement' must start with 'cmi.' ({$this->other['cmielement']}).");
        }

                if (!isset($this->other['cmivalue'])) {
            throw new \coding_exception("The 'cmivalue' must be set in other.");
        }
    }
}
