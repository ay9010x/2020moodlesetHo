<?php




defined('MOODLE_INTERNAL') || die();

global $CFG;
require_once($CFG->dirroot . '/mod/assign/locallib.php');
require_once($CFG->dirroot . '/mod/assign/upgradelib.php');


class mod_assign_base_testcase extends advanced_testcase {

    
    const DEFAULT_STUDENT_COUNT = 3;
    
    const DEFAULT_TEACHER_COUNT = 2;
    
    const DEFAULT_EDITING_TEACHER_COUNT = 2;
    
    const EXTRA_STUDENT_COUNT = 40;
    
    const EXTRA_SUSPENDED_COUNT = 10;
    
    const EXTRA_TEACHER_COUNT = 5;
    
    const EXTRA_EDITING_TEACHER_COUNT = 5;
    
    const GROUP_COUNT = 6;

    
    protected $course = null;

    
    protected $teachers = null;

    
    protected $editingteachers = null;

    
    protected $students = null;

    
    protected $extrateachers = null;

    
    protected $extraeditingteachers = null;

    
    protected $extrastudents = null;

    
    protected $extrasuspendedstudents = null;

    
    protected $groups = null;

    
    protected function setUp() {
        global $DB;

        $this->resetAfterTest(true);

        $this->course = $this->getDataGenerator()->create_course(array('enablecompletion' => 1));
        $this->teachers = array();
        for ($i = 0; $i < self::DEFAULT_TEACHER_COUNT; $i++) {
            array_push($this->teachers, $this->getDataGenerator()->create_user());
        }

        $this->editingteachers = array();
        for ($i = 0; $i < self::DEFAULT_EDITING_TEACHER_COUNT; $i++) {
            array_push($this->editingteachers, $this->getDataGenerator()->create_user());
        }

        $this->students = array();
        for ($i = 0; $i < self::DEFAULT_STUDENT_COUNT; $i++) {
            array_push($this->students, $this->getDataGenerator()->create_user());
        }

        $this->groups = array();
        for ($i = 0; $i < self::GROUP_COUNT; $i++) {
            array_push($this->groups, $this->getDataGenerator()->create_group(array('courseid'=>$this->course->id)));
        }

        $teacherrole = $DB->get_record('role', array('shortname'=>'teacher'));
        foreach ($this->teachers as $i => $teacher) {
            $this->getDataGenerator()->enrol_user($teacher->id,
                                                  $this->course->id,
                                                  $teacherrole->id);
            groups_add_member($this->groups[$i % self::GROUP_COUNT], $teacher);
        }

        $editingteacherrole = $DB->get_record('role', array('shortname'=>'editingteacher'));
        foreach ($this->editingteachers as $i => $editingteacher) {
            $this->getDataGenerator()->enrol_user($editingteacher->id,
                                                  $this->course->id,
                                                  $editingteacherrole->id);
            groups_add_member($this->groups[$i % self::GROUP_COUNT], $editingteacher);
        }

        $studentrole = $DB->get_record('role', array('shortname'=>'student'));
        foreach ($this->students as $i => $student) {
            $this->getDataGenerator()->enrol_user($student->id,
                                                  $this->course->id,
                                                  $studentrole->id);
            groups_add_member($this->groups[$i % self::GROUP_COUNT], $student);
        }
    }

    
    protected function create_extra_users() {
        global $DB;
        $this->extrateachers = array();
        for ($i = 0; $i < self::EXTRA_TEACHER_COUNT; $i++) {
            array_push($this->extrateachers, $this->getDataGenerator()->create_user());
        }

        $this->extraeditingteachers = array();
        for ($i = 0; $i < self::EXTRA_EDITING_TEACHER_COUNT; $i++) {
            array_push($this->extraeditingteachers, $this->getDataGenerator()->create_user());
        }

        $this->extrastudents = array();
        for ($i = 0; $i < self::EXTRA_STUDENT_COUNT; $i++) {
            array_push($this->extrastudents, $this->getDataGenerator()->create_user());
        }

        $this->extrasuspendedstudents = array();
        for ($i = 0; $i < self::EXTRA_SUSPENDED_COUNT; $i++) {
            array_push($this->extrasuspendedstudents, $this->getDataGenerator()->create_user());
        }

        $teacherrole = $DB->get_record('role', array('shortname'=>'teacher'));
        foreach ($this->extrateachers as $i => $teacher) {
            $this->getDataGenerator()->enrol_user($teacher->id,
                                                  $this->course->id,
                                                  $teacherrole->id);
            groups_add_member($this->groups[$i % self::GROUP_COUNT], $teacher);
        }

        $editingteacherrole = $DB->get_record('role', array('shortname'=>'editingteacher'));
        foreach ($this->extraeditingteachers as $i => $editingteacher) {
            $this->getDataGenerator()->enrol_user($editingteacher->id,
                                                  $this->course->id,
                                                  $editingteacherrole->id);
            groups_add_member($this->groups[$i % self::GROUP_COUNT], $editingteacher);
        }

        $studentrole = $DB->get_record('role', array('shortname'=>'student'));
        foreach ($this->extrastudents as $i => $student) {
            $this->getDataGenerator()->enrol_user($student->id,
                                                  $this->course->id,
                                                  $studentrole->id);
            if ($i < (self::EXTRA_STUDENT_COUNT / 2)) {
                groups_add_member($this->groups[$i % self::GROUP_COUNT], $student);
            }
        }

        foreach ($this->extrasuspendedstudents as $i => $suspendedstudent) {
            $this->getDataGenerator()->enrol_user($suspendedstudent->id,
                                                  $this->course->id,
                                                  $studentrole->id, 'manual', 0, 0, ENROL_USER_SUSPENDED);
            if ($i < (self::EXTRA_SUSPENDED_COUNT / 2)) {
                groups_add_member($this->groups[$i % self::GROUP_COUNT], $suspendedstudent);
            }
        }
    }

    
    protected function create_instance($params=array()) {
        $generator = $this->getDataGenerator()->get_plugin_generator('mod_assign');
        $params['course'] = $this->course->id;
        $instance = $generator->create_instance($params);
        $cm = get_coursemodule_from_instance('assign', $instance->id);
        $context = context_module::instance($cm->id);
        return new testable_assign($context, $cm, $this->course);
    }

