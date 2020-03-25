<?php




if (!defined('MAX_MODINFO_CACHE_SIZE')) {
    define('MAX_MODINFO_CACHE_SIZE', 10);
}



class course_modinfo {
    
    public static $cachedfields = array('shortname', 'fullname', 'format',
            'enablecompletion', 'groupmode', 'groupmodeforce', 'cacherev');

    
    private $course;

    
    private $sectioninfo;

    
    private $userid;

    
    private $sections;

    
    private $cms;

    
    private $instances;

    
    private $groups;

    
    private static $standardproperties = array(
        'courseid' => 'get_course_id',
        'userid' => 'get_user_id',
        'sections' => 'get_sections',
        'cms' => 'get_cms',
        'instances' => 'get_instances',
        'groups' => 'get_groups_all',
    );

    
    public function __get($name) {
        if (isset(self::$standardproperties[$name])) {
            $method = self::$standardproperties[$name];
            return $this->$method();
        } else {
            debugging('Invalid course_modinfo property accessed: '.$name);
            return null;
        }
    }

    
    public function __isset($name) {
        if (isset(self::$standardproperties[$name])) {
            $value = $this->__get($name);
            return isset($value);
        }
        return false;
    }

    
    public function __empty($name) {
        if (isset(self::$standardproperties[$name])) {
            $value = $this->__get($name);
            return empty($value);
        }
        return true;
    }

    
    public function __set($name, $value) {
        debugging("It is not allowed to set the property course_modinfo::\${$name}", DEBUG_DEVELOPER);
    }

    
    public function get_course() {
        return $this->course;
    }

    
    public function get_course_id() {
        return $this->course->id;
    }

    
    public function get_user_id() {
        return $this->userid;
    }

    
    public function get_sections() {
        return $this->sections;
    }

    
    public function get_cms() {
        return $this->cms;
    }

    
    public function get_cm($cmid) {
        if (empty($this->cms[$cmid])) {
            throw new moodle_exception('invalidcoursemodule', 'error');
        }
        return $this->cms[$cmid];
    }

    
    public function get_instances() {
        return $this->instances;
    }

    
    public function get_used_module_names($plural = false) {
        $modnames = get_module_types_names($plural);
        $modnamesused = array();
        foreach ($this->get_cms() as $cmid => $mod) {
            if (!isset($modnamesused[$mod->modname]) && isset($modnames[$mod->modname]) && $mod->uservisible) {
                $modnamesused[$mod->modname] = $modnames[$mod->modname];
            }
        }
        core_collator::asort($modnamesused);
        return $modnamesused;
    }

    
    public function get_instances_of($modname) {
        if (empty($this->instances[$modname])) {
            return array();
        }
        return $this->instances[$modname];
    }

    
    private function get_groups_all() {
        if (is_null($this->groups)) {
                                                                        $this->groups = groups_get_user_groups($this->course->id, $this->userid);
        }
        return $this->groups;
    }

    
    public function get_groups($groupingid = 0) {
        $allgroups = $this->get_groups_all();
        if (!isset($allgroups[$groupingid])) {
            return array();
        }
        return $allgroups[$groupingid];
    }

    
    public function get_section_info_all() {
        return $this->sectioninfo;
    }

    
    public function get_section_info($sectionnumber, $strictness = IGNORE_MISSING) {
        if (!array_key_exists($sectionnumber, $this->sectioninfo)) {
            if ($strictness === MUST_EXIST) {
                throw new moodle_exception('sectionnotexist');
            } else {
                return null;
            }
        }
        return $this->sectioninfo[$sectionnumber];
    }

    
    protected static $instancecache = array();

    
    protected static $cacheaccessed = array();

    
    public static function clear_instance_cache($courseorid = null) {
        if (empty($courseorid)) {
            self::$instancecache = array();
            self::$cacheaccessed = array();
            return;
        }
        if (is_object($courseorid)) {
            $courseorid = $courseorid->id;
        }
        if (isset(self::$instancecache[$courseorid])) {
                                                self::$instancecache[$courseorid] = '';
            unset(self::$instancecache[$courseorid]);
            unset(self::$cacheaccessed[$courseorid]);
        }
    }

    
    public static function instance($courseorid, $userid = 0) {
        global $USER;
        if (is_object($courseorid)) {
            $course = $courseorid;
        } else {
            $course = (object)array('id' => $courseorid);
        }
        if (empty($userid)) {
            $userid = $USER->id;
        }

        if (!empty(self::$instancecache[$course->id])) {
            if (self::$instancecache[$course->id]->userid == $userid &&
                    (!isset($course->cacherev) ||
                    $course->cacherev == self::$instancecache[$course->id]->get_course()->cacherev)) {
                                self::$cacheaccessed[$course->id] = microtime(true);
                return self::$instancecache[$course->id];
            } else {
                                self::clear_instance_cache($course->id);
            }
        }
        $modinfo = new course_modinfo($course, $userid);

                if (count(self::$instancecache) >= MAX_MODINFO_CACHE_SIZE) {
                        asort(self::$cacheaccessed, SORT_NUMERIC);
            $courseidtoremove = key(array_reverse(self::$cacheaccessed, true));
            self::clear_instance_cache($courseidtoremove);
        }

                self::$instancecache[$course->id] = $modinfo;
        self::$cacheaccessed[$course->id] = microtime(true);

        return $modinfo;
    }

    
    public function __construct($course, $userid) {
        global $CFG, $COURSE, $SITE, $DB;

        if (!isset($course->cacherev)) {
                                    $course = get_course($course->id, false);
        }

        $cachecoursemodinfo = cache::make('core', 'coursemodinfo');

                $coursemodinfo = $cachecoursemodinfo->get($course->id);
        if ($coursemodinfo === false || ($course->cacherev != $coursemodinfo->cacherev)) {
            $coursemodinfo = self::build_course_cache($course);
        }

                $this->userid = $userid;
        $this->sections = array();
        $this->cms = array();
        $this->instances = array();
        $this->groups = null;

                        context_helper::preload_course($course->id);

                                if ($course->id == $COURSE->id || $course->id == $SITE->id) {
                                    foreach ($coursemodinfo->modinfo as $mod) {
                if (!context_module::instance($mod->cm, IGNORE_MISSING)) {
                    debugging('Course cache integrity check failed: course module with id '. $mod->cm.
                            ' does not have context. Rebuilding cache for course '. $course->id);
                                        $course = $DB->get_record('course', array('id' => $course->id), '*', MUST_EXIST);
                    $coursemodinfo = self::build_course_cache($course);
                    break;
                }
            }
        }

                $this->course = fullclone($course);
        foreach ($coursemodinfo as $key => $value) {
            if ($key !== 'modinfo' && $key !== 'sectioncache' &&
                    (!isset($this->course->$key) || $key === 'cacherev')) {
                $this->course->$key = $value;
            }
        }

                static $modexists = array();
        foreach ($coursemodinfo->modinfo as $mod) {
            if (!isset($mod->name) || strval($mod->name) === '') {
                                continue;
            }

                        if (!array_key_exists($mod->mod, $modexists)) {
                $modexists[$mod->mod] = file_exists("$CFG->dirroot/mod/$mod->mod/lib.php");
            }
            if (!$modexists[$mod->mod]) {
                continue;
            }

                        $cm = new cm_info($this, null, $mod, null);

                        if (!isset($this->instances[$cm->modname])) {
                $this->instances[$cm->modname] = array();
            }
            $this->instances[$cm->modname][$cm->instance] = $cm;
            $this->cms[$cm->id] = $cm;

                        if (!isset($this->sections[$cm->sectionnum])) {
                $this->sections[$cm->sectionnum] = array();
            }
            $this->sections[$cm->sectionnum][] = $cm->id;
        }

                $this->sectioninfo = array();
        foreach ($coursemodinfo->sectioncache as $number => $data) {
            $this->sectioninfo[$number] = new section_info($data, $number, null, null,
                    $this, null);
        }
    }

    
    public static function build_section_cache($courseid) {
        global $DB;
        debugging('Function course_modinfo::build_section_cache() is deprecated. It can only be used internally to build course cache.');
        $course = $DB->get_record('course', array('id' => $course->id),
                        array_merge(array('id'), self::$cachedfields), MUST_EXIST);
        return self::build_course_section_cache($course);
    }

    
    protected static function build_course_section_cache($course) {
        global $DB;

                $sections = $DB->get_records('course_sections', array('course' => $course->id), 'section',
                'section, id, course, name, summary, summaryformat, sequence, visible, ' .
                'availability');
        $compressedsections = array();

        $formatoptionsdef = course_get_format($course)->section_format_options();
                foreach ($sections as $number => $section) {
                        foreach ($formatoptionsdef as $key => $option) {
                if (!empty($option['cache'])) {
                    $formatoptions = course_get_format($course)->get_format_options($section);
                    if (!array_key_exists('cachedefault', $option) || $option['cachedefault'] !== $formatoptions[$key]) {
                        $section->$key = $formatoptions[$key];
                    }
                }
            }
                        $compressedsections[$number] = clone($section);
            section_info::convert_for_section_cache($compressedsections[$number]);
        }

        return $compressedsections;
    }

    
    public static function build_course_cache($course) {
        global $DB, $CFG;
        require_once("$CFG->dirroot/course/lib.php");
        if (empty($course->id)) {
            throw new coding_exception('Object $course is missing required property \id\'');
        }
                foreach (self::$cachedfields as $key) {
            if (!isset($course->$key)) {
                $course = $DB->get_record('course', array('id' => $course->id),
                        implode(',', array_merge(array('id'), self::$cachedfields)), MUST_EXIST);
                break;
            }
        }
                                $coursemodinfo = new stdClass();
        $coursemodinfo->modinfo = get_array_of_activities($course->id);
        $coursemodinfo->sectioncache = self::build_course_section_cache($course);
        foreach (self::$cachedfields as $key) {
            $coursemodinfo->$key = $course->$key;
        }
                $cachecoursemodinfo = cache::make('core', 'coursemodinfo');
        $cachecoursemodinfo->set($course->id, $coursemodinfo);
        return $coursemodinfo;
    }
}



