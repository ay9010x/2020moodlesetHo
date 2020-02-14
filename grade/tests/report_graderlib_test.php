<?php



defined('MOODLE_INTERNAL') || die();

global $CFG;
require_once($CFG->dirroot.'/grade/lib.php');
require_once($CFG->dirroot.'/grade/report/grader/lib.php');


class core_grade_report_graderlib_testcase extends advanced_testcase {

    
    public function test_process_data() {
        global $DB, $CFG;

        $this->resetAfterTest(true);

        $course = $this->getDataGenerator()->create_course();

                $student = $this->getDataGenerator()->create_user(array('username' => 'Student Sam'));
        $role = $DB->get_record('role', array('shortname' => 'student'), '*', MUST_EXIST);
        $this->getDataGenerator()->enrol_user($student->id, $course->id, $role->id);

                $CFG->unlimitedgrades = 0;

        $forummax = 80;
        $forum1 = $this->getDataGenerator()->create_module('forum', array('assessed' => 1, 'scale' => $forummax, 'course' => $course->id));
                $forum1 = grade_item::fetch(array('itemtype' => 'mod', 'itemmodule' => 'forum', 'iteminstance' => $forum1->id, 'courseid' => $course->id));

        $report = $this->create_report($course);
        $testgrade = 60.00;

        $data = new stdClass();
        $data->id = $course->id;
        $data->report = 'grader';
        $data->timepageload = time();

        $data->grade = array();
        $data->grade[$student->id] = array();
        $data->grade[$student->id][$forum1->id] = $testgrade;

        $warnings = $report->process_data($data);
        $this->assertEquals(count($warnings), 0);

        $studentgrade = grade_grade::fetch(array('itemid' => $forum1->id, '' => $student->id));
        $this->assertEquals($studentgrade->finalgrade, $testgrade);

                $toobig = 200.00;
        $data->grade[$student->id][$forum1->id] = $toobig;
        $data->timepageload = time();
        $warnings = $report->process_data($data);
        $this->assertEquals(count($warnings), 1);

        $studentgrade = grade_grade::fetch(array('itemid' => $forum1->id, '' => $student->id));
        $this->assertEquals($studentgrade->finalgrade, $forummax);

                $toosmall = -10.00;
        $data->grade[$student->id][$forum1->id] = $toosmall;
        $data->timepageload = time();
        $warnings = $report->process_data($data);
        $this->assertEquals(count($warnings), 1);

        $studentgrade = grade_grade::fetch(array('itemid' => $forum1->id, '' => $student->id));
        $this->assertEquals($studentgrade->finalgrade, 0);

                $CFG->unlimitedgrades = 1;

        $data->grade[$student->id][$forum1->id] = $toobig;
        $data->timepageload = time();
        $warnings = $report->process_data($data);
        $this->assertEquals(count($warnings), 0);

        $studentgrade = grade_grade::fetch(array('itemid' => $forum1->id, '' => $student->id));
        $this->assertEquals($studentgrade->finalgrade, $toobig);
    }

