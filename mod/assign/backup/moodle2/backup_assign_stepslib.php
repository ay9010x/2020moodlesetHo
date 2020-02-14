<?php



defined('MOODLE_INTERNAL') || die();


class backup_assign_activity_structure_step extends backup_activity_structure_step {

    
    protected function define_structure() {

                $userinfo = $this->get_setting_value('userinfo');

                $assign = new backup_nested_element('assign', array('id'),
                                            array('name',
                                                  'intro',
                                                  'introformat',
                                                  'alwaysshowdescription',
                                                  'submissiondrafts',
                                                  'sendnotifications',
                                                  'sendlatenotifications',
                                                  'sendstudentnotifications',
                                                  'duedate',
                                                  'cutoffdate',
                                                  'allowsubmissionsfromdate',
                                                  'grade',
                                                  'timemodified',
                                                  'completionsubmit',
                                                  'requiresubmissionstatement',
                                                  'teamsubmission',
                                                  'requireallteammemberssubmit',
                                                  'teamsubmissiongroupingid',
                                                  'blindmarking',
                                                  'revealidentities',
                                                  'attemptreopenmethod',
                                                  'maxattempts',
                                                  'markingworkflow',
                                                  'markingallocation',
                                                  'preventsubmissionnotingroup'));

        $userflags = new backup_nested_element('userflags');

        $userflag = new backup_nested_element('userflag', array('id'),
                                                array('userid',
                                                      'assignment',
                                                      'mailed',
                                                      'locked',
                                                      'extensionduedate',
                                                      'workflowstate',
                                                      'allocatedmarker'));

        $submissions = new backup_nested_element('submissions');

        $submission = new backup_nested_element('submission', array('id'),
                                                array('userid',
                                                      'timecreated',
                                                      'timemodified',
                                                      'status',
                                                      'groupid',
                                                      'attemptnumber',
                                                      'latest'));

        $grades = new backup_nested_element('grades');

        $grade = new backup_nested_element('grade', array('id'),
                                           array('userid',
                                                 'timecreated',
                                                 'timemodified',
                                                 'grader',
                                                 'grade',
                                                 'attemptnumber'));

        $pluginconfigs = new backup_nested_element('plugin_configs');

        $pluginconfig = new backup_nested_element('plugin_config', array('id'),
                                                   array('plugin',
                                                         'subtype',
                                                         'name',
                                                         'value'));

                $assign->add_child($userflags);
        $userflags->add_child($userflag);
        $assign->add_child($submissions);
        $submissions->add_child($submission);
        $assign->add_child($grades);
        $grades->add_child($grade);
        $assign->add_child($pluginconfigs);
        $pluginconfigs->add_child($pluginconfig);

                $assign->set_source_table('assign', array('id' => backup::VAR_ACTIVITYID));
        $pluginconfig->set_source_table('assign_plugin_config',
                                        array('assignment' => backup::VAR_PARENTID));

        if ($userinfo) {
            $userflag->set_source_table('assign_user_flags',
                                     array('assignment' => backup::VAR_PARENTID));

            $submission->set_source_table('assign_submission',
                                     array('assignment' => backup::VAR_PARENTID));

            $grade->set_source_table('assign_grades',
                                     array('assignment' => backup::VAR_PARENTID));

                        $this->add_subplugin_structure('assignsubmission', $submission, true);
            $this->add_subplugin_structure('assignfeedback', $grade, true);
        }

                $userflag->annotate_ids('user', 'userid');
        $userflag->annotate_ids('user', 'allocatedmarker');
        $submission->annotate_ids('user', 'userid');
        $submission->annotate_ids('group', 'groupid');
        $grade->annotate_ids('user', 'userid');
        $grade->annotate_ids('user', 'grader');
        $assign->annotate_ids('grouping', 'teamsubmissiongroupingid');

                        $assign->annotate_files('mod_assign', 'intro', null);
        $assign->annotate_files('mod_assign', 'introattachment', null);

        
        return $this->prepare_activity_structure($assign);
    }
}
