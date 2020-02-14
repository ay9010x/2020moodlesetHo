<?php



defined('MOODLE_INTERNAL') || die();

global $CFG;
require_once($CFG->dirroot . '/mod/assign/tests/base_test.php');


class assignfeedback_comments_testcase extends mod_assign_base_testcase {

    
    protected function create_assign_and_submit_text() {
        $assign = $this->create_instance(array('assignsubmission_onlinetext_enabled' => 1,
                                               'assignfeedback_comments_enabled' => 1));

        $user = $this->students[0];
        $this->setUser($user);

                $submission = $assign->get_user_submission($user->id, true);

        $data = new stdClass();
        $data->onlinetext_editor = array(
                'text' => '<p>This is some text.</p>',
                'format' => 1,
                'itemid' => file_get_unused_draft_itemid());
        $plugin = $assign->get_submission_plugin_by_type('onlinetext');
        $plugin->save($submission, $data);

        return $assign;
    }

    
    public function test_is_feedback_modified() {
        $assign = $this->create_assign_and_submit_text();

        $this->setUser($this->teachers[0]);

                $data = new stdClass();
        $data->assignfeedbackcomments_editor = array(
                'text' => '<p>first comment for this test</p>',
                'format' => 1
            );
        $grade = $assign->get_user_grade($this->students[0]->id, true);

                $plugin = $assign->get_feedback_plugin_by_type('comments');
        $this->assertTrue($plugin->is_feedback_modified($grade, $data));
                $plugin->save($grade, $data);
                $this->assertFalse($plugin->is_feedback_modified($grade, $data));
                $data->assignfeedbackcomments_editor = array(
                'text' => '<p>Altered comment for this test</p>',
                'format' => 1
            );
        $this->assertTrue($plugin->is_feedback_modified($grade, $data));
    }
}
