<?php




defined('MOODLE_INTERNAL') || die();




function lesson_add_instance($data, $mform) {
    global $DB;

    $cmid = $data->coursemodule;
    $draftitemid = $data->mediafile;
    $context = context_module::instance($cmid);

    lesson_process_pre_save($data);

    unset($data->mediafile);
    $lessonid = $DB->insert_record("lesson", $data);
    $data->id = $lessonid;

    lesson_update_media_file($lessonid, $context, $draftitemid);

    lesson_process_post_save($data);

    lesson_grade_item_update($data);

    return $lessonid;
}


function lesson_update_instance($data, $mform) {
    global $DB;

    $data->id = $data->instance;
    $cmid = $data->coursemodule;
    $draftitemid = $data->mediafile;
    $context = context_module::instance($cmid);

    lesson_process_pre_save($data);

    unset($data->mediafile);
    $DB->update_record("lesson", $data);

    lesson_update_media_file($data->id, $context, $draftitemid);

    lesson_process_post_save($data);

        lesson_grade_item_update($data);

        lesson_update_grades($data, 0, false);

    return true;
}


function lesson_update_events($lesson, $override = null) {
    global $CFG, $DB;

    require_once($CFG->dirroot . '/mod/lesson/locallib.php');
    require_once($CFG->dirroot . '/calendar/lib.php');

        $conds = array('modulename' => 'lesson',
                   'instance' => $lesson->id);
    if (!empty($override)) {
                if (isset($override->userid)) {
            $conds['userid'] = $override->userid;
        } else {
            $conds['groupid'] = $override->groupid;
        }
    }
    $oldevents = $DB->get_records('event', $conds);

        if (empty($override)) {
                        $overrides = $DB->get_records('lesson_overrides', array('lessonid' => $lesson->id));
                $overrides[] = new stdClass();
    } else {
                $overrides = array($override);
    }

    foreach ($overrides as $current) {
        $groupid   = isset($current->groupid) ? $current->groupid : 0;
        $userid    = isset($current->userid) ? $current->userid : 0;
        $available  = isset($current->available) ? $current->available : $lesson->available;
        $deadline = isset($current->deadline) ? $current->deadline : $lesson->deadline;

                $addopen  = empty($current->id) || !empty($current->available);
        $addclose = empty($current->id) || !empty($current->deadline);

        if (!empty($lesson->coursemodule)) {
            $cmid = $lesson->coursemodule;
        } else {
            $cmid = get_coursemodule_from_instance('lesson', $lesson->id, $lesson->course)->id;
        }

        $event = new stdClass();
        $event->description = format_module_intro('lesson', $lesson, $cmid);
                $event->courseid    = ($userid) ? 0 : $lesson->course;
        $event->groupid     = $groupid;
        $event->userid      = $userid;
        $event->modulename  = 'lesson';
        $event->instance    = $lesson->id;
        $event->timestart   = $available;
        $event->timeduration = max($deadline - $available, 0);
        $event->visible     = instance_is_visible('lesson', $lesson);
        $event->eventtype   = 'open';

                if ($groupid) {
            $params = new stdClass();
            $params->lesson = $lesson->name;
            $params->group = groups_get_group_name($groupid);
            if ($params->group === false) {
                                continue;
            }
            $eventname = get_string('overridegroupeventname', 'lesson', $params);
        } else if ($userid) {
            $params = new stdClass();
            $params->lesson = $lesson->name;
            $eventname = get_string('overrideusereventname', 'lesson', $params);
        } else {
            $eventname = $lesson->name;
        }
        if ($addopen or $addclose) {
            if ($deadline and $available and $event->timeduration <= LESSON_MAX_EVENT_LENGTH) {
                                if ($oldevent = array_shift($oldevents)) {
                    $event->id = $oldevent->id;
                } else {
                    unset($event->id);
                }
                $event->name = $eventname;
                                calendar_event::create($event);
            } else {
                                $event->timeduration  = 0;
                if ($available && $addopen) {
                    if ($oldevent = array_shift($oldevents)) {
                        $event->id = $oldevent->id;
                    } else {
                        unset($event->id);
                    }
                    $event->name = $eventname.' ('.get_string('lessonopens', 'lesson').')';
                                        calendar_event::create($event);
                }
                if ($deadline && $addclose) {
                    if ($oldevent = array_shift($oldevents)) {
                        $event->id = $oldevent->id;
                    } else {
                        unset($event->id);
                    }
                    $event->name      = $eventname.' ('.get_string('lessoncloses', 'lesson').')';
                    $event->timestart = $deadline;
                    $event->eventtype = 'close';
                    calendar_event::create($event);
                }
            }
        }
    }

        foreach ($oldevents as $badevent) {
        $badevent = calendar_event::load($badevent);
        $badevent->delete();
    }
}


