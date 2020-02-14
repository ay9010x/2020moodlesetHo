<?php



defined('MOODLE_INTERNAL') || die();

require_once(__DIR__.'/fixtures/event_fixtures.php');

class core_events_testcase extends advanced_testcase {

    
    public function setUp() {
        $this->resetAfterTest();
    }

    
    public function test_course_category_created() {
                $sink = $this->redirectEvents();
        $category = $this->getDataGenerator()->create_category();
        $events = $sink->get_events();
        $event = reset($events);

                $this->assertInstanceOf('\core\event\course_category_created', $event);
        $this->assertEquals(context_coursecat::instance($category->id), $event->get_context());
        $url = new moodle_url('/course/management.php', array('categoryid' => $event->objectid));
        $this->assertEquals($url, $event->get_url());
        $expected = array(SITEID, 'category', 'add', 'editcategory.php?id=' . $category->id, $category->id);
        $this->assertEventLegacyLogData($expected, $event);
        $this->assertEventContextNotUsed($event);
    }

    
    public function test_course_category_updated() {
                $category = $this->getDataGenerator()->create_category();

                $data = new stdClass();
        $data->name = 'Category name change';

                $sink = $this->redirectEvents();
        $category->update($data);
        $events = $sink->get_events();
        $event = reset($events);

                $this->assertInstanceOf('\core\event\course_category_updated', $event);
        $this->assertEquals(context_coursecat::instance($category->id), $event->get_context());
        $url = new moodle_url('/course/editcategory.php', array('id' => $event->objectid));
        $this->assertEquals($url, $event->get_url());
        $expected = array(SITEID, 'category', 'update', 'editcategory.php?id=' . $category->id, $category->id);
        $this->assertEventLegacyLogData($expected, $event);

                $category2 = $this->getDataGenerator()->create_category();
        $childcat = $this->getDataGenerator()->create_category(array('parent' => $category2->id));

                $sink = $this->redirectEvents();
        $childcat->change_parent($category);
        $events = $sink->get_events();
        $event = reset($events);

                $this->assertInstanceOf('\core\event\course_category_updated', $event);
        $this->assertEquals(context_coursecat::instance($childcat->id), $event->get_context());
        $expected = array(SITEID, 'category', 'move', 'editcategory.php?id=' . $childcat->id, $childcat->id);
        $this->assertEventLegacyLogData($expected, $event);

                $sink = $this->redirectEvents();
        $category2->change_sortorder_by_one(true);
        $events = $sink->get_events();
        $event = reset($events);

                $this->assertInstanceOf('\core\event\course_category_updated', $event);
        $this->assertEquals(context_coursecat::instance($category2->id), $event->get_context());
        $expected = array(SITEID, 'category', 'move', 'management.php?categoryid=' . $category2->id, $category2->id);
        $this->assertEventLegacyLogData($expected, $event);

                $sink = $this->redirectEvents();
        $category->delete_move($category->id);
        $events = $sink->get_events();
        $event = reset($events);

                $this->assertInstanceOf('\core\event\course_category_updated', $event);
        $this->assertEquals(context_coursecat::instance($childcat->id), $event->get_context());
        $expected = array(SITEID, 'category', 'move', 'editcategory.php?id=' . $childcat->id, $childcat->id);
        $this->assertEventLegacyLogData($expected, $event);

                $sink = $this->redirectEvents();
        $category2->hide();
        $events = $sink->get_events();
        $event = reset($events);

                $this->assertInstanceOf('\core\event\course_category_updated', $event);
        $this->assertEquals(context_coursecat::instance($category2->id), $event->get_context());
        $expected = array(SITEID, 'category', 'hide', 'editcategory.php?id=' . $category2->id, $category2->id);
        $this->assertEventLegacyLogData($expected, $event);

                $sink = $this->redirectEvents();
        $category2->show();
        $events = $sink->get_events();
        $event = reset($events);

                $this->assertInstanceOf('\core\event\course_category_updated', $event);
        $this->assertEquals(context_coursecat::instance($category2->id), $event->get_context());
        $expected = array(SITEID, 'category', 'show', 'editcategory.php?id=' . $category2->id, $category2->id);
        $this->assertEventLegacyLogData($expected, $event);
        $this->assertEventContextNotUsed($event);
    }

    
    public function test_email_failed() {
                $event = \core\event\email_failed::create(array(
            'context' => context_system::instance(),
            'userid' => 1,
            'relateduserid' => 2,
            'other' => array(
                'subject' => 'This is a subject',
                'message' => 'This is a message',
                'errorinfo' => 'The email failed to send!'
            )
        ));

                $sink = $this->redirectEvents();
        $event->trigger();
        $events = $sink->get_events();
        $event = reset($events);

        $this->assertInstanceOf('\core\event\email_failed', $event);
        $this->assertEquals(context_system::instance(), $event->get_context());
        $expected = array(SITEID, 'library', 'mailer', qualified_me(), 'ERROR: The email failed to send!');
        $this->assertEventLegacyLogData($expected, $event);
        $this->assertEventContextNotUsed($event);
    }

    
    public function test_course_user_report_viewed() {

        $user = $this->getDataGenerator()->create_user();
        $course = $this->getDataGenerator()->create_course();
        $context = context_course::instance($course->id);

        $eventparams = array();
        $eventparams['context'] = $context;
        $eventparams['relateduserid'] = $user->id;
        $eventparams['other'] = array();
        $eventparams['other']['mode'] = 'grade';
        $event = \core\event\course_user_report_viewed::create($eventparams);

                $sink = $this->redirectEvents();
        $event->trigger();
        $events = $sink->get_events();
        $event = reset($events);

        $this->assertInstanceOf('\core\event\course_user_report_viewed', $event);
        $this->assertEquals(context_course::instance($course->id), $event->get_context());
        $expected = array($course->id, 'course', 'user report', 'user.php?id=' . $course->id . '&amp;user='
                . $user->id . '&amp;mode=grade', $user->id);
        $this->assertEventLegacyLogData($expected, $event);
        $this->assertEventContextNotUsed($event);
    }

    
    public function test_course_viewed() {

        $user = $this->getDataGenerator()->create_user();
        $course = $this->getDataGenerator()->create_course();
        $context = context_course::instance($course->id);

                $eventparams = array();
        $eventparams['context'] = $context;
        $event = \core\event\course_viewed::create($eventparams);

                $sink = $this->redirectEvents();
        $event->trigger();
        $events = $sink->get_events();
        $event = reset($events);

        $this->assertInstanceOf('\core\event\course_viewed', $event);
        $this->assertEquals(context_course::instance($course->id), $event->get_context());
        $expected = array($course->id, 'course', 'view', 'view.php?id=' . $course->id, $course->id);
        $this->assertEventLegacyLogData($expected, $event);
        $this->assertEventContextNotUsed($event);

                $sectionnumber = 7;
        $eventparams = array();
        $eventparams['context'] = $context;
        $eventparams['other'] = array('coursesectionnumber' => $sectionnumber);
        $event = \core\event\course_viewed::create($eventparams);

                $sink = $this->redirectEvents();
        $event->trigger();
        $loggeddata = $event->get_data();
        $events = $sink->get_events();
        $event = reset($events);


        $this->assertInstanceOf('\core\event\course_viewed', $event);
        $this->assertEquals(context_course::instance($course->id), $event->get_context());
        $expected = array($course->id, 'course', 'view section', 'view.php?id=' . $course->id . '&amp;section='
                . $sectionnumber, $sectionnumber);
        $this->assertEventLegacyLogData($expected, $event);
        $this->assertEventContextNotUsed($event);

        delete_course($course->id, false);
        $restored = \core\event\base::restore($loggeddata, array('origin' => 'web', 'ip' => '127.0.0.1'));
        $this->assertInstanceOf('\core\event\course_viewed', $restored);
        $this->assertNull($restored->get_url());
    }

