<?php


defined('MOODLE_INTERNAL') || die();

class progress_display_test extends \advanced_testcase {

    
    public function test_progress_display_update() {
        ob_start();
        $progress = new core_mock_progress_display();
        $progress->start_progress('');
        $this->assertEquals(1, $progress->get_current_state());
        $this->assertEquals(1, $progress->get_direction());
        $this->assertTimeCurrent($progress->get_last_wibble());
                $this->waitForSecond();
        $progress->update_progress();
        $this->assertEquals(2, $progress->get_current_state());
        $this->assertEquals(1, $progress->get_direction());
        $this->assertTimeCurrent($progress->get_last_wibble());
        $output = ob_get_clean();
        $this->assertContains('wibbler', $output);
        $this->assertContains('wibble state0', $output);
        $this->assertContains('wibble state1', $output);
    }

    
    public function test_progress_display_wibbler() {
        ob_start();
        $progress = new core_mock_progress_display();
        $progress->start_progress('');
        $this->assertEquals(1, $progress->get_direction());

                $progress->set_current_state(core_mock_progress_display::WIBBLE_STATES);
        $this->waitForSecond();
        $progress->update_progress();
        $this->assertEquals(core_mock_progress_display::WIBBLE_STATES - 1, $progress->get_current_state());
        $this->assertEquals(-1, $progress->get_direction());

                $progress->set_current_state(0);
        $this->waitForSecond();
        $progress->update_progress();
        $this->assertEquals(1, $progress->get_current_state());
        $this->assertEquals(1, $progress->get_direction());
        $output = ob_get_clean();
        $this->assertContains('wibbler', $output);
        $this->assertContains('wibble state0', $output);
        $this->assertContains('wibble state13', $output);

    }

}


class core_mock_progress_display extends \core\progress\display {
    public function get_last_wibble() {
        return $this->lastwibble;
    }

    public function get_current_state() {
        return $this->currentstate;
    }

    public function get_direction() {
        return $this->direction;
    }

    public function set_current_state($state) {
        $this->currentstate = $state;
    }

    public function set_direction($direction) {
        $this->direction = $direction;
    }
}
