<?php



defined('MOODLE_INTERNAL') || die();

global $CFG;
require_once($CFG->dirroot . '/mod/workshop/locallib.php'); require_once(__DIR__ . '/fixtures/testable.php');



class mod_workshop_internal_api_testcase extends advanced_testcase {

    
    protected $workshop;

    
    protected function setUp() {
        parent::setUp();
        $this->setAdminUser();
        $course = $this->getDataGenerator()->create_course();
        $workshop = $this->getDataGenerator()->create_module('workshop', array('course' => $course));
        $cm = get_coursemodule_from_instance('workshop', $workshop->id, $course->id, false, MUST_EXIST);
        $this->workshop = new testable_workshop($workshop, $cm, $course);
    }

    protected function tearDown() {
        $this->workshop = null;
        parent::tearDown();
    }

    public function test_aggregate_submission_grades_process_notgraded() {
        $this->resetAfterTest(true);

                $batch = array();           $batch[] = (object)array('submissionid' => 12, 'submissiongrade' => null, 'weight' => 1, 'grade' => null);
                        $this->workshop->aggregate_submission_grades_process($batch);
    }

    public function test_aggregate_submission_grades_process_single() {
        $this->resetAfterTest(true);

                $batch = array();           $batch[] = (object)array('submissionid' => 12, 'submissiongrade' => null, 'weight' => 1, 'grade' => 10.12345);
        $expected = 10.12345;
                        $this->workshop->aggregate_submission_grades_process($batch);
    }

    public function test_aggregate_submission_grades_process_null_doesnt_influence() {
        $this->resetAfterTest(true);

                $batch = array();           $batch[] = (object)array('submissionid' => 12, 'submissiongrade' => null, 'weight' => 1, 'grade' => 45.54321);
        $batch[] = (object)array('submissionid' => 12, 'submissiongrade' => null, 'weight' => 1, 'grade' => null);
        $expected = 45.54321;
                        $this->workshop->aggregate_submission_grades_process($batch);
    }

    public function test_aggregate_submission_grades_process_weighted_single() {
        $this->resetAfterTest(true);

                $batch = array();           $batch[] = (object)array('submissionid' => 12, 'submissiongrade' => null, 'weight' => 4, 'grade' => 14.00012);
        $expected = 14.00012;
                        $this->workshop->aggregate_submission_grades_process($batch);
    }

    public function test_aggregate_submission_grades_process_mean() {
        $this->resetAfterTest(true);

                $batch = array();           $batch[] = (object)array('submissionid' => 45, 'submissiongrade' => null, 'weight' => 1, 'grade' => 56.12000);
        $batch[] = (object)array('submissionid' => 45, 'submissiongrade' => null, 'weight' => 1, 'grade' => 12.59000);
        $batch[] = (object)array('submissionid' => 45, 'submissiongrade' => null, 'weight' => 1, 'grade' => 10.00000);
        $batch[] = (object)array('submissionid' => 45, 'submissiongrade' => null, 'weight' => 1, 'grade' => 0.00000);
        $expected = 19.67750;
                        $this->workshop->aggregate_submission_grades_process($batch);
    }

    public function test_aggregate_submission_grades_process_mean_changed() {
        $this->resetAfterTest(true);

                $batch = array();           $batch[] = (object)array('submissionid' => 45, 'submissiongrade' => 12.57750, 'weight' => 1, 'grade' => 56.12000);
        $batch[] = (object)array('submissionid' => 45, 'submissiongrade' => 12.57750, 'weight' => 1, 'grade' => 12.59000);
        $batch[] = (object)array('submissionid' => 45, 'submissiongrade' => 12.57750, 'weight' => 1, 'grade' => 10.00000);
        $batch[] = (object)array('submissionid' => 45, 'submissiongrade' => 12.57750, 'weight' => 1, 'grade' => 0.00000);
        $expected = 19.67750;
                        $this->workshop->aggregate_submission_grades_process($batch);
    }

