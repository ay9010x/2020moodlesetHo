<?php



defined('MOODLE_INTERNAL') || die();

require_once(__DIR__.'/fixtures/lib.php');

class core_grade_item_testcase extends grade_base_testcase {
    public function test_grade_item() {
        $this->sub_test_grade_item_construct();
        $this->sub_test_grade_item_insert();
        $this->sub_test_grade_item_delete();
        $this->sub_test_grade_item_update();
        $this->sub_test_grade_item_load_scale();
        $this->sub_test_grade_item_load_outcome();
        $this->sub_test_grade_item_qualifies_for_regrading();
        $this->sub_test_grade_item_force_regrading();
        $this->sub_test_grade_item_fetch();
        $this->sub_test_grade_item_fetch_all();
        $this->sub_test_grade_item_get_all_finals();
        $this->sub_test_grade_item_get_final();
        $this->sub_test_grade_item_get_sortorder();
        $this->sub_test_grade_item_set_sortorder();
        $this->sub_test_grade_item_move_after_sortorder();
        $this->sub_test_grade_item_get_name();
        $this->sub_test_grade_item_set_parent();
        $this->sub_test_grade_item_get_parent_category();
        $this->sub_test_grade_item_load_parent_category();
        $this->sub_test_grade_item_get_item_category();
        $this->sub_test_grade_item_load_item_category();
        $this->sub_test_grade_item_regrade_final_grades();
        $this->sub_test_grade_item_adjust_raw_grade();
        $this->sub_test_grade_item_rescale_grades_keep_percentage();
        $this->sub_test_grade_item_set_locked();
        $this->sub_test_grade_item_is_locked();
        $this->sub_test_grade_item_set_hidden();
        $this->sub_test_grade_item_is_hidden();
        $this->sub_test_grade_item_is_category_item();
        $this->sub_test_grade_item_is_course_item();
        $this->sub_test_grade_item_fetch_course_item();
        $this->sub_test_grade_item_depends_on();
        $this->sub_test_refresh_grades();
        $this->sub_test_grade_item_is_calculated();
        $this->sub_test_grade_item_set_calculation();
        $this->sub_test_grade_item_get_calculation();
        $this->sub_test_grade_item_compute();
        $this->sub_test_update_final_grade();
        $this->sub_test_grade_item_can_control_visibility();
        $this->sub_test_grade_item_fix_sortorder();
    }

    protected function sub_test_grade_item_construct() {
        $params = new stdClass();

        $params->courseid = $this->courseid;
        $params->categoryid = $this->grade_categories[1]->id;
        $params->itemname = 'unittestgradeitem4';
        $params->itemtype = 'mod';
        $params->itemmodule = 'database';
        $params->iteminfo = 'Grade item used for unit testing';

        $grade_item = new grade_item($params, false);

        $this->assertEquals($params->courseid, $grade_item->courseid);
        $this->assertEquals($params->categoryid, $grade_item->categoryid);
        $this->assertEquals($params->itemmodule, $grade_item->itemmodule);
    }

    protected function sub_test_grade_item_insert() {
        $grade_item = new grade_item();
        $this->assertTrue(method_exists($grade_item, 'insert'));

        $grade_item->courseid = $this->courseid;
        $grade_item->categoryid = $this->grade_categories[1]->id;
        $grade_item->itemname = 'unittestgradeitem4';
        $grade_item->itemtype = 'mod';
        $grade_item->itemmodule = 'quiz';
        $grade_item->iteminfo = 'Grade item used for unit testing';

        $grade_item->insert();

        $last_grade_item = end($this->grade_items);

        $this->assertEquals($grade_item->id, $last_grade_item->id + 1);
        $this->assertEquals(18, $grade_item->sortorder);

                $this->grade_items[] = $grade_item;
    }

    protected function sub_test_grade_item_delete() {
        global $DB;
        $grade_item = new grade_item($this->grade_items[7], false);         $this->assertTrue(method_exists($grade_item, 'delete'));

        $this->assertTrue($grade_item->delete());

        $this->assertFalse($DB->get_record('grade_items', array('id' => $grade_item->id)));

                unset($this->grade_items[7]);
    }

    protected function sub_test_grade_item_update() {
        global $DB;
        $grade_item = new grade_item($this->grade_items[0], false);
        $this->assertTrue(method_exists($grade_item, 'update'));

        $grade_item->iteminfo = 'Updated info for this unittest grade_item';

        $this->assertTrue($grade_item->update());

        $grade_item->grademin = 14;
        $this->assertTrue($grade_item->qualifies_for_regrading());
        $this->assertTrue($grade_item->update());

        $iteminfo = $DB->get_field('grade_items', 'iteminfo', array('id' => $this->grade_items[0]->id));
        $this->assertEquals($grade_item->iteminfo, $iteminfo);
    }

