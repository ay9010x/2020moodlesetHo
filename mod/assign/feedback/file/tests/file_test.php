<?php



defined('MOODLE_INTERNAL') || die();

global $CFG;
require_once($CFG->dirroot . '/mod/assign/tests/base_test.php');


class assignfeedback_file_testcase extends mod_assign_base_testcase {

    
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

        $fs = get_file_storage();
        $context = context_user::instance($this->teachers[0]->id);
        $draftitemid = file_get_unused_draft_itemid();
        file_prepare_draft_area($draftitemid, $context->id, 'assignfeedback_file', 'feedback_files', 1);

        $dummy = array(
            'contextid' => $context->id,
            'component' => 'user',
            'filearea' => 'draft',
            'itemid' => $draftitemid,
            'filepath' => '/',
            'filename' => 'feedback1.txt'
        );

        $file = $fs->create_file_from_string($dummy, 'This is the first feedback file');

                $data = new stdClass();
        $data->{'files_' . $this->students[0]->id . '_filemanager'} = $draftitemid;

        $grade = $assign->get_user_grade($this->students[0]->id, true);

                $plugin = $assign->get_feedback_plugin_by_type('file');
        $this->assertTrue($plugin->is_feedback_modified($grade, $data));
                $plugin->save($grade, $data);
                $draftitemid = file_get_unused_draft_itemid();
        file_prepare_draft_area($draftitemid, $context->id, 'assignfeedback_file', 'feedback_files', 1);

        $dummy['itemid'] = $draftitemid;

        $file = $fs->create_file_from_string($dummy, 'This is the first feedback file');

                $data = new stdClass();
        $data->{'files_' . $this->students[0]->id . '_filemanager'} = $draftitemid;

        $this->assertFalse($plugin->is_feedback_modified($grade, $data));

                $draftitemid = file_get_unused_draft_itemid();
        file_prepare_draft_area($draftitemid, $context->id, 'assignfeedback_file', 'feedback_files', 1);

        $dummy['itemid'] = $draftitemid;

        $file = $fs->create_file_from_string($dummy, 'This is different feedback');

                $data = new stdClass();
        $data->{'files_' . $this->students[0]->id . '_filemanager'} = $draftitemid;

        $this->assertTrue($plugin->is_feedback_modified($grade, $data));
        $plugin->save($grade, $data);

                $draftitemid = file_get_unused_draft_itemid();
        file_prepare_draft_area($draftitemid, $context->id, 'assignfeedback_file', 'feedback_files', 1);

        $dummy['itemid'] = $draftitemid;

        $file = $fs->create_file_from_string($dummy, 'This is different feedback');
        $dummy['filename'] = 'feedback2.txt';
        $file = $fs->create_file_from_string($dummy, 'A second feedback file');

                $data = new stdClass();
        $data->{'files_' . $this->students[0]->id . '_filemanager'} = $draftitemid;

        $this->assertTrue($plugin->is_feedback_modified($grade, $data));
        $plugin->save($grade, $data);

                $draftitemid = file_get_unused_draft_itemid();
        file_prepare_draft_area($draftitemid, $context->id, 'assignfeedback_file', 'feedback_files', 1);

        $dummy['itemid'] = $draftitemid;

        $file = $fs->create_file_from_string($dummy, 'This is different feedback');

                $data = new stdClass();
        $data->{'files_' . $this->students[0]->id . '_filemanager'} = $draftitemid;

        $this->assertTrue($plugin->is_feedback_modified($grade, $data));
        $plugin->save($grade, $data);

                $draftitemid = file_get_unused_draft_itemid();
        file_prepare_draft_area($draftitemid, $context->id, 'assignfeedback_file', 'feedback_files', 1);

        $dummy['itemid'] = $draftitemid;
        $dummy['filepath'] = '/testdir/';

        $file = $fs->create_file_from_string($dummy, 'This is different feedback');

                $data = new stdClass();
        $data->{'files_' . $this->students[0]->id . '_filemanager'} = $draftitemid;

        $this->assertTrue($plugin->is_feedback_modified($grade, $data));
        $plugin->save($grade, $data);

                $draftitemid = file_get_unused_draft_itemid();
        file_prepare_draft_area($draftitemid, $context->id, 'assignfeedback_file', 'feedback_files', 1);

        $dummy['itemid'] = $draftitemid;
        $dummy['filepath'] = '/testdir/';

        $file = $fs->create_file_from_string($dummy, 'This is different feedback');

                $data = new stdClass();
        $data->{'files_' . $this->students[0]->id . '_filemanager'} = $draftitemid;

        $this->assertFalse($plugin->is_feedback_modified($grade, $data));
    }
}
