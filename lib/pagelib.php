<?php



defined('MOODLE_INTERNAL') || die();


class moodle_page {

    
    const STATE_BEFORE_HEADER = 0;

    
    const STATE_PRINTING_HEADER = 1;

    
    const STATE_IN_BODY = 2;

    
    const STATE_DONE = 3;

    
    protected $_state = self::STATE_BEFORE_HEADER;

    
    protected $_course = null;

    
    protected $_cm = null;

    
    protected $_module = null;

    
    protected $_context = null;

    
    protected $_categories = null;

    
    protected $_bodyclasses = array();

    
    protected $_title = '';

    
    protected $_heading = '';

    
    protected $_pagetype = null;

    
    protected $_pagelayout = 'base';

    
    protected $_layout_options = null;

    
    protected $_subpage = '';

    
    protected $_docspath = null;

    
    protected $_legacyclass = null;

    
    protected $_url = null;

    
    protected $_alternateversions = array();

    
    protected $_blocks = null;

    
    protected $_requires = null;

    
    protected $_blockseditingcap = 'moodle/site:manageblocks';

    
    protected $_block_actions_done = false;

    
    protected $_othereditingcaps = array();

    
    protected $_cacheable = true;

    
    protected $_focuscontrol = '';

    
    protected $_button = '';

    
    protected $_theme = null;

    
    protected $_navigation = null;

    
    protected $_settingsnav = null;

    
    protected $_navbar = null;

    
    protected $_headingmenu = null;

    
    protected $_wherethemewasinitialised = null;

    
    protected $_opencontainers;

    
    protected $_periodicrefreshdelay = null;

    
    protected $_legacybrowsers = array('MSIE' => 6.0);

    
    protected $_devicetypeinuse = null;

    
    protected $_https_login_required = false;

    
    protected $_popup_notification_allowed = true;

            
    
