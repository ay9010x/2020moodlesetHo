<?php




defined('MOODLE_INTERNAL') || die();

require_once($CFG->dirroot . '/calendar/lib.php');



function workshop_supports($feature) {
    switch($feature) {
        case FEATURE_GRADE_HAS_GRADE:   return true;
        case FEATURE_GROUPS:            return true;
        case FEATURE_GROUPINGS:         return true;
        case FEATURE_MOD_INTRO:         return true;
        case FEATURE_BACKUP_MOODLE2:    return true;
        case FEATURE_COMPLETION_TRACKS_VIEWS:
            return true;
        case FEATURE_SHOW_DESCRIPTION:  return true;
        case FEATURE_PLAGIARISM:        return true;
        default:                        return null;
    }
}


function workshop_add_instance(stdclass $workshop) {
    global $CFG, $DB;
    require_once(dirname(__FILE__) . '/locallib.php');

    $workshop->phase                 = workshop::PHASE_SETUP;
    $workshop->timecreated           = time();
    $workshop->timemodified          = $workshop->timecreated;
    $workshop->useexamples           = (int)!empty($workshop->useexamples);
    $workshop->usepeerassessment     = 1;
    $workshop->useselfassessment     = (int)!empty($workshop->useselfassessment);
    $workshop->latesubmissions       = (int)!empty($workshop->latesubmissions);
    $workshop->phaseswitchassessment = (int)!empty($workshop->phaseswitchassessment);
    $workshop->evaluation            = 'best';

    if (isset($workshop->gradinggradepass)) {
        $workshop->gradinggradepass = (float)unformat_float($workshop->gradinggradepass);
    }

    if (isset($workshop->submissiongradepass)) {
        $workshop->submissiongradepass = (float)unformat_float($workshop->submissiongradepass);
    }

    if (isset($workshop->submissionfiletypes)) {
        $workshop->submissionfiletypes = workshop::clean_file_extensions($workshop->submissionfiletypes);
    }

    if (isset($workshop->overallfeedbackfiletypes)) {
        $workshop->overallfeedbackfiletypes = workshop::clean_file_extensions($workshop->overallfeedbackfiletypes);
    }

        $workshop->id = $DB->insert_record('workshop', $workshop);

        $cmid = $workshop->coursemodule;
    $DB->set_field('course_modules', 'instance', $workshop->id, array('id' => $cmid));
    $context = context_module::instance($cmid);

        if ($draftitemid = $workshop->instructauthorseditor['itemid']) {
        $workshop->instructauthors = file_save_draft_area_files($draftitemid, $context->id, 'mod_workshop', 'instructauthors',
                0, workshop::instruction_editors_options($context), $workshop->instructauthorseditor['text']);
        $workshop->instructauthorsformat = $workshop->instructauthorseditor['format'];
    }

    if ($draftitemid = $workshop->instructreviewerseditor['itemid']) {
        $workshop->instructreviewers = file_save_draft_area_files($draftitemid, $context->id, 'mod_workshop', 'instructreviewers',
                0, workshop::instruction_editors_options($context), $workshop->instructreviewerseditor['text']);
        $workshop->instructreviewersformat = $workshop->instructreviewerseditor['format'];
    }

    if ($draftitemid = $workshop->conclusioneditor['itemid']) {
        $workshop->conclusion = file_save_draft_area_files($draftitemid, $context->id, 'mod_workshop', 'conclusion',
                0, workshop::instruction_editors_options($context), $workshop->conclusioneditor['text']);
        $workshop->conclusionformat = $workshop->conclusioneditor['format'];
    }

        $DB->update_record('workshop', $workshop);

        workshop_grade_item_update($workshop);
    workshop_grade_item_category_update($workshop);

        workshop_calendar_update($workshop, $workshop->coursemodule);

    return $workshop->id;
}


function workshop_update_instance(stdclass $workshop) {
    global $CFG, $DB;
    require_once(dirname(__FILE__) . '/locallib.php');

    $workshop->timemodified          = time();
    $workshop->id                    = $workshop->instance;
    $workshop->useexamples           = (int)!empty($workshop->useexamples);
    $workshop->usepeerassessment     = 1;
    $workshop->useselfassessment     = (int)!empty($workshop->useselfassessment);
    $workshop->latesubmissions       = (int)!empty($workshop->latesubmissions);
    $workshop->phaseswitchassessment = (int)!empty($workshop->phaseswitchassessment);

    if (isset($workshop->gradinggradepass)) {
        $workshop->gradinggradepass = (float)unformat_float($workshop->gradinggradepass);
    }

    if (isset($workshop->submissiongradepass)) {
        $workshop->submissiongradepass = (float)unformat_float($workshop->submissiongradepass);
    }

    if (isset($workshop->submissionfiletypes)) {
        $workshop->submissionfiletypes = workshop::clean_file_extensions($workshop->submissionfiletypes);
    }

    if (isset($workshop->overallfeedbackfiletypes)) {
        $workshop->overallfeedbackfiletypes = workshop::clean_file_extensions($workshop->overallfeedbackfiletypes);
    }

    
    $DB->update_record('workshop', $workshop);
    $context = context_module::instance($workshop->coursemodule);

        if ($draftitemid = $workshop->instructauthorseditor['itemid']) {
        $workshop->instructauthors = file_save_draft_area_files($draftitemid, $context->id, 'mod_workshop', 'instructauthors',
                0, workshop::instruction_editors_options($context), $workshop->instructauthorseditor['text']);
        $workshop->instructauthorsformat = $workshop->instructauthorseditor['format'];
    }

    if ($draftitemid = $workshop->instructreviewerseditor['itemid']) {
        $workshop->instructreviewers = file_save_draft_area_files($draftitemid, $context->id, 'mod_workshop', 'instructreviewers',
                0, workshop::instruction_editors_options($context), $workshop->instructreviewerseditor['text']);
        $workshop->instructreviewersformat = $workshop->instructreviewerseditor['format'];
    }

    if ($draftitemid = $workshop->conclusioneditor['itemid']) {
        $workshop->conclusion = file_save_draft_area_files($draftitemid, $context->id, 'mod_workshop', 'conclusion',
                0, workshop::instruction_editors_options($context), $workshop->conclusioneditor['text']);
        $workshop->conclusionformat = $workshop->conclusioneditor['format'];
    }

        $DB->update_record('workshop', $workshop);

        workshop_grade_item_update($workshop);
    workshop_grade_item_category_update($workshop);

        workshop_calendar_update($workshop, $workshop->coursemodule);

    return true;
}


