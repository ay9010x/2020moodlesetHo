<?php


namespace tool_moodleset;

require_once("$CFG->libdir/externallib.php");

use external_api;
use external_function_parameters;
use external_value;
use external_format_value;
use external_single_structure;
use external_multiple_structure;
use invalid_parameter_exception;


class external extends external_api {

        
    public static function create_users_parameters() {
        global $CFG;
        return new external_function_parameters(
            array(
                'users' => new external_multiple_structure(
                    new external_single_structure(
                        array(
                            'username' =>
                                new external_value(\core_user::get_property_type('username'), '使用者帳號(username)'),
                            'password' =>
                                new external_value(\core_user::get_property_type('password'), '使用者密碼(請依循Moodle密碼規範)', VALUE_OPTIONAL),
                                                                                    'firstname' =>
                                new external_value(\core_user::get_property_type('firstname'), '使用者-名'),
                            'lastname' =>
                                new external_value(\core_user::get_property_type('lastname'), '使用者-姓'),
                            'email' =>
                                new external_value(\core_user::get_property_type('email'), '有效且唯一的email位址'),
                            'auth' =>
                                new external_value(\core_user::get_property_type('auth'), '使用者身份認證(如:mainual)', VALUE_DEFAULT,
                                    'manual', \core_user::get_property_null('auth')),
                            'idnumber' =>
                                new external_value(\core_user::get_property_type('idnumber'), '在校內的身份識別編號', VALUE_DEFAULT, ''),
                            'lang' =>
                                new external_value(\core_user::get_property_type('lang'), '使用者預設語系(如: en 或 zh_tw)', VALUE_DEFAULT,
                                    \core_user::get_property_default('lang'), \core_user::get_property_null('lang')),
                                                                                                                                                                                                                                                                                                                                                                            'description' =>
                                new external_value(\core_user::get_property_type('description'), '個人自我介紹', VALUE_OPTIONAL),
                                                                                                                                            'firstnamephonetic' =>
                                new external_value(\core_user::get_property_type('firstnamephonetic'), 'The first name(s) phonetically of the user', VALUE_OPTIONAL),
                            'lastnamephonetic' =>
                                new external_value(\core_user::get_property_type('lastnamephonetic'), 'The family name phonetically of the user', VALUE_OPTIONAL),
                                                                                                                                                                                                                                                                                                                                                'customfields' => new external_multiple_structure(
                                new external_single_structure(
                                    array(
                                        'type'  => new external_value(PARAM_ALPHANUMEXT, '自定義欄位-名稱(name)'),
                                        'value' => new external_value(PARAM_RAW, '自定義欄位-值(value)')
                                    )
                                ), '使用者-自定義欄位(個人資料欄位)', VALUE_OPTIONAL)
                        )
                    )
                )
            )
        );
    }

    
    public static function create_users($users) {
        global $CFG, $DB;
        require_once($CFG->dirroot."/lib/weblib.php");
        require_once($CFG->dirroot."/user/lib.php");
        require_once($CFG->dirroot."/user/profile/lib.php");                 $context = \context_system::instance();
        self::validate_context($context);
        require_capability('moodle/user:create', $context);

                        $params = self::validate_parameters(self::create_users_parameters(), array('users' => $users));

        $availableauths  = \core_component::get_plugin_list('auth');
        unset($availableauths['mnet']);               unset($availableauths['webservice']); 
        $availablethemes = \core_component::get_plugin_list('theme');
        $availablelangs  = get_string_manager()->get_list_of_translations();

        $transaction = $DB->start_delegated_transaction();

        $userids = array();
        $createpassword = false;
        foreach ($params['users'] as $user) {
                        if ($DB->record_exists('user', array('username' => $user['username'], 'mnethostid' => $CFG->mnet_localhost_id))) {
                throw new invalid_parameter_exception('Username already exists: '.$user['username']);
            }

                        if (empty($availableauths[$user['auth']])) {
                throw new invalid_parameter_exception('Invalid authentication type: '.$user['auth']);
            }

                        if (empty($availablelangs[$user['lang']])) {
                throw new invalid_parameter_exception('Invalid language code: '.$user['lang']);
            }

                        if (!empty($user['theme']) && empty($availablethemes[$user['theme']])) {                                                                                                                                                                                                                                                                                throw new invalid_parameter_exception('Invalid theme: '.$user['theme']);
            }

                                                
            $user['confirmed'] = true;
            $user['mnethostid'] = $CFG->mnet_localhost_id;

                                    if (!validate_email($user['email'])) {
                throw new invalid_parameter_exception('Email address is invalid: '.$user['email']);
            } else if (empty($CFG->allowaccountssameemail) &&
                    $DB->record_exists('user', array('email' => $user['email'], 'mnethostid' => $user['mnethostid']))) {
                throw new invalid_parameter_exception('Email address already exists: '.$user['email']);
            }
            
            $createpassword = !empty($user['createpassword']);
            unset($user['createpassword']);
            if ($createpassword) {
                $user['password'] = '';
                $updatepassword = false;
            } else {
                $updatepassword = true;
            }

                        $user['id'] = user_create_user($user, $updatepassword, false);

                        if (!empty($user['customfields'])) {
                foreach ($user['customfields'] as $customfield) {
                                                            $user["profile_field_".$customfield['type']] = $customfield['value'];
                }
                profile_save_data((object) $user);
            }

            if ($createpassword) {
                $userobject = (object)$user;
                setnew_password_and_mail($userobject);
                unset_user_preference('create_password', $userobject);
                set_user_preference('auth_forcepasswordchange', 1, $userobject);
            }

                        \core\event\user_created::create_from_userid($user['id'])->trigger();

                                                                        
            $userids[] = array('id' => $user['id'], 'username' => $user['username']);
        }

        $transaction->allow_commit();

        return $userids;
    }

    
    public static function create_users_returns() {
        return new external_multiple_structure(
            new external_single_structure(
                array(
                    'id'       => new external_value(\core_user::get_property_type('id'), '使用者ID'),
                    'username' => new external_value(\core_user::get_property_type('username'), '使用者帳號(username)'),
                )
            )
        );
    }
    
    
    public static function update_users_parameters() {
        return new external_function_parameters(
            array(
                'users' => new external_multiple_structure(
                    new external_single_structure(
                        array(
                            'id' =>
                                new external_value(\core_user::get_property_type('id'), '使用者ID'),
                            'username' =>
                                new external_value(\core_user::get_property_type('username'), '使用者帳號(username)',
                                    VALUE_OPTIONAL, '', NULL_NOT_ALLOWED),
                            'password' =>
                                new external_value(\core_user::get_property_type('password'), '使用者密碼', VALUE_OPTIONAL,
                                    '', NULL_NOT_ALLOWED),
                            'firstname' =>
                                new external_value(\core_user::get_property_type('firstname'), '使用者-名', VALUE_OPTIONAL, '', NULL_NOT_ALLOWED),
                            'lastname' =>
                                new external_value(\core_user::get_property_type('lastname'), '使用者-姓', VALUE_OPTIONAL),
                            'email' =>
                                new external_value(\core_user::get_property_type('email'), '有效且唯一的email位址', VALUE_OPTIONAL, '',
                                    NULL_NOT_ALLOWED),
                            'auth' =>
                                new external_value(\core_user::get_property_type('auth'), '使用者身份認證(如:mainual)', VALUE_OPTIONAL, '', NULL_NOT_ALLOWED),
                            'idnumber' =>
                                new external_value(\core_user::get_property_type('idnumber'), '在校內的身份識別編號', VALUE_OPTIONAL),
                            'lang' =>
                                new external_value(\core_user::get_property_type('lang'), '使用者預設語系(如: en 或 zh_tw)', VALUE_OPTIONAL, '', NULL_NOT_ALLOWED),
                                                                                                                                                                                                                                                                                                                                                                            'description' =>
                                new external_value(\core_user::get_property_type('description'), '個人自我介紹, no HTML', VALUE_OPTIONAL),
                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                'customfields' => new external_multiple_structure(
                                new external_single_structure(
                                    array(
                                        'type'  => new external_value(PARAM_ALPHANUMEXT, '自定義欄位-名稱(name)'),
                                        'value' => new external_value(PARAM_RAW, '自定義欄位-值(value)')
                                    )
                                ), '使用者-自定義欄位(個人資料欄位)', VALUE_OPTIONAL),
                        )
                    )
                )
            )
        );
    }

    
    public static function update_users($users) {
        global $CFG, $DB, $USER;
        require_once($CFG->dirroot."/user/lib.php");
        require_once($CFG->dirroot."/user/profile/lib.php");                 $context = \context_system::instance();
        require_capability('moodle/user:update', $context);
        self::validate_context($context);

        $params = self::validate_parameters(self::update_users_parameters(), array('users' => $users));

        $transaction = $DB->start_delegated_transaction();

        foreach ($params['users'] as $user) {
                        if (!$existinguser = \core_user::get_user($user['id'])) {
                continue;
            }
                        if ($existinguser->id != $USER->id and is_siteadmin($existinguser) and !is_siteadmin($USER)) {
                continue;
            }
                        if ($existinguser->deleted or is_mnet_remote_user($existinguser) or isguestuser($existinguser->id)) {
                continue;
            }
            user_update_user($user, true, false);
                        if (!empty($user['customfields'])) {

                foreach ($user['customfields'] as $customfield) {
                                                            $user["profile_field_".$customfield['type']] = $customfield['value'];
                }
                profile_save_data((object) $user);
            }

                        \core\event\user_updated::create_from_userid($user['id'])->trigger();

                        if (!empty($user['preferences'])) {
                foreach ($user['preferences'] as $preference) {
                    set_user_preference($preference['type'], $preference['value'], $user['id']);
                }
            }
        }

        $transaction->allow_commit();

        return null;
    }

    
    public static function update_users_returns() {
        return null;
    }

        
    public static function enrol_users_parameters() {
        return new external_function_parameters(
                array(
                    'enrolments' => new external_multiple_structure(
                            new external_single_structure(
                                    array(
                                        'roleid' => new external_value(PARAM_INT, 'Role to assign to the user'),
                                        'userid' => new external_value(PARAM_INT, 'The user that is going to be enrolled'),
                                        'courseid' => new external_value(PARAM_INT, 'The course to enrol the user role in'),
                                        'timestart' => new external_value(PARAM_INT, 'Timestamp when the enrolment start', VALUE_OPTIONAL),
                                        'timeend' => new external_value(PARAM_INT, 'Timestamp when the enrolment end', VALUE_OPTIONAL),
                                        'suspend' => new external_value(PARAM_INT, 'set to 1 to suspend the enrolment', VALUE_OPTIONAL)
                                    )
                            )
                    )
                )
        );
    }

    
    public static function enrol_users($enrolments) {
        global $DB, $CFG;

        require_once($CFG->libdir . '/enrollib.php');

        $params = self::validate_parameters(self::enrol_users_parameters(),
                array('enrolments' => $enrolments));
        $transaction = $DB->start_delegated_transaction();                                                            
                $enrol = enrol_get_plugin('manual');
        if (empty($enrol)) {
            throw new \moodle_exception('manualpluginnotinstalled', 'enrol_manual');
        }

        foreach ($params['enrolments'] as $enrolment) {
                        $context = \context_course::instance($enrolment['courseid'], IGNORE_MISSING);
            self::validate_context($context);

                        require_capability('enrol/manual:enrol', $context);

                        $roles = get_assignable_roles($context);
            if (!array_key_exists($enrolment['roleid'], $roles)) {
                $errorparams = new \stdClass();
                $errorparams->roleid = $enrolment['roleid'];
                $errorparams->courseid = $enrolment['courseid'];
                $errorparams->userid = $enrolment['userid'];
                throw new \moodle_exception('wsusercannotassign', 'enrol_manual', '', $errorparams);
            }

                        $instance = null;
            $enrolinstances = enrol_get_instances($enrolment['courseid'], true);
            foreach ($enrolinstances as $courseenrolinstance) {
              if ($courseenrolinstance->enrol == "manual") {
                  $instance = $courseenrolinstance;
                  break;
              }
            }
            if (empty($instance)) {
              $errorparams = new stdClass();
              $errorparams->courseid = $enrolment['courseid'];
              throw new \moodle_exception('wsnoinstance', 'enrol_manual', $errorparams);
            }

                        if (!$enrol->allow_enrol($instance)) {
                $errorparams = new stdClass();
                $errorparams->roleid = $enrolment['roleid'];
                $errorparams->courseid = $enrolment['courseid'];
                $errorparams->userid = $enrolment['userid'];
                throw new \moodle_exception('wscannotenrol', 'enrol_manual', '', $errorparams);
            }

                        $enrolment['timestart'] = isset($enrolment['timestart']) ? $enrolment['timestart'] : 0;
            $enrolment['timeend'] = isset($enrolment['timeend']) ? $enrolment['timeend'] : 0;
            $enrolment['status'] = (isset($enrolment['suspend']) && !empty($enrolment['suspend'])) ?
                    ENROL_USER_SUSPENDED : ENROL_USER_ACTIVE;

            $enrol->enrol_user($instance, $enrolment['userid'], $enrolment['roleid'],
                    $enrolment['timestart'], $enrolment['timeend'], $enrolment['status']);

        }

        $transaction->allow_commit();
    }

    
    public static function enrol_users_returns() {
        return null;
    }

    
    public static function unenrol_users_parameters() {
        return new external_function_parameters(array(
            'enrolments' => new external_multiple_structure(
                new external_single_structure(
                    array(
                        'userid' => new external_value(PARAM_INT, 'The user that is going to be unenrolled'),
                        'courseid' => new external_value(PARAM_INT, 'The course to unenrol the user from'),
                        'roleid' => new external_value(PARAM_INT, 'The user role', VALUE_OPTIONAL),
                    )
                )
            )
        ));
    }

    
    public static function unenrol_users($enrolments) {
        global $CFG, $DB;
        $params = self::validate_parameters(self::unenrol_users_parameters(), array('enrolments' => $enrolments));
        require_once($CFG->libdir . '/enrollib.php');
        $transaction = $DB->start_delegated_transaction();         $enrol = enrol_get_plugin('manual');
        if (empty($enrol)) {
            throw new \moodle_exception('manualpluginnotinstalled', 'enrol_manual');
        }
debugBreak();
        foreach ($params['enrolments'] as $enrolment) {
            $context = \context_course::instance($enrolment['courseid']);
            self::validate_context($context);
            require_capability('enrol/manual:unenrol', $context);
            $instance = $DB->get_record('enrol', array('courseid' => $enrolment['courseid'], 'enrol' => 'manual'));
            if (!$instance) {
                throw new \moodle_exception('wsnoinstance', 'enrol_manual', $enrolment);
            }
            $user = $DB->get_record('user', array('id' => $enrolment['userid']));
            if (!$user) {
                throw new invalid_parameter_exception('User id not exist: '.$enrolment['userid']);
            }
            if (!$enrol->allow_unenrol($instance)) {
                throw new \moodle_exception('wscannotunenrol', 'enrol_manual', '', $enrolment);
            }
            $enrol->unenrol_user($instance, $enrolment['userid']);
        }
        $transaction->allow_commit();
    }

    
    public static function unenrol_users_returns() {
        return null;
    }
    
        
    public static function create_categories_parameters() {
        return new external_function_parameters(
            array(
                'categories' => new external_multiple_structure(
                        new external_single_structure(
                            array(
                                'name' => new external_value(PARAM_TEXT, '新類別名稱'),                                 'parent' => new external_value(PARAM_INT,
                                        'the parent category id inside which the new category will be created
                                         - set to 0 for a root category',
                                        VALUE_DEFAULT, 0),
                                'idnumber' => new external_value(PARAM_RAW,
                                        'the new category idnumber', VALUE_OPTIONAL),
                                'description' => new external_value(PARAM_RAW,
                                        'the new category description', VALUE_OPTIONAL),
                                'descriptionformat' => new external_format_value('description', VALUE_DEFAULT),
                                'theme' => new external_value(PARAM_THEME,
                                        'the new category theme. This option must be enabled on moodle',
                                        VALUE_OPTIONAL),
                        )
                    )
                )
            )
        );
    }

    
    public static function create_categories($categories) {
        global $CFG, $DB;
        require_once($CFG->libdir . "/coursecatlib.php");

        $params = self::validate_parameters(self::create_categories_parameters(),
                        array('categories' => $categories));

        $transaction = $DB->start_delegated_transaction();

        $createdcategories = array();
        foreach ($params['categories'] as $category) {
            if ($category['parent']) {
                if (!$DB->record_exists('course_categories', array('id' => $category['parent']))) {
                    throw new \moodle_exception('unknowcategory');
                }
                $context = \context_coursecat::instance($category['parent']);
            } else {
                $context = \context_system::instance();
            }
            self::validate_context($context);
            require_capability('moodle/category:manage', $context);

                        external_validate_format($category['descriptionformat']);

            $newcategory = \coursecat::create($category);

            $createdcategories[] = array('id' => $newcategory->id, 'name' => $newcategory->name);
        }

        $transaction->allow_commit();

        return $createdcategories;
    }

    
    public static function create_categories_returns() {
        return new external_multiple_structure(
            new external_single_structure(
                array(
                    'id' => new external_value(PARAM_INT, 'new category id'),
                    'name' => new external_value(PARAM_TEXT, 'new category name'),
                )
            )
        );
    }
    
    
    public static function delete_categories_parameters() {
        return new external_function_parameters(
            array(
                'categories' => new external_multiple_structure(
                    new external_single_structure(
                        array(
                            'id' => new external_value(PARAM_INT, 'category id to delete'),
                            'newparent' => new external_value(PARAM_INT,
                                'the parent category to move the contents to, if specified', VALUE_OPTIONAL),
                            'recursive' => new external_value(PARAM_BOOL, '1: recursively delete all contents inside this
                                category, 0 (default): move contents to newparent or current parent category (except if parent is root)', VALUE_DEFAULT, 0)
                        )
                    )
                )
            )
        );
    }

    
    public static function delete_categories($categories) {
        global $CFG, $DB;
        require_once($CFG->dirroot . "/course/lib.php");
        require_once($CFG->libdir . "/coursecatlib.php");

                $params = self::validate_parameters(self::delete_categories_parameters(), array('categories' => $categories));

        $transaction = $DB->start_delegated_transaction();

        foreach ($params['categories'] as $category) {
            $deletecat = \coursecat::get($category['id'], MUST_EXIST);
            $context = \context_coursecat::instance($deletecat->id);
            require_capability('moodle/category:manage', $context);
            self::validate_context($context);
            self::validate_context(get_category_or_system_context($deletecat->parent));

            if ($category['recursive']) {
                                if ($deletecat->can_delete_full()) {
                    $deletecat->delete_full(false);
                } else {
                    throw new \moodle_exception('youcannotdeletecategory', '', '', $deletecat->get_formatted_name());
                }
            } else {
                                                                if (!empty($category['newparent'])) {
                    $newparentcat = \coursecat::get($category['newparent']);
                } else {
                    $newparentcat = \coursecat::get($deletecat->parent);
                }

                                if (!$newparentcat->id) {
                    throw new \moodle_exception('movecatcontentstoroot');
                }

                self::validate_context(\context_coursecat::instance($newparentcat->id));
                if ($deletecat->can_move_content_to($newparentcat->id)) {
                    $deletecat->delete_move($newparentcat->id, false);
                } else {
                    throw new \moodle_exception('youcannotdeletecategory', '', '', $deletecat->get_formatted_name());
                }
            }
        }

        $transaction->allow_commit();
    }

    
    public static function delete_categories_returns() {
        return null;
    }
    
        
    public static function create_courses_parameters() {
        $courseconfig = get_config('moodlecourse');         return new external_function_parameters(
            array(
                'courses' => new external_multiple_structure(
                    new external_single_structure(
                        array(
                            'fullname' => new external_value(PARAM_TEXT, 'full name'),
                            'shortname' => new external_value(PARAM_TEXT, 'course short name'),
                            'categoryid' => new external_value(PARAM_INT, 'category id'),
                            'idnumber' => new external_value(PARAM_RAW, 'id number', VALUE_OPTIONAL),
                            'summary' => new external_value(PARAM_RAW, 'summary', VALUE_OPTIONAL),
                            'summaryformat' => new external_format_value('summary', VALUE_DEFAULT),
                            'format' => new external_value(PARAM_PLUGIN,
                                    'course format: weeks, topics, social, site,..',
                                    VALUE_DEFAULT, $courseconfig->format),
                            'showgrades' => new external_value(PARAM_INT,
                                    '1 if grades are shown, otherwise 0', VALUE_DEFAULT,
                                    $courseconfig->showgrades),
                            'newsitems' => new external_value(PARAM_INT,
                                    'number of recent items appearing on the course page',
                                    VALUE_DEFAULT, $courseconfig->newsitems),
                            'startdate' => new external_value(PARAM_INT,
                                    'timestamp when the course start', VALUE_OPTIONAL),
                            'numsections' => new external_value(PARAM_INT,
                                    '(deprecated, use courseformatoptions) number of weeks/topics',
                                    VALUE_OPTIONAL),
                            'maxbytes' => new external_value(PARAM_INT,
                                    'largest size of file that can be uploaded into the course',
                                    VALUE_DEFAULT, $courseconfig->maxbytes),
                            'showreports' => new external_value(PARAM_INT,
                                    'are activity report shown (yes = 1, no =0)', VALUE_DEFAULT,
                                    $courseconfig->showreports),
                            'visible' => new external_value(PARAM_INT,
                                    '1: available to student, 0:not available', VALUE_OPTIONAL),
                            'hiddensections' => new external_value(PARAM_INT,
                                    '(deprecated, use courseformatoptions) How the hidden sections in the course are displayed to students',
                                    VALUE_OPTIONAL),
                            'groupmode' => new external_value(PARAM_INT, 'no group, separate, visible',
                                    VALUE_DEFAULT, $courseconfig->groupmode),
                            'groupmodeforce' => new external_value(PARAM_INT, '1: yes, 0: no',
                                    VALUE_DEFAULT, $courseconfig->groupmodeforce),
                            'defaultgroupingid' => new external_value(PARAM_INT, 'default grouping id',
                                    VALUE_DEFAULT, 0),
                            'enablecompletion' => new external_value(PARAM_INT,
                                    'Enabled, control via completion and activity settings. Disabled,
                                        not shown in activity settings.',
                                    VALUE_OPTIONAL),
                            'completionnotify' => new external_value(PARAM_INT,
                                    '1: yes 0: no', VALUE_OPTIONAL),
                            'lang' => new external_value(PARAM_SAFEDIR,
                                    'forced course language', VALUE_OPTIONAL),
                            'forcetheme' => new external_value(PARAM_PLUGIN,
                                    'name of the force theme', VALUE_OPTIONAL),
                            'courseformatoptions' => new external_multiple_structure(
                                new external_single_structure(
                                    array('name' => new external_value(PARAM_ALPHANUMEXT, 'course format option name'),
                                        'value' => new external_value(PARAM_RAW, 'course format option value')
                                )),
                                    'additional options for particular course format', VALUE_OPTIONAL),
                        )
                    ), 'courses to create'
                )
            )
        );
    }

    
    public static function create_courses($courses) {
        global $CFG, $DB;
        require_once($CFG->dirroot . "/course/lib.php");
        require_once($CFG->libdir . '/completionlib.php');

        $params = self::validate_parameters(self::create_courses_parameters(),
                        array('courses' => $courses));

        $availablethemes = \core_component::get_plugin_list('theme');
        $availablelangs = get_string_manager()->get_list_of_translations();

        $transaction = $DB->start_delegated_transaction();

        foreach ($params['courses'] as $course) {

                        $context = \context_coursecat::instance($course['categoryid'], IGNORE_MISSING);
            try {
                self::validate_context($context);
            } catch (Exception $e) {
                $exceptionparam = new stdClass();
                $exceptionparam->message = $e->getMessage();
                $exceptionparam->catid = $course['categoryid'];
                throw new \moodle_exception('errorcatcontextnotvalid', 'webservice', '', $exceptionparam);
            }
            require_capability('moodle/course:create', $context);

                                                
                        

                        $category = $DB->get_record('course_categories', array('id' => $course['categoryid']));
            if (!has_capability('moodle/course:visibility', $context)) {
                $course['visible'] = $category->visible;
            }

                                                                                                            
            $course['category'] = $course['categoryid'];

                        $course['summaryformat'] = external_validate_format($course['summaryformat']);

            if (!empty($course['courseformatoptions'])) {
                foreach ($course['courseformatoptions'] as $option) {
                    $course[$option['name']] = $option['value'];
                }
            }

                        $course['id'] = create_course((object) $course)->id;

            $resultcourses[] = array('id' => $course['id'], 'shortname' => $course['shortname']);
        }

        $transaction->allow_commit();

        return $resultcourses;
    }

    
    public static function create_courses_returns() {
        return new external_multiple_structure(
            new external_single_structure(
                array(
                    'id'       => new external_value(PARAM_INT, 'course id'),
                    'shortname' => new external_value(PARAM_TEXT, 'short name'),
                )
            )
        );
    }
    
    
    public static function delete_courses_parameters() {
        return new external_function_parameters(
            array(
                'courseids' => new external_multiple_structure(new external_value(PARAM_INT, 'course ID')),
            )
        );
    }

    
    public static function delete_courses($courseids) {
        global $CFG, $DB;
        require_once($CFG->dirroot."/course/lib.php");

                $params = self::validate_parameters(self::delete_courses_parameters(), array('courseids'=>$courseids));

        $warnings = array();

        foreach ($params['courseids'] as $courseid) {
            $course = $DB->get_record('course', array('id' => $courseid));

            if ($course === false) {
                $warnings[] = array(
                                'item' => 'course',
                                'itemid' => $courseid,
                                'warningcode' => 'unknowncourseidnumber',
                                'message' => 'Unknown course ID ' . $courseid
                            );
                continue;
            }

                        $coursecontext = \context_course::instance($course->id);
            self::validate_context($coursecontext);

                        if (!can_delete_course($courseid)) {
                $warnings[] = array(
                                'item' => 'course',
                                'itemid' => $courseid,
                                'warningcode' => 'cannotdeletecourse',
                                'message' => 'You do not have the permission to delete this course' . $courseid
                            );
                continue;
            }

            if (delete_course($course, false) === false) {
                $warnings[] = array(
                                'item' => 'course',
                                'itemid' => $courseid,
                                'warningcode' => 'cannotdeletecategorycourse',
                                'message' => 'Course ' . $courseid . ' failed to be deleted'
                            );
                continue;
            }
        }

        fix_course_sortorder();

        return array('warnings' => $warnings);
    }

    
    public static function delete_courses_returns() {
        return new external_single_structure(
            array(
                'warnings' => new \external_warnings()
            )
        );
    }
    
        
    public static function get_grades_parameters() {
        return new external_function_parameters (
            array(
                'courseid' => new external_value(PARAM_INT, '課號(course Id)', VALUE_REQUIRED),
                'userid'   => new external_value(PARAM_INT, '學生ID(user id)', VALUE_DEFAULT, 0)
            )
        );
    }

    
    public static function get_grades($courseid, $userid = 0) {
        global $CFG, $USER;

        $warnings = array();
                $params = self::validate_parameters(self::get_grades_parameters(),
            array(
                'courseid' => $courseid,
                'userid' => $userid)
            );

                $courseid = $params['courseid'];
        $userid   = $params['userid'];

                $course = get_course($courseid);

        $context = \context_course::instance($courseid);
        self::validate_context($context);

                require_capability('gradereport/user:view', $context);

        $user = null;

        if (empty($userid)) {
            require_capability('moodle/grade:viewall', $context);
        } else {
            $user = \core_user::get_user($userid, '*', MUST_EXIST);
            \core_user::require_active_user($user);
        }

        $access = false;

        if (has_capability('moodle/grade:viewall', $context)) {
                        $access = true;
        } else if ($userid == $USER->id and has_capability('moodle/grade:view', $context) and $course->showgrades) {
                        $access = true;
        }

        if (!$access) {
            throw new \moodle_exception('nopermissiontoviewgrades', 'error');
        }

                require_once($CFG->dirroot . '/group/lib.php');
        require_once($CFG->libdir  . '/gradelib.php');
        require_once($CFG->dirroot . '/grade/lib.php');
        require_once($CFG->dirroot . '/grade/report/user/lib.php');

                grade_regrade_final_grades($course->id);

        $gpr = new \grade_plugin_return(
            array(
                'type' => 'report',
                'plugin' => 'user',
                'courseid' => $courseid,
                'userid' => $userid)
            );

        $tables = array();

                if ($user) {
            $report = new \grade_report_user($courseid, $gpr, $context, $userid);
            $report->fill_table();

            $tables[] = array(
                'courseid'      => $courseid,
                'userid'        => $user->id,
                'userfullname'  => fullname($user),
                'maxdepth'      => $report->maxdepth,
                'tabledata'     => $report->tabledata
            );

        } else {
            $defaultgradeshowactiveenrol = !empty($CFG->grade_report_showonlyactiveenrol);
            $showonlyactiveenrol = get_user_preferences('grade_report_showonlyactiveenrol', $defaultgradeshowactiveenrol);
            $showonlyactiveenrol = $showonlyactiveenrol || !has_capability('moodle/course:viewsuspendedusers', $context);

            $gui = new \graded_users_iterator($course);
            $gui->require_active_enrolment($showonlyactiveenrol);
            $gui->init();

            while ($userdata = $gui->next_user()) {
                $currentuser = $userdata->user;
                $report = new \grade_report_user($courseid, $gpr, $context, $currentuser->id);
                $report->fill_table();

                $tables[] = array(
                    'courseid'      => $courseid,
                    'userid'        => $currentuser->id,
                    'userfullname'  => fullname($currentuser),
                    'maxdepth'      => $report->maxdepth,
                    'tabledata'     => $report->tabledata
                );
            }
            $gui->close();
        }

        $result = array();
        $result['tables'] = $tables;
        $result['warnings'] = $warnings;
        return $result;
    }

    
    private static function grades_table_column() {
        return array (
            'class'   => new external_value(PARAM_RAW, 'class'),
            'content' => new external_value(PARAM_RAW, 'cell content'),
            'headers' => new external_value(PARAM_RAW, 'headers')
        );
    }

    
    public static function get_grades_returns() {
        return new external_single_structure(
            array(
                'tables' => new external_multiple_structure(
                    new external_single_structure(
                        array(
                            'courseid' => new external_value(PARAM_INT, '課號(course id)'),
                            'userid'   => new external_value(PARAM_INT, '學生ID(user id)'),
                            'userfullname' => new external_value(PARAM_TEXT, '學生全名(fullname)'),
                            'maxdepth'   => new external_value(PARAM_INT, 'table max depth (needed for printing it)'),
                            'tabledata' => new external_multiple_structure(
                                new external_single_structure(
                                    array(
                                        'itemname' => new external_single_structure(
                                            array (
                                                'class' => new external_value(PARAM_RAW, 'class'),
                                                'colspan' => new external_value(PARAM_INT, 'col span'),
                                                'content'  => new external_value(PARAM_RAW, 'cell content'),
                                                'celltype'  => new external_value(PARAM_RAW, 'cell type'),
                                                'id'  => new external_value(PARAM_ALPHANUMEXT, 'id')
                                            ), 'The item returned data', VALUE_OPTIONAL
                                        ),
                                        'leader' => new external_single_structure(
                                            array (
                                                'class' => new external_value(PARAM_RAW, 'class'),
                                                'rowspan' => new external_value(PARAM_INT, 'row span')
                                            ), 'The item returned data', VALUE_OPTIONAL
                                        ),
                                        'weight' => new external_single_structure(
                                            self::grades_table_column(), 'weight column', VALUE_OPTIONAL
                                        ),
                                        'grade' => new external_single_structure(
                                            self::grades_table_column(), 'grade column', VALUE_OPTIONAL
                                        ),
                                        'range' => new external_single_structure(
                                            self::grades_table_column(), 'range column', VALUE_OPTIONAL
                                        ),
                                        'percentage' => new external_single_structure(
                                            self::grades_table_column(), 'percentage column', VALUE_OPTIONAL
                                        ),
                                        'lettergrade' => new external_single_structure(
                                            self::grades_table_column(), 'lettergrade column', VALUE_OPTIONAL
                                        ),
                                        'rank' => new external_single_structure(
                                            self::grades_table_column(), 'rank column', VALUE_OPTIONAL
                                        ),
                                        'average' => new external_single_structure(
                                            self::grades_table_column(), 'average column', VALUE_OPTIONAL
                                        ),
                                        'feedback' => new external_single_structure(
                                            self::grades_table_column(), 'feedback column', VALUE_OPTIONAL
                                        ),
                                        'contributiontocoursetotal' => new external_single_structure(
                                            self::grades_table_column(), 'contributiontocoursetotal column', VALUE_OPTIONAL
                                        ),
                                    ), 'table'
                                )
                            )
                        )
                    )
                ),
                'warnings' => new \external_warnings()
            )
        );
    }
}
