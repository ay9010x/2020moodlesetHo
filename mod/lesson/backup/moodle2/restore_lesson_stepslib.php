<?php







class restore_lesson_activity_structure_step extends restore_activity_structure_step {
            protected $answers = array();

    protected function define_structure() {

        $paths = array();
        $userinfo = $this->get_setting_value('userinfo');

        $paths[] = new restore_path_element('lesson', '/activity/lesson');
        $paths[] = new restore_path_element('lesson_page', '/activity/lesson/pages/page');
        $paths[] = new restore_path_element('lesson_answer', '/activity/lesson/pages/page/answers/answer');
        $paths[] = new restore_path_element('lesson_override', '/activity/lesson/overrides/override');
        if ($userinfo) {
            $paths[] = new restore_path_element('lesson_attempt', '/activity/lesson/pages/page/answers/answer/attempts/attempt');
            $paths[] = new restore_path_element('lesson_grade', '/activity/lesson/grades/grade');
            $paths[] = new restore_path_element('lesson_branch', '/activity/lesson/pages/page/branches/branch');
            $paths[] = new restore_path_element('lesson_highscore', '/activity/lesson/highscores/highscore');
            $paths[] = new restore_path_element('lesson_timer', '/activity/lesson/timers/timer');
        }

                return $this->prepare_activity_structure($paths);
    }

    protected function process_lesson($data) {
        global $DB;

        $data = (object)$data;
        $oldid = $data->id;
        $data->course = $this->get_courseid();

        $data->available = $this->apply_date_offset($data->available);
        $data->deadline = $this->apply_date_offset($data->deadline);
        $data->timemodified = $this->apply_date_offset($data->timemodified);

                        if (isset($data->showhighscores)) {
            unset($data->showhighscores);
        }
        if (isset($data->highscores)) {
            unset($data->highscores);
        }

                if (!isset($data->completionendreached)) {
            $data->completionendreached = 0;
        }
        if (!isset($data->completiontimespent)) {
            $data->completiontimespent = 0;
        }

        if (!isset($data->intro)) {
            $data->intro = '';
            $data->introformat = FORMAT_HTML;
        }

                if (!isset($data->timelimit)) {
            if (isset($data->timed) && isset($data->maxtime) && $data->timed) {
                $data->timelimit = 60 * $data->maxtime;
            } else {
                $data->timelimit = 0;
            }
        }
                $newitemid = $DB->insert_record('lesson', $data);
                $this->apply_activity_instance($newitemid);
    }

    protected function process_lesson_page($data) {
        global $DB;

        $data = (object)$data;
        $oldid = $data->id;
        $data->lessonid = $this->get_new_parentid('lesson');

                $data->timemodified = $this->apply_date_offset($data->timemodified);
        $data->timecreated = $this->apply_date_offset($data->timecreated);

        $newitemid = $DB->insert_record('lesson_pages', $data);
        $this->set_mapping('lesson_page', $oldid, $newitemid, true);     }

    protected function process_lesson_answer($data) {
        global $DB;

        $data = (object)$data;
        $data->lessonid = $this->get_new_parentid('lesson');
        $data->pageid = $this->get_new_parentid('lesson_page');
        $data->answer = $data->answer_text;
        $data->timemodified = $this->apply_date_offset($data->timemodified);
        $data->timecreated = $this->apply_date_offset($data->timecreated);

                        $this->set_mapping('lesson_answer', $data->id, 0, true); 
                        $this->answers[$data->id] = $data;
    }

    protected function process_lesson_attempt($data) {
        global $DB;

        $data = (object)$data;
        $oldid = $data->id;
        $data->lessonid = $this->get_new_parentid('lesson');
        $data->pageid = $this->get_new_parentid('lesson_page');

                $data->answerid = $this->get_old_parentid('lesson_answer');
        $data->userid = $this->get_mappingid('user', $data->userid);
        $data->timeseen = $this->apply_date_offset($data->timeseen);

        $newitemid = $DB->insert_record('lesson_attempts', $data);
        $this->set_mapping('lesson_attempt', $oldid, $newitemid, true);     }

    protected function process_lesson_grade($data) {
        global $DB;

        $data = (object)$data;
        $oldid = $data->id;
        $data->lessonid = $this->get_new_parentid('lesson');
        $data->userid = $this->get_mappingid('user', $data->userid);
        $data->completed = $this->apply_date_offset($data->completed);

        $newitemid = $DB->insert_record('lesson_grades', $data);
        $this->set_mapping('lesson_grade', $oldid, $newitemid);
    }

    protected function process_lesson_branch($data) {
        global $DB;

        $data = (object)$data;
        $oldid = $data->id;
        $data->lessonid = $this->get_new_parentid('lesson');
        $data->pageid = $this->get_new_parentid('lesson_page');
        $data->userid = $this->get_mappingid('user', $data->userid);
        $data->timeseen = $this->apply_date_offset($data->timeseen);

        $newitemid = $DB->insert_record('lesson_branch', $data);
    }

    protected function process_lesson_highscore($data) {
                    }