function workshop_delete_instance($id) {
    global $CFG, $DB;
    require_once($CFG->libdir.'/gradelib.php');

    if (! $workshop = $DB->get_record('workshop', array('id' => $id))) {
        return false;
    }

        $DB->delete_records('workshop_aggregations', array('workshopid' => $workshop->id));

        $submissions = $DB->get_records('workshop_submissions', array('workshopid' => $workshop->id), '', 'id');

        $assessments = $DB->get_records_list('workshop_assessments', 'submissionid', array_keys($submissions), '', 'id');

        $DB->delete_records_list('workshop_grades', 'assessmentid', array_keys($assessments));
    $DB->delete_records_list('workshop_assessments', 'id', array_keys($assessments));
    $DB->delete_records_list('workshop_submissions', 'id', array_keys($submissions));

        $strategies = core_component::get_plugin_list('workshopform');
    foreach ($strategies as $strategy => $path) {
        require_once($path.'/lib.php');
        $classname = 'workshop_'.$strategy.'_strategy';
        call_user_func($classname.'::delete_instance', $workshop->id);
    }

    $allocators = core_component::get_plugin_list('workshopallocation');
    foreach ($allocators as $allocator => $path) {
        require_once($path.'/lib.php');
        $classname = 'workshop_'.$allocator.'_allocator';
        call_user_func($classname.'::delete_instance', $workshop->id);
    }

    $evaluators = core_component::get_plugin_list('workshopeval');
    foreach ($evaluators as $evaluator => $path) {
        require_once($path.'/lib.php');
        $classname = 'workshop_'.$evaluator.'_evaluation';
        call_user_func($classname.'::delete_instance', $workshop->id);
    }

        $events = $DB->get_records('event', array('modulename' => 'workshop', 'instance' => $workshop->id));
    foreach ($events as $event) {
        $event = calendar_event::load($event);
        $event->delete();
    }

        $DB->delete_records('workshop', array('id' => $workshop->id));

        grade_update('mod/workshop', $workshop->course, 'mod', 'workshop', $workshop->id, 0, null, array('deleted' => true));
    grade_update('mod/workshop', $workshop->course, 'mod', 'workshop', $workshop->id, 1, null, array('deleted' => true));

    return true;
}


function workshop_refresh_events($courseid = 0) {
    global $DB;

    if ($courseid) {
                if (!is_numeric($courseid)) {
            return false;
        }
        if (!$workshops = $DB->get_records('workshop', array('course' => $courseid))) {
            return false;
        }
    } else {
        if (!$workshops = $DB->get_records('workshop')) {
            return false;
        }
    }
    foreach ($workshops as $workshop) {
        if (!$cm = get_coursemodule_from_instance('workshop', $workshop->id, $courseid, false)) {
            continue;
        }
        workshop_calendar_update($workshop, $cm->id);
    }
    return true;
}


function workshop_get_view_actions() {
    return array('view', 'view all', 'view submission', 'view example');
}


function workshop_get_post_actions() {
    return array('add', 'add assessment', 'add example', 'add submission',
                 'update', 'update assessment', 'update example', 'update submission');
}


function workshop_user_outline($course, $user, $mod, $workshop) {
    global $CFG, $DB;
    require_once($CFG->libdir.'/gradelib.php');

    $grades = grade_get_grades($course->id, 'mod', 'workshop', $workshop->id, $user->id);

    $submissiongrade = null;
    $assessmentgrade = null;

    $info = '';
    $time = 0;

    if (!empty($grades->items[0]->grades)) {
        $submissiongrade = reset($grades->items[0]->grades);
        $info .= get_string('submissiongrade', 'workshop') . ': ' . $submissiongrade->str_long_grade . html_writer::empty_tag('br');
        $time = max($time, $submissiongrade->dategraded);
    }
    if (!empty($grades->items[1]->grades)) {
        $assessmentgrade = reset($grades->items[1]->grades);
        $info .= get_string('gradinggrade', 'workshop') . ': ' . $assessmentgrade->str_long_grade;
        $time = max($time, $assessmentgrade->dategraded);
    }

    if (!empty($info) and !empty($time)) {
        $return = new stdclass();
        $return->time = $time;
        $return->info = $info;
        return $return;
    }

    return null;
}


function workshop_user_complete($course, $user, $mod, $workshop) {
    global $CFG, $DB, $OUTPUT;
    require_once(dirname(__FILE__).'/locallib.php');
    require_once($CFG->libdir.'/gradelib.php');

    $workshop   = new workshop($workshop, $mod, $course);
    $grades     = grade_get_grades($course->id, 'mod', 'workshop', $workshop->id, $user->id);

    if (!empty($grades->items[0]->grades)) {
        $submissiongrade = reset($grades->items[0]->grades);
        $info = get_string('submissiongrade', 'workshop') . ': ' . $submissiongrade->str_long_grade;
        echo html_writer::tag('li', $info, array('class'=>'submissiongrade'));
    }
    if (!empty($grades->items[1]->grades)) {
        $assessmentgrade = reset($grades->items[1]->grades);
        $info = get_string('gradinggrade', 'workshop') . ': ' . $assessmentgrade->str_long_grade;
        echo html_writer::tag('li', $info, array('class'=>'gradinggrade'));
    }

    if (has_capability('mod/workshop:viewallsubmissions', $workshop->context)) {
        $canviewsubmission = true;
        if (groups_get_activity_groupmode($workshop->cm) == SEPARATEGROUPS) {
                        if (!has_capability('moodle/site:accessallgroups', $workshop->context)) {
                $usersgroups = groups_get_activity_allowed_groups($workshop->cm);
                $authorsgroups = groups_get_all_groups($workshop->course->id, $user->id, $workshop->cm->groupingid, 'g.id');
                $sharedgroups = array_intersect_key($usersgroups, $authorsgroups);
                if (empty($sharedgroups)) {
                    $canviewsubmission = false;
                }
            }
        }
        if ($canviewsubmission and $submission = $workshop->get_submission_by_author($user->id)) {
            $title      = format_string($submission->title);
            $url        = $workshop->submission_url($submission->id);
            $link       = html_writer::link($url, $title);
            $info       = get_string('submission', 'workshop').': '.$link;
            echo html_writer::tag('li', $info, array('class'=>'submission'));
        }
    }

    if (has_capability('mod/workshop:viewallassessments', $workshop->context)) {
        if ($assessments = $workshop->get_assessments_by_reviewer($user->id)) {
            foreach ($assessments as $assessment) {
                $a = new stdclass();
                $a->submissionurl = $workshop->submission_url($assessment->submissionid)->out();
                $a->assessmenturl = $workshop->assess_url($assessment->id)->out();
                $a->submissiontitle = s($assessment->submissiontitle);
                echo html_writer::tag('li', get_string('assessmentofsubmission', 'workshop', $a));
            }
        }
    }
}