class cm_info implements IteratorAggregate {
    
    const STATE_BASIC = 0;

    
    const STATE_BUILDING_DYNAMIC = 1;

    
    const STATE_DYNAMIC = 2;

    
    const STATE_BUILDING_VIEW = 3;

    
    const STATE_VIEW = 4;

    
    private $modinfo;

    
    private $state;

    
    private $id;

    
    private $instance;

    
    private $idnumber;

    
    private $added;

    
    private $score;

    
    private $visible;

    
    private $visibleold;

    
    private $groupmode;

    
    private $groupingid;

    
    private $indent;

    
    private $completion;

    
    private $completiongradeitemnumber;

    
    private $completionview;

    
    private $completionexpected;

    
    private $availability;

    
    private $showdescription;

    
    private $extra;

    
    private $icon;

    
    private $iconcomponent;

    
    private $modname;

    
    private $module;

    
    private $name;

    
    private $sectionnum;

    
    private $section;

    
    private $conditionscompletion;

    
    private $conditionsgrade;

    
    private $conditionsfield;

    
    private $available;

    
    private $availableinfo;

    
    private $uservisible;

    
    private $url;

    
    private $content;

    
    private $extraclasses;

    
    private $iconurl;

    
    private $onclick;

    
    private $customdata;

    
    private $afterlink;

    
    private $afterediticons;

    
    private static $standardproperties = array(
        'url' => 'get_url',
        'content' => 'get_content',
        'extraclasses' => 'get_extra_classes',
        'onclick' => 'get_on_click',
        'customdata' => 'get_custom_data',
        'afterlink' => 'get_after_link',
        'afterediticons' => 'get_after_edit_icons',
        'modfullname' => 'get_module_type_name',
        'modplural' => 'get_module_type_name_plural',
        'id' => false,
        'added' => false,
        'availability' => false,
        'available' => 'get_available',
        'availableinfo' => 'get_available_info',
        'completion' => false,
        'completionexpected' => false,
        'completiongradeitemnumber' => false,
        'completionview' => false,
        'conditionscompletion' => false,
        'conditionsfield' => false,
        'conditionsgrade' => false,
        'context' => 'get_context',
        'course' => 'get_course_id',
        'coursegroupmode' => 'get_course_groupmode',
        'coursegroupmodeforce' => 'get_course_groupmodeforce',
        'effectivegroupmode' => 'get_effective_groupmode',
        'extra' => false,
        'groupingid' => false,
        'groupmembersonly' => 'get_deprecated_group_members_only',
        'groupmode' => false,
        'icon' => false,
        'iconcomponent' => false,
        'idnumber' => false,
        'indent' => false,
        'instance' => false,
        'modname' => false,
        'module' => false,
        'name' => 'get_name',
        'score' => false,
        'section' => false,
        'sectionnum' => false,
        'showdescription' => false,
        'uservisible' => 'get_user_visible',
        'visible' => false,
        'visibleold' => false,
    );

    
    private static $standardmethods = array(
                'get_url',
        'get_content',
        'get_extra_classes',
        'get_on_click',
        'get_custom_data',
        'get_after_link',
        'get_after_edit_icons',
                'obtain_dynamic_data',
    );

    
    public function __call($name, $arguments) {
        global $CFG;

        if (in_array($name, self::$standardmethods)) {
            if ($CFG->debugdeveloper) {
                if ($alternative = array_search($name, self::$standardproperties)) {
                                        debugging("cm_info::$name() is deprecated, please use the property cm_info->$alternative instead.", DEBUG_DEVELOPER);
                } else {
                    debugging("cm_info::$name() is deprecated and should not be used.", DEBUG_DEVELOPER);
                }
            }
                        return $this->$name();
        }
        throw new coding_exception("Method cm_info::{$name}() does not exist");
    }

    
    public function __get($name) {
        if (isset(self::$standardproperties[$name])) {
            if ($method = self::$standardproperties[$name]) {
                return $this->$method();
            } else {
                return $this->$name;
            }
        } else {
            debugging('Invalid cm_info property accessed: '.$name);
            return null;
        }
    }

    
    public function getIterator() {
                $this->obtain_dynamic_data();
        $ret = array();

                $props = self::$standardproperties;
        unset($props['groupmembersonly']);

        foreach ($props as $key => $unused) {
            $ret[$key] = $this->__get($key);
        }
        return new ArrayIterator($ret);
    }

    
    public function __isset($name) {
        if (isset(self::$standardproperties[$name])) {
            $value = $this->__get($name);
            return isset($value);
        }
        return false;
    }

    
    public function __empty($name) {
        if (isset(self::$standardproperties[$name])) {
            $value = $this->__get($name);
            return empty($value);
        }
        return true;
    }

    
    public function __set($name, $value) {
        debugging("It is not allowed to set the property cm_info::\${$name}", DEBUG_DEVELOPER);
    }

    
    public function has_view() {
        return !is_null($this->url);
    }

    
    private function get_url() {
        $this->obtain_dynamic_data();
        return $this->url;
    }

    
    private function get_content() {
        $this->obtain_view_data();
        return $this->content;
    }

    
    public function get_formatted_content($options = array()) {
        $this->obtain_view_data();
        if (empty($this->content)) {
            return '';
        }
                                filter_preload_activities($this->get_modinfo());

        $options = (array)$options;
        if (!isset($options['context'])) {
            $options['context'] = $this->get_context();
        }
        return format_text($this->content, FORMAT_HTML, $options);
    }

    
    private function get_name() {
        $this->obtain_dynamic_data();
        return $this->name;
    }

    
    public function get_formatted_name($options = array()) {
        global $CFG;
        $options = (array)$options;
        if (!isset($options['context'])) {
            $options['context'] = $this->get_context();
        }
                                if (!empty($CFG->filterall)) {
            filter_preload_activities($this->get_modinfo());
        }
        return format_string($this->get_name(), true,  $options);
    }

    
    private function get_extra_classes() {
        $this->obtain_view_data();
        return $this->extraclasses;
    }

    
    private function get_on_click() {
                $this->obtain_dynamic_data();
        return $this->onclick;
    }
    
