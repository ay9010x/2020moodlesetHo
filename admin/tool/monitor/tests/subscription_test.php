<?php
defined('MOODLE_INTERNAL') || exit();


class tool_monitor_subscription_testcase extends advanced_testcase {

    
    private $subscription;

    
    public function setUp() {
        $this->resetAfterTest(true);

                $sub = new stdClass();
        $sub->id = 100;
        $sub->name = 'My test rule';
        $sub->courseid = 20;
        $this->subscription = $this->getMock('\tool_monitor\subscription',null, array($sub));
    }

    
    public function test_magic_isset() {
        $this->assertEquals(true, isset($this->subscription->name));
        $this->assertEquals(true, isset($this->subscription->courseid));
        $this->assertEquals(false, isset($this->subscription->ruleid));
    }

    
    public function test_magic_get() {
        $this->assertEquals(20, $this->subscription->courseid);
        $this->setExpectedException('coding_exception');
        $this->subscription->ruleid;
    }
}
