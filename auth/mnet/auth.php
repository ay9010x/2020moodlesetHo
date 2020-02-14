<?php



defined('MOODLE_INTERNAL') || die();

require_once($CFG->libdir.'/authlib.php');


class auth_plugin_mnet extends auth_plugin_base {

    
    public function __construct() {
        $this->authtype = 'mnet';
        $this->config = get_config('auth_mnet');
        $this->mnet = get_mnet_environment();
    }

    
    public function auth_plugin_mnet() {
        debugging('Use of class name as constructor is deprecated', DEBUG_DEVELOPER);
        self::__construct();
    }

    
    function user_login($username, $password) {
        return false;     }

    
    function user_authorise($token, $useragent) {
        global $CFG, $SITE, $DB;
        $remoteclient = get_mnet_remote_client();
        require_once $CFG->dirroot . '/mnet/xmlrpc/serverlib.php';

        $mnet_session = $DB->get_record('mnet_session', array('token'=>$token, 'useragent'=>$useragent));
        if (empty($mnet_session)) {
            throw new mnet_server_exception(1, 'authfail_nosessionexists');
        }

                if ($mnet_session->confirm_timeout < time()) {
            throw new mnet_server_exception(2, 'authfail_sessiontimedout');
        }

                if (!$user = $DB->get_record('user', array('id'=>$mnet_session->userid))) {
            throw new mnet_server_exception(3, 'authfail_usermismatch');
        }

        $userdata = mnet_strip_user((array)$user, mnet_fields_to_send($remoteclient));

                $userdata['auth']                    = 'mnet';
        $userdata['wwwroot']                 = $this->mnet->wwwroot;
        $userdata['session.gc_maxlifetime']  = ini_get('session.gc_maxlifetime');

        if (array_key_exists('picture', $userdata) && !empty($user->picture)) {
            $fs = get_file_storage();
            $usercontext = context_user::instance($user->id, MUST_EXIST);
            if ($usericonfile = $fs->get_file($usercontext->id, 'user', 'icon', 0, '/', 'f1.png')) {
                $userdata['_mnet_userpicture_timemodified'] = $usericonfile->get_timemodified();
                $userdata['_mnet_userpicture_mimetype'] = $usericonfile->get_mimetype();
            } else if ($usericonfile = $fs->get_file($usercontext->id, 'user', 'icon', 0, '/', 'f1.jpg')) {
                $userdata['_mnet_userpicture_timemodified'] = $usericonfile->get_timemodified();
                $userdata['_mnet_userpicture_mimetype'] = $usericonfile->get_mimetype();
            }
        }

        $userdata['myhosts'] = array();
        if ($courses = enrol_get_users_courses($user->id, false)) {
            $userdata['myhosts'][] = array('name'=> $SITE->shortname, 'url' => $CFG->wwwroot, 'count' => count($courses));
        }

        $sql = "SELECT h.name AS hostname, h.wwwroot, h.id AS hostid,
                       COUNT(c.id) AS count
                  FROM {mnetservice_enrol_courses} c
                  JOIN {mnetservice_enrol_enrolments} e ON (e.hostid = c.hostid AND e.remotecourseid = c.remoteid)
                  JOIN {mnet_host} h ON h.id = c.hostid
                 WHERE e.userid = ? AND c.hostid = ?
              GROUP BY h.name, h.wwwroot, h.id";

        if ($courses = $DB->get_records_sql($sql, array($user->id, $remoteclient->id))) {
            foreach($courses as $course) {
                $userdata['myhosts'][] = array('name'=> $course->hostname, 'url' => $CFG->wwwroot.'/auth/mnet/jump.php?hostid='.$course->hostid, 'count' => $course->count);
            }
        }

        return $userdata;
    }

    
    function generate_token() {
        return sha1(str_shuffle('' . mt_rand() . time()));
    }

    
    function start_jump_session($mnethostid, $wantsurl, $wantsurlbackhere=false) {
        global $CFG, $USER, $DB;
        require_once $CFG->dirroot . '/mnet/xmlrpc/client.php';

        if (\core\session\manager::is_loggedinas()) {
            print_error('notpermittedtojumpas', 'mnet');
        }

                if (! has_capability('moodle/site:mnetlogintoremote', context_system::instance())
                or is_mnet_remote_user($USER)
                or isguestuser()
                or !isloggedin()) {
            print_error('notpermittedtojump', 'mnet');
        }

                if ($this->has_service($mnethostid, 'sso_sp') == false) {
            print_error('hostnotconfiguredforsso', 'mnet');
        }

                if (empty($this->config->rpc_negotiation_timeout)) {
            $this->config->rpc_negotiation_timeout = 30;
            set_config('rpc_negotiation_timeout', '30', 'auth_mnet');
        }

                $mnet_peer = new mnet_peer();
        $mnet_peer->set_id($mnethostid);

                $mnet_session = $DB->get_record('mnet_session',
                                   array('userid'=>$USER->id, 'mnethostid'=>$mnethostid,
                                   'useragent'=>sha1($_SERVER['HTTP_USER_AGENT'])));
        if ($mnet_session == false) {
            $mnet_session = new stdClass();
            $mnet_session->mnethostid = $mnethostid;
            $mnet_session->userid = $USER->id;
            $mnet_session->username = $USER->username;
            $mnet_session->useragent = sha1($_SERVER['HTTP_USER_AGENT']);
            $mnet_session->token = $this->generate_token();
            $mnet_session->confirm_timeout = time() + $this->config->rpc_negotiation_timeout;
            $mnet_session->expires = time() + (integer)ini_get('session.gc_maxlifetime');
            $mnet_session->session_id = session_id();
            $mnet_session->id = $DB->insert_record('mnet_session', $mnet_session);
        } else {
            $mnet_session->useragent = sha1($_SERVER['HTTP_USER_AGENT']);
            $mnet_session->token = $this->generate_token();
            $mnet_session->confirm_timeout = time() + $this->config->rpc_negotiation_timeout;
            $mnet_session->expires = time() + (integer)ini_get('session.gc_maxlifetime');
            $mnet_session->session_id = session_id();
            $DB->update_record('mnet_session', $mnet_session);
        }

                        $wantsurl = urlencode($wantsurl);
        $url = "{$mnet_peer->wwwroot}{$mnet_peer->application->sso_land_url}?token={$mnet_session->token}&idp={$this->mnet->wwwroot}&wantsurl={$wantsurl}";
        if ($wantsurlbackhere) {
            $url .= '&remoteurl=1';
        }

        return $url;
    }

    
    function confirm_mnet_session($token, $remotepeer) {
        global $CFG, $DB;
        require_once $CFG->dirroot . '/mnet/xmlrpc/client.php';
        require_once $CFG->libdir . '/gdlib.php';
        require_once($CFG->dirroot.'/user/lib.php');

                if (! $remotehost = $DB->get_record('mnet_host', array('wwwroot' => $remotepeer->wwwroot, 'deleted' => 0))) {
            print_error('notpermittedtoland', 'mnet');
        }

                $mnetrequest = new mnet_xmlrpc_client();
        $mnetrequest->set_method('auth/mnet/auth.php/user_authorise');

                $mnetrequest->add_param($token);
        $mnetrequest->add_param(sha1($_SERVER['HTTP_USER_AGENT']));

                if ($mnetrequest->send($remotepeer) === true) {
            $remoteuser = (object) $mnetrequest->response;
        } else {
            foreach ($mnetrequest->error as $errormessage) {
                list($code, $message) = array_map('trim',explode(':', $errormessage, 2));
                if($code == 702) {
                    $site = get_site();
                    print_error('mnet_session_prohibited', 'mnet', $remotepeer->wwwroot, format_string($site->fullname));
                    exit;
                }
                $message .= "ERROR $code:<br/>$errormessage<br/>";
            }
            print_error("rpcerror", '', '', $message);
        }
        unset($mnetrequest);

        if (empty($remoteuser) or empty($remoteuser->username)) {
            print_error('unknownerror', 'mnet');
            exit;
        }

        if (user_not_fully_set_up($remoteuser, false)) {
            print_error('notenoughidpinfo', 'mnet');
            exit;
        }

        $remoteuser = mnet_strip_user($remoteuser, mnet_fields_to_import($remotepeer));

        $remoteuser->auth = 'mnet';
        $remoteuser->wwwroot = $remotepeer->wwwroot;

                        if (isset($remoteuser->lang)) {
            $remoteuser->lang = clean_param(str_replace('_utf8', '', $remoteuser->lang), PARAM_LANG);
        }
        if (empty($remoteuser->lang)) {
            if (!empty($CFG->lang)) {
                $remoteuser->lang = $CFG->lang;
            } else {
                $remoteuser->lang = 'en';
            }
        }
        $firsttime = false;

                $localuser = $DB->get_record('user', array('username'=>$remoteuser->username, 'mnethostid'=>$remotehost->id));

                        if (empty($localuser) || ! $localuser->id) {
            
            $remoteuser->mnethostid = $remotehost->id;
            $remoteuser->firstaccess = 0;
            $remoteuser->confirmed = 1;

            $remoteuser->id = user_create_user($remoteuser, false);
            $firsttime = true;
            $localuser = $remoteuser;
        }

                if (!$this->can_login_remotely($localuser->username, $remotehost->id)) {
            print_error('sso_mnet_login_refused', 'mnet', '', array('user'=>$localuser->username, 'host'=>$remotehost->name));
        }

        $fs = get_file_storage();

                foreach ((array) $remoteuser as $key => $val) {

            if ($key == '_mnet_userpicture_timemodified' and empty($CFG->disableuserimages) and isset($remoteuser->picture)) {
                                $usercontext = context_user::instance($localuser->id, MUST_EXIST);
                if ($usericonfile = $fs->get_file($usercontext->id, 'user', 'icon', 0, '/', 'f1.png')) {
                    $localtimemodified = $usericonfile->get_timemodified();
                } else if ($usericonfile = $fs->get_file($usercontext->id, 'user', 'icon', 0, '/', 'f1.jpg')) {
                    $localtimemodified = $usericonfile->get_timemodified();
                } else {
                    $localtimemodified = 0;
                }

                if (!empty($val) and $localtimemodified < $val) {
                    mnet_debug('refetching the user picture from the identity provider host');
                    $fetchrequest = new mnet_xmlrpc_client();
                    $fetchrequest->set_method('auth/mnet/auth.php/fetch_user_image');
                    $fetchrequest->add_param($localuser->username);
                    if ($fetchrequest->send($remotepeer) === true) {
                        if (strlen($fetchrequest->response['f1']) > 0) {
                            $imagefilename = $CFG->tempdir . '/mnet-usericon-' . $localuser->id;
                            $imagecontents = base64_decode($fetchrequest->response['f1']);
                            file_put_contents($imagefilename, $imagecontents);
                            if ($newrev = process_new_icon($usercontext, 'user', 'icon', 0, $imagefilename)) {
                                $localuser->picture = $newrev;
                            }
                            unlink($imagefilename);
                        }
                                                                                            }
                }
            }

            if($key == 'myhosts') {
                $localuser->mnet_foreign_host_array = array();
                foreach($val as $rhost) {
                    $name  = clean_param($rhost['name'], PARAM_ALPHANUM);
                    $url   = clean_param($rhost['url'], PARAM_URL);
                    $count = clean_param($rhost['count'], PARAM_INT);
                    $url_is_local = stristr($url , $CFG->wwwroot);
                    if (!empty($name) && !empty($count) && empty($url_is_local)) {
                        $localuser->mnet_foreign_host_array[] = array('name'  => $name,
                                                                      'url'   => $url,
                                                                      'count' => $count);
                    }
                }
            }

            $localuser->{$key} = $val;
        }

        $localuser->mnethostid = $remotepeer->id;
        user_update_user($localuser, false);

        if (!$firsttime) {
                                                $mnetrequest = new mnet_xmlrpc_client();
            $mnetrequest->set_method('auth/mnet/auth.php/update_enrolments');

                                    $mnetrequest->add_param($remoteuser->username);
            $fields = 'id, category, sortorder, fullname, shortname, idnumber, summary, startdate, visible';
            $courses = enrol_get_users_courses($localuser->id, false, $fields, 'visible DESC,sortorder ASC');
            if (is_array($courses) && !empty($courses)) {
                                                $sql = "SELECT c.id,
                               cc.name AS cat_name, cc.description AS cat_description
                          FROM {course} c
                          JOIN {course_categories} cc ON c.category = cc.id
                         WHERE c.id IN (" . join(',',array_keys($courses)) . ')';
                $extra = $DB->get_records_sql($sql);

                $keys = array_keys($courses);
                $studentroles = get_archetype_roles('student');
                if (!empty($studentroles)) {
                    $defaultrole = reset($studentroles);
                                        foreach ($keys AS $id) {
                        if ($courses[$id]->visible == 0) {
                            unset($courses[$id]);
                            continue;
                        }
                        $courses[$id]->cat_id          = $courses[$id]->category;
                        $courses[$id]->defaultroleid   = $defaultrole->id;
                        unset($courses[$id]->category);
                        unset($courses[$id]->visible);

                        $courses[$id]->cat_name        = $extra[$id]->cat_name;
                        $courses[$id]->cat_description = $extra[$id]->cat_description;
                        $courses[$id]->defaultrolename = $defaultrole->name;
                                                $courses[$id] = (array)$courses[$id];
                    }
                } else {
                    throw new moodle_exception('unknownrole', 'error', '', 'student');
                }
            } else {
                                                $courses = array();
            }
            $mnetrequest->add_param($courses);

                                    if ($mnetrequest->send($remotepeer) === false) {
                            }
        }

        return $localuser;
    }


    
    public function update_mnet_session($user, $token, $remotepeer) {
        global $DB;
        $session_gc_maxlifetime = 1440;
        if (isset($user->session_gc_maxlifetime)) {
            $session_gc_maxlifetime = $user->session_gc_maxlifetime;
        }
        if (!$mnet_session = $DB->get_record('mnet_session',
                                   array('userid'=>$user->id, 'mnethostid'=>$remotepeer->id,
                                   'useragent'=>sha1($_SERVER['HTTP_USER_AGENT'])))) {
            $mnet_session = new stdClass();
            $mnet_session->mnethostid = $remotepeer->id;
            $mnet_session->userid = $user->id;
            $mnet_session->username = $user->username;
            $mnet_session->useragent = sha1($_SERVER['HTTP_USER_AGENT']);
            $mnet_session->token = $token;                                                        $mnet_session->confirm_timeout = time();
            $mnet_session->expires = time() + (integer)$session_gc_maxlifetime;
            $mnet_session->session_id = session_id();
            $mnet_session->id = $DB->insert_record('mnet_session', $mnet_session);
        } else {
            $mnet_session->expires = time() + (integer)$session_gc_maxlifetime;
            $DB->update_record('mnet_session', $mnet_session);
        }
    }



    
    function update_enrolments($username, $courses) {
        global $CFG, $DB;
        $remoteclient = get_mnet_remote_client();

        if (empty($username) || !is_array($courses)) {
            return false;
        }
                        $mnetsessions = $DB->get_records('mnet_session', array('username' => $username, 'mnethostid' => $remoteclient->id), '', 'id, userid');
        $userid = null;
        foreach ($mnetsessions as $mnetsession) {
            if (is_null($userid)) {
                $userid = $mnetsession->userid;
                continue;
            }
            if ($userid != $mnetsession->userid) {
                throw new mnet_server_exception(3, 'authfail_usermismatch');
            }
        }

        if (empty($courses)) {             $DB->delete_records('mnetservice_enrol_enrolments', array('hostid'=>$remoteclient->id, 'userid'=>$userid));
            return true;
        }

                                $sql = "SELECT c.remoteid, c.id, c.categoryid AS cat_id, c.categoryname AS cat_name, c.sortorder,
                       c.fullname, c.shortname, c.idnumber, c.summary, c.summaryformat, c.startdate,
                       e.id AS enrolmentid
                  FROM {mnetservice_enrol_courses} c
             LEFT JOIN {mnetservice_enrol_enrolments} e ON (e.hostid = c.hostid AND e.remotecourseid = c.remoteid)
                 WHERE e.userid = ? AND c.hostid = ?";

        $currentcourses = $DB->get_records_sql($sql, array($userid, $remoteclient->id));

        $local_courseid_array = array();
        foreach($courses as $ix => $course) {

            $course['remoteid'] = $course['id'];
            $course['hostid']   =  (int)$remoteclient->id;
            $userisregd         = false;

                                    if (array_key_exists($course['remoteid'], $currentcourses)) {
                                $currentcourse =& $currentcourses[$course['remoteid']];
                                $course['id'] = $currentcourse->id;

                $saveflag = false;

                foreach($course as $key => $value) {
                    if ($currentcourse->$key != $value) {
                        $saveflag = true;
                        $currentcourse->$key = $value;
                    }
                }

                if ($saveflag) {
                    $DB->update_record('mnetervice_enrol_courses', $currentcourse);
                }

                if (isset($currentcourse->enrolmentid) && is_numeric($currentcourse->enrolmentid)) {
                    $userisregd = true;
                }
            } else {
                unset ($courses[$ix]);
                continue;
            }

                        $local_courseid_array[] = $course['id'];

                        if ($userisregd) {
                                                            } else {
                                $assignObj = new stdClass();
                $assignObj->userid    = $userid;
                $assignObj->hostid    = (int)$remoteclient->id;
                $assignObj->remotecourseid = $course['remoteid'];
                $assignObj->rolename  = $course['defaultrolename'];
                $assignObj->id = $DB->insert_record('mnetservice_enrol_enrolments', $assignObj);
            }
        }

                if (!empty($local_courseid_array)) {
            $local_courseid_string = implode(', ', $local_courseid_array);
            $whereclause = " userid = ? AND hostid = ? AND remotecourseid NOT IN ($local_courseid_string)";
            $DB->delete_records_select('mnetservice_enrol_enrolments', $whereclause, array($userid, $remoteclient->id));
        }
    }

    function prevent_local_passwords() {
        return true;
    }

    
    function is_internal() {
        return false;
    }

    
    function can_change_password() {
                return false;
    }

    
    function change_password_url() {
        return null;
    }

    
    function config_form($config, $err, $user_fields) {
        global $CFG, $DB;

         $query = "
            SELECT
                h.id,
                h.name as hostname,
                h.wwwroot,
                h2idp.publish as idppublish,
                h2idp.subscribe as idpsubscribe,
                idp.name as idpname,
                h2sp.publish as sppublish,
                h2sp.subscribe as spsubscribe,
                sp.name as spname
            FROM
                {mnet_host} h
            LEFT JOIN
                {mnet_host2service} h2idp
            ON
               (h.id = h2idp.hostid AND
               (h2idp.publish = 1 OR
                h2idp.subscribe = 1))
            INNER JOIN
                {mnet_service} idp
            ON
               (h2idp.serviceid = idp.id AND
                idp.name = 'sso_idp')
            LEFT JOIN
                {mnet_host2service} h2sp
            ON
               (h.id = h2sp.hostid AND
               (h2sp.publish = 1 OR
                h2sp.subscribe = 1))
            INNER JOIN
                {mnet_service} sp
            ON
               (h2sp.serviceid = sp.id AND
                sp.name = 'sso_sp')
            WHERE
               ((h2idp.publish = 1 AND h2sp.subscribe = 1) OR
               (h2sp.publish = 1 AND h2idp.subscribe = 1)) AND
                h.id != ?
            ORDER BY
                h.name ASC";

        $id_providers       = array();
        $service_providers  = array();
        if ($resultset = $DB->get_records_sql($query, array($CFG->mnet_localhost_id))) {
            foreach($resultset as $hostservice) {
                if(!empty($hostservice->idppublish) && !empty($hostservice->spsubscribe)) {
                    $service_providers[]= array('id' => $hostservice->id, 'name' => $hostservice->hostname, 'wwwroot' => $hostservice->wwwroot);
                }
                if(!empty($hostservice->idpsubscribe) && !empty($hostservice->sppublish)) {
                    $id_providers[]= array('id' => $hostservice->id, 'name' => $hostservice->hostname, 'wwwroot' => $hostservice->wwwroot);
                }
            }
        }

        include "config.html";
    }

    
    function process_config($config) {
                if (!isset ($config->rpc_negotiation_timeout)) {
            $config->rpc_negotiation_timeout = '30';
        }
        

                set_config('rpc_negotiation_timeout', $config->rpc_negotiation_timeout, 'auth_mnet');

        return true;
    }

    
    function keepalive_client() {
        global $CFG, $DB;
        $cutoff = time() - 300;                                 
        $sql = "
            select
                id,
                username,
                mnethostid
            from
                {user}
            where
                lastaccess > ? AND
                mnethostid != ?
            order by
                mnethostid";

        $immigrants = $DB->get_records_sql($sql, array($cutoff, $CFG->mnet_localhost_id));

        if ($immigrants == false) {
            return true;
        }

        $usersArray = array();
        foreach($immigrants as $immigrant) {
            $usersArray[$immigrant->mnethostid][] = $immigrant->username;
        }

        require_once $CFG->dirroot . '/mnet/xmlrpc/client.php';
        foreach($usersArray as $mnethostid => $users) {
            $mnet_peer = new mnet_peer();
            $mnet_peer->set_id($mnethostid);

            $mnet_request = new mnet_xmlrpc_client();
            $mnet_request->set_method('auth/mnet/auth.php/keepalive_server');

                        $mnet_request->add_param($users);

            if ($mnet_request->send($mnet_peer) === true) {
                if (!isset($mnet_request->response['code'])) {
                    debugging("Server side error has occured on host $mnethostid");
                    continue;
                } elseif ($mnet_request->response['code'] > 0) {
                    debugging($mnet_request->response['message']);
                }

                if (!isset($mnet_request->response['last log id'])) {
                    debugging("Server side error has occured on host $mnethostid\nNo log ID was received.");
                    continue;
                }
            } else {
                debugging("Server side error has occured on host $mnethostid: " .
                          join("\n", $mnet_request->error));
                break;
            }
        }
    }

    
    function refresh_log($array) {
        debugging('refresh_log() is deprecated, The transfer of logs through mnet are no longer recorded.', DEBUG_DEVELOPER);
        return array('code' => 0, 'message' => 'All ok');
    }

    
    function keepalive_server($array) {
        global $CFG, $DB;
        $remoteclient = get_mnet_remote_client();

                $start = ob_start();

                $superArray = array_chunk($array, 30);

        $returnString = '';

        foreach($superArray as $subArray) {
            $subArray = array_values($subArray);
            $instring = "('".implode("', '",$subArray)."')";
            $query = "select id, session_id, username from {mnet_session} where username in $instring";
            $results = $DB->get_records_sql($query);

            if ($results == false) {
                                                $returnString .= "We failed to refresh the session for the following usernames: \n".implode("\n", $subArray)."\n\n";
            } else {
                foreach($results as $emigrant) {
                    \core\session\manager::touch_session($emigrant->session_id);
                }
            }
        }

        $end = ob_end_clean();

        if (empty($returnString)) return array('code' => 0, 'message' => 'All ok', 'last log id' => $remoteclient->last_log_id);
        return array('code' => 1, 'message' => $returnString, 'last log id' => $remoteclient->last_log_id);
    }

    
    function cron() {
        global $DB;

                $this->keepalive_client();

        $random100 = rand(0,100);
        if ($random100 < 10) {                             $longtime = time() - (1 * 3600 * 24);
            $DB->delete_records_select('mnet_session', "expires < ?", array($longtime));
        }
    }

    
    function prelogout_hook() {
        global $CFG, $USER;

        if (!is_enabled_auth('mnet')) {
            return;
        }

                if ($USER->mnethostid == $this->mnet->id) {
            $this->kill_children($USER->username, sha1($_SERVER['HTTP_USER_AGENT']));

                } else {
            $this->kill_parent($USER->username, sha1($_SERVER['HTTP_USER_AGENT']));

        }
    }

    
    function kill_parent($username, $useragent) {
        global $CFG, $USER, $DB;

        require_once $CFG->dirroot.'/mnet/xmlrpc/client.php';
        $sql = "
            select
                *
            from
                {mnet_session} s
            where
                s.username   = ? AND
                s.useragent  = ? AND
                s.mnethostid = ?";

        $mnetsessions = $DB->get_records_sql($sql, array($username, $useragent, $USER->mnethostid));

        $ignore = $DB->delete_records('mnet_session',
                                 array('username'=>$username,
                                 'useragent'=>$useragent,
                                 'mnethostid'=>$USER->mnethostid));

        if (false != $mnetsessions) {
            $mnet_peer = new mnet_peer();
            $mnet_peer->set_id($USER->mnethostid);

            $mnet_request = new mnet_xmlrpc_client();
            $mnet_request->set_method('auth/mnet/auth.php/kill_children');

                        $mnet_request->add_param($username);
            $mnet_request->add_param($useragent);
            if ($mnet_request->send($mnet_peer) === false) {
                debugging(join("\n", $mnet_request->error));
                return false;
            }
        }

        return true;
    }

    
    function kill_children($username, $useragent) {
        global $CFG, $USER, $DB;
        $remoteclient = null;
        if (defined('MNET_SERVER')) {
            $remoteclient = get_mnet_remote_client();
        }
        require_once $CFG->dirroot.'/mnet/xmlrpc/client.php';

        $userid = $DB->get_field('user', 'id', array('mnethostid'=>$CFG->mnet_localhost_id, 'username'=>$username));

        $returnstring = '';

        $mnetsessions = $DB->get_records('mnet_session', array('userid' => $userid, 'useragent' => $useragent));

        if (false == $mnetsessions) {
            $returnstring .= "Could find no remote sessions\n";
            $mnetsessions = array();
        }

        foreach($mnetsessions as $mnetsession) {
                                                if (isset($remoteclient->id) && ($mnetsession->mnethostid == $remoteclient->id)) {
                continue;
            }
            $returnstring .=  "Deleting session\n";

            $mnet_peer = new mnet_peer();
            $mnet_peer->set_id($mnetsession->mnethostid);

            $mnet_request = new mnet_xmlrpc_client();
            $mnet_request->set_method('auth/mnet/auth.php/kill_child');

                        $mnet_request->add_param($username);
            $mnet_request->add_param($useragent);
            if ($mnet_request->send($mnet_peer) === false) {
                debugging("Server side error has occured on host $mnetsession->mnethostid: " .
                          join("\n", $mnet_request->error));
            }
        }

        $ignore = $DB->delete_records('mnet_session',
                                 array('useragent'=>$useragent, 'userid'=>$userid));

        if (isset($remoteclient) && isset($remoteclient->id)) {
            \core\session\manager::kill_user_sessions($userid);
        }
        return $returnstring;
    }

    
    function kill_child($username, $useragent) {
        global $CFG, $DB;
        $remoteclient = get_mnet_remote_client();
        $session = $DB->get_record('mnet_session', array('username'=>$username, 'mnethostid'=>$remoteclient->id, 'useragent'=>$useragent));
        $DB->delete_records('mnet_session', array('username'=>$username, 'mnethostid'=>$remoteclient->id, 'useragent'=>$useragent));
        if (false != $session) {
            \core\session\manager::kill_session($session->session_id);
            return true;
        }
        return false;
    }

    
    function end_local_sessions(&$sessionArray) {
        global $CFG;
        if (is_array($sessionArray)) {
            while($session = array_pop($sessionArray)) {
                \core\session\manager::kill_session($session->session_id);
            }
            return true;
        }
        return false;
    }

    
    function fetch_user_image($username) {
        global $CFG, $DB;

        if ($user = $DB->get_record('user', array('username' => $username, 'mnethostid' => $CFG->mnet_localhost_id))) {
            $fs = get_file_storage();
            $usercontext = context_user::instance($user->id, MUST_EXIST);
            $return = array();
            if ($f1 = $fs->get_file($usercontext->id, 'user', 'icon', 0, '/', 'f1.png')) {
                $return['f1'] = base64_encode($f1->get_content());
                $return['f1_mimetype'] = $f1->get_mimetype();
            } else if ($f1 = $fs->get_file($usercontext->id, 'user', 'icon', 0, '/', 'f1.jpg')) {
                $return['f1'] = base64_encode($f1->get_content());
                $return['f1_mimetype'] = $f1->get_mimetype();
            }
            if ($f2 = $fs->get_file($usercontext->id, 'user', 'icon', 0, '/', 'f2.png')) {
                $return['f2'] = base64_encode($f2->get_content());
                $return['f2_mimetype'] = $f2->get_mimetype();
            } else if ($f2 = $fs->get_file($usercontext->id, 'user', 'icon', 0, '/', 'f2.jpg')) {
                $return['f2'] = base64_encode($f2->get_content());
                $return['f2_mimetype'] = $f2->get_mimetype();
            }
            return $return;
        }
        return false;
    }

    
    function fetch_theme_info() {
        global $CFG;

        $themename = "$CFG->theme";
        $logourl   = "$CFG->wwwroot/theme/$CFG->theme/images/logo.jpg";

        $return['themename'] = $themename;
        $return['logourl'] = $logourl;
        return $return;
    }

    
    function has_service($mnethostid, $servicename) {
        global $CFG, $DB;

        $sql = "
            SELECT
                svc.id as serviceid,
                svc.name,
                svc.description,
                svc.offer,
                svc.apiversion,
                h2s.id as h2s_id
            FROM
                {mnet_host} h,
                {mnet_service} svc,
                {mnet_host2service} h2s
            WHERE
                h.deleted = '0' AND
                h.id = h2s.hostid AND
                h2s.hostid = ? AND
                h2s.serviceid = svc.id AND
                svc.name = ? AND
                h2s.subscribe = '1'";

        return $DB->get_records_sql($sql, array($mnethostid, $servicename));
    }

    
    function can_login_remotely($username, $mnethostid) {
        global $DB;

        $accessctrl = 'allow';
        $aclrecord = $DB->get_record('mnet_sso_access_control', array('username'=>$username, 'mnet_host_id'=>$mnethostid));
        if (!empty($aclrecord)) {
            $accessctrl = $aclrecord->accessctrl;
        }
        return $accessctrl == 'allow';
    }

    function logoutpage_hook() {
        global $USER, $CFG, $redirect, $DB;

        if (!empty($USER->mnethostid) and $USER->mnethostid != $CFG->mnet_localhost_id) {
            $host = $DB->get_record('mnet_host', array('id'=>$USER->mnethostid));
            $redirect = $host->wwwroot.'/';
        }
    }

    
    function trim_logline ($logline) {
        $limits = array('ip' => 15, 'coursename' => 40, 'module' => 20, 'action' => 40,
                        'url' => 255);
        foreach ($limits as $property => $limit) {
            if (isset($logline->$property)) {
                $logline->$property = substr($logline->$property, 0, $limit);
            }
        }

        return $logline;
    }

    
    function loginpage_idp_list($wantsurl) {
        global $DB, $CFG;

                $wantsurl = preg_replace('/(' . preg_quote($CFG->wwwroot, '/') . '|' . preg_quote($CFG->httpswwwroot, '/') . ')/', '', $wantsurl);

        $sql = "SELECT DISTINCT h.id, h.wwwroot, h.name, a.sso_jump_url, a.name as application
                  FROM {mnet_host} h
                  JOIN {mnet_host2service} m ON h.id = m.hostid
                  JOIN {mnet_service} s ON s.id = m.serviceid
                  JOIN {mnet_application} a ON h.applicationid = a.id
                 WHERE s.name = ? AND h.deleted = ? AND m.publish = ?";
        $params = array('sso_sp', 0, 1);

        if (!empty($CFG->mnet_all_hosts_id)) {
            $sql .= " AND h.id <> ?";
            $params[] = $CFG->mnet_all_hosts_id;
        }

        if (!$hosts = $DB->get_records_sql($sql, $params)) {
            return array();
        }

        $idps = array();
        foreach ($hosts as $host) {
            $idps[] = array(
                'url'  => new moodle_url($host->wwwroot . $host->sso_jump_url, array('hostwwwroot' => $CFG->wwwroot, 'wantsurl' => $wantsurl, 'remoteurl' => 1)),
                'icon' => new pix_icon('i/' . $host->application . '_host', $host->name),
                'name' => $host->name,
            );
        }
        return $idps;
    }
}
