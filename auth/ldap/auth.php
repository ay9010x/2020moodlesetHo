<?php



defined('MOODLE_INTERNAL') || die();

if (!defined('AUTH_AD_ACCOUNTDISABLE')) {
    define('AUTH_AD_ACCOUNTDISABLE', 0x0002);
}
if (!defined('AUTH_AD_NORMAL_ACCOUNT')) {
    define('AUTH_AD_NORMAL_ACCOUNT', 0x0200);
}
if (!defined('AUTH_NTLMTIMEOUT')) {      define('AUTH_NTLMTIMEOUT', 10);
}

if (!defined('UF_DONT_EXPIRE_PASSWD')) {
    define ('UF_DONT_EXPIRE_PASSWD', 0x00010000);
}

if (!defined('AUTH_UID_NOBODY')) {
    define('AUTH_UID_NOBODY', -2);
}
if (!defined('AUTH_GID_NOGROUP')) {
    define('AUTH_GID_NOGROUP', -2);
}

if (!defined('AUTH_NTLM_VALID_USERNAME')) {
    define('AUTH_NTLM_VALID_USERNAME', '[^/\\\\\\\\\[\]:;|=,+*?<>@"]+');
}
if (!defined('AUTH_NTLM_VALID_DOMAINNAME')) {
    define('AUTH_NTLM_VALID_DOMAINNAME', '[^\\\\\\\\\/:*?"<>|]+');
}
if (!defined('AUTH_NTLM_DEFAULT_FORMAT')) {
    define('AUTH_NTLM_DEFAULT_FORMAT', '%domain%\\%username%');
}
if (!defined('AUTH_NTLM_FASTPATH_ATTEMPT')) {
    define('AUTH_NTLM_FASTPATH_ATTEMPT', 0);
}
if (!defined('AUTH_NTLM_FASTPATH_YESFORM')) {
    define('AUTH_NTLM_FASTPATH_YESFORM', 1);
}
if (!defined('AUTH_NTLM_FASTPATH_YESATTEMPT')) {
    define('AUTH_NTLM_FASTPATH_YESATTEMPT', 2);
}

if (!defined('LDAP_OPT_DIAGNOSTIC_MESSAGE')) {
    define('LDAP_OPT_DIAGNOSTIC_MESSAGE', 0x0032);
}

require_once($CFG->libdir.'/authlib.php');
require_once($CFG->libdir.'/ldaplib.php');
require_once($CFG->dirroot.'/user/lib.php');


