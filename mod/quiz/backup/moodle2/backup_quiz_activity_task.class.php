<?php



defined('MOODLE_INTERNAL') || die();

require_once($CFG->dirroot . '/mod/quiz/backup/moodle2/backup_quiz_stepslib.php');


class backup_quiz_activity_task extends backup_activity_task {

    
    protected function define_my_settings() {
    }

    
    protected function define_my_steps() {
                        $this->add_step(new backup_quiz_activity_structure_step('quiz_structure', 'quiz.xml'));

                        
                                $this->add_step(new backup_calculate_question_categories('activity_question_categories'));

                                $this->add_step(new backup_delete_temp_questions('clean_temp_questions'));
    }

    
    public static function encode_content_links($content) {
        global $CFG;

        $base = preg_quote($CFG->wwwroot, '/');

                $search="/(".$base."\/mod\/quiz\/index.php\?id\=)([0-9]+)/";
        $content= preg_replace($search, '$@QUIZINDEX*$2@$', $content);

                $search="/(".$base."\/mod\/quiz\/view.php\?id\=)([0-9]+)/";
        $content= preg_replace($search, '$@QUIZVIEWBYID*$2@$', $content);

                $search="/(".$base."\/mod\/quiz\/view.php\?q\=)([0-9]+)/";
        $content= preg_replace($search, '$@QUIZVIEWBYQ*$2@$', $content);

        return $content;
    }
}
