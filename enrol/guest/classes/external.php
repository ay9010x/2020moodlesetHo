<?php



defined('MOODLE_INTERNAL') || die;

require_once($CFG->libdir . '/externallib.php');
require_once($CFG->libdir . '/enrollib.php');


class enrol_guest_external extends external_api {

    
    public static function get_instance_info_parameters() {
        return new external_function_parameters(
                array('instanceid' => new external_value(PARAM_INT, 'Instance id of guest enrolment plugin.'))
            );
    }

    
    public static function get_instance_info($instanceid) {
        global $DB;

        $params = self::validate_parameters(self::get_instance_info_parameters(), array('instanceid' => $instanceid));
        $warnings = array();

                $enrolplugin = enrol_get_plugin('guest');
        if (empty($enrolplugin)) {
            throw new moodle_exception('invaliddata', 'error');
        }

        self::validate_context(context_system::instance());
        $enrolinstance = $DB->get_record('enrol', array('id' => $params['instanceid']), '*', MUST_EXIST);

        $course = $DB->get_record('course', array('id' => $enrolinstance->courseid), '*', MUST_EXIST);
        $context = context_course::instance($course->id);
        if (!$course->visible and !has_capability('moodle/course:viewhiddencourses', $context)) {
            throw new moodle_exception('coursehidden');
        }

        $instanceinfo = $enrolplugin->get_enrol_info($enrolinstance);
                $instanceinfo->passwordrequired = $instanceinfo->requiredparam->passwordrequired;

        unset($instanceinfo->requiredparam);

        $result = array();
        $result['instanceinfo'] = $instanceinfo;
        $result['warnings'] = $warnings;
        return $result;
    }

    
    public static function get_instance_info_returns() {
        return new external_single_structure(
            array(
                'instanceinfo' => new external_single_structure(
                    array(
                        'id' => new external_value(PARAM_INT, 'Id of course enrolment instance'),
                        'courseid' => new external_value(PARAM_INT, 'Id of course'),
                        'type' => new external_value(PARAM_PLUGIN, 'Type of enrolment plugin'),
                        'name' => new external_value(PARAM_RAW, 'Name of enrolment plugin'),
                        'status' => new external_value(PARAM_BOOL, 'Is the enrolment enabled?'),
                        'passwordrequired' => new external_value(PARAM_BOOL, 'Is a password required?'),
                    )
                ),
                'warnings' => new external_warnings()
            )
        );
    }

}
