<?php




defined('MOODLE_INTERNAL') || die();


class restricted_context_exception extends moodle_exception {
    
    function __construct() {
        parent::__construct('restrictedcontextexception', 'error');
    }
}


class external_api {

    
    private static $contextrestriction;

    
    public static function external_function_info($function, $strictness=MUST_EXIST) {
        global $DB, $CFG;

        if (!is_object($function)) {
            if (!$function = $DB->get_record('external_functions', array('name' => $function), '*', $strictness)) {
                return false;
            }
        }

                if (!class_exists($function->classname)) {
                        if (empty($function->classpath)) {
                $function->classpath = core_component::get_component_directory($function->component).'/externallib.php';
            } else {
                $function->classpath = $CFG->dirroot.'/'.$function->classpath;
            }
            if (!file_exists($function->classpath)) {
                throw new coding_exception('Cannot find file with external function implementation');
            }
            require_once($function->classpath);
            if (!class_exists($function->classname)) {
                throw new coding_exception('Cannot find external class');
            }
        }

        $function->ajax_method = $function->methodname.'_is_allowed_from_ajax';
        $function->parameters_method = $function->methodname.'_parameters';
        $function->returns_method    = $function->methodname.'_returns';
        $function->deprecated_method = $function->methodname.'_is_deprecated';

                if (!method_exists($function->classname, $function->methodname)) {
            throw new coding_exception('Missing implementation method of '.$function->classname.'::'.$function->methodname);
        }
        if (!method_exists($function->classname, $function->parameters_method)) {
            throw new coding_exception('Missing parameters description');
        }
        if (!method_exists($function->classname, $function->returns_method)) {
            throw new coding_exception('Missing returned values description');
        }
        if (method_exists($function->classname, $function->deprecated_method)) {
            if (call_user_func(array($function->classname, $function->deprecated_method)) === true) {
                $function->deprecated = true;
            }
        }
        $function->allowed_from_ajax = false;

                $function->parameters_desc = call_user_func(array($function->classname, $function->parameters_method));
        if (!($function->parameters_desc instanceof external_function_parameters)) {
            throw new coding_exception('Invalid parameters description');
        }

                $function->returns_desc = call_user_func(array($function->classname, $function->returns_method));
                if (!is_null($function->returns_desc) and !($function->returns_desc instanceof external_description)) {
            throw new coding_exception('Invalid return description');
        }

        
                                        $function->description = null;
        $servicesfile = core_component::get_component_directory($function->component).'/db/services.php';
        if (file_exists($servicesfile)) {
            $functions = null;
            include($servicesfile);
            if (isset($functions[$function->name]['description'])) {
                $function->description = $functions[$function->name]['description'];
            }
            if (isset($functions[$function->name]['testclientpath'])) {
                $function->testclientpath = $functions[$function->name]['testclientpath'];
            }
            if (isset($functions[$function->name]['type'])) {
                $function->type = $functions[$function->name]['type'];
            }
            if (isset($functions[$function->name]['ajax'])) {
                $function->allowed_from_ajax = $functions[$function->name]['ajax'];
            } else if (method_exists($function->classname, $function->ajax_method)) {
                if (call_user_func(array($function->classname, $function->ajax_method)) === true) {
                    debugging('External function ' . $function->ajax_method . '() function is deprecated.' .
                              'Set ajax=>true in db/service.php instead.', DEBUG_DEVELOPER);
                    $function->allowed_from_ajax = true;
                }
            }
            if (isset($functions[$function->name]['loginrequired'])) {
                $function->loginrequired = $functions[$function->name]['loginrequired'];
            } else {
                $function->loginrequired = true;
            }
        }

        return $function;
    }

    
    public static function call_external_function($function, $args, $ajaxonly=false) {
        global $PAGE, $COURSE, $CFG, $SITE;

        require_once($CFG->libdir . "/pagelib.php");

        $externalfunctioninfo = self::external_function_info($function);

        $currentpage = $PAGE;
        $currentcourse = $COURSE;
        $response = array();

        try {
                        if (!empty($CFG->moodlepageclass)) {
                if (!empty($CFG->moodlepageclassfile)) {
                    require_once($CFG->moodlepageclassfile);
                }
                $classname = $CFG->moodlepageclass;
            } else {
                $classname = 'moodle_page';
            }
            $PAGE = new $classname();
            $COURSE = clone($SITE);

            if ($ajaxonly && !$externalfunctioninfo->allowed_from_ajax) {
                throw new moodle_exception('servicenotavailable', 'webservice');
            }

                        if ($externalfunctioninfo->loginrequired) {
                if (defined('NO_MOODLE_COOKIES') && NO_MOODLE_COOKIES && !PHPUNIT_TEST) {
                    throw new moodle_exception('servicenotavailable', 'webservice');
                }
                if (!isloggedin()) {
                    throw new moodle_exception('servicenotavailable', 'webservice');
                } else {
                    require_sesskey();
                }
            }

                        $callable = array($externalfunctioninfo->classname, 'validate_parameters');
            $params = call_user_func($callable,
                                     $externalfunctioninfo->parameters_desc,
                                     $args);

                        $callable = array($externalfunctioninfo->classname, $externalfunctioninfo->methodname);
            $result = call_user_func_array($callable,
                                           array_values($params));

                        if ($externalfunctioninfo->returns_desc !== null) {
                $callable = array($externalfunctioninfo->classname, 'clean_returnvalue');
                $result = call_user_func($callable, $externalfunctioninfo->returns_desc, $result);
            }

            $response['error'] = false;
            $response['data'] = $result;
        } catch (Exception $e) {
            $exception = get_exception_info($e);
            unset($exception->a);
            $exception->backtrace = format_backtrace($exception->backtrace, true);
            if (!debugging('', DEBUG_DEVELOPER)) {
                unset($exception->debuginfo);
                unset($exception->backtrace);
            }
            $response['error'] = true;
            $response['exception'] = $exception;
                    }

        $PAGE = $currentpage;
        $COURSE = $currentcourse;

        return $response;
    }

    
    public static function set_context_restriction($context) {
        self::$contextrestriction = $context;
    }

    
    public static function set_timeout($seconds=360) {
        $seconds = ($seconds < 300) ? 300 : $seconds;
        core_php_time_limit::raise($seconds);
    }

    
    public static function validate_parameters(external_description $description, $params) {
        if ($description instanceof external_value) {
            if (is_array($params) or is_object($params)) {
                throw new invalid_parameter_exception('Scalar type expected, array or object received.');
            }

            if ($description->type == PARAM_BOOL) {
                                if (is_bool($params) or $params === 0 or $params === 1 or $params === '0' or $params === '1') {
                    return (bool)$params;
                }
            }
            $debuginfo = 'Invalid external api parameter: the value is "' . $params .
                    '", the server was expecting "' . $description->type . '" type';
            return validate_param($params, $description->type, $description->allownull, $debuginfo);

        } else if ($description instanceof external_single_structure) {
            if (!is_array($params)) {
                throw new invalid_parameter_exception('Only arrays accepted. The bad value is: \''
                        . print_r($params, true) . '\'');
            }
            $result = array();
            foreach ($description->keys as $key=>$subdesc) {
                if (!array_key_exists($key, $params)) {
                    if ($subdesc->required == VALUE_REQUIRED) {
                        throw new invalid_parameter_exception('Missing required key in single structure: '. $key);
                    }
                    if ($subdesc->required == VALUE_DEFAULT) {
                        try {
                            $result[$key] = self::validate_parameters($subdesc, $subdesc->default);
                        } catch (invalid_parameter_exception $e) {
                                                                                    throw new invalid_parameter_exception($key." => ".$e->getMessage() . ': ' .$e->debuginfo);
                        }
                    }
                } else {
                    try {
                        $result[$key] = self::validate_parameters($subdesc, $params[$key]);
                    } catch (invalid_parameter_exception $e) {
                                                                        throw new invalid_parameter_exception($key." => ".$e->getMessage() . ': ' .$e->debuginfo);
                    }
                }
                unset($params[$key]);
            }
            if (!empty($params)) {
                throw new invalid_parameter_exception('Unexpected keys (' . implode(', ', array_keys($params)) . ') detected in parameter array.');
            }
            return $result;

        } else if ($description instanceof external_multiple_structure) {
            if (!is_array($params)) {
                throw new invalid_parameter_exception('Only arrays accepted. The bad value is: \''
                        . print_r($params, true) . '\'');
            }
            $result = array();
            foreach ($params as $param) {
                $result[] = self::validate_parameters($description->content, $param);
            }
            return $result;

        } else {
            throw new invalid_parameter_exception('Invalid external api description');
        }
    }

    
    public static function clean_returnvalue(external_description $description, $response) {
        if ($description instanceof external_value) {
            if (is_array($response) or is_object($response)) {
                throw new invalid_response_exception('Scalar type expected, array or object received.');
            }

            if ($description->type == PARAM_BOOL) {
                                if (is_bool($response) or $response === 0 or $response === 1 or $response === '0' or $response === '1') {
                    return (bool)$response;
                }
            }
            $debuginfo = 'Invalid external api response: the value is "' . $response .
                    '", the server was expecting "' . $description->type . '" type';
            try {
                return validate_param($response, $description->type, $description->allownull, $debuginfo);
            } catch (invalid_parameter_exception $e) {
                                throw new invalid_response_exception($e->debuginfo);
            }

        } else if ($description instanceof external_single_structure) {
            if (!is_array($response) && !is_object($response)) {
                throw new invalid_response_exception('Only arrays/objects accepted. The bad value is: \'' .
                        print_r($response, true) . '\'');
            }

                        if (is_object($response)) {
                $response = (array) $response;
            }

            $result = array();
            foreach ($description->keys as $key=>$subdesc) {
                if (!array_key_exists($key, $response)) {
                    if ($subdesc->required == VALUE_REQUIRED) {
                        throw new invalid_response_exception('Error in response - Missing following required key in a single structure: ' . $key);
                    }
                    if ($subdesc instanceof external_value) {
                        if ($subdesc->required == VALUE_DEFAULT) {
                            try {
                                    $result[$key] = self::clean_returnvalue($subdesc, $subdesc->default);
                            } catch (invalid_response_exception $e) {
                                                                throw new invalid_response_exception($key." => ".$e->getMessage() . ': ' . $e->debuginfo);
                            }
                        }
                    }
                } else {
                    try {
                        $result[$key] = self::clean_returnvalue($subdesc, $response[$key]);
                    } catch (invalid_response_exception $e) {
                                                throw new invalid_response_exception($key." => ".$e->getMessage() . ': ' . $e->debuginfo);
                    }
                }
                unset($response[$key]);
            }

            return $result;

        } else if ($description instanceof external_multiple_structure) {
            if (!is_array($response)) {
                throw new invalid_response_exception('Only arrays accepted. The bad value is: \'' .
                        print_r($response, true) . '\'');
            }
            $result = array();
            foreach ($response as $param) {
                $result[] = self::clean_returnvalue($description->content, $param);
            }
            return $result;

        } else {
            throw new invalid_response_exception('Invalid external api response description');
        }
    }

    
    public static function validate_context($context) {
        global $CFG, $PAGE;

        if (empty($context)) {
            throw new invalid_parameter_exception('Context does not exist');
        }
        if (empty(self::$contextrestriction)) {
            self::$contextrestriction = context_system::instance();
        }
        $rcontext = self::$contextrestriction;

        if ($rcontext->contextlevel == $context->contextlevel) {
            if ($rcontext->id != $context->id) {
                throw new restricted_context_exception();
            }
        } else if ($rcontext->contextlevel > $context->contextlevel) {
            throw new restricted_context_exception();
        } else {
            $parents = $context->get_parent_context_ids();
            if (!in_array($rcontext->id, $parents)) {
                throw new restricted_context_exception();
            }
        }

        $PAGE->reset_theme_and_output();
        list($unused, $course, $cm) = get_context_info_array($context->id);
        require_login($course, false, $cm, false, true);
        $PAGE->set_context($context);
    }

    
    protected static function get_context_from_params($param) {
        $levels = context_helper::get_all_levels();
        if (!empty($param['contextid'])) {
            return context::instance_by_id($param['contextid'], IGNORE_MISSING);
        } else if (!empty($param['contextlevel']) && isset($param['instanceid'])) {
            $contextlevel = "context_".$param['contextlevel'];
            if (!array_search($contextlevel, $levels)) {
                throw new invalid_parameter_exception('Invalid context level = '.$param['contextlevel']);
            }
           return $contextlevel::instance($param['instanceid'], IGNORE_MISSING);
        } else {
                        throw new invalid_parameter_exception('Missing parameters, please provide either context level with instance id or contextid');
        }
    }
}


