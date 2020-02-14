<?php




require_once($CFG->libdir.'/externallib.php');


define('WEBSERVICE_AUTHMETHOD_USERNAME', 0);


define('WEBSERVICE_AUTHMETHOD_PERMANENT_TOKEN', 1);


define('WEBSERVICE_AUTHMETHOD_SESSION_TOKEN', 2);


class webservice {

    
    public function authenticate_user($token) {
        global $DB, $CFG;

                if (!$CFG->enablewebservices) {
            throw new webservice_access_exception('Web services are not enabled in Advanced features.');
        }

                if (!$token = $DB->get_record('external_tokens', array('token' => $token))) {
                        throw new moodle_exception('invalidtoken', 'webservice');
        }

        $loginfaileddefaultparams = array(
            'other' => array(
                'method' => WEBSERVICE_AUTHMETHOD_PERMANENT_TOKEN,
                'reason' => null,
                'tokenid' => $token->id
            )
        );

                if ($token->validuntil and $token->validuntil < time()) {
            $params = $loginfaileddefaultparams;
            $params['other']['reason'] = 'token_expired';
            $event = \core\event\webservice_login_failed::create($params);
            $event->add_record_snapshot('external_tokens', $token);
            $event->set_legacy_logdata(array(SITEID, 'webservice', get_string('tokenauthlog', 'webservice'), '',
                get_string('invalidtimedtoken', 'webservice'), 0));
            $event->trigger();
            $DB->delete_records('external_tokens', array('token' => $token->token));
            throw new webservice_access_exception('Invalid token - token expired - check validuntil time for the token');
        }

                if ($token->iprestriction and !address_in_subnet(getremoteaddr(), $token->iprestriction)) {
            $params = $loginfaileddefaultparams;
            $params['other']['reason'] = 'ip_restricted';
            $event = \core\event\webservice_login_failed::create($params);
            $event->add_record_snapshot('external_tokens', $token);
            $event->set_legacy_logdata(array(SITEID, 'webservice', get_string('tokenauthlog', 'webservice'), '',
                get_string('failedtolog', 'webservice') . ": " . getremoteaddr(), 0));
            $event->trigger();
            throw new webservice_access_exception('Invalid token - IP:' . getremoteaddr()
                    . ' is not supported');
        }

                $user = $DB->get_record('user', array('id' => $token->userid, 'deleted' => 0), '*', MUST_EXIST);

                enrol_check_plugins($user);

                \core\session\manager::set_user($user);
        set_login_session_preferences();

                if ($token->sid) {
            if (!\core\session\manager::session_exists($token->sid)) {
                $DB->delete_records('external_tokens', array('sid' => $token->sid));
                throw new webservice_access_exception('Invalid session based token - session not found or expired');
            }
        }

                $hassiteconfig = has_capability('moodle/site:config', context_system::instance(), $user);
        if (!empty($CFG->maintenance_enabled) and !$hassiteconfig) {
                        throw new moodle_exception('sitemaintenance', 'admin');
        }

                $service = $DB->get_record('external_services', array('id' => $token->externalserviceid, 'enabled' => 1));
        if (empty($service)) {
                        throw new webservice_access_exception('Web service is not available (it doesn\'t exist or might be disabled)');
        }

                if ($service->requiredcapability and !has_capability($service->requiredcapability, context_system::instance(), $user)) {
            throw new webservice_access_exception('The capability ' . $service->requiredcapability . ' is required.');
        }

                if ($service->restrictedusers) {
            $authoriseduser = $DB->get_record('external_services_users', array('externalserviceid' => $service->id, 'userid' => $user->id));

            if (empty($authoriseduser)) {
                throw new webservice_access_exception(
                        'The user is not allowed for this service. First you need to allow this user on the '
                        . $service->name . '\'s allowed users administration page.');
            }

            if (!empty($authoriseduser->validuntil) and $authoriseduser->validuntil < time()) {
                throw new webservice_access_exception('Invalid service - service expired - check validuntil time for this allowed user');
            }

            if (!empty($authoriseduser->iprestriction) and !address_in_subnet(getremoteaddr(), $authoriseduser->iprestriction)) {
                throw new webservice_access_exception('Invalid service - IP:' . getremoteaddr()
                    . ' is not supported - check this allowed user');
            }
        }

                if (empty($user->confirmed)) {
            $params = $loginfaileddefaultparams;
            $params['other']['reason'] = 'user_unconfirmed';
            $event = \core\event\webservice_login_failed::create($params);
            $event->add_record_snapshot('external_tokens', $token);
            $event->set_legacy_logdata(array(SITEID, 'webservice', 'user unconfirmed', '', $user->username));
            $event->trigger();
            throw new moodle_exception('usernotconfirmed', 'moodle', '', $user->username);
        }

                if (!empty($user->suspended)) {
            $params = $loginfaileddefaultparams;
            $params['other']['reason'] = 'user_suspended';
            $event = \core\event\webservice_login_failed::create($params);
            $event->add_record_snapshot('external_tokens', $token);
            $event->set_legacy_logdata(array(SITEID, 'webservice', 'user suspended', '', $user->username));
            $event->trigger();
            throw new webservice_access_exception('Refused web service access for suspended username: ' . $user->username);
        }

                if ($user->auth == 'nologin') {
            $params = $loginfaileddefaultparams;
            $params['other']['reason'] = 'nologin';
            $event = \core\event\webservice_login_failed::create($params);
            $event->add_record_snapshot('external_tokens', $token);
            $event->set_legacy_logdata(array(SITEID, 'webservice', 'nologin auth attempt with web service', '', $user->username));
            $event->trigger();
            throw new webservice_access_exception('Refused web service access for nologin authentication username: ' . $user->username);
        }

                $auth = get_auth_plugin($user->auth);
        if (!empty($auth->config->expiration) and $auth->config->expiration == 1) {
            $days2expire = $auth->password_expire($user->username);
            if (intval($days2expire) < 0) {
                $params = $loginfaileddefaultparams;
                $params['other']['reason'] = 'password_expired';
                $event = \core\event\webservice_login_failed::create($params);
                $event->add_record_snapshot('external_tokens', $token);
                $event->set_legacy_logdata(array(SITEID, 'webservice', 'expired password', '', $user->username));
                $event->trigger();
                throw new moodle_exception('passwordisexpired', 'webservice');
            }
        }

                $DB->set_field('external_tokens', 'lastaccess', time(), array('id' => $token->id));

        return array('user' => $user, 'token' => $token, 'service' => $service);
    }

    
    public function add_ws_authorised_user($user) {
        global $DB;
        $user->timecreated = time();
        $DB->insert_record('external_services_users', $user);
    }

    
    public function remove_ws_authorised_user($user, $serviceid) {
        global $DB;
        $DB->delete_records('external_services_users',
                array('externalserviceid' => $serviceid, 'userid' => $user->id));
    }

    
    public function update_ws_authorised_user($user) {
        global $DB;
        $DB->update_record('external_services_users', $user);
    }

    
    public function get_ws_authorised_users($serviceid) {
        global $DB, $CFG;
        $params = array($CFG->siteguest, $serviceid);
        $sql = " SELECT u.id as id, esu.id as serviceuserid, u.email as email, u.firstname as firstname,
                        u.lastname as lastname,
                        esu.iprestriction as iprestriction, esu.validuntil as validuntil,
                        esu.timecreated as timecreated
                   FROM {user} u, {external_services_users} esu
                  WHERE u.id <> ? AND u.deleted = 0 AND u.confirmed = 1
                        AND esu.userid = u.id
                        AND esu.externalserviceid = ?";

        $users = $DB->get_records_sql($sql, $params);
        return $users;
    }

    
    public function get_ws_authorised_user($serviceid, $userid) {
        global $DB, $CFG;
        $params = array($CFG->siteguest, $serviceid, $userid);
        $sql = " SELECT u.id as id, esu.id as serviceuserid, u.email as email, u.firstname as firstname,
                        u.lastname as lastname,
                        esu.iprestriction as iprestriction, esu.validuntil as validuntil,
                        esu.timecreated as timecreated
                   FROM {user} u, {external_services_users} esu
                  WHERE u.id <> ? AND u.deleted = 0 AND u.confirmed = 1
                        AND esu.userid = u.id
                        AND esu.externalserviceid = ?
                        AND u.id = ?";
        $user = $DB->get_record_sql($sql, $params);
        return $user;
    }

    
    public function generate_user_ws_tokens($userid) {
        global $CFG, $DB;

                if (!is_siteadmin() && has_capability('moodle/webservice:createtoken', context_system::instance(), $userid) && !empty($CFG->enablewebservices)) {
            
                        $norestrictedservices = $DB->get_records('external_services', array('restrictedusers' => 0));
            $serviceidlist = array();
            foreach ($norestrictedservices as $service) {
                $serviceidlist[] = $service->id;
            }

                        $servicesusers = $DB->get_records('external_services_users', array('userid' => $userid));
            foreach ($servicesusers as $serviceuser) {
                if (!in_array($serviceuser->externalserviceid,$serviceidlist)) {
                     $serviceidlist[] = $serviceuser->externalserviceid;
                }
            }

                        $usertokens = $DB->get_records('external_tokens', array('userid' => $userid, 'tokentype' => EXTERNAL_TOKEN_PERMANENT));
            $tokenizedservice = array();
            foreach ($usertokens as $token) {
                    $tokenizedservice[]  = $token->externalserviceid;
            }

                        foreach ($serviceidlist as $serviceid) {
                if (!in_array($serviceid, $tokenizedservice)) {
                                        $newtoken = new stdClass();
                    $newtoken->token = md5(uniqid(rand(),1));
                                        $newtoken->tokentype = EXTERNAL_TOKEN_PERMANENT;
                    $newtoken->userid = $userid;
                    $newtoken->externalserviceid = $serviceid;
                                        $newtoken->contextid = context_system::instance()->id;
                    $newtoken->creatorid = $userid;
                    $newtoken->timecreated = time();

                    $DB->insert_record('external_tokens', $newtoken);
                }
            }


        }
    }

    
    public function get_user_ws_tokens($userid) {
        global $DB;
                $sql = "SELECT
                    t.id, t.creatorid, t.token, u.firstname, u.lastname, s.id as wsid, s.name, s.enabled, s.restrictedusers, t.validuntil
                FROM
                    {external_tokens} t, {user} u, {external_services} s
                WHERE
                    t.userid=? AND t.tokentype = ".EXTERNAL_TOKEN_PERMANENT." AND s.id = t.externalserviceid AND t.userid = u.id";
        $tokens = $DB->get_records_sql($sql, array( $userid));
        return $tokens;
    }

    
    public function get_created_by_user_ws_token($userid, $tokenid) {
        global $DB;
        $sql = "SELECT
                        t.id, t.token, u.firstname, u.lastname, s.name
                    FROM
                        {external_tokens} t, {user} u, {external_services} s
                    WHERE
                        t.creatorid=? AND t.id=? AND t.tokentype = "
                . EXTERNAL_TOKEN_PERMANENT
                . " AND s.id = t.externalserviceid AND t.userid = u.id";
                $token = $DB->get_record_sql($sql, array($userid, $tokenid), MUST_EXIST);
        return $token;
    }

    
    public function get_token_by_id($tokenid) {
        global $DB;
        return $DB->get_record('external_tokens', array('id' => $tokenid));
    }

    
    public function delete_user_ws_token($tokenid) {
        global $DB;
        $DB->delete_records('external_tokens', array('id'=>$tokenid));
    }

    
    public static function delete_user_ws_tokens($userid) {
        global $DB;
        $DB->delete_records('external_tokens', array('userid' => $userid));
    }

    
    public function delete_service($serviceid) {
        global $DB;
        $DB->delete_records('external_services_users', array('externalserviceid' => $serviceid));
        $DB->delete_records('external_services_functions', array('externalserviceid' => $serviceid));
        $DB->delete_records('external_tokens', array('externalserviceid' => $serviceid));
        $DB->delete_records('external_services', array('id' => $serviceid));
    }

    
    public function get_user_ws_token($token) {
        global $DB;
        return $DB->get_record('external_tokens', array('token'=>$token), '*', MUST_EXIST);
    }

    
    public function get_external_functions($serviceids) {
        global $DB;
        if (!empty($serviceids)) {
            list($serviceids, $params) = $DB->get_in_or_equal($serviceids);
            $sql = "SELECT f.*
                      FROM {external_functions} f
                     WHERE f.name IN (SELECT sf.functionname
                                        FROM {external_services_functions} sf
                                       WHERE sf.externalserviceid $serviceids)";
            $functions = $DB->get_records_sql($sql, $params);
        } else {
            $functions = array();
        }
        return $functions;
    }

    
    public function get_external_functions_by_enabled_services($serviceshortnames, $enabledonly = true) {
        global $DB;
        if (!empty($serviceshortnames)) {
            $enabledonlysql = $enabledonly?' AND s.enabled = 1 ':'';
            list($serviceshortnames, $params) = $DB->get_in_or_equal($serviceshortnames);
            $sql = "SELECT f.*
                      FROM {external_functions} f
                     WHERE f.name IN (SELECT sf.functionname
                                        FROM {external_services_functions} sf, {external_services} s
                                       WHERE s.shortname $serviceshortnames
                                             AND sf.externalserviceid = s.id
                                             " . $enabledonlysql . ")";
            $functions = $DB->get_records_sql($sql, $params);
        } else {
            $functions = array();
        }
        return $functions;
    }

    
    public function get_not_associated_external_functions($serviceid) {
        global $DB;
        $select = "name NOT IN (SELECT s.functionname
                                  FROM {external_services_functions} s
                                 WHERE s.externalserviceid = :sid
                               )";