function workshop_print_recent_activity($course, $viewfullnames, $timestart) {
    global $CFG, $USER, $DB, $OUTPUT;

    $authoramefields = get_all_user_name_fields(true, 'author', null, 'author');
    $reviewerfields = get_all_user_name_fields(true, 'reviewer', null, 'reviewer');

    $sql = "SELECT s.id AS submissionid, s.title AS submissiontitle, s.timemodified AS submissionmodified,
                   author.id AS authorid, $authoramefields, a.id AS assessmentid, a.timemodified AS assessmentmodified,
                   reviewer.id AS reviewerid, $reviewerfields, cm.id AS cmid
              FROM {workshop} w
        INNER JOIN {course_modules} cm ON cm.instance = w.id
        INNER JOIN {modules} md ON md.id = cm.module
        INNER JOIN {workshop_submissions} s ON s.workshopid = w.id
        INNER JOIN {user} author ON s.authorid = author.id
         LEFT JOIN {workshop_assessments} a ON a.submissionid = s.id
         LEFT JOIN {user} reviewer ON a.reviewerid = reviewer.id
             WHERE cm.course = ?
                   AND md.name = 'workshop'
                   AND s.example = 0
                   AND (s.timemodified > ? OR a.timemodified > ?)
          ORDER BY s.timemodified";

    $rs = $DB->get_recordset_sql($sql, array($course->id, $timestart, $timestart));

    $modinfo = get_fast_modinfo($course); 
    $submissions = array();     $assessments = array();     $users       = array();

    foreach ($rs as $activity) {
        if (!array_key_exists($activity->cmid, $modinfo->cms)) {
                        continue;
        }

        $cm = $modinfo->cms[$activity->cmid];
        if (!$cm->uservisible) {
            continue;
        }

                if (empty($users[$activity->authorid])) {
            $u = new stdclass();
            $users[$activity->authorid] = username_load_fields_from_object($u, $activity, 'author');
        }
        if ($activity->reviewerid and empty($users[$activity->reviewerid])) {
            $u = new stdclass();
            $users[$activity->reviewerid] = username_load_fields_from_object($u, $activity, 'reviewer');
        }

        $context = context_module::instance($cm->id);
        $groupmode = groups_get_activity_groupmode($cm, $course);

        if ($activity->submissionmodified > $timestart and empty($submissions[$activity->submissionid])) {
            $s = new stdclass();
            $s->title = $activity->submissiontitle;
            $s->authorid = $activity->authorid;
            $s->timemodified = $activity->submissionmodified;
            $s->cmid = $activity->cmid;
            if ($activity->authorid == $USER->id || has_capability('mod/workshop:viewauthornames', $context)) {
                $s->authornamevisible = true;
            } else {
                $s->authornamevisible = false;
            }

                        do {
                if ($s->authorid === $USER->id) {
                                        $submissions[$activity->submissionid] = $s;
                    break;
                }

                if (has_capability('mod/workshop:viewallsubmissions', $context)) {
                    if ($groupmode == SEPARATEGROUPS and !has_capability('moodle/site:accessallgroups', $context)) {
                        if (isguestuser()) {
                                                        break;
                        }

                                                if (!$modinfo->get_groups($cm->groupingid)) {
                            break;
                        }
                        $authorsgroups = groups_get_all_groups($course->id, $s->authorid, $cm->groupingid);
                        if (is_array($authorsgroups)) {
                            $authorsgroups = array_keys($authorsgroups);
                            $intersect = array_intersect($authorsgroups, $modinfo->get_groups($cm->groupingid));
                            if (empty($intersect)) {
                                break;
                            } else {
                                                                $submissions[$activity->submissionid] = $s;
                                break;
                            }
                        }

                    } else {
                                                $submissions[$activity->submissionid] = $s;
                    }
                }
            } while (0);
        }

        if ($activity->assessmentmodified > $timestart and empty($assessments[$activity->assessmentid])) {
            $a = new stdclass();
            $a->submissionid = $activity->submissionid;
            $a->submissiontitle = $activity->submissiontitle;
            $a->reviewerid = $activity->reviewerid;
            $a->timemodified = $activity->assessmentmodified;
            $a->cmid = $activity->cmid;
            if ($activity->reviewerid == $USER->id || has_capability('mod/workshop:viewreviewernames', $context)) {
                $a->reviewernamevisible = true;
            } else {
                $a->reviewernamevisible = false;
            }

                        do {
                if ($a->reviewerid === $USER->id) {
                                        $assessments[$activity->assessmentid] = $a;
                    break;
                }

                if (has_capability('mod/workshop:viewallassessments', $context)) {
                    if ($groupmode == SEPARATEGROUPS and !has_capability('moodle/site:accessallgroups', $context)) {
                        if (isguestuser()) {
                                                        break;
                        }

                                                if (!$modinfo->get_groups($cm->groupingid)) {
                            break;
                        }
                        $reviewersgroups = groups_get_all_groups($course->id, $a->reviewerid, $cm->groupingid);
                        if (is_array($reviewersgroups)) {
                            $reviewersgroups = array_keys($reviewersgroups);
                            $intersect = array_intersect($reviewersgroups, $modinfo->get_groups($cm->groupingid));
                            if (empty($intersect)) {
                                break;
                            } else {
                                                                $assessments[$activity->assessmentid] = $a;
                                break;
                            }
                        }

                    } else {
                                                $assessments[$activity->assessmentid] = $a;
                    }
                }
            } while (0);
        }
    }
    $rs->close();

    $shown = false;

    if (!empty($submissions)) {
        $shown = true;
        echo $OUTPUT->heading(get_string('recentsubmissions', 'workshop'), 3);
        foreach ($submissions as $id => $submission) {
            $link = new moodle_url('/mod/workshop/submission.php', array('id'=>$id, 'cmid'=>$submission->cmid));
            if ($submission->authornamevisible) {
                $author = $users[$submission->authorid];
            } else {
                $author = null;
            }
            print_recent_activity_note($submission->timemodified, $author, $submission->title, $link->out(), false, $viewfullnames);
        }
    }

    if (!empty($assessments)) {
        $shown = true;
        echo $OUTPUT->heading(get_string('recentassessments', 'workshop'), 3);
        core_collator::asort_objects_by_property($assessments, 'timemodified');
        foreach ($assessments as $id => $assessment) {
            $link = new moodle_url('/mod/workshop/assessment.php', array('asid' => $id));
            if ($assessment->reviewernamevisible) {
                $reviewer = $users[$assessment->reviewerid];
            } else {
                $reviewer = null;
            }
            print_recent_activity_note($assessment->timemodified, $reviewer, $assessment->submissiontitle, $link->out(), false, $viewfullnames);
        }
    }

    if ($shown) {
        return true;
    }

    return false;
}