abstract class external_description {
    
    public $desc;

    
    public $required;

    
    public $default;

    
    public function __construct($desc, $required, $default) {
        $this->desc = $desc;
        $this->required = $required;
        $this->default = $default;
    }
}


class external_value extends external_description {

    
    public $type;

    
    public $allownull;

    
    public function __construct($type, $desc='', $required=VALUE_REQUIRED,
            $default=null, $allownull=NULL_ALLOWED) {
        parent::__construct($desc, $required, $default);
        $this->type      = $type;
        $this->allownull = $allownull;
    }
}


class external_single_structure extends external_description {

     
    public $keys;

    
    public function __construct(array $keys, $desc='',
            $required=VALUE_REQUIRED, $default=null) {
        parent::__construct($desc, $required, $default);
        $this->keys = $keys;
    }
}


class external_multiple_structure extends external_description {

     
    public $content;

    
    public function __construct(external_description $content, $desc='',
            $required=VALUE_REQUIRED, $default=null) {
        parent::__construct($desc, $required, $default);
        $this->content = $content;
    }
}


class external_function_parameters extends external_single_structure {

    
    public function __construct(array $keys, $desc='', $required=VALUE_REQUIRED, $default=null) {
        global $CFG;

        if ($CFG->debugdeveloper) {
            foreach ($keys as $key => $value) {
                if ($value instanceof external_value) {
                    if ($value->required == VALUE_OPTIONAL) {
                        debugging('External function parameters: invalid OPTIONAL value specified.', DEBUG_DEVELOPER);
                        break;
                    }
                }
            }
        }
        parent::__construct($keys, $desc, $required, $default);
    }
}