    public function test_aggregate_submission_grades_process_mean_nochange() {
        $this->resetAfterTest(true);

                $batch = array();           $batch[] = (object)array('submissionid' => 45, 'submissiongrade' => 19.67750, 'weight' => 1, 'grade' => 56.12000);
        $batch[] = (object)array('submissionid' => 45, 'submissiongrade' => 19.67750, 'weight' => 1, 'grade' => 12.59000);
        $batch[] = (object)array('submissionid' => 45, 'submissiongrade' => 19.67750, 'weight' => 1, 'grade' => 10.00000);
        $batch[] = (object)array('submissionid' => 45, 'submissiongrade' => 19.67750, 'weight' => 1, 'grade' => 0.00000);
                        $this->workshop->aggregate_submission_grades_process($batch);
    }

    public function test_aggregate_submission_grades_process_rounding() {
        $this->resetAfterTest(true);

                $batch = array();           $batch[] = (object)array('submissionid' => 45, 'submissiongrade' => null, 'weight' => 1, 'grade' => 4.00000);
        $batch[] = (object)array('submissionid' => 45, 'submissiongrade' => null, 'weight' => 1, 'grade' => 2.00000);
        $batch[] = (object)array('submissionid' => 45, 'submissiongrade' => null, 'weight' => 1, 'grade' => 1.00000);
        $expected = 2.33333;
                        $this->workshop->aggregate_submission_grades_process($batch);
    }

    public function test_aggregate_submission_grades_process_weighted_mean() {
        $this->resetAfterTest(true);

                $batch = array();           $batch[] = (object)array('submissionid' => 45, 'submissiongrade' => null, 'weight' => 3, 'grade' => 12.00000);
        $batch[] = (object)array('submissionid' => 45, 'submissiongrade' => null, 'weight' => 2, 'grade' => 30.00000);
        $batch[] = (object)array('submissionid' => 45, 'submissiongrade' => null, 'weight' => 1, 'grade' => 10.00000);
        $batch[] = (object)array('submissionid' => 45, 'submissiongrade' => null, 'weight' => 0, 'grade' => 1000.00000);
        $expected = 17.66667;
                        $this->workshop->aggregate_submission_grades_process($batch);
    }

    public function test_aggregate_grading_grades_process_nograding() {
        $this->resetAfterTest(true);
                $batch = array();
        $batch[] = (object)array('reviewerid'=>2, 'gradinggrade'=>null, 'gradinggradeover'=>null, 'aggregationid'=>null, 'aggregatedgrade'=>null);
                                $this->workshop->aggregate_grading_grades_process($batch);
    }

    public function test_aggregate_grading_grades_process_single_grade_new() {
        $this->resetAfterTest(true);
                $batch = array();
        $batch[] = (object)array('reviewerid'=>3, 'gradinggrade'=>82.87670, 'gradinggradeover'=>null, 'aggregationid'=>null, 'aggregatedgrade'=>null);
                $now = time();
        $expected = new stdclass();
        $expected->workshopid = $this->workshop->id;
        $expected->userid = 3;
        $expected->gradinggrade = 82.87670;
        $expected->timegraded = $now;
                        $this->workshop->aggregate_grading_grades_process($batch, $now);
    }

    public function test_aggregate_grading_grades_process_single_grade_update() {
        $this->resetAfterTest(true);
                $batch = array();
        $batch[] = (object)array('reviewerid'=>3, 'gradinggrade'=>90.00000, 'gradinggradeover'=>null, 'aggregationid'=>1, 'aggregatedgrade'=>82.87670);
                                $this->workshop->aggregate_grading_grades_process($batch);
    }

    public function test_aggregate_grading_grades_process_single_grade_uptodate() {
        $this->resetAfterTest(true);
                $batch = array();
        $batch[] = (object)array('reviewerid'=>3, 'gradinggrade'=>90.00000, 'gradinggradeover'=>null, 'aggregationid'=>1, 'aggregatedgrade'=>90.00000);
                                $this->workshop->aggregate_grading_grades_process($batch);
    }

    public function test_aggregate_grading_grades_process_single_grade_overridden() {
        $this->resetAfterTest(true);
                $batch = array();
        $batch[] = (object)array('reviewerid'=>4, 'gradinggrade'=>91.56700, 'gradinggradeover'=>82.32105, 'aggregationid'=>2, 'aggregatedgrade'=>91.56700);
                                $this->workshop->aggregate_grading_grades_process($batch);
    }

