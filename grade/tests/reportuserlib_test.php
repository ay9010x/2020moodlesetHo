<?php



defined('MOODLE_INTERNAL') || die();

global $CFG;
require_once($CFG->dirroot.'/grade/lib.php');
require_once($CFG->dirroot.'/grade/report/user/lib.php');



class core_grade_reportuserlib_testcase extends advanced_testcase {

    
    public function test_inject_rowspans() {
        global $CFG, $USER, $DB;

        parent::setUp();
        $this->resetAfterTest(true);

        $CFG->enableavailability = 1;
        $CFG->enablecompletion = 1;

                $course = $this->getDataGenerator()->create_course();
        $coursecategory = grade_category::fetch_course_category($course->id);
        $coursecontext = context_course::instance($course->id);

                $student = $this->getDataGenerator()->create_user(array('username' => 'Student Sam'));
        $role = $DB->get_record('role', array('shortname' => 'student'), '*', MUST_EXIST);
        $this->getDataGenerator()->enrol_user($student->id, $course->id, $role->id);

        $teacher = $this->getDataGenerator()->create_user(array('username' => 'Teacher T'));
        $role = $DB->get_record('role', array('shortname' => 'editingteacher'), '*', MUST_EXIST);
        $this->getDataGenerator()->enrol_user($teacher->id, $course->id, $role->id);

                $users = array($student, $teacher);

                $this->setUser($student);

                $report = $this->create_report($course, $student, $coursecontext);
                $this->assertEquals(2, $report->inject_rowspans($report->gtree->top_element));
        $this->assertEquals(2, $report->gtree->top_element['rowspan']);
        $this->assertEquals(2, $report->maxdepth);

                if (array_key_exists('rowspan', $report->gtree->top_element['children'][1])) {
            $this->fail('Elements without children should not have rowspan set');
        }

                $data1 = $this->getDataGenerator()->create_module('data', array('assessed' => 1, 'scale' => 100, 'course' => $course->id));

        $forum1 = $this->getDataGenerator()->create_module('forum', array('assessed' => 1, 'scale' => 100, 'course' => $course->id));
        $forum1cm = get_coursemodule_from_id('forum', $forum1->cmid);
                $forum1 = grade_item::fetch(array('itemtype' => 'mod', 'itemmodule' => 'forum', 'iteminstance' => $forum1->id, 'courseid' => $course->id));

        $report = $this->create_report($course, $student, $coursecontext);
                $this->assertEquals(4, $report->inject_rowspans($report->gtree->top_element));
        $this->assertEquals(4, $report->gtree->top_element['rowspan']);
                $this->assertEquals(2, $report->maxdepth);

                if (array_key_exists('rowspan', $report->gtree->top_element['children'][1])) {
            $this->fail('Elements without children should not have rowspan set');
        }

                set_coursemodule_visible($forum1cm->id, 0);

        foreach ($users as $user) {

            $this->setUser($user);
            $message = 'Testing with ' . $user->username;
            accesslib_clear_all_caches_for_unit_testing();

            $report = $this->create_report($course, $user, $coursecontext);
                        $this->assertEquals(4, $report->inject_rowspans($report->gtree->top_element), $message);
            $this->assertEquals(4, $report->gtree->top_element['rowspan'], $message);
                        $this->assertEquals(2, $report->maxdepth, $message);
        }

                set_coursemodule_visible($forum1cm->id, 1);

                $params = new stdClass();
        $params->courseid = $course->id;
        $params->fullname = 'unittestcategory';
        $params->parent = $coursecategory->id;
        $gradecategory = new grade_category($params, false);
        $gradecategory->insert();

        $forum1->set_parent($gradecategory->id);

        $report = $this->create_report($course, $student, $coursecontext);
                $this->assertEquals(6, $report->inject_rowspans($report->gtree->top_element));
        $this->assertEquals(6, $report->gtree->top_element['rowspan']);
                $this->assertEquals(3, $report->maxdepth);

                $this->assertEquals(3, $report->gtree->top_element['children'][4]['rowspan']);
                if (array_key_exists('rowspan', $report->gtree->top_element['children'][4]['children'][3])) {
            $this->fail('The forum has no children so should not have rowspan set');
        }

                                                                                $DB->set_field('course_modules', 'availability', '{"op":"|","show":false,"c":[' .
                '{"type":"grade","min":5.5,"id":37}]}', array('id' => $forum1cm->id));
        get_fast_modinfo($course->id, 0, true);
        foreach ($users as $user) {

            $this->setUser($user);
            $message = 'Testing with ' . $user->username;
            accesslib_clear_all_caches_for_unit_testing();

            $report = $this->create_report($course, $user, $coursecontext);
                        $this->assertEquals(6, $report->inject_rowspans($report->gtree->top_element), $message);
            $this->assertEquals(6, $report->gtree->top_element['rowspan'], $message);
                        $this->assertEquals(3, $report->maxdepth, $message);
        }
    }

    private function create_report($course, $user, $coursecontext) {

        $gpr = new grade_plugin_return(array('type' => 'report', 'plugin'=>'user', 'courseid' => $course->id, 'userid' => $user->id));
        $report = new grade_report_user($course->id, $gpr, $coursecontext, $user->id);

        return $report;
    }

}