function lesson_refresh_events($courseid = 0) {
    global $DB;

    if ($courseid == 0) {
        if (!$lessons = $DB->get_records('lessons')) {
            return true;
        }
    } else {
        if (!$lessons = $DB->get_records('lesson', array('course' => $courseid))) {
            return true;
        }
    }

    foreach ($lessons as $lesson) {
        lesson_update_events($lesson);
    }

    return true;
}


function lesson_delete_instance($id) {
    global $DB, $CFG;
    require_once($CFG->dirroot . '/mod/lesson/locallib.php');

    $lesson = $DB->get_record("lesson", array("id"=>$id), '*', MUST_EXIST);
    $lesson = new lesson($lesson);
    return $lesson->delete();
}


function lesson_delete_course($course, $feedback=true) {
    return true;
}


function lesson_user_outline($course, $user, $mod, $lesson) {
    global $CFG, $DB;

    require_once("$CFG->libdir/gradelib.php");
    $grades = grade_get_grades($course->id, 'mod', 'lesson', $lesson->id, $user->id);
    $return = new stdClass();

    if (empty($grades->items[0]->grades)) {
        $return->info = get_string("nolessonattempts", "lesson");
    } else {
        $grade = reset($grades->items[0]->grades);
        if (empty($grade->grade)) {

                        $sql = "SELECT *
                      FROM {lesson_timer}
                     WHERE lessonid = :lessonid
                       AND userid = :userid
                  ORDER BY starttime DESC";
            $params = array('lessonid' => $lesson->id, 'userid' => $user->id);

            if ($attempts = $DB->get_records_sql($sql, $params, 0, 1)) {
                $attempt = reset($attempts);
                if ($attempt->completed) {
                    $return->info = get_string("completed", "lesson");
                } else {
                    $return->info = get_string("notyetcompleted", "lesson");
                }
                $return->time = $attempt->lessontime;
            } else {
                $return->info = get_string("nolessonattempts", "lesson");
            }
        } else {
            $return->info = get_string("grade") . ': ' . $grade->str_long_grade;

                                                if ($grade->usermodified == $user->id || empty($grade->datesubmitted)) {
                $return->time = $grade->dategraded;
            } else {
                $return->time = $grade->datesubmitted;
            }
        }
    }
    return $return;
}


function lesson_user_complete($course, $user, $mod, $lesson) {
    global $DB, $OUTPUT, $CFG;

    require_once("$CFG->libdir/gradelib.php");

    $grades = grade_get_grades($course->id, 'mod', 'lesson', $lesson->id, $user->id);

        if (empty($grades->items[0]->grades)) {
        echo $OUTPUT->container(get_string("nolessonattempts", "lesson"));
    } else {
        $grade = reset($grades->items[0]->grades);
        if (empty($grade->grade)) {
                        $sql = "SELECT *
                      FROM {lesson_timer}
                     WHERE lessonid = :lessonid
                       AND userid = :userid
                     ORDER by starttime desc";
            $params = array('lessonid' => $lesson->id, 'userid' => $user->id);

            if ($attempt = $DB->get_record_sql($sql, $params, IGNORE_MULTIPLE)) {
                if ($attempt->completed) {
                    $status = get_string("completed", "lesson");
                } else {
                    $status = get_string("notyetcompleted", "lesson");
                }
            } else {
                $status = get_string("nolessonattempts", "lesson");
            }
        } else {
            $status = get_string("grade") . ': ' . $grade->str_long_grade;
        }

                echo $OUTPUT->container($status);

        if ($grade->str_feedback) {
            echo $OUTPUT->container(get_string('feedback').': '.$grade->str_feedback);
        }
    }

            $params = array ("lessonid" => $lesson->id, "userid" => $user->id);
    $attempts = $DB->get_records_select("lesson_attempts", "lessonid = :lessonid AND userid = :userid", $params, "retry, timeseen");
    $branches = $DB->get_records_select("lesson_branch", "lessonid = :lessonid AND userid = :userid", $params, "retry, timeseen");
    if (!empty($attempts) or !empty($branches)) {
        echo $OUTPUT->box_start();
        $table = new html_table();
                $table->head = array (get_string("attemptheader", "lesson"),
            get_string("totalpagesviewedheader", "lesson"),
            get_string("numberofpagesviewedheader", "lesson"),
            get_string("numberofcorrectanswersheader", "lesson"),
            get_string("time"));
        $table->width = "100%";
        $table->align = array ("center", "center", "center", "center", "center");
        $table->size = array ("*", "*", "*", "*", "*");
        $table->cellpadding = 2;
        $table->cellspacing = 0;

        $retry = 0;
        $nquestions = 0;
        $npages = 0;
        $ncorrect = 0;

                foreach ($attempts as $attempt) {
            if ($attempt->retry == $retry) {
                $npages++;
                $nquestions++;
                if ($attempt->correct) {
                    $ncorrect++;
                }
                $timeseen = $attempt->timeseen;
            } else {
                $table->data[] = array($retry + 1, $npages, $nquestions, $ncorrect, userdate($timeseen));
                $retry++;
                $nquestions = 1;
                $npages = 1;
                if ($attempt->correct) {
                    $ncorrect = 1;
                } else {
                    $ncorrect = 0;
                }
            }
        }

                foreach ($branches as $branch) {
            if ($branch->retry == $retry) {
                $npages++;

                $timeseen = $branch->timeseen;
            } else {
                $table->data[] = array($retry + 1, $npages, $nquestions, $ncorrect, userdate($timeseen));
                $retry++;
                $npages = 1;
            }
        }
        if ($npages > 0) {
                $table->data[] = array($retry + 1, $npages, $nquestions, $ncorrect, userdate($timeseen));
        }
        echo html_writer::table($table);
        echo $OUTPUT->box_end();
    }

    return true;
}


