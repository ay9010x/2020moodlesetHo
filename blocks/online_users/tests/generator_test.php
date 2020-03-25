<?php



defined('MOODLE_INTERNAL') || die();



class block_online_users_generator_testcase extends advanced_testcase {
    public function test_generator() {
        global $DB;

        $this->resetAfterTest(true);

        $beforeblocks = $DB->count_records('block_instances');
        $beforecontexts = $DB->count_records('context');

        
        $generator = $this->getDataGenerator()->get_plugin_generator('block_online_users');
        $this->assertInstanceOf('block_online_users_generator', $generator);
        $this->assertEquals('online_users', $generator->get_blockname());

        $generator->create_instance();
        $generator->create_instance();
        $bi = $generator->create_instance();
        $this->assertEquals($beforeblocks+3, $DB->count_records('block_instances'));

    }
}
