<?php



defined('MOODLE_INTERNAL') || die();

require_once(__DIR__.'/fixtures/lib.php');


class core_grade_grade_testcase extends grade_base_testcase {

    public function test_grade_grade() {
        $this->sub_test_grade_grade_construct();
        $this->sub_test_grade_grade_insert();
        $this->sub_test_grade_grade_update();
        $this->sub_test_grade_grade_fetch();
        $this->sub_test_grade_grade_fetch_all();
        $this->sub_test_grade_grade_load_grade_item();
        $this->sub_test_grade_grade_standardise_score();
        $this->sub_test_grade_grade_is_locked();
        $this->sub_test_grade_grade_set_hidden();
        $this->sub_test_grade_grade_is_hidden();
    }

    protected function sub_test_grade_grade_construct() {
        $params = new stdClass();

        $params->itemid = $this->grade_items[0]->id;
        $params->userid = 1;
        $params->rawgrade = 88;
        $params->rawgrademax = 110;
        $params->rawgrademin = 18;

        $grade_grade = new grade_grade($params, false);
        $this->assertEquals($params->itemid, $grade_grade->itemid);
        $this->assertEquals($params->rawgrade, $grade_grade->rawgrade);
    }

    protected function sub_test_grade_grade_insert() {
        $grade_grade = new grade_grade();
        $this->assertTrue(method_exists($grade_grade, 'insert'));

        $grade_grade->itemid = $this->grade_items[0]->id;
        $grade_grade->userid = 10;
        $grade_grade->rawgrade = 88;
        $grade_grade->rawgrademax = 110;
        $grade_grade->rawgrademin = 18;

                $grade_grade->load_grade_item();
        $this->assertEmpty($grade_grade->grade_item->needsupdate);

        $grade_grade->insert();

        $last_grade_grade = end($this->grade_grades);

        $this->assertEquals($grade_grade->id, $last_grade_grade->id + 1);

                $this->assertTrue(empty($grade_grade->timecreated));
                $this->assertTrue(empty($grade_grade->timemodified));

                $this->grade_grades[] = $grade_grade;
    }

    protected function sub_test_grade_grade_update() {
        $grade_grade = new grade_grade($this->grade_grades[0], false);
        $this->assertTrue(method_exists($grade_grade, 'update'));
    }

    protected function sub_test_grade_grade_fetch() {
        $grade_grade = new grade_grade();
        $this->assertTrue(method_exists($grade_grade, 'fetch'));

        $grades = grade_grade::fetch(array('id'=>$this->grade_grades[0]->id));
        $this->assertEquals($this->grade_grades[0]->id, $grades->id);
        $this->assertEquals($this->grade_grades[0]->rawgrade, $grades->rawgrade);
    }

    protected function sub_test_grade_grade_fetch_all() {
        $grade_grade = new grade_grade();
        $this->assertTrue(method_exists($grade_grade, 'fetch_all'));

        $grades = grade_grade::fetch_all(array());
        $this->assertEquals(count($this->grade_grades), count($grades));
    }

    protected function sub_test_grade_grade_load_grade_item() {
        $grade_grade = new grade_grade($this->grade_grades[0], false);
        $this->assertTrue(method_exists($grade_grade, 'load_grade_item'));
        $this->assertNull($grade_grade->grade_item);
        $this->assertNotEmpty($grade_grade->itemid);
        $this->assertNotNull($grade_grade->load_grade_item());
        $this->assertNotNull($grade_grade->grade_item);
        $this->assertEquals($this->grade_items[0]->id, $grade_grade->grade_item->id);
    }


    protected function sub_test_grade_grade_standardise_score() {
        $this->assertEquals(4, round(grade_grade::standardise_score(6, 0, 7, 0, 5)));
        $this->assertEquals(40, grade_grade::standardise_score(50, 30, 80, 0, 100));
    }


    