    protected function magic_get_state() {
        return $this->_state;
    }

    
    protected function magic_get_headerprinted() {
        return $this->_state >= self::STATE_IN_BODY;
    }

    
    protected function magic_get_course() {
        global $SITE;
        if (is_null($this->_course)) {
            return $SITE;
        }
        return $this->_course;
    }

    
    protected function magic_get_cm() {
        return $this->_cm;
    }

    
    protected function magic_get_activityrecord() {
        if (is_null($this->_module) && !is_null($this->_cm)) {
            $this->load_activity_record();
        }
        return $this->_module;
    }

    
    protected function magic_get_activityname() {
        if (is_null($this->_cm)) {
            return null;
        }
        return $this->_cm->modname;
    }

    
    protected function magic_get_category() {
        $this->ensure_category_loaded();
        if (!empty($this->_categories)) {
            return reset($this->_categories);
        } else {
            return null;
        }
    }

    
    protected function magic_get_categories() {
        $this->ensure_categories_loaded();
        return $this->_categories;
    }

    
    protected function magic_get_context() {
        global $CFG;
        if (is_null($this->_context)) {
            if (CLI_SCRIPT or NO_MOODLE_COOKIES) {
                                            } else if (AJAX_SCRIPT && $CFG->debugdeveloper) {
                                throw new coding_exception('$PAGE->context was not set. You may have forgotten '
                    .'to call require_login() or $PAGE->set_context()');
            } else {
                debugging('Coding problem: $PAGE->context was not set. You may have forgotten '
                    .'to call require_login() or $PAGE->set_context(). The page may not display '
                    .'correctly as a result');
            }
            $this->_context = context_system::instance();
        }
        return $this->_context;
    }

    
    protected function magic_get_pagetype() {
        global $CFG;
        if (is_null($this->_pagetype) || isset($CFG->pagepath)) {
            $this->initialise_default_pagetype();
        }
        return $this->_pagetype;
    }

    
    protected function magic_get_bodyid() {
        return 'page-'.$this->pagetype;
    }

    
    protected function magic_get_pagelayout() {
        return $this->_pagelayout;
    }

    
    protected function magic_get_layout_options() {
        if (!is_array($this->_layout_options)) {
            $this->_layout_options = $this->_theme->pagelayout_options($this->pagelayout);
        }
        return $this->_layout_options;
    }

    
    protected function magic_get_subpage() {
        return $this->_subpage;
    }

    
    protected function magic_get_bodyclasses() {
        return implode(' ', array_keys($this->_bodyclasses));
    }

    
    protected function magic_get_title() {
        return $this->_title;
    }

    
    protected function magic_get_heading() {
        return $this->_heading;
    }

    
    protected function magic_get_headingmenu() {
        return $this->_headingmenu;
    }

    
    protected function magic_get_docspath() {
        if (is_string($this->_docspath)) {
            return $this->_docspath;
        } else {
            return str_replace('-', '/', $this->pagetype);
        }
    }

    
    protected function magic_get_url() {
        global $FULLME;
        if (is_null($this->_url)) {
            debugging('This page did not call $PAGE->set_url(...). Using '.s($FULLME), DEBUG_DEVELOPER);
            $this->_url = new moodle_url($FULLME);
                        $this->_url->remove_params('sesskey');
        }
        return new moodle_url($this->_url);     }

    
    protected function magic_get_alternateversions() {
        return $this->_alternateversions;
    }

    
    protected function magic_get_blocks() {
        global $CFG;
        if (is_null($this->_blocks)) {
            if (!empty($CFG->blockmanagerclass)) {
                if (!empty($CFG->blockmanagerclassfile)) {
                    require_once($CFG->blockmanagerclassfile);
                }
                $classname = $CFG->blockmanagerclass;
            } else {
                $classname = 'block_manager';
            }
            $this->_blocks = new $classname($this);
        }
        return $this->_blocks;
    }

    
    protected function magic_get_requires() {
        if (is_null($this->_requires)) {
            $this->_requires = new page_requirements_manager();
        }
        return $this->_requires;
    }

    
    protected function magic_get_cacheable() {
        return $this->_cacheable;
    }

    
    protected function magic_get_focuscontrol() {
        return $this->_focuscontrol;
    }

    
    protected function magic_get_button() {
        return $this->_button;
    }

    
    protected function magic_get_theme() {
        if (is_null($this->_theme)) {
            $this->initialise_theme_and_output();
        }
        return $this->_theme;
    }

    
    protected function magic_get_blockmanipulations() {
        if (!right_to_left()) {
            return false;
        }
        if (is_null($this->_theme)) {
            $this->initialise_theme_and_output();
        }
        return $this->_theme->blockrtlmanipulations;
    }

    
    protected function magic_get_devicetypeinuse() {
        if (empty($this->_devicetypeinuse)) {
            $this->_devicetypeinuse = core_useragent::get_user_device_type();
        }
        return $this->_devicetypeinuse;
    }

    
    protected function magic_get_periodicrefreshdelay() {
        return $this->_periodicrefreshdelay;
    }

    
    protected function magic_get_opencontainers() {
        if (is_null($this->_opencontainers)) {
            $this->_opencontainers = new xhtml_container_stack();
        }
        return $this->_opencontainers;
    }

    
    protected function magic_get_navigation() {
        if ($this->_navigation === null) {
            $this->_navigation = new global_navigation($this);
        }
        return $this->_navigation;
    }

    
    protected function magic_get_navbar() {
        if ($this->_navbar === null) {
            $this->_navbar = new navbar($this);
        }
        return $this->_navbar;
    }

    
    protected function magic_get_settingsnav() {
        if ($this->_settingsnav === null) {
            $this->_settingsnav = new settings_navigation($this);
            $this->_settingsnav->initialise();
        }
        return $this->_settingsnav;
    }

    
    protected function magic_get_requestip() {
        return getremoteaddr(null);
    }

    
    protected function magic_get_requestorigin() {
        if (class_exists('restore_controller', false) && restore_controller::is_executing()) {
            return 'restore';
        }

        if (WS_SERVER) {
            return 'ws';
        }

        if (CLI_SCRIPT) {
            return 'cli';
        }

        return 'web';
    }

    
    public function __get($name) {
        $getmethod = 'magic_get_' . $name;
        if (method_exists($this, $getmethod)) {
            return $this->$getmethod();
        } else {
            throw new coding_exception('Unknown property ' . $name . ' of $PAGE.');
        }
    }

    
    public function __set($name, $value) {
        if (method_exists($this, 'set_' . $name)) {
            throw new coding_exception('Invalid attempt to modify page object', "Use \$PAGE->set_$name() instead.");
        } else {
            throw new coding_exception('Invalid attempt to modify page object', "Unknown property $name");
        }
    }

    
    
