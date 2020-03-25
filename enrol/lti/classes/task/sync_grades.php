<?php



namespace enrol_lti\task;


class sync_grades extends \core\task\scheduled_task {

    
    public function get_name() {
        return get_string('tasksyncgrades', 'enrol_lti');
    }

    
    public function execute() {
        global $DB, $CFG;

        require_once($CFG->dirroot . '/enrol/lti/ims-blti/OAuth.php');
        require_once($CFG->dirroot . '/enrol/lti/ims-blti/OAuthBody.php');
        require_once($CFG->dirroot . '/lib/completionlib.php');
        require_once($CFG->libdir . '/gradelib.php');
        require_once($CFG->dirroot . '/grade/querylib.php');

                if (!is_enabled_auth('lti')) {
            mtrace('Skipping task - ' . get_string('pluginnotenabled', 'auth', get_string('pluginname', 'auth_lti')));
            return true;
        }

                        if (!enrol_is_enabled('lti')) {
            mtrace('Skipping task - ' . get_string('enrolisdisabled', 'enrol_lti'));
            return true;
        }

                if ($tools = \enrol_lti\helper::get_lti_tools(array('status' => ENROL_INSTANCE_ENABLED, 'gradesync' => 1))) {
            foreach ($tools as $tool) {
                mtrace("Starting - Grade sync for shared tool '$tool->id' for the course '$tool->courseid'.");

                                $usercount = 0;
                $sendcount = 0;

                                if ($ltiusers = $DB->get_records('enrol_lti_users', array('toolid' => $tool->id), 'lastaccess DESC')) {
                    $completion = new \completion_info(get_course($tool->courseid));
                    foreach ($ltiusers as $ltiuser) {
                        $mtracecontent = "for the user '$ltiuser->userid' in the tool '$tool->id' for the course " .
                            "'$tool->courseid'";

                        $usercount = $usercount + 1;

                                                if (empty($ltiuser->serviceurl)) {
                            mtrace("Skipping - Empty serviceurl $mtracecontent.");
                            continue;
                        }

                                                if (empty($ltiuser->sourceid)) {
                            mtrace("Skipping - Empty sourceid $mtracecontent.");
                            continue;
                        }

                                                if (!$context = \context::instance_by_id($tool->contextid)) {
                            mtrace("Failed - Invalid contextid '$tool->contextid' for the tool '$tool->id'.");
                            continue;
                        }

                                                $grade = false;
                        if ($context->contextlevel == CONTEXT_COURSE) {
                                                        if ($tool->gradesynccompletion && !$completion->is_course_complete($ltiuser->userid)) {
                                mtrace("Skipping - Course not completed $mtracecontent.");
                                continue;
                            }

                                                        if ($grade = grade_get_course_grade($ltiuser->userid, $tool->courseid)) {
                                $grademax = floatval($grade->item->grademax);
                                $grade = $grade->grade;
                            }
                        } else if ($context->contextlevel == CONTEXT_MODULE) {
                            $cm = get_coursemodule_from_id(false, $context->instanceid, 0, false, MUST_EXIST);

                            if ($tool->gradesynccompletion) {
                                $data = $completion->get_data($cm, false, $ltiuser->userid);
                                if ($data->completionstate != COMPLETION_COMPLETE_PASS &&
                                    $data->completionstate != COMPLETION_COMPLETE) {
                                    mtrace("Skipping - Activity not completed $mtracecontent.");
                                    continue;
                                }
                            }

                            $grades = grade_get_grades($cm->course, 'mod', $cm->modname, $cm->instance, $ltiuser->userid);
                            if (!empty($grades->items[0]->grades)) {
                                $grade = reset($grades->items[0]->grades);
                                if (!empty($grade->item)) {
                                    $grademax = floatval($grade->item->grademax);
                                } else {
                                    $grademax = floatval($grades->items[0]->grademax);
                                }
                                $grade = $grade->grade;
                            }
                        }

                        if ($grade === false || $grade === null || strlen($grade) < 1) {
                            mtrace("Skipping - Invalid grade $mtracecontent.");
                            continue;
                        }

                                                if (empty($grademax)) {
                            mtrace("Skipping - Invalid grade $mtracecontent.");
                            continue;
                        }

                                                if ($grade == $ltiuser->lastgrade) {
                            mtrace("Not sent - The grade $mtracecontent was not sent as the grades are the same.");
                            continue;
                        }

                                                $floatgrade = $grade / $grademax;
                        $body = \enrol_lti\helper::create_service_body($ltiuser->sourceid, $floatgrade);

                        try {
                            $response = sendOAuthBodyPOST('POST', $ltiuser->serviceurl,
                                $ltiuser->consumerkey, $ltiuser->consumersecret, 'application/xml', $body);
                        } catch (\Exception $e) {
                            mtrace("Failed - The grade '$floatgrade' $mtracecontent failed to send.");
                            mtrace($e->getMessage());
                            continue;
                        }

                        if (strpos(strtolower($response), 'success') !== false) {
                            $DB->set_field('enrol_lti_users', 'lastgrade', intval($grade), array('id' => $ltiuser->id));
                            mtrace("Success - The grade '$floatgrade' $mtracecontent was sent.");
                            $sendcount = $sendcount + 1;
                        } else {
                            mtrace("Failed - The grade '$floatgrade' $mtracecontent failed to send.");
                        }

                    }
                }
                mtrace("Completed - Synced grades for tool '$tool->id' in the course '$tool->courseid'. " .
                    "Processed $usercount users; sent $sendcount grades.");
                mtrace("");
            }
        }
    }
}