    protected function sub_test_grade_grade_is_locked() {
        $grade = new grade_grade($this->grade_grades[0], false);
        $this->assertTrue(method_exists($grade, 'is_locked'));

        $this->assertFalse($grade->is_locked());
        $grade->locked = time();
        $this->assertTrue($grade->is_locked());
    }

    protected function sub_test_grade_grade_set_hidden() {
        $grade = new grade_grade($this->grade_grades[0], false);
        $grade_item = new grade_item($this->grade_items[0], false);
        $this->assertTrue(method_exists($grade, 'set_hidden'));

        $this->assertEquals(0, $grade_item->hidden);
        $this->assertEquals(0, $grade->hidden);

        $grade->set_hidden(0);
        $this->assertEquals(0, $grade->hidden);

        $grade->set_hidden(1);
        $this->assertEquals(1, $grade->hidden);

        $grade->set_hidden(0);
        $this->assertEquals(0, $grade->hidden);
    }

    protected function sub_test_grade_grade_is_hidden() {
        $grade = new grade_grade($this->grade_grades[0], false);
        $this->assertTrue(method_exists($grade, 'is_hidden'));

        $this->assertFalse($grade->is_hidden());
        $grade->hidden = 1;
        $this->assertTrue($grade->is_hidden());

        $grade->hidden = time()-666;
        $this->assertFalse($grade->is_hidden());

        $grade->hidden = time()+666;
        $this->assertTrue($grade->is_hidden());
    }

    public function test_flatten_dependencies() {
                $a = array(1 => array(2, 3), 2 => array(), 3 => array(4), 4 => array());
        $b = array();
        $expecteda = array(1 => array(2, 3, 4), 2 => array(), 3 => array(4), 4 => array());
        $expectedb = array(1 => 1);

        test_grade_grade_flatten_dependencies_array::test_flatten_dependencies_array($a, $b);
        $this->assertSame($expecteda, $a);
        $this->assertSame($expectedb, $b);

                $a = $b = $expecteda = $expectedb = array();

        test_grade_grade_flatten_dependencies_array::test_flatten_dependencies_array($a, $b);
        $this->assertSame($expecteda, $a);
        $this->assertSame($expectedb, $b);

                $a = array(1 => array(2), 2 => array(3), 3 => array(1));
        $b = array();
        $expecteda = array(1 => array(1, 2, 3), 2 => array(1, 2, 3), 3 => array(1, 2, 3));

        test_grade_grade_flatten_dependencies_array::test_flatten_dependencies_array($a, $b);
        $this->assertSame($expecteda, $a);
                
                $a = array(1 => array(2), 2 => array(3), 3 => array(4), 4 => array(2, 1));
        $b = array();
        $expecteda = array(1 => array(1, 2, 3, 4), 2 => array(1, 2, 3, 4), 3 => array(1, 2, 3, 4), 4 => array(1, 2, 3, 4));

        test_grade_grade_flatten_dependencies_array::test_flatten_dependencies_array($a, $b);
        $this->assertSame($expecteda, $a);
    }

