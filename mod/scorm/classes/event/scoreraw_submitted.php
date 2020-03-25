<?php



namespace mod_scorm\event;
defined('MOODLE_INTERNAL') || die();


class scoreraw_submitted extends cmielement_submitted {

    
    public static function get_name() {
        return get_string('eventscorerawsubmitted', 'mod_scorm');
    }

    
    protected function validate_data() {
        parent::validate_data();

        if (!strstr($this->other['cmielement'], '.score.raw')) {
            throw new \coding_exception(
                "The 'cmielement' must represents a valid CMI raw score ({$this->other['cmielement']}).");
        }

            }
}
