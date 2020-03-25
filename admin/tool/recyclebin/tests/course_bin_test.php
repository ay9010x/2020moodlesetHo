<?php



defined('MOODLE_INTERNAL') || die();


class tool_recyclebin_course_bin_tests extends advanced_testcase {

    
    protected $course;

    
    protected $quiz;

    
    protected function setUp() {
        $this->resetAfterTest(true);
        $this->setAdminUser();

                set_config('coursebinenable', 1, 'tool_recyclebin');

        $this->course = $this->getDataGenerator()->create_course();
        $this->quiz = $this->getDataGenerator()->get_plugin_generator('mod_quiz')->create_instance(array(
            'course' => $this->course->id
        ));
    }

    
    public function test_pre_course_module_delete_hook() {
        global $DB;

                $this->assertEquals(0, $DB->count_records('tool_recyclebin_course'));

                course_delete_module($this->quiz->cmid);

                $this->assertEquals(1, $DB->count_records('tool_recyclebin_course'));

                $recyclebin = new \tool_recyclebin\course_bin($this->course->id);
        $this->assertEquals(1, count($recyclebin->get_items()));
    }

    
    public function test_restore() {
        global $DB;

        $startcount = $DB->count_records('course_modules');

                course_delete_module($this->quiz->cmid);

                $recyclebin = new \tool_recyclebin\course_bin($this->course->id);
        foreach ($recyclebin->get_items() as $item) {
            $recyclebin->restore_item($item);
        }

                $this->assertEquals($startcount, $DB->count_records('course_modules'));
        $this->assertEquals(0, count($recyclebin->get_items()));
    }

    
    public function test_delete() {
        global $DB;

        $startcount = $DB->count_records('course_modules');

                course_delete_module($this->quiz->cmid);

                $recyclebin = new \tool_recyclebin\course_bin($this->course->id);
        foreach ($recyclebin->get_items() as $item) {
            $recyclebin->delete_item($item);
        }

                $this->assertEquals($startcount - 1, $DB->count_records('course_modules'));
        $this->assertEquals(0, count($recyclebin->get_items()));
    }

    
    public function test_cleanup_task() {
        global $DB;

        set_config('coursebinexpiry', WEEKSECS, 'tool_recyclebin');

                course_delete_module($this->quiz->cmid);

                $recyclebin = new \tool_recyclebin\course_bin($this->course->id);
        foreach ($recyclebin->get_items() as $item) {
            $item->timecreated = time() - WEEKSECS;
            $DB->update_record('tool_recyclebin_course', $item);
        }

                $book = $this->getDataGenerator()->get_plugin_generator('mod_book')->create_instance(array(
            'course' => $this->course->id));

        course_delete_module($book->cmid);

                $this->assertEquals(2, count($recyclebin->get_items()));

                $this->expectOutputRegex("/\[tool_recyclebin\] Deleting item '\d+' from the course recycle bin/");
        $task = new \tool_recyclebin\task\cleanup_course_bin();
        $task->execute();

                $items = $recyclebin->get_items();
        $this->assertEquals(1, count($items));
        $deletedbook = reset($items);
        $this->assertEquals($book->name, $deletedbook->name);
    }
}