    public function get_renderer($component, $subtype = null, $target = null) {
        if ($this->pagelayout === 'maintenance') {
                                                $target = RENDERER_TARGET_MAINTENANCE;
        }
        return $this->magic_get_theme()->get_renderer($this, $component, $subtype, $target);
    }

    
    public function has_navbar() {
        if ($this->_navbar === null) {
            $this->_navbar = new navbar($this);
        }
        return $this->_navbar->has_items();
    }

    
    public function start_collecting_javascript_requirements() {
        global $CFG;
        require_once($CFG->libdir.'/outputfragmentrequirementslib.php');

                if (get_class($this->_requires) == 'fragment_requirements_manager') {
            throw new coding_exception('JavaScript collection has already been started.');
        }
                        if (!empty($this->_wherethemewasinitialised)) {
                        $this->_requires = new fragment_requirements_manager();
        } else {
            throw new coding_exception('$OUTPUT->header() needs to be called before collecting JavaScript requirements.');
        }
    }

    
    public function user_is_editing() {
        global $USER;
        return !empty($USER->editing) && $this->user_allowed_editing();
    }

    
    public function user_can_edit_blocks() {
        return has_capability($this->_blockseditingcap, $this->_context);
    }

    
    public function user_allowed_editing() {
        return has_any_capability($this->all_editing_caps(), $this->_context);
    }

    
    public function debug_summary() {
        $summary = '';
        $summary .= 'General type: ' . $this->pagelayout . '. ';
        if (!during_initial_install()) {
            $summary .= 'Context ' . $this->context->get_context_name() . ' (context id ' . $this->_context->id . '). ';
        }
        $summary .= 'Page type ' . $this->pagetype .  '. ';
        if ($this->subpage) {
            $summary .= 'Sub-page ' . $this->subpage .  '. ';
        }
        return $summary;
    }

    
    
