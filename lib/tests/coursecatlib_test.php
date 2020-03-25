<?php



defined('MOODLE_INTERNAL') || die();

global $CFG;
require_once($CFG->libdir . '/coursecatlib.php');


class core_coursecatlib_testcase extends advanced_testcase {

    protected $roles;

    protected function setUp() {
        parent::setUp();
        $this->resetAfterTest();
        $user = $this->getDataGenerator()->create_user();
        $this->setUser($user);
    }

    protected function get_roleid($context = null) {
        global $USER;
        if ($context === null) {
            $context = context_system::instance();
        }
        if (is_object($context)) {
            $context = $context->id;
        }
        if (empty($this->roles)) {
            $this->roles = array();
        }
        if (empty($this->roles[$USER->id])) {
            $this->roles[$USER->id] = array();
        }
        if (empty($this->roles[$USER->id][$context])) {
            $this->roles[$USER->id][$context] = create_role('Role for '.$USER->id.' in '.$context, 'role'.$USER->id.'-'.$context, '-');
            role_assign($this->roles[$USER->id][$context], $USER->id, $context);
        }
        return $this->roles[$USER->id][$context];
    }

    protected function assign_capability($capability, $permission = CAP_ALLOW, $contextid = null) {
        if ($contextid === null) {
            $contextid = context_system::instance();
        }
        if (is_object($contextid)) {
            $contextid = $contextid->id;
        }
        assign_capability($capability, $permission, $this->get_roleid($contextid), $contextid, true);
        accesslib_clear_all_caches_for_unit_testing();
    }

    public function test_create_coursecat() {
                $data = new stdClass();
        $data->name = 'aaa';
        $data->description = 'aaa';
        $data->idnumber = '';

        $category1 = coursecat::create($data);

                $this->assertSame($data->name, $category1->name);
        $this->assertSame($data->description, $category1->description);
        $this->assertSame($data->idnumber, $category1->idnumber);

        $this->assertGreaterThanOrEqual(1, $category1->sortorder);

                $data->name = 'ccc';
        $category2 = coursecat::create($data);

        $data->name = 'bbb';
        $category3 = coursecat::create($data);

        $this->assertGreaterThan($category1->sortorder, $category2->sortorder);
        $this->assertGreaterThan($category2->sortorder, $category3->sortorder);
    }

    public function test_name_idnumber_exceptions() {
        try {
            coursecat::create(array('name' => ''));
            $this->fail('Missing category name exception expected in coursecat::create');
        } catch (moodle_exception $e) {
            $this->assertInstanceOf('moodle_exception', $e);
        }
        $cat1 = coursecat::create(array('name' => 'Cat1', 'idnumber' => '1'));
        try {
            $cat1->update(array('name' => ''));
            $this->fail('Missing category name exception expected in coursecat::update');
        } catch (moodle_exception $e) {
            $this->assertInstanceOf('moodle_exception', $e);
        }
        try {
            coursecat::create(array('name' => 'Cat2', 'idnumber' => '1'));
            $this->fail('Duplicate idnumber exception expected in coursecat::create');
        } catch (moodle_exception $e) {
            $this->assertInstanceOf('moodle_exception', $e);
        }
        $cat2 = coursecat::create(array('name' => 'Cat2', 'idnumber' => '2'));
        try {
            $cat2->update(array('idnumber' => '1'));
            $this->fail('Duplicate idnumber exception expected in coursecat::update');
        } catch (moodle_exception $e) {
            $this->assertInstanceOf('moodle_exception', $e);
        }
                coursecat::create(array('name' => 'Cat3', 'idnumber' => '0'));
        try {
            coursecat::create(array('name' => 'Cat4', 'idnumber' => '0'));
            $this->fail('Duplicate idnumber "0" exception expected in coursecat::create');
        } catch (moodle_exception $e) {
            $this->assertInstanceOf('moodle_exception', $e);
        }
                try {
            $cat2->update(array('idnumber' => '0'));
            $this->fail('Duplicate idnumber "0" exception expected in coursecat::update');
        } catch (Exception $e) {
            $this->assertInstanceOf('moodle_exception', $e);
        }
    }