function lesson_print_overview($courses, &$htmlarray) {
    global $USER, $CFG, $DB, $OUTPUT;

    if (!$lessons = get_all_instances_in_courses('lesson', $courses)) {
        return;
    }

        $params = array($USER->id);
    $sql = 'SELECT lessonid, userid, count(userid) as attempts
              FROM {lesson_grades}
             WHERE userid = ?
          GROUP BY lessonid, userid';
    $allattempts = $DB->get_records_sql($sql, $params);
    $completedattempts = array();
    foreach ($allattempts as $myattempt) {
        $completedattempts[$myattempt->lessonid] = $myattempt->attempts;
    }

        $listoflessons = array();
    foreach ($lessons as $lesson) {
        $listoflessons[] = $lesson->id;
    }
        list($insql, $inparams) = $DB->get_in_or_equal($listoflessons, SQL_PARAMS_NAMED);
    $dbparams = array_merge($inparams, array('userid' => $USER->id));

        $select = "SELECT l.id, l.timeseen, l.lessonid, l.userid, l.retry, l.pageid, l.answerid as nextpageid, p.qtype ";
    $from = "FROM {lesson_attempts} l
             JOIN (
                   SELECT idselect.lessonid, idselect.userid, MAX(idselect.id) AS id
                     FROM {lesson_attempts} idselect
                     JOIN (
                           SELECT lessonid, userid, MAX(timeseen) AS timeseen
                             FROM {lesson_attempts}
                            WHERE userid = :userid
                              AND lessonid $insql
                         GROUP BY userid, lessonid
                           ) timeselect
                       ON timeselect.timeseen = idselect.timeseen
                      AND timeselect.userid = idselect.userid
                      AND timeselect.lessonid = idselect.lessonid
                 GROUP BY idselect.userid, idselect.lessonid
                   ) aid
               ON l.id = aid.id
             JOIN {lesson_pages} p
               ON l.pageid = p.id ";
    $lastattempts = $DB->get_records_sql($select . $from, $dbparams);

        $select = "SELECT l.id, l.timeseen, l.lessonid, l.userid, l.retry, l.pageid, l.nextpageid, p.qtype ";
    $from = str_replace('{lesson_attempts}', '{lesson_branch}', $from);
    $lastbranches = $DB->get_records_sql($select . $from, $dbparams);

    $lastviewed = array();
    foreach ($lastattempts as $lastattempt) {
        $lastviewed[$lastattempt->lessonid] = $lastattempt;
    }

            foreach ($lastbranches as $lastbranch) {
        if (!isset($lastviewed[$lastbranch->lessonid])) {
            $lastviewed[$lastbranch->lessonid] = $lastbranch;
        } else if ($lastviewed[$lastbranch->lessonid]->timeseen < $lastbranch->timeseen) {
            $lastviewed[$lastbranch->lessonid] = $lastbranch;
        }
    }

        require_once($CFG->dirroot . '/mod/lesson/locallib.php');

    $now = time();
    foreach ($lessons as $lesson) {
        if ($lesson->deadline != 0                                                     and $lesson->deadline >= $now                                              and ($lesson->available == 0 or $lesson->available <= $now)) { 
                        $class = (!$lesson->visible) ? 'dimmed' : '';

                        $context = context_module::instance($lesson->coursemodule);

                        $url = new moodle_url('/mod/lesson/view.php', array('id' => $lesson->coursemodule));
            $url = html_writer::link($url, format_string($lesson->name, true, array('context' => $context)), array('class' => $class));
            $str = $OUTPUT->box(get_string('lessonname', 'lesson', $url), 'name');

                        $str .= $OUTPUT->box(get_string('lessoncloseson', 'lesson', userdate($lesson->deadline)), 'info');

                        if (has_capability('mod/lesson:manage', $context)) {
                                $attempts = $DB->count_records('lesson_grades', array('lessonid' => $lesson->id));
                $str     .= $OUTPUT->box(get_string('xattempts', 'lesson', $attempts), 'info');
                $str      = $OUTPUT->box($str, 'lesson overview');
            } else {
                                if (isset($lastviewed[$lesson->id]->timeseen)) {
                                        if (isset($completedattempts[$lesson->id]) &&
                             ($completedattempts[$lesson->id] == ($lastviewed[$lesson->id]->retry + 1))) {
                                                if ($lesson->retake) {
                                                        $str .= $OUTPUT->box(get_string('additionalattemptsremaining', 'lesson'), 'info');
                            $str = $OUTPUT->box($str, 'lesson overview');
                        } else {
                                                        $str = '';
                        }

                    } else {
                                                                        require_once($CFG->dirroot . '/mod/lesson/pagetypes/branchtable.php');
                        if ($lastviewed[$lesson->id]->qtype == LESSON_PAGE_BRANCHTABLE) {
                                                        if ($lastviewed[$lesson->id]->nextpageid == LESSON_EOL) {
                                                                if ($lesson->retake) {
                                                                        $str .= $OUTPUT->box(get_string('additionalattemptsremaining', 'lesson'), 'info');
                                    $str = $OUTPUT->box($str, 'lesson overview');
                                } else {
                                                                        $str = '';
                                }

                            } else {
                                                                $str .= $OUTPUT->box(get_string('notyetcompleted', 'lesson'), 'info');
                                $str = $OUTPUT->box($str, 'lesson overview');
                            }

                        } else {
                                                        $str .= $OUTPUT->box(get_string('notyetcompleted', 'lesson'), 'info');
                            $str = $OUTPUT->box($str, 'lesson overview');
                        }
                    }

                } else {
                                        $str .= $OUTPUT->box(get_string('nolessonattempts', 'lesson'), 'info');
                    $str = $OUTPUT->box($str, 'lesson overview');
                }
            }
            if (!empty($str)) {
                if (empty($htmlarray[$lesson->course]['lesson'])) {
                    $htmlarray[$lesson->course]['lesson'] = $str;
                } else {
                    $htmlarray[$lesson->course]['lesson'] .= $str;
                }
            }
        }
    }
}


