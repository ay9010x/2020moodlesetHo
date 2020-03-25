<?php



defined('MOODLE_INTERNAL') || die();


class enrol_lti_helper_testcase extends advanced_testcase {

    
    public $user1;

    
    public $user2;

    
    public function setUp() {
        $this->resetAfterTest();

                $this->setAdminUser();

                $this->user1 = self::getDataGenerator()->create_user();
        $this->user2 = self::getDataGenerator()->create_user();
    }

    
    public function test_update_user_profile_image() {
        global $DB, $CFG;

                \enrol_lti\helper::update_user_profile_image($this->user1->id, $this->getExternalTestFileUrl('/test.jpg'));

                $this->user1 = $DB->get_record('user', array('id' => $this->user1->id));

                $page = new moodle_page();
        $page->set_url('/user/profile.php');
        $page->set_context(context_system::instance());
        $renderer = $page->get_renderer('core');
        $usercontext = context_user::instance($this->user1->id);

                $userpicture = new user_picture($this->user1);
        $this->assertSame($CFG->wwwroot . '/pluginfile.php/' . $usercontext->id . '/user/icon/clean/f2?rev=' .$this->user1->picture,
            $userpicture->get_url($page, $renderer)->out(false));
    }

    
    public function test_enrol_user_max_enrolled() {
        global $DB;

                $data = new stdClass();
        $data->maxenrolled = 1;
        $tool = $this->create_tool($data);

                $tool = \enrol_lti\helper::get_lti_tool($tool->id);

                $result = \enrol_lti\helper::enrol_user($tool, $this->user1->id);

                $this->assertEquals(true, $result);
        $this->assertEquals(1, $DB->count_records('user_enrolments', array('enrolid' => $tool->enrolid)));

                $result = \enrol_lti\helper::enrol_user($tool, $this->user2->id);

                $this->assertEquals(\enrol_lti\helper::ENROLMENT_MAX_ENROLLED, $result);
        $this->assertEquals(1, $DB->count_records('user_enrolments', array('enrolid' => $tool->enrolid)));
    }

    
    public function test_enrol_user_enrolment_not_started() {
        global $DB;

                $data = new stdClass();
        $data->enrolstartdate = time() + DAYSECS;         $tool = $this->create_tool($data);

                $tool = \enrol_lti\helper::get_lti_tool($tool->id);

                $result = \enrol_lti\helper::enrol_user($tool, $this->user1->id);

                $this->assertEquals(\enrol_lti\helper::ENROLMENT_NOT_STARTED, $result);
        $this->assertEquals(0, $DB->count_records('user_enrolments', array('enrolid' => $tool->enrolid)));
    }

    
    public function test_enrol_user_enrolment_finished() {
        global $DB;

                $data = new stdClass();
        $data->enrolenddate = time() - DAYSECS;         $tool = $this->create_tool($data);

                $tool = \enrol_lti\helper::get_lti_tool($tool->id);

                $result = \enrol_lti\helper::enrol_user($tool, $this->user1->id);

                $this->assertEquals(\enrol_lti\helper::ENROLMENT_FINISHED, $result);
        $this->assertEquals(0, $DB->count_records('user_enrolments', array('enrolid' => $tool->enrolid)));
    }

    
    public function test_count_lti_tools() {
                $course1 = $this->getDataGenerator()->create_course();
        $data = new stdClass();
        $data->courseid = $course1->id;
        $this->create_tool($data);
        $this->create_tool($data);

                $course2 = $this->getDataGenerator()->create_course();
        $data = new stdClass();
        $data->courseid = $course2->id;
        $this->create_tool($data);

                $data->status = ENROL_INSTANCE_DISABLED;
        $this->create_tool($data);

                $count = \enrol_lti\helper::count_lti_tools();
        $this->assertEquals(4, $count);

                $count = \enrol_lti\helper::count_lti_tools(array('courseid' => $course1->id));
        $this->assertEquals(2, $count);

                $count = \enrol_lti\helper::count_lti_tools(array('courseid' => $course2->id, 'status' => ENROL_INSTANCE_DISABLED));
        $this->assertEquals(1, $count);

                $count = \enrol_lti\helper::count_lti_tools(array('status' => ENROL_INSTANCE_ENABLED));
        $this->assertEquals(3, $count);
    }

    
    public function test_get_lti_tools() {
                $course1 = $this->getDataGenerator()->create_course();
        $data = new stdClass();
        $data->courseid = $course1->id;
        $tool1 = $this->create_tool($data);
        $tool2 = $this->create_tool($data);

                $course2 = $this->getDataGenerator()->create_course();
        $data = new stdClass();
        $data->courseid = $course2->id;
        $tool3 = $this->create_tool($data);

                $data->status = ENROL_INSTANCE_DISABLED;
        $tool4 = $this->create_tool($data);

                $tools = \enrol_lti\helper::get_lti_tools();

                $this->assertEquals(4, count($tools));

                $tools = \enrol_lti\helper::get_lti_tools(array('courseid' => $course1->id));

                $this->assertEquals(2, count($tools));
        $this->assertTrue(isset($tools[$tool1->id]));
        $this->assertTrue(isset($tools[$tool2->id]));

                $tools = \enrol_lti\helper::get_lti_tools(array('courseid' => $course2->id, 'status' => ENROL_INSTANCE_DISABLED));

                $this->assertEquals(1, count($tools));
        $this->assertTrue(isset($tools[$tool4->id]));

                $tools = \enrol_lti\helper::get_lti_tools(array('status' => ENROL_INSTANCE_ENABLED));

                $this->assertEquals(3, count($tools));
        $this->assertTrue(isset($tools[$tool1->id]));
        $this->assertTrue(isset($tools[$tool2->id]));
        $this->assertTrue(isset($tools[$tool3->id]));
    }

    
    protected function create_tool($data = array()) {
        global $DB;

        $studentrole = $DB->get_record('role', array('shortname' => 'student'));
        $teacherrole = $DB->get_record('role', array('shortname' => 'teacher'));

                if (empty($data->courseid)) {
            $course = $this->getDataGenerator()->create_course();
            $data->courseid = $course->id;
        } else {
            $course = get_course($data->courseid);
        }

                if (!isset($data->status)) {
            $data->status = ENROL_INSTANCE_ENABLED;
        }

                $data->name = 'Test LTI';
        $data->contextid = context_course::instance($data->courseid)->id;
        $data->roleinstructor = $studentrole->id;
        $data->rolelearner = $teacherrole->id;

                $enrolplugin = enrol_get_plugin('lti');
        $instanceid = $enrolplugin->add_instance($course, (array) $data);

                return $DB->get_record('enrol_lti_tools', array('enrolid' => $instanceid));
    }
}