    public function test_aggregate_grading_grades_process_multiple_grades_new() {
        $this->resetAfterTest(true);
                $batch = array();
        $batch[] = (object)array('reviewerid'=>5, 'gradinggrade'=>99.45670, 'gradinggradeover'=>null, 'aggregationid'=>null, 'aggregatedgrade'=>null);
        $batch[] = (object)array('reviewerid'=>5, 'gradinggrade'=>87.34311, 'gradinggradeover'=>null, 'aggregationid'=>null, 'aggregatedgrade'=>null);
        $batch[] = (object)array('reviewerid'=>5, 'gradinggrade'=>51.12000, 'gradinggradeover'=>null, 'aggregationid'=>null, 'aggregatedgrade'=>null);
                $now = time();
        $expected = new stdclass();
        $expected->workshopid = $this->workshop->id;
        $expected->userid = 5;
        $expected->gradinggrade = 79.3066;
        $expected->timegraded = $now;
                        $this->workshop->aggregate_grading_grades_process($batch, $now);
    }

    public function test_aggregate_grading_grades_process_multiple_grades_update() {
        $this->resetAfterTest(true);
                $batch = array();
        $batch[] = (object)array('reviewerid'=>5, 'gradinggrade'=>56.23400, 'gradinggradeover'=>null, 'aggregationid'=>2, 'aggregatedgrade'=>79.30660);
        $batch[] = (object)array('reviewerid'=>5, 'gradinggrade'=>87.34311, 'gradinggradeover'=>null, 'aggregationid'=>2, 'aggregatedgrade'=>79.30660);
        $batch[] = (object)array('reviewerid'=>5, 'gradinggrade'=>51.12000, 'gradinggradeover'=>null, 'aggregationid'=>2, 'aggregatedgrade'=>79.30660);
                                $this->workshop->aggregate_grading_grades_process($batch);
    }

    public function test_aggregate_grading_grades_process_multiple_grades_overriden() {
        $this->resetAfterTest(true);
                $batch = array();
        $batch[] = (object)array('reviewerid'=>5, 'gradinggrade'=>56.23400, 'gradinggradeover'=>99.45670, 'aggregationid'=>2, 'aggregatedgrade'=>64.89904);
        $batch[] = (object)array('reviewerid'=>5, 'gradinggrade'=>87.34311, 'gradinggradeover'=>null, 'aggregationid'=>2, 'aggregatedgrade'=>64.89904);
        $batch[] = (object)array('reviewerid'=>5, 'gradinggrade'=>51.12000, 'gradinggradeover'=>null, 'aggregationid'=>2, 'aggregatedgrade'=>64.89904);
                                $this->workshop->aggregate_grading_grades_process($batch);
    }

    public function test_aggregate_grading_grades_process_multiple_grades_one_missing() {
        $this->resetAfterTest(true);
                $batch = array();
        $batch[] = (object)array('reviewerid'=>6, 'gradinggrade'=>50.00000, 'gradinggradeover'=>null, 'aggregationid'=>3, 'aggregatedgrade'=>100.00000);
        $batch[] = (object)array('reviewerid'=>6, 'gradinggrade'=>null, 'gradinggradeover'=>null, 'aggregationid'=>3, 'aggregatedgrade'=>100.00000);
        $batch[] = (object)array('reviewerid'=>6, 'gradinggrade'=>52.20000, 'gradinggradeover'=>null, 'aggregationid'=>3, 'aggregatedgrade'=>100.00000);
                                $this->workshop->aggregate_grading_grades_process($batch);
    }

    public function test_aggregate_grading_grades_process_multiple_grades_missing_overridden() {
        $this->resetAfterTest(true);
                $batch = array();
        $batch[] = (object)array('reviewerid'=>6, 'gradinggrade'=>50.00000, 'gradinggradeover'=>null, 'aggregationid'=>3, 'aggregatedgrade'=>100.00000);
        $batch[] = (object)array('reviewerid'=>6, 'gradinggrade'=>null, 'gradinggradeover'=>69.00000, 'aggregationid'=>3, 'aggregatedgrade'=>100.00000);
        $batch[] = (object)array('reviewerid'=>6, 'gradinggrade'=>52.20000, 'gradinggradeover'=>null, 'aggregationid'=>3, 'aggregatedgrade'=>100.00000);
                                $this->workshop->aggregate_grading_grades_process($batch);
    }

    public function test_percent_to_value() {
        $this->resetAfterTest(true);
                $total = 185;
        $percent = 56.6543;
                $part = workshop::percent_to_value($percent, $total);
                $this->assertEquals($part, $total * $percent / 100);
    }