function lesson_cron () {
    global $CFG;

    return true;
}


function lesson_get_user_grades($lesson, $userid=0) {
    global $CFG, $DB;

    $params = array("lessonid" => $lesson->id,"lessonid2" => $lesson->id);

    if (!empty($userid)) {
        $params["userid"] = $userid;
        $params["userid2"] = $userid;
        $user = "AND u.id = :userid";
        $fuser = "AND uu.id = :userid2";
    }
    else {
        $user="";
        $fuser="";
    }

    if ($lesson->retake) {
        if ($lesson->usemaxgrade) {
            $sql = "SELECT u.id, u.id AS userid, MAX(g.grade) AS rawgrade
                      FROM {user} u, {lesson_grades} g
                     WHERE u.id = g.userid AND g.lessonid = :lessonid
                           $user
                  GROUP BY u.id";
        } else {
            $sql = "SELECT u.id, u.id AS userid, AVG(g.grade) AS rawgrade
                      FROM {user} u, {lesson_grades} g
                     WHERE u.id = g.userid AND g.lessonid = :lessonid
                           $user
                  GROUP BY u.id";
        }
        unset($params['lessonid2']);
        unset($params['userid2']);
    } else {
                $firstonly = "SELECT uu.id AS userid, MIN(gg.id) AS firstcompleted
                        FROM {user} uu, {lesson_grades} gg
                       WHERE uu.id = gg.userid AND gg.lessonid = :lessonid2
                             $fuser
                       GROUP BY uu.id";

        $sql = "SELECT u.id, u.id AS userid, g.grade AS rawgrade
                  FROM {user} u, {lesson_grades} g, ($firstonly) f
                 WHERE u.id = g.userid AND g.lessonid = :lessonid
                       AND g.id = f.firstcompleted AND g.userid=f.userid
                       $user";
    }

    return $DB->get_records_sql($sql, $params);
}


