<?php



defined('MOODLE_INTERNAL') || die;

require_once("$CFG->libdir/externallib.php");


class mod_data_external extends external_api {

    
    public static function get_databases_by_courses_parameters() {
        return new external_function_parameters (
            array(
                'courseids' => new external_multiple_structure(
                    new external_value(PARAM_INT, 'course id', VALUE_REQUIRED),
                    'Array of course ids', VALUE_DEFAULT, array()
                ),
            )
        );
    }

    
    public static function get_databases_by_courses($courseids = array()) {
        global $CFG;

        $params = self::validate_parameters(self::get_databases_by_courses_parameters(), array('courseids' => $courseids));
        $warnings = array();

        $mycourses = array();
        if (empty($params['courseids'])) {
            $mycourses = enrol_get_my_courses();
            $params['courseids'] = array_keys($mycourses);
        }

                $arrdatabases = array();

                if (!empty($params['courseids'])) {

            list($dbcourses, $warnings) = external_util::validate_courses($params['courseids'], $mycourses);

                                    $databases = get_all_instances_in_courses("data", $dbcourses);

            foreach ($databases as $database) {

                $datacontext = context_module::instance($database->coursemodule);

                                $newdb = array();

                                $newdb['id'] = $database->id;
                $newdb['coursemodule'] = $database->coursemodule;
                $newdb['course'] = $database->course;
                $newdb['name']  = external_format_string($database->name, $datacontext->id);
                                list($newdb['intro'], $newdb['introformat']) =
                    external_format_text($database->intro, $database->introformat,
                                            $datacontext->id, 'mod_data', 'intro', null);

                                if (has_capability('mod/data:viewentry', $datacontext)) {
                    $viewablefields = array('comments', 'timeavailablefrom', 'timeavailableto', 'timeviewfrom',
                                            'timeviewto', 'requiredentries', 'requiredentriestoview');

                                                            foreach ($viewablefields as $field) {
                                                if (!property_exists($database, $field)) {
                            throw new invalid_response_exception('Missing database module required field: ' . $field);
                        }
                        $newdb[$field] = $database->{$field};
                    }
                }

                                                if (has_capability('moodle/course:manageactivities', $datacontext)) {

                    $additionalfields = array('maxentries', 'rssarticles', 'singletemplate', 'listtemplate',
                        'listtemplateheader', 'listtemplatefooter', 'addtemplate', 'rsstemplate', 'rsstitletemplate',
                        'csstemplate', 'jstemplate', 'asearchtemplate', 'approval', 'manageapproved', 'scale', 'assessed', 'assesstimestart',
                        'assesstimefinish', 'defaultsort', 'defaultsortdir', 'editany', 'notification', 'timemodified');

                                        foreach ($additionalfields as $field) {
                        if (property_exists($database, $field)) {
                            $newdb[$field] = $database->{$field};
                        }
                    }
                }

                $arrdatabases[] = $newdb;
            }
        }

        $result = array();
        $result['databases'] = $arrdatabases;
        $result['warnings'] = $warnings;
        return $result;
    }

    
    public static function get_databases_by_courses_returns() {

        return new external_single_structure(
            array(
                'databases' => new external_multiple_structure(
                    new external_single_structure(
                        array(
                            'id' => new external_value(PARAM_INT, 'Database id'),
                            'coursemodule' => new external_value(PARAM_INT, 'Course module id'),
                            'course' => new external_value(PARAM_INT, 'Course id'),
                            'name' => new external_value(PARAM_RAW, 'Database name'),
                            'intro' => new external_value(PARAM_RAW, 'The Database intro'),
                            'introformat' => new external_format_value('intro'),
                            'comments' => new external_value(PARAM_BOOL, 'comments enabled', VALUE_OPTIONAL),
                            'timeavailablefrom' => new external_value(PARAM_INT, 'timeavailablefrom field', VALUE_OPTIONAL),
                            'timeavailableto' => new external_value(PARAM_INT, 'timeavailableto field', VALUE_OPTIONAL),
                            'timeviewfrom' => new external_value(PARAM_INT, 'timeviewfrom field', VALUE_OPTIONAL),
                            'timeviewto' => new external_value(PARAM_INT, 'timeviewto field', VALUE_OPTIONAL),
                            'requiredentries' => new external_value(PARAM_INT, 'requiredentries field', VALUE_OPTIONAL),
                            'requiredentriestoview' => new external_value(PARAM_INT, 'requiredentriestoview field', VALUE_OPTIONAL),
                            'maxentries' => new external_value(PARAM_INT, 'maxentries field', VALUE_OPTIONAL),
                            'rssarticles' => new external_value(PARAM_INT, 'rssarticles field', VALUE_OPTIONAL),
                            'singletemplate' => new external_value(PARAM_RAW, 'singletemplate field', VALUE_OPTIONAL),
                            'listtemplate' => new external_value(PARAM_RAW, 'listtemplate field', VALUE_OPTIONAL),
                            'listtemplateheader' => new external_value(PARAM_RAW, 'listtemplateheader field', VALUE_OPTIONAL),
                            'listtemplatefooter' => new external_value(PARAM_RAW, 'listtemplatefooter field', VALUE_OPTIONAL),
                            'addtemplate' => new external_value(PARAM_RAW, 'addtemplate field', VALUE_OPTIONAL),
                            'rsstemplate' => new external_value(PARAM_RAW, 'rsstemplate field', VALUE_OPTIONAL),
                            'rsstitletemplate' => new external_value(PARAM_RAW, 'rsstitletemplate field', VALUE_OPTIONAL),
                            'csstemplate' => new external_value(PARAM_RAW, 'csstemplate field', VALUE_OPTIONAL),
                            'jstemplate' => new external_value(PARAM_RAW, 'jstemplate field', VALUE_OPTIONAL),
                            'asearchtemplate' => new external_value(PARAM_RAW, 'asearchtemplate field', VALUE_OPTIONAL),
                            'approval' => new external_value(PARAM_BOOL, 'approval field', VALUE_OPTIONAL),
                            'manageapproved' => new external_value(PARAM_BOOL, 'manageapproved field', VALUE_OPTIONAL),
                            'scale' => new external_value(PARAM_INT, 'scale field', VALUE_OPTIONAL),
                            'assessed' => new external_value(PARAM_INT, 'assessed field', VALUE_OPTIONAL),
                            'assesstimestart' => new external_value(PARAM_INT, 'assesstimestart field', VALUE_OPTIONAL),
                            'assesstimefinish' => new external_value(PARAM_INT, 'assesstimefinish field', VALUE_OPTIONAL),
                            'defaultsort' => new external_value(PARAM_INT, 'defaultsort field', VALUE_OPTIONAL),
                            'defaultsortdir' => new external_value(PARAM_INT, 'defaultsortdir field', VALUE_OPTIONAL),
                            'editany' => new external_value(PARAM_BOOL, 'editany field', VALUE_OPTIONAL),
                            'notification' => new external_value(PARAM_INT, 'notification field', VALUE_OPTIONAL),
                            'timemodified' => new external_value(PARAM_INT, 'Time modified', VALUE_OPTIONAL)
                        ), 'Database'
                    )
                ),
                'warnings' => new external_warnings(),
            )
        );
    }

}