    public function set_state($state) {
        if ($state != $this->_state + 1 || $state > self::STATE_DONE) {
            throw new coding_exception('Invalid state passed to moodle_page::set_state. We are in state ' .
                    $this->_state . ' and state ' . $state . ' was requested.');
        }

        if ($state == self::STATE_PRINTING_HEADER) {
            $this->starting_output();
        }

        $this->_state = $state;
    }

    
    public function set_course($course) {
        global $COURSE, $PAGE, $CFG, $SITE;

        if (empty($course->id)) {
            throw new coding_exception('$course passed to moodle_page::set_course does not look like a proper course object.');
        }

        $this->ensure_theme_not_set();

        if (!empty($this->_course->id) && $this->_course->id != $course->id) {
            $this->_categories = null;
        }

        $this->_course = clone($course);

        if ($this === $PAGE) {
            $COURSE = $this->_course;
            moodle_setlocale();
        }

        if (!$this->_context) {
            $this->set_context(context_course::instance($this->_course->id));
        }

                if ($this->_course->id != $SITE->id) {
            require_once($CFG->dirroot.'/course/lib.php');
            $courseformat = course_get_format($this->_course);
            $this->add_body_class('format-'. $courseformat->get_format());
            $courseformat->page_set_course($this);
        } else {
            $this->add_body_class('format-site');
        }
    }

    
    public function set_context($context) {
        if ($context === null) {
                                    if (!$this->_context) {
                $this->_context = context_system::instance();
            }
            return;
        }
                if (isset($this->_context) && $context->id !== $this->_context->id) {
            $current = $this->_context->contextlevel;
            if ($current == CONTEXT_SYSTEM or $current == CONTEXT_COURSE) {
                            } else if ($current == CONTEXT_MODULE and ($parentcontext = $context->get_parent_context()) and
                $this->_context->id == $parentcontext->id) {
                            } else {
                                                debugging("Coding problem: unsupported modification of PAGE->context from {$current} to {$context->contextlevel}");
            }
        }

        $this->_context = $context;
    }

    
    public function set_cm($cm, $course = null, $module = null) {
        global $DB, $CFG, $SITE;

        if (!isset($cm->id) || !isset($cm->course)) {
            throw new coding_exception('Invalid $cm. It has to be instance of cm_info or record from the course_modules table.');
        }

        if (!$this->_course || $this->_course->id != $cm->course) {
            if (!$course) {
                $course = $DB->get_record('course', array('id' => $cm->course), '*', MUST_EXIST);
            }
            if ($course->id != $cm->course) {
                throw new coding_exception('The course you passed to $PAGE->set_cm does not correspond to the $cm.');
            }
            $this->set_course($course);
        }

                if (!($cm instanceof cm_info)) {
            $modinfo = get_fast_modinfo($this->_course);
            $cm = $modinfo->get_cm($cm->id);
        }
        $this->_cm = $cm;

                        if (empty($this->_context) or $this->_context->contextlevel != CONTEXT_BLOCK) {
            $context = context_module::instance($cm->id);
            $this->set_context($context);
        }

        if ($module) {
            $this->set_activity_record($module);
        }

                if ($this->_course->id != $SITE->id) {
            require_once($CFG->dirroot.'/course/lib.php');
            course_get_format($this->_course)->page_set_cm($this);
        }
    }

    
    public function set_activity_record($module) {
        if (is_null($this->_cm)) {
            throw new coding_exception('You cannot call $PAGE->set_activity_record until after $PAGE->cm has been set.');
        }
        if ($module->id != $this->_cm->instance || $module->course != $this->_course->id) {
            throw new coding_exception('The activity record does not seem to correspond to the cm that has been set.');
        }
        $this->_module = $module;
    }

    
    public function set_pagetype($pagetype) {
        $this->_pagetype = $pagetype;
    }

    
    public function set_pagelayout($pagelayout) {
        global $SESSION;

        if (!empty($SESSION->forcepagelayout)) {
            $this->_pagelayout = $SESSION->forcepagelayout;
        } else {
                                                $this->_pagelayout = $pagelayout;
        }
    }

    
    public function set_subpage($subpage) {
        if (empty($subpage)) {
            $this->_subpage = '';
        } else {
            $this->_subpage = $subpage;
        }
    }

    
    public function add_body_class($class) {
        if ($this->_state > self::STATE_BEFORE_HEADER) {
            throw new coding_exception('Cannot call moodle_page::add_body_class after output has been started.');
        }
        $this->_bodyclasses[$class] = 1;
    }

    
    public function add_body_classes($classes) {
        foreach ($classes as $class) {
            $this->add_body_class($class);
        }
    }

    
    public function set_title($title) {
        $title = format_string($title);
        $title = strip_tags($title);
        $title = str_replace('"', '&quot;', $title);
        $this->_title = $title;
    }

    
    public function set_heading($heading) {
        $this->_heading = format_string($heading);
    }

    
    public function set_headingmenu($menu) {
        $this->_headingmenu = $menu;
    }

    
    public function set_category_by_id($categoryid) {
        global $SITE;
        if (!is_null($this->_course)) {
            throw new coding_exception('Course has already been set. You cannot change the category now.');
        }
        if (is_array($this->_categories)) {
            throw new coding_exception('Course category has already been set. You cannot to change it now.');
        }
        $this->ensure_theme_not_set();
        $this->set_course($SITE);
        $this->load_category($categoryid);
        $this->set_context(context_coursecat::instance($categoryid));
    }

    
    public function set_docs_path($path) {
        $this->_docspath = $path;
    }

    
    public function set_url($url, array $params = null) {
        global $CFG;

        if (is_string($url) && strpos($url, 'http') !== 0) {
            if (strpos($url, '/') === 0) {
                                $url = $CFG->httpswwwroot . $url;
            } else {
                throw new coding_exception('Invalid parameter $url, has to be full url or in shortened form starting with /.');
            }
        }

        $this->_url = new moodle_url($url, $params);

        $fullurl = $this->_url->out_omit_querystring();
        if (strpos($fullurl, "$CFG->httpswwwroot/") !== 0) {
            debugging('Most probably incorrect set_page() url argument, it does not match the httpswwwroot!');
        }
        $shorturl = str_replace("$CFG->httpswwwroot/", '', $fullurl);

        if (is_null($this->_pagetype)) {
            $this->initialise_default_pagetype($shorturl);
        }
    }

    
    public function ensure_param_not_in_url($param) {
        $this->_url->remove_params($param);
    }

    
    public function add_alternate_version($title, $url, $mimetype) {
        if ($this->_state > self::STATE_BEFORE_HEADER) {
            throw new coding_exception('Cannot call moodle_page::add_alternate_version after output has been started.');
        }
        $alt = new stdClass;
        $alt->title = $title;
        $alt->url = $url;
        $this->_alternateversions[$mimetype] = $alt;
    }

    
    public function set_focuscontrol($controlid) {
        $this->_focuscontrol = $controlid;
    }

    
    public function set_button($html) {
        $this->_button = $html;
    }

    
    public function set_blocks_editing_capability($capability) {
        $this->_blockseditingcap = $capability;
    }

    
    public function set_other_editing_capability($capability) {
        if (is_array($capability)) {
            $this->_othereditingcaps = array_unique($this->_othereditingcaps + $capability);
        } else {
            $this->_othereditingcaps[] = $capability;
        }
    }

    
    public function set_cacheable($cacheable) {
        $this->_cacheable = $cacheable;
    }

    
    public function set_periodic_refresh_delay($delay = null) {
        if ($this->_state > self::STATE_BEFORE_HEADER) {
            throw new coding_exception('You cannot set a periodic refresh delay after the header has been printed');
        }
        if ($delay === null) {
            $this->_periodicrefreshdelay = null;
        } else if (is_int($delay)) {
            $this->_periodicrefreshdelay = $delay;
        }
    }

    
    public function force_theme($themename) {
        $this->ensure_theme_not_set();
        $this->_theme = theme_config::load($themename);
    }

    
    public function reload_theme() {
        if (!is_null($this->_theme)) {
            $this->_theme = theme_config::load($this->_theme->name);
        }
    }

    
    public function https_required() {
        global $CFG;

        if (!is_null($this->_url)) {
            throw new coding_exception('https_required() must be used before setting page url!');
        }

        $this->ensure_theme_not_set();

        $this->_https_login_required = true;

        if (!empty($CFG->loginhttps)) {
            $CFG->httpswwwroot = str_replace('http:', 'https:', $CFG->wwwroot);
        } else {
            $CFG->httpswwwroot = $CFG->wwwroot;
        }
    }

    
    public function verify_https_required() {
        global $CFG, $FULLME;

        if (is_null($this->_url)) {
            throw new coding_exception('verify_https_required() must be called after setting page url!');
        }

        if (!$this->_https_login_required) {
            throw new coding_exception('verify_https_required() must be called only after https_required()!');
        }

        if (empty($CFG->loginhttps)) {
                        return;
        }

        if (strpos($this->_url, 'https://')) {
                        throw new coding_exception('Invalid page url. It must start with https:// for pages that set https_required()!');
        }

        if (!empty($CFG->sslproxy)) {
                        return;
        }

                                if (strpos($FULLME, 'https:') !== 0) {
                                    redirect($this->_url);
        }
    }

        
    
