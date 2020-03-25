<?php




require_once('../../config.php');
require_once($CFG->dirroot.'/mod/lesson/locallib.php');
require_once($CFG->dirroot.'/mod/lesson/pagetypes/essay.php');
require_once($CFG->dirroot.'/mod/lesson/essay_form.php');
require_once($CFG->libdir.'/eventslib.php');

$id   = required_param('id', PARAM_INT);             $mode = optional_param('mode', 'display', PARAM_ALPHA);

$cm = get_coursemodule_from_id('lesson', $id, 0, false, MUST_EXIST);
$course = $DB->get_record('course', array('id' => $cm->course), '*', MUST_EXIST);
$dblesson = $DB->get_record('lesson', array('id' => $cm->instance), '*', MUST_EXIST);
$lesson = new lesson($dblesson);

require_login($course, false, $cm);
$context = context_module::instance($cm->id);
require_capability('mod/lesson:grade', $context);

$url = new moodle_url('/mod/lesson/essay.php', array('id'=>$id));
if ($mode !== 'display') {
    $url->param('mode', $mode);
}
$PAGE->set_url($url);

$currentgroup = groups_get_activity_group($cm, true);

$attempt = new stdClass();
$user = new stdClass();
$attemptid = optional_param('attemptid', 0, PARAM_INT);

$formattextdefoptions = new stdClass();
$formattextdefoptions->noclean = true;
$formattextdefoptions->para = false;
$formattextdefoptions->context = $context;

if ($attemptid > 0) {
    $attempt = $DB->get_record('lesson_attempts', array('id' => $attemptid));
    $answer = $DB->get_record('lesson_answers', array('lessonid' => $lesson->id, 'pageid' => $attempt->pageid));
    $user = $DB->get_record('user', array('id' => $attempt->userid));
        $lesson->update_effective_access($user->id);
    $scoreoptions = array();
    if ($lesson->custom) {
        $i = $answer->score;
        while ($i >= 0) {
            $scoreoptions[$i] = (string)$i;
            $i--;
        }
    } else {
        $scoreoptions[0] = get_string('nocredit', 'lesson');
        $scoreoptions[1] = get_string('credit', 'lesson');
    }
}

