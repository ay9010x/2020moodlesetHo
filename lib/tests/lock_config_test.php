<?php



defined('MOODLE_INTERNAL') || die();



class lock_config_testcase extends advanced_testcase {

    
    public function test_lock_config() {
        global $CFG;
        $original = null;
        if (isset($CFG->lock_factory)) {
            $original = $CFG->lock_factory;
        }

                unset($CFG->lock_factory);

        $factory = \core\lock\lock_config::get_lock_factory('cache');

        $this->assertNotEmpty($factory, 'Get a default factory with no configuration');

        $CFG->lock_factory = '\core\lock\file_lock_factory';

        $factory = \core\lock\lock_config::get_lock_factory('cache');
        $this->assertTrue($factory instanceof \core\lock\file_lock_factory,
                          'Get a default factory with a set configuration');

        $CFG->lock_factory = '\core\lock\db_record_lock_factory';

        $factory = \core\lock\lock_config::get_lock_factory('cache');
        $this->assertTrue($factory instanceof \core\lock\db_record_lock_factory,
                          'Get a default factory with a changed configuration');

        if ($original) {
            $CFG->lock_factory = $original;
        } else {
            unset($CFG->lock_factory);
        }
    }
}