    public function test_grade_grade_min_max() {
        global $CFG;
        $initialminmaxtouse = $CFG->grade_minmaxtouse;

        $this->setAdminUser();
        $course = $this->getDataGenerator()->create_course();
        $user = $this->getDataGenerator()->create_user();
        $assignrecord = $this->getDataGenerator()->create_module('assign', array('course' => $course, 'grade' => 100));
        $cm = get_coursemodule_from_instance('assign', $assignrecord->id);
        $assigncontext = context_module::instance($cm->id);
        $assign = new assign($assigncontext, $cm, $course);

                $giparams = array('itemtype' => 'mod', 'itemmodule' => 'assign', 'iteminstance' => $assignrecord->id,
                'courseid' => $course->id, 'itemnumber' => 0);
        $gi = grade_item::fetch($giparams);
        $this->assertEquals(0, $gi->grademin);
        $this->assertEquals(100, $gi->grademax);

                $usergrade = $assign->get_user_grade($user->id, true);
        $usergrade->grade = 10;
        $assign->update_grade($usergrade);

                $gg = grade_grade::fetch(array('userid' => $user->id, 'itemid' => $gi->id));
        $this->assertEquals(10, $gg->rawgrade);
        $this->assertEquals(0, $gg->get_grade_min());
        $this->assertEquals(100, $gg->get_grade_max());

                $gi->grademax = 50;
        $gi->grademin = 2;
        $gi->update();

                $gi = grade_item::fetch($giparams);

                $CFG->grade_minmaxtouse = GRADE_MIN_MAX_FROM_GRADE_ITEM;
        grade_set_setting($course->id, 'minmaxtouse', null); 
        $gg = grade_grade::fetch(array('userid' => $user->id, 'itemid' => $gi->id));
        $this->assertEquals(2, $gg->get_grade_min());
        $this->assertEquals(50, $gg->get_grade_max());

                $CFG->grade_minmaxtouse = GRADE_MIN_MAX_FROM_GRADE_GRADE;
        grade_set_setting($course->id, 'minmaxtouse', null);         $gg = grade_grade::fetch(array('userid' => $user->id, 'itemid' => $gi->id));
        $this->assertEquals(0, $gg->get_grade_min());
        $this->assertEquals(100, $gg->get_grade_max());

                $CFG->grade_minmaxtouse = GRADE_MIN_MAX_FROM_GRADE_ITEM;
        grade_set_setting($course->id, 'minmaxtouse', GRADE_MIN_MAX_FROM_GRADE_GRADE);
        $gg = grade_grade::fetch(array('userid' => $user->id, 'itemid' => $gi->id));
        $this->assertEquals(0, $gg->get_grade_min());
        $this->assertEquals(100, $gg->get_grade_max());

        $CFG->grade_minmaxtouse = GRADE_MIN_MAX_FROM_GRADE_GRADE;
        grade_set_setting($course->id, 'minmaxtouse', GRADE_MIN_MAX_FROM_GRADE_ITEM);
        $gg = grade_grade::fetch(array('userid' => $user->id, 'itemid' => $gi->id));
        $this->assertEquals(2, $gg->get_grade_min());
        $this->assertEquals(50, $gg->get_grade_max());

        $CFG->grade_minmaxtouse = $initialminmaxtouse;
    }

    public function test_grade_grade_min_max_with_course_item() {
        global $CFG, $DB;
        $initialminmaxtouse = $CFG->grade_minmaxtouse;

        $this->setAdminUser();
        $course = $this->getDataGenerator()->create_course();
        $user = $this->getDataGenerator()->create_user();
        $gi = grade_item::fetch_course_item($course->id);

                $this->assertEquals(0, $gi->grademin);
        $this->assertEquals(100, $gi->grademax);

                $gi->update_final_grade($user->id, 10);

                $gg = grade_grade::fetch(array('userid' => $user->id, 'itemid' => $gi->id));
        $this->assertEquals(0, $gg->get_grade_min());
        $this->assertEquals(100, $gg->get_grade_max());

                $gi->grademin = 2;
        $gi->grademax = 50;
        $gi->update();

                $gi = grade_item::fetch_course_item($course->id);

                $CFG->grade_minmaxtouse = GRADE_MIN_MAX_FROM_GRADE_ITEM;
        grade_set_setting($course->id, 'minmaxtouse', null); 
        $gg = grade_grade::fetch(array('userid' => $user->id, 'itemid' => $gi->id));
        $this->assertEquals(0, $gg->get_grade_min());
        $this->assertEquals(100, $gg->get_grade_max());

                $CFG->grade_minmaxtouse = GRADE_MIN_MAX_FROM_GRADE_GRADE;
        grade_set_setting($course->id, 'minmaxtouse', null);         $gg = grade_grade::fetch(array('userid' => $user->id, 'itemid' => $gi->id));
        $this->assertEquals(0, $gg->get_grade_min());
        $this->assertEquals(100, $gg->get_grade_max());

                $CFG->grade_minmaxtouse = GRADE_MIN_MAX_FROM_GRADE_ITEM;
        grade_set_setting($course->id, 'minmaxtouse', GRADE_MIN_MAX_FROM_GRADE_GRADE);
        $gg = grade_grade::fetch(array('userid' => $user->id, 'itemid' => $gi->id));
        $this->assertEquals(0, $gg->get_grade_min());
        $this->assertEquals(100, $gg->get_grade_max());

        $CFG->grade_minmaxtouse = GRADE_MIN_MAX_FROM_GRADE_GRADE;
        grade_set_setting($course->id, 'minmaxtouse', GRADE_MIN_MAX_FROM_GRADE_ITEM);
        $gg = grade_grade::fetch(array('userid' => $user->id, 'itemid' => $gi->id));
        $this->assertEquals(0, $gg->get_grade_min());
        $this->assertEquals(100, $gg->get_grade_max());

        $CFG->grade_minmaxtouse = $initialminmaxtouse;
    }