function workshop_get_recent_mod_activity(&$activities, &$index, $timestart, $courseid, $cmid, $userid=0, $groupid=0) {
    global $CFG, $COURSE, $USER, $DB;

    if ($COURSE->id == $courseid) {
        $course = $COURSE;
    } else {
        $course = $DB->get_record('course', array('id'=>$courseid));
    }

    $modinfo = get_fast_modinfo($course);

    $cm = $modinfo->cms[$cmid];

    $params = array();
    if ($userid) {
        $userselect = "AND (author.id = :authorid OR reviewer.id = :reviewerid)";
        $params['authorid'] = $userid;
        $params['reviewerid'] = $userid;
    } else {
        $userselect = "";
    }

    if ($groupid) {
        $groupselect = "AND (authorgroupmembership.groupid = :authorgroupid OR reviewergroupmembership.groupid = :reviewergroupid)";
        $groupjoin   = "LEFT JOIN {groups_members} authorgroupmembership ON authorgroupmembership.userid = author.id
                        LEFT JOIN {groups_members} reviewergroupmembership ON reviewergroupmembership.userid = reviewer.id";
        $params['authorgroupid'] = $groupid;
        $params['reviewergroupid'] = $groupid;
    } else {
        $groupselect = "";
        $groupjoin   = "";
    }

    $params['cminstance'] = $cm->instance;
    $params['submissionmodified'] = $timestart;
    $params['assessmentmodified'] = $timestart;

    $authornamefields = get_all_user_name_fields(true, 'author', null, 'author');
    $reviewerfields = get_all_user_name_fields(true, 'reviewer', null, 'reviewer');

    $sql = "SELECT s.id AS submissionid, s.title AS submissiontitle, s.timemodified AS submissionmodified,
                   author.id AS authorid, $authornamefields, author.picture AS authorpicture, author.imagealt AS authorimagealt,
                   author.email AS authoremail, a.id AS assessmentid, a.timemodified AS assessmentmodified,
                   reviewer.id AS reviewerid, $reviewerfields, reviewer.picture AS reviewerpicture,
                   reviewer.imagealt AS reviewerimagealt, reviewer.email AS revieweremail
              FROM {workshop_submissions} s
        INNER JOIN {workshop} w ON s.workshopid = w.id
        INNER JOIN {user} author ON s.authorid = author.id
         LEFT JOIN {workshop_assessments} a ON a.submissionid = s.id
         LEFT JOIN {user} reviewer ON a.reviewerid = reviewer.id
        $groupjoin
             WHERE w.id = :cminstance
                   AND s.example = 0
                   $userselect $groupselect
                   AND (s.timemodified > :submissionmodified OR a.timemodified > :assessmentmodified)
          ORDER BY s.timemodified ASC, a.timemodified ASC";

    $rs = $DB->get_recordset_sql($sql, $params);

    $groupmode       = groups_get_activity_groupmode($cm, $course);
    $context         = context_module::instance($cm->id);
    $grader          = has_capability('moodle/grade:viewall', $context);
    $accessallgroups = has_capability('moodle/site:accessallgroups', $context);
    $viewauthors     = has_capability('mod/workshop:viewauthornames', $context);
    $viewreviewers   = has_capability('mod/workshop:viewreviewernames', $context);

    $submissions = array();     $assessments = array();     $users       = array();

    foreach ($rs as $activity) {

                if (empty($users[$activity->authorid])) {
            $u = new stdclass();
            $additionalfields = explode(',', user_picture::fields());
            $u = username_load_fields_from_object($u, $activity, 'author', $additionalfields);
            $users[$activity->authorid] = $u;
        }
        if ($activity->reviewerid and empty($users[$activity->reviewerid])) {
            $u = new stdclass();
            $additionalfields = explode(',', user_picture::fields());
            $u = username_load_fields_from_object($u, $activity, 'reviewer', $additionalfields);
            $users[$activity->reviewerid] = $u;
        }

        if ($activity->submissionmodified > $timestart and empty($submissions[$activity->submissionid])) {
            $s = new stdclass();
            $s->id = $activity->submissionid;
            $s->title = $activity->submissiontitle;
            $s->authorid = $activity->authorid;
            $s->timemodified = $activity->submissionmodified;
            if ($activity->authorid == $USER->id || has_capability('mod/workshop:viewauthornames', $context)) {
                $s->authornamevisible = true;
            } else {
                $s->authornamevisible = false;
            }

                        do {
                if ($s->authorid === $USER->id) {
                                        $submissions[$activity->submissionid] = $s;
                    break;
                }

                if (has_capability('mod/workshop:viewallsubmissions', $context)) {
                    if ($groupmode == SEPARATEGROUPS and !has_capability('moodle/site:accessallgroups', $context)) {
                        if (isguestuser()) {
                                                        break;
                        }

                                                if (!$modinfo->get_groups($cm->groupingid)) {
                            break;
                        }
                        $authorsgroups = groups_get_all_groups($course->id, $s->authorid, $cm->groupingid);
                        if (is_array($authorsgroups)) {
                            $authorsgroups = array_keys($authorsgroups);
                            $intersect = array_intersect($authorsgroups, $modinfo->get_groups($cm->groupingid));
                            if (empty($intersect)) {
                                break;
                            } else {
                                                                $submissions[$activity->submissionid] = $s;
                                break;
                            }
                        }

                    } else {
                                                $submissions[$activity->submissionid] = $s;
                    }
                }
            } while (0);
        }

        if ($activity->assessmentmodified > $timestart and empty($assessments[$activity->assessmentid])) {
            $a = new stdclass();
            $a->id = $activity->assessmentid;
            $a->submissionid = $activity->submissionid;
            $a->submissiontitle = $activity->submissiontitle;
            $a->reviewerid = $activity->reviewerid;
            $a->timemodified = $activity->assessmentmodified;
            if ($activity->reviewerid == $USER->id || has_capability('mod/workshop:viewreviewernames', $context)) {
                $a->reviewernamevisible = true;
            } else {
                $a->reviewernamevisible = false;
            }

                        do {
                if ($a->reviewerid === $USER->id) {
                                        $assessments[$activity->assessmentid] = $a;
                    break;
                }

                if (has_capability('mod/workshop:viewallassessments', $context)) {
                    if ($groupmode == SEPARATEGROUPS and !has_capability('moodle/site:accessallgroups', $context)) {
                        if (isguestuser()) {
                                                        break;
                        }

                                                if (!$modinfo->get_groups($cm->groupingid)) {
                            break;
                        }
                        $reviewersgroups = groups_get_all_groups($course->id, $a->reviewerid, $cm->groupingid);
                        if (is_array($reviewersgroups)) {
                            $reviewersgroups = array_keys($reviewersgroups);
                            $intersect = array_intersect($reviewersgroups, $modinfo->get_groups($cm->groupingid));
                            if (empty($intersect)) {
                                break;
                            } else {
                                                                $assessments[$activity->assessmentid] = $a;
                                break;
                            }
                        }

                    } else {
                                                $assessments[$activity->assessmentid] = $a;
                    }
                }
            } while (0);
        }
    }
    $rs->close();

    $workshopname = format_string($cm->name, true);

    if ($grader) {
        require_once($CFG->libdir.'/gradelib.php');
        $grades = grade_get_grades($courseid, 'mod', 'workshop', $cm->instance, array_keys($users));
    }

    foreach ($submissions as $submission) {
        $tmpactivity                = new stdclass();
        $tmpactivity->type          = 'workshop';
        $tmpactivity->cmid          = $cm->id;
        $tmpactivity->name          = $workshopname;
        $tmpactivity->sectionnum    = $cm->sectionnum;
        $tmpactivity->timestamp     = $submission->timemodified;
        $tmpactivity->subtype       = 'submission';
        $tmpactivity->content       = $submission;
        if ($grader) {
            $tmpactivity->grade     = $grades->items[0]->grades[$submission->authorid]->str_long_grade;
        }
        if ($submission->authornamevisible and !empty($users[$submission->authorid])) {
            $tmpactivity->user      = $users[$submission->authorid];
        }
        $activities[$index++]       = $tmpactivity;
    }

    foreach ($assessments as $assessment) {
        $tmpactivity                = new stdclass();
        $tmpactivity->type          = 'workshop';
        $tmpactivity->cmid          = $cm->id;
        $tmpactivity->name          = $workshopname;
        $tmpactivity->sectionnum    = $cm->sectionnum;
        $tmpactivity->timestamp     = $assessment->timemodified;
        $tmpactivity->subtype       = 'assessment';
        $tmpactivity->content       = $assessment;
        if ($grader) {
            $tmpactivity->grade     = $grades->items[1]->grades[$assessment->reviewerid]->str_long_grade;
        }
        if ($assessment->reviewernamevisible and !empty($users[$assessment->reviewerid])) {
            $tmpactivity->user      = $users[$assessment->reviewerid];
        }
        $activities[$index++]       = $tmpactivity;
    }
}


