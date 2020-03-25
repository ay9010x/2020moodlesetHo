<?php

defined('MOODLE_INTERNAL') || die();

global $CFG;

require_once($CFG->dirroot . '/webservice/tests/helpers.php');


class core_grading_externallib_testcase extends externallib_advanced_testcase {

    
    public function test_get_definitions() {
        global $DB, $CFG, $USER;

        $this->resetAfterTest(true);
                $coursedata['idnumber'] = 'idnumbercourse';
        $coursedata['fullname'] = 'Lightwork Course';
        $coursedata['summary'] = 'Lightwork Course description';
        $coursedata['summaryformat'] = FORMAT_MOODLE;
        $course = self::getDataGenerator()->create_course($coursedata);

        $assigndata['course'] = $course->id;
        $assigndata['name'] = 'lightwork assignment';

        $cm = self::getDataGenerator()->create_module('assign', $assigndata);

                $manualenroldata['enrol'] = 'manual';
        $manualenroldata['status'] = 0;
        $manualenroldata['courseid'] = $course->id;
        $enrolid = $DB->insert_record('enrol', $manualenroldata);

                $coursecontext = context_course::instance($course->id);
        $roleid = $this->assignUserCapability('moodle/course:viewparticipants', $coursecontext->id, 3);
        $modulecontext = context_module::instance($cm->cmid);
        $this->assignUserCapability('mod/assign:grade', $modulecontext->id, $roleid);

                $userenrolmentdata['status'] = 0;
        $userenrolmentdata['enrolid'] = $enrolid;
        $userenrolmentdata['userid'] = $USER->id;
        $DB->insert_record('user_enrolments', $userenrolmentdata);

                $gradingarea = array(
            'contextid' => $modulecontext->id,
            'component' => 'mod_assign',
            'areaname' => 'submissions',
            'activemethod' => 'rubric'
        );
        $areaid = $DB->insert_record('grading_areas', $gradingarea);

                $rubricdefinition = array (
            'areaid' => $areaid,
            'method' => 'rubric',
            'name' => 'test',
            'status' => 20,
            'copiedfromid' => 1,
            'timecreated' => 1,
            'usercreated' => $USER->id,
            'timemodified' => 1,
            'usermodified' => $USER->id,
            'timecopied' => 0
        );
        $definitionid = $DB->insert_record('grading_definitions', $rubricdefinition);

                $rubriccriteria1 = array (
            'definitionid' => $definitionid,
            'sortorder' => 1,
            'description' => 'Demonstrate an understanding of disease control',
            'descriptionformat' => 0
        );
        $criterionid1 = $DB->insert_record('gradingform_rubric_criteria', $rubriccriteria1);
        $rubriclevel1 = array (
            'criterionid' => $criterionid1,
            'score' => 5,
            'definition' => 'pass',
            'definitionformat' => 0
        );
        $DB->insert_record('gradingform_rubric_levels', $rubriclevel1);
        $rubriclevel2 = array (
            'criterionid' => $criterionid1,
            'score' => 10,
            'definition' => 'excellent',
            'definitionformat' => 0
        );
        $DB->insert_record('gradingform_rubric_levels', $rubriclevel2);

                $rubriccriteria2 = array (
            'definitionid' => $definitionid,
            'sortorder' => 2,
            'description' => 'Demonstrate an understanding of brucellosis',
            'descriptionformat' => 0
        );
        $criterionid2 = $DB->insert_record('gradingform_rubric_criteria', $rubriccriteria2);
        $rubriclevel1 = array (
            'criterionid' => $criterionid2,
            'score' => 5,
            'definition' => 'pass',
            'definitionformat' => 0
        );
        $DB->insert_record('gradingform_rubric_levels', $rubriclevel1);
        $rubriclevel2 = array (
            'criterionid' => $criterionid2,
            'score' => 10,
            'definition' => 'excellent',
            'definitionformat' => 0
        );
        $DB->insert_record('gradingform_rubric_levels', $rubriclevel2);

                $cmids = array ($cm->cmid);
        $areaname = 'submissions';
        $result = core_grading_external::get_definitions($cmids, $areaname);
        $result = external_api::clean_returnvalue(core_grading_external::get_definitions_returns(), $result);

        $this->assertEquals(1, count($result['areas']));
        $this->assertEquals(1, count($result['areas'][0]['definitions']));
        $definition = $result['areas'][0]['definitions'][0];

        $this->assertEquals($rubricdefinition['method'], $definition['method']);
        $this->assertEquals($USER->id, $definition['usercreated']);

        require_once("$CFG->dirroot/grade/grading/lib.php");
        require_once($CFG->dirroot.'/grade/grading/form/'.$rubricdefinition['method'].'/lib.php');

        $gradingmanager = get_grading_manager($areaid);

        $this->assertEquals(1, count($definition[$rubricdefinition['method']]));

        $rubricdetails = $definition[$rubricdefinition['method']];
        $details = call_user_func('gradingform_'.$rubricdefinition['method'].'_controller::get_external_definition_details');

        $this->assertEquals(2, count($rubricdetails[key($details)]));

        $found = false;
        foreach ($rubricdetails[key($details)] as $criterion) {
            if ($criterion['id'] == $criterionid1) {
                $this->assertEquals($rubriccriteria1['description'], $criterion['description']);
                $this->assertEquals(2, count($criterion['levels']));
                $found = true;
                break;
            }
        }
        $this->assertTrue($found);
    }

    
    public function test_get_gradingform_instances() {
        global $DB, $USER;

        $this->resetAfterTest(true);
                $coursedata['idnumber'] = 'idnumbercourse';
        $coursedata['fullname'] = 'Lightwork Course';
        $coursedata['summary'] = 'Lightwork Course description';
        $coursedata['summaryformat'] = FORMAT_MOODLE;
        $course = self::getDataGenerator()->create_course($coursedata);

        $assigndata['course'] = $course->id;
        $assigndata['name'] = 'lightwork assignment';

        $assign = self::getDataGenerator()->create_module('assign', $assigndata);

                $manualenroldata['enrol'] = 'manual';
        $manualenroldata['status'] = 0;
        $manualenroldata['courseid'] = $course->id;
        $enrolid = $DB->insert_record('enrol', $manualenroldata);

                $coursecontext = context_course::instance($course->id);
        $roleid = $this->assignUserCapability('moodle/course:viewparticipants', $coursecontext->id, 3);
        $modulecontext = context_module::instance($assign->cmid);
        $this->assignUserCapability('mod/assign:grade', $modulecontext->id, $roleid);

                $userenrolmentdata['status'] = 0;
        $userenrolmentdata['enrolid'] = $enrolid;
        $userenrolmentdata['userid'] = $USER->id;
        $DB->insert_record('user_enrolments', $userenrolmentdata);

                $student = self::getDataGenerator()->create_user();
        $assigngrade = new stdClass();
        $assigngrade->assignment = $assign->id;
        $assigngrade->userid = $student->id;
        $assigngrade->timecreated = time();
        $assigngrade->timemodified = $assigngrade->timecreated;
        $assigngrade->grader = $USER->id;
        $assigngrade->grade = 50;
        $assigngrade->attemptnumber = 0;
        $gid = $DB->insert_record('assign_grades', $assigngrade);

                $gradingarea = array(
            'contextid' => $modulecontext->id,
            'component' => 'mod_assign',
            'areaname' => 'submissions',
            'activemethod' => 'rubric'
        );
        $areaid = $DB->insert_record('grading_areas', $gradingarea);

                $rubricdefinition = array (
            'areaid' => $areaid,
            'method' => 'rubric',
            'name' => 'test',
            'status' => 20,
            'copiedfromid' => 1,
            'timecreated' => 1,
            'usercreated' => $USER->id,
            'timemodified' => 1,
            'usermodified' => $USER->id,
            'timecopied' => 0
        );
        $definitionid = $DB->insert_record('grading_definitions', $rubricdefinition);

                $rubriccriteria = array (
            'definitionid' => $definitionid,
            'sortorder' => 1,
            'description' => 'Demonstrate an understanding of disease control',
            'descriptionformat' => 0
        );
        $criterionid = $DB->insert_record('gradingform_rubric_criteria', $rubriccriteria);
        $rubriclevel = array (
            'criterionid' => $criterionid,
            'score' => 50,
            'definition' => 'pass',
            'definitionformat' => 0
        );
        $levelid = $DB->insert_record('gradingform_rubric_levels', $rubriclevel);

                $instance = array (
            'definitionid' => $definitionid,
            'raterid' => $USER->id,
            'itemid' => $gid,
            'status' => 1,
            'feedbackformat' => 0,
            'timemodified' => 1
        );
        $instanceid = $DB->insert_record('grading_instances', $instance);

                $filling = array (
            'instanceid' => $instanceid,
            'criterionid' => $criterionid,
            'levelid' => $levelid,
            'remark' => 'excellent work',
            'remarkformat' => 0
        );
        $DB->insert_record('gradingform_rubric_fillings', $filling);

                $result = core_grading_external::get_gradingform_instances($definitionid, 0);
        $result = external_api::clean_returnvalue(core_grading_external::get_gradingform_instances_returns(), $result);

        $this->assertEquals(1, count($result['instances']));
        $this->assertEquals($USER->id, $result['instances'][0]['raterid']);
        $this->assertEquals($gid, $result['instances'][0]['itemid']);
        $this->assertEquals(1, $result['instances'][0]['status']);
        $this->assertEquals(1, $result['instances'][0]['timemodified']);
        $this->assertEquals(1, count($result['instances'][0]['rubric']));
        $this->assertEquals(1, count($result['instances'][0]['rubric']['criteria']));
        $criteria = $result['instances'][0]['rubric']['criteria'];
        $this->assertEquals($criterionid, $criteria[0]['criterionid']);
        $this->assertEquals($levelid, $criteria[0]['levelid']);
        $this->assertEquals('excellent work', $criteria[0]['remark']);
    }

    
    public function test_save_definitions_rubric() {
        global $DB, $CFG, $USER;

        $this->resetAfterTest(true);
                $course = self::getDataGenerator()->create_course();
        $generator = $this->getDataGenerator()->get_plugin_generator('mod_assign');
        $params['course'] = $course->id;
        $instance = $generator->create_instance($params);
        $cm = get_coursemodule_from_instance('assign', $instance->id);
        $context = context_module::instance($cm->id);
        $coursecontext = context_course::instance($course->id);

                $teacher = self::getDataGenerator()->create_user();
        $USER->id = $teacher->id;
        $teacherrole = $DB->get_record('role', array('shortname' => 'editingteacher'));
        $this->assignUserCapability('moodle/grade:managegradingforms', $context->id, $teacherrole->id);
        $this->getDataGenerator()->enrol_user($teacher->id,
                                              $course->id,
                                              $teacherrole->id);

                $gradingarea = array(
            'cmid' => $cm->id,
            'contextid' => $context->id,
            'component' => 'mod_assign',
            'areaname'  => 'submissions',
            'activemethod' => 'rubric'
        );

                $rubricdefinition = array(
            'method' => 'rubric',
            'name' => 'test',
            'description' => '',
            'status' => 20,
            'copiedfromid' => 1,
            'timecreated' => 1,
            'usercreated' => $teacher->id,
            'timemodified' => 1,
            'usermodified' => $teacher->id,
            'timecopied' => 0
        );

                $rubriccriteria1 = array (
             'sortorder' => 1,
             'description' => 'Demonstrate an understanding of disease control',
             'descriptionformat' => 0
        );

                $rubriclevel1 = array (
            'score' => 50,
            'definition' => 'pass',
            'definitionformat' => 0
        );
        $rubriclevel2 = array (
            'score' => 100,
            'definition' => 'excellent',
            'definitionformat' => 0
        );
        $rubriclevel3 = array (
            'score' => 0,
            'definition' => 'fail',
            'definitionformat' => 0
        );

        $rubriccriteria1['levels'] = array($rubriclevel1, $rubriclevel2, $rubriclevel3);
        $rubricdefinition['rubric'] = array('rubric_criteria' => array($rubriccriteria1));
        $gradingarea['definitions'] = array($rubricdefinition);

        $results = core_grading_external::save_definitions(array($gradingarea));

        $area = $DB->get_record('grading_areas',
                                array('contextid' => $context->id, 'component' => 'mod_assign', 'areaname' => 'submissions'),
                                '*', MUST_EXIST);
        $this->assertEquals($area->activemethod, 'rubric');

        $definition = $DB->get_record('grading_definitions', array('areaid' => $area->id, 'method' => 'rubric'), '*', MUST_EXIST);
        $this->assertEquals($rubricdefinition['name'], $definition->name);

        $criterion1 = $DB->get_record('gradingform_rubric_criteria', array('definitionid' => $definition->id), '*', MUST_EXIST);
        $levels = $DB->get_records('gradingform_rubric_levels', array('criterionid' => $criterion1->id));
        $validlevelcount = 0;
        $expectedvalue = true;
        foreach ($levels as $level) {
            if ($level->score == 0) {
                $this->assertEquals('fail', $level->definition);
                $validlevelcount++;
            } else if ($level->score == 50) {
                $this->assertEquals('pass', $level->definition);
                $validlevelcount++;
            } else if ($level->score == 100) {
                $this->assertEquals('excellent', $level->definition);
                $excellentlevelid = $level->id;
                $validlevelcount++;
            } else {
                $expectedvalue = false;
            }
        }
        $this->assertEquals(3, $validlevelcount);
        $this->assertTrue($expectedvalue, 'A level with an unexpected score was found');

                        
                $rubricdefinition = array(
            'id' => $definition->id,
            'method' => 'rubric',
            'name' => 'test changed',
            'description' => '',
            'status' => 20,
            'copiedfromid' => 1,
            'timecreated' => 1,
            'usercreated' => $teacher->id,
            'timemodified' => 1,
            'usermodified' => $teacher->id,
            'timecopied' => 0
        );

                $rubriccriteria1 = array (
             'id' => $criterion1->id,
             'sortorder' => 1,
             'description' => 'Demonstrate an understanding of rabies control',
             'descriptionformat' => 0
        );

                $rubriccriteria2 = array (
             'sortorder' => 2,
             'description' => 'Demonstrate an understanding of anthrax control',
             'descriptionformat' => 0
        );

                $rubriclevel2 = array (
            'id' => $excellentlevelid,
            'score' => 75,
            'definition' => 'excellent',
            'definitionformat' => 0
        );

                $rubriclevel4 = array (
            'score' => 100,
            'definition' => 'superb',
            'definitionformat' => 0
        );

        $rubriccriteria1['levels'] = array($rubriclevel1, $rubriclevel2, $rubriclevel3, $rubriclevel4);
        $rubricdefinition['rubric'] = array('rubric_criteria' => array($rubriccriteria1, $rubriccriteria2));
        $gradingarea['definitions'] = array($rubricdefinition);

        $results = core_grading_external::save_definitions(array($gradingarea));

                $definition = $DB->get_record('grading_definitions', array('id' => $definition->id), '*', MUST_EXIST);
        $this->assertEquals('test changed', $definition->name);

                $modifiedcriteria = $DB->get_record('gradingform_rubric_criteria', array('id' => $criterion1->id), '*', MUST_EXIST);
        $this->assertEquals('Demonstrate an understanding of rabies control', $modifiedcriteria->description);

                $newcriteria = $DB->get_record('gradingform_rubric_criteria',
                                       array('definitionid' => $definition->id, 'sortorder' => 2), '*', MUST_EXIST);
        $this->assertEquals('Demonstrate an understanding of anthrax control', $newcriteria->description);

                $modifiedlevel = $DB->get_record('gradingform_rubric_levels', array('id' => $excellentlevelid), '*', MUST_EXIST);
        $this->assertEquals(75, $modifiedlevel->score);

                $newlevel = $DB->get_record('gradingform_rubric_levels',
                                       array('criterionid' => $criterion1->id, 'score' => 100), '*', MUST_EXIST);
        $this->assertEquals('superb', $newlevel->definition);

                                $rubricdefinition = array(
            'id' => $definition->id,
            'method' => 'rubric',
            'name' => 'test changed',
            'description' => '',
            'status' => 20,
            'copiedfromid' => 1,
            'timecreated' => 1,
            'usercreated' => $teacher->id,
            'timemodified' => 1,
            'usermodified' => $teacher->id,
            'timecopied' => 0
        );

        $rubriccriteria1 = array (
             'id' => $criterion1->id,
             'sortorder' => 1,
             'description' => 'Demonstrate an understanding of rabies control',
             'descriptionformat' => 0
        );

        $rubriclevel1 = array (
            'score' => 0,
            'definition' => 'fail',
            'definitionformat' => 0
        );
        $rubriclevel2 = array (
            'score' => 100,
            'definition' => 'pass',
            'definitionformat' => 0
        );

        $rubriccriteria1['levels'] = array($rubriclevel1, $rubriclevel2);
        $rubricdefinition['rubric'] = array('rubric_criteria' => array($rubriccriteria1));
        $gradingarea['definitions'] = array($rubricdefinition);

        $results = core_grading_external::save_definitions(array($gradingarea));

                $this->assertEquals(1, $DB->count_records('gradingform_rubric_criteria', array('definitionid' => $definition->id)));
        $criterion1 = $DB->get_record('gradingform_rubric_criteria', array('definitionid' => $definition->id), '*', MUST_EXIST);
        $this->assertEquals('Demonstrate an understanding of rabies control', $criterion1->description);
                $this->assertEquals(2, $DB->count_records('gradingform_rubric_levels', array('criterionid' => $criterion1->id)));

        $gradingarea['activemethod'] = 'invalid';
        $this->setExpectedException('moodle_exception');
        $results = core_grading_external::save_definitions(array($gradingarea));
    }

    
    public function test_save_definitions_marking_guide() {
        global $DB, $CFG, $USER;

        $this->resetAfterTest(true);
                $course = self::getDataGenerator()->create_course();
        $generator = $this->getDataGenerator()->get_plugin_generator('mod_assign');
        $params['course'] = $course->id;
        $instance = $generator->create_instance($params);
        $cm = get_coursemodule_from_instance('assign', $instance->id);
        $context = context_module::instance($cm->id);
        $coursecontext = context_course::instance($course->id);

                $teacher = self::getDataGenerator()->create_user();
        $USER->id = $teacher->id;
        $teacherrole = $DB->get_record('role', array('shortname' => 'editingteacher'));
        $this->assignUserCapability('moodle/grade:managegradingforms', $context->id, $teacherrole->id);
        $this->getDataGenerator()->enrol_user($teacher->id,
                                              $course->id,
                                              $teacherrole->id);

                $gradingarea = array(
            'cmid' => $cm->id,
            'contextid' => $context->id,
            'component' => 'mod_assign',
            'areaname'  => 'submissions',
            'activemethod' => 'guide'
        );

        $guidedefinition = array(
            'method' => 'guide',
            'name' => 'test',
            'description' => '',
            'status' => 20,
            'copiedfromid' => 1,
            'timecreated' => 1,
            'usercreated' => $teacher->id,
            'timemodified' => 1,
            'usermodified' => $teacher->id,
            'timecopied' => 0
        );

        $guidecomment = array(
             'sortorder' => 1,
             'description' => 'Students need to show that they understand the control of zoonoses',
             'descriptionformat' => 0
        );
        $guidecriteria1 = array (
             'sortorder' => 1,
             'shortname' => 'Rabies Control',
             'description' => 'Understand rabies control techniques',
             'descriptionformat' => 0,
             'descriptionmarkers' => 'Student must demonstrate that they understand rabies control',
             'descriptionmarkersformat' => 0,
             'maxscore' => 50
        );
        $guidecriteria2 = array (
             'sortorder' => 2,
             'shortname' => 'Anthrax Control',
             'description' => 'Understand anthrax control',
             'descriptionformat' => 0,
             'descriptionmarkers' => 'Student must demonstrate that they understand anthrax control',
             'descriptionmarkersformat' => 0,
             'maxscore' => 50
        );

        $guidedefinition['guide'] = array('guide_criteria' => array($guidecriteria1, $guidecriteria2),
                                          'guide_comments' => array($guidecomment));
        $gradingarea['definitions'] = array($guidedefinition);

        $results = core_grading_external::save_definitions(array($gradingarea));
        $area = $DB->get_record('grading_areas',
                                array('contextid' => $context->id, 'component' => 'mod_assign', 'areaname' => 'submissions'),
                                '*', MUST_EXIST);
        $this->assertEquals($area->activemethod, 'guide');

        $definition = $DB->get_record('grading_definitions', array('areaid' => $area->id, 'method' => 'guide'), '*', MUST_EXIST);
        $this->assertEquals($guidedefinition['name'], $definition->name);
        $this->assertEquals(2, $DB->count_records('gradingform_guide_criteria', array('definitionid' => $definition->id)));
        $this->assertEquals(1, $DB->count_records('gradingform_guide_comments', array('definitionid' => $definition->id)));

                $guidedefinition['guide'] = array('guide_criteria' => array($guidecriteria1),
                                          'guide_comments' => array($guidecomment));
        $gradingarea['definitions'] = array($guidedefinition);
        $results = core_grading_external::save_definitions(array($gradingarea));
        $this->assertEquals(1, $DB->count_records('gradingform_guide_criteria', array('definitionid' => $definition->id)));

                $guidedefinition['method'] = 'invalid';
        $gradingarea['definitions'] = array($guidedefinition);
        $this->setExpectedException('invalid_parameter_exception');
        $results = core_grading_external::save_definitions(array($gradingarea));
    }
}