switch ($mode) {
    case 'grade':
                require_sesskey();

        if (empty($attempt)) {
            print_error('cannotfindattempt', 'lesson');
        }
        if (empty($user)) {
            print_error('cannotfinduser', 'lesson');
        }
        if (empty($answer)) {
            print_error('cannotfindanswer', 'lesson');
        }
        break;

    case 'update':
        require_sesskey();

        if (empty($attempt)) {
            print_error('cannotfindattempt', 'lesson');
        }
        if (empty($user)) {
            print_error('cannotfinduser', 'lesson');
        }

        $editoroptions = array('noclean' => true, 'maxfiles' => EDITOR_UNLIMITED_FILES,
                'maxbytes' => $CFG->maxbytes, 'context' => $context);
        $essayinfo = lesson_page_type_essay::extract_useranswer($attempt->useranswer);
        $essayinfo = file_prepare_standard_editor($essayinfo, 'response', $editoroptions, $context,
                'mod_lesson', 'essay_responses', $attempt->id);
        $mform = new essay_grading_form(null, array('scoreoptions' => $scoreoptions, 'user' => $user));
        $mform->set_data($essayinfo);
        if ($mform->is_cancelled()) {
            redirect("$CFG->wwwroot/mod/lesson/essay.php?id=$cm->id");
        }
        if ($form = $mform->get_data()) {
            if (!$grades = $DB->get_records('lesson_grades', array("lessonid"=>$lesson->id, "userid"=>$attempt->userid), 'completed', '*', $attempt->retry, 1)) {
                print_error('cannotfindgrade', 'lesson');
            }

            $essayinfo->graded = 1;
            $essayinfo->score = $form->score;
            $form = file_postupdate_standard_editor($form, 'response', $editoroptions, $context,
                                        'mod_lesson', 'essay_responses', $attempt->id);
            $essayinfo->response = $form->response;
            $essayinfo->responseformat = $form->responseformat;
            $essayinfo->sent = 0;
            if (!$lesson->custom && $essayinfo->score == 1) {
                $attempt->correct = 1;
            } else {
                $attempt->correct = 0;
            }

            $attempt->useranswer = serialize($essayinfo);

            $DB->update_record('lesson_attempts', $attempt);

                        $grade = current($grades);
            $gradeinfo = lesson_grade($lesson, $attempt->retry, $attempt->userid);

                        $updategrade = new stdClass();
            $updategrade->id = $grade->id;
            $updategrade->grade = $gradeinfo->grade;
            $DB->update_record('lesson_grades', $updategrade);

            $params = array(
                'context' => $context,
                'objectid' => $grade->id,
                'courseid' => $course->id,
                'relateduserid' => $attempt->userid,
                'other' => array(
                    'lessonid' => $lesson->id,
                    'attemptid' => $attemptid
                )
            );
            $event = \mod_lesson\event\essay_assessed::create($params);
            $event->add_record_snapshot('lesson', $dblesson);
            $event->trigger();

            $lesson->add_message(get_string('changessaved'), 'notifysuccess');

                        lesson_update_grades($lesson, $grade->userid);

            redirect(new moodle_url('/mod/lesson/essay.php', array('id'=>$cm->id)));
        } else {
            print_error('invalidformdata');
        }
        break;
    case 'email':
                require_sesskey();

                if ($userid = optional_param('userid', 0, PARAM_INT)) {
            $queryadd = " AND userid = ?";
            if (! $users = $DB->get_records('user', array('id' => $userid))) {
                print_error('cannotfinduser', 'lesson');
            }
        } else {
            $queryadd = '';

                        list($esql, $params) = get_enrolled_sql($context, '', $currentgroup, true);
            list($sort, $sortparams) = users_order_by_sql('u');
            $params['lessonid'] = $lesson->id;

                        if (!$users = $DB->get_records_sql("
                SELECT u.*
                  FROM {user} u
                  JOIN (
                           SELECT DISTINCT userid
                             FROM {lesson_attempts}
                            WHERE lessonid = :lessonid
                       ) ui ON u.id = ui.userid
                  JOIN ($esql) ue ON ue.id = u.id
                  ORDER BY $sort", $params)) {
                print_error('cannotfinduser', 'lesson');
            }
        }

        $pages = $lesson->load_all_pages();
        foreach ($pages as $key=>$page) {
            if ($page->qtype != LESSON_PAGE_ESSAY) {
                unset($pages[$key]);
            }
        }

                list($usql, $params) = $DB->get_in_or_equal(array_keys($pages));
        if (!empty($queryadd)) {
            $params[] = $userid;
        }
        if (!$attempts = $DB->get_records_select('lesson_attempts', "pageid $usql".$queryadd, $params)) {
            print_error('nooneansweredthisquestion', 'lesson');
        }
                list($answerUsql, $parameters) = $DB->get_in_or_equal(array_keys($pages));
        array_unshift($parameters, $lesson->id);
        if (!$answers = $DB->get_records_select('lesson_answers', "lessonid = ? AND pageid $answerUsql", $parameters, '', 'pageid, score')) {
            print_error('cannotfindanswer', 'lesson');
        }

        foreach ($attempts as $attempt) {
            $essayinfo = lesson_page_type_essay::extract_useranswer($attempt->useranswer);
            if ($essayinfo->graded && !$essayinfo->sent) {
                                $a = new stdClass;

                                $grades = $DB->get_records('lesson_grades', array("lessonid"=>$lesson->id, "userid"=>$attempt->userid), 'completed', '*', $attempt->retry, 1);
                $grade  = current($grades);
                $a->newgrade = $grade->grade;

                                if ($lesson->custom) {
                    $a->earned = $essayinfo->score;
                    $a->outof  = $answers[$attempt->pageid]->score;
                } else {
                    $a->earned = $essayinfo->score;
                    $a->outof  = 1;
                }

                                $currentpage = $lesson->load_page($attempt->pageid);
                $a->question = format_text($currentpage->contents, $currentpage->contentsformat, $formattextdefoptions);
                $a->response = format_text($essayinfo->answer, $essayinfo->answerformat,
                        array('context' => $context, 'para' => true));
                $a->comment = $essayinfo->response;
                $a->comment = file_rewrite_pluginfile_urls($a->comment, 'pluginfile.php', $context->id,
                            'mod_lesson', 'essay_responses', $attempt->id);
                $a->comment  = format_text($a->comment, $essayinfo->responseformat, $formattextdefoptions);
                $a->lesson = format_string($lesson->name, true);

                                $message  = get_string('essayemailmessage2', 'lesson', $a);
                $plaintext = format_text_email($message, FORMAT_HTML);

                                $subject = get_string('essayemailsubject', 'lesson');

                $eventdata = new stdClass();
                $eventdata->modulename       = 'lesson';
                $eventdata->userfrom         = $USER;
                $eventdata->userto           = $users[$attempt->userid];
                $eventdata->subject          = $subject;
                $eventdata->fullmessage      = $plaintext;
                $eventdata->fullmessageformat = FORMAT_PLAIN;
                $eventdata->fullmessagehtml  = $message;
                $eventdata->smallmessage     = '';

                                $eventdata->component = 'mod_lesson';
                $eventdata->name = 'graded_essay';

                message_send($eventdata);
                $essayinfo->sent = 1;
                $attempt->useranswer = serialize($essayinfo);
                $DB->update_record('lesson_attempts', $attempt);
            }
        }
        $lesson->add_message(get_string('emailsuccess', 'lesson'), 'notifysuccess');
        redirect(new moodle_url('/mod/lesson/essay.php', array('id'=>$cm->id)));
        break;
    case 'display':      default:
                $pages = $lesson->load_all_pages();
        foreach ($pages as $key=>$page) {
            if ($page->qtype != LESSON_PAGE_ESSAY) {
                unset($pages[$key]);
            }
        }
        if (count($pages) > 0) {
                        list($usql, $parameters) = $DB->get_in_or_equal(array_keys($pages), SQL_PARAMS_NAMED);
                        list($esql, $params) = get_enrolled_sql($context, '', $currentgroup, true);
            $parameters = array_merge($params, $parameters);

            $sql = "SELECT a.*
                        FROM {lesson_attempts} a
                        JOIN ($esql) ue ON a.userid = ue.id
                        WHERE pageid $usql";
            if ($essayattempts = $DB->get_records_sql($sql, $parameters)) {
                $ufields = user_picture::fields('u');
                                list($sort, $sortparams) = users_order_by_sql('u');

                $params['lessonid'] = $lesson->id;
                $sql = "SELECT DISTINCT $ufields
                        FROM {user} u
                        JOIN {lesson_attempts} a ON u.id = a.userid
                        JOIN ($esql) ue ON ue.id = a.userid
                        WHERE a.lessonid = :lessonid
                        ORDER BY $sort";
                if (!$users = $DB->get_records_sql($sql, $params)) {
                    $mode = 'none';                     if (!empty($currentgroup)) {
                        $groupname = groups_get_group_name($currentgroup);
                        $lesson->add_message(get_string('noonehasansweredgroup', 'lesson', $groupname));
                    } else {
                        $lesson->add_message(get_string('noonehasanswered', 'lesson'));
                    }
                }
            } else {
                $mode = 'none';                 if (!empty($currentgroup)) {
                    $groupname = groups_get_group_name($currentgroup);
                    $lesson->add_message(get_string('noonehasansweredgroup', 'lesson', $groupname));
                } else {
                    $lesson->add_message(get_string('noonehasanswered', 'lesson'));
                }
            }
        } else {
            $mode = 'none';             $lesson->add_message(get_string('noessayquestionsfound', 'lesson'));
        }
        break;
}

$lessonoutput = $PAGE->get_renderer('mod_lesson');
echo $lessonoutput->header($lesson, $cm, 'essay', false, null, get_string('manualgrading', 'lesson'));

switch ($mode) {
    case 'display':
        groups_print_activity_menu($cm, $url);
        
                $studentessays = array();
        foreach ($essayattempts as $essay) {
                                                $studentessays[$essay->userid][$essay->pageid][$essay->retry][] = $essay;
        }

                $table = new html_table();
        $table->head = array(get_string('name'), get_string('essays', 'lesson'), get_string('email', 'lesson'));
        $table->attributes['class'] = 'standardtable generaltable';
        $table->align = array('left', 'left', 'left');
        $table->wrap = array('nowrap', 'nowrap', '');

                foreach (array_keys($studentessays) as $userid) {
            $studentname = fullname($users[$userid], true);
            $essaylinks = array();

                        $attempts = $DB->count_records('lesson_grades', array('userid'=>$userid, 'lessonid'=>$lesson->id));

                        foreach ($studentessays[$userid] as $page => $tries) {
                $count = 0;

                                foreach($tries as $try) {
                    if ($count == $attempts) {
                        break;                      }
                    $count++;

                                        if (count($try) > $lesson->maxattempts) {
                        $essay = $try[$lesson->maxattempts-1];
                    } else {
                        $essay = end($try);
                    }

                                        $essayinfo = lesson_page_type_essay::extract_useranswer($essay->useranswer);

                                        $url = new moodle_url('/mod/lesson/essay.php', array('id'=>$cm->id,'mode'=>'grade','attemptid'=>$essay->id,'sesskey'=>sesskey()));
                    $attributes = array();
                                        if (!$essayinfo->graded) {
                        $attributes['class'] = "essayungraded";
                    } elseif (!$essayinfo->sent) {
                        $attributes['class'] = "essaygraded";
                    } else {
                        $attributes['class'] = "essaysent";
                    }
                    $essaylinks[] = html_writer::link($url, userdate($essay->timeseen, get_string('strftimedatetime')).' '.format_string($pages[$essay->pageid]->title,true), $attributes);
                }
            }
                        $url = new moodle_url('/mod/lesson/essay.php', array('id'=>$cm->id,'mode'=>'email','userid'=>$userid,'sesskey'=>sesskey()));
            $emaillink = html_writer::link($url, get_string('emailgradedessays', 'lesson'));

            $table->data[] = array($OUTPUT->user_picture($users[$userid], array('courseid'=>$course->id)).$studentname, implode("<br />", $essaylinks), $emaillink);
        }

                $url = new moodle_url('/mod/lesson/essay.php', array('id'=>$cm->id,'mode'=>'email','sesskey'=>sesskey()));
        $emailalllink = html_writer::link($url, get_string('emailallgradedessays', 'lesson'));

        $table->data[] = array(' ', ' ', $emailalllink);

        echo html_writer::table($table);
        break;
    case 'grade':
                $event = \mod_lesson\event\essay_attempt_viewed::create(array(
            'objectid' => $attempt->id,
            'relateduserid' => $attempt->userid,
            'context' => $context,
            'courseid' => $course->id,
        ));
        $event->add_record_snapshot('lesson_attempts', $attempt);
        $event->trigger();

                        $essayinfo = lesson_page_type_essay::extract_useranswer($attempt->useranswer);
        $currentpage = $lesson->load_page($attempt->pageid);

        $mform = new essay_grading_form(null, array('scoreoptions'=>$scoreoptions, 'user'=>$user));
        $data = new stdClass;
        $data->id = $cm->id;
        $data->attemptid = $attemptid;
        $data->score = $essayinfo->score;
        $data->question = format_text($currentpage->contents, $currentpage->contentsformat, $formattextdefoptions);
        $data->studentanswer = format_text($essayinfo->answer, $essayinfo->answerformat,
                array('context' => $context, 'para' => true));
        $data->response = $essayinfo->response;
        $data->responseformat = $essayinfo->responseformat;
        $editoroptions = array('noclean' => true, 'maxfiles' => EDITOR_UNLIMITED_FILES,
                'maxbytes' => $CFG->maxbytes, 'context' => $context);
        $data = file_prepare_standard_editor($data, 'response', $editoroptions, $context,
                'mod_lesson', 'essay_responses', $attempt->id);
        $mform->set_data($data);

        $mform->display();
        break;
    default:
        groups_print_activity_menu($cm, $url);
        break;
}

echo $OUTPUT->footer();
