<?php


defined('MOODLE_INTERNAL') || die();


function assign_add_instance(stdClass $data, mod_assign_mod_form $form = null) {
    global $CFG;
    require_once($CFG->dirroot . '/mod/assign/locallib.php');

    $assignment = new assign(context_module::instance($data->coursemodule), null, null);
    return $assignment->add_instance($data, true);
}


function assign_delete_instance($id) {
    global $CFG;
    require_once($CFG->dirroot . '/mod/assign/locallib.php');
    $cm = get_coursemodule_from_instance('assign', $id, 0, false, MUST_EXIST);
    $context = context_module::instance($cm->id);

    $assignment = new assign($context, null, null);
    return $assignment->delete_instance();
}


function assign_reset_userdata($data) {
    global $CFG, $DB;
    require_once($CFG->dirroot . '/mod/assign/locallib.php');

    $status = array();
    $params = array('courseid'=>$data->courseid);
    $sql = "SELECT a.id FROM {assign} a WHERE a.course=:courseid";
    $course = $DB->get_record('course', array('id'=>$data->courseid), '*', MUST_EXIST);
    if ($assigns = $DB->get_records_sql($sql, $params)) {
        foreach ($assigns as $assign) {
            $cm = get_coursemodule_from_instance('assign',
                                                 $assign->id,
                                                 $data->courseid,
                                                 false,
                                                 MUST_EXIST);
            $context = context_module::instance($cm->id);
            $assignment = new assign($context, $cm, $course);
            $status = array_merge($status, $assignment->reset_userdata($data));
        }
    }
    return $status;
}


function assign_refresh_events($courseid = 0) {
    global $CFG, $DB;
    require_once($CFG->dirroot . '/mod/assign/locallib.php');

    if ($courseid) {
                if (!is_numeric($courseid)) {
            return false;
        }
        if (!$assigns = $DB->get_records('assign', array('course' => $courseid))) {
            return false;
        }
                if (!$course = $DB->get_record('course', array('id' => $courseid), '*')) {
            return false;
        }
    } else {
        if (!$assigns = $DB->get_records('assign')) {
            return false;
        }
    }
    foreach ($assigns as $assign) {
                if (!$courseid) {
            $courseid = $assign->course;
            if (!$course = $DB->get_record('course', array('id' => $courseid), '*')) {
                continue;
            }
        }
        if (!$cm = get_coursemodule_from_instance('assign', $assign->id, $courseid, false)) {
            continue;
        }
        $context = context_module::instance($cm->id);
        $assignment = new assign($context, $cm, $course);
        $assignment->update_calendar($cm->id);
    }

    return true;
}


function assign_reset_gradebook($courseid, $type='') {
    global $CFG, $DB;

    $params = array('moduletype'=>'assign', 'courseid'=>$courseid);
    $sql = 'SELECT a.*, cm.idnumber as cmidnumber, a.course as courseid
            FROM {assign} a, {course_modules} cm, {modules} m
            WHERE m.name=:moduletype AND m.id=cm.module AND cm.instance=a.id AND a.course=:courseid';

    if ($assignments = $DB->get_records_sql($sql, $params)) {
        foreach ($assignments as $assignment) {
            assign_grade_item_update($assignment, 'reset');
        }
    }
}


function assign_reset_course_form_definition(&$mform) {
    $mform->addElement('header', 'assignheader', get_string('modulenameplural', 'assign'));
    $name = get_string('deleteallsubmissions', 'assign');
    $mform->addElement('advcheckbox', 'reset_assign_submissions', $name);
}


function assign_reset_course_form_defaults($course) {
    return array('reset_assign_submissions'=>1);
}


function assign_update_instance(stdClass $data, $form) {
    global $CFG;
    require_once($CFG->dirroot . '/mod/assign/locallib.php');
    $context = context_module::instance($data->coursemodule);
    $assignment = new assign($context, null, null);
    return $assignment->update_instance($data);
}


function assign_supports($feature) {
    switch($feature) {
        case FEATURE_GROUPS:
            return true;
        case FEATURE_GROUPINGS:
            return true;
        case FEATURE_MOD_INTRO:
            return true;
        case FEATURE_COMPLETION_TRACKS_VIEWS:
            return true;
        case FEATURE_COMPLETION_HAS_RULES:
            return true;
        case FEATURE_GRADE_HAS_GRADE:
            return true;
        case FEATURE_GRADE_OUTCOMES:
            return true;
        case FEATURE_BACKUP_MOODLE2:
            return true;
        case FEATURE_SHOW_DESCRIPTION:
            return true;
        case FEATURE_ADVANCED_GRADING:
            return true;
        case FEATURE_PLAGIARISM:
            return true;

        default:
            return null;
    }
}


