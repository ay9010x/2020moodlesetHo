<?php



require_once(dirname(__FILE__).'/../../config.php');
require_once("{$CFG->libdir}/completionlib.php");

$id = required_param('course', PARAM_INT);
$userid = optional_param('user', 0, PARAM_INT);

$course = $DB->get_record('course', array('id' => $id), '*', MUST_EXIST);

if ($userid) {
    $user = $DB->get_record('user', array('id' => $userid), '*', MUST_EXIST);
} else {
    $user = $USER;
}

require_login();

if (!completion_can_view_data($user->id, $course)) {
    print_error('cannotviewreport');
}

$info = new completion_info($course);

$returnurl = new moodle_url('/course/view.php', array('id' => $id));

if (!$info->is_enabled()) {
    print_error('completionnotenabled', 'completion', $returnurl);
}

if (!$info->is_tracked_user($user->id)) {
    if ($USER->id == $user->id) {
        print_error('notenroled', 'completion', $returnurl);
    } else {
        print_error('usernotenroled', 'completion', $returnurl);
    }
}


$PAGE->set_context(context_course::instance($course->id));

$page = get_string('completionprogressdetails', 'block_completionstatus');
$title = format_string($course->fullname) . ': ' . $page;

$PAGE->navbar->add($page);
$PAGE->set_pagelayout('report');
$PAGE->set_url('/blocks/completionstatus/details.php', array('course' => $course->id, 'user' => $user->id));
$PAGE->set_title(get_string('course') . ': ' . $course->fullname);
$PAGE->set_heading($title);
echo $OUTPUT->header();


echo html_writer::start_tag('table', array('class' => 'generalbox boxaligncenter'));
echo html_writer::start_tag('tbody');

if ($USER->id != $user->id) {
    echo html_writer::start_tag('tr');
    echo html_writer::start_tag('td', array('colspan' => '2'));
    echo html_writer::tag('b', get_string('showinguser', 'completion'));
    $url = new moodle_url('/user/view.php', array('id' => $user->id, 'course' => $course->id));
    echo html_writer::link($url, fullname($user));
    echo html_writer::end_tag('td');
    echo html_writer::end_tag('tr');
}

echo html_writer::start_tag('tr');
echo html_writer::start_tag('td', array('colspan' => '2'));
echo html_writer::tag('b', get_string('status'));

$coursecomplete = $info->is_course_complete($user->id);

$criteriacomplete = $info->count_course_user_data($user->id);

$params = array(
    'userid' => $user->id,
    'course' => $course->id,
);
$ccompletion = new completion_completion($params);

if ($coursecomplete) {
    echo get_string('complete');
} else if (!$criteriacomplete && !$ccompletion->timestarted) {
    echo html_writer::tag('i', get_string('notyetstarted', 'completion'));
} else {
    echo html_writer::tag('i', get_string('inprogress', 'completion'));
}

echo html_writer::end_tag('td');
echo html_writer::end_tag('tr');

$completions = $info->get_completions($user->id);

