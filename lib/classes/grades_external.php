<?php



defined('MOODLE_INTERNAL') || die;

require_once("$CFG->libdir/externallib.php");
require_once("$CFG->libdir/gradelib.php");
require_once("$CFG->dirroot/grade/querylib.php");


class core_grades_external extends external_api {
    
    public static function get_grades_parameters() {
        return new external_function_parameters(
            array(
                'courseid' => new external_value(PARAM_INT, 'id of course'),
                'component' => new external_value(
                    PARAM_COMPONENT, 'A component, for example mod_forum or mod_quiz', VALUE_DEFAULT, ''),
                'activityid' => new external_value(PARAM_INT, 'The activity ID', VALUE_DEFAULT, null),
                'userids' => new external_multiple_structure(
                    new external_value(PARAM_INT, 'user ID'),
                    'An array of user IDs, leave empty to just retrieve grade item information', VALUE_DEFAULT, array()
                )
            )
        );
    }

    
    public static function get_grades($courseid, $component = null, $activityid = null, $userids = array()) {
        global $CFG, $USER, $DB;

        $params = self::validate_parameters(self::get_grades_parameters(),
            array('courseid' => $courseid, 'component' => $component, 'activityid' => $activityid, 'userids' => $userids));

        $gradesarray = array(
            'items'     => array(),
            'outcomes'  => array()
        );

        $coursecontext = context_course::instance($params['courseid']);

        try {
            self::validate_context($coursecontext);
        } catch (Exception $e) {
            $exceptionparam = new stdClass();
            $exceptionparam->message = $e->getMessage();
            $exceptionparam->courseid = $params['courseid'];
            throw new moodle_exception('errorcoursecontextnotvalid' , 'webservice', '', $exceptionparam);
        }

        require_capability('moodle/grade:viewhidden', $coursecontext);

        $course = $DB->get_record('course', array('id' => $params['courseid']), '*', MUST_EXIST);

        $access = false;
        if (has_capability('moodle/grade:viewall', $coursecontext)) {
                        $access = true;

        } else if ($course->showgrades && count($params['userids']) == 1) {
            
            if ($params['userids'][0] == $USER->id and has_capability('moodle/grade:view', $coursecontext)) {
                                $access = true;

            } else if (has_capability('moodle/grade:viewall', context_user::instance($params['userids'][0]))) {
                                $access = true;
            }
        }

        if (!$access) {
            throw new moodle_exception('nopermissiontoviewgrades', 'error');
        }

        $itemtype = null;
        $itemmodule = null;
        $iteminstance = null;

        if (!empty($params['component'])) {
            list($itemtype, $itemmodule) = normalize_component($params['component']);
        }

        $cm = null;
        if (!empty($itemmodule) && !empty($params['activityid'])) {
            if (!$cm = get_coursemodule_from_id($itemmodule, $params['activityid'])) {
                throw new moodle_exception('invalidcoursemodule');
            }
            $iteminstance = $cm->instance;
        }

                $modinfo = get_fast_modinfo($params['courseid']);
        $activityinstances = $modinfo->get_instances();

        $gradeparams = array('courseid' => $params['courseid']);
        if (!empty($itemtype)) {
            $gradeparams['itemtype'] = $itemtype;
        }
        if (!empty($itemmodule)) {
            $gradeparams['itemmodule'] = $itemmodule;
        }
        if (!empty($iteminstance)) {
            $gradeparams['iteminstance'] = $iteminstance;
        }

        if ($activitygrades = grade_item::fetch_all($gradeparams)) {
            $canviewhidden = has_capability('moodle/grade:viewhidden', context_course::instance($params['courseid']));

            foreach ($activitygrades as $activitygrade) {

                if ($activitygrade->itemtype != 'course' and $activitygrade->itemtype != 'mod') {
                                        continue;
                }

                $context = $coursecontext;

                if ($activitygrade->itemtype == 'course') {
                    $item = grade_get_course_grades($course->id, $params['userids']);
                    $item->itemnumber = 0;

                    $grades = new stdClass;
                    $grades->items = array($item);
                    $grades->outcomes = array();

                } else {
                    $cm = $activityinstances[$activitygrade->itemmodule][$activitygrade->iteminstance];
                    $instance = $cm->instance;
                    $context = context_module::instance($cm->id, IGNORE_MISSING);

                    $grades = grade_get_grades($params['courseid'], $activitygrade->itemtype,
                                                $activitygrade->itemmodule, $instance, $params['userids']);
                }

                                                foreach ($grades->items as $gradeitem) {
                                        $gradeiteminstance = self::get_grade_item(
                        $course->id, $activitygrade->itemtype, $activitygrade->itemmodule, $activitygrade->iteminstance, 0);
                    if (!$canviewhidden && $gradeiteminstance->is_hidden()) {
                        continue;
                    }

                                        $gradeitem->hidden = (empty($gradeitem->hidden)) ? 0 : $gradeitem->hidden;
                    $gradeitem->locked = (empty($gradeitem->locked)) ? 0 : $gradeitem->locked;

                    $gradeitemarray = (array)$gradeitem;
                    $gradeitemarray['grades'] = array();

                    if (!empty($gradeitem->grades)) {
                        foreach ($gradeitem->grades as $studentid => $studentgrade) {
                            if (!$canviewhidden) {
                                                                $gradegradeinstance = grade_grade::fetch(
                                    array(
                                        'userid' => $studentid,
                                        'itemid' => $gradeiteminstance->id
                                    )
                                );
                                                                if (!empty($gradegradeinstance) && $gradegradeinstance->is_hidden()) {
                                    continue;
                                }
                            }

                                                        $studentgrade->hidden = (empty($studentgrade->hidden)) ? 0 : $studentgrade->hidden;
                            $studentgrade->locked = (empty($studentgrade->locked)) ? 0 : $studentgrade->locked;
                            $studentgrade->overridden = (empty($studentgrade->overridden)) ? 0 : $studentgrade->overridden;

                            if ($gradeiteminstance->itemtype != 'course' and !empty($studentgrade->feedback)) {
                                list($studentgrade->feedback, $studentgrade->feedbackformat) =
                                    external_format_text($studentgrade->feedback, $studentgrade->feedbackformat,
                                    $context->id, $params['component'], 'feedback', null);
                            }

                            $gradeitemarray['grades'][$studentid] = (array)$studentgrade;
                                                        $gradeitemarray['grades'][$studentid]['userid'] = $studentid;
                        }
                    }

                    if ($gradeiteminstance->itemtype == 'course') {
                        $gradesarray['items']['course'] = $gradeitemarray;
                        $gradesarray['items']['course']['activityid'] = 'course';
                    } else {
                        $gradesarray['items'][$cm->id] = $gradeitemarray;
                                                $gradesarray['items'][$cm->id]['activityid'] = $cm->id;
                    }
                }

                foreach ($grades->outcomes as $outcome) {
                                        $outcome->hidden = (empty($outcome->hidden)) ? 0 : $outcome->hidden;
                    $outcome->locked = (empty($outcome->locked)) ? 0 : $outcome->locked;

                    $gradesarray['outcomes'][$cm->id] = (array)$outcome;
                    $gradesarray['outcomes'][$cm->id]['activityid'] = $cm->id;

                    $gradesarray['outcomes'][$cm->id]['grades'] = array();
                    if (!empty($outcome->grades)) {
                        foreach ($outcome->grades as $studentid => $studentgrade) {
                            if (!$canviewhidden) {
                                                                $gradeiteminstance = self::get_grade_item($course->id, $activitygrade->itemtype,
                                                                           $activitygrade->itemmodule, $activitygrade->iteminstance,
                                                                           $activitygrade->itemnumber);
                                $gradegradeinstance = grade_grade::fetch(
                                    array(
                                        'userid' => $studentid,
                                        'itemid' => $gradeiteminstance->id
                                    )
                                );
                                                                if (!empty($gradegradeinstance ) && $gradegradeinstance->is_hidden()) {
                                    continue;
                                }
                            }

                                                        $studentgrade->hidden = (empty($studentgrade->hidden)) ? 0 : $studentgrade->hidden;
                            $studentgrade->locked = (empty($studentgrade->locked)) ? 0 : $studentgrade->locked;

                            if (!empty($studentgrade->feedback)) {
                                list($studentgrade->feedback, $studentgrade->feedbackformat) =
                                    external_format_text($studentgrade->feedback, $studentgrade->feedbackformat,
                                    $context->id, $params['component'], 'feedback', null);
                            }

                            $gradesarray['outcomes'][$cm->id]['grades'][$studentid] = (array)$studentgrade;

                                                        $gradesarray['outcomes'][$cm->id]['grades'][$studentid]['userid'] = $studentid;
                        }
                    }
                }
            }
        }

        return $gradesarray;
    }

    
    private static function get_grade_item($courseid, $itemtype, $itemmodule = null, $iteminstance = null, $itemnumber = null) {
        $gradeiteminstance = null;
        if ($itemtype == 'course') {
            $gradeiteminstance = grade_item::fetch(array('courseid' => $courseid, 'itemtype' => $itemtype));
        } else {
            $gradeiteminstance = grade_item::fetch(
                array('courseid' => $courseid, 'itemtype' => $itemtype,
                    'itemmodule' => $itemmodule, 'iteminstance' => $iteminstance, 'itemnumber' => $itemnumber));
        }
        return $gradeiteminstance;
    }

    
    public static function get_grades_returns() {
        return new external_single_structure(
            array(
                'items'  => new external_multiple_structure(
                    new external_single_structure(
                        array(
                            'activityid' => new external_value(
                                PARAM_ALPHANUM, 'The ID of the activity or "course" for the course grade item'),
                            'itemnumber'  => new external_value(PARAM_INT, 'Will be 0 unless the module has multiple grades'),
                            'scaleid' => new external_value(PARAM_INT, 'The ID of the custom scale or 0'),
                            'name' => new external_value(PARAM_RAW, 'The module name'),
                            'grademin' => new external_value(PARAM_FLOAT, 'Minimum grade'),
                            'grademax' => new external_value(PARAM_FLOAT, 'Maximum grade'),
                            'gradepass' => new external_value(PARAM_FLOAT, 'The passing grade threshold'),
                            'locked' => new external_value(PARAM_INT, '0 means not locked, > 1 is a date to lock until'),
                            'hidden' => new external_value(PARAM_INT, '0 means not hidden, > 1 is a date to hide until'),
                            'grades' => new external_multiple_structure(
                                new external_single_structure(
                                    array(
                                        'userid' => new external_value(
                                            PARAM_INT, 'Student ID'),
                                        'grade' => new external_value(
                                            PARAM_FLOAT, 'Student grade'),
                                        'locked' => new external_value(
                                            PARAM_INT, '0 means not locked, > 1 is a date to lock until'),
                                        'hidden' => new external_value(
                                            PARAM_INT, '0 means not hidden, 1 hidden, > 1 is a date to hide until'),
                                        'overridden' => new external_value(
                                            PARAM_INT, '0 means not overridden, > 1 means overridden'),
                                        'feedback' => new external_value(
                                            PARAM_RAW, 'Feedback from the grader'),
                                        'feedbackformat' => new external_value(
                                            PARAM_INT, 'The format of the feedback'),
                                        'usermodified' => new external_value(
                                            PARAM_INT, 'The ID of the last user to modify this student grade'),
                                        'datesubmitted' => new external_value(
                                            PARAM_INT, 'A timestamp indicating when the student submitted the activity'),
                                        'dategraded' => new external_value(
                                            PARAM_INT, 'A timestamp indicating when the assignment was grades'),
                                        'str_grade' => new external_value(
                                            PARAM_RAW, 'A string representation of the grade'),
                                        'str_long_grade' => new external_value(
                                            PARAM_RAW, 'A nicely formatted string representation of the grade'),
                                        'str_feedback' => new external_value(
                                            PARAM_RAW, 'A formatted string representation of the feedback from the grader'),
                                    )
                                )
                            ),
                        )
                    )
                ),
                'outcomes'  => new external_multiple_structure(
                    new external_single_structure(
                        array(
                            'activityid' => new external_value(
                                PARAM_ALPHANUM, 'The ID of the activity or "course" for the course grade item'),
                            'itemnumber'  => new external_value(PARAM_INT, 'Will be 0 unless the module has multiple grades'),
                            'scaleid' => new external_value(PARAM_INT, 'The ID of the custom scale or 0'),
                            'name' => new external_value(PARAM_RAW, 'The module name'),
                            'locked' => new external_value(PARAM_INT, '0 means not locked, > 1 is a date to lock until'),
                            'hidden' => new external_value(PARAM_INT, '0 means not hidden, > 1 is a date to hide until'),
                            'grades' => new external_multiple_structure(
                                new external_single_structure(
                                    array(
                                        'userid' => new external_value(
                                            PARAM_INT, 'Student ID'),
                                        'grade' => new external_value(
                                            PARAM_FLOAT, 'Student grade'),
                                        'locked' => new external_value(
                                            PARAM_INT, '0 means not locked, > 1 is a date to lock until'),
                                        'hidden' => new external_value(
                                            PARAM_INT, '0 means not hidden, 1 hidden, > 1 is a date to hide until'),
                                        'feedback' => new external_value(
                                            PARAM_RAW, 'Feedback from the grader'),
                                        'feedbackformat' => new external_value(
                                            PARAM_INT, 'The feedback format'),
                                        'usermodified' => new external_value(
                                            PARAM_INT, 'The ID of the last user to modify this student grade'),
                                        'str_grade' => new external_value(
                                            PARAM_RAW, 'A string representation of the grade'),
                                        'str_feedback' => new external_value(
                                            PARAM_RAW, 'A formatted string representation of the feedback from the grader'),
                                    )
                                )
                            ),
                        )
                    ), 'An array of outcomes associated with the grade items', VALUE_OPTIONAL
                )
            )
        );

    }

    
    public static function update_grades_parameters() {
        return new external_function_parameters(
            array(
                'source' => new external_value(PARAM_TEXT, 'The source of the grade update'),
                'courseid' => new external_value(PARAM_INT, 'id of course'),
                'component' => new external_value(PARAM_COMPONENT, 'A component, for example mod_forum or mod_quiz'),
                'activityid' => new external_value(PARAM_INT, 'The activity ID'),
                'itemnumber' => new external_value(
                    PARAM_INT, 'grade item ID number for modules that have multiple grades. Typically this is 0.'),
                'grades' => new external_multiple_structure(
                    new external_single_structure(
                        array(
                            'studentid' => new external_value(PARAM_INT, 'Student ID'),
                            'grade' => new external_value(PARAM_FLOAT, 'Student grade'),
                            'str_feedback' => new external_value(
                                PARAM_TEXT, 'A string representation of the feedback from the grader', VALUE_OPTIONAL),
                        )
                ), 'Any student grades to alter', VALUE_DEFAULT, array()),
                'itemdetails' => new external_single_structure(
                    array(
                        'itemname' => new external_value(
                            PARAM_ALPHANUMEXT, 'The grade item name', VALUE_OPTIONAL),
                        'idnumber' => new external_value(
                            PARAM_INT, 'Arbitrary ID provided by the module responsible for the grade item', VALUE_OPTIONAL),
                        'gradetype' => new external_value(
                            PARAM_INT, 'The type of grade (0 = none, 1 = value, 2 = scale, 3 = text)', VALUE_OPTIONAL),
                        'grademax' => new external_value(
                            PARAM_FLOAT, 'Maximum grade allowed', VALUE_OPTIONAL),
                        'grademin' => new external_value(
                            PARAM_FLOAT, 'Minimum grade allowed', VALUE_OPTIONAL),
                        'scaleid' => new external_value(
                            PARAM_INT, 'The ID of the custom scale being is used', VALUE_OPTIONAL),
                        'multfactor' => new external_value(
                            PARAM_FLOAT, 'Multiply all grades by this number', VALUE_OPTIONAL),
                        'plusfactor' => new external_value(
                            PARAM_FLOAT, 'Add this to all grades', VALUE_OPTIONAL),
                        'deleted' => new external_value(
                            PARAM_BOOL, 'True if the grade item should be deleted', VALUE_OPTIONAL),
                        'hidden' => new external_value(
                            PARAM_BOOL, 'True if the grade item is hidden', VALUE_OPTIONAL),
                    ), 'Any grade item settings to alter', VALUE_DEFAULT, array()
                )
            )
        );
    }

    
    public static function update_grades($source, $courseid, $component, $activityid,
        $itemnumber, $grades = array(), $itemdetails = array()) {
        global $CFG;

        $params = self::validate_parameters(
            self::update_grades_parameters(),
            array(
                'source' => $source,
                'courseid' => $courseid,
                'component' => $component,
                'activityid' => $activityid,
                'itemnumber' => $itemnumber,
                'grades' => $grades,
                'itemdetails' => $itemdetails
            )
        );

        list($itemtype, $itemmodule) = normalize_component($params['component']);

        if (! $cm = get_coursemodule_from_id($itemmodule, $activityid)) {
            throw new moodle_exception('invalidcoursemodule');
        }
        $iteminstance = $cm->instance;

        $coursecontext = context_course::instance($params['courseid']);

        try {
            self::validate_context($coursecontext);
        } catch (Exception $e) {
            $exceptionparam = new stdClass();
            $exceptionparam->message = $e->getMessage();
            $exceptionparam->courseid = $params['courseid'];
            throw new moodle_exception('errorcoursecontextnotvalid' , 'webservice', '', $exceptionparam);
        }

        $hidinggrades = false;
        $editinggradeitem = false;
        $editinggrades = false;

        $gradestructure = array();
        foreach ($grades as $grade) {
            $editinggrades = true;
            $gradestructure[ $grade['studentid'] ] = array('userid' => $grade['studentid'], 'rawgrade' => $grade['grade']);
        }
        if (!empty($params['itemdetails'])) {
            if (isset($params['itemdetails']['hidden'])) {
                $hidinggrades = true;
            } else {
                $editinggradeitem = true;
            }
        }

        if ($editinggradeitem && !has_capability('moodle/grade:manage', $coursecontext)) {
            throw new moodle_exception('nopermissiontoviewgrades', 'error', '', null,
                'moodle/grade:manage required to edit grade information');
        }
        if ($hidinggrades && !has_capability('moodle/grade:hide', $coursecontext) &&
            !has_capability('moodle/grade:hide', $coursecontext)) {
            throw new moodle_exception('nopermissiontoviewgrades', 'error', '', null,
                'moodle/grade:hide required to hide grade items');
        }
        if ($editinggrades && !has_capability('moodle/grade:edit', $coursecontext)) {
            throw new moodle_exception('nopermissiontoviewgrades', 'error', '', null,
                'moodle/grade:edit required to edit grades');
        }

        return grade_update($params['source'], $params['courseid'], $itemtype,
            $itemmodule, $iteminstance, $itemnumber, $gradestructure, $params['itemdetails']);
    }

    
    public static function update_grades_returns() {
        return new external_value(
            PARAM_INT,
            'A value like ' . GRADE_UPDATE_OK . ' => OK, ' . GRADE_UPDATE_FAILED . ' => FAILED
            as defined in lib/grade/constants.php'
        );
    }
}