    public function test_visibility() {
        $this->assign_capability('moodle/category:viewhiddencategories');
        $this->assign_capability('moodle/category:manage');

                $category1 = coursecat::create(array('name' => 'Cat1', 'visible' => 0));
        $this->assertEquals(0, $category1->visible);
        $this->assertEquals(0, $category1->visibleold);

                $category2 = coursecat::create(array('name' => 'Cat2', 'visible' => 0, 'parent' => $category1->id));
        $this->assertEquals(0, $category2->visible);
        $this->assertEquals(0, $category2->visibleold);

                $category3 = coursecat::create(array('name' => 'Cat3', 'visible' => 1, 'parent' => $category1->id));
        $this->assertEquals(0, $category3->visible);
        $this->assertEquals(1, $category3->visibleold);

                $category1->show();
        $this->assertEquals(1, coursecat::get($category1->id)->visible);
        $this->assertEquals(0, coursecat::get($category2->id)->visible);
        $this->assertEquals(1, coursecat::get($category3->id)->visible);

                $category4 = coursecat::create(array('name' => 'Cat4'));
        $this->assertEquals(1, $category4->visible);
        $this->assertEquals(1, $category4->visibleold);

                $category5 = coursecat::create(array('name' => 'Cat5', 'parent' => $category4->id));
        $this->assertEquals(1, $category5->visible);
        $this->assertEquals(1, $category5->visibleold);

                $category4->hide();
        $this->assertEquals(0, $category4->visible);
        $this->assertEquals(0, $category4->visibleold);
        $category5 = coursecat::get($category5->id);         $this->assertEquals(0, $category5->visible);
        $this->assertEquals(1, $category5->visibleold);

                $category4->show();
        $this->assertEquals(1, $category4->visible);
        $this->assertEquals(1, $category4->visibleold);
        $category5 = coursecat::get($category5->id);         $this->assertEquals(1, $category5->visible);
        $this->assertEquals(1, $category5->visibleold);

                $category5->change_parent($category2->id);
        $this->assertEquals(0, $category5->visible);
        $this->assertEquals(1, $category5->visibleold);

                $category5 = coursecat::get($category5->id);
        $this->assertEquals(0, $category5->visible);
        $this->assertEquals(1, $category5->visibleold);

                        $category5->change_parent(0);
        $this->assertEquals(0, $category5->visible);
        $this->assertEquals(1, $category5->visibleold);
    }

    public function test_hierarchy() {
        $this->assign_capability('moodle/category:viewhiddencategories');
        $this->assign_capability('moodle/category:manage');

        $category1 = coursecat::create(array('name' => 'Cat1'));
        $category2 = coursecat::create(array('name' => 'Cat2', 'parent' => $category1->id));
        $category3 = coursecat::create(array('name' => 'Cat3', 'parent' => $category1->id));
        $category4 = coursecat::create(array('name' => 'Cat4', 'parent' => $category2->id));

                $this->assertEquals(array($category2->id, $category3->id), array_keys($category1->get_children()));
                $this->assertEquals(array($category1->id, $category2->id), $category4->get_parents());

                $this->assertFalse($category1->can_change_parent($category2->id));
        $this->assertFalse($category2->can_change_parent($category2->id));
                $this->assertTrue($category4->can_change_parent($category1->id));

        try {
            $category2->change_parent($category4->id);
            $this->fail('Exception expected - can not move category');
        } catch (moodle_exception $e) {
            $this->assertInstanceOf('moodle_exception', $e);
        }

        $category4->change_parent(0);
        $this->assertEquals(array(), $category4->get_parents());
        $this->assertEquals(array($category2->id, $category3->id), array_keys($category1->get_children()));
        $this->assertEquals(array(), array_keys($category2->get_children()));
    }

