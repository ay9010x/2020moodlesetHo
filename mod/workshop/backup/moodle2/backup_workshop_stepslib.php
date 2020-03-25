<?php




defined('MOODLE_INTERNAL') || die();


class backup_workshop_activity_structure_step extends backup_activity_structure_step {

    
    protected function define_structure() {

                $userinfo = $this->get_setting_value('userinfo');

                        
                $workshop = new backup_nested_element('workshop', array('id'), array(
            'name', 'intro', 'introformat', 'instructauthors',
            'instructauthorsformat', 'instructreviewers',
            'instructreviewersformat', 'timemodified', 'phase', 'useexamples',
            'usepeerassessment', 'useselfassessment', 'grade', 'gradinggrade',
            'strategy', 'evaluation', 'gradedecimals', 'nattachments', 'submissionfiletypes',
            'latesubmissions', 'maxbytes', 'examplesmode', 'submissionstart',
            'submissionend', 'assessmentstart', 'assessmentend',
            'conclusion', 'conclusionformat', 'overallfeedbackmode',
            'overallfeedbackfiles', 'overallfeedbackfiletypes', 'overallfeedbackmaxbytes'));

                $this->add_subplugin_structure('workshopform', $workshop, true);

                $this->add_subplugin_structure('workshopeval', $workshop, true);

                $examplesubmissions = new backup_nested_element('examplesubmissions');
        $examplesubmission  = new backup_nested_element('examplesubmission', array('id'), array(
            'timecreated', 'timemodified', 'title', 'content', 'contentformat',
            'contenttrust', 'attachment'));

                $referenceassessment  = new backup_nested_element('referenceassessment', array('id'), array(
            'timecreated', 'timemodified', 'grade', 'feedbackauthor', 'feedbackauthorformat',
            'feedbackauthorattachment'));

                $this->add_subplugin_structure('workshopform', $referenceassessment, true);

                        
                $exampleassessments = new backup_nested_element('exampleassessments');
        $exampleassessment  = new backup_nested_element('exampleassessment', array('id'), array(
            'reviewerid', 'weight', 'timecreated', 'timemodified', 'grade',
            'gradinggrade', 'gradinggradeover', 'gradinggradeoverby',
            'feedbackauthor', 'feedbackauthorformat', 'feedbackauthorattachment',
            'feedbackreviewer', 'feedbackreviewerformat'));

                $this->add_subplugin_structure('workshopform', $exampleassessment, true);

                $submissions = new backup_nested_element('submissions');
        $submission  = new backup_nested_element('submission', array('id'), array(
            'authorid', 'timecreated', 'timemodified', 'title', 'content',
            'contentformat', 'contenttrust', 'attachment', 'grade',
            'gradeover', 'gradeoverby', 'feedbackauthor',
            'feedbackauthorformat', 'timegraded', 'published', 'late'));

                $assessments = new backup_nested_element('assessments');
        $assessment  = new backup_nested_element('assessment', array('id'), array(
            'reviewerid', 'weight', 'timecreated', 'timemodified', 'grade',
            'gradinggrade', 'gradinggradeover', 'gradinggradeoverby',
            'feedbackauthor', 'feedbackauthorformat', 'feedbackauthorattachment',
            'feedbackreviewer', 'feedbackreviewerformat'));

                $this->add_subplugin_structure('workshopform', $assessment, true);

                $aggregations = new backup_nested_element('aggregations');
        $aggregation = new backup_nested_element('aggregation', array('id'), array(
            'userid', 'gradinggrade', 'timegraded'));

                                $workshop->add_child($examplesubmissions);
        $examplesubmissions->add_child($examplesubmission);

        $examplesubmission->add_child($referenceassessment);

        $examplesubmission->add_child($exampleassessments);
        $exampleassessments->add_child($exampleassessment);

        $workshop->add_child($submissions);
        $submissions->add_child($submission);

        $submission->add_child($assessments);
        $assessments->add_child($assessment);

        $workshop->add_child($aggregations);
        $aggregations->add_child($aggregation);

                        
        $workshop->set_source_table('workshop', array('id' => backup::VAR_ACTIVITYID));

        $examplesubmission->set_source_sql("
            SELECT *
              FROM {workshop_submissions}
             WHERE workshopid = ? AND example = 1",
            array(backup::VAR_PARENTID));

        $referenceassessment->set_source_sql("
            SELECT *
              FROM {workshop_assessments}
             WHERE weight = 1 AND submissionid = ?",
            array(backup::VAR_PARENTID));

                        
        if ($userinfo) {

            $exampleassessment->set_source_sql("
                SELECT *
                  FROM {workshop_assessments}
                 WHERE weight = 0 AND submissionid = ?",
                array(backup::VAR_PARENTID));

            $submission->set_source_sql("
                SELECT *
                  FROM {workshop_submissions}
                 WHERE workshopid = ? AND example = 0",
                 array(backup::VAR_PARENTID));  
            $assessment->set_source_table('workshop_assessments', array('submissionid' => backup::VAR_PARENTID));

            $aggregation->set_source_table('workshop_aggregations', array('workshopid' => backup::VAR_PARENTID));
        }

                        
        $exampleassessment->annotate_ids('user', 'reviewerid');
        $submission->annotate_ids('user', 'authorid');
        $submission->annotate_ids('user', 'gradeoverby');
        $assessment->annotate_ids('user', 'reviewerid');
        $assessment->annotate_ids('user', 'gradinggradeoverby');
        $aggregation->annotate_ids('user', 'userid');

                        
        $workshop->annotate_files('mod_workshop', 'intro', null);         $workshop->annotate_files('mod_workshop', 'instructauthors', null);         $workshop->annotate_files('mod_workshop', 'instructreviewers', null);         $workshop->annotate_files('mod_workshop', 'conclusion', null); 
        $examplesubmission->annotate_files('mod_workshop', 'submission_content', 'id');
        $examplesubmission->annotate_files('mod_workshop', 'submission_attachment', 'id');

        $referenceassessment->annotate_files('mod_workshop', 'overallfeedback_content', 'id');
        $referenceassessment->annotate_files('mod_workshop', 'overallfeedback_attachment', 'id');

        $exampleassessment->annotate_files('mod_workshop', 'overallfeedback_content', 'id');
        $exampleassessment->annotate_files('mod_workshop', 'overallfeedback_attachment', 'id');

        $submission->annotate_files('mod_workshop', 'submission_content', 'id');
        $submission->annotate_files('mod_workshop', 'submission_attachment', 'id');

        $assessment->annotate_files('mod_workshop', 'overallfeedback_content', 'id');
        $assessment->annotate_files('mod_workshop', 'overallfeedback_attachment', 'id');

                return $this->prepare_activity_structure($workshop);
    }
}
