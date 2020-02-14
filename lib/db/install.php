<?php



defined('MOODLE_INTERNAL') || die();


function xmldb_main_install() {
    global $CFG, $DB, $SITE, $OUTPUT;
        $syscontext = context_system::instance(0, MUST_EXIST, false);
    if ($syscontext->id != SYSCONTEXTID) {
        throw new moodle_exception('generalexceptionmessage', 'error', '', 'Unexpected new system context id!');
    }


        if ($DB->record_exists('course', array())) {
        throw new moodle_exception('generalexceptionmessage', 'error', '', 'Can not create frontpage course, courses already exist.');
    }
    $newsite = new stdClass();
    $newsite->fullname     = 'MoodleSET';
    $newsite->shortname    = 'MoodleSET';
    $newsite->summary      = NULL;
    $newsite->newsitems    = 3;
    $newsite->numsections  = 1;
    $newsite->category     = 0;
    $newsite->format       = 'site';      $newsite->timecreated  = time();
    $newsite->timemodified = $newsite->timecreated;

    if (defined('SITEID')) {
        $newsite->id = SITEID;
        $DB->import_record('course', $newsite);
        $DB->get_manager()->reset_sequence('course');
    } else {
        $newsite->id = $DB->insert_record('course', $newsite);
        define('SITEID', $newsite->id);
    }
            $DB->insert_record('course_format_options', array('courseid' => SITEID, 'format' => 'site',
        'sectionid' => 0, 'name' => 'numsections', 'value' => $newsite->numsections));
    $SITE = get_site();
    if ($newsite->id != $SITE->id) {
        throw new moodle_exception('generalexceptionmessage', 'error', '', 'Unexpected new site course id!');
    }
        context_course::instance($SITE->id);
        $SITE = $DB->get_record('course', array('id'=>$newsite->id), '*', MUST_EXIST);


        if ($DB->record_exists('course_categories', array())) {
        throw new moodle_exception('generalexceptionmessage', 'error', '', 'Can not create default course category, categories already exist.');
    }
    $cat = new stdClass();
    $cat->name         = get_string('miscellaneous');
    $cat->depth        = 1;
    $cat->sortorder    = MAX_COURSES_IN_CATEGORY;
    $cat->timemodified = time();
    $catid = $DB->insert_record('course_categories', $cat);
    $DB->set_field('course_categories', 'path', '/'.$catid, array('id'=>$catid));
        context_coursecat::instance($catid);


        $defaults = array(
        'rolesactive'           => '0',         'auth'                  => 'email',
        'auth_pop3mailbox'      => 'INBOX',
        'enrol_plugins_enabled' => 'manual,guest,self,cohort',
        'theme'                 => 'academi',
        'filter_multilang_converted' => 1,
        'siteidentifier'        => random_string(32).get_host_from_url($CFG->wwwroot),
        'backup_version'        => 2008111700,
        'backup_release'        => '2.0 dev',
        'mnet_dispatcher_mode'  => 'on',
        'enablewebservices'     => 'on',
        'webserviceprotocols'   => 'rest,soap,xmlrpc',
        'sessiontimeout'        => 7200,         'stringfilters'         => '',         'filterall'             => 0,         'texteditors'           => 'atto,tinymce,textarea',
        'antiviruses'           => '',
        'upgrade_minmaxgradestepignored' => 1,         'upgrade_extracreditweightsstepignored' => 1,         'upgrade_calculatedgradeitemsignored' => 1,         'upgrade_letterboundarycourses' => 1,     );
    foreach($defaults as $key => $value) {
        set_config($key, $value);
    }


        $mnethost = new stdClass();
    $mnethost->wwwroot    = $CFG->wwwroot;
    $mnethost->name       = '';
    $mnethost->name       = '';
    $mnethost->public_key = '';

    if (empty($_SERVER['SERVER_ADDR'])) {
                preg_match("@^(?:http[s]?://)?([A-Z0-9\-\.]+).*@i", $CFG->wwwroot, $matches);
        $my_hostname = $matches[1];
        $my_ip       = gethostbyname($my_hostname);          if ($my_ip == $my_hostname) {
            $mnethost->ip_address = 'UNKNOWN';
        } else {
            $mnethost->ip_address = $my_ip;
        }
    } else {
        $mnethost->ip_address = $_SERVER['SERVER_ADDR'];
    }

    $mnetid = $DB->insert_record('mnet_host', $mnethost);
    set_config('mnet_localhost_id', $mnetid);

        $mnet_app = new stdClass();
    $mnet_app->name              = 'moodle';
    $mnet_app->display_name      = 'Moodle';
    $mnet_app->xmlrpc_server_url = '/mnet/xmlrpc/server.php';
    $mnet_app->sso_land_url      = '/auth/mnet/land.php';
    $mnet_app->sso_jump_url      = '/auth/mnet/jump.php';
    $moodleapplicationid = $DB->insert_record('mnet_application', $mnet_app);
    
    $mnet_app = new stdClass();
    $mnet_app->name              = 'moodleset';
    $mnet_app->display_name      = 'MoodleSET';
    $mnet_app->xmlrpc_server_url = '/mnet/xmlrpc/server.php';
    $mnet_app->sso_land_url      = '/auth/mnet/land.php';
    $mnet_app->sso_jump_url      = '/auth/mnet/jump.php';
    $DB->insert_record('mnet_application', $mnet_app);

    $mnet_app = new stdClass();
    $mnet_app->name              = 'mahara';
    $mnet_app->display_name      = 'Mahara';
    $mnet_app->xmlrpc_server_url = '/api/xmlrpc/server.php';
    $mnet_app->sso_land_url      = '/auth/xmlrpc/land.php';
    $mnet_app->sso_jump_url      = '/auth/xmlrpc/jump.php';
    $DB->insert_record('mnet_application', $mnet_app);

        $mnetallhosts                     = new stdClass();
    $mnetallhosts->wwwroot            = '';
    $mnetallhosts->ip_address         = '';
    $mnetallhosts->public_key         = '';
    $mnetallhosts->public_key_expires = 0;
    $mnetallhosts->last_connect_time  = 0;
    $mnetallhosts->last_log_id        = 0;
    $mnetallhosts->deleted            = 0;
    $mnetallhosts->name               = 'All Hosts';
    $mnetallhosts->applicationid      = $moodleapplicationid;
    $mnetallhosts->id                 = $DB->insert_record('mnet_host', $mnetallhosts, true);
    set_config('mnet_all_hosts_id', $mnetallhosts->id);

        if ($DB->record_exists('user', array())) {
        throw new moodle_exception('generalexceptionmessage', 'error', '', 'Can not create default users, users already exist.');
    }
    $guest = new stdClass();
    $guest->auth        = 'manual';
    $guest->username    = 'guest';
    $guest->password    = hash_internal_user_password('guest');
    $guest->firstname   = get_string('guestuser');
    $guest->lastname    = ' ';
    $guest->email       = 'root@localhost';
    $guest->description = get_string('guestuserinfo');
    $guest->mnethostid  = $CFG->mnet_localhost_id;
    $guest->confirmed   = 1;
    $guest->lang        = $CFG->lang;
    $guest->timemodified= time();
    $guest->id = $DB->insert_record('user', $guest);
    if ($guest->id != 1) {
        echo $OUTPUT->notification('Unexpected id generated for the Guest account. Your database configuration or clustering setup may not be fully supported', 'notifyproblem');
    }
        set_config('siteguest', $guest->id);
        context_user::instance($guest->id);


        $admin = new stdClass();
    $admin->auth         = 'manual';
    $admin->firstname    = get_string('admin');
    $admin->lastname     = get_string('user');
    $admin->username     = 'admin';
    $admin->password     = 'adminsetuppending';
    $admin->email        = '';
    $admin->confirmed    = 1;
    $admin->mnethostid   = $CFG->mnet_localhost_id;
    $admin->lang         = $CFG->lang;
    $admin->maildisplay  = 1;
    $admin->timemodified = time();
    $admin->lastip       = CLI_SCRIPT ? '0.0.0.0' : getremoteaddr();     $admin->id = $DB->insert_record('user', $admin);

    if ($admin->id != 2) {
        echo $OUTPUT->notification('Unexpected id generated for the Admin account. Your database configuration or clustering setup may not be fully supported', 'notifyproblem');
    }
    if ($admin->id != ($guest->id + 1)) {
        echo $OUTPUT->notification('Nonconsecutive id generated for the Admin account. Your database configuration or clustering setup may not be fully supported.', 'notifyproblem');
    }

        set_config('siteadmins', $admin->id);
        context_user::instance($admin->id);

        $modset = new stdClass();
    $modset->auth         = 'manual';
    $modset->firstname    = 'WebService';
    $modset->lastname     = 'MoodleSET';
    $modset->username     = 'moodlesetws';
    $modset->password     = 'adminsetuppending';
    $modset->email        = 'moodleset@click-ap.com';
    $modset->confirmed    = 1;
    $modset->mnethostid   = $CFG->mnet_localhost_id;
    $modset->lang         = 'en';
    $modset->maildisplay  = 1;
    $modset->timemodified = time();
    $modset->lastip       = CLI_SCRIPT ? '0.0.0.0' : getremoteaddr();     $modset->id = $DB->insert_record('user', $modset);

    if ($modset->id != 3) {
        echo $OUTPUT->notification('Unexpected id generated for the MoodleSET-API account. Your database configuration or clustering setup may not be fully supported', 'notifyproblem');
    }
    if ($modset->id != ($admin->id + 1)) {
        echo $OUTPUT->notification('Nonconsecutive id generated for the MoodleSET-API account. Your database configuration or clustering setup may not be fully supported.', 'notifyproblem');
    }
        set_config('modsetws', $modset->id);
        context_user::instance($modset->id);

        $managerrole        = create_role('', 'manager', '', 'manager');
    $coursecreatorrole  = create_role('', 'coursecreator', '', 'coursecreator');
    $modsetwsrrole      = create_role('MoodleSET', 'modsetapi', 'The MoodleSET role for WebService, who can create new courses, enrol/unrol student by API.', 'modsetws');
    $departmanagerrole  = create_role('', 'departmentmanager', '', 'departmentmanager');
    $departassistantrole  = create_role('', 'departmentassistant', '', 'departmentassistant');
    $editteacherrole    = create_role('', 'editingteacher', '', 'editingteacher');
        $assistantrole      = create_role('', 'teacherassistant', '', 'teacherassistant');
    $studentrole        = create_role('', 'student', '', 'student');
    $auditorrole        = create_role('', 'auditor', '', 'auditor');
    $guestrole          = create_role('', 'guest', '', 'guest');
    $userrole           = create_role('', 'user', '', 'user');
    $frontpagerole      = create_role('', 'frontpage', '', 'frontpage');

        update_capabilities('moodle');

        foreach ($DB->get_records('role') as $role) {
        foreach (array('assign', 'override', 'switch') as $type) {
            $function = 'allow_'.$type;
            $allows = get_default_role_archetype_allows($type, $role->archetype);
            foreach ($allows as $allowid) {
                $function($role->id, $allowid);
            }
        }
    }

        set_role_contextlevels($managerrole,        get_default_contextlevels('manager'));
    set_role_contextlevels($coursecreatorrole,  get_default_contextlevels('coursecreator'));
    set_role_contextlevels($modsetwsrrole,      get_default_contextlevels('modsetws'));
    set_role_contextlevels($departmanagerrole,  get_default_contextlevels('departmentmanager'));
    set_role_contextlevels($departassistantrole,  get_default_contextlevels('departmentassistant'));
    set_role_contextlevels($editteacherrole,    get_default_contextlevels('editingteacher'));
        set_role_contextlevels($assistantrole,      get_default_contextlevels('teacherassistant'));
    set_role_contextlevels($studentrole,        get_default_contextlevels('student'));
    set_role_contextlevels($auditorrole,        get_default_contextlevels('auditor'));
    set_role_contextlevels($guestrole,          get_default_contextlevels('guest'));
    set_role_contextlevels($userrole,           get_default_contextlevels('user'));

        $modsetrole = get_modset_role();
    role_assign($modsetrole->id, $modset->id, $syscontext->id);
    
        
    
        set_config('themerev', time());
    set_config('jsrev', time());

        set_config('gdversion', 2);

        require_once($CFG->libdir . '/licenselib.php');
    license_manager::install_licenses();

        if ($DB->record_exists('my_pages', array())) {
        throw new moodle_exception('generalexceptionmessage', 'error', '', 'Can not create default profile pages, records already exist.');
    }
    $mypage = new stdClass();
    $mypage->userid = NULL;
    $mypage->name = '__default';
    $mypage->private = 0;
    $mypage->sortorder  = 0;
    $DB->insert_record('my_pages', $mypage);
    $mypage->private = 1;
    $DB->insert_record('my_pages', $mypage);

        set_config('multichoice_sortorder', 1, 'question');
    set_config('truefalse_sortorder', 2, 'question');
    set_config('match_sortorder', 3, 'question');
    set_config('shortanswer_sortorder', 4, 'question');
    set_config('numerical_sortorder', 5, 'question');
    set_config('essay_sortorder', 6, 'question');

    require_once($CFG->libdir . '/db/upgradelib.php');
    make_default_scale();
    make_competence_scale();
    
            }