function workshop_print_recent_mod_activity($activity, $courseid, $detail, $modnames, $viewfullnames) {
    global $CFG, $OUTPUT;

    if (!empty($activity->user)) {
        echo html_writer::tag('div', $OUTPUT->user_picture($activity->user, array('courseid'=>$courseid)),
                array('style' => 'float: left; padding: 7px;'));
    }

    if ($activity->subtype == 'submission') {
        echo html_writer::start_tag('div', array('class'=>'submission', 'style'=>'padding: 7px; float:left;'));

        if ($detail) {
            echo html_writer::start_tag('h4', array('class'=>'workshop'));
            $url = new moodle_url('/mod/workshop/view.php', array('id'=>$activity->cmid));
            $name = s($activity->name);
            echo html_writer::empty_tag('img', array('src'=>$OUTPUT->pix_url('icon', $activity->type), 'class'=>'icon', 'alt'=>$name));
            echo ' ' . $modnames[$activity->type];
            echo html_writer::link($url, $name, array('class'=>'name', 'style'=>'margin-left: 5px'));
            echo html_writer::end_tag('h4');
        }

        echo html_writer::start_tag('div', array('class'=>'title'));
        $url = new moodle_url('/mod/workshop/submission.php', array('cmid'=>$activity->cmid, 'id'=>$activity->content->id));
        $name = s($activity->content->title);
        echo html_writer::tag('strong', html_writer::link($url, $name));
        echo html_writer::end_tag('div');

        if (!empty($activity->user)) {
            echo html_writer::start_tag('div', array('class'=>'user'));
            $url = new moodle_url('/user/view.php', array('id'=>$activity->user->id, 'course'=>$courseid));
            $name = fullname($activity->user);
            $link = html_writer::link($url, $name);
            echo get_string('submissionby', 'workshop', $link);
            echo ' - '.userdate($activity->timestamp);
            echo html_writer::end_tag('div');
        } else {
            echo html_writer::start_tag('div', array('class'=>'anonymous'));
            echo get_string('submission', 'workshop');
            echo ' - '.userdate($activity->timestamp);
            echo html_writer::end_tag('div');
        }

        echo html_writer::end_tag('div');
    }

    if ($activity->subtype == 'assessment') {
        echo html_writer::start_tag('div', array('class'=>'assessment', 'style'=>'padding: 7px; float:left;'));

        if ($detail) {
            echo html_writer::start_tag('h4', array('class'=>'workshop'));
            $url = new moodle_url('/mod/workshop/view.php', array('id'=>$activity->cmid));
            $name = s($activity->name);
            echo html_writer::empty_tag('img', array('src'=>$OUTPUT->pix_url('icon', $activity->type), 'class'=>'icon', 'alt'=>$name));
            echo ' ' . $modnames[$activity->type];
            echo html_writer::link($url, $name, array('class'=>'name', 'style'=>'margin-left: 5px'));
            echo html_writer::end_tag('h4');
        }

        echo html_writer::start_tag('div', array('class'=>'title'));
        $url = new moodle_url('/mod/workshop/assessment.php', array('asid'=>$activity->content->id));
        $name = s($activity->content->submissiontitle);
        echo html_writer::tag('em', html_writer::link($url, $name));
        echo html_writer::end_tag('div');

        if (!empty($activity->user)) {
            echo html_writer::start_tag('div', array('class'=>'user'));
            $url = new moodle_url('/user/view.php', array('id'=>$activity->user->id, 'course'=>$courseid));
            $name = fullname($activity->user);
            $link = html_writer::link($url, $name);
            echo get_string('assessmentbyfullname', 'workshop', $link);
            echo ' - '.userdate($activity->timestamp);
            echo html_writer::end_tag('div');
        } else {
            echo html_writer::start_tag('div', array('class'=>'anonymous'));
            echo get_string('assessment', 'workshop');
            echo ' - '.userdate($activity->timestamp);
            echo html_writer::end_tag('div');
        }

        echo html_writer::end_tag('div');
    }

    echo html_writer::empty_tag('br', array('style'=>'clear:both'));
}


function workshop_cron() {
    global $CFG, $DB;

    $now = time();

    mtrace(' processing workshop subplugins ...');
    cron_execute_plugin_type('workshopallocation', 'workshop allocation methods');

            $workshops = $DB->get_records_select("workshop",
        "phase = 20 AND phaseswitchassessment = 1 AND submissionend > 0 AND submissionend < ?", array($now));

    if (!empty($workshops)) {
        mtrace('Processing automatic assessment phase switch in '.count($workshops).' workshop(s) ... ', '');
        require_once($CFG->dirroot.'/mod/workshop/locallib.php');
        foreach ($workshops as $workshop) {
            $cm = get_coursemodule_from_instance('workshop', $workshop->id, $workshop->course, false, MUST_EXIST);
            $course = $DB->get_record('course', array('id' => $cm->course), '*', MUST_EXIST);
            $workshop = new workshop($workshop, $cm, $course);
            $workshop->switch_phase(workshop::PHASE_ASSESSMENT);

            $params = array(
                'objectid' => $workshop->id,
                'context' => $workshop->context,
                'courseid' => $workshop->course->id,
                'other' => array(
                    'workshopphase' => $workshop->phase
                )
            );
            $event = \mod_workshop\event\phase_switched::create($params);
            $event->trigger();

                                    $DB->set_field('workshop', 'phaseswitchassessment', 0, array('id' => $workshop->id));

                    }
        mtrace('done');
    }

    return true;
}


function workshop_scale_used($workshopid, $scaleid) {
    global $CFG; 
    $strategies = core_component::get_plugin_list('workshopform');
    foreach ($strategies as $strategy => $strategypath) {
        $strategylib = $strategypath . '/lib.php';
        if (is_readable($strategylib)) {
            require_once($strategylib);
        } else {
            throw new coding_exception('the grading forms subplugin must contain library ' . $strategylib);
        }
        $classname = 'workshop_' . $strategy . '_strategy';
        if (method_exists($classname, 'scale_used')) {
            if (call_user_func_array(array($classname, 'scale_used'), array($scaleid, $workshopid))) {
                                return true;
            }
        }
    }

    return false;
}


function workshop_scale_used_anywhere($scaleid) {
    global $CFG; 
    $strategies = core_component::get_plugin_list('workshopform');
    foreach ($strategies as $strategy => $strategypath) {
        $strategylib = $strategypath . '/lib.php';
        if (is_readable($strategylib)) {
            require_once($strategylib);
        } else {
            throw new coding_exception('the grading forms subplugin must contain library ' . $strategylib);
        }
        $classname = 'workshop_' . $strategy . '_strategy';
        if (method_exists($classname, 'scale_used')) {
            if (call_user_func(array($classname, 'scale_used'), $scaleid)) {
                                return true;
            }
        }
    }

    return false;
}


function workshop_get_extra_capabilities() {
    return array('moodle/site:accessallgroups');
}



