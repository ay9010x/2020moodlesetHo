<?php




class mod_workshop_generator_testcase extends advanced_testcase {

    public function test_create_instance() {
        global $DB;
        $this->resetAfterTest();
        $this->setAdminUser();

        $course = $this->getDataGenerator()->create_course();

        $this->assertFalse($DB->record_exists('workshop', array('course' => $course->id)));
        $workshop = $this->getDataGenerator()->create_module('workshop', array('course' => $course));
        $records = $DB->get_records('workshop', array('course' => $course->id), 'id');
        $this->assertEquals(1, count($records));
        $this->assertTrue(array_key_exists($workshop->id, $records));

        $params = array('course' => $course->id, 'name' => 'Another workshop');
        $workshop = $this->getDataGenerator()->create_module('workshop', $params);
        $records = $DB->get_records('workshop', array('course' => $course->id), 'id');
        $this->assertEquals(2, count($records));
        $this->assertEquals('Another workshop', $records[$workshop->id]->name);
    }

    public function test_create_submission() {
        global $DB;
        $this->resetAfterTest();
        $this->setAdminUser();

        $course = $this->getDataGenerator()->create_course();
        $workshop = $this->getDataGenerator()->create_module('workshop', array('course' => $course));
        $user = $this->getDataGenerator()->create_user();
        $this->getDataGenerator()->enrol_user($user->id, $course->id);
        $workshopgenerator = $this->getDataGenerator()->get_plugin_generator('mod_workshop');

        $id = $workshopgenerator->create_submission($workshop->id, $user->id, array(
            'title' => 'My custom title',
        ));

        $submissions = $DB->get_records('workshop_submissions', array('workshopid' => $workshop->id));
        $this->assertEquals(1, count($submissions));
        $this->assertTrue(isset($submissions[$id]));
        $this->assertEquals($submissions[$id]->authorid, $user->id);
        $this->assertSame('My custom title', $submissions[$id]->title);
    }

    public function test_create_assessment() {
        global $DB;
        $this->resetAfterTest();
        $this->setAdminUser();

        $course = $this->getDataGenerator()->create_course();
        $workshop = $this->getDataGenerator()->create_module('workshop', array('course' => $course));
        $user1 = $this->getDataGenerator()->create_user();
        $user2 = $this->getDataGenerator()->create_user();
        $this->getDataGenerator()->enrol_user($user1->id, $course->id);
        $this->getDataGenerator()->enrol_user($user2->id, $course->id);
        $workshopgenerator = $this->getDataGenerator()->get_plugin_generator('mod_workshop');

        $submissionid1 = $workshopgenerator->create_submission($workshop->id, $user1->id);
        $submissionid2 = $workshopgenerator->create_submission($workshop->id, $user2->id);

        $assessmentid1 = $workshopgenerator->create_assessment($submissionid1, $user2->id, array(
            'weight' => 3,
            'grade' => 95.00000,
        ));
        $assessmentid2 = $workshopgenerator->create_assessment($submissionid2, $user1->id);

        $assessments = $DB->get_records('workshop_assessments');
        $this->assertTrue(isset($assessments[$assessmentid1]));
        $this->assertTrue(isset($assessments[$assessmentid2]));
        $this->assertEquals(3, $assessments[$assessmentid1]->weight);
        $this->assertEquals(95.00000, $assessments[$assessmentid1]->grade);
        $this->assertEquals(1, $assessments[$assessmentid2]->weight);
        $this->assertNull($assessments[$assessmentid2]->grade);
    }
}
