<?php


namespace assignfeedback_editpdf\task;

use core\task\scheduled_task;
use assignfeedback_editpdf\document_services;
use context_module;
use assign;


class convert_submissions extends scheduled_task {

    
    public function get_name() {
        return get_string('preparesubmissionsforannotation', 'assignfeedback_editpdf');
    }

    
    public function execute() {
        global $CFG, $DB;

        require_once($CFG->dirroot . '/mod/assign/locallib.php');

        $records = $DB->get_records('assignfeedback_editpdf_queue');

        $assignmentcache = array();

        foreach ($records as $record) {
            $submissionid = $record->submissionid;
            $submission = $DB->get_record('assign_submission', array('id' => $submissionid), '*', IGNORE_MISSING);
            if (!$submission) {
                                $DB->delete_records('assignfeedback_editpdf_queue', array('id' => $record->id));
                continue;
            }

            $assignmentid = $submission->assignment;
            $attemptnumber = $record->submissionattempt;

            if (empty($assignmentcache[$assignmentid])) {
                $cm = get_coursemodule_from_instance('assign', $assignmentid, 0, false, MUST_EXIST);
                $context = context_module::instance($cm->id);

                $assignment = new assign($context, null, null);
                $assignmentcache[$assignmentid] = $assignment;
            } else {
                $assignment = $assignmentcache[$assignmentid];
            }

            $users = array();
            if ($submission->userid) {
                array_push($users, $submission->userid);
            } else {
                $members = $assignment->get_submission_group_members($submission->groupid, true);

                foreach ($members as $member) {
                    array_push($users, $member->id);
                }
            }

            mtrace('Convert ' . count($users) . ' submission attempt(s) for assignment ' . $assignmentid);
            foreach ($users as $userid) {
                try {
                    document_services::get_page_images_for_attempt($assignment,
                                                                   $userid,
                                                                   $attemptnumber,
                                                                   true);
                    document_services::get_page_images_for_attempt($assignment,
                                                                   $userid,
                                                                   $attemptnumber,
                                                                   false);
                } catch (\moodle_exception $e) {
                    mtrace('Conversion failed with error:' . $e->errorcode);
                }
            }

            $DB->delete_records('assignfeedback_editpdf_queue', array('id' => $record->id));
        }
    }

}