    protected function sub_test_grade_item_load_scale() {
        $grade_item = new grade_item($this->grade_items[2], false);
        $this->assertTrue(method_exists($grade_item, 'load_scale'));
        $scale = $grade_item->load_scale();
        $this->assertFalse(empty($grade_item->scale));
        $this->assertEquals($scale->id, $this->grade_items[2]->scaleid);
    }

    protected function sub_test_grade_item_load_outcome() {
        $grade_item = new grade_item($this->grade_items[0], false);
        $this->assertTrue(method_exists($grade_item, 'load_outcome'));
            }

    protected function sub_test_grade_item_qualifies_for_regrading() {
        $grade_item = new grade_item($this->grade_items[3], false);         $this->assertTrue(method_exists($grade_item, 'qualifies_for_regrading'));

        $this->assertFalse($grade_item->qualifies_for_regrading());

        $grade_item->iteminfo = 'Updated info for this unittest grade_item';

        $this->assertFalse($grade_item->qualifies_for_regrading());

        $grade_item->grademin = 14;

        $this->assertTrue($grade_item->qualifies_for_regrading());
    }

    protected function sub_test_grade_item_force_regrading() {
        $grade_item = new grade_item($this->grade_items[3], false);         $this->assertTrue(method_exists($grade_item, 'force_regrading'));

        $this->assertEquals(0, $grade_item->needsupdate);

        $grade_item->force_regrading();
        $this->assertEquals(1, $grade_item->needsupdate);
        $grade_item->update_from_db();
        $this->assertEquals(1, $grade_item->needsupdate);
    }

    protected function sub_test_grade_item_fetch() {
        $grade_item = new grade_item();
        $this->assertTrue(method_exists($grade_item, 'fetch'));

                $grade_item = grade_item::fetch(array('id'=>$this->grade_items[1]->id));
        $this->assertEquals($this->grade_items[1]->id, $grade_item->id);
        $this->assertEquals($this->grade_items[1]->iteminfo, $grade_item->iteminfo);

        $grade_item = grade_item::fetch(array('itemtype'=>$this->grade_items[1]->itemtype, 'itemmodule'=>$this->grade_items[1]->itemmodule));
        $this->assertEquals($this->grade_items[1]->id, $grade_item->id);
        $this->assertEquals($this->grade_items[1]->iteminfo, $grade_item->iteminfo);
    }

    protected function sub_test_grade_item_fetch_all() {
        $grade_item = new grade_item();
        $this->assertTrue(method_exists($grade_item, 'fetch_all'));

        $grade_items = grade_item::fetch_all(array('courseid'=>$this->courseid));
        $this->assertEquals(count($this->grade_items), count($grade_items)-1);     }

        protected function sub_test_grade_item_get_all_finals() {
        $grade_item = new grade_item($this->grade_items[0], false);
        $this->assertTrue(method_exists($grade_item, 'get_final'));

        $final_grades = $grade_item->get_final();
        $this->assertEquals(3, count($final_grades));
    }


        protected function sub_test_grade_item_get_final() {
        $grade_item = new grade_item($this->grade_items[0], false);
        $this->assertTrue(method_exists($grade_item, 'get_final'));
        $final_grade = $grade_item->get_final($this->user[1]->id);
        $this->assertEquals($this->grade_grades[0]->finalgrade, $final_grade->finalgrade);
    }

    protected function sub_test_grade_item_get_sortorder() {
        $grade_item = new grade_item($this->grade_items[0], false);
        $this->assertTrue(method_exists($grade_item, 'get_sortorder'));
        $sortorder = $grade_item->get_sortorder();
        $this->assertEquals($this->grade_items[0]->sortorder, $sortorder);
    }

    protected function sub_test_grade_item_set_sortorder() {
        $grade_item = new grade_item($this->grade_items[0], false);
        $this->assertTrue(method_exists($grade_item, 'set_sortorder'));
        $grade_item->set_sortorder(999);
        $this->assertEquals($grade_item->sortorder, 999);
    }

    protected function sub_test_grade_item_move_after_sortorder() {
        $grade_item = new grade_item($this->grade_items[0], false);
        $this->assertTrue(method_exists($grade_item, 'move_after_sortorder'));
        $grade_item->move_after_sortorder(5);
        $this->assertEquals($grade_item->sortorder, 6);

        $grade_item = grade_item::fetch(array('id'=>$this->grade_items[0]->id));
        $this->assertEquals($grade_item->sortorder, 6);

        $after = grade_item::fetch(array('id'=>$this->grade_items[6]->id));
        $this->assertEquals($after->sortorder, 8);
    }

    protected function sub_test_grade_item_get_name() {
        $grade_item = new grade_item($this->grade_items[0], false);
        $this->assertTrue(method_exists($grade_item, 'get_name'));

        $name = $grade_item->get_name();
        $this->assertEquals($this->grade_items[0]->itemname, $name);
    }

