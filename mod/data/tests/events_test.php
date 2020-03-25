<?php



defined('MOODLE_INTERNAL') || die();

global $CFG;

class mod_data_events_testcase extends advanced_testcase {

    
    public function setUp() {
        $this->resetAfterTest();
    }

    
    public function test_field_created() {
        $this->setAdminUser();

                $course = $this->getDataGenerator()->create_course();

                $generator = $this->getDataGenerator()->get_plugin_generator('mod_data');

                $data = $generator->create_instance(array('course' => $course->id));

                $field = data_get_field_new('text', $data);
        $fielddata = new stdClass();
        $fielddata->name = 'Test';
        $fielddata->description = 'Test description';
        $field->define_field($fielddata);

                $sink = $this->redirectEvents();
        $field->insert_field();
        $events = $sink->get_events();
        $event = reset($events);

                $this->assertInstanceOf('\mod_data\event\field_created', $event);
        $this->assertEquals(context_module::instance($data->cmid), $event->get_context());
        $expected = array($course->id, 'data', 'fields add', 'field.php?d=' . $data->id . '&amp;mode=display&amp;fid=' .
            $field->field->id, $field->field->id, $data->cmid);
        $this->assertEventLegacyLogData($expected, $event);
        $this->assertEventContextNotUsed($event);
        $url = new moodle_url('/mod/data/field.php', array('d' => $data->id));
        $this->assertEquals($url, $event->get_url());
    }

    
    public function test_field_updated() {
        $this->setAdminUser();

                $course = $this->getDataGenerator()->create_course();

                $generator = $this->getDataGenerator()->get_plugin_generator('mod_data');

                $data = $generator->create_instance(array('course' => $course->id));

                $field = data_get_field_new('text', $data);
        $fielddata = new stdClass();
        $fielddata->name = 'Test';
        $fielddata->description = 'Test description';
        $field->define_field($fielddata);
        $field->insert_field();

                $sink = $this->redirectEvents();
        $field->update_field();
        $events = $sink->get_events();
        $event = reset($events);

                $this->assertInstanceOf('\mod_data\event\field_updated', $event);
        $this->assertEquals(context_module::instance($data->cmid), $event->get_context());
        $expected = array($course->id, 'data', 'fields update', 'field.php?d=' . $data->id . '&amp;mode=display&amp;fid=' .
            $field->field->id, $field->field->id, $data->cmid);
        $this->assertEventLegacyLogData($expected, $event);
        $this->assertEventContextNotUsed($event);
        $url = new moodle_url('/mod/data/field.php', array('d' => $data->id));
        $this->assertEquals($url, $event->get_url());
    }

    
    public function test_field_deleted() {
        $this->setAdminUser();

                $course = $this->getDataGenerator()->create_course();

                $generator = $this->getDataGenerator()->get_plugin_generator('mod_data');

                $data = $generator->create_instance(array('course' => $course->id));

                $field = data_get_field_new('text', $data);
        $fielddata = new stdClass();
        $fielddata->name = 'Test';
        $fielddata->description = 'Test description';
        $field->define_field($fielddata);
        $field->insert_field();

                $sink = $this->redirectEvents();
        $field->delete_field();
        $events = $sink->get_events();
        $event = reset($events);

                $this->assertInstanceOf('\mod_data\event\field_deleted', $event);
        $this->assertEquals(context_module::instance($data->cmid), $event->get_context());
        $expected = array($course->id, 'data', 'fields delete', 'field.php?d=' . $data->id, $field->field->name, $data->cmid);
        $this->assertEventLegacyLogData($expected, $event);
        $this->assertEventContextNotUsed($event);
        $url = new moodle_url('/mod/data/field.php', array('d' => $data->id));
        $this->assertEquals($url, $event->get_url());
    }

    
    public function test_record_created() {
                $course = $this->getDataGenerator()->create_course();

                $generator = $this->getDataGenerator()->get_plugin_generator('mod_data');

                $data = $generator->create_instance(array('course' => $course->id));

                $sink = $this->redirectEvents();
        $recordid = data_add_record($data);
        $events = $sink->get_events();
        $event = reset($events);

                $this->assertInstanceOf('\mod_data\event\record_created', $event);
        $this->assertEquals(context_module::instance($data->cmid), $event->get_context());
        $expected = array($course->id, 'data', 'add', 'view.php?d=' . $data->id . '&amp;rid=' . $recordid,
            $data->id, $data->cmid);
        $this->assertEventLegacyLogData($expected, $event);
        $this->assertEventContextNotUsed($event);
        $url = new moodle_url('/mod/data/view.php', array('d' => $data->id, 'rid' => $recordid));
        $this->assertEquals($url, $event->get_url());
    }

    
    public function test_record_updated() {
                $course = $this->getDataGenerator()->create_course();

                $generator = $this->getDataGenerator()->get_plugin_generator('mod_data');

                $data = $generator->create_instance(array('course' => $course->id));

                $event = \mod_data\event\record_updated::create(array(
            'objectid' => 1,
            'context' => context_module::instance($data->cmid),
            'courseid' => $course->id,
            'other' => array(
                'dataid' => $data->id
            )
        ));

                $sink = $this->redirectEvents();
        $event->trigger();
        $events = $sink->get_events();
        $event = reset($events);

                $this->assertInstanceOf('\mod_data\event\record_updated', $event);
        $this->assertEquals(context_module::instance($data->cmid), $event->get_context());
        $expected = array($course->id, 'data', 'update', 'view.php?d=' . $data->id . '&amp;rid=1', $data->id, $data->cmid);
        $this->assertEventLegacyLogData($expected, $event);
        $this->assertEventContextNotUsed($event);
        $url = new moodle_url('/mod/data/view.php', array('d' => $data->id, 'rid' => $event->objectid));
        $this->assertEquals($url, $event->get_url());
    }

    
    public function test_record_deleted() {
        global $DB;

                $course = $this->getDataGenerator()->create_course();

                $generator = $this->getDataGenerator()->get_plugin_generator('mod_data');

                $data = $generator->create_instance(array('course' => $course->id));

                $field = data_get_field_new('text', $data);
        $fielddata = new stdClass();
        $fielddata->name = 'Test';
        $fielddata->description = 'Test description';
        $field->define_field($fielddata);
        $field->insert_field();

                $datarecords = new stdClass();
        $datarecords->userid = '2';
        $datarecords->dataid = $data->id;
        $datarecords->id = $DB->insert_record('data_records', $datarecords);

                $datacontent = new stdClass();
        $datacontent->fieldid = $field->field->id;
        $datacontent->recordid = $datarecords->id;
        $datacontent->id = $DB->insert_record('data_content', $datacontent);

                $sink = $this->redirectEvents();
        data_delete_record($datarecords->id, $data, $course->id, $data->cmid);
        $events = $sink->get_events();
        $event = reset($events);

                $this->assertInstanceOf('\mod_data\event\record_deleted', $event);
        $this->assertEquals(context_module::instance($data->cmid), $event->get_context());
        $expected = array($course->id, 'data', 'record delete', 'view.php?id=' . $data->cmid, $data->id, $data->cmid);
        $this->assertEventLegacyLogData($expected, $event);
        $this->assertEventContextNotUsed($event);
        $url = new moodle_url('/mod/data/view.php', array('d' => $data->id));
        $this->assertEquals($url, $event->get_url());
    }

    
    public function test_template_viewed() {
                $course = $this->getDataGenerator()->create_course();

                $generator = $this->getDataGenerator()->get_plugin_generator('mod_data');

                $data = $generator->create_instance(array('course' => $course->id));

                $event = \mod_data\event\template_viewed::create(array(
            'context' => context_module::instance($data->cmid),
            'courseid' => $course->id,
            'other' => array(
                'dataid' => $data->id
            )
        ));

                $sink = $this->redirectEvents();
        $event->trigger();
        $events = $sink->get_events();
        $event = reset($events);

                $this->assertInstanceOf('\mod_data\event\template_viewed', $event);
        $this->assertEquals(context_module::instance($data->cmid), $event->get_context());
        $expected = array($course->id, 'data', 'templates view', 'templates.php?id=' . $data->cmid . '&amp;d=' .
            $data->id, $data->id, $data->cmid);
        $this->assertEventLegacyLogData($expected, $event);
        $this->assertEventContextNotUsed($event);
        $url = new moodle_url('/mod/data/templates.php', array('d' => $data->id));
        $this->assertEquals($url, $event->get_url());
    }

    
    public function test_template_updated() {
                $course = $this->getDataGenerator()->create_course();

                $generator = $this->getDataGenerator()->get_plugin_generator('mod_data');

                $data = $generator->create_instance(array('course' => $course->id));

                $event = \mod_data\event\template_updated::create(array(
            'context' => context_module::instance($data->cmid),
            'courseid' => $course->id,
            'other' => array(
                'dataid' => $data->id,
            )
        ));

                $sink = $this->redirectEvents();
        $event->trigger();
        $events = $sink->get_events();
        $event = reset($events);

                $this->assertInstanceOf('\mod_data\event\template_updated', $event);
        $this->assertEquals(context_module::instance($data->cmid), $event->get_context());
        $expected = array($course->id, 'data', 'templates saved', 'templates.php?id=' . $data->cmid . '&amp;d=' .
            $data->id, $data->id, $data->cmid);
        $this->assertEventLegacyLogData($expected, $event);
        $this->assertEventContextNotUsed($event);
        $url = new moodle_url('/mod/data/templates.php', array('d' => $data->id));
        $this->assertEquals($url, $event->get_url());
    }
}