function assign_grading_areas_list() {
    return array('submissions'=>get_string('submissions', 'assign'));
}



function assign_extend_settings_navigation(settings_navigation $settings, navigation_node $navref) {
    global $PAGE, $DB;

    $cm = $PAGE->cm;
    if (!$cm) {
        return;
    }

    $context = $cm->context;
    $course = $PAGE->course;

    if (!$course) {
        return;
    }

        if (has_capability('gradereport/grader:view', $cm->context) &&
            has_capability('moodle/grade:viewall', $cm->context)) {
        $link = new moodle_url('/grade/report/grader/index.php', array('id' => $course->id));
        $linkname = get_string('viewgradebook', 'assign');
        $node = $navref->add($linkname, $link, navigation_node::TYPE_SETTING);
    }

        if (has_any_capability(array('mod/assign:grade', 'mod/assign:viewgrades'), $context)) {
        $link = new moodle_url('/mod/assign/view.php', array('id' => $cm->id, 'action'=>'grading'));
        $node = $navref->add(get_string('viewgrading', 'assign'), $link, navigation_node::TYPE_SETTING);

        $link = new moodle_url('/mod/assign/view.php', array('id' => $cm->id, 'action'=>'downloadall'));
        $node = $navref->add(get_string('downloadall', 'assign'), $link, navigation_node::TYPE_SETTING);
    }

    if (has_capability('mod/assign:revealidentities', $context)) {
        $dbparams = array('id'=>$cm->instance);
        $assignment = $DB->get_record('assign', $dbparams, 'blindmarking, revealidentities');

        if ($assignment && $assignment->blindmarking && !$assignment->revealidentities) {
            $urlparams = array('id' => $cm->id, 'action'=>'revealidentities');
            $url = new moodle_url('/mod/assign/view.php', $urlparams);
            $linkname = get_string('revealidentities', 'assign');
            $node = $navref->add($linkname, $url, navigation_node::TYPE_SETTING);
        }
    }
}


function assign_get_coursemodule_info($coursemodule) {
    global $CFG, $DB;

    $dbparams = array('id'=>$coursemodule->instance);
    $fields = 'id, name, alwaysshowdescription, allowsubmissionsfromdate, intro, introformat';
    if (! $assignment = $DB->get_record('assign', $dbparams, $fields)) {
        return false;
    }

    $result = new cached_cm_info();
    $result->name = $assignment->name;
    if ($coursemodule->showdescription) {
        if ($assignment->alwaysshowdescription || time() > $assignment->allowsubmissionsfromdate) {
                        $result->content = format_module_intro('assign', $assignment, $coursemodule->id, false);
        }
    }
    return $result;
}


function assign_page_type_list($pagetype, $parentcontext, $currentcontext) {
    $modulepagetype = array(
        'mod-assign-*' => get_string('page-mod-assign-x', 'assign'),
        'mod-assign-view' => get_string('page-mod-assign-view', 'assign'),
    );
    return $modulepagetype;
}


