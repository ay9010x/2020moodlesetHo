<?php



defined('MOODLE_INTERNAL') || die();
global $CFG;
require_once($CFG->dirroot.'/mod/wiki/locallib.php');

class mod_wiki_events_testcase extends advanced_testcase {
    private $course;
    private $wiki;
    private $wikigenerator;
    private $student;
    private $teacher;

    
    public function setUp() {
        global $DB;

        $this->resetAfterTest();
                $this->course = $this->getDataGenerator()->create_course();
        $this->wiki = $this->getDataGenerator()->create_module('wiki', array('course' => $this->course->id));
        $this->wikigenerator = $this->getDataGenerator()->get_plugin_generator('mod_wiki');

                $studentrole = $DB->get_record('role', array('shortname' => 'student'));
        $teacherrole = $DB->get_record('role', array('shortname' => 'teacher'));
        $this->student = $this->getDataGenerator()->create_user();
        $this->teacher = $this->getDataGenerator()->create_user();
        $this->getDataGenerator()->enrol_user($this->student->id, $this->course->id, $studentrole->id);
        $this->getDataGenerator()->enrol_user($this->student->id, $this->course->id, $teacherrole->id);
        $this->setAdminUser();
    }

    
    public function test_comment_created() {
        $this->setUp();

        $page = $this->wikigenerator->create_first_page($this->wiki);
        $context = context_module::instance($this->wiki->cmid);

                $sink = $this->redirectEvents();
        wiki_add_comment($context, $page->id, 'Test comment', $this->wiki->defaultformat);
        $events = $sink->get_events();
        $this->assertCount(1, $events);
        $event = reset($events);

                $this->assertInstanceOf('\mod_wiki\event\comment_created', $event);
        $this->assertEquals($context, $event->get_context());
        $this->assertEquals($page->id, $event->other['itemid']);
        $this->assertEventContextNotUsed($event);
    }

    
    public function test_comment_deleted() {
        $this->setUp();

        $page = $this->wikigenerator->create_first_page($this->wiki);
        $context = context_module::instance($this->wiki->cmid);

                wiki_add_comment($context, $page->id, 'Test comment', 'html');
        $comment = wiki_get_comments($context->id, $page->id);
        $this->assertCount(1, $comment);
        $comment = array_shift($comment);

                $sink = $this->redirectEvents();
        wiki_delete_comment($comment->id, $context, $page->id);
        $events = $sink->get_events();
        $this->assertCount(1, $events);
        $event = reset($events);

                $this->assertInstanceOf('\mod_wiki\event\comment_deleted', $event);
        $this->assertEquals($context, $event->get_context());
        $this->assertEquals($page->id, $event->other['itemid']);
        $this->assertEventContextNotUsed($event);
    }

    
    public function test_comment_viewed() {
                
        $this->setUp();
        $page = $this->wikigenerator->create_first_page($this->wiki);
        $context = context_module::instance($this->wiki->cmid);

        $params = array(
                'context' => $context,
                'objectid' => $page->id
                );
        $event = \mod_wiki\event\comments_viewed::create($params);

                $sink = $this->redirectEvents();
        $event->trigger();
        $events = $sink->get_events();
        $this->assertCount(1, $events);
        $event = reset($events);

                $this->assertInstanceOf('\mod_wiki\event\comments_viewed', $event);
        $this->assertEquals($context, $event->get_context());
        $expected = array($this->course->id, 'wiki', 'comments', 'comments.php?pageid=' . $page->id, $page->id, $this->wiki->cmid);
        $this->assertEventLegacyLogData($expected, $event);
        $this->assertEventContextNotUsed($event);
    }

    
    public function test_course_module_instance_list_viewed() {
                
        $this->setUp();
        $context = context_course::instance($this->course->id);

        $params = array('context' => $context);
        $event = \mod_wiki\event\course_module_instance_list_viewed::create($params);

                $sink = $this->redirectEvents();
        $event->trigger();
        $events = $sink->get_events();
        $this->assertCount(1, $events);
        $event = reset($events);

                $this->assertInstanceOf('\mod_wiki\event\course_module_instance_list_viewed', $event);
        $this->assertEquals($context, $event->get_context());
        $expected = array($this->course->id, 'wiki', 'view all', 'index.php?id=' . $this->course->id, '');
        $this->assertEventLegacyLogData($expected, $event);
        $this->assertEventContextNotUsed($event);
    }

    
    public function test_course_module_viewed() {
                
        $this->setUp();
        $context = context_module::instance($this->wiki->cmid);

        $params = array(
                'context' => $context,
                'objectid' => $this->wiki->id
                );
        $event = \mod_wiki\event\course_module_viewed::create($params);

                $sink = $this->redirectEvents();
        $event->trigger();
        $events = $sink->get_events();
        $this->assertCount(1, $events);
        $event = reset($events);

                $this->assertInstanceOf('\mod_wiki\event\course_module_viewed', $event);
        $this->assertEquals($context, $event->get_context());
        $this->assertEquals($this->wiki->id, $event->objectid);
        $expected = array($this->course->id, 'wiki', 'view', 'view.php?id=' . $this->wiki->cmid,
            $this->wiki->id, $this->wiki->cmid);
        $this->assertEventLegacyLogData($expected, $event);
        $this->assertEventContextNotUsed($event);
    }

    
    public function test_page_viewed() {
                
        $this->setUp();

        $page = $this->wikigenerator->create_first_page($this->wiki);
        $context = context_module::instance($this->wiki->cmid);

        $params = array(
                'context' => $context,
                'objectid' => $page->id
                );
        $event = \mod_wiki\event\page_viewed::create($params);

                $sink = $this->redirectEvents();
        $event->trigger();
        $events = $sink->get_events();
        $this->assertCount(1, $events);
        $event = reset($events);

                $this->assertInstanceOf('\mod_wiki\event\page_viewed', $event);
        $this->assertEquals($context, $event->get_context());
        $this->assertEquals($page->id, $event->objectid);
        $expected = array($this->course->id, 'wiki', 'view', 'view.php?pageid=' . $page->id, $page->id, $this->wiki->cmid);
        $this->assertEventLegacyLogData($expected, $event);
        $this->assertEventContextNotUsed($event);
    }

    
    public function test_pretty_page_viewed() {
                
        $this->setUp();

        $page = $this->wikigenerator->create_first_page($this->wiki);
        $context = context_module::instance($this->wiki->cmid);

        $params = array(
                'context' => $context,
                'objectid' => $page->id,
                'other' => array('prettyview' => true)
                );
        $event = \mod_wiki\event\page_viewed::create($params);

                $sink = $this->redirectEvents();
        $event->trigger();
        $events = $sink->get_events();
        $this->assertCount(1, $events);
        $event = reset($events);

                $this->assertInstanceOf('\mod_wiki\event\page_viewed', $event);
        $this->assertEquals($context, $event->get_context());
        $this->assertEquals($page->id, $event->objectid);
        $expected = array($this->course->id, 'wiki', 'view', 'prettyview.php?pageid=' . $page->id, $page->id, $this->wiki->cmid);
        $this->assertEventLegacyLogData($expected, $event);
        $this->assertEventContextNotUsed($event);
    }

    
    public function test_page_created() {
        global $USER;

        $this->setUp();

        $context = context_module::instance($this->wiki->cmid);

                $sink = $this->redirectEvents();
        $page = $this->wikigenerator->create_first_page($this->wiki);
        $events = $sink->get_events();
        $this->assertCount(2, $events);
        $event = reset($events);

                $this->assertInstanceOf('\mod_wiki\event\page_created', $event);
        $this->assertEquals($context, $event->get_context());
        $this->assertEquals($page->id, $event->objectid);
        $expected = array($this->course->id, 'wiki', 'add page',
            'view.php?pageid=' . $page->id, $page->id, $this->wiki->cmid);
        $this->assertEventLegacyLogData($expected, $event);
        $this->assertEventContextNotUsed($event);
    }

    
    public function test_page_deleted() {
        global $DB;

        $this->setUp();

        $page = $this->wikigenerator->create_first_page($this->wiki);
        $context = context_module::instance($this->wiki->cmid);
        $oldversions = $DB->get_records('wiki_versions', array('pageid' => $page->id));
        $oldversion = array_shift($oldversions);

                $sink = $this->redirectEvents();
        wiki_delete_pages($context, array($page->id));
        $events = $sink->get_events();
        $this->assertCount(4, $events);
        $event = array_shift($events);

                $this->assertInstanceOf('\mod_wiki\event\page_version_deleted', $event);
        $this->assertEquals($context, $event->get_context());
        $this->assertEquals($page->id, $event->other['pageid']);
        $this->assertEquals($oldversion->id, $event->objectid);
        $expected = array($this->course->id, 'wiki', 'admin', 'admin.php?pageid=' .  $page->id,  $page->id, $this->wiki->cmid);
        $this->assertEventLegacyLogData($expected, $event);

                $event = array_pop($events);
        $this->assertInstanceOf('\mod_wiki\event\page_deleted', $event);
        $this->assertEquals($context, $event->get_context());
        $this->assertEquals($page->id, $event->objectid);
        $expected = array($this->course->id, 'wiki', 'admin', 'admin.php?pageid=' . $page->id, $page->id, $this->wiki->cmid);
        $this->assertEventLegacyLogData($expected, $event);

                $event = array_pop($events);
        $this->assertInstanceOf('\mod_wiki\event\page_locks_deleted', $event);
        $this->assertEquals($context, $event->get_context());
        $this->assertEquals($page->id, $event->objectid);
        $expected = array($this->course->id, 'wiki', 'overridelocks', 'view.php?pageid=' . $page->id, $page->id, $this->wiki->cmid);
        $this->assertEventLegacyLogData($expected, $event);

                $page1 = $this->wikigenerator->create_first_page($this->wiki);
        $page2 = $this->wikigenerator->create_content($this->wiki);
        $page3 = $this->wikigenerator->create_content($this->wiki, array('title' => 'Custom title'));

                $sink = $this->redirectEvents();
        wiki_delete_pages($context, array($page1->id, $page2->id));
        $events = $sink->get_events();
        $this->assertCount(8, $events);
        $event = array_pop($events);

                $this->assertInstanceOf('\mod_wiki\event\page_deleted', $event);
        $this->assertEquals($context, $event->get_context());
        $this->assertEquals($page2->id, $event->objectid);
        $expected = array($this->course->id, 'wiki', 'admin', 'admin.php?pageid=' . $page2->id, $page2->id, $this->wiki->cmid);
        $this->assertEventLegacyLogData($expected, $event);
        $this->assertEventContextNotUsed($event);
    }

    
    public function test_page_updated() {
        global $USER;

        $this->setUp();

        $page = $this->wikigenerator->create_first_page($this->wiki);
        $context = context_module::instance($this->wiki->cmid);

                $sink = $this->redirectEvents();
        wiki_save_page($page, 'New content', $USER->id);
        $events = $sink->get_events();
        $this->assertCount(1, $events);
        $event = reset($events);

                $this->assertInstanceOf('\mod_wiki\event\page_updated', $event);
        $this->assertEquals($context, $event->get_context());
        $this->assertEquals($page->id, $event->objectid);
        $expected = array($this->course->id, 'wiki', 'edit',
            'view.php?pageid=' . $page->id, $page->id, $this->wiki->cmid);
        $this->assertEventLegacyLogData($expected, $event);
        $this->assertEventContextNotUsed($event);
    }

    
    public function test_page_diff_viewed() {
                
        $this->setUp();

        $page = $this->wikigenerator->create_first_page($this->wiki);
        $context = context_module::instance($this->wiki->cmid);

        $params = array(
                'context' => $context,
                'objectid' => $page->id,
                'other' => array(
                    'comparewith' => 1,
                    'compare' => 2
                    )
                );
        $event = \mod_wiki\event\page_diff_viewed::create($params);

                $sink = $this->redirectEvents();
        $event->trigger();
        $events = $sink->get_events();
        $this->assertCount(1, $events);
        $event = reset($events);

                $this->assertInstanceOf('\mod_wiki\event\page_diff_viewed', $event);
        $this->assertEquals($context, $event->get_context());
        $this->assertEquals($page->id, $event->objectid);
        $expected = array($this->course->id, 'wiki', 'diff', 'diff.php?pageid=' . $page->id . '&comparewith=' .
            1 . '&compare=' .  2, $page->id, $this->wiki->cmid);
        $this->assertEventLegacyLogData($expected, $event);
        $this->assertEventContextNotUsed($event);
    }

    
    public function test_page_history_viewed() {
                
        $this->setUp();

        $page = $this->wikigenerator->create_first_page($this->wiki);
        $context = context_module::instance($this->wiki->cmid);

        $params = array(
                'context' => $context,
                'objectid' => $page->id
                );
        $event = \mod_wiki\event\page_history_viewed::create($params);

                $sink = $this->redirectEvents();
        $event->trigger();
        $events = $sink->get_events();
        $this->assertCount(1, $events);
        $event = reset($events);

                $this->assertInstanceOf('\mod_wiki\event\page_history_viewed', $event);
        $this->assertEquals($context, $event->get_context());
        $this->assertEquals($page->id, $event->objectid);
        $expected = array($this->course->id, 'wiki', 'history', 'history.php?pageid=' . $page->id, $page->id, $this->wiki->cmid);
        $this->assertEventLegacyLogData($expected, $event);
        $this->assertEventContextNotUsed($event);
    }

    
    public function test_page_map_viewed() {
                
        $this->setUp();

        $page = $this->wikigenerator->create_first_page($this->wiki);
        $context = context_module::instance($this->wiki->cmid);

        $params = array(
                'context' => $context,
                'objectid' => $page->id,
                'other' => array(
                    'option' => 0
                    )
                );
        $event = \mod_wiki\event\page_map_viewed::create($params);

                $sink = $this->redirectEvents();
        $event->trigger();
        $events = $sink->get_events();
        $this->assertCount(1, $events);
        $event = reset($events);

                $this->assertInstanceOf('\mod_wiki\event\page_map_viewed', $event);
        $this->assertEquals($context, $event->get_context());
        $this->assertEquals($page->id, $event->objectid);
        $this->assertEquals(0, $event->other['option']);
        $expected = array($this->course->id, 'wiki', 'map', 'map.php?pageid=' . $page->id, $page->id, $this->wiki->cmid);
        $this->assertEventLegacyLogData($expected, $event);
        $this->assertEventContextNotUsed($event);
    }

    
    public function test_page_version_viewed() {
                
        $this->setUp();

        $page = $this->wikigenerator->create_first_page($this->wiki);
        $context = context_module::instance($this->wiki->cmid);

        $params = array(
                'context' => $context,
                'objectid' => $page->id,
                'other' => array(
                    'versionid' => 1
                    )
                );
        $event = \mod_wiki\event\page_version_viewed::create($params);

                $sink = $this->redirectEvents();
        $event->trigger();
        $events = $sink->get_events();
        $this->assertCount(1, $events);
        $event = reset($events);

                $this->assertInstanceOf('\mod_wiki\event\page_version_viewed', $event);
        $this->assertEquals($context, $event->get_context());
        $this->assertEquals($page->id, $event->objectid);
        $this->assertEquals(1, $event->other['versionid']);
        $expected = array($this->course->id, 'wiki', 'history', 'viewversion.php?pageid=' . $page->id . '&versionid=1',
            $page->id, $this->wiki->cmid);
        $this->assertEventLegacyLogData($expected, $event);
        $this->assertEventContextNotUsed($event);
    }

    
    public function test_page_version_restored() {
        $this->setUp();

        $page = $this->wikigenerator->create_first_page($this->wiki);
        $context = context_module::instance($this->wiki->cmid);
        $version = wiki_get_current_version($page->id);

                $sink = $this->redirectEvents();
        wiki_restore_page($page, $version, $context);
        $events = $sink->get_events();
        $this->assertCount(2, $events);
        $event = array_pop($events);

                $this->assertInstanceOf('\mod_wiki\event\page_version_restored', $event);
        $this->assertEquals($context, $event->get_context());
        $this->assertEquals($version->id, $event->objectid);
        $this->assertEquals($page->id, $event->other['pageid']);
        $expected = array($this->course->id, 'wiki', 'restore', 'view.php?pageid=' . $page->id, $page->id, $this->wiki->cmid);
        $this->assertEventLegacyLogData($expected, $event);
        $this->assertEventContextNotUsed($event);
    }
}
