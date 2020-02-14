<?php



defined('MOODLE_INTERNAL') || die();


define('NAVIGATION_CACHE_NAME', 'navigation');
define('NAVIGATION_SITE_ADMIN_CACHE_NAME', 'navigationsiteadmin');


class navigation_node implements renderable {
    
    const NODETYPE_LEAF =   0;
    
    const NODETYPE_BRANCH = 1;
    
    const TYPE_UNKNOWN =    null;
    
    const TYPE_ROOTNODE =   0;
    
    const TYPE_SYSTEM =     1;
    
    const TYPE_CATEGORY =   10;
    
    const TYPE_MY_CATEGORY = 11;
    
    const TYPE_COURSE =     20;
    
    const TYPE_SECTION =    30;
    
    const TYPE_ACTIVITY =   40;
    
    const TYPE_RESOURCE =   50;
    
    const TYPE_CUSTOM =     60;
    
    const TYPE_SETTING =    70;
    
    const TYPE_SITE_ADMIN = 71;
    
    const TYPE_USER =       80;
    
    const TYPE_CONTAINER =  90;
    
    const COURSE_OTHER = 0;
    
    const COURSE_MY = 1;
    
    const COURSE_CURRENT = 2;

    
    public $id = null;
    
    public $key = null;
    
    public $text = null;
    
    public $shorttext = null;
    
    public $title = null;
    
    public $helpbutton = null;
    
    public $action = null;
    
    public $icon = null;
    
    public $type = self::TYPE_UNKNOWN;
    
    public $nodetype = self::NODETYPE_LEAF;
    
    public $collapse = false;
    
    public $forceopen = false;
    
    public $classes = array();
    
    public $children = array();
    
    public $isactive = false;
    
    public $hidden = false;
    
    public $display = true;
    
    public $preceedwithhr = false;
    
    public $mainnavonly = false;
    
    public $forcetitle = false;
    
    public $parent = null;
    
    public $hideicon = false;
    
    public $isexpandable = false;
    
    protected $namedtypes = array(0=>'system',10=>'category',20=>'course',30=>'structure',40=>'activity',50=>'resource',60=>'custom',70=>'setting',71=>'siteadmin', 80=>'user');
    
    protected static $fullmeurl = null;
    
    public static $autofindactive = true;
    
    protected static $loadadmintree = false;
    
    public $includesectionnum = false;
    
    public $requiresajaxloading = false;

    
    public function __construct($properties) {
        if (is_array($properties)) {
                                                                                                            if (array_key_exists('text', $properties)) {
                $this->text = $properties['text'];
            }
            if (array_key_exists('shorttext', $properties)) {
                $this->shorttext = $properties['shorttext'];
            }
            if (!array_key_exists('icon', $properties)) {
                $properties['icon'] = new pix_icon('i/navigationitem', '');
            }
            $this->icon = $properties['icon'];
            if ($this->icon instanceof pix_icon) {
                if (empty($this->icon->attributes['class'])) {
                    $this->icon->attributes['class'] = 'navicon';
                } else {
                    $this->icon->attributes['class'] .= ' navicon';
                }
            }
            if (array_key_exists('type', $properties)) {
                $this->type = $properties['type'];
            } else {
                $this->type = self::TYPE_CUSTOM;
            }
            if (array_key_exists('key', $properties)) {
                $this->key = $properties['key'];
            }
                        if (array_key_exists('action', $properties)) {
                $this->action = $properties['action'];
                if (is_string($this->action)) {
                    $this->action = new moodle_url($this->action);
                }
                if (self::$autofindactive) {
                    $this->check_if_active();
                }
            }
            if (array_key_exists('parent', $properties)) {
                $this->set_parent($properties['parent']);
            }
        } else if (is_string($properties)) {
            $this->text = $properties;
        }
        if ($this->text === null) {
            throw new coding_exception('You must set the text for the node when you create it.');
        }
                $this->children = new navigation_node_collection();
    }

    
    public function check_if_active($strength=URL_MATCH_EXACT) {
        global $FULLME, $PAGE;
                if (self::$fullmeurl == null) {
            if ($PAGE->has_set_url()) {
                self::override_active_url(new moodle_url($PAGE->url));
            } else {
                self::override_active_url(new moodle_url($FULLME));
            }
        }

                if ($this->action instanceof moodle_url && $this->action->compare(self::$fullmeurl, $strength)) {
            $this->make_active();
            return true;
        }
        return false;
    }

    
    public static function override_active_url(moodle_url $url, $loadadmintree = false) {
                self::$fullmeurl = new moodle_url($url);
                if ($loadadmintree) {
                        self::$loadadmintree = true;
        }
    }

    
    public static function require_admin_tree() {
        self::$loadadmintree = true;
    }

    
    public static function create($text, $action=null, $type=self::TYPE_CUSTOM,
            $shorttext=null, $key=null, pix_icon $icon=null) {
                $itemarray = array(
            'text' => $text,
            'type' => $type
        );
                if ($action!==null) {
            $itemarray['action'] = $action;
        }
                if ($shorttext!==null) {
            $itemarray['shorttext'] = $shorttext;
        }
                if ($icon!==null) {
            $itemarray['icon'] = $icon;
        }
                $itemarray['key'] = $key;
                return new navigation_node($itemarray);
    }

    
    public function add($text, $action=null, $type=self::TYPE_CUSTOM, $shorttext=null, $key=null, pix_icon $icon=null) {
                $childnode = self::create($text, $action, $type, $shorttext, $key, $icon);

                return $this->add_node($childnode);
    }

    
    public function add_node(navigation_node $childnode, $beforekey=null) {
                if ($this->nodetype !== self::NODETYPE_BRANCH) {
            $this->nodetype = self::NODETYPE_BRANCH;
        }
                $childnode->set_parent($this);

                if ($childnode->key === null) {
            $childnode->key = $this->children->count();
        }

                $node = $this->children->add($childnode, $beforekey);

                        $type = $childnode->type;
        if (($type == self::TYPE_CATEGORY) || (isloggedin() && ($type == self::TYPE_COURSE)) || ($type == self::TYPE_MY_CATEGORY) ||
                ($type === self::TYPE_SITE_ADMIN)) {
            $node->nodetype = self::NODETYPE_BRANCH;
        }
                if ($this->hidden) {
            $node->hidden = true;
        }
                return $node;
    }

    
    public function get_children_key_list() {
        return $this->children->get_key_list();
    }

    
    public function find($key, $type) {
        return $this->children->find($key, $type);
    }

    
    public function get($key, $type=null) {
        return $this->children->get($key, $type);
    }

    
    public function remove() {
        return $this->parent->children->remove($this->key, $this->type);
    }

    
    public function has_children() {
        return ($this->nodetype === navigation_node::NODETYPE_BRANCH || $this->children->count()>0 || $this->isexpandable);
    }

    
    public function make_active() {
        $this->isactive = true;
        $this->add_class('active_tree_node');
        $this->force_open();
        if ($this->parent !== null) {
            $this->parent->make_inactive();
        }
    }

    
    public function make_inactive() {
        $this->isactive = false;
        $this->remove_class('active_tree_node');
        if ($this->parent !== null) {
            $this->parent->make_inactive();
        }
    }

    
    public function force_open() {
        $this->forceopen = true;
        if ($this->parent !== null) {
            $this->parent->force_open();
        }
    }

    
    public function add_class($class) {
        if (!in_array($class, $this->classes)) {
            $this->classes[] = $class;
        }
        return true;
    }

    
    public function remove_class($class) {
        if (in_array($class, $this->classes)) {
            $key = array_search($class,$this->classes);
            if ($key!==false) {
                unset($this->classes[$key]);
                return true;
            }
        }
        return false;
    }

    
    public function title($title) {
        $this->title = $title;
        $this->forcetitle = true;
    }

    
    public function __wakeup(){
        $this->forceopen = false;
        $this->isactive = false;
        $this->remove_class('active_tree_node');
    }

    
    public function contains_active_node() {
        if ($this->isactive) {
            return true;
        } else {
            foreach ($this->children as $child) {
                if ($child->isactive || $child->contains_active_node()) {
                    return true;
                }
            }
        }
        return false;
    }

    
    public function find_active_node() {
        if ($this->isactive) {
            return $this;
        } else {
            foreach ($this->children as &$child) {
                $outcome = $child->find_active_node();
                if ($outcome !== false) {
                    return $outcome;
                }
            }
        }
        return false;
    }

    
    public function search_for_active_node() {
        if ($this->check_if_active(URL_MATCH_BASE)) {
            return $this;
        } else {
            foreach ($this->children as &$child) {
                $outcome = $child->search_for_active_node();
                if ($outcome !== false) {
                    return $outcome;
                }
            }
        }
        return false;
    }

    
    public function get_content($shorttext=false) {
        if ($shorttext && $this->shorttext!==null) {
            return format_string($this->shorttext);
        } else {
            return format_string($this->text);
        }
    }

    
    public function get_title() {
        if ($this->forcetitle || $this->action != null){
            return $this->title;
        } else {
            return '';
        }
    }

    
    public function get_css_type() {
        if (array_key_exists($this->type, $this->namedtypes)) {
            return 'type_'.$this->namedtypes[$this->type];
        }
        return 'type_unknown';
    }

    
    public function find_expandable(array &$expandable) {
        foreach ($this->children as &$child) {
            if ($child->display && $child->has_children() && $child->children->count() == 0) {
                $child->id = 'expandable_branch_'.$child->type.'_'.clean_param($child->key, PARAM_ALPHANUMEXT);
                $this->add_class('canexpand');
                $child->requiresajaxloading = true;
                $expandable[] = array('id' => $child->id, 'key' => $child->key, 'type' => $child->type);
            }
            $child->find_expandable($expandable);
        }
    }

    
    public function find_all_of_type($type) {
        $nodes = $this->children->type($type);
        foreach ($this->children as &$node) {
            $childnodes = $node->find_all_of_type($type);
            $nodes = array_merge($nodes, $childnodes);
        }
        return $nodes;
    }

    
    public function trim_if_empty() {
        if ($this->children->count() == 0) {
            $this->remove();
        }
    }

    
    public function get_tabs_array(array $inactive=array(), $return=false) {
        $tabs = array();
        $rows = array();
        $selected = null;
        $activated = array();
        foreach ($this->children as $node) {
            $tabs[] = new tabobject($node->key, $node->action, $node->get_content(), $node->get_title());
            if ($node->contains_active_node()) {
                if ($node->children->count() > 0) {
                    $activated[] = $node->key;
                    foreach ($node->children as $child) {
                        if ($child->contains_active_node()) {
                            $selected = $child->key;
                        }
                        $rows[] = new tabobject($child->key, $child->action, $child->get_content(), $child->get_title());
                    }
                } else {
                    $selected = $node->key;
                }
            }
        }
        return array(array($tabs, $rows), $selected, $inactive, $activated, $return);
    }

    
    public function set_parent(navigation_node $parent) {
                $this->parent = $parent;
                if ($this->isactive) {
                        $this->parent->force_open();
                        $this->parent->make_inactive();
        }
    }

    
    public function hide(array $typestohide = null) {
        if ($typestohide === null || in_array($this->type, $typestohide)) {
            $this->display = false;
            if ($this->has_children()) {
                foreach ($this->children as $child) {
                    $child->hide($typestohide);
                }
            }
        }
    }
}


class navigation_node_collection implements IteratorAggregate {
    
    protected $collection = array();
    
    protected $orderedcollection = array();
    
    protected $last = null;
    
    protected $count = 0;

    
    public function add(navigation_node $node, $beforekey=null) {
        global $CFG;
        $key = $node->key;
        $type = $node->type;

                if (!array_key_exists($type, $this->orderedcollection)) {
            $this->orderedcollection[$type] = array();
        }
                if ($CFG->debug && array_key_exists($key, $this->orderedcollection[$type])) {
            debugging('Navigation node intersect: Adding a node that already exists '.$key, DEBUG_DEVELOPER);
        }

                $newindex = $this->count;
        $last = true;
        if ($beforekey !== null) {
            foreach ($this->collection as $index => $othernode) {
                if ($othernode->key === $beforekey) {
                    $newindex = $index;
                    $last = false;
                    break;
                }
            }
            if ($newindex === $this->count) {
                debugging('Navigation node add_before: Reference node not found ' . $beforekey .
                        ', options: ' . implode(' ', $this->get_key_list()), DEBUG_DEVELOPER);
            }
        }

                        $this->orderedcollection[$type][$key] = $node;
        if (!$last) {
                                    for ($oldindex = $this->count; $oldindex > $newindex; $oldindex--) {
                $this->collection[$oldindex] = $this->collection[$oldindex - 1];
            }
        }
                $this->collection[$newindex] = $this->orderedcollection[$type][$key];
                $this->last = $this->orderedcollection[$type][$key];

                if (!$last) {
            ksort($this->collection);
        }
        $this->count++;
                return $node;
    }

    
    public function get_key_list() {
        $keys = array();
        foreach ($this->collection as $node) {
            $keys[] = $node->key;
        }
        return $keys;
    }

    
    public function get($key, $type=null) {
        if ($type !== null) {
                        if (!empty($this->orderedcollection[$type][$key])) {
                return $this->orderedcollection[$type][$key];
            }
        } else {
                        foreach ($this->collection as $node) {
                if ($node->key === $key) {
                    return $node;
                }
            }
        }
        return false;
    }

    
    public function find($key, $type=null) {
        if ($type !== null && array_key_exists($type, $this->orderedcollection) && array_key_exists($key, $this->orderedcollection[$type])) {
            return $this->orderedcollection[$type][$key];
        } else {
            $nodes = $this->getIterator();
                        foreach ($nodes as &$node) {
                if ($node->key === $key && ($type === null || $type === $node->type)) {
                    return $node;
                }
            }
                        foreach ($nodes as &$node) {
                $result = $node->children->find($key, $type);
                if ($result !== false) {
                    return $result;
                }
            }
        }
        return false;
    }

    
    public function last() {
        return $this->last;
    }

    
    public function type($type) {
        if (!array_key_exists($type, $this->orderedcollection)) {
            $this->orderedcollection[$type] = array();
        }
        return $this->orderedcollection[$type];
    }
    
    public function remove($key, $type=null) {
        $child = $this->get($key, $type);
        if ($child !== false) {
            foreach ($this->collection as $colkey => $node) {
                if ($node->key === $key && (is_null($type) || $node->type == $type)) {
                    unset($this->collection[$colkey]);
                    $this->collection = array_values($this->collection);
                    break;
                }
            }
            unset($this->orderedcollection[$child->type][$child->key]);
            $this->count--;
            return true;
        }
        return false;
    }

    
    public function count() {
        return $this->count;
    }
    
    public function getIterator() {
        return new ArrayIterator($this->collection);
    }
}


class global_navigation extends navigation_node {
    
    protected $page;
    
    protected $initialised = false;
    
    protected $mycourses = array();
    
    protected $rootnodes = array();
    
    protected $showemptysections = true;
    
    protected $showcategories = null;
    
    protected $showmycategories = null;
    
    protected $extendforuser = array();
    
    protected $cache;
    
    protected $addedcourses = array();
    
    protected $allcategoriesloaded = false;
    
    protected $addedcategories = array();
    
    protected $expansionlimit = 0;
    
    protected $useridtouseforparentchecks = 0;
    
    protected $cacheexpandcourse = null;

    
    const LOAD_ROOT_CATEGORIES = 0;
    
