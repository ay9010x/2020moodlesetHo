<?php



defined('MOODLE_INTERNAL') || die();



class lock_testcase extends advanced_testcase {

    
    protected function setUp() {
        $this->resetAfterTest(true);
    }

    
    protected function run_on_lock_factory(\core\lock\lock_factory $lockfactory) {

        if ($lockfactory->is_available()) {
                        $lock1 = $lockfactory->get_lock('abc', 2);
            $this->assertNotEmpty($lock1, 'Get a lock');

            if ($lockfactory->supports_timeout()) {
                if ($lockfactory->supports_recursion()) {
                    $lock2 = $lockfactory->get_lock('abc', 2);
                    $this->assertNotEmpty($lock2, 'Get a stacked lock');
                    $this->assertTrue($lock2->release(), 'Release a stacked lock');
                } else {
                                        $lock2 = $lockfactory->get_lock('abc', 2);
                    $this->assertFalse($lock2, 'Cannot get a stacked lock');
                }
            }
                        $this->assertTrue($lock1->release(), 'Release a lock');
                        $lock3 = $lockfactory->get_lock('abc', 2);

            $this->assertNotEmpty($lock3, 'Get a lock again');
                        $this->assertTrue($lock3->release(), 'Release a lock again');
                        $this->assertFalse($lock3->release(), 'Release a lock that is not held');
            if (!$lockfactory->supports_auto_release()) {
                                $lock4 = $lockfactory->get_lock('abc', 2, 2);
                $this->assertNotEmpty($lock4, 'Get a lock');
                sleep(3);

                $lock5 = $lockfactory->get_lock('abc', 2, 2);
                $this->assertNotEmpty($lock5, 'Get another lock after a timeout');
                $this->assertTrue($lock5->release(), 'Release the lock');
                $this->assertTrue($lock4->release(), 'Release the lock');
            }
        }
    }

    
    public function test_locks() {
                $defaultfactory = \core\lock\lock_config::get_lock_factory('default');
        $this->run_on_lock_factory($defaultfactory);

                $dblockfactory = new \core\lock\db_record_lock_factory('test');
        $this->run_on_lock_factory($dblockfactory);

        $filelockfactory = new \core\lock\file_lock_factory('test');
        $this->run_on_lock_factory($filelockfactory);

    }

}