    public function test_update() {
        $category1 = coursecat::create(array('name' => 'Cat1'));
        $timecreated = $category1->timemodified;
        $this->assertSame('Cat1', $category1->name);
        $this->assertTrue(empty($category1->description));
        $this->waitForSecond();
        $testdescription = 'This is cat 1 а также русский текст';
        $category1->update(array('description' => $testdescription));
        $this->assertSame($testdescription, $category1->description);
        $category1 = coursecat::get($category1->id);
        $this->assertSame($testdescription, $category1->description);
        cache_helper::purge_by_event('changesincoursecat');
        $category1 = coursecat::get($category1->id);
        $this->assertSame($testdescription, $category1->description);

        $this->assertGreaterThan($timecreated, $category1->timemodified);
    }

    public function test_delete() {
        global $DB;

        $this->assign_capability('moodle/category:manage');
        $this->assign_capability('moodle/course:create');

        $initialcatid = $DB->get_field_sql('SELECT max(id) from {course_categories}');

        $category1 = coursecat::create(array('name' => 'Cat1'));
        $category2 = coursecat::create(array('name' => 'Cat2', 'parent' => $category1->id));
        $category3 = coursecat::create(array('name' => 'Cat3'));
        $category4 = coursecat::create(array('name' => 'Cat4', 'parent' => $category2->id));

        $course1 = $this->getDataGenerator()->create_course(array('category' => $category2->id));
        $course2 = $this->getDataGenerator()->create_course(array('category' => $category4->id));
        $course3 = $this->getDataGenerator()->create_course(array('category' => $category4->id));
        $course4 = $this->getDataGenerator()->create_course(array('category' => $category1->id));

                                                                                
                $this->setUser($this->getDataGenerator()->create_user());

                $this->assertFalse($category2->can_move_content_to($category3->id));                 $this->assign_capability('moodle/course:create', CAP_ALLOW, context_coursecat::instance($category3->id));
        $this->assign_capability('moodle/category:manage');
        $this->assertTrue($category2->can_move_content_to($category3->id));         $category2->delete_move($category3->id);

                                                                        
        $this->assertNull(coursecat::get($category2->id, IGNORE_MISSING, true));
        $this->assertEquals(array(), $category1->get_children());
        $this->assertEquals(array($category4->id), array_keys($category3->get_children()));
        $this->assertEquals($category4->id, $DB->get_field('course', 'category', array('id' => $course2->id)));
        $this->assertEquals($category4->id, $DB->get_field('course', 'category', array('id' => $course3->id)));
        $this->assertEquals($category3->id, $DB->get_field('course', 'category', array('id' => $course1->id)));

                $this->assertFalse($category3->can_delete_full());                 $this->assign_capability('moodle/course:delete', CAP_ALLOW, context_coursecat::instance($category3->id));
        $this->assertTrue($category3->can_delete_full());         $category3->delete_full();

                                
                $this->assertEquals(1, $DB->get_field_sql('SELECT count(*) FROM {course_categories} WHERE id > ?', array($initialcatid)));
        $this->assertEquals($category1->id, $DB->get_field_sql('SELECT max(id) FROM {course_categories}'));
        $this->assertEquals(1, $DB->get_field_sql('SELECT count(*) FROM {course} WHERE id <> ?', array(SITEID)));
        $this->assertEquals(array('id' => $course4->id, 'category' => $category1->id),
                (array)$DB->get_record_sql('SELECT id, category from {course} where id <> ?', array(SITEID)));
    }