        $functions = $DB->get_records_select('external_functions',
                        $select, array('sid' => $serviceid), 'name');

        return $functions;
    }

    
    public function get_service_required_capabilities($serviceid) {
        $functions = $this->get_external_functions(array($serviceid));
        $requiredusercaps = array();
        foreach ($functions as $function) {
            $functioncaps = explode(',', $function->capabilities);
            if (!empty($functioncaps) and !empty($functioncaps[0])) {
                foreach ($functioncaps as $functioncap) {
                    $requiredusercaps[$function->name][] = trim($functioncap);
                }
            }
        }
        return $requiredusercaps;
    }

    
    public function get_user_capabilities($userid) {
        global $DB;
                $sql = "SELECT DISTINCT rc.id, rc.capability FROM {role_capabilities} rc, {role_assignments} ra
            WHERE rc.roleid=ra.roleid AND ra.userid= ? AND rc.permission = ?";
        $dbusercaps = $DB->get_records_sql($sql, array($userid, CAP_ALLOW));
        $usercaps = array();
        foreach ($dbusercaps as $usercap) {
            $usercaps[$usercap->capability] = true;
        }
        return $usercaps;
    }

    
    public function get_missing_capabilities_by_users($users, $serviceid) {
        global $DB;
        $usersmissingcaps = array();

                $servicecaps = $this->get_service_required_capabilities($serviceid);

                foreach ($users as $user) {
                        if (is_array($user)) {
                $user = (object) $user;
            }
            $usercaps = $this->get_user_capabilities($user->id);

                        foreach ($servicecaps as $functioname => $functioncaps) {
                foreach ($functioncaps as $functioncap) {
                    if (!array_key_exists($functioncap, $usercaps)) {
                        if (!isset($usersmissingcaps[$user->id])
                                or array_search($functioncap, $usersmissingcaps[$user->id]) === false) {
                            $usersmissingcaps[$user->id][] = $functioncap;
                        }
                    }
                }
            }
        }

        return $usersmissingcaps;
    }

    
    public function get_external_service_by_id($serviceid, $strictness=IGNORE_MISSING) {
        global $DB;
        $service = $DB->get_record('external_services',
                        array('id' => $serviceid), '*', $strictness);
        return $service;
    }

    
    public function get_external_service_by_shortname($shortname, $strictness=IGNORE_MISSING) {
        global $DB;
        $service = $DB->get_record('external_services',
                        array('shortname' => $shortname), '*', $strictness);
        return $service;
    }
    
    public function get_modset_service() {
        global $CFG, $DB;
            if (empty($CFG->modsetwsshortname)) {
            if ($services = $DB->get_records('external_services', array('shortname'=>'moodleset_api'))) {
                $service = array_shift($services);                   set_config('modsetwsshortname', $service->shortname);
                return $service;
            } else {
                debugging('Can not find any MoodleSET service');
                return false;
            }
        } else {
            if ($services = $DB->get_record('external_services', array('shortname'=>$CFG->modsetwsshortname))) {
                return $services;
            } else {
                                set_config('modsetwsshortname', '');
                return false;
            }
        }
    }

    
    public function get_external_function_by_id($functionid, $strictness=IGNORE_MISSING) {
        global $DB;
        $function = $DB->get_record('external_functions',
                            array('id' => $functionid), '*', $strictness);
        return $function;
    }

    
    public function add_external_function_to_service($functionname, $serviceid) {
        global $DB;
        $addedfunction = new stdClass();
        $addedfunction->externalserviceid = $serviceid;
        $addedfunction->functionname = $functionname;
        $DB->insert_record('external_services_functions', $addedfunction);
    }

    
    public function add_external_service($service) {
        global $DB;
        $service->timecreated = time();
        $serviceid = $DB->insert_record('external_services', $service);
        return $serviceid;
    }

    
    public function update_external_service($service) {
        global $DB;
        $service->timemodified = time();
        $DB->update_record('external_services', $service);
    }

    
    public function service_function_exists($functionname, $serviceid) {
        global $DB;
        return $DB->record_exists('external_services_functions',
                            array('externalserviceid' => $serviceid,
                                'functionname' => $functionname));
    }

    
    public function remove_external_function_from_service($functionname, $serviceid) {
        global $DB;
        $DB->delete_records('external_services_functions',
                    array('externalserviceid' => $serviceid, 'functionname' => $functionname));

    }


}