    public function test_recent_capability_viewed() {
        $this->resetAfterTest();

        $this->setAdminUser();
        $course = $this->getDataGenerator()->create_course();
        $context = context_course::instance($course->id);

        $event = \core\event\recent_activity_viewed::create(array('context' => $context));

                $sink = $this->redirectEvents();
        $event->trigger();
        $events = $sink->get_events();
        $event = reset($events);

        $this->assertInstanceOf('\core\event\recent_activity_viewed', $event);
        $this->assertEquals($context, $event->get_context());
        $expected = array($course->id, "course", "recent", "recent.php?id=$course->id", $course->id);
        $this->assertEventLegacyLogData($expected, $event);
        $this->assertEventContextNotUsed($event);
        $url = new moodle_url('/course/recent.php', array('id' => $course->id));
        $this->assertEquals($url, $event->get_url());
        $event->get_name();
    }

    public function test_user_profile_viewed() {
        $this->resetAfterTest();
        $this->setAdminUser();

        $user = $this->getDataGenerator()->create_user();
        $course = $this->getDataGenerator()->create_course();
        $coursecontext = context_course::instance($course->id);

                $eventparams = array(
            'objectid' => $user->id,
            'relateduserid' => $user->id,
            'courseid' => $course->id,
            'context' => $coursecontext,
            'other' => array(
                'courseid' => $course->id,
                'courseshortname' => $course->shortname,
                'coursefullname' => $course->fullname
            )
        );
        $event = \core\event\user_profile_viewed::create($eventparams);

                $sink = $this->redirectEvents();
        $event->trigger();
        $events = $sink->get_events();
        $event = reset($events);

        $this->assertInstanceOf('\core\event\user_profile_viewed', $event);
        $log = array($course->id, 'user', 'view', 'view.php?id=' . $user->id . '&course=' . $course->id, $user->id);
        $this->assertEventLegacyLogData($log, $event);
        $this->assertEventContextNotUsed($event);

                $usercontext = context_user::instance($user->id);
        $eventparams['context'] = $usercontext;
        unset($eventparams['courseid'], $eventparams['other']);
        $event = \core\event\user_profile_viewed::create($eventparams);

                $sink = $this->redirectEvents();
        $event->trigger();
        $events = $sink->get_events();
        $event = reset($events);

        $this->assertInstanceOf('\core\event\user_profile_viewed', $event);
        $expected = null;
        $this->assertEventLegacyLogData($expected, $event);
        $this->assertEventContextNotUsed($event);
    }

    
    public function test_grade_viewed() {
        $this->resetAfterTest();
        $this->setAdminUser();

        $user = $this->getDataGenerator()->create_user();
        $course = $this->getDataGenerator()->create_course();
        $coursecontext = context_course::instance($course->id);

        $event = \core_tests\event\grade_report_viewed::create(
            array(
                'context' => $coursecontext,
                'courseid' => $course->id,
                'userid' => $user->id,
            )
        );

                $sink = $this->redirectEvents();
        $event->trigger();
        $events = $sink->get_events();
        $event = reset($events);

        $this->assertInstanceOf('\core\event\grade_report_viewed', $event);
        $this->assertEquals($event->courseid, $course->id);
        $this->assertEquals($event->userid, $user->id);
        $this->assertEventContextNotUsed($event);
    }
}
