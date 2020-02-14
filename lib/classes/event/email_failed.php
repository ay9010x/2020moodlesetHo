<?php



namespace core\event;

defined('MOODLE_INTERNAL') || die();


class email_failed extends base {

    
    protected function init() {
        $this->data['crud'] = 'c';
        $this->data['edulevel'] = self::LEVEL_OTHER;
    }

    
    public static function get_name() {
        return get_string('eventemailfailed');
    }

    
    public function get_description() {
        return "Failed to send an email from the user with id '$this->userid' to the user with id '$this->relateduserid'
            due to the following error: \"{$this->other['errorinfo']}\".";
    }

    
    protected function get_legacy_logdata() {
        return array(SITEID, 'library', 'mailer', qualified_me(), 'ERROR: ' . $this->other['errorinfo']);
    }

    
    protected function validate_data() {
        parent::validate_data();

        if (!isset($this->relateduserid)) {
            throw new \coding_exception('The \'relateduserid\' must be set.');
        }
        if (!isset($this->other['subject'])) {
            throw new \coding_exception('The \'subject\' value must be set in other.');
        }
        if (!isset($this->other['message'])) {
            throw new \coding_exception('The \'message\' value must be set in other.');
        }
        if (!isset($this->other['errorinfo'])) {
            throw new \coding_exception('The \'errorinfo\' value must be set in other.');
        }
    }

    public static function get_other_mapping() {
        return false;
    }
}
