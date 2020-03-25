<?php



defined('MOODLE_INTERNAL') || die;

require_once("$CFG->libdir/externallib.php");
require_once("$CFG->dirroot/user/externallib.php");
require_once("$CFG->dirroot/mod/assign/locallib.php");


class mod_assign_external extends external_api {

    
    private static function generate_warning($assignmentid, $warningcode, $detail) {
        $warningmessages = array(
            'couldnotlock'=>'Could not lock the submission for this user.',
            'couldnotunlock'=>'Could not unlock the submission for this user.',
            'couldnotsubmitforgrading'=>'Could not submit assignment for grading.',
            'couldnotrevealidentities'=>'Could not reveal identities.',
            'couldnotgrantextensions'=>'Could not grant submission date extensions.',
            'couldnotrevert'=>'Could not revert submission to draft.',
            'invalidparameters'=>'Invalid parameters.',
            'couldnotsavesubmission'=>'Could not save submission.',
            'couldnotsavegrade'=>'Could not save grade.'
        );

        $message = $warningmessages[$warningcode];
        if (empty($message)) {
            $message = 'Unknown warning type.';
        }

        return array('item'=>$detail,
                     'itemid'=>$assignmentid,
                     'warningcode'=>$warningcode,
                     'message'=>$message);
    }

    
    public static function get_grades_parameters() {
        return new external_function_parameters(
            array(
                'assignmentids' => new external_multiple_structure(
                    new external_value(PARAM_INT, 'assignment id'),
                    '1 or more assignment ids',
                    VALUE_REQUIRED),
                'since' => new external_value(PARAM_INT,
                          'timestamp, only return records where timemodified >= since',
                          VALUE_DEFAULT, 0)
            )
        );
    }

    
    public static function get_grades($assignmentids, $since = 0) {
        global $DB;
        $params = self::validate_parameters(self::get_grades_parameters(),
                        array('assignmentids' => $assignmentids,
                              'since' => $since));

        $assignments = array();
        $warnings = array();
        $requestedassignmentids = $params['assignmentids'];

                $placeholders = array();
        list($sqlassignmentids, $placeholders) = $DB->get_in_or_equal($requestedassignmentids, SQL_PARAMS_NAMED);
        $sql = "SELECT cm.id, cm.instance FROM {course_modules} cm JOIN {modules} md ON md.id = cm.module ".
               "WHERE md.name = :modname AND cm.instance ".$sqlassignmentids;
        $placeholders['modname'] = 'assign';
        $cms = $DB->get_records_sql($sql, $placeholders);
        foreach ($cms as $cm) {
            try {
                $context = context_module::instance($cm->id);
                self::validate_context($context);
                require_capability('mod/assign:grade', $context);
            } catch (Exception $e) {
                $requestedassignmentids = array_diff($requestedassignmentids, array($cm->instance));
                $warning = array();
                $warning['item'] = 'assignment';
                $warning['itemid'] = $cm->instance;
                $warning['warningcode'] = '1';
                $warning['message'] = 'No access rights in module context';
                $warnings[] = $warning;
            }
        }

                if (count ($requestedassignmentids) > 0) {
            $placeholders = array();
            list($inorequalsql, $placeholders) = $DB->get_in_or_equal($requestedassignmentids, SQL_PARAMS_NAMED);

            $sql = "SELECT ag.id,
                           ag.assignment,
                           ag.userid,
                           ag.timecreated,
                           ag.timemodified,
                           ag.grader,
                           ag.grade,
                           ag.attemptnumber
                      FROM {assign_grades} ag, {assign_submission} s
                     WHERE s.assignment $inorequalsql
                       AND s.userid = ag.userid
                       AND s.latest = 1
                       AND s.attemptnumber = ag.attemptnumber
                       AND ag.timemodified  >= :since
                       AND ag.assignment = s.assignment
                  ORDER BY ag.assignment, ag.id";

            $placeholders['since'] = $params['since'];
            $rs = $DB->get_recordset_sql($sql, $placeholders);
            $currentassignmentid = null;
            $assignment = null;
            foreach ($rs as $rd) {
                $grade = array();
                $grade['id'] = $rd->id;
                $grade['userid'] = $rd->userid;
                $grade['timecreated'] = $rd->timecreated;
                $grade['timemodified'] = $rd->timemodified;
                $grade['grader'] = $rd->grader;
                $grade['attemptnumber'] = $rd->attemptnumber;
                $grade['grade'] = (string)$rd->grade;

                if (is_null($currentassignmentid) || ($rd->assignment != $currentassignmentid )) {
                    if (!is_null($assignment)) {
                        $assignments[] = $assignment;
                    }
                    $assignment = array();
                    $assignment['assignmentid'] = $rd->assignment;
                    $assignment['grades'] = array();
                    $requestedassignmentids = array_diff($requestedassignmentids, array($rd->assignment));
                }
                $assignment['grades'][] = $grade;

                $currentassignmentid = $rd->assignment;
            }
            if (!is_null($assignment)) {
                $assignments[] = $assignment;
            }
            $rs->close();
        }
        foreach ($requestedassignmentids as $assignmentid) {
            $warning = array();
            $warning['item'] = 'assignment';
            $warning['itemid'] = $assignmentid;
            $warning['warningcode'] = '3';
            $warning['message'] = 'No grades found';
            $warnings[] = $warning;
        }

        $result = array();
        $result['assignments'] = $assignments;
        $result['warnings'] = $warnings;
        return $result;
    }

    
    private static function get_grade_structure($required = VALUE_REQUIRED) {
        return new external_single_structure(
            array(
                'id'                => new external_value(PARAM_INT, 'grade id'),
                'assignment'        => new external_value(PARAM_INT, 'assignment id', VALUE_OPTIONAL),
                'userid'            => new external_value(PARAM_INT, 'student id'),
                'attemptnumber'     => new external_value(PARAM_INT, 'attempt number'),
                'timecreated'       => new external_value(PARAM_INT, 'grade creation time'),
                'timemodified'      => new external_value(PARAM_INT, 'grade last modified time'),
                'grader'            => new external_value(PARAM_INT, 'grader'),
                'grade'             => new external_value(PARAM_TEXT, 'grade'),
                'gradefordisplay'   => new external_value(PARAM_RAW, 'grade rendered into a format suitable for display',
                                                            VALUE_OPTIONAL),
            ), 'grade information', $required
        );
    }

    
    private static function assign_grades() {
        return new external_single_structure(
            array (
                'assignmentid'  => new external_value(PARAM_INT, 'assignment id'),
                'grades'        => new external_multiple_structure(self::get_grade_structure())
            )
        );
    }

    
    public static function get_grades_returns() {
        return new external_single_structure(
            array(
                'assignments' => new external_multiple_structure(self::assign_grades(), 'list of assignment grade information'),
                'warnings'      => new external_warnings('item is always \'assignment\'',
                    'when errorcode is 3 then itemid is an assignment id. When errorcode is 1, itemid is a course module id',
                    'errorcode can be 3 (no grades found) or 1 (no permission to get grades)')
            )
        );
    }

    
    public static function get_assignments_parameters() {
        return new external_function_parameters(
            array(
                'courseids' => new external_multiple_structure(
                    new external_value(PARAM_INT, 'course id, empty for retrieving all the courses where the user is enroled in'),
                    '0 or more course ids',
                    VALUE_DEFAULT, array()
                ),
                'capabilities'  => new external_multiple_structure(
                    new external_value(PARAM_CAPABILITY, 'capability'),
                    'list of capabilities used to filter courses',
                    VALUE_DEFAULT, array()
                ),
                'includenotenrolledcourses' => new external_value(PARAM_BOOL, 'whether to return courses that the user can see
                                                                    even if is not enroled in. This requires the parameter courseids
                                                                    to not be empty.', VALUE_DEFAULT, false)
            )
        );
    }

    
    public static function get_assignments($courseids = array(), $capabilities = array(), $includenotenrolledcourses = false) {
        global $USER, $DB, $CFG;

        $params = self::validate_parameters(
            self::get_assignments_parameters(),
            array(
                'courseids' => $courseids,
                'capabilities' => $capabilities,
                'includenotenrolledcourses' => $includenotenrolledcourses
            )
        );

        $warnings = array();
        $courses = array();
        $fields = 'sortorder,shortname,fullname,timemodified';

                if (empty($params['courseids'])) {
            $courses = enrol_get_users_courses($USER->id, true, $fields);
            $courseids = array_keys($courses);
        } else if ($includenotenrolledcourses) {
                        $courseids = $params['courseids'];
        } else {
                        $mycourses = enrol_get_users_courses($USER->id, true, $fields);
            $mycourseids = array_keys($mycourses);

            foreach ($params['courseids'] as $courseid) {
                if (!in_array($courseid, $mycourseids)) {
                    unset($courses[$courseid]);
                    $warnings[] = array(
                        'item' => 'course',
                        'itemid' => $courseid,
                        'warningcode' => '2',
                        'message' => 'User is not enrolled or does not have requested capability'
                    );
                } else {
                    $courses[$courseid] = $mycourses[$courseid];
                }
            }
            $courseids = array_keys($courses);
        }

        foreach ($courseids as $cid) {

            try {
                $context = context_course::instance($cid);
                self::validate_context($context);

                                if (!isset($courses[$cid])) {
                    $courses[$cid] = get_course($cid);
                }
                $courses[$cid]->contextid = $context->id;
            } catch (Exception $e) {
                unset($courses[$cid]);
                $warnings[] = array(
                    'item' => 'course',
                    'itemid' => $cid,
                    'warningcode' => '1',
                    'message' => 'No access rights in course context '.$e->getMessage()
                );
                continue;
            }
            if (count($params['capabilities']) > 0 && !has_all_capabilities($params['capabilities'], $context)) {
                unset($courses[$cid]);
            }
        }
        $extrafields='m.id as assignmentid, ' .
                     'm.course, ' .
                     'm.nosubmissions, ' .
                     'm.submissiondrafts, ' .
                     'm.sendnotifications, '.
                     'm.sendlatenotifications, ' .
                     'm.sendstudentnotifications, ' .
                     'm.duedate, ' .
                     'm.allowsubmissionsfromdate, '.
                     'm.grade, ' .
                     'm.timemodified, '.
                     'm.completionsubmit, ' .
                     'm.cutoffdate, ' .
                     'm.teamsubmission, ' .
                     'm.requireallteammemberssubmit, '.
                     'm.teamsubmissiongroupingid, ' .
                     'm.blindmarking, ' .
                     'm.revealidentities, ' .
                     'm.attemptreopenmethod, '.
                     'm.maxattempts, ' .
                     'm.markingworkflow, ' .
                     'm.markingallocation, ' .
                     'm.requiresubmissionstatement, '.
                     'm.intro, '.
                     'm.introformat';
        $coursearray = array();
        foreach ($courses as $id => $course) {
            $assignmentarray = array();
                        if ($modules = get_coursemodules_in_course('assign', $courses[$id]->id, $extrafields)) {
                foreach ($modules as $module) {
                    $context = context_module::instance($module->id);
                    try {
                        self::validate_context($context);
                        require_capability('mod/assign:view', $context);
                    } catch (Exception $e) {
                        $warnings[] = array(
                            'item' => 'module',
                            'itemid' => $module->id,
                            'warningcode' => '1',
                            'message' => 'No access rights in module context'
                        );
                        continue;
                    }
                    $configrecords = $DB->get_recordset('assign_plugin_config', array('assignment' => $module->assignmentid));
                    $configarray = array();
                    foreach ($configrecords as $configrecord) {
                        $configarray[] = array(
                            'id' => $configrecord->id,
                            'assignment' => $configrecord->assignment,
                            'plugin' => $configrecord->plugin,
                            'subtype' => $configrecord->subtype,
                            'name' => $configrecord->name,
                            'value' => $configrecord->value
                        );
                    }
                    $configrecords->close();

                    $assignment = array(
                        'id' => $module->assignmentid,
                        'cmid' => $module->id,
                        'course' => $module->course,
                        'name' => $module->name,
                        'nosubmissions' => $module->nosubmissions,
                        'submissiondrafts' => $module->submissiondrafts,
                        'sendnotifications' => $module->sendnotifications,
                        'sendlatenotifications' => $module->sendlatenotifications,
                        'sendstudentnotifications' => $module->sendstudentnotifications,
                        'duedate' => $module->duedate,
                        'allowsubmissionsfromdate' => $module->allowsubmissionsfromdate,
                        'grade' => $module->grade,
                        'timemodified' => $module->timemodified,
                        'completionsubmit' => $module->completionsubmit,
                        'cutoffdate' => $module->cutoffdate,
                        'teamsubmission' => $module->teamsubmission,
                        'requireallteammemberssubmit' => $module->requireallteammemberssubmit,
                        'teamsubmissiongroupingid' => $module->teamsubmissiongroupingid,
                        'blindmarking' => $module->blindmarking,
                        'revealidentities' => $module->revealidentities,
                        'attemptreopenmethod' => $module->attemptreopenmethod,
                        'maxattempts' => $module->maxattempts,
                        'markingworkflow' => $module->markingworkflow,
                        'markingallocation' => $module->markingallocation,
                        'requiresubmissionstatement' => $module->requiresubmissionstatement,
                        'configs' => $configarray
                    );

                                        $assign = new assign($context, null, null);

                    if ($assign->show_intro()) {

                        list($assignment['intro'], $assignment['introformat']) = external_format_text($module->intro,
                            $module->introformat, $context->id, 'mod_assign', 'intro', null);

                        $fs = get_file_storage();
                        if ($files = $fs->get_area_files($context->id, 'mod_assign', ASSIGN_INTROATTACHMENT_FILEAREA,
                                                            0, 'timemodified', false)) {

                            $assignment['introattachments'] = array();
                            foreach ($files as $file) {
                                $filename = $file->get_filename();

                                $assignment['introattachments'][] = array(
                                    'filename' => $filename,
                                    'mimetype' => $file->get_mimetype(),
                                    'fileurl'  => moodle_url::make_webservice_pluginfile_url(
                                        $context->id, 'mod_assign', ASSIGN_INTROATTACHMENT_FILEAREA, 0, '/', $filename)->out(false)
                                );
                            }
                        }
                    }

                    $assignmentarray[] = $assignment;
                }
            }
            $coursearray[]= array(
                'id' => $courses[$id]->id,
                'fullname' => external_format_string($courses[$id]->fullname, $course->contextid),
                'shortname' => external_format_string($courses[$id]->shortname, $course->contextid),
                'timemodified' => $courses[$id]->timemodified,
                'assignments' => $assignmentarray
            );
        }

        $result = array(
            'courses' => $coursearray,
            'warnings' => $warnings
        );
        return $result;
    }

    
    private static function get_assignments_assignment_structure() {
        return new external_single_structure(
            array(
                'id' => new external_value(PARAM_INT, 'assignment id'),
                'cmid' => new external_value(PARAM_INT, 'course module id'),
                'course' => new external_value(PARAM_INT, 'course id'),
                'name' => new external_value(PARAM_TEXT, 'assignment name'),
                'nosubmissions' => new external_value(PARAM_INT, 'no submissions'),
                'submissiondrafts' => new external_value(PARAM_INT, 'submissions drafts'),
                'sendnotifications' => new external_value(PARAM_INT, 'send notifications'),
                'sendlatenotifications' => new external_value(PARAM_INT, 'send notifications'),
                'sendstudentnotifications' => new external_value(PARAM_INT, 'send student notifications (default)'),
                'duedate' => new external_value(PARAM_INT, 'assignment due date'),
                'allowsubmissionsfromdate' => new external_value(PARAM_INT, 'allow submissions from date'),
                'grade' => new external_value(PARAM_INT, 'grade type'),
                'timemodified' => new external_value(PARAM_INT, 'last time assignment was modified'),
                'completionsubmit' => new external_value(PARAM_INT, 'if enabled, set activity as complete following submission'),
                'cutoffdate' => new external_value(PARAM_INT, 'date after which submission is not accepted without an extension'),
                'teamsubmission' => new external_value(PARAM_INT, 'if enabled, students submit as a team'),
                'requireallteammemberssubmit' => new external_value(PARAM_INT, 'if enabled, all team members must submit'),
                'teamsubmissiongroupingid' => new external_value(PARAM_INT, 'the grouping id for the team submission groups'),
                'blindmarking' => new external_value(PARAM_INT, 'if enabled, hide identities until reveal identities actioned'),
                'revealidentities' => new external_value(PARAM_INT, 'show identities for a blind marking assignment'),
                'attemptreopenmethod' => new external_value(PARAM_TEXT, 'method used to control opening new attempts'),
                'maxattempts' => new external_value(PARAM_INT, 'maximum number of attempts allowed'),
                'markingworkflow' => new external_value(PARAM_INT, 'enable marking workflow'),
                'markingallocation' => new external_value(PARAM_INT, 'enable marking allocation'),
                'requiresubmissionstatement' => new external_value(PARAM_INT, 'student must accept submission statement'),
                'configs' => new external_multiple_structure(self::get_assignments_config_structure(), 'configuration settings'),
                'intro' => new external_value(PARAM_RAW,
                    'assignment intro, not allways returned because it deppends on the activity configuration', VALUE_OPTIONAL),
                'introformat' => new external_format_value('intro', VALUE_OPTIONAL),
                'introattachments' => new external_multiple_structure(
                    new external_single_structure(
                        array (
                            'filename' => new external_value(PARAM_FILE, 'file name'),
                            'mimetype' => new external_value(PARAM_RAW, 'mime type'),
                            'fileurl'  => new external_value(PARAM_URL, 'file download url')
                        )
                    ), 'intro attachments files', VALUE_OPTIONAL
                )
            ), 'assignment information object');
    }

    
    private static function get_assignments_config_structure() {
        return new external_single_structure(
            array(
                'id' => new external_value(PARAM_INT, 'assign_plugin_config id'),
                'assignment' => new external_value(PARAM_INT, 'assignment id'),
                'plugin' => new external_value(PARAM_TEXT, 'plugin'),
                'subtype' => new external_value(PARAM_TEXT, 'subtype'),
                'name' => new external_value(PARAM_TEXT, 'name'),
                'value' => new external_value(PARAM_TEXT, 'value')
            ), 'assignment configuration object'
        );
    }

    
    private static function get_assignments_course_structure() {
        return new external_single_structure(
            array(
                'id' => new external_value(PARAM_INT, 'course id'),
                'fullname' => new external_value(PARAM_TEXT, 'course full name'),
                'shortname' => new external_value(PARAM_TEXT, 'course short name'),
                'timemodified' => new external_value(PARAM_INT, 'last time modified'),
                'assignments' => new external_multiple_structure(self::get_assignments_assignment_structure(), 'assignment info')
              ), 'course information object'
        );
    }

    
    public static function get_assignments_returns() {
        return new external_single_structure(
            array(
                'courses' => new external_multiple_structure(self::get_assignments_course_structure(), 'list of courses'),
                'warnings'  => new external_warnings('item can be \'course\' (errorcode 1 or 2) or \'module\' (errorcode 1)',
                    'When item is a course then itemid is a course id. When the item is a module then itemid is a module id',
                    'errorcode can be 1 (no access rights) or 2 (not enrolled or no permissions)')
            )
        );
    }

    
    private static function get_plugins_data($assign, $assignplugins, $item) {
        global $CFG;

        $plugins = array();
        $fs = get_file_storage();

        foreach ($assignplugins as $assignplugin) {

            if (!$assignplugin->is_enabled() or !$assignplugin->is_visible()) {
                continue;
            }

            $plugin = array(
                'name' => $assignplugin->get_name(),
                'type' => $assignplugin->get_type()
            );
                        $component = $assignplugin->get_subtype().'_'.$assignplugin->get_type();

            $fileareas = $assignplugin->get_file_areas();
            foreach ($fileareas as $filearea => $name) {
                $fileareainfo = array('area' => $filearea);
                $files = $fs->get_area_files(
                    $assign->get_context()->id,
                    $component,
                    $filearea,
                    $item->id,
                    "timemodified",
                    false
                );
                foreach ($files as $file) {
                    $filepath = $file->get_filepath().$file->get_filename();
                    $fileurl = file_encode_url($CFG->wwwroot . '/webservice/pluginfile.php', '/' . $assign->get_context()->id .
                        '/' . $component. '/'. $filearea . '/' . $item->id . $filepath);
                    $fileinfo = array(
                        'filepath' => $filepath,
                        'fileurl' => $fileurl
                        );
                    $fileareainfo['files'][] = $fileinfo;
                }
                $plugin['fileareas'][] = $fileareainfo;
            }

            $editorfields = $assignplugin->get_editor_fields();
            foreach ($editorfields as $name => $description) {
                $editorfieldinfo = array(
                    'name' => $name,
                    'description' => $description,
                    'text' => $assignplugin->get_editor_text($name, $item->id),
                    'format' => $assignplugin->get_editor_format($name, $item->id)
                );

                                foreach ($fileareas as $filearea => $name) {
                    list($editorfieldinfo['text'], $editorfieldinfo['format']) = external_format_text(
                        $editorfieldinfo['text'], $editorfieldinfo['format'], $assign->get_context()->id,
                        $component, $filearea, $item->id);
                }

                $plugin['editorfields'][] = $editorfieldinfo;
            }
            $plugins[] = $plugin;
        }
        return $plugins;
    }

    
    public static function get_submissions_parameters() {
        return new external_function_parameters(
            array(
                'assignmentids' => new external_multiple_structure(
                    new external_value(PARAM_INT, 'assignment id'),
                    '1 or more assignment ids',
                    VALUE_REQUIRED),
                'status' => new external_value(PARAM_ALPHA, 'status', VALUE_DEFAULT, ''),
                'since' => new external_value(PARAM_INT, 'submitted since', VALUE_DEFAULT, 0),
                'before' => new external_value(PARAM_INT, 'submitted before', VALUE_DEFAULT, 0)
            )
        );
    }

    
    public static function get_submissions($assignmentids, $status = '', $since = 0, $before = 0) {
        global $DB, $CFG;

        $params = self::validate_parameters(self::get_submissions_parameters(),
                        array('assignmentids' => $assignmentids,
                              'status' => $status,
                              'since' => $since,
                              'before' => $before));

        $warnings = array();
        $assignments = array();

                $placeholders = array();
        list($inorequalsql, $placeholders) = $DB->get_in_or_equal($params['assignmentids'], SQL_PARAMS_NAMED);
        $sql = "SELECT cm.id, cm.instance FROM {course_modules} cm JOIN {modules} md ON md.id = cm.module ".
               "WHERE md.name = :modname AND cm.instance ".$inorequalsql;
        $placeholders['modname'] = 'assign';
        $cms = $DB->get_records_sql($sql, $placeholders);
        $assigns = array();
        foreach ($cms as $cm) {
            try {
                $context = context_module::instance($cm->id);
                self::validate_context($context);
                require_capability('mod/assign:grade', $context);
                $assign = new assign($context, null, null);
                $assigns[] = $assign;
            } catch (Exception $e) {
                $warnings[] = array(
                    'item' => 'assignment',
                    'itemid' => $cm->instance,
                    'warningcode' => '1',
                    'message' => 'No access rights in module context'
                );
            }
        }

        foreach ($assigns as $assign) {
            $submissions = array();
            $placeholders = array('assignid1' => $assign->get_instance()->id,
                                  'assignid2' => $assign->get_instance()->id);

            $submissionmaxattempt = 'SELECT mxs.userid, MAX(mxs.attemptnumber) AS maxattempt
                                     FROM {assign_submission} mxs
                                     WHERE mxs.assignment = :assignid1 GROUP BY mxs.userid';

            $sql = "SELECT mas.id, mas.assignment,mas.userid,".
                   "mas.timecreated,mas.timemodified,mas.status,mas.groupid,mas.attemptnumber ".
                   "FROM {assign_submission} mas ".
                   "JOIN ( " . $submissionmaxattempt . " ) smx ON mas.userid = smx.userid ".
                   "WHERE mas.assignment = :assignid2 AND mas.attemptnumber = smx.maxattempt";

            if (!empty($params['status'])) {
                $placeholders['status'] = $params['status'];
                $sql = $sql." AND mas.status = :status";
            }
            if (!empty($params['before'])) {
                $placeholders['since'] = $params['since'];
                $placeholders['before'] = $params['before'];
                $sql = $sql." AND mas.timemodified BETWEEN :since AND :before";
            } else {
                $placeholders['since'] = $params['since'];
                $sql = $sql." AND mas.timemodified >= :since";
            }

            $submissionrecords = $DB->get_records_sql($sql, $placeholders);

            if (!empty($submissionrecords)) {
                $submissionplugins = $assign->get_submission_plugins();
                foreach ($submissionrecords as $submissionrecord) {
                    $submission = array(
                        'id' => $submissionrecord->id,
                        'userid' => $submissionrecord->userid,
                        'timecreated' => $submissionrecord->timecreated,
                        'timemodified' => $submissionrecord->timemodified,
                        'status' => $submissionrecord->status,
                        'attemptnumber' => $submissionrecord->attemptnumber,
                        'groupid' => $submissionrecord->groupid,
                        'plugins' => self::get_plugins_data($assign, $submissionplugins, $submissionrecord)
                    );
                    $submissions[] = $submission;
                }
            } else {
                $warnings[] = array(
                    'item' => 'module',
                    'itemid' => $assign->get_instance()->id,
                    'warningcode' => '3',
                    'message' => 'No submissions found'
                );
            }

            $assignments[] = array(
                'assignmentid' => $assign->get_instance()->id,
                'submissions' => $submissions
            );

        }

        $result = array(
            'assignments' => $assignments,
            'warnings' => $warnings
        );
        return $result;
    }

    
    private static function get_plugin_structure() {
        return new external_single_structure(
            array(
                'type' => new external_value(PARAM_TEXT, 'submission plugin type'),
                'name' => new external_value(PARAM_TEXT, 'submission plugin name'),
                'fileareas' => new external_multiple_structure(
                    new external_single_structure(
                        array (
                            'area' => new external_value (PARAM_TEXT, 'file area'),
                            'files' => new external_multiple_structure(
                                new external_single_structure(
                                    array (
                                        'filepath' => new external_value (PARAM_TEXT, 'file path'),
                                        'fileurl' => new external_value (PARAM_URL, 'file download url',
                                            VALUE_OPTIONAL)
                                    )
                                ), 'files', VALUE_OPTIONAL
                            )
                        )
                    ), 'fileareas', VALUE_OPTIONAL
                ),
                'editorfields' => new external_multiple_structure(
                    new external_single_structure(
                        array(
                            'name' => new external_value(PARAM_TEXT, 'field name'),
                            'description' => new external_value(PARAM_TEXT, 'field description'),
                            'text' => new external_value (PARAM_RAW, 'field value'),
                            'format' => new external_format_value ('text')
                        )
                    )
                    , 'editorfields', VALUE_OPTIONAL
                )
            )
        );
    }

    
    private static function get_submission_structure($required = VALUE_REQUIRED) {
        return new external_single_structure(
            array(
                'id' => new external_value(PARAM_INT, 'submission id'),
                'userid' => new external_value(PARAM_INT, 'student id'),
                'attemptnumber' => new external_value(PARAM_INT, 'attempt number'),
                'timecreated' => new external_value(PARAM_INT, 'submission creation time'),
                'timemodified' => new external_value(PARAM_INT, 'submission last modified time'),
                'status' => new external_value(PARAM_TEXT, 'submission status'),
                'groupid' => new external_value(PARAM_INT, 'group id'),
                'assignment' => new external_value(PARAM_INT, 'assignment id', VALUE_OPTIONAL),
                'latest' => new external_value(PARAM_INT, 'latest attempt', VALUE_OPTIONAL),
                'plugins' => new external_multiple_structure(self::get_plugin_structure(), 'plugins', VALUE_OPTIONAL)
            ), 'submission info', $required
        );
    }

    
    private static function get_submissions_structure() {
        return new external_single_structure(
            array (
                'assignmentid' => new external_value(PARAM_INT, 'assignment id'),
                'submissions' => new external_multiple_structure(self::get_submission_structure())
            )
        );
    }

    
    public static function get_submissions_returns() {
        return new external_single_structure(
            array(
                'assignments' => new external_multiple_structure(self::get_submissions_structure(), 'assignment submissions'),
                'warnings' => new external_warnings()
            )
        );
    }

    
    public static function set_user_flags_parameters() {
        return new external_function_parameters(
            array(
                'assignmentid'    => new external_value(PARAM_INT, 'assignment id'),
                'userflags' => new external_multiple_structure(
                    new external_single_structure(
                        array(
                            'userid'           => new external_value(PARAM_INT, 'student id'),
                            'locked'           => new external_value(PARAM_INT, 'locked', VALUE_OPTIONAL),
                            'mailed'           => new external_value(PARAM_INT, 'mailed', VALUE_OPTIONAL),
                            'extensionduedate' => new external_value(PARAM_INT, 'extension due date', VALUE_OPTIONAL),
                            'workflowstate'    => new external_value(PARAM_ALPHA, 'marking workflow state', VALUE_OPTIONAL),
                            'allocatedmarker'  => new external_value(PARAM_INT, 'allocated marker', VALUE_OPTIONAL)
                        )
                    )
                )
            )
        );
    }

    
    public static function set_user_flags($assignmentid, $userflags = array()) {
        global $CFG, $DB;

        $params = self::validate_parameters(self::set_user_flags_parameters(),
                                            array('assignmentid' => $assignmentid,
                                                  'userflags' => $userflags));

                $cm = get_coursemodule_from_instance('assign', $params['assignmentid'], 0, false, MUST_EXIST);
        $context = context_module::instance($cm->id);
        self::validate_context($context);
        require_capability('mod/assign:grade', $context);
        $assign = new assign($context, null, null);

        $results = array();
        foreach ($params['userflags'] as $userflag) {
            $success = true;
            $result = array();

            $record = $assign->get_user_flags($userflag['userid'], false);
            if ($record) {
                if (isset($userflag['locked'])) {
                    $record->locked = $userflag['locked'];
                }
                if (isset($userflag['mailed'])) {
                    $record->mailed = $userflag['mailed'];
                }
                if (isset($userflag['extensionduedate'])) {
                    $record->extensionduedate = $userflag['extensionduedate'];
                }
                if (isset($userflag['workflowstate'])) {
                    $record->workflowstate = $userflag['workflowstate'];
                }
                if (isset($userflag['allocatedmarker'])) {
                    $record->allocatedmarker = $userflag['allocatedmarker'];
                }
                if ($assign->update_user_flags($record)) {
                    $result['id'] = $record->id;
                    $result['userid'] = $userflag['userid'];
                } else {
                    $result['id'] = $record->id;
                    $result['userid'] = $userflag['userid'];
                    $result['errormessage'] = 'Record created but values could not be set';
                }
            } else {
                $record = $assign->get_user_flags($userflag['userid'], true);
                $setfields = isset($userflag['locked'])
                             || isset($userflag['mailed'])
                             || isset($userflag['extensionduedate'])
                             || isset($userflag['workflowstate'])
                             || isset($userflag['allocatedmarker']);
                if ($record) {
                    if ($setfields) {
                        if (isset($userflag['locked'])) {
                            $record->locked = $userflag['locked'];
                        }
                        if (isset($userflag['mailed'])) {
                            $record->mailed = $userflag['mailed'];
                        }
                        if (isset($userflag['extensionduedate'])) {
                            $record->extensionduedate = $userflag['extensionduedate'];
                        }
                        if (isset($userflag['workflowstate'])) {
                            $record->workflowstate = $userflag['workflowstate'];
                        }
                        if (isset($userflag['allocatedmarker'])) {
                            $record->allocatedmarker = $userflag['allocatedmarker'];
                        }
                        if ($assign->update_user_flags($record)) {
                            $result['id'] = $record->id;
                            $result['userid'] = $userflag['userid'];
                        } else {
                            $result['id'] = $record->id;
                            $result['userid'] = $userflag['userid'];
                            $result['errormessage'] = 'Record created but values could not be set';
                        }
                    } else {
                        $result['id'] = $record->id;
                        $result['userid'] = $userflag['userid'];
                    }
                } else {
                    $result['id'] = -1;
                    $result['userid'] = $userflag['userid'];
                    $result['errormessage'] = 'Record could not be created';
                }
            }

            $results[] = $result;
        }
        return $results;
    }

    
    public static function set_user_flags_returns() {
        return new external_multiple_structure(
            new external_single_structure(
                array(
                    'id' => new external_value(PARAM_INT, 'id of record if successful, -1 for failure'),
                    'userid' => new external_value(PARAM_INT, 'userid of record'),
                    'errormessage' => new external_value(PARAM_TEXT, 'Failure error message', VALUE_OPTIONAL)
                )
            )
        );
    }

    
    public static function get_user_flags_parameters() {
        return new external_function_parameters(
            array(
                'assignmentids' => new external_multiple_structure(
                    new external_value(PARAM_INT, 'assignment id'),
                    '1 or more assignment ids',
                    VALUE_REQUIRED)
            )
        );
    }

    
    public static function get_user_flags($assignmentids) {
        global $DB;
        $params = self::validate_parameters(self::get_user_flags_parameters(),
                        array('assignmentids' => $assignmentids));

        $assignments = array();
        $warnings = array();
        $requestedassignmentids = $params['assignmentids'];

                $placeholders = array();
        list($sqlassignmentids, $placeholders) = $DB->get_in_or_equal($requestedassignmentids, SQL_PARAMS_NAMED);
        $sql = "SELECT cm.id, cm.instance FROM {course_modules} cm JOIN {modules} md ON md.id = cm.module ".
               "WHERE md.name = :modname AND cm.instance ".$sqlassignmentids;
        $placeholders['modname'] = 'assign';
        $cms = $DB->get_records_sql($sql, $placeholders);
        foreach ($cms as $cm) {
            try {
                $context = context_module::instance($cm->id);
                self::validate_context($context);
                require_capability('mod/assign:grade', $context);
            } catch (Exception $e) {
                $requestedassignmentids = array_diff($requestedassignmentids, array($cm->instance));
                $warning = array();
                $warning['item'] = 'assignment';
                $warning['itemid'] = $cm->instance;
                $warning['warningcode'] = '1';
                $warning['message'] = 'No access rights in module context';
                $warnings[] = $warning;
            }
        }

                if (count ($requestedassignmentids) > 0) {
            $placeholders = array();
            list($inorequalsql, $placeholders) = $DB->get_in_or_equal($requestedassignmentids, SQL_PARAMS_NAMED);

            $sql = "SELECT auf.id,auf.assignment,auf.userid,auf.locked,auf.mailed,".
                   "auf.extensionduedate,auf.workflowstate,auf.allocatedmarker ".
                   "FROM {assign_user_flags} auf ".
                   "WHERE auf.assignment ".$inorequalsql.
                   " ORDER BY auf.assignment, auf.id";

            $rs = $DB->get_recordset_sql($sql, $placeholders);
            $currentassignmentid = null;
            $assignment = null;
            foreach ($rs as $rd) {
                $userflag = array();
                $userflag['id'] = $rd->id;
                $userflag['userid'] = $rd->userid;
                $userflag['locked'] = $rd->locked;
                $userflag['mailed'] = $rd->mailed;
                $userflag['extensionduedate'] = $rd->extensionduedate;
                $userflag['workflowstate'] = $rd->workflowstate;
                $userflag['allocatedmarker'] = $rd->allocatedmarker;

                if (is_null($currentassignmentid) || ($rd->assignment != $currentassignmentid )) {
                    if (!is_null($assignment)) {
                        $assignments[] = $assignment;
                    }
                    $assignment = array();
                    $assignment['assignmentid'] = $rd->assignment;
                    $assignment['userflags'] = array();
                    $requestedassignmentids = array_diff($requestedassignmentids, array($rd->assignment));
                }
                $assignment['userflags'][] = $userflag;

                $currentassignmentid = $rd->assignment;
            }
            if (!is_null($assignment)) {
                $assignments[] = $assignment;
            }
            $rs->close();

        }

        foreach ($requestedassignmentids as $assignmentid) {
            $warning = array();
            $warning['item'] = 'assignment';
            $warning['itemid'] = $assignmentid;
            $warning['warningcode'] = '3';
            $warning['message'] = 'No user flags found';
            $warnings[] = $warning;
        }

        $result = array();
        $result['assignments'] = $assignments;
        $result['warnings'] = $warnings;
        return $result;
    }

    
    private static function assign_user_flags() {
        return new external_single_structure(
            array (
                'assignmentid'    => new external_value(PARAM_INT, 'assignment id'),
                'userflags'   => new external_multiple_structure(new external_single_structure(
                        array(
                            'id'               => new external_value(PARAM_INT, 'user flag id'),
                            'userid'           => new external_value(PARAM_INT, 'student id'),
                            'locked'           => new external_value(PARAM_INT, 'locked'),
                            'mailed'           => new external_value(PARAM_INT, 'mailed'),
                            'extensionduedate' => new external_value(PARAM_INT, 'extension due date'),
                            'workflowstate'    => new external_value(PARAM_ALPHA, 'marking workflow state', VALUE_OPTIONAL),
                            'allocatedmarker'  => new external_value(PARAM_INT, 'allocated marker')
                        )
                    )
                )
            )
        );
    }

    
    public static function get_user_flags_returns() {
        return new external_single_structure(
            array(
                'assignments' => new external_multiple_structure(self::assign_user_flags(), 'list of assign user flag information'),
                'warnings'      => new external_warnings('item is always \'assignment\'',
                    'when errorcode is 3 then itemid is an assignment id. When errorcode is 1, itemid is a course module id',
                    'errorcode can be 3 (no user flags found) or 1 (no permission to get user flags)')
            )
        );
    }

    
    public static function get_user_mappings_parameters() {
        return new external_function_parameters(
            array(
                'assignmentids' => new external_multiple_structure(
                    new external_value(PARAM_INT, 'assignment id'),
                    '1 or more assignment ids',
                    VALUE_REQUIRED)
            )
        );
    }

    
    public static function get_user_mappings($assignmentids) {
        global $DB;
        $params = self::validate_parameters(self::get_user_mappings_parameters(),
                        array('assignmentids' => $assignmentids));

        $assignments = array();
        $warnings = array();
        $requestedassignmentids = $params['assignmentids'];

                $placeholders = array();
        list($sqlassignmentids, $placeholders) = $DB->get_in_or_equal($requestedassignmentids, SQL_PARAMS_NAMED);
        $sql = "SELECT cm.id, cm.instance FROM {course_modules} cm JOIN {modules} md ON md.id = cm.module ".
               "WHERE md.name = :modname AND cm.instance ".$sqlassignmentids;
        $placeholders['modname'] = 'assign';
        $cms = $DB->get_records_sql($sql, $placeholders);
        foreach ($cms as $cm) {
            try {
                $context = context_module::instance($cm->id);
                self::validate_context($context);
                require_capability('mod/assign:revealidentities', $context);
            } catch (Exception $e) {
                $requestedassignmentids = array_diff($requestedassignmentids, array($cm->instance));
                $warning = array();
                $warning['item'] = 'assignment';
                $warning['itemid'] = $cm->instance;
                $warning['warningcode'] = '1';
                $warning['message'] = 'No access rights in module context';
                $warnings[] = $warning;
            }
        }

                if (count ($requestedassignmentids) > 0) {
            $placeholders = array();
            list($inorequalsql, $placeholders) = $DB->get_in_or_equal($requestedassignmentids, SQL_PARAMS_NAMED);

            $sql = "SELECT aum.id,aum.assignment,aum.userid ".
                   "FROM {assign_user_mapping} aum ".
                   "WHERE aum.assignment ".$inorequalsql.
                   " ORDER BY aum.assignment, aum.id";

            $rs = $DB->get_recordset_sql($sql, $placeholders);
            $currentassignmentid = null;
            $assignment = null;
            foreach ($rs as $rd) {
                $mapping = array();
                $mapping['id'] = $rd->id;
                $mapping['userid'] = $rd->userid;

                if (is_null($currentassignmentid) || ($rd->assignment != $currentassignmentid )) {
                    if (!is_null($assignment)) {
                        $assignments[] = $assignment;
                    }
                    $assignment = array();
                    $assignment['assignmentid'] = $rd->assignment;
                    $assignment['mappings'] = array();
                    $requestedassignmentids = array_diff($requestedassignmentids, array($rd->assignment));
                }
                $assignment['mappings'][] = $mapping;

                $currentassignmentid = $rd->assignment;
            }
            if (!is_null($assignment)) {
                $assignments[] = $assignment;
            }
            $rs->close();

        }

        foreach ($requestedassignmentids as $assignmentid) {
            $warning = array();
            $warning['item'] = 'assignment';
            $warning['itemid'] = $assignmentid;
            $warning['warningcode'] = '3';
            $warning['message'] = 'No mappings found';
            $warnings[] = $warning;
        }

        $result = array();
        $result['assignments'] = $assignments;
        $result['warnings'] = $warnings;
        return $result;
    }

    
    private static function assign_user_mappings() {
        return new external_single_structure(
            array (
                'assignmentid'    => new external_value(PARAM_INT, 'assignment id'),
                'mappings'   => new external_multiple_structure(new external_single_structure(
                        array(
                            'id'     => new external_value(PARAM_INT, 'user mapping id'),
                            'userid' => new external_value(PARAM_INT, 'student id')
                        )
                    )
                )
            )
        );
    }

    
    public static function get_user_mappings_returns() {
        return new external_single_structure(
            array(
                'assignments' => new external_multiple_structure(self::assign_user_mappings(), 'list of assign user mapping data'),
                'warnings'      => new external_warnings('item is always \'assignment\'',
                    'when errorcode is 3 then itemid is an assignment id. When errorcode is 1, itemid is a course module id',
                    'errorcode can be 3 (no user mappings found) or 1 (no permission to get user mappings)')
            )
        );
    }

    
    public static function lock_submissions_parameters() {
        return new external_function_parameters(
            array(
                'assignmentid' => new external_value(PARAM_INT, 'The assignment id to operate on'),
                'userids' => new external_multiple_structure(
                    new external_value(PARAM_INT, 'user id'),
                    '1 or more user ids',
                    VALUE_REQUIRED),
            )
        );
    }

    
    public static function lock_submissions($assignmentid, $userids) {
        global $CFG;

        $params = self::validate_parameters(self::lock_submissions_parameters(),
                        array('assignmentid' => $assignmentid,
                              'userids' => $userids));

        $cm = get_coursemodule_from_instance('assign', $params['assignmentid'], 0, false, MUST_EXIST);
        $context = context_module::instance($cm->id);
        self::validate_context($context);

        $assignment = new assign($context, $cm, null);

        $warnings = array();
        foreach ($params['userids'] as $userid) {
            if (!$assignment->lock_submission($userid)) {
                $detail = 'User id: ' . $userid . ', Assignment id: ' . $params['assignmentid'];
                $warnings[] = self::generate_warning($params['assignmentid'],
                                                     'couldnotlock',
                                                     $detail);
            }
        }

        return $warnings;
    }

    
    public static function lock_submissions_returns() {
        return new external_warnings();
    }

    
    public static function revert_submissions_to_draft_parameters() {
        return new external_function_parameters(
            array(
                'assignmentid' => new external_value(PARAM_INT, 'The assignment id to operate on'),
                'userids' => new external_multiple_structure(
                    new external_value(PARAM_INT, 'user id'),
                    '1 or more user ids',
                    VALUE_REQUIRED),
            )
        );
    }

    
    public static function revert_submissions_to_draft($assignmentid, $userids) {
        global $CFG;

        $params = self::validate_parameters(self::revert_submissions_to_draft_parameters(),
                        array('assignmentid' => $assignmentid,
                              'userids' => $userids));

        $cm = get_coursemodule_from_instance('assign', $params['assignmentid'], 0, false, MUST_EXIST);
        $context = context_module::instance($cm->id);
        self::validate_context($context);

        $assignment = new assign($context, $cm, null);

        $warnings = array();
        foreach ($params['userids'] as $userid) {
            if (!$assignment->revert_to_draft($userid)) {
                $detail = 'User id: ' . $userid . ', Assignment id: ' . $params['assignmentid'];
                $warnings[] = self::generate_warning($params['assignmentid'],
                                                     'couldnotrevert',
                                                     $detail);
            }
        }

        return $warnings;
    }

    
    public static function revert_submissions_to_draft_returns() {
        return new external_warnings();
    }

    
    public static function unlock_submissions_parameters() {
        return new external_function_parameters(
            array(
                'assignmentid' => new external_value(PARAM_INT, 'The assignment id to operate on'),
                'userids' => new external_multiple_structure(
                    new external_value(PARAM_INT, 'user id'),
                    '1 or more user ids',
                    VALUE_REQUIRED),
            )
        );
    }

    
    public static function unlock_submissions($assignmentid, $userids) {
        global $CFG;

        $params = self::validate_parameters(self::unlock_submissions_parameters(),
                        array('assignmentid' => $assignmentid,
                              'userids' => $userids));

        $cm = get_coursemodule_from_instance('assign', $params['assignmentid'], 0, false, MUST_EXIST);
        $context = context_module::instance($cm->id);
        self::validate_context($context);

        $assignment = new assign($context, $cm, null);

        $warnings = array();
        foreach ($params['userids'] as $userid) {
            if (!$assignment->unlock_submission($userid)) {
                $detail = 'User id: ' . $userid . ', Assignment id: ' . $params['assignmentid'];
                $warnings[] = self::generate_warning($params['assignmentid'],
                                                     'couldnotunlock',
                                                     $detail);
            }
        }

        return $warnings;
    }

    
    public static function unlock_submissions_returns() {
        return new external_warnings();
    }

    
    public static function submit_grading_form_parameters() {
        return new external_function_parameters(
            array(
                'assignmentid' => new external_value(PARAM_INT, 'The assignment id to operate on'),
                'userid' => new external_value(PARAM_INT, 'The user id the submission belongs to'),
                'jsonformdata' => new external_value(PARAM_RAW, 'The data from the grading form, encoded as a json array')
            )
        );
    }

    
    public static function submit_grading_form($assignmentid, $userid, $jsonformdata) {
        global $CFG, $USER;

        require_once($CFG->dirroot . '/mod/assign/locallib.php');
        require_once($CFG->dirroot . '/mod/assign/gradeform.php');

        $params = self::validate_parameters(self::submit_grading_form_parameters(),
                                            array(
                                                'assignmentid' => $assignmentid,
                                                'userid' => $userid,
                                                'jsonformdata' => $jsonformdata
                                            ));

        $cm = get_coursemodule_from_instance('assign', $params['assignmentid'], 0, false, MUST_EXIST);
        $context = context_module::instance($cm->id);
        self::validate_context($context);

        $assignment = new assign($context, $cm, null);

        $serialiseddata = json_decode($params['jsonformdata']);

        $data = array();
        parse_str($serialiseddata, $data);

        $warnings = array();

        $options = array(
            'userid' => $params['userid'],
            'attemptnumber' => $data['attemptnumber'],
            'rownum' => 0,
            'gradingpanel' => true
        );

        $customdata = (object) $data;
        $formparams = array($assignment, $customdata, $options);

                $mform = new mod_assign_grade_form(null, $formparams, 'post', '', null, true, $data);
        $validateddata = $mform->get_data();

        if ($validateddata) {
            $assignment->save_grade($params['userid'], $validateddata);
        } else {
            $warnings[] = self::generate_warning($params['assignmentid'],
                                                 'couldnotsavegrade',
                                                 'Form validation failed.');
        }


        return $warnings;
    }

    
    public static function submit_grading_form_returns() {
        return new external_warnings();
    }

    
    public static function submit_for_grading_parameters() {
        return new external_function_parameters(
            array(
                'assignmentid' => new external_value(PARAM_INT, 'The assignment id to operate on'),
                'acceptsubmissionstatement' => new external_value(PARAM_BOOL, 'Accept the assignment submission statement')
            )
        );
    }

    
    public static function submit_for_grading($assignmentid, $acceptsubmissionstatement) {
        global $CFG, $USER;

        $params = self::validate_parameters(self::submit_for_grading_parameters(),
                                            array('assignmentid' => $assignmentid,
                                                  'acceptsubmissionstatement' => $acceptsubmissionstatement));

        $cm = get_coursemodule_from_instance('assign', $params['assignmentid'], 0, false, MUST_EXIST);
        $context = context_module::instance($cm->id);
        self::validate_context($context);

        $assignment = new assign($context, $cm, null);

        $warnings = array();
        $data = new stdClass();
        $data->submissionstatement = $params['acceptsubmissionstatement'];
        $notices = array();

        if (!$assignment->submit_for_grading($data, $notices)) {
            $detail = 'User id: ' . $USER->id . ', Assignment id: ' . $params['assignmentid'] . ' Notices:' . implode(', ', $notices);
            $warnings[] = self::generate_warning($params['assignmentid'],
                                                 'couldnotsubmitforgrading',
                                                 $detail);
        }

        return $warnings;
    }

    
    public static function submit_for_grading_returns() {
        return new external_warnings();
    }

    
    public static function save_user_extensions_parameters() {
        return new external_function_parameters(
            array(
                'assignmentid' => new external_value(PARAM_INT, 'The assignment id to operate on'),
                'userids' => new external_multiple_structure(
                    new external_value(PARAM_INT, 'user id'),
                    '1 or more user ids',
                    VALUE_REQUIRED),
                'dates' => new external_multiple_structure(
                    new external_value(PARAM_INT, 'dates'),
                    '1 or more extension dates (timestamp)',
                    VALUE_REQUIRED),
            )
        );
    }

    
    public static function save_user_extensions($assignmentid, $userids, $dates) {
        global $CFG;

        $params = self::validate_parameters(self::save_user_extensions_parameters(),
                        array('assignmentid' => $assignmentid,
                              'userids' => $userids,
                              'dates' => $dates));

        if (count($params['userids']) != count($params['dates'])) {
            $detail = 'Length of userids and dates parameters differ.';
            $warnings[] = self::generate_warning($params['assignmentid'],
                                                 'invalidparameters',
                                                 $detail);

            return $warnings;
        }

        $cm = get_coursemodule_from_instance('assign', $params['assignmentid'], 0, false, MUST_EXIST);
        $context = context_module::instance($cm->id);
        self::validate_context($context);

        $assignment = new assign($context, $cm, null);

        $warnings = array();
        foreach ($params['userids'] as $idx => $userid) {
            $duedate = $params['dates'][$idx];
            if (!$assignment->save_user_extension($userid, $duedate)) {
                $detail = 'User id: ' . $userid . ', Assignment id: ' . $params['assignmentid'] . ', Extension date: ' . $duedate;
                $warnings[] = self::generate_warning($params['assignmentid'],
                                                     'couldnotgrantextensions',
                                                     $detail);
            }
        }

        return $warnings;
    }

    
    public static function save_user_extensions_returns() {
        return new external_warnings();
    }

    
    public static function reveal_identities_parameters() {
        return new external_function_parameters(
            array(
                'assignmentid' => new external_value(PARAM_INT, 'The assignment id to operate on')
            )
        );
    }

    
    public static function reveal_identities($assignmentid) {
        global $CFG, $USER;

        $params = self::validate_parameters(self::reveal_identities_parameters(),
                                            array('assignmentid' => $assignmentid));

        $cm = get_coursemodule_from_instance('assign', $params['assignmentid'], 0, false, MUST_EXIST);
        $context = context_module::instance($cm->id);
        self::validate_context($context);

        $assignment = new assign($context, $cm, null);

        $warnings = array();
        if (!$assignment->reveal_identities()) {
            $detail = 'User id: ' . $USER->id . ', Assignment id: ' . $params['assignmentid'];
            $warnings[] = self::generate_warning($params['assignmentid'],
                                                 'couldnotrevealidentities',
                                                 $detail);
        }

        return $warnings;
    }

    
    public static function reveal_identities_returns() {
        return new external_warnings();
    }

    
    public static function save_submission_parameters() {
        global $CFG;
        $instance = new assign(null, null, null);
        $pluginsubmissionparams = array();

        foreach ($instance->get_submission_plugins() as $plugin) {
            if ($plugin->is_visible()) {
                $pluginparams = $plugin->get_external_parameters();
                if (!empty($pluginparams)) {
                    $pluginsubmissionparams = array_merge($pluginsubmissionparams, $pluginparams);
                }
            }
        }

        return new external_function_parameters(
            array(
                'assignmentid' => new external_value(PARAM_INT, 'The assignment id to operate on'),
                'plugindata' => new external_single_structure(
                    $pluginsubmissionparams
                )
            )
        );
    }

    
    public static function save_submission($assignmentid, $plugindata) {
        global $CFG, $USER;

        $params = self::validate_parameters(self::save_submission_parameters(),
                                            array('assignmentid' => $assignmentid,
                                                  'plugindata' => $plugindata));

        $cm = get_coursemodule_from_instance('assign', $params['assignmentid'], 0, false, MUST_EXIST);
        $context = context_module::instance($cm->id);
        self::validate_context($context);

        $assignment = new assign($context, $cm, null);

        $notices = array();

        if (!$assignment->submissions_open($USER->id)) {
            $notices[] = get_string('duedatereached', 'assign');
        } else {
            $submissiondata = (object)$params['plugindata'];
            $assignment->save_submission($submissiondata, $notices);
        }

        $warnings = array();
        foreach ($notices as $notice) {
            $warnings[] = self::generate_warning($params['assignmentid'],
                                                 'couldnotsavesubmission',
                                                 $notice);
        }

        return $warnings;
    }

    
    public static function save_submission_returns() {
        return new external_warnings();
    }

    
    public static function save_grade_parameters() {
        global $CFG;
        require_once("$CFG->dirroot/grade/grading/lib.php");
        $instance = new assign(null, null, null);
        $pluginfeedbackparams = array();

        foreach ($instance->get_feedback_plugins() as $plugin) {
            if ($plugin->is_visible()) {
                $pluginparams = $plugin->get_external_parameters();
                if (!empty($pluginparams)) {
                    $pluginfeedbackparams = array_merge($pluginfeedbackparams, $pluginparams);
                }
            }
        }

        $advancedgradingdata = array();
        $methods = array_keys(grading_manager::available_methods(false));
        foreach ($methods as $method) {
            require_once($CFG->dirroot.'/grade/grading/form/'.$method.'/lib.php');
            $details  = call_user_func('gradingform_'.$method.'_controller::get_external_instance_filling_details');
            if (!empty($details)) {
                $items = array();
                foreach ($details as $key => $value) {
                    $value->required = VALUE_OPTIONAL;
                    unset($value->content->keys['id']);
                    $items[$key] = new external_multiple_structure (new external_single_structure(
                        array(
                            'criterionid' => new external_value(PARAM_INT, 'criterion id'),
                            'fillings' => $value
                        )
                    ));
                }
                $advancedgradingdata[$method] = new external_single_structure($items, 'items', VALUE_OPTIONAL);
            }
        }

        return new external_function_parameters(
            array(
                'assignmentid' => new external_value(PARAM_INT, 'The assignment id to operate on'),
                'userid' => new external_value(PARAM_INT, 'The student id to operate on'),
                'grade' => new external_value(PARAM_FLOAT, 'The new grade for this user. Ignored if advanced grading used'),
                'attemptnumber' => new external_value(PARAM_INT, 'The attempt number (-1 means latest attempt)'),
                'addattempt' => new external_value(PARAM_BOOL, 'Allow another attempt if the attempt reopen method is manual'),
                'workflowstate' => new external_value(PARAM_ALPHA, 'The next marking workflow state'),
                'applytoall' => new external_value(PARAM_BOOL, 'If true, this grade will be applied ' .
                                                               'to all members ' .
                                                               'of the group (for group assignments).'),
                'plugindata' => new external_single_structure($pluginfeedbackparams, 'plugin data', VALUE_DEFAULT, array()),
                'advancedgradingdata' => new external_single_structure($advancedgradingdata, 'advanced grading data',
                                                                       VALUE_DEFAULT, array())
            )
        );
    }

    
    public static function save_grade($assignmentid,
                                      $userid,
                                      $grade,
                                      $attemptnumber,
                                      $addattempt,
                                      $workflowstate,
                                      $applytoall,
                                      $plugindata = array(),
                                      $advancedgradingdata = array()) {
        global $CFG, $USER;

        $params = self::validate_parameters(self::save_grade_parameters(),
                                            array('assignmentid' => $assignmentid,
                                                  'userid' => $userid,
                                                  'grade' => $grade,
                                                  'attemptnumber' => $attemptnumber,
                                                  'workflowstate' => $workflowstate,
                                                  'addattempt' => $addattempt,
                                                  'applytoall' => $applytoall,
                                                  'plugindata' => $plugindata,
                                                  'advancedgradingdata' => $advancedgradingdata));

        $cm = get_coursemodule_from_instance('assign', $params['assignmentid'], 0, false, MUST_EXIST);
        $context = context_module::instance($cm->id);
        self::validate_context($context);

        $assignment = new assign($context, $cm, null);

        $gradedata = (object)$params['plugindata'];

        $gradedata->addattempt = $params['addattempt'];
        $gradedata->attemptnumber = $params['attemptnumber'];
        $gradedata->workflowstate = $params['workflowstate'];
        $gradedata->applytoall = $params['applytoall'];
        $gradedata->grade = $params['grade'];

        if (!empty($params['advancedgradingdata'])) {
            $advancedgrading = array();
            $criteria = reset($params['advancedgradingdata']);
            foreach ($criteria as $key => $criterion) {
                $details = array();
                foreach ($criterion as $value) {
                    foreach ($value['fillings'] as $filling) {
                        $details[$value['criterionid']] = $filling;
                    }
                }
                $advancedgrading[$key] = $details;
            }
            $gradedata->advancedgrading = $advancedgrading;
        }

        $assignment->save_grade($params['userid'], $gradedata);

        return null;
    }

    
    public static function save_grade_returns() {
        return null;
    }

    
    public static function save_grades_parameters() {
        global $CFG;
        require_once("$CFG->dirroot/grade/grading/lib.php");
        $instance = new assign(null, null, null);
        $pluginfeedbackparams = array();

        foreach ($instance->get_feedback_plugins() as $plugin) {
            if ($plugin->is_visible()) {
                $pluginparams = $plugin->get_external_parameters();
                if (!empty($pluginparams)) {
                    $pluginfeedbackparams = array_merge($pluginfeedbackparams, $pluginparams);
                }
            }
        }

        $advancedgradingdata = array();
        $methods = array_keys(grading_manager::available_methods(false));
        foreach ($methods as $method) {
            require_once($CFG->dirroot.'/grade/grading/form/'.$method.'/lib.php');
            $details  = call_user_func('gradingform_'.$method.'_controller::get_external_instance_filling_details');
            if (!empty($details)) {
                $items = array();
                foreach ($details as $key => $value) {
                    $value->required = VALUE_OPTIONAL;
                    unset($value->content->keys['id']);
                    $items[$key] = new external_multiple_structure (new external_single_structure(
                        array(
                            'criterionid' => new external_value(PARAM_INT, 'criterion id'),
                            'fillings' => $value
                        )
                    ));
                }
                $advancedgradingdata[$method] = new external_single_structure($items, 'items', VALUE_OPTIONAL);
            }
        }

        return new external_function_parameters(
            array(
                'assignmentid' => new external_value(PARAM_INT, 'The assignment id to operate on'),
                'applytoall' => new external_value(PARAM_BOOL, 'If true, this grade will be applied ' .
                                                               'to all members ' .
                                                               'of the group (for group assignments).'),
                'grades' => new external_multiple_structure(
                    new external_single_structure(
                        array (
                            'userid' => new external_value(PARAM_INT, 'The student id to operate on'),
                            'grade' => new external_value(PARAM_FLOAT, 'The new grade for this user. '.
                                                                       'Ignored if advanced grading used'),
                            'attemptnumber' => new external_value(PARAM_INT, 'The attempt number (-1 means latest attempt)'),
                            'addattempt' => new external_value(PARAM_BOOL, 'Allow another attempt if manual attempt reopen method'),
                            'workflowstate' => new external_value(PARAM_ALPHA, 'The next marking workflow state'),
                            'plugindata' => new external_single_structure($pluginfeedbackparams, 'plugin data',
                                                                          VALUE_DEFAULT, array()),
                            'advancedgradingdata' => new external_single_structure($advancedgradingdata, 'advanced grading data',
                                                                                   VALUE_DEFAULT, array())
                        )
                    )
                )
            )
        );
    }

    
    public static function save_grades($assignmentid, $applytoall = false, $grades) {
        global $CFG, $USER;

        $params = self::validate_parameters(self::save_grades_parameters(),
                                            array('assignmentid' => $assignmentid,
                                                  'applytoall' => $applytoall,
                                                  'grades' => $grades));

        $cm = get_coursemodule_from_instance('assign', $params['assignmentid'], 0, false, MUST_EXIST);
        $context = context_module::instance($cm->id);
        self::validate_context($context);
        $assignment = new assign($context, $cm, null);

        if ($assignment->get_instance()->teamsubmission && $params['applytoall']) {
                        $groupids = array();
            foreach ($params['grades'] as $gradeinfo) {
                $group = $assignment->get_submission_group($gradeinfo['userid']);
                if (in_array($group->id, $groupids)) {
                    throw new invalid_parameter_exception('Multiple grades for the same team have been supplied '
                                                          .' this is not permitted when the applytoall flag is set');
                } else {
                    $groupids[] = $group->id;
                }
            }
        }

        foreach ($params['grades'] as $gradeinfo) {
            $gradedata = (object)$gradeinfo['plugindata'];
            $gradedata->addattempt = $gradeinfo['addattempt'];
            $gradedata->attemptnumber = $gradeinfo['attemptnumber'];
            $gradedata->workflowstate = $gradeinfo['workflowstate'];
            $gradedata->applytoall = $params['applytoall'];
            $gradedata->grade = $gradeinfo['grade'];

            if (!empty($gradeinfo['advancedgradingdata'])) {
                $advancedgrading = array();
                $criteria = reset($gradeinfo['advancedgradingdata']);
                foreach ($criteria as $key => $criterion) {
                    $details = array();
                    foreach ($criterion as $value) {
                        foreach ($value['fillings'] as $filling) {
                            $details[$value['criterionid']] = $filling;
                        }
                    }
                    $advancedgrading[$key] = $details;
                }
                $gradedata->advancedgrading = $advancedgrading;
            }
            $assignment->save_grade($gradeinfo['userid'], $gradedata);
        }

        return null;
    }

    
    public static function save_grades_returns() {
        return null;
    }

    
    public static function copy_previous_attempt_parameters() {
        return new external_function_parameters(
            array(
                'assignmentid' => new external_value(PARAM_INT, 'The assignment id to operate on'),
            )
        );
    }

    
    public static function copy_previous_attempt($assignmentid) {

        $params = self::validate_parameters(self::copy_previous_attempt_parameters(),
                                            array('assignmentid' => $assignmentid));

        $cm = get_coursemodule_from_instance('assign', $params['assignmentid'], 0, false, MUST_EXIST);
        $context = context_module::instance($cm->id);
        self::validate_context($context);

        $assignment = new assign($context, $cm, null);

        $notices = array();

        $assignment->copy_previous_attempt($notices);

        $warnings = array();
        foreach ($notices as $notice) {
            $warnings[] = self::generate_warning($assignmentid,
                                                 'couldnotcopyprevioussubmission',
                                                 $notice);
        }

        return $warnings;
    }

    
    public static function copy_previous_attempt_returns() {
        return new external_warnings();
    }

    
    public static function view_grading_table_parameters() {
        return new external_function_parameters(
            array(
                'assignid' => new external_value(PARAM_INT, 'assign instance id')
            )
        );
    }

    
    public static function view_grading_table($assignid) {
        global $DB, $CFG;

        $params = self::validate_parameters(self::view_grading_table_parameters(),
                                            array(
                                                'assignid' => $assignid
                                            ));
        $warnings = array();

                $assign = $DB->get_record('assign', array('id' => $params['assignid']), 'id', MUST_EXIST);
        list($course, $cm) = get_course_and_cm_from_instance($assign, 'assign');

        $context = context_module::instance($cm->id);
        self::validate_context($context);

        require_capability('mod/assign:view', $context);

        $assign = new assign($context, null, null);
        $assign->require_view_grades();
        \mod_assign\event\grading_table_viewed::create_from_assign($assign)->trigger();

        $result = array();
        $result['status'] = true;
        $result['warnings'] = $warnings;
        return $result;
    }

    
    public static function view_grading_table_returns() {
        return new external_single_structure(
            array(
                'status' => new external_value(PARAM_BOOL, 'status: true if success'),
                'warnings' => new external_warnings()
            )
        );
    }

    
    public static function view_submission_status_parameters() {
        return new external_function_parameters (
            array(
                'assignid' => new external_value(PARAM_INT, 'assign instance id'),
            )
        );
    }

    
    public static function view_submission_status($assignid) {
        global $DB, $CFG;

        $warnings = array();
        $params = array(
            'assignid' => $assignid,
        );
        $params = self::validate_parameters(self::view_submission_status_parameters(), $params);

                $assign = $DB->get_record('assign', array('id' => $params['assignid']), 'id', MUST_EXIST);
        list($course, $cm) = get_course_and_cm_from_instance($assign, 'assign');

        $context = context_module::instance($cm->id);
                self::validate_context($context);

        $assign = new assign($context, $cm, $course);
        \mod_assign\event\submission_status_viewed::create_from_assign($assign)->trigger();

        $result = array();
        $result['status'] = true;
        $result['warnings'] = $warnings;
        return $result;
    }

    
    public static function view_submission_status_returns() {
        return new external_single_structure(
            array(
                'status' => new external_value(PARAM_BOOL, 'status: true if success'),
                'warnings' => new external_warnings(),
            )
        );
    }

    
    public static function get_submission_status_parameters() {
        return new external_function_parameters (
            array(
                'assignid' => new external_value(PARAM_INT, 'assignment instance id'),
                'userid' => new external_value(PARAM_INT, 'user id (empty for current user)', VALUE_DEFAULT, 0),
            )
        );
    }

    
    public static function get_submission_status($assignid, $userid = 0) {
        global $USER, $DB;

        $warnings = array();

        $params = array(
            'assignid' => $assignid,
            'userid' => $userid,
        );
        $params = self::validate_parameters(self::get_submission_status_parameters(), $params);

                $assign = $DB->get_record('assign', array('id' => $params['assignid']), 'id', MUST_EXIST);
        list($course, $cm) = get_course_and_cm_from_instance($assign, 'assign');

        $context = context_module::instance($cm->id);
        self::validate_context($context);

        $assign = new assign($context, $cm, $course);

                if (empty($params['userid'])) {
            $params['userid'] = $USER->id;
        }
        $user = core_user::get_user($params['userid'], '*', MUST_EXIST);
        core_user::require_active_user($user);

        if (!$assign->can_view_submission($user->id)) {
            throw new required_capability_exception($context, 'mod/assign:viewgrades', 'nopermission', '');
        }

        $gradingsummary = $lastattempt = $feedback = $previousattempts = null;

                if ($assign->can_view_grades()) {
            $gradingsummary = $assign->get_assign_grading_summary_renderable();
        }

                if (has_capability('mod/assign:submit', $assign->get_context(), $user)) {
            $lastattempt = $assign->get_assign_submission_status_renderable($user, true);
        }

        $feedback = $assign->get_assign_feedback_status_renderable($user);

        $previousattempts = $assign->get_assign_attempt_history_renderable($user);

                $result = array();

                if ($gradingsummary) {
            $result['gradingsummary'] = $gradingsummary;
        }

                if ($lastattempt) {
            $submissionplugins = $assign->get_submission_plugins();

            if (empty($lastattempt->submission)) {
                unset($lastattempt->submission);
            } else {
                $lastattempt->submission->plugins = self::get_plugins_data($assign, $submissionplugins, $lastattempt->submission);
            }

            if (empty($lastattempt->teamsubmission)) {
                unset($lastattempt->teamsubmission);
            } else {
                $lastattempt->teamsubmission->plugins = self::get_plugins_data($assign, $submissionplugins,
                                                                                $lastattempt->teamsubmission);
            }

                        if (!empty($lastattempt->submissiongroup)) {
                $lastattempt->submissiongroup = $lastattempt->submissiongroup->id;
            } else {
                unset($lastattempt->submissiongroup);
            }

            if (!empty($lastattempt->usergroups)) {
                $lastattempt->usergroups = array_keys($lastattempt->usergroups);
            }
                        if (!empty($lastattempt->submissiongroupmemberswhoneedtosubmit)) {
                $lastattempt->submissiongroupmemberswhoneedtosubmit = array_map(
                                                                            function($e){
                                                                                return $e->id;
                                                                            },
                                                                            $lastattempt->submissiongroupmemberswhoneedtosubmit);
            }

            $result['lastattempt'] = $lastattempt;
        }

                if ($feedback) {
            if ($feedback->grade) {
                $feedbackplugins = $assign->get_feedback_plugins();
                $feedback->plugins = self::get_plugins_data($assign, $feedbackplugins, $feedback->grade);
            } else {
                unset($feedback->plugins);
                unset($feedback->grade);
            }

            $result['feedback'] = $feedback;
        }

                if ($previousattempts and count($previousattempts->submissions) > 1) {
                        array_pop($previousattempts->submissions);

                        $previousattempts->submissions = array_reverse($previousattempts->submissions);

            foreach ($previousattempts->submissions as $i => $submission) {
                $attempt = array();

                $grade = null;
                foreach ($previousattempts->grades as $onegrade) {
                    if ($onegrade->attemptnumber == $submission->attemptnumber) {
                        $grade = $onegrade;
                        break;
                    }
                }

                $attempt['attemptnumber'] = $submission->attemptnumber;

                if ($submission) {
                    $submission->plugins = self::get_plugins_data($assign, $previousattempts->submissionplugins, $submission);
                    $attempt['submission'] = $submission;
                }

                if ($grade) {
                                        $grade->grader = $grade->grader->id;
                    $feedbackplugins = self::get_plugins_data($assign, $previousattempts->feedbackplugins, $grade);

                    $attempt['grade'] = $grade;
                    $attempt['feedbackplugins'] = $feedbackplugins;
                }
                $result['previousattempts'][] = $attempt;
            }
        }

        $result['warnings'] = $warnings;
        return $result;
    }

    
    public static function get_submission_status_returns() {
        return new external_single_structure(
            array(
                'gradingsummary' => new external_single_structure(
                    array(
                        'participantcount' => new external_value(PARAM_INT, 'Number of users who can submit.'),
                        'submissiondraftscount' => new external_value(PARAM_INT, 'Number of submissions in draft status.'),
                        'submissiondraftscount' => new external_value(PARAM_INT, 'Number of submissions in draft status.'),
                        'submissionsenabled' => new external_value(PARAM_BOOL, 'Whether submissions are enabled or not.'),
                        'submissionssubmittedcount' => new external_value(PARAM_INT, 'Number of submissions in submitted status.'),
                        'submissionsneedgradingcount' => new external_value(PARAM_INT, 'Number of submissions that need grading.'),
                        'warnofungroupedusers' => new external_value(PARAM_BOOL, 'Whether we need to warn people that there
                                                                        are users without groups.'),
                    ), 'Grading information.', VALUE_OPTIONAL
                ),
                'lastattempt' => new external_single_structure(
                    array(
                        'submission' => self::get_submission_structure(VALUE_OPTIONAL),
                        'teamsubmission' => self::get_submission_structure(VALUE_OPTIONAL),
                        'submissiongroup' => new external_value(PARAM_INT, 'The submission group id (for group submissions only).',
                                                                VALUE_OPTIONAL),
                        'submissiongroupmemberswhoneedtosubmit' => new external_multiple_structure(
                            new external_value(PARAM_INT, 'USER id.'),
                            'List of users who still need to submit (for group submissions only).',
                            VALUE_OPTIONAL
                        ),
                        'submissionsenabled' => new external_value(PARAM_BOOL, 'Whether submissions are enabled or not.'),
                        'locked' => new external_value(PARAM_BOOL, 'Whether new submissions are locked.'),
                        'graded' => new external_value(PARAM_BOOL, 'Whether the submission is graded.'),
                        'canedit' => new external_value(PARAM_BOOL, 'Whether the user can edit the current submission.'),
                        'cansubmit' => new external_value(PARAM_BOOL, 'Whether the user can submit.'),
                        'extensionduedate' => new external_value(PARAM_INT, 'Extension due date.'),
                        'blindmarking' => new external_value(PARAM_BOOL, 'Whether blind marking is enabled.'),
                        'gradingstatus' => new external_value(PARAM_ALPHANUMEXT, 'Grading status.'),
                        'usergroups' => new external_multiple_structure(
                            new external_value(PARAM_INT, 'Group id.'), 'User groups in the course.'
                        ),
                    ), 'Last attempt information.', VALUE_OPTIONAL
                ),
                'feedback' => new external_single_structure(
                    array(
                        'grade' => self::get_grade_structure(VALUE_OPTIONAL),
                        'gradefordisplay' => new external_value(PARAM_RAW, 'Grade rendered into a format suitable for display.'),
                        'gradeddate' => new external_value(PARAM_INT, 'The date the user was graded.'),
                        'plugins' => new external_multiple_structure(self::get_plugin_structure(), 'Plugins info.', VALUE_OPTIONAL),
                    ), 'Feedback for the last attempt.', VALUE_OPTIONAL
                ),
                'previousattempts' => new external_multiple_structure(
                    new external_single_structure(
                        array(
                            'attemptnumber' => new external_value(PARAM_INT, 'Attempt number.'),
                            'submission' => self::get_submission_structure(VALUE_OPTIONAL),
                            'grade' => self::get_grade_structure(VALUE_OPTIONAL),
                            'feedbackplugins' => new external_multiple_structure(self::get_plugin_structure(), 'Feedback info.',
                                                                                    VALUE_OPTIONAL),
                        )
                    ), 'List all the previous attempts did by the user.', VALUE_OPTIONAL
                ),
                'warnings' => new external_warnings(),
            )
        );
    }

    
    public static function list_participants_parameters() {
        return new external_function_parameters(
            array(
                'assignid' => new external_value(PARAM_INT, 'assign instance id'),
                'groupid' => new external_value(PARAM_INT, 'group id'),
                'filter' => new external_value(PARAM_RAW, 'search string to filter the results'),
                'skip' => new external_value(PARAM_INT, 'number of records to skip', VALUE_DEFAULT, 0),
                'limit' => new external_value(PARAM_INT, 'maximum number of records to return', VALUE_DEFAULT, 0),
                'onlyids' => new external_value(PARAM_BOOL, 'Do not return all user fields', VALUE_DEFAULT, false),
                'includeenrolments' => new external_value(PARAM_BOOL, 'Do return courses where the user is enrolled',
                                                          VALUE_DEFAULT, true)
            )
        );
    }

    
    public static function list_participants($assignid, $groupid, $filter, $skip, $limit, $onlyids, $includeenrolments) {
        global $DB, $CFG;
        require_once($CFG->dirroot . "/mod/assign/locallib.php");
        require_once($CFG->dirroot . "/user/lib.php");

        $params = self::validate_parameters(self::list_participants_parameters(),
                                            array(
                                                'assignid' => $assignid,
                                                'groupid' => $groupid,
                                                'filter' => $filter,
                                                'skip' => $skip,
                                                'limit' => $limit,
                                                'onlyids' => $onlyids,
                                                'includeenrolments' => $includeenrolments
                                            ));
        $warnings = array();

                $assign = $DB->get_record('assign', array('id' => $params['assignid']), 'id', MUST_EXIST);
        list($course, $cm) = get_course_and_cm_from_instance($assign, 'assign');

        $context = context_module::instance($cm->id);
        self::validate_context($context);

        require_capability('mod/assign:view', $context);

        $assign = new assign($context, null, null);
        $assign->require_view_grades();

        $participants = array();
        if (groups_group_visible($params['groupid'], $course, $cm)) {
            $participants = $assign->list_participants_with_filter_status_and_group($params['groupid']);
        }

        $userfields = user_get_default_fields();
        if (!$params['includeenrolments']) {
                        $key = array_search('enrolledcourses', $userfields);
            if ($key !== false) {
                unset($userfields[$key]);
            } else {
                throw new moodle_exception('invaliduserfield', 'error', '', 'enrolledcourses');
            }
        }

        $result = array();
        $index = 0;
        foreach ($participants as $record) {
                        $fullname = $record->fullname;
            $searchable = $fullname;
            $match = false;
            if (empty($filter)) {
                $match = true;
            } else {
                $filter = core_text::strtolower($filter);
                $value = core_text::strtolower($searchable);
                if (is_string($value) && (core_text::strpos($value, $filter) !== false)) {
                    $match = true;
                }
            }
            if ($match) {
                $index++;
                if ($index <= $params['skip']) {
                    continue;
                }
                if (($params['limit'] > 0) && (($index - $params['skip']) > $params['limit'])) {
                    break;
                }
                                if (!$assign->is_blind_marking() && !$params['onlyids']) {
                    $userdetails = user_get_user_details($record, $course, $userfields);
                } else {
                    $userdetails = array('id' => $record->id);
                }
                $userdetails['fullname'] = $fullname;
                $userdetails['submitted'] = $record->submitted;
                $userdetails['requiregrading'] = $record->requiregrading;
                if (!empty($record->groupid)) {
                    $userdetails['groupid'] = $record->groupid;
                }
                if (!empty($record->groupname)) {
                    $userdetails['groupname'] = $record->groupname;
                }

                $result[] = $userdetails;
            }
        }
        return $result;
    }

    
    public static function list_participants_returns() {
                $userdesc = core_user_external::user_description();
                $unneededproperties = [
            'auth', 'confirmed', 'lang', 'calendartype', 'theme', 'timezone', 'mailformat'
        ];
                foreach ($unneededproperties as $prop) {
            unset($userdesc->keys[$prop]);
        }

                $userdesc->keys['fullname']->type = PARAM_NOTAGS;
        $userdesc->keys['profileimageurlsmall']->required = VALUE_OPTIONAL;
        $userdesc->keys['profileimageurl']->required = VALUE_OPTIONAL;
        $userdesc->keys['email']->desc = 'Email address';
        $userdesc->keys['idnumber']->desc = 'The idnumber of the user';

                $otherkeys = [
            'groups' => new external_multiple_structure(
                new external_single_structure(
                    [
                        'id' => new external_value(PARAM_INT, 'group id'),
                        'name' => new external_value(PARAM_RAW, 'group name'),
                        'description' => new external_value(PARAM_RAW, 'group description'),
                    ]
                ), 'user groups', VALUE_OPTIONAL
            ),
            'roles' => new external_multiple_structure(
                new external_single_structure(
                    [
                        'roleid' => new external_value(PARAM_INT, 'role id'),
                        'name' => new external_value(PARAM_RAW, 'role name'),
                        'shortname' => new external_value(PARAM_ALPHANUMEXT, 'role shortname'),
                        'sortorder' => new external_value(PARAM_INT, 'role sortorder')
                    ]
                ), 'user roles', VALUE_OPTIONAL
            ),
            'enrolledcourses' => new external_multiple_structure(
                new external_single_structure(
                    [
                        'id' => new external_value(PARAM_INT, 'Id of the course'),
                        'fullname' => new external_value(PARAM_RAW, 'Fullname of the course'),
                        'shortname' => new external_value(PARAM_RAW, 'Shortname of the course')
                    ]
                ), 'Courses where the user is enrolled - limited by which courses the user is able to see', VALUE_OPTIONAL
            ),
            'submitted' => new external_value(PARAM_BOOL, 'have they submitted their assignment'),
            'requiregrading' => new external_value(PARAM_BOOL, 'is their submission waiting for grading'),
            'groupid' => new external_value(PARAM_INT, 'for group assignments this is the group id', VALUE_OPTIONAL),
            'groupname' => new external_value(PARAM_NOTAGS, 'for group assignments this is the group name', VALUE_OPTIONAL),
        ];

                $userdesc->keys = array_merge($userdesc->keys, $otherkeys);
        return new external_multiple_structure($userdesc);
    }

    
    public static function get_participant_parameters() {
        return new external_function_parameters(
            array(
                'assignid' => new external_value(PARAM_INT, 'assign instance id'),
                'userid' => new external_value(PARAM_INT, 'user id'),
                'embeduser' => new external_value(PARAM_BOOL, 'user id', VALUE_DEFAULT, false),
            )
        );
    }

    
    public static function get_participant($assignid, $userid, $embeduser) {
        global $DB, $CFG;
        require_once($CFG->dirroot . "/mod/assign/locallib.php");
        require_once($CFG->dirroot . "/user/lib.php");

        $params = self::validate_parameters(self::get_participant_parameters(), array(
            'assignid' => $assignid,
            'userid' => $userid,
            'embeduser' => $embeduser
        ));

                $assign = $DB->get_record('assign', array('id' => $params['assignid']), 'id', MUST_EXIST);
        list($course, $cm) = get_course_and_cm_from_instance($assign, 'assign');

        $context = context_module::instance($cm->id);
        self::validate_context($context);

        $assign = new assign($context, null, null);
        $assign->require_view_grades();

        $participant = $assign->get_participant($params['userid']);
        if (!$participant) {
                        throw new moodle_exception('usernotincourse');
        }

        $return = array(
            'id' => $participant->id,
            'fullname' => $participant->fullname,
            'submitted' => $participant->submitted,
            'requiregrading' => $participant->requiregrading,
            'blindmarking' => $assign->is_blind_marking(),
        );

        if (!empty($participant->groupid)) {
            $return['groupid'] = $participant->groupid;
        }
        if (!empty($participant->groupname)) {
            $return['groupname'] = $participant->groupname;
        }

                        if (!$assign->is_blind_marking() && $embeduser) {
            $return['user'] = user_get_user_details($participant, $course);
        }

        return $return;
    }

    
    public static function get_participant_returns() {
        $userdescription = core_user_external::user_description();
        $userdescription->default = [];
        $userdescription->required = VALUE_OPTIONAL;

        return new external_single_structure(array(
            'id' => new external_value(PARAM_INT, 'ID of the user'),
            'fullname' => new external_value(PARAM_NOTAGS, 'The fullname of the user'),
            'submitted' => new external_value(PARAM_BOOL, 'have they submitted their assignment'),
            'requiregrading' => new external_value(PARAM_BOOL, 'is their submission waiting for grading'),
            'blindmarking' => new external_value(PARAM_BOOL, 'is blind marking enabled for this assignment'),
            'groupid' => new external_value(PARAM_INT, 'for group assignments this is the group id', VALUE_OPTIONAL),
            'groupname' => new external_value(PARAM_NOTAGS, 'for group assignments this is the group name', VALUE_OPTIONAL),
            'user' => $userdescription,
        ));
    }
}
