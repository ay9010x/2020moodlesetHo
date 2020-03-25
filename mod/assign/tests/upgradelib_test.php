<?php




defined('MOODLE_INTERNAL') || die();

global $CFG;
require_once($CFG->dirroot . '/mod/assign/locallib.php');
require_once($CFG->dirroot . '/mod/assign/upgradelib.php');
require_once($CFG->dirroot . '/mod/assignment/lib.php');
require_once($CFG->dirroot . '/mod/assign/tests/base_test.php');


class mod_assign_upgradelib_testcase extends mod_assign_base_testcase {

    public function test_upgrade_upload_assignment() {
        global $DB, $CFG;

        $commentconfig = false;
        if (!empty($CFG->usecomments)) {
            $commentconfig = $CFG->usecomments;
        }
        $CFG->usecomments = false;

        $this->setUser($this->editingteachers[0]);
        $generator = $this->getDataGenerator()->get_plugin_generator('mod_assignment');
        $params = array('course'=>$this->course->id,
                        'assignmenttype'=>'upload');
        $assignment = $generator->create_instance($params);

        $this->setAdminUser();
        $log = '';
        $upgrader = new assign_upgrade_manager();

        $this->assertTrue($upgrader->upgrade_assignment($assignment->id, $log));
        $record = $DB->get_record('assign', array('course'=>$this->course->id));

        $cm = get_coursemodule_from_instance('assign', $record->id);
        $context = context_module::instance($cm->id);

        $assign = new assign($context, $cm, $this->course);

        $plugin = $assign->get_submission_plugin_by_type('onlinetext');
        $this->assertEmpty($plugin->is_enabled());
        $plugin = $assign->get_submission_plugin_by_type('comments');
        $this->assertEmpty($plugin->is_enabled());
        $plugin = $assign->get_submission_plugin_by_type('file');
        $this->assertNotEmpty($plugin->is_enabled());
        $plugin = $assign->get_feedback_plugin_by_type('comments');
        $this->assertNotEmpty($plugin->is_enabled());
        $plugin = $assign->get_feedback_plugin_by_type('file');
        $this->assertNotEmpty($plugin->is_enabled());
        $plugin = $assign->get_feedback_plugin_by_type('offline');
        $this->assertEmpty($plugin->is_enabled());

        $CFG->usecomments = $commentconfig;
        course_delete_module($cm->id);
    }

    public function test_upgrade_uploadsingle_assignment() {
        global $DB, $CFG;

        $commentconfig = false;
        if (!empty($CFG->usecomments)) {
            $commentconfig = $CFG->usecomments;
        }
        $CFG->usecomments = false;

        $this->setUser($this->editingteachers[0]);
        $generator = $this->getDataGenerator()->get_plugin_generator('mod_assignment');
        $params = array('course'=>$this->course->id,
                        'assignmenttype'=>'uploadsingle');
        $assignment = $generator->create_instance($params);

        $this->setAdminUser();
        $log = '';
        $upgrader = new assign_upgrade_manager();

        $this->assertTrue($upgrader->upgrade_assignment($assignment->id, $log));
        $record = $DB->get_record('assign', array('course'=>$this->course->id));

        $cm = get_coursemodule_from_instance('assign', $record->id);
        $context = context_module::instance($cm->id);

        $assign = new assign($context, $cm, $this->course);

        $plugin = $assign->get_submission_plugin_by_type('onlinetext');
        $this->assertEmpty($plugin->is_enabled());
        $plugin = $assign->get_submission_plugin_by_type('comments');
        $this->assertEmpty($plugin->is_enabled());
        $plugin = $assign->get_submission_plugin_by_type('file');
        $this->assertNotEmpty($plugin->is_enabled());
        $plugin = $assign->get_feedback_plugin_by_type('comments');
        $this->assertNotEmpty($plugin->is_enabled());
        $plugin = $assign->get_feedback_plugin_by_type('file');
        $this->assertNotEmpty($plugin->is_enabled());
        $plugin = $assign->get_feedback_plugin_by_type('offline');
        $this->assertEmpty($plugin->is_enabled());

        $CFG->usecomments = $commentconfig;
        course_delete_module($cm->id);
    }

