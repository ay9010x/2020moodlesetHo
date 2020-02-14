<?php




require_once(dirname(__FILE__) . '/../../config.php');
require_once($CFG->dirroot.'/mod/quiz/lib.php');
require_once($CFG->dirroot.'/mod/quiz/locallib.php');
require_once($CFG->dirroot.'/mod/quiz/override_form.php');


$cmid = required_param('cmid', PARAM_INT);
$mode = optional_param('mode', '', PARAM_ALPHA); 
list($course, $cm) = get_course_and_cm_from_cmid($cmid, 'quiz');
$quiz = $DB->get_record('quiz', array('id' => $cm->instance), '*', MUST_EXIST);

$groups = groups_get_all_groups($cm->course);
if ($groups === false) {
    $groups = array();
}

if ($mode != "user" and $mode != "group") {
    if (!empty($groups)) {
        $mode = "group";
    } else {
        $mode = "user";
    }
}
$groupmode = ($mode == "group");

$url = new moodle_url('/mod/quiz/overrides.php', array('cmid'=>$cm->id, 'mode'=>$mode));

$PAGE->set_url($url);

require_login($course, false, $cm);

$context = context_module::instance($cm->id);

require_capability('mod/quiz:manageoverrides', $context);

$PAGE->set_pagelayout('admin');
$PAGE->set_title(get_string('overrides', 'quiz'));
$PAGE->set_heading($course->fullname);
echo $OUTPUT->header();
echo $OUTPUT->heading(format_string($quiz->name, true, array('context' => $context)));

$sql = 'SELECT o.id
            FROM {quiz_overrides} o LEFT JOIN {groups} g
            ON o.groupid = g.id
            WHERE o.groupid IS NOT NULL
              AND g.id IS NULL
              AND o.quiz = ?';
$params = array($quiz->id);
$orphaned = $DB->get_records_sql($sql, $params);
if (!empty($orphaned)) {
    $DB->delete_records_list('quiz_overrides', 'id', array_keys($orphaned));
}

if ($groupmode) {
    $colname = get_string('group');
    $sql = 'SELECT o.*, g.name
                FROM {quiz_overrides} o
                JOIN {groups} g ON o.groupid = g.id
                WHERE o.quiz = :quizid
                ORDER BY g.name';
    $params = array('quizid' => $quiz->id);
} else {
    $colname = get_string('user');
    list($sort, $params) = users_order_by_sql('u');
    $sql = 'SELECT o.*, ' . get_all_user_name_fields(true, 'u') . '
            FROM {quiz_overrides} o
            JOIN {user} u ON o.userid = u.id
            WHERE o.quiz = :quizid
            ORDER BY ' . $sort;
    $params['quizid'] = $quiz->id;
}

$overrides = $DB->get_records_sql($sql, $params);

$table = new html_table();
$table->headspan = array(1, 2, 1);
$table->colclasses = array('colname', 'colsetting', 'colvalue', 'colaction');
$table->head = array(
        $colname,
        get_string('overrides', 'quiz'),
        get_string('action'),
);

$userurl = new moodle_url('/user/view.php', array());
$groupurl = new moodle_url('/group/overview.php', array('id' => $cm->course));

$overridedeleteurl = new moodle_url('/mod/quiz/overridedelete.php');
$overrideediturl = new moodle_url('/mod/quiz/overrideedit.php');

