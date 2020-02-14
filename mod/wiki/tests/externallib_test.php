<?php



defined('MOODLE_INTERNAL') || die();

global $CFG;

require_once($CFG->dirroot . '/webservice/tests/helpers.php');
require_once($CFG->dirroot . '/mod/wiki/lib.php');


class mod_wiki_external_testcase extends externallib_advanced_testcase {

    
    public function setUp() {
        global $DB;
        $this->resetAfterTest();
        $this->setAdminUser();

                $this->course = $this->getDataGenerator()->create_course();
        $this->wiki = $this->getDataGenerator()->create_module('wiki', array('course' => $this->course->id));
        $this->context = context_module::instance($this->wiki->cmid);
        $this->cm = get_coursemodule_from_instance('wiki', $this->wiki->id);

                $this->student = self::getDataGenerator()->create_user();
        $this->student2 = self::getDataGenerator()->create_user();
        $this->teacher = self::getDataGenerator()->create_user();

                $this->studentrole = $DB->get_record('role', array('shortname' => 'student'));
        $this->teacherrole = $DB->get_record('role', array('shortname' => 'editingteacher'));
        $this->getDataGenerator()->enrol_user($this->student->id, $this->course->id, $this->studentrole->id, 'manual');
        $this->getDataGenerator()->enrol_user($this->student2->id, $this->course->id, $this->studentrole->id, 'manual');
        $this->getDataGenerator()->enrol_user($this->teacher->id, $this->course->id, $this->teacherrole->id, 'manual');

                $this->firstpage = $this->getDataGenerator()->get_plugin_generator('mod_wiki')->create_first_page($this->wiki);
    }

    
    private function create_collaborative_wikis_with_groups() {
                if (!isset($this->group1)) {
            $this->group1 = $this->getDataGenerator()->create_group(array('courseid' => $this->course->id));
            $this->getDataGenerator()->create_group_member(array('userid' => $this->student->id, 'groupid' => $this->group1->id));
            $this->getDataGenerator()->create_group_member(array('userid' => $this->student2->id, 'groupid' => $this->group1->id));
        }
        if (!isset($this->group2)) {
            $this->group2 = $this->getDataGenerator()->create_group(array('courseid' => $this->course->id));
        }

                $this->wikisep = $this->getDataGenerator()->create_module('wiki',
                                                        array('course' => $this->course->id, 'groupmode' => SEPARATEGROUPS));
        $this->wikivis = $this->getDataGenerator()->create_module('wiki',
                                                        array('course' => $this->course->id, 'groupmode' => VISIBLEGROUPS));

                $wikigenerator = $this->getDataGenerator()->get_plugin_generator('mod_wiki');
        $this->fpsepg1 = $wikigenerator->create_first_page($this->wikisep, array('group' => $this->group1->id));
        $this->fpsepg2 = $wikigenerator->create_first_page($this->wikisep, array('group' => $this->group2->id));
        $this->fpsepall = $wikigenerator->create_first_page($this->wikisep, array('group' => 0));         $this->fpvisg1 = $wikigenerator->create_first_page($this->wikivis, array('group' => $this->group1->id));
        $this->fpvisg2 = $wikigenerator->create_first_page($this->wikivis, array('group' => $this->group2->id));
        $this->fpvisall = $wikigenerator->create_first_page($this->wikivis, array('group' => 0));     }

    
    private function create_individual_wikis_with_groups() {
                if (!isset($this->group1)) {
            $this->group1 = $this->getDataGenerator()->create_group(array('courseid' => $this->course->id));
            $this->getDataGenerator()->create_group_member(array('userid' => $this->student->id, 'groupid' => $this->group1->id));
            $this->getDataGenerator()->create_group_member(array('userid' => $this->student2->id, 'groupid' => $this->group1->id));
        }
        if (!isset($this->group2)) {
            $this->group2 = $this->getDataGenerator()->create_group(array('courseid' => $this->course->id));
        }

                $this->wikisepind = $this->getDataGenerator()->create_module('wiki', array('course' => $this->course->id,
                                                        'groupmode' => SEPARATEGROUPS, 'wikimode' => 'individual'));
        $this->wikivisind = $this->getDataGenerator()->create_module('wiki', array('course' => $this->course->id,
                                                        'groupmode' => VISIBLEGROUPS, 'wikimode' => 'individual'));

                $wikigenerator = $this->getDataGenerator()->get_plugin_generator('mod_wiki');
        $this->setUser($this->teacher);
        $this->fpsepg1indt = $wikigenerator->create_first_page($this->wikisepind, array('group' => $this->group1->id));
        $this->fpsepg2indt = $wikigenerator->create_first_page($this->wikisepind, array('group' => $this->group2->id));
        $this->fpsepallindt = $wikigenerator->create_first_page($this->wikisepind, array('group' => 0));         $this->fpvisg1indt = $wikigenerator->create_first_page($this->wikivisind, array('group' => $this->group1->id));
        $this->fpvisg2indt = $wikigenerator->create_first_page($this->wikivisind, array('group' => $this->group2->id));
        $this->fpvisallindt = $wikigenerator->create_first_page($this->wikivisind, array('group' => 0)); 
        $this->setUser($this->student);
        $this->fpsepg1indstu = $wikigenerator->create_first_page($this->wikisepind, array('group' => $this->group1->id));
        $this->fpvisg1indstu = $wikigenerator->create_first_page($this->wikivisind, array('group' => $this->group1->id));

        $this->setUser($this->student2);
        $this->fpsepg1indstu2 = $wikigenerator->create_first_page($this->wikisepind, array('group' => $this->group1->id));
        $this->fpvisg1indstu2 = $wikigenerator->create_first_page($this->wikivisind, array('group' => $this->group1->id));

    }

    
    public function test_mod_wiki_get_wikis_by_courses() {

                $course2 = self::getDataGenerator()->create_course();

                $record = new stdClass();
        $record->course = $course2->id;
        $wiki2 = self::getDataGenerator()->create_module('wiki', $record);

                $enrol = enrol_get_plugin('manual');
        $enrolinstances = enrol_get_instances($course2->id, true);
        foreach ($enrolinstances as $courseenrolinstance) {
            if ($courseenrolinstance->enrol == "manual") {
                $instance2 = $courseenrolinstance;
                break;
            }
        }
        $enrol->enrol_user($instance2, $this->student->id, $this->studentrole->id);

        self::setUser($this->student);

        $returndescription = mod_wiki_external::get_wikis_by_courses_returns();

                        $expectedfields = array('id', 'coursemodule', 'course', 'name', 'intro', 'introformat', 'firstpagetitle', 'wikimode',
                                'defaultformat', 'forceformat', 'editbegin', 'editend', 'section', 'visible', 'groupmode',
                                'groupingid');

                $wiki1 = $this->wiki;
        $wiki1->coursemodule = $wiki1->cmid;
        $wiki1->introformat = 1;
        $wiki1->section = 0;
        $wiki1->visible = true;
        $wiki1->groupmode = 0;
        $wiki1->groupingid = 0;

        $wiki2->coursemodule = $wiki2->cmid;
        $wiki2->introformat = 1;
        $wiki2->section = 0;
        $wiki2->visible = true;
        $wiki2->groupmode = 0;
        $wiki2->groupingid = 0;

        foreach ($expectedfields as $field) {
            $expected1[$field] = $wiki1->{$field};
            $expected2[$field] = $wiki2->{$field};
        }
                $expected1['cancreatepages'] = true;
        $expected2['cancreatepages'] = true;

        $expectedwikis = array($expected2, $expected1);

                $result = mod_wiki_external::get_wikis_by_courses(array($course2->id, $this->course->id));
        $result = external_api::clean_returnvalue($returndescription, $result);

        $this->assertEquals($expectedwikis, $result['wikis']);
        $this->assertCount(0, $result['warnings']);

                $result = mod_wiki_external::get_wikis_by_courses();
        $result = external_api::clean_returnvalue($returndescription, $result);
        $this->assertEquals($expectedwikis, $result['wikis']);
        $this->assertCount(0, $result['warnings']);

                $enrol->unenrol_user($instance2, $this->student->id);
        array_shift($expectedwikis);

                $result = mod_wiki_external::get_wikis_by_courses();
        $result = external_api::clean_returnvalue($returndescription, $result);
        $this->assertEquals($expectedwikis, $result['wikis']);

                $result = mod_wiki_external::get_wikis_by_courses(array($course2->id));
        $this->assertCount(1, $result['warnings']);
        $this->assertEquals('1', $result['warnings'][0]['warningcode']);
        $this->assertEquals($course2->id, $result['warnings'][0]['itemid']);

                self::setUser($this->teacher);

        $additionalfields = array('timecreated', 'timemodified');

        foreach ($additionalfields as $field) {
            $expectedwikis[0][$field] = $wiki1->{$field};
        }

        $result = mod_wiki_external::get_wikis_by_courses();
        $result = external_api::clean_returnvalue($returndescription, $result);
        $this->assertEquals($expectedwikis, $result['wikis']);

                self::setAdminUser();

        $result = mod_wiki_external::get_wikis_by_courses(array($this->course->id));
        $result = external_api::clean_returnvalue($returndescription, $result);
        $this->assertEquals($expectedwikis, $result['wikis']);

                $this->setUser($this->student);
        $contextcourse1 = context_course::instance($this->course->id);
                assign_capability('mod/wiki:viewpage', CAP_PROHIBIT, $this->studentrole->id, $contextcourse1->id);
        accesslib_clear_all_caches_for_unit_testing();

        $wikis = mod_wiki_external::get_wikis_by_courses(array($this->course->id));
        $wikis = external_api::clean_returnvalue(mod_wiki_external::get_wikis_by_courses_returns(), $wikis);
        $this->assertFalse(isset($wikis['wikis'][0]['intro']));

                assign_capability('mod/wiki:createpage', CAP_PROHIBIT, $this->studentrole->id, $contextcourse1->id);
        accesslib_clear_all_caches_for_unit_testing();

        $wikis = mod_wiki_external::get_wikis_by_courses(array($this->course->id));
        $wikis = external_api::clean_returnvalue(mod_wiki_external::get_wikis_by_courses_returns(), $wikis);
        $this->assertFalse($wikis['wikis'][0]['cancreatepages']);

    }

    
    public function test_view_wiki() {

                try {
            mod_wiki_external::view_wiki(0);
            $this->fail('Exception expected due to invalid mod_wiki instance id.');
        } catch (moodle_exception $e) {
            $this->assertEquals('incorrectwikiid', $e->errorcode);
        }

                $usernotenrolled = self::getDataGenerator()->create_user();
        $this->setUser($usernotenrolled);
        try {
            mod_wiki_external::view_wiki($this->wiki->id);
            $this->fail('Exception expected due to not enrolled user.');
        } catch (moodle_exception $e) {
            $this->assertEquals('requireloginerror', $e->errorcode);
        }

                $this->setUser($this->student);

                $sink = $this->redirectEvents();

        $result = mod_wiki_external::view_wiki($this->wiki->id);
        $result = external_api::clean_returnvalue(mod_wiki_external::view_wiki_returns(), $result);

        $events = $sink->get_events();
        $this->assertCount(1, $events);
        $event = array_shift($events);

                $this->assertInstanceOf('\mod_wiki\event\course_module_viewed', $event);
        $this->assertEquals($this->context, $event->get_context());
        $moodlewiki = new \moodle_url('/mod/wiki/view.php', array('id' => $this->cm->id));
        $this->assertEquals($moodlewiki, $event->get_url());
        $this->assertEventContextNotUsed($event);
        $this->assertNotEmpty($event->get_name());

                        assign_capability('mod/wiki:viewpage', CAP_PROHIBIT, $this->studentrole->id, $this->context->id);
        accesslib_clear_all_caches_for_unit_testing();

        try {
            mod_wiki_external::view_wiki($this->wiki->id);
            $this->fail('Exception expected due to missing capability.');
        } catch (moodle_exception $e) {
            $this->assertEquals('cannotviewpage', $e->errorcode);
        }

    }

    
    public function test_view_page() {

                try {
            mod_wiki_external::view_page(0);
            $this->fail('Exception expected due to invalid view_page page id.');
        } catch (moodle_exception $e) {
            $this->assertEquals('incorrectpageid', $e->errorcode);
        }

                $usernotenrolled = self::getDataGenerator()->create_user();
        $this->setUser($usernotenrolled);
        try {
            mod_wiki_external::view_page($this->firstpage->id);
            $this->fail('Exception expected due to not enrolled user.');
        } catch (moodle_exception $e) {
            $this->assertEquals('requireloginerror', $e->errorcode);
        }

                $this->setUser($this->student);

                $sink = $this->redirectEvents();

        $result = mod_wiki_external::view_page($this->firstpage->id);
        $result = external_api::clean_returnvalue(mod_wiki_external::view_page_returns(), $result);

        $events = $sink->get_events();
        $this->assertCount(1, $events);
        $event = array_shift($events);

                $this->assertInstanceOf('\mod_wiki\event\page_viewed', $event);
        $this->assertEquals($this->context, $event->get_context());
        $pageurl = new \moodle_url('/mod/wiki/view.php', array('pageid' => $this->firstpage->id));
        $this->assertEquals($pageurl, $event->get_url());
        $this->assertEventContextNotUsed($event);
        $this->assertNotEmpty($event->get_name());

                        assign_capability('mod/wiki:viewpage', CAP_PROHIBIT, $this->studentrole->id, $this->context->id);
        accesslib_clear_all_caches_for_unit_testing();

        try {
            mod_wiki_external::view_page($this->firstpage->id);
            $this->fail('Exception expected due to missing capability.');
        } catch (moodle_exception $e) {
            $this->assertEquals('cannotviewpage', $e->errorcode);
        }

    }

    
    public function test_get_subwikis() {

                try {
            mod_wiki_external::get_subwikis(0);
            $this->fail('Exception expected due to invalid get_subwikis wiki id.');
        } catch (moodle_exception $e) {
            $this->assertEquals('incorrectwikiid', $e->errorcode);
        }

                $usernotenrolled = self::getDataGenerator()->create_user();
        $this->setUser($usernotenrolled);
        try {
            mod_wiki_external::get_subwikis($this->wiki->id);
            $this->fail('Exception expected due to not enrolled user.');
        } catch (moodle_exception $e) {
            $this->assertEquals('requireloginerror', $e->errorcode);
        }

                $this->setUser($this->student);

                        $expectedsubwikis = array();
        $expectedsubwiki = array(
                'id' => $this->firstpage->subwikiid,
                'wikiid' => $this->wiki->id,
                'groupid' => 0,
                'userid' => 0,
                'canedit' => true
            );
        $expectedsubwikis[] = $expectedsubwiki;

        $result = mod_wiki_external::get_subwikis($this->wiki->id);
        $result = external_api::clean_returnvalue(mod_wiki_external::get_subwikis_returns(), $result);
        $this->assertEquals($expectedsubwikis, $result['subwikis']);
        $this->assertCount(0, $result['warnings']);

                        assign_capability('mod/wiki:viewpage', CAP_PROHIBIT, $this->studentrole->id, $this->context->id);
        accesslib_clear_all_caches_for_unit_testing();

        try {
            mod_wiki_external::get_subwikis($this->wiki->id);
            $this->fail('Exception expected due to missing capability.');
        } catch (moodle_exception $e) {
            $this->assertEquals('nopermissions', $e->errorcode);
        }

    }

    
    public function test_get_subwiki_pages_invalid_instance() {
        $this->setExpectedException('moodle_exception');
        mod_wiki_external::get_subwiki_pages(0);
    }

    
    public function test_get_subwiki_pages_unenrolled_user() {
                $usernotenrolled = self::getDataGenerator()->create_user();
        $this->setUser($usernotenrolled);

        $this->setExpectedException('require_login_exception');
        mod_wiki_external::get_subwiki_pages($this->wiki->id);
    }

    
    public function test_get_subwiki_pages_hidden_wiki_as_student() {
                $hiddenwiki = $this->getDataGenerator()->create_module('wiki',
                            array('course' => $this->course->id, 'visible' => false));

        $this->setUser($this->student);
        $this->setExpectedException('require_login_exception');
        mod_wiki_external::get_subwiki_pages($hiddenwiki->id);
    }

    
    public function test_get_subwiki_pages_without_viewpage_capability() {
                $contextcourse = context_course::instance($this->course->id);
        assign_capability('mod/wiki:viewpage', CAP_PROHIBIT, $this->studentrole->id, $contextcourse->id);
        accesslib_clear_all_caches_for_unit_testing();

        $this->setUser($this->student);
        $this->setExpectedException('moodle_exception');
        mod_wiki_external::get_subwiki_pages($this->wiki->id);
    }

    
    public function test_get_subwiki_pages_invalid_userid() {
                $indwiki = $this->getDataGenerator()->create_module('wiki',
                                array('course' => $this->course->id, 'wikimode' => 'individual'));

        $this->setExpectedException('moodle_exception');
        mod_wiki_external::get_subwiki_pages($indwiki->id, 0, -10);
    }

    
    public function test_get_subwiki_pages_invalid_groupid() {
                $this->create_collaborative_wikis_with_groups();

        $this->setExpectedException('moodle_exception');
        mod_wiki_external::get_subwiki_pages($this->wikisep->id, -111);
    }

    
    public function test_get_subwiki_pages_individual_student_see_other_user() {
                $indwiki = $this->getDataGenerator()->create_module('wiki',
                                array('course' => $this->course->id, 'wikimode' => 'individual'));

        $this->setUser($this->student);
        $this->setExpectedException('moodle_exception');
        mod_wiki_external::get_subwiki_pages($indwiki->id, 0, $this->teacher->id);
    }

    
    public function test_get_subwiki_pages_collaborative_separate_groups_student_see_other_group() {
                $this->create_collaborative_wikis_with_groups();

        $this->setUser($this->student);
        $this->setExpectedException('moodle_exception');
        mod_wiki_external::get_subwiki_pages($this->wikisep->id, $this->group2->id);
    }

    
    public function test_get_subwiki_pages_individual_separate_groups_student_see_other_group() {
                $this->create_individual_wikis_with_groups();

        $this->setUser($this->student);
        $this->setExpectedException('moodle_exception');
        mod_wiki_external::get_subwiki_pages($this->wikisepind->id, $this->group2->id, $this->teacher->id);
    }

    
    public function test_get_subwiki_pages_collaborative_separate_groups_student_see_all_participants() {
                $this->create_collaborative_wikis_with_groups();

        $this->setUser($this->student);
        $this->setExpectedException('moodle_exception');
        mod_wiki_external::get_subwiki_pages($this->wikisep->id, 0);
    }

    
    public function test_get_subwiki_pages_individual_separate_groups_student_see_all_participants() {
                $this->create_individual_wikis_with_groups();

        $this->setUser($this->student);
        $this->setExpectedException('moodle_exception');
        mod_wiki_external::get_subwiki_pages($this->wikisepind->id, 0, $this->teacher->id);
    }

    
    public function test_get_subwiki_pages_collaborative() {

                $this->setUser($this->student);

                $expectedpages = array();
        $expectedfirstpage = (array) $this->firstpage;
        $expectedfirstpage['caneditpage'] = true;         $expectedfirstpage['firstpage'] = true;
        $expectedfirstpage['contentformat'] = 1;
        $expectedpages[] = $expectedfirstpage;

        $result = mod_wiki_external::get_subwiki_pages($this->wiki->id);
        $result = external_api::clean_returnvalue(mod_wiki_external::get_subwiki_pages_returns(), $result);
        $this->assertEquals($expectedpages, $result['pages']);

                $result = mod_wiki_external::get_subwiki_pages($this->wiki->id, 1234);
        $result = external_api::clean_returnvalue(mod_wiki_external::get_subwiki_pages_returns(), $result);
        $this->assertEquals($expectedpages, $result['pages']);

                $result = mod_wiki_external::get_subwiki_pages($this->wiki->id, 1234, 1234);
        $result = external_api::clean_returnvalue(mod_wiki_external::get_subwiki_pages_returns(), $result);
        $this->assertEquals($expectedpages, $result['pages']);

                $newpage = $this->getDataGenerator()->get_plugin_generator('mod_wiki')->create_page(
                                $this->wiki, array('title' => 'AAA'));

        $expectednewpage = (array) $newpage;
        $expectednewpage['caneditpage'] = true;         $expectednewpage['firstpage'] = false;
        $expectednewpage['contentformat'] = 1;
        array_unshift($expectedpages, $expectednewpage); 
        $result = mod_wiki_external::get_subwiki_pages($this->wiki->id);
        $result = external_api::clean_returnvalue(mod_wiki_external::get_subwiki_pages_returns(), $result);
        $this->assertEquals($expectedpages, $result['pages']);

                $expectedpages = array($expectedfirstpage, $expectednewpage);
        $result = mod_wiki_external::get_subwiki_pages($this->wiki->id, 0, 0, array('sortby' => 'id'));
        $result = external_api::clean_returnvalue(mod_wiki_external::get_subwiki_pages_returns(), $result);
        $this->assertEquals($expectedpages, $result['pages']);

                foreach ($expectedpages as $i => $expectedpage) {
            if (function_exists('mb_strlen') && ((int)ini_get('mbstring.func_overload') & 2)) {
                $expectedpages[$i]['contentsize'] = mb_strlen($expectedpages[$i]['cachedcontent'], '8bit');
            } else {
                $expectedpages[$i]['contentsize'] = strlen($expectedpages[$i]['cachedcontent']);
            }
            unset($expectedpages[$i]['cachedcontent']);
            unset($expectedpages[$i]['contentformat']);
        }
        $result = mod_wiki_external::get_subwiki_pages($this->wiki->id, 0, 0, array('sortby' => 'id', 'includecontent' => 0));
        $result = external_api::clean_returnvalue(mod_wiki_external::get_subwiki_pages_returns(), $result);
        $this->assertEquals($expectedpages, $result['pages']);
    }

    
    public function test_get_subwiki_pages_individual() {

                $indwiki = $this->getDataGenerator()->create_module('wiki',
                                array('course' => $this->course->id, 'wikimode' => 'individual'));

                $result = mod_wiki_external::get_subwiki_pages($indwiki->id);
        $result = external_api::clean_returnvalue(mod_wiki_external::get_subwiki_pages_returns(), $result);
        $this->assertEquals(array(), $result['pages']);

                $this->setUser($this->student);
        $indfirstpagestudent = $this->getDataGenerator()->get_plugin_generator('mod_wiki')->create_first_page($indwiki);
        $this->setUser($this->teacher);
        $indfirstpageteacher = $this->getDataGenerator()->get_plugin_generator('mod_wiki')->create_first_page($indwiki);

                $expectedteacherpage = (array) $indfirstpageteacher;
        $expectedteacherpage['caneditpage'] = true;
        $expectedteacherpage['firstpage'] = true;
        $expectedteacherpage['contentformat'] = 1;
        $expectedpages = array($expectedteacherpage);

        $result = mod_wiki_external::get_subwiki_pages($indwiki->id, 0, $this->teacher->id);
        $result = external_api::clean_returnvalue(mod_wiki_external::get_subwiki_pages_returns(), $result);
        $this->assertEquals($expectedpages, $result['pages']);

                $expectedstudentpage = (array) $indfirstpagestudent;
        $expectedstudentpage['caneditpage'] = true;
        $expectedstudentpage['firstpage'] = true;
        $expectedstudentpage['contentformat'] = 1;
        $expectedpages = array($expectedstudentpage);

        $result = mod_wiki_external::get_subwiki_pages($indwiki->id, 0, $this->student->id);
        $result = external_api::clean_returnvalue(mod_wiki_external::get_subwiki_pages_returns(), $result);
        $this->assertEquals($expectedpages, $result['pages']);

                $this->setUser($this->student);

        $result = mod_wiki_external::get_subwiki_pages($indwiki->id, 0, $this->student->id);
        $result = external_api::clean_returnvalue(mod_wiki_external::get_subwiki_pages_returns(), $result);
        $this->assertEquals($expectedpages, $result['pages']);

                $result = mod_wiki_external::get_subwiki_pages($indwiki->id, 0);
        $result = external_api::clean_returnvalue(mod_wiki_external::get_subwiki_pages_returns(), $result);
        $this->assertEquals($expectedpages, $result['pages']);
    }

    
    public function test_get_subwiki_pages_separate_groups_collaborative() {

                $this->create_collaborative_wikis_with_groups();

        $this->setUser($this->student);

        
        $expectedpage = (array) $this->fpsepg1;
        $expectedpage['caneditpage'] = true;         $expectedpage['firstpage'] = true;
        $expectedpage['contentformat'] = 1;
        $expectedpages = array($expectedpage);

        $result = mod_wiki_external::get_subwiki_pages($this->wikisep->id, $this->group1->id);
        $result = external_api::clean_returnvalue(mod_wiki_external::get_subwiki_pages_returns(), $result);
        $this->assertEquals($expectedpages, $result['pages']);

                $result = mod_wiki_external::get_subwiki_pages($this->wikisep->id);
        $result = external_api::clean_returnvalue(mod_wiki_external::get_subwiki_pages_returns(), $result);
        $this->assertEquals($expectedpages, $result['pages']);

                $this->setUser($this->teacher);
        $result = mod_wiki_external::get_subwiki_pages($this->wikisep->id, $this->group1->id);
        $result = external_api::clean_returnvalue(mod_wiki_external::get_subwiki_pages_returns(), $result);
        $this->assertEquals($expectedpages, $result['pages']);

                $expectedpage = (array) $this->fpsepall;
        $expectedpage['caneditpage'] = true;
        $expectedpage['firstpage'] = true;
        $expectedpage['contentformat'] = 1;
        $expectedpages = array($expectedpage);

        $result = mod_wiki_external::get_subwiki_pages($this->wikisep->id, 0);
        $result = external_api::clean_returnvalue(mod_wiki_external::get_subwiki_pages_returns(), $result);
        $this->assertEquals($expectedpages, $result['pages']);
    }

    
    public function test_get_subwiki_pages_visible_groups_collaborative() {

                $this->create_collaborative_wikis_with_groups();

        $this->setUser($this->student);

        
        $expectedpage = (array) $this->fpvisg1;
        $expectedpage['caneditpage'] = true;         $expectedpage['firstpage'] = true;
        $expectedpage['contentformat'] = 1;
        $expectedpages = array($expectedpage);

        $result = mod_wiki_external::get_subwiki_pages($this->wikivis->id, $this->group1->id);
        $result = external_api::clean_returnvalue(mod_wiki_external::get_subwiki_pages_returns(), $result);
        $this->assertEquals($expectedpages, $result['pages']);

                $expectedpage = (array) $this->fpvisg2;
        $expectedpage['caneditpage'] = false;         $expectedpage['firstpage'] = true;
        $expectedpage['contentformat'] = 1;
        $expectedpages = array($expectedpage);

        $result = mod_wiki_external::get_subwiki_pages($this->wikivis->id, $this->group2->id);
        $result = external_api::clean_returnvalue(mod_wiki_external::get_subwiki_pages_returns(), $result);
        $this->assertEquals($expectedpages, $result['pages']);

                $expectedpage = (array) $this->fpvisall;
        $expectedpage['caneditpage'] = false;
        $expectedpage['firstpage'] = true;
        $expectedpage['contentformat'] = 1;
        $expectedpages = array($expectedpage);

        $result = mod_wiki_external::get_subwiki_pages($this->wikivis->id, 0);
        $result = external_api::clean_returnvalue(mod_wiki_external::get_subwiki_pages_returns(), $result);
        $this->assertEquals($expectedpages, $result['pages']);
    }

    
    public function test_get_subwiki_pages_separate_groups_individual() {

                $this->create_individual_wikis_with_groups();

        $this->setUser($this->student);

                $expectedpage = (array) $this->fpsepg1indstu;
        $expectedpage['caneditpage'] = true;
        $expectedpage['firstpage'] = true;
        $expectedpage['contentformat'] = 1;
        $expectedpages = array($expectedpage);

        $result = mod_wiki_external::get_subwiki_pages($this->wikisepind->id, $this->group1->id, $this->student->id);
        $result = external_api::clean_returnvalue(mod_wiki_external::get_subwiki_pages_returns(), $result);
        $this->assertEquals($expectedpages, $result['pages']);

                $result = mod_wiki_external::get_subwiki_pages($this->wikisepind->id, $this->group1->id);
        $result = external_api::clean_returnvalue(mod_wiki_external::get_subwiki_pages_returns(), $result);
        $this->assertEquals($expectedpages, $result['pages']);

                $this->setUser($this->teacher);
        $result = mod_wiki_external::get_subwiki_pages($this->wikisepind->id, $this->group1->id, $this->student->id);
        $result = external_api::clean_returnvalue(mod_wiki_external::get_subwiki_pages_returns(), $result);
        $this->assertEquals($expectedpages, $result['pages']);

                $this->setUser($this->student);
        $expectedpage = (array) $this->fpsepg1indstu2;
        $expectedpage['caneditpage'] = false;
        $expectedpage['firstpage'] = true;
        $expectedpage['contentformat'] = 1;
        $expectedpages = array($expectedpage);

        $result = mod_wiki_external::get_subwiki_pages($this->wikisepind->id, $this->group1->id, $this->student2->id);
        $result = external_api::clean_returnvalue(mod_wiki_external::get_subwiki_pages_returns(), $result);
        $this->assertEquals($expectedpages, $result['pages']);
    }

    
    public function test_get_subwiki_pages_visible_groups_individual() {

                $this->create_individual_wikis_with_groups();

        $this->setUser($this->student);

                $expectedpage = (array) $this->fpvisg1indstu;
        $expectedpage['caneditpage'] = true;
        $expectedpage['firstpage'] = true;
        $expectedpage['contentformat'] = 1;
        $expectedpages = array($expectedpage);

        $result = mod_wiki_external::get_subwiki_pages($this->wikivisind->id, $this->group1->id, $this->student->id);
        $result = external_api::clean_returnvalue(mod_wiki_external::get_subwiki_pages_returns(), $result);
        $this->assertEquals($expectedpages, $result['pages']);

                $expectedpage = (array) $this->fpvisg2indt;
        $expectedpage['caneditpage'] = false;
        $expectedpage['firstpage'] = true;
        $expectedpage['contentformat'] = 1;
        $expectedpages = array($expectedpage);

        $result = mod_wiki_external::get_subwiki_pages($this->wikivisind->id, $this->group2->id, $this->teacher->id);
        $result = external_api::clean_returnvalue(mod_wiki_external::get_subwiki_pages_returns(), $result);
        $this->assertEquals($expectedpages, $result['pages']);

                $expectedpage = (array) $this->fpvisallindt;
        $expectedpage['caneditpage'] = false;
        $expectedpage['firstpage'] = true;
        $expectedpage['contentformat'] = 1;
        $expectedpages = array($expectedpage);

        $result = mod_wiki_external::get_subwiki_pages($this->wikivisind->id, 0, $this->teacher->id);
        $result = external_api::clean_returnvalue(mod_wiki_external::get_subwiki_pages_returns(), $result);
        $this->assertEquals($expectedpages, $result['pages']);
    }

    
    public function test_get_page_contents_invalid_pageid() {
        $this->setExpectedException('moodle_exception');
        mod_wiki_external::get_page_contents(0);
    }

    
    public function test_get_page_contents_unenrolled_user() {
                $usernotenrolled = self::getDataGenerator()->create_user();
        $this->setUser($usernotenrolled);

        $this->setExpectedException('require_login_exception');
        mod_wiki_external::get_page_contents($this->firstpage->id);
    }

    
    public function test_get_page_contents_hidden_wiki_as_student() {
                $hiddenwiki = $this->getDataGenerator()->create_module('wiki',
                            array('course' => $this->course->id, 'visible' => false));
        $hiddenpage = $this->getDataGenerator()->get_plugin_generator('mod_wiki')->create_page($hiddenwiki);

        $this->setUser($this->student);
        $this->setExpectedException('require_login_exception');
        mod_wiki_external::get_page_contents($hiddenpage->id);
    }

    
    public function test_get_page_contents_without_viewpage_capability() {
                $contextcourse = context_course::instance($this->course->id);
        assign_capability('mod/wiki:viewpage', CAP_PROHIBIT, $this->studentrole->id, $contextcourse->id);
        accesslib_clear_all_caches_for_unit_testing();

        $this->setUser($this->student);
        $this->setExpectedException('moodle_exception');
        mod_wiki_external::get_page_contents($this->firstpage->id);
    }

    
    public function test_get_page_contents_separate_groups_student_see_other_group() {
                $this->create_individual_wikis_with_groups();

        $this->setUser($this->student);
        $this->setExpectedException('moodle_exception');
        mod_wiki_external::get_page_contents($this->fpsepg2indt->id);
    }

    
    public function test_get_page_contents() {

                $this->setUser($this->student);

                $expectedpage = array(
            'id' => $this->firstpage->id,
            'wikiid' => $this->wiki->id,
            'subwikiid' => $this->firstpage->subwikiid,
            'groupid' => 0,             'userid' => 0,             'title' => $this->firstpage->title,
            'cachedcontent' => $this->firstpage->cachedcontent,
            'contentformat' => 1,
            'caneditpage' => true
        );

        $result = mod_wiki_external::get_page_contents($this->firstpage->id);
        $result = external_api::clean_returnvalue(mod_wiki_external::get_page_contents_returns(), $result);
        $this->assertEquals($expectedpage, $result['page']);

                $newpage = $this->getDataGenerator()->get_plugin_generator('mod_wiki')->create_page($this->wiki);

        $expectedpage['id'] = $newpage->id;
        $expectedpage['title'] = $newpage->title;
        $expectedpage['cachedcontent'] = $newpage->cachedcontent;

        $result = mod_wiki_external::get_page_contents($newpage->id);
        $result = external_api::clean_returnvalue(mod_wiki_external::get_page_contents_returns(), $result);
        $this->assertEquals($expectedpage, $result['page']);
    }

    
    public function test_get_page_contents_with_groups() {

                $this->create_individual_wikis_with_groups();

                $this->setUser($this->student);

        $expectedfpsepg1indstu = array(
            'id' => $this->fpsepg1indstu->id,
            'wikiid' => $this->wikisepind->id,
            'subwikiid' => $this->fpsepg1indstu->subwikiid,
            'groupid' => $this->group1->id,
            'userid' => $this->student->id,
            'title' => $this->fpsepg1indstu->title,
            'cachedcontent' => $this->fpsepg1indstu->cachedcontent,
            'contentformat' => 1,
            'caneditpage' => true
        );

        $result = mod_wiki_external::get_page_contents($this->fpsepg1indstu->id);
        $result = external_api::clean_returnvalue(mod_wiki_external::get_page_contents_returns(), $result);
        $this->assertEquals($expectedfpsepg1indstu, $result['page']);

                $this->setUser($this->teacher);
        $result = mod_wiki_external::get_page_contents($this->fpsepg1indstu->id);
        $result = external_api::clean_returnvalue(mod_wiki_external::get_page_contents_returns(), $result);
        $this->assertEquals($expectedfpsepg1indstu, $result['page']);
    }

    
    public function test_get_subwiki_files_no_files() {
        $result = mod_wiki_external::get_subwiki_files($this->wiki->id);
        $result = external_api::clean_returnvalue(mod_wiki_external::get_subwiki_files_returns(), $result);
        $this->assertCount(0, $result['files']);
        $this->assertCount(0, $result['warnings']);
    }

    
    public function test_get_subwiki_files_separate_groups_student_see_other_group() {
                $this->create_collaborative_wikis_with_groups();

        $this->setUser($this->student);
        $this->setExpectedException('moodle_exception');
        mod_wiki_external::get_subwiki_files($this->wikisep->id, $this->group2->id);
    }

    
    public function test_get_subwiki_files_collaborative_no_groups() {
        $this->setUser($this->student);

                $fs = get_file_storage();
        $file = array('component' => 'mod_wiki', 'filearea' => 'attachments',
                'contextid' => $this->context->id, 'itemid' => $this->firstpage->subwikiid,
                'filename' => 'image.jpg', 'filepath' => '/', 'timemodified' => time());
        $content = 'IMAGE';
        $fs->create_file_from_string($file, $content);

        $expectedfile = array(
            'filename' => $file['filename'],
            'filepath' => $file['filepath'],
            'mimetype' => 'image/jpeg',
            'filesize' => strlen($content),
            'timemodified' => $file['timemodified'],
            'fileurl' => moodle_url::make_webservice_pluginfile_url($file['contextid'], $file['component'],
                            $file['filearea'], $file['itemid'], $file['filepath'], $file['filename']),
        );

                $result = mod_wiki_external::get_subwiki_files($this->wiki->id);
        $result = external_api::clean_returnvalue(mod_wiki_external::get_subwiki_files_returns(), $result);
        $this->assertCount(1, $result['files']);
        $this->assertEquals($expectedfile, $result['files'][0]);

                $file['filename'] = 'Another image.jpg';
        $file['timemodified'] = time();
        $content = 'ANOTHER IMAGE';
        $fs->create_file_from_string($file, $content);

        $expectedfile['filename'] = $file['filename'];
        $expectedfile['timemodified'] = $file['timemodified'];
        $expectedfile['filesize'] = strlen($content);
        $expectedfile['fileurl'] = moodle_url::make_webservice_pluginfile_url($file['contextid'], $file['component'],
                            $file['filearea'], $file['itemid'], $file['filepath'], $file['filename']);

                $result = mod_wiki_external::get_subwiki_files($this->wiki->id);
        $result = external_api::clean_returnvalue(mod_wiki_external::get_subwiki_files_returns(), $result);
        $this->assertCount(2, $result['files']);
                $this->assertEquals($expectedfile, $result['files'][0]);
    }

    
    public function test_get_subwiki_files_visible_groups_individual() {
                $this->create_individual_wikis_with_groups();

        $this->setUser($this->student);

                $fs = get_file_storage();
        $contextwiki = context_module::instance($this->wikivisind->cmid);
        $file = array('component' => 'mod_wiki', 'filearea' => 'attachments',
                'contextid' => $contextwiki->id, 'itemid' => $this->fpvisg1indstu->subwikiid,
                'filename' => 'image.jpg', 'filepath' => '/', 'timemodified' => time());
        $content = 'IMAGE';
        $fs->create_file_from_string($file, $content);

        $expectedfile = array(
            'filename' => $file['filename'],
            'filepath' => $file['filepath'],
            'mimetype' => 'image/jpeg',
            'filesize' => strlen($content),
            'timemodified' => $file['timemodified'],
            'fileurl' => moodle_url::make_webservice_pluginfile_url($file['contextid'], $file['component'],
                            $file['filearea'], $file['itemid'], $file['filepath'], $file['filename']),
        );

                $result = mod_wiki_external::get_subwiki_files($this->wikivisind->id, $this->group1->id);
        $result = external_api::clean_returnvalue(mod_wiki_external::get_subwiki_files_returns(), $result);
        $this->assertCount(1, $result['files']);
        $this->assertEquals($expectedfile, $result['files'][0]);

                $this->setUser($this->teacher);
        $result = mod_wiki_external::get_subwiki_files($this->wikivisind->id, $this->group1->id, $this->student->id);
        $result = external_api::clean_returnvalue(mod_wiki_external::get_subwiki_files_returns(), $result);
        $this->assertCount(1, $result['files']);
        $this->assertEquals($expectedfile, $result['files'][0]);
    }


    
    public function test_get_page_for_editing() {

        $this->create_individual_wikis_with_groups();

                $sectioncontent = '<h1><span>Title1</span></h1>Text inside section';
        $pagecontent = $sectioncontent.'<h1>Title2</h1>Text inside section';
        $newpage = $this->getDataGenerator()->get_plugin_generator('mod_wiki')->create_page(
                                $this->wiki, array('content' => $pagecontent));

                $this->setUser($this->student);

                $expected = array(
            'content' => $pagecontent,
            'contentformat' => 'html',
            'version' => '1'
        );

        $result = mod_wiki_external::get_page_for_editing($newpage->id);
        $result = external_api::clean_returnvalue(mod_wiki_external::get_page_for_editing_returns(), $result);
        $this->assertEquals($expected, $result['pagesection']);

                $expected = array(
            'content' => $sectioncontent,
            'contentformat' => 'html',
            'version' => '1'
        );

        $result = mod_wiki_external::get_page_for_editing($newpage->id, '<span>Title1</span>');
        $result = external_api::clean_returnvalue(mod_wiki_external::get_page_for_editing_returns(), $result);
        $this->assertEquals($expected, $result['pagesection']);
    }

    
    public function test_new_page() {

        $this->create_individual_wikis_with_groups();

        $sectioncontent = '<h1>Title1</h1>Text inside section';
        $pagecontent = $sectioncontent.'<h1>Title2</h1>Text inside section';
        $pagetitle = 'Page Title';

                $this->setUser($this->student);

                $result = mod_wiki_external::new_page($pagetitle, $pagecontent, 'html', $this->fpsepg1indstu->subwikiid);
        $result = external_api::clean_returnvalue(mod_wiki_external::new_page_returns(), $result);
        $this->assertInternalType('int', $result['pageid']);

        $version = wiki_get_current_version($result['pageid']);
        $this->assertEquals($pagecontent, $version->content);
        $this->assertEquals('html', $version->contentformat);

        $page = wiki_get_page($result['pageid']);
        $this->assertEquals($pagetitle, $page->title);

                try {
            mod_wiki_external::new_page($pagetitle, $pagecontent, 'html', $this->fpsepg1indstu->subwikiid);
            $this->fail('Exception expected due to creation of an existing page.');
        } catch (moodle_exception $e) {
            $this->assertEquals('pageexists', $e->errorcode);
        }

                $this->getDataGenerator()->create_group_member(array('userid' => $this->student->id, 'groupid' => $this->group2->id));
        $result = mod_wiki_external::new_page($pagetitle, $pagecontent, 'html', null, $this->wikisepind->id, $this->student->id,
            $this->group2->id);
        $result = external_api::clean_returnvalue(mod_wiki_external::new_page_returns(), $result);
        $this->assertInternalType('int', $result['pageid']);

        $version = wiki_get_current_version($result['pageid']);
        $this->assertEquals($pagecontent, $version->content);
        $this->assertEquals('html', $version->contentformat);

        $page = wiki_get_page($result['pageid']);
        $this->assertEquals($pagetitle, $page->title);

        $subwiki = wiki_get_subwiki($page->subwikiid);
        $expected = new StdClass();
        $expected->id = $subwiki->id;
        $expected->wikiid = $this->wikisepind->id;
        $expected->groupid = $this->group2->id;
        $expected->userid = $this->student->id;
        $this->assertEquals($expected, $subwiki);

                $this->studentnotincourse = self::getDataGenerator()->create_user();
        $this->anothercourse = $this->getDataGenerator()->create_course();
        $this->groupnotincourse = $this->getDataGenerator()->create_group(array('courseid' => $this->anothercourse->id));

        try {
            mod_wiki_external::new_page($pagetitle, $pagecontent, 'html', null, $this->wikisepind->id,
                $this->studentnotincourse->id, $this->groupnotincourse->id);
            $this->fail('Exception expected due to creation of an invalid subwiki creation.');
        } catch (moodle_exception $e) {
            $this->assertEquals('cannoteditpage', $e->errorcode);
        }

    }

    
    public function test_edit_page() {

        $this->create_individual_wikis_with_groups();

                $this->setUser($this->student);

        $newpage = $this->getDataGenerator()->get_plugin_generator('mod_wiki')->create_page($this->wikisepind,
            array('group' => $this->group1->id, 'content' => 'Test'));

                        $sectioncontent = '<h1><span>Title1</span></h1>Text inside section';
        $newpagecontent = $sectioncontent.'<h1><span>Title2</span></h1>Text inside section';

        $result = mod_wiki_external::edit_page($newpage->id, $newpagecontent);
        $result = external_api::clean_returnvalue(mod_wiki_external::edit_page_returns(), $result);
        $this->assertInternalType('int', $result['pageid']);

        $version = wiki_get_current_version($result['pageid']);
        $this->assertEquals($newpagecontent, $version->content);

                $newsectioncontent = '<h1><span>Title2</span></h1>New test2';
        $section = '<span>Title2</span>';

        $result = mod_wiki_external::edit_page($newpage->id, $newsectioncontent, $section);
        $result = external_api::clean_returnvalue(mod_wiki_external::edit_page_returns(), $result);
        $this->assertInternalType('int', $result['pageid']);

        $expected = $sectioncontent . $newsectioncontent;

        $version = wiki_get_current_version($result['pageid']);
        $this->assertEquals($expected, $version->content);

                $newsectioncontent = '<h1><span>Title2</span></h1>New test2';
        $section = '<span>Title2</span>';

        try {
                        wiki_set_lock($newpage->id, 1, $section, true);
            mod_wiki_external::edit_page($newpage->id, $newsectioncontent, $section);
            $this->fail('Exception expected due to locked section');
        } catch (moodle_exception $e) {
            $this->assertEquals('pageislocked', $e->errorcode);
        }

                $newsectioncontent = '<h1>Title3</h1>New test3';
        $section = 'Title3';

        try {
            mod_wiki_external::edit_page($newpage->id, $newsectioncontent, $section);
            $this->fail('Exception expected due to non existing section in the page.');
        } catch (moodle_exception $e) {
            $this->assertEquals('invalidsection', $e->errorcode);
        }

    }

}
