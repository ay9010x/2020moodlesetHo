<?php



namespace mod_scorm\event;
defined('MOODLE_INTERNAL') || die();


class status_submitted extends cmielement_submitted {

    
    public static function get_name() {
        return get_string('eventstatussubmitted', 'mod_scorm');
    }

    
    protected function validate_data() {
        parent::validate_data();

        if (!in_array($this->other['cmielement'],
                array('cmi.completion_status', 'cmi.core.lesson_status', 'cmi.success_status'))) {
            throw new \coding_exception(
                "The 'cmielement' must represents a valid CMI status element ({$this->other['cmielement']}).");
        }

        if (!in_array($this->other['cmivalue'],
                array('passed', 'completed', 'failed', 'incomplete', 'browsed', 'not attempted', 'unknown'))) {
            throw new \coding_exception(
                "The 'cmivalue' must represents a valid CMI status value ({$this->other['cmivalue']}).");
        }
    }
}
