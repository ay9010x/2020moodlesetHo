<?php



defined('MOODLE_INTERNAL') || die();

require_once(__DIR__.'/fixtures/event_fixtures.php');

class core_event_unknown_logged_testcase extends advanced_testcase {

    public function test_restore_event() {
        $event1 = \core_tests\event\unittest_executed::create(array('context' => context_system::instance(), 'other' => array('sample' => 1, 'xx' => 10)));
        $data1 = $event1->get_data();

        $data1['eventname'] = '\mod_xx\event\xx_yy';
        $data1['component'] = 'mod_xx';
        $data1['action'] = 'yy';
        $data1['target'] = 'xx';
        $extra1 = array('origin' => 'cli');

        $event2 = \core\event\base::restore($data1, $extra1);
        $data2 = $event2->get_data();
        $extra2 = $event2->get_logextra();

        $this->assertInstanceOf('core\event\unknown_logged', $event2);
        $this->assertTrue($event2->is_triggered());
        $this->assertTrue($event2->is_restored());
        $this->assertNull($event2->get_url());
        $this->assertEquals($data1, $data2);
        $this->assertEquals($extra1, $extra2);
    }
}