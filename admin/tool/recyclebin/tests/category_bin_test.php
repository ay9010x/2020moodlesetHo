<?php



defined('MOODLE_INTERNAL') || die();


class tool_recyclebin_category_bin_tests extends advanced_testcase {

    
    protected $course;

    
    protected $coursebeingrestored;

    
    protected function setUp() {
        $this->resetAfterTest();
        $this->setAdminUser();

                set_config('categorybinenable', 1, 'tool_recyclebin');

        $this->course = $this->getDataGenerator()->create_course();
    }

    
    public function test_pre_course_delete_hook() {
        global $DB;

                $this->coursebeingrestored = $this->getDataGenerator()->create_course();
        $this->coursebeingrestored->deletesource = 'restore';

                $this->assertEquals(0, $DB->count_records('tool_recyclebin_category'));

        delete_course($this->course, false);
                delete_course($this->coursebeingrestored, false);

                $this->assertEquals(1, $DB->count_records('tool_recyclebin_category'));

                $recyclebin = new \tool_recyclebin\category_bin($this->course->category);
        $this->assertEquals(1, count($recyclebin->get_items()));
    }

    
    public function test_pre_course_category_delete_hook() {
        global $DB;

                $this->assertEquals(0, $DB->count_records('tool_recyclebin_category'));

        delete_course($this->course, false);

                $this->assertEquals(1, $DB->count_records('tool_recyclebin_category'));

                $category = coursecat::get($this->course->category);
        $category->delete_full(false);

                $this->assertEquals(0, $DB->count_records('tool_recyclebin_category'));
    }

    
    public function test_restore() {
        global $DB;

        delete_course($this->course, false);

        $recyclebin = new \tool_recyclebin\category_bin($this->course->category);
        foreach ($recyclebin->get_items() as $item) {
            $recyclebin->restore_item($item);
        }

                $this->assertEquals(2, $DB->count_records('course'));         $this->assertEquals(0, count($recyclebin->get_items()));
    }

    
    public function test_delete() {
        global $DB;

        delete_course($this->course, false);

        $recyclebin = new \tool_recyclebin\category_bin($this->course->category);
        foreach ($recyclebin->get_items() as $item) {
            $recyclebin->delete_item($item);
        }

                $this->assertEquals(1, $DB->count_records('course'));         $this->assertEquals(0, count($recyclebin->get_items()));
    }

    
    public function test_cleanup_task() {
        global $DB;

                set_config('categorybinexpiry', WEEKSECS, 'tool_recyclebin');

        delete_course($this->course, false);

        $recyclebin = new \tool_recyclebin\category_bin($this->course->category);

                foreach ($recyclebin->get_items() as $item) {
            $item->timecreated = time() - WEEKSECS;
            $DB->update_record('tool_recyclebin_category', $item);
        }

                $course = $this->getDataGenerator()->create_course();
        delete_course($course, false);

                $this->assertEquals(2, count($recyclebin->get_items()));

                $this->expectOutputRegex("/\[tool_recyclebin\] Deleting item '\d+' from the category recycle bin/");
        $task = new \tool_recyclebin\task\cleanup_category_bin();
        $task->execute();

                $courses = $recyclebin->get_items();
        $this->assertEquals(1, count($courses));
        $course = reset($courses);
        $this->assertEquals('Test course 2', $course->fullname);
    }
}