    protected function sub_test_grade_item_set_parent() {
        $grade_item = new grade_item($this->grade_items[0], false);
        $this->assertTrue(method_exists($grade_item, 'set_parent'));

        $old = $grade_item->get_parent_category();
        $new = new grade_category($this->grade_categories[3], false);
        $new_item = $new->get_grade_item();

        $this->assertTrue($grade_item->set_parent($new->id));

        $new_item->update_from_db();
        $grade_item->update_from_db();

        $this->assertEquals($grade_item->categoryid, $new->id);
    }

    protected function sub_test_grade_item_get_parent_category() {
        $grade_item = new grade_item($this->grade_items[0], false);
        $this->assertTrue(method_exists($grade_item, 'get_parent_category'));

        $category = $grade_item->get_parent_category();
        $this->assertEquals($this->grade_categories[1]->fullname, $category->fullname);
    }

    protected function sub_test_grade_item_load_parent_category() {
        $grade_item = new grade_item($this->grade_items[0], false);
        $this->assertTrue(method_exists($grade_item, 'load_parent_category'));

        $category = $grade_item->load_parent_category();
        $this->assertEquals($this->grade_categories[1]->fullname, $category->fullname);
        $this->assertEquals($this->grade_categories[1]->fullname, $grade_item->parent_category->fullname);
    }

    protected function sub_test_grade_item_get_item_category() {
        $grade_item = new grade_item($this->grade_items[3], false);
        $this->assertTrue(method_exists($grade_item, 'get_item_category'));

        $category = $grade_item->get_item_category();
        $this->assertEquals($this->grade_categories[0]->fullname, $category->fullname);
    }

    protected function sub_test_grade_item_load_item_category() {
        $grade_item = new grade_item($this->grade_items[3], false);
        $this->assertTrue(method_exists($grade_item, 'load_item_category'));

        $category = $grade_item->load_item_category();
        $this->assertEquals($this->grade_categories[0]->fullname, $category->fullname);
        $this->assertEquals($this->grade_categories[0]->fullname, $grade_item->item_category->fullname);
    }

    protected function sub_test_grade_item_regrade_final_grades() {
        $grade_item = new grade_item($this->grade_items[0], false);
        $this->assertTrue(method_exists($grade_item, 'regrade_final_grades'));
        $this->assertEquals(true, $grade_item->regrade_final_grades());
            }

    protected function sub_test_grade_item_adjust_raw_grade() {
        $grade_item = new grade_item($this->grade_items[2], false);         $this->assertTrue(method_exists($grade_item, 'adjust_raw_grade'));

        $grade_raw = new stdClass();
        $grade_raw->rawgrade = 40;
        $grade_raw->grademax = 100;
        $grade_raw->grademin = 0;

        $grade_item->gradetype = GRADE_TYPE_VALUE;
        $grade_item->multfactor = 1;
        $grade_item->plusfactor = 0;
        $grade_item->grademax = 50;
        $grade_item->grademin = 0;

        $original_grade_raw  = clone($grade_raw);
        $original_grade_item = clone($grade_item);

        $this->assertEquals(20, $grade_item->adjust_raw_grade($grade_raw->rawgrade, $grade_raw->grademin, $grade_raw->grademax));

                $grade_item->grademax = 150;
        $grade_item->grademin = 0;
        $this->assertEquals(60, $grade_item->adjust_raw_grade($grade_raw->rawgrade, $grade_raw->grademin, $grade_raw->grademax));

                $grade_item->grademin = 50;

        $this->assertEquals(90, $grade_item->adjust_raw_grade($grade_raw->rawgrade, $grade_raw->grademin, $grade_raw->grademax));

                $grade_raw->grademax = 50;
        $grade_raw->grademin = 0;
        $grade_item->grademax = 100;
        $grade_item->grademin = 0;

        $this->assertEquals(80, $grade_item->adjust_raw_grade($grade_raw->rawgrade, $grade_raw->grademin, $grade_raw->grademax));

                $grade_item->grademax = 100;
        $grade_item->grademin = 40;

        $this->assertEquals(88, $grade_item->adjust_raw_grade($grade_raw->rawgrade, $grade_raw->grademin, $grade_raw->grademax));

                $grade_raw = clone($original_grade_raw);
        $grade_item = clone($original_grade_item);
        $grade_item->multfactor = 1.23;
        $grade_item->plusfactor = 3;

        $this->assertEquals(27.6, $grade_item->adjust_raw_grade($grade_raw->rawgrade, $grade_raw->grademin, $grade_raw->grademax));

                $grade_raw = clone($original_grade_raw);
        $grade_item = clone($original_grade_item);
        $grade_item->multfactor = 0.23;
        $grade_item->plusfactor = -3;

        $this->assertEquals(round(1.6), round($grade_item->adjust_raw_grade($grade_raw->rawgrade, $grade_raw->grademin, $grade_raw->grademax)));
    }

