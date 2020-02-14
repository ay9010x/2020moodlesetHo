<?php



global $CFG;
require_once(__DIR__ . '/fixtures/screen.php');
require_once($CFG->libdir . '/gradelib.php');

defined('MOODLE_INTERNAL') || die();

class gradereport_singleview_screen_testcase extends advanced_testcase {

    
    public function test_load_users() {
        global $DB;

        $this->setAdminUser();
        $this->resetAfterTest(true);

        $roleteacher = $DB->get_record('role', array('shortname' => 'teacher'), '*', MUST_EXIST);

                $course = $this->getDataGenerator()->create_course();
        $coursecontext = context_course::instance($course->id);
        $group = $this->getDataGenerator()->create_group(array('courseid' => $course->id));
        $teacher = $this->getDataGenerator()->create_user();
        $user1 = $this->getDataGenerator()->create_user();
        $user2 = $this->getDataGenerator()->create_user();
        $this->getDataGenerator()->enrol_user($teacher->id, $course->id, $roleteacher->id);
        $this->getDataGenerator()->enrol_user($user1->id, $course->id);
        $this->getDataGenerator()->enrol_user($user2->id, $course->id);
        $this->getDataGenerator()->create_group_member(array('groupid' => $group->id, 'userid' => $teacher->id));
        $this->getDataGenerator()->create_group_member(array('groupid' => $group->id, 'userid' => $user1->id));
        $this->getDataGenerator()->create_group_member(array('groupid' => $group->id, 'userid' => $user2->id));

                grade_regrade_final_grades($course->id);
        $screentest = new gradereport_singleview_screen_testable($course->id, 0, $group->id);
        $groupusers = $screentest->test_load_users();
        $this->assertCount(2, $groupusers);

                $this->getDataGenerator()->enrol_user($user2->id, $course->id, $roleteacher->id, 'manual', 0, 0, ENROL_USER_SUSPENDED);
        $users = $screentest->test_load_users();
        $this->assertCount(1, $users);

                assign_capability('moodle/course:viewsuspendedusers', CAP_ALLOW, $roleteacher->id, $coursecontext, true);
        set_user_preference('grade_report_showonlyactiveenrol', false, $teacher);
        accesslib_clear_all_caches_for_unit_testing();
        $this->setUser($teacher);
        $screentest = new gradereport_singleview_screen_testable($course->id, 0, $group->id);
        $users = $screentest->test_load_users();
        $this->assertCount(2, $users);

                assign_capability('moodle/course:viewsuspendedusers', CAP_PROHIBIT, $roleteacher->id, $coursecontext, true);
        set_user_preference('grade_report_showonlyactiveenrol', false, $teacher);
        accesslib_clear_all_caches_for_unit_testing();
        $users = $screentest->test_load_users();
        $this->assertCount(1, $users);

                $this->getDataGenerator()->enrol_user($user2->id, $course->id, $roleteacher->id, 'manual', 0, 0, ENROL_USER_ACTIVE);
        $users = $screentest->test_load_users();
        $this->assertCount(2, $users);
    }
}