$hasinactive = false; 
foreach ($overrides as $override) {

    $fields = array();
    $values = array();
    $active = true;

        if (!$groupmode) {
        if (!has_capability('mod/quiz:attempt', $context, $override->userid)) {
                        $active = false;
        } else if (!\core_availability\info_module::is_user_visible($cm, $override->userid)) {
                        $active = false;
        }
    }

        if (isset($override->timeopen)) {
        $fields[] = get_string('quizopens', 'quiz');
        $values[] = $override->timeopen > 0 ?
                userdate($override->timeopen) : get_string('noopen', 'quiz');
    }

        if (isset($override->timeclose)) {
        $fields[] = get_string('quizcloses', 'quiz');
        $values[] = $override->timeclose > 0 ?
                userdate($override->timeclose) : get_string('noclose', 'quiz');
    }

        if (isset($override->timelimit)) {
        $fields[] = get_string('timelimit', 'quiz');
        $values[] = $override->timelimit > 0 ?
                format_time($override->timelimit) : get_string('none', 'quiz');
    }

        if (isset($override->attempts)) {
        $fields[] = get_string('attempts', 'quiz');
        $values[] = $override->attempts > 0 ?
                $override->attempts : get_string('unlimited');
    }

        if (isset($override->password)) {
        $fields[] = get_string('requirepassword', 'quiz');
        $values[] = $override->password !== '' ?
                get_string('enabled', 'quiz') : get_string('none', 'quiz');
    }

        $iconstr = '';

    if ($active) {
                $editurlstr = $overrideediturl->out(true, array('id' => $override->id));
        $iconstr = '<a title="' . get_string('edit') . '" href="'. $editurlstr . '">' .
                '<img src="' . $OUTPUT->pix_url('t/edit') . '" class="iconsmall" alt="' .
                get_string('edit') . '" /></a> ';
                $copyurlstr = $overrideediturl->out(true,
                array('id' => $override->id, 'action' => 'duplicate'));
        $iconstr .= '<a title="' . get_string('copy') . '" href="' . $copyurlstr . '">' .
                '<img src="' . $OUTPUT->pix_url('t/copy') . '" class="iconsmall" alt="' .
                get_string('copy') . '" /></a> ';
    }
        $deleteurlstr = $overridedeleteurl->out(true,
            array('id' => $override->id, 'sesskey' => sesskey()));
    $iconstr .= '<a title="' . get_string('delete') . '" href="' . $deleteurlstr . '">' .
            '<img src="' . $OUTPUT->pix_url('t/delete') . '" class="iconsmall" alt="' .
            get_string('delete') . '" /></a> ';

    if ($groupmode) {
        $usergroupstr = '<a href="' . $groupurl->out(true,
                array('group' => $override->groupid)) . '" >' . $override->name . '</a>';
    } else {
        $usergroupstr = '<a href="' . $userurl->out(true,
                array('id' => $override->userid)) . '" >' . fullname($override) . '</a>';
    }

    $class = '';
    if (!$active) {
        $class = "dimmed_text";
        $usergroupstr .= '*';
        $hasinactive = true;
    }

    $usergroupcell = new html_table_cell();
    $usergroupcell->rowspan = count($fields);
    $usergroupcell->text = $usergroupstr;
    $actioncell = new html_table_cell();
    $actioncell->rowspan = count($fields);
    $actioncell->text = $iconstr;

    for ($i = 0; $i < count($fields); ++$i) {
        $row = new html_table_row();
        $row->attributes['class'] = $class;
        if ($i == 0) {
            $row->cells[] = $usergroupcell;
        }
        $cell1 = new html_table_cell();
        $cell1->text = $fields[$i];
        $row->cells[] = $cell1;
        $cell2 = new html_table_cell();
        $cell2->text = $values[$i];
        $row->cells[] = $cell2;
        if ($i == 0) {
            $row->cells[] = $actioncell;
        }
        $table->data[] = $row;
    }
}

echo html_writer::start_tag('div', array('id' => 'quizoverrides'));
if (count($table->data)) {
    echo html_writer::table($table);
}
if ($hasinactive) {
    echo $OUTPUT->notification(get_string('inactiveoverridehelp', 'quiz'), 'dimmed_text');
}

echo html_writer::start_tag('div', array('class' => 'buttons'));
$options = array();
if ($groupmode) {
    if (empty($groups)) {
                echo $OUTPUT->notification(get_string('groupsnone', 'quiz'), 'error');
        $options['disabled'] = true;
    }
    echo $OUTPUT->single_button($overrideediturl->out(true,
            array('action' => 'addgroup', 'cmid' => $cm->id)),
            get_string('addnewgroupoverride', 'quiz'), 'post', $options);
} else {
    $users = array();
        $users = get_users_by_capability($context, 'mod/quiz:attempt', 'u.id');
    $info = new \core_availability\info_module($cm);
    $users = $info->filter_user_list($users);

    if (empty($users)) {
                echo $OUTPUT->notification(get_string('usersnone', 'quiz'), 'error');
        $options['disabled'] = true;
    }
    echo $OUTPUT->single_button($overrideediturl->out(true,
            array('action' => 'adduser', 'cmid' => $cm->id)),
            get_string('addnewuseroverride', 'quiz'), 'get', $options);
}
echo html_writer::end_tag('div');
echo html_writer::end_tag('div');

echo $OUTPUT->footer();
