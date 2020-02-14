<?php



defined('MOODLE_INTERNAL') || die();

global $CFG;
require_once($CFG->libdir.'/gradelib.php');
require_once($CFG->dirroot.'/grade/querylib.php');


class core_grade_querylib_testcase extends advanced_testcase {

    public function test_grade_get_gradable_activities() {
        $this->resetAfterTest(true);

        $course = $this->getDataGenerator()->create_course();
        $data1 = $this->getDataGenerator()->create_module('data', array('assessed'=>1, 'scale'=>100, 'course'=>$course->id));
        $data2 = $this->getDataGenerator()->create_module('data', array('assessed'=>0, 'course'=>$course->id));
        $forum1 = $this->getDataGenerator()->create_module('forum', array('assessed'=>1, 'scale'=>100, 'course'=>$course->id));
        $forum2 = $this->getDataGenerator()->create_module('forum', array('assessed'=>0, 'course'=>$course->id));

        $cms = grade_get_gradable_activities($course->id);
        $this->assertEquals(2, count($cms));
        $this->assertTrue(isset($cms[$data1->cmid]));
        $this->assertTrue(isset($cms[$forum1->cmid]));

        $cms = grade_get_gradable_activities($course->id, 'forum');
        $this->assertEquals(1, count($cms));
        $this->assertTrue(isset($cms[$forum1->cmid]));
    }

    public function test_grade_get_grade_items_for_activity() {
        $this->resetAfterTest(true);

        $course = $this->getDataGenerator()->create_course();
        $forum = $this->getDataGenerator()->create_module('forum', array('course'=>$course->id));
        $cm = get_coursemodule_from_id('forum', $forum->cmid, $forum->course);
        unset($cm->modname);
        $grade = grade_get_grade_items_for_activity($cm);

    }
}