    private function get_custom_data() {
        return $this->customdata;
    }

    
    private function get_after_link() {
        $this->obtain_view_data();
        return $this->afterlink;
    }

    
    private function get_after_edit_icons() {
        $this->obtain_view_data();
        return $this->afterediticons;
    }

    
    public function get_icon_url($output = null) {
        global $OUTPUT;
        $this->obtain_dynamic_data();
        if (!$output) {
            $output = $OUTPUT;
        }
                if (!empty($this->iconurl)) {
            $icon = $this->iconurl;

                } else if (!empty($this->icon)) {
            if (substr($this->icon, 0, 4) === 'mod/') {
                list($modname, $iconname) = explode('/', substr($this->icon, 4), 2);
                $icon = $output->pix_url($iconname, $modname);
            } else {
                if (!empty($this->iconcomponent)) {
                                        $icon = $output->pix_url($this->icon, $this->iconcomponent);
                } else {
                                        $icon = $output->pix_url($this->icon);
                }
            }
        } else {
            $icon = $output->pix_url('icon', $this->modname);
        }
        return $icon;
    }

    
    public function get_grouping_label($textclasses = '') {
        $groupinglabel = '';
        if (!empty($this->groupingid) && has_capability('moodle/course:managegroups', context_course::instance($this->course))) {
            $groupings = groups_get_all_groupings($this->course);
            $groupinglabel = html_writer::tag('span', '('.format_string($groupings[$this->groupingid]->name).')',
                array('class' => 'groupinglabel '.$textclasses));
        }
        return $groupinglabel;
    }

    
    public function get_module_type_name($plural = false) {
        $modnames = get_module_types_names($plural);
        if (isset($modnames[$this->modname])) {
            return $modnames[$this->modname];
        } else {
            return null;
        }
    }

    
    private function get_module_type_name_plural() {
        return $this->get_module_type_name(true);
    }

    
    public function get_modinfo() {
        return $this->modinfo;
    }

    
    public function get_course() {
        return $this->modinfo->get_course();
    }

    
    private function get_course_id() {
        return $this->modinfo->get_course_id();
    }

    
    private function get_course_groupmode() {
        return $this->modinfo->get_course()->groupmode;
    }

    
    private function get_course_groupmodeforce() {
        return $this->modinfo->get_course()->groupmodeforce;
    }

    
    private function get_effective_groupmode() {
        $groupmode = $this->groupmode;
        if ($this->modinfo->get_course()->groupmodeforce) {
            $groupmode = $this->modinfo->get_course()->groupmode;
            if ($groupmode != NOGROUPS && !plugin_supports('mod', $this->modname, FEATURE_GROUPS, 0)) {
                $groupmode = NOGROUPS;
            }
        }
        return $groupmode;
    }

    
    private function get_context() {
        return context_module::instance($this->id);
    }

    
    public function get_course_module_record($additionalfields = false) {
        $cmrecord = new stdClass();

                static $cmfields = array('id', 'course', 'module', 'instance', 'section', 'idnumber', 'added',
            'score', 'indent', 'visible', 'visibleold', 'groupmode', 'groupingid',
            'completion', 'completiongradeitemnumber', 'completionview', 'completionexpected',
            'showdescription', 'availability');
        foreach ($cmfields as $key) {
            $cmrecord->$key = $this->$key;
        }

                if ($additionalfields) {
            $cmrecord->name = $this->name;
            $cmrecord->modname = $this->modname;
            $cmrecord->sectionnum = $this->sectionnum;
        }

        return $cmrecord;
    }

        
    
