<?php



defined('MOODLE_INTERNAL') || die();


class testing_data_generator {
    
    protected $gradecategorycounter = 0;
    
    protected $gradeitemcounter = 0;
    
    protected $gradeoutcomecounter = 0;
    protected $usercounter = 0;
    protected $categorycount = 0;
    protected $cohortcount = 0;
    protected $coursecount = 0;
    protected $scalecount = 0;
    protected $groupcount = 0;
    protected $groupingcount = 0;
    protected $rolecount = 0;
    protected $tagcount = 0;

    
    protected $generators = array();

    
    public $lastnames = array(
        'Smith', 'Johnson', 'Williams', 'Brown', 'Jones', 'Miller', 'Davis', 'García', 'Rodríguez', 'Wilson',
        'Müller', 'Schmidt', 'Schneider', 'Fischer', 'Meyer', 'Weber', 'Schulz', 'Wagner', 'Becker', 'Hoffmann',
        'Novák', 'Svoboda', 'Novotný', 'Dvořák', 'Černý', 'Procházková', 'Kučerová', 'Veselá', 'Horáková', 'Němcová',
        'Смирнов', 'Иванов', 'Кузнецов', 'Соколов', 'Попов', 'Лебедева', 'Козлова', 'Новикова', 'Морозова', 'Петрова',
        '王', '李', '张', '刘', '陈', '楊', '黃', '趙', '吳', '周',
        '佐藤', '鈴木', '高橋', '田中', '渡辺', '伊藤', '山本', '中村', '小林', '斎藤',
    );

    
    public $firstnames = array(
        'Jacob', 'Ethan', 'Michael', 'Jayden', 'William', 'Isabella', 'Sophia', 'Emma', 'Olivia', 'Ava',
        'Lukas', 'Leon', 'Luca', 'Timm', 'Paul', 'Leonie', 'Leah', 'Lena', 'Hanna', 'Laura',
        'Jakub', 'Jan', 'Tomáš', 'Lukáš', 'Matěj', 'Tereza', 'Eliška', 'Anna', 'Adéla', 'Karolína',
        'Даниил', 'Максим', 'Артем', 'Иван', 'Александр', 'София', 'Анастасия', 'Дарья', 'Мария', 'Полина',
        '伟', '伟', '芳', '伟', '秀英', '秀英', '娜', '秀英', '伟', '敏',
        '翔', '大翔', '拓海', '翔太', '颯太', '陽菜', 'さくら', '美咲', '葵', '美羽',
    );