    public function test_get_children() {
        $category1 = coursecat::create(array('name' => 'Cat1'));
        $category2 = coursecat::create(array('name' => 'Cat2', 'parent' => $category1->id));
        $category3 = coursecat::create(array('name' => 'Cat3', 'parent' => $category1->id, 'visible' => 0));
        $category4 = coursecat::create(array('name' => 'Cat4', 'idnumber' => '12', 'parent' => $category1->id));
        $category5 = coursecat::create(array('name' => 'Cat5', 'idnumber' => '11', 'parent' => $category1->id, 'visible' => 0));
        $category6 = coursecat::create(array('name' => 'Cat6', 'idnumber' => '10', 'parent' => $category1->id));
        $category7 = coursecat::create(array('name' => 'Cat0', 'parent' => $category1->id));

        $children = $category1->get_children();
                        $this->assertEquals(array($category2->id, $category4->id, $category6->id, $category7->id), array_keys($children));
        $this->assertEquals(4, $category1->get_children_count());

        $children = $category1->get_children(array('offset' => 2));
        $this->assertEquals(array($category6->id, $category7->id), array_keys($children));
        $this->assertEquals(4, $category1->get_children_count());

        $children = $category1->get_children(array('limit' => 2));
        $this->assertEquals(array($category2->id, $category4->id), array_keys($children));

        $children = $category1->get_children(array('offset' => 1, 'limit' => 2));
        $this->assertEquals(array($category4->id, $category6->id), array_keys($children));

        $children = $category1->get_children(array('sort' => array('name' => 1)));
                $this->assertEquals(array($category7->id, $category2->id, $category4->id, $category6->id), array_keys($children));

        $children = $category1->get_children(array('sort' => array('idnumber' => 1, 'name' => -1)));
                $this->assertEquals(array($category2->id, $category7->id, $category6->id, $category4->id), array_keys($children));

                cache_helper::purge_by_event('changesincoursecat');
        $children = $category1->get_children();
        $this->assertEquals(array($category2->id, $category4->id, $category6->id, $category7->id), array_keys($children));
        $this->assertEquals(4, $category1->get_children_count());
    }

    
    public function test_count_all() {
        global $DB;
                $numcategories = $DB->count_records('course_categories');
        $this->assertEquals($numcategories, coursecat::count_all());
        $category1 = coursecat::create(array('name' => 'Cat1'));
        $category2 = coursecat::create(array('name' => 'Cat2', 'parent' => $category1->id));
        $category3 = coursecat::create(array('name' => 'Cat3', 'parent' => $category2->id, 'visible' => 0));
                $this->assertEquals($numcategories + 3, coursecat::count_all());
        cache_helper::purge_by_event('changesincoursecat');
                $this->assertEquals($numcategories + 3, coursecat::count_all());
    }

    
    public function test_resort_courses() {
        $this->resetAfterTest(true);
        $generator = $this->getDataGenerator();
        $category = $generator->create_category();
        $course1 = $generator->create_course(array(
            'category' => $category->id,
            'idnumber' => '006-01',
            'shortname' => 'Biome Study',
            'fullname' => '<span lang="ar" class="multilang">'.'دراسة منطقة إحيائية'.'</span><span lang="en" class="multilang">Biome Study</span>',
            'timecreated' => '1000000001'
        ));
        $course2 = $generator->create_course(array(
            'category' => $category->id,
            'idnumber' => '007-02',
            'shortname' => 'Chemistry Revision',
            'fullname' => 'Chemistry Revision',
            'timecreated' => '1000000002'
        ));
        $course3 = $generator->create_course(array(
            'category' => $category->id,
            'idnumber' => '007-03',
            'shortname' => 'Swiss Rolls and Sunflowers',
            'fullname' => 'Aarkvarks guide to Swiss Rolls and Sunflowers',
            'timecreated' => '1000000003'
        ));
        $course4 = $generator->create_course(array(
            'category' => $category->id,
            'idnumber' => '006-04',
            'shortname' => 'Scratch',
            'fullname' => '<a href="test.php">Basic Scratch</a>',
            'timecreated' => '1000000004'
        ));
        $c1 = (int)$course1->id;
        $c2 = (int)$course2->id;
        $c3 = (int)$course3->id;
        $c4 = (int)$course4->id;

        $coursecat = coursecat::get($category->id);
        $this->assertTrue($coursecat->resort_courses('idnumber'));
        $this->assertSame(array($c1, $c4, $c2, $c3), array_keys($coursecat->get_courses()));

        $this->assertTrue($coursecat->resort_courses('shortname'));
        $this->assertSame(array($c1, $c2, $c4, $c3), array_keys($coursecat->get_courses()));

        $this->assertTrue($coursecat->resort_courses('timecreated'));
        $this->assertSame(array($c1, $c2, $c3, $c4), array_keys($coursecat->get_courses()));

        try {
                        filter_set_global_state('multilang', TEXTFILTER_ON);
            filter_set_applies_to_strings('multilang', true);
            $expected = array($c3, $c4, $c1, $c2);
        } catch (coding_exception $ex) {
            $expected = array($c3, $c4, $c2, $c1);
        }
        $this->assertTrue($coursecat->resort_courses('fullname'));
        $this->assertSame($expected, array_keys($coursecat->get_courses()));
    }