    protected function sub_test_grade_item_rescale_grades_keep_percentage() {
        global $DB;
        $gradeitem = new grade_item($this->grade_items[10], false); 
                $gradeids = array();
        $grade = new stdClass();
        $grade->itemid = $gradeitem->id;
        $grade->userid = $this->user[2]->id;
        $grade->finalgrade = 10;
        $grade->rawgrademax = $gradeitem->grademax;
        $grade->rawgrademin = $gradeitem->grademin;
        $grade->timecreated = time();
        $grade->timemodified = time();
        $gradeids[] = $DB->insert_record('grade_grades', $grade);

        $grade->userid = $this->user[3]->id;
        $grade->finalgrade = 50;
        $grade->rawgrademax = $gradeitem->grademax;
        $grade->rawgrademin = $gradeitem->grademin;
        $gradeids[] = $DB->insert_record('grade_grades', $grade);

                $gradeitem->grademax = 33;
        $gradeitem->grademin = 3;
        $gradeitem->update();
        $gradeitem->rescale_grades_keep_percentage(0, 100, 3, 33, 'test');

                $grade = $DB->get_record('grade_grades', array('id' => $gradeids[0]));
        $this->assertEquals($gradeitem->grademax, $grade->rawgrademax, 'Max grade mismatch', 0.0001);
        $this->assertEquals($gradeitem->grademin, $grade->rawgrademin, 'Min grade mismatch', 0.0001);
        $this->assertEquals(6, $grade->finalgrade, 'Min grade mismatch', 0.0001);

        $grade = $DB->get_record('grade_grades', array('id' => $gradeids[1]));
        $this->assertEquals($gradeitem->grademax, $grade->rawgrademax, 'Max grade mismatch', 0.0001);
        $this->assertEquals($gradeitem->grademin, $grade->rawgrademin, 'Min grade mismatch', 0.0001);
        $this->assertEquals(18, $grade->finalgrade, 'Min grade mismatch', 0.0001);
    }

    protected function sub_test_grade_item_set_locked() {
                                $grade_item = grade_item::fetch(array('id'=>$this->grade_items[8]->id));

        $this->assertTrue(method_exists($grade_item, 'set_locked'));

        $grade_grade = new grade_grade($grade_item->get_final($this->user[1]->id), false);
        $this->assertTrue(empty($grade_item->locked));        $this->assertTrue(empty($grade_grade->locked));
        $this->assertTrue($grade_item->set_locked(true, true, false));
        $grade_grade = new grade_grade($grade_item->get_final($this->user[1]->id), false);

        $this->assertFalse(empty($grade_item->locked));        $this->assertFalse(empty($grade_grade->locked)); 
        $this->assertTrue($grade_item->set_locked(false, true, false));
        $grade = new grade_grade($grade_item->get_final($this->user[1]->id), false);

        $this->assertTrue(empty($grade_item->locked));
        $this->assertTrue(empty($grade->locked));     }

    protected function sub_test_grade_item_is_locked() {
        $grade_item = new grade_item($this->grade_items[10], false);
        $this->assertTrue(method_exists($grade_item, 'is_locked'));

        $this->assertFalse($grade_item->is_locked());
        $this->assertFalse($grade_item->is_locked($this->user[1]->id));
        $this->assertTrue($grade_item->set_locked(true, true, false));
        $this->assertTrue($grade_item->is_locked());
        $this->assertTrue($grade_item->is_locked($this->user[1]->id));
    }

    protected function sub_test_grade_item_set_hidden() {
        $grade_item = new grade_item($this->grade_items[0], false);
        $this->assertTrue(method_exists($grade_item, 'set_hidden'));

        $grade = new grade_grade($grade_item->get_final($this->user[1]->id), false);
        $this->assertEquals(0, $grade_item->hidden);
        $this->assertEquals(0, $grade->hidden);

        $grade_item->set_hidden(666, true);
        $grade = new grade_grade($grade_item->get_final($this->user[1]->id), false);

        $this->assertEquals(666, $grade_item->hidden);
        $this->assertEquals(666, $grade->hidden);
    }

    protected function sub_test_grade_item_is_hidden() {
        $grade_item = new grade_item($this->grade_items[0], false);
        $this->assertTrue(method_exists($grade_item, 'is_hidden'));

        $this->assertFalse($grade_item->is_hidden());
        $this->assertFalse($grade_item->is_hidden(1));

        $grade_item->set_hidden(1);
        $this->assertTrue($grade_item->is_hidden());
        $this->assertTrue($grade_item->is_hidden(1));

        $grade_item->set_hidden(666);
        $this->assertFalse($grade_item->is_hidden());
        $this->assertFalse($grade_item->is_hidden(1));

        $grade_item->set_hidden(time()+666);
        $this->assertTrue($grade_item->is_hidden());
        $this->assertTrue($grade_item->is_hidden(1));
    }

    protected function sub_test_grade_item_is_category_item() {
        $grade_item = new grade_item($this->grade_items[3], false);
        $this->assertTrue(method_exists($grade_item, 'is_category_item'));
        $this->assertTrue($grade_item->is_category_item());
    }