    const LOAD_ALL_CATEGORIES = -1;

    
    public function __construct(moodle_page $page) {
        global $CFG, $SITE, $USER;

        if (during_initial_install()) {
            return;
        }

        if (get_home_page() == HOMEPAGE_SITE) {
                        $properties = array(
                'key' => 'home',
                'type' => navigation_node::TYPE_SYSTEM,
                'text' => get_string('home'),
                'action' => new moodle_url('/')
            );
        } else {
                        $properties = array(
                'key' => 'myhome',
                'type' => navigation_node::TYPE_SYSTEM,
                'text' => get_string('myhome'),
                'action' => new moodle_url('/my/')
            );
        }

                parent::__construct($properties);

                $this->page = $page;
        $this->forceopen = true;
        $this->cache = new navigation_cache(NAVIGATION_CACHE_NAME);
    }

    
    public function set_userid_for_parent_checks($userid) {
        $this->useridtouseforparentchecks = $userid;
    }


    
    public function initialise() {
        global $CFG, $SITE, $USER;
                if ($this->initialised || during_initial_install()) {
            return true;
        }
        $this->initialised = true;

                                                                        $this->rootnodes = array();
        if (get_home_page() == HOMEPAGE_SITE) {
                        if (isloggedin() && !isguestuser()) {                  $this->rootnodes['home'] = $this->add(get_string('myhome'), new moodle_url('/my/'), self::TYPE_SETTING, null, 'home');
            }
        } else {
                        $this->rootnodes['home'] = $this->add(get_string('sitehome'), new moodle_url('/'), self::TYPE_SETTING, null, 'home');
            if (!empty($CFG->defaulthomepage) && ($CFG->defaulthomepage == HOMEPAGE_MY)) {
                                $this->rootnodes['home']->action->param('redirect', '0');
            }
        }
        $this->rootnodes['site'] = $this->add_course($SITE);
        $this->rootnodes['myprofile'] = $this->add(get_string('profile'), null, self::TYPE_USER, null, 'myprofile');
        $this->rootnodes['currentcourse'] = $this->add(get_string('currentcourse'), null, self::TYPE_ROOTNODE, null, 'currentcourse');
        $this->rootnodes['mycourses'] = $this->add(get_string('mycourses'), null, self::TYPE_ROOTNODE, null, 'mycourses');
        $this->rootnodes['courses'] = $this->add(get_string('courses'), new moodle_url('/course/index.php'), self::TYPE_ROOTNODE, null, 'courses');
        $this->rootnodes['users'] = $this->add(get_string('users'), null, self::TYPE_ROOTNODE, null, 'users');

                        $this->add_front_page_course_essentials($this->rootnodes['site'], $SITE);
        $this->load_course_sections($SITE, $this->rootnodes['site']);

        $course = $this->page->course;

                $issite = ($this->page->course->id == $SITE->id);
                $enrolledinanycourse = enrol_user_sees_own_courses();

        $this->rootnodes['currentcourse']->mainnavonly = true;
        if ($enrolledinanycourse) {
            $this->rootnodes['mycourses']->isexpandable = true;
            if ($CFG->navshowallcourses) {
                                $this->rootnodes['courses']->isexpandable = true;
            }
        } else {
            $this->rootnodes['courses']->isexpandable = true;
        }

                        if (!empty($CFG->navexpandmycourses) && $this->page->pagelayout === 'mydashboard'){
            $this->rootnodes['mycourses']->forceopen = true;
            $this->load_courses_enrolled();
        } else {
            $this->rootnodes['mycourses']->collapse = true;
            $this->rootnodes['mycourses']->make_inactive();
        }

        $canviewcourseprofile = true;

                switch ($this->page->context->contextlevel) {
            case CONTEXT_SYSTEM :
                                break;
            case CONTEXT_COURSECAT :
                                $this->load_all_categories($this->page->context->instanceid, true);
                break;
            case CONTEXT_BLOCK :
            case CONTEXT_COURSE :
                if ($issite) {
                                        break;
                }

                                $coursenode = $this->add_course($course, false, self::COURSE_CURRENT);
                                if (!$coursenode) {
                    $canviewcourseprofile = false;
                    break;
                }

                                
                                if (!can_access_course($course, null, '', true)) {
                    if ($coursenode->isexpandable === true) {
                                                                                                $this->get_expand_course_cache()->set($course->id, 1);
                        $coursenode->isexpandable = true;
                        $coursenode->nodetype = self::NODETYPE_BRANCH;
                    }
                                                            if (!$this->current_user_is_parent_role()) {
                        $coursenode->make_active();
                        $canviewcourseprofile = false;
                        break;
                    }
                } else if ($coursenode->isexpandable === false) {
                                                                                $this->get_expand_course_cache()->set($course->id, 1);
                    $coursenode->isexpandable = true;
                    $coursenode->nodetype = self::NODETYPE_BRANCH;
                }

                                $this->add_course_essentials($coursenode, $course);
                                $this->load_course_sections($course, $coursenode);
                if (!$coursenode->contains_active_node() && !$coursenode->search_for_active_node()) {
                    $coursenode->make_active();
                }

                break;
            case CONTEXT_MODULE :
                if ($issite) {
                                                                                                    $activitynode = $this->rootnodes['site']->find($this->page->cm->id, navigation_node::TYPE_ACTIVITY);
                    if ($activitynode) {
                        $this->load_activity($this->page->cm, $this->page->course, $activitynode);
                    }
                    break;
                }

                $course = $this->page->course;
                $cm = $this->page->cm;

                                $coursenode = $this->add_course($course, false, self::COURSE_CURRENT);

                                if (!$coursenode) {
                    $canviewcourseprofile = false;
                    break;
                }

                                                if (!can_access_course($course, null, '', true)) {
                    $coursenode->make_active();
                    $canviewcourseprofile = false;
                    break;
                }

                $this->add_course_essentials($coursenode, $course);

                                $this->load_course_sections($course, $coursenode, null, $cm);
                $activity = $coursenode->find($cm->id, navigation_node::TYPE_ACTIVITY);
                if (!empty($activity)) {
                                        $this->load_activity($cm, $course, $activity);
                                        if (!$activity->contains_active_node() && !$activity->search_for_active_node()) {
                                                $activity->make_active();
                    }
                }
                break;
            case CONTEXT_USER :
                if ($issite) {
                                                            break;
                }
                $course = $this->page->course;
                                $coursenode = $this->add_course($course, false, self::COURSE_CURRENT);

                                if (!$coursenode) {
                    $canviewcourseprofile = false;
                    break;
                }

                                                if (!can_access_course($course, null, '', true)) {
                    $coursenode->make_active();
                    $canviewcourseprofile = false;
                    break;
                }
                $this->add_course_essentials($coursenode, $course);
                $this->load_course_sections($course, $coursenode);
                break;
        }

                $this->load_for_user();
        if ($this->page->context->contextlevel >= CONTEXT_COURSE && $this->page->context->instanceid != $SITE->id && $canviewcourseprofile) {
            $this->load_for_user(null, true);
        }
                foreach ($this->extendforuser as $user) {
            if ($user->id != $USER->id) {
                $this->load_for_user($user);
            }
        }

                foreach (get_plugin_list_with_function('local', 'extend_navigation') as $function) {
            $function($this);
        }

  if(isset($this->rootnodes['home'])) {$this->rootnodes['home']->remove();}       // by YCJ
  if(isset($this->rootnodes['site'])) {$this->rootnodes['site']->remove();}

                foreach ($this->rootnodes as $node) {
                        
            if ($node->key !== 'home' && !$node->has_children() && !$node->isactive) {
                $node->remove();
            }
        }

        if (!$this->contains_active_node()) {
            $this->search_for_active_node();
        }

                        if (!isloggedin()) {
            $activities = clone($this->rootnodes['site']->children);
            $this->rootnodes['site']->remove();
            $children = clone($this->children);
            $this->children = new navigation_node_collection();
            foreach ($activities as $child) {
                $this->children->add($child);
            }
            foreach ($children as $child) {
                $this->children->add($child);
            }
        }
        return true;
    }

    
    protected function current_user_is_parent_role() {
        global $USER, $DB;
        if ($this->useridtouseforparentchecks && $this->useridtouseforparentchecks != $USER->id) {
            $usercontext = context_user::instance($this->useridtouseforparentchecks, MUST_EXIST);
            if (!has_capability('moodle/user:viewdetails', $usercontext)) {
                return false;
            }
            if ($DB->record_exists('role_assignments', array('userid' => $USER->id, 'contextid' => $usercontext->id))) {
                return true;
            }
        }
        return false;
    }

    
    protected function show_categories($ismycourse = false) {
        global $CFG, $DB;
        if ($ismycourse) {
            return $this->show_my_categories();
        }
        if ($this->showcategories === null) {
            $show = false;
            if ($this->page->context->contextlevel == CONTEXT_COURSECAT) {
                $show = true;
            } else if (!empty($CFG->navshowcategories) && $DB->count_records('course_categories') > 1) {
                $show = true;
            }
            $this->showcategories = $show;
        }
        return $this->showcategories;
    }

    
    protected function show_my_categories() {
        global $CFG, $DB;
        if ($this->showmycategories === null) {
            $this->showmycategories = !empty($CFG->navshowmycoursecategories) && $DB->count_records('course_categories') > 1;
        }
        return $this->showmycategories;
    }

    
    protected function load_all_courses($categoryids = null) {
        global $CFG, $DB, $SITE;

                $limit = 20;
        if (!empty($CFG->navcourselimit)) {
            $limit = $CFG->navcourselimit;
        }

        $toload = (empty($CFG->navshowallcourses))?self::LOAD_ROOT_CATEGORIES:self::LOAD_ALL_CATEGORIES;

                        if ($this->show_categories()) {
            $this->load_all_categories($toload);
        }

                $coursenodes = array();

                if ($this->show_categories()) {
                                                if ($categoryids !== null) {
                if (!is_array($categoryids)) {
                    $categoryids = array($categoryids);
                }
                list($categorywhere, $categoryparams) = $DB->get_in_or_equal($categoryids, SQL_PARAMS_NAMED, 'cc');
                $categorywhere = 'WHERE cc.id '.$categorywhere;
            } else if ($toload == self::LOAD_ROOT_CATEGORIES) {
                $categorywhere = 'WHERE cc.depth = 1 OR cc.depth = 2';
                $categoryparams = array();
            } else {
                $categorywhere = '';
                $categoryparams = array();
            }

                                    $sql = "SELECT cc.id, COUNT(c.id) AS coursecount
                        FROM {course_categories} cc
                    LEFT JOIN {course} c ON c.category = cc.id
                            {$categorywhere}
                    GROUP BY cc.id";
            $categories = $DB->get_recordset_sql($sql, $categoryparams);
            $fullfetch = array();
            $partfetch = array();
            foreach ($categories as $category) {
                if (!$this->can_add_more_courses_to_category($category->id)) {
                    continue;
                }
                if ($category->coursecount > $limit * 5) {
                    $partfetch[] = $category->id;
                } else if ($category->coursecount > 0) {
                    $fullfetch[] = $category->id;
                }
            }
            $categories->close();

            if (count($fullfetch)) {
                                                list($categoryids, $categoryparams) = $DB->get_in_or_equal($fullfetch, SQL_PARAMS_NAMED, 'lcategory');
                $ccselect = ', ' . context_helper::get_preload_record_columns_sql('ctx');
                $ccjoin = "LEFT JOIN {context} ctx ON (ctx.instanceid = c.id AND ctx.contextlevel = :contextlevel)";
                $categoryparams['contextlevel'] = CONTEXT_COURSE;
                $sql = "SELECT c.id, c.sortorder, c.visible, c.fullname, c.shortname, c.category $ccselect
                            FROM {course} c
                                $ccjoin
                            WHERE c.category {$categoryids}
                        ORDER BY c.sortorder ASC";
                $coursesrs = $DB->get_recordset_sql($sql, $categoryparams);
                foreach ($coursesrs as $course) {
                    if ($course->id == $SITE->id) {
                                                continue;
                    }
                    if (array_key_exists($course->id, $this->addedcourses)) {
                                                                                                continue;
                    }
                    if (!$this->can_add_more_courses_to_category($course->category)) {
                        continue;
                    }
                    context_helper::preload_from_record($course);
                    if (!$course->visible && !is_role_switched($course->id) && !has_capability('moodle/course:viewhiddencourses', context_course::instance($course->id))) {
                        continue;
                    }
                    $coursenodes[$course->id] = $this->add_course($course);
                }
                $coursesrs->close();
            }

            if (count($partfetch)) {
                                                foreach ($partfetch as $categoryid) {
                    $ccselect = ', ' . context_helper::get_preload_record_columns_sql('ctx');
                    $ccjoin = "LEFT JOIN {context} ctx ON (ctx.instanceid = c.id AND ctx.contextlevel = :contextlevel)";
                    $sql = "SELECT c.id, c.sortorder, c.visible, c.fullname, c.shortname, c.category $ccselect
                                FROM {course} c
                                    $ccjoin
                                WHERE c.category = :categoryid
                            ORDER BY c.sortorder ASC";
                    $courseparams = array('categoryid' => $categoryid, 'contextlevel' => CONTEXT_COURSE);
                    $coursesrs = $DB->get_recordset_sql($sql, $courseparams, 0, $limit * 5);
                    foreach ($coursesrs as $course) {
                        if ($course->id == $SITE->id) {
                                                        continue;
                        }
                        if (array_key_exists($course->id, $this->addedcourses)) {
                                                                                                                                            continue;
                        }
                        if (!$this->can_add_more_courses_to_category($course->category)) {
                            break;
                        }
                        context_helper::preload_from_record($course);
                        if (!$course->visible && !is_role_switched($course->id) && !has_capability('moodle/course:viewhiddencourses', context_course::instance($course->id))) {
                            continue;
                        }
                        $coursenodes[$course->id] = $this->add_course($course);
                    }
                    $coursesrs->close();
                }
            }
        } else {
                        list($courseids, $courseparams) = $DB->get_in_or_equal(array_keys($this->addedcourses), SQL_PARAMS_NAMED, 'lc', false);
            $ccselect = ', ' . context_helper::get_preload_record_columns_sql('ctx');
            $ccjoin = "LEFT JOIN {context} ctx ON (ctx.instanceid = c.id AND ctx.contextlevel = :contextlevel)";
            $courseparams['contextlevel'] = CONTEXT_COURSE;
            $sql = "SELECT c.id, c.sortorder, c.visible, c.fullname, c.shortname, c.category $ccselect
                        FROM {course} c
                            $ccjoin
                        WHERE c.id {$courseids}
                    ORDER BY c.sortorder ASC";
            $coursesrs = $DB->get_recordset_sql($sql, $courseparams);
            foreach ($coursesrs as $course) {
                if ($course->id == $SITE->id) {
                                        continue;
                }
                if ($this->page->course && ($this->page->course->id == $course->id)) {
                                        continue;
                }
                context_helper::preload_from_record($course);
                if (!$course->visible && !is_role_switched($course->id) && !has_capability('moodle/course:viewhiddencourses', context_course::instance($course->id))) {
                    continue;
                }
                $coursenodes[$course->id] = $this->add_course($course);
                if (count($coursenodes) >= $limit) {
                    break;
                }
            }
            $coursesrs->close();
        }

        return $coursenodes;
    }

    
    protected function can_add_more_courses_to_category($category) {
        global $CFG;
        $limit = 20;
        if (!empty($CFG->navcourselimit)) {
            $limit = (int)$CFG->navcourselimit;
        }
        if (is_numeric($category)) {
            if (!array_key_exists($category, $this->addedcategories)) {
                return true;
            }
            $coursecount = count($this->addedcategories[$category]->children->type(self::TYPE_COURSE));
        } else if ($category instanceof navigation_node) {
            if (($category->type != self::TYPE_CATEGORY) || ($category->type != self::TYPE_MY_CATEGORY)) {
                return false;
            }
            $coursecount = count($category->children->type(self::TYPE_COURSE));
        } else if (is_object($category) && property_exists($category,'id')) {
            $coursecount = count($this->addedcategories[$category->id]->children->type(self::TYPE_COURSE));
        }
        return ($coursecount <= $limit);
    }

    
    protected function load_all_categories($categoryid = self::LOAD_ROOT_CATEGORIES, $showbasecategories = false) {
        global $CFG, $DB;

                if ($this->allcategoriesloaded || ($categoryid < 1 && $this->is_category_fully_loaded($categoryid))) {
            return true;
        }

        $catcontextsql = context_helper::get_preload_record_columns_sql('ctx');
        $sqlselect = "SELECT cc.*, $catcontextsql
                      FROM {course_categories} cc
                      JOIN {context} ctx ON cc.id = ctx.instanceid";
        $sqlwhere = "WHERE ctx.contextlevel = ".CONTEXT_COURSECAT;
        $sqlorder = "ORDER BY cc.depth ASC, cc.sortorder ASC, cc.id ASC";
        $params = array();

        $categoriestoload = array();
        if ($categoryid == self::LOAD_ALL_CATEGORIES) {
                                } else if ($categoryid == self::LOAD_ROOT_CATEGORIES) {                         $sqlwhere .= " AND cc.parent = 0";
        } else if (array_key_exists($categoryid, $this->addedcategories)) {
                                    $addedcategories = $this->addedcategories;
            unset($addedcategories[$categoryid]);
            if (count($addedcategories) > 0) {
                list($sql, $params) = $DB->get_in_or_equal(array_keys($addedcategories), SQL_PARAMS_NAMED, 'parent', false);
                if ($showbasecategories) {
                                        $sqlwhere .= " AND (cc.parent = :categoryid OR cc.parent = 0) AND cc.parent {$sql}";
                } else {
                                        $sqlwhere .= " AND cc.parent = :categoryid AND cc.parent {$sql}";
                }
            }
            $params['categoryid'] = $categoryid;
        } else {
                                    $category = $DB->get_record('course_categories', array('id' => $categoryid), 'path', MUST_EXIST);
            $categoriestoload = explode('/', trim($category->path, '/'));
            list($select, $params) = $DB->get_in_or_equal($categoriestoload);
                        $params = array_merge($params, $params);
            $basecategorysql = ($showbasecategories)?' OR cc.depth = 1':'';
            $sqlwhere .= " AND (cc.id {$select} OR cc.parent {$select}{$basecategorysql})";
        }

        $categoriesrs = $DB->get_recordset_sql("$sqlselect $sqlwhere $sqlorder", $params);
        $categories = array();
        foreach ($categoriesrs as $category) {
                                    context_helper::preload_from_record($category);
            if (array_key_exists($category->id, $this->addedcategories)) {
                            } else if ($category->parent == '0') {
                                $this->add_category($category, $this->rootnodes['courses']);
            } else if (array_key_exists($category->parent, $this->addedcategories)) {
                                $this->add_category($category, $this->addedcategories[$category->parent]);
            } else {
                $categories[] = $category;
            }
        }
        $categoriesrs->close();

                while (!empty($categories)) {
            $category = reset($categories);
            if (array_key_exists($category->id, $this->addedcategories)) {
                            } else if ($category->parent == '0') {
                $this->add_category($category, $this->rootnodes['courses']);
            } else if (array_key_exists($category->parent, $this->addedcategories)) {
                $this->add_category($category, $this->addedcategories[$category->parent]);
            } else {
                                                $path = explode('/', trim($category->path, '/'));
                foreach ($path as $catid) {
                    if (!array_key_exists($catid, $this->addedcategories)) {
                                                $subcategory = $categories[$catid];
                        if ($subcategory->parent == '0') {
                                                                                    $this->add_category($subcategory, $this->rootnodes['courses']);
                        } else if (array_key_exists($subcategory->parent, $this->addedcategories)) {
                                                        $this->add_category($subcategory, $this->addedcategories[$subcategory->parent]);
                                                        unset($categories[$catid]);
                        } else {
                                                                                    throw new coding_exception('Category path order is incorrect and/or there are missing categories');
                        }
                    }
                }
            }
                        unset($categories[$category->id]);
        }
        if ($categoryid === self::LOAD_ALL_CATEGORIES) {
            $this->allcategoriesloaded = true;
        }
                if (count($categoriestoload) > 0) {
            $readytoloadcourses = array();
            foreach ($categoriestoload as $category) {
                if ($this->can_add_more_courses_to_category($category)) {
                    $readytoloadcourses[] = $category;
                }
            }
            if (count($readytoloadcourses)) {
                $this->load_all_courses($readytoloadcourses);
            }
        }

                if (!empty($this->addedcategories)) {
            $categoryids = array();
            foreach ($this->addedcategories as $category) {
                if ($this->can_add_more_courses_to_category($category)) {
                    $categoryids[] = $category->key;
                }
            }
            if ($categoryids) {
                list($categoriessql, $params) = $DB->get_in_or_equal($categoryids, SQL_PARAMS_NAMED);
                $params['limit'] = (!empty($CFG->navcourselimit))?$CFG->navcourselimit:20;
                $sql = "SELECT cc.id, COUNT(c.id) AS coursecount
                          FROM {course_categories} cc
                          JOIN {course} c ON c.category = cc.id
                         WHERE cc.id {$categoriessql}
                      GROUP BY cc.id
                        HAVING COUNT(c.id) > :limit";
                $excessivecategories = $DB->get_records_sql($sql, $params);
                foreach ($categories as &$category) {
                    if (array_key_exists($category->key, $excessivecategories) && !$this->can_add_more_courses_to_category($category)) {
                        $url = new moodle_url('/course/index.php', array('categoryid' => $category->key));
                        $category->add(get_string('viewallcourses'), $url, self::TYPE_SETTING);
                    }
                }
            }
        }
    }

    
    protected function add_category(stdClass $category, navigation_node $parent, $nodetype = self::TYPE_CATEGORY) {
        if (array_key_exists($category->id, $this->addedcategories)) {
            return;
        }
        $url = new moodle_url('/course/index.php', array('categoryid' => $category->id));
        $context = context_coursecat::instance($category->id);
        $categoryname = format_string($category->name, true, array('context' => $context));
        $categorynode = $parent->add($categoryname, $url, $nodetype, $categoryname, $category->id);
        if (empty($category->visible)) {
            if (has_capability('moodle/category:viewhiddencategories', context_system::instance())) {
                $categorynode->hidden = true;
            } else {
                $categorynode->display = false;
            }
        }
        $this->addedcategories[$category->id] = $categorynode;
    }

    
    protected function load_course(stdClass $course) {
        global $SITE;
        if ($course->id == $SITE->id) {
                        return $this->rootnodes['site'];
        } else if (array_key_exists($course->id, $this->addedcourses)) {
                        return $this->addedcourses[$course->id];
        } else {
                        return $this->add_course($course);
        }
    }

    
    protected function load_course_sections(stdClass $course, navigation_node $coursenode, $sectionnum = null, $cm = null) {
        global $CFG, $SITE;
        require_once($CFG->dirroot.'/course/lib.php');
        if (isset($cm->sectionnum)) {
            $sectionnum = $cm->sectionnum;
        }
        if ($sectionnum !== null) {
            $this->includesectionnum = $sectionnum;
        }
        course_get_format($course)->extend_course_navigation($this, $coursenode, $sectionnum, $cm);
        if (isset($cm->id)) {
            $activity = $coursenode->find($cm->id, self::TYPE_ACTIVITY);
            if (empty($activity)) {
                $activity = $this->load_stealth_activity($coursenode, get_fast_modinfo($course));
            }
        }
   }

    
    protected function generate_sections_and_activities(stdClass $course) {
        global $CFG;
        require_once($CFG->dirroot.'/course/lib.php');

        $modinfo = get_fast_modinfo($course);
        $sections = $modinfo->get_section_info_all();

                $courseformatoptions = course_get_format($course)->get_format_options();
        if (isset($courseformatoptions['numsections'])) {
            $sections = array_slice($sections, 0, $courseformatoptions['numsections']+1, true);
        }

        $activities = array();

        foreach ($sections as $key => $section) {
                        $sections[$key] = clone($section);
            unset($sections[$key]->summary);
            $sections[$key]->hasactivites = false;
            if (!array_key_exists($section->section, $modinfo->sections)) {
                continue;
            }
            foreach ($modinfo->sections[$section->section] as $cmid) {
                $cm = $modinfo->cms[$cmid];
                $activity = new stdClass;
                $activity->id = $cm->id;
                $activity->course = $course->id;
                $activity->section = $section->section;
                $activity->name = $cm->name;
                $activity->icon = $cm->icon;
                $activity->iconcomponent = $cm->iconcomponent;
                $activity->hidden = (!$cm->visible);
                $activity->modname = $cm->modname;
                $activity->nodetype = navigation_node::NODETYPE_LEAF;
                $activity->onclick = $cm->onclick;
                $url = $cm->url;
                if (!$url) {
                    $activity->url = null;
                    $activity->display = false;
                } else {
                    $activity->url = $url->out();
                    $activity->display = $cm->uservisible ? true : false;
                    if (self::module_extends_navigation($cm->modname)) {
                        $activity->nodetype = navigation_node::NODETYPE_BRANCH;
                    }
                }
                $activities[$cmid] = $activity;
                if ($activity->display) {
                    $sections[$key]->hasactivites = true;
                }
            }
        }

        return array($sections, $activities);
    }

    
    public function load_generic_course_sections(stdClass $course, navigation_node $coursenode) {
        global $CFG, $DB, $USER, $SITE;
        require_once($CFG->dirroot.'/course/lib.php');

        list($sections, $activities) = $this->generate_sections_and_activities($course);

        $navigationsections = array();
        foreach ($sections as $sectionid => $section) {
            $section = clone($section);
            if ($course->id == $SITE->id) {
                $this->load_section_activities($coursenode, $section->section, $activities);
            } else {
                if (!$section->uservisible || (!$this->showemptysections &&
                        !$section->hasactivites && $this->includesectionnum !== $section->section)) {
                    continue;
                }

                $sectionname = get_section_name($course, $section);
                $url = course_get_url($course, $section->section, array('navigation' => true));

                $sectionnode = $coursenode->add($sectionname, $url, navigation_node::TYPE_SECTION, null, $section->id);
                $sectionnode->nodetype = navigation_node::NODETYPE_BRANCH;
                $sectionnode->hidden = (!$section->visible || !$section->available);
                if ($this->includesectionnum !== false && $this->includesectionnum == $section->section) {
                    $this->load_section_activities($sectionnode, $section->section, $activities);
                }
                $section->sectionnode = $sectionnode;
                $navigationsections[$sectionid] = $section;
            }
        }
        return $navigationsections;
    }

    
    protected function load_section_activities(navigation_node $sectionnode, $sectionnumber, array $activities, $course = null) {
        global $CFG, $SITE;
                static $legacyonclickcounter = 0;

        $activitynodes = array();
        if (empty($activities)) {
            return $activitynodes;
        }

        if (!is_object($course)) {
            $activity = reset($activities);
            $courseid = $activity->course;
        } else {
            $courseid = $course->id;
        }
        $showactivities = ($courseid != $SITE->id || !empty($CFG->navshowfrontpagemods));

        foreach ($activities as $activity) {
            if ($activity->section != $sectionnumber) {
                continue;
            }
            if ($activity->icon) {
                $icon = new pix_icon($activity->icon, get_string('modulename', $activity->modname), $activity->iconcomponent);
            } else {
                $icon = new pix_icon('icon', get_string('modulename', $activity->modname), $activity->modname);
            }

                        $activityname = format_string($activity->name, true, array('context' => context_module::instance($activity->id)));
            $action = new moodle_url($activity->url);

                        if (!empty($activity->onclick)) {
                                $legacyonclickcounter++;
                                $functionname = 'legacy_activity_onclick_handler_'.$legacyonclickcounter;
                $propogrationhandler = '';
                                                                if (strpos($activity->onclick, 'return false')) {
                    $propogrationhandler = 'e.halt();';
                }
                                $onclick = htmlspecialchars_decode($activity->onclick, ENT_QUOTES);
                                $jscode = "function {$functionname}(e) { $propogrationhandler $onclick }";
                $this->page->requires->js_init_code($jscode);
                                $action = new action_link($action, $activityname, new component_action('click', $functionname));
            }

            $activitynode = $sectionnode->add($activityname, $action, navigation_node::TYPE_ACTIVITY, null, $activity->id, $icon);
            $activitynode->title(get_string('modulename', $activity->modname));
            $activitynode->hidden = $activity->hidden;
            $activitynode->display = $showactivities && $activity->display;
            $activitynode->nodetype = $activity->nodetype;
            $activitynodes[$activity->id] = $activitynode;
        }

        return $activitynodes;
    }
    