function assign_print_overview($courses, &$htmlarray) {
    global $CFG, $DB;

    if (empty($courses) || !is_array($courses) || count($courses) == 0) {
        return true;
    }

    if (!$assignments = get_all_instances_in_courses('assign', $courses)) {
        return true;
    }

    $assignmentids = array();

        foreach ($assignments as $key => $assignment) {
        $time = time();
        $isopen = false;
        if ($assignment->duedate) {
            $duedate = false;
            if ($assignment->cutoffdate) {
                $duedate = $assignment->cutoffdate;
            }
            if ($duedate) {
                $isopen = ($assignment->allowsubmissionsfromdate <= $time && $time <= $duedate);
            } else {
                $isopen = ($assignment->allowsubmissionsfromdate <= $time);
            }
        }
        if ($isopen) {
            $assignmentids[] = $assignment->id;
        }
    }

    if (empty($assignmentids)) {
                return true;
    }

        require_once($CFG->dirroot . '/mod/assign/locallib.php');

    $strduedate = get_string('duedate', 'assign');
    $strcutoffdate = get_string('nosubmissionsacceptedafter', 'assign');
    $strnolatesubmissions = get_string('nolatesubmissions', 'assign');
    $strduedateno = get_string('duedateno', 'assign');
    $strassignment = get_string('modulename', 'assign');

        list($sqlassignmentids, $assignmentidparams) = $DB->get_in_or_equal($assignmentids);

    $mysubmissions = null;
    $unmarkedsubmissions = null;

    foreach ($assignments as $assignment) {

                if (!in_array($assignment->id, $assignmentids)) {
            continue;
        }

        $context = context_module::instance($assignment->coursemodule);

                if (has_capability('mod/assign:submit', $context, null, false)) {
                        $submitdetails = assign_get_mysubmission_details_for_print_overview($mysubmissions, $sqlassignmentids,
                    $assignmentidparams, $assignment);
        } else {
            $submitdetails = false;
        }

        if (has_capability('mod/assign:grade', $context, null, false)) {
                        $gradedetails = assign_get_grade_details_for_print_overview($unmarkedsubmissions, $sqlassignmentids,
                    $assignmentidparams, $assignment, $context);
        } else {
            $gradedetails = false;
        }

        if (empty($submitdetails) && empty($gradedetails)) {
                        continue;
        }

        $dimmedclass = '';
        if (!$assignment->visible) {
            $dimmedclass = ' class="dimmed"';
        }
        $href = $CFG->wwwroot . '/mod/assign/view.php?id=' . $assignment->coursemodule;
        $basestr = '<div class="assign overview">' .
               '<div class="name">' .
               $strassignment . ': '.
               '<a ' . $dimmedclass .
                   'title="' . $strassignment . '" ' .
                   'href="' . $href . '">' .
               format_string($assignment->name) .
               '</a></div>';
        if ($assignment->duedate) {
            $userdate = userdate($assignment->duedate);
            $basestr .= '<div class="info">' . $strduedate . ': ' . $userdate . '</div>';
        } else {
            $basestr .= '<div class="info">' . $strduedateno . '</div>';
        }
        if ($assignment->cutoffdate) {
            if ($assignment->cutoffdate == $assignment->duedate) {
                $basestr .= '<div class="info">' . $strnolatesubmissions . '</div>';
            } else {
                $userdate = userdate($assignment->cutoffdate);
                $basestr .= '<div class="info">' . $strcutoffdate . ': ' . $userdate . '</div>';
            }
        }

                if (!empty($submitdetails)) {
            $basestr .= $submitdetails;
        }

        if (!empty($gradedetails)) {
            $basestr .= $gradedetails;
        }
        $basestr .= '</div>';

        if (empty($htmlarray[$assignment->course]['assign'])) {
            $htmlarray[$assignment->course]['assign'] = $basestr;
        } else {
            $htmlarray[$assignment->course]['assign'] .= $basestr;
        }
    }
    return true;
}