    public function set_content($content) {
        $this->content = $content;
    }

    
    public function set_extra_classes($extraclasses) {
        $this->extraclasses = $extraclasses;
    }

    
    public function set_icon_url(moodle_url $iconurl) {
        $this->iconurl = $iconurl;
    }

    
    public function set_on_click($onclick) {
        $this->check_not_view_only();
        $this->onclick = $onclick;
    }

    
    public function set_after_link($afterlink) {
        $this->afterlink = $afterlink;
    }

    
    public function set_after_edit_icons($afterediticons) {
        $this->afterediticons = $afterediticons;
    }

    
    public function set_name($name) {
        if ($this->state < self::STATE_BUILDING_DYNAMIC) {
            $this->update_user_visible();
        }
        $this->name = $name;
    }

    
    public function set_no_view_link() {
        $this->check_not_view_only();
        $this->url = null;
    }

    
    public function set_user_visible($uservisible) {
        $this->check_not_view_only();
        $this->uservisible = $uservisible;
    }

    
    public function set_available($available, $showavailability=0, $availableinfo='') {
        $this->check_not_view_only();
        $this->available = $available;
        if (!$showavailability) {
            $availableinfo = '';
        }
        $this->availableinfo = $availableinfo;
        $this->update_user_visible();
    }

    
    private function check_not_view_only() {
        if ($this->state >= self::STATE_DYNAMIC) {
            throw new coding_exception('Cannot set this data from _cm_info_view because it may ' .
                    'affect other pages as well as view');
        }
    }

    
    public function __construct(course_modinfo $modinfo, $notused1, $mod, $notused2) {
        $this->modinfo = $modinfo;

        $this->id               = $mod->cm;
        $this->instance         = $mod->id;
        $this->modname          = $mod->mod;
        $this->idnumber         = isset($mod->idnumber) ? $mod->idnumber : '';
        $this->name             = $mod->name;
        $this->visible          = $mod->visible;
        $this->sectionnum       = $mod->section;         $this->groupmode        = isset($mod->groupmode) ? $mod->groupmode : 0;
        $this->groupingid       = isset($mod->groupingid) ? $mod->groupingid : 0;
        $this->indent           = isset($mod->indent) ? $mod->indent : 0;
        $this->extra            = isset($mod->extra) ? $mod->extra : '';
        $this->extraclasses     = isset($mod->extraclasses) ? $mod->extraclasses : '';
                $this->iconurl          = isset($mod->iconurl) ? new moodle_url($mod->iconurl) : '';
        $this->onclick          = isset($mod->onclick) ? $mod->onclick : '';
        $this->content          = isset($mod->content) ? $mod->content : '';
        $this->icon             = isset($mod->icon) ? $mod->icon : '';
        $this->iconcomponent    = isset($mod->iconcomponent) ? $mod->iconcomponent : '';
        $this->customdata       = isset($mod->customdata) ? $mod->customdata : '';
        $this->showdescription  = isset($mod->showdescription) ? $mod->showdescription : 0;
        $this->state = self::STATE_BASIC;

        $this->section = isset($mod->sectionid) ? $mod->sectionid : 0;
        $this->module = isset($mod->module) ? $mod->module : 0;
        $this->added = isset($mod->added) ? $mod->added : 0;
        $this->score = isset($mod->score) ? $mod->score : 0;
        $this->visibleold = isset($mod->visibleold) ? $mod->visibleold : 0;

                                $this->completion = isset($mod->completion) ? $mod->completion : 0;
        $this->completiongradeitemnumber = isset($mod->completiongradeitemnumber)
                ? $mod->completiongradeitemnumber : null;
        $this->completionview = isset($mod->completionview)
                ? $mod->completionview : 0;
        $this->completionexpected = isset($mod->completionexpected)
                ? $mod->completionexpected : 0;
        $this->availability = isset($mod->availability) ? $mod->availability : null;
        $this->conditionscompletion = isset($mod->conditionscompletion)
                ? $mod->conditionscompletion : array();
        $this->conditionsgrade = isset($mod->conditionsgrade)
                ? $mod->conditionsgrade : array();
        $this->conditionsfield = isset($mod->conditionsfield)
                ? $mod->conditionsfield : array();

        static $modviews = array();
        if (!isset($modviews[$this->modname])) {
            $modviews[$this->modname] = !plugin_supports('mod', $this->modname,
                    FEATURE_NO_VIEW_LINK);
        }
        $this->url = $modviews[$this->modname]
                ? new moodle_url('/mod/' . $this->modname . '/view.php', array('id'=>$this->id))
                : null;
    }

    
    public static function create($cm, $userid = 0) {
                if (!$cm) {
            return null;
        }
                if ($cm instanceof cm_info) {
            return $cm;
        }
                if (empty($cm->id) || empty($cm->course)) {
            throw new coding_exception('$cm must contain ->id and ->course');
        }
        $modinfo = get_fast_modinfo($cm->course, $userid);
        return $modinfo->get_cm($cm->id);
    }

    
    private function obtain_dynamic_data() {
        global $CFG;
        $userid = $this->modinfo->get_user_id();
        if ($this->state >= self::STATE_BUILDING_DYNAMIC || $userid == -1) {
            return;
        }
        $this->state = self::STATE_BUILDING_DYNAMIC;

        if (!empty($CFG->enableavailability)) {
                        $ci = new \core_availability\info_module($this);

                                    $this->available = $ci->is_available($this->availableinfo, true,
                    $userid, $this->modinfo);
        } else {
            $this->available = true;
        }

                if ($this->available) {
            $parentsection = $this->modinfo->get_section_info($this->sectionnum);
            if (!$parentsection->available) {
                                                                $this->available = false;
            }
        }

                $this->update_user_visible();

                $this->call_mod_function('cm_info_dynamic');
        $this->state = self::STATE_DYNAMIC;
    }

    
    private function get_user_visible() {
        $this->obtain_dynamic_data();
        return $this->uservisible;
    }

    
    private function get_available() {
        $this->obtain_dynamic_data();
        return $this->available;
    }

    
    private function get_deprecated_group_members_only() {
        debugging('$cm->groupmembersonly has been deprecated and always returns zero. ' .
                'If used to restrict a list of enrolled users to only those who can ' .
                'access the module, consider \core_availability\info_module::filter_user_list.',
                DEBUG_DEVELOPER);
        return 0;
    }

    
    private function get_available_info() {
        $this->obtain_dynamic_data();
        return $this->availableinfo;
    }

    
    private function update_user_visible() {
        $userid = $this->modinfo->get_user_id();
        if ($userid == -1) {
            return null;
        }
        $this->uservisible = true;

                        if ((!$this->visible or !$this->get_available()) and
                !has_capability('moodle/course:viewhiddenactivities', $this->get_context(), $userid)) {

            $this->uservisible = false;
        }

                if ($this->is_user_access_restricted_by_capability()) {

             $this->uservisible = false;
                        $this->availableinfo = '';
        }
    }

    
    public function is_user_access_restricted_by_group() {
        debugging('cm_info::is_user_access_restricted_by_group() ' .
                'is deprecated and always returns false; use $cm->uservisible ' .
                'to decide whether the current user can access an activity', DEBUG_DEVELOPER);
        return false;
    }

    
    public function is_user_access_restricted_by_capability() {
        $userid = $this->modinfo->get_user_id();
        if ($userid == -1) {
            return null;
        }
        $capability = 'mod/' . $this->modname . ':view';
        $capabilityinfo = get_capability_info($capability);
        if (!$capabilityinfo) {
                        return false;
        }

                return !has_capability($capability, $this->get_context(), $userid);
    }

    
    public function is_user_access_restricted_by_conditional_access() {
        throw new coding_exception('cm_info::is_user_access_restricted_by_conditional_access() ' .
                'can not be used any more; this function is not needed (use $cm->uservisible ' .
                'and $cm->availableinfo to decide whether it should be available ' .
                'or appear)');
    }

    
    private function call_mod_function($type) {
        global $CFG;
        $libfile = $CFG->dirroot . '/mod/' . $this->modname . '/lib.php';
        if (file_exists($libfile)) {
            include_once($libfile);
            $function = 'mod_' . $this->modname . '_' . $type;
            if (function_exists($function)) {
                $function($this);
            } else {
                $function = $this->modname . '_' . $type;
                if (function_exists($function)) {
                    $function($this);
                }
            }
        }
    }

    
    private function obtain_view_data() {
        if ($this->state >= self::STATE_BUILDING_VIEW || $this->modinfo->get_user_id() == -1) {
            return;
        }
        $this->obtain_dynamic_data();
        $this->state = self::STATE_BUILDING_VIEW;

                $this->call_mod_function('cm_info_view');
        $this->state = self::STATE_VIEW;
    }
}