function workshop_grade_item_update(stdclass $workshop, $submissiongrades=null, $assessmentgrades=null) {
    global $CFG;
    require_once($CFG->libdir.'/gradelib.php');

    $a = new stdclass();
    $a->workshopname = clean_param($workshop->name, PARAM_NOTAGS);

    $item = array();
    $item['itemname'] = get_string('gradeitemsubmission', 'workshop', $a);
    $item['gradetype'] = GRADE_TYPE_VALUE;
    $item['grademax']  = $workshop->grade;
    $item['grademin']  = 0;
    grade_update('mod/workshop', $workshop->course, 'mod', 'workshop', $workshop->id, 0, $submissiongrades , $item);

    $item = array();
    $item['itemname'] = get_string('gradeitemassessment', 'workshop', $a);
    $item['gradetype'] = GRADE_TYPE_VALUE;
    $item['grademax']  = $workshop->gradinggrade;
    $item['grademin']  = 0;
    grade_update('mod/workshop', $workshop->course, 'mod', 'workshop', $workshop->id, 1, $assessmentgrades, $item);
}


function workshop_update_grades(stdclass $workshop, $userid=0) {
    global $CFG, $DB;
    require_once($CFG->libdir.'/gradelib.php');

    $whereuser = $userid ? ' AND authorid = :userid' : '';
    $params = array('workshopid' => $workshop->id, 'userid' => $userid);
    $sql = 'SELECT authorid, grade, gradeover, gradeoverby, feedbackauthor, feedbackauthorformat, timemodified, timegraded
              FROM {workshop_submissions}
             WHERE workshopid = :workshopid AND example=0' . $whereuser;
    $records = $DB->get_records_sql($sql, $params);
    $submissiongrades = array();
    foreach ($records as $record) {
        $grade = new stdclass();
        $grade->userid = $record->authorid;
        if (!is_null($record->gradeover)) {
            $grade->rawgrade = grade_floatval($workshop->grade * $record->gradeover / 100);
            $grade->usermodified = $record->gradeoverby;
        } else {
            $grade->rawgrade = grade_floatval($workshop->grade * $record->grade / 100);
        }
        $grade->feedback = $record->feedbackauthor;
        $grade->feedbackformat = $record->feedbackauthorformat;
        $grade->datesubmitted = $record->timemodified;
        $grade->dategraded = $record->timegraded;
        $submissiongrades[$record->authorid] = $grade;
    }

    $whereuser = $userid ? ' AND userid = :userid' : '';
    $params = array('workshopid' => $workshop->id, 'userid' => $userid);
    $sql = 'SELECT userid, gradinggrade, timegraded
              FROM {workshop_aggregations}
             WHERE workshopid = :workshopid' . $whereuser;
    $records = $DB->get_records_sql($sql, $params);
    $assessmentgrades = array();
    foreach ($records as $record) {
        $grade = new stdclass();
        $grade->userid = $record->userid;
        $grade->rawgrade = grade_floatval($workshop->gradinggrade * $record->gradinggrade / 100);
        $grade->dategraded = $record->timegraded;
        $assessmentgrades[$record->userid] = $grade;
    }

    workshop_grade_item_update($workshop, $submissiongrades, $assessmentgrades);
}


function workshop_grade_item_category_update($workshop) {

    $gradeitems = grade_item::fetch_all(array(
        'itemtype'      => 'mod',
        'itemmodule'    => 'workshop',
        'iteminstance'  => $workshop->id,
        'courseid'      => $workshop->course));

    if (!empty($gradeitems)) {
        foreach ($gradeitems as $gradeitem) {
            if ($gradeitem->itemnumber == 0) {
                if (isset($workshop->submissiongradepass) &&
                        $gradeitem->gradepass != $workshop->submissiongradepass) {
                    $gradeitem->gradepass = $workshop->submissiongradepass;
                    $gradeitem->update();
                }
                if ($gradeitem->categoryid != $workshop->gradecategory) {
                    $gradeitem->set_parent($workshop->gradecategory);
                }
            } else if ($gradeitem->itemnumber == 1) {
                if (isset($workshop->gradinggradepass) &&
                        $gradeitem->gradepass != $workshop->gradinggradepass) {
                    $gradeitem->gradepass = $workshop->gradinggradepass;
                    $gradeitem->update();
                }
                if ($gradeitem->categoryid != $workshop->gradinggradecategory) {
                    $gradeitem->set_parent($workshop->gradinggradecategory);
                }
            }
        }
    }
}



function workshop_get_file_areas($course, $cm, $context) {
    $areas = array();
    $areas['instructauthors']          = get_string('areainstructauthors', 'workshop');
    $areas['instructreviewers']        = get_string('areainstructreviewers', 'workshop');
    $areas['submission_content']       = get_string('areasubmissioncontent', 'workshop');
    $areas['submission_attachment']    = get_string('areasubmissionattachment', 'workshop');
    $areas['conclusion']               = get_string('areaconclusion', 'workshop');
    $areas['overallfeedback_content']  = get_string('areaoverallfeedbackcontent', 'workshop');
    $areas['overallfeedback_attachment'] = get_string('areaoverallfeedbackattachment', 'workshop');

    return $areas;
}


