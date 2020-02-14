<?php




defined('MOODLE_INTERNAL') || die();

require_once($CFG->dirroot . '/mod/quiz/locallib.php');



class mod_quiz_overdue_attempt_updater {

    
    public function update_overdue_attempts($timenow, $processto) {
        global $DB;

        $attemptstoprocess = $this->get_list_of_overdue_attempts($processto);

        $course = null;
        $quiz = null;
        $cm = null;

        $count = 0;
        $quizcount = 0;
        foreach ($attemptstoprocess as $attempt) {
            try {

                                if (!$quiz || $attempt->quiz != $quiz->id) {
                    $quiz = $DB->get_record('quiz', array('id' => $attempt->quiz), '*', MUST_EXIST);
                    $cm = get_coursemodule_from_instance('quiz', $attempt->quiz);
                    $quizcount += 1;
                }

                                if (!$course || $course->id != $quiz->course) {
                    $course = $DB->get_record('course', array('id' => $quiz->course), '*', MUST_EXIST);
                }

                                $quizforuser = clone($quiz);
                $quizforuser->timeclose = $attempt->usertimeclose;
                $quizforuser->timelimit = $attempt->usertimelimit;

                                $attemptobj = new quiz_attempt($attempt, $quizforuser, $cm, $course);
                $attemptobj->handle_if_time_expired($timenow, false);
                $count += 1;

            } catch (moodle_exception $e) {
                                mtrace("Error while processing attempt {$attempt->id} at {$attempt->quiz} quiz:");
                mtrace($e->getMessage());
                mtrace($e->getTraceAsString());
                                                $DB->force_transaction_rollback();
            }
        }

        $attemptstoprocess->close();
        return array($count, $quizcount);
    }

    
    public function get_list_of_overdue_attempts($processto) {
        global $DB;


                $quizausersql = quiz_get_attempt_usertime_sql(
                "iquiza.state IN ('inprogress', 'overdue') AND iquiza.timecheckstate <= :iprocessto");

                return $DB->get_recordset_sql("
         SELECT quiza.*,
                quizauser.usertimeclose,
                quizauser.usertimelimit

           FROM {quiz_attempts} quiza
           JOIN {quiz} quiz ON quiz.id = quiza.quiz
           JOIN ( $quizausersql ) quizauser ON quizauser.id = quiza.id

          WHERE quiza.state IN ('inprogress', 'overdue')
            AND quiza.timecheckstate <= :processto
       ORDER BY quiz.course, quiza.quiz",

                array('processto' => $processto, 'iprocessto' => $processto));
    }
}