if (empty($completions)) {
    echo html_writer::start_tag('tr');
    echo html_writer::start_tag('td', array('colspan' => '2'));
    echo html_writer::start_tag('br');
    echo $OUTPUT->box(get_string('nocriteriaset', 'completion'), 'noticebox');
    echo html_writer::end_tag('td');
    echo html_writer::end_tag('tr');
    echo html_writer::end_tag('tbody');
    echo html_writer::end_tag('table');
} else {
    echo html_writer::start_tag('tr');
    echo html_writer::start_tag('td', array('colspan' => '2'));
    echo html_writer::tag('b', get_string('required'));

        $overall = $info->get_aggregation_method();

    if ($overall == COMPLETION_AGGREGATION_ALL) {
        echo get_string('criteriarequiredall', 'completion');
    } else {
        echo get_string('criteriarequiredany', 'completion');
    }

    echo html_writer::end_tag('td');
    echo html_writer::end_tag('tr');
    echo html_writer::end_tag('tbody');
    echo html_writer::end_tag('table');

        echo html_writer::start_tag('table',
            array('class' => 'generalbox logtable boxaligncenter', 'id' => 'criteriastatus', 'width' => '100%'));
    echo html_writer::start_tag('tbody');
    echo html_writer::start_tag('tr', array('class' => 'ccheader'));
    echo html_writer::tag('th', get_string('criteriagroup', 'block_completionstatus'), array('class' => 'c0 header', 'scope' => 'col'));
    echo html_writer::tag('th', get_string('criteria', 'completion'), array('class' => 'c1 header', 'scope' => 'col'));
    echo html_writer::tag('th', get_string('requirement', 'block_completionstatus'), array('class' => 'c2 header', 'scope' => 'col'));
    echo html_writer::tag('th', get_string('status'), array('class' => 'c3 header', 'scope' => 'col'));
    echo html_writer::tag('th', get_string('complete'), array('class' => 'c4 header', 'scope' => 'col'));
    echo html_writer::tag('th', get_string('completiondate', 'report_completion'), array('class' => 'c5 header', 'scope' => 'col'));
    echo html_writer::end_tag('tr');

        $rows = array();

        foreach ($completions as $completion) {
        $criteria = $completion->get_criteria();

        $row = array();
        $row['type'] = $criteria->criteriatype;
        $row['title'] = $criteria->get_title();
        $row['status'] = $completion->get_status();
        $row['complete'] = $completion->is_complete();
        $row['timecompleted'] = $completion->timecompleted;
        $row['details'] = $criteria->get_details($completion);
        $rows[] = $row;
    }

        $last_type = '';
    $agg_type = false;
    $oddeven = 0;

    foreach ($rows as $row) {

        echo html_writer::start_tag('tr', array('class' => 'r' . $oddeven));
                echo html_writer::start_tag('td', array('class' => 'cell c0'));
        if ($last_type !== $row['details']['type']) {
            $last_type = $row['details']['type'];
            echo $last_type;

                        $agg_type = true;
        } else {
                        if ($agg_type) {
                $agg = $info->get_aggregation_method($row['type']);
                echo '('. html_writer::start_tag('i');
                if ($agg == COMPLETION_AGGREGATION_ALL) {
                    echo core_text::strtolower(get_string('all', 'completion'));
                } else {
                    echo core_text::strtolower(get_string('any', 'completion'));
                }

                echo html_writer::end_tag('i') .core_text::strtolower(get_string('required')).')';
                $agg_type = false;
            }
        }
        echo html_writer::end_tag('td');

                echo html_writer::start_tag('td', array('class' => 'cell c1'));
        echo $row['details']['criteria'];
        echo html_writer::end_tag('td');

                echo html_writer::start_tag('td', array('class' => 'cell c2'));
        echo $row['details']['requirement'];
        echo html_writer::end_tag('td');

                echo html_writer::start_tag('td', array('class' => 'cell c3'));
        echo $row['details']['status'];
        echo html_writer::end_tag('td');

                echo html_writer::start_tag('td', array('class' => 'cell c4'));
        echo $row['complete'] ? get_string('yes') : get_string('no');
        echo html_writer::end_tag('td');

                echo html_writer::start_tag('td', array('class' => 'cell c5'));
        if ($row['timecompleted']) {
            echo userdate($row['timecompleted'], get_string('strftimedate', 'langconfig'));
        } else {
            echo '-';
        }
        echo html_writer::end_tag('td');
        echo html_writer::end_tag('tr');
                $oddeven = $oddeven ? 0 : 1;
    }

    echo html_writer::end_tag('tbody');
    echo html_writer::end_tag('table');
}
$courseurl = new moodle_url("/course/view.php", array('id' => $course->id));
echo html_writer::start_tag('div', array('class' => 'buttons'));
echo $OUTPUT->single_button($courseurl, get_string('returntocourse', 'block_completionstatus'), 'get');
echo html_writer::end_tag('div');
echo $OUTPUT->footer();