class webservice_access_exception extends moodle_exception {

    
    function __construct($debuginfo) {
        parent::__construct('accessexception', 'webservice', '', null, $debuginfo);
    }
}


function webservice_protocol_is_enabled($protocol) {
    global $CFG;

    if (empty($CFG->enablewebservices)) {
        return false;
    }

    $active = explode(',', $CFG->webserviceprotocols);

    return(in_array($protocol, $active));
}


interface webservice_test_client_interface {

    
    public function simpletest($serverurl, $function, $params);
}


interface webservice_server_interface {

    
    public function run();
}


abstract class webservice_server implements webservice_server_interface {

    
    protected $wsname = null;

    
    protected $username = null;

    
    protected $password = null;

    
    protected $userid = null;

    
    protected $authmethod;

    
    protected $token = null;

    
    protected $restricted_context;

    
    protected $restricted_serviceid = null;

    
    public function __construct($authmethod) {
        $this->authmethod = $authmethod;
    }


    
    protected function authenticate_user() {
        global $CFG, $DB;

        if (!NO_MOODLE_COOKIES) {
            throw new coding_exception('Cookies must be disabled in WS servers!');
        }

        $loginfaileddefaultparams = array(
            'context' => context_system::instance(),
            'other' => array(
                'method' => $this->authmethod,
                'reason' => null
            )
        );

        if ($this->authmethod == WEBSERVICE_AUTHMETHOD_USERNAME) {

                                    if (!is_enabled_auth('webservice')) {
                throw new webservice_access_exception('The web service authentication plugin is disabled.');
            }

            if (!$auth = get_auth_plugin('webservice')) {
                throw new webservice_access_exception('The web service authentication plugin is missing.');
            }

            $this->restricted_context = context_system::instance();

            if (!$this->username) {
                throw new moodle_exception('missingusername', 'webservice');
            }

            if (!$this->password) {
                throw new moodle_exception('missingpassword', 'webservice');
            }

            if (!$auth->user_login_webservice($this->username, $this->password)) {

                                $params = $loginfaileddefaultparams;
                $params['other']['reason'] = 'password';
                $params['other']['username'] = $this->username;
                $event = \core\event\webservice_login_failed::create($params);
                $event->set_legacy_logdata(array(SITEID, 'webservice', get_string('simpleauthlog', 'webservice'), '' ,
                    get_string('failedtolog', 'webservice').": ".$this->username."/".$this->password." - ".getremoteaddr() , 0));
                $event->trigger();

                throw new moodle_exception('wrongusernamepassword', 'webservice');
            }

            $user = $DB->get_record('user', array('username'=>$this->username, 'mnethostid'=>$CFG->mnet_localhost_id), '*', MUST_EXIST);

        } else if ($this->authmethod == WEBSERVICE_AUTHMETHOD_PERMANENT_TOKEN){
            $user = $this->authenticate_by_token(EXTERNAL_TOKEN_PERMANENT);
        } else {
            $user = $this->authenticate_by_token(EXTERNAL_TOKEN_EMBEDDED);
        }

                $hassiteconfig = has_capability('moodle/site:config', context_system::instance(), $user);
        if (!empty($CFG->maintenance_enabled) and !$hassiteconfig) {
            throw new moodle_exception('sitemaintenance', 'admin');
        }

                if (!empty($user->deleted)) {
            $params = $loginfaileddefaultparams;
            $params['other']['reason'] = 'user_deleted';
            $params['other']['username'] = $user->username;
            $event = \core\event\webservice_login_failed::create($params);
            $event->set_legacy_logdata(array(SITEID, '', '', '', get_string('wsaccessuserdeleted', 'webservice',
                $user->username) . " - ".getremoteaddr(), 0, $user->id));
            $event->trigger();
            throw new webservice_access_exception('Refused web service access for deleted username: ' . $user->username);
        }

                if (empty($user->confirmed)) {
            $params = $loginfaileddefaultparams;
            $params['other']['reason'] = 'user_unconfirmed';
            $params['other']['username'] = $user->username;
            $event = \core\event\webservice_login_failed::create($params);
            $event->set_legacy_logdata(array(SITEID, '', '', '', get_string('wsaccessuserunconfirmed', 'webservice',
                $user->username) . " - ".getremoteaddr(), 0, $user->id));
            $event->trigger();
            throw new moodle_exception('wsaccessuserunconfirmed', 'webservice', '', $user->username);
        }

                if (!empty($user->suspended)) {
            $params = $loginfaileddefaultparams;
            $params['other']['reason'] = 'user_unconfirmed';
            $params['other']['username'] = $user->username;
            $event = \core\event\webservice_login_failed::create($params);
            $event->set_legacy_logdata(array(SITEID, '', '', '', get_string('wsaccessusersuspended', 'webservice',
                $user->username) . " - ".getremoteaddr(), 0, $user->id));
            $event->trigger();
            throw new webservice_access_exception('Refused web service access for suspended username: ' . $user->username);
        }

                if (empty($auth)) {
          $auth  = get_auth_plugin($user->auth);
        }

                if (!empty($auth->config->expiration) and $auth->config->expiration == 1) {
            $days2expire = $auth->password_expire($user->username);
            if (intval($days2expire) < 0 ) {
                $params = $loginfaileddefaultparams;
                $params['other']['reason'] = 'password_expired';
                $params['other']['username'] = $user->username;
                $event = \core\event\webservice_login_failed::create($params);
                $event->set_legacy_logdata(array(SITEID, '', '', '', get_string('wsaccessuserexpired', 'webservice',
                    $user->username) . " - ".getremoteaddr(), 0, $user->id));
                $event->trigger();
                throw new webservice_access_exception('Refused web service access for password expired username: ' . $user->username);
            }
        }

                if ($user->auth=='nologin') {
            $params = $loginfaileddefaultparams;
            $params['other']['reason'] = 'login';
            $params['other']['username'] = $user->username;
            $event = \core\event\webservice_login_failed::create($params);
            $event->set_legacy_logdata(array(SITEID, '', '', '', get_string('wsaccessusernologin', 'webservice',
                $user->username) . " - ".getremoteaddr(), 0, $user->id));
            $event->trigger();
            throw new webservice_access_exception('Refused web service access for nologin authentication username: ' . $user->username);
        }

                enrol_check_plugins($user);
        \core\session\manager::set_user($user);
        set_login_session_preferences();
        $this->userid = $user->id;

        if ($this->authmethod != WEBSERVICE_AUTHMETHOD_SESSION_TOKEN && !has_capability("webservice/$this->wsname:use", $this->restricted_context)) {
            throw new webservice_access_exception('You are not allowed to use the {$a} protocol (missing capability: webservice/' . $this->wsname . ':use)');
        }

        external_api::set_context_restriction($this->restricted_context);
    }

    
    protected function authenticate_by_token($tokentype){
        global $DB;

        $loginfaileddefaultparams = array(
            'context' => context_system::instance(),
            'other' => array(
                'method' => $this->authmethod,
                'reason' => null
            )
        );

        if (!$token = $DB->get_record('external_tokens', array('token'=>$this->token, 'tokentype'=>$tokentype))) {
                        $params = $loginfaileddefaultparams;
            $params['other']['reason'] = 'invalid_token';
            $event = \core\event\webservice_login_failed::create($params);
            $event->set_legacy_logdata(array(SITEID, 'webservice', get_string('tokenauthlog', 'webservice'), '' ,
                get_string('failedtolog', 'webservice').": ".$this->token. " - ".getremoteaddr() , 0));
            $event->trigger();
            throw new moodle_exception('invalidtoken', 'webservice');
        }

        if ($token->validuntil and $token->validuntil < time()) {
            $DB->delete_records('external_tokens', array('token'=>$this->token, 'tokentype'=>$tokentype));
            throw new webservice_access_exception('Invalid token - token expired - check validuntil time for the token');
        }

        if ($token->sid){            if (!\core\session\manager::session_exists($token->sid)){
                $DB->delete_records('external_tokens', array('sid'=>$token->sid));
                throw new webservice_access_exception('Invalid session based token - session not found or expired');
            }
        }

        if ($token->iprestriction and !address_in_subnet(getremoteaddr(), $token->iprestriction)) {
            $params = $loginfaileddefaultparams;
            $params['other']['reason'] = 'ip_restricted';
            $params['other']['tokenid'] = $token->id;
            $event = \core\event\webservice_login_failed::create($params);
            $event->add_record_snapshot('external_tokens', $token);
            $event->set_legacy_logdata(array(SITEID, 'webservice', get_string('tokenauthlog', 'webservice'), '' ,
                get_string('failedtolog', 'webservice').": ".getremoteaddr() , 0));
            $event->trigger();
            throw new webservice_access_exception('Invalid service - IP:' . getremoteaddr()
                    . ' is not supported - check this allowed user');
        }

        $this->restricted_context = context::instance_by_id($token->contextid);
        $this->restricted_serviceid = $token->externalserviceid;

        $user = $DB->get_record('user', array('id'=>$token->userid), '*', MUST_EXIST);

                $DB->set_field('external_tokens', 'lastaccess', time(), array('id'=>$token->id));

        return $user;

    }

    
    protected function set_web_service_call_settings() {
        global $CFG;

                                $externalsettings = array(
            'raw' => false,
            'fileurl' => true,
            'filter' =>  false);

                $settings = external_settings::get_instance();
        foreach ($externalsettings as $name => $default) {

            $wsparamname = 'moodlewssetting' . $name;

                        $value = optional_param($wsparamname, $default, PARAM_BOOL);
            unset($_GET[$wsparamname]);
            unset($_POST[$wsparamname]);

            $functioname = 'set_' . $name;
            $settings->$functioname($value);
        }

    }
}