    public function test_percent_to_value_negative() {
        $this->resetAfterTest(true);
                $total = 185;
        $percent = -7.098;
                $this->setExpectedException('coding_exception');
                $part = workshop::percent_to_value($percent, $total);
    }

    public function test_percent_to_value_over_hundred() {
        $this->resetAfterTest(true);
                $total = 185;
        $percent = 121.08;
                $this->setExpectedException('coding_exception');
                $part = workshop::percent_to_value($percent, $total);
    }

    public function test_lcm() {
        $this->resetAfterTest(true);
                $this->assertEquals(workshop::lcm(1,4), 4);
        $this->assertEquals(workshop::lcm(2,4), 4);
        $this->assertEquals(workshop::lcm(4,2), 4);
        $this->assertEquals(workshop::lcm(2,3), 6);
        $this->assertEquals(workshop::lcm(6,4), 12);
    }

    public function test_lcm_array() {
        $this->resetAfterTest(true);
                $numbers = array(5,3,15);
                $lcm = array_reduce($numbers, 'workshop::lcm', 1);
                $this->assertEquals($lcm, 15);
    }

    public function test_prepare_example_assessment() {
        $this->resetAfterTest(true);
                $fakerawrecord = (object)array(
            'id'                => 42,
            'submissionid'      => 56,
            'weight'            => 0,
            'timecreated'       => time() - 10,
            'timemodified'      => time() - 5,
            'grade'             => null,
            'gradinggrade'      => null,
            'gradinggradeover'  => null,
            'feedbackauthor'    => null,
            'feedbackauthorformat' => 0,
            'feedbackauthorattachment' => 0,
        );
                $a = $this->workshop->prepare_example_assessment($fakerawrecord);
                $this->assertTrue($a instanceof workshop_example_assessment);
        $this->assertTrue($a->url instanceof moodle_url);

                $fakerawrecord->weight = 1;
        $this->setExpectedException('coding_exception');
                $a = $this->workshop->prepare_example_assessment($fakerawrecord);
    }