function workshop_pluginfile($course, $cm, $context, $filearea, array $args, $forcedownload, array $options=array()) {
    global $DB, $CFG, $USER;

    if ($context->contextlevel != CONTEXT_MODULE) {
        return false;
    }

    require_login($course, true, $cm);

    if ($filearea === 'instructauthors' or $filearea === 'instructreviewers' or $filearea === 'conclusion') {
                $relativepath = implode('/', $args);
        $fullpath = "/$context->id/mod_workshop/$filearea/0/$relativepath";

        $fs = get_file_storage();
        if (!$file = $fs->get_file_by_hash(sha1($fullpath)) or $file->is_directory()) {
            send_file_not_found();
        }
        send_stored_file($file, null, 0, $forcedownload, $options);

    } else if ($filearea === 'submission_content' or $filearea === 'submission_attachment') {
        $itemid = (int)array_shift($args);
        if (!$workshop = $DB->get_record('workshop', array('id' => $cm->instance))) {
            return false;
        }
        if (!$submission = $DB->get_record('workshop_submissions', array('id' => $itemid, 'workshopid' => $workshop->id))) {
            return false;
        }

                if (empty($submission->example)) {
            if ($USER->id != $submission->authorid) {
                if ($submission->published == 1 and $workshop->phase == 50
                        and has_capability('mod/workshop:viewpublishedsubmissions', $context)) {
                                                        } else if (!$DB->record_exists('workshop_assessments', array('submissionid' => $submission->id, 'reviewerid' => $USER->id))) {
                    if (!has_capability('mod/workshop:viewallsubmissions', $context)) {
                        send_file_not_found();
                    } else {
                        $gmode = groups_get_activity_groupmode($cm, $course);
                        if ($gmode == SEPARATEGROUPS and !has_capability('moodle/site:accessallgroups', $context)) {
                                                                                    $sql = "SELECT 'x'
                                      FROM {workshop_submissions} s
                                      JOIN {user} a ON (a.id = s.authorid)
                                      JOIN {groups_members} agm ON (a.id = agm.userid)
                                      JOIN {user} u ON (u.id = ?)
                                      JOIN {groups_members} ugm ON (u.id = ugm.userid)
                                     WHERE s.example = 0 AND s.workshopid = ? AND s.id = ? AND agm.groupid = ugm.groupid";
                            $params = array($USER->id, $workshop->id, $submission->id);
                            if (!$DB->record_exists_sql($sql, $params)) {
                                send_file_not_found();
                            }
                        }
                    }
                }
            }
        }

        $fs = get_file_storage();
        $relativepath = implode('/', $args);
        $fullpath = "/$context->id/mod_workshop/$filearea/$itemid/$relativepath";
        if (!$file = $fs->get_file_by_hash(sha1($fullpath)) or $file->is_directory()) {
            return false;
        }
                        send_stored_file($file, 0, 0, true, $options);

    } else if ($filearea === 'overallfeedback_content' or $filearea === 'overallfeedback_attachment') {
        $itemid = (int)array_shift($args);
        if (!$workshop = $DB->get_record('workshop', array('id' => $cm->instance))) {
            return false;
        }
        if (!$assessment = $DB->get_record('workshop_assessments', array('id' => $itemid))) {
            return false;
        }
        if (!$submission = $DB->get_record('workshop_submissions', array('id' => $assessment->submissionid, 'workshopid' => $workshop->id))) {
            return false;
        }

        if ($USER->id == $assessment->reviewerid) {
                    } else if ($USER->id == $submission->authorid and $workshop->phase == 50) {
                    } else if (!empty($submission->example) and $assessment->weight == 1) {
                    } else if (!has_capability('mod/workshop:viewallassessments', $context)) {
            send_file_not_found();
        } else {
            $gmode = groups_get_activity_groupmode($cm, $course);
            if ($gmode == SEPARATEGROUPS and !has_capability('moodle/site:accessallgroups', $context)) {
                                                $sql = "SELECT 'x'
                          FROM {workshop_submissions} s
                          JOIN {user} a ON (a.id = s.authorid)
                          JOIN {groups_members} agm ON (a.id = agm.userid)
                          JOIN {user} u ON (u.id = ?)
                          JOIN {groups_members} ugm ON (u.id = ugm.userid)
                         WHERE s.example = 0 AND s.workshopid = ? AND s.id = ? AND agm.groupid = ugm.groupid";
                $params = array($USER->id, $workshop->id, $submission->id);
                if (!$DB->record_exists_sql($sql, $params)) {
                    send_file_not_found();
                }
            }
        }

        $fs = get_file_storage();
        $relativepath = implode('/', $args);
        $fullpath = "/$context->id/mod_workshop/$filearea/$itemid/$relativepath";
        if (!$file = $fs->get_file_by_hash(sha1($fullpath)) or $file->is_directory()) {
            return false;
        }
                        send_stored_file($file, 0, 0, true, $options);
    }

    return false;
}


function workshop_get_file_info($browser, $areas, $course, $cm, $context, $filearea, $itemid, $filepath, $filename) {
    global $CFG, $DB, $USER;

    
    static $submissionauthors = array();

    $fs = get_file_storage();

    if ($filearea === 'submission_content' or $filearea === 'submission_attachment') {

        if (!has_capability('mod/workshop:viewallsubmissions', $context)) {
            return null;
        }

        if (is_null($itemid)) {
                        require_once($CFG->dirroot . '/mod/workshop/fileinfolib.php');
            return new workshop_file_info_submissions_container($browser, $course, $cm, $context, $areas, $filearea);
        }

                $gmode = groups_get_activity_groupmode($cm, $course);

        if ($gmode == SEPARATEGROUPS and !has_capability('moodle/site:accessallgroups', $context)) {
                                                $sql = "SELECT 'x'
                      FROM {workshop_submissions} s
                      JOIN {user} a ON (a.id = s.authorid)
                      JOIN {groups_members} agm ON (a.id = agm.userid)
                      JOIN {user} u ON (u.id = ?)
                      JOIN {groups_members} ugm ON (u.id = ugm.userid)
                     WHERE s.example = 0 AND s.workshopid = ? AND s.id = ? AND agm.groupid = ugm.groupid";
            $params = array($USER->id, $cm->instance, $itemid);
            if (!$DB->record_exists_sql($sql, $params)) {
                return null;
            }
        }

        
        $filepath = is_null($filepath) ? '/' : $filepath;
        $filename = is_null($filename) ? '.' : $filename;

        if (!$storedfile = $fs->get_file($context->id, 'mod_workshop', $filearea, $itemid, $filepath, $filename)) {
            if ($filepath === '/' and $filename === '.') {
                $storedfile = new virtual_root_file($context->id, 'mod_workshop', $filearea, $itemid);
            } else {
                                return null;
            }
        }

                        if (!has_capability('moodle/course:managefiles', $context) && $storedfile->get_userid() != $USER->id) {
            return null;
        }

        
        if (isset($submissionauthors[$itemid])) {
            $topvisiblename = $submissionauthors[$itemid];

        } else {

            $sql = "SELECT s.id, u.lastname, u.firstname
                      FROM {workshop_submissions} s
                      JOIN {user} u ON (s.authorid = u.id)
                     WHERE s.example = 0 AND s.workshopid = ?";
            $params = array($cm->instance);
            $rs = $DB->get_recordset_sql($sql, $params);

            foreach ($rs as $submissionauthor) {
                $title = s(fullname($submissionauthor));                 $submissionauthors[$submissionauthor->id] = $title;
            }
            $rs->close();

            if (!isset($submissionauthors[$itemid])) {
                                return null;
            } else {
                $topvisiblename = $submissionauthors[$itemid];
            }
        }

        $urlbase = $CFG->wwwroot . '/pluginfile.php';
                return new file_info_stored($browser, $context, $storedfile, $urlbase, $topvisiblename, true, true, false, false);
    }

    if ($filearea === 'overallfeedback_content' or $filearea === 'overallfeedback_attachment') {

        if (!has_capability('mod/workshop:viewallassessments', $context)) {
            return null;
        }

        if (is_null($itemid)) {
                        require_once($CFG->dirroot . '/mod/workshop/fileinfolib.php');
            return new workshop_file_info_overallfeedback_container($browser, $course, $cm, $context, $areas, $filearea);
        }

                $gmode = groups_get_activity_groupmode($cm, $course);
        if ($gmode == SEPARATEGROUPS and !has_capability('moodle/site:accessallgroups', $context)) {
                                    $sql = "SELECT 'x'
                      FROM {workshop_submissions} s
                      JOIN {user} a ON (a.id = s.authorid)
                      JOIN {groups_members} agm ON (a.id = agm.userid)
                      JOIN {user} u ON (u.id = ?)
                      JOIN {groups_members} ugm ON (u.id = ugm.userid)
                     WHERE s.example = 0 AND s.workshopid = ? AND s.id = ? AND agm.groupid = ugm.groupid";
            $params = array($USER->id, $cm->instance, $itemid);
            if (!$DB->record_exists_sql($sql, $params)) {
                return null;
            }
        }

                $filepath = is_null($filepath) ? '/' : $filepath;
        $filename = is_null($filename) ? '.' : $filename;

        if (!$storedfile = $fs->get_file($context->id, 'mod_workshop', $filearea, $itemid, $filepath, $filename)) {
            if ($filepath === '/' and $filename === '.') {
                $storedfile = new virtual_root_file($context->id, 'mod_workshop', $filearea, $itemid);
            } else {
                                return null;
            }
        }

                if (!has_capability('moodle/course:managefiles', $context) and $storedfile->get_userid() != $USER->id) {
            return null;
        }

        $urlbase = $CFG->wwwroot . '/pluginfile.php';

                return new file_info_stored($browser, $context, $storedfile, $urlbase, $itemid, true, true, false, false);
    }

    if ($filearea == 'instructauthors' or $filearea == 'instructreviewers' or $filearea == 'conclusion') {
        
        $filepath = is_null($filepath) ? '/' : $filepath;
        $filename = is_null($filename) ? '.' : $filename;

        $urlbase = $CFG->wwwroot.'/pluginfile.php';
        if (!$storedfile = $fs->get_file($context->id, 'mod_workshop', $filearea, 0, $filepath, $filename)) {
            if ($filepath === '/' and $filename === '.') {
                $storedfile = new virtual_root_file($context->id, 'mod_workshop', $filearea, 0);
            } else {
                                return null;
            }
        }
        return new file_info_stored($browser, $context, $storedfile, $urlbase, $areas[$filearea], false, true, true, false);
    }
}