    protected function load_stealth_activity(navigation_node $coursenode, $modinfo) {
        if (empty($modinfo->cms[$this->page->cm->id])) {
            return null;
        }
        $cm = $modinfo->cms[$this->page->cm->id];
        if ($cm->icon) {
            $icon = new pix_icon($cm->icon, get_string('modulename', $cm->modname), $cm->iconcomponent);
        } else {
            $icon = new pix_icon('icon', get_string('modulename', $cm->modname), $cm->modname);
        }
        $url = $cm->url;
        $activitynode = $coursenode->add(format_string($cm->name), $url, navigation_node::TYPE_ACTIVITY, null, $cm->id, $icon);
        $activitynode->title(get_string('modulename', $cm->modname));
        $activitynode->hidden = (!$cm->visible);
        if (!$cm->uservisible) {
                                    $activitynode->display = false;
        } else if (!$url) {
                        $activitynode->display = false;
        } else if (self::module_extends_navigation($cm->modname)) {
            $activitynode->nodetype = navigation_node::NODETYPE_BRANCH;
        }
        return $activitynode;
    }
    
    protected function load_activity($cm, stdClass $course, navigation_node $activity) {
        global $CFG, $DB;

                if (!($cm instanceof cm_info)) {
            $modinfo = get_fast_modinfo($course);
            $cm = $modinfo->get_cm($cm->id);
        }
        $activity->nodetype = navigation_node::NODETYPE_LEAF;
        $activity->make_active();
        $file = $CFG->dirroot.'/mod/'.$cm->modname.'/lib.php';
        $function = $cm->modname.'_extend_navigation';

        if (file_exists($file)) {
            require_once($file);
            if (function_exists($function)) {
                $activtyrecord = $DB->get_record($cm->modname, array('id' => $cm->instance), '*', MUST_EXIST);
                $function($activity, $course, $activtyrecord, $cm);
            }
        }

                $featuresfunc = $cm->modname.'_supports';
        if (function_exists($featuresfunc) && $featuresfunc(FEATURE_ADVANCED_GRADING)) {
            require_once($CFG->dirroot.'/grade/grading/lib.php');
            $gradingman = get_grading_manager($cm->context,  'mod_'.$cm->modname);
            $gradingman->extend_navigation($this, $activity);
        }

        return $activity->has_children();
    }
    
    protected function load_for_user($user=null, $forceforcontext=false) {
        global $DB, $CFG, $USER, $SITE;

        if ($user === null) {
                                    if (!isloggedin() || isguestuser()) {
                return false;
            }
            $user = $USER;
        } else if (!is_object($user)) {
                        $select = context_helper::get_preload_record_columns_sql('ctx');
            $sql = "SELECT u.*, $select
                      FROM {user} u
                      JOIN {context} ctx ON u.id = ctx.instanceid
                     WHERE u.id = :userid AND
                           ctx.contextlevel = :contextlevel";
            $user = $DB->get_record_sql($sql, array('userid' => (int)$user, 'contextlevel' => CONTEXT_USER), MUST_EXIST);
            context_helper::preload_from_record($user);
        }

        $iscurrentuser = ($user->id == $USER->id);

        $usercontext = context_user::instance($user->id);

                $course = $this->page->course;
        $baseargs = array('id'=>$user->id);
        if ($course->id != $SITE->id && (!$iscurrentuser || $forceforcontext)) {
            $coursenode = $this->add_course($course, false, self::COURSE_CURRENT);
            $baseargs['course'] = $course->id;
            $coursecontext = context_course::instance($course->id);
            $issitecourse = false;
        } else {
                        $coursecontext = context_system::instance();
            $issitecourse = true;
        }

                $usersnode = null;
        if (!$issitecourse) {
                        $usersnode = $coursenode->get('participants', navigation_node::TYPE_CONTAINER);
            $userviewurl = new moodle_url('/user/view.php', $baseargs);
        } else if ($USER->id != $user->id) {
                        $usersnode = $this->rootnodes['users'];
            if (has_capability('moodle/course:viewparticipants', $coursecontext)) {
                $usersnode->action = new moodle_url('/user/index.php', array('id' => $course->id));
            }
            $userviewurl = new moodle_url('/user/profile.php', $baseargs);
        }
        if (!$usersnode) {
                                                                        return false;
        }
                $canseefullname = has_capability('moodle/site:viewfullnames', $coursecontext);
        $usernode = $usersnode->add(fullname($user, $canseefullname), $userviewurl, self::TYPE_USER, null, 'user' . $user->id);
        if ($this->page->context->contextlevel == CONTEXT_USER && $user->id == $this->page->context->instanceid) {
            $usernode->make_active();
        }

                if ($issitecourse) {

                                    if ($iscurrentuser || has_capability('moodle/user:viewdetails', $coursecontext) ||
                    has_capability('moodle/user:viewdetails', $usercontext)) {
                if ($issitecourse || ($iscurrentuser && !$forceforcontext)) {
                    $usernode->add(get_string('viewprofile'), new moodle_url('/user/profile.php', $baseargs));
                } else {
                    $usernode->add(get_string('viewprofile'), new moodle_url('/user/view.php', $baseargs));
                }
            }

            if (!empty($CFG->navadduserpostslinks)) {
                                                                $forumtab = $usernode->add(get_string('forumposts', 'forum'));
                $forumtab->add(get_string('posts', 'forum'), new moodle_url('/mod/forum/user.php', $baseargs));
                $forumtab->add(get_string('discussions', 'forum'), new moodle_url('/mod/forum/user.php',
                        array_merge($baseargs, array('mode' => 'discussions'))));
            }

                        if (!empty($CFG->enableblogs)) {
                if (!$this->cache->cached('userblogoptions'.$user->id)) {
                    require_once($CFG->dirroot.'/blog/lib.php');
                                        $options = blog_get_options_for_user($user);
                    $this->cache->set('userblogoptions'.$user->id, $options);
                } else {
                    $options = $this->cache->{'userblogoptions'.$user->id};
                }

                if (count($options) > 0) {
                    $blogs = $usernode->add(get_string('blogs', 'blog'), null, navigation_node::TYPE_CONTAINER);
                    foreach ($options as $type => $option) {
                        if ($type == "rss") {
                            $blogs->add($option['string'], $option['link'], settings_navigation::TYPE_SETTING, null, null,
                                    new pix_icon('i/rss', ''));
                        } else {
                            $blogs->add($option['string'], $option['link']);
                        }
                    }
                }
            }

                                    if (!empty($CFG->messaging)) {
                $messageargs = array('user1' => $USER->id);
                if ($USER->id != $user->id) {
                    $messageargs['user2'] = $user->id;
                }
                if ($course->id != $SITE->id) {
                    $messageargs['viewing'] = MESSAGE_VIEW_COURSE. $course->id;
                }
                $url = new moodle_url('/message/index.php', $messageargs);
                $usernode->add(get_string('messages', 'message'), $url, self::TYPE_SETTING, null, 'messages');
            }

                                    if ($issitecourse && $iscurrentuser && has_capability('moodle/user:manageownfiles', $usercontext)) {
                $url = new moodle_url('/user/files.php');
                $usernode->add(get_string('privatefiles'), $url, self::TYPE_SETTING);
            }

                        if (!empty($CFG->enablenotes) &&
                    has_any_capability(array('moodle/notes:manage', 'moodle/notes:view'), $coursecontext)) {
                $url = new moodle_url('/notes/index.php', array('user' => $user->id));
                if ($coursecontext->instanceid != SITEID) {
                    $url->param('course', $coursecontext->instanceid);
                }
                $usernode->add(get_string('notes', 'notes'), $url);
            }

                        if (($issitecourse && $iscurrentuser) || has_capability('moodle/user:viewdetails', $usercontext)) {
                require_once($CFG->dirroot . '/user/lib.php');
                                if ($course->id == SITEID) {
                    $url = user_mygrades_url($user->id, $course->id);
                } else {                     $url = new moodle_url('/course/user.php', array('mode' => 'grade', 'id' => $course->id, 'user' => $user->id));
                }
                if ($USER->id != $user->id) {
                    $usernode->add(get_string('grades', 'grades'), $url, self::TYPE_SETTING, null, 'usergrades');
                } else {
                    $usernode->add(get_string('grades', 'grades'), $url);
                }
            }

                        $hiddenfields = array_flip(explode(',', $CFG->hiddenuserfields));
            if (!$iscurrentuser &&
                    $course->id == $SITE->id &&
                    has_capability('moodle/user:viewdetails', $usercontext) &&
                    (!in_array('mycourses', $hiddenfields) || has_capability('moodle/user:viewhiddendetails', $coursecontext))) {

                                $reports = core_component::get_plugin_list('gradereport');
                arsort($reports); 
                $userscourses = enrol_get_users_courses($user->id);
                $userscoursesnode = $usernode->add(get_string('courses'));

                $count = 0;
                foreach ($userscourses as $usercourse) {
                    if ($count === (int)$CFG->navcourselimit) {
                        $url = new moodle_url('/user/profile.php', array('id' => $user->id, 'showallcourses' => 1));
                        $userscoursesnode->add(get_string('showallcourses'), $url);
                        break;
                    }
                    $count++;
                    $usercoursecontext = context_course::instance($usercourse->id);
                    $usercourseshortname = format_string($usercourse->shortname, true, array('context' => $usercoursecontext));
                    $usercoursenode = $userscoursesnode->add($usercourseshortname, new moodle_url('/user/view.php',
                            array('id' => $user->id, 'course' => $usercourse->id)), self::TYPE_CONTAINER);

                    $gradeavailable = has_capability('moodle/grade:viewall', $usercoursecontext);
                    if (!$gradeavailable && !empty($usercourse->showgrades) && is_array($reports) && !empty($reports)) {
                        foreach ($reports as $plugin => $plugindir) {
                            if (has_capability('gradereport/'.$plugin.':view', $usercoursecontext)) {
                                                                $gradeavailable = true;
                                break;
                            }
                        }
                    }

                    if ($gradeavailable) {
                        $url = new moodle_url('/grade/report/index.php', array('id' => $usercourse->id));
                        $usercoursenode->add(get_string('grades'), $url, self::TYPE_SETTING, null, null,
                                new pix_icon('i/grades', ''));
                    }

                                        if (!empty($CFG->enablenotes) &&
                            has_any_capability(array('moodle/notes:manage', 'moodle/notes:view'), $usercoursecontext)) {
                        $url = new moodle_url('/notes/index.php', array('user' => $user->id, 'course' => $usercourse->id));
                        $usercoursenode->add(get_string('notes', 'notes'), $url, self::TYPE_SETTING);
                    }

                    if (can_access_course($usercourse, $user->id, '', true)) {
                        $usercoursenode->add(get_string('entercourse'), new moodle_url('/course/view.php',
                                array('id' => $usercourse->id)), self::TYPE_SETTING, null, null, new pix_icon('i/course', ''));
                    }

                    $reporttab = $usercoursenode->add(get_string('activityreports'));

                    $reports = get_plugin_list_with_function('report', 'extend_navigation_user', 'lib.php');
                    foreach ($reports as $reportfunction) {
                        $reportfunction($reporttab, $user, $usercourse);
                    }

                    $reporttab->trim_if_empty();
                }
            }

                        $pluginsfunction = get_plugins_with_function('extend_navigation_user', 'lib.php');
            foreach ($pluginsfunction as $plugintype => $plugins) {
                if ($plugintype != 'report') {
                    foreach ($plugins as $pluginfunction) {
                        $pluginfunction($usernode, $user, $usercontext, $course, $coursecontext);
                    }
                }
            }
        }
        return true;
    }

    
    public static function module_extends_navigation($modname) {
        global $CFG;
        static $extendingmodules = array();
        if (!array_key_exists($modname, $extendingmodules)) {
            $extendingmodules[$modname] = false;
            $file = $CFG->dirroot.'/mod/'.$modname.'/lib.php';
            if (file_exists($file)) {
                $function = $modname.'_extend_navigation';
                require_once($file);
                $extendingmodules[$modname] = (function_exists($function));
            }
        }
        return $extendingmodules[$modname];
    }
    