    protected function sub_test_grade_item_is_course_item() {
        $grade_item = grade_item::fetch_course_item($this->courseid);
        $this->assertTrue(method_exists($grade_item, 'is_course_item'));
        $this->assertTrue($grade_item->is_course_item());
    }

    protected function sub_test_grade_item_fetch_course_item() {
        $grade_item = grade_item::fetch_course_item($this->courseid);
        $this->assertTrue(method_exists($grade_item, 'fetch_course_item'));
        $this->assertEquals($grade_item->itemtype, 'course');
    }

    protected function sub_test_grade_item_depends_on() {
        global $CFG;

        $origenableoutcomes = $CFG->enableoutcomes;
        $CFG->enableoutcomes = 0;
        $grade_item = new grade_item($this->grade_items[1], false);

                $deps = $grade_item->depends_on();
        sort($deps, SORT_NUMERIC);         $this->assertEquals(array($this->grade_items[0]->id), $deps);

                $grade_item->locked = time();
        $grade_item->update();
        $deps = $grade_item->depends_on();
        sort($deps, SORT_NUMERIC);         $this->assertEquals(array(), $deps);

                $grade_item = new grade_item($this->grade_items[3], false);
        $deps = $grade_item->depends_on();
        sort($deps, SORT_NUMERIC);         $res = array($this->grade_items[4]->id, $this->grade_items[5]->id);
        $this->assertEquals($res, $deps);
    }

    protected function scales_outcomes_test_grade_item_depends_on() {
        $CFG->enableoutcomes = 1;
        $origgradeincludescalesinaggregation = $CFG->grade_includescalesinaggregation;
        $CFG->grade_includescalesinaggregation = 1;

                $grade_item = new grade_item($this->grade_items[14], false);
        $deps = $grade_item->depends_on();
        sort($deps, SORT_NUMERIC);
        $res = array($this->grade_items[16]->id);
        $this->assertEquals($res, $deps);

                $CFG->grade_includescalesinaggregation = 0;
        $grade_item = new grade_item($this->grade_items[14], false);
        $deps = $grade_item->depends_on();
        $res = array();
        $this->assertEquals($res, $deps);
        $CFG->grade_includescalesinaggregation = 1;

                $CFG->enableoutcomes = 0;
        $grade_item = new grade_item($this->grade_items[14], false);
        $deps = $grade_item->depends_on();
        sort($deps, SORT_NUMERIC);
        $res = array($this->grade_items[16]->id, $this->grade_items[17]->id);
        $this->assertEquals($res, $deps);

        $CFG->enableoutcomes = $origenableoutcomes;
        $CFG->grade_includescalesinaggregation = $origgradeincludescalesinaggregation;
    }

    protected function sub_test_refresh_grades() {
                $grade_item = new grade_item($this->grade_items[0], false);
        $this->assertTrue(method_exists($grade_item, 'refresh_grades'));
        $this->assertTrue($grade_item->refresh_grades());

                $grade_item->iteminstance = 123456789;
        $this->assertFalse($grade_item->refresh_grades());
        $this->assertDebuggingCalled();
    }

    protected function sub_test_grade_item_is_calculated() {
        $grade_item = new grade_item($this->grade_items[1], false);
        $this->assertTrue(method_exists($grade_item, 'is_calculated'));
        $this->assertTrue($grade_item->is_calculated());

        $grade_item = new grade_item($this->grade_items[0], false);
        $this->assertFalse($grade_item->is_calculated());
    }

    protected function sub_test_grade_item_set_calculation() {
        $grade_item = new grade_item($this->grade_items[1], false);
        $this->assertTrue(method_exists($grade_item, 'set_calculation'));
        $grade_itemsource = new grade_item($this->grade_items[0], false);

        $grade_item->set_calculation('=[['.$grade_itemsource->idnumber.']]');

        $this->assertTrue(!empty($grade_item->needsupdate));
        $this->assertEquals('=##gi'.$grade_itemsource->id.'##', $grade_item->calculation);
    }

    protected function sub_test_grade_item_get_calculation() {
        $grade_item = new grade_item($this->grade_items[1], false);
        $this->assertTrue(method_exists($grade_item, 'get_calculation'));
        $grade_itemsource = new grade_item($this->grade_items[0], false);

        $denormalizedformula = str_replace('##gi'.$grade_itemsource->id.'##', '[['.$grade_itemsource->idnumber.']]', $this->grade_items[1]->calculation);

        $formula = $grade_item->get_calculation();
        $this->assertTrue(!empty($grade_item->needsupdate));
        $this->assertEquals($denormalizedformula, $formula);
    }