function external_generate_token($tokentype, $serviceorid, $userid, $contextorid, $validuntil=0, $iprestriction=''){
    global $DB, $USER;
        $numtries = 0;
    do {
        $numtries ++;
        $generatedtoken = md5(uniqid(rand(),1));
        if ($numtries > 5){
            throw new moodle_exception('tokengenerationfailed');
        }
    } while ($DB->record_exists('external_tokens', array('token'=>$generatedtoken)));
    $newtoken = new stdClass();
    $newtoken->token = $generatedtoken;
    if (!is_object($serviceorid)){
        $service = $DB->get_record('external_services', array('id' => $serviceorid));
    } else {
        $service = $serviceorid;
    }
    if (!is_object($contextorid)){
        $context = context::instance_by_id($contextorid, MUST_EXIST);
    } else {
        $context = $contextorid;
    }
    if (empty($service->requiredcapability) || has_capability($service->requiredcapability, $context, $userid)) {
        $newtoken->externalserviceid = $service->id;
    } else {
        throw new moodle_exception('nocapabilitytousethisservice');
    }
    $newtoken->tokentype = $tokentype;
    $newtoken->userid = $userid;
    if ($tokentype == EXTERNAL_TOKEN_EMBEDDED){
        $newtoken->sid = session_id();
    }

    $newtoken->contextid = $context->id;
    $newtoken->creatorid = $USER->id;
    $newtoken->timecreated = time();
    $newtoken->validuntil = $validuntil;
    if (!empty($iprestriction)) {
        $newtoken->iprestriction = $iprestriction;
    }
    $DB->insert_record('external_tokens', $newtoken);
    return $newtoken->token;
}


