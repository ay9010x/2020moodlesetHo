<?php



defined('MOODLE_INTERNAL') || die();

global $CFG;
require_once($CFG->libdir.'/upgradelib.php');


class core_upgradelib_testcase extends advanced_testcase {

    
    public function test_upgrade_stale_php_files_present() {
                        $this->assertFalse(upgrade_stale_php_files_present());
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

    public function test_upgrade_fix_missing_root_folders_draft() {
        global $DB, $SITE;

        $this->resetAfterTest(true);

        $user = $this->getDataGenerator()->create_user();
        $usercontext = context_user::instance($user->id);
        $this->setUser($user);
        $resource1 = $this->getDataGenerator()->get_plugin_generator('mod_resource')
            ->create_instance(array('course' => $SITE->id));
        $context = context_module::instance($resource1->cmid);
        $draftitemid = 0;
        file_prepare_draft_area($draftitemid, $context->id, 'mod_resource', 'content', 0);

        $queryparams = array(
            'component' => 'user',
            'contextid' => $usercontext->id,
            'filearea' => 'draft',
            'itemid' => $draftitemid,
        );

                $records = $DB->get_records_menu('files', $queryparams, '', 'id, filename');
        $this->assertEquals(2, count($records));
        $this->assertTrue(in_array('.', $records));
        $originalhash = $DB->get_field('files', 'pathnamehash', $queryparams + array('filename' => '.'));

                $DB->delete_records('files', $queryparams + array('filename' => '.'));

        $records = $DB->get_records_menu('files', $queryparams, '', 'id, filename');
        $this->assertEquals(1, count($records));
        $this->assertFalse(in_array('.', $records));

                upgrade_fix_missing_root_folders_draft();

        $records = $DB->get_records_menu('files', $queryparams, '', 'id, filename');
        $this->assertEquals(2, count($records));
        $this->assertTrue(in_array('.', $records));
        $newhash = $DB->get_field('files', 'pathnamehash', $queryparams + array('filename' => '.'));
        $this->assertEquals($originalhash, $newhash);
    }

    
    public function test_upgrade_minmaxgrade() {
        global $CFG, $DB;
        require_once($CFG->libdir . '/gradelib.php');
        $initialminmax = $CFG->grade_minmaxtouse;
        $this->resetAfterTest();

        $c1 = $this->getDataGenerator()->create_course();
        $c2 = $this->getDataGenerator()->create_course();
        $c3 = $this->getDataGenerator()->create_course();
        $u1 = $this->getDataGenerator()->create_user();
        $a1 = $this->getDataGenerator()->create_module('assign', array('course' => $c1, 'grade' => 100));
        $a2 = $this->getDataGenerator()->create_module('assign', array('course' => $c2, 'grade' => 100));
        $a3 = $this->getDataGenerator()->create_module('assign', array('course' => $c3, 'grade' => 100));

        $cm1 = get_coursemodule_from_instance('assign', $a1->id);
        $ctx1 = context_module::instance($cm1->id);
        $assign1 = new assign($ctx1, $cm1, $c1);

        $cm2 = get_coursemodule_from_instance('assign', $a2->id);
        $ctx2 = context_module::instance($cm2->id);
        $assign2 = new assign($ctx2, $cm2, $c2);

        $cm3 = get_coursemodule_from_instance('assign', $a3->id);
        $ctx3 = context_module::instance($cm3->id);
        $assign3 = new assign($ctx3, $cm3, $c3);

                $ug = $assign1->get_user_grade($u1->id, true);
        $ug->grade = 10;
        $assign1->update_grade($ug);

        $ug = $assign2->get_user_grade($u1->id, true);
        $ug->grade = 20;
        $assign2->update_grade($ug);

        $ug = $assign3->get_user_grade($u1->id, true);
        $ug->grade = 30;
        $assign3->update_grade($ug);


                upgrade_minmaxgrade();

                $this->assertFalse($DB->record_exists('config', array('name' => 'show_min_max_grades_changed_' . $c1->id)));
        $this->assertSame(false, grade_get_setting($c1->id, 'minmaxtouse', false, true));
        $this->assertFalse($DB->record_exists('grade_items', array('needsupdate' => 1, 'courseid' => $c1->id)));
        $this->assertFalse($DB->record_exists('config', array('name' => 'show_min_max_grades_changed_' . $c2->id)));
        $this->assertSame(false, grade_get_setting($c2->id, 'minmaxtouse', false, true));
        $this->assertFalse($DB->record_exists('grade_items', array('needsupdate' => 1, 'courseid' => $c2->id)));
        $this->assertFalse($DB->record_exists('config', array('name' => 'show_min_max_grades_changed_' . $c3->id)));
        $this->assertSame(false, grade_get_setting($c3->id, 'minmaxtouse', false, true));
        $this->assertFalse($DB->record_exists('grade_items', array('needsupdate' => 1, 'courseid' => $c3->id)));

                $giparams = array('itemtype' => 'mod', 'itemmodule' => 'assign', 'iteminstance' => $a1->id,
                'courseid' => $c1->id, 'itemnumber' => 0);
        $gi = grade_item::fetch($giparams);
        $gi->grademin = 5;
        $gi->update();

        $giparams = array('itemtype' => 'mod', 'itemmodule' => 'assign', 'iteminstance' => $a2->id,
                'courseid' => $c2->id, 'itemnumber' => 0);
        $gi = grade_item::fetch($giparams);
        $gi->grademax = 50;
        $gi->update();


                $CFG->grade_minmaxtouse = GRADE_MIN_MAX_FROM_GRADE_GRADE;

                upgrade_minmaxgrade();

                $this->assertTrue($DB->record_exists('config', array('name' => 'show_min_max_grades_changed_' . $c1->id)));
        $this->assertSame(false, grade_get_setting($c1->id, 'minmaxtouse', false, true));
        $this->assertTrue($DB->record_exists('grade_items', array('needsupdate' => 1, 'courseid' => $c1->id)));
        $this->assertTrue($DB->record_exists('config', array('name' => 'show_min_max_grades_changed_' . $c2->id)));
        $this->assertSame(false, grade_get_setting($c2->id, 'minmaxtouse', false, true));
        $this->assertTrue($DB->record_exists('grade_items', array('needsupdate' => 1, 'courseid' => $c2->id)));

                $this->assertFalse($DB->record_exists('config', array('name' => 'show_min_max_grades_changed_' . $c3->id)));
        $this->assertSame(false, grade_get_setting($c3->id, 'minmaxtouse', false, true));
        $this->assertFalse($DB->record_exists('grade_items', array('needsupdate' => 1, 'courseid' => $c3->id)));


                $CFG->grade_minmaxtouse = GRADE_MIN_MAX_FROM_GRADE_ITEM;
        grade_set_setting($c1->id, 'minmaxtouse', -1); 
                upgrade_minmaxgrade();

                $this->assertSame((string) GRADE_MIN_MAX_FROM_GRADE_GRADE, grade_get_setting($c2->id, 'minmaxtouse', false, true));

                $this->assertSame('-1', grade_get_setting($c1->id, 'minmaxtouse', false, true));

                $this->assertFalse($DB->record_exists('config', array('name' => 'show_min_max_grades_changed_' . $c3->id)));
        $this->assertSame(false, grade_get_setting($c3->id, 'minmaxtouse', false, true));
        $this->assertFalse($DB->record_exists('grade_items', array('needsupdate' => 1, 'courseid' => $c3->id)));


                unset($CFG->grade_minmaxtouse);
        grade_set_setting($c1->id, 'minmaxtouse', null);

                upgrade_minmaxgrade();

                $this->assertSame((string) GRADE_MIN_MAX_FROM_GRADE_GRADE, grade_get_setting($c1->id, 'minmaxtouse', false, true));

                $this->assertFalse($DB->record_exists('config', array('name' => 'show_min_max_grades_changed_' . $c3->id)));
        $this->assertSame(false, grade_get_setting($c3->id, 'minmaxtouse', false, true));
        $this->assertFalse($DB->record_exists('grade_items', array('needsupdate' => 1, 'courseid' => $c3->id)));

                $CFG->grade_minmaxtouse = $initialminmax;
    }

    public function test_upgrade_extra_credit_weightoverride() {
        global $DB, $CFG;

        $this->resetAfterTest(true);

        require_once($CFG->libdir . '/db/upgradelib.php');

        $c = array();
        $a = array();
        $gi = array();
        for ($i=0; $i<5; $i++) {
            $c[$i] = $this->getDataGenerator()->create_course();
            $a[$i] = array();
            $gi[$i] = array();
            for ($j=0;$j<3;$j++) {
                $a[$i][$j] = $this->getDataGenerator()->create_module('assign', array('course' => $c[$i], 'grade' => 100));
                $giparams = array('itemtype' => 'mod', 'itemmodule' => 'assign', 'iteminstance' => $a[$i][$j]->id,
                    'courseid' => $c[$i]->id, 'itemnumber' => 0);
                $gi[$i][$j] = grade_item::fetch($giparams);
            }
        }

                $coursecategory = grade_category::fetch_course_category($c[0]->id);
        $coursecategory->aggregation = GRADE_AGGREGATE_WEIGHTED_MEAN;
        $coursecategory->update();
        $gi[0][1]->aggregationcoef = 1;
        $gi[0][1]->update();
        $gi[0][2]->weightoverride = 1;
        $gi[0][2]->update();

        
                $gi[2][1]->aggregationcoef = 1;
        $gi[2][1]->update();

                $gi[3][2]->weightoverride = 1;
        $gi[3][2]->update();

                $gi[4][1]->aggregationcoef = 1;
        $gi[4][1]->update();
        $gi[4][2]->weightoverride = 1;
        $gi[4][2]->update();

                upgrade_extra_credit_weightoverride();

        $this->assertTrue(empty($CFG->{'gradebook_calculations_freeze_' . $c[0]->id}));
        $this->assertTrue(empty($CFG->{'gradebook_calculations_freeze_' . $c[1]->id}));
        $this->assertTrue(empty($CFG->{'gradebook_calculations_freeze_' . $c[2]->id}));
        $this->assertTrue(empty($CFG->{'gradebook_calculations_freeze_' . $c[3]->id}));
        $this->assertEquals(20150619, $CFG->{'gradebook_calculations_freeze_' . $c[4]->id});

        set_config('gradebook_calculations_freeze_' . $c[4]->id, null);

                upgrade_extra_credit_weightoverride($c[0]->id);
        $this->assertTrue(empty($CFG->{'gradebook_calculations_freeze_' . $c[0]->id}));
        upgrade_extra_credit_weightoverride($c[4]->id);
        $this->assertEquals(20150619, $CFG->{'gradebook_calculations_freeze_' . $c[4]->id});
    }

    
    public function test_upgrade_calculated_grade_items_freeze() {
        global $DB, $CFG;

        $this->resetAfterTest();

        require_once($CFG->libdir . '/db/upgradelib.php');

                $user = $this->getDataGenerator()->create_user();

                $course1 = $this->getDataGenerator()->create_course();
        $course2 = $this->getDataGenerator()->create_course();
        $course3 = $this->getDataGenerator()->create_course();

                $studentrole = $DB->get_record('role', array('shortname' => 'student'));
        $maninstance1 = $DB->get_record('enrol', array('courseid' => $course1->id, 'enrol' => 'manual'), '*', MUST_EXIST);
        $maninstance2 = $DB->get_record('enrol', array('courseid' => $course2->id, 'enrol' => 'manual'), '*', MUST_EXIST);
        $maninstance3 = $DB->get_record('enrol', array('courseid' => $course3->id, 'enrol' => 'manual'), '*', MUST_EXIST);
        $manual = enrol_get_plugin('manual');
        $manual->enrol_user($maninstance1, $user->id, $studentrole->id);
        $manual->enrol_user($maninstance2, $user->id, $studentrole->id);
        $manual->enrol_user($maninstance3, $user->id, $studentrole->id);

                set_config('gradebook_calculations_freeze_' . $course1->id, 20150627);
        set_config('gradebook_calculations_freeze_' . $course2->id, 20150627);
        set_config('gradebook_calculations_freeze_' . $course3->id, 20150627);
        $CFG->grade_minmaxtouse = 2;

                $gradecategory = new grade_category();
        $gradecategory->fullname = 'calculated grade category';
        $gradecategory->courseid = $course1->id;
        $gradecategory->insert();
        $gradecategoryid = $gradecategory->id;

                $gradeitem = new grade_item();
        $gradeitem->itemname = 'grade item one';
        $gradeitem->itemtype = 'manual';
        $gradeitem->categoryid = $gradecategoryid;
        $gradeitem->courseid = $course1->id;
        $gradeitem->idnumber = 'gi1';
        $gradeitem->insert();

                $gradecategoryitem = grade_item::fetch(array('iteminstance' => $gradecategory->id));
        $gradecategoryitem->calculation = '=##gi' . $gradeitem->id . '##/2';
        $gradecategoryitem->update();

                $grade = $gradeitem->get_grade($user->id, true);
        $grade->finalgrade = 50;
        $grade->update();
                grade_regrade_final_grades($course1->id);
                $gradecategoryitem->grademax = 50;
        $gradecategoryitem->grademin = 5;
        $gradecategoryitem->update();

                        $gradeitem = new grade_item();
        $gradeitem->itemname = 'grade item one';
        $gradeitem->itemtype = 'manual';
        $gradeitem->courseid = $course2->id;
        $gradeitem->idnumber = 'gi1';
        $gradeitem->grademax = 25;
        $gradeitem->insert();

                $calculatedgradeitem = new grade_item();
        $calculatedgradeitem->itemname = 'calculated grade';
        $calculatedgradeitem->itemtype = 'manual';
        $calculatedgradeitem->courseid = $course2->id;
        $calculatedgradeitem->calculation = '=##gi' . $gradeitem->id . '##*2';
        $calculatedgradeitem->grademax = 50;
        $calculatedgradeitem->insert();

                $grade = $gradeitem->get_grade($user->id, true);
        $grade->finalgrade = 10;
        $grade->update();

                grade_regrade_final_grades($course2->id);

                        $gradeitem = new grade_item();
        $gradeitem->itemname = 'grade item one';
        $gradeitem->itemtype = 'manual';
        $gradeitem->courseid = $course3->id;
        $gradeitem->idnumber = 'gi1';
        $gradeitem->grademax = 25;
        $gradeitem->insert();

                $calculatedgradeitem = new grade_item();
        $calculatedgradeitem->itemname = 'calculated grade';
        $calculatedgradeitem->itemtype = 'manual';
        $calculatedgradeitem->courseid = $course3->id;
        $calculatedgradeitem->calculation = '=##gi' . $gradeitem->id . '##*2';
        $calculatedgradeitem->grademax = 50;
        $calculatedgradeitem->insert();

                $grade = $gradeitem->get_grade($user->id, true);
        $grade->finalgrade = 10;
        $grade->update();

                grade_regrade_final_grades($course3->id);
                set_config('gradebook_calculations_freeze_' . $course3->id, null);
        upgrade_calculated_grade_items($course3->id);
        $this->assertEquals(20150627, $CFG->{'gradebook_calculations_freeze_' . $course3->id});

                set_config('gradebook_calculations_freeze_' . $course1->id, null);
        set_config('gradebook_calculations_freeze_' . $course2->id, null);
                upgrade_calculated_grade_items();
                $this->assertEquals(20150627, $CFG->{'gradebook_calculations_freeze_' . $course1->id});
        $this->assertEquals(20150627, $CFG->{'gradebook_calculations_freeze_' . $course2->id});
    }

    function test_upgrade_calculated_grade_items_regrade() {
        global $DB, $CFG;

        $this->resetAfterTest();

        require_once($CFG->libdir . '/db/upgradelib.php');

                $user = $this->getDataGenerator()->create_user();

                $course = $this->getDataGenerator()->create_course();

                $studentrole = $DB->get_record('role', array('shortname' => 'student'));
        $maninstance1 = $DB->get_record('enrol', array('courseid' => $course->id, 'enrol' => 'manual'), '*', MUST_EXIST);
        $manual = enrol_get_plugin('manual');
        $manual->enrol_user($maninstance1, $user->id, $studentrole->id);

        set_config('upgrade_calculatedgradeitemsonlyregrade', 1);

                $gradecategory = new grade_category();
        $gradecategory->fullname = 'calculated grade category';
        $gradecategory->courseid = $course->id;
        $gradecategory->insert();
        $gradecategoryid = $gradecategory->id;

                $gradeitem = new grade_item();
        $gradeitem->itemname = 'grade item one';
        $gradeitem->itemtype = 'manual';
        $gradeitem->categoryid = $gradecategoryid;
        $gradeitem->courseid = $course->id;
        $gradeitem->idnumber = 'gi1';
        $gradeitem->insert();

                $gradecategoryitem = grade_item::fetch(array('iteminstance' => $gradecategory->id));
        $gradecategoryitem->calculation = '=##gi' . $gradeitem->id . '##/2';
        $gradecategoryitem->grademax = 50;
        $gradecategoryitem->grademin = 15;
        $gradecategoryitem->update();

                $grade = $gradeitem->get_grade($user->id, true);
        $grade->finalgrade = 50;
        $grade->update();

        grade_regrade_final_grades($course->id);
        $grade = grade_grade::fetch(array('itemid' => $gradecategoryitem->id, 'userid' => $user->id));
        $grade->rawgrademax = 100;
        $grade->rawgrademin = 0;
        $grade->update();
        $this->assertNotEquals($gradecategoryitem->grademax, $grade->rawgrademax);
        $this->assertNotEquals($gradecategoryitem->grademin, $grade->rawgrademin);

                        upgrade_calculated_grade_items();
        grade_regrade_final_grades($course->id);

        $grade = grade_grade::fetch(array('itemid' => $gradecategoryitem->id, 'userid' => $user->id));

        $this->assertEquals($gradecategoryitem->grademax, $grade->rawgrademax);
        $this->assertEquals($gradecategoryitem->grademin, $grade->rawgrademin);
    }

    public function test_upgrade_course_tags() {
        global $DB, $CFG;

        $this->resetAfterTest();

        require_once($CFG->libdir . '/db/upgradelib.php');

                upgrade_course_tags();
        $this->assertFalse($DB->record_exists('tag_instance', array()));

                $DB->insert_record('tag_instance', array('itemid' => 123, 'tagid' => 101, 'tiuserid' => 0,
            'itemtype' => 'post', 'component' => 'core', 'contextid' => 1));
        $DB->insert_record('tag_instance', array('itemid' => 333, 'tagid' => 103, 'tiuserid' => 1002,
            'itemtype' => 'post', 'component' => 'core', 'contextid' => 1));

        upgrade_course_tags();
        $records = array_values($DB->get_records('tag_instance', array(), 'id', '*'));
        $this->assertEquals(2, count($records));
        $this->assertEquals(123, $records[0]->itemid);
        $this->assertEquals(333, $records[1]->itemid);

                $keys = array('itemid', 'tagid', 'tiuserid');
        $valuesets = array(
            array(1, 101, 0),
            array(1, 102, 0),

            array(2, 102, 0),
            array(2, 103, 1001),

            array(3, 103, 0),
            array(3, 103, 1001),

            array(3, 104, 1006),
            array(3, 104, 1001),
            array(3, 104, 1002),
        );

        foreach ($valuesets as $values) {
            $DB->insert_record('tag_instance', array_combine($keys, $values) +
                    array('itemtype' => 'course', 'component' => 'core', 'contextid' => 1));
        }

        upgrade_course_tags();
                $records = array_values($DB->get_records('tag_instance', array(), 'id', '*'));
        $this->assertEquals(8, count($records));
        $this->assertEquals(7, $DB->count_records('tag_instance', array('tiuserid' => 0)));
                $this->assertEquals(array(101, 102), array_values($DB->get_fieldset_select('tag_instance', 'tagid',
                'itemtype = ? AND itemid = ? ORDER BY tagid', array('course', 1))));
                $this->assertEquals(array(102, 103), array_values($DB->get_fieldset_select('tag_instance', 'tagid',
                'itemtype = ? AND itemid = ? ORDER BY tagid', array('course', 2))));
                $this->assertEquals(array(103, 104), array_values($DB->get_fieldset_select('tag_instance', 'tagid',
                'itemtype = ? AND itemid = ? ORDER BY tagid', array('course', 3))));
    }

    
    public function test_upgrade_course_letter_boundary() {
        global $CFG, $DB;
        $this->resetAfterTest(true);

        require_once($CFG->libdir . '/db/upgradelib.php');

                $user = $this->getDataGenerator()->create_user();

                $courses = array();
        $contexts = array();
        for ($i = 0; $i < 45; $i++) {
            $course = $this->getDataGenerator()->create_course();
            $context = context_course::instance($course->id);
            if (in_array($i, array(2, 5, 10, 13, 14, 19, 23, 25, 30, 34, 36))) {
                                $this->assign_good_letter_boundary($context->id);
            }
            if (in_array($i, array(3, 6, 11, 15, 20, 24, 26, 31, 35))) {
                                $this->assign_bad_letter_boundary($context->id);
            }

            if (in_array($i, array(3, 9, 10, 11, 18, 19, 20, 29, 30, 31, 40))) {
                grade_set_setting($course->id, 'displaytype', '3');
            } else if (in_array($i, array(8, 17, 28))) {
                grade_set_setting($course->id, 'displaytype', '2');
            }

            if (in_array($i, array(37, 43))) {
                                grade_set_setting($course->id, 'report_user_showlettergrade', '1');
            } else if (in_array($i, array(38, 42))) {
                                grade_set_setting($course->id, 'report_user_showlettergrade', '0');
            }

            $assignrow = $this->getDataGenerator()->create_module('assign', array('course' => $course->id, 'name' => 'Test!'));
            $gi = grade_item::fetch(
                    array('itemtype' => 'mod',
                          'itemmodule' => 'assign',
                          'iteminstance' => $assignrow->id,
                          'courseid' => $course->id));
            if (in_array($i, array(6, 13, 14, 15, 23, 24, 34, 35, 36, 41))) {
                grade_item::set_properties($gi, array('display' => 3));
                $gi->update();
            } else if (in_array($i, array(12, 21, 32))) {
                grade_item::set_properties($gi, array('display' => 2));
                $gi->update();
            }
            $gradegrade = new grade_grade();
            $gradegrade->itemid = $gi->id;
            $gradegrade->userid = $user->id;
            $gradegrade->rawgrade = 55.5563;
            $gradegrade->finalgrade = 55.5563;
            $gradegrade->rawgrademax = 100;
            $gradegrade->rawgrademin = 0;
            $gradegrade->timecreated = time();
            $gradegrade->timemodified = time();
            $gradegrade->insert();

            $contexts[] = $context;
            $courses[] = $course;
        }

        upgrade_course_letter_boundary();

                        $this->assertTrue(empty($CFG->{'gradebook_calculations_freeze_' . $courses[0]->id}));
                $this->assertTrue(empty($CFG->{'gradebook_calculations_freeze_' . $courses[1]->id}));
                $this->assertTrue(empty($CFG->{'gradebook_calculations_freeze_' . $courses[2]->id}));
                $this->assertEquals(20160518, $CFG->{'gradebook_calculations_freeze_' . $courses[3]->id});
                $this->assertTrue(empty($CFG->{'gradebook_calculations_freeze_' . $courses[4]->id}));
                $this->assertTrue(empty($CFG->{'gradebook_calculations_freeze_' . $courses[5]->id}));
                $this->assertEquals(20160518, $CFG->{'gradebook_calculations_freeze_' . $courses[6]->id});

                set_config('grade_displaytype', '3');
        for ($i = 0; $i < 45; $i++) {
            unset_config('gradebook_calculations_freeze_' . $courses[$i]->id);
        }
        upgrade_course_letter_boundary();

                $this->assertTrue(empty($CFG->{'gradebook_calculations_freeze_' . $courses[7]->id}));
                $this->assertTrue(empty($CFG->{'gradebook_calculations_freeze_' . $courses[8]->id}));
                $this->assertTrue(empty($CFG->{'gradebook_calculations_freeze_' . $courses[9]->id}));
                $this->assertTrue(empty($CFG->{'gradebook_calculations_freeze_' . $courses[10]->id}));
                $this->assertEquals(20160518, $CFG->{'gradebook_calculations_freeze_' . $courses[11]->id});
                $this->assertTrue(empty($CFG->{'gradebook_calculations_freeze_' . $courses[12]->id}));
                $this->assertTrue(empty($CFG->{'gradebook_calculations_freeze_' . $courses[13]->id}));
                $this->assertTrue(empty($CFG->{'gradebook_calculations_freeze_' . $courses[14]->id}));
                $this->assertEquals(20160518, $CFG->{'gradebook_calculations_freeze_' . $courses[15]->id});

                $systemcontext = context_system::instance();
        $this->assign_bad_letter_boundary($systemcontext->id);
        for ($i = 0; $i < 45; $i++) {
            unset_config('gradebook_calculations_freeze_' . $courses[$i]->id);
        }
        upgrade_course_letter_boundary();

                $this->assertEquals(20160518, $CFG->{'gradebook_calculations_freeze_' . $courses[16]->id});
                $this->assertTrue(empty($CFG->{'gradebook_calculations_freeze_' . $courses[17]->id}));
                $this->assertEquals(20160518, $CFG->{'gradebook_calculations_freeze_' . $courses[18]->id});
                $this->assertTrue(empty($CFG->{'gradebook_calculations_freeze_' . $courses[19]->id}));
                $this->assertEquals(20160518, $CFG->{'gradebook_calculations_freeze_' . $courses[20]->id});
                $this->assertEquals(20160518, $CFG->{'gradebook_calculations_freeze_' . $courses[21]->id});
                $this->assertEquals(20160518, $CFG->{'gradebook_calculations_freeze_' . $courses[22]->id});
                $this->assertTrue(empty($CFG->{'gradebook_calculations_freeze_' . $courses[23]->id}));
                $this->assertEquals(20160518, $CFG->{'gradebook_calculations_freeze_' . $courses[24]->id});
                $this->assertTrue(empty($CFG->{'gradebook_calculations_freeze_' . $courses[25]->id}));
                $this->assertEquals(20160518, $CFG->{'gradebook_calculations_freeze_' . $courses[26]->id});

                set_config('grade_displaytype', '2');
        for ($i = 0; $i < 45; $i++) {
            unset_config('gradebook_calculations_freeze_' . $courses[$i]->id);
        }
        upgrade_course_letter_boundary();

                $this->assertTrue(empty($CFG->{'gradebook_calculations_freeze_' . $courses[27]->id}));
                $this->assertTrue(empty($CFG->{'gradebook_calculations_freeze_' . $courses[28]->id}));
                $this->assertEquals(20160518, $CFG->{'gradebook_calculations_freeze_' . $courses[29]->id});
                $this->assertTrue(empty($CFG->{'gradebook_calculations_freeze_' . $courses[30]->id}));
                $this->assertEquals(20160518, $CFG->{'gradebook_calculations_freeze_' . $courses[31]->id});
                $this->assertTrue(empty($CFG->{'gradebook_calculations_freeze_' . $courses[32]->id}));
                $this->assertTrue(empty($CFG->{'gradebook_calculations_freeze_' . $courses[33]->id}));
                $this->assertTrue(empty($CFG->{'gradebook_calculations_freeze_' . $courses[34]->id}));
                $this->assertEquals(20160518, $CFG->{'gradebook_calculations_freeze_' . $courses[35]->id});
                $this->assertTrue(empty($CFG->{'gradebook_calculations_freeze_' . $courses[36]->id}));

                for ($i = 0; $i < 45; $i++) {
            unset_config('gradebook_calculations_freeze_' . $courses[$i]->id);
        }
        upgrade_course_letter_boundary();

                $this->assertEquals(20160518, $CFG->{'gradebook_calculations_freeze_' . $courses[37]->id});
                $this->assertTrue(empty($CFG->{'gradebook_calculations_freeze_' . $courses[38]->id}));
                $this->assertTrue(empty($CFG->{'gradebook_calculations_freeze_' . $courses[39]->id}));
                $this->assertEquals(20160518, $CFG->{'gradebook_calculations_freeze_' . $courses[40]->id});
                $this->assertEquals(20160518, $CFG->{'gradebook_calculations_freeze_' . $courses[41]->id});

                for ($i = 0; $i < 45; $i++) {
            unset_config('gradebook_calculations_freeze_' . $courses[$i]->id);
        }
        set_config('grade_report_user_showlettergrade', '1');
        upgrade_course_letter_boundary();

                $this->assertTrue(empty($CFG->{'gradebook_calculations_freeze_' . $courses[42]->id}));
                $this->assertEquals(20160518, $CFG->{'gradebook_calculations_freeze_' . $courses[43]->id});
                $this->assertEquals(20160518, $CFG->{'gradebook_calculations_freeze_' . $courses[44]->id});
    }

    
    public function test_upgrade_letter_boundary_needs_freeze() {
        global $CFG;

        $this->resetAfterTest();

        require_once($CFG->libdir . '/db/upgradelib.php');

        $courses = array();
        $contexts = array();
        for ($i = 0; $i < 3; $i++) {
            $courses[] = $this->getDataGenerator()->create_course();
            $contexts[] = context_course::instance($courses[$i]->id);
        }

                $this->assertFalse(upgrade_letter_boundary_needs_freeze($contexts[0]));

                $this->assign_bad_letter_boundary($contexts[1]->id);
        $this->assertTrue(upgrade_letter_boundary_needs_freeze($contexts[1]));
                $this->assign_good_letter_boundary($contexts[2]->id);
        $this->assertFalse(upgrade_letter_boundary_needs_freeze($contexts[2]));
                $systemcontext = context_system::instance();
        $this->assertFalse(upgrade_letter_boundary_needs_freeze($systemcontext));
    }

    
    private function assign_bad_letter_boundary($contextid) {
        global $DB;
        $newlettersscale = array(
                array('contextid' => $contextid, 'lowerboundary' => 90.00000, 'letter' => 'A'),
                array('contextid' => $contextid, 'lowerboundary' => 85.00000, 'letter' => 'A-'),
                array('contextid' => $contextid, 'lowerboundary' => 80.00000, 'letter' => 'B+'),
                array('contextid' => $contextid, 'lowerboundary' => 75.00000, 'letter' => 'B'),
                array('contextid' => $contextid, 'lowerboundary' => 70.00000, 'letter' => 'B-'),
                array('contextid' => $contextid, 'lowerboundary' => 65.00000, 'letter' => 'C+'),
                array('contextid' => $contextid, 'lowerboundary' => 57.00000, 'letter' => 'C'),
                array('contextid' => $contextid, 'lowerboundary' => 50.00000, 'letter' => 'C-'),
                array('contextid' => $contextid, 'lowerboundary' => 40.00000, 'letter' => 'D+'),
                array('contextid' => $contextid, 'lowerboundary' => 25.00000, 'letter' => 'D'),
                array('contextid' => $contextid, 'lowerboundary' => 0.00000, 'letter' => 'F'),
            );

        $DB->delete_records('grade_letters', array('contextid' => $contextid));
        foreach ($newlettersscale as $record) {
                        $DB->insert_record('grade_letters', $record);
        }
    }

    
    private function assign_good_letter_boundary($contextid) {
        global $DB;
        $newlettersscale = array(
                array('contextid' => $contextid, 'lowerboundary' => 90.00000, 'letter' => 'A'),
                array('contextid' => $contextid, 'lowerboundary' => 85.00000, 'letter' => 'A-'),
                array('contextid' => $contextid, 'lowerboundary' => 80.00000, 'letter' => 'B+'),
                array('contextid' => $contextid, 'lowerboundary' => 75.00000, 'letter' => 'B'),
                array('contextid' => $contextid, 'lowerboundary' => 70.00000, 'letter' => 'B-'),
                array('contextid' => $contextid, 'lowerboundary' => 65.00000, 'letter' => 'C+'),
                array('contextid' => $contextid, 'lowerboundary' => 54.00000, 'letter' => 'C'),
                array('contextid' => $contextid, 'lowerboundary' => 50.00000, 'letter' => 'C-'),
                array('contextid' => $contextid, 'lowerboundary' => 40.00000, 'letter' => 'D+'),
                array('contextid' => $contextid, 'lowerboundary' => 25.00000, 'letter' => 'D'),
                array('contextid' => $contextid, 'lowerboundary' => 0.00000, 'letter' => 'F'),
            );

        $DB->delete_records('grade_letters', array('contextid' => $contextid));
        foreach ($newlettersscale as $record) {
                        $DB->insert_record('grade_letters', $record);
        }
    }

    
    public function test_check_libcurl_version() {
        $supportedversion = 0x071304;
        $curlinfo = curl_version();
        $currentversion = $curlinfo['version_number'];

        $result = new environment_results("custom_checks");
        if ($currentversion < $supportedversion) {
            $this->assertFalse(check_libcurl_version($result)->getStatus());
        } else {
            $this->assertNull(check_libcurl_version($result));
        }
    }
}
