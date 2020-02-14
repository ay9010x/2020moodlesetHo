<?php




defined('MOODLE_INTERNAL') || die();



class backup_quiz_activity_structure_step extends backup_questions_activity_structure_step {

    protected function define_structure() {

                $userinfo = $this->get_setting_value('userinfo');

                $quiz = new backup_nested_element('quiz', array('id'), array(
            'name', 'intro', 'introformat', 'timeopen', 'timeclose', 'timelimit',
            'overduehandling', 'graceperiod', 'preferredbehaviour', 'canredoquestions', 'attempts_number',
            'attemptonlast', 'grademethod', 'decimalpoints', 'questiondecimalpoints',
            'reviewattempt', 'reviewcorrectness', 'reviewmarks',
            'reviewspecificfeedback', 'reviewgeneralfeedback',
            'reviewrightanswer', 'reviewoverallfeedback',
            'questionsperpage', 'navmethod', 'shuffleanswers',
            'sumgrades', 'grade', 'timecreated',
            'timemodified', 'password', 'subnet', 'browsersecurity',
            'delay1', 'delay2', 'showuserpicture', 'showblocks', 'completionattemptsexhausted', 'completionpass'));

                $this->add_subplugin_structure('quizaccess', $quiz, true);

        $qinstances = new backup_nested_element('question_instances');

        $qinstance = new backup_nested_element('question_instance', array('id'), array(
            'slot', 'page', 'requireprevious', 'questionid', 'maxmark'));

        $sections = new backup_nested_element('sections');

        $section = new backup_nested_element('section', array('id'), array(
            'firstslot', 'heading', 'shufflequestions'));

        $feedbacks = new backup_nested_element('feedbacks');

        $feedback = new backup_nested_element('feedback', array('id'), array(
            'feedbacktext', 'feedbacktextformat', 'mingrade', 'maxgrade'));

        $overrides = new backup_nested_element('overrides');

        $override = new backup_nested_element('override', array('id'), array(
            'userid', 'groupid', 'timeopen', 'timeclose',
            'timelimit', 'attempts', 'password'));

        $grades = new backup_nested_element('grades');

        $grade = new backup_nested_element('grade', array('id'), array(
            'userid', 'gradeval', 'timemodified'));

        $attempts = new backup_nested_element('attempts');

        $attempt = new backup_nested_element('attempt', array('id'), array(
            'userid', 'attemptnum', 'uniqueid', 'layout', 'currentpage', 'preview',
            'state', 'timestart', 'timefinish', 'timemodified', 'timecheckstate', 'sumgrades'));

                        $this->add_question_usages($attempt, 'uniqueid');

                $this->add_subplugin_structure('quizaccess', $attempt, true);

                $quiz->add_child($qinstances);
        $qinstances->add_child($qinstance);

        $quiz->add_child($sections);
        $sections->add_child($section);

        $quiz->add_child($feedbacks);
        $feedbacks->add_child($feedback);

        $quiz->add_child($overrides);
        $overrides->add_child($override);

        $quiz->add_child($grades);
        $grades->add_child($grade);

        $quiz->add_child($attempts);
        $attempts->add_child($attempt);

                $quiz->set_source_table('quiz', array('id' => backup::VAR_ACTIVITYID));

        $qinstance->set_source_table('quiz_slots',
                array('quizid' => backup::VAR_PARENTID));

        $section->set_source_table('quiz_sections',
                array('quizid' => backup::VAR_PARENTID));

        $feedback->set_source_table('quiz_feedback',
                array('quizid' => backup::VAR_PARENTID));

                $overrideparams = array('quiz' => backup::VAR_PARENTID);
        if (!$userinfo) {             $overrideparams['userid'] = backup_helper::is_sqlparam(null);

        }
        $override->set_source_table('quiz_overrides', $overrideparams);

                if ($userinfo) {
            $grade->set_source_table('quiz_grades', array('quiz' => backup::VAR_PARENTID));
            $attempt->set_source_sql('
                    SELECT *
                    FROM {quiz_attempts}
                    WHERE quiz = :quiz AND preview = 0',
                    array('quiz' => backup::VAR_PARENTID));
        }

                $quiz->set_source_alias('attempts', 'attempts_number');
        $grade->set_source_alias('grade', 'gradeval');
        $attempt->set_source_alias('attempt', 'attemptnum');

                $qinstance->annotate_ids('question', 'questionid');
        $override->annotate_ids('user', 'userid');
        $override->annotate_ids('group', 'groupid');
        $grade->annotate_ids('user', 'userid');
        $attempt->annotate_ids('user', 'userid');

                $quiz->annotate_files('mod_quiz', 'intro', null);         $feedback->annotate_files('mod_quiz', 'feedback', 'id');

                return $this->prepare_activity_structure($quiz);
    }
}
