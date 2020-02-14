<?php



defined('MOODLE_INTERNAL') || die();

global $CFG;
require_once($CFG->dirroot . '/mod/wiki/lib.php');
require_once($CFG->dirroot . '/mod/wiki/locallib.php');
require_once($CFG->libdir . '/completionlib.php');


class mod_wiki_lib_testcase extends advanced_testcase {

    
    public function test_wiki_view() {
        global $CFG;

        $CFG->enablecompletion = COMPLETION_ENABLED;
        $this->resetAfterTest();

        $this->setAdminUser();
                $course = $this->getDataGenerator()->create_course(array('enablecompletion' => COMPLETION_ENABLED));
        $options = array('completion' => COMPLETION_TRACKING_AUTOMATIC, 'completionview' => COMPLETION_VIEW_REQUIRED);
        $wiki = $this->getDataGenerator()->create_module('wiki', array('course' => $course->id), $options);
        $context = context_module::instance($wiki->cmid);
        $cm = get_coursemodule_from_instance('wiki', $wiki->id);

                $sink = $this->redirectEvents();

        wiki_view($wiki, $course, $cm, $context);

        $events = $sink->get_events();
                $this->assertCount(3, $events);
        $event = array_shift($events);

                $this->assertInstanceOf('\mod_wiki\event\course_module_viewed', $event);
        $this->assertEquals($context, $event->get_context());
        $moodleurl = new \moodle_url('/mod/wiki/view.php', array('id' => $cm->id));
        $this->assertEquals($moodleurl, $event->get_url());
        $this->assertEventContextNotUsed($event);
        $this->assertNotEmpty($event->get_name());

                $completion = new completion_info($course);
        $completiondata = $completion->get_data($cm);
        $this->assertEquals(1, $completiondata->completionstate);

    }

    
    public function test_wiki_page_view() {
        global $CFG;

        $CFG->enablecompletion = COMPLETION_ENABLED;
        $this->resetAfterTest();

        $this->setAdminUser();
                $course = $this->getDataGenerator()->create_course(array('enablecompletion' => COMPLETION_ENABLED));
        $options = array('completion' => COMPLETION_TRACKING_AUTOMATIC, 'completionview' => COMPLETION_VIEW_REQUIRED);
        $wiki = $this->getDataGenerator()->create_module('wiki', array('course' => $course->id), $options);
        $context = context_module::instance($wiki->cmid);
        $cm = get_coursemodule_from_instance('wiki', $wiki->id);
        $firstpage = $this->getDataGenerator()->get_plugin_generator('mod_wiki')->create_first_page($wiki);

                $sink = $this->redirectEvents();

        wiki_page_view($wiki, $firstpage, $course, $cm, $context);

        $events = $sink->get_events();
                $this->assertCount(3, $events);
        $event = array_shift($events);

                $this->assertInstanceOf('\mod_wiki\event\page_viewed', $event);
        $this->assertEquals($context, $event->get_context());
        $pageurl = new \moodle_url('/mod/wiki/view.php', array('pageid' => $firstpage->id));
        $this->assertEquals($pageurl, $event->get_url());
        $this->assertEventContextNotUsed($event);
        $this->assertNotEmpty($event->get_name());

                $completion = new completion_info($course);
        $completiondata = $completion->get_data($cm);
        $this->assertEquals(1, $completiondata->completionstate);

    }

    
    public function test_wiki_user_can_edit() {
        global $DB;

        $this->resetAfterTest();
        $this->setAdminUser();

                $course = $this->getDataGenerator()->create_course();
        $indwiki = $this->getDataGenerator()->create_module('wiki', array('course' => $course->id, 'wikimode' => 'individual'));
        $colwiki = $this->getDataGenerator()->create_module('wiki', array('course' => $course->id, 'wikimode' => 'collaborative'));

                $student = self::getDataGenerator()->create_user();
        $teacher = self::getDataGenerator()->create_user();

                $studentrole = $DB->get_record('role', array('shortname' => 'student'));
        $teacherrole = $DB->get_record('role', array('shortname' => 'editingteacher'));
        $this->getDataGenerator()->enrol_user($student->id, $course->id, $studentrole->id, 'manual');
        $this->getDataGenerator()->enrol_user($teacher->id, $course->id, $teacherrole->id, 'manual');

                $swcol = new stdClass();
        $swcol->id = -1;
        $swcol->wikiid = $colwiki->id;
        $swcol->groupid = 0;
        $swcol->userid = 0;

                $swindstudent = clone($swcol);
        $swindstudent->wikiid = $indwiki->id;
        $swindstudent->userid = $student->id;

        $swindteacher = clone($swindstudent);
        $swindteacher->userid = $teacher->id;

        $this->setUser($student);

                $this->assertTrue(wiki_user_can_edit($swcol));

                $this->assertTrue(wiki_user_can_edit($swindstudent));

                $this->assertFalse(wiki_user_can_edit($swindteacher));

                $this->setUser($teacher);

                $this->assertTrue(wiki_user_can_edit($swcol));

                $this->assertTrue(wiki_user_can_edit($swindteacher));

                $this->assertTrue(wiki_user_can_edit($swindstudent));

    }

    
    public function test_wiki_user_can_edit_with_groups_collaborative() {
        global $DB;

        $this->resetAfterTest();
        $this->setAdminUser();

                $course = $this->getDataGenerator()->create_course();
        $wikisepcol = $this->getDataGenerator()->create_module('wiki', array('course' => $course->id,
                                                        'groupmode' => SEPARATEGROUPS, 'wikimode' => 'collaborative'));
        $wikiviscol = $this->getDataGenerator()->create_module('wiki', array('course' => $course->id,
                                                        'groupmode' => VISIBLEGROUPS, 'wikimode' => 'collaborative'));

                $student = self::getDataGenerator()->create_user();
        $student2 = self::getDataGenerator()->create_user();
        $teacher = self::getDataGenerator()->create_user();

                $studentrole = $DB->get_record('role', array('shortname' => 'student'));
        $teacherrole = $DB->get_record('role', array('shortname' => 'editingteacher'));
        $this->getDataGenerator()->enrol_user($student->id, $course->id, $studentrole->id, 'manual');
        $this->getDataGenerator()->enrol_user($student2->id, $course->id, $studentrole->id, 'manual');
        $this->getDataGenerator()->enrol_user($teacher->id, $course->id, $teacherrole->id, 'manual');

                $group1 = $this->getDataGenerator()->create_group(array('courseid' => $course->id));
        $this->getDataGenerator()->create_group_member(array('userid' => $student->id, 'groupid' => $group1->id));
        $this->getDataGenerator()->create_group_member(array('userid' => $student2->id, 'groupid' => $group1->id));
        $group2 = $this->getDataGenerator()->create_group(array('courseid' => $course->id));
        $this->getDataGenerator()->create_group_member(array('userid' => $student2->id, 'groupid' => $group2->id));

                        $swsepcolg1 = new stdClass();
        $swsepcolg1->id = -1;
        $swsepcolg1->wikiid = $wikisepcol->id;
        $swsepcolg1->groupid = $group1->id;
        $swsepcolg1->userid = 0;

        $swsepcolg2 = clone($swsepcolg1);
        $swsepcolg2->groupid = $group2->id;

        $swsepcolallparts = clone($swsepcolg1);         $swsepcolallparts->groupid = 0;

        $swviscolg1 = clone($swsepcolg1);
        $swviscolg1->wikiid = $wikiviscol->id;

        $swviscolg2 = clone($swviscolg1);
        $swviscolg2->groupid = $group2->id;

        $swviscolallparts = clone($swviscolg1);         $swviscolallparts->groupid = 0;

        $this->setUser($student);

                $this->assertTrue(wiki_user_can_edit($swsepcolg1));
        $this->assertTrue(wiki_user_can_edit($swviscolg1));

                $this->assertFalse(wiki_user_can_edit($swsepcolg2));
        $this->assertFalse(wiki_user_can_edit($swviscolg2));

                $this->setUser($student2);

                $this->assertTrue(wiki_user_can_edit($swsepcolg1));
        $this->assertTrue(wiki_user_can_edit($swviscolg1));
        $this->assertTrue(wiki_user_can_edit($swsepcolg2));
        $this->assertTrue(wiki_user_can_edit($swviscolg2));

                $this->assertFalse(wiki_user_can_edit($swsepcolallparts));
        $this->assertFalse(wiki_user_can_edit($swviscolallparts));

                $this->setUser($teacher);

                $this->assertTrue(wiki_user_can_edit($swsepcolg1));
        $this->assertTrue(wiki_user_can_edit($swviscolg1));
        $this->assertTrue(wiki_user_can_edit($swsepcolg2));
        $this->assertTrue(wiki_user_can_edit($swviscolg2));
        $this->assertTrue(wiki_user_can_edit($swsepcolallparts));
        $this->assertTrue(wiki_user_can_edit($swviscolallparts));
    }

    
    public function test_wiki_user_can_edit_with_groups_individual() {
        global $DB;

        $this->resetAfterTest();
        $this->setAdminUser();

                $course = $this->getDataGenerator()->create_course();
        $wikisepind = $this->getDataGenerator()->create_module('wiki', array('course' => $course->id,
                                                        'groupmode' => SEPARATEGROUPS, 'wikimode' => 'individual'));
        $wikivisind = $this->getDataGenerator()->create_module('wiki', array('course' => $course->id,
                                                        'groupmode' => VISIBLEGROUPS, 'wikimode' => 'individual'));

                $student = self::getDataGenerator()->create_user();
        $student2 = self::getDataGenerator()->create_user();
        $teacher = self::getDataGenerator()->create_user();

                $studentrole = $DB->get_record('role', array('shortname' => 'student'));
        $teacherrole = $DB->get_record('role', array('shortname' => 'editingteacher'));
        $this->getDataGenerator()->enrol_user($student->id, $course->id, $studentrole->id, 'manual');
        $this->getDataGenerator()->enrol_user($student2->id, $course->id, $studentrole->id, 'manual');
        $this->getDataGenerator()->enrol_user($teacher->id, $course->id, $teacherrole->id, 'manual');

                $group1 = $this->getDataGenerator()->create_group(array('courseid' => $course->id));
        $this->getDataGenerator()->create_group_member(array('userid' => $student->id, 'groupid' => $group1->id));
        $this->getDataGenerator()->create_group_member(array('userid' => $student2->id, 'groupid' => $group1->id));
        $group2 = $this->getDataGenerator()->create_group(array('courseid' => $course->id));
        $this->getDataGenerator()->create_group_member(array('userid' => $student2->id, 'groupid' => $group2->id));

                        $swsepindg1s1 = new stdClass();
        $swsepindg1s1->id = -1;
        $swsepindg1s1->wikiid = $wikisepind->id;
        $swsepindg1s1->groupid = $group1->id;
        $swsepindg1s1->userid = $student->id;

        $swsepindg1s2 = clone($swsepindg1s1);
        $swsepindg1s2->userid = $student2->id;

        $swsepindg2s2 = clone($swsepindg1s2);
        $swsepindg2s2->groupid = $group2->id;

        $swsepindteacher = clone($swsepindg1s1);
        $swsepindteacher->userid = $teacher->id;
        $swsepindteacher->groupid = 0;

        $swvisindg1s1 = clone($swsepindg1s1);
        $swvisindg1s1->wikiid = $wikivisind->id;

        $swvisindg1s2 = clone($swvisindg1s1);
        $swvisindg1s2->userid = $student2->id;

        $swvisindg2s2 = clone($swvisindg1s2);
        $swvisindg2s2->groupid = $group2->id;

        $swvisindteacher = clone($swvisindg1s1);
        $swvisindteacher->userid = $teacher->id;
        $swvisindteacher->groupid = 0;

        $this->setUser($student);

                $this->assertTrue(wiki_user_can_edit($swsepindg1s1));
        $this->assertTrue(wiki_user_can_edit($swvisindg1s1));

                $this->assertFalse(wiki_user_can_edit($swsepindg1s2));
        $this->assertFalse(wiki_user_can_edit($swvisindg1s2));

                $this->setUser($student2);

                $this->assertTrue(wiki_user_can_edit($swsepindg1s2));
        $this->assertTrue(wiki_user_can_edit($swvisindg1s2));
        $this->assertTrue(wiki_user_can_edit($swsepindg2s2));
        $this->assertTrue(wiki_user_can_edit($swvisindg2s2));

                $this->setUser($teacher);

                $this->assertTrue(wiki_user_can_edit($swsepindg1s1));
        $this->assertTrue(wiki_user_can_edit($swsepindg1s2));
        $this->assertTrue(wiki_user_can_edit($swsepindg2s2));
        $this->assertTrue(wiki_user_can_edit($swsepindteacher));
        $this->assertTrue(wiki_user_can_edit($swvisindg1s1));
        $this->assertTrue(wiki_user_can_edit($swvisindg1s2));
        $this->assertTrue(wiki_user_can_edit($swvisindg2s2));
        $this->assertTrue(wiki_user_can_edit($swvisindteacher));
    }

    
    public function test_wiki_get_visible_subwikis_without_groups() {
        global $DB;

        $this->resetAfterTest();
        $this->setAdminUser();

                $course = $this->getDataGenerator()->create_course();
        $indwiki = $this->getDataGenerator()->create_module('wiki', array('course' => $course->id, 'wikimode' => 'individual'));
        $colwiki = $this->getDataGenerator()->create_module('wiki', array('course' => $course->id, 'wikimode' => 'collaborative'));

                $student = self::getDataGenerator()->create_user();
        $teacher = self::getDataGenerator()->create_user();

                $studentrole = $DB->get_record('role', array('shortname' => 'student'));
        $teacherrole = $DB->get_record('role', array('shortname' => 'editingteacher'));
        $this->getDataGenerator()->enrol_user($student->id, $course->id, $studentrole->id, 'manual');
        $this->getDataGenerator()->enrol_user($teacher->id, $course->id, $teacherrole->id, 'manual');

        $this->setUser($student);

                $result = wiki_get_visible_subwikis(null);
        $this->assertEquals(array(), $result);

                $expectedsubwikis = array();
        $expectedsubwiki = new stdClass();
        $expectedsubwiki->id = -1;         $expectedsubwiki->wikiid = $colwiki->id;
        $expectedsubwiki->groupid = 0;
        $expectedsubwiki->userid = 0;
        $expectedsubwikis[] = $expectedsubwiki;

        $result = wiki_get_visible_subwikis($colwiki);
        $this->assertEquals($expectedsubwikis, $result);

                $colfirstpage = $this->getDataGenerator()->get_plugin_generator('mod_wiki')->create_first_page($colwiki);

                $expectedsubwikis[0]->id = $colfirstpage->subwikiid;
        $result = wiki_get_visible_subwikis($colwiki);
        $this->assertEquals($expectedsubwikis, $result);

                $this->setUser($teacher);
        $result = wiki_get_visible_subwikis($colwiki);
        $this->assertEquals($expectedsubwikis, $result);

                $this->setUser($student);
        $expectedsubwikis[0]->id = -1;
        $expectedsubwikis[0]->wikiid = $indwiki->id;
        $expectedsubwikis[0]->userid = $student->id;
        $result = wiki_get_visible_subwikis($indwiki);
        $this->assertEquals($expectedsubwikis, $result);

                $this->setUser($teacher);
        $teachersubwiki = new stdClass();
        $teachersubwiki->id = -1;
        $teachersubwiki->wikiid = $indwiki->id;
        $teachersubwiki->groupid = 0;
        $teachersubwiki->userid = $teacher->id;
        $expectedsubwikis[] = $teachersubwiki;

        $result = wiki_get_visible_subwikis($indwiki);
        $this->assertEquals($expectedsubwikis, $result, '', 0, 10, true);     }

    
    public function test_wiki_get_visible_subwikis_with_groups_collaborative() {
        global $DB;

        $this->resetAfterTest();
        $this->setAdminUser();

                $course = $this->getDataGenerator()->create_course();
        $wikisepcol = $this->getDataGenerator()->create_module('wiki', array('course' => $course->id,
                                                        'groupmode' => SEPARATEGROUPS, 'wikimode' => 'collaborative'));
        $wikiviscol = $this->getDataGenerator()->create_module('wiki', array('course' => $course->id,
                                                        'groupmode' => VISIBLEGROUPS, 'wikimode' => 'collaborative'));

                $student = self::getDataGenerator()->create_user();
        $student2 = self::getDataGenerator()->create_user();
        $student3 = self::getDataGenerator()->create_user();
        $teacher = self::getDataGenerator()->create_user();

                $studentrole = $DB->get_record('role', array('shortname' => 'student'));
        $teacherrole = $DB->get_record('role', array('shortname' => 'editingteacher'));
        $this->getDataGenerator()->enrol_user($student->id, $course->id, $studentrole->id, 'manual');
        $this->getDataGenerator()->enrol_user($student2->id, $course->id, $studentrole->id, 'manual');
        $this->getDataGenerator()->enrol_user($student3->id, $course->id, $studentrole->id, 'manual');
        $this->getDataGenerator()->enrol_user($teacher->id, $course->id, $teacherrole->id, 'manual');

                $group1 = $this->getDataGenerator()->create_group(array('courseid' => $course->id));
        $this->getDataGenerator()->create_group_member(array('userid' => $student->id, 'groupid' => $group1->id));
        $this->getDataGenerator()->create_group_member(array('userid' => $student2->id, 'groupid' => $group1->id));
        $group2 = $this->getDataGenerator()->create_group(array('courseid' => $course->id));
        $this->getDataGenerator()->create_group_member(array('userid' => $student2->id, 'groupid' => $group2->id));
        $this->getDataGenerator()->create_group_member(array('userid' => $student3->id, 'groupid' => $group2->id));

        $this->setUser($student);

                        $swsepcolg1 = new stdClass();
        $swsepcolg1->id = -1;
        $swsepcolg1->wikiid = $wikisepcol->id;
        $swsepcolg1->groupid = $group1->id;
        $swsepcolg1->userid = 0;

        $swsepcolg2 = clone($swsepcolg1);
        $swsepcolg2->groupid = $group2->id;

        $swsepcolallparts = clone($swsepcolg1);         $swsepcolallparts->groupid = 0;

        $swviscolg1 = clone($swsepcolg1);
        $swviscolg1->wikiid = $wikiviscol->id;

        $swviscolg2 = clone($swviscolg1);
        $swviscolg2->groupid = $group2->id;

        $swviscolallparts = clone($swviscolg1);         $swviscolallparts->groupid = 0;

                $expectedsubwikis = array($swsepcolg1);
        $result = wiki_get_visible_subwikis($wikisepcol);
        $this->assertEquals($expectedsubwikis, $result);

                $expectedsubwikis = array($swviscolallparts, $swviscolg1, $swviscolg2);
        $result = wiki_get_visible_subwikis($wikiviscol);
        $this->assertEquals($expectedsubwikis, $result, '', 0, 10, true);

                $this->setUser($teacher);

                $expectedsubwikis = array($swsepcolg1, $swsepcolg2, $swsepcolallparts);
        $result = wiki_get_visible_subwikis($wikisepcol);
        $this->assertEquals($expectedsubwikis, $result, '', 0, 10, true);
    }

    
    public function test_wiki_get_visible_subwikis_with_groups_individual() {
        global $DB;

        $this->resetAfterTest();
        $this->setAdminUser();

                $course = $this->getDataGenerator()->create_course();
        $wikisepind = $this->getDataGenerator()->create_module('wiki', array('course' => $course->id,
                                                        'groupmode' => SEPARATEGROUPS, 'wikimode' => 'individual'));
        $wikivisind = $this->getDataGenerator()->create_module('wiki', array('course' => $course->id,
                                                        'groupmode' => VISIBLEGROUPS, 'wikimode' => 'individual'));

                $student = self::getDataGenerator()->create_user();
        $student2 = self::getDataGenerator()->create_user();
        $student3 = self::getDataGenerator()->create_user();
        $teacher = self::getDataGenerator()->create_user();

                $studentrole = $DB->get_record('role', array('shortname' => 'student'));
        $teacherrole = $DB->get_record('role', array('shortname' => 'editingteacher'));
        $this->getDataGenerator()->enrol_user($student->id, $course->id, $studentrole->id, 'manual');
        $this->getDataGenerator()->enrol_user($student2->id, $course->id, $studentrole->id, 'manual');
        $this->getDataGenerator()->enrol_user($student3->id, $course->id, $studentrole->id, 'manual');
        $this->getDataGenerator()->enrol_user($teacher->id, $course->id, $teacherrole->id, 'manual');

                $group1 = $this->getDataGenerator()->create_group(array('courseid' => $course->id));
        $this->getDataGenerator()->create_group_member(array('userid' => $student->id, 'groupid' => $group1->id));
        $this->getDataGenerator()->create_group_member(array('userid' => $student2->id, 'groupid' => $group1->id));
        $group2 = $this->getDataGenerator()->create_group(array('courseid' => $course->id));
        $this->getDataGenerator()->create_group_member(array('userid' => $student2->id, 'groupid' => $group2->id));
        $this->getDataGenerator()->create_group_member(array('userid' => $student3->id, 'groupid' => $group2->id));

        $this->setUser($student);

                        $swsepindg1s1 = new stdClass();
        $swsepindg1s1->id = -1;
        $swsepindg1s1->wikiid = $wikisepind->id;
        $swsepindg1s1->groupid = $group1->id;
        $swsepindg1s1->userid = $student->id;

        $swsepindg1s2 = clone($swsepindg1s1);
        $swsepindg1s2->userid = $student2->id;

        $swsepindg2s2 = clone($swsepindg1s2);
        $swsepindg2s2->groupid = $group2->id;

        $swsepindg2s3 = clone($swsepindg1s1);
        $swsepindg2s3->userid = $student3->id;
        $swsepindg2s3->groupid = $group2->id;

        $swsepindteacher = clone($swsepindg1s1);
        $swsepindteacher->userid = $teacher->id;
        $swsepindteacher->groupid = 0;

        $swvisindg1s1 = clone($swsepindg1s1);
        $swvisindg1s1->wikiid = $wikivisind->id;

        $swvisindg1s2 = clone($swvisindg1s1);
        $swvisindg1s2->userid = $student2->id;

        $swvisindg2s2 = clone($swvisindg1s2);
        $swvisindg2s2->groupid = $group2->id;

        $swvisindg2s3 = clone($swvisindg1s1);
        $swvisindg2s3->userid = $student3->id;
        $swvisindg2s3->groupid = $group2->id;

        $swvisindteacher = clone($swvisindg1s1);
        $swvisindteacher->userid = $teacher->id;
        $swvisindteacher->groupid = 0;

                $expectedsubwikis = array($swsepindg1s1, $swsepindg1s2);
        $result = wiki_get_visible_subwikis($wikisepind);
        $this->assertEquals($expectedsubwikis, $result, '', 0, 10, true);

                $expectedsubwikis = array($swvisindg1s1, $swvisindg1s2, $swvisindg2s2, $swvisindg2s3, $swvisindteacher);
        $result = wiki_get_visible_subwikis($wikivisind);
        $this->assertEquals($expectedsubwikis, $result, '', 0, 10, true);

                $this->setUser($teacher);

                $expectedsubwikis = array($swsepindg1s1, $swsepindg1s2, $swsepindg2s2, $swsepindg2s3, $swsepindteacher);
        $result = wiki_get_visible_subwikis($wikisepind);
        $this->assertEquals($expectedsubwikis, $result, '', 0, 10, true);
    }