    public function test_collapsed_preferences() {
        $this->resetAfterTest(true);

        $emptypreferences = array('aggregatesonly' => array(), 'gradesonly' => array());

        $user1 = $this->getDataGenerator()->create_user();
        $course1 = $this->getDataGenerator()->create_course();
        $course2 = $this->getDataGenerator()->create_course();
        $course3 = $this->getDataGenerator()->create_course();

        $this->setUser($user1);

        $report = $this->create_report($course1);
        $this->assertEquals($emptypreferences, $report->collapsed);

                $report->process_action('cg13', 'switch_minus');
        $report = $this->create_report($course1);
        $this->assertEquals(array(13), $report->collapsed['aggregatesonly']);
        $this->assertEmpty($report->collapsed['gradesonly']);

        $report->process_action('cg13', 'switch_plus');
        $report = $this->create_report($course1);
        $this->assertEmpty($report->collapsed['aggregatesonly']);
        $this->assertEquals(array(13), $report->collapsed['gradesonly']);

        $report->process_action('cg13', 'switch_whole');
        $report = $this->create_report($course1);
        $this->assertEquals($emptypreferences, $report->collapsed);

        
        $course1cats = $course2cats = $course3cats = array();
        for ($i=0;$i<10;$i++) {
            $course1cats[] = $this->create_grade_category($course1)->id;
            $course2cats[] = $this->create_grade_category($course2)->id;
            $course3cats[] = $this->create_grade_category($course3)->id;
        }

        $report1 = $this->create_report($course1);
        foreach ($course1cats as $catid) {
            $report1->process_action('cg'.$catid, 'switch_minus');
        }
        $report2 = $this->create_report($course2);
        foreach ($course2cats as $catid) {
            $report2->process_action('cg'.$catid, 'switch_minus');
            $report2->process_action('cg'.$catid, 'switch_plus');
        }
        $report3 = $this->create_report($course3);
        foreach ($course3cats as $catid) {
            $report3->process_action('cg'.$catid, 'switch_minus');
            if (($i++)%2) {
                $report3->process_action('cg'.$catid, 'switch_plus');
            }
        }

        $report1 = $this->create_report($course1);
        $this->assertEquals(10, count($report1->collapsed['aggregatesonly']));
        $this->assertEquals(0, count($report1->collapsed['gradesonly']));
        $report2 = $this->create_report($course2);
        $this->assertEquals(0, count($report2->collapsed['aggregatesonly']));
        $this->assertEquals(10, count($report2->collapsed['gradesonly']));
        $report3 = $this->create_report($course3);
        $this->assertEquals(5, count($report3->collapsed['aggregatesonly']));
        $this->assertEquals(5, count($report3->collapsed['gradesonly']));

                
        $user2 = $this->getDataGenerator()->create_user();
        $alldata = array(
            'aggregatesonly' => array_merge($report1->collapsed['aggregatesonly'], $report2->collapsed['aggregatesonly'], $report3->collapsed['aggregatesonly']),
            'gradesonly' => array_merge($report1->collapsed['gradesonly'], $report2->collapsed['gradesonly'], $report3->collapsed['gradesonly']),
        );
        set_user_preference('grade_report_grader_collapsed_categories', serialize($alldata), $user2);

        $this->setUser($user2);
        $convertedreport1 = $this->create_report($course1);
        $this->assertEquals($report1->collapsed, $convertedreport1->collapsed);
        $convertedreport2 = $this->create_report($course2);
        $this->assertEquals($report2->collapsed, $convertedreport2->collapsed);
        $convertedreport3 = $this->create_report($course3);
        $this->assertEquals($report3->collapsed, $convertedreport3->collapsed);
                $this->assertEmpty(get_user_preferences('grade_report_grader_collapsed_categories'));

        
        $toobigvalue = $expectedvalue = $report1->collapsed;
        for ($i = 0; strlen(json_encode($toobigvalue)) < 1333; $i++) {
            $toobigvalue[($i < 7) ? 'gradesonly' : 'aggregatesonly'][] = $course1cats[9] + 1 + $i;
        }
        $lastvalue = array_pop($toobigvalue['gradesonly']);
        set_user_preference('grade_report_grader_collapsed_categories'.$course1->id, json_encode($toobigvalue));

        $report1 = $this->create_report($course1);
        $report1->process_action('cg'.$lastvalue, 'switch_minus');

        $report1 = $this->create_report($course1);
        $this->assertEquals($expectedvalue, $report1->collapsed);

        
        $toobigvalue = $report1->collapsed;
        for ($i = 0; strlen(json_encode($toobigvalue)) < 1333; $i++) {
            $catid = $this->create_grade_category($course1)->id;
            $toobigvalue[($i < 7) ? 'gradesonly' : 'aggregatesonly'][] = $catid;
        }
        $lastcatid = array_pop($toobigvalue['gradesonly']);
        set_user_preference('grade_report_grader_collapsed_categories'.$course1->id, json_encode($toobigvalue));
        $toobigvalue['aggregatesonly'][] = $lastcatid;

        $report1 = $this->create_report($course1);
        $report1->process_action('cg'.$lastcatid, 'switch_minus');

                $report1 = $this->create_report($course1);
        $this->assertEquals(count($toobigvalue['aggregatesonly']) - 1, count($report1->collapsed['aggregatesonly']));
        $this->assertEquals(count($toobigvalue['gradesonly']) - 1, count($report1->collapsed['gradesonly']));
    }

    private function create_grade_category($course) {
        static $cnt = 0;
        $cnt++;
        $grade_category = new grade_category(array('courseid' => $course->id, 'fullname' => 'Cat '.$cnt), false);
        $grade_category->apply_default_settings();
        $grade_category->apply_forced_settings();
        $grade_category->insert();
        return $grade_category;
    }

    private function create_report($course) {

        $coursecontext = context_course::instance($course->id);
        $gpr = new grade_plugin_return(array('type' => 'report', 'plugin'=>'grader', 'courseid' => $course->id));
        $report = new grade_report_grader($course->id, $gpr, $coursecontext);

        return $report;
    }
}