    public function test_get_search_courses() {
        $cat1 = coursecat::create(array('name' => 'Cat1'));
        $cat2 = coursecat::create(array('name' => 'Cat2', 'parent' => $cat1->id));
        $c1 = $this->getDataGenerator()->create_course(array('category' => $cat1->id, 'fullname' => 'Test 3', 'summary' => ' ', 'idnumber' => 'ID3'));
        $c2 = $this->getDataGenerator()->create_course(array('category' => $cat1->id, 'fullname' => 'Test 1', 'summary' => ' ', 'visible' => 0));
        $c3 = $this->getDataGenerator()->create_course(array('category' => $cat1->id, 'fullname' => 'Математика', 'summary' => ' Test '));
        $c4 = $this->getDataGenerator()->create_course(array('category' => $cat1->id, 'fullname' => 'Test 4', 'summary' => ' ', 'idnumber' => 'ID4'));

        $c5 = $this->getDataGenerator()->create_course(array('category' => $cat2->id, 'fullname' => 'Test 5', 'summary' => ' '));
        $c6 = $this->getDataGenerator()->create_course(array('category' => $cat2->id, 'fullname' => 'Дискретная Математика', 'summary' => ' '));
        $c7 = $this->getDataGenerator()->create_course(array('category' => $cat2->id, 'fullname' => 'Test 7', 'summary' => ' ', 'visible' => 0));
        $c8 = $this->getDataGenerator()->create_course(array('category' => $cat2->id, 'fullname' => 'Test 8', 'summary' => ' '));

                $res = $cat1->get_courses(array('sortorder' => 1));
        $this->assertEquals(array($c4->id, $c3->id, $c1->id), array_keys($res));         $this->assertEquals(3, $cat1->get_courses_count());

                $res = $cat1->get_courses(array('recursive' => 1));
        $this->assertEquals(array($c4->id, $c3->id, $c1->id, $c8->id, $c6->id, $c5->id), array_keys($res));
        $this->assertEquals(6, $cat1->get_courses_count(array('recursive' => 1)));

                $res = $cat1->get_courses(array('sort' => array('fullname' => 1)));
        $this->assertEquals(array($c1->id, $c4->id, $c3->id), array_keys($res));
        $this->assertEquals(3, $cat1->get_courses_count(array('sort' => array('fullname' => 1))));

                $res = $cat1->get_courses(array('recursive' => 1, 'sort' => array('fullname' => 1)));
        $this->assertEquals(array($c1->id, $c4->id, $c5->id, $c8->id, $c6->id, $c3->id), array_keys($res));
        $this->assertEquals(6, $cat1->get_courses_count(array('recursive' => 1, 'sort' => array('fullname' => 1))));

                $res = $cat1->get_courses(array('recursive' => 1, 'offset' => 1, 'limit' => 2, 'sort' => array('fullname' => -1)));
        $this->assertEquals(array($c6->id, $c8->id), array_keys($res));
                $this->assertEquals(6, $cat1->get_courses_count(array('recursive' => 1, 'offset' => 1, 'limit' => 2, 'sort' => array('fullname' => 1))));

                $this->assertEquals(3, $cat2->get_courses_count(array('recursive' => 1, 'sort' => array('idnumber' => 1))));

        
                $res = coursecat::search_courses(array('search' => 'Test'));
        $this->assertEquals(array($c4->id, $c3->id, $c1->id, $c8->id, $c5->id), array_keys($res));
        $this->assertEquals(5, coursecat::search_courses_count(array('search' => 'Test')));

                $options = array('sort' => array('fullname' => 1), 'offset' => 1, 'limit' => 2);
        $res = coursecat::search_courses(array('search' => 'Test'), $options);
        $this->assertEquals(array($c4->id, $c5->id), array_keys($res));
        $this->assertEquals(5, coursecat::search_courses_count(array('search' => 'Test'), $options));

                        $res = coursecat::search_courses(array('search' => 'test'));
        $this->assertEquals(array($c4->id, $c3->id, $c1->id, $c8->id, $c5->id), array_keys($res));
        $this->assertEquals(5, coursecat::search_courses_count(array('search' => 'test')));

                $res = coursecat::search_courses(array('search' => 'Математика'));
        $this->assertEquals(array($c3->id, $c6->id), array_keys($res));
        $this->assertEquals(2, coursecat::search_courses_count(array('search' => 'Математика'), array()));

        $this->setUser($this->getDataGenerator()->create_user());

                $this->assign_capability('moodle/course:create', CAP_ALLOW, context_coursecat::instance($cat2->id));
                $reqcaps = array('moodle/course:create');
        $res = coursecat::search_courses(array('search' => 'test'), array(), $reqcaps);
        $this->assertEquals(array($c8->id, $c5->id), array_keys($res));
        $this->assertEquals(2, coursecat::search_courses_count(array('search' => 'test'), array(), $reqcaps));
    }

