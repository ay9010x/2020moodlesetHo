<?php







class backup_assignment_activity_structure_step extends backup_activity_structure_step {

    protected function define_structure() {

                $userinfo = $this->get_setting_value('userinfo');

                $assignment = new backup_nested_element('assignment', array('id'), array(
            'name', 'intro', 'introformat', 'assignmenttype',
            'resubmit', 'preventlate', 'emailteachers', 'var1',
            'var2', 'var3', 'var4', 'var5',
            'maxbytes', 'timedue', 'timeavailable', 'grade',
            'timemodified'));

        $submissions = new backup_nested_element('submissions');

        $submission = new backup_nested_element('submission', array('id'), array(
            'userid', 'timecreated', 'timemodified', 'numfiles',
            'data1', 'data2', 'grade', 'submissioncomment',
            'format', 'teacher', 'timemarked', 'mailed'));

        
                        $this->add_subplugin_structure('assignment', $assignment, false);

        $assignment->add_child($submissions);
        $submissions->add_child($submission);

                $this->add_subplugin_structure('assignment', $submission, false);

                $assignment->set_source_table('assignment', array('id' => backup::VAR_ACTIVITYID));

                if ($userinfo) {
            $submission->set_source_table('assignment_submissions', array('assignment' => backup::VAR_PARENTID));
        }

                $assignment->annotate_ids('scale', 'grade');
        $submission->annotate_ids('user', 'userid');
        $submission->annotate_ids('user', 'teacher');

                $assignment->annotate_files('mod_assignment', 'intro', null);         $submission->annotate_files('mod_assignment', 'submission', 'id');
        $submission->annotate_files('mod_assignment', 'response', 'id');

                return $this->prepare_activity_structure($assignment);
    }
}