    public function test_upgrade_onlinetext_assignment() {
        global $DB, $CFG;

        $commentconfig = false;
        if (!empty($CFG->usecomments)) {
            $commentconfig = $CFG->usecomments;
        }
        $CFG->usecomments = false;

        $this->setUser($this->editingteachers[0]);
        $generator = $this->getDataGenerator()->get_plugin_generator('mod_assignment');
        $params = array('course'=>$this->course->id,
                        'assignmenttype'=>'online');
        $assignment = $generator->create_instance($params);

        $this->setAdminUser();
        $log = '';
        $upgrader = new assign_upgrade_manager();

        $this->assertTrue($upgrader->upgrade_assignment($assignment->id, $log));
        $record = $DB->get_record('assign', array('course'=>$this->course->id));

        $cm = get_coursemodule_from_instance('assign', $record->id);
        $context = context_module::instance($cm->id);

        $assign = new assign($context, $cm, $this->course);

        $plugin = $assign->get_submission_plugin_by_type('onlinetext');
        $this->assertNotEmpty($plugin->is_enabled());
        $plugin = $assign->get_submission_plugin_by_type('comments');
        $this->assertEmpty($plugin->is_enabled());
        $plugin = $assign->get_submission_plugin_by_type('file');
        $this->assertEmpty($plugin->is_enabled());
        $plugin = $assign->get_feedback_plugin_by_type('comments');
        $this->assertNotEmpty($plugin->is_enabled());
        $plugin = $assign->get_feedback_plugin_by_type('file');
        $this->assertEmpty($plugin->is_enabled());
        $plugin = $assign->get_feedback_plugin_by_type('offline');
        $this->assertEmpty($plugin->is_enabled());

        $CFG->usecomments = $commentconfig;
        course_delete_module($cm->id);
    }

    public function test_upgrade_offline_assignment() {
        global $DB, $CFG;

        $commentconfig = false;
        if (!empty($CFG->usecomments)) {
            $commentconfig = $CFG->usecomments;
        }
        $CFG->usecomments = false;

        $this->setUser($this->editingteachers[0]);
        $generator = $this->getDataGenerator()->get_plugin_generator('mod_assignment');
        $params = array('course'=>$this->course->id,
                        'assignmenttype'=>'offline');
        $assignment = $generator->create_instance($params);

        $this->setAdminUser();
        $log = '';
        $upgrader = new assign_upgrade_manager();

        $this->assertTrue($upgrader->upgrade_assignment($assignment->id, $log));
        $record = $DB->get_record('assign', array('course'=>$this->course->id));

        $cm = get_coursemodule_from_instance('assign', $record->id);
        $context = context_module::instance($cm->id);

        $assign = new assign($context, $cm, $this->course);

        $plugin = $assign->get_submission_plugin_by_type('onlinetext');
        $this->assertEmpty($plugin->is_enabled());
        $plugin = $assign->get_submission_plugin_by_type('comments');
        $this->assertEmpty($plugin->is_enabled());
        $plugin = $assign->get_submission_plugin_by_type('file');
        $this->assertEmpty($plugin->is_enabled());
        $plugin = $assign->get_feedback_plugin_by_type('comments');
        $this->assertNotEmpty($plugin->is_enabled());
        $plugin = $assign->get_feedback_plugin_by_type('file');
        $this->assertEmpty($plugin->is_enabled());
        $plugin = $assign->get_feedback_plugin_by_type('offline');
        $this->assertEmpty($plugin->is_enabled());

        $CFG->usecomments = $commentconfig;
        course_delete_module($cm->id);
    }
}