    public function test_course_contacts() {
        global $DB, $CFG;
        $teacherrole = $DB->get_record('role', array('shortname'=>'editingteacher'));
        $managerrole = $DB->get_record('role', array('shortname'=>'manager'));
        $studentrole = $DB->get_record('role', array('shortname'=>'student'));
        $oldcoursecontact = $CFG->coursecontact;

        $CFG->coursecontact = $managerrole->id. ','. $teacherrole->id;

        

                                                                                                                                $category = $course = $enrol = $user = array();
        $category[1] = coursecat::create(array('name' => 'Cat1'))->id;
        $category[2] = coursecat::create(array('name' => 'Cat2', 'parent' => $category[1]))->id;
        $category[3] = coursecat::create(array('name' => 'Cat3', 'parent' => $category[1]))->id;
        $category[4] = coursecat::create(array('name' => 'Cat4', 'parent' => $category[2]))->id;
        foreach (array(1, 2, 3, 4) as $catid) {
            foreach (array(1, 2) as $courseid) {
                $course[$catid][$courseid] = $this->getDataGenerator()->create_course(array('idnumber' => 'id'.$catid.$courseid,
                    'category' => $category[$catid]))->id;
                $enrol[$catid][$courseid] = $DB->get_record('enrol', array('courseid'=>$course[$catid][$courseid], 'enrol'=>'manual'), '*', MUST_EXIST);
            }
        }
        foreach (array(1, 2, 3, 4, 5) as $userid) {
            $user[$userid] = $this->getDataGenerator()->create_user(array('firstname' => 'F'.$userid, 'lastname' => 'L'.$userid))->id;
        }

        $manual = enrol_get_plugin('manual');

                $allcourses = coursecat::get(0)->get_courses(array('recursive' => true, 'coursecontacts' => true, 'sort' => array('idnumber' => 1)));
        foreach ($allcourses as $onecourse) {
            $this->assertEmpty($onecourse->get_course_contacts());
        }

                role_assign($teacherrole->id, $user[2], context_coursecat::instance($category[1]));
                $manual->enrol_user($enrol[2][1], $user[2], $managerrole->id);
                $manual->enrol_user($enrol[2][2], $user[2], $studentrole->id);
                role_assign($managerrole->id, $user[2], context_coursecat::instance($category[4]));
                $manual->enrol_user($enrol[4][1], $user[4], $teacherrole->id);
        $manual->enrol_user($enrol[4][1], $user[5], $managerrole->id);
                $manual->enrol_user($enrol[4][2], $user[2], $teacherrole->id);
                role_assign($managerrole->id, $user[3], context_coursecat::instance($category[3]));
                $manual->enrol_user($enrol[3][1], $user[3], $studentrole->id);
                $manual->enrol_user($enrol[1][1], $user[1], $teacherrole->id);
                        role_assign($teacherrole->id, $user[1], context_course::instance($course[1][2]));
        $manual->enrol_user($enrol[1][2], $user[4], $teacherrole->id, 0, 0, ENROL_USER_SUSPENDED);

        $allcourses = coursecat::get(0)->get_courses(array('recursive' => true, 'coursecontacts' => true, 'sort' => array('idnumber' => 1)));
                $contacts = array();
        foreach (array(1, 2, 3, 4) as $catid) {
            foreach (array(1, 2) as $courseid) {
                $tmp = array();
                foreach ($allcourses[$course[$catid][$courseid]]->get_course_contacts() as $contact) {
                    $tmp[] = $contact['rolename']. ': '. $contact['username'];
                }
                $contacts[$catid][$courseid] = join(', ', $tmp);
            }
        }

                        $this->assertSame('Manager: F2 L2', $contacts[2][1]);
                $this->assertSame('Teacher: F2 L2', $contacts[2][2]);
                $this->assertSame('Manager: F5 L5, Teacher: F4 L4', $contacts[4][1]);
                $this->assertSame('Manager: F2 L2', $contacts[4][2]);
                $this->assertSame('Manager: F3 L3', $contacts[3][1]);
                $this->assertSame('', $contacts[3][2]);
                $this->assertSame('Teacher: F1 L1', $contacts[1][1]);
                $this->assertSame('', $contacts[1][2]);

                $manual->enrol_user($enrol[4][1], $user[4], $teacherrole->id, 0, 0, ENROL_USER_SUSPENDED);
        $allcourses = coursecat::get(0)->get_courses(array('recursive' => true, 'coursecontacts' => true, 'sort' => array('idnumber' => 1)));
        $contacts = $allcourses[$course[4][1]]->get_course_contacts();
        $this->assertCount(1, $contacts);
        $contact = reset($contacts);
        $this->assertEquals('F5 L5', $contact['username']);

        $CFG->coursecontact = $oldcoursecontact;
    }

