<?php



defined('MOODLE_INTERNAL') || die();

global $CFG;
require_once($CFG->dirroot . '/grade/import/lib.php');


class core_grade_import_lib_test extends advanced_testcase {

    
    private function import_grades($data) {
        global $DB, $USER;
        $graderecord = new stdClass();
        $graderecord->importcode = $data['importcode'];
        if (isset($data['itemid'])) {
            $graderecord->itemid = $data['itemid'];
        }
        $graderecord->userid = $data['userid'];
        if (isset($data['importer'])) {
            $graderecord->importer = $data['importer'];
        } else {
            $graderecord->importer = $USER->id;
        }
        if (isset($data['finalgrade'])) {
            $graderecord->finalgrade = $data['finalgrade'];
        } else {
            $graderecord->finalgrade = rand(0, 100);
        }
        if (isset($data['feedback'])) {
            $graderecord->feedback = $data['feedback'];
        }
        if (isset($data['importonlyfeedback'])) {
            $graderecord->importonlyfeedback = $data['importonlyfeedback'];
        } else {
            $graderecord->importonlyfeedback = false;
        }
        if (isset($data['newgradeitem'])) {
            $graderecord->newgradeitem = $data['newgradeitem'];
        }
        return $DB->insert_record('grade_import_values', $graderecord);
    }

    
    public function test_grade_import_commit() {
        global $USER, $DB, $CFG;
        $this->resetAfterTest();

        $importcode = get_new_importcode();
        $user1 = $this->getDataGenerator()->create_user();
        $user2 = $this->getDataGenerator()->create_user();

        $course = $this->getDataGenerator()->create_course();
        $assign = $this->getDataGenerator()->create_module('assign', array('course' => $course->id));
        $itemname = $assign->name;
        $modulecontext = context_module::instance($assign->cmid);
                $assign = new assign($modulecontext, false, false);
        $cm = $assign->get_course_module();

                $this->getDataGenerator()->enrol_user($user1->id, $course->id);
        $this->getDataGenerator()->enrol_user($user2->id, $course->id);

                $gradeitem = grade_item::fetch(array('courseid' => $course->id, 'itemtype' => 'mod'));

                $originalgrade = 55;
        $this->import_grades(array(
            'importcode' => $importcode,
            'itemid' => $gradeitem->id,
            'userid' => $user1->id,
            'finalgrade' => $originalgrade
        ));

        $status = grade_import_commit($course->id, $importcode, false, false);
        $this->assertTrue($status);

                $gradegrade = grade_grade::fetch(array('itemid' => $gradeitem->id, 'userid' => $user1->id));
        $this->assertEquals($originalgrade, $gradegrade->finalgrade);
                $this->assertTrue($gradegrade->is_overridden());

                $importcode = get_new_importcode();
        $record = new stdClass();
        $record->itemname = 'New grade item';
        $record->importcode = $importcode;
        $record->importer = $USER->id;
        $insertid = $DB->insert_record('grade_import_newitem', $record);

        $finalgrade = 75;
        $this->import_grades(array(
            'importcode' => $importcode,
            'userid' => $user1->id,
            'finalgrade' => $finalgrade,
            'newgradeitem' => $insertid
        ));

        $status = grade_import_commit($course->id, $importcode, false, false);
        $this->assertTrue($status);
                $gradeitem = grade_item::fetch(array('courseid' => $course->id, 'itemtype' => 'manual'));
        $this->assertEquals($record->itemname, $gradeitem->itemname);
                $gradegrade = grade_grade::fetch(array('itemid' => $gradeitem->id, 'userid' => $user1->id));
        $this->assertEquals($finalgrade, $gradegrade->finalgrade);
                $this->assertFalse($gradegrade->is_overridden());

                $importcode = get_new_importcode();
        $gradeitem = grade_item::fetch(array('courseid' => $course->id, 'itemtype' => 'mod'));

        $originalfeedback = 'feedback can be useful';
        $this->import_grades(array(
            'importcode' => $importcode,
            'userid' => $user1->id,
            'itemid' => $gradeitem->id,
            'feedback' => $originalfeedback,
            'importonlyfeedback' => true
        ));

        $status = grade_import_commit($course->id, $importcode, true, false);
        $this->assertTrue($status);
        $gradegrade = grade_grade::fetch(array('itemid' => $gradeitem->id, 'userid' => $user1->id));
                $this->assertEquals($originalgrade, $gradegrade->finalgrade);
        $this->assertTrue($gradegrade->is_overridden());

                $importcode = get_new_importcode();
        $gradeitem = grade_item::fetch(array('courseid' => $course->id, 'itemtype' => 'mod'));

        $finalgrade = 60;
        $this->import_grades(array(
            'importcode' => $importcode,
            'userid' => $user1->id,
            'itemid' => $gradeitem->id,
            'finalgrade' => $finalgrade,
            'feedback' => 'feedback can still be useful'
        ));

        $status = grade_import_commit($course->id, $importcode, false, false);
        $this->assertTrue($status);
        $gradegrade = grade_grade::fetch(array('itemid' => $gradeitem->id, 'userid' => $user1->id));
        $this->assertEquals($finalgrade, $gradegrade->finalgrade);
                $this->assertEquals($originalfeedback, $gradegrade->feedback);
        $this->assertTrue($gradegrade->is_overridden());

                $importcode = get_new_importcode();
        $gradeitem = grade_item::fetch(array('courseid' => $course->id, 'itemtype' => 'mod'));

        $this->import_grades(array(
            'importcode' => $importcode,
            'userid' => $user1->id,
            'itemid' => $gradeitem->id
        ));

        $url = $CFG->wwwroot . '/grade/index.php';
        $expectedresponse = "++ Grade import success ++
<div class=\"continuebutton\"><form method=\"get\" action=\"$url\"><div><input type=\"submit\" value=\"Continue\" /><input type=\"hidden\" name=\"id\" value=\"$course->id\" /></div></form></div>";

        ob_start();
        $status = grade_import_commit($course->id, $importcode);
        $output = ob_get_contents();
        ob_end_clean();
        $this->assertTrue($status);
        $this->assertEquals($expectedresponse, $output);
    }
}