    public function extend_for_user($user) {
        $this->extendforuser[] = $user;
    }

    
    public function get_extending_users() {
        return $this->extendforuser;
    }
    
    public function add_course(stdClass $course, $forcegeneric = false, $coursetype = self::COURSE_OTHER) {
        global $CFG, $SITE;

                if (!$forcegeneric && array_key_exists($course->id, $this->addedcourses)) {
            return $this->addedcourses[$course->id];
        }

        $coursecontext = context_course::instance($course->id);

        if ($course->id != $SITE->id && !$course->visible) {
            if (is_role_switched($course->id)) {
                            } else if (!has_capability('moodle/course:viewhiddencourses', $coursecontext)) {
                return false;
            }
        }

        $issite = ($course->id == $SITE->id);
        $shortname = format_string($course->shortname, true, array('context' => $coursecontext));
        $fullname = format_string($course->fullname, true, array('context' => $coursecontext));
                $coursename = empty($CFG->navshowfullcoursenames) ? $shortname : $fullname;

                $canexpandcourse = true;
        if ($issite) {
            $parent = $this;
            $url = null;
            if (empty($CFG->usesitenameforsitepages)) {
                $coursename = get_string('sitepages');
            }
        } else if ($coursetype == self::COURSE_CURRENT) {
            $parent = $this->rootnodes['currentcourse'];
            $url = new moodle_url('/course/view.php', array('id'=>$course->id));
            $canexpandcourse = $this->can_expand_course($course);
        } else if ($coursetype == self::COURSE_MY && !$forcegeneric) {
            if (!empty($CFG->navshowmycoursecategories) && ($parent = $this->rootnodes['mycourses']->find($course->category, self::TYPE_MY_CATEGORY))) {
                            } else {
                $parent = $this->rootnodes['mycourses'];
            }
            $url = new moodle_url('/course/view.php', array('id'=>$course->id));
        } else {
            $parent = $this->rootnodes['courses'];
            $url = new moodle_url('/course/view.php', array('id'=>$course->id));
                        $canexpandcourse = $this->can_expand_course($course);
            if (!empty($course->category) && $this->show_categories($coursetype == self::COURSE_MY)) {
                if (!$this->is_category_fully_loaded($course->category)) {
                                        $this->load_all_categories($course->category, false);
                }
                if (array_key_exists($course->category, $this->addedcategories)) {
                    $parent = $this->addedcategories[$course->category];
                                        if (!$forcegeneric && array_key_exists($course->id, $this->addedcourses)) {
                        return $this->addedcourses[$course->id];
                    }
                }
            }
        }

        $coursenode = $parent->add($coursename, $url, self::TYPE_COURSE, $shortname, $course->id);
        $coursenode->hidden = (!$course->visible);
        $coursenode->title(format_string($course->fullname, true, array('context' => $coursecontext, 'escape' => false)));
        if ($canexpandcourse) {
                        $coursenode->nodetype = self::NODETYPE_BRANCH;
            $coursenode->isexpandable = true;
        } else {
            $coursenode->nodetype = self::NODETYPE_LEAF;
            $coursenode->isexpandable = false;
        }
        if (!$forcegeneric) {
            $this->addedcourses[$course->id] = $coursenode;
        }

        return $coursenode;
    }

    
    protected function get_expand_course_cache() {
        if ($this->cacheexpandcourse === null) {
            $this->cacheexpandcourse = cache::make('core', 'navigation_expandcourse');
        }
        return $this->cacheexpandcourse;
    }

    
    protected function can_expand_course($course) {
        $cache = $this->get_expand_course_cache();
        $canexpand = $cache->get($course->id);
        if ($canexpand === false) {
            $canexpand = isloggedin() && can_access_course($course, null, '', true);
            $canexpand = (int)$canexpand;
            $cache->set($course->id, $canexpand);
        }
        return ($canexpand === 1);
    }

    
    protected function is_category_fully_loaded($categoryid) {
        return (array_key_exists($categoryid, $this->addedcategories) && ($this->allcategoriesloaded || $this->addedcategories[$categoryid]->children->count() > 0));
    }

    
    public function add_course_essentials($coursenode, stdClass $course) {
        global $CFG, $SITE;

        if ($course->id == $SITE->id) {
            return $this->add_front_page_course_essentials($coursenode, $course);
        }

        if ($coursenode == false || !($coursenode instanceof navigation_node) || $coursenode->get('participants', navigation_node::TYPE_CONTAINER)) {
            return true;
        }

/*                if (has_capability('moodle/course:viewparticipants', $this->page->context)) {
            $participants = $coursenode->add(get_string('participants'), new moodle_url('/user/index.php?id='.$course->id), self::TYPE_CONTAINER, get_string('participants'), 'participants');
            if (!empty($CFG->enableblogs)) {
                if (($CFG->bloglevel == BLOG_GLOBAL_LEVEL or ($CFG->bloglevel == BLOG_SITE_LEVEL and (isloggedin() and !isguestuser())))
                   and has_capability('moodle/blog:view', context_system::instance())) {
                    $blogsurls = new moodle_url('/blog/index.php');
                    if ($currentgroup = groups_get_course_group($course, true)) {
                        $blogsurls->param('groupid', $currentgroup);
                    } else {
                        $blogsurls->param('courseid', $course->id);
                    }
                    $participants->add(get_string('blogscourse', 'blog'), $blogsurls->out(), self::TYPE_SETTING, null, 'courseblogs');
                }
            }
            if (!empty($CFG->enablenotes) && (has_capability('moodle/notes:manage', $this->page->context) || has_capability('moodle/notes:view', $this->page->context))) {
                $participants->add(get_string('notes', 'notes'), new moodle_url('/notes/index.php', array('filtertype' => 'course', 'filterselect' => $course->id)), self::TYPE_SETTING, null, 'currentcoursenotes');
            }
        } else if (count($this->extendforuser) > 0 || $this->page->course->id == $course->id) {
            $participants = $coursenode->add(get_string('participants'), null, self::TYPE_CONTAINER, get_string('participants'), 'participants');
        }
*/
                if (!empty($CFG->enablebadges) && !empty($CFG->badges_allowcoursebadges) &&
            has_capability('moodle/badges:viewbadges', $this->page->context)) {
            $url = new moodle_url('/badges/view.php', array('type' => 2, 'id' => $course->id));

            $coursenode->add(get_string('coursebadges', 'badges'), null,
                    navigation_node::TYPE_CONTAINER, null, 'coursebadges');
            $coursenode->get('coursebadges')->add(get_string('badgesview', 'badges'), $url,
                    navigation_node::TYPE_SETTING, null, 'badgesview',
                    new pix_icon('i/badge', get_string('badgesview', 'badges')));
        }

        return true;
    }
    
    public function add_front_page_course_essentials(navigation_node $coursenode, stdClass $course) {
        global $CFG;

        if ($coursenode == false || $coursenode->get('frontpageloaded', navigation_node::TYPE_CUSTOM)) {
            return true;
        }

        $sitecontext = context_system::instance();
        $isfrontpage = ($course->id == SITEID);

                        $coursenode->add('frontpageloaded', null, self::TYPE_CUSTOM, null, 'frontpageloaded')->display = false;

                                if (($isfrontpage && has_capability('moodle/site:viewparticipants', $sitecontext)) ||
                (!$isfrontpage && has_capability('moodle/course:viewparticipants', context_course::instance($course->id)))) {
            $coursenode->add(get_string('participants'), new moodle_url('/user/index.php?id='.$course->id), self::TYPE_CUSTOM, get_string('participants'), 'participants');
        }

                if (!empty($CFG->enableblogs)
          and ($CFG->bloglevel == BLOG_GLOBAL_LEVEL or ($CFG->bloglevel == BLOG_SITE_LEVEL and (isloggedin() and !isguestuser())))
          and has_capability('moodle/blog:view', $sitecontext)) {
            $blogsurls = new moodle_url('/blog/index.php');
            $coursenode->add(get_string('blogssite', 'blog'), $blogsurls->out(), self::TYPE_SYSTEM, null, 'siteblog');
        }

        $filterselect = 0;

                if (!empty($CFG->enablebadges) && has_capability('moodle/badges:viewbadges', $sitecontext)) {
            $url = new moodle_url($CFG->wwwroot . '/badges/view.php', array('type' => 1));
            $coursenode->add(get_string('sitebadges', 'badges'), $url, navigation_node::TYPE_CUSTOM);
        }

                if (!empty($CFG->enablenotes) && has_any_capability(array('moodle/notes:manage', 'moodle/notes:view'), $sitecontext)) {
            $coursenode->add(get_string('notes', 'notes'), new moodle_url('/notes/index.php',
                array('filtertype' => 'course', 'filterselect' => $filterselect)), self::TYPE_SETTING, null, 'notes');
        }

                if (!empty($CFG->usetags) && isloggedin()) {
            $node = $coursenode->add(get_string('tags', 'tag'), new moodle_url('/tag/search.php'),
                    self::TYPE_SETTING, null, 'tags');
        }

                if (!empty($CFG->enableglobalsearch) && has_capability('moodle/search:query', $sitecontext)) {
            $node = $coursenode->add(get_string('search', 'search'), new moodle_url('/search/index.php'),
                    self::TYPE_SETTING, null, 'search');
        }

        if (isloggedin()) {
                        $calendarurl = new moodle_url('/calendar/view.php', array('view' => 'month'));
            $coursenode->add(get_string('calendar', 'calendar'), $calendarurl, self::TYPE_CUSTOM, null, 'calendar');
        }

        return true;
    }

    
    public function clear_cache() {
        $this->cache->clear();
    }

    
    public function set_expansion_limit($type) {
        global $SITE;
        $nodes = $this->find_all_of_type($type);

                                $typestohide = array(
            self::TYPE_CATEGORY,
            self::TYPE_COURSE,
            self::TYPE_SECTION,
            self::TYPE_ACTIVITY
        );

        foreach ($nodes as $node) {
                        if ($type == self::TYPE_COURSE && $node->key == $SITE->id) {
                continue;
            }
            foreach ($node->children as $child) {
                $child->hide($typestohide);
            }
        }
        return true;
    }
    
    public function get($key, $type = null) {
        if (!$this->initialised) {
            $this->initialise();
        }
        return parent::get($key, $type);
    }

    
    public function find($key, $type) {
        if (!$this->initialised) {
            $this->initialise();
        }
        if ($type == self::TYPE_ROOTNODE && array_key_exists($key, $this->rootnodes)) {
            return $this->rootnodes[$key];
        }
        return parent::find($key, $type);
    }

    
    protected function load_courses_enrolled() {
        global $CFG, $DB;
        $sortorder = 'visible DESC';
                if (empty($CFG->navsortmycoursessort)) {
            $CFG->navsortmycoursessort = 'sortorder';
        }
                $sortorder = $sortorder . ',' . $CFG->navsortmycoursessort . ' ASC';
        $courses = enrol_get_my_courses(null, $sortorder);
        if (count($courses) && $this->show_my_categories()) {
                                                $categoryids = array();
            foreach ($courses as $course) {
                $categoryids[] = $course->category;
            }
            $categoryids = array_unique($categoryids);
            list($sql, $params) = $DB->get_in_or_equal($categoryids);
            $categories = $DB->get_recordset_select('course_categories', 'id '.$sql.' AND parent <> 0', $params, 'sortorder, id', 'id, path');
            foreach ($categories as $category) {
                $bits = explode('/', trim($category->path,'/'));
                $categoryids[] = array_shift($bits);
            }
            $categoryids = array_unique($categoryids);
            $categories->close();

                        list($sql, $params) = $DB->get_in_or_equal($categoryids);
            $categories = $DB->get_recordset_select('course_categories', 'id '.$sql.' AND parent = 0', $params, 'sortorder, id');
            foreach ($categories as $category) {
                $this->add_category($category, $this->rootnodes['mycourses'], self::TYPE_MY_CATEGORY);
            }
            $categories->close();
        } else {
            foreach ($courses as $course) {
                $this->add_course($course, false, self::COURSE_MY);
            }
        }
    }
}


class global_navigation_for_ajax extends global_navigation {

    
    protected $branchtype;

    
    protected $instanceid;

    
    protected $expandable = array();

    
    public function __construct($page, $branchtype, $id) {
        $this->page = $page;
        $this->cache = new navigation_cache(NAVIGATION_CACHE_NAME);
        $this->children = new navigation_node_collection();
        $this->branchtype = $branchtype;
        $this->instanceid = $id;
        $this->initialise();
    }
    
