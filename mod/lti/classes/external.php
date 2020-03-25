<?php



defined('MOODLE_INTERNAL') || die;

require_once($CFG->libdir . '/externallib.php');
require_once($CFG->dirroot . '/mod/lti/lib.php');
require_once($CFG->dirroot . '/mod/lti/locallib.php');


class mod_lti_external extends external_api {

    
    private static function tool_type_return_structure() {
        return new external_single_structure(
            array(
                'id' => new external_value(PARAM_INT, 'Tool type id'),
                'name' => new external_value(PARAM_NOTAGS, 'Tool type name'),
                'description' => new external_value(PARAM_NOTAGS, 'Tool type description'),
                'urls' => new external_single_structure(
                    array(
                        'icon' => new external_value(PARAM_URL, 'Tool type icon URL'),
                        'edit' => new external_value(PARAM_URL, 'Tool type edit URL'),
                        'course' => new external_value(PARAM_URL, 'Tool type edit URL', VALUE_OPTIONAL),
                    )
                ),
                'state' => new external_single_structure(
                    array(
                        'text' => new external_value(PARAM_TEXT, 'Tool type state name string'),
                        'pending' => new external_value(PARAM_BOOL, 'Is the state pending'),
                        'configured' => new external_value(PARAM_BOOL, 'Is the state configured'),
                        'rejected' => new external_value(PARAM_BOOL, 'Is the state rejected'),
                        'unknown' => new external_value(PARAM_BOOL, 'Is the state unknown'),
                    )
                ),
                'hascapabilitygroups' => new external_value(PARAM_BOOL, 'Indicate if capabilitygroups is populated'),
                'capabilitygroups' => new external_multiple_structure(
                    new external_value(PARAM_TEXT, 'Tool type capability groups enabled'),
                    'Array of capability groups', VALUE_DEFAULT, array()
                ),
                'courseid' => new external_value(PARAM_INT, 'Tool type course', VALUE_DEFAULT, 0),
                'instanceids' => new external_multiple_structure(
                    new external_value(PARAM_INT, 'LTI instance ID'),
                    'IDs for the LTI instances using this type', VALUE_DEFAULT, array()
                ),
                'instancecount' => new external_value(PARAM_INT, 'The number of times this tool is being used')
            ), 'Tool'
        );
    }

    
    private static function tool_proxy_return_structure() {
        return new external_function_parameters(
            array(
                'id' => new external_value(PARAM_INT, 'Tool proxy id'),
                'name' => new external_value(PARAM_TEXT, 'Tool proxy name'),
                'regurl' => new external_value(PARAM_URL, 'Tool proxy registration URL'),
                'state' => new external_value(PARAM_INT, 'Tool proxy state'),
                'guid' => new external_value(PARAM_TEXT, 'Tool proxy globally unique identifier'),
                'secret' => new external_value(PARAM_TEXT, 'Tool proxy shared secret'),
                'vendorcode' => new external_value(PARAM_TEXT, 'Tool proxy consumer code'),
                'capabilityoffered' => new external_value(PARAM_TEXT, 'Tool proxy capabilities offered'),
                'serviceoffered' => new external_value(PARAM_TEXT, 'Tool proxy services offered'),
                'toolproxy' => new external_value(PARAM_TEXT, 'Tool proxy'),
                'timecreated' => new external_value(PARAM_INT, 'Tool proxy time created'),
                'timemodified' => new external_value(PARAM_INT, 'Tool proxy modified'),
            )
        );
    }

    
    public static function get_tool_proxies_parameters() {
        return new external_function_parameters(
            array(
                'orphanedonly' => new external_value(PARAM_BOOL, 'Orphaned tool types only', VALUE_DEFAULT, 0)
            )
        );
    }

    
    public static function get_tool_proxies($orphanedonly) {
        global $PAGE;
        $params = self::validate_parameters(self::get_tool_proxies_parameters(),
                                            array(
                                                'orphanedonly' => $orphanedonly
                                            ));
        $orphanedonly = $params['orphanedonly'];

        $proxies = array();
        $context = context_system::instance();

        self::validate_context($context);
        require_capability('moodle/site:config', $context);

        $proxies = lti_get_tool_proxies($orphanedonly);

        return array_map('serialise_tool_proxy', $proxies);
    }

    
    public static function get_tool_proxies_returns() {
        return new external_multiple_structure(
            self::tool_type_return_structure()
        );
    }

    
    public static function get_tool_launch_data_parameters() {
        return new external_function_parameters(
            array(
                'toolid' => new external_value(PARAM_INT, 'external tool instance id')
            )
        );
    }

    
    public static function get_tool_launch_data($toolid) {
        global $DB, $CFG;
        require_once($CFG->dirroot . '/mod/lti/lib.php');

        $params = self::validate_parameters(self::get_tool_launch_data_parameters(),
                                            array(
                                                'toolid' => $toolid
                                            ));
        $warnings = array();

                $lti = $DB->get_record('lti', array('id' => $params['toolid']), '*', MUST_EXIST);
        list($course, $cm) = get_course_and_cm_from_instance($lti, 'lti');

        $context = context_module::instance($cm->id);
        self::validate_context($context);

        require_capability('mod/lti:view', $context);

        $lti->cmid = $cm->id;
        list($endpoint, $parms) = lti_get_launch_data($lti);

        $parameters = array();
        foreach ($parms as $name => $value) {
            $parameters[] = array(
                'name' => $name,
                'value' => $value
            );
        }

        $result = array();
        $result['endpoint'] = $endpoint;
        $result['parameters'] = $parameters;
        $result['warnings'] = $warnings;
        return $result;
    }

    
    public static function get_tool_launch_data_returns() {
        return new external_single_structure(
            array(
                'endpoint' => new external_value(PARAM_RAW, 'Endpoint URL'),                 'parameters' => new external_multiple_structure(
                    new external_single_structure(
                        array(
                            'name' => new external_value(PARAM_NOTAGS, 'Parameter name'),
                            'value' => new external_value(PARAM_RAW, 'Parameter value')
                        )
                    )
                ),
                'warnings' => new external_warnings()
            )
        );
    }

    
    public static function get_ltis_by_courses_parameters() {
        return new external_function_parameters (
            array(
                'courseids' => new external_multiple_structure(
                    new external_value(PARAM_INT, 'course id'), 'Array of course ids', VALUE_DEFAULT, array()
                ),
            )
        );
    }

    
    public static function get_ltis_by_courses($courseids = array()) {
        global $CFG;

        $returnedltis = array();
        $warnings = array();

        $params = self::validate_parameters(self::get_ltis_by_courses_parameters(), array('courseids' => $courseids));

        $mycourses = array();
        if (empty($params['courseids'])) {
            $mycourses = enrol_get_my_courses();
            $params['courseids'] = array_keys($mycourses);
        }

                if (!empty($params['courseids'])) {

            list($courses, $warnings) = external_util::validate_courses($params['courseids'], $mycourses);

                                    $ltis = get_all_instances_in_courses("lti", $courses);

            foreach ($ltis as $lti) {

                $context = context_module::instance($lti->coursemodule);

                                $module = array();

                                $module['id'] = $lti->id;
                $module['coursemodule'] = $lti->coursemodule;
                $module['course'] = $lti->course;
                $module['name']  = external_format_string($lti->name, $context->id);

                $viewablefields = [];
                if (has_capability('mod/lti:view', $context)) {
                    list($module['intro'], $module['introformat']) =
                        external_format_text($lti->intro, $lti->introformat, $context->id, 'mod_lti', 'intro', null);

                    $viewablefields = array('launchcontainer', 'showtitlelaunch', 'showdescriptionlaunch', 'icon', 'secureicon');
                }

                                if (has_capability('moodle/course:manageactivities', $context)) {

                    $additionalfields = array('timecreated', 'timemodified', 'typeid', 'toolurl', 'securetoolurl',
                        'instructorchoicesendname', 'instructorchoicesendemailaddr', 'instructorchoiceallowroster',
                        'instructorchoiceallowsetting', 'instructorcustomparameters', 'instructorchoiceacceptgrades', 'grade',
                        'resourcekey', 'password', 'debuglaunch', 'servicesalt', 'visible', 'groupmode', 'groupingid');
                    $viewablefields = array_merge($viewablefields, $additionalfields);

                }

                foreach ($viewablefields as $field) {
                    $module[$field] = $lti->{$field};
                }

                $returnedltis[] = $module;
            }
        }

        $result = array();
        $result['ltis'] = $returnedltis;
        $result['warnings'] = $warnings;
        return $result;
    }

    
    public static function get_ltis_by_courses_returns() {

        return new external_single_structure(
            array(
                'ltis' => new external_multiple_structure(
                    new external_single_structure(
                        array(
                            'id' => new external_value(PARAM_INT, 'External tool id'),
                            'coursemodule' => new external_value(PARAM_INT, 'Course module id'),
                            'course' => new external_value(PARAM_INT, 'Course id'),
                            'name' => new external_value(PARAM_RAW, 'LTI name'),
                            'intro' => new external_value(PARAM_RAW, 'The LTI intro', VALUE_OPTIONAL),
                            'introformat' => new external_format_value('intro', VALUE_OPTIONAL),
                            'timecreated' => new external_value(PARAM_INT, 'Time of creation', VALUE_OPTIONAL),
                            'timemodified' => new external_value(PARAM_INT, 'Time of last modification', VALUE_OPTIONAL),
                            'typeid' => new external_value(PARAM_INT, 'Type id', VALUE_OPTIONAL),
                            'toolurl' => new external_value(PARAM_URL, 'Tool url', VALUE_OPTIONAL),
                            'securetoolurl' => new external_value(PARAM_RAW, 'Secure tool url', VALUE_OPTIONAL),
                            'instructorchoicesendname' => new external_value(PARAM_TEXT, 'Instructor choice send name',
                                                                               VALUE_OPTIONAL),
                            'instructorchoicesendemailaddr' => new external_value(PARAM_INT, 'instructor choice send mail address',
                                                                                    VALUE_OPTIONAL),
                            'instructorchoiceallowroster' => new external_value(PARAM_INT, 'Instructor choice allow roster',
                                                                                VALUE_OPTIONAL),
                            'instructorchoiceallowsetting' => new external_value(PARAM_INT, 'Instructor choice allow setting',
                                                                                 VALUE_OPTIONAL),
                            'instructorcustomparameters' => new external_value(PARAM_RAW, 'instructor custom parameters',
                                                                                VALUE_OPTIONAL),
                            'instructorchoiceacceptgrades' => new external_value(PARAM_INT, 'instructor choice accept grades',
                                                                                    VALUE_OPTIONAL),
                            'grade' => new external_value(PARAM_INT, 'Enable grades', VALUE_OPTIONAL),
                            'launchcontainer' => new external_value(PARAM_INT, 'Launch container mode', VALUE_OPTIONAL),
                            'resourcekey' => new external_value(PARAM_RAW, 'Resource key', VALUE_OPTIONAL),
                            'password' => new external_value(PARAM_RAW, 'Shared secret', VALUE_OPTIONAL),
                            'debuglaunch' => new external_value(PARAM_INT, 'Debug launch', VALUE_OPTIONAL),
                            'showtitlelaunch' => new external_value(PARAM_INT, 'Show title launch', VALUE_OPTIONAL),
                            'showdescriptionlaunch' => new external_value(PARAM_INT, 'Show description launch', VALUE_OPTIONAL),
                            'servicesalt' => new external_value(PARAM_RAW, 'Service salt', VALUE_OPTIONAL),
                            'icon' => new external_value(PARAM_URL, 'Alternative icon URL', VALUE_OPTIONAL),
                            'secureicon' => new external_value(PARAM_URL, 'Secure icon URL', VALUE_OPTIONAL),
                            'section' => new external_value(PARAM_INT, 'course section id', VALUE_OPTIONAL),
                            'visible' => new external_value(PARAM_INT, 'visible', VALUE_OPTIONAL),
                            'groupmode' => new external_value(PARAM_INT, 'group mode', VALUE_OPTIONAL),
                            'groupingid' => new external_value(PARAM_INT, 'group id', VALUE_OPTIONAL),
                        ), 'Tool'
                    )
                ),
                'warnings' => new external_warnings(),
            )
        );
    }

    
    public static function view_lti_parameters() {
        return new external_function_parameters(
            array(
                'ltiid' => new external_value(PARAM_INT, 'lti instance id')
            )
        );
    }

    
    public static function view_lti($ltiid) {
        global $DB;

        $params = self::validate_parameters(self::view_lti_parameters(),
                                            array(
                                                'ltiid' => $ltiid
                                            ));
        $warnings = array();

                $lti = $DB->get_record('lti', array('id' => $params['ltiid']), '*', MUST_EXIST);
        list($course, $cm) = get_course_and_cm_from_instance($lti, 'lti');

        $context = context_module::instance($cm->id);
        self::validate_context($context);
        require_capability('mod/lti:view', $context);

                lti_view($lti, $course, $cm, $context);

        $result = array();
        $result['status'] = true;
        $result['warnings'] = $warnings;
        return $result;
    }

    
    public static function view_lti_returns() {
        return new external_single_structure(
            array(
                'status' => new external_value(PARAM_BOOL, 'status: true if success'),
                'warnings' => new external_warnings()
            )
        );
    }

    
    public static function create_tool_proxy_parameters() {
        return new external_function_parameters(
            array(
                'name' => new external_value(PARAM_TEXT, 'Tool proxy name', VALUE_DEFAULT, ''),
                'regurl' => new external_value(PARAM_URL, 'Tool proxy registration URL'),
                'capabilityoffered' => new external_multiple_structure(
                    new external_value(PARAM_TEXT, 'Tool proxy capabilities offered'),
                    'Array of capabilities', VALUE_DEFAULT, array()
                ),
                'serviceoffered' => new external_multiple_structure(
                    new external_value(PARAM_TEXT, 'Tool proxy services offered'),
                    'Array of services', VALUE_DEFAULT, array()
                )
            )
        );
    }

    
    public static function create_tool_proxy($name, $registrationurl, $capabilityoffered, $serviceoffered) {
        $params = self::validate_parameters(self::create_tool_proxy_parameters(),
                                            array(
                                                'name' => $name,
                                                'regurl' => $registrationurl,
                                                'capabilityoffered' => $capabilityoffered,
                                                'serviceoffered' => $serviceoffered
                                            ));
        $name = $params['name'];
        $regurl = $params['regurl'];
        $capabilityoffered = $params['capabilityoffered'];
        $serviceoffered = $params['serviceoffered'];

        $context = context_system::instance();
        self::validate_context($context);
        require_capability('moodle/site:config', $context);

                $duplicates = lti_get_tool_proxies_from_registration_url($registrationurl);
        if (!empty($duplicates)) {
            throw new moodle_exception('duplicateregurl', 'mod_lti');
        }

        $config = new stdClass();
        $config->lti_registrationurl = $registrationurl;

        if (!empty($name)) {
            $config->lti_registrationname = $name;
        }

        if (!empty($capabilityoffered)) {
            $config->lti_capabilities = $capabilityoffered;
        }

        if (!empty($serviceoffered)) {
            $config->lti_services = $serviceoffered;
        }

        $id = lti_add_tool_proxy($config);
        $toolproxy = lti_get_tool_proxy($id);

                        $toolproxy->state = LTI_TOOL_PROXY_STATE_PENDING;
        lti_update_tool_proxy($toolproxy);

        return $toolproxy;
    }

    
    public static function create_tool_proxy_returns() {
        return self::tool_proxy_return_structure();
    }

    
    public static function delete_tool_proxy_parameters() {
        return new external_function_parameters(
            array(
                'id' => new external_value(PARAM_INT, 'Tool proxy id'),
            )
        );
    }

    
    public static function delete_tool_proxy($id) {
        $params = self::validate_parameters(self::delete_tool_proxy_parameters(),
                                            array(
                                                'id' => $id,
                                            ));
        $id = $params['id'];

        $context = context_system::instance();
        self::validate_context($context);
        require_capability('moodle/site:config', $context);

        $toolproxy = lti_get_tool_proxy($id);

        lti_delete_tool_proxy($id);

        return $toolproxy;
    }

    
    public static function delete_tool_proxy_returns() {
        return self::tool_proxy_return_structure();
    }

    
    public static function get_tool_proxy_registration_request_parameters() {
        return new external_function_parameters(
            array(
                'id' => new external_value(PARAM_INT, 'Tool proxy id'),
            )
        );
    }

    
    public static function get_tool_proxy_registration_request($id) {
        $params = self::validate_parameters(self::get_tool_proxy_registration_request_parameters(),
                                            array(
                                                'id' => $id,
                                            ));
        $id = $params['id'];

        $context = context_system::instance();
        self::validate_context($context);
        require_capability('moodle/site:config', $context);

        $toolproxy = lti_get_tool_proxy($id);
        return lti_build_registration_request($toolproxy);
    }

    
    public static function get_tool_proxy_registration_request_returns() {
        return new external_function_parameters(
            array(
                'lti_message_type' => new external_value(PARAM_ALPHANUMEXT, 'LTI message type'),
                'lti_version' => new external_value(PARAM_ALPHANUMEXT, 'LTI version'),
                'reg_key' => new external_value(PARAM_TEXT, 'Tool proxy registration key'),
                'reg_password' => new external_value(PARAM_TEXT, 'Tool proxy registration password'),
                'reg_url' => new external_value(PARAM_TEXT, 'Tool proxy registration url'),
                'tc_profile_url' => new external_value(PARAM_URL, 'Tool consumers profile URL'),
                'launch_presentation_return_url' => new external_value(PARAM_URL, 'URL to redirect on registration completion'),
            )
        );
    }

    
    public static function get_tool_types_parameters() {
        return new external_function_parameters(
            array(
                'toolproxyid' => new external_value(PARAM_INT, 'Tool proxy id', VALUE_DEFAULT, 0)
            )
        );
    }

    
    public static function get_tool_types($toolproxyid) {
        global $PAGE;
        $params = self::validate_parameters(self::get_tool_types_parameters(),
                                            array(
                                                'toolproxyid' => $toolproxyid
                                            ));
        $toolproxyid = $params['toolproxyid'];

        $types = array();
        $context = context_system::instance();

        self::validate_context($context);
        require_capability('moodle/site:config', $context);

        if (!empty($toolproxyid)) {
            $types = lti_get_lti_types_from_proxy_id($toolproxyid);
        } else {
            $types = lti_get_lti_types();
        }

        return array_map("serialise_tool_type", array_values($types));
    }

    
    public static function get_tool_types_returns() {
        return new external_multiple_structure(
            self::tool_type_return_structure()
        );
    }

    
    public static function create_tool_type_parameters() {
        return new external_function_parameters(
            array(
                'cartridgeurl' => new external_value(PARAM_URL, 'URL to cardridge to load tool information', VALUE_DEFAULT, ''),
                'key' => new external_value(PARAM_TEXT, 'Consumer key', VALUE_DEFAULT, ''),
                'secret' => new external_value(PARAM_TEXT, 'Shared secret', VALUE_DEFAULT, ''),
            )
        );
    }

    
    public static function create_tool_type($cartridgeurl, $key, $secret) {
        $params = self::validate_parameters(self::create_tool_type_parameters(),
                                            array(
                                                'cartridgeurl' => $cartridgeurl,
                                                'key' => $key,
                                                'secret' => $secret
                                            ));
        $cartridgeurl = $params['cartridgeurl'];
        $key = $params['key'];
        $secret = $params['secret'];

        $context = context_system::instance();
        self::validate_context($context);
        require_capability('moodle/site:config', $context);

        $id = null;

        if (!empty($cartridgeurl)) {
            $type = new stdClass();
            $data = new stdClass();
            $type->state = LTI_TOOL_STATE_CONFIGURED;
            $data->lti_coursevisible = 1;
            $data->lti_sendname = LTI_SETTING_DELEGATE;
            $data->lti_sendemailaddr = LTI_SETTING_DELEGATE;
            $data->lti_acceptgrades = LTI_SETTING_DELEGATE;
            $data->lti_forcessl = 0;

            if (!empty($key)) {
                $data->lti_resourcekey = $key;
            }

            if (!empty($secret)) {
                $data->lti_password = $secret;
            }

            lti_load_type_from_cartridge($cartridgeurl, $data);
            if (empty($data->lti_toolurl)) {
                throw new moodle_exception('unabletocreatetooltype', 'mod_lti');
            } else {
                $id = lti_add_type($type, $data);
            }
        }

        if (!empty($id)) {
            $type = lti_get_type($id);
            return serialise_tool_type($type);
        } else {
            throw new moodle_exception('unabletocreatetooltype', 'mod_lti');
        }
    }

    
    public static function create_tool_type_returns() {
        return self::tool_type_return_structure();
    }

    
    public static function update_tool_type_parameters() {
        return new external_function_parameters(
            array(
                'id' => new external_value(PARAM_INT, 'Tool type id'),
                'name' => new external_value(PARAM_RAW, 'Tool type name', VALUE_DEFAULT, null),
                'description' => new external_value(PARAM_RAW, 'Tool type description', VALUE_DEFAULT, null),
                'state' => new external_value(PARAM_INT, 'Tool type state', VALUE_DEFAULT, null)
            )
        );
    }

    
    public static function update_tool_type($id, $name, $description, $state) {
        $params = self::validate_parameters(self::update_tool_type_parameters(),
                                            array(
                                                'id' => $id,
                                                'name' => $name,
                                                'description' => $description,
                                                'state' => $state,
                                            ));
        $id = $params['id'];
        $name = $params['name'];
        $description = $params['description'];
        $state = $params['state'];

        $context = context_system::instance();
        self::validate_context($context);
        require_capability('moodle/site:config', $context);

        $type = lti_get_type($id);

        if (empty($type)) {
            throw new moodle_exception('unabletofindtooltype', 'mod_lti', '', array('id' => $id));
        }

        if (!empty($name)) {
            $type->name = $name;
        }

        if (!empty($description)) {
            $type->description = $description;
        }

        if (!empty($state)) {
                        if (in_array($state, array(1, 2, 3))) {
                $type->state = $state;
            } else {
                throw new moodle_exception("Invalid state: $state - must be 1, 2, or 3");
            }
        }

        lti_update_type($type, new stdClass());

        return serialise_tool_type($type);
    }

    
    public static function update_tool_type_returns() {
        return self::tool_type_return_structure();
    }

    
    public static function delete_tool_type_parameters() {
        return new external_function_parameters(
            array(
                'id' => new external_value(PARAM_INT, 'Tool type id'),
            )
        );
    }

    
    public static function delete_tool_type($id) {
        $params = self::validate_parameters(self::delete_tool_type_parameters(),
                                            array(
                                                'id' => $id,
                                            ));
        $id = $params['id'];

        $context = context_system::instance();
        self::validate_context($context);
        require_capability('moodle/site:config', $context);

        $type = lti_get_type($id);

        if (!empty($type)) {
            lti_delete_type($id);

                                    $types = lti_get_lti_types_from_proxy_id($type->toolproxyid);
            if (empty($types)) {
                lti_delete_tool_proxy($type->toolproxyid);
            }
        }

        return array('id' => $id);
    }

    
    public static function delete_tool_type_returns() {
        return new external_function_parameters(
            array(
                'id' => new external_value(PARAM_INT, 'Tool type id'),
            )
        );
    }

    
    public static function is_cartridge_parameters() {
        return new external_function_parameters(
            array(
                'url' => new external_value(PARAM_URL, 'Tool url'),
            )
        );
    }

    
    public static function is_cartridge($url) {
        $params = self::validate_parameters(self::is_cartridge_parameters(),
                                            array(
                                                'url' => $url,
                                            ));
        $url = $params['url'];

        $context = context_system::instance();
        self::validate_context($context);
        require_capability('moodle/site:config', $context);

        $iscartridge = lti_is_cartridge($url);

        return array('iscartridge' => $iscartridge);
    }

    
    public static function is_cartridge_returns() {
        return new external_function_parameters(
            array(
                'iscartridge' => new external_value(PARAM_BOOL, 'True if the URL is a cartridge'),
            )
        );
    }
}