    public function test_prepare_example_reference_assessment() {
        global $USER;
        $this->resetAfterTest(true);
                $fakerawrecord = (object)array(
            'id'                => 38,
            'submissionid'      => 56,
            'weight'            => 1,
            'timecreated'       => time() - 100,
            'timemodified'      => time() - 50,
            'grade'             => 0.75000,
            'gradinggrade'      => 1.00000,
            'gradinggradeover'  => null,
            'feedbackauthor'    => null,
            'feedbackauthorformat' => 0,
            'feedbackauthorattachment' => 0,
        );
                $a = $this->workshop->prepare_example_reference_assessment($fakerawrecord);
                $this->assertTrue($a instanceof workshop_example_reference_assessment);

                $fakerawrecord->weight = 0;
        $this->setExpectedException('coding_exception');
                $a = $this->workshop->prepare_example_reference_assessment($fakerawrecord);
    }

    
    public function test_user_restrictions() {
        global $DB, $CFG;

        $this->resetAfterTest();

                $courseid = $this->workshop->course->id;

                $generator = $this->getDataGenerator();
        $grouping = $generator->create_grouping(array('courseid' => $courseid));
        $group1 = $generator->create_group(array('courseid' => $courseid));
        groups_assign_grouping($grouping->id, $group1->id);
        $group2 = $generator->create_group(array('courseid' => $courseid));
        groups_assign_grouping($grouping->id, $group2->id);

                $group3 = $generator->create_group(array('courseid' => $courseid));

                $roleids = $DB->get_records_menu('role', null, '', 'shortname, id');
        $student1 = $generator->create_user();
        $student2 = $generator->create_user();
        $student3 = $generator->create_user();
        $generator->enrol_user($student1->id, $courseid, $roleids['student']);
        $generator->enrol_user($student2->id, $courseid, $roleids['student']);
        $generator->enrol_user($student3->id, $courseid, $roleids['student']);

                groups_add_member($group1, $student1);
        groups_add_member($group2, $student2);
        groups_add_member($group3, $student3);

                        $allusers = get_enrolled_users(context_course::instance($courseid));
        $result = $this->workshop->get_grouped($allusers);
        $this->assertCount(4, $result);
        $users = array_keys($result[0]);
        sort($users);
        $this->assertEquals(array($student1->id, $student2->id, $student3->id), $users);
        $this->assertEquals(array($student1->id), array_keys($result[$group1->id]));
        $this->assertEquals(array($student2->id), array_keys($result[$group2->id]));
        $this->assertEquals(array($student3->id), array_keys($result[$group3->id]));

                $users = $this->workshop->get_potential_authors(false);
        $this->assertCount(3, $users);
        $users = $this->workshop->get_potential_authors(false, $group2->id);
        $this->assertEquals(array($student2->id), array_keys($users));

                $workshopitem = $this->getDataGenerator()->create_module('workshop',
                array('course' => $courseid, 'groupmode' => SEPARATEGROUPS,
                'groupingid' => $grouping->id));
        $cm = get_coursemodule_from_instance('workshop', $workshopitem->id,
                $courseid, false, MUST_EXIST);
        $workshopgrouping = new testable_workshop($workshopitem, $cm, $this->workshop->course);

                        $result = $workshopgrouping->get_grouped($allusers);
        $this->assertCount(3, $result);
        $users = array_keys($result[0]);
        sort($users);
        $this->assertEquals(array($student1->id, $student2->id), $users);
        $this->assertEquals(array($student1->id), array_keys($result[$group1->id]));
        $this->assertEquals(array($student2->id), array_keys($result[$group2->id]));

                $users = $workshopgrouping->get_potential_authors(false);
        $userids = array_keys($users);
        sort($userids);
        $this->assertEquals(array($student1->id, $student2->id), $userids);
        $users = $workshopgrouping->get_potential_authors(false, $group2->id);
        $this->assertEquals(array($student2->id), array_keys($users));

                        $CFG->enableavailability = true;
        $workshopitem = $this->getDataGenerator()->create_module('workshop',
                array('course' => $courseid, 'availability' => json_encode(
                    \core_availability\tree::get_root_json(array(
                    \availability_grouping\condition::get_json($grouping->id)),
                    \core_availability\tree::OP_AND, false))));
        $cm = get_coursemodule_from_instance('workshop', $workshopitem->id,
                $courseid, false, MUST_EXIST);
        $workshoprestricted = new testable_workshop($workshopitem, $cm, $this->workshop->course);

                                        $result = $workshoprestricted->get_grouped($allusers);
        $this->assertCount(4, $result);
        $this->assertCount(3, $result[0]);

                $users = $workshoprestricted->get_potential_authors(false);
        $userids = array_keys($users);
        sort($userids);
        $this->assertEquals(array($student1->id, $student2->id), $userids);
        $users = $workshoprestricted->get_potential_authors(false, $group2->id);
        $this->assertEquals(array($student2->id), array_keys($users));
    }

    
    public function test_reset_phase() {
        $this->resetAfterTest(true);

        $this->workshop->switch_phase(workshop::PHASE_CLOSED);
        $this->assertEquals(workshop::PHASE_CLOSED, $this->workshop->phase);

        $settings = (object)array(
            'reset_workshop_phase' => 0,
        );
        $status = $this->workshop->reset_userdata($settings);
        $this->assertEquals(workshop::PHASE_CLOSED, $this->workshop->phase);

        $settings = (object)array(
            'reset_workshop_phase' => 1,
        );
        $status = $this->workshop->reset_userdata($settings);
        $this->assertEquals(workshop::PHASE_SETUP, $this->workshop->phase);
        foreach ($status as $result) {
            $this->assertFalse($result['error']);
        }
    }

    
    public function test_reset_userdata_assessments() {
        global $DB;
        $this->resetAfterTest(true);

        $student1 = $this->getDataGenerator()->create_user();
        $student2 = $this->getDataGenerator()->create_user();

        $this->getDataGenerator()->enrol_user($student1->id, $this->workshop->course->id);
        $this->getDataGenerator()->enrol_user($student2->id, $this->workshop->course->id);

        $workshopgenerator = $this->getDataGenerator()->get_plugin_generator('mod_workshop');

        $subid1 = $workshopgenerator->create_submission($this->workshop->id, $student1->id);
        $subid2 = $workshopgenerator->create_submission($this->workshop->id, $student2->id);

        $asid1 = $workshopgenerator->create_assessment($subid1, $student2->id);
        $asid2 = $workshopgenerator->create_assessment($subid2, $student1->id);

        $settings = (object)array(
            'reset_workshop_assessments' => 1,
        );
        $status = $this->workshop->reset_userdata($settings);

        foreach ($status as $result) {
            $this->assertFalse($result['error']);
        }

        $this->assertEquals(2, $DB->count_records('workshop_submissions', array('workshopid' => $this->workshop->id)));
        $this->assertEquals(0, $DB->count_records('workshop_assessments'));
    }

    
    public function test_reset_userdata_submissions() {
        global $DB;
        $this->resetAfterTest(true);

        $student1 = $this->getDataGenerator()->create_user();
        $student2 = $this->getDataGenerator()->create_user();

        $this->getDataGenerator()->enrol_user($student1->id, $this->workshop->course->id);
        $this->getDataGenerator()->enrol_user($student2->id, $this->workshop->course->id);

        $workshopgenerator = $this->getDataGenerator()->get_plugin_generator('mod_workshop');

        $subid1 = $workshopgenerator->create_submission($this->workshop->id, $student1->id);
        $subid2 = $workshopgenerator->create_submission($this->workshop->id, $student2->id);

        $asid1 = $workshopgenerator->create_assessment($subid1, $student2->id);
        $asid2 = $workshopgenerator->create_assessment($subid2, $student1->id);

        $settings = (object)array(
            'reset_workshop_submissions' => 1,
        );
        $status = $this->workshop->reset_userdata($settings);

        foreach ($status as $result) {
            $this->assertFalse($result['error']);
        }

        $this->assertEquals(0, $DB->count_records('workshop_submissions', array('workshopid' => $this->workshop->id)));
        $this->assertEquals(0, $DB->count_records('workshop_assessments'));
    }

    
    public function test_normalize_file_extensions() {
        $this->resetAfterTest(true);

        $this->assertSame(['.odt'], workshop::normalize_file_extensions('odt'));
        $this->assertSame(['.odt'], workshop::normalize_file_extensions('.odt'));
        $this->assertSame(['.odt'], workshop::normalize_file_extensions('.ODT'));
        $this->assertSame(['.doc', '.jpg', '.mp3'], workshop::normalize_file_extensions('doc, jpg, mp3'));
        $this->assertSame(['.doc', '.jpg', '.mp3'], workshop::normalize_file_extensions(['.doc', '.jpg', '.mp3']));
        $this->assertSame(['.doc', '.jpg', '.mp3'], workshop::normalize_file_extensions('doc, *.jpg, mp3'));
        $this->assertSame(['.doc', '.jpg', '.mp3'], workshop::normalize_file_extensions(['doc ', ' JPG ', '.mp3']));
        $this->assertSame(['.rtf', '.pdf', '.docx'], workshop::normalize_file_extensions("RTF,.pdf\n...DocX,,,;\rPDF\trtf ...Rtf"));
        $this->assertSame(['.tgz', '.tar.gz'], workshop::normalize_file_extensions('tgz,TAR.GZ tar.gz .tar.gz tgz TGZ'));
        $this->assertSame(['.notebook'], workshop::normalize_file_extensions('"Notebook":notebook;NOTEBOOK;,\'NoTeBook\''));
        $this->assertSame([], workshop::normalize_file_extensions(''));
        $this->assertSame([], workshop::normalize_file_extensions([]));
        $this->assertSame(['.0'], workshop::normalize_file_extensions(0));
        $this->assertSame(['.0'], workshop::normalize_file_extensions('0'));
        $this->assertSame(['.odt'], workshop::normalize_file_extensions('*.odt'));
        $this->assertSame([], workshop::normalize_file_extensions('.'));
        $this->assertSame(['.foo'], workshop::normalize_file_extensions('. foo'));
        $this->assertSame([], workshop::normalize_file_extensions('*'));
        $this->assertSame([], workshop::normalize_file_extensions('*~'));
        $this->assertSame(['.pdf', '.ps'], workshop::normalize_file_extensions('* pdf *.ps foo* *bar .r??'));
    }

    
    public function test_clean_file_extensions() {
        $this->resetAfterTest(true);

        $this->assertSame('', workshop::clean_file_extensions(''));
        $this->assertSame('', workshop::clean_file_extensions(null));
        $this->assertSame('', workshop::clean_file_extensions(' '));
        $this->assertSame('0', workshop::clean_file_extensions(0));
        $this->assertSame('0', workshop::clean_file_extensions('0'));
        $this->assertSame('doc, rtf, pdf', workshop::clean_file_extensions('*.Doc, RTF, PDF, .rtf'.PHP_EOL.'PDF '));
        $this->assertSame('doc, rtf, pdf', 'doc, rtf, pdf');
    }

    
    public function test_invalid_file_extensions() {
        $this->resetAfterTest(true);

        $this->assertSame([], workshop::invalid_file_extensions('', ''));
        $this->assertSame([], workshop::invalid_file_extensions('', '.doc'));
        $this->assertSame([], workshop::invalid_file_extensions('odt', ''));
        $this->assertSame([], workshop::invalid_file_extensions('odt', '*'));
        $this->assertSame([], workshop::invalid_file_extensions('odt', 'odt'));
        $this->assertSame([], workshop::invalid_file_extensions('doc, odt, pdf', ['pdf', 'doc', 'odt']));
        $this->assertSame([], workshop::invalid_file_extensions(['doc', 'odt', 'PDF'], ['.doc', '.pdf', '.odt']));
        $this->assertSame([], workshop::invalid_file_extensions('*~ .docx, Odt PDF :doc .pdf', '*.docx *.odt *.pdf *.doc'));
        $this->assertSame(['.00001-wtf-is-this'], workshop::invalid_file_extensions('docx tgz .00001-wtf-is-this', 'tgz docx'));
        $this->assertSame(['.foobar', '.wtfisthis'], workshop::invalid_file_extensions(['.pdf', '.foobar', 'wtfisthis'], 'pdf'));
        $this->assertSame([], workshop::invalid_file_extensions('', ''));
        $this->assertSame(['.odt'], workshop::invalid_file_extensions(['.PDF', 'PDF', '.ODT'], 'jpg pdf png gif'));
        $this->assertSame(['.odt'], workshop::invalid_file_extensions(['.PDF', 'PDF', '.ODT'], '.jpg,.pdf,  .png .gif'));
        $this->assertSame(['.exe', '.bat'], workshop::invalid_file_extensions(['.exe', '.odt', '.bat', ''], 'odt'));
    }

    
    public function test_is_allowed_file_type() {
        $this->resetAfterTest(true);

        $this->assertTrue(workshop::is_allowed_file_type('README.txt', ''));
        $this->assertTrue(workshop::is_allowed_file_type('README.txt', ['']));
        $this->assertFalse(workshop::is_allowed_file_type('README.txt', '0'));

        $this->assertFalse(workshop::is_allowed_file_type('README.txt', 'xt'));
        $this->assertFalse(workshop::is_allowed_file_type('README.txt', 'old.txt'));

        $this->assertTrue(workshop::is_allowed_file_type('README.txt', 'txt'));
        $this->assertTrue(workshop::is_allowed_file_type('README.txt', '.TXT'));
        $this->assertTrue(workshop::is_allowed_file_type('README.TXT', 'txt'));
        $this->assertTrue(workshop::is_allowed_file_type('README.txt', '.txt .md'));
        $this->assertTrue(workshop::is_allowed_file_type('README.txt', 'HTML TXT DOC RTF'));
        $this->assertTrue(workshop::is_allowed_file_type('README.txt', ['HTML', '...TXT', 'DOC', 'RTF']));

        $this->assertTrue(workshop::is_allowed_file_type('C:\Moodle\course-data.tar.gz', 'gzip zip 7z tar.gz'));
        $this->assertFalse(workshop::is_allowed_file_type('C:\Moodle\course-data.tar.gz', 'gzip zip 7z tar'));
        $this->assertTrue(workshop::is_allowed_file_type('~/course-data.tar.gz', 'gzip zip 7z gz'));
        $this->assertFalse(workshop::is_allowed_file_type('~/course-data.tar.gz', 'gzip zip 7z'));

        $this->assertFalse(workshop::is_allowed_file_type('Alice on the beach.jpg.exe', 'png gif jpg bmp'));
        $this->assertFalse(workshop::is_allowed_file_type('xfiles.exe.jpg', 'exe com bat sh'));
        $this->assertFalse(workshop::is_allowed_file_type('solution.odt~', 'odt, xls'));
        $this->assertTrue(workshop::is_allowed_file_type('solution.odt~', 'odt, odt~'));
    }
}
