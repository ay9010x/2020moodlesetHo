<?php



defined('MOODLE_INTERNAL') || die();

global $CFG;
require_once($CFG->dirroot.'/grade/lib.php');
require_once($CFG->dirroot.'/grade/report/lib.php');


class grade_report_test extends grade_report {
    public function __construct($courseid, $gpr, $context, $user) {
        parent::__construct($courseid, $gpr, $context);
        $this->user = $user;
    }

    
    public function blank_hidden_total_and_adjust_bounds($courseid, $courseitem, $finalgrade) {
        return parent::blank_hidden_total_and_adjust_bounds($courseid, $courseitem, $finalgrade);
    }

    
    public function process_data($data) {
    }

    
    public function process_action($target, $action) {
    }
}


class core_grade_reportlib_testcase extends advanced_testcase {

    
    public function test_blank_hidden_total_and_adjust_bounds() {
        global $DB;

        $this->resetAfterTest(true);

        $student = $this->getDataGenerator()->create_user();
        $this->setUser($student);

                        $course = $this->getDataGenerator()->create_course();
        $coursegradeitem = grade_item::fetch_course_item($course->id);
        $coursecontext = context_course::instance($course->id);

        $data = $this->getDataGenerator()->create_module('data', array('assessed' => 1, 'scale' => 100, 'course' => $course->id));
        $datacm = get_coursemodule_from_id('data', $data->cmid);

        $forum = $this->getDataGenerator()->create_module('forum', array('assessed' => 1, 'scale' => 100, 'course' => $course->id));
        $forumcm = get_coursemodule_from_id('forum', $forum->cmid);

                $gi = grade_item::fetch(array('itemtype' => 'mod', 'itemmodule' => 'data', 'iteminstance' => $data->id, 'courseid' => $course->id));
        $datagrade = 50;
        $grade_grade = new grade_grade();
        $grade_grade->itemid = $gi->id;
        $grade_grade->userid = $student->id;
        $grade_grade->rawgrade = $datagrade;
        $grade_grade->finalgrade = $datagrade;
        $grade_grade->rawgrademax = 100;
        $grade_grade->rawgrademin = 0;
        $grade_grade->timecreated = time();
        $grade_grade->timemodified = time();
        $grade_grade->insert();

        $gi = grade_item::fetch(array('itemtype' => 'mod', 'itemmodule' => 'forum', 'iteminstance' => $forum->id, 'courseid' => $course->id));
        $forumgrade = 70;
        $grade_grade = new grade_grade();
        $grade_grade->itemid = $gi->id;
        $grade_grade->userid = $student->id;
        $grade_grade->rawgrade = $forumgrade;
        $grade_grade->finalgrade = $forumgrade;
        $grade_grade->rawgrademax = 100;
        $grade_grade->rawgrademin = 0;
        $grade_grade->timecreated = time();
        $grade_grade->timemodified = time();
        $grade_grade->insert();

                set_coursemodule_visible($datacm->id, 0);

        $gpr = new grade_plugin_return(array('type' => 'report', 'courseid' => $course->id));
        $report = new grade_report_test($course->id, $gpr, $coursecontext, $student);

                $report->showtotalsifcontainhidden = array($course->id => GRADE_REPORT_SHOW_REAL_TOTAL_IF_CONTAINS_HIDDEN);
        $result = $report->blank_hidden_total_and_adjust_bounds($course->id, $coursegradeitem, $datagrade + $forumgrade);
        $this->assertEquals(array('grade' => $datagrade + $forumgrade,
                                  'grademax' => $coursegradeitem->grademax,
                                  'grademin' => $coursegradeitem->grademin,
                                  'aggregationstatus' => 'unknown',
                                  'aggregationweight' => null), $result);
                $report->showtotalsifcontainhidden = array($course->id => GRADE_REPORT_HIDE_TOTAL_IF_CONTAINS_HIDDEN);
        $result = $report->blank_hidden_total_and_adjust_bounds($course->id, $coursegradeitem, $datagrade + $forumgrade);
        $this->assertEquals(array('grade' => null,
                                  'grademax' => $coursegradeitem->grademax,
                                  'grademin' => $coursegradeitem->grademin,
                                  'aggregationstatus' => 'unknown',
                                  'aggregationweight' => null), $result);

                $report->showtotalsifcontainhidden = array($course->id => GRADE_REPORT_SHOW_TOTAL_IF_CONTAINS_HIDDEN);
        $result = $report->blank_hidden_total_and_adjust_bounds($course->id, $coursegradeitem, $datagrade + $forumgrade);
        $this->assertEquals(array('grade' => floatval($forumgrade),
                                  'grademax' => $coursegradeitem->grademax,
                                  'grademin' => $coursegradeitem->grademin,
                                  'aggregationstatus' => 'unknown',
                                  'aggregationweight' => null), $result);

                        
                                $course = $this->getDataGenerator()->create_course();
        $coursegradeitem = grade_item::fetch_course_item($course->id);
        $coursecontext = context_course::instance($course->id);

        $data = $this->getDataGenerator()->create_module('data', array('assessed' => 1, 'scale' => 100, 'course' => $course->id));
        $datacm = get_coursemodule_from_id('data', $data->cmid);

        $forum = $this->getDataGenerator()->create_module('forum', array('assessed' => 1, 'scale' => 100, 'course' => $course->id));
        $forumcm = get_coursemodule_from_id('forum', $forum->cmid);

        $gi = grade_item::fetch(array('itemtype' => 'mod', 'itemmodule' => 'data', 'iteminstance' => $data->id, 'courseid' => $course->id));
        $datagrade = 50;
        $grade_grade = new grade_grade();
        $grade_grade->itemid = $gi->id;
        $grade_grade->userid = $student->id;
        $grade_grade->rawgrade = $datagrade;
        $grade_grade->finalgrade = $datagrade;
        $grade_grade->rawgrademax = 100;
        $grade_grade->rawgrademin = 0;
        $grade_grade->timecreated = time();
        $grade_grade->timemodified = time();
        $grade_grade->insert();

        $gi = grade_item::fetch(array('itemtype' => 'mod', 'itemmodule' => 'forum', 'iteminstance' => $forum->id, 'courseid' => $course->id));
        $forumgrade = 70;
        $grade_grade = new grade_grade();
        $grade_grade->itemid = $gi->id;
        $grade_grade->userid = $student->id;
        $grade_grade->rawgrade = $forumgrade;
        $grade_grade->finalgrade = $forumgrade;
        $grade_grade->rawgrademax = 100;
        $grade_grade->rawgrademin = 0;
        $grade_grade->timecreated = time();
        $grade_grade->timemodified = time();
        $grade_grade->insert();

                set_coursemodule_visible($datacm->id, 0);
        set_coursemodule_visible($forumcm->id, 0);

        $gpr = new grade_plugin_return(array('type' => 'report', 'courseid' => $course->id));
        $report = new grade_report_test($course->id, $gpr, $coursecontext, $student);

                $report->showtotalsifcontainhidden = array($course->id => GRADE_REPORT_SHOW_REAL_TOTAL_IF_CONTAINS_HIDDEN);
        $result = $report->blank_hidden_total_and_adjust_bounds($course->id, $coursegradeitem, $datagrade + $forumgrade);
        $this->assertEquals(array('grade' => $datagrade + $forumgrade,
                                  'grademax' => $coursegradeitem->grademax,
                                  'grademin' => $coursegradeitem->grademin,
                                  'aggregationstatus' => 'unknown',
                                  'aggregationweight' => null), $result);

                $report->showtotalsifcontainhidden = array($course->id => GRADE_REPORT_HIDE_TOTAL_IF_CONTAINS_HIDDEN);
        $result = $report->blank_hidden_total_and_adjust_bounds($course->id, $coursegradeitem, $datagrade + $forumgrade);
        $this->assertEquals(array('grade' => null,
                                  'grademax' => $coursegradeitem->grademax,
                                  'grademin' => $coursegradeitem->grademin,
                                  'aggregationstatus' => 'unknown',
                                  'aggregationweight' => null), $result);

                        $report->showtotalsifcontainhidden = array($course->id => GRADE_REPORT_SHOW_TOTAL_IF_CONTAINS_HIDDEN);
        $result = $report->blank_hidden_total_and_adjust_bounds($course->id, $coursegradeitem, $datagrade + $forumgrade);
        $this->assertEquals(array('grade' => null,
                                  'grademax' => $coursegradeitem->grademax,
                                  'grademin' => $coursegradeitem->grademin,
                                  'aggregationstatus' => 'unknown',
                                  'aggregationweight' => null), $result);
    }
}
