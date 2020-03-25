<?php


namespace assignfeedback_editpdf\event;


class observer {

    
    public static function submission_created(\mod_assign\event\submission_created $event) {
        global $DB;

        $submissionid = $event->other['submissionid'];
        $submissionattempt = $event->other['submissionattempt'];
        $fields = array( 'submissionid' => $submissionid, 'submissionattempt' => $submissionattempt);
        $record = (object) $fields;

        $exists = $DB->get_records('assignfeedback_editpdf_queue', $fields);
        if (!$exists) {
            $DB->insert_record('assignfeedback_editpdf_queue', $record);
        }
    }

    
    public static function submission_updated(\mod_assign\event\submission_updated $event) {
        global $DB;

        $submissionid = $event->other['submissionid'];
        $submissionattempt = $event->other['submissionattempt'];
        $fields = array( 'submissionid' => $submissionid, 'submissionattempt' => $submissionattempt);
        $record = (object) $fields;

        $exists = $DB->get_records('assignfeedback_editpdf_queue', $fields);
        if (!$exists) {
            $DB->insert_record('assignfeedback_editpdf_queue', $record);
        }
    }
}