    public function test_grade_grade_min_max_with_category_item() {
        global $CFG, $DB;
        $initialminmaxtouse = $CFG->grade_minmaxtouse;

        $this->setAdminUser();
        $course = $this->getDataGenerator()->create_course();
        $user = $this->getDataGenerator()->create_user();
        $coursegi = grade_item::fetch_course_item($course->id);

                $gc = new grade_category(array('courseid' => $course->id, 'fullname' => 'test'), false);
        $gc->insert();
        $gi = $gc->get_grade_item();
        $gi->grademax = 100;
        $gi->grademin = 0;
        $gi->update();

                $giparams = array('itemtype' => 'category', 'iteminstance' => $gc->id);
        $gi = grade_item::fetch($giparams);
        $this->assertEquals(0, $gi->grademin);
        $this->assertEquals(100, $gi->grademax);

                $gi->update_final_grade($user->id, 10);

                $gg = grade_grade::fetch(array('userid' => $user->id, 'itemid' => $gi->id));
        $this->assertEquals(0, $gg->get_grade_min());
        $this->assertEquals(100, $gg->get_grade_max());

                $gi->grademin = 2;
        $gi->grademax = 50;
        $gi->update();

                $gi = grade_item::fetch($giparams);

                $CFG->grade_minmaxtouse = GRADE_MIN_MAX_FROM_GRADE_ITEM;
        grade_set_setting($course->id, 'minmaxtouse', null); 
        $gg = grade_grade::fetch(array('userid' => $user->id, 'itemid' => $gi->id));
        $this->assertEquals(0, $gg->get_grade_min());
        $this->assertEquals(100, $gg->get_grade_max());

                $CFG->grade_minmaxtouse = GRADE_MIN_MAX_FROM_GRADE_GRADE;
        grade_set_setting($course->id, 'minmaxtouse', null);         $gg = grade_grade::fetch(array('userid' => $user->id, 'itemid' => $gi->id));
        $this->assertEquals(0, $gg->get_grade_min());
        $this->assertEquals(100, $gg->get_grade_max());

                $CFG->grade_minmaxtouse = GRADE_MIN_MAX_FROM_GRADE_ITEM;
        grade_set_setting($course->id, 'minmaxtouse', GRADE_MIN_MAX_FROM_GRADE_GRADE);
        $gg = grade_grade::fetch(array('userid' => $user->id, 'itemid' => $gi->id));
        $this->assertEquals(0, $gg->get_grade_min());
        $this->assertEquals(100, $gg->get_grade_max());

        $CFG->grade_minmaxtouse = GRADE_MIN_MAX_FROM_GRADE_GRADE;
        grade_set_setting($course->id, 'minmaxtouse', GRADE_MIN_MAX_FROM_GRADE_ITEM);
        $gg = grade_grade::fetch(array('userid' => $user->id, 'itemid' => $gi->id));
        $this->assertEquals(0, $gg->get_grade_min());
        $this->assertEquals(100, $gg->get_grade_max());

        $CFG->grade_minmaxtouse = $initialminmaxtouse;
    }
}