function lesson_update_grades($lesson, $userid=0, $nullifnone=true) {
    global $CFG, $DB;
    require_once($CFG->libdir.'/gradelib.php');

    if ($lesson->grade == 0 || $lesson->practice) {
        lesson_grade_item_update($lesson);

    } else if ($grades = lesson_get_user_grades($lesson, $userid)) {
        lesson_grade_item_update($lesson, $grades);

    } else if ($userid and $nullifnone) {
        $grade = new stdClass();
        $grade->userid   = $userid;
        $grade->rawgrade = null;
        lesson_grade_item_update($lesson, $grade);

    } else {
        lesson_grade_item_update($lesson);
    }
}


function lesson_grade_item_update($lesson, $grades=null) {
    global $CFG;
    if (!function_exists('grade_update')) {         require_once($CFG->libdir.'/gradelib.php');
    }

    if (array_key_exists('cmidnumber', $lesson)) {         $params = array('itemname'=>$lesson->name, 'idnumber'=>$lesson->cmidnumber);
    } else {
        $params = array('itemname'=>$lesson->name);
    }

    if (!$lesson->practice and $lesson->grade > 0) {
        $params['gradetype']  = GRADE_TYPE_VALUE;
        $params['grademax']   = $lesson->grade;
        $params['grademin']   = 0;
    } else if (!$lesson->practice and $lesson->grade < 0) {
        $params['gradetype']  = GRADE_TYPE_SCALE;
        $params['scaleid']   = -$lesson->grade;

                $currentgrade = null;
        if (!empty($grades)) {
            if (is_array($grades)) {
                $currentgrade = reset($grades);
            } else {
                $currentgrade = $grades;
            }
        }

                if (!empty($currentgrade) && $currentgrade->rawgrade !== null) {
            $grade = grade_get_grades($lesson->course, 'mod', 'lesson', $lesson->id, $currentgrade->userid);
            $params['grademax']   = reset($grade->items)->grademax;
        }
    } else {
        $params['gradetype']  = GRADE_TYPE_NONE;
    }

    if ($grades  === 'reset') {
        $params['reset'] = true;
        $grades = null;
    } else if (!empty($grades)) {
                if (is_object($grades)) {
            $grades = array($grades->userid => $grades);
        } else if (array_key_exists('userid', $grades)) {
            $grades = array($grades['userid'] => $grades);
        }
        foreach ($grades as $key => $grade) {
            if (!is_array($grade)) {
                $grades[$key] = $grade = (array) $grade;
            }
                        if ($grade['rawgrade'] !== null) {
                $grades[$key]['rawgrade'] = ($grade['rawgrade'] * $params['grademax'] / 100);
            } else {
                                $grades[$key]['rawgrade'] = null;
            }
        }
    }

    return grade_update('mod/lesson', $lesson->course, 'mod', 'lesson', $lesson->id, 0, $grades, $params);
}


function lesson_get_view_actions() {
    return array('view','view all');
}


function lesson_get_post_actions() {
    return array('end','start');
}


function lesson_process_pre_save(&$lesson) {
    global $DB;

    $lesson->timemodified = time();

    if (empty($lesson->timelimit)) {
        $lesson->timelimit = 0;
    }
    if (empty($lesson->timespent) or !is_numeric($lesson->timespent) or $lesson->timespent < 0) {
        $lesson->timespent = 0;
    }
    if (!isset($lesson->completed)) {
        $lesson->completed = 0;
    }
    if (empty($lesson->gradebetterthan) or !is_numeric($lesson->gradebetterthan) or $lesson->gradebetterthan < 0) {
        $lesson->gradebetterthan = 0;
    } else if ($lesson->gradebetterthan > 100) {
        $lesson->gradebetterthan = 100;
    }

    if (empty($lesson->width)) {
        $lesson->width = 640;
    }
    if (empty($lesson->height)) {
        $lesson->height = 480;
    }
    if (empty($lesson->bgcolor)) {
        $lesson->bgcolor = '#FFFFFF';
    }

        $conditions = new stdClass;
    $conditions->timespent = $lesson->timespent;
    $conditions->completed = $lesson->completed;
    $conditions->gradebetterthan = $lesson->gradebetterthan;
    $lesson->conditions = serialize($conditions);
    unset($lesson->timespent);
    unset($lesson->completed);
    unset($lesson->gradebetterthan);

    if (empty($lesson->password)) {
        unset($lesson->password);
    }
}


function lesson_process_post_save(&$lesson) {
        lesson_update_events($lesson);
}



function lesson_reset_course_form_definition(&$mform) {
    $mform->addElement('header', 'lessonheader', get_string('modulenameplural', 'lesson'));
    $mform->addElement('advcheckbox', 'reset_lesson', get_string('deleteallattempts','lesson'));
    $mform->addElement('advcheckbox', 'reset_lesson_user_overrides',
            get_string('removealluseroverrides', 'lesson'));
    $mform->addElement('advcheckbox', 'reset_lesson_group_overrides',
            get_string('removeallgroupoverrides', 'lesson'));
}