function external_create_service_token($servicename, $context){
    global $USER, $DB;
    $service = $DB->get_record('external_services', array('name'=>$servicename), '*', MUST_EXIST);
    return external_generate_token(EXTERNAL_TOKEN_EMBEDDED, $service, $USER->id, $context, 0);
}


function external_delete_descriptions($component) {
    global $DB;

    $params = array($component);

    $DB->delete_records_select('external_tokens',
            "externalserviceid IN (SELECT id FROM {external_services} WHERE component = ?)", $params);
    $DB->delete_records_select('external_services_users',
            "externalserviceid IN (SELECT id FROM {external_services} WHERE component = ?)", $params);
    $DB->delete_records_select('external_services_functions',
            "functionname IN (SELECT name FROM {external_functions} WHERE component = ?)", $params);
    $DB->delete_records('external_services', array('component'=>$component));
    $DB->delete_records('external_functions', array('component'=>$component));
}


class external_warnings extends external_multiple_structure {

    
    public function __construct($itemdesc = 'item', $itemiddesc = 'item id',
        $warningcodedesc = 'the warning code can be used by the client app to implement specific behaviour') {

        parent::__construct(
            new external_single_structure(
                array(
                    'item' => new external_value(PARAM_TEXT, $itemdesc, VALUE_OPTIONAL),
                    'itemid' => new external_value(PARAM_INT, $itemiddesc, VALUE_OPTIONAL),
                    'warningcode' => new external_value(PARAM_ALPHANUM, $warningcodedesc),
                    'message' => new external_value(PARAM_TEXT,
                            'untranslated english message to explain the warning')
                ), 'warning'),
            'list of warnings', VALUE_OPTIONAL);
    }
}