function workshop_extend_navigation(navigation_node $navref, stdclass $course, stdclass $module, cm_info $cm) {
    global $CFG;

    if (has_capability('mod/workshop:submit', context_module::instance($cm->id))) {
        $url = new moodle_url('/mod/workshop/submission.php', array('cmid' => $cm->id));
        $mysubmission = $navref->add(get_string('mysubmission', 'workshop'), $url);
        $mysubmission->mainnavonly = true;
    }
}


function workshop_extend_settings_navigation(settings_navigation $settingsnav, navigation_node $workshopnode=null) {
    global $PAGE;

    
    if (has_capability('mod/workshop:editdimensions', $PAGE->cm->context)) {
        $url = new moodle_url('/mod/workshop/editform.php', array('cmid' => $PAGE->cm->id));
        $workshopnode->add(get_string('editassessmentform', 'workshop'), $url, settings_navigation::TYPE_SETTING);
    }
    if (has_capability('mod/workshop:allocate', $PAGE->cm->context)) {
        $url = new moodle_url('/mod/workshop/allocation.php', array('cmid' => $PAGE->cm->id));
        $workshopnode->add(get_string('allocate', 'workshop'), $url, settings_navigation::TYPE_SETTING);
    }
}


function workshop_page_type_list($pagetype, $parentcontext, $currentcontext) {
    $module_pagetype = array('mod-workshop-*'=>get_string('page-mod-workshop-x', 'workshop'));
    return $module_pagetype;
}



function workshop_calendar_update(stdClass $workshop, $cmid) {
    global $DB;

        $currentevents = $DB->get_records('event', array('modulename' => 'workshop', 'instance' => $workshop->id));

        $base = new stdClass();
    $base->description  = format_module_intro('workshop', $workshop, $cmid, false);
    $base->courseid     = $workshop->course;
    $base->groupid      = 0;
    $base->userid       = 0;
    $base->modulename   = 'workshop';
    $base->eventtype    = 'pluginname';
    $base->instance     = $workshop->id;
    $base->visible      = instance_is_visible('workshop', $workshop);
    $base->timeduration = 0;

    if ($workshop->submissionstart) {
        $event = clone($base);
        $event->name = get_string('submissionstartevent', 'mod_workshop', $workshop->name);
        $event->timestart = $workshop->submissionstart;
        if ($reusedevent = array_shift($currentevents)) {
            $event->id = $reusedevent->id;
        } else {
                        unset($event->id);
        }
                $eventobj = new calendar_event($event);
        $eventobj->update($event, false);
    }

    if ($workshop->submissionend) {
        $event = clone($base);
        $event->name = get_string('submissionendevent', 'mod_workshop', $workshop->name);
        $event->timestart = $workshop->submissionend;
        if ($reusedevent = array_shift($currentevents)) {
            $event->id = $reusedevent->id;
        } else {
                        unset($event->id);
        }
                $eventobj = new calendar_event($event);
        $eventobj->update($event, false);
    }

    if ($workshop->assessmentstart) {
        $event = clone($base);
        $event->name = get_string('assessmentstartevent', 'mod_workshop', $workshop->name);
        $event->timestart = $workshop->assessmentstart;
        if ($reusedevent = array_shift($currentevents)) {
            $event->id = $reusedevent->id;
        } else {
                        unset($event->id);
        }
                $eventobj = new calendar_event($event);
        $eventobj->update($event, false);
    }

    if ($workshop->assessmentend) {
        $event = clone($base);
        $event->name = get_string('assessmentendevent', 'mod_workshop', $workshop->name);
        $event->timestart = $workshop->assessmentend;
        if ($reusedevent = array_shift($currentevents)) {
            $event->id = $reusedevent->id;
        } else {
                        unset($event->id);
        }
                $eventobj = new calendar_event($event);
        $eventobj->update($event, false);
    }

        foreach ($currentevents as $oldevent) {
        $oldevent = calendar_event::load($oldevent);
        $oldevent->delete();
    }
}



function workshop_reset_course_form_definition($mform) {

    $mform->addElement('header', 'workshopheader', get_string('modulenameplural', 'mod_workshop'));

    $mform->addElement('advcheckbox', 'reset_workshop_submissions', get_string('resetsubmissions', 'mod_workshop'));
    $mform->addHelpButton('reset_workshop_submissions', 'resetsubmissions', 'mod_workshop');

    $mform->addElement('advcheckbox', 'reset_workshop_assessments', get_string('resetassessments', 'mod_workshop'));
    $mform->addHelpButton('reset_workshop_assessments', 'resetassessments', 'mod_workshop');
    $mform->disabledIf('reset_workshop_assessments', 'reset_workshop_submissions', 'checked');

    $mform->addElement('advcheckbox', 'reset_workshop_phase', get_string('resetphase', 'mod_workshop'));
    $mform->addHelpButton('reset_workshop_phase', 'resetphase', 'mod_workshop');
}


function workshop_reset_course_form_defaults(stdClass $course) {

    $defaults = array(
        'reset_workshop_submissions'    => 1,
        'reset_workshop_assessments'    => 1,
        'reset_workshop_phase'          => 1,
    );

    return $defaults;
}


function workshop_reset_userdata(stdClass $data) {
    global $CFG, $DB;

    if (empty($data->reset_workshop_submissions)
            and empty($data->reset_workshop_assessments)
            and empty($data->reset_workshop_phase) ) {
                return array();
    }

    $workshoprecords = $DB->get_records('workshop', array('course' => $data->courseid));

    if (empty($workshoprecords)) {
                return array();
    }

    require_once($CFG->dirroot . '/mod/workshop/locallib.php');

    $course = $DB->get_record('course', array('id' => $data->courseid), '*', MUST_EXIST);
    $status = array();

    foreach ($workshoprecords as $workshoprecord) {
        $cm = get_coursemodule_from_instance('workshop', $workshoprecord->id, $course->id, false, MUST_EXIST);
        $workshop = new workshop($workshoprecord, $cm, $course);
        $status = array_merge($status, $workshop->reset_userdata($data));
    }

    return $status;
}
