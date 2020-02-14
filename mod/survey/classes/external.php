<?php



defined('MOODLE_INTERNAL') || die;

require_once($CFG->libdir . '/externallib.php');
require_once($CFG->dirroot . '/mod/survey/lib.php');


class mod_survey_external extends external_api {

    
    public static function get_surveys_by_courses_parameters() {
        return new external_function_parameters (
            array(
                'courseids' => new external_multiple_structure(
                    new external_value(PARAM_INT, 'course id'), 'Array of course ids', VALUE_DEFAULT, array()
                ),
            )
        );
    }

    
    public static function get_surveys_by_courses($courseids = array()) {
        global $CFG, $USER, $DB;

        $returnedsurveys = array();
        $warnings = array();

        $params = self::validate_parameters(self::get_surveys_by_courses_parameters(), array('courseids' => $courseids));

        $mycourses = array();
        if (empty($params['courseids'])) {
            $mycourses = enrol_get_my_courses();
            $params['courseids'] = array_keys($mycourses);
        }

                if (!empty($params['courseids'])) {

            list($courses, $warnings) = external_util::validate_courses($params['courseids'], $mycourses);

                                    $surveys = get_all_instances_in_courses("survey", $courses);
            foreach ($surveys as $survey) {
                $context = context_module::instance($survey->coursemodule);
                                $surveydetails = array();
                                $surveydetails['id'] = $survey->id;
                $surveydetails['coursemodule']      = $survey->coursemodule;
                $surveydetails['course']            = $survey->course;
                $surveydetails['name']              = external_format_string($survey->name, $context->id);

                if (has_capability('mod/survey:participate', $context)) {
                    $trimmedintro = trim($survey->intro);
                    if (empty($trimmedintro)) {
                        $tempo = $DB->get_field("survey", "intro", array("id" => $survey->template));
                        $survey->intro = get_string($tempo, "survey");
                    }

                                        list($surveydetails['intro'], $surveydetails['introformat']) =
                        external_format_text($survey->intro, $survey->introformat, $context->id, 'mod_survey', 'intro', null);

                    $surveydetails['template']  = $survey->template;
                    $surveydetails['days']      = $survey->days;
                    $surveydetails['questions'] = $survey->questions;
                    $surveydetails['surveydone'] = survey_already_done($survey->id, $USER->id) ? 1 : 0;

                }

                if (has_capability('moodle/course:manageactivities', $context)) {
                    $surveydetails['timecreated']   = $survey->timecreated;
                    $surveydetails['timemodified']  = $survey->timemodified;
                    $surveydetails['section']       = $survey->section;
                    $surveydetails['visible']       = $survey->visible;
                    $surveydetails['groupmode']     = $survey->groupmode;
                    $surveydetails['groupingid']    = $survey->groupingid;
                }
                $returnedsurveys[] = $surveydetails;
            }
        }
        $result = array();
        $result['surveys'] = $returnedsurveys;
        $result['warnings'] = $warnings;
        return $result;
    }

    
    public static function get_surveys_by_courses_returns() {
        return new external_single_structure(
            array(
                'surveys' => new external_multiple_structure(
                    new external_single_structure(
                        array(
                            'id' => new external_value(PARAM_INT, 'Survey id'),
                            'coursemodule' => new external_value(PARAM_INT, 'Course module id'),
                            'course' => new external_value(PARAM_INT, 'Course id'),
                            'name' => new external_value(PARAM_RAW, 'Survey name'),
                            'intro' => new external_value(PARAM_RAW, 'The Survey intro', VALUE_OPTIONAL),
                            'introformat' => new external_format_value('intro', VALUE_OPTIONAL),
                            'template' => new external_value(PARAM_INT, 'Survey type', VALUE_OPTIONAL),
                            'days' => new external_value(PARAM_INT, 'Days', VALUE_OPTIONAL),
                            'questions' => new external_value(PARAM_RAW, 'Question ids', VALUE_OPTIONAL),
                            'surveydone' => new external_value(PARAM_INT, 'Did I finish the survey?', VALUE_OPTIONAL),
                            'timecreated' => new external_value(PARAM_INT, 'Time of creation', VALUE_OPTIONAL),
                            'timemodified' => new external_value(PARAM_INT, 'Time of last modification', VALUE_OPTIONAL),
                            'section' => new external_value(PARAM_INT, 'Course section id', VALUE_OPTIONAL),
                            'visible' => new external_value(PARAM_INT, 'Visible', VALUE_OPTIONAL),
                            'groupmode' => new external_value(PARAM_INT, 'Group mode', VALUE_OPTIONAL),
                            'groupingid' => new external_value(PARAM_INT, 'Group id', VALUE_OPTIONAL),
                        ), 'Surveys'
                    )
                ),
                'warnings' => new external_warnings(),
            )
        );
    }

    
    public static function view_survey_parameters() {
        return new external_function_parameters(
            array(
                'surveyid' => new external_value(PARAM_INT, 'survey instance id')
            )
        );
    }

    
    public static function view_survey($surveyid) {
        global $DB, $USER;

        $params = self::validate_parameters(self::view_survey_parameters(),
                                            array(
                                                'surveyid' => $surveyid
                                            ));
        $warnings = array();

                $survey = $DB->get_record('survey', array('id' => $params['surveyid']), '*', MUST_EXIST);
        list($course, $cm) = get_course_and_cm_from_instance($survey, 'survey');

        $context = context_module::instance($cm->id);
        self::validate_context($context);
        require_capability('mod/survey:participate', $context);

        $viewed = survey_already_done($survey->id, $USER->id) ? 'graph' : 'form';

                survey_view($survey, $course, $cm, $context, $viewed);

        $result = array();
        $result['status'] = true;
        $result['warnings'] = $warnings;
        return $result;
    }

    
    public static function view_survey_returns() {
        return new external_single_structure(
            array(
                'status' => new external_value(PARAM_BOOL, 'status: true if success'),
                'warnings' => new external_warnings()
            )
        );
    }

    
    public static function get_questions_parameters() {
        return new external_function_parameters(
            array(
                'surveyid' => new external_value(PARAM_INT, 'survey instance id')
            )
        );
    }

    
    public static function get_questions($surveyid) {
        global $DB, $USER;

        $params = self::validate_parameters(self::get_questions_parameters(),
                                            array(
                                                'surveyid' => $surveyid
                                            ));
        $warnings = array();

                $survey = $DB->get_record('survey', array('id' => $params['surveyid']), '*', MUST_EXIST);
        list($course, $cm) = get_course_and_cm_from_instance($survey, 'survey');

        $context = context_module::instance($cm->id);
        self::validate_context($context);
        require_capability('mod/survey:participate', $context);

        $mainquestions = survey_get_questions($survey);

        foreach ($mainquestions as $question) {
            if ($question->type >= 0) {
                                $question->parent = 0;
                $questions[] = survey_translate_question($question);

                                if ($question->multi) {
                    $subquestions = survey_get_subquestions($question);
                    foreach ($subquestions as $sq) {
                        $sq->parent = $question->id;
                        $questions[] = survey_translate_question($sq);
                    }
                }
            }
        }

        $result = array();
        $result['questions'] = $questions;
        $result['warnings'] = $warnings;
        return $result;
    }

    
    public static function get_questions_returns() {
        return new external_single_structure(
            array(
                'questions' => new external_multiple_structure(
                    new external_single_structure(
                        array(
                            'id' => new external_value(PARAM_INT, 'Question id'),
                            'text' => new external_value(PARAM_RAW, 'Question text'),
                            'shorttext' => new external_value(PARAM_RAW, 'Question short text'),
                            'multi' => new external_value(PARAM_RAW, 'Subquestions ids'),
                            'intro' => new external_value(PARAM_RAW, 'The question intro'),
                            'type' => new external_value(PARAM_INT, 'Question type'),
                            'options' => new external_value(PARAM_RAW, 'Question options'),
                            'parent' => new external_value(PARAM_INT, 'Parent question (for subquestions)'),
                        ), 'Questions'
                    )
                ),
                'warnings' => new external_warnings()
            )
        );
    }

    
    public static function submit_answers_parameters() {
        return new external_function_parameters(
            array(
                'surveyid' => new external_value(PARAM_INT, 'Survey id'),
                'answers' => new external_multiple_structure(
                    new external_single_structure(
                        array(
                            'key' => new external_value(PARAM_RAW, 'Answer key'),
                            'value' => new external_value(PARAM_RAW, 'Answer value')
                        )
                    )
                ),
            )
        );
    }

    
    public static function submit_answers($surveyid, $answers) {
        global $DB, $USER;

        $params = self::validate_parameters(self::submit_answers_parameters(),
                                            array(
                                                'surveyid' => $surveyid,
                                                'answers' => $answers
                                            ));
        $warnings = array();

                $survey = $DB->get_record('survey', array('id' => $params['surveyid']), '*', MUST_EXIST);
        list($course, $cm) = get_course_and_cm_from_instance($survey, 'survey');

        $context = context_module::instance($cm->id);
        self::validate_context($context);
        require_capability('mod/survey:participate', $context);

        if (survey_already_done($survey->id, $USER->id)) {
            throw new moodle_exception("alreadysubmitted", "survey");
        }

                $answers = array();
        foreach ($params['answers'] as $answer) {
            $key = $answer['key'];
            $answers[$key] = $answer['value'];
        }

        survey_save_answers($survey, $answers, $course, $context);

        $result = array();
        $result['status'] = true;
        $result['warnings'] = $warnings;
        return $result;
    }

    
    public static function submit_answers_returns() {
        return new external_single_structure(
            array(
                'status' => new external_value(PARAM_BOOL, 'status: true if success'),
                'warnings' => new external_warnings()
            )
        );
    }

}