class auth_plugin_ldap extends auth_plugin_base {

    
    function init_plugin($authtype) {
        $this->pluginconfig = 'auth/'.$authtype;
        $this->config = get_config($this->pluginconfig);
        if (empty($this->config->ldapencoding)) {
            $this->config->ldapencoding = 'utf-8';
        }
        if (empty($this->config->user_type)) {
            $this->config->user_type = 'default';
        }

        $ldap_usertypes = ldap_supported_usertypes();
        $this->config->user_type_name = $ldap_usertypes[$this->config->user_type];
        unset($ldap_usertypes);

        $default = ldap_getdefaults();

                foreach ($default as $key => $value) {
                        if (!isset($this->config->{$key}) or $this->config->{$key} == '') {
                $this->config->{$key} = $value[$this->config->user_type];
            }
        }

                $this->config->objectclass = ldap_normalise_objectclass($this->config->objectclass);
    }

    
    public function __construct() {
        $this->authtype = 'ldap';
        $this->roleauth = 'auth_ldap';
        $this->errorlogtag = '[AUTH LDAP] ';
        $this->init_plugin($this->authtype);
    }

    
    public function auth_plugin_ldap() {
        debugging('Use of class name as constructor is deprecated', DEBUG_DEVELOPER);
        self::__construct();
    }

    
    function user_login($username, $password) {
        if (! function_exists('ldap_bind')) {
            print_error('auth_ldapnotinstalled', 'auth_ldap');
            return false;
        }

        if (!$username or !$password) {                return false;
        }

        $extusername = core_text::convert($username, 'utf-8', $this->config->ldapencoding);
        $extpassword = core_text::convert($password, 'utf-8', $this->config->ldapencoding);

                                $key = sesskey();
        if (!empty($this->config->ntlmsso_enabled) && $key === $password) {
            $cf = get_cache_flags($this->pluginconfig.'/ntlmsess');
                                    if (!isset($cf[$key]) || $cf[$key] === '') {
                return false;
            }

            $sessusername = $cf[$key];
            if ($username === $sessusername) {
                unset($sessusername);
                unset($cf);

                                $validuser = false;
                $ldapconnection = $this->ldap_connect();
                                                if ($this->ldap_find_userdn($ldapconnection, $extusername)) {
                    $validuser = true;
                }
                $this->ldap_close();

                                return $validuser;
            }
        }         unset($key);

        $ldapconnection = $this->ldap_connect();
        $ldap_user_dn = $this->ldap_find_userdn($ldapconnection, $extusername);

                if (!$ldap_user_dn) {
            $this->ldap_close();
            return false;
        }

                $ldap_login = @ldap_bind($ldapconnection, $ldap_user_dn, $extpassword);

                                        if (!$ldap_login && ($this->config->user_type == 'ad')
            && $this->can_change_password()
            && (!empty($this->config->expiration) and ($this->config->expiration == 1))) {

                                    ldap_get_option($ldapconnection, LDAP_OPT_DIAGNOSTIC_MESSAGE, $diagmsg);

            if ($this->ldap_ad_pwdexpired_from_diagmsg($diagmsg)) {
                                                                $ldap_login = true;
            }
        }
        $this->ldap_close();
        return $ldap_login;
    }

    
    function get_userinfo($username) {
        $extusername = core_text::convert($username, 'utf-8', $this->config->ldapencoding);

        $ldapconnection = $this->ldap_connect();
        if(!($user_dn = $this->ldap_find_userdn($ldapconnection, $extusername))) {
            $this->ldap_close();
            return false;
        }

        $search_attribs = array();
        $attrmap = $this->ldap_attributes();
        foreach ($attrmap as $key => $values) {
            if (!is_array($values)) {
                $values = array($values);
            }
            foreach ($values as $value) {
                if (!in_array($value, $search_attribs)) {
                    array_push($search_attribs, $value);
                }
            }
        }

        if (!$user_info_result = ldap_read($ldapconnection, $user_dn, '(objectClass=*)', $search_attribs)) {
            $this->ldap_close();
            return false;         }

        $user_entry = ldap_get_entries_moodle($ldapconnection, $user_info_result);
        if (empty($user_entry)) {
            $this->ldap_close();
            return false;         }

        $result = array();
        foreach ($attrmap as $key => $values) {
            if (!is_array($values)) {
                $values = array($values);
            }
            $ldapval = NULL;
            foreach ($values as $value) {
                $entry = array_change_key_case($user_entry[0], CASE_LOWER);
                if (($value == 'dn') || ($value == 'distinguishedname')) {
                    $result[$key] = $user_dn;
                    continue;
                }
                if (!array_key_exists($value, $entry)) {
                    continue;                 }
                if (is_array($entry[$value])) {
                    $newval = core_text::convert($entry[$value][0], $this->config->ldapencoding, 'utf-8');
                } else {
                    $newval = core_text::convert($entry[$value], $this->config->ldapencoding, 'utf-8');
                }
                if (!empty($newval)) {                     $ldapval = $newval;
                }
            }
            if (!is_null($ldapval)) {
                $result[$key] = $ldapval;
            }
        }

        $this->ldap_close();
        return $result;
    }

    
    function get_userinfo_asobj($username) {
        $user_array = $this->get_userinfo($username);
        if ($user_array == false) {
            return false;         }
        $user_array = truncate_userinfo($user_array);
        $user = new stdClass();
        foreach ($user_array as $key=>$value) {
            $user->{$key} = $value;
        }
        return $user;
    }

    
    function get_userlist() {
        return $this->ldap_get_userlist("({$this->config->user_attribute}=*)");
    }

    
    function user_exists($username) {
        $extusername = core_text::convert($username, 'utf-8', $this->config->ldapencoding);

                $users = $this->ldap_get_userlist('('.$this->config->user_attribute.'='.ldap_filter_addslashes($extusername).')');
        return count($users);
    }

    
    function user_create($userobject, $plainpass) {
        $extusername = core_text::convert($userobject->username, 'utf-8', $this->config->ldapencoding);
        $extpassword = core_text::convert($plainpass, 'utf-8', $this->config->ldapencoding);

        switch ($this->config->passtype) {
            case 'md5':
                $extpassword = '{MD5}' . base64_encode(pack('H*', md5($extpassword)));
                break;
            case 'sha1':
                $extpassword = '{SHA}' . base64_encode(pack('H*', sha1($extpassword)));
                break;
            case 'plaintext':
            default:
                break;         }

        $ldapconnection = $this->ldap_connect();
        $attrmap = $this->ldap_attributes();

        $newuser = array();

        foreach ($attrmap as $key => $values) {
            if (!is_array($values)) {
                $values = array($values);
            }
            foreach ($values as $value) {
                if (!empty($userobject->$key) ) {
                    $newuser[$value] = core_text::convert($userobject->$key, 'utf-8', $this->config->ldapencoding);
                }
            }
        }

                                
        switch ($this->config->user_type)  {
            case 'edir':
                $newuser['objectClass']   = array('inetOrgPerson', 'organizationalPerson', 'person', 'top');
                $newuser['uniqueId']      = $extusername;
                $newuser['logindisabled'] = 'TRUE';
                $newuser['userpassword']  = $extpassword;
                $uadd = ldap_add($ldapconnection, $this->config->user_attribute.'='.ldap_addslashes($extusername).','.$this->config->create_context, $newuser);
                break;
            case 'rfc2307':
            case 'rfc2307bis':
                                                                                                                                                                                                                
                $newuser['objectClass']   = array('posixAccount', 'inetOrgPerson', 'organizationalPerson', 'person', 'top');
                $newuser['cn']            = $extusername;
                $newuser['uid']           = $extusername;
                $newuser['uidNumber']     = AUTH_UID_NOBODY;
                $newuser['gidNumber']     = AUTH_GID_NOGROUP;
                $newuser['homeDirectory'] = '/';
                $newuser['loginShell']    = '/bin/false';

                                                                                                                                                                                                                                                
                $newuser['userPassword']  = '*'.$extpassword;
                $uadd = ldap_add($ldapconnection, $this->config->user_attribute.'='.ldap_addslashes($extusername).','.$this->config->create_context, $newuser);
                break;
            case 'ad':
                                                                
                                                                if (!function_exists('mb_convert_encoding')) {
                    print_error('auth_ldap_no_mbstring', 'auth_ldap');
                }

                                if (preg_match('#[/\\[\]:;|=,+*?<>@"]#', $extusername)) {
                    print_error ('auth_ldap_ad_invalidchars', 'auth_ldap');
                }

                                $newuser['objectClass'] = array('top', 'person', 'user', 'organizationalPerson');
                $newuser['sAMAccountName'] = $extusername;
                $newuser['userAccountControl'] = AUTH_AD_NORMAL_ACCOUNT |
                                                 AUTH_AD_ACCOUNTDISABLE;
                $userdn = 'cn='.ldap_addslashes($extusername).','.$this->config->create_context;
                if (!ldap_add($ldapconnection, $userdn, $newuser)) {
                    print_error('auth_ldap_ad_create_req', 'auth_ldap');
                }

                                unset($newuser);
                $newuser['unicodePwd'] = mb_convert_encoding('"' . $extpassword . '"',
                                                             'UCS-2LE', 'UTF-8');
                if(!ldap_modify($ldapconnection, $userdn, $newuser)) {
                                        ldap_delete ($ldapconnection, $userdn);
                    print_error('auth_ldap_ad_create_req', 'auth_ldap');
                }
                $uadd = true;
                break;
            default:
               print_error('auth_ldap_unsupportedusertype', 'auth_ldap', '', $this->config->user_type_name);
        }
        $this->ldap_close();
        return $uadd;
    }

    
    function can_reset_password() {
        return !empty($this->config->stdchangepassword);
    }

    
    function can_be_manually_set() {
        return true;
    }

    
    function can_signup() {
        return (!empty($this->config->auth_user_create) and !empty($this->config->create_context));
    }

    
    function user_signup($user, $notify=true) {
        global $CFG, $DB, $PAGE, $OUTPUT;

        require_once($CFG->dirroot.'/user/profile/lib.php');
        require_once($CFG->dirroot.'/user/lib.php');

        if ($this->user_exists($user->username)) {
            print_error('auth_ldap_user_exists', 'auth_ldap');
        }

        $plainslashedpassword = $user->password;
        unset($user->password);

        if (! $this->user_create($user, $plainslashedpassword)) {
            print_error('auth_ldap_create_error', 'auth_ldap');
        }

        $user->id = user_create_user($user, false, false);

        user_add_password_history($user->id, $plainslashedpassword);

                profile_save_data($user);

        $this->update_user_record($user->username);
                                update_internal_user_password($user, $plainslashedpassword);

        $user = $DB->get_record('user', array('id'=>$user->id));

        \core\event\user_created::create_from_userid($user->id)->trigger();

        if (! send_confirmation_email($user)) {
            print_error('noemail', 'auth_ldap');
        }

        if ($notify) {
            $emailconfirm = get_string('emailconfirm');
            $PAGE->set_url('/auth/ldap/auth.php');
            $PAGE->navbar->add($emailconfirm);
            $PAGE->set_title($emailconfirm);
            $PAGE->set_heading($emailconfirm);
            echo $OUTPUT->header();
            notice(get_string('emailconfirmsent', '', $user->email), "{$CFG->wwwroot}/index.php");
        } else {
            return true;
        }
    }

    
    function can_confirm() {
        return $this->can_signup();
    }

    
    function user_confirm($username, $confirmsecret) {
        global $DB;

        $user = get_complete_user_data('username', $username);

        if (!empty($user)) {
            if ($user->auth != $this->authtype) {
                return AUTH_CONFIRM_ERROR;

            } else if ($user->secret == $confirmsecret && $user->confirmed) {
                return AUTH_CONFIRM_ALREADY;

            } else if ($user->secret == $confirmsecret) {                   if (!$this->user_activate($username)) {
                    return AUTH_CONFIRM_FAIL;
                }
                $user->confirmed = 1;
                user_update_user($user, false);
                return AUTH_CONFIRM_OK;
            }
        } else {
            return AUTH_CONFIRM_ERROR;
        }
    }

    
    function password_expire($username) {
        $result = 0;

        $extusername = core_text::convert($username, 'utf-8', $this->config->ldapencoding);

        $ldapconnection = $this->ldap_connect();
        $user_dn = $this->ldap_find_userdn($ldapconnection, $extusername);
        $search_attribs = array($this->config->expireattr);
        $sr = ldap_read($ldapconnection, $user_dn, '(objectClass=*)', $search_attribs);
        if ($sr)  {
            $info = ldap_get_entries_moodle($ldapconnection, $sr);
            if (!empty ($info)) {
                $info = array_change_key_case($info[0], CASE_LOWER);
                if (isset($info[$this->config->expireattr][0])) {
                    $expiretime = $this->ldap_expirationtime2unix($info[$this->config->expireattr][0], $ldapconnection, $user_dn);
                    if ($expiretime != 0) {
                        $now = time();
                        if ($expiretime > $now) {
                            $result = ceil(($expiretime - $now) / DAYSECS);
                        } else {
                            $result = floor(($expiretime - $now) / DAYSECS);
                        }
                    }
                }
            }
        } else {
            error_log($this->errorlogtag.get_string('didtfindexpiretime', 'auth_ldap'));
        }

        return $result;
    }

    
    function sync_users($do_updates=true) {
        global $CFG, $DB;

        print_string('connectingldap', 'auth_ldap');
        $ldapconnection = $this->ldap_connect();

        $dbman = $DB->get_manager();

            $table = new xmldb_table('tmp_extuser');
        $table->add_field('id', XMLDB_TYPE_INTEGER, '10', XMLDB_UNSIGNED, XMLDB_NOTNULL, XMLDB_SEQUENCE, null);
        $table->add_field('username', XMLDB_TYPE_CHAR, '100', null, XMLDB_NOTNULL, null, null);
        $table->add_field('mnethostid', XMLDB_TYPE_INTEGER, '10', XMLDB_UNSIGNED, XMLDB_NOTNULL, null, null);
        $table->add_key('primary', XMLDB_KEY_PRIMARY, array('id'));
        $table->add_index('username', XMLDB_INDEX_UNIQUE, array('mnethostid', 'username'));

        print_string('creatingtemptable', 'auth_ldap', 'tmp_extuser');
        $dbman->create_temp_table($table);

                                        $filter = '(&('.$this->config->user_attribute.'=*)'.$this->config->objectclass.')';

        $contexts = explode(';', $this->config->contexts);

        if (!empty($this->config->create_context)) {
            array_push($contexts, $this->config->create_context);
        }

        $ldap_pagedresults = ldap_paged_results_supported($this->config->ldap_version);
        $ldap_cookie = '';
        foreach ($contexts as $context) {
            $context = trim($context);
            if (empty($context)) {
                continue;
            }

            do {
                if ($ldap_pagedresults) {
                    ldap_control_paged_result($ldapconnection, $this->config->pagesize, true, $ldap_cookie);
                }
                if ($this->config->search_sub) {
                                        $ldap_result = ldap_search($ldapconnection, $context, $filter, array($this->config->user_attribute));
                } else {
                                        $ldap_result = ldap_list($ldapconnection, $context, $filter, array($this->config->user_attribute));
                }
                if(!$ldap_result) {
                    continue;
                }
                if ($ldap_pagedresults) {
                    ldap_control_paged_result_response($ldapconnection, $ldap_result, $ldap_cookie);
                }
                if ($entry = @ldap_first_entry($ldapconnection, $ldap_result)) {
                    do {
                        $value = ldap_get_values_len($ldapconnection, $entry, $this->config->user_attribute);
                        $value = core_text::convert($value[0], $this->config->ldapencoding, 'utf-8');
                        $value = trim($value);
                        $this->ldap_bulk_insert($value);
                    } while ($entry = ldap_next_entry($ldapconnection, $entry));
                }
                unset($ldap_result);             } while ($ldap_pagedresults && $ldap_cookie !== null && $ldap_cookie != '');
        }

                        if ($ldap_pagedresults) {
            $this->ldap_close(true);
            $ldapconnection = $this->ldap_connect();
        }

                                $count = $DB->count_records_sql('SELECT COUNT(username) AS count, 1 FROM {tmp_extuser}');
        if ($count < 1) {
            print_string('didntgetusersfromldap', 'auth_ldap');
            exit;
        } else {
            print_string('gotcountrecordsfromldap', 'auth_ldap', $count);
        }


                
        if ($this->config->removeuser == AUTH_REMOVEUSER_FULLDELETE) {
            $sql = "SELECT u.*
                      FROM {user} u
                 LEFT JOIN {tmp_extuser} e ON (u.username = e.username AND u.mnethostid = e.mnethostid)
                     WHERE u.auth = :auth
                           AND u.deleted = 0
                           AND e.username IS NULL";
            $remove_users = $DB->get_records_sql($sql, array('auth'=>$this->authtype));

            if (!empty($remove_users)) {
                print_string('userentriestoremove', 'auth_ldap', count($remove_users));
                foreach ($remove_users as $user) {
                    if (delete_user($user)) {
                        echo "\t"; print_string('auth_dbdeleteuser', 'auth_db', array('name'=>$user->username, 'id'=>$user->id)); echo "\n";
                    } else {
                        echo "\t"; print_string('auth_dbdeleteusererror', 'auth_db', $user->username); echo "\n";
                    }
                }
            } else {
                print_string('nouserentriestoremove', 'auth_ldap');
            }
            unset($remove_users); 
        } else if ($this->config->removeuser == AUTH_REMOVEUSER_SUSPEND) {
            $sql = "SELECT u.*
                      FROM {user} u
                 LEFT JOIN {tmp_extuser} e ON (u.username = e.username AND u.mnethostid = e.mnethostid)
                     WHERE u.auth = :auth
                           AND u.deleted = 0
                           AND u.suspended = 0
                           AND e.username IS NULL";
            $remove_users = $DB->get_records_sql($sql, array('auth'=>$this->authtype));

            if (!empty($remove_users)) {
                print_string('userentriestoremove', 'auth_ldap', count($remove_users));

                foreach ($remove_users as $user) {
                    $updateuser = new stdClass();
                    $updateuser->id = $user->id;
                    $updateuser->suspended = 1;
                    user_update_user($updateuser, false);
                    echo "\t"; print_string('auth_dbsuspenduser', 'auth_db', array('name'=>$user->username, 'id'=>$user->id)); echo "\n";
                    \core\session\manager::kill_user_sessions($user->id);
                }
            } else {
                print_string('nouserentriestoremove', 'auth_ldap');
            }
            unset($remove_users);         }

        if (!empty($this->config->removeuser) and $this->config->removeuser == AUTH_REMOVEUSER_SUSPEND) {
            $sql = "SELECT u.id, u.username
                      FROM {user} u
                      JOIN {tmp_extuser} e ON (u.username = e.username AND u.mnethostid = e.mnethostid)
                     WHERE (u.auth = 'nologin' OR (u.auth = ? AND u.suspended = 1)) AND u.deleted = 0";
                        $revive_users = $DB->get_records_sql($sql, array($this->authtype));

            if (!empty($revive_users)) {
                print_string('userentriestorevive', 'auth_ldap', count($revive_users));

                foreach ($revive_users as $user) {
                    $updateuser = new stdClass();
                    $updateuser->id = $user->id;
                    $updateuser->auth = $this->authtype;
                    $updateuser->suspended = 0;
                    user_update_user($updateuser, false);
                    echo "\t"; print_string('auth_dbreviveduser', 'auth_db', array('name'=>$user->username, 'id'=>$user->id)); echo "\n";
                }
            } else {
                print_string('nouserentriestorevive', 'auth_ldap');
            }

            unset($revive_users);
        }


        if ($do_updates) {
                        $all_keys = array_keys(get_object_vars($this->config));
            $updatekeys = array();
            foreach ($all_keys as $key) {
                if (preg_match('/^field_updatelocal_(.+)$/', $key, $match)) {
                                                                                if (!empty($this->config->{'field_map_'.$match[1]})
                         and $this->config->{$match[0]} === 'onlogin') {
                        array_push($updatekeys, $match[1]);                     }
                }
            }
            if ($this->config->suspended_attribute && $this->config->sync_suspended) {
                $updatekeys[] = 'suspended';
            }
            unset($all_keys); unset($key);

        } else {
            print_string('noupdatestobedone', 'auth_ldap');
        }
        if ($do_updates and !empty($updatekeys)) {             $users = $DB->get_records_sql('SELECT u.username, u.id
                                             FROM {user} u
                                            WHERE u.deleted = 0 AND u.auth = ? AND u.mnethostid = ?',
                                          array($this->authtype, $CFG->mnet_localhost_id));
            if (!empty($users)) {
                print_string('userentriestoupdate', 'auth_ldap', count($users));

                $sitecontext = context_system::instance();
                if (!empty($this->config->creators) and !empty($this->config->memberattribute)
                  and $roles = get_archetype_roles('coursecreator')) {
                    $creatorrole = array_shift($roles);                      } else {
                    $creatorrole = false;
                }

                $transaction = $DB->start_delegated_transaction();
                $xcount = 0;
                $maxxcount = 100;

                foreach ($users as $user) {
                    echo "\t"; print_string('auth_dbupdatinguser', 'auth_db', array('name'=>$user->username, 'id'=>$user->id));
                    if (!$this->update_user_record($user->username, $updatekeys, true)) {
                        echo ' - '.get_string('skipped');
                    }
                    echo "\n";
                    $xcount++;

                                        if ($creatorrole !== false) {
                        if ($this->iscreator($user->username)) {
                            role_assign($creatorrole->id, $user->id, $sitecontext->id, $this->roleauth);
                        } else {
                            role_unassign($creatorrole->id, $user->id, $sitecontext->id, $this->roleauth);
                        }
                    }
                }
                $transaction->allow_commit();
                unset($users);             }
        } else {             print_string('noupdatestobedone', 'auth_ldap');
        }

                                $sql = 'SELECT e.id, e.username
                  FROM {tmp_extuser} e
                  LEFT JOIN {user} u ON (e.username = u.username AND e.mnethostid = u.mnethostid)
                 WHERE u.id IS NULL';
        $add_users = $DB->get_records_sql($sql);

        if (!empty($add_users)) {
            print_string('userentriestoadd', 'auth_ldap', count($add_users));

            $sitecontext = context_system::instance();
            if (!empty($this->config->creators) and !empty($this->config->memberattribute)
              and $roles = get_archetype_roles('coursecreator')) {
                $creatorrole = array_shift($roles);                  } else {
                $creatorrole = false;
            }

            $transaction = $DB->start_delegated_transaction();
            foreach ($add_users as $user) {
                $user = $this->get_userinfo_asobj($user->username);

                                $user->modified   = time();
                $user->confirmed  = 1;
                $user->auth       = $this->authtype;
                $user->mnethostid = $CFG->mnet_localhost_id;
                                                $user->username = trim(core_text::strtolower($user->username));
                                                                                                $user->suspended = (int)$this->is_user_suspended($user);
                if (empty($user->lang)) {
                    $user->lang = $CFG->lang;
                }
                if (empty($user->calendartype)) {
                    $user->calendartype = $CFG->calendartype;
                }

                $id = user_create_user($user, false);
                echo "\t"; print_string('auth_dbinsertuser', 'auth_db', array('name'=>$user->username, 'id'=>$id)); echo "\n";
                $euser = $DB->get_record('user', array('id' => $id));

                if (!empty($this->config->forcechangepassword)) {
                    set_user_preference('auth_forcepasswordchange', 1, $id);
                }

                                if ($creatorrole !== false and $this->iscreator($user->username)) {
                    role_assign($creatorrole->id, $id, $sitecontext->id, $this->roleauth);
                }

            }
            $transaction->allow_commit();
            unset($add_users);         } else {
            print_string('nouserstobeadded', 'auth_ldap');
        }

        $dbman->drop_table($table);
        $this->ldap_close();

        return true;
    }

    
    function update_user_record($username, $updatekeys = false, $triggerevent = false) {
        global $CFG, $DB;

                $username = trim(core_text::strtolower($username));

                $user = $DB->get_record('user', array('username'=>$username, 'mnethostid'=>$CFG->mnet_localhost_id));
        if (empty($user)) {             error_log($this->errorlogtag.get_string('auth_dbusernotexist', 'auth_db', '', $username));
            print_error('auth_dbusernotexist', 'auth_db', '', $username);
            die;
        }

                $userid = $user->id;

        if ($newinfo = $this->get_userinfo($username)) {
            $newinfo = truncate_userinfo($newinfo);

            if (empty($updatekeys)) {                 $updatekeys = array_keys($newinfo);
            }

            if (!empty($updatekeys)) {
                $newuser = new stdClass();
                $newuser->id = $userid;
                                $newuser->suspended = (int)$this->is_user_suspended((object) $newinfo);

                foreach ($updatekeys as $key) {
                    if (isset($newinfo[$key])) {
                        $value = $newinfo[$key];
                    } else {
                        $value = '';
                    }

                    if (!empty($this->config->{'field_updatelocal_' . $key})) {
                                                if ($user->{$key} != $value) {
                            $newuser->$key = $value;
                        }
                    }
                }
                user_update_user($newuser, false, $triggerevent);
            }
        } else {
            return false;
        }
        return $DB->get_record('user', array('id'=>$userid, 'deleted'=>0));
    }

    
    function ldap_bulk_insert($username) {
        global $DB, $CFG;

        $username = core_text::strtolower($username);         $DB->insert_record_raw('tmp_extuser', array('username'=>$username,
                                                    'mnethostid'=>$CFG->mnet_localhost_id), false, true);
        echo '.';
    }

    
    function user_activate($username) {
        $extusername = core_text::convert($username, 'utf-8', $this->config->ldapencoding);

        $ldapconnection = $this->ldap_connect();

        $userdn = $this->ldap_find_userdn($ldapconnection, $extusername);
        switch ($this->config->user_type)  {
            case 'edir':
                $newinfo['loginDisabled'] = 'FALSE';
                break;
            case 'rfc2307':
            case 'rfc2307bis':
                                                                $sr = ldap_read($ldapconnection, $userdn, '(objectClass=*)',
                                array('userPassword'));
                $info = ldap_get_entries($ldapconnection, $sr);
                $info[0] = array_change_key_case($info[0], CASE_LOWER);
                $newinfo['userPassword'] = ltrim($info[0]['userpassword'][0], '*');
                break;
            case 'ad':
                                                                $sr = ldap_read($ldapconnection, $userdn, '(objectClass=*)',
                                array('userAccountControl'));
                $info = ldap_get_entries($ldapconnection, $sr);
                $info[0] = array_change_key_case($info[0], CASE_LOWER);
                $newinfo['userAccountControl'] = $info[0]['useraccountcontrol'][0]
                                                 & (~AUTH_AD_ACCOUNTDISABLE);
                break;
            default:
                print_error('user_activatenotsupportusertype', 'auth_ldap', '', $this->config->user_type_name);
        }
        $result = ldap_modify($ldapconnection, $userdn, $newinfo);
        $this->ldap_close();
        return $result;
    }

    
    function iscreator($username) {
        if (empty($this->config->creators) or empty($this->config->memberattribute)) {
            return null;
        }

        $extusername = core_text::convert($username, 'utf-8', $this->config->ldapencoding);

        $ldapconnection = $this->ldap_connect();

        if ($this->config->memberattribute_isdn) {
            if(!($userid = $this->ldap_find_userdn($ldapconnection, $extusername))) {
                return false;
            }
        } else {
            $userid = $extusername;
        }

        $group_dns = explode(';', $this->config->creators);
        $creator = ldap_isgroupmember($ldapconnection, $userid, $group_dns, $this->config->memberattribute);

        $this->ldap_close();

        return $creator;
    }

    
    function user_update($olduser, $newuser) {
        global $USER;

        if (isset($olduser->username) and isset($newuser->username) and $olduser->username != $newuser->username) {
            error_log($this->errorlogtag.get_string('renamingnotallowed', 'auth_ldap'));
            return false;
        }

        if (isset($olduser->auth) and $olduser->auth != $this->authtype) {
            return true;         }

        $attrmap = $this->ldap_attributes();
                        $update_external = false;
        foreach ($attrmap as $key => $ldapkeys) {
            if (!empty($this->config->{'field_updateremote_'.$key})) {
                $update_external = true;
                break;
            }
        }
        if (!$update_external) {
            return true;
        }

        $extoldusername = core_text::convert($olduser->username, 'utf-8', $this->config->ldapencoding);

        $ldapconnection = $this->ldap_connect();

        $search_attribs = array();
        foreach ($attrmap as $key => $values) {
            if (!is_array($values)) {
                $values = array($values);
            }
            foreach ($values as $value) {
                if (!in_array($value, $search_attribs)) {
                    array_push($search_attribs, $value);
                }
            }
        }

        if(!($user_dn = $this->ldap_find_userdn($ldapconnection, $extoldusername))) {
            return false;
        }

        $success = true;
        $user_info_result = ldap_read($ldapconnection, $user_dn, '(objectClass=*)', $search_attribs);
        if ($user_info_result) {
            $user_entry = ldap_get_entries_moodle($ldapconnection, $user_info_result);
            if (empty($user_entry)) {
                $attribs = join (', ', $search_attribs);
                error_log($this->errorlogtag.get_string('updateusernotfound', 'auth_ldap',
                                                          array('userdn'=>$user_dn,
                                                                'attribs'=>$attribs)));
                return false;             } else if (count($user_entry) > 1) {
                error_log($this->errorlogtag.get_string('morethanoneuser', 'auth_ldap'));
                return false;
            }

            $user_entry = array_change_key_case($user_entry[0], CASE_LOWER);

            foreach ($attrmap as $key => $ldapkeys) {
                $profilefield = '';
                                                $customprofilefield = 'profile_field_' . $key;
                if (isset($olduser->$key) and isset($newuser->$key)
                    and ($olduser->$key !== $newuser->$key)) {
                    $profilefield = $key;
                } else if (isset($olduser->$customprofilefield) && isset($newuser->$customprofilefield)
                    && $olduser->$customprofilefield !== $newuser->$customprofilefield) {
                    $profilefield = $customprofilefield;
                }

                if (!empty($profilefield) && !empty($this->config->{'field_updateremote_' . $key})) {
                                                                                $ambiguous = true;
                    $changed   = false;
                    if (!is_array($ldapkeys)) {
                        $ldapkeys = array($ldapkeys);
                    }
                    if (count($ldapkeys) < 2) {
                        $ambiguous = false;
                    }

                    $nuvalue = core_text::convert($newuser->$profilefield, 'utf-8', $this->config->ldapencoding);
                    empty($nuvalue) ? $nuvalue = array() : $nuvalue;
                    $ouvalue = core_text::convert($olduser->$profilefield, 'utf-8', $this->config->ldapencoding);

                    foreach ($ldapkeys as $ldapkey) {
                        $ldapkey   = $ldapkey;
                        $ldapvalue = $user_entry[$ldapkey][0];
                        if (!$ambiguous) {
                                                        if ($nuvalue !== $ldapvalue) {
                                                                if (@ldap_modify($ldapconnection, $user_dn, array($ldapkey => $nuvalue))) {
                                    $changed = true;
                                    continue;
                                } else {
                                    $success = false;
                                    error_log($this->errorlogtag.get_string ('updateremfail', 'auth_ldap',
                                                                             array('errno'=>ldap_errno($ldapconnection),
                                                                                   'errstring'=>ldap_err2str(ldap_errno($ldapconnection)),
                                                                                   'key'=>$key,
                                                                                   'ouvalue'=>$ouvalue,
                                                                                   'nuvalue'=>$nuvalue)));
                                    continue;
                                }
                            }
                        } else {
                                                                                    if ($ouvalue === '') {                                                                 if (@ldap_modify($ldapconnection, $user_dn, array($ldapkey => $nuvalue))) {
                                    $changed = true;
                                    continue;
                                } else {
                                    $success = false;
                                    error_log($this->errorlogtag.get_string ('updateremfail', 'auth_ldap',
                                                                             array('errno'=>ldap_errno($ldapconnection),
                                                                                   'errstring'=>ldap_err2str(ldap_errno($ldapconnection)),
                                                                                   'key'=>$key,
                                                                                   'ouvalue'=>$ouvalue,
                                                                                   'nuvalue'=>$nuvalue)));
                                    continue;
                                }
                            }

                                                        if ($ouvalue !== '' and $ouvalue === $ldapvalue ) {
                                                                if (@ldap_modify($ldapconnection, $user_dn, array($ldapkey => $nuvalue))) {
                                    $changed = true;
                                    continue;
                                } else {
                                    $success = false;
                                    error_log($this->errorlogtag.get_string ('updateremfail', 'auth_ldap',
                                                                             array('errno'=>ldap_errno($ldapconnection),
                                                                                   'errstring'=>ldap_err2str(ldap_errno($ldapconnection)),
                                                                                   'key'=>$key,
                                                                                   'ouvalue'=>$ouvalue,
                                                                                   'nuvalue'=>$nuvalue)));
                                    continue;
                                }
                            }
                        }
                    }

                    if ($ambiguous and !$changed) {
                        $success = false;
                        error_log($this->errorlogtag.get_string ('updateremfailamb', 'auth_ldap',
                                                                 array('key'=>$key,
                                                                       'ouvalue'=>$ouvalue,
                                                                       'nuvalue'=>$nuvalue)));
                    }
                }
            }
        } else {
            error_log($this->errorlogtag.get_string ('usernotfound', 'auth_ldap'));
            $success = false;
        }

        $this->ldap_close();
        return $success;

    }

    
    function user_update_password($user, $newpassword) {
        global $USER;

        $result = false;
        $username = $user->username;

        $extusername = core_text::convert($username, 'utf-8', $this->config->ldapencoding);
        $extpassword = core_text::convert($newpassword, 'utf-8', $this->config->ldapencoding);

        switch ($this->config->passtype) {
            case 'md5':
                $extpassword = '{MD5}' . base64_encode(pack('H*', md5($extpassword)));
                break;
            case 'sha1':
                $extpassword = '{SHA}' . base64_encode(pack('H*', sha1($extpassword)));
                break;
            case 'plaintext':
            default:
                break;         }

        $ldapconnection = $this->ldap_connect();

        $user_dn = $this->ldap_find_userdn($ldapconnection, $extusername);

        if (!$user_dn) {
            error_log($this->errorlogtag.get_string ('nodnforusername', 'auth_ldap', $user->username));
            return false;
        }

        switch ($this->config->user_type) {
            case 'edir':
                                $result = ldap_modify($ldapconnection, $user_dn, array('userPassword' => $extpassword));
                if (!$result) {
                    error_log($this->errorlogtag.get_string ('updatepasserror', 'auth_ldap',
                                                               array('errno'=>ldap_errno($ldapconnection),
                                                                     'errstring'=>ldap_err2str(ldap_errno($ldapconnection)))));
                }
                                $search_attribs = array($this->config->expireattr, 'passwordExpirationInterval', 'loginGraceLimit');
                $sr = ldap_read($ldapconnection, $user_dn, '(objectClass=*)', $search_attribs);
                if ($sr) {
                    $entry = ldap_get_entries_moodle($ldapconnection, $sr);
                    $info = array_change_key_case($entry[0], CASE_LOWER);
                    $newattrs = array();
                    if (!empty($info[$this->config->expireattr][0])) {
                                                if (!empty($info['passwordexpirationinterval'][0])) {
                           $expirationtime = time() + $info['passwordexpirationinterval'][0];
                           $ldapexpirationtime = $this->ldap_unix2expirationtime($expirationtime);
                           $newattrs['passwordExpirationTime'] = $ldapexpirationtime;
                        }

                                                if (!empty($info['logingracelimit'][0])) {
                           $newattrs['loginGraceRemaining']= $info['logingracelimit'][0];
                        }

                                                $result = ldap_modify($ldapconnection, $user_dn, $newattrs);
                        if (!$result) {
                            error_log($this->errorlogtag.get_string ('updatepasserrorexpiregrace', 'auth_ldap',
                                                                       array('errno'=>ldap_errno($ldapconnection),
                                                                             'errstring'=>ldap_err2str(ldap_errno($ldapconnection)))));
                        }
                    }
                }
                else {
                    error_log($this->errorlogtag.get_string ('updatepasserrorexpire', 'auth_ldap',
                                                             array('errno'=>ldap_errno($ldapconnection),
                                                                   'errstring'=>ldap_err2str(ldap_errno($ldapconnection)))));
                }
                break;

            case 'ad':
                                                                if (!function_exists('mb_convert_encoding')) {
                    error_log($this->errorlogtag.get_string ('needmbstring', 'auth_ldap'));
                    return false;
                }
                $extpassword = mb_convert_encoding('"'.$extpassword.'"', "UCS-2LE", $this->config->ldapencoding);
                $result = ldap_modify($ldapconnection, $user_dn, array('unicodePwd' => $extpassword));
                if (!$result) {
                    error_log($this->errorlogtag.get_string ('updatepasserror', 'auth_ldap',
                                                             array('errno'=>ldap_errno($ldapconnection),
                                                                   'errstring'=>ldap_err2str(ldap_errno($ldapconnection)))));
                }
                break;

            default:
                                $result = ldap_modify($ldapconnection, $user_dn, array('userPassword' => $extpassword));
                if (!$result) {
                    error_log($this->errorlogtag.get_string ('updatepasserror', 'auth_ldap',
                                                             array('errno'=>ldap_errno($ldapconnection),
                                                                   'errstring'=>ldap_err2str(ldap_errno($ldapconnection)))));
                }

        }

        $this->ldap_close();
        return $result;
    }

    
    function ldap_expirationtime2unix ($time, $ldapconnection, $user_dn) {
        $result = false;
        switch ($this->config->user_type) {
            case 'edir':
                $yr=substr($time, 0, 4);
                $mo=substr($time, 4, 2);
                $dt=substr($time, 6, 2);
                $hr=substr($time, 8, 2);
                $min=substr($time, 10, 2);
                $sec=substr($time, 12, 2);
                $result = mktime($hr, $min, $sec, $mo, $dt, $yr);
                break;
            case 'rfc2307':
            case 'rfc2307bis':
                $result = $time * DAYSECS;                 break;
            case 'ad':
                $result = $this->ldap_get_ad_pwdexpire($time, $ldapconnection, $user_dn);
                break;
            default:
                print_error('auth_ldap_usertypeundefined', 'auth_ldap');
        }
        return $result;
    }

    
    function ldap_unix2expirationtime($time) {
        $result = false;
        switch ($this->config->user_type) {
            case 'edir':
                $result=date('YmdHis', $time).'Z';
                break;
            case 'rfc2307':
            case 'rfc2307bis':
                $result = $time ;                 break;
            default:
                print_error('auth_ldap_usertypeundefined2', 'auth_ldap');
        }
        return $result;

    }

    