    protected function starting_output() {
        global $CFG;

        if (!during_initial_install()) {
            $this->blocks->load_blocks();
            if (empty($this->_block_actions_done)) {
                $this->_block_actions_done = true;
                if ($this->blocks->process_url_actions($this)) {
                    redirect($this->url->out(false));
                }
            }
            $this->blocks->create_all_block_instances();
        }

                if (!empty($CFG->maintenance_enabled)) {
            $this->set_button('<a href="' . $CFG->wwwroot . '/' . $CFG->admin .
                    '/settings.php?section=maintenancemode">' . get_string('maintenancemode', 'admin') .
                    '</a> ' . $this->button);

            $title = $this->title;
            if ($title) {
                $title .= ' - ';
            }
            $this->set_title($title . get_string('maintenancemode', 'admin'));
        } else {
                        message_popup_window();
        }

        $this->initialise_standard_body_classes();
    }

    
    public function initialise_theme_and_output() {
        global $OUTPUT, $PAGE, $SITE, $CFG;

        if (!empty($this->_wherethemewasinitialised)) {
            return;
        }

        if (!during_initial_install()) {
                        $this->magic_get_context();
        }

        if (!$this->_course && !during_initial_install()) {
            $this->set_course($SITE);
        }

        if (is_null($this->_theme)) {
            $themename = $this->resolve_theme();
            $this->_theme = theme_config::load($themename);
        }

        $this->_theme->setup_blocks($this->pagelayout, $this->blocks);
        if ($this->_theme->enable_dock && !empty($CFG->allowblockstodock)) {
            $this->requires->strings_for_js(array('addtodock', 'undockitem', 'dockblock', 'undockblock', 'undockall', 'hidedockpanel', 'hidepanel'), 'block');
            $this->requires->string_for_js('thisdirectionvertical', 'langconfig');
            $this->requires->yui_module('moodle-core-dock-loader', 'M.core.dock.loader.initLoader');
        }

        if ($this === $PAGE) {
            $target = null;
            if ($this->pagelayout === 'maintenance') {
                                                                $target = RENDERER_TARGET_MAINTENANCE;
            }
            $OUTPUT = $this->get_renderer('core', null, $target);
        }

        $this->_wherethemewasinitialised = debug_backtrace();
    }

    
    public function reset_theme_and_output() {
        global $COURSE, $SITE;

        $COURSE = clone($SITE);
        $this->_theme = null;
        $this->_wherethemewasinitialised = null;
        $this->_course = null;
        $this->_cm = null;
        $this->_module = null;
        $this->_context = null;
    }

    
    protected function resolve_theme() {
        global $CFG, $USER, $SESSION;
        if (empty($CFG->themeorder)) {
            $themeorder = array('course', 'category', 'session', 'user', 'site');
        } else {
            $themeorder = $CFG->themeorder;
                        $themeorder[] = 'site';
        }
        
        $mnetpeertheme = '';
        if (isloggedin() and isset($CFG->mnet_localhost_id) and $USER->mnethostid != $CFG->mnet_localhost_id) {
            require_once($CFG->dirroot.'/mnet/peer.php');
            $mnetpeer = new mnet_peer();
            $mnetpeer->set_id($USER->mnethostid);
            if ($mnetpeer->force_theme == 1 && $mnetpeer->theme != '') {
                $mnetpeertheme = $mnetpeer->theme;
            }
        }

        $devicetheme = core_useragent::get_device_type_theme($this->devicetypeinuse);

                $hascustomdevicetheme = core_useragent::DEVICETYPE_DEFAULT != $this->devicetypeinuse && !empty($devicetheme);
        
        $extrasql = "id IN (SELECT userid FROM {role_assignments} a
                         INNER JOIN {context} b ON a.contextid=b.id
 WHERE (b.contextlevel=50 AND a.roleid in (:ex_courserole0_roleid)) OR (b.contextlevel=40 AND a.roleid in (:ex_sysrole0_roleid)) ) AND username LIKE :ex_text0 ";
        if(isset($USER->username) && !is_siteadmin($USER)){
            error_log("check username");
                            require_once("$CFG->libdir/accesslib.php");
                $sysroles = array('departmentmanager', 'departmentassistant', 'coursecreator');
                $sysroleids = array();
                
                foreach($sysroles as $r){
                    $roles = get_archetype_roles($r);            // by YCJ
                    if(!empty($roles)){
                    	//$roles = array_column($roles, 'id');     // by YCJ only in PHP 7
                      $roles = array_map(function($o){return $o->id;}, $roles);    // PHP < 7.0
                      $sysroleids = array_merge($sysroleids, $roles);
                    }

                }
                $teachroles = array('editingteacher', 'teacher', 'teacherassistant');
                $roleids = array();
                foreach($teachroles as $r){
                    $roles = get_archetype_roles($r);            // by YCJ
                    if(!empty($roles)){
                    	//$roles = array_column($roles, 'id');     // by YCJ only in PHP > 7
                      $roles = array_map(function($o){return $o->id;}, $roles);    // PHP < 7.0 
                      $roleids = array_merge($roleids, $roles);
                    }
                }                

                $params = array('ex_courserole0_roleid' => implode(',', $roleids) , 'ex_text0' => $USER->username, 'ex_sysrole0_roleid' => implode(',', $sysroleids));
                $users = get_users(true, '', false, null, "", '', '', '', 1, '*', $extrasql, $params);
              if(empty($users)){
                    $devicetheme = $hascustomdevicetheme = 'sn'.'ap';
              }
                    }

