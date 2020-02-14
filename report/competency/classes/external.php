<?php


namespace report_competency;

defined('MOODLE_INTERNAL') || die();

require_once("$CFG->libdir/externallib.php");

use context_course;
use external_api;
use external_function_parameters;
use external_multiple_structure;
use external_single_structure;
use external_value;
use core_competency\external\user_competency_course_exporter;
use core_competency\external\user_summary_exporter;
use tool_lp\external\competency_summary_exporter;
use tool_lp\external\course_summary_exporter;


class external extends external_api {

    
    public static function data_for_report_parameters() {
        $courseid = new external_value(
            PARAM_INT,
            'The course id',
            VALUE_REQUIRED
        );
        $userid = new external_value(
            PARAM_INT,
            'The user id',
            VALUE_REQUIRED
        );
        $params = array(
            'courseid' => $courseid,
            'userid' => $userid
        );
        return new external_function_parameters($params);
    }

    
    public static function data_for_report($courseid, $userid) {
        global $PAGE;

        $params = self::validate_parameters(
            self::data_for_report_parameters(),
            array(
                'courseid' => $courseid,
                'userid' => $userid
            )
        );
        $context = context_course::instance($params['courseid']);
        self::validate_context($context);
        if (!is_enrolled($context, $params['userid'], 'moodle/competency:coursecompetencygradable')) {
            throw new coding_exception('invaliduser');
        }

        $renderable = new output\report($params['courseid'], $params['userid']);
        $renderer = $PAGE->get_renderer('report_competency');

        $data = $renderable->export_for_template($renderer);

        return $data;
    }

    
    public static function data_for_report_returns() {
        return new external_single_structure(array (
            'courseid' => new external_value(PARAM_INT, 'Course id'),
            'user' => user_summary_exporter::get_read_structure(),
            'course' => course_summary_exporter::get_read_structure(),
            'usercompetencies' => new external_multiple_structure(
                new external_single_structure(array(
                    'usercompetencycourse' => user_competency_course_exporter::get_read_structure(),
                    'competency' => competency_summary_exporter::get_read_structure()
                ))
            ),
            'pushratingstouserplans' => new external_value(PARAM_BOOL, 'True if rating is push to user plans')
        ));
    }

}