abstract class webservice_base_server extends webservice_server {

    
    protected $parameters = null;

    
    protected $functionname = null;

    
    protected $function = null;

    
    protected $returns = null;

    
    protected $servicemethods;

    
    protected $servicestructs;

    
    abstract protected function parse_request();

    
    abstract protected function send_response();

    
    abstract protected function send_error($ex=null);

    
    public function run() {
                raise_memory_limit(MEMORY_EXTRA);

                                external_api::set_timeout();

                                set_exception_handler(array($this, 'exception_handler'));

                $this->parse_request();

                        $this->authenticate_user();

                $this->load_function_info();

                $params = array(
            'other' => array(
                'function' => $this->functionname
            )
        );
        $event = \core\event\webservice_function_called::create($params);
        $event->set_legacy_logdata(array(SITEID, 'webservice', $this->functionname, '' , getremoteaddr() , 0, $this->userid));
        $event->trigger();

                $this->execute();

                $this->send_response();

                $this->session_cleanup();

        die;
    }

    
    public function exception_handler($ex) {
                abort_all_db_transactions();

                $this->session_cleanup($ex);

                $this->send_error($ex);

                exit(1);
    }

    
    protected function session_cleanup($exception=null) {
        if ($this->authmethod == WEBSERVICE_AUTHMETHOD_USERNAME) {
                    } else {
                    }
    }

    
    protected function load_function_info() {
        global $DB, $USER, $CFG;

        if (empty($this->functionname)) {
            throw new invalid_parameter_exception('Missing function name');
        }

                $function = external_api::external_function_info($this->functionname);

        if ($this->restricted_serviceid) {
            $params = array('sid1'=>$this->restricted_serviceid, 'sid2'=>$this->restricted_serviceid);
            $wscond1 = 'AND s.id = :sid1';
            $wscond2 = 'AND s.id = :sid2';
        } else {
            $params = array();
            $wscond1 = '';
            $wscond2 = '';
        }

        
                                                
        $sql = "SELECT s.*, NULL AS iprestriction
                  FROM {external_services} s
                  JOIN {external_services_functions} sf ON (sf.externalserviceid = s.id AND s.restrictedusers = 0 AND sf.functionname = :name1)
                 WHERE s.enabled = 1 $wscond1

                 UNION

                SELECT s.*, su.iprestriction
                  FROM {external_services} s
                  JOIN {external_services_functions} sf ON (sf.externalserviceid = s.id AND s.restrictedusers = 1 AND sf.functionname = :name2)
                  JOIN {external_services_users} su ON (su.externalserviceid = s.id AND su.userid = :userid)
                 WHERE s.enabled = 1 AND (su.validuntil IS NULL OR su.validuntil < :now) $wscond2";
        $params = array_merge($params, array('userid'=>$USER->id, 'name1'=>$function->name, 'name2'=>$function->name, 'now'=>time()));

        $rs = $DB->get_recordset_sql($sql, $params);
                $remoteaddr = getremoteaddr();
        $allowed = false;
        foreach ($rs as $service) {
            if ($service->requiredcapability and !has_capability($service->requiredcapability, $this->restricted_context)) {
                continue;             }
            if ($service->iprestriction and !address_in_subnet($remoteaddr, $service->iprestriction)) {
                continue;             }
            $allowed = true;
            break;         }
        $rs->close();
        if (!$allowed) {
            throw new webservice_access_exception(
                    'Access to the function '.$this->functionname.'() is not allowed.
                     There could be multiple reasons for this:
                     1. The service linked to the user token does not contain the function.
                     2. The service is user-restricted and the user is not listed.
                     3. The service is IP-restricted and the user IP is not listed.
                     4. The service is time-restricted and the time has expired.
                     5. The token is time-restricted and the time has expired.
                     6. The service requires a specific capability which the user does not have.
                     7. The function is called with username/password (no user token is sent)
                     and none of the services has the function to allow the user.
                     These settings can be found in Administration > Site administration
                     > Plugins > Web services > External services and Manage tokens.');
        }

                $this->function = $function;
    }

    
    protected function execute() {
                $params = call_user_func(array($this->function->classname, 'validate_parameters'), $this->function->parameters_desc, $this->parameters);

                $this->returns = call_user_func_array(array($this->function->classname, $this->function->methodname), array_values($params));
    }

    
    protected function init_service_class() {
        global $USER, $DB;

                $this->servicemethods = array();
        $this->servicestructs = array();

        $params = array();
        $wscond1 = '';
        $wscond2 = '';
        if ($this->restricted_serviceid) {
            $params = array('sid1' => $this->restricted_serviceid, 'sid2' => $this->restricted_serviceid);
            $wscond1 = 'AND s.id = :sid1';
            $wscond2 = 'AND s.id = :sid2';
        }

        $sql = "SELECT s.*, NULL AS iprestriction
                  FROM {external_services} s
                  JOIN {external_services_functions} sf ON (sf.externalserviceid = s.id AND s.restrictedusers = 0)
                 WHERE s.enabled = 1 $wscond1

                 UNION

                SELECT s.*, su.iprestriction
                  FROM {external_services} s
                  JOIN {external_services_functions} sf ON (sf.externalserviceid = s.id AND s.restrictedusers = 1)
                  JOIN {external_services_users} su ON (su.externalserviceid = s.id AND su.userid = :userid)
                 WHERE s.enabled = 1 AND (su.validuntil IS NULL OR su.validuntil < :now) $wscond2";
        $params = array_merge($params, array('userid' => $USER->id, 'now' => time()));

        $serviceids = array();
        $remoteaddr = getremoteaddr();

                $rs = $DB->get_recordset_sql($sql, $params);

                foreach ($rs as $service) {
            if (isset($serviceids[$service->id])) {
                continue;             }
            if ($service->requiredcapability and !has_capability($service->requiredcapability, $this->restricted_context)) {
                continue;             }
            if ($service->iprestriction and !address_in_subnet($remoteaddr, $service->iprestriction)) {
                continue;             }
            $serviceids[$service->id] = $service->id;
        }
        $rs->close();

                $classname = 'webservices_virtual_class_000000';
        while (class_exists($classname)) {
            $classname++;
        }
        $this->serviceclass = $classname;

                $wsmanager = new webservice();
        $functions = $wsmanager->get_external_functions($serviceids);

                $methods = '';
        foreach ($functions as $function) {
            $methods .= $this->get_virtual_method_code($function);
        }

        $code = <<<EOD
/**
 * Virtual class web services for user id $USER->id in context {$this->restricted_context->id}.
 */
class $classname {
$methods
}
EOD;
                eval($code);
    }

    
    protected function generate_simple_struct_class(external_single_structure $structdesc) {
        global $USER;

        $propeties = array();
        $fields = array();
        foreach ($structdesc->keys as $name => $fieldsdesc) {
            $type = $this->get_phpdoc_type($fieldsdesc);
            $propertytype = array('type' => $type);
            if (empty($fieldsdesc->allownull) || $fieldsdesc->allownull == NULL_ALLOWED) {
                $propertytype['nillable'] = true;
            }
            $propeties[$name] = $propertytype;
            $fields[] = '    /** @var ' . $type . ' $' . $name . '*/';
            $fields[] = '    public $' . $name .';';
        }
        $fieldsstr = implode("\n", $fields);

                $classname = 'webservices_struct_class_000000';
        while (class_exists($classname)) {
            $classname++;
        }
        $code = <<<EOD
/**
 * Virtual struct class for web services for user id $USER->id in context {$this->restricted_context->id}.
 */
class $classname {
$fieldsstr
}
EOD;
                eval($code);

                $structinfo = new stdClass();
        $structinfo->classname = $classname;
        $structinfo->properties = $propeties;
                $this->servicestructs[] = $structinfo;

        return $classname;
    }

    
    protected function get_virtual_method_code($function) {
        $function = external_api::external_function_info($function);

                $paramanddefaults = array();
                $params = array();
        $paramdesc = array();
                $inputparams = array();
                $outputparams = array();

        foreach ($function->parameters_desc->keys as $name => $keydesc) {
            $param = '$' . $name;
            $paramanddefault = $param;
            if ($keydesc->required == VALUE_OPTIONAL) {
                                throw new moodle_exception('erroroptionalparamarray', 'webservice', '', $name);
            } else if ($keydesc->required == VALUE_DEFAULT) {
                                if ($keydesc instanceof external_value) {
                    if ($keydesc->default === null) {
                        $paramanddefault .= ' = null';
                    } else {
                        switch ($keydesc->type) {
                            case PARAM_BOOL:
                                $default = (int)$keydesc->default;
                                break;
                            case PARAM_INT:
                                $default = $keydesc->default;
                                break;
                            case PARAM_FLOAT;
                                $default = $keydesc->default;
                                break;
                            default:
                                $default = "'$keydesc->default'";
                        }
                        $paramanddefault .= " = $default";
                    }
                } else {
                                        if (isset($keydesc->default) && is_array($keydesc->default) && empty($keydesc->default)) {
                        $paramanddefault .= ' = array()';
                    } else {
                                                throw new moodle_exception('errornotemptydefaultparamarray', 'webservice', '', $name);
                    }
                }
            }

            $params[] = $param;
            $paramanddefaults[] = $paramanddefault;
            $type = $this->get_phpdoc_type($keydesc);
            $inputparams[$name]['type'] = $type;

            $paramdesc[] = '* @param ' . $type . ' $' . $name . ' ' . $keydesc->desc;
        }
        $paramanddefaults = implode(', ', $paramanddefaults);
        $paramdescstr = implode("\n ", $paramdesc);

        $serviceclassmethodbody = $this->service_class_method_body($function, $params);

        if (empty($function->returns_desc)) {
            $return = '* @return void';
        } else {
            $type = $this->get_phpdoc_type($function->returns_desc);
            $outputparams['return']['type'] = $type;
            $return = '* @return ' . $type . ' ' . $function->returns_desc->desc;
        }

                $code = <<<EOD
/**
 * $function->description.
 *
 $paramdescstr
 $return
 */
public function $function->name($paramanddefaults) {
$serviceclassmethodbody
}
EOD;

                $methodinfo = new stdClass();
        $methodinfo->name = $function->name;
        $methodinfo->inputparams = $inputparams;
        $methodinfo->outputparams = $outputparams;
        $methodinfo->description = $function->description;
                $this->servicemethods[] = $methodinfo;

        return $code;
    }

    
    protected function get_phpdoc_type($keydesc) {
        $type = null;
        if ($keydesc instanceof external_value) {
            switch ($keydesc->type) {
                case PARAM_BOOL:                 case PARAM_INT:
                    $type = 'int';
                    break;
                case PARAM_FLOAT;
                    $type = 'double';
                    break;
                default:
                    $type = 'string';
            }
        } else if ($keydesc instanceof external_single_structure) {
            $type = $this->generate_simple_struct_class($keydesc);
        } else if ($keydesc instanceof external_multiple_structure) {
            $type = 'array';
        }

        return $type;
    }

    
    protected function service_class_method_body($function, $params) {
                $castingcode = '';
        $paramsstr = '';
        if (!empty($params)) {
            foreach ($params as $paramtocast) {
                                $paramtocast = trim($paramtocast);
                $castingcode .= "    $paramtocast = json_decode(json_encode($paramtocast), true);\n";
            }
            $paramsstr = implode(', ', $params);
        }

        $descriptionmethod = $function->methodname . '_returns()';
        $callforreturnvaluedesc = $function->classname . '::' . $descriptionmethod;

        $methodbody = <<<EOD
$castingcode
    if ($callforreturnvaluedesc == null) {
        $function->classname::$function->methodname($paramsstr);
        return null;
    }
    return external_api::clean_returnvalue($callforreturnvaluedesc, $function->classname::$function->methodname($paramsstr));
EOD;
        return $methodbody;
    }
}
