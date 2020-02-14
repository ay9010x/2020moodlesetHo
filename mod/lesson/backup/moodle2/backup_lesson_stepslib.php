<?php





class backup_lesson_activity_structure_step extends backup_activity_structure_step {

    protected function define_structure() {

                                        $lesson = new backup_nested_element('lesson', array('id'), array(
            'course', 'name', 'intro', 'introformat', 'practice', 'modattempts',
            'usepassword', 'password',
            'dependency', 'conditions', 'grade', 'custom', 'ongoing', 'usemaxgrade',
            'maxanswers', 'maxattempts', 'review', 'nextpagedefault', 'feedback',
            'minquestions', 'maxpages', 'timelimit', 'retake', 'activitylink',
            'mediafile', 'mediaheight', 'mediawidth', 'mediaclose', 'slideshow',
            'width', 'height', 'bgcolor', 'displayleft', 'displayleftif', 'progressbar',
            'available', 'deadline', 'timemodified',
            'completionendreached', 'completiontimespent'
        ));

                                        $pages = new backup_nested_element('pages');
        $page = new backup_nested_element('page', array('id'), array(
            'prevpageid','nextpageid','qtype','qoption','layout',
            'display','timecreated','timemodified','title','contents',
            'contentsformat'
        ));

                                        $answers = new backup_nested_element('answers');
        $answer = new backup_nested_element('answer', array('id'), array(
            'jumpto','grade','score','flags','timecreated','timemodified','answer_text',
            'response', 'answerformat', 'responseformat'
        ));
                        $answer->set_source_alias('answer', 'answer_text');

                                $attempts = new backup_nested_element('attempts');
        $attempt = new backup_nested_element('attempt', array('id'), array(
            'userid','retry','correct','useranswer','timeseen'
        ));

                                $branches = new backup_nested_element('branches');
        $branch = new backup_nested_element('branch', array('id'), array(
             'userid', 'retry', 'flag', 'timeseen', 'nextpageid'
        ));

                        $grades = new backup_nested_element('grades');
        $grade = new backup_nested_element('grade', array('id'), array(
            'userid','grade','late','completed'
        ));

                        $timers = new backup_nested_element('timers');
        $timer = new backup_nested_element('timer', array('id'), array(
            'userid', 'starttime', 'lessontime', 'completed'
        ));

        $overrides = new backup_nested_element('overrides');
        $override = new backup_nested_element('override', array('id'), array(
            'groupid', 'userid', 'available', 'deadline', 'timelimit',
            'review', 'maxattempts', 'retake', 'password'));

                        $lesson->add_child($pages);
        $pages->add_child($page);
        $page->add_child($answers);
        $answers->add_child($answer);
        $answer->add_child($attempts);
        $attempts->add_child($attempt);
        $page->add_child($branches);
        $branches->add_child($branch);
        $lesson->add_child($grades);
        $grades->add_child($grade);
        $lesson->add_child($timers);
        $timers->add_child($timer);
        $lesson->add_child($overrides);
        $overrides->add_child($override);

                        $lesson->set_source_table('lesson', array('id' => backup::VAR_ACTIVITYID));
                $page->set_source_table('lesson_pages', array('lessonid' => backup::VAR_PARENTID), 'prevpageid ASC');

                $answer->set_source_table('lesson_answers', array('pageid' => backup::VAR_PARENTID), 'id ASC');

                $overrideparams = array('lessonid' => backup::VAR_PARENTID);

                if ($this->get_setting_value('userinfo')) {
                                    $attempt->set_source_table('lesson_attempts', array('answerid' => backup::VAR_PARENTID));
            $branch->set_source_table('lesson_branch', array('pageid' => backup::VAR_PARENTID));
            $grade->set_source_table('lesson_grades', array('lessonid'=>backup::VAR_PARENTID));
            $timer->set_source_table('lesson_timer', array('lessonid' => backup::VAR_PARENTID));
        } else {
            $overrideparams['userid'] = backup_helper::is_sqlparam(null);         }

        $override->set_source_table('lesson_overrides', $overrideparams);

                $attempt->annotate_ids('user', 'userid');
        $branch->annotate_ids('user', 'userid');
        $grade->annotate_ids('user', 'userid');
        $timer->annotate_ids('user', 'userid');
        $override->annotate_ids('user', 'userid');
        $override->annotate_ids('group', 'groupid');

                $lesson->annotate_files('mod_lesson', 'intro', null);
        $lesson->annotate_files('mod_lesson', 'mediafile', null);
        $page->annotate_files('mod_lesson', 'page_contents', 'id');
        $answer->annotate_files('mod_lesson', 'page_answers', 'id');
        $answer->annotate_files('mod_lesson', 'page_responses', 'id');
        $attempt->annotate_files('mod_lesson', 'essay_responses', 'id');

                return $this->prepare_activity_structure($lesson);
    }
}
