<?php



defined('MOODLE_INTERNAL') || die();


class core_progress_testcase extends basic_testcase {

    
    public function test_basic() {
        $progress = new core_mock_progress();

                $this->assertFalse($progress->is_in_progress_section());

                        $progress->start_progress('hello', 10);
        $this->assertTrue($progress->was_update_called());
        $this->assertTrue($progress->is_in_progress_section());
        $this->assertEquals('hello', $progress->get_current_description());

                $this->assert_min_max(0.0, 0.0, $progress);
        $this->assertEquals(0, $progress->get_progress_count());

                $progress->step_time();
        core_php_time_limit::get_and_clear_unit_test_data();
        $progress->progress(2);
        $this->assertTrue($progress->was_update_called());
        $this->assertEquals(array(\core\progress\base::TIME_LIMIT_WITHOUT_PROGRESS),
                core_php_time_limit::get_and_clear_unit_test_data());

                $this->assert_min_max(0.2, 0.2, $progress);

                $progress->progress(3);
        $this->assertFalse($progress->was_update_called());
        $this->assert_min_max(0.3, 0.3, $progress);

                $progress->end_progress();
        $this->assertTrue($progress->was_update_called());

                $this->assert_min_max(1.0, 1.0, $progress);

                $this->assertEquals(1, $progress->get_progress_count());
    }

    
    public function test_nested() {
                $progress = new core_mock_progress();
        $progress->start_progress('hello', 10);

                $progress->step_time();
        $progress->progress(4);
        $this->assert_min_max(0.4, 0.4, $progress);
        $this->assertEquals('hello', $progress->get_current_description());

                $progress->start_progress('world');
        $this->assert_min_max(0.4, 0.5, $progress);
        $this->assertEquals('world', $progress->get_current_description());

                $progress->step_time();
        $progress->progress();
        $this->assertEquals(2, $progress->get_progress_count());
        $progress->progress();
        $this->assertEquals(2, $progress->get_progress_count());
        $progress->step_time();
        $progress->progress();
        $this->assertEquals(3, $progress->get_progress_count());
        $this->assert_min_max(0.4, 0.5, $progress);

                $progress->end_progress();
        $this->assert_min_max(0.5, 0.5, $progress);

        $progress->step_time();
        $progress->progress(7);
        $this->assert_min_max(0.7, 0.7, $progress);

                $progress->start_progress('frogs', 5);
        $this->assert_min_max(0.7, 0.7, $progress);
        $progress->step_time();
        $progress->progress(1);
        $this->assert_min_max(0.72, 0.72, $progress);
        $progress->step_time();
        $progress->progress(3);
        $this->assert_min_max(0.76, 0.76, $progress);

                $progress->start_progress('and');
        $this->assert_min_max(0.76, 0.78, $progress);

                $progress->step_time();
        $progress->progress();
        $this->assertEquals(7, $progress->get_progress_count());

                $progress->start_progress('zombies', 2);
        $progress->step_time();
        $progress->progress(1);
        $this->assert_min_max(0.76, 0.78, $progress);
        $this->assertEquals(8, $progress->get_progress_count());

                $progress->end_progress();

                $progress->end_progress();
        $this->assert_min_max(0.78, 0.78, $progress);

                $progress->end_progress();
        $this->assert_min_max(0.8, 0.8, $progress);
        $progress->end_progress();
        $this->assertFalse($progress->is_in_progress_section());
    }

    
    public function test_nested_weighted() {
        $progress = new core_mock_progress();
        $progress->start_progress('', 10);

                $progress->start_progress('', 2);
        $progress->step_time();
        $progress->progress(1);
        $this->assert_min_max(0.05, 0.05, $progress);
        $progress->end_progress();
        $this->assert_min_max(0.1, 0.1, $progress);

                $progress->start_progress('weighted', 2, 3);
        $progress->step_time();
        $progress->progress(1);
        $this->assert_min_max(0.25, 0.25, $progress);
        $progress->end_progress();
        $this->assert_min_max(0.4, 0.4, $progress);

                $progress->start_progress('', \core\progress\base::INDETERMINATE, 6);
        $progress->step_time();
        $progress->progress();
        $this->assert_min_max(0.4, 1.0, $progress);
        $progress->end_progress();
        $this->assert_min_max(1.0, 1.0, $progress);
    }

    
    public function test_realistic() {
        $progress = new core_mock_progress();
        $progress->start_progress('parent', 100);
        $progress->start_progress('child', 1);
        $progress->progress(1);
        $this->assert_min_max(0.01, 0.01, $progress);
        $progress->end_progress();
        $this->assert_min_max(0.01, 0.01, $progress);
    }

    
    public function test_zero() {
        $progress = new core_mock_progress();
        $progress->start_progress('parent', 100);
        $progress->progress(1);
        $this->assert_min_max(0.01, 0.01, $progress);
        $progress->start_progress('child', 0);

                        $this->assert_min_max(0.02, 0.02, $progress);
        $progress->progress(0);
        $this->assert_min_max(0.02, 0.02, $progress);
        $progress->end_progress();
        $this->assert_min_max(0.02, 0.02, $progress);
    }

    
    public function test_exceptions() {
        $progress = new core_mock_progress();

                try {
            $progress->progress();
            $this->fail();
        } catch (coding_exception $e) {
            $this->assertEquals(1, preg_match('~without start_progress~', $e->getMessage()));
        }
        try {
            $progress->end_progress();
            $this->fail();
        } catch (coding_exception $e) {
            $this->assertEquals(1, preg_match('~without start_progress~', $e->getMessage()));
        }
        try {
            $progress->get_current_description();
            $this->fail();
        } catch (coding_exception $e) {
            $this->assertEquals(1, preg_match('~Not inside progress~', $e->getMessage()));
        }
        try {
            $progress->start_progress('', 1, 7);
            $this->fail();
        } catch (coding_exception $e) {
            $this->assertEquals(1, preg_match('~must be 1~', $e->getMessage()));
        }

                try {
            $progress->start_progress('hello', -2);
            $this->fail();
        } catch (coding_exception $e) {
            $this->assertEquals(1, preg_match('~cannot be negative~', $e->getMessage()));
        }

                $progress->start_progress('hello', 10);
        try {
            $progress->progress(\core\progress\base::INDETERMINATE);
            $this->fail();
        } catch (coding_exception $e) {
            $this->assertEquals(1, preg_match('~expecting value~', $e->getMessage()));
        }

                $progress->start_progress('hello');
        try {
            $progress->progress(4);
            $this->fail();
        } catch (coding_exception $e) {
            $this->assertEquals(1, preg_match('~expecting INDETERMINATE~', $e->getMessage()));
        }

                $progress->start_progress('hello', 10);
        try {
            $progress->progress(-2);
            $this->fail();
        } catch (coding_exception $e) {
            $this->assertEquals(1, preg_match('~out of range~', $e->getMessage()));
        }
        try {
            $progress->progress(11);
            $this->fail();
        } catch (coding_exception $e) {
            $this->assertEquals(1, preg_match('~out of range~', $e->getMessage()));
        }

                $progress->progress(4);
        $progress->step_time();
        $progress->progress(4);
        $progress->step_time();

                try {
            $progress->progress(3);
            $this->fail();
        } catch (coding_exception $e) {
            $this->assertEquals(1, preg_match('~backwards~', $e->getMessage()));
        }

                try {
            $progress->start_progress('', 1, 7);
            $this->fail();
        } catch (coding_exception $e) {
            $this->assertEquals(1, preg_match('~would exceed max~', $e->getMessage()));
        }
    }

