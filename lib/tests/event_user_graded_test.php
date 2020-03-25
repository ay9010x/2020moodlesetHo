<?php



defined('MOODLE_INTERNAL') || die();

global $CFG;

require_once($CFG->libdir . '/mathslib.php');


class core_event_user_graded_testcase extends advanced_testcase {

    
    public function setUp() {
        $this->resetAfterTest();
    }

    
    public function test_event() {
        global $CFG;
        require_once("$CFG->libdir/gradelib.php");

        $course = $this->getDataGenerator()->create_course();
        $user = $this->getDataGenerator()->create_user();
        $this->getDataGenerator()->enrol_user($user->id, $course->id);

        $grade_category = grade_category::fetch_course_category($course->id);
        $grade_category->load_grade_item();
        $grade_item = $grade_category->grade_item;

        $grade_item->update_final_grade($user->id, 10, 'gradebook');

        $grade_grade = new grade_grade(array('userid' => $user->id, 'itemid' => $grade_item->id), true);
        $grade_grade->grade_item = $grade_item;

        $event = \core\event\user_graded::create_from_grade($grade_grade);

        $this->assertEventLegacyLogData(
            array($course->id, 'grade', 'update', '/report/grader/index.php?id=' . $course->id, $grade_item->itemname . ': ' . fullname($user)),
            $event
        );
        $this->assertEquals(context_course::instance($course->id), $event->get_context());
        $this->assertSame($event->objecttable, 'grade_grades');
        $this->assertEquals($event->objectid, $grade_grade->id);
        $this->assertEquals($event->other['itemid'], $grade_item->id);
        $this->assertTrue($event->other['overridden']);
        $this->assertEquals(10, $event->other['finalgrade']);

                $sink = $this->redirectEvents();
        $event->trigger();
        $result = $sink->get_events();
        $sink->close();

        $this->assertCount(1, $result);

        $event = reset($result);
        $this->assertEventContextNotUsed($event);

        $grade = $event->get_grade();
        $this->assertInstanceOf('grade_grade', $grade);
        $this->assertEquals($grade_grade->id, $grade->id);
    }

    
    public function test_event_is_triggered() {
        global $DB;

                $course = $this->getDataGenerator()->create_course();
        $user = $this->getDataGenerator()->create_user();
        $this->getDataGenerator()->enrol_user($user->id, $course->id);
        $quiz = $this->getDataGenerator()->create_module('quiz', array('course' => $course->id));
        $quizitemparams = array('itemtype' => 'mod', 'itemmodule' => 'quiz', 'iteminstance' => $quiz->id,
            'courseid' => $course->id);
        $gradeitem = grade_item::fetch($quizitemparams);
        $courseitem = grade_item::fetch_course_item($course->id);

                $grade = array();
        $grade['userid'] = $user->id;
        $grade['rawgrade'] = 60;

        $sink = $this->redirectEvents();
        grade_update('mod/quiz', $course->id, 'mod', 'quiz', $quiz->id, 0, $grade);
        $events = $sink->get_events();
        $sink->close();

                $this->assertEquals(2, count($events));
        $this->assertInstanceOf('\core\event\user_graded', $events[0]);
        $this->assertEquals($gradeitem->id, $events[0]->other['itemid']);
        $this->assertInstanceOf('\core\event\user_graded', $events[1]);
        $this->assertEquals($courseitem->id, $events[1]->other['itemid']);

                        $gradeitem->delete_all_grades();
        grade_regrade_final_grades($course->id);
        $gradeitem = grade_item::fetch($quizitemparams);

                $sink = $this->redirectEvents();
        $gradeitem->update_raw_grade($user->id, 10);
        $events = $sink->get_events();
        $sink->close();

                $this->assertEquals(2, count($events));
        $this->assertInstanceOf('\core\event\user_graded', $events[0]);
        $this->assertEquals($gradeitem->id, $events[0]->other['itemid']);
        $this->assertInstanceOf('\core\event\user_graded', $events[1]);
        $this->assertEquals($courseitem->id, $events[1]->other['itemid']);

                $sink = $this->redirectEvents();
        $gradeitem->update_raw_grade($user->id, 20);
        $events = $sink->get_events();
        $sink->close();

                $this->assertEquals(2, count($events));
        $this->assertInstanceOf('\core\event\user_graded', $events[0]);
        $this->assertEquals($gradeitem->id, $events[0]->other['itemid']);
        $this->assertInstanceOf('\core\event\user_graded', $events[1]);
        $this->assertEquals($courseitem->id, $events[1]->other['itemid']);

                        $gradeitem->delete_all_grades();
        grade_regrade_final_grades($course->id);
        $gradeitem = grade_item::fetch($quizitemparams);

                $sink = $this->redirectEvents();
        $gradeitem->update_final_grade($user->id, 30);
        $events = $sink->get_events();
        $sink->close();

                $this->assertEquals(2, count($events));
        $this->assertInstanceOf('\core\event\user_graded', $events[0]);
        $this->assertEquals($gradeitem->id, $events[0]->other['itemid']);
        $this->assertInstanceOf('\core\event\user_graded', $events[1]);
        $this->assertEquals($courseitem->id, $events[1]->other['itemid']);

                $sink = $this->redirectEvents();
        $gradeitem->update_final_grade($user->id, 40);
        $events = $sink->get_events();
        $sink->close();

                $this->assertEquals(2, count($events));
        $this->assertInstanceOf('\core\event\user_graded', $events[0]);
        $this->assertEquals($gradeitem->id, $events[0]->other['itemid']);
        $this->assertInstanceOf('\core\event\user_graded', $events[1]);
        $this->assertEquals($courseitem->id, $events[1]->other['itemid']);

                $gradegrade = grade_grade::fetch(array('itemid' => $gradeitem->id, 'userid' => $user->id));
        $gradegrade->set_overridden(false, false);

                $calculation = calc_formula::unlocalize("=3");
        $gradeitem->set_calculation($calculation);

                $sink = $this->redirectEvents();
        grade_regrade_final_grades($course->id);
        $events = $sink->get_events();
        $sink->close();

                $this->assertEquals(2, count($events));
        $this->assertInstanceOf('\core\event\user_graded', $events[0]);
        $this->assertEquals($gradeitem->id, $events[0]->other['itemid']);
        $this->assertInstanceOf('\core\event\user_graded', $events[1]);
        $this->assertEquals($courseitem->id, $events[1]->other['itemid']);

                        $gradeitem = grade_item::fetch($quizitemparams);
        $gradeitem->set_calculation('');
        $gradegrade = grade_grade::fetch(array('itemid' => $gradeitem->id, 'userid' => $user->id));
        $gradegrade->rawgrade = 50;
        $gradegrade->update();

        $sink = $this->redirectEvents();
        grade_regrade_final_grades($course->id);
        $events = $sink->get_events();
        $sink->close();

                $this->assertEquals(2, count($events));
        $this->assertInstanceOf('\core\event\user_graded', $events[0]);
        $this->assertEquals($gradeitem->id, $events[0]->other['itemid']);
        $this->assertInstanceOf('\core\event\user_graded', $events[1]);
        $this->assertEquals($courseitem->id, $events[1]->other['itemid']);
    }
}