    function ldap_attributes () {
        $moodleattributes = array();
                $customfields = $this->get_custom_user_profile_fields();
        if (!empty($customfields) && !empty($this->userfields)) {
            $userfields = array_merge($this->userfields, $customfields);
        } else {
            $userfields = $this->userfields;
        }

        foreach ($userfields as $field) {
            if (!empty($this->config->{"field_map_$field"})) {
                $moodleattributes[$field] = core_text::strtolower(trim($this->config->{"field_map_$field"}));
                if (preg_match('/,/', $moodleattributes[$field])) {
                    $moodleattributes[$field] = explode(',', $moodleattributes[$field]);                 }
            }
        }
        $moodleattributes['username'] = core_text::strtolower(trim($this->config->user_attribute));
        $moodleattributes['suspended'] = core_text::strtolower(trim($this->config->suspended_attribute));
        return $moodleattributes;
    }

    
    function ldap_get_userlist($filter='*') {
        $fresult = array();

        $ldapconnection = $this->ldap_connect();

        if ($filter == '*') {
           $filter = '(&('.$this->config->user_attribute.'=*)'.$this->config->objectclass.')';
        }

        $contexts = explode(';', $this->config->contexts);
        if (!empty($this->config->create_context)) {
            array_push($contexts, $this->config->create_context);
        }

        $ldap_cookie = '';
        $ldap_pagedresults = ldap_paged_results_supported($this->config->ldap_version);
        foreach ($contexts as $context) {
            $context = trim($context);
            if (empty($context)) {
                continue;
            }

            do {
                if ($ldap_pagedresults) {
                    ldap_control_paged_result($ldapconnection, $this->config->pagesize, true, $ldap_cookie);
                }
                if ($this->config->search_sub) {
                                        $ldap_result = ldap_search($ldapconnection, $context, $filter, array($this->config->user_attribute));
                } else {
                                        $ldap_result = ldap_list($ldapconnection, $context, $filter, array($this->config->user_attribute));
                }
                if(!$ldap_result) {
                    continue;
                }
                if ($ldap_pagedresults) {
                    ldap_control_paged_result_response($ldapconnection, $ldap_result, $ldap_cookie);
                }
                $users = ldap_get_entries_moodle($ldapconnection, $ldap_result);
                                for ($i = 0; $i < count($users); $i++) {
                    $extuser = core_text::convert($users[$i][$this->config->user_attribute][0],
                                                $this->config->ldapencoding, 'utf-8');
                    array_push($fresult, $extuser);
                }
                unset($ldap_result);             } while ($ldap_pagedresults && !empty($ldap_cookie));
        }

                $this->ldap_close($ldap_pagedresults);
        return $fresult;
    }

    
    function prevent_local_passwords() {
        return !empty($this->config->preventpassindb);
    }

    
    function is_internal() {
        return false;
    }

    
    function can_change_password() {
        return !empty($this->config->stdchangepassword) or !empty($this->config->changepasswordurl);
    }

    
    function change_password_url() {
        if (empty($this->config->stdchangepassword)) {
            if (!empty($this->config->changepasswordurl)) {
                return new moodle_url($this->config->changepasswordurl);
            } else {
                return null;
            }
        } else {
            return null;
        }
    }

    
    function loginpage_hook() {
        global $CFG, $SESSION;

                
        if (($_SERVER['REQUEST_METHOD'] === 'GET'                      || ($_SERVER['REQUEST_METHOD'] === 'POST'
                 && (get_local_referer() != strip_querystring(qualified_me()))))
                                                                                                                                && !empty($this->config->ntlmsso_enabled)                 && !empty($this->config->ntlmsso_subnet)                  && empty($_GET['authldap_skipntlmsso'])                   && (isguestuser() || !isloggedin())                       && address_in_subnet(getremoteaddr(), $this->config->ntlmsso_subnet)) {

                        if (empty($SESSION->wantsurl)) {
                $SESSION->wantsurl = null;
                $referer = get_local_referer(false);
                if ($referer &&
                        $referer != $CFG->wwwroot &&
                        $referer != $CFG->wwwroot . '/' &&
                        $referer != $CFG->httpswwwroot . '/login/' &&
                        $referer != $CFG->httpswwwroot . '/login/index.php') {
                    $SESSION->wantsurl = $referer;
                }
            }

                        if($this->config->ntlmsso_ie_fastpath == AUTH_NTLM_FASTPATH_YESATTEMPT ||
                $this->config->ntlmsso_ie_fastpath == AUTH_NTLM_FASTPATH_YESFORM) {
                if (core_useragent::is_ie()) {
                    $sesskey = sesskey();
                    redirect($CFG->wwwroot.'/auth/ldap/ntlmsso_magic.php?sesskey='.$sesskey);
                } else if ($this->config->ntlmsso_ie_fastpath == AUTH_NTLM_FASTPATH_YESFORM) {
                    redirect($CFG->httpswwwroot.'/login/index.php?authldap_skipntlmsso=1');
                }
            }
            redirect($CFG->wwwroot.'/auth/ldap/ntlmsso_attempt.php');
        }

        
                                                        if (empty($SESSION->wantsurl)
            && (get_local_referer() == $CFG->httpswwwroot.'/auth/ldap/ntlmsso_finish.php')) {

            $SESSION->wantsurl = $CFG->wwwroot;
        }
    }

    
    function ntlmsso_magic($sesskey) {
        if (isset($_SERVER['REMOTE_USER']) && !empty($_SERVER['REMOTE_USER'])) {

                                                            $username = core_text::convert($_SERVER['REMOTE_USER'], 'iso-8859-1', 'utf-8');

            switch ($this->config->ntlmsso_type) {
                case 'ntlm':
                                        $username = $this->get_ntlm_remote_user($username);
                    if (empty($username)) {
                        return false;
                    }
                    break;
                case 'kerberos':
                                        $username = substr($username, 0, strpos($username, '@'));
                    break;
                default:
                    error_log($this->errorlogtag.get_string ('ntlmsso_unknowntype', 'auth_ldap'));
                    return false;             }

            $username = core_text::strtolower($username);             set_cache_flag($this->pluginconfig.'/ntlmsess', $sesskey, $username, AUTH_NTLMTIMEOUT);
            return true;
        }
        return false;
    }

    
    function ntlmsso_finish() {
        global $CFG, $USER, $SESSION;

        $key = sesskey();
        $cf = get_cache_flags($this->pluginconfig.'/ntlmsess');
        if (!isset($cf[$key]) || $cf[$key] === '') {
            return false;
        }
        $username   = $cf[$key];

                        $user = authenticate_user_login($username, $key);
        if ($user) {
            complete_user_login($user);

                                    unset_cache_flag($this->pluginconfig.'/ntlmsess', $key);

                        if (user_not_fully_set_up($USER, true)) {
                $urltogo = $CFG->wwwroot.'/user/edit.php';
                            } else if (isset($SESSION->wantsurl) and (strpos($SESSION->wantsurl, $CFG->wwwroot) === 0)) {
                $urltogo = $SESSION->wantsurl;                    unset($SESSION->wantsurl);
            } else {
                                $urltogo = $CFG->wwwroot.'/';
                unset($SESSION->wantsurl);
            }
                        if (!PHPUNIT_TEST) {
                redirect($urltogo);
            }
        }
                return false;
    }

    
    function sync_roles($user) {
        $iscreator = $this->iscreator($user->username);
        if ($iscreator === null) {
            return;         }

        if ($roles = get_archetype_roles('coursecreator')) {
            $creatorrole = array_shift($roles);                  $systemcontext = context_system::instance();

            if ($iscreator) {                 role_assign($creatorrole->id, $user->id, $systemcontext->id, $this->roleauth);
            } else {
                                role_unassign($creatorrole->id, $user->id, $systemcontext->id, $this->roleauth);
            }
        }
    }

    
    function config_form($config, $err, $user_fields) {
        global $CFG, $OUTPUT;

        if (!function_exists('ldap_connect')) {             echo $OUTPUT->notification(get_string('auth_ldap_noextension', 'auth_ldap'));
            return;
        }

        include($CFG->dirroot.'/auth/ldap/config.html');
    }

    
    function process_config($config) {
                if (!isset($config->host_url)) {
             $config->host_url = '';
        }
        if (!isset($config->start_tls)) {
             $config->start_tls = false;
        }
        if (empty($config->ldapencoding)) {
         $config->ldapencoding = 'utf-8';
        }
        if (!isset($config->pagesize)) {
            $config->pagesize = LDAP_DEFAULT_PAGESIZE;
        }
        if (!isset($config->contexts)) {
             $config->contexts = '';
        }
        if (!isset($config->user_type)) {
             $config->user_type = 'default';
        }
        if (!isset($config->user_attribute)) {
             $config->user_attribute = '';
        }
        if (!isset($config->suspended_attribute)) {
            $config->suspended_attribute = '';
        }
        if (!isset($config->sync_suspended)) {
            $config->sync_suspended = false;
        }
        if (!isset($config->search_sub)) {
             $config->search_sub = '';
        }
        if (!isset($config->opt_deref)) {
             $config->opt_deref = LDAP_DEREF_NEVER;
        }
        if (!isset($config->preventpassindb)) {
             $config->preventpassindb = 0;
        }
        if (!isset($config->bind_dn)) {
            $config->bind_dn = '';
        }
        if (!isset($config->bind_pw)) {
            $config->bind_pw = '';
        }
        if (!isset($config->ldap_version)) {
            $config->ldap_version = '3';
        }
        if (!isset($config->objectclass)) {
            $config->objectclass = '';
        }
        if (!isset($config->memberattribute)) {
            $config->memberattribute = '';
        }
        if (!isset($config->memberattribute_isdn)) {
            $config->memberattribute_isdn = '';
        }
        if (!isset($config->creators)) {
            $config->creators = '';
        }
        if (!isset($config->create_context)) {
            $config->create_context = '';
        }
        if (!isset($config->expiration)) {
            $config->expiration = '';
        }
        if (!isset($config->expiration_warning)) {
            $config->expiration_warning = '10';
        }
        if (!isset($config->expireattr)) {
            $config->expireattr = '';
        }
        if (!isset($config->gracelogins)) {
            $config->gracelogins = '';
        }
        if (!isset($config->graceattr)) {
            $config->graceattr = '';
        }
        if (!isset($config->auth_user_create)) {
            $config->auth_user_create = '';
        }
        if (!isset($config->forcechangepassword)) {
            $config->forcechangepassword = 0;
        }
        if (!isset($config->stdchangepassword)) {
            $config->stdchangepassword = 0;
        }
        if (!isset($config->passtype)) {
            $config->passtype = 'plaintext';
        }
        if (!isset($config->changepasswordurl)) {
            $config->changepasswordurl = '';
        }
        if (!isset($config->removeuser)) {
            $config->removeuser = AUTH_REMOVEUSER_KEEP;
        }
        if (!isset($config->ntlmsso_enabled)) {
            $config->ntlmsso_enabled = 0;
        }
        if (!isset($config->ntlmsso_subnet)) {
            $config->ntlmsso_subnet = '';
        }
        if (!isset($config->ntlmsso_ie_fastpath)) {
            $config->ntlmsso_ie_fastpath = 0;
        }
        if (!isset($config->ntlmsso_type)) {
            $config->ntlmsso_type = 'ntlm';
        }
        if (!isset($config->ntlmsso_remoteuserformat)) {
            $config->ntlmsso_remoteuserformat = '';
        }

                $config->contexts = explode(';', $config->contexts);
        $config->contexts = array_map(create_function('$x', 'return core_text::strtolower(trim($x));'),
                                      $config->contexts);
        $config->contexts = implode(';', array_unique($config->contexts));

                set_config('host_url', trim($config->host_url), $this->pluginconfig);
        set_config('start_tls', $config->start_tls, $this->pluginconfig);
        set_config('ldapencoding', trim($config->ldapencoding), $this->pluginconfig);
        set_config('pagesize', (int)trim($config->pagesize), $this->pluginconfig);
        set_config('contexts', $config->contexts, $this->pluginconfig);
        set_config('user_type', core_text::strtolower(trim($config->user_type)), $this->pluginconfig);
        set_config('user_attribute', core_text::strtolower(trim($config->user_attribute)), $this->pluginconfig);
        set_config('suspended_attribute', core_text::strtolower(trim($config->suspended_attribute)), $this->pluginconfig);
        set_config('sync_suspended', $config->sync_suspended, $this->pluginconfig);
        set_config('search_sub', $config->search_sub, $this->pluginconfig);
        set_config('opt_deref', $config->opt_deref, $this->pluginconfig);
        set_config('preventpassindb', $config->preventpassindb, $this->pluginconfig);
        set_config('bind_dn', trim($config->bind_dn), $this->pluginconfig);
        set_config('bind_pw', $config->bind_pw, $this->pluginconfig);
        set_config('ldap_version', $config->ldap_version, $this->pluginconfig);
        set_config('objectclass', trim($config->objectclass), $this->pluginconfig);
        set_config('memberattribute', core_text::strtolower(trim($config->memberattribute)), $this->pluginconfig);
        set_config('memberattribute_isdn', $config->memberattribute_isdn, $this->pluginconfig);
        set_config('creators', trim($config->creators), $this->pluginconfig);
        set_config('create_context', trim($config->create_context), $this->pluginconfig);
        set_config('expiration', $config->expiration, $this->pluginconfig);
        set_config('expiration_warning', trim($config->expiration_warning), $this->pluginconfig);
        set_config('expireattr', core_text::strtolower(trim($config->expireattr)), $this->pluginconfig);
        set_config('gracelogins', $config->gracelogins, $this->pluginconfig);
        set_config('graceattr', core_text::strtolower(trim($config->graceattr)), $this->pluginconfig);
        set_config('auth_user_create', $config->auth_user_create, $this->pluginconfig);
        set_config('forcechangepassword', $config->forcechangepassword, $this->pluginconfig);
        set_config('stdchangepassword', $config->stdchangepassword, $this->pluginconfig);
        set_config('passtype', $config->passtype, $this->pluginconfig);
        set_config('changepasswordurl', trim($config->changepasswordurl), $this->pluginconfig);
        set_config('removeuser', $config->removeuser, $this->pluginconfig);
        set_config('ntlmsso_enabled', (int)$config->ntlmsso_enabled, $this->pluginconfig);
        set_config('ntlmsso_subnet', trim($config->ntlmsso_subnet), $this->pluginconfig);
        set_config('ntlmsso_ie_fastpath', (int)$config->ntlmsso_ie_fastpath, $this->pluginconfig);
        set_config('ntlmsso_type', $config->ntlmsso_type, 'auth/ldap');
        set_config('ntlmsso_remoteuserformat', trim($config->ntlmsso_remoteuserformat), 'auth/ldap');

        return true;
    }

    
    function ldap_get_ad_pwdexpire($pwdlastset, $ldapconn, $user_dn){
        global $CFG;

        if (!function_exists('bcsub')) {
            error_log($this->errorlogtag.get_string ('needbcmath', 'auth_ldap'));
            return 0;
        }

                        $sr = ldap_read($ldapconn, $user_dn, '(objectClass=*)',
                        array('userAccountControl'));
        if (!$sr) {
            error_log($this->errorlogtag.get_string ('useracctctrlerror', 'auth_ldap', $user_dn));
                                    return 0;
        }

        $entry = ldap_get_entries_moodle($ldapconn, $sr);
        $info = array_change_key_case($entry[0], CASE_LOWER);
        $useraccountcontrol = $info['useraccountcontrol'][0];
        if ($useraccountcontrol & UF_DONT_EXPIRE_PASSWD) {
                        return 0;
        }

                                if ($pwdlastset === '0') {
                        return -1;
        }

                                                                                                                                                                        
        $sr = ldap_read($ldapconn, ROOTDSE, '(objectClass=*)',
                        array('defaultNamingContext'));
        if (!$sr) {
            error_log($this->errorlogtag.get_string ('rootdseerror', 'auth_ldap'));
            return 0;
        }

        $entry = ldap_get_entries_moodle($ldapconn, $sr);
        $info = array_change_key_case($entry[0], CASE_LOWER);
        $domaindn = $info['defaultnamingcontext'][0];

        $sr = ldap_read ($ldapconn, $domaindn, '(objectClass=*)',
                         array('maxPwdAge'));
        $entry = ldap_get_entries_moodle($ldapconn, $sr);
        $info = array_change_key_case($entry[0], CASE_LOWER);
        $maxpwdage = $info['maxpwdage'][0];
        if ($sr = ldap_read($ldapconn, $user_dn, '(objectClass=*)', array('msDS-ResultantPSO'))) {
            if ($entry = ldap_get_entries_moodle($ldapconn, $sr)) {
                $info = array_change_key_case($entry[0], CASE_LOWER);
                $userpso = $info['msds-resultantpso'][0];

                                                if (!empty($userpso)) {
                    $sr = ldap_read($ldapconn, $userpso, '(objectClass=*)', array('msDS-MaximumPasswordAge'));
                    if ($entry = ldap_get_entries_moodle($ldapconn, $sr)) {
                        $info = array_change_key_case($entry[0], CASE_LOWER);
                                                $maxpwdage = $info['msds-maximumpasswordage'][0];
                    }
                }
            }
        }
                                                                                                                                                                                                                                                                                        
                                if (bcmod ($maxpwdage, 4294967296) === '0') {
            return 0;
        }

                                $pwdexpire = bcsub ($pwdlastset, $maxpwdage);

                        return bcsub( bcdiv($pwdexpire, '10000000'), '11644473600');
    }

    
    function ldap_connect() {
                                                if(!empty($this->ldapconnection)) {
            $this->ldapconns++;
            return $this->ldapconnection;
        }

        if($ldapconnection = ldap_connect_moodle($this->config->host_url, $this->config->ldap_version,
                                                 $this->config->user_type, $this->config->bind_dn,
                                                 $this->config->bind_pw, $this->config->opt_deref,
                                                 $debuginfo, $this->config->start_tls)) {
            $this->ldapconns = 1;
            $this->ldapconnection = $ldapconnection;
            return $ldapconnection;
        }

        print_error('auth_ldap_noconnect_all', 'auth_ldap', '', $debuginfo);
    }

    
    function ldap_close($force=false) {
        $this->ldapconns--;
        if (($this->ldapconns == 0) || ($force)) {
            $this->ldapconns = 0;
            @ldap_close($this->ldapconnection);
            unset($this->ldapconnection);
        }
    }

    
    function ldap_find_userdn($ldapconnection, $extusername) {
        $ldap_contexts = explode(';', $this->config->contexts);
        if (!empty($this->config->create_context)) {
            array_push($ldap_contexts, $this->config->create_context);
        }

        return ldap_find_userdn($ldapconnection, $extusername, $ldap_contexts, $this->config->objectclass,
                                $this->config->user_attribute, $this->config->search_sub);
    }


    
    function validate_form($form, &$err) {
        if ($form->ntlmsso_type == 'ntlm') {
            $format = trim($form->ntlmsso_remoteuserformat);
            if (!empty($format) && !preg_match('/%username%/i', $format)) {
                $err['ntlmsso_remoteuserformat'] = get_string('auth_ntlmsso_missing_username', 'auth_ldap');
            }
        }
    }


    
    protected function get_ntlm_remote_user($remoteuser) {
        if (empty($this->config->ntlmsso_remoteuserformat)) {
            $format = AUTH_NTLM_DEFAULT_FORMAT;
        } else {
            $format = $this->config->ntlmsso_remoteuserformat;
        }

        $format = preg_quote($format);
        $formatregex = preg_replace(array('#%domain%#', '#%username%#'),
                                    array('('.AUTH_NTLM_VALID_DOMAINNAME.')', '('.AUTH_NTLM_VALID_USERNAME.')'),
                                    $format);
        if (preg_match('#^'.$formatregex.'$#', $remoteuser, $matches)) {
            $user = end($matches);
            return $user;
        }

        
        error_log($this->errorlogtag.get_string ('auth_ntlmsso_maybeinvalidformat', 'auth_ldap'));
        return '';
    }

    
    protected function ldap_ad_pwdexpired_from_diagmsg($diagmsg) {
                                                                        $diagmsg = explode(',', $diagmsg);
        if (preg_match('/data (773|532)/i', trim($diagmsg[2]))) {
            return true;
        }
        return false;
    }

    
    protected function is_user_suspended($user) {
        if (!$this->config->suspended_attribute || !isset($user->suspended)) {
            return false;
        }
        if ($this->config->suspended_attribute == 'useraccountcontrol' && $this->config->user_type == 'ad') {
            return (bool)($user->suspended & AUTH_AD_ACCOUNTDISABLE);
        }

        return (bool)$user->suspended;
    }

} 