    public function sub_test_grade_item_compute() {
        $grade_item = grade_item::fetch(array('id'=>$this->grade_items[1]->id));
        $this->assertTrue(method_exists($grade_item, 'compute'));

                $this->grade_grades[3] = grade_grade::fetch(array('id'=>$this->grade_grades[3]->id));
        $grade_grade = grade_grade::fetch(array('id'=>$this->grade_grades[3]->id));
        $grade_grade->delete();

        $this->grade_grades[4] = grade_grade::fetch(array('id'=>$this->grade_grades[4]->id));
        $grade_grade = grade_grade::fetch(array('id'=>$this->grade_grades[4]->id));
        $grade_grade->delete();

        $this->grade_grades[5] = grade_grade::fetch(array('id'=>$this->grade_grades[5]->id));
        $grade_grade = grade_grade::fetch(array('id'=>$this->grade_grades[5]->id));
        $grade_grade->delete();

                $grade_item->compute();

        $grade_grade = grade_grade::fetch(array('userid'=>$this->grade_grades[3]->userid, 'itemid'=>$this->grade_grades[3]->itemid));
        $this->assertEquals($this->grade_grades[3]->finalgrade, $grade_grade->finalgrade);

        $grade_grade = grade_grade::fetch(array('userid'=>$this->grade_grades[4]->userid, 'itemid'=>$this->grade_grades[4]->itemid));
        $this->assertEquals($this->grade_grades[4]->finalgrade, $grade_grade->finalgrade);

        $grade_grade = grade_grade::fetch(array('userid'=>$this->grade_grades[5]->userid, 'itemid'=>$this->grade_grades[5]->itemid));
        $this->assertEquals($this->grade_grades[5]->finalgrade, $grade_grade->finalgrade);
    }

    protected function sub_test_update_final_grade() {

                        $min = 2;
        $max = 8;

                $grade_item = new grade_item();
        $this->assertTrue(method_exists($grade_item, 'insert'));

        $grade_item->courseid = $this->courseid;
        $grade_item->categoryid = $this->grade_categories[1]->id;
        $grade_item->itemname = 'brand new unit test grade item';
        $grade_item->itemtype = 'mod';
        $grade_item->itemmodule = 'quiz';
        $grade_item->iteminfo = 'Grade item used for unit testing';
        $grade_item->iteminstance = $this->activities[7]->id;
        $grade_item->grademin = $min;
        $grade_item->grademax = $max;
        $grade_item->insert();

                $grade_item->update_final_grade($this->user[1]->id, 7, 'gradebook', '', FORMAT_MOODLE);

                $grade_grade = grade_grade::fetch(array('userid'=>$this->user[1]->id, 'itemid'=>$grade_item->id));
        $this->assertEquals($min, $grade_grade->rawgrademin);
        $this->assertEquals($max, $grade_grade->rawgrademax);
    }

    protected function sub_test_grade_item_can_control_visibility() {
                $grade_item = new grade_item($this->grade_items[0], false);
        $this->assertTrue($grade_item->can_control_visibility());

                $grade_item = new grade_item($this->grade_items[11], false);
        $this->assertFalse($grade_item->can_control_visibility());
    }

    
    public function sub_test_grade_item_fix_sortorder() {
        global $DB;

        $this->resetAfterTest(true);

                        $testsets = array(
                        array(1,2,3),
            array(5,6,7),
            array(7,6,1,3,2,5),
                        array(1,2,2,3,3,4,5),
                        array(1,1),
            array(3,3),
                        array(3,3,7,5,6,6,9,10,8,3),
            array(7,7,3),
            array(3,4,5,3,5,4,7,1)
        );
        $origsequence = array();

                foreach ($testsets as $testset) {
            $course = $this->getDataGenerator()->create_course();
            foreach ($testset as $sortorder) {
                $this->insert_fake_grade_item_sortorder($course->id, $sortorder);
            }
            $DB->get_records('grade_items');
            $origsequence[$course->id] = $DB->get_fieldset_sql("SELECT id FROM {grade_items} ".
                "WHERE courseid = ? ORDER BY sortorder, id", array($course->id));
        }

        $duplicatedetectionsql = "SELECT courseid, sortorder
                                    FROM {grade_items}
                                WHERE courseid = :courseid
                                GROUP BY courseid, sortorder
                                  HAVING COUNT(id) > 1";

                foreach ($origsequence as $courseid => $ignore) {
            grade_item::fix_duplicate_sortorder($courseid);
                        $dupes = $DB->record_exists_sql($duplicatedetectionsql, array('courseid' => $courseid));
            $this->assertFalse($dupes);
        }

                $idx = 0;
        foreach ($origsequence as $courseid => $sequence) {
            if (count(($testsets[$idx])) == count(array_unique($testsets[$idx]))) {
                                $newsortorders = $DB->get_fieldset_sql("SELECT sortorder from {grade_items} WHERE courseid=? ORDER BY id", array($courseid));
                $this->assertEquals($testsets[$idx], $newsortorders);
            }
            $newsequence = $DB->get_fieldset_sql("SELECT id FROM {grade_items} ".
                "WHERE courseid = ? ORDER BY sortorder, id", array($courseid));
            $this->assertEquals($sequence, $newsequence,
                    "Sequences do not match for test set $idx : ".join(',', $testsets[$idx]));
            $idx++;
        }
    }

    
    private function insert_fake_grade_item_sortorder($courseid, $sortorder) {
        global $DB, $CFG;
        require_once($CFG->libdir.'/gradelib.php');

        $item = new stdClass();
        $item->courseid = $courseid;
        $item->sortorder = $sortorder;
        $item->gradetype = GRADE_TYPE_VALUE;
        $item->grademin = 30;
        $item->grademax = 110;
        $item->itemnumber = 1;
        $item->iteminfo = '';
        $item->timecreated = time();
        $item->timemodified = time();

        $item->id = $DB->insert_record('grade_items', $item);

        return $DB->get_record('grade_items', array('id' => $item->id));
    }

