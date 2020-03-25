<?php



defined('MOODLE_INTERNAL') || die();


class mod_feedback_generator extends testing_module_generator {

    public function create_instance($record = null, array $options = null) {
        global $CFG;
        require_once($CFG->dirroot.'/mod/feedback/lib.php');
        $record = (object)(array)$record;

        if (!isset($record->anonymous)) {
            $record->anonymous = FEEDBACK_ANONYMOUS_YES;
        }
        if (!isset($record->email_notification)) {
            $record->email_notification = 0;
        }
        if (!isset($record->multiple_submit)) {
            $record->multiple_submit = 0;
        }
        if (!isset($record->autonumbering)) {
            $record->autonumbering = 0;
        }
        if (!isset($record->site_after_submit)) {
            $record->site_after_submit = '';
        }
        if (!isset($record->page_after_submit)) {
            $record->page_after_submit = 'This is page after submit';
        }
        if (!isset($record->page_after_submitformat)) {
            $record->page_after_submitformat = FORMAT_MOODLE;
        }
        if (!isset($record->publish_stats)) {
            $record->publish_stats = 0;
        }
        if (!isset($record->timeopen)) {
            $record->timeopen = 0;
        }
        if (!isset($record->timeclose)) {
            $record->timeclose = 0;
        }
        if (!isset($record->timemodified)) {
            $record->timemodified = time();
        }
        if (!isset($record->completionsubmit)) {
            $record->completionsubmit = 0;
        }

                $record->page_after_submit_editor['itemid'] = false;

        return parent::create_instance($record, (array)$options);
    }

}

