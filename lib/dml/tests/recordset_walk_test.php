<?php



defined('MOODLE_INTERNAL') || die();


class core_recordset_walk_testcase extends advanced_testcase {

    public function setUp() {
        parent::setUp();
        $this->resetAfterTest();
    }

    public function test_no_data() {
        global $DB;

        $recordset = $DB->get_recordset('assign');
        $walker = new \core\dml\recordset_walk($recordset, array($this, 'simple_callback'));
        $this->assertFalse($walker->valid());

        $count = 0;
        foreach ($walker as $data) {
                        $count++;
        }
        $this->assertEquals(0, $count);
        $walker->close();
    }

    public function test_simple_callback() {
        global $DB;

        $generator = $this->getDataGenerator()->get_plugin_generator('mod_assign');
        $courses = array();
        for ($i = 0; $i < 10; $i++) {
            $courses[$i] = $generator->create_instance(array('course' => SITEID));
        }

                $recordset = $DB->get_recordset('assign');
        $walker = new \core\dml\recordset_walk($recordset, array($this, 'simple_callback'));

        $count = 0;
        foreach ($walker as $data) {
                        $this->assertEquals($data->id . ' potatoes', $data->newfield);
            $count++;
        }
        $this->assertEquals(10, $count);
                $walker->close();
    }

    public function test_extra_params_callback() {
        global $DB;

        $generator = $this->getDataGenerator()->get_plugin_generator('mod_assign');
        $courses = array();
        for ($i = 0; $i < 10; $i++) {
            $courses[$i] = $generator->create_instance(array('course' => SITEID));
        }

                $recordset = $DB->get_recordset('assign');

        $walker = new \core\dml\recordset_walk(
            $recordset,
            array($this, 'extra_callback'),
            array('brown' => 'onions')
        );

        $count = 0;
        foreach ($walker as $data) {
                                    $this->assertEquals('onions', $data->brown);
            $count++;
        }
        $this->assertEquals(10, $count);

        $walker->close();
    }

    
    public function simple_callback($data, $nothing = 'notpassed') {
                $this->assertEquals('notpassed', $nothing);
        $data->newfield = $data->id . ' potatoes';
        return $data;
    }

    
    public function extra_callback($data, $extra) {
        $data->brown = $extra['brown'];
        return $data;
    }
}
