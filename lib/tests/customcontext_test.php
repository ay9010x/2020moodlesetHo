<?php



defined('MOODLE_INTERNAL') || die();


class context_bogus1 extends context {
    
    public function get_url() {
        global $ME;
        return $ME;
    }

    
    public function get_capabilities() {
        return array();
    }
}


class context_bogus2 extends context {
    
    public function get_url() {
        global $ME;
        return $ME;
    }

    
    public function get_capabilities() {
        return array();
    }
}


class context_bogus3 extends context {
    
    public function get_url() {
        global $ME;
        return $ME;
    }

    
    public function get_capabilities() {
        return array();
    }
}

class customcontext_testcase extends advanced_testcase {

    
    protected function setUp() {
        parent::setUp();
        $this->resetAfterTest(true);
    }

    
    public function test_customcontexts() {
        global $CFG;
        static $customcontexts = array(
            11 => 'context_bogus1',
            12 => 'context_bogus2',
            13 => 'context_bogus3'
        );

                $existingcustomcontexts = get_config(null, 'custom_context_classes');

        set_config('custom_context_classes', serialize($customcontexts));
        initialise_cfg();
        context_helper::reset_levels();
        $alllevels = context_helper::get_all_levels();
        $this->assertEquals($alllevels[11], 'context_bogus1');
        $this->assertEquals($alllevels[12], 'context_bogus2');
        $this->assertEquals($alllevels[13], 'context_bogus3');

                set_config('custom_context_classes', ($existingcustomcontexts === false) ? null : $existingcustomcontexts);
        initialise_cfg();
        context_helper::reset_levels();
    }
}
