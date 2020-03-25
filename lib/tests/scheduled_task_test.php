<?php



defined('MOODLE_INTERNAL') || die();
require_once(__DIR__ . '/fixtures/task_fixtures.php');


class core_scheduled_task_testcase extends advanced_testcase {

    
    public function test_eval_cron_field() {
        $testclass = new \core\task\scheduled_test_task();

        $this->assertEquals(20, count($testclass->eval_cron_field('*/3', 0, 59)));
        $this->assertEquals(31, count($testclass->eval_cron_field('1,*/2', 0, 59)));
        $this->assertEquals(15, count($testclass->eval_cron_field('1-10,5-15', 0, 59)));
        $this->assertEquals(13, count($testclass->eval_cron_field('1-10,5-15/2', 0, 59)));
        $this->assertEquals(3, count($testclass->eval_cron_field('1,2,3,1,2,3', 0, 59)));
        $this->assertEquals(1, count($testclass->eval_cron_field('-1,10,80', 0, 59)));
    }

    public function test_get_next_scheduled_time() {
        global $CFG;
        $this->resetAfterTest();

        $this->setTimezone('Europe/London');

                $testclass = new \core\task\scheduled_test_task();

                $testclass->set_hour('1');
        $testclass->set_minute('0');
                $nexttime = $testclass->get_next_scheduled_time();

        $oneamdate = new DateTime('now', new DateTimeZone('Europe/London'));
        $oneamdate->setTime(1, 0, 0);
                if ($oneamdate->getTimestamp() < time()) {
            $oneamdate->add(new DateInterval('P1D'));
        }
        $oneam = $oneamdate->getTimestamp();

        $this->assertEquals($oneam, $nexttime, 'Next scheduled time is 1am.');

                $testclass->set_disabled(true);
        $nexttime = $testclass->get_next_scheduled_time();
        $this->assertEquals($oneam, $nexttime, 'Next scheduled time is 1am.');

                $testclass = new \core\task\scheduled_test_task();

                $testclass->set_minute('*/10');
                $nexttime = $testclass->get_next_scheduled_time();

        $minutes = ((intval(date('i') / 10))+1) * 10;
        $nexttenminutes = mktime(date('H'), $minutes, 0);

        $this->assertEquals($nexttenminutes, $nexttime, 'Next scheduled time is in 10 minutes.');

                $testclass->set_disabled(true);
        $nexttime = $testclass->get_next_scheduled_time();
        $this->assertEquals($nexttenminutes, $nexttime, 'Next scheduled time is in 10 minutes.');

                $testclass = new \core\task\scheduled_test_task();
        $testclass->set_minute('0');
        $testclass->set_day_of_week('7');

        $nexttime = $testclass->get_next_scheduled_time();

        $this->assertEquals(7, date('N', $nexttime));
        $this->assertEquals(0, date('i', $nexttime));

                $testclass = new \core\task\scheduled_test_task();
        $testclass->set_minute('32');
        $testclass->set_hour('0');
        $testclass->set_day('1');

        $nexttime = $testclass->get_next_scheduled_time();

        $this->assertEquals(32, date('i', $nexttime));
        $this->assertEquals(0, date('G', $nexttime));
        $this->assertEquals(1, date('j', $nexttime));
    }

    public function test_timezones() {
        global $CFG, $USER;

                $this->resetAfterTest();

        $this->setTimezone('Asia/Kabul');

        $testclass = new \core\task\scheduled_test_task();

                $testclass->set_hour('1');
        $testclass->set_minute('0');

                $nexttime = $testclass->get_next_scheduled_time();

                $USER->timezone = 'Asia/Kathmandu';
        $userdate = userdate($nexttime);

                                $this->assertContains('2:15 AM', core_text::strtoupper($userdate));
    }