function lesson_reset_course_form_defaults($course) {
    return array('reset_lesson' => 1,
            'reset_lesson_group_overrides' => 1,
            'reset_lesson_user_overrides' => 1);
}


function lesson_reset_gradebook($courseid, $type='') {
    global $CFG, $DB;

    $sql = "SELECT l.*, cm.idnumber as cmidnumber, l.course as courseid
              FROM {lesson} l, {course_modules} cm, {modules} m
             WHERE m.name='lesson' AND m.id=cm.module AND cm.instance=l.id AND l.course=:course";
    $params = array ("course" => $courseid);
    if ($lessons = $DB->get_records_sql($sql,$params)) {
        foreach ($lessons as $lesson) {
            lesson_grade_item_update($lesson, 'reset');
        }
    }
}


function lesson_reset_userdata($data) {
    global $CFG, $DB;

    $componentstr = get_string('modulenameplural', 'lesson');
    $status = array();

    if (!empty($data->reset_lesson)) {
        $lessonssql = "SELECT l.id
                         FROM {lesson} l
                        WHERE l.course=:course";

        $params = array ("course" => $data->courseid);
        $lessons = $DB->get_records_sql($lessonssql, $params);

                $fs = get_file_storage();
        if ($lessons) {
            foreach ($lessons as $lessonid => $unused) {
                if (!$cm = get_coursemodule_from_instance('lesson', $lessonid)) {
                    continue;
                }
                $context = context_module::instance($cm->id);
                $fs->delete_area_files($context->id, 'mod_lesson', 'essay_responses');
            }
        }

        $DB->delete_records_select('lesson_timer', "lessonid IN ($lessonssql)", $params);
        $DB->delete_records_select('lesson_grades', "lessonid IN ($lessonssql)", $params);
        $DB->delete_records_select('lesson_attempts', "lessonid IN ($lessonssql)", $params);
        $DB->delete_records_select('lesson_branch', "lessonid IN ($lessonssql)", $params);

                if (empty($data->reset_gradebook_grades)) {
            lesson_reset_gradebook($data->courseid);
        }

        $status[] = array('component'=>$componentstr, 'item'=>get_string('deleteallattempts', 'lesson'), 'error'=>false);
    }

        if (!empty($data->reset_lesson_user_overrides)) {
        $DB->delete_records_select('lesson_overrides',
                'lessonid IN (SELECT id FROM {lesson} WHERE course = ?) AND userid IS NOT NULL', array($data->courseid));
        $status[] = array(
        'component' => $componentstr,
        'item' => get_string('useroverridesdeleted', 'lesson'),
        'error' => false);
    }
        if (!empty($data->reset_lesson_group_overrides)) {
        $DB->delete_records_select('lesson_overrides',
        'lessonid IN (SELECT id FROM {lesson} WHERE course = ?) AND groupid IS NOT NULL', array($data->courseid));
        $status[] = array(
        'component' => $componentstr,
        'item' => get_string('groupoverridesdeleted', 'lesson'),
        'error' => false);
    }
        if ($data->timeshift) {
        $DB->execute("UPDATE {lesson_overrides}
                         SET available = available + ?
                       WHERE lessonid IN (SELECT id FROM {lesson} WHERE course = ?)
                         AND available <> 0", array($data->timeshift, $data->courseid));
        $DB->execute("UPDATE {lesson_overrides}
                         SET deadline = deadline + ?
                       WHERE lessonid IN (SELECT id FROM {lesson} WHERE course = ?)
                         AND deadline <> 0", array($data->timeshift, $data->courseid));

        shift_course_mod_dates('lesson', array('available', 'deadline'), $data->timeshift, $data->courseid);
        $status[] = array('component'=>$componentstr, 'item'=>get_string('datechanged'), 'error'=>false);
    }

    return $status;
}


function lesson_get_extra_capabilities() {
    return array('moodle/site:accessallgroups');
}


function lesson_supports($feature) {
    switch($feature) {
        case FEATURE_GROUPS:
            return true;
        case FEATURE_GROUPINGS:
            return true;
        case FEATURE_MOD_INTRO:
            return true;
        case FEATURE_COMPLETION_TRACKS_VIEWS:
            return true;
        case FEATURE_GRADE_HAS_GRADE:
            return true;
        case FEATURE_COMPLETION_HAS_RULES:
            return true;
        case FEATURE_GRADE_OUTCOMES:
            return true;
        case FEATURE_BACKUP_MOODLE2:
            return true;
        case FEATURE_SHOW_DESCRIPTION:
            return true;
        default:
            return null;
    }
}


function lesson_get_completion_state($course, $cm, $userid, $type) {
    global $CFG, $DB;

        $lesson = $DB->get_record('lesson', array('id' => $cm->instance), '*',
            MUST_EXIST);

    $result = $type;         if ($lesson->completionendreached) {
        $value = $DB->record_exists('lesson_timer', array(
                'lessonid' => $lesson->id, 'userid' => $userid, 'completed' => 1));
        if ($type == COMPLETION_AND) {
            $result = $result && $value;
        } else {
            $result = $result || $value;
        }
    }
    if ($lesson->completiontimespent != 0) {
        $duration = $DB->get_field_sql(
                        "SELECT SUM(lessontime - starttime)
                               FROM {lesson_timer}
                              WHERE lessonid = :lessonid
                                AND userid = :userid",
                        array('userid' => $userid, 'lessonid' => $lesson->id));
        if (!$duration) {
            $duration = 0;
        }
        if ($type == COMPLETION_AND) {
            $result = $result && ($lesson->completiontimespent < $duration);
        } else {
            $result = $result || ($lesson->completiontimespent < $duration);
        }
    }
    return $result;
}

function lesson_extend_settings_navigation($settings, $lessonnode) {
    global $PAGE, $DB;

            $keys = $lessonnode->get_children_key_list();
    $beforekey = null;
    $i = array_search('modedit', $keys);
    if ($i === false and array_key_exists(0, $keys)) {
        $beforekey = $keys[0];
    } else if (array_key_exists($i + 1, $keys)) {
        $beforekey = $keys[$i + 1];
    }

    if (has_capability('mod/lesson:manageoverrides', $PAGE->cm->context)) {
        $url = new moodle_url('/mod/lesson/overrides.php', array('cmid' => $PAGE->cm->id));
        $node = navigation_node::create(get_string('groupoverrides', 'lesson'),
                new moodle_url($url, array('mode' => 'group')),
                navigation_node::TYPE_SETTING, null, 'mod_lesson_groupoverrides');
        $lessonnode->add_node($node, $beforekey);

        $node = navigation_node::create(get_string('useroverrides', 'lesson'),
                new moodle_url($url, array('mode' => 'user')),
                navigation_node::TYPE_SETTING, null, 'mod_lesson_useroverrides');
        $lessonnode->add_node($node, $beforekey);
    }

    if (has_capability('mod/lesson:edit', $PAGE->cm->context)) {
        $url = new moodle_url('/mod/lesson/view.php', array('id' => $PAGE->cm->id));
        $lessonnode->add(get_string('preview', 'lesson'), $url);
        $editnode = $lessonnode->add(get_string('edit', 'lesson'));
        $url = new moodle_url('/mod/lesson/edit.php', array('id' => $PAGE->cm->id, 'mode' => 'collapsed'));
        $editnode->add(get_string('collapsed', 'lesson'), $url);
        $url = new moodle_url('/mod/lesson/edit.php', array('id' => $PAGE->cm->id, 'mode' => 'full'));
        $editnode->add(get_string('full', 'lesson'), $url);
    }

    if (has_capability('mod/lesson:viewreports', $PAGE->cm->context)) {
        $reportsnode = $lessonnode->add(get_string('reports', 'lesson'));
        $url = new moodle_url('/mod/lesson/report.php', array('id'=>$PAGE->cm->id, 'action'=>'reportoverview'));
        $reportsnode->add(get_string('overview', 'lesson'), $url);
        $url = new moodle_url('/mod/lesson/report.php', array('id'=>$PAGE->cm->id, 'action'=>'reportdetail'));
        $reportsnode->add(get_string('detailedstats', 'lesson'), $url);
    }

    if (has_capability('mod/lesson:grade', $PAGE->cm->context)) {
        $url = new moodle_url('/mod/lesson/essay.php', array('id'=>$PAGE->cm->id));
        $lessonnode->add(get_string('manualgrading', 'lesson'), $url);
    }

}


function lesson_get_import_export_formats($type) {
    global $CFG;
    $fileformats = core_component::get_plugin_list("qformat");

    $fileformatname=array();
    foreach ($fileformats as $fileformat=>$fdir) {
        $format_file = "$fdir/format.php";
        if (file_exists($format_file) ) {
            require_once($format_file);
        } else {
            continue;
        }
        $classname = "qformat_$fileformat";
        $format_class = new $classname();
        if ($type=='import') {
            $provided = $format_class->provide_import();
        } else {
            $provided = $format_class->provide_export();
        }
        if ($provided) {
            $fileformatnames[$fileformat] = get_string('pluginname', 'qformat_'.$fileformat);
        }
    }
    natcasesort($fileformatnames);

    return $fileformatnames;
}


function lesson_pluginfile($course, $cm, $context, $filearea, $args, $forcedownload, array $options=array()) {
    global $CFG, $DB;

    if ($context->contextlevel != CONTEXT_MODULE) {
        return false;
    }

    $fileareas = lesson_get_file_areas();
    if (!array_key_exists($filearea, $fileareas)) {
        return false;
    }

    if (!$lesson = $DB->get_record('lesson', array('id'=>$cm->instance))) {
        return false;
    }

    require_course_login($course, true, $cm);

    if ($filearea === 'page_contents') {
        $pageid = (int)array_shift($args);
        if (!$page = $DB->get_record('lesson_pages', array('id'=>$pageid))) {
            return false;
        }
        $fullpath = "/$context->id/mod_lesson/$filearea/$pageid/".implode('/', $args);

    } else if ($filearea === 'page_answers' || $filearea === 'page_responses') {
        $itemid = (int)array_shift($args);
        if (!$pageanswers = $DB->get_record('lesson_answers', array('id' => $itemid))) {
            return false;
        }
        $fullpath = "/$context->id/mod_lesson/$filearea/$itemid/".implode('/', $args);

    } else if ($filearea === 'essay_responses') {
        $itemid = (int)array_shift($args);
        if (!$attempt = $DB->get_record('lesson_attempts', array('id' => $itemid))) {
            return false;
        }
        $fullpath = "/$context->id/mod_lesson/$filearea/$itemid/".implode('/', $args);

    } else if ($filearea === 'mediafile') {
        if (count($args) > 1) {
                                    array_shift($args);
        }
        $fullpath = "/$context->id/mod_lesson/$filearea/0/".implode('/', $args);

    } else {
        return false;
    }

    $fs = get_file_storage();
    if (!$file = $fs->get_file_by_hash(sha1($fullpath)) or $file->is_directory()) {
        return false;
    }

        send_stored_file($file, 0, 0, $forcedownload, $options); }


function lesson_get_file_areas() {
    $areas = array();
    $areas['page_contents'] = get_string('pagecontents', 'mod_lesson');
    $areas['mediafile'] = get_string('mediafile', 'mod_lesson');
    $areas['page_answers'] = get_string('pageanswers', 'mod_lesson');
    $areas['page_responses'] = get_string('pageresponses', 'mod_lesson');
    $areas['essay_responses'] = get_string('essayresponses', 'mod_lesson');
    return $areas;
}


function lesson_get_file_info($browser, $areas, $course, $cm, $context, $filearea, $itemid, $filepath, $filename) {
    global $CFG, $DB;

    if (!has_capability('moodle/course:managefiles', $context)) {
                return null;
    }

            if ($filearea == 'mediafile' && is_null($itemid)) {
        $itemid = 0;
    }

    if (is_null($itemid)) {
        return new mod_lesson_file_info($browser, $course, $cm, $context, $areas, $filearea);
    }

    $fs = get_file_storage();
    $filepath = is_null($filepath) ? '/' : $filepath;
    $filename = is_null($filename) ? '.' : $filename;
    if (!$storedfile = $fs->get_file($context->id, 'mod_lesson', $filearea, $itemid, $filepath, $filename)) {
        return null;
    }

    $itemname = $filearea;
    if ($filearea == 'page_contents') {
        $itemname = $DB->get_field('lesson_pages', 'title', array('lessonid' => $cm->instance, 'id' => $itemid));
        $itemname = format_string($itemname, true, array('context' => $context));
    } else {
        $areas = lesson_get_file_areas();
        if (isset($areas[$filearea])) {
            $itemname = $areas[$filearea];
        }
    }

    $urlbase = $CFG->wwwroot . '/pluginfile.php';
    return new file_info_stored($browser, $context, $storedfile, $urlbase, $itemname, $itemid, true, true, false);
}



function lesson_page_type_list($pagetype, $parentcontext, $currentcontext) {
    $module_pagetype = array(
        'mod-lesson-*'=>get_string('page-mod-lesson-x', 'lesson'),
        'mod-lesson-view'=>get_string('page-mod-lesson-view', 'lesson'),
        'mod-lesson-edit'=>get_string('page-mod-lesson-edit', 'lesson'));
    return $module_pagetype;
}


function lesson_update_media_file($lessonid, $context, $draftitemid) {
    global $DB;

        $fs = get_file_storage();
        file_save_draft_area_files($draftitemid, $context->id, 'mod_lesson', 'mediafile', 0);
        $files = $fs->get_area_files($context->id, 'mod_lesson', 'mediafile', 0, 'itemid, filepath, filename', false);
        if (count($files) == 1) {
                $file = reset($files);
                $DB->set_field('lesson', 'mediafile', '/' . $file->get_filename(), array('id' => $lessonid));
    } else {
                $DB->set_field('lesson', 'mediafile', '', array('id' => $lessonid));
    }
}
