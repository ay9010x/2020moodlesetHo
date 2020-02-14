<?php




defined('MOODLE_INTERNAL') || die;

require_once("$CFG->libdir/externallib.php");


class core_webservice_external extends external_api {

    
    public static function get_site_info_parameters() {
        return new external_function_parameters(
            array('serviceshortnames' => new external_multiple_structure (
                new external_value(
                    PARAM_ALPHANUMEXT,
                    'service shortname'),
                    'DEPRECATED PARAMETER - it was a design error in the original implementation. \
                    It is ignored now. (parameter kept for backward compatibility)',
                    VALUE_DEFAULT,
                    array()
                ),
            )
        );
    }

    
    public static function get_site_info($serviceshortnames = array()) {
        global $USER, $SITE, $CFG, $DB, $PAGE;

        $params = self::validate_parameters(self::get_site_info_parameters(),
                      array('serviceshortnames'=>$serviceshortnames));

        $context = context_user::instance($USER->id);

        $userpicture = new user_picture($USER);
        $userpicture->size = 1;         $profileimageurl = $userpicture->get_url($PAGE);

                $siteinfo =  array(
            'sitename' => $SITE->fullname,
            'siteurl' => $CFG->wwwroot,
            'username' => $USER->username,
            'firstname' => $USER->firstname,
            'lastname' => $USER->lastname,
            'fullname' => fullname($USER),
            'lang' => current_language(),
            'userid' => $USER->id,
            'userpictureurl' => $profileimageurl->out(false)
        );

                                        $functions = array();
        if ($CFG->enablewebservices) {             $token = optional_param('wstoken', '', PARAM_ALPHANUM);

            if (!empty($token)) {                                 $servicesql = 'SELECT s.*
                               FROM {external_services} s, {external_tokens} t
                               WHERE t.externalserviceid = s.id AND token = ? AND t.userid = ? AND s.enabled = 1';
                $service = $DB->get_record_sql($servicesql, array($token, $USER->id));

                $siteinfo['downloadfiles'] = $service->downloadfiles;
                $siteinfo['uploadfiles'] = $service->uploadfiles;

                if (!empty($service)) {
                                        $siteinfo['release'] = $CFG->release;
                    $siteinfo['version'] = $CFG->version;
                                        $functionssql = "SELECT f.*
                            FROM {external_functions} f, {external_services_functions} sf
                            WHERE f.name = sf.functionname AND sf.externalserviceid = ?";
                    $functions = $DB->get_records_sql($functionssql, array($service->id));
                } else {
                    throw new coding_exception('No service found in get_site_info: something is buggy, \
                                                it should have fail at the ws server authentication layer.');
                }
            }
        }

                $componentversions = array();
        $availablefunctions = array();
        foreach ($functions as $function) {
            $functioninfo = array();
            $functioninfo['name'] = $function->name;
            if ($function->component == 'moodle' || $function->component == 'core') {
                $version = $CFG->version;             } else {
                $versionpath = core_component::get_component_directory($function->component).'/version.php';
                if (is_readable($versionpath)) {
                                        if (!isset($componentversions[$function->component])) {
                        $plugin = new stdClass();
                        include($versionpath);
                        $componentversions[$function->component] = $plugin->version;
                        $version = $plugin->version;
                    } else {
                        $version = $componentversions[$function->component];
                    }
                } else {
                                                            throw new moodle_exception('missingversionfile', 'webservice', '', $function->component);
                }
            }
            $functioninfo['version'] = $version;
            $availablefunctions[] = $functioninfo;
        }

        $siteinfo['functions'] = $availablefunctions;

                $siteinfo['mobilecssurl'] = $CFG->mobilecssurl;

                $advancedfeatures = array("usecomments", "usetags", "enablenotes", "messaging", "enableblogs",
                                    "enablecompletion", "enablebadges");
        foreach ($advancedfeatures as $feature) {
            if (isset($CFG->{$feature})) {
                $siteinfo['advancedfeatures'][] = array(
                    'name' => $feature,
                    'value' => (int) $CFG->{$feature}
                );
            }
        }
                $siteinfo['advancedfeatures'][] = array(
            'name' => 'mnet_dispatcher_mode',
            'value' => ($CFG->mnet_dispatcher_mode == 'strict') ? 1 : 0
        );

                $siteinfo['usercanmanageownfiles'] = has_capability('moodle/user:manageownfiles', $context);

                $siteinfo['userquota'] = 0;
        if (!has_capability('moodle/user:ignoreuserquota', $context)) {
            $siteinfo['userquota'] = $CFG->userquota;
        }

                $siteinfo['usermaxuploadfilesize'] = get_user_max_upload_file_size($context, $CFG->maxbytes);

                $siteinfo['userhomepage'] = get_home_page();

        return $siteinfo;
    }

    
    public static function get_site_info_returns() {
        return new external_single_structure(
            array(
                'sitename'       => new external_value(PARAM_RAW, 'site name'),
                'username'       => new external_value(PARAM_RAW, 'username'),
                'firstname'      => new external_value(PARAM_TEXT, 'first name'),
                'lastname'       => new external_value(PARAM_TEXT, 'last name'),
                'fullname'       => new external_value(PARAM_TEXT, 'user full name'),
                'lang'           => new external_value(PARAM_LANG, 'user language'),
                'userid'         => new external_value(PARAM_INT, 'user id'),
                'siteurl'        => new external_value(PARAM_RAW, 'site url'),
                'userpictureurl' => new external_value(PARAM_URL, 'the user profile picture.
                    Warning: this url is the public URL that only works when forcelogin is set to NO and guestaccess is set to YES.
                    In order to retrieve user profile pictures independently of the Moodle config, replace "pluginfile.php" by
                    "webservice/pluginfile.php?token=WSTOKEN&file="
                    Of course the user can only see profile picture depending
                    on his/her permissions. Moreover it is recommended to use HTTPS too.'),
                'functions'      => new external_multiple_structure(
                    new external_single_structure(
                        array(
                            'name' => new external_value(PARAM_RAW, 'function name'),
                            'version' => new external_value(PARAM_TEXT,
                                        'The version number of the component to which the function belongs')
                        ), 'functions that are available')
                    ),
                'downloadfiles'  => new external_value(PARAM_INT, '1 if users are allowed to download files, 0 if not',
                                                       VALUE_OPTIONAL),
                'uploadfiles'  => new external_value(PARAM_INT, '1 if users are allowed to upload files, 0 if not',
                                                       VALUE_OPTIONAL),
                'release'  => new external_value(PARAM_TEXT, 'Moodle release number', VALUE_OPTIONAL),
                'version'  => new external_value(PARAM_TEXT, 'Moodle version number', VALUE_OPTIONAL),
                'mobilecssurl'  => new external_value(PARAM_URL, 'Mobile custom CSS theme', VALUE_OPTIONAL),
                'advancedfeatures' => new external_multiple_structure(
                    new external_single_structure(
                        array(
                            'name'  => new external_value(PARAM_ALPHANUMEXT, 'feature name'),
                            'value' => new external_value(PARAM_INT, 'feature value. Usually 1 means enabled.')
                        ),
                        'Advanced features availability'
                    ),
                    'Advanced features availability',
                    VALUE_OPTIONAL
                ),
                'usercanmanageownfiles' => new external_value(PARAM_BOOL,
                                            'true if the user can manage his own files', VALUE_OPTIONAL),
                'userquota' => new external_value(PARAM_INT,
                                    'user quota (bytes). 0 means user can ignore the quota', VALUE_OPTIONAL),
                'usermaxuploadfilesize' => new external_value(PARAM_INT,
                                            'user max upload file size (bytes). -1 means the user can ignore the upload file size',
                                            VALUE_OPTIONAL),
                'userhomepage' => new external_value(PARAM_INT,
                                                        'the default home page for the user: 0 for the site home, 1 for dashboard',
                                                        VALUE_OPTIONAL)
            )
        );
    }
}