    public function test_overview_files() {
        global $CFG;
        $this->setAdminUser();
        $cat1 = coursecat::create(array('name' => 'Cat1'));

                $dratid1 = $this->fill_draft_area(array('filename.jpg' => 'Test file contents1'));
        $c1 = $this->getDataGenerator()->create_course(array('category' => $cat1->id,
            'fullname' => 'Test 1', 'overviewfiles_filemanager' => $dratid1));
                $dratid2 = $this->fill_draft_area(array('filename21.jpg' => 'Test file contents21', 'filename22.jpg' => 'Test file contents22'));
        $c2 = $this->getDataGenerator()->create_course(array('category' => $cat1->id,
            'fullname' => 'Test 2', 'overviewfiles_filemanager' => $dratid2));
                $c3 = $this->getDataGenerator()->create_course(array('category' => $cat1->id, 'fullname' => 'Test 3'));

                $CFG->courseoverviewfileslimit = 3;
        $CFG->courseoverviewfilesext = '*';
                $dratid4 = $this->fill_draft_area(array('filename41.jpg' => 'Test file contents41', 'filename42.jpg' => 'Test file contents42'));
        $c4 = $this->getDataGenerator()->create_course(array('category' => $cat1->id,
            'fullname' => 'Test 4', 'overviewfiles_filemanager' => $dratid4));
                $dratid5 = $this->fill_draft_area(array('filename51.zip' => 'Test file contents51'));
        $c5 = $this->getDataGenerator()->create_course(array('category' => $cat1->id,
            'fullname' => 'Test 5', 'overviewfiles_filemanager' => $dratid5));

                $CFG->courseoverviewfileslimit = 1;
        $CFG->courseoverviewfilesext = '.jpg,.gif,.png';

        $courses = $cat1->get_courses();
        $this->assertTrue($courses[$c1->id]->has_course_overviewfiles());
        $this->assertTrue($courses[$c2->id]->has_course_overviewfiles());
        $this->assertFalse($courses[$c3->id]->has_course_overviewfiles());
        $this->assertTrue($courses[$c4->id]->has_course_overviewfiles());
        $this->assertTrue($courses[$c5->id]->has_course_overviewfiles()); 
        $this->assertEquals(1, count($courses[$c1->id]->get_course_overviewfiles()));
        $this->assertEquals(1, count($courses[$c2->id]->get_course_overviewfiles()));
        $this->assertEquals(0, count($courses[$c3->id]->get_course_overviewfiles()));
        $this->assertEquals(1, count($courses[$c4->id]->get_course_overviewfiles()));
        $this->assertEquals(0, count($courses[$c5->id]->get_course_overviewfiles())); 
                $CFG->courseoverviewfileslimit = 0;

        $this->assertFalse($courses[$c1->id]->has_course_overviewfiles());
        $this->assertFalse($courses[$c2->id]->has_course_overviewfiles());
        $this->assertFalse($courses[$c3->id]->has_course_overviewfiles());
        $this->assertFalse($courses[$c4->id]->has_course_overviewfiles());
        $this->assertFalse($courses[$c5->id]->has_course_overviewfiles());

        $this->assertEquals(0, count($courses[$c1->id]->get_course_overviewfiles()));
        $this->assertEquals(0, count($courses[$c2->id]->get_course_overviewfiles()));
        $this->assertEquals(0, count($courses[$c3->id]->get_course_overviewfiles()));
        $this->assertEquals(0, count($courses[$c4->id]->get_course_overviewfiles()));
        $this->assertEquals(0, count($courses[$c5->id]->get_course_overviewfiles()));

                $CFG->courseoverviewfileslimit = 3;

        $this->assertTrue($courses[$c1->id]->has_course_overviewfiles());
        $this->assertTrue($courses[$c2->id]->has_course_overviewfiles());
        $this->assertFalse($courses[$c3->id]->has_course_overviewfiles());
        $this->assertTrue($courses[$c4->id]->has_course_overviewfiles());
        $this->assertTrue($courses[$c5->id]->has_course_overviewfiles()); 
        $this->assertEquals(1, count($courses[$c1->id]->get_course_overviewfiles()));
        $this->assertEquals(1, count($courses[$c2->id]->get_course_overviewfiles()));         $this->assertEquals(0, count($courses[$c3->id]->get_course_overviewfiles()));
        $this->assertEquals(2, count($courses[$c4->id]->get_course_overviewfiles()));
        $this->assertEquals(0, count($courses[$c5->id]->get_course_overviewfiles()));

                $CFG->courseoverviewfilesext = '*';

        $this->assertTrue($courses[$c1->id]->has_course_overviewfiles());
        $this->assertTrue($courses[$c2->id]->has_course_overviewfiles());
        $this->assertFalse($courses[$c3->id]->has_course_overviewfiles());
        $this->assertTrue($courses[$c4->id]->has_course_overviewfiles());
        $this->assertTrue($courses[$c5->id]->has_course_overviewfiles());

        $this->assertEquals(1, count($courses[$c1->id]->get_course_overviewfiles()));
        $this->assertEquals(1, count($courses[$c2->id]->get_course_overviewfiles()));
        $this->assertEquals(0, count($courses[$c3->id]->get_course_overviewfiles()));
        $this->assertEquals(2, count($courses[$c4->id]->get_course_overviewfiles()));
        $this->assertEquals(1, count($courses[$c5->id]->get_course_overviewfiles()));
    }

    
    protected function fill_draft_area(array $files) {
        global $USER;
        $usercontext = context_user::instance($USER->id);
        $draftid = file_get_unused_draft_itemid();
        foreach ($files as $filename => $filecontents) {
                        $filerecord = array('component' => 'user', 'filearea' => 'draft',
                    'contextid' => $usercontext->id, 'itemid' => $draftid,
                    'filename' => $filename, 'filepath' => '/');
            $fs = get_file_storage();
            $fs->create_file_from_string($filerecord, $filecontents);
        }
        return $draftid;
    }
}