function get_fast_modinfo($courseorid, $userid = 0, $resetonly = false) {
        if ($courseorid === 'reset') {
        debugging("Using the string 'reset' as the first argument of get_fast_modinfo() is deprecated. Use get_fast_modinfo(0,0,true) instead.", DEBUG_DEVELOPER);
        $courseorid = 0;
        $resetonly = true;
    }

        if (!$resetonly) {
        upgrade_ensure_not_running();
    }

        if ($resetonly) {
        course_modinfo::clear_instance_cache($courseorid);
        return null;
    }

        return course_modinfo::instance($courseorid, $userid);
}


function get_course_and_cm_from_cmid($cmorid, $modulename = '', $courseorid = 0, $userid = 0) {
    global $DB;
    if (is_object($cmorid)) {
        $cmid = $cmorid->id;
        if (isset($cmorid->course)) {
            $courseid = (int)$cmorid->course;
        } else {
            $courseid = 0;
        }
    } else {
        $cmid = (int)$cmorid;
        $courseid = 0;
    }

        if ($modulename && !core_component::is_valid_plugin_name('mod', $modulename)) {
        throw new coding_exception('Invalid modulename parameter');
    }

        $course = null;
    if (is_object($courseorid)) {
        $course = $courseorid;
    } else if ($courseorid) {
        $courseid = (int)$courseorid;
    }

    if (!$course) {
        if ($courseid) {
                        $course = get_course($courseid);
        } else {
                        $course = $DB->get_record_sql("
                    SELECT c.*
                      FROM {course_modules} cm
                      JOIN {course} c ON c.id = cm.course
                     WHERE cm.id = ?", array($cmid), MUST_EXIST);
        }
    }

        $modinfo = get_fast_modinfo($course, $userid);
    $cm = $modinfo->get_cm($cmid);
    if ($modulename && $cm->modname !== $modulename) {
        throw new moodle_exception('invalidcoursemodule', 'error');
    }
    return array($course, $cm);
}


function get_course_and_cm_from_instance($instanceorid, $modulename, $courseorid = 0, $userid = 0) {
    global $DB;

        if (is_object($instanceorid)) {
        $instanceid = $instanceorid->id;
        if (isset($instanceorid->course)) {
            $courseid = (int)$instanceorid->course;
        } else {
            $courseid = 0;
        }
    } else {
        $instanceid = (int)$instanceorid;
        $courseid = 0;
    }

        $course = null;
    if (is_object($courseorid)) {
        $course = $courseorid;
    } else if ($courseorid) {
        $courseid = (int)$courseorid;
    }

        if (!core_component::is_valid_plugin_name('mod', $modulename)) {
        throw new coding_exception('Invalid modulename parameter');
    }

    if (!$course) {
        if ($courseid) {
                        $course = get_course($courseid);
        } else {
                        $pagetable = '{' . $modulename . '}';
            $course = $DB->get_record_sql("
                    SELECT c.*
                      FROM $pagetable instance
                      JOIN {course} c ON c.id = instance.course
                     WHERE instance.id = ?", array($instanceid), MUST_EXIST);
        }
    }

        $modinfo = get_fast_modinfo($course, $userid);
    $instances = $modinfo->get_instances_of($modulename);
    if (!array_key_exists($instanceid, $instances)) {
        throw new moodle_exception('invalidmoduleid', 'error', $instanceid);
    }
    return array($course, $instances[$instanceid]);
}



function rebuild_course_cache($courseid=0, $clearonly=false) {
    global $COURSE, $SITE, $DB, $CFG;

        if (!$clearonly && !upgrade_ensure_not_running(true)) {
        $clearonly = true;
    }

        navigation_cache::destroy_volatile_caches();

    if (class_exists('format_base')) {
                format_base::reset_course_cache($courseid);
    }

    $cachecoursemodinfo = cache::make('core', 'coursemodinfo');
    if (empty($courseid)) {
                increment_revision_number('course', 'cacherev', '');
        $cachecoursemodinfo->purge();
        course_modinfo::clear_instance_cache();
                $sitecacherev = $DB->get_field('course', 'cacherev', array('id' => SITEID));
        $SITE->cachrev = $sitecacherev;
        if ($COURSE->id == SITEID) {
            $COURSE->cacherev = $sitecacherev;
        } else {
            $COURSE->cacherev = $DB->get_field('course', 'cacherev', array('id' => $COURSE->id));
        }
    } else {
                increment_revision_number('course', 'cacherev', 'id = :id', array('id' => $courseid));
        $cachecoursemodinfo->delete($courseid);
        course_modinfo::clear_instance_cache($courseid);
                if ($courseid == $COURSE->id || $courseid == $SITE->id) {
            $cacherev = $DB->get_field('course', 'cacherev', array('id' => $courseid));
            if ($courseid == $COURSE->id) {
                $COURSE->cacherev = $cacherev;
            }
            if ($courseid == $SITE->id) {
                $SITE->cachrev = $cacherev;
            }
        }
    }

    if ($clearonly) {
        return;
    }

    if ($courseid) {
        $select = array('id'=>$courseid);
    } else {
        $select = array();
        core_php_time_limit::raise();      }

    $rs = $DB->get_recordset("course", $select,'','id,'.join(',', course_modinfo::$cachedfields));
        foreach ($rs as $course) {
        course_modinfo::build_course_cache($course);
    }
    $rs->close();
}



class cached_cm_info {
    
    public $name;

    
    public $icon;

    
    public $iconcomponent;

    
    public $content;

    
    public $customdata;

    
    public $extraclasses;

    
    public $iconurl;

    
    public $onclick;
}



class section_info implements IteratorAggregate {
    
    private $_id;

    
    private $_section;

    
    private $_name;

    
    private $_visible;

    
    private $_summary;

    
    private $_summaryformat;

    
    private $_availability;

    
    private $_conditionscompletion;

    
    private $_conditionsgrade;

    
    private $_conditionsfield;

    
    private $_available;

    
    private $_availableinfo;

    
    private $_uservisible;

    
    private static $sectioncachedefaults = array(
        'name' => null,
        'summary' => '',
        'summaryformat' => '1',         'visible' => '1',
        'availability' => null,
    );

    
    private $cachedformatoptions = array();

    
    static private $sectionformatoptions = array();

    
    private $modinfo;

    
    public function __construct($data, $number, $notused1, $notused2, $modinfo, $notused3) {
        global $CFG;
        require_once($CFG->dirroot.'/course/lib.php');

                $this->_id = $data->id;

        $defaults = self::$sectioncachedefaults +
                array('conditionscompletion' => array(),
                    'conditionsgrade' => array(),
                    'conditionsfield' => array());

                foreach ($defaults as $field => $value) {
            if (isset($data->{$field})) {
                $this->{'_'.$field} = $data->{$field};
            } else {
                $this->{'_'.$field} = $value;
            }
        }

                $this->_section = $number;
        $this->modinfo = $modinfo;

                $course = $modinfo->get_course();
        if (!isset(self::$sectionformatoptions[$course->format])) {
                                    self::$sectionformatoptions[$course->format] =
                    course_get_format($course)->section_format_options();
        }
        foreach (self::$sectionformatoptions[$course->format] as $field => $option) {
            if (!empty($option['cache'])) {
                if (isset($data->{$field})) {
                    $this->cachedformatoptions[$field] = $data->{$field};
                } else if (array_key_exists('cachedefault', $option)) {
                    $this->cachedformatoptions[$field] = $option['cachedefault'];
                }
            }
        }
    }

    
    public function __isset($name) {
        if (method_exists($this, 'get_'.$name) ||
                property_exists($this, '_'.$name) ||
                array_key_exists($name, self::$sectionformatoptions[$this->modinfo->get_course()->format])) {
            $value = $this->__get($name);
            return isset($value);
        }
        return false;
    }

    
    public function __empty($name) {
        if (method_exists($this, 'get_'.$name) ||
                property_exists($this, '_'.$name) ||
                array_key_exists($name, self::$sectionformatoptions[$this->modinfo->get_course()->format])) {
            $value = $this->__get($name);
            return empty($value);
        }
        return true;
    }

    
    public function __get($name) {
        if (method_exists($this, 'get_'.$name)) {
            return $this->{'get_'.$name}();
        }
        if (property_exists($this, '_'.$name)) {
            return $this->{'_'.$name};
        }
        if (array_key_exists($name, $this->cachedformatoptions)) {
            return $this->cachedformatoptions[$name];
        }
                if (array_key_exists($name, self::$sectionformatoptions[$this->modinfo->get_course()->format])) {
            $formatoptions = course_get_format($this->modinfo->get_course())->get_format_options($this);
            return $formatoptions[$name];
        }
        debugging('Invalid section_info property accessed! '.$name);
        return null;
    }

    
    private function get_available() {
        global $CFG;
        $userid = $this->modinfo->get_user_id();
        if ($this->_available !== null || $userid == -1) {
                        return $this->_available;
        }
        $this->_available = true;
        $this->_availableinfo = '';
        if (!empty($CFG->enableavailability)) {
                        $ci = new \core_availability\info_section($this);
            $this->_available = $ci->is_available($this->_availableinfo, true,
                    $userid, $this->modinfo);
        }
                $currentavailable = $this->_available;
        course_get_format($this->modinfo->get_course())->
            section_get_available_hook($this, $this->_available, $this->_availableinfo);
        if (!$currentavailable && $this->_available) {
            debugging('section_get_available_hook() can not make unavailable section available', DEBUG_DEVELOPER);
            $this->_available = $currentavailable;
        }
        return $this->_available;
    }

    
    private function get_availableinfo() {
                        $this->get_available();
        return $this->_availableinfo;
    }

    
    public function getIterator() {
        $ret = array();
        foreach (get_object_vars($this) as $key => $value) {
            if (substr($key, 0, 1) == '_') {
                if (method_exists($this, 'get'.$key)) {
                    $ret[substr($key, 1)] = $this->{'get'.$key}();
                } else {
                    $ret[substr($key, 1)] = $this->$key;
                }
            }
        }
        $ret['sequence'] = $this->get_sequence();
        $ret['course'] = $this->get_course();
        $ret = array_merge($ret, course_get_format($this->modinfo->get_course())->get_format_options($this->_section));
        return new ArrayIterator($ret);
    }

    
    private function get_uservisible() {
        $userid = $this->modinfo->get_user_id();
        if ($this->_uservisible !== null || $userid == -1) {
                        return $this->_uservisible;
        }
        $this->_uservisible = true;
        if (!$this->_visible || !$this->get_available()) {
            $coursecontext = context_course::instance($this->get_course());
            if (!has_capability('moodle/course:viewhiddensections', $coursecontext, $userid)) {
                $this->_uservisible = false;
            }
        }
        return $this->_uservisible;
    }

    
    private function get_sequence() {
        if (!empty($this->modinfo->sections[$this->_section])) {
            return implode(',', $this->modinfo->sections[$this->_section]);
        } else {
            return '';
        }
    }

    
    private function get_course() {
        return $this->modinfo->get_course_id();
    }

    
    private function get_modinfo() {
        return $this->modinfo;
    }

    
    public static function convert_for_section_cache($section) {
        global $CFG;

                unset($section->course);
                unset($section->section);
                unset($section->sequence);

                foreach (self::$sectioncachedefaults as $field => $value) {
                                    if (isset($section->{$field}) && $section->{$field} === $value) {
                unset($section->{$field});
            }
        }
    }
}