    protected function process_lesson_timer($data) {
        global $DB;

        $data = (object)$data;
        $oldid = $data->id;
        $data->lessonid = $this->get_new_parentid('lesson');
        $data->userid = $this->get_mappingid('user', $data->userid);
        $data->starttime = $this->apply_date_offset($data->starttime);
        $data->lessontime = $this->apply_date_offset($data->lessontime);
                if (!isset($data->completed)) {
            $data->completed = 0;
        }
        $newitemid = $DB->insert_record('lesson_timer', $data);
    }

    
    protected function process_lesson_override($data) {
        global $DB;

        $data = (object)$data;
        $oldid = $data->id;

                $userinfo = $this->get_setting_value('userinfo');

                if (!$userinfo && !is_null($data->userid)) {
            return;
        }

        $data->lessonid = $this->get_new_parentid('lesson');

        if (!is_null($data->userid)) {
            $data->userid = $this->get_mappingid('user', $data->userid);
        }
        if (!is_null($data->groupid)) {
            $data->groupid = $this->get_mappingid('group', $data->groupid);
        }

        $data->available = $this->apply_date_offset($data->available);
        $data->deadline = $this->apply_date_offset($data->deadline);

        $newitemid = $DB->insert_record('lesson_overrides', $data);

                $this->set_mapping('lesson_override', $oldid, $newitemid);
    }

    protected function after_execute() {
        global $DB;

                ksort($this->answers);
        foreach ($this->answers as $answer) {
            $newitemid = $DB->insert_record('lesson_answers', $answer);
            $this->set_mapping('lesson_answer', $answer->id, $newitemid, true);

                        $DB->set_field('lesson_attempts', 'answerid', $newitemid, array(
                    'lessonid' => $answer->lessonid,
                    'pageid' => $answer->pageid,
                    'answerid' => $answer->id));
        }

                $this->add_related_files('mod_lesson', 'intro', null);
        $this->add_related_files('mod_lesson', 'mediafile', null);
                $this->add_related_files('mod_lesson', 'page_contents', 'lesson_page');
        $this->add_related_files('mod_lesson', 'page_answers', 'lesson_answer');
        $this->add_related_files('mod_lesson', 'page_responses', 'lesson_answer');
        $this->add_related_files('mod_lesson', 'essay_responses', 'lesson_attempt');

                $rs = $DB->get_recordset('lesson_pages', array('lessonid' => $this->task->get_activityid()),
                                 '', 'id, prevpageid, nextpageid');
        foreach ($rs as $page) {
            $page->prevpageid = (empty($page->prevpageid)) ? 0 : $this->get_mappingid('lesson_page', $page->prevpageid);
            $page->nextpageid = (empty($page->nextpageid)) ? 0 : $this->get_mappingid('lesson_page', $page->nextpageid);
            $DB->update_record('lesson_pages', $page);
        }
        $rs->close();

                $rs = $DB->get_recordset('lesson_answers', array('lessonid' => $this->task->get_activityid()),
                                 '', 'id, jumpto');
        foreach ($rs as $answer) {
            if ($answer->jumpto > 0) {
                $answer->jumpto = $this->get_mappingid('lesson_page', $answer->jumpto);
                $DB->update_record('lesson_answers', $answer);
            }
        }
        $rs->close();

                $rs = $DB->get_recordset('lesson_branch', array('lessonid' => $this->task->get_activityid()),
                                 '', 'id, nextpageid');
        foreach ($rs as $answer) {
            if ($answer->nextpageid > 0) {
                $answer->nextpageid = $this->get_mappingid('lesson_page', $answer->nextpageid);
                $DB->update_record('lesson_branch', $answer);
            }
        }
        $rs->close();

                        
        $sql = 'SELECT a.*
                  FROM {lesson_answers} a
                  JOIN {lesson_pages} p ON p.id = a.pageid
                 WHERE a.answerformat <> :format
                   AND a.lessonid = :lessonid
                   AND p.qtype IN (1, 8, 20)';
        $badanswers = $DB->get_recordset_sql($sql, array('lessonid' => $this->task->get_activityid(), 'format' => FORMAT_MOODLE));

        foreach ($badanswers as $badanswer) {
                        $badanswer->answer = strip_tags($badanswer->answer);
            $badanswer->answerformat = FORMAT_MOODLE;
            $DB->update_record('lesson_answers', $badanswer);
        }
        $badanswers->close();

                        if ($DB->get_dbfamily() === 'mysql') {
            $sql = "DELETE {lesson_branch}
                      FROM {lesson_branch}
                 LEFT JOIN {lesson_pages}
                        ON {lesson_branch}.pageid = {lesson_pages}.id
                     WHERE {lesson_pages}.id IS NULL";
        } else {
            $sql = "DELETE FROM {lesson_branch}
               WHERE NOT EXISTS (
                         SELECT 'x' FROM {lesson_pages}
                          WHERE {lesson_branch}.pageid = {lesson_pages}.id)";
        }

        $DB->execute($sql);
    }
}
