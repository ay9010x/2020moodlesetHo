<?php



if (!defined('MOODLE_INTERNAL')) {
    die('Direct access to this script is forbidden.');    }

require_once($CFG->libdir.'/authlib.php');
require_once($CFG->dirroot.'/user/profile/lib.php');



class auth_plugin_emailadmin extends auth_plugin_base {

    
    public function __construct() {
        $this->authtype = 'emailadmin';
        $this->config = get_config('auth/emailadmin');
    }

    
    public function auth_plugin_email() {
        debugging('Use of class name as constructor is deprecated', DEBUG_DEVELOPER);
        self::__construct();
    }

    
    public function user_login ($username, $password) {
        global $CFG, $DB;
        if ($user = $DB->get_record('user', array('username' => $username, 'mnethostid' => $CFG->mnet_localhost_id))) {
            return validate_internal_user_password($user, $password);
        }
        return false;
    }

    
    public function user_update_password($user, $newpassword) {
        $user = get_complete_user_data('id', $user->id);
        return update_internal_user_password($user, $newpassword);
    }

    public function can_signup() {
        return true;
    }

    
    public function user_signup($user, $notify=true) {
        global $CFG, $DB;
        require_once($CFG->dirroot.'/user/profile/lib.php');
        require_once($CFG->dirroot.'/user/lib.php');

        $user->password = hash_internal_user_password($user->password);

        $user->id = $DB->insert_record('user', $user);

                profile_save_data($user);

        $user = $DB->get_record('user', array('id' => $user->id));

        $usercontext = context_user::instance($user->id);
        $event = \core\event\user_created::create(
            array(
                'objectid' => $user->id,
                'relateduserid' => $user->id,
                'context' => $usercontext
                )
            );
        $event->trigger();

        if (! $this->send_confirmation_email_support($user, $this->config)) {
            print_error('auth_emailadminnoemail', 'auth_emailadmin');
        }

        if ($notify) {
            global $CFG, $PAGE, $OUTPUT;
            $emailconfirm = get_string('emailconfirm');
            $PAGE->navbar->add($emailconfirm);
            $PAGE->set_title($emailconfirm);
            $PAGE->set_heading($PAGE->course->fullname);
            echo $OUTPUT->header();
            notice(get_string('auth_emailadminconfirmsent', 'auth_emailadmin', $user->email), "$CFG->wwwroot/index.php");
        } else {
            return true;
        }
    }

    
    public function can_confirm() {
        return true;
    }

    
    public function user_confirm($username, $confirmsecret) {
        global $CFG, $DB;
        $user = get_complete_user_data('username', $username);

        if (!empty($user)) {
            if ($user->confirmed) {
                return AUTH_CONFIRM_ALREADY;

            } else if ($user->auth != $this->authtype) {
                mtrace("Auth mismatch for user ". $user->username .": ". $user->auth ." != ". $this->authtype);
                return AUTH_CONFIRM_ERROR;

            } else if ($user->secret == $confirmsecret) {                   $DB->set_field("user", "confirmed", 1, array("id" => $user->id));
                if ($user->firstaccess == 0) {
                    $DB->set_field("user", "firstaccess", time(), array("id" => $user->id));
                                                            $newcategory = new stdClass();
                    $newcategory->name   = fullname($user);                     $newcategory->parent = 0;
                    $newcategory->depth  = 1;
                    $newcategory->descriptionformat = FORMAT_MOODLE;
                    $newcategory->description       = '';
                    $newcategory->parent            = 0;
                    $newcategory->visible           = 1;
                    $newcategory->timemodified = time();
                                        $newcategory->id = $DB->insert_record('course_categories', $newcategory);
                    $path = '/' . $newcategory->id;
                    $DB->set_field('course_categories', 'path', $path, array('id' => $newcategory->id));
                    $catcontext = context_coursecat::instance($newcategory->id);
                    $catcontext->mark_dirty();
                    fix_course_sortorder();
                    cache_helper::purge_by_event('changesincoursecat');

                    require_once($CFG->libdir.'/accesslib.php');
                                        $deptrole = get_archetype_role('departmentmanager');
                    role_assign($deptrole->id, $user->id, $catcontext->id);                 }
                return AUTH_CONFIRM_OK;
            }
        } else {
            mtrace("User not found: ". $username);
            return AUTH_CONFIRM_ERROR;
        }
    }