    public function test_progress_change() {

        $progress = new core_mock_progress();

        $progress->start_progress('hello', 50);


        for ($n = 1; $n <= 10; $n++) {
            $progress->increment_progress();
        }

                $this->assert_min_max(0.2, 0.2, $progress);
        $this->assertEquals(1, $progress->get_progress_count());

                $progress->step_time();

        for ($n = 1; $n <= 20; $n++) {
            $progress->increment_progress();
        }

        $this->assertTrue($progress->was_update_called());

                $this->assert_min_max(0.6, 0.6, $progress);
        $this->assertEquals(2, $progress->get_progress_count());

        for ($n = 1; $n <= 10; $n++) {
            $progress->increment_progress();
        }
        $this->assertFalse($progress->was_update_called());
        $this->assert_min_max(0.8, 0.8, $progress);
        $this->assertEquals(2, $progress->get_progress_count());

                $progress->increment_progress(5);
        $this->assertFalse($progress->was_update_called());
        $this->assert_min_max(0.9, 0.9, $progress);
        $this->assertEquals(2, $progress->get_progress_count());

        for ($n = 1; $n <= 3; $n++) {
            $progress->step_time();
            $progress->increment_progress(1);
        }
        $this->assertTrue($progress->was_update_called());
        $this->assert_min_max(0.96, 0.96, $progress);
        $this->assertEquals(5, $progress->get_progress_count());


                $progress->end_progress();
        $this->assertTrue($progress->was_update_called());
        $this->assertEquals(5, $progress->get_progress_count());

                $this->assert_min_max(1.0, 1.0, $progress);
    }

    
    private function assert_min_max($min, $max, core_mock_progress $progress) {
        $this->assertEquals(array($min, $max),
                $progress->get_progress_proportion_range());
    }
}


class core_mock_progress extends \core\progress\base {
    private $updatecalled = false;
    private $time = 1;

    
    public function was_update_called() {
        if ($this->updatecalled) {
            $this->updatecalled = false;
            return true;
        }
        return false;
    }

    
    public function step_time() {
        $this->time++;
    }

    protected function update_progress() {
        $this->updatecalled = true;
    }

    protected function get_time() {
        return $this->time;
    }
}