    public function initialise() {
        global $DB, $SITE;

        if ($this->initialised || during_initial_install()) {
            return $this->expandable;
        }
        $this->initialised = true;

        $this->rootnodes = array();
        $this->rootnodes['site']    = $this->add_course($SITE);
        $this->rootnodes['mycourses'] = $this->add(get_string('mycourses'), new moodle_url('/my'), self::TYPE_ROOTNODE, null, 'mycourses');
        $this->rootnodes['courses'] = $this->add(get_string('courses'), null, self::TYPE_ROOTNODE, null, 'courses');
                        $this->rootnodes['courses']->isexpandable = true;

                switch ($this->branchtype) {
            case 0:
                if ($this->instanceid === 'mycourses') {
                    $this->load_courses_enrolled();
                } else if ($this->instanceid === 'courses') {
                    $this->load_courses_other();
                }
                break;
            case self::TYPE_CATEGORY :
                $this->load_category($this->instanceid);
                break;
            case self::TYPE_MY_CATEGORY :
                $this->load_category($this->instanceid, self::TYPE_MY_CATEGORY);
                break;
            case self::TYPE_COURSE :
                $course = $DB->get_record('course', array('id' => $this->instanceid), '*', MUST_EXIST);
                if (!can_access_course($course, null, '', true)) {
                                                            $this->add_course($course);
                    break;
                }
                require_course_login($course, true, null, false, true);
                $this->page->set_context(context_course::instance($course->id));
                $coursenode = $this->add_course($course);
                $this->add_course_essentials($coursenode, $course);
                $this->load_course_sections($course, $coursenode);
                break;
            case self::TYPE_SECTION :
                $sql = 'SELECT c.*, cs.section AS sectionnumber
                        FROM {course} c
                        LEFT JOIN {course_sections} cs ON cs.course = c.id
                        WHERE cs.id = ?';
                $course = $DB->get_record_sql($sql, array($this->instanceid), MUST_EXIST);
                require_course_login($course, true, null, false, true);
                $this->page->set_context(context_course::instance($course->id));
                $coursenode = $this->add_course($course);
                $this->add_course_essentials($coursenode, $course);
                $this->load_course_sections($course, $coursenode, $course->sectionnumber);
                break;
            case self::TYPE_ACTIVITY :
                $sql = "SELECT c.*
                          FROM {course} c
                          JOIN {course_modules} cm ON cm.course = c.id
                         WHERE cm.id = :cmid";
                $params = array('cmid' => $this->instanceid);
                $course = $DB->get_record_sql($sql, $params, MUST_EXIST);
                $modinfo = get_fast_modinfo($course);
                $cm = $modinfo->get_cm($this->instanceid);
                require_course_login($course, true, $cm, false, true);
                $this->page->set_context(context_module::instance($cm->id));
                $coursenode = $this->load_course($course);
                $this->load_course_sections($course, $coursenode, null, $cm);
                $activitynode = $coursenode->find($cm->id, self::TYPE_ACTIVITY);
                if ($activitynode) {
                    $modulenode = $this->load_activity($cm, $course, $activitynode);
                }
                break;
            default:
                throw new Exception('Unknown type');
                return $this->expandable;
        }

        if ($this->page->context->contextlevel == CONTEXT_COURSE && $this->page->context->instanceid != $SITE->id) {
            $this->load_for_user(null, true);
        }

        $this->find_expandable($this->expandable);
        return $this->expandable;
    }

    
    protected function load_courses_other() {
        $this->load_all_courses();
    }

    
    protected function load_category($categoryid, $nodetype = self::TYPE_CATEGORY) {
        global $CFG, $DB;

        $limit = 20;
        if (!empty($CFG->navcourselimit)) {
            $limit = (int)$CFG->navcourselimit;
        }

        $catcontextsql = context_helper::get_preload_record_columns_sql('ctx');
        $sql = "SELECT cc.*, $catcontextsql
                  FROM {course_categories} cc
                  JOIN {context} ctx ON cc.id = ctx.instanceid
                 WHERE ctx.contextlevel = ".CONTEXT_COURSECAT." AND
                       (cc.id = :categoryid1 OR cc.parent = :categoryid2)
              ORDER BY cc.depth ASC, cc.sortorder ASC, cc.id ASC";
        $params = array('categoryid1' => $categoryid, 'categoryid2' => $categoryid);
        $categories = $DB->get_recordset_sql($sql, $params, 0, $limit);
        $categorylist = array();
        $subcategories = array();
        $basecategory = null;
        foreach ($categories as $category) {
            $categorylist[] = $category->id;
            context_helper::preload_from_record($category);
            if ($category->id == $categoryid) {
                $this->add_category($category, $this, $nodetype);
                $basecategory = $this->addedcategories[$category->id];
            } else {
                $subcategories[$category->id] = $category;
            }
        }
        $categories->close();


                        if ($nodetype === self::TYPE_MY_CATEGORY) {
            $courses = enrol_get_my_courses();
            $categoryids = array();

                        if (!is_null($basecategory)) {
                                foreach ($courses as $course) {
                    $categoryids[] = $course->category;
                }

                                                $coursesubcategories = array();
                $addedsubcategories = array();

                list($sql, $params) = $DB->get_in_or_equal($categoryids);
                $categories = $DB->get_recordset_select('course_categories', 'id '.$sql, $params, 'sortorder, id', 'id, path');

                foreach ($categories as $category){
                    $coursesubcategories = array_merge($coursesubcategories, explode('/', trim($category->path, "/")));
                }
                $coursesubcategories = array_unique($coursesubcategories);

                                                foreach ($subcategories as $subid => $subcategory) {
                    if (in_array($subid, $coursesubcategories) &&
                            !in_array($subid, $addedsubcategories)) {
                            $this->add_category($subcategory, $basecategory, $nodetype);
                            $addedsubcategories[] = $subid;
                    }
                }
            }

            foreach ($courses as $course) {
                                if (in_array($course->category, $categorylist)) {
                    $this->add_course($course, true, self::COURSE_MY);
                }
            }
        } else {
            if (!is_null($basecategory)) {
                foreach ($subcategories as $key=>$category) {
                    $this->add_category($category, $basecategory, $nodetype);
                }
            }
            $courses = $DB->get_recordset('course', array('category' => $categoryid), 'sortorder', '*' , 0, $limit);
            foreach ($courses as $course) {
                $this->add_course($course);
            }
            $courses->close();
        }
    }

    
    public function get_expandable() {
        return $this->expandable;
    }
}


class navbar extends navigation_node {
    
    protected $initialised = false;
    
    protected $keys = array();
    
    protected $content = null;
    
    protected $page;
    
    protected $ignoreactive = false;
    
    protected $duringinstall = false;
    
    protected $hasitems = false;
    
    protected $items;
    
    public $children = array();
    
    public $includesettingsbase = false;
    
    protected $prependchildren = array();
    


    public function __construct(moodle_page $page) {
        global $CFG;
        if (during_initial_install()) {
            $this->duringinstall = true;
            return false;
        }
        $this->page = $page;
        $this->text = get_string('home');
        $this->shorttext = get_string('home');
        $this->action = new moodle_url($CFG->wwwroot);
        $this->nodetype = self::NODETYPE_BRANCH;
        $this->type = self::TYPE_SYSTEM;
    }

    
    public function has_items() {
        if ($this->duringinstall) {
            return false;
        } else if ($this->hasitems !== false) {
            return true;
        }
        if (count($this->children) > 0 || count($this->prependchildren) > 0) {
                        $outcome = true;
        } else if (!$this->ignoreactive) {
                        $this->page->navigation->initialise($this->page);
            $outcome = ($this->page->navigation->contains_active_node() || $this->page->settingsnav->contains_active_node());
        }
        $this->hasitems = $outcome;
        return $outcome;
    }

    
    public function ignore_active($setting=true) {
        $this->ignoreactive = ($setting);
    }

    
    public function get($key, $type = null) {
        foreach ($this->children as &$child) {
            if ($child->key === $key && ($type == null || $type == $child->type)) {
                return $child;
            }
        }
        foreach ($this->prependchildren as &$child) {
            if ($child->key === $key && ($type == null || $type == $child->type)) {
                return $child;
            }
        }
        return false;
    }
    
    public function get_items() {
        global $CFG;
        $items = array();
                if (!$this->has_items()) {
            return $items;
        }
        if ($this->items !== null) {
            return $this->items;
        }

        if (count($this->children) > 0) {
                        $items = array_reverse($this->children);
        }

                if (!$this->ignoreactive) {
                        $this->page->navigation->initialise($this->page);
                        $navigationactivenode = $this->page->navigation->find_active_node();
            $settingsactivenode = $this->page->settingsnav->find_active_node();

            if ($navigationactivenode && $settingsactivenode) {
                                while ($settingsactivenode && $settingsactivenode->parent !== null) {
                    if (!$settingsactivenode->mainnavonly) {
                        $items[] = new breadcrumb_navigation_node($settingsactivenode);
                    }
                    $settingsactivenode = $settingsactivenode->parent;
                }
                if (!$this->includesettingsbase) {
                                        array_pop($items);
                }
                while ($navigationactivenode && $navigationactivenode->parent !== null) {
                    if (!$navigationactivenode->mainnavonly) {
                        $items[] = new breadcrumb_navigation_node($navigationactivenode);
                    }
                    if (!empty($CFG->navshowcategories) &&
                            $navigationactivenode->type === self::TYPE_COURSE &&
                            $navigationactivenode->parent->key === 'currentcourse') {
                        foreach ($this->get_course_categories() as $item) {
                            $items[] = new breadcrumb_navigation_node($item);
                        }
                    }
                    $navigationactivenode = $navigationactivenode->parent;
                }
            } else if ($navigationactivenode) {
                                while ($navigationactivenode && $navigationactivenode->parent !== null) {
                    if (!$navigationactivenode->mainnavonly) {
                        $items[] = new breadcrumb_navigation_node($navigationactivenode);
                    }
                    if (!empty($CFG->navshowcategories) &&
                            $navigationactivenode->type === self::TYPE_COURSE &&
                            $navigationactivenode->parent->key === 'currentcourse') {
                        foreach ($this->get_course_categories() as $item) {
                            $items[] = new breadcrumb_navigation_node($item);
                        }
                    }
                    $navigationactivenode = $navigationactivenode->parent;
                }
            } else if ($settingsactivenode) {
                                while ($settingsactivenode && $settingsactivenode->parent !== null) {
                    if (!$settingsactivenode->mainnavonly) {
                        $items[] = new breadcrumb_navigation_node($settingsactivenode);
                    }
                    $settingsactivenode = $settingsactivenode->parent;
                }
            }
        }

        $items[] = new breadcrumb_navigation_node(array(
            'text' => $this->page->navigation->text,
            'shorttext' => $this->page->navigation->shorttext,
            'key' => $this->page->navigation->key,
            'action' => $this->page->navigation->action
        ));

        if (count($this->prependchildren) > 0) {
                        $items = array_merge($items, array_reverse($this->prependchildren));
        }

        $this->items = array_reverse($items);
        return $this->items;
    }

    
    private function get_course_categories() {
        global $CFG;
        require_once($CFG->dirroot.'/course/lib.php');
        require_once($CFG->libdir.'/coursecatlib.php');

        $categories = array();
        $cap = 'moodle/category:viewhiddencategories';
        $showcategories = coursecat::count_all() > 1;

        if ($showcategories) {
            foreach ($this->page->categories as $category) {
                if (!$category->visible && !has_capability($cap, get_category_or_system_context($category->parent))) {
                    continue;
                }
                $url = new moodle_url('/course/index.php', array('categoryid' => $category->id));
                $name = format_string($category->name, true, array('context' => context_coursecat::instance($category->id)));
                $categorynode = breadcrumb_navigation_node::create($name, $url, self::TYPE_CATEGORY, null, $category->id);
                if (!$category->visible) {
                    $categorynode->hidden = true;
                }
                $categories[] = $categorynode;
            }
        }

                if (!is_enrolled(context_course::instance($this->page->course->id, null, '', true))) {
            $courses = $this->page->navigation->get('courses');
            if (!$courses) {
                                $courses = breadcrumb_navigation_node::create(
                    get_string('courses'),
                    new moodle_url('/course/index.php'),
                    self::TYPE_CONTAINER
                );
            }
            $categories[] = $courses;
        }

        return $categories;
    }

    
    public function add($text, $action=null, $type=self::TYPE_CUSTOM, $shorttext=null, $key=null, pix_icon $icon=null) {
        if ($this->content !== null) {
            debugging('Nav bar items must be printed before $OUTPUT->header() has been called', DEBUG_DEVELOPER);
        }

                $itemarray = array(
            'text' => $text,
            'type' => $type
        );
                if ($action!==null) {
            $itemarray['action'] = $action;
        }
                if ($shorttext!==null) {
            $itemarray['shorttext'] = $shorttext;
        }
                if ($icon!==null) {
            $itemarray['icon'] = $icon;
        }
                if ($key === null) {
            $key = count($this->children);
        }
                $itemarray['key'] = $key;
                $itemarray['parent'] = $this;
                $this->children[] = new breadcrumb_navigation_node($itemarray);
        return $this;
    }

    
    public function prepend($text, $action=null, $type=self::TYPE_CUSTOM, $shorttext=null, $key=null, pix_icon $icon=null) {
        if ($this->content !== null) {
            debugging('Nav bar items must be printed before $OUTPUT->header() has been called', DEBUG_DEVELOPER);
        }
                $itemarray = array(
            'text' => $text,
            'type' => $type
        );
                if ($action!==null) {
            $itemarray['action'] = $action;
        }
                if ($shorttext!==null) {
            $itemarray['shorttext'] = $shorttext;
        }
                if ($icon!==null) {
            $itemarray['icon'] = $icon;
        }
                if ($key === null) {
            $key = count($this->children);
        }
                $itemarray['key'] = $key;
                $itemarray['parent'] = $this;
                $this->prependchildren[] = new breadcrumb_navigation_node($itemarray);
        return $this;
    }
}


class breadcrumb_navigation_node extends navigation_node {

    
    public function __construct($navnode) {
        if (is_array($navnode)) {
            parent::__construct($navnode);
        } else if ($navnode instanceof navigation_node) {

                        $objvalues = get_object_vars($navnode);
            foreach ($objvalues as $key => $value) {
                 $this->$key = $value;
            }
        } else {
            throw coding_exception('Not a valid breadcrumb_navigation_node');
        }
    }

}


class settings_navigation extends navigation_node {
    
    protected $context;
    
    protected $page;
    
    protected $adminsection;
    
    protected $initialised = false;
    
    protected $userstoextendfor = array();
    
    protected $cache;

    
    public function __construct(moodle_page &$page) {
        if (during_initial_install()) {
            return false;
        }
        $this->page = $page;
                        $this->page->navigation->initialise();
                $this->cache = new navigation_cache(NAVIGATION_CACHE_NAME);
        $this->children = new navigation_node_collection();
    }
    
    public function initialise() {
        global $DB, $SESSION, $SITE;

        if (during_initial_install()) {
            return false;
        } else if ($this->initialised) {
            return true;
        }
        $this->id = 'settingsnav';
        $this->context = $this->page->context;

        $context = $this->context;
        if ($context->contextlevel == CONTEXT_BLOCK) {
            $this->load_block_settings();
            $context = $context->get_parent_context();
        }
        switch ($context->contextlevel) {
            case CONTEXT_SYSTEM:
                if ($this->page->url->compare(new moodle_url('/admin/settings.php', array('section'=>'frontpagesettings')))) {
                    $this->load_front_page_settings(($context->id == $this->context->id));
                }
                break;
            case CONTEXT_COURSECAT:
                $this->load_category_settings();
                break;
            case CONTEXT_COURSE:
                if ($this->page->course->id != $SITE->id) {
                    $this->load_course_settings(($context->id == $this->context->id));
                } else {
                    $this->load_front_page_settings(($context->id == $this->context->id));
                }
                break;
            case CONTEXT_MODULE:
                $this->load_module_settings();
                $this->load_course_settings();
                break;
            case CONTEXT_USER:
                if ($this->page->course->id != $SITE->id) {
                    $this->load_course_settings();
                }
                break;
        }

        $usersettings = $this->load_user_settings($this->page->course->id);

        $adminsettings = false;
        if (isloggedin() && !isguestuser() && (!isset($SESSION->load_navigation_admin) || $SESSION->load_navigation_admin)) {
            $isadminpage = $this->is_admin_tree_needed();

            if (has_capability('moodle/site:config', context_system::instance())) {
                                                $SESSION->load_navigation_admin = 1;
                if ($isadminpage) {
                    $adminsettings = $this->load_administration_settings();
                }

            } else if (!isset($SESSION->load_navigation_admin)) {
                $adminsettings = $this->load_administration_settings();
                $SESSION->load_navigation_admin = (int)($adminsettings->children->count() > 0);

            } else if ($SESSION->load_navigation_admin) {
                if ($isadminpage) {
                    $adminsettings = $this->load_administration_settings();
                }
            }

                        if ($SESSION->load_navigation_admin && !$isadminpage) {
                if ($adminsettings) {
                                        $adminsettings->remove();
                    $adminsettings = false;
                }
                $siteadminnode = $this->add(get_string('administrationsite'), new moodle_url('/admin'), self::TYPE_SITE_ADMIN, null, 'siteadministration');
                $siteadminnode->id = 'expandable_branch_'.$siteadminnode->type.'_'.clean_param($siteadminnode->key, PARAM_ALPHANUMEXT);
                $siteadminnode->requiresajaxloading = 'true';
            }
        }

        if ($context->contextlevel == CONTEXT_SYSTEM && $adminsettings) {
            $adminsettings->force_open();
        } else if ($context->contextlevel == CONTEXT_USER && $usersettings) {
            $usersettings->force_open();
        }

                $this->load_local_plugin_settings();

        foreach ($this->children as $key=>$node) {
            if ($node->nodetype == self::NODETYPE_BRANCH && $node->children->count() == 0) {
                                if (!empty($SESSION->load_navigation_admin) && ($node->type === self::TYPE_SITE_ADMIN)) {
                    continue;
                }
                $node->remove();
            }
        }
        $this->initialised = true;
    }
    