    public function test_reset_scheduled_tasks_for_component() {
        global $DB;

        $this->resetAfterTest(true);
                $defaulttasks = \core\task\manager::load_scheduled_tasks_for_component('moodle');
        $initcount = count($defaulttasks);
                $firsttask = reset($defaulttasks);
        $firsttask->set_minute('1');
        $firsttask->set_hour('2');
        $firsttask->set_month('3');
        $firsttask->set_day_of_week('4');
        $firsttask->set_day('5');
        $firsttask->set_customised('1');
        \core\task\manager::configure_scheduled_task($firsttask);
        $firsttaskrecord = \core\task\manager::record_from_scheduled_task($firsttask);
                $firsttaskrecord->nextruntime = '0';

                $secondtask = next($defaulttasks);
        $DB->delete_records('task_scheduled', array('classname' => '\\' . trim(get_class($secondtask), '\\')));
        $this->assertFalse(\core\task\manager::get_scheduled_task(get_class($secondtask)));

                $thirdtask = next($defaulttasks);
        $thirdtask->set_minute('1');
        $thirdtask->set_hour('2');
        $thirdtask->set_month('3');
        $thirdtask->set_day_of_week('4');
        $thirdtask->set_day('5');
        $thirdtaskbefore = \core\task\manager::get_scheduled_task(get_class($thirdtask));
        $thirdtaskbefore->set_next_run_time(null);              \core\task\manager::configure_scheduled_task($thirdtask);
        $thirdtask = \core\task\manager::get_scheduled_task(get_class($thirdtask));
        $thirdtask->set_next_run_time(null);                    $this->assertNotEquals($thirdtaskbefore, $thirdtask);

                \core\task\manager::reset_scheduled_tasks_for_component('moodle');

                $defaulttasks = \core\task\manager::load_scheduled_tasks_for_component('moodle');
        $finalcount = count($defaulttasks);
                $newfirsttask = reset($defaulttasks);
        $newfirsttaskrecord = \core\task\manager::record_from_scheduled_task($newfirsttask);
                $newfirsttaskrecord->nextruntime = '0';

                $this->assertEquals($firsttaskrecord, $newfirsttaskrecord);

                $secondtaskafter = \core\task\manager::get_scheduled_task(get_class($secondtask));
        $secondtaskafter->set_next_run_time(null);           $secondtask->set_next_run_time(null);
        $this->assertEquals($secondtask, $secondtaskafter);

                $thirdtaskafter = \core\task\manager::get_scheduled_task(get_class($thirdtask));
        $thirdtaskafter->set_next_run_time(null);
        $this->assertEquals($thirdtaskbefore, $thirdtaskafter);

                $this->assertEquals($initcount, $finalcount);
    }

    
    public function test_reset_scheduled_tasks_for_component_delete() {
        global $DB;
        $this->resetAfterTest(true);

        $count = $DB->count_records('task_scheduled', array('component' => 'moodle'));
        $allcount = $DB->count_records('task_scheduled');

        $task = new \core\task\scheduled_test_task();
        $task->set_component('moodle');
        $record = \core\task\manager::record_from_scheduled_task($task);
        $DB->insert_record('task_scheduled', $record);
        $this->assertTrue($DB->record_exists('task_scheduled', array('classname' => '\core\task\scheduled_test_task',
            'component' => 'moodle')));

        $task = new \core\task\scheduled_test2_task();
        $task->set_component('moodle');
        $record = \core\task\manager::record_from_scheduled_task($task);
        $DB->insert_record('task_scheduled', $record);
        $this->assertTrue($DB->record_exists('task_scheduled', array('classname' => '\core\task\scheduled_test2_task',
            'component' => 'moodle')));

        $aftercount = $DB->count_records('task_scheduled', array('component' => 'moodle'));
        $afterallcount = $DB->count_records('task_scheduled');

        $this->assertEquals($count + 2, $aftercount);
        $this->assertEquals($allcount + 2, $afterallcount);

                \core\task\manager::reset_scheduled_tasks_for_component('moodle');

        $this->assertEquals($count, $DB->count_records('task_scheduled', array('component' => 'moodle')));
        $this->assertEquals($allcount, $DB->count_records('task_scheduled'));
        $this->assertFalse($DB->record_exists('task_scheduled', array('classname' => '\core\task\scheduled_test2_task',
            'component' => 'moodle')));
        $this->assertFalse($DB->record_exists('task_scheduled', array('classname' => '\core\task\scheduled_test_task',
            'component' => 'moodle')));
    }

    public function test_get_next_scheduled_task() {
        global $DB;

        $this->resetAfterTest(true);
                $DB->delete_records('task_scheduled');
        
                $record = new stdClass();
        $record->blocking = true;
        $record->minute = '0';
        $record->hour = '0';
        $record->dayofweek = '*';
        $record->day = '*';
        $record->month = '*';
        $record->component = 'test_scheduled_task';
        $record->classname = '\core\task\scheduled_test_task';

        $DB->insert_record('task_scheduled', $record);
                $record->classname = '\core\task\scheduled_test2_task';
        $DB->insert_record('task_scheduled', $record);
                $record->classname = '\core\task\scheduled_test3_task';
        $record->disabled = 1;
        $DB->insert_record('task_scheduled', $record);

        $now = time();

                $task = \core\task\manager::get_next_scheduled_task($now);
        $this->assertInstanceOf('\core\task\scheduled_test_task', $task);
        $task->execute();

        \core\task\manager::scheduled_task_complete($task);
                $task = \core\task\manager::get_next_scheduled_task($now);
        $this->assertInstanceOf('\core\task\scheduled_test2_task', $task);
        $task->execute();

        \core\task\manager::scheduled_task_failed($task);
                $task = \core\task\manager::get_next_scheduled_task($now);
        $this->assertNull($task);

                $task = \core\task\manager::get_next_scheduled_task($now + 120);
        $this->assertInstanceOf('\core\task\scheduled_test2_task', $task);
        $task->execute();

        \core\task\manager::scheduled_task_complete($task);

                $task = \core\task\manager::get_next_scheduled_task($now);
        $this->assertNull($task);

                $DB->delete_records('task_scheduled');
        $record->lastruntime = 2;
        $record->disabled = 0;
        $record->classname = '\core\task\scheduled_test_task';
        $DB->insert_record('task_scheduled', $record);

        $record->lastruntime = 1;
        $record->classname = '\core\task\scheduled_test2_task';
        $DB->insert_record('task_scheduled', $record);

                $task = \core\task\manager::get_next_scheduled_task($now);
        $this->assertInstanceOf('\core\task\scheduled_test2_task', $task);
        $task->execute();
        \core\task\manager::scheduled_task_complete($task);

                $task = \core\task\manager::get_next_scheduled_task($now);
        $this->assertInstanceOf('\core\task\scheduled_test_task', $task);
        $task->execute();
        \core\task\manager::scheduled_task_complete($task);

                $task = \core\task\manager::get_next_scheduled_task($now);
        $this->assertNull($task);
    }

