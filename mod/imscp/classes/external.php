<?php



defined('MOODLE_INTERNAL') || die;

require_once("$CFG->libdir/externallib.php");


class mod_imscp_external extends external_api {

    
    public static function view_imscp_parameters() {
        return new external_function_parameters(
            array(
                'imscpid' => new external_value(PARAM_INT, 'imscp instance id')
            )
        );
    }

    
    public static function view_imscp($imscpid) {
        global $DB, $CFG;
        require_once($CFG->dirroot . "/mod/imscp/lib.php");

        $params = self::validate_parameters(self::view_imscp_parameters(),
                                            array(
                                                'imscpid' => $imscpid
                                            ));
        $warnings = array();

                $imscp = $DB->get_record('imscp', array('id' => $params['imscpid']), '*', MUST_EXIST);
        list($course, $cm) = get_course_and_cm_from_instance($imscp, 'imscp');

        $context = context_module::instance($cm->id);
        self::validate_context($context);

        require_capability('mod/imscp:view', $context);

                imscp_view($imscp, $course, $cm, $context);

        $result = array();
        $result['status'] = true;
        $result['warnings'] = $warnings;
        return $result;
    }

    
    public static function view_imscp_returns() {
        return new external_single_structure(
            array(
                'status' => new external_value(PARAM_BOOL, 'status: true if success'),
                'warnings' => new external_warnings()
            )
        );
    }

    
    public static function get_imscps_by_courses_parameters() {
        return new external_function_parameters (
            array(
                'courseids' => new external_multiple_structure(
                    new external_value(PARAM_INT, 'course id'), 'Array of course ids', VALUE_DEFAULT, array()
                ),
            )
        );
    }

    
    public static function get_imscps_by_courses($courseids = array()) {
        global $CFG;

        $returnedimscps = array();
        $warnings = array();

        $params = self::validate_parameters(self::get_imscps_by_courses_parameters(), array('courseids' => $courseids));

        $courses = array();
        if (empty($params['courseids'])) {
            $courses = enrol_get_my_courses();
            $params['courseids'] = array_keys($courses);
        }

                if (!empty($params['courseids'])) {

            list($courses, $warnings) = external_util::validate_courses($params['courseids'], $courses);

                                    $imscps = get_all_instances_in_courses("imscp", $courses);
            foreach ($imscps as $imscp) {
                $context = context_module::instance($imscp->coursemodule);

                                $imscpdetails = array();
                                $imscpdetails['id'] = $imscp->id;
                $imscpdetails['coursemodule']      = $imscp->coursemodule;
                $imscpdetails['course']            = $imscp->course;
                $imscpdetails['name']              = external_format_string($imscp->name, $context->id);

                if (has_capability('mod/imscp:view', $context)) {
                                        list($imscpdetails['intro'], $imscpdetails['introformat']) =
                        external_format_text($imscp->intro, $imscp->introformat, $context->id, 'mod_imscp', 'intro', null);
                }

                if (has_capability('moodle/course:manageactivities', $context)) {
                    $imscpdetails['revision']      = $imscp->revision;
                    $imscpdetails['keepold']       = $imscp->keepold;
                    $imscpdetails['structure']     = $imscp->structure;
                    $imscpdetails['timemodified']  = $imscp->timemodified;
                    $imscpdetails['section']       = $imscp->section;
                    $imscpdetails['visible']       = $imscp->visible;
                    $imscpdetails['groupmode']     = $imscp->groupmode;
                    $imscpdetails['groupingid']    = $imscp->groupingid;
                }
                $returnedimscps[] = $imscpdetails;
            }
        }
        $result = array();
        $result['imscps'] = $returnedimscps;
        $result['warnings'] = $warnings;
        return $result;
    }

    
    public static function get_imscps_by_courses_returns() {
        return new external_single_structure(
            array(
                'imscps' => new external_multiple_structure(
                    new external_single_structure(
                        array(
                            'id' => new external_value(PARAM_INT, 'IMSCP id'),
                            'coursemodule' => new external_value(PARAM_INT, 'Course module id'),
                            'course' => new external_value(PARAM_INT, 'Course id'),
                            'name' => new external_value(PARAM_RAW, 'Activity name'),
                            'intro' => new external_value(PARAM_RAW, 'The IMSCP intro', VALUE_OPTIONAL),
                            'introformat' => new external_format_value('intro', VALUE_OPTIONAL),
                            'revision' => new external_value(PARAM_INT, 'Revision', VALUE_OPTIONAL),
                            'keepold' => new external_value(PARAM_INT, 'Number of old IMSCP to keep', VALUE_OPTIONAL),
                            'structure' => new external_value(PARAM_RAW, 'IMSCP structure', VALUE_OPTIONAL),
                            'timemodified' => new external_value(PARAM_RAW, 'Time of last modification', VALUE_OPTIONAL),
                            'section' => new external_value(PARAM_INT, 'Course section id', VALUE_OPTIONAL),
                            'visible' => new external_value(PARAM_BOOL, 'If visible', VALUE_OPTIONAL),
                            'groupmode' => new external_value(PARAM_INT, 'Group mode', VALUE_OPTIONAL),
                            'groupingid' => new external_value(PARAM_INT, 'Group id', VALUE_OPTIONAL),
                        ), 'IMS content packages'
                    )
                ),
                'warnings' => new external_warnings(),
            )
        );
    }

}