    public function test_create_instance() {
        $this->assertNotEmpty($this->create_instance());
    }

}


class testable_assign extends assign {

    public function testable_show_intro() {
        return parent::show_intro();
    }

    public function testable_delete_grades() {
        return parent::delete_grades();
    }

    public function testable_apply_grade_to_user($formdata, $userid, $attemptnumber) {
        return parent::apply_grade_to_user($formdata, $userid, $attemptnumber);
    }

    public function testable_format_submission_for_log(stdClass $submission) {
        return parent::format_submission_for_log($submission);
    }

    public function testable_get_grading_userid_list() {
        return parent::get_grading_userid_list();
    }

    public function testable_is_graded($userid) {
        return parent::is_graded($userid);
    }

    public function testable_update_submission(stdClass $submission, $userid, $updatetime, $teamsubmission) {
        return parent::update_submission($submission, $userid, $updatetime, $teamsubmission);
    }

    public function testable_process_add_attempt($userid = 0) {
        return parent::process_add_attempt($userid);
    }

    public function testable_process_save_quick_grades($postdata) {
                global $_POST;
        $_POST = $postdata;
        return parent::process_save_quick_grades();
    }

    public function testable_process_set_batch_marking_allocation($selectedusers, $markerid) {
        global $CFG;
        require_once($CFG->dirroot . '/mod/assign/batchsetallocatedmarkerform.php');

                $data = array();
        $data['id'] = $this->get_course_module()->id;
        $data['selectedusers'] = $selectedusers;
        $data['allocatedmarker'] = $markerid;
        $data['action'] = 'setbatchmarkingallocation';
        mod_assign_batch_set_allocatedmarker_form::mock_submit($data);

        return parent::process_set_batch_marking_allocation();
    }

    public function testable_process_set_batch_marking_workflow_state($selectedusers, $state) {
        global $CFG;
        require_once($CFG->dirroot . '/mod/assign/batchsetmarkingworkflowstateform.php');

                $data = array();
        $data['id'] = $this->get_course_module()->id;
        $data['selectedusers'] = $selectedusers;
        $data['markingworkflowstate'] = $state;
        $data['action'] = 'setbatchmarkingworkflowstate';
        mod_assign_batch_set_marking_workflow_state_form::mock_submit($data);

        return parent::process_set_batch_marking_workflow_state();
    }

    public function testable_submissions_open($userid = 0) {
        return parent::submissions_open($userid);
    }

    public function testable_save_user_extension($userid, $extensionduedate) {
        return parent::save_user_extension($userid, $extensionduedate);
    }

    public function testable_get_graders($userid) {
                return parent::get_graders($userid);
    }

    public function testable_get_notifiable_users($userid) {
        return parent::get_notifiable_users($userid);
    }

    public function testable_view_batch_set_workflow_state($selectedusers) {
        global $PAGE;
        $PAGE->set_url('/mod/assign/view.php');
        $mform = $this->testable_grading_batch_operations_form('setmarkingworkflowstate', $selectedusers);
        return parent::view_batch_set_workflow_state($mform);
    }

    public function testable_view_batch_markingallocation($selectedusers) {
        global $PAGE;
        $PAGE->set_url('/mod/assign/view.php');
        $mform = $this->testable_grading_batch_operations_form('setmarkingallocation', $selectedusers);
        return parent::view_batch_markingallocation($mform);
    }

    public function testable_grading_batch_operations_form($operation, $selectedusers) {
        global $CFG;

        require_once($CFG->dirroot . '/mod/assign/gradingbatchoperationsform.php');

                $data = array();
        $data['id'] = $this->get_course_module()->id;
        $data['selectedusers'] = $selectedusers;
        $data['returnaction'] = 'grading';
        $data['operation'] = $operation;
        mod_assign_grading_batch_operations_form::mock_submit($data);

                $formparams = array();
        $formparams['submissiondrafts'] = 1;
        $formparams['duedate'] = 1;
        $formparams['attemptreopenmethod'] = ASSIGN_ATTEMPT_REOPEN_METHOD_MANUAL;
        $formparams['feedbackplugins'] = array();
        $formparams['markingworkflow'] = 1;
        $formparams['markingallocation'] = 1;
        $formparams['cm'] = $this->get_course_module()->id;
        $formparams['context'] = $this->get_context();
        $mform = new mod_assign_grading_batch_operations_form(null, $formparams);

        return $mform;
    }

    public function testable_update_activity_completion_records($teamsubmission,
                                                          $requireallteammemberssubmit,
                                                          $submission,
                                                          $userid,
                                                          $complete,
                                                          $completion) {
        return parent::update_activity_completion_records($teamsubmission,
                                                          $requireallteammemberssubmit,
                                                          $submission,
                                                          $userid,
                                                          $complete,
                                                          $completion);
    }
}