    public function test_get_broken_scheduled_task() {
        global $DB;

        $this->resetAfterTest(true);
                $DB->delete_records('task_scheduled');
        
                $record = new stdClass();
        $record->blocking = true;
        $record->minute = '*';
        $record->hour = '*';
        $record->dayofweek = '*';
        $record->day = '*';
        $record->month = '*';
        $record->component = 'test_scheduled_task';
        $record->classname = '\core\task\scheduled_test_task_broken';

        $DB->insert_record('task_scheduled', $record);

        $now = time();
                $task = \core\task\manager::get_next_scheduled_task($now);
        $this->assertDebuggingCalled();
        $this->assertNull($task);
    }

    
    public function test_random_time_specification() {

                        $testclass = new \core\task\scheduled_test_task();

                $this->assertInternalType('string', $testclass->get_minute());
        $this->assertInternalType('string', $testclass->get_hour());

                $testclass->set_minute('R');
        $testclass->set_hour('R');
        $testclass->set_day_of_week('R');

                $minute = $testclass->get_minute();
        $this->assertInternalType('int', $minute);
        $this->assertGreaterThanOrEqual(0, $minute);
        $this->assertLessThanOrEqual(59, $minute);

                $hour = $testclass->get_hour();
        $this->assertInternalType('int', $hour);
        $this->assertGreaterThanOrEqual(0, $hour);
        $this->assertLessThanOrEqual(23, $hour);

                $dayofweek = $testclass->get_day_of_week();
        $this->assertInternalType('int', $dayofweek);
        $this->assertGreaterThanOrEqual(0, $dayofweek);
        $this->assertLessThanOrEqual(6, $dayofweek);
    }

    
    public function test_file_temp_cleanup_task() {
        global $CFG;

                $dir = $CFG->tempdir . DIRECTORY_SEPARATOR . 'backup' . DIRECTORY_SEPARATOR . 'backup01' . DIRECTORY_SEPARATOR . 'courses';
        mkdir($dir, 0777, true);

                $file01 = $dir . DIRECTORY_SEPARATOR . 'sections.xml';
        file_put_contents($file01, 'test data 001');
        $file02 = $dir . DIRECTORY_SEPARATOR . 'modules.xml';
        file_put_contents($file02, 'test data 002');
                touch($file01, time() - (8 * 24 * 3600));

        $task = \core\task\manager::get_scheduled_task('\\core\\task\\file_temp_cleanup_task');
        $this->assertInstanceOf('\core\task\file_temp_cleanup_task', $task);
        $task->execute();

                $filesarray = scandir($dir);
        $this->assertEquals('modules.xml', $filesarray[2]);
        $this->assertEquals(3, count($filesarray));

                touch($file02, time() - (8 * 24 * 3600));
                touch($CFG->tempdir . DIRECTORY_SEPARATOR . 'backup' . DIRECTORY_SEPARATOR . 'backup01' . DIRECTORY_SEPARATOR .
                'courses', time() - (8 * 24 * 3600));
                $task->execute();
        $filesarray = scandir($CFG->tempdir . DIRECTORY_SEPARATOR . 'backup' . DIRECTORY_SEPARATOR . 'backup01');
                $this->assertEquals(2, count($filesarray));

                $dir = new \RecursiveDirectoryIterator($CFG->tempdir);
                $iter = new \RecursiveIteratorIterator($dir, \RecursiveIteratorIterator::CHILD_FIRST);

        for ($iter->rewind(); $iter->valid(); $iter->next()) {
            if ($iter->isDir() && !$iter->isDot()) {
                $node = $iter->getRealPath();
                touch($node, time() - (8 * 24 * 3600));
            }
        }

                $task->execute();
        $filesarray = scandir($CFG->tempdir);
                        $this->assertEquals(2, count($filesarray));
    }
}