    public function add($text, $url=null, $type=null, $shorttext=null, $key=null, pix_icon $icon=null) {
        $node = parent::add($text, $url, $type, $shorttext, $key, $icon);
        $node->add_class('root_node');
        return $node;
    }

    
    public function prepend($text, $url=null, $type=null, $shorttext=null, $key=null, pix_icon $icon=null) {
        $children = $this->children;
        $childrenclass = get_class($children);
        $this->children = new $childrenclass;
        $node = $this->add($text, $url, $type, $shorttext, $key, $icon);
        foreach ($children as $child) {
            $this->children->add($child);
        }
        return $node;
    }

    
    protected function is_admin_tree_needed() {
        if (self::$loadadmintree) {
                        return true;
        }

        if ($this->page->pagelayout === 'admin' or strpos($this->page->pagetype, 'admin-') === 0) {
                        if ($this->page->context->contextlevel != CONTEXT_SYSTEM) {
                return false;
            }
            return true;
        }

        return false;
    }

    
    protected function load_administration_settings(navigation_node $referencebranch=null, part_of_admin_tree $adminbranch=null) {
        global $CFG;

                if ($referencebranch === null) {

                        if (!function_exists('admin_get_root')) {
                require_once($CFG->dirroot.'/lib/adminlib.php');
            }
            $adminroot = admin_get_root(false, false);
                        $this->adminsection = $this->page->url->param('section');

                        navigation_node::$autofindactive = false;
            $referencebranch = $this->add(get_string('administrationsite'), null, self::TYPE_SITE_ADMIN, null, 'root');
            foreach ($adminroot->children as $adminbranch) {
                $this->load_administration_settings($referencebranch, $adminbranch);
            }
            navigation_node::$autofindactive = true;

                        if (!$this->contains_active_node() && $current = $adminroot->locate($this->adminsection, true)) {
                $currentnode = $this;
                while (($pathkey = array_pop($current->path))!==null && $currentnode) {
                    $currentnode = $currentnode->get($pathkey);
                }
                if ($currentnode) {
                    $currentnode->make_active();
                }
            } else {
                $this->scan_for_active_node($referencebranch);
            }
            return $referencebranch;
        } else if ($adminbranch->check_access()) {
                                    $url = null;
            $icon = null;
            if ($adminbranch instanceof admin_settingpage) {
                $url = new moodle_url('/'.$CFG->admin.'/settings.php', array('section'=>$adminbranch->name));
            } else if ($adminbranch instanceof admin_externalpage) {
                $url = $adminbranch->url;
            } else if (!empty($CFG->linkadmincategories) && $adminbranch instanceof admin_category) {
                $url = new moodle_url('/'.$CFG->admin.'/category.php', array('category' => $adminbranch->name));
            }

                        $reference = $referencebranch->add($adminbranch->visiblename, $url, self::TYPE_SETTING, null, $adminbranch->name, $icon);

            if ($adminbranch->is_hidden()) {
                if (($adminbranch instanceof admin_externalpage || $adminbranch instanceof admin_settingpage) && $adminbranch->name == $this->adminsection) {
                    $reference->add_class('hidden');
                } else {
                    $reference->display = false;
                }
            }

                        if ($adminbranch->name === 'adminnotifications' && admin_critical_warnings_present()) {
                $reference->add_class('criticalnotification');
            }
                        if ($reference && isset($adminbranch->children) && is_array($adminbranch->children) && count($adminbranch->children)>0) {
                foreach ($adminbranch->children as $branch) {
                                        $this->load_administration_settings($reference, $branch);
                }
            } else {
                $reference->icon = new pix_icon('i/settings', '');
            }
        }
    }

    
    protected function scan_for_active_node(navigation_node $node) {
        if (!$node->check_if_active() && $node->children->count()>0) {
            foreach ($node->children as &$child) {
                $this->scan_for_active_node($child);
            }
        }
    }

    
    protected function get_by_path(array $path) {
        $node = $this->get(array_shift($path));
        foreach ($path as $key) {
            $node->get($key);
        }
        return $node;
    }

    
    protected function load_course_settings($forceopen = false) {
        global $CFG, $USER;

        $course = $this->page->course;
        $coursecontext = context_course::instance($course->id);

        
        $coursenode = $this->add(get_string('courseadministration'), null, self::TYPE_COURSE, null, 'courseadmin');
        if ($forceopen) {
            $coursenode->force_open();
        }

        if ($this->page->user_allowed_editing()) {
            
            if ($this->page->url->compare(new moodle_url('/course/view.php'), URL_MATCH_BASE)) {
                                $baseurl = clone($this->page->url);
                $baseurl->param('sesskey', sesskey());
            } else {
                                $baseurl = new moodle_url('/course/view.php', array('id'=>$course->id, 'return'=>$this->page->url->out_as_local_url(false), 'sesskey'=>sesskey()));
            }

            $editurl = clone($baseurl);
            if ($this->page->user_is_editing()) {
                $editurl->param('edit', 'off');
                $editstring = get_string('turneditingoff');
            } else {
                $editurl->param('edit', 'on');
                $editstring = get_string('turneditingon');
            }
            $coursenode->add($editstring, $editurl, self::TYPE_SETTING, null, 'turneditingonoff', new pix_icon('i/edit', ''));
        }

        if (has_capability('moodle/course:update', $coursecontext) && is_siteadmin($USER)) {
                        $url = new moodle_url('/course/edit.php', array('id'=>$course->id));
            $coursenode->add(get_string('editsettings'), $url, self::TYPE_SETTING, null, 'editsettings', new pix_icon('i/settings', ''));

                        if ($CFG->enablecompletion && $course->enablecompletion) {
                $url = new moodle_url('/course/completion.php', array('id'=>$course->id));
                $coursenode->add(get_string('coursecompletion', 'completion'), $url, self::TYPE_SETTING, null, null, new pix_icon('i/settings', ''));
            }
        } else if (has_capability('moodle/course:tag', $coursecontext)) {
            $url = new moodle_url('/course/tags.php', array('id' => $course->id));
            $coursenode->add(get_string('coursetags', 'tag'), $url, self::TYPE_SETTING, null, 'coursetags', new pix_icon('i/settings', ''));
        }

                enrol_add_course_navigation($coursenode, $course);

                if (has_capability('moodle/filter:manage', $coursecontext) && count(filter_get_available_in_context($coursecontext))>0) {
            $url = new moodle_url('/filter/manage.php', array('contextid'=>$coursecontext->id));
            $coursenode->add(get_string('filters', 'admin'), $url, self::TYPE_SETTING, null, null, new pix_icon('i/filter', ''));
        }

                if (has_capability('moodle/site:viewreports', $coursecontext)) {             $reportnav = $coursenode->add(get_string('reports'), null, self::TYPE_CONTAINER, null, 'coursereports',
                    new pix_icon('i/stats', ''));
            $coursereports = core_component::get_plugin_list('coursereport');
            foreach ($coursereports as $report => $dir) {
                $libfile = $CFG->dirroot.'/course/report/'.$report.'/lib.php';
                if (file_exists($libfile)) {
                    require_once($libfile);
                    $reportfunction = $report.'_report_extend_navigation';
                    if (function_exists($report.'_report_extend_navigation')) {
                        $reportfunction($reportnav, $course, $coursecontext);
                    }
                }
            }

            $reports = get_plugin_list_with_function('report', 'extend_navigation_course', 'lib.php');
            foreach ($reports as $reportfunction) {
                $reportfunction($reportnav, $course, $coursecontext);
            }
        }

                $reportavailable = false;
        if (has_capability('moodle/grade:viewall', $coursecontext)) {
            $reportavailable = true;
        } else if (!empty($course->showgrades)) {
            $reports = core_component::get_plugin_list('gradereport');
            if (is_array($reports) && count($reports)>0) {                     arsort($reports);                 foreach ($reports as $plugin => $plugindir) {
                    if (has_capability('gradereport/'.$plugin.':view', $coursecontext)) {
                                                $reportavailable = true;
                        break;
                    }
                }
            }
        }
        if ($reportavailable) {
            $url = new moodle_url('/grade/report/index.php', array('id'=>$course->id));
            $gradenode = $coursenode->add(get_string('grades'), $url, self::TYPE_SETTING, null, 'grades', new pix_icon('i/grades', ''));
        }

                if (has_capability('moodle/grade:manage', $coursecontext)) {
            $url = new moodle_url('/grade/edit/tree/index.php', array('id' => $course->id));
            $coursenode->add(get_string('gradebooksetup', 'grades'), $url, self::TYPE_SETTING,
                null, 'gradebooksetup', new pix_icon('i/settings', ''));
        }

                if (!empty($CFG->enableoutcomes) && has_capability('moodle/course:update', $coursecontext)) {
            $url = new moodle_url('/grade/edit/outcome/course.php', array('id'=>$course->id));
            $coursenode->add(get_string('outcomes', 'grades'), $url, self::TYPE_SETTING, null, 'outcomes', new pix_icon('i/outcomes', ''));
        }

                if (!empty($CFG->enablebadges)) {
            require_once($CFG->libdir .'/badgeslib.php');
            badges_add_course_navigation($coursenode, $course);
        }

                if (has_capability('moodle/backup:backupcourse', $coursecontext)) {
            $url = new moodle_url('/backup/backup.php', array('id'=>$course->id));
            $coursenode->add(get_string('backup'), $url, self::TYPE_SETTING, null, 'backup', new pix_icon('i/backup', ''));
        }

                if (has_capability('moodle/restore:restorecourse', $coursecontext)) {
            $url = new moodle_url('/backup/restorefile.php', array('contextid'=>$coursecontext->id));
            $coursenode->add(get_string('restore'), $url, self::TYPE_SETTING, null, 'restore', new pix_icon('i/restore', ''));
        }

                if (has_capability('moodle/restore:restoretargetimport', $coursecontext)) {
            $url = new moodle_url('/backup/import.php', array('id'=>$course->id));
            $coursenode->add(get_string('import'), $url, self::TYPE_SETTING, null, 'import', new pix_icon('i/import', ''));
        }

                if (has_capability('moodle/course:publish', $coursecontext)) {
            $url = new moodle_url('/course/publish/index.php', array('id'=>$course->id));
            $coursenode->add(get_string('publish'), $url, self::TYPE_SETTING, null, 'publish', new pix_icon('i/publish', ''));
        }

                if (has_capability('moodle/course:reset', $coursecontext)) {
            $url = new moodle_url('/course/reset.php', array('id'=>$course->id));
            $coursenode->add(get_string('reset'), $url, self::TYPE_SETTING, null, 'reset', new pix_icon('i/return', ''));
        }

                require_once($CFG->libdir . '/questionlib.php');
        question_extend_settings_navigation($coursenode, $coursecontext)->trim_if_empty();

        if (has_capability('moodle/course:update', $coursecontext)) {
                        if (!$this->cache->cached('contexthasrepos'.$coursecontext->id)) {
                require_once($CFG->dirroot . '/repository/lib.php');
                $editabletypes = repository::get_editable_types($coursecontext);
                $haseditabletypes = !empty($editabletypes);
                unset($editabletypes);
                $this->cache->set('contexthasrepos'.$coursecontext->id, $haseditabletypes);
            } else {
                $haseditabletypes = $this->cache->{'contexthasrepos'.$coursecontext->id};
            }
            if ($haseditabletypes) {
                $url = new moodle_url('/repository/manage_instances.php', array('contextid' => $coursecontext->id));
                $coursenode->add(get_string('repositories'), $url, self::TYPE_SETTING, null, null, new pix_icon('i/repository', ''));
            }
        }

                if ($course->legacyfiles == 2 and has_capability('moodle/course:managefiles', $coursecontext)) {
                        $url = new moodle_url('/files/index.php', array('contextid'=>$coursecontext->id));
            $coursenode->add(get_string('courselegacyfiles'), $url, self::TYPE_SETTING, null, 'coursefiles', new pix_icon('i/folder', ''));

        }

                $roles = array();
        $assumedrole = $this->in_alternative_role();
        if ($assumedrole !== false) {
            $roles[0] = get_string('switchrolereturn');
        }
        if (has_capability('moodle/role:switchroles', $coursecontext)) {
            $availableroles = get_switchable_roles($coursecontext);
            if (is_array($availableroles)) {
                foreach ($availableroles as $key=>$role) {
                    if ($assumedrole == (int)$key) {
                        continue;
                    }
                    $roles[$key] = $role;
                }
            }
        }
        if (is_array($roles) && count($roles)>0) {
            $switchroles = $this->add(get_string('switchroleto'), null, self::TYPE_CONTAINER, null, 'switchroleto');
            if ((count($roles)==1 && array_key_exists(0, $roles))|| $assumedrole!==false) {
                $switchroles->force_open();
            }
            foreach ($roles as $key => $name) {
                $url = new moodle_url('/course/switchrole.php', array('id'=>$course->id, 'sesskey'=>sesskey(), 'switchrole'=>$key, 'returnurl'=>$this->page->url->out_as_local_url(false)));
                $switchroles->add($name, $url, self::TYPE_SETTING, null, $key, new pix_icon('i/switchrole', ''));
            }
        }

                $pluginsfunction = get_plugins_with_function('extend_navigation_course', 'lib.php');
        foreach ($pluginsfunction as $plugintype => $plugins) {
                        if ($plugintype == 'report') {
                continue;
            }
            foreach ($plugins as $pluginfunction) {
                $pluginfunction($coursenode, $course, $coursecontext);
            }
        }

                return $coursenode;
    }

    
    protected function load_module_settings() {
        global $CFG;

        if (!$this->page->cm && $this->context->contextlevel == CONTEXT_MODULE && $this->context->instanceid) {
            $cm = get_coursemodule_from_id(false, $this->context->instanceid, 0, false, MUST_EXIST);
            $this->page->set_cm($cm, $this->page->course);
        }

        $file = $CFG->dirroot.'/mod/'.$this->page->activityname.'/lib.php';
        if (file_exists($file)) {
            require_once($file);
        }

        $modulenode = $this->add(get_string('pluginadministration', $this->page->activityname), null, self::TYPE_SETTING, null, 'modulesettings');
        $modulenode->nodetype = navigation_node::NODETYPE_BRANCH;
        $modulenode->force_open();

                if (has_capability('moodle/course:manageactivities', $this->page->cm->context)) {
            $url = new moodle_url('/course/modedit.php', array('update' => $this->page->cm->id, 'return' => 1));
            $modulenode->add(get_string('editsettings'), $url, navigation_node::TYPE_SETTING, null, 'modedit');
        }
                if (count(get_assignable_roles($this->page->cm->context))>0) {
            $url = new moodle_url('/'.$CFG->admin.'/roles/assign.php', array('contextid'=>$this->page->cm->context->id));
            $modulenode->add(get_string('localroles', 'role'), $url, self::TYPE_SETTING, null, 'roleassign');
        }
                if (has_capability('moodle/role:review', $this->page->cm->context) or count(get_overridable_roles($this->page->cm->context))>0) {
            $url = new moodle_url('/'.$CFG->admin.'/roles/permissions.php', array('contextid'=>$this->page->cm->context->id));
            $modulenode->add(get_string('permissions', 'role'), $url, self::TYPE_SETTING, null, 'roleoverride');
        }
                if (has_any_capability(array('moodle/role:assign', 'moodle/role:safeoverride','moodle/role:override', 'moodle/role:assign'), $this->page->cm->context)) {
            $url = new moodle_url('/'.$CFG->admin.'/roles/check.php', array('contextid'=>$this->page->cm->context->id));
            $modulenode->add(get_string('checkpermissions', 'role'), $url, self::TYPE_SETTING, null, 'rolecheck');
        }
                if (has_capability('moodle/filter:manage', $this->page->cm->context) && count(filter_get_available_in_context($this->page->cm->context))>0) {
            $url = new moodle_url('/filter/manage.php', array('contextid'=>$this->page->cm->context->id));
            $modulenode->add(get_string('filters', 'admin'), $url, self::TYPE_SETTING, null, 'filtermanage');
        }
                $reports = get_plugin_list_with_function('report', 'extend_navigation_module', 'lib.php');
        foreach ($reports as $reportfunction) {
            $reportfunction($modulenode, $this->page->cm);
        }
                $featuresfunc = $this->page->activityname.'_supports';
        if (function_exists($featuresfunc) && $featuresfunc(FEATURE_BACKUP_MOODLE2) && has_capability('moodle/backup:backupactivity', $this->page->cm->context)) {
            $url = new moodle_url('/backup/backup.php', array('id'=>$this->page->cm->course, 'cm'=>$this->page->cm->id));
            $modulenode->add(get_string('backup'), $url, self::TYPE_SETTING, null, 'backup');
        }

                $featuresfunc = $this->page->activityname.'_supports';
        if (function_exists($featuresfunc) && $featuresfunc(FEATURE_BACKUP_MOODLE2) && has_capability('moodle/restore:restoreactivity', $this->page->cm->context)) {
            $url = new moodle_url('/backup/restorefile.php', array('contextid'=>$this->page->cm->context->id));
            $modulenode->add(get_string('restore'), $url, self::TYPE_SETTING, null, 'restore');
        }

                $featuresfunc = $this->page->activityname.'_supports';
        if (function_exists($featuresfunc) && $featuresfunc(FEATURE_ADVANCED_GRADING) && has_capability('moodle/grade:managegradingforms', $this->page->cm->context)) {
            require_once($CFG->dirroot.'/grade/grading/lib.php');
            $gradingman = get_grading_manager($this->page->cm->context, 'mod_'.$this->page->activityname);
            $gradingman->extend_settings_navigation($this, $modulenode);
        }

        $function = $this->page->activityname.'_extend_settings_navigation';
        if (function_exists($function)) {
            $function($this, $modulenode);
        }

                if ($modulenode->children->count() <= 0) {
            $modulenode->remove();
        }

        return $modulenode;
    }

    
    protected function load_user_settings($courseid = SITEID) {
        global $USER, $CFG;

        if (isguestuser() || !isloggedin()) {
            return false;
        }

        $navusers = $this->page->navigation->get_extending_users();

        if (count($this->userstoextendfor) > 0 || count($navusers) > 0) {
            $usernode = null;
            foreach ($this->userstoextendfor as $userid) {
                if ($userid == $USER->id) {
                    continue;
                }
                $node = $this->generate_user_settings($courseid, $userid, 'userviewingsettings');
                if (is_null($usernode)) {
                    $usernode = $node;
                }
            }
            foreach ($navusers as $user) {
                if ($user->id == $USER->id) {
                    continue;
                }
                $node = $this->generate_user_settings($courseid, $user->id, 'userviewingsettings');
                if (is_null($usernode)) {
                    $usernode = $node;
                }
            }
            $this->generate_user_settings($courseid, $USER->id);
        } else {
            $usernode = $this->generate_user_settings($courseid, $USER->id);
        }
        return $usernode;
    }

    
    public function extend_for_user($userid) {
        global $CFG;

        if (!in_array($userid, $this->userstoextendfor)) {
            $this->userstoextendfor[] = $userid;
            if ($this->initialised) {
                $this->generate_user_settings($this->page->course->id, $userid, 'userviewingsettings');
                $children = array();
                foreach ($this->children as $child) {
                    $children[] = $child;
                }
                array_unshift($children, array_pop($children));
                $this->children = new navigation_node_collection();
                foreach ($children as $child) {
                    $this->children->add($child);
                }
            }
        }
    }

    
    protected function generate_user_settings($courseid, $userid, $gstitle='usercurrentsettings') {
        global $DB, $CFG, $USER, $SITE;

        if ($courseid != $SITE->id) {
            if (!empty($this->page->course->id) && $this->page->course->id == $courseid) {
                $course = $this->page->course;
            } else {
                $select = context_helper::get_preload_record_columns_sql('ctx');
                $sql = "SELECT c.*, $select
                          FROM {course} c
                          JOIN {context} ctx ON c.id = ctx.instanceid
                         WHERE c.id = :courseid AND ctx.contextlevel = :contextlevel";
                $params = array('courseid' => $courseid, 'contextlevel' => CONTEXT_COURSE);
                $course = $DB->get_record_sql($sql, $params, MUST_EXIST);
                context_helper::preload_from_record($course);
            }
        } else {
            $course = $SITE;
        }

        $coursecontext = context_course::instance($course->id);           $systemcontext   = context_system::instance();
        $currentuser = ($USER->id == $userid);

        if ($currentuser) {
            $user = $USER;
            $usercontext = context_user::instance($user->id);               } else {
            $select = context_helper::get_preload_record_columns_sql('ctx');
            $sql = "SELECT u.*, $select
                      FROM {user} u
                      JOIN {context} ctx ON u.id = ctx.instanceid
                     WHERE u.id = :userid AND ctx.contextlevel = :contextlevel";
            $params = array('userid' => $userid, 'contextlevel' => CONTEXT_USER);
            $user = $DB->get_record_sql($sql, $params, IGNORE_MISSING);
            if (!$user) {
                return false;
            }
            context_helper::preload_from_record($user);

                        $usercontext = context_user::instance($user->id);             $canviewuser = has_capability('moodle/user:viewdetails', $usercontext);

            if ($course->id == $SITE->id) {
                if ($CFG->forceloginforprofiles && !has_coursecontact_role($user->id) && !$canviewuser) {                                          return false;
                }
            } else {
                $canviewusercourse = has_capability('moodle/user:viewdetails', $coursecontext);
                $userisenrolled = is_enrolled($coursecontext, $user->id, '', true);
                if ((!$canviewusercourse && !$canviewuser) || !$userisenrolled) {
                    return false;
                }
                $canaccessallgroups = has_capability('moodle/site:accessallgroups', $coursecontext);
                if (!$canaccessallgroups && groups_get_course_groupmode($course) == SEPARATEGROUPS && !$canviewuser) {
                                        if ($courseid == $this->page->course->id) {
                        $mygroups = get_fast_modinfo($this->page->course)->groups;
                    } else {
                        $mygroups = groups_get_user_groups($courseid);
                    }
                    $usergroups = groups_get_user_groups($courseid, $userid);
                    if (!array_intersect_key($mygroups[0], $usergroups[0])) {
                        return false;
                    }
                }
            }
        }

        $fullname = fullname($user, has_capability('moodle/site:viewfullnames', $this->page->context));

        $key = $gstitle;
        $prefurl = new moodle_url('/user/preferences.php');
        if ($gstitle != 'usercurrentsettings') {
            $key .= $userid;
            $prefurl->param('userid', $userid);
        }

                if ($gstitle == 'usercurrentsettings') {
            $dashboard = $this->add(get_string('myhome'), new moodle_url('/my/'), self::TYPE_CONTAINER, null, 'dashboard');
                                    $dashboard->display = false;
            if (get_home_page() == HOMEPAGE_MY) {
                $dashboard->mainnavonly = true;
            }

            $iscurrentuser = ($user->id == $USER->id);

            $baseargs = array('id' => $user->id);
            if ($course->id != $SITE->id && !$iscurrentuser) {
                $baseargs['course'] = $course->id;
                $issitecourse = false;
            } else {
                                $issitecourse = true;
            }

                        $profilenode = $dashboard->add(get_string('profile'), new moodle_url('/user/profile.php',
                    array('id' => $user->id)), self::TYPE_SETTING, null, 'myprofile');

            if (!empty($CFG->navadduserpostslinks)) {
                                                                $forumtab = $profilenode->add(get_string('forumposts', 'forum'));
                $forumtab->add(get_string('posts', 'forum'), new moodle_url('/mod/forum/user.php', $baseargs), null, 'myposts');
                $forumtab->add(get_string('discussions', 'forum'), new moodle_url('/mod/forum/user.php',
                        array_merge($baseargs, array('mode' => 'discussions'))), null, 'mydiscussions');
            }

                        if (!empty($CFG->enableblogs)) {
                if (!$this->cache->cached('userblogoptions'.$user->id)) {
                    require_once($CFG->dirroot.'/blog/lib.php');
                                        $options = blog_get_options_for_user($user);
                    $this->cache->set('userblogoptions'.$user->id, $options);
                } else {
                    $options = $this->cache->{'userblogoptions'.$user->id};
                }

                if (count($options) > 0) {
                    $blogs = $profilenode->add(get_string('blogs', 'blog'), null, navigation_node::TYPE_CONTAINER);
                    foreach ($options as $type => $option) {
                        if ($type == "rss") {
                            $blogs->add($option['string'], $option['link'], self::TYPE_SETTING, null, null,
                                    new pix_icon('i/rss', ''));
                        } else {
                            $blogs->add($option['string'], $option['link'], self::TYPE_SETTING, null, 'blog' . $type);
                        }
                    }
                }
            }

                                    if (!empty($CFG->messaging)) {
                $messageargs = array('user1' => $USER->id);
                if ($USER->id != $user->id) {
                    $messageargs['user2'] = $user->id;
                }
                if ($course->id != $SITE->id) {
                    $messageargs['viewing'] = MESSAGE_VIEW_COURSE. $course->id;
                }
                $url = new moodle_url('/message/index.php', $messageargs);
                $dashboard->add(get_string('messages', 'message'), $url, self::TYPE_SETTING, null, 'messages');
            }

                                    if ($issitecourse && $iscurrentuser && has_capability('moodle/user:manageownfiles', $usercontext)) {
                $url = new moodle_url('/user/files.php');
                $dashboard->add(get_string('privatefiles'), $url, self::TYPE_SETTING);
            }

                        if (!empty($CFG->enablenotes) &&
                    has_any_capability(array('moodle/notes:manage', 'moodle/notes:view'), $coursecontext)) {
                $url = new moodle_url('/notes/index.php', array('user' => $user->id));
                if ($coursecontext->instanceid != SITEID) {
                    $url->param('course', $coursecontext->instanceid);
                }
                $profilenode->add(get_string('notes', 'notes'), $url);
            }

                        if (($issitecourse && $iscurrentuser) || has_capability('moodle/user:viewdetails', $usercontext)) {
                require_once($CFG->dirroot . '/user/lib.php');
                                if ($course->id == SITEID) {
                    $url = user_mygrades_url($user->id, $course->id);
                } else {                     $url = new moodle_url('/course/user.php', array('mode' => 'grade', 'id' => $course->id, 'user' => $user->id));
                }
                $dashboard->add(get_string('grades', 'grades'), $url, self::TYPE_SETTING, null, 'mygrades');
            }

                        $pluginsfunction = get_plugins_with_function('extend_navigation_user', 'lib.php');
            foreach ($pluginsfunction as $plugintype => $plugins) {
                if ($plugintype != 'report') {
                    foreach ($plugins as $pluginfunction) {
                        $pluginfunction($profilenode, $user, $usercontext, $course, $coursecontext);
                    }
                }
            }

            $usersetting = navigation_node::create(get_string('preferences', 'moodle'), $prefurl, self::TYPE_CONTAINER, null, $key);
            $dashboard->add_node($usersetting);
        } else {
            $usersetting = $this->add(get_string('preferences', 'moodle'), $prefurl, self::TYPE_CONTAINER, null, $key);
            $usersetting->display = false;
        }
        $usersetting->id = 'usersettings';

                if ($user->deleted) {
            if (!has_capability('moodle/user:update', $coursecontext)) {
                                $usersetting->add(get_string('userdeleted'), null, self::TYPE_SETTING);
            } else {
                                if ($course->id == $SITE->id) {
                    $profileurl = new moodle_url('/user/profile.php', array('id'=>$user->id));
                } else {
                    $profileurl = new moodle_url('/user/view.php', array('id'=>$user->id, 'course'=>$course->id));
                }
                $usersetting->add(get_string('userdeleted'), $profileurl, self::TYPE_SETTING);
            }
            return true;
        }

        $userauthplugin = false;
        if (!empty($user->auth)) {
            $userauthplugin = get_auth_plugin($user->auth);
        }

        $useraccount = $usersetting->add(get_string('useraccount'), null, self::TYPE_CONTAINER, null, 'useraccount');

                if (isloggedin() && !isguestuser($user) && !is_mnet_remote_user($user)) {
            if (($currentuser || is_siteadmin($USER) || !is_siteadmin($user)) &&
                    has_capability('moodle/user:update', $systemcontext)) {
                $url = new moodle_url('/user/editadvanced.php', array('id'=>$user->id, 'course'=>$course->id));
                $useraccount->add(get_string('editmyprofile'), $url, self::TYPE_SETTING);
            } else if ((has_capability('moodle/user:editprofile', $usercontext) && !is_siteadmin($user)) ||
                    ($currentuser && has_capability('moodle/user:editownprofile', $systemcontext))) {
                if ($userauthplugin && $userauthplugin->can_edit_profile()) {
                    $url = $userauthplugin->edit_profile_url();
                    if (empty($url)) {
                        $url = new moodle_url('/user/edit.php', array('id'=>$user->id, 'course'=>$course->id));
                    }
                    $useraccount->add(get_string('editmyprofile'), $url, self::TYPE_SETTING);
                }
            }
        }

                if ($userauthplugin && $currentuser && !\core\session\manager::is_loggedinas() && !isguestuser() &&
                has_capability('moodle/user:changeownpassword', $systemcontext) && $userauthplugin->can_change_password()) {
            $passwordchangeurl = $userauthplugin->change_password_url();
            if (empty($passwordchangeurl)) {
                $passwordchangeurl = new moodle_url('/login/change_password.php', array('id'=>$course->id));
            }
            $useraccount->add(get_string("changepassword"), $passwordchangeurl, self::TYPE_SETTING, null, 'changepassword');
        }

        if (isloggedin() && !isguestuser($user) && !is_mnet_remote_user($user)) {
            if ($currentuser && has_capability('moodle/user:editownprofile', $systemcontext) ||
                    has_capability('moodle/user:editprofile', $usercontext)) {
                $url = new moodle_url('/user/language.php', array('id' => $user->id, 'course' => $course->id));
                $useraccount->add(get_string('preferredlanguage'), $url, self::TYPE_SETTING, null, 'preferredlanguage');
            }
        }
        $pluginmanager = core_plugin_manager::instance();
        $enabled = $pluginmanager->get_enabled_plugins('mod');
        if (isset($enabled['forum']) && isloggedin() && !isguestuser($user) && !is_mnet_remote_user($user)) {
            if ($currentuser && has_capability('moodle/user:editownprofile', $systemcontext) ||
                    has_capability('moodle/user:editprofile', $usercontext)) {
                $url = new moodle_url('/user/forum.php', array('id' => $user->id, 'course' => $course->id));
                $useraccount->add(get_string('forumpreferences'), $url, self::TYPE_SETTING);
            }
        }
        $editors = editors_get_enabled();
        if (count($editors) > 1) {
            if (isloggedin() && !isguestuser($user) && !is_mnet_remote_user($user)) {
                if ($currentuser && has_capability('moodle/user:editownprofile', $systemcontext) ||
                        has_capability('moodle/user:editprofile', $usercontext)) {
                    $url = new moodle_url('/user/editor.php', array('id' => $user->id, 'course' => $course->id));
                    $useraccount->add(get_string('editorpreferences'), $url, self::TYPE_SETTING);
                }
            }
        }

                if (has_any_capability(array('moodle/role:assign', 'moodle/role:safeoverride', 'moodle/role:override',
                'moodle/role:manage'), $usercontext)) {
            $roles = $usersetting->add(get_string('roles'), null, self::TYPE_SETTING);

            $url = new moodle_url('/admin/roles/usersroles.php', array('userid'=>$user->id, 'courseid'=>$course->id));
            $roles->add(get_string('thisusersroles', 'role'), $url, self::TYPE_SETTING);

            $assignableroles = get_assignable_roles($usercontext, ROLENAME_BOTH);

            if (!empty($assignableroles)) {
                $url = new moodle_url('/admin/roles/assign.php',
                        array('contextid' => $usercontext->id, 'userid' => $user->id, 'courseid' => $course->id));
                $roles->add(get_string('assignrolesrelativetothisuser', 'role'), $url, self::TYPE_SETTING);
            }

            if (has_capability('moodle/role:review', $usercontext) || count(get_overridable_roles($usercontext, ROLENAME_BOTH))>0) {
                $url = new moodle_url('/admin/roles/permissions.php',
                        array('contextid' => $usercontext->id, 'userid' => $user->id, 'courseid' => $course->id));
                $roles->add(get_string('permissions', 'role'), $url, self::TYPE_SETTING);
            }

            $url = new moodle_url('/admin/roles/check.php',
                    array('contextid' => $usercontext->id, 'userid' => $user->id, 'courseid' => $course->id));
            $roles->add(get_string('checkpermissions', 'role'), $url, self::TYPE_SETTING);
        }

                if (!$this->cache->cached('contexthasrepos'.$usercontext->id)) {
            require_once($CFG->dirroot . '/repository/lib.php');
            $editabletypes = repository::get_editable_types($usercontext);
            $haseditabletypes = !empty($editabletypes);
            unset($editabletypes);
            $this->cache->set('contexthasrepos'.$usercontext->id, $haseditabletypes);
        } else {
            $haseditabletypes = $this->cache->{'contexthasrepos'.$usercontext->id};
        }
        if ($haseditabletypes) {
            $repositories = $usersetting->add(get_string('repositories', 'repository'), null, self::TYPE_SETTING);
            $repositories->add(get_string('manageinstances', 'repository'), new moodle_url('/repository/manage_instances.php',
                array('contextid' => $usercontext->id)));
        }

                if ($currentuser && !empty($CFG->enableportfolios) && has_capability('moodle/portfolio:export', $systemcontext)) {
            require_once($CFG->libdir . '/portfoliolib.php');
            if (portfolio_has_visible_instances()) {
                $portfolio = $usersetting->add(get_string('portfolios', 'portfolio'), null, self::TYPE_SETTING);

                $url = new moodle_url('/user/portfolio.php', array('courseid'=>$course->id));
                $portfolio->add(get_string('configure', 'portfolio'), $url, self::TYPE_SETTING);

                $url = new moodle_url('/user/portfoliologs.php', array('courseid'=>$course->id));
                $portfolio->add(get_string('logs', 'portfolio'), $url, self::TYPE_SETTING);
            }
        }

        $enablemanagetokens = false;
        if (!empty($CFG->enablerssfeeds)) {
            $enablemanagetokens = true;
        } else if (!is_siteadmin($USER->id)
             && !empty($CFG->enablewebservices)
             && has_capability('moodle/webservice:createtoken', context_system::instance()) ) {
            $enablemanagetokens = true;
        }
                if ($currentuser && $enablemanagetokens) {
            $url = new moodle_url('/user/managetoken.php', array('sesskey'=>sesskey()));
            $useraccount->add(get_string('securitykeys', 'webservice'), $url, self::TYPE_SETTING);
        }

                if (($currentuser && has_capability('moodle/user:editownmessageprofile', $systemcontext)) || (!isguestuser($user) &&
                has_capability('moodle/user:editmessageprofile', $usercontext) && !is_primary_admin($user->id))) {
            $url = new moodle_url('/message/edit.php', array('id'=>$user->id));
            $useraccount->add(get_string('messaging', 'message'), $url, self::TYPE_SETTING);
        }

                if ($currentuser && !empty($CFG->enableblogs)) {
            $blog = $usersetting->add(get_string('blogs', 'blog'), null, navigation_node::TYPE_CONTAINER, null, 'blogs');
            if (has_capability('moodle/blog:view', $systemcontext)) {
                $blog->add(get_string('preferences', 'blog'), new moodle_url('/blog/preferences.php'),
                        navigation_node::TYPE_SETTING);
            }
            if (!empty($CFG->useexternalblogs) && $CFG->maxexternalblogsperuser > 0 &&
                    has_capability('moodle/blog:manageexternal', $systemcontext)) {
                $blog->add(get_string('externalblogs', 'blog'), new moodle_url('/blog/external_blogs.php'),
                        navigation_node::TYPE_SETTING);
                $blog->add(get_string('addnewexternalblog', 'blog'), new moodle_url('/blog/external_blog_edit.php'),
                        navigation_node::TYPE_SETTING);
            }
                        $blog->trim_if_empty();
        }

                if ($currentuser && !empty($CFG->enablebadges)) {
            $badges = $usersetting->add(get_string('badges'), null, navigation_node::TYPE_CONTAINER, null, 'badges');
            if (has_capability('moodle/badges:manageownbadges', $usercontext)) {
                $url = new moodle_url('/badges/mybadges.php');
                $badges->add(get_string('managebadges', 'badges'), $url, self::TYPE_SETTING);
            }
            $badges->add(get_string('preferences', 'badges'), new moodle_url('/badges/preferences.php'),
                    navigation_node::TYPE_SETTING);
            if (!empty($CFG->badges_allowexternalbackpack)) {
                $badges->add(get_string('backpackdetails', 'badges'), new moodle_url('/badges/mybackpack.php'),
                        navigation_node::TYPE_SETTING);
            }
        }

                $pluginsfunction = get_plugins_with_function('extend_navigation_user_settings', 'lib.php');
        foreach ($pluginsfunction as $plugintype => $plugins) {
            foreach ($plugins as $pluginfunction) {
                $pluginfunction($usersetting, $user, $usercontext, $course, $coursecontext);
            }
        }

        return $usersetting;
    }

    
    protected function load_block_settings() {
        global $CFG;

        $blocknode = $this->add($this->context->get_context_name(), null, self::TYPE_SETTING, null, 'blocksettings');
        $blocknode->force_open();

                if (get_assignable_roles($this->context, ROLENAME_ORIGINAL)) {
            $assignurl = new moodle_url('/'.$CFG->admin.'/roles/assign.php', array('contextid' => $this->context->id));
            $blocknode->add(get_string('assignroles', 'role'), $assignurl, self::TYPE_SETTING, null,
                'roles', new pix_icon('i/assignroles', ''));
        }

                if (has_capability('moodle/role:review', $this->context) or  count(get_overridable_roles($this->context))>0) {
            $url = new moodle_url('/'.$CFG->admin.'/roles/permissions.php', array('contextid'=>$this->context->id));
            $blocknode->add(get_string('permissions', 'role'), $url, self::TYPE_SETTING, null,
                'permissions', new pix_icon('i/permissions', ''));
        }
                if (has_any_capability(array('moodle/role:assign', 'moodle/role:safeoverride','moodle/role:override', 'moodle/role:assign'), $this->context)) {
            $url = new moodle_url('/'.$CFG->admin.'/roles/check.php', array('contextid'=>$this->context->id));
            $blocknode->add(get_string('checkpermissions', 'role'), $url, self::TYPE_SETTING, null,
                'checkpermissions', new pix_icon('i/checkpermissions', ''));
        }

        return $blocknode;
    }

    
    protected function load_category_settings() {
        global $CFG;

                        if ($this->context->contextlevel == CONTEXT_BLOCK) {
            $catcontext = $this->context->get_parent_context();
        } else {
            $catcontext = $this->context;
        }

                if ($catcontext->contextlevel != CONTEXT_COURSECAT) {
            throw new coding_exception('Unexpected context while loading category settings.');
        }

        $categorynode = $this->add($catcontext->get_context_name(), null, null, null, 'categorysettings');
        $categorynode->nodetype = navigation_node::NODETYPE_BRANCH;
        $categorynode->force_open();

        if (can_edit_in_category($catcontext->instanceid)) {
            $url = new moodle_url('/course/management.php', array('categoryid' => $catcontext->instanceid));
            $editstring = get_string('managecategorythis');
            $categorynode->add($editstring, $url, self::TYPE_SETTING, null, null, new pix_icon('i/edit', ''));
        }

        if (has_capability('moodle/category:manage', $catcontext)) {
            $editurl = new moodle_url('/course/editcategory.php', array('id' => $catcontext->instanceid));
            $categorynode->add(get_string('editcategorythis'), $editurl, self::TYPE_SETTING, null, 'edit', new pix_icon('i/edit', ''));

            $addsubcaturl = new moodle_url('/course/editcategory.php', array('parent' => $catcontext->instanceid));
            $categorynode->add(get_string('addsubcategory'), $addsubcaturl, self::TYPE_SETTING, null, 'addsubcat', new pix_icon('i/withsubcat', ''));
        }

                $assignableroles = get_assignable_roles($catcontext);
        if (!empty($assignableroles)) {
            $assignurl = new moodle_url('/'.$CFG->admin.'/roles/assign.php', array('contextid' => $catcontext->id));
            $categorynode->add(get_string('assignroles', 'role'), $assignurl, self::TYPE_SETTING, null, 'roles', new pix_icon('i/assignroles', ''));
        }

                if (has_capability('moodle/role:review', $catcontext) or count(get_overridable_roles($catcontext)) > 0) {
            $url = new moodle_url('/'.$CFG->admin.'/roles/permissions.php', array('contextid' => $catcontext->id));
            $categorynode->add(get_string('permissions', 'role'), $url, self::TYPE_SETTING, null, 'permissions', new pix_icon('i/permissions', ''));
        }
                if (has_any_capability(array('moodle/role:assign', 'moodle/role:safeoverride',
                'moodle/role:override', 'moodle/role:assign'), $catcontext)) {
            $url = new moodle_url('/'.$CFG->admin.'/roles/check.php', array('contextid' => $catcontext->id));
            $categorynode->add(get_string('checkpermissions', 'role'), $url, self::TYPE_SETTING, null, 'checkpermissions', new pix_icon('i/checkpermissions', ''));
        }

                if (has_any_capability(array('moodle/cohort:view', 'moodle/cohort:manage'), $catcontext)) {
            $categorynode->add(get_string('cohorts', 'cohort'), new moodle_url('/cohort/index.php',
                array('contextid' => $catcontext->id)), self::TYPE_SETTING, null, 'cohort', new pix_icon('i/cohort', ''));
        }

                if (has_capability('moodle/filter:manage', $catcontext) && count(filter_get_available_in_context($catcontext)) > 0) {
            $url = new moodle_url('/filter/manage.php', array('contextid' => $catcontext->id));
            $categorynode->add(get_string('filters', 'admin'), $url, self::TYPE_SETTING, null, 'filters', new pix_icon('i/filter', ''));
        }

                if (has_capability('moodle/restore:restorecourse', $catcontext)) {
            $url = new moodle_url('/backup/restorefile.php', array('contextid' => $catcontext->id));
            $categorynode->add(get_string('restorecourse', 'admin'), $url, self::TYPE_SETTING, null, 'restorecourse', new pix_icon('i/restore', ''));
        }

                $pluginsfunction = get_plugins_with_function('extend_navigation_category_settings', 'lib.php');
        foreach ($pluginsfunction as $plugintype => $plugins) {
            foreach ($plugins as $pluginfunction) {
                $pluginfunction($categorynode, $catcontext);
            }
        }

        return $categorynode;
    }

    
    protected function in_alternative_role() {
        global $USER;
        if (!empty($USER->access['rsw']) && is_array($USER->access['rsw'])) {
            if (!empty($this->page->context) && !empty($USER->access['rsw'][$this->page->context->path])) {
                return $USER->access['rsw'][$this->page->context->path];
            }
            foreach ($USER->access['rsw'] as $key=>$role) {
                if (strpos($this->context->path,$key)===0) {
                    return $role;
                }
            }
        }
        return false;
    }

    
    protected function load_front_page_settings($forceopen = false) {
        global $SITE, $CFG;

        $course = clone($SITE);
        $coursecontext = context_course::instance($course->id);   
        $frontpage = $this->add(get_string('frontpagesettings'), null, self::TYPE_SETTING, null, 'frontpage');
        if ($forceopen) {
            $frontpage->force_open();
        }
        $frontpage->id = 'frontpagesettings';

        if ($this->page->user_allowed_editing()) {

                        $url = new moodle_url('/course/view.php', array('id'=>$course->id, 'sesskey'=>sesskey()));
            if ($this->page->user_is_editing()) {
                $url->param('edit', 'off');
                $editstring = get_string('turneditingoff');
            } else {
                $url->param('edit', 'on');
                $editstring = get_string('turneditingon');
            }
            $frontpage->add($editstring, $url, self::TYPE_SETTING, null, null, new pix_icon('i/edit', ''));
        }

        if (has_capability('moodle/course:update', $coursecontext)) {
                        $url = new moodle_url('/admin/settings.php', array('section'=>'frontpagesettings'));
            $frontpage->add(get_string('editsettings'), $url, self::TYPE_SETTING, null, null, new pix_icon('i/settings', ''));
        }

                enrol_add_course_navigation($frontpage, $course);

                if (has_capability('moodle/filter:manage', $coursecontext) && count(filter_get_available_in_context($coursecontext))>0) {
            $url = new moodle_url('/filter/manage.php', array('contextid'=>$coursecontext->id));
            $frontpage->add(get_string('filters', 'admin'), $url, self::TYPE_SETTING, null, null, new pix_icon('i/filter', ''));
        }

                if (has_capability('moodle/site:viewreports', $coursecontext)) {             $frontpagenav = $frontpage->add(get_string('reports'), null, self::TYPE_CONTAINER, null, 'frontpagereports',
                    new pix_icon('i/stats', ''));
            $coursereports = core_component::get_plugin_list('coursereport');
            foreach ($coursereports as $report=>$dir) {
                $libfile = $CFG->dirroot.'/course/report/'.$report.'/lib.php';
                if (file_exists($libfile)) {
                    require_once($libfile);
                    $reportfunction = $report.'_report_extend_navigation';
                    if (function_exists($report.'_report_extend_navigation')) {
                        $reportfunction($frontpagenav, $course, $coursecontext);
                    }
                }
            }

            $reports = get_plugin_list_with_function('report', 'extend_navigation_course', 'lib.php');
            foreach ($reports as $reportfunction) {
                $reportfunction($frontpagenav, $course, $coursecontext);
            }
        }

                if (has_capability('moodle/backup:backupcourse', $coursecontext)) {
            $url = new moodle_url('/backup/backup.php', array('id'=>$course->id));
            $frontpage->add(get_string('backup'), $url, self::TYPE_SETTING, null, null, new pix_icon('i/backup', ''));
        }

                if (has_capability('moodle/restore:restorecourse', $coursecontext)) {
            $url = new moodle_url('/backup/restorefile.php', array('contextid'=>$coursecontext->id));
            $frontpage->add(get_string('restore'), $url, self::TYPE_SETTING, null, null, new pix_icon('i/restore', ''));
        }

                require_once($CFG->libdir . '/questionlib.php');
        question_extend_settings_navigation($frontpage, $coursecontext)->trim_if_empty();

                if ($course->legacyfiles == 2 and has_capability('moodle/course:managefiles', $this->context)) {
                        $url = new moodle_url('/files/index.php', array('contextid'=>$coursecontext->id));
            $frontpage->add(get_string('sitelegacyfiles'), $url, self::TYPE_SETTING, null, null, new pix_icon('i/folder', ''));
        }

                $pluginsfunction = get_plugins_with_function('extend_navigation_frontpage', 'lib.php');
        foreach ($pluginsfunction as $plugintype => $plugins) {
            foreach ($plugins as $pluginfunction) {
                $pluginfunction($frontpage, $course, $coursecontext);
            }
        }

        return $frontpage;
    }

    
    protected function load_local_plugin_settings() {

        foreach (get_plugin_list_with_function('local', 'extend_settings_navigation') as $function) {
            $function($this, $this->context);
        }
    }

    
    public function clear_cache() {
        $this->cache->volatile();
    }

    
    public function can_view_user_preferences($userid) {
        if (is_siteadmin()) {
            return true;
        }
                $preferencenode = $this->find('userviewingsettings' . $userid, null);
        if ($preferencenode && $preferencenode->has_children()) {
                        foreach ($preferencenode->children as $childnode) {
                                if ($childnode->has_children()) {
                    return true;
                }
            }
        }
                return false;
    }
}


