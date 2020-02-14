<?php



defined('MOODLE_INTERNAL') || die();
require_once(__DIR__ . '/fixtures/task_fixtures.php');



class core_adhoc_task_testcase extends advanced_testcase {

    
    public function test_get_next_adhoc_task_now() {
        $this->resetAfterTest(true);

                $task = new \core\task\adhoc_test_task();

                \core\task\manager::queue_adhoc_task($task);

        $now = time();
                $task = \core\task\manager::get_next_adhoc_task($now);
        $this->assertInstanceOf('\\core\\task\\adhoc_test_task', $task);
        $task->execute();
        \core\task\manager::adhoc_task_complete($task);
    }

    
    public function test_get_next_adhoc_task_fail_retry() {
        $this->resetAfterTest(true);

                $task = new \core\task\adhoc_test_task();
        \core\task\manager::queue_adhoc_task($task);

        $now = time();

                $task = \core\task\manager::get_next_adhoc_task($now);
        $task->execute();
        \core\task\manager::adhoc_task_failed($task);

                $this->assertNull(\core\task\manager::get_next_adhoc_task($now));

                $task = \core\task\manager::get_next_adhoc_task($now + 120);
        $this->assertInstanceOf('\\core\\task\\adhoc_test_task', $task);
        $task->execute();

        \core\task\manager::adhoc_task_complete($task);

                $this->assertNull(\core\task\manager::get_next_adhoc_task($now));
    }

    
    public function test_get_next_adhoc_task_future() {
        $this->resetAfterTest(true);

        $now = time();
                $task = new \core\task\adhoc_test_task();
        $task->set_next_run_time($now + 1000);
        \core\task\manager::queue_adhoc_task($task);

                $this->assertNull(\core\task\manager::get_next_adhoc_task($now));

                $task = \core\task\manager::get_next_adhoc_task($now + 1020);
        $this->assertInstanceOf('\\core\\task\\adhoc_test_task', $task);
        $task->execute();
        \core\task\manager::adhoc_task_complete($task);
    }
}
