<?php



defined('MOODLE_INTERNAL') || die;

require_once("$CFG->libdir/externallib.php");


class mod_folder_external extends external_api {

    
    public static function view_folder_parameters() {
        return new external_function_parameters(
            array(
                'folderid' => new external_value(PARAM_INT, 'folder instance id')
            )
        );
    }

    
    public static function view_folder($folderid) {
        global $DB, $CFG;
        require_once($CFG->dirroot . "/mod/folder/lib.php");

        $params = self::validate_parameters(self::view_folder_parameters(),
                                            array(
                                                'folderid' => $folderid
                                            ));
        $warnings = array();

                $folder = $DB->get_record('folder', array('id' => $params['folderid']), '*', MUST_EXIST);
        list($course, $cm) = get_course_and_cm_from_instance($folder, 'folder');

        $context = context_module::instance($cm->id);
        self::validate_context($context);

        require_capability('mod/folder:view', $context);

                folder_view($folder, $course, $cm, $context);

        $result = array();
        $result['status'] = true;
        $result['warnings'] = $warnings;
        return $result;
    }

    
    public static function view_folder_returns() {
        return new external_single_structure(
            array(
                'status' => new external_value(PARAM_BOOL, 'status: true if success'),
                'warnings' => new external_warnings()
            )
        );
    }

}