function assign_get_mysubmission_details_for_print_overview(&$mysubmissions, $sqlassignmentids, $assignmentidparams,
                                                            $assignment) {
    global $USER, $DB;

    if ($assignment->nosubmissions) {
                return false;
    }

    $strnotsubmittedyet = get_string('notsubmittedyet', 'assign');

    if (!isset($mysubmissions)) {

                $dbparams = array_merge(array($USER->id), $assignmentidparams, array($USER->id));
        $mysubmissions = $DB->get_records_sql('SELECT a.id AS assignment,
                                                      a.nosubmissions AS nosubmissions,
                                                      g.timemodified AS timemarked,
                                                      g.grader AS grader,
                                                      g.grade AS grade,
                                                      s.status AS status
                                                 FROM {assign} a, {assign_submission} s
                                            LEFT JOIN {assign_grades} g ON
                                                      g.assignment = s.assignment AND
                                                      g.userid = ? AND
                                                      g.attemptnumber = s.attemptnumber
                                                WHERE a.id ' . $sqlassignmentids . ' AND
                                                      s.latest = 1 AND
                                                      s.assignment = a.id AND
                                                      s.userid = ?', $dbparams);
    }

    $submitdetails = '';
    $submitdetails .= '<div class="details">';
    $submitdetails .= get_string('mysubmission', 'assign');
    $submission = false;

    if (isset($mysubmissions[$assignment->id])) {
        $submission = $mysubmissions[$assignment->id];
    }

    if ($submission && $submission->status == ASSIGN_SUBMISSION_STATUS_SUBMITTED) {
                return false;
    }

        if (!$submission ||
        !$submission->status ||
        $submission->status == ASSIGN_SUBMISSION_STATUS_DRAFT ||
        $submission->status == ASSIGN_SUBMISSION_STATUS_NEW
    ) {
        $submitdetails .= $strnotsubmittedyet;
    } else {
        $submitdetails .= get_string('submissionstatus_' . $submission->status, 'assign');
    }
    if ($assignment->markingworkflow) {
        $workflowstate = $DB->get_field('assign_user_flags', 'workflowstate', array('assignment' =>
                $assignment->id, 'userid' => $USER->id));
        if ($workflowstate) {
            $gradingstatus = 'markingworkflowstate' . $workflowstate;
        } else {
            $gradingstatus = 'markingworkflowstate' . ASSIGN_MARKING_WORKFLOW_STATE_NOTMARKED;
        }
    } else if (!empty($submission->grade) && $submission->grade !== null && $submission->grade >= 0) {
        $gradingstatus = ASSIGN_GRADING_STATUS_GRADED;
    } else {
        $gradingstatus = ASSIGN_GRADING_STATUS_NOT_GRADED;
    }
    $submitdetails .= ', ' . get_string($gradingstatus, 'assign');
    $submitdetails .= '</div>';
    return $submitdetails;
}


function assign_get_grade_details_for_print_overview(&$unmarkedsubmissions, $sqlassignmentids, $assignmentidparams,
                                                     $assignment, $context) {
    global $DB;
    if (!isset($unmarkedsubmissions)) {
                        $dbparams = array_merge(array(ASSIGN_SUBMISSION_STATUS_SUBMITTED), $assignmentidparams);
        $rs = $DB->get_recordset_sql('SELECT s.assignment as assignment,
                                             s.userid as userid,
                                             s.id as id,
                                             s.status as status,
                                             g.timemodified as timegraded
                                        FROM {assign_submission} s
                                   LEFT JOIN {assign_grades} g ON
                                             s.userid = g.userid AND
                                             s.assignment = g.assignment AND
                                             g.attemptnumber = s.attemptnumber
                                       WHERE
                                             ( g.timemodified is NULL OR
                                             s.timemodified > g.timemodified OR
                                             g.grade IS NULL ) AND
                                             s.timemodified IS NOT NULL AND
                                             s.status = ? AND
                                             s.latest = 1 AND
                                             s.assignment ' . $sqlassignmentids, $dbparams);

        $unmarkedsubmissions = array();
        foreach ($rs as $rd) {
            $unmarkedsubmissions[$rd->assignment][$rd->userid] = $rd->id;
        }
        $rs->close();
    }

        $submissions = 0;
    if ($students = get_enrolled_users($context, 'mod/assign:view', 0, 'u.id')) {
        foreach ($students as $student) {
            if (isset($unmarkedsubmissions[$assignment->id][$student->id])) {
                $submissions++;
            }
        }
    }

    if ($submissions) {
        $urlparams = array('id' => $assignment->coursemodule, 'action' => 'grading');
        $url = new moodle_url('/mod/assign/view.php', $urlparams);
        $gradedetails = '<div class="details">' .
                '<a href="' . $url . '">' .
                get_string('submissionsnotgraded', 'assign', $submissions) .
                '</a></div>';
        return $gradedetails;
    } else {
        return false;
    }

}


function assign_print_recent_activity($course, $viewfullnames, $timestart) {
    global $CFG, $USER, $DB, $OUTPUT;
    require_once($CFG->dirroot . '/mod/assign/locallib.php');

    
    $dbparams = array($timestart, $course->id, 'assign', ASSIGN_SUBMISSION_STATUS_SUBMITTED);
    $namefields = user_picture::fields('u', null, 'userid');
    if (!$submissions = $DB->get_records_sql("SELECT asb.id, asb.timemodified, cm.id AS cmid, um.id as recordid,
                                                     $namefields
                                                FROM {assign_submission} asb
                                                     JOIN {assign} a      ON a.id = asb.assignment
                                                     JOIN {course_modules} cm ON cm.instance = a.id
                                                     JOIN {modules} md        ON md.id = cm.module
                                                     JOIN {user} u            ON u.id = asb.userid
                                                LEFT JOIN {assign_user_mapping} um ON um.userid = u.id AND um.assignment = a.id
                                               WHERE asb.timemodified > ? AND
                                                     asb.latest = 1 AND
                                                     a.course = ? AND
                                                     md.name = ? AND
                                                     asb.status = ?
                                            ORDER BY asb.timemodified ASC", $dbparams)) {
         return false;
    }

    $modinfo = get_fast_modinfo($course);
    $show    = array();
    $grader  = array();

    $showrecentsubmissions = get_config('assign', 'showrecentsubmissions');

    foreach ($submissions as $submission) {
        if (!array_key_exists($submission->cmid, $modinfo->get_cms())) {
            continue;
        }
        $cm = $modinfo->get_cm($submission->cmid);
        if (!$cm->uservisible) {
            continue;
        }
        if ($submission->userid == $USER->id) {
            $show[] = $submission;
            continue;
        }

        $context = context_module::instance($submission->cmid);
                        if (empty($showrecentsubmissions)) {
            if (!array_key_exists($cm->id, $grader)) {
                $grader[$cm->id] = has_capability('moodle/grade:viewall', $context);
            }
            if (!$grader[$cm->id]) {
                continue;
            }
        }

        $groupmode = groups_get_activity_groupmode($cm, $course);

        if ($groupmode == SEPARATEGROUPS &&
                !has_capability('moodle/site:accessallgroups',  $context)) {
            if (isguestuser()) {
                                continue;
            }

                        if (!$modinfo->get_groups($cm->groupingid)) {
                continue;
            }
            $usersgroups =  groups_get_all_groups($course->id, $submission->userid, $cm->groupingid);
            if (is_array($usersgroups)) {
                $usersgroups = array_keys($usersgroups);
                $intersect = array_intersect($usersgroups, $modinfo->get_groups($cm->groupingid));
                if (empty($intersect)) {
                    continue;
                }
            }
        }
        $show[] = $submission;
    }

    if (empty($show)) {
        return false;
    }

    echo $OUTPUT->heading(get_string('newsubmissions', 'assign').':', 3);

    foreach ($show as $submission) {
        $cm = $modinfo->get_cm($submission->cmid);
        $context = context_module::instance($submission->cmid);
        $assign = new assign($context, $cm, $cm->course);
        $link = $CFG->wwwroot.'/mod/assign/view.php?id='.$cm->id;
                if ($assign->is_blind_marking()) {
            $submission->firstname = get_string('participant', 'mod_assign');
            if (empty($submission->recordid)) {
                $submission->recordid = $assign->get_uniqueid_for_user($submission->userid);
            }
            $submission->lastname = $submission->recordid;
        }
        print_recent_activity_note($submission->timemodified,
                                   $submission,
                                   $cm->name,
                                   $link,
                                   false,
                                   $viewfullnames);
    }

    return true;
}


function assign_get_recent_mod_activity(&$activities,
                                        &$index,
                                        $timestart,
                                        $courseid,
                                        $cmid,
                                        $userid=0,
                                        $groupid=0) {
    global $CFG, $COURSE, $USER, $DB;

    require_once($CFG->dirroot . '/mod/assign/locallib.php');

    if ($COURSE->id == $courseid) {
        $course = $COURSE;
    } else {
        $course = $DB->get_record('course', array('id'=>$courseid));
    }

    $modinfo = get_fast_modinfo($course);

    $cm = $modinfo->get_cm($cmid);
    $params = array();
    if ($userid) {
        $userselect = 'AND u.id = :userid';
        $params['userid'] = $userid;
    } else {
        $userselect = '';
    }

    if ($groupid) {
        $groupselect = 'AND gm.groupid = :groupid';
        $groupjoin   = 'JOIN {groups_members} gm ON  gm.userid=u.id';
        $params['groupid'] = $groupid;
    } else {
        $groupselect = '';
        $groupjoin   = '';
    }

    $params['cminstance'] = $cm->instance;
    $params['timestart'] = $timestart;
    $params['submitted'] = ASSIGN_SUBMISSION_STATUS_SUBMITTED;

    $userfields = user_picture::fields('u', null, 'userid');

    if (!$submissions = $DB->get_records_sql('SELECT asb.id, asb.timemodified, ' .
                                                     $userfields .
                                             '  FROM {assign_submission} asb
                                                JOIN {assign} a ON a.id = asb.assignment
                                                JOIN {user} u ON u.id = asb.userid ' .
                                          $groupjoin .
                                            '  WHERE asb.timemodified > :timestart AND
                                                     asb.status = :submitted AND
                                                     a.id = :cminstance
                                                     ' . $userselect . ' ' . $groupselect .
                                            ' ORDER BY asb.timemodified ASC', $params)) {
         return;
    }

    $groupmode       = groups_get_activity_groupmode($cm, $course);
    $cmcontext      = context_module::instance($cm->id);
    $grader          = has_capability('moodle/grade:viewall', $cmcontext);
    $accessallgroups = has_capability('moodle/site:accessallgroups', $cmcontext);
    $viewfullnames   = has_capability('moodle/site:viewfullnames', $cmcontext);


    $showrecentsubmissions = get_config('assign', 'showrecentsubmissions');
    $show = array();
    foreach ($submissions as $submission) {
        if ($submission->userid == $USER->id) {
            $show[] = $submission;
            continue;
        }
                        if (empty($showrecentsubmissions)) {
            if (!$grader) {
                continue;
            }
        }

        if ($groupmode == SEPARATEGROUPS and !$accessallgroups) {
            if (isguestuser()) {
                                continue;
            }

                        if (!$modinfo->get_groups($cm->groupingid)) {
                continue;
            }
            $usersgroups =  groups_get_all_groups($course->id, $submission->userid, $cm->groupingid);
            if (is_array($usersgroups)) {
                $usersgroups = array_keys($usersgroups);
                $intersect = array_intersect($usersgroups, $modinfo->get_groups($cm->groupingid));
                if (empty($intersect)) {
                    continue;
                }
            }
        }
        $show[] = $submission;
    }

    if (empty($show)) {
        return;
    }

    if ($grader) {
        require_once($CFG->libdir.'/gradelib.php');
        $userids = array();
        foreach ($show as $id => $submission) {
            $userids[] = $submission->userid;
        }
        $grades = grade_get_grades($courseid, 'mod', 'assign', $cm->instance, $userids);
    }

    $aname = format_string($cm->name, true);
    foreach ($show as $submission) {
        $activity = new stdClass();

        $activity->type         = 'assign';
        $activity->cmid         = $cm->id;
        $activity->name         = $aname;
        $activity->sectionnum   = $cm->sectionnum;
        $activity->timestamp    = $submission->timemodified;
        $activity->user         = new stdClass();
        if ($grader) {
            $activity->grade = $grades->items[0]->grades[$submission->userid]->str_long_grade;
        }

        $userfields = explode(',', user_picture::fields());
        foreach ($userfields as $userfield) {
            if ($userfield == 'id') {
                                $activity->user->{$userfield} = $submission->userid;
            } else {
                $activity->user->{$userfield} = $submission->{$userfield};
            }
        }
        $activity->user->fullname = fullname($submission, $viewfullnames);

        $activities[$index++] = $activity;
    }

    return;
}


function assign_print_recent_mod_activity($activity, $courseid, $detail, $modnames) {
    global $CFG, $OUTPUT;

    echo '<table border="0" cellpadding="3" cellspacing="0" class="assignment-recent">';

    echo '<tr><td class="userpicture" valign="top">';
    echo $OUTPUT->user_picture($activity->user);
    echo '</td><td>';

    if ($detail) {
        $modname = $modnames[$activity->type];
        echo '<div class="title">';
        echo '<img src="' . $OUTPUT->pix_url('icon', 'assign') . '" '.
             'class="icon" alt="' . $modname . '">';
        echo '<a href="' . $CFG->wwwroot . '/mod/assign/view.php?id=' . $activity->cmid . '">';
        echo $activity->name;
        echo '</a>';
        echo '</div>';
    }

    if (isset($activity->grade)) {
        echo '<div class="grade">';
        echo get_string('grade').': ';
        echo $activity->grade;
        echo '</div>';
    }

    echo '<div class="user">';
    echo "<a href=\"$CFG->wwwroot/user/view.php?id={$activity->user->id}&amp;course=$courseid\">";
    echo "{$activity->user->fullname}</a>  - " . userdate($activity->timestamp);
    echo '</div>';

    echo '</td></tr></table>';
}


function assign_scale_used($assignmentid, $scaleid) {
    global $DB;

    $return = false;
    $rec = $DB->get_record('assign', array('id'=>$assignmentid, 'grade'=>-$scaleid));

    if (!empty($rec) && !empty($scaleid)) {
        $return = true;
    }

    return $return;
}


function assign_scale_used_anywhere($scaleid) {
    global $DB;

    if ($scaleid and $DB->record_exists('assign', array('grade'=>-$scaleid))) {
        return true;
    } else {
        return false;
    }
}


function assign_get_view_actions() {
    return array('view submission', 'view feedback');
}


function assign_get_post_actions() {
    return array('upload', 'submit', 'submit for grading');
}


function assign_cron() {
    global $CFG;

    require_once($CFG->dirroot . '/mod/assign/locallib.php');
    assign::cron();

    $plugins = core_component::get_plugin_list('assignsubmission');

    foreach ($plugins as $name => $plugin) {
        $disabled = get_config('assignsubmission_' . $name, 'disabled');
        if (!$disabled) {
            $class = 'assign_submission_' . $name;
            require_once($CFG->dirroot . '/mod/assign/submission/' . $name . '/locallib.php');
            $class::cron();
        }
    }
    $plugins = core_component::get_plugin_list('assignfeedback');

    foreach ($plugins as $name => $plugin) {
        $disabled = get_config('assignfeedback_' . $name, 'disabled');
        if (!$disabled) {
            $class = 'assign_feedback_' . $name;
            require_once($CFG->dirroot . '/mod/assign/feedback/' . $name . '/locallib.php');
            $class::cron();
        }
    }

    return true;
}


function assign_get_extra_capabilities() {
    return array('gradereport/grader:view',
                 'moodle/grade:viewall',
                 'moodle/site:viewfullnames',
                 'moodle/site:config');
}


function assign_grade_item_update($assign, $grades=null) {
    global $CFG;
    require_once($CFG->libdir.'/gradelib.php');

    if (!isset($assign->courseid)) {
        $assign->courseid = $assign->course;
    }

    $params = array('itemname'=>$assign->name, 'idnumber'=>$assign->cmidnumber);

            $gradefeedbackenabled = false;

    if (isset($assign->gradefeedbackenabled)) {
        $gradefeedbackenabled = $assign->gradefeedbackenabled;
    } else if ($assign->grade == 0) {         require_once($CFG->dirroot . '/mod/assign/locallib.php');
        $mod = get_coursemodule_from_instance('assign', $assign->id, $assign->courseid);
        $cm = context_module::instance($mod->id);
        $assignment = new assign($cm, null, null);
        $gradefeedbackenabled = $assignment->is_gradebook_feedback_enabled();
    }

    if ($assign->grade > 0) {
        $params['gradetype'] = GRADE_TYPE_VALUE;
        $params['grademax']  = $assign->grade;
        $params['grademin']  = 0;

    } else if ($assign->grade < 0) {
        $params['gradetype'] = GRADE_TYPE_SCALE;
        $params['scaleid']   = -$assign->grade;

    } else if ($gradefeedbackenabled) {
                $params['gradetype'] = GRADE_TYPE_TEXT;
    } else {
                $params['gradetype'] = GRADE_TYPE_NONE;
    }

    if ($grades  === 'reset') {
        $params['reset'] = true;
        $grades = null;
    }

    return grade_update('mod/assign',
                        $assign->courseid,
                        'mod',
                        'assign',
                        $assign->id,
                        0,
                        $grades,
                        $params);
}


function assign_get_user_grades($assign, $userid=0) {
    global $CFG;

    require_once($CFG->dirroot . '/mod/assign/locallib.php');

    $cm = get_coursemodule_from_instance('assign', $assign->id, 0, false, MUST_EXIST);
    $context = context_module::instance($cm->id);
    $assignment = new assign($context, null, null);
    $assignment->set_instance($assign);
    return $assignment->get_user_grades_for_gradebook($userid);
}


function assign_update_grades($assign, $userid=0, $nullifnone=true) {
    global $CFG;
    require_once($CFG->libdir.'/gradelib.php');

    if ($assign->grade == 0) {
        assign_grade_item_update($assign);

    } else if ($grades = assign_get_user_grades($assign, $userid)) {
        foreach ($grades as $k => $v) {
            if ($v->rawgrade == -1) {
                $grades[$k]->rawgrade = null;
            }
        }
        assign_grade_item_update($assign, $grades);

    } else {
        assign_grade_item_update($assign);
    }
}


function assign_get_file_areas($course, $cm, $context) {
    global $CFG;
    require_once($CFG->dirroot . '/mod/assign/locallib.php');

    $areas = array(ASSIGN_INTROATTACHMENT_FILEAREA => get_string('introattachments', 'mod_assign'));

    $assignment = new assign($context, $cm, $course);
    foreach ($assignment->get_submission_plugins() as $plugin) {
        if ($plugin->is_visible()) {
            $pluginareas = $plugin->get_file_areas();

            if ($pluginareas) {
                $areas = array_merge($areas, $pluginareas);
            }
        }
    }
    foreach ($assignment->get_feedback_plugins() as $plugin) {
        if ($plugin->is_visible()) {
            $pluginareas = $plugin->get_file_areas();

            if ($pluginareas) {
                $areas = array_merge($areas, $pluginareas);
            }
        }
    }

    return $areas;
}


function assign_get_file_info($browser,
                              $areas,
                              $course,
                              $cm,
                              $context,
                              $filearea,
                              $itemid,
                              $filepath,
                              $filename) {
    global $CFG;
    require_once($CFG->dirroot . '/mod/assign/locallib.php');

    if ($context->contextlevel != CONTEXT_MODULE) {
        return null;
    }

    $urlbase = $CFG->wwwroot.'/pluginfile.php';
    $fs = get_file_storage();
    $filepath = is_null($filepath) ? '/' : $filepath;
    $filename = is_null($filename) ? '.' : $filename;

        $assignment = new assign($context, $cm, $course);
    if ($filearea === ASSIGN_INTROATTACHMENT_FILEAREA) {
        if (!has_capability('moodle/course:managefiles', $context)) {
                        return null;
        }
        if (!($storedfile = $fs->get_file($assignment->get_context()->id,
                                          'mod_assign', $filearea, 0, $filepath, $filename))) {
            return null;
        }
        return new file_info_stored($browser,
                        $assignment->get_context(),
                        $storedfile,
                        $urlbase,
                        $filearea,
                        $itemid,
                        true,
                        true,
                        false);
    }

    $pluginowner = null;
    foreach ($assignment->get_submission_plugins() as $plugin) {
        if ($plugin->is_visible()) {
            $pluginareas = $plugin->get_file_areas();

            if (array_key_exists($filearea, $pluginareas)) {
                $pluginowner = $plugin;
                break;
            }
        }
    }
    if (!$pluginowner) {
        foreach ($assignment->get_feedback_plugins() as $plugin) {
            if ($plugin->is_visible()) {
                $pluginareas = $plugin->get_file_areas();

                if (array_key_exists($filearea, $pluginareas)) {
                    $pluginowner = $plugin;
                    break;
                }
            }
        }
    }

    if (!$pluginowner) {
        return null;
    }

    $result = $pluginowner->get_file_info($browser, $filearea, $itemid, $filepath, $filename);
    return $result;
}


function assign_user_complete($course, $user, $coursemodule, $assign) {
    global $CFG;
    require_once($CFG->dirroot . '/mod/assign/locallib.php');

    $context = context_module::instance($coursemodule->id);

    $assignment = new assign($context, $coursemodule, $course);

    echo $assignment->view_student_summary($user, false);
}


function assign_rescale_activity_grades($course, $cm, $oldmin, $oldmax, $newmin, $newmax) {
    global $DB;

    if ($oldmax <= $oldmin) {
                return false;
    }
    $scale = ($newmax - $newmin) / ($oldmax - $oldmin);
    if (($newmax - $newmin) <= 1) {
                return false;
    }

    $params = array(
        'p1' => $oldmin,
        'p2' => $scale,
        'p3' => $newmin,
        'a' => $cm->instance
    );

    $sql = 'UPDATE {assign_grades} set grade = (((grade - :p1) * :p2) + :p3) where assignment = :a';
    $dbupdate = $DB->execute($sql, $params);
    if (!$dbupdate) {
        return false;
    }

        $dbparams = array('id' => $cm->instance);
    $assign = $DB->get_record('assign', $dbparams);
    $assign->cmidnumber = $cm->idnumber;

    assign_update_grades($assign);

    return true;
}


function assign_user_outline($course, $user, $coursemodule, $assignment) {
    global $CFG;
    require_once($CFG->libdir.'/gradelib.php');
    require_once($CFG->dirroot.'/grade/grading/lib.php');

    $gradinginfo = grade_get_grades($course->id,
                                        'mod',
                                        'assign',
                                        $assignment->id,
                                        $user->id);

    $gradingitem = $gradinginfo->items[0];
    $gradebookgrade = $gradingitem->grades[$user->id];

    if (empty($gradebookgrade->str_long_grade)) {
        return null;
    }
    $result = new stdClass();
    $result->info = get_string('outlinegrade', 'assign', $gradebookgrade->str_long_grade);
    $result->time = $gradebookgrade->dategraded;

    return $result;
}


function assign_get_completion_state($course, $cm, $userid, $type) {
    global $CFG, $DB;
    require_once($CFG->dirroot . '/mod/assign/locallib.php');

    $assign = new assign(null, $cm, $course);

        if ($assign->get_instance()->completionsubmit) {
        if ($assign->get_instance()->teamsubmission) {
            $submission = $assign->get_group_submission($userid, 0, false);
        } else {
            $submission = $assign->get_user_submission($userid, false);
        }
        return $submission && $submission->status == ASSIGN_SUBMISSION_STATUS_SUBMITTED;
    } else {
                return $type;
    }
}


function assign_pluginfile($course,
                $cm,
                context $context,
                $filearea,
                $args,
                $forcedownload,
                array $options=array()) {
    global $CFG;

    if ($context->contextlevel != CONTEXT_MODULE) {
        return false;
    }

    require_login($course, false, $cm);
    if (!has_capability('mod/assign:view', $context)) {
        return false;
    }

    require_once($CFG->dirroot . '/mod/assign/locallib.php');
    $assign = new assign($context, $cm, $course);

    if ($filearea !== ASSIGN_INTROATTACHMENT_FILEAREA) {
        return false;
    }
    if (!$assign->show_intro()) {
        return false;
    }

    $itemid = (int)array_shift($args);
    if ($itemid != 0) {
        return false;
    }

    $relativepath = implode('/', $args);

    $fullpath = "/{$context->id}/mod_assign/$filearea/$itemid/$relativepath";

    $fs = get_file_storage();
    if (!$file = $fs->get_file_by_hash(sha1($fullpath)) or $file->is_directory()) {
        return false;
    }
    send_stored_file($file, 0, 0, $forcedownload, $options);
}


function mod_assign_output_fragment_gradingpanel($args) {
    global $CFG;

    $context = $args['context'];

    if ($context->contextlevel != CONTEXT_MODULE) {
        return null;
    }
    require_once($CFG->dirroot . '/mod/assign/locallib.php');
    $assign = new assign($context, null, null);

    $userid = clean_param($args['userid'], PARAM_INT);
    $attemptnumber = clean_param($args['attemptnumber'], PARAM_INT);
    $formdata = array();
    if (!empty($args['jsonformdata'])) {
        $serialiseddata = json_decode($args['jsonformdata']);
        parse_str($serialiseddata, $formdata);
    }
    $viewargs = array(
        'userid' => $userid,
        'attemptnumber' => $attemptnumber,
        'formdata' => $formdata
    );

    return $assign->view('gradingpanel', $viewargs);
}
