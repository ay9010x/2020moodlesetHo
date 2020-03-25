<?php



defined('MOODLE_INTERNAL') || die();

require_once($CFG->libdir.'/authlib.php');


class auth_plugin_db extends auth_plugin_base {

    
    function __construct() {
        global $CFG;
        require_once($CFG->libdir.'/adodb/adodb.inc.php');

        $this->authtype = 'db';
        $this->config = get_config('auth/db');
        if (empty($this->config->extencoding)) {
            $this->config->extencoding = 'utf-8';
        }
    }

    
    function user_login($username, $password) {
        global $CFG, $DB;

        if ($this->is_configured() === false) {
            debugging(get_string('auth_notconfigured', 'auth', $this->authtype));
            return false;
        }

        $extusername = core_text::convert($username, 'utf-8', $this->config->extencoding);
        $extpassword = core_text::convert($password, 'utf-8', $this->config->extencoding);

        if ($this->is_internal()) {
                                    
            if (isset($this->config->removeuser) and $this->config->removeuser == AUTH_REMOVEUSER_KEEP) {
                                if ($user = $DB->get_record('user', array('username'=>$username, 'mnethostid'=>$CFG->mnet_localhost_id, 'auth'=>$this->authtype))) {
                    return validate_internal_user_password($user, $password);
                } else {
                    return false;
                }
            }

            $authdb = $this->db_init();

            $rs = $authdb->Execute("SELECT *
                                      FROM {$this->config->table}
                                     WHERE {$this->config->fielduser} = '".$this->ext_addslashes($extusername)."'");
            if (!$rs) {
                $authdb->Close();
                debugging(get_string('auth_dbcantconnect','auth_db'));
                return false;
            }

            if (!$rs->EOF) {
                $rs->Close();
                $authdb->Close();
                                if ($user = $DB->get_record('user', array('username'=>$username, 'mnethostid'=>$CFG->mnet_localhost_id, 'auth'=>$this->authtype))) {
                    return validate_internal_user_password($user, $password);
                }
            } else {
                $rs->Close();
                $authdb->Close();
                                return false;
            }

        } else {
            
            $authdb = $this->db_init();

            $rs = $authdb->Execute("SELECT {$this->config->fieldpass}
                                      FROM {$this->config->table}
                                     WHERE {$this->config->fielduser} = '".$this->ext_addslashes($extusername)."'");
            if (!$rs) {
                $authdb->Close();
                debugging(get_string('auth_dbcantconnect','auth_db'));
                return false;
            }

            if ($rs->EOF) {
                $authdb->Close();
                return false;
            }

            $fields = array_change_key_case($rs->fields, CASE_LOWER);
            $fromdb = $fields[strtolower($this->config->fieldpass)];
            $rs->Close();
            $authdb->Close();

            if ($this->config->passtype === 'plaintext') {
                return ($fromdb == $extpassword);
            } else if ($this->config->passtype === 'md5') {
                return (strtolower($fromdb) == md5($extpassword));
            } else if ($this->config->passtype === 'sha1') {
                return (strtolower($fromdb) == sha1($extpassword));
            } else if ($this->config->passtype === 'saltedcrypt') {
                require_once($CFG->libdir.'/password_compat/lib/password.php');
                return password_verify($extpassword, $fromdb);
            } else {
                return false;
            }

        }
    }

    
    function db_init() {
        if ($this->is_configured() === false) {
            throw new moodle_exception('auth_dbcantconnect', 'auth_db');
        }

                $authdb = ADONewConnection($this->config->type);
        if (!empty($this->config->debugauthdb)) {
            $authdb->debug = true;
            ob_start();         }
        $authdb->Connect($this->config->host, $this->config->user, $this->config->pass, $this->config->name, true);
        $authdb->SetFetchMode(ADODB_FETCH_ASSOC);
        if (!empty($this->config->setupsql)) {
            $authdb->Execute($this->config->setupsql);
        }

        return $authdb;
    }

    
    function db_attributes() {
        $moodleattributes = array();
                $customfields = $this->get_custom_user_profile_fields();
        if (!empty($customfields) && !empty($this->userfields)) {
            $userfields = array_merge($this->userfields, $customfields);
        } else {
            $userfields = $this->userfields;
        }

        foreach ($userfields as $field) {
            if (!empty($this->config->{"field_map_$field"})) {
                $moodleattributes[$field] = $this->config->{"field_map_$field"};
            }
        }
        $moodleattributes['username'] = $this->config->fielduser;
        return $moodleattributes;
    }

    
    function get_userinfo($username) {
        global $CFG;

        $extusername = core_text::convert($username, 'utf-8', $this->config->extencoding);

        $authdb = $this->db_init();

                $selectfields = $this->db_attributes();

        $result = array();
                if ($selectfields) {
            $select = array();
            foreach ($selectfields as $localname=>$externalname) {
                $select[] = "$externalname";
            }
            $select = implode(', ', $select);
            $sql = "SELECT $select
                      FROM {$this->config->table}
                     WHERE {$this->config->fielduser} = '".$this->ext_addslashes($extusername)."'";

            if ($rs = $authdb->Execute($sql)) {
                if (!$rs->EOF) {
                    $fields = $rs->FetchRow();
                                        $fields = array_values($fields);
                    foreach (array_keys($selectfields) as $index => $localname) {
                        $value = $fields[$index];
                        $result[$localname] = core_text::convert($value, $this->config->extencoding, 'utf-8');
                     }
                 }
                 $rs->Close();
            }
        }
        $authdb->Close();
        return $result;
    }

    
    function user_update_password($user, $newpassword) {
        global $DB;

        if ($this->is_internal()) {
            $puser = $DB->get_record('user', array('id'=>$user->id), '*', MUST_EXIST);
                                                if (update_internal_user_password($puser, $newpassword)) {
                $user->password = $puser->password;
                return true;
            } else {
                return false;
            }
        } else {
                        return false;
        }
    }

    
    function sync_users(progress_trace $trace, $do_updates=false) {
        global $CFG, $DB;

        require_once($CFG->dirroot . '/user/lib.php');

                $userlist = $this->get_userlist();

                if (!empty($this->config->removeuser)) {

            $suspendselect = "";
            if ($this->config->removeuser == AUTH_REMOVEUSER_SUSPEND) {
                $suspendselect = "AND u.suspended = 0";
            }

                        if (count($userlist)) {
                $removeusers = array();
                $params['authtype'] = $this->authtype;
                $sql = "SELECT u.id, u.username
                          FROM {user} u
                         WHERE u.auth=:authtype
                           AND u.deleted=0
                           AND u.mnethostid=:mnethostid
                           $suspendselect";
                $params['mnethostid'] = $CFG->mnet_localhost_id;
                $internalusersrs = $DB->get_recordset_sql($sql, $params);

                $usernamelist = array_flip($userlist);
                foreach ($internalusersrs as $internaluser) {
                    if (!array_key_exists($internaluser->username, $usernamelist)) {
                        $removeusers[] = $internaluser;
                    }
                }
                $internalusersrs->close();
            } else {
                $sql = "SELECT u.id, u.username
                          FROM {user} u
                         WHERE u.auth=:authtype AND u.deleted=0 AND u.mnethostid=:mnethostid $suspendselect";
                $params = array();
                $params['authtype'] = $this->authtype;
                $params['mnethostid'] = $CFG->mnet_localhost_id;
                $removeusers = $DB->get_records_sql($sql, $params);
            }

            if (!empty($removeusers)) {
                $trace->output(get_string('auth_dbuserstoremove', 'auth_db', count($removeusers)));

                foreach ($removeusers as $user) {
                    if ($this->config->removeuser == AUTH_REMOVEUSER_FULLDELETE) {
                        delete_user($user);
                        $trace->output(get_string('auth_dbdeleteuser', 'auth_db', array('name'=>$user->username, 'id'=>$user->id)), 1);
                    } else if ($this->config->removeuser == AUTH_REMOVEUSER_SUSPEND) {
                        $updateuser = new stdClass();
                        $updateuser->id   = $user->id;
                        $updateuser->suspended = 1;
                        user_update_user($updateuser, false);
                        $trace->output(get_string('auth_dbsuspenduser', 'auth_db', array('name'=>$user->username, 'id'=>$user->id)), 1);
                    }
                }
            }
            unset($removeusers);
        }

        if (!count($userlist)) {
                        $trace->finished();
            return 0;
        }

                if ($do_updates) {
                        $all_keys = array_keys(get_object_vars($this->config));
            $updatekeys = array();
            foreach ($all_keys as $key) {
                if (preg_match('/^field_updatelocal_(.+)$/',$key, $match)) {
                    if ($this->config->{$key} === 'onlogin') {
                        array_push($updatekeys, $match[1]);                     }
                }
            }
            unset($all_keys); unset($key);

                        if (!empty($updatekeys)) {
                $update_users = array();
                                $userlistchunks = array_chunk($userlist , 10000);
                foreach($userlistchunks as $userlistchunk) {
                    list($in_sql, $params) = $DB->get_in_or_equal($userlistchunk, SQL_PARAMS_NAMED, 'u', true);
                    $params['authtype'] = $this->authtype;
                    $params['mnethostid'] = $CFG->mnet_localhost_id;
                    $sql = "SELECT u.id, u.username
                          FROM {user} u
                         WHERE u.auth = :authtype AND u.deleted = 0 AND u.mnethostid = :mnethostid AND u.username {$in_sql}";
                    $update_users = $update_users + $DB->get_records_sql($sql, $params);
                }

                if ($update_users) {
                    $trace->output("User entries to update: ".count($update_users));

                    foreach ($update_users as $user) {
                        if ($this->update_user_record($user->username, $updatekeys)) {
                            $trace->output(get_string('auth_dbupdatinguser', 'auth_db', array('name'=>$user->username, 'id'=>$user->id)), 1);
                        } else {
                            $trace->output(get_string('auth_dbupdatinguser', 'auth_db', array('name'=>$user->username, 'id'=>$user->id))." - ".get_string('skipped'), 1);
                        }
                    }
                    unset($update_users);
                }
            }
        }


                        $suspendselect = "";
        if ($this->config->removeuser == AUTH_REMOVEUSER_SUSPEND) {
            $suspendselect = "AND u.suspended = 0";
        }
        $sql = "SELECT u.id, u.username
                  FROM {user} u
                 WHERE u.auth=:authtype AND u.deleted='0' AND mnethostid=:mnethostid $suspendselect";

        $users = $DB->get_records_sql($sql, array('authtype'=>$this->authtype, 'mnethostid'=>$CFG->mnet_localhost_id));

                $usernames = array();
        if (!empty($users)) {
            foreach ($users as $user) {
                array_push($usernames, $user->username);
            }
            unset($users);
        }

        $add_users = array_diff($userlist, $usernames);
        unset($usernames);

        if (!empty($add_users)) {
            $trace->output(get_string('auth_dbuserstoadd','auth_db',count($add_users)));
                        foreach($add_users as $user) {
                $username = $user;
                if ($this->config->removeuser == AUTH_REMOVEUSER_SUSPEND) {
                    if ($olduser = $DB->get_record('user', array('username' => $username, 'deleted' => 0, 'suspended' => 1,
                            'mnethostid' => $CFG->mnet_localhost_id, 'auth' => $this->authtype))) {
                        $updateuser = new stdClass();
                        $updateuser->id = $olduser->id;
                        $updateuser->suspended = 0;
                        user_update_user($updateuser);
                        $trace->output(get_string('auth_dbreviveduser', 'auth_db', array('name' => $username,
                            'id' => $olduser->id)), 1);
                        continue;
                    }
                }

                
                                $user = $this->get_userinfo_asobj($user);
                $user->username   = $username;
                $user->confirmed  = 1;
                $user->auth       = $this->authtype;
                $user->mnethostid = $CFG->mnet_localhost_id;
                if (empty($user->lang)) {
                    $user->lang = $CFG->lang;
                }
                if ($collision = $DB->get_record_select('user', "username = :username AND mnethostid = :mnethostid AND auth <> :auth", array('username'=>$user->username, 'mnethostid'=>$CFG->mnet_localhost_id, 'auth'=>$this->authtype), 'id,username,auth')) {
                    $trace->output(get_string('auth_dbinsertuserduplicate', 'auth_db', array('username'=>$user->username, 'auth'=>$collision->auth)), 1);
                    continue;
                }
                try {
                    $id = user_create_user($user, false);                     $trace->output(get_string('auth_dbinsertuser', 'auth_db', array('name'=>$user->username, 'id'=>$id)), 1);
                } catch (moodle_exception $e) {
                    $trace->output(get_string('auth_dbinsertusererror', 'auth_db', $user->username), 1);
                    continue;
                }
                                if ($this->is_internal()) {
                    set_user_preference('auth_forcepasswordchange', 1, $id);
                    set_user_preference('create_password',          1, $id);
                }
                                context_user::instance($id);
            }
            unset($add_users);
        }
        $trace->finished();
        return 0;
    }

    function user_exists($username) {

                $result = false;

        $extusername = core_text::convert($username, 'utf-8', $this->config->extencoding);

        $authdb = $this->db_init();

        $rs = $authdb->Execute("SELECT *
                                  FROM {$this->config->table}
                                 WHERE {$this->config->fielduser} = '".$this->ext_addslashes($extusername)."' ");

        if (!$rs) {
            print_error('auth_dbcantconnect','auth_db');
        } else if (!$rs->EOF) {
                        $result = true;
        }

        $authdb->Close();
        return $result;
    }


    function get_userlist() {

                $result = array();

        $authdb = $this->db_init();

                $rs = $authdb->Execute("SELECT {$this->config->fielduser}
                                  FROM {$this->config->table} ");

        if (!$rs) {
            print_error('auth_dbcantconnect','auth_db');
        } else if (!$rs->EOF) {
            while ($rec = $rs->FetchRow()) {
                $rec = array_change_key_case((array)$rec, CASE_LOWER);
                array_push($result, $rec[strtolower($this->config->fielduser)]);
            }
        }

        $authdb->Close();
        return $result;
    }

    
    function get_userinfo_asobj($username) {
        $user_array = truncate_userinfo($this->get_userinfo($username));
        $user = new stdClass();
        foreach($user_array as $key=>$value) {
            $user->{$key} = $value;
        }
        return $user;
    }

    
    function update_user_record($username, $updatekeys=false) {
        global $CFG, $DB;

                $username = trim(core_text::strtolower($username));

                $user = $DB->get_record('user', array('username'=>$username, 'mnethostid'=>$CFG->mnet_localhost_id));
        if (empty($user)) {             error_log("Cannot update non-existent user: $username");
            print_error('auth_dbusernotexist','auth_db',$username);
            die;
        }

                $userid = $user->id;
        $needsupdate = false;

        $updateuser = new stdClass();
        $updateuser->id = $userid;
        if ($newinfo = $this->get_userinfo($username)) {
            $newinfo = truncate_userinfo($newinfo);

            if (empty($updatekeys)) {                 $updatekeys = array_keys($newinfo);
            }

            foreach ($updatekeys as $key) {
                if (isset($newinfo[$key])) {
                    $value = $newinfo[$key];
                } else {
                    $value = '';
                }

                if (!empty($this->config->{'field_updatelocal_' . $key})) {
                    if (isset($user->{$key}) and $user->{$key} != $value) {                         $needsupdate = true;
                        $updateuser->$key = $value;
                    }
                }
            }
        }
        if ($needsupdate) {
            require_once($CFG->dirroot . '/user/lib.php');
            user_update_user($updateuser);
        }
        return $DB->get_record('user', array('id'=>$userid, 'deleted'=>0));
    }

    
    function user_update($olduser, $newuser) {
        if (isset($olduser->username) and isset($newuser->username) and $olduser->username != $newuser->username) {
            error_log("ERROR:User renaming not allowed in ext db");
            return false;
        }

        if (isset($olduser->auth) and $olduser->auth != $this->authtype) {
            return true;         }

        $curruser = $this->get_userinfo($olduser->username);
        if (empty($curruser)) {
            error_log("ERROR:User $olduser->username found in ext db");
            return false;
        }

        $extusername = core_text::convert($olduser->username, 'utf-8', $this->config->extencoding);

        $authdb = $this->db_init();

        $update = array();
        foreach($curruser as $key=>$value) {
            if ($key == 'username') {
                continue;             }
            if (empty($this->config->{"field_updateremote_$key"})) {
                continue;             }
            if (!isset($newuser->$key)) {
                continue;
            }
            $nuvalue = $newuser->$key;
                        if (isset($nuvalue['text'])) {
                $nuvalue = $nuvalue['text'];
            }
            if ($nuvalue != $value) {
                $update[] = $this->config->{"field_map_$key"}."='".$this->ext_addslashes(core_text::convert($nuvalue, 'utf-8', $this->config->extencoding))."'";
            }
        }
        if (!empty($update)) {
            $authdb->Execute("UPDATE {$this->config->table}
                                 SET ".implode(',', $update)."
                               WHERE {$this->config->fielduser}='".$this->ext_addslashes($extusername)."'");
        }
        $authdb->Close();
        return true;
    }

    
     function validate_form($form, &$err) {
        if ($form->passtype === 'internal') {
            $this->config->changepasswordurl = '';
            set_config('changepasswordurl', '', 'auth/db');
        }
    }

    function prevent_local_passwords() {
        return !$this->is_internal();
    }

    
    function is_internal() {
        if (!isset($this->config->passtype)) {
            return true;
        }
        return ($this->config->passtype === 'internal');
    }

    
    public function is_configured() {
        if (!empty($this->config->type)) {
            return true;
        }
        return false;
    }

    
    function is_synchronised_with_external() {
        return true;
    }

    
    function can_change_password() {
        return ($this->is_internal() or !empty($this->config->changepasswordurl));
    }

    
    function change_password_url() {
        if ($this->is_internal() || empty($this->config->changepasswordurl)) {
                        return null;
        } else {
                        return new moodle_url($this->config->changepasswordurl);
        }
    }

    
    function can_reset_password() {
        return $this->is_internal();
    }

    
    function config_form($config, $err, $user_fields) {
        include 'config.html';
    }

    
    function process_config($config) {
                if (!isset($config->host)) {
            $config->host = 'localhost';
        }
        if (!isset($config->type)) {
            $config->type = 'mysql';
        }
        if (!isset($config->sybasequoting)) {
            $config->sybasequoting = 0;
        }
        if (!isset($config->name)) {
            $config->name = '';
        }
        if (!isset($config->user)) {
            $config->user = '';
        }
        if (!isset($config->pass)) {
            $config->pass = '';
        }
        if (!isset($config->table)) {
            $config->table = '';
        }
        if (!isset($config->fielduser)) {
            $config->fielduser = '';
        }
        if (!isset($config->fieldpass)) {
            $config->fieldpass = '';
        }
        if (!isset($config->passtype)) {
            $config->passtype = 'plaintext';
        }
        if (!isset($config->extencoding)) {
            $config->extencoding = 'utf-8';
        }
        if (!isset($config->setupsql)) {
            $config->setupsql = '';
        }
        if (!isset($config->debugauthdb)) {
            $config->debugauthdb = 0;
        }
        if (!isset($config->removeuser)) {
            $config->removeuser = AUTH_REMOVEUSER_KEEP;
        }
        if (!isset($config->changepasswordurl)) {
            $config->changepasswordurl = '';
        }

                set_config('host',          $config->host,          'auth/db');
        set_config('type',          $config->type,          'auth/db');
        set_config('sybasequoting', $config->sybasequoting, 'auth/db');
        set_config('name',          $config->name,          'auth/db');
        set_config('user',          $config->user,          'auth/db');
        set_config('pass',          $config->pass,          'auth/db');
        set_config('table',         $config->table,         'auth/db');
        set_config('fielduser',     $config->fielduser,     'auth/db');
        set_config('fieldpass',     $config->fieldpass,     'auth/db');
        set_config('passtype',      $config->passtype,      'auth/db');
        set_config('extencoding',   trim($config->extencoding), 'auth/db');
        set_config('setupsql',      trim($config->setupsql),'auth/db');
        set_config('debugauthdb',   $config->debugauthdb,   'auth/db');
        set_config('removeuser',    $config->removeuser,    'auth/db');
        set_config('changepasswordurl', trim($config->changepasswordurl), 'auth/db');

        return true;
    }

    
    function ext_addslashes($text) {
        if (empty($this->config->sybasequoting)) {
            $text = str_replace('\\', '\\\\', $text);
            $text = str_replace(array('\'', '"', "\0"), array('\\\'', '\\"', '\\0'), $text);
        } else {
            $text = str_replace("'", "''", $text);
        }
        return $text;
    }

    
    public function test_settings() {
        global $CFG, $OUTPUT;

        
        raise_memory_limit(MEMORY_HUGE);

        if (empty($this->config->table)) {
            echo $OUTPUT->notification('External table not specified.', 'notifyproblem');
            return;
        }

        if (empty($this->config->fielduser)) {
            echo $OUTPUT->notification('External user field not specified.', 'notifyproblem');
            return;
        }

        $olddebug = $CFG->debug;
        $olddisplay = ini_get('display_errors');
        ini_set('display_errors', '1');
        $CFG->debug = DEBUG_DEVELOPER;
        $olddebugauthdb = $this->config->debugauthdb;
        $this->config->debugauthdb = 1;
        error_reporting($CFG->debug);

        $adodb = $this->db_init();

        if (!$adodb or !$adodb->IsConnected()) {
            $this->config->debugauthdb = $olddebugauthdb;
            $CFG->debug = $olddebug;
            ini_set('display_errors', $olddisplay);
            error_reporting($CFG->debug);
            ob_end_flush();

            echo $OUTPUT->notification('Cannot connect the database.', 'notifyproblem');
            return;
        }

        $rs = $adodb->Execute("SELECT *
                                 FROM {$this->config->table}
                                WHERE {$this->config->fielduser} <> 'random_unlikely_username'"); 
        if (!$rs) {
            echo $OUTPUT->notification('Can not read external table.', 'notifyproblem');

        } else if ($rs->EOF) {
            echo $OUTPUT->notification('External table is empty.', 'notifyproblem');
            $rs->close();

        } else {
            $fields_obj = $rs->FetchObj();
            $columns = array_keys((array)$fields_obj);

            echo $OUTPUT->notification('External table contains following columns:<br />'.implode(', ', $columns), 'notifysuccess');
            $rs->close();
        }

        $adodb->Close();

        $this->config->debugauthdb = $olddebugauthdb;
        $CFG->debug = $olddebug;
        ini_set('display_errors', $olddisplay);
        error_reporting($CFG->debug);
        ob_end_flush();
    }

    
    public function clean_data($user) {
        debugging('The method clean_data() has been deprecated, please use core_user::clean_data() instead.',
            DEBUG_DEVELOPER);
        return core_user::clean_data($user);
    }
}