class settings_navigation_ajax extends settings_navigation {
    
    public function __construct(moodle_page &$page) {
        $this->page = $page;
        $this->cache = new navigation_cache(NAVIGATION_CACHE_NAME);
        $this->children = new navigation_node_collection();
        $this->initialise();
    }

    
    public function initialise() {
        if ($this->initialised || during_initial_install()) {
            return false;
        }
        $this->context = $this->page->context;
        $this->load_administration_settings();

                $this->load_local_plugin_settings();

        $this->initialised = true;
    }
}


class navigation_json {
    
    protected $nodetype = array('node','branch');
    
    protected $expandable = array();
    
    public function convert($branch) {
        $xml = $this->convert_child($branch);
        return $xml;
    }
    
    public function set_expandable($expandable) {
        foreach ($expandable as $node) {
            $this->expandable[$node['key'].':'.$node['type']] = $node;
        }
    }
    
    protected function convert_child($child, $depth=1) {
        if (!$child->display) {
            return '';
        }
        $attributes = array();
        $attributes['id'] = $child->id;
        $attributes['name'] = (string)$child->text;         $attributes['type'] = $child->type;
        $attributes['key'] = $child->key;
        $attributes['class'] = $child->get_css_type();
        $attributes['requiresajaxloading'] = $child->requiresajaxloading;

        if ($child->icon instanceof pix_icon) {
            $attributes['icon'] = array(
                'component' => $child->icon->component,
                'pix' => $child->icon->pix,
            );
            foreach ($child->icon->attributes as $key=>$value) {
                if ($key == 'class') {
                    $attributes['icon']['classes'] = explode(' ', $value);
                } else if (!array_key_exists($key, $attributes['icon'])) {
                    $attributes['icon'][$key] = $value;
                }

            }
        } else if (!empty($child->icon)) {
            $attributes['icon'] = (string)$child->icon;
        }

        if ($child->forcetitle || $child->title !== $child->text) {
            $attributes['title'] = htmlentities($child->title, ENT_QUOTES, 'UTF-8');
        }
        if (array_key_exists($child->key.':'.$child->type, $this->expandable)) {
            $attributes['expandable'] = $child->key;
            $child->add_class($this->expandable[$child->key.':'.$child->type]['id']);
        }

        if (count($child->classes)>0) {
            $attributes['class'] .= ' '.join(' ',$child->classes);
        }
        if (is_string($child->action)) {
            $attributes['link'] = $child->action;
        } else if ($child->action instanceof moodle_url) {
            $attributes['link'] = $child->action->out();
        } else if ($child->action instanceof action_link) {
            $attributes['link'] = $child->action->url->out();
        }
        $attributes['hidden'] = ($child->hidden);
        $attributes['haschildren'] = ($child->children->count()>0 || $child->type == navigation_node::TYPE_CATEGORY);
        $attributes['haschildren'] = $attributes['haschildren'] || $child->type == navigation_node::TYPE_MY_CATEGORY;

        if ($child->children->count() > 0) {
            $attributes['children'] = array();
            foreach ($child->children as $subchild) {
                $attributes['children'][] = $this->convert_child($subchild, $depth+1);
            }
        }

        if ($depth > 1) {
            return $attributes;
        } else {
            return json_encode($attributes);
        }
    }
}