    public function test_mod_wiki_get_tagged_pages() {
        global $DB;

        $this->resetAfterTest();
        $this->setAdminUser();

                $wikigenerator = $this->getDataGenerator()->get_plugin_generator('mod_wiki');
        $course3 = $this->getDataGenerator()->create_course();
        $course2 = $this->getDataGenerator()->create_course();
        $course1 = $this->getDataGenerator()->create_course();
        $wiki1 = $this->getDataGenerator()->create_module('wiki', array('course' => $course1->id));
        $wiki2 = $this->getDataGenerator()->create_module('wiki', array('course' => $course2->id));
        $wiki3 = $this->getDataGenerator()->create_module('wiki', array('course' => $course3->id));
        $page11 = $wikigenerator->create_content($wiki1, array('tags' => array('Cats', 'Dogs')));
        $page12 = $wikigenerator->create_content($wiki1, array('tags' => array('Cats', 'mice')));
        $page13 = $wikigenerator->create_content($wiki1, array('tags' => array('Cats')));
        $page14 = $wikigenerator->create_content($wiki1);
        $page15 = $wikigenerator->create_content($wiki1, array('tags' => array('Cats')));
        $page21 = $wikigenerator->create_content($wiki2, array('tags' => array('Cats')));
        $page22 = $wikigenerator->create_content($wiki2, array('tags' => array('Cats', 'Dogs')));
        $page23 = $wikigenerator->create_content($wiki2, array('tags' => array('mice', 'Cats')));
        $page31 = $wikigenerator->create_content($wiki3, array('tags' => array('mice', 'Cats')));

        $tag = core_tag_tag::get_by_name(0, 'Cats');

                $res = mod_wiki_get_tagged_pages($tag, false,
                0, 0, 1, 0);
        $this->assertRegExp('/'.$page11->title.'/', $res->content);
        $this->assertRegExp('/'.$page12->title.'/', $res->content);
        $this->assertRegExp('/'.$page13->title.'/', $res->content);
        $this->assertNotRegExp('/'.$page14->title.'/', $res->content);
        $this->assertRegExp('/'.$page15->title.'/', $res->content);
        $this->assertRegExp('/'.$page21->title.'/', $res->content);
        $this->assertNotRegExp('/'.$page22->title.'/', $res->content);
        $this->assertNotRegExp('/'.$page23->title.'/', $res->content);
        $this->assertNotRegExp('/'.$page31->title.'/', $res->content);
        $this->assertEmpty($res->prevpageurl);
        $this->assertNotEmpty($res->nextpageurl);
        $res = mod_wiki_get_tagged_pages($tag, false,
                0, 0, 1, 1);
        $this->assertNotRegExp('/'.$page11->title.'/', $res->content);
        $this->assertNotRegExp('/'.$page12->title.'/', $res->content);
        $this->assertNotRegExp('/'.$page13->title.'/', $res->content);
        $this->assertNotRegExp('/'.$page14->title.'/', $res->content);
        $this->assertNotRegExp('/'.$page15->title.'/', $res->content);
        $this->assertNotRegExp('/'.$page21->title.'/', $res->content);
        $this->assertRegExp('/'.$page22->title.'/', $res->content);
        $this->assertRegExp('/'.$page23->title.'/', $res->content);
        $this->assertRegExp('/'.$page31->title.'/', $res->content);
        $this->assertNotEmpty($res->prevpageurl);
        $this->assertEmpty($res->nextpageurl);

                $student = self::getDataGenerator()->create_user();
        $studentrole = $DB->get_record('role', array('shortname' => 'student'));
        $this->getDataGenerator()->enrol_user($student->id, $course1->id, $studentrole->id, 'manual');
        $this->getDataGenerator()->enrol_user($student->id, $course2->id, $studentrole->id, 'manual');
        $this->setUser($student);
        core_tag_index_builder::reset_caches();

                $res = mod_wiki_get_tagged_pages($tag, false,
                0, 0, 1, 1);
        $this->assertRegExp('/'.$page22->title.'/', $res->content);
        $this->assertRegExp('/'.$page23->title.'/', $res->content);
        $this->assertNotRegExp('/'.$page31->title.'/', $res->content);

                $coursecontext = context_course::instance($course1->id);
        $res = mod_wiki_get_tagged_pages($tag, false,
                0, $coursecontext->id, 1, 0);
        $this->assertRegExp('/'.$page11->title.'/', $res->content);
        $this->assertRegExp('/'.$page12->title.'/', $res->content);
        $this->assertRegExp('/'.$page13->title.'/', $res->content);
        $this->assertNotRegExp('/'.$page14->title.'/', $res->content);
        $this->assertRegExp('/'.$page15->title.'/', $res->content);
        $this->assertNotRegExp('/'.$page21->title.'/', $res->content);
        $this->assertNotRegExp('/'.$page22->title.'/', $res->content);
        $this->assertNotRegExp('/'.$page23->title.'/', $res->content);
        $this->assertEmpty($res->nextpageurl);
    }
}
