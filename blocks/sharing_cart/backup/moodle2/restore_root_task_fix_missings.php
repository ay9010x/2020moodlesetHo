<?php


defined('MOODLE_INTERNAL') || die();

require_once __DIR__.'/restore_fix_missing_questions.php';


class restore_root_task_fix_missings extends restore_root_task
{
    public function build()
    {
        parent::build();

                        $fix_missing_questions = new restore_fix_missing_questions('fix_missing_questions');
        $fix_missing_questions->set_task($this);
        foreach ($this->steps as $i => $step) {
            if ($step instanceof restore_create_categories_and_questions) {
                array_splice($this->steps, $i, 0, array($fix_missing_questions));
                break;
            }
        }
    }
}