class external_format_value extends external_value {

    
    public function __construct($textfieldname, $required = VALUE_REQUIRED) {

        $default = ($required == VALUE_DEFAULT) ? FORMAT_HTML : null;

        $desc = $textfieldname . ' format (' . FORMAT_HTML . ' = HTML, '
                . FORMAT_MOODLE . ' = MOODLE, '
                . FORMAT_PLAIN . ' = PLAIN or '
                . FORMAT_MARKDOWN . ' = MARKDOWN)';

        parent::__construct(PARAM_INT, $desc, $required, $default);
    }
}


function external_validate_format($format) {
    $allowedformats = array(FORMAT_HTML, FORMAT_MOODLE, FORMAT_PLAIN, FORMAT_MARKDOWN);
    if (!in_array($format, $allowedformats)) {
        throw new moodle_exception('formatnotsupported', 'webservice', '' , null,
                'The format with value=' . $format . ' is not supported by this Moodle site');
    }
    return $format;
}


function external_format_string($str, $contextid, $striplinks = true, $options = array()) {

        $settings = external_settings::get_instance();
    if (empty($contextid)) {
        throw new coding_exception('contextid is required');
    }

    if (!$settings->get_raw()) {
        $context = context::instance_by_id($contextid);
        $options['context'] = $context;
        $options['filter'] = isset($options['filter']) && !$options['filter'] ? false : $settings->get_filter();
        $str = format_string($str, $striplinks, $options);
    }

    return $str;
}


function external_format_text($text, $textformat, $contextid, $component, $filearea, $itemid, $options = null) {
    global $CFG;

        $settings = external_settings::get_instance();

    if ($settings->get_fileurl()) {
        require_once($CFG->libdir . "/filelib.php");
        $text = file_rewrite_pluginfile_urls($text, $settings->get_file(), $contextid, $component, $filearea, $itemid);
    }

    if (!$settings->get_raw()) {
        $options = (array)$options;

                if (isset($options['context'])) {
            if ((is_object($options['context']) && $options['context']->id != $contextid)
                    || (!is_object($options['context']) && $options['context'] != $contextid)) {
                debugging('Different contexts found in external_format_text parameters. $options[\'context\'] not allowed.
                    Using $contextid parameter...', DEBUG_DEVELOPER);
            }
        }

        $options['filter'] = isset($options['filter']) && !$options['filter'] ? false : $settings->get_filter();
        $options['para'] = isset($options['para']) ? $options['para'] : false;
        $options['context'] = context::instance_by_id($contextid);
        $options['allowid'] = isset($options['allowid']) ? $options['allowid'] : true;

        $text = format_text($text, $textformat, $options);
        $textformat = FORMAT_HTML;     }

    return array($text, $textformat);
}


class external_settings {

    
    public static $instance = null;

    
    private $raw = false;

    
    private $filter = false;

    
    private $fileurl = true;

    
    private $file = 'webservice/pluginfile.php';

    
    protected function __construct() {
        if (!defined('AJAX_SCRIPT') && !defined('CLI_SCRIPT') && !defined('WS_SERVER')) {
                        $this->filter = true;
        }
    }

    
    private final function __clone() {
    }

    
    public static function get_instance() {
        if (self::$instance === null) {
            self::$instance = new external_settings;
        }

        return self::$instance;
    }

    
    public function set_raw($raw) {
        $this->raw = $raw;
    }

    
    public function get_raw() {
        return $this->raw;
    }

    
    public function set_filter($filter) {
        $this->filter = $filter;
    }

    
    public function get_filter() {
        return $this->filter;
    }

    
    public function set_fileurl($fileurl) {
        $this->fileurl = $fileurl;
    }

    
    public function get_fileurl() {
        return $this->fileurl;
    }

    
    public function set_file($file) {
        $this->file = $file;
    }

    
    public function get_file() {
        return $this->file;
    }
}


class external_util {

    
    public static function validate_courses($courseids, $courses = array()) {
                $courseids = array_unique($courseids);
        $warnings = array();

                $courses =  array_intersect_key($courses, array_flip($courseids));

        foreach ($courseids as $cid) {
                        try {
                $context = context_course::instance($cid);
                external_api::validate_context($context);

                if (!isset($courses[$cid])) {
                    $courses[$cid] = get_course($cid);
                }
            } catch (Exception $e) {
                unset($courses[$cid]);
                $warnings[] = array(
                    'item' => 'course',
                    'itemid' => $cid,
                    'warningcode' => '1',
                    'message' => 'No access rights in course context'
                );
            }
        }

        return array($courses, $warnings);
    }

}
