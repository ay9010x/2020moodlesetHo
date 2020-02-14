<?php




defined('MOODLE_INTERNAL') || die();

require_once($CFG->dirroot . '/mod/workshop/backup/moodle2/restore_workshop_stepslib.php'); 

class restore_workshop_activity_task extends restore_activity_task {

    
    protected function define_my_settings() {
            }

    
    protected function define_my_steps() {
                $this->add_step(new restore_workshop_activity_structure_step('workshop_structure', 'workshop.xml'));
    }

    
    static public function define_decode_contents() {
        $contents = array();

        $contents[] = new restore_decode_content('workshop',
                          array('intro', 'instructauthors', 'instructreviewers', 'conclusion'), 'workshop');
        $contents[] = new restore_decode_content('workshop_submissions',
                          array('content', 'feedbackauthor'), 'workshop_submission');
        $contents[] = new restore_decode_content('workshop_assessments',
                          array('feedbackauthor', 'feedbackreviewer'), 'workshop_assessment');

        return $contents;
    }

    
    static public function define_decode_rules() {
        $rules = array();

        $rules[] = new restore_decode_rule('WORKSHOPVIEWBYID', '/mod/workshop/view.php?id=$1', 'course_module');
        $rules[] = new restore_decode_rule('WORKSHOPINDEX', '/mod/workshop/index.php?id=$1', 'course');

        return $rules;

    }

    
    static public function define_restore_log_rules() {
        $rules = array();

        $rules[] = new restore_log_rule('workshop', 'add', 'view.php?id={course_module}', '{workshop}');
        $rules[] = new restore_log_rule('workshop', 'update', 'view.php?id={course_module}', '{workshop}');
        $rules[] = new restore_log_rule('workshop', 'view', 'view.php?id={course_module}', '{workshop}');

        $rules[] = new restore_log_rule('workshop', 'add assessment',
                       'assessment.php?asid={workshop_assessment}', '{workshop_submission}');
        $rules[] = new restore_log_rule('workshop', 'update assessment',
                       'assessment.php?asid={workshop_assessment}', '{workshop_submission}');

        $rules[] = new restore_log_rule('workshop', 'add reference assessment',
                       'exassessment.php?asid={workshop_referenceassessment}', '{workshop_examplesubmission}');
        $rules[] = new restore_log_rule('workshop', 'update reference assessment',
                       'exassessment.php?asid={workshop_referenceassessment}', '{workshop_examplesubmission}');

        $rules[] = new restore_log_rule('workshop', 'add example assessment',
                       'exassessment.php?asid={workshop_exampleassessment}', '{workshop_examplesubmission}');
        $rules[] = new restore_log_rule('workshop', 'update example assessment',
                       'exassessment.php?asid={workshop_exampleassessment}', '{workshop_examplesubmission}');

        $rules[] = new restore_log_rule('workshop', 'view submission',
                       'submission.php?cmid={course_module}&id={workshop_submission}', '{workshop_submission}');
        $rules[] = new restore_log_rule('workshop', 'add submission',
                       'submission.php?cmid={course_module}&id={workshop_submission}', '{workshop_submission}');
        $rules[] = new restore_log_rule('workshop', 'update submission',
                       'submission.php?cmid={course_module}&id={workshop_submission}', '{workshop_submission}');

        $rules[] = new restore_log_rule('workshop', 'view example',
                       'exsubmission.php?cmid={course_module}&id={workshop_examplesubmission}', '{workshop_examplesubmission}');
        $rules[] = new restore_log_rule('workshop', 'add example',
                       'exsubmission.php?cmid={course_module}&id={workshop_examplesubmission}', '{workshop_examplesubmission}');
        $rules[] = new restore_log_rule('workshop', 'update example',
                       'exsubmission.php?cmid={course_module}&id={workshop_examplesubmission}', '{workshop_examplesubmission}');

        $rules[] = new restore_log_rule('workshop', 'update aggregate grades', 'view.php?id={course_module}', '{workshop}');
        $rules[] = new restore_log_rule('workshop', 'update switch phase', 'view.php?id={course_module}', '[phase]');
        $rules[] = new restore_log_rule('workshop', 'update clear aggregated grades', 'view.php?id={course_module}', '{workshop}');
        $rules[] = new restore_log_rule('workshop', 'update clear assessments', 'view.php?id={course_module}', '{workshop}');

        return $rules;
    }

    
    static public function define_restore_log_rules_for_course() {
        $rules = array();

        $rules[] = new restore_log_rule('workshop', 'view all', 'index.php?id={course}', null);

        return $rules;
    }
}