        foreach ($themeorder as $themetype) {

            switch ($themetype) {
                case 'course':
                    if (!empty($CFG->allowcoursethemes) && !empty($this->_course->theme) && !$hascustomdevicetheme) {
                        return $this->_course->theme;
                    }
                break;

                case 'category':
                    if (!empty($CFG->allowcategorythemes) && !$hascustomdevicetheme) {
                        $categories = $this->categories;
                        foreach ($categories as $category) {
                            if (!empty($category->theme)) {
                                return $category->theme;
                            }
                        }
                    }
                break;

                case 'session':
                    if (!empty($SESSION->theme)) {
                        return $SESSION->theme;
                    }
                break;

                case 'user':
                    if (!empty($CFG->allowuserthemes) && !empty($USER->theme) && !$hascustomdevicetheme) {
                        if ($mnetpeertheme) {
                            return $mnetpeertheme;
                        } else {
                            return $USER->theme;
                        }
                    }
                break;

                case 'site':
                    if ($mnetpeertheme) {
                        return $mnetpeertheme;
                    }
                                        if (!empty($devicetheme)) {
                        return $devicetheme;
                    }
                                        $devicetheme = core_useragent::get_device_type_theme(core_useragent::DEVICETYPE_DEFAULT);
                    if (!empty($devicetheme)) {
                        return $devicetheme;
                    }
                                        return theme_config::DEFAULT_THEME;
            }
        }