class navigation_cache {
    
    protected $creation;
    
    protected $session;
    
    protected $area;
    
    protected $timeout;
    
    protected $currentcontext;
    
    const CACHETIME = 0;
    
    const CACHEUSERID = 1;
    
    const CACHEVALUE = 2;
    
    public static $volatilecaches;

    
    public function __construct($area, $timeout=1800) {
        $this->creation = time();
        $this->area = $area;
        $this->timeout = time() - $timeout;
        if (rand(0,100) === 0) {
            $this->garbage_collection();
        }
    }

    
    protected function ensure_session_cache_initialised() {
        global $SESSION;
        if (empty($this->session)) {
            if (!isset($SESSION->navcache)) {
                $SESSION->navcache = new stdClass;
            }
            if (!isset($SESSION->navcache->{$this->area})) {
                $SESSION->navcache->{$this->area} = array();
            }
            $this->session = &$SESSION->navcache->{$this->area};         }
    }

    
    public function __get($key) {
        if (!$this->cached($key)) {
            return;
        }
        $information = $this->session[$key][self::CACHEVALUE];
        return unserialize($information);
    }

    
    public function __set($key, $information) {
        $this->set($key, $information);
    }

    
    public function set($key, $information) {
        global $USER;
        $this->ensure_session_cache_initialised();
        $information = serialize($information);
        $this->session[$key]= array(self::CACHETIME=>time(), self::CACHEUSERID=>$USER->id, self::CACHEVALUE=>$information);
    }
    
    public function cached($key) {
        global $USER;
        $this->ensure_session_cache_initialised();
        if (!array_key_exists($key, $this->session) || !is_array($this->session[$key]) || $this->session[$key][self::CACHEUSERID]!=$USER->id || $this->session[$key][self::CACHETIME] < $this->timeout) {
            return false;
        }
        return true;
    }
    
    public function compare($key, $value, $serialise = true) {
        if ($this->cached($key)) {
            if ($serialise) {
                $value = serialize($value);
            }
            if ($this->session[$key][self::CACHEVALUE] === $value) {
                return true;
            }
        }
        return false;
    }
    
    public function clear() {
        global $SESSION;
        unset($SESSION->navcache);
        $this->session = null;
    }
    
    protected function garbage_collection() {
        if (empty($this->session)) {
            return true;
        }
        foreach ($this->session as $key=>$cachedinfo) {
            if (is_array($cachedinfo) && $cachedinfo[self::CACHETIME]<$this->timeout) {
                unset($this->session[$key]);
            }
        }
    }

    
    public function volatile($setting = true) {
        if (self::$volatilecaches===null) {
            self::$volatilecaches = array();
            core_shutdown_manager::register_function(array('navigation_cache','destroy_volatile_caches'));
        }

        if ($setting) {
            self::$volatilecaches[$this->area] = $this->area;
        } else if (array_key_exists($this->area, self::$volatilecaches)) {
            unset(self::$volatilecaches[$this->area]);
        }
    }

    
    public static function destroy_volatile_caches() {
        global $SESSION;
        if (is_array(self::$volatilecaches) && count(self::$volatilecaches)>0) {
            foreach (self::$volatilecaches as $area) {
                $SESSION->navcache->{$area} = array();
            }
        } else {
            $SESSION->navcache = new stdClass;
        }
    }
}