    public function prevent_local_passwords() {
        return false;
    }

    
    public function is_internal() {
        return true;
    }

    
    public function can_change_password() {
        return true;
    }

    
    public function change_password_url() {
        return null;     }

    
    public function can_reset_password() {
        return true;
    }

    
    public function config_form($config, $err, $user_fields) {
        include("config.html");
    }

    
    public function process_config($config) {
                if (!isset($config->recaptcha)) {
            $config->recaptcha = false;
        }

        
        if (!isset($config->notif_strategy) || !is_numeric($config->notif_strategy) || $config->notif_strategy < -2) {
            $config->notif_strategy = -1;         }

                set_config('recaptcha', $config->recaptcha, 'auth/emailadmin');
        set_config('notif_strategy', $config->notif_strategy, 'auth/emailadmin');
        return true;
    }

    
    public function is_captcha_enabled() {
        global $CFG;
        return isset($CFG->recaptchapublickey) &&
            isset($CFG->recaptchaprivatekey) &&
            get_config("auth/{$this->authtype}", 'recaptcha');
    }

    
    public function send_confirmation_email_support($user, $config) {
        global $CFG;

        
        $site = get_site();
        $supportuser = core_user::get_support_user();

        $data = array();

                $data["userdata"] = '';

        $skip = array("userdata", "password", "secret");
        foreach (((array) $user) as $dataname => $datavalue) {
            if ( in_array($dataname, $skip) ) {
                continue;
            }

            $data[$dataname]      = $datavalue;
            $data["userdata"]      .= $dataname . ': ' . $datavalue . PHP_EOL;
        }
        $data["sitename"]  = format_string($site->fullname);
        $data["admin"]     = generate_email_signoff();

                        
                global $USER, $COURSE, $SESSION;
        $lang_hack = new stdClass();
        $lang_hack->forcelang = $supportuser->lang;
        $lang_hack->lang = $supportuser->lang;
        $hack_backup = ['USER' => false, 'COURSE' => false, 'SESSION' => false];
        foreach ($hack_backup as $hack_backup_key => $hack_backup_value) {
            $hack_backup[$hack_backup_key] = $GLOBALS[$hack_backup_key];
            $GLOBALS[$hack_backup_key] = $lang_hack;
        }
                $use_lang = 'zh_tw';
        foreach ($hack_backup as $hack_backup_key => $hack_backup_value) {
            $GLOBALS[$hack_backup_key] = $hack_backup_value;
        }
        

        $subject = get_string_manager()->get_string('auth_emailadminconfirmationsubject',
                                                    'auth_emailadmin',
                                                    format_string($site->fullname),
                                                    $use_lang);

        $username = urlencode($user->username);
        $username = str_replace('.', '%2E', $username);         $data["link"] = $CFG->wwwroot .'/auth/emailadmin/confirm.php?data='. $user->secret .'/'. $username;
        $message     = get_string_manager()->get_string('auth_emailadminconfirmation', 'auth_emailadmin', $data, $use_lang);
        $messagehtml = text_to_html($message, false, false, true);

        $user->mailformat = 1;  
                $admins = get_admins();
        $return = false;
        $admin_found = false;

                        $config->notif_strategy = intval($config->notif_strategy);
                
        $send_list = array();
        foreach ($admins as $admin) {
                        if ($config->notif_strategy < 0 || $config->notif_strategy == $admin->id) {
                $admin_found = true;
            }
            if ($admin_found) {
                $send_list[] = $admin;
                if ($config->notif_strategy == -1 || $config->notif_strategy >= 0 ) {
                    break;
                }
            }
        }

        $errors = array();
        foreach ($send_list as $admin) {
            $result = email_to_user($admin, $supportuser, $subject, $message, $messagehtml);
            $return |= $result;
            if (! $result) {
                $errors[] = $admin->username;
            }
        }

        $error = '';
        if (!$admin_found) {
            $error = get_string("auth_emailadminnoadmin", "auth_emailadmin");
        }

        if (count($errors) > 0) {
            $error = get_string("auth_emailadminnotif_failed", "auth_emailadmin");
            foreach ($errors as $admin) {
                $error .= $admin . " ";
            }
        }

        if ($error != '') {
            error_log($error);
            $subject = get_string('auth_emailadminconfirmationsubject', 'auth_emailadmin', format_string($site->fullname));
            $message = $error . "\n" . get_string('auth_emailadminconfirmation', 'auth_emailadmin', $data);
            $messagehtml = text_to_html($message, false, false, true);
            foreach ($admins as $admin) {
                if (!in_array($admin->username, $errors)) {
                    $result = email_to_user($admin, $supportuser, $subject, $message, $messagehtml);
                }
            }
        }

        return $return;
    }

    
    public function list_custom_fields($user) {
        global $CFG, $DB;

        $result = '';
        if ($fields = $DB->get_records('user_info_field')) {
            foreach ($fields as $field) {
                $fieldobj = new profile_field_base($field->id, $user->id);
                $result .= format_string($fieldobj->field->name.':') . ' ' . $fieldobj->display_data() . PHP_EOL;
            }
        }

        return $result;
    }
}