                debugging('Error resolving the theme to use for this page.', DEBUG_DEVELOPER);
        return theme_config::DEFAULT_THEME;
    }


    
    protected function initialise_default_pagetype($script = null) {
        global $CFG, $SCRIPT;

        if (isset($CFG->pagepath)) {
            debugging('Some code appears to have set $CFG->pagepath. That was a horrible deprecated thing. ' .
                    'Don\'t do it! Try calling $PAGE->set_pagetype() instead.');
            $script = $CFG->pagepath;
            unset($CFG->pagepath);
        }

        if (is_null($script)) {
            $script = ltrim($SCRIPT, '/');
            $len = strlen($CFG->admin);
            if (substr($script, 0, $len) == $CFG->admin) {
                $script = 'admin' . substr($script, $len);
            }
        }

        $path = str_replace('.php', '', $script);
        if (substr($path, -1) == '/') {
            $path .= 'index';
        }

        if (empty($path) || $path == 'index') {
            $this->_pagetype = 'site-index';
        } else {
            $this->_pagetype = str_replace('/', '-', $path);
        }
    }

    
    protected function initialise_standard_body_classes() {
        global $CFG, $USER;

        $pagetype = $this->pagetype;
        if ($pagetype == 'site-index') {
            $this->_legacyclass = 'course';
        } else if (substr($pagetype, 0, 6) == 'admin-') {
            $this->_legacyclass = 'admin';
        }
        $this->add_body_class($this->_legacyclass);

        $pathbits = explode('-', trim($pagetype));
        for ($i = 1; $i < count($pathbits); $i++) {
            $this->add_body_class('path-' . join('-', array_slice($pathbits, 0, $i)));
        }

        $this->add_body_classes(core_useragent::get_browser_version_classes());
        $this->add_body_class('dir-' . get_string('thisdirection', 'langconfig'));
        $this->add_body_class('lang-' . current_language());
        $this->add_body_class('yui-skin-sam');         $this->add_body_class('yui3-skin-sam');         $this->add_body_class($this->url_to_class_name($CFG->wwwroot));

                $this->add_body_class('pagelayout-' . $this->_pagelayout);

        if (!during_initial_install()) {
            $this->add_body_class('course-' . $this->_course->id);
            $this->add_body_class('context-' . $this->_context->id);
        }

        if (!empty($this->_cm)) {
            $this->add_body_class('cmid-' . $this->_cm->id);
        }

        if (!empty($CFG->allowcategorythemes)) {
            $this->ensure_category_loaded();
            foreach ($this->_categories as $catid => $notused) {
                $this->add_body_class('category-' . $catid);
            }
        } else {
            $catid = 0;
            if (is_array($this->_categories)) {
                $catids = array_keys($this->_categories);
                $catid = reset($catids);
            } else if (!empty($this->_course->category)) {
                $catid = $this->_course->category;
            }
            if ($catid) {
                $this->add_body_class('category-' . $catid);
            }
        }

        if (!isloggedin()) {
            $this->add_body_class('notloggedin');
        }

        if ($this->user_is_editing()) {
            $this->add_body_class('editing');
            if (optional_param('bui_moveid', false, PARAM_INT)) {
                $this->add_body_class('blocks-moving');
            }
        }

        if (!empty($CFG->blocksdrag)) {
            $this->add_body_class('drag');
        }

        if ($this->_devicetypeinuse != 'default') {
            $this->add_body_class($this->_devicetypeinuse . 'theme');
        }

                if (defined('BEHAT_SITE_RUNNING')) {
            $this->add_body_class('behat-site');
        }
    }

    
    protected function load_activity_record() {
        global $DB;
        if (is_null($this->_cm)) {
            return;
        }
        $this->_module = $DB->get_record($this->_cm->modname, array('id' => $this->_cm->instance));
    }

    
    protected function ensure_category_loaded() {
        if (is_array($this->_categories)) {
            return;         }
        if (is_null($this->_course)) {
            throw new coding_exception('Attempt to get the course category for this page before the course was set.');
        }
        if ($this->_course->category == 0) {
            $this->_categories = array();
        } else {
            $this->load_category($this->_course->category);
        }
    }

    
    protected function load_category($categoryid) {
        global $DB;
        $category = $DB->get_record('course_categories', array('id' => $categoryid));
        if (!$category) {
            throw new moodle_exception('unknowncategory');
        }
        $this->_categories[$category->id] = $category;
        $parentcategoryids = explode('/', trim($category->path, '/'));
        array_pop($parentcategoryids);
        foreach (array_reverse($parentcategoryids) as $catid) {
            $this->_categories[$catid] = null;
        }
    }

    
    protected function ensure_categories_loaded() {
        global $DB;
        $this->ensure_category_loaded();
        if (!is_null(end($this->_categories))) {
            return;         }
        $idstoload = array_keys($this->_categories);
        array_shift($idstoload);
        $categories = $DB->get_records_list('course_categories', 'id', $idstoload);
        foreach ($idstoload as $catid) {
            $this->_categories[$catid] = $categories[$catid];
        }
    }

    
    protected function ensure_theme_not_set() {
                if (WS_SERVER) {
            return;
        }

        if (!is_null($this->_theme)) {
            throw new coding_exception('The theme has already been set up for this page ready for output. ' .
                    'Therefore, you can no longer change the theme, or anything that might affect what ' .
                    'the current theme is, for example, the course.',
                    'Stack trace when the theme was set up: ' . format_backtrace($this->_wherethemewasinitialised));
        }
    }

    
    protected function url_to_class_name($url) {
        $bits = parse_url($url);
        $class = str_replace('.', '-', $bits['host']);
        if (!empty($bits['port'])) {
            $class .= '--' . $bits['port'];
        }
        if (!empty($bits['path'])) {
            $path = trim($bits['path'], '/');
            if ($path) {
                $class .= '--' . str_replace('/', '-', $path);
            }
        }
        return $class;
    }

    
    protected function all_editing_caps() {
        $caps = $this->_othereditingcaps;
        $caps[] = $this->_blockseditingcap;
        return $caps;
    }

    
    public function has_set_url() {
        return ($this->_url!==null);
    }

    
    public function set_block_actions_done($setting = true) {
        $this->_block_actions_done = $setting;
    }

    
    public function get_popup_notification_allowed() {
        return $this->_popup_notification_allowed;
    }

    
    public function set_popup_notification_allowed($allowed) {
        $this->_popup_notification_allowed = $allowed;
    }

    
    public function apply_theme_region_manipulations($region) {
        if ($this->blockmanipulations && isset($this->blockmanipulations[$region])) {
            $regionwas = $region;
            $regionnow = $this->blockmanipulations[$region];
            if ($this->blocks->is_known_region($regionwas) && $this->blocks->is_known_region($regionnow)) {
                                return $regionnow;
            }
                        return $regionwas;
        }
        return $region;
    }

    
    public function add_report_nodes($userid, $nodeinfo) {
        global $USER;
                $newusernode = $this->navigation->find('user' . $userid, null);
        $reportnode = null;
        $navigationnodeerror =
                'Could not find the navigation node requested. Please check that the node you are looking for exists.';
        if ($userid != $USER->id) {
                        if (empty($newusernode)) {
                                throw new coding_exception($navigationnodeerror);
            }
                        $reportnode = $newusernode->add(get_string('reports'));
        } else {
                        $myprofilenode = $this->settingsnav->find('myprofile', null);
                        if (empty($myprofilenode)) {
                                throw new coding_exception($navigationnodeerror);
            }
                        $reportnode = $myprofilenode->add(get_string('reports'));
        }
                $reportnode->add($nodeinfo['name'], $nodeinfo['url'], navigation_node::TYPE_COURSE);
    }
}