    public $loremipsum = <<<EOD
Lorem ipsum dolor sit amet, consectetuer adipiscing elit. Nulla non arcu lacinia neque faucibus fringilla. Vivamus porttitor turpis ac leo. Integer in sapien. Nullam eget nisl. Aliquam erat volutpat. Cras elementum. Mauris suscipit, ligula sit amet pharetra semper, nibh ante cursus purus, vel sagittis velit mauris vel metus. Integer malesuada. Nullam lectus justo, vulputate eget mollis sed, tempor sed magna. Mauris elementum mauris vitae tortor. Aliquam erat volutpat.
Temporibus autem quibusdam et aut officiis debitis aut rerum necessitatibus saepe eveniet ut et voluptates repudiandae sint et molestiae non recusandae. Pellentesque ipsum. Cras pede libero, dapibus nec, pretium sit amet, tempor quis. Aliquam ante. Proin in tellus sit amet nibh dignissim sagittis. Vivamus porttitor turpis ac leo. Duis bibendum, lectus ut viverra rhoncus, dolor nunc faucibus libero, eget facilisis enim ipsum id lacus. In sem justo, commodo ut, suscipit at, pharetra vitae, orci. Aliquam erat volutpat. Nulla est.
Vivamus luctus egestas leo. Aenean fermentum risus id tortor. Mauris dictum facilisis augue. Aliquam erat volutpat. Aliquam ornare wisi eu metus. Aliquam id dolor. Duis condimentum augue id magna semper rutrum. Donec iaculis gravida nulla. Pellentesque ipsum. Etiam dictum tincidunt diam. Quisque tincidunt scelerisque libero. Etiam egestas wisi a erat.
Integer lacinia. Cum sociis natoque penatibus et magnis dis parturient montes, nascetur ridiculus mus. Mauris tincidunt sem sed arcu. Nullam feugiat, turpis at pulvinar vulputate, erat libero tristique tellus, nec bibendum odio risus sit amet ante. Aliquam id dolor. Maecenas sollicitudin. Et harum quidem rerum facilis est et expedita distinctio. Mauris suscipit, ligula sit amet pharetra semper, nibh ante cursus purus, vel sagittis velit mauris vel metus. Nullam dapibus fermentum ipsum. Pellentesque habitant morbi tristique senectus et netus et malesuada fames ac turpis egestas. Nemo enim ipsam voluptatem quia voluptas sit aspernatur aut odit aut fugit, sed quia consequuntur magni dolores eos qui ratione voluptatem sequi nesciunt. Pellentesque sapien. Duis risus. Mauris elementum mauris vitae tortor. Suspendisse nisl. Integer rutrum, orci vestibulum ullamcorper ultricies, lacus quam ultricies odio, vitae placerat pede sem sit amet enim.
In laoreet, magna id viverra tincidunt, sem odio bibendum justo, vel imperdiet sapien wisi sed libero. Proin pede metus, vulputate nec, fermentum fringilla, vehicula vitae, justo. Nullam justo enim, consectetuer nec, ullamcorper ac, vestibulum in, elit. Quis autem vel eum iure reprehenderit qui in ea voluptate velit esse quam nihil molestiae consequatur, vel illum qui dolorem eum fugiat quo voluptas nulla pariatur? Maecenas lorem. Etiam posuere lacus quis dolor. Class aptent taciti sociosqu ad litora torquent per conubia nostra, per inceptos hymenaeos. Curabitur ligula sapien, pulvinar a vestibulum quis, facilisis vel sapien. Nam sed tellus id magna elementum tincidunt. Suspendisse nisl. Vivamus luctus egestas leo. Nulla non arcu lacinia neque faucibus fringilla. Etiam dui sem, fermentum vitae, sagittis id, malesuada in, quam. Etiam dictum tincidunt diam. Etiam commodo dui eget wisi. Nemo enim ipsam voluptatem quia voluptas sit aspernatur aut odit aut fugit, sed quia consequuntur magni dolores eos qui ratione voluptatem sequi nesciunt. Proin pede metus, vulputate nec, fermentum fringilla, vehicula vitae, justo. Duis ante orci, molestie vitae vehicula venenatis, tincidunt ac pede. Pellentesque sapien.
EOD;

    
    public function reset() {
        $this->usercounter = 0;
        $this->categorycount = 0;
        $this->coursecount = 0;
        $this->scalecount = 0;

        foreach ($this->generators as $generator) {
            $generator->reset();
        }
    }

    
    public function get_plugin_generator($component) {
        list($type, $plugin) = core_component::normalize_component($component);
        $cleancomponent = $type . '_' . $plugin;
        if ($cleancomponent != $component) {
            debugging("Please specify the component you want a generator for as " .
                    "{$cleancomponent}, not {$component}.", DEBUG_DEVELOPER);
            $component = $cleancomponent;
        }

        if (isset($this->generators[$component])) {
            return $this->generators[$component];
        }

        $dir = core_component::get_component_directory($component);
        $lib = $dir . '/tests/generator/lib.php';
        if (!$dir || !is_readable($lib)) {
            throw new coding_exception("Component {$component} does not support " .
                    "generators yet. Missing tests/generator/lib.php.");
        }

        include_once($lib);
        $classname = $component . '_generator';

        if (!class_exists($classname)) {
            throw new coding_exception("Component {$component} does not support " .
                    "data generators yet. Class {$classname} not found.");
        }

        $this->generators[$component] = new $classname($this);
        return $this->generators[$component];
    }

    
    public function create_user($record=null, array $options=null) {
        global $DB, $CFG;

        $this->usercounter++;
        $i = $this->usercounter;

        $record = (array)$record;

        if (!isset($record['auth'])) {
            $record['auth'] = 'manual';
        }

        if (!isset($record['firstname']) and !isset($record['lastname'])) {
            $country = rand(0, 5);
            $firstname = rand(0, 4);
            $lastname = rand(0, 4);
            $female = rand(0, 1);
            $record['firstname'] = $this->firstnames[($country*10) + $firstname + ($female*5)];
            $record['lastname'] = $this->lastnames[($country*10) + $lastname + ($female*5)];

        } else if (!isset($record['firstname'])) {
            $record['firstname'] = 'Firstname'.$i;

        } else if (!isset($record['lastname'])) {
            $record['lastname'] = 'Lastname'.$i;
        }

        if (!isset($record['firstnamephonetic'])) {
            $firstnamephonetic = rand(0, 59);
            $record['firstnamephonetic'] = $this->firstnames[$firstnamephonetic];
        }

        if (!isset($record['lastnamephonetic'])) {
            $lastnamephonetic = rand(0, 59);
            $record['lastnamephonetic'] = $this->lastnames[$lastnamephonetic];
        }

        if (!isset($record['middlename'])) {
            $middlename = rand(0, 59);
            $record['middlename'] = $this->firstnames[$middlename];
        }

        if (!isset($record['alternatename'])) {
            $alternatename = rand(0, 59);
            $record['alternatename'] = $this->firstnames[$alternatename];
        }

        if (!isset($record['idnumber'])) {
            $record['idnumber'] = '';
        }

        if (!isset($record['mnethostid'])) {
            $record['mnethostid'] = $CFG->mnet_localhost_id;
        }

        if (!isset($record['username'])) {
            $record['username'] = 'username'.$i;
            $j = 2;
            while ($DB->record_exists('user', array('username'=>$record['username'], 'mnethostid'=>$record['mnethostid']))) {
                $record['username'] = 'username'.$i.'_'.$j;
                $j++;
            }
        }

        if (isset($record['password'])) {
            $record['password'] = hash_internal_user_password($record['password']);
        } else {
                                    $record['password'] = AUTH_PASSWORD_NOT_CACHED;
        }

        if (!isset($record['email'])) {
            $record['email'] = $record['username'].'@example.com';
        }

        if (!isset($record['confirmed'])) {
            $record['confirmed'] = 1;
        }

        if (!isset($record['lang'])) {
            $record['lang'] = 'en';
        }

        if (!isset($record['maildisplay'])) {
            $record['maildisplay'] = $CFG->defaultpreference_maildisplay;
        }

        if (!isset($record['mailformat'])) {
            $record['mailformat'] = $CFG->defaultpreference_mailformat;
        }

        if (!isset($record['maildigest'])) {
            $record['maildigest'] = $CFG->defaultpreference_maildigest;
        }

        if (!isset($record['autosubscribe'])) {
            $record['autosubscribe'] = $CFG->defaultpreference_autosubscribe;
        }

        if (!isset($record['trackforums'])) {
            $record['trackforums'] = $CFG->defaultpreference_trackforums;
        }

        if (!isset($record['deleted'])) {
            $record['deleted'] = 0;
        }

        if (!isset($record['timecreated'])) {
            $record['timecreated'] = time();
        }

        $record['timemodified'] = $record['timecreated'];
        $record['lastip'] = '0.0.0.0';

        if ($record['deleted']) {
            $delname = $record['email'].'.'.time();
            while ($DB->record_exists('user', array('username'=>$delname))) {
                $delname++;
            }
            $record['idnumber'] = '';
            $record['email']    = md5($record['username']);
            $record['username'] = $delname;
            $record['picture']  = 0;
        }

        $userid = $DB->insert_record('user', $record);

        if (!$record['deleted']) {
            context_user::instance($userid);
        }

        $user = $DB->get_record('user', array('id' => $userid), '*', MUST_EXIST);

        if (!$record['deleted'] && isset($record['interests'])) {
            require_once($CFG->dirroot . '/user/editlib.php');
            if (!is_array($record['interests'])) {
                $record['interests'] = preg_split('/\s*,\s*/', trim($record['interests']), -1, PREG_SPLIT_NO_EMPTY);
            }
            useredit_update_interests($user, $record['interests']);
        }

        return $user;
    }

    
    public function create_category($record=null, array $options=null) {
        global $DB, $CFG;
        require_once("$CFG->libdir/coursecatlib.php");

        $this->categorycount++;
        $i = $this->categorycount;

        $record = (array)$record;

        if (!isset($record['name'])) {
            $record['name'] = 'Course category '.$i;
        }

        if (!isset($record['description'])) {
            $record['description'] = "Test course category $i\n$this->loremipsum";
        }

        if (!isset($record['idnumber'])) {
            $record['idnumber'] = '';
        }

        return coursecat::create($record);
    }

    
    public function create_cohort($record=null, array $options=null) {
        global $DB, $CFG;
        require_once("$CFG->dirroot/cohort/lib.php");

        $this->cohortcount++;
        $i = $this->cohortcount;

        $record = (array)$record;

        if (!isset($record['contextid'])) {
            $record['contextid'] = context_system::instance()->id;
        }

        if (!isset($record['name'])) {
            $record['name'] = 'Cohort '.$i;
        }

        if (!isset($record['idnumber'])) {
            $record['idnumber'] = '';
        }

        if (!isset($record['description'])) {
            $record['description'] = "Test cohort $i\n$this->loremipsum";
        }

        if (!isset($record['descriptionformat'])) {
            $record['descriptionformat'] = FORMAT_MOODLE;
        }

        if (!isset($record['visible'])) {
            $record['visible'] = 1;
        }

        if (!isset($record['component'])) {
            $record['component'] = '';
        }

        $id = cohort_add_cohort((object)$record);

        return $DB->get_record('cohort', array('id'=>$id), '*', MUST_EXIST);
    }

    
    public function create_course($record=null, array $options=null) {
        global $DB, $CFG;
        require_once("$CFG->dirroot/course/lib.php");

        $this->coursecount++;
        $i = $this->coursecount;

        $record = (array)$record;

        if (!isset($record['fullname'])) {
            $record['fullname'] = 'Test course '.$i;
        }

        if (!isset($record['shortname'])) {
            $record['shortname'] = 'tc_'.$i;
        }

        if (!isset($record['idnumber'])) {
            $record['idnumber'] = '';
        }

        if (!isset($record['format'])) {
            $record['format'] = 'topics';
        }

        if (!isset($record['newsitems'])) {
            $record['newsitems'] = 0;
        }

        if (!isset($record['numsections'])) {
            $record['numsections'] = 5;
        }

        if (!isset($record['summary'])) {
            $record['summary'] = "Test course $i\n$this->loremipsum";
        }

        if (!isset($record['summaryformat'])) {
            $record['summaryformat'] = FORMAT_MOODLE;
        }

        if (!isset($record['category'])) {
            $record['category'] = $DB->get_field_select('course_categories', "MIN(id)", "parent=0");
        }

        if (isset($record['tags']) && !is_array($record['tags'])) {
            $record['tags'] = preg_split('/\s*,\s*/', trim($record['tags']), -1, PREG_SPLIT_NO_EMPTY);
        }

        $course = create_course((object)$record);
        context_course::instance($course->id);
        if (!empty($options['createsections'])) {
            if (isset($course->numsections)) {
                course_create_sections_if_missing($course, range(0, $course->numsections));
            } else {
                course_create_sections_if_missing($course, 0);
            }
        }

        return $course;
    }

    
    public function create_course_section($record = null, array $options = null) {
        global $DB;

        $record = (array)$record;

        if (empty($record['course'])) {
            throw new coding_exception('course must be present in testing_data_generator::create_course_section() $record');
        }

        if (!isset($record['section'])) {
            throw new coding_exception('section must be present in testing_data_generator::create_course_section() $record');
        }

        course_create_sections_if_missing($record['course'], $record['section']);
        return get_fast_modinfo($record['course'])->get_section_info($record['section']);
    }

    
    public function create_block($blockname, $record=null, array $options=array()) {
        $generator = $this->get_plugin_generator('block_'.$blockname);
        return $generator->create_instance($record, $options);
    }

    
    public function create_module($modulename, $record=null, array $options=null) {
        $generator = $this->get_plugin_generator('mod_'.$modulename);
        return $generator->create_instance($record, $options);
    }

    
    public function create_group($record) {
        global $DB, $CFG;

        require_once($CFG->dirroot . '/group/lib.php');

        $this->groupcount++;
        $i = $this->groupcount;

        $record = (array)$record;

        if (empty($record['courseid'])) {
            throw new coding_exception('courseid must be present in testing_data_generator::create_group() $record');
        }

        if (!isset($record['name'])) {
            $record['name'] = 'group-' . $i;
        }

        if (!isset($record['description'])) {
            $record['description'] = "Test Group $i\n{$this->loremipsum}";
        }

        if (!isset($record['descriptionformat'])) {
            $record['descriptionformat'] = FORMAT_MOODLE;
        }

        $id = groups_create_group((object)$record);

        return $DB->get_record('groups', array('id'=>$id));
    }

    
    public function create_group_member($record) {
        global $DB, $CFG;

        require_once($CFG->dirroot . '/group/lib.php');

        $record = (array)$record;

        if (empty($record['userid'])) {
            throw new coding_exception('user must be present in testing_util::create_group_member() $record');
        }

        if (!isset($record['groupid'])) {
            throw new coding_exception('group must be present in testing_util::create_group_member() $record');
        }

        if (!isset($record['component'])) {
            $record['component'] = null;
        }
        if (!isset($record['itemid'])) {
            $record['itemid'] = 0;
        }

        return groups_add_member($record['groupid'], $record['userid'], $record['component'], $record['itemid']);
    }

    
    public function create_grouping($record) {
        global $DB, $CFG;

        require_once($CFG->dirroot . '/group/lib.php');

        $this->groupingcount++;
        $i = $this->groupingcount;

        $record = (array)$record;

        if (empty($record['courseid'])) {
            throw new coding_exception('courseid must be present in testing_data_generator::create_grouping() $record');
        }

        if (!isset($record['name'])) {
            $record['name'] = 'grouping-' . $i;
        }

        if (!isset($record['description'])) {
            $record['description'] = "Test Grouping $i\n{$this->loremipsum}";
        }

        if (!isset($record['descriptionformat'])) {
            $record['descriptionformat'] = FORMAT_MOODLE;
        }

        $id = groups_create_grouping((object)$record);

        return $DB->get_record('groupings', array('id'=>$id));
    }

    
    public function create_grouping_group($record) {
        global $DB, $CFG;

        require_once($CFG->dirroot . '/group/lib.php');

        $record = (array)$record;

        if (empty($record['groupingid'])) {
            throw new coding_exception('grouping must be present in testing::create_grouping_group() $record');
        }

        if (!isset($record['groupid'])) {
            throw new coding_exception('group must be present in testing_util::create_grouping_group() $record');
        }

        return groups_assign_grouping($record['groupingid'], $record['groupid']);
    }

    
    public function create_repository($type, $record=null, array $options = null) {
        $generator = $this->get_plugin_generator('repository_'.$type);
        return $generator->create_instance($record, $options);
    }

    
    public function create_repository_type($type, $record=null, array $options = null) {
        $generator = $this->get_plugin_generator('repository_'.$type);
        return $generator->create_type($record, $options);
    }


    
    public function create_scale($record=null, array $options=null) {
        global $DB;

        $this->scalecount++;
        $i = $this->scalecount;

        $record = (array)$record;

        if (!isset($record['name'])) {
            $record['name'] = 'Test scale '.$i;
        }

        if (!isset($record['scale'])) {
            $record['scale'] = 'A,B,C,D,F';
        }

        if (!isset($record['courseid'])) {
            $record['courseid'] = 0;
        }

        if (!isset($record['userid'])) {
            $record['userid'] = 0;
        }

        if (!isset($record['description'])) {
            $record['description'] = 'Test scale description '.$i;
        }

        if (!isset($record['descriptionformat'])) {
            $record['descriptionformat'] = FORMAT_MOODLE;
        }

        $record['timemodified'] = time();

        if (isset($record['id'])) {
            $DB->import_record('scale', $record);
            $DB->get_manager()->reset_sequence('scale');
            $id = $record['id'];
        } else {
            $id = $DB->insert_record('scale', $record);
        }

        return $DB->get_record('scale', array('id'=>$id), '*', MUST_EXIST);
    }

    
    public function create_role($record=null) {
        global $DB;

        $this->rolecount++;
        $i = $this->rolecount;

        $record = (array)$record;

        if (empty($record['shortname'])) {
            $record['shortname'] = 'role-' . $i;
        }

        if (empty($record['name'])) {
            $record['name'] = 'Test role ' . $i;
        }

        if (empty($record['description'])) {
            $record['description'] = 'Test role ' . $i . ' description';
        }

        if (empty($record['archetype'])) {
            $record['archetype'] = '';
        } else {
            $archetypes = get_role_archetypes();
            if (empty($archetypes[$record['archetype']])) {
                throw new coding_exception('\'role\' requires the field \'archetype\' to specify a ' .
                    'valid archetype shortname (editingteacher, student...)');
            }
        }

                if (!$newroleid = create_role($record['name'], $record['shortname'], $record['description'], $record['archetype'])) {
            throw new coding_exception('There was an error creating \'' . $record['shortname'] . '\' role');
        }

                        if (!$record['archetype']) {
            $contextlevels = array_keys(context_helper::get_all_levels());
        } else {
                        $archetyperoleid = $DB->get_field(
                'role',
                'id',
                array('shortname' => $record['archetype'], 'archetype' => $record['archetype'])
            );
            $contextlevels = get_role_contextlevels($archetyperoleid);
        }
        set_role_contextlevels($newroleid, $contextlevels);

        if ($record['archetype']) {

                        if ($record['archetype']) {
                $types = array('assign', 'override', 'switch');
                foreach ($types as $type) {
                    $rolestocopy = get_default_role_archetype_allows($type, $record['archetype']);
                    foreach ($rolestocopy as $tocopy) {
                        $functionname = 'allow_' . $type;
                        $functionname($newroleid, $tocopy);
                    }
                }
            }

                        $sourcerole = $DB->get_record('role', array('id' => $archetyperoleid));
            role_cap_duplicate($sourcerole, $newroleid);
        }

        return $newroleid;
    }

    
    public function create_tag($record = null) {
        global $DB, $USER;

        $this->tagcount++;
        $i = $this->tagcount;

        $record = (array) $record;

        if (!isset($record['userid'])) {
            $record['userid'] = $USER->id;
        }

        if (!isset($record['rawname'])) {
            if (isset($record['name'])) {
                $record['rawname'] = $record['name'];
            } else {
                $record['rawname'] = 'Tag name ' . $i;
            }
        }

                if (!isset($record['name'])) {
            $record['name'] = core_text::strtolower($record['rawname']);
        } else {
            $record['name'] = core_text::strtolower($record['name']);
        }

        if (!isset($record['tagcollid'])) {
            $record['tagcollid'] = core_tag_collection::get_default();
        }

        if (!isset($record['description'])) {
            $record['description'] = 'Tag description';
        }

        if (!isset($record['descriptionformat'])) {
            $record['descriptionformat'] = FORMAT_MOODLE;
        }

        if (!isset($record['flag'])) {
            $record['flag'] = 0;
        }

        if (!isset($record['timemodified'])) {
            $record['timemodified'] = time();
        }

        $id = $DB->insert_record('tag', $record);

        return $DB->get_record('tag', array('id' => $id), '*', MUST_EXIST);
    }

    
    public function combine_defaults_and_record(array $defaults, $record) {
        $record = (array) $record;

        foreach ($defaults as $key => $defaults) {
            if (!array_key_exists($key, $record)) {
                $record[$key] = $defaults;
            }
        }
        return $record;
    }

    
    public function enrol_user($userid, $courseid, $roleidorshortname = null, $enrol = 'manual',
            $timestart = 0, $timeend = 0, $status = null) {
        global $DB;

                if (!is_numeric($roleidorshortname) && is_string($roleidorshortname)) {
            $roleid = $DB->get_field('role', 'id', array('shortname' => $roleidorshortname), MUST_EXIST);
        } else {
            $roleid = $roleidorshortname;
        }

        if (!$plugin = enrol_get_plugin($enrol)) {
            return false;
        }

        $instances = $DB->get_records('enrol', array('courseid'=>$courseid, 'enrol'=>$enrol));
        if (count($instances) != 1) {
            return false;
        }
        $instance = reset($instances);

        if (is_null($roleid) and $instance->roleid) {
            $roleid = $instance->roleid;
        }

        $plugin->enrol_user($instance, $userid, $roleid, $timestart, $timeend, $status);
        return true;
    }

    
    public function role_assign($roleid, $userid, $contextid = false) {

                if (!$contextid) {
            $context = context_system::instance();
            $contextid = $context->id;
        }

        if (empty($roleid)) {
            throw new coding_exception('roleid must be present in testing_data_generator::role_assign() arguments');
        }

        if (empty($userid)) {
            throw new coding_exception('userid must be present in testing_data_generator::role_assign() arguments');
        }

        return role_assign($roleid, $userid, $contextid);
    }

    
    public function create_grade_category($record = null) {
        global $CFG;

        $this->gradecategorycounter++;

        $record = (array)$record;

        if (empty($record['courseid'])) {
            throw new coding_exception('courseid must be present in testing::create_grade_category() $record');
        }

        if (!isset($record['fullname'])) {
            $record['fullname'] = 'Grade category ' . $this->gradecategorycounter;
        }

                require_once($CFG->libdir . '/gradelib.php');
                $gradecategory = new grade_category(array('courseid' => $record['courseid']), false);
        $gradecategory->apply_default_settings();
        grade_category::set_properties($gradecategory, $record);
        $gradecategory->apply_forced_settings();
        $gradecategory->insert();

                $gradeitem = $gradecategory->load_grade_item();

        $gradecategory->update_from_db();
        return $gradecategory->get_record_data();
    }

    
    public function create_grade_item($record = null) {
        global $CFG;
        require_once("$CFG->libdir/gradelib.php");

        $this->gradeitemcounter++;

        if (!isset($record['itemtype'])) {
            $record['itemtype'] = 'manual';
        }

        if (!isset($record['itemname'])) {
            $record['itemname'] = 'Grade item ' . $this->gradeitemcounter;
        }

        if (isset($record['outcomeid'])) {
            $outcome = new grade_outcome(array('id' => $record['outcomeid']));
            $record['scaleid'] = $outcome->scaleid;
        }
        if (isset($record['scaleid'])) {
            $record['gradetype'] = GRADE_TYPE_SCALE;
        } else if (!isset($record['gradetype'])) {
            $record['gradetype'] = GRADE_TYPE_VALUE;
        }

                $gradeitem = new grade_item($record, false);
        $gradeitem->insert();

        $gradeitem->update_from_db();
        return $gradeitem->get_record_data();
    }

    
    public function create_grade_outcome($record = null) {
        global $CFG;

        $this->gradeoutcomecounter++;
        $i = $this->gradeoutcomecounter;

        if (!isset($record['fullname'])) {
            $record['fullname'] = 'Grade outcome ' . $i;
        }

                require_once($CFG->libdir . '/gradelib.php');
                $gradeoutcome = new grade_outcome($record, false);
        $gradeoutcome->insert();

        $gradeoutcome->update_from_db();
        return $gradeoutcome->get_record_data();
    }
}
