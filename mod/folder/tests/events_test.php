<?php



defined('MOODLE_INTERNAL') || die();

class mod_folder_events_testcase extends advanced_testcase {

    
    public function setUp() {
        $this->resetAfterTest();
    }

    
    public function test_folder_updated() {
        $this->setAdminUser();
        $course = $this->getDataGenerator()->create_course();
        $folder = $this->getDataGenerator()->create_module('folder', array('course' => $course->id));

        $params = array(
            'context' => context_module::instance($folder->cmid),
            'objectid' => $folder->id,
            'courseid' => $course->id
        );
        $event = \mod_folder\event\folder_updated::create($params);
        $event->add_record_snapshot('folder', $folder);

                $sink = $this->redirectEvents();
        $event->trigger();
        $events = $sink->get_events();
        $this->assertCount(1, $events);
        $event = reset($events);

                $this->assertInstanceOf('\mod_folder\event\folder_updated', $event);
        $this->assertEquals(context_module::instance($folder->cmid), $event->get_context());
        $this->assertEquals($folder->id, $event->objectid);
        $expected = array($course->id, 'folder', 'edit', 'edit.php?id=' . $folder->cmid, $folder->id, $folder->cmid);
        $this->assertEventLegacyLogData($expected, $event);
        $this->assertEventContextNotUsed($event);
    }

    
    public function test_all_files_downloaded() {
        $this->setAdminUser();
        $course = $this->getDataGenerator()->create_course();
        $folder = $this->getDataGenerator()->create_module('folder', array('course' => $course->id));
        $context = context_module::instance($folder->cmid);
        $cm = get_coursemodule_from_id('folder', $folder->cmid, $course->id, true, MUST_EXIST);

        $sink = $this->redirectEvents();
        folder_downloaded($folder, $course, $cm, $context);
        $events = $sink->get_events();
        $this->assertCount(1, $events);
        $event = reset($events);

                $this->assertInstanceOf('\mod_folder\event\all_files_downloaded', $event);
        $this->assertEquals(context_module::instance($folder->cmid), $event->get_context());
        $this->assertEquals($folder->id, $event->objectid);
        $expected = array($course->id, 'folder', 'edit', 'edit.php?id=' . $folder->cmid, $folder->id, $folder->cmid);
        $this->assertEventContextNotUsed($event);
    }
}
