<?php




defined('MOODLE_INTERNAL') || die();

require_once($CFG->dirroot . '/mod/quiz/backup/moodle2/restore_quiz_stepslib.php');



class restore_quiz_activity_task extends restore_activity_task {

    
    protected function define_my_settings() {
            }

    
    protected function define_my_steps() {
                $this->add_step(new restore_quiz_activity_structure_step('quiz_structure', 'quiz.xml'));
    }

    
    public static function define_decode_contents() {
        $contents = array();

        $contents[] = new restore_decode_content('quiz', array('intro'), 'quiz');
        $contents[] = new restore_decode_content('quiz_feedback',
                array('feedbacktext'), 'quiz_feedback');

        return $contents;
    }

    
    public static function define_decode_rules() {
        $rules = array();

        $rules[] = new restore_decode_rule('QUIZVIEWBYID',
                '/mod/quiz/view.php?id=$1', 'course_module');
        $rules[] = new restore_decode_rule('QUIZVIEWBYQ',
                '/mod/quiz/view.php?q=$1', 'quiz');
        $rules[] = new restore_decode_rule('QUIZINDEX',
                '/mod/quiz/index.php?id=$1', 'course');

        return $rules;

    }

    
    public static function define_restore_log_rules() {
        $rules = array();

        $rules[] = new restore_log_rule('quiz', 'add',
                'view.php?id={course_module}', '{quiz}');
        $rules[] = new restore_log_rule('quiz', 'update',
                'view.php?id={course_module}', '{quiz}');
        $rules[] = new restore_log_rule('quiz', 'view',
                'view.php?id={course_module}', '{quiz}');
        $rules[] = new restore_log_rule('quiz', 'preview',
                'view.php?id={course_module}', '{quiz}');
        $rules[] = new restore_log_rule('quiz', 'report',
                'report.php?id={course_module}', '{quiz}');
        $rules[] = new restore_log_rule('quiz', 'editquestions',
                'view.php?id={course_module}', '{quiz}');
        $rules[] = new restore_log_rule('quiz', 'delete attempt',
                'report.php?id={course_module}', '[oldattempt]');
        $rules[] = new restore_log_rule('quiz', 'edit override',
                'overrideedit.php?id={quiz_override}', '{quiz}');
        $rules[] = new restore_log_rule('quiz', 'delete override',
                'overrides.php.php?cmid={course_module}', '{quiz}');
        $rules[] = new restore_log_rule('quiz', 'addcategory',
                'view.php?id={course_module}', '{question_category}');
        $rules[] = new restore_log_rule('quiz', 'view summary',
                'summary.php?attempt={quiz_attempt}', '{quiz}');
        $rules[] = new restore_log_rule('quiz', 'manualgrade',
                'comment.php?attempt={quiz_attempt}&question={question}', '{quiz}');
        $rules[] = new restore_log_rule('quiz', 'manualgrading',
                'report.php?mode=grading&q={quiz}', '{quiz}');
                                                $rules[] = new restore_log_rule('quiz', 'attempt',
                'review.php?id={course_module}&attempt={quiz_attempt}', '{quiz}',
                null, null, 'review.php?attempt={quiz_attempt}');
        $rules[] = new restore_log_rule('quiz', 'attempt',
                'review.php?attempt={quiz_attempt}', '{quiz}',
                null, null, 'review.php?attempt={quiz_attempt}');
                $rules[] = new restore_log_rule('quiz', 'submit',
                'review.php?id={course_module}&attempt={quiz_attempt}', '{quiz}',
                null, null, 'review.php?attempt={quiz_attempt}');
        $rules[] = new restore_log_rule('quiz', 'submit',
                'review.php?attempt={quiz_attempt}', '{quiz}');
                $rules[] = new restore_log_rule('quiz', 'review',
                'review.php?id={course_module}&attempt={quiz_attempt}', '{quiz}',
                null, null, 'review.php?attempt={quiz_attempt}');
        $rules[] = new restore_log_rule('quiz', 'review',
                'review.php?attempt={quiz_attempt}', '{quiz}');
                $rules[] = new restore_log_rule('quiz', 'start attempt',
                'review.php?id={course_module}&attempt={quiz_attempt}', '{quiz}',
                null, null, 'review.php?attempt={quiz_attempt}');
        $rules[] = new restore_log_rule('quiz', 'start attempt',
                'review.php?attempt={quiz_attempt}', '{quiz}');
                $rules[] = new restore_log_rule('quiz', 'close attempt',
                'review.php?id={course_module}&attempt={quiz_attempt}', '{quiz}',
                null, null, 'review.php?attempt={quiz_attempt}');
        $rules[] = new restore_log_rule('quiz', 'close attempt',
                'review.php?attempt={quiz_attempt}', '{quiz}');
                $rules[] = new restore_log_rule('quiz', 'continue attempt',
                'review.php?id={course_module}&attempt={quiz_attempt}', '{quiz}',
                null, null, 'review.php?attempt={quiz_attempt}');
        $rules[] = new restore_log_rule('quiz', 'continue attempt',
                'review.php?attempt={quiz_attempt}', '{quiz}');
                $rules[] = new restore_log_rule('quiz', 'continue attemp',
                'review.php?id={course_module}&attempt={quiz_attempt}', '{quiz}',
                null, 'continue attempt', 'review.php?attempt={quiz_attempt}');
        $rules[] = new restore_log_rule('quiz', 'continue attemp',
                'review.php?attempt={quiz_attempt}', '{quiz}',
                null, 'continue attempt');

        return $rules;
    }

    
    public static function define_restore_log_rules_for_course() {
        $rules = array();

        $rules[] = new restore_log_rule('quiz', 'view all', 'index.php?id={course}', null);

        return $rules;
    }
}