    public function test_set_aggregation_fields_for_aggregation() {
        $course = $this->getDataGenerator()->create_course();
        $gi = new grade_item(array('courseid' => $course->id, 'itemtype' => 'manual'), false);

        $methods = array(GRADE_AGGREGATE_MEAN, GRADE_AGGREGATE_MEDIAN, GRADE_AGGREGATE_MIN, GRADE_AGGREGATE_MAX,
            GRADE_AGGREGATE_MODE, GRADE_AGGREGATE_WEIGHTED_MEAN, GRADE_AGGREGATE_WEIGHTED_MEAN2,
            GRADE_AGGREGATE_EXTRACREDIT_MEAN, GRADE_AGGREGATE_SUM);

                foreach ($methods as $method) {
            $defaults = grade_category::get_default_aggregation_coefficient_values($method);
            $gi->aggregationcoef = $defaults['aggregationcoef'];
            $gi->aggregationcoef2 = $defaults['aggregationcoef2'];
            $gi->weightoverride = $defaults['weightoverride'];
            $this->assertFalse($gi->set_aggregation_fields_for_aggregation($method, $method));
            $this->assertEquals($defaults['aggregationcoef'], $gi->aggregationcoef);
            $this->assertEquals($defaults['aggregationcoef2'], $gi->aggregationcoef2);
            $this->assertEquals($defaults['weightoverride'], $gi->weightoverride);
        }

                foreach ($methods as $from) {
            $fromsupportsec = grade_category::aggregation_uses_extracredit($from);
            $fromdefaults = grade_category::get_default_aggregation_coefficient_values($from);

            foreach ($methods as $to) {
                $tosupportsec = grade_category::aggregation_uses_extracredit($to);
                $todefaults = grade_category::get_default_aggregation_coefficient_values($to);

                                if ($fromsupportsec) {
                    $gi->aggregationcoef = 1;
                } else {
                    $gi->aggregationcoef = $fromdefaults['aggregationcoef'];
                }

                                $gi->aggregationcoef2 = $todefaults['aggregationcoef2'];
                $gi->weightoverride = $todefaults['weightoverride'];

                if ($fromsupportsec && $tosupportsec) {
                    $this->assertFalse($gi->set_aggregation_fields_for_aggregation($from, $to), "From: $from, to: $to");
                    $this->assertEquals(1, $gi->aggregationcoef);

                } else if ($fromsupportsec && !$tosupportsec) {
                    if ($to == GRADE_AGGREGATE_WEIGHTED_MEAN) {
                                                $this->assertFalse($gi->set_aggregation_fields_for_aggregation($from, $to), "From: $from, to: $to");
                        $this->assertEquals($todefaults['aggregationcoef'], $gi->aggregationcoef);
                    } else {
                        $this->assertTrue($gi->set_aggregation_fields_for_aggregation($from, $to), "From: $from, to: $to");
                        $this->assertEquals($todefaults['aggregationcoef'], $gi->aggregationcoef);
                    }
                } else {
                                        if (($from == GRADE_AGGREGATE_WEIGHTED_MEAN || $to == GRADE_AGGREGATE_WEIGHTED_MEAN) && $from != $to) {
                                                $this->assertTrue($gi->set_aggregation_fields_for_aggregation($from, $to), "From: $from, to: $to");
                        $this->assertEquals($todefaults['aggregationcoef'], $gi->aggregationcoef);
                    } else {
                        $this->assertFalse($gi->set_aggregation_fields_for_aggregation($from, $to), "From: $from, to: $to");
                        $this->assertEquals($todefaults['aggregationcoef'], $gi->aggregationcoef);
                    }
                }
            }
        }

                $from = GRADE_AGGREGATE_EXTRACREDIT_MEAN;
        $fromdefaults = grade_category::get_default_aggregation_coefficient_values($from);

        foreach ($methods as $to) {
            if (!grade_category::aggregation_uses_extracredit($to)) {
                continue;
            }

            $todefaults = grade_category::get_default_aggregation_coefficient_values($to);
            $gi->aggregationcoef = 8;

                        $gi->aggregationcoef2 = $todefaults['aggregationcoef2'];
            $gi->weightoverride = $todefaults['weightoverride'];

            if ($to == $from) {
                $this->assertFalse($gi->set_aggregation_fields_for_aggregation($from, $to), "From: $from, to: $to");
                $this->assertEquals(8, $gi->aggregationcoef);
            } else {
                $this->assertTrue($gi->set_aggregation_fields_for_aggregation($from, $to), "From: $from, to: $to");
                $this->assertEquals(1, $gi->aggregationcoef);
            }
        }

                $from = GRADE_AGGREGATE_SUM;
        $fromdefaults = grade_category::get_default_aggregation_coefficient_values($from);

        $gi->aggregationcoef = $fromdefaults['aggregationcoef'];
        $gi->aggregationcoef2 = 0.321;
        $gi->weightoverride = $fromdefaults['weightoverride'];

        $to = GRADE_AGGREGATE_WEIGHTED_MEAN;
        $todefaults = grade_category::get_default_aggregation_coefficient_values($to);

        $this->assertTrue($gi->set_aggregation_fields_for_aggregation($from, $to), "From: $from, to: $to");
        $this->assertEquals($todefaults['aggregationcoef'], $gi->aggregationcoef);
        $this->assertEquals($todefaults['aggregationcoef2'], $gi->aggregationcoef2);
        $this->assertEquals($todefaults['weightoverride'], $gi->weightoverride);

        $gi->aggregationcoef = $fromdefaults['aggregationcoef'];
        $gi->aggregationcoef2 = 0.321;
        $gi->weightoverride = $fromdefaults['weightoverride'];

        $to = GRADE_AGGREGATE_SUM;
        $todefaults = grade_category::get_default_aggregation_coefficient_values($to);

        $this->assertTrue($gi->set_aggregation_fields_for_aggregation($from, $to), "From: $from, to: $to");
        $this->assertEquals($todefaults['aggregationcoef'], $gi->aggregationcoef);
        $this->assertEquals($todefaults['aggregationcoef2'], $gi->aggregationcoef2);
        $this->assertEquals($todefaults['weightoverride'], $gi->weightoverride);

                $from = GRADE_AGGREGATE_SUM;
        $fromdefaults = grade_category::get_default_aggregation_coefficient_values($from);

        $gi->aggregationcoef = $fromdefaults['aggregationcoef'];
        $gi->aggregationcoef2 = 0.321;
        $gi->weightoverride = 1;

        $to = GRADE_AGGREGATE_SUM;
        $todefaults = grade_category::get_default_aggregation_coefficient_values($to);

        $this->assertFalse($gi->set_aggregation_fields_for_aggregation($from, $to), "From: $from, to: $to");
        $this->assertEquals($todefaults['aggregationcoef'], $gi->aggregationcoef);
        $this->assertEquals(0.321, $gi->aggregationcoef2);
        $this->assertEquals(1, $gi->weightoverride);

        $gi->aggregationcoef2 = 0.321;
        $gi->aggregationcoef = $fromdefaults['aggregationcoef'];
        $gi->weightoverride = 1;

        $to = GRADE_AGGREGATE_WEIGHTED_MEAN;
        $todefaults = grade_category::get_default_aggregation_coefficient_values($to);

        $this->assertTrue($gi->set_aggregation_fields_for_aggregation($from, $to), "From: $from, to: $to");
        $this->assertEquals($todefaults['aggregationcoef'], $gi->aggregationcoef);
        $this->assertEquals($todefaults['aggregationcoef2'], $gi->aggregationcoef2);
        $this->assertEquals($todefaults['weightoverride'], $gi->weightoverride);

                $from = GRADE_AGGREGATE_WEIGHTED_MEAN;
        $fromdefaults = grade_category::get_default_aggregation_coefficient_values($from);

        $gi->aggregationcoef = 18;
        $gi->aggregationcoef2 = $fromdefaults['aggregationcoef2'];
        $gi->weightoverride = $fromdefaults['weightoverride'];

        $to = GRADE_AGGREGATE_WEIGHTED_MEAN;
        $todefaults = grade_category::get_default_aggregation_coefficient_values($to);

        $this->assertFalse($gi->set_aggregation_fields_for_aggregation($from, $to), "From: $from, to: $to");
        $this->assertEquals(18, $gi->aggregationcoef);
        $this->assertEquals($todefaults['aggregationcoef2'], $gi->aggregationcoef2);
        $this->assertEquals($todefaults['weightoverride'], $gi->weightoverride);

        $gi->aggregationcoef = 18;
        $gi->aggregationcoef2 = $fromdefaults['aggregationcoef2'];
        $gi->weightoverride = $fromdefaults['weightoverride'];

        $to = GRADE_AGGREGATE_SUM;
        $todefaults = grade_category::get_default_aggregation_coefficient_values($to);

        $this->assertTrue($gi->set_aggregation_fields_for_aggregation($from, $to), "From: $from, to: $to");
        $this->assertEquals($todefaults['aggregationcoef'], $gi->aggregationcoef);
        $this->assertEquals($todefaults['aggregationcoef2'], $gi->aggregationcoef2);
        $this->assertEquals($todefaults['weightoverride'], $gi->weightoverride);
    }

}
