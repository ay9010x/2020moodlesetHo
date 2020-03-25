<?php







class restore_workshop_activity_structure_step extends restore_activity_structure_step {

    protected function define_structure() {

        $paths = array();

        $userinfo = $this->get_setting_value('userinfo'); 
                        
                $workshop = new restore_path_element('workshop', '/activity/workshop');
        $paths[] = $workshop;

                $this->add_subplugin_structure('workshopform', $workshop);

                $this->add_subplugin_structure('workshopeval', $workshop);

                $paths[] = new restore_path_element('workshop_examplesubmission',
                       '/activity/workshop/examplesubmissions/examplesubmission');

                $referenceassessment = new restore_path_element('workshop_referenceassessment',
                                   '/activity/workshop/examplesubmissions/examplesubmission/referenceassessment');
        $paths[] = $referenceassessment;

                $this->add_subplugin_structure('workshopform', $referenceassessment);

                if (!$userinfo) {
            return $this->prepare_activity_structure($paths);
        }

                        
                $exampleassessment = new restore_path_element('workshop_exampleassessment',
                                 '/activity/workshop/examplesubmissions/examplesubmission/exampleassessments/exampleassessment');
        $paths[] = $exampleassessment;

                $this->add_subplugin_structure('workshopform', $exampleassessment);

                $paths[] = new restore_path_element('workshop_submission', '/activity/workshop/submissions/submission');

                $assessment = new restore_path_element('workshop_assessment',
                          '/activity/workshop/submissions/submission/assessments/assessment');
        $paths[] = $assessment;

                $this->add_subplugin_structure('workshopform', $assessment);

                $paths[] = new restore_path_element('workshop_aggregation', '/activity/workshop/aggregations/aggregation');

                return $this->prepare_activity_structure($paths);
    }

    protected function process_workshop($data) {
        global $DB;

        $data = (object)$data;
        $oldid = $data->id;
        $data->course = $this->get_courseid();

        $data->timemodified = $this->apply_date_offset($data->timemodified);
        $data->submissionstart = $this->apply_date_offset($data->submissionstart);
        $data->submissionend = $this->apply_date_offset($data->submissionend);
        $data->assessmentstart = $this->apply_date_offset($data->assessmentstart);
        $data->assessmentend = $this->apply_date_offset($data->assessmentend);

                $newitemid = $DB->insert_record('workshop', $data);
                $this->apply_activity_instance($newitemid);
    }

    protected function process_workshop_examplesubmission($data) {
        global $DB;

        $data = (object)$data;
        $oldid = $data->id;

        $data->workshopid = $this->get_new_parentid('workshop');
        $data->example = 1;
        $data->authorid = $this->task->get_userid();
        $data->timecreated = $this->apply_date_offset($data->timecreated);
        $data->timemodified = $this->apply_date_offset($data->timemodified);

        $newitemid = $DB->insert_record('workshop_submissions', $data);
        $this->set_mapping('workshop_examplesubmission', $oldid, $newitemid, true);     }

    protected function process_workshop_referenceassessment($data) {
        global $DB;

        $data = (object)$data;
        $oldid = $data->id;

        $data->submissionid = $this->get_new_parentid('workshop_examplesubmission');
        $data->reviewerid = $this->task->get_userid();
        $data->timecreated = $this->apply_date_offset($data->timecreated);
        $data->timemodified = $this->apply_date_offset($data->timemodified);

        $newitemid = $DB->insert_record('workshop_assessments', $data);
        $this->set_mapping('workshop_referenceassessment', $oldid, $newitemid, true);     }

    protected function process_workshop_exampleassessment($data) {
        global $DB;

        $data = (object)$data;
        $oldid = $data->id;

        $data->submissionid = $this->get_new_parentid('workshop_examplesubmission');
        $data->reviewerid = $this->get_mappingid('user', $data->reviewerid);
        $data->timecreated = $this->apply_date_offset($data->timecreated);
        $data->timemodified = $this->apply_date_offset($data->timemodified);

        $newitemid = $DB->insert_record('workshop_assessments', $data);
        $this->set_mapping('workshop_exampleassessment', $oldid, $newitemid, true);     }

    protected function process_workshop_submission($data) {
        global $DB;

        $data = (object)$data;
        $oldid = $data->id;

        $data->workshopid = $this->get_new_parentid('workshop');
        $data->example = 0;
        $data->authorid = $this->get_mappingid('user', $data->authorid);
        $data->timecreated = $this->apply_date_offset($data->timecreated);
        $data->timemodified = $this->apply_date_offset($data->timemodified);

        $newitemid = $DB->insert_record('workshop_submissions', $data);
        $this->set_mapping('workshop_submission', $oldid, $newitemid, true);     }

    protected function process_workshop_assessment($data) {
        global $DB;

        $data = (object)$data;
        $oldid = $data->id;

        $data->submissionid = $this->get_new_parentid('workshop_submission');
        $data->reviewerid = $this->get_mappingid('user', $data->reviewerid);
        $data->timecreated = $this->apply_date_offset($data->timecreated);
        $data->timemodified = $this->apply_date_offset($data->timemodified);

        $newitemid = $DB->insert_record('workshop_assessments', $data);
        $this->set_mapping('workshop_assessment', $oldid, $newitemid, true);     }

    protected function process_workshop_aggregation($data) {
        global $DB;

        $data = (object)$data;
        $oldid = $data->id;

        $data->workshopid = $this->get_new_parentid('workshop');
        $data->userid = $this->get_mappingid('user', $data->userid);
        $data->timegraded = $this->apply_date_offset($data->timegraded);

        $newitemid = $DB->insert_record('workshop_aggregations', $data);
        $this->set_mapping('workshop_aggregation', $oldid, $newitemid, true);
    }

    protected function after_execute() {
                $this->add_related_files('mod_workshop', 'intro', null);
        $this->add_related_files('mod_workshop', 'instructauthors', null);
        $this->add_related_files('mod_workshop', 'instructreviewers', null);
        $this->add_related_files('mod_workshop', 'conclusion', null);

                $this->add_related_files('mod_workshop', 'submission_content', 'workshop_examplesubmission');
        $this->add_related_files('mod_workshop', 'submission_attachment', 'workshop_examplesubmission');

                $this->add_related_files('mod_workshop', 'overallfeedback_content', 'workshop_referenceassessment');
        $this->add_related_files('mod_workshop', 'overallfeedback_attachment', 'workshop_referenceassessment');

                $this->add_related_files('mod_workshop', 'overallfeedback_content', 'workshop_exampleassessment');
        $this->add_related_files('mod_workshop', 'overallfeedback_attachment', 'workshop_exampleassessment');

                $this->add_related_files('mod_workshop', 'submission_content', 'workshop_submission');
        $this->add_related_files('mod_workshop', 'submission_attachment', 'workshop_submission');

                $this->add_related_files('mod_workshop', 'overallfeedback_content', 'workshop_assessment');
        $this->add_related_files('mod_workshop', 'overallfeedback_attachment', 'workshop_assessment');
    }
}
