<?php



require('../../config.php');
require_once($CFG->dirroot.'/report/completion/lib.php');
require_once($CFG->libdir.'/completionlib.php');

$userid   = required_param('id', PARAM_INT);
$courseid = required_param('course', PARAM_INT);

$user = $DB->get_record('user', array('id'=>$userid, 'deleted'=>0), '*', MUST_EXIST);
$course = $DB->get_record('course', array('id'=>$courseid), '*', MUST_EXIST);

$coursecontext   = context_course::instance($course->id);
$personalcontext = context_user::instance($user->id);

if ($USER->id != $user->id and has_capability('moodle/user:viewuseractivitiesreport', $personalcontext)
        and !is_enrolled($coursecontext, $USER) and is_enrolled($coursecontext, $user)) {
        require_login();
    $PAGE->set_course($course);
} else {
    require_login($course);
}

if (!report_completion_can_access_user_report($user, $course, true)) {
        print_error('nocapability', 'report_completion');
}

$stractivityreport = get_string('activityreport');

$PAGE->set_pagelayout('admin');
$PAGE->set_url('/report/completion/user.php', array('id'=>$user->id, 'course'=>$course->id));
$PAGE->navigation->extend_for_user($user);
$PAGE->navigation->set_userid_for_parent_checks($user->id); $PAGE->set_title("$course->shortname: $stractivityreport");
$PAGE->set_heading($course->fullname);
echo $OUTPUT->header();



$sql = "
    SELECT DISTINCT
        c.id AS id
    FROM
        {course} c
    INNER JOIN
        {context} con
     ON con.instanceid = c.id
    INNER JOIN
        {role_assignments} ra
     ON ra.contextid = con.id
    INNER JOIN
        {enrol} e
     ON c.id = e.courseid
    INNER JOIN
        {user_enrolments} ue
     ON e.id = ue.enrolid AND ra.userid = ue.userid
    AND ra.userid = {$user->id}
";

if ($roles = $CFG->gradebookroles) {
    $sql .= '
        AND ra.roleid IN ('.$roles.')
    ';
}

$sql .= '
    WHERE
        con.contextlevel = '.CONTEXT_COURSE.'
    AND c.enablecompletion = 1
';


if ($course->id != 1) {
    $sql .= '
        AND c.id = '.(int)$course->id.'
    ';
}

$rs = $DB->get_recordset_sql($sql);
if (!$rs->valid()) {

    if ($course->id != 1) {
        $error = get_string('nocompletions', 'report_completion');     } else {
        $error = get_string('nocompletioncoursesenroled', 'report_completion');     }

    echo $OUTPUT->notification($error);
    $rs->close();     echo $OUTPUT->footer();
    die();
}

$courses = array(
    'inprogress'    => array(),
    'complete'      => array(),
    'unstarted'     => array()
);

foreach ($rs as $course_completion) {
    $c_info = new completion_info((object)$course_completion);

        $coursecomplete = $c_info->is_course_complete($user->id);

        $criteriacomplete = $c_info->count_course_user_data($user->id);

    if ($coursecomplete) {
        $courses['complete'][] = $c_info;
    } else if ($criteriacomplete) {
        $courses['inprogress'][] = $c_info;
    } else {
        $courses['unstarted'][] = $c_info;
    }
}
$rs->close(); 
foreach ($courses as $type => $infos) {

        if (!empty($infos)) {

        echo '<h1 align="center">'.get_string($type, 'report_completion').'</h1>';
        echo '<table class="generaltable boxaligncenter">';
        echo '<tr class="ccheader">';
        echo '<th class="c0 header" scope="col">'.get_string('course').'</th>';
        echo '<th class="c1 header" scope="col">'.get_string('requiredcriteria', 'completion').'</th>';
        echo '<th class="c2 header" scope="col">'.get_string('status').'</th>';
        echo '<th class="c3 header" scope="col" width="15%">'.get_string('info').'</th>';

        if ($type === 'complete') {
            echo '<th class="c4 header" scope="col">'.get_string('completiondate', 'report_completion').'</th>';
        }

        echo '</tr>';

                foreach ($infos as $c_info) {

                        $c_course = $DB->get_record('course', array('id' => $c_info->course_id));
            $course_context = context_course::instance($c_course->id, MUST_EXIST);
            $course_name = format_string($c_course->fullname, true, array('context' => $course_context));

                        $completions = $c_info->get_completions($user->id);

                        $rows = array();

                        $activities = array();
            $activities_complete = 0;

                        $prerequisites = array();
            $prerequisites_complete = 0;

                        foreach ($completions as $completion) {
                $criteria = $completion->get_criteria();
                $complete = $completion->is_complete();

                                if ($criteria->criteriatype == COMPLETION_CRITERIA_TYPE_ACTIVITY) {
                    $activities[$criteria->moduleinstance] = $complete;

                    if ($complete) {
                        $activities_complete++;
                    }

                    continue;
                }

                                if ($criteria->criteriatype == COMPLETION_CRITERIA_TYPE_COURSE) {
                    $prerequisites[$criteria->courseinstance] = $complete;

                    if ($complete) {
                        $prerequisites_complete++;
                    }

                    continue;
                }

                $row = array();
                $row['title'] = $criteria->get_title();
                $row['status'] = $completion->get_status();
                $rows[] = $row;
            }

                        if (!empty($activities)) {

                $row = array();
                $row['title'] = get_string('activitiescomplete', 'report_completion');
                $row['status'] = $activities_complete.' of '.count($activities);
                $rows[] = $row;
            }

                        if (!empty($prerequisites)) {

                $row = array();
                $row['title'] = get_string('prerequisitescompleted', 'completion');
                $row['status'] = $prerequisites_complete.' of '.count($prerequisites);
                array_splice($rows, 0, 0, array($row));
            }

            $first_row = true;

                        foreach ($rows as $row) {

                                if ($first_row) {
                    echo '<tr><td class="c0"><a href="'.$CFG->wwwroot.'/course/view.php?id='.$c_course->id.'">'.$course_name.'</a></td>';
                } else {
                    echo '<tr><td class="c0"></td>';
                }

                echo '<td class="c1">';
                echo $row['title'];
                echo '</td><td class="c2">';

                switch ($row['status']) {
                    case 'Yes':
                        echo get_string('complete');
                        break;

                    case 'No':
                        echo get_string('incomplete', 'report_completion');
                        break;

                    default:
                        echo $row['status'];
                }

                                echo '</td><td class="c3">';
                if ($first_row) {
                    echo '<a href="'.$CFG->wwwroot.'/blocks/completionstatus/details.php?course='.$c_course->id.'&user='.$user->id.'">'.get_string('detailedview', 'report_completion').'</a>';
                }
                echo '</td>';

                                if ($type === 'complete' && $first_row) {
                    $params = array(
                        'userid'    => $user->id,
                        'course'  => $c_course->id
                    );

                    $ccompletion = new completion_completion($params);
                    echo '<td class="c4">'.userdate($ccompletion->timecompleted, '%e %B %G').'</td>';
                }

                $first_row = false;
                echo '</tr>';
            }
        }

        echo '</table>';
    }
}


echo $OUTPUT->footer();
$event = \report_completion\event\user_report_viewed::create(array('context' => $coursecontext, 'relateduserid' => $userid));
$event->trigger();
