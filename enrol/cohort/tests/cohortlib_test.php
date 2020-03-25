<?php



defined('MOODLE_INTERNAL') || die();

global $CFG;
require_once($CFG->dirroot.'/cohort/lib.php');
require_once($CFG->dirroot.'/group/lib.php');


class enrol_cohort_lib_testcase extends advanced_testcase {

    
    public function test_enrol_cohort_create_new_group() {
        global $DB;
        $this->resetAfterTest();
                $category = $this->getDataGenerator()->create_category();
                $course = $this->getDataGenerator()->create_course(array('category' => $category->id));
        $course2 = $this->getDataGenerator()->create_course(array('category' => $category->id));
                $cohort = $this->getDataGenerator()->create_cohort(array('context' => context_coursecat::instance($category->id)->id));
                $groupid = enrol_cohort_create_new_group($course->id, $cohort->id);
                $group = $DB->get_record('groups', array('id' => $groupid));
                $this->assertEquals($cohort->name . ' cohort', $group->name);
                $this->assertEquals($course->id, $group->courseid);

                $groupdata = new stdClass();
        $groupdata->courseid = $course2->id;
        $groupdata->name = $cohort->name . ' cohort';
        groups_create_group($groupdata);
                $groupid = enrol_cohort_create_new_group($course2->id, $cohort->id);
        $groupinfo = $DB->get_record('groups', array('id' => $groupid));
                $this->assertEquals($cohort->name . ' cohort (2)', $groupinfo->name);

                $groupdata = new stdClass();
        $groupdata->courseid = $course2->id;
        $groupdata->name = $cohort->name . ' cohort (2)';
        groups_create_group($groupdata);
                $groupid = enrol_cohort_create_new_group($course2->id, $cohort->id);
        $groupinfo = $DB->get_record('groups', array('id' => $groupid));
                $this->assertEquals($cohort->name . ' cohort (3)', $groupinfo->name);

    }
}
