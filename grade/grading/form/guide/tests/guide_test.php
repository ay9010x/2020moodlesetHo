<?php



defined('MOODLE_INTERNAL') || die();

global $CFG;
require_once($CFG->dirroot . '/grade/grading/lib.php');
require_once($CFG->dirroot . '/grade/grading/form/guide/lib.php');


class gradingform_guide_testcase extends advanced_testcase {
    
    public function test_get_or_create_instance() {
        global $DB;

        $this->resetAfterTest(true);

                $fakearea = (object)array(
            'contextid'    => 1,
            'component'    => 'mod_assign',
            'areaname'     => 'submissions',
            'activemethod' => 'guide'
        );
        $fakearea1id = $DB->insert_record('grading_areas', $fakearea);
        $fakearea->contextid = 2;
        $fakearea2id = $DB->insert_record('grading_areas', $fakearea);

                $fakedefinition = (object)array(
            'areaid'       => $fakearea1id,
            'method'       => 'guide',
            'name'         => 'fakedef',
            'status'       => gradingform_controller::DEFINITION_STATUS_READY,
            'timecreated'  => 0,
            'usercreated'  => 1,
            'timemodified' => 0,
            'usermodified' => 1,
        );
        $fakedef1id = $DB->insert_record('grading_definitions', $fakedefinition);
        $fakedefinition->areaid = $fakearea2id;
        $fakedef2id = $DB->insert_record('grading_definitions', $fakedefinition);

                $fakeinstance = (object)array(
            'definitionid'   => $fakedef1id,
            'raterid'        => 1,
            'itemid'         => 1,
            'rawgrade'       => null,
            'status'         => 0,
            'feedback'       => null,
            'feedbackformat' => 0,
            'timemodified'   => 0
        );
        $fakeinstanceid = $DB->insert_record('grading_instances', $fakeinstance);

        $manager1 = get_grading_manager($fakearea1id);
        $manager2 = get_grading_manager($fakearea2id);
        $controller1 = $manager1->get_controller('guide');
        $controller2 = $manager2->get_controller('guide');

        $instance1 = $controller1->get_or_create_instance(0, 1, 1);
        $instance2 = $controller2->get_or_create_instance(0, 1, 1);

                $this->assertEquals(false, $instance1->get_data('definitionid') == $instance2->get_data('definitionid'));
    }
}
