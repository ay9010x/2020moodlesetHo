<?php



require_once('../../config.php');
require_once('locallib.php');

$attemptid = required_param('attempt', PARAM_INT);
$slot = required_param('slot', PARAM_INT); 
$PAGE->set_url('/mod/quiz/comment.php', array('attempt' => $attemptid, 'slot' => $slot));

$attemptobj = quiz_attempt::create($attemptid);
$student = $DB->get_record('user', array('id' => $attemptobj->get_userid()));

if (!$attemptobj->is_finished()) {
    print_error('attemptclosed', 'quiz');
}

require_login($attemptobj->get_course(), false, $attemptobj->get_cm());
$attemptobj->require_capability('mod/quiz:grade');

$PAGE->set_pagelayout('popup');
$PAGE->set_title(get_string('manualgradequestion', 'quiz', array(
        'question' => format_string($attemptobj->get_question_name($slot)),
        'quiz' => format_string($attemptobj->get_quiz_name()), 'user' => fullname($student))));
$PAGE->set_heading($attemptobj->get_course()->fullname);
$output = $PAGE->get_renderer('mod_quiz');
echo $output->header();

$summarydata = array();

$userpicture = new user_picture($student);
$userpicture->courseid = $attemptobj->get_courseid();
$summarydata['user'] = array(
    'title'   => $userpicture,
    'content' => new action_link(new moodle_url('/user/view.php', array(
            'id' => $student->id, 'course' => $attemptobj->get_courseid())),
            fullname($student, true)),
);

$summarydata['quizname'] = array(
    'title'   => get_string('modulename', 'quiz'),
    'content' => format_string($attemptobj->get_quiz_name()),
);

$summarydata['questionname'] = array(
    'title'   => get_string('question', 'quiz'),
    'content' => $attemptobj->get_question_name($slot),
);

if (data_submitted() && confirm_sesskey()) {
    if (optional_param('submit', false, PARAM_BOOL) && question_engine::is_manual_grade_in_range($attemptobj->get_uniqueid(), $slot)) {
        $transaction = $DB->start_delegated_transaction();
        $attemptobj->process_submitted_actions(time());
        $transaction->allow_commit();

                $params = array(
            'objectid' => $attemptobj->get_question_attempt($slot)->get_question()->id,
            'courseid' => $attemptobj->get_courseid(),
            'context' => context_module::instance($attemptobj->get_cmid()),
            'other' => array(
                'quizid' => $attemptobj->get_quizid(),
                'attemptid' => $attemptobj->get_attemptid(),
                'slot' => $slot
            )
        );
        $event = \mod_quiz\event\question_manually_graded::create($params);
        $event->trigger();

        echo $output->notification(get_string('changessaved'), 'notifysuccess');
        close_window(2, true);
        die;
    }
}

echo $output->review_summary_table($summarydata, 0);

echo '<form method="post" class="mform" id="manualgradingform" action="' .
        $CFG->wwwroot . '/mod/quiz/comment.php">';
echo $attemptobj->render_question_for_commenting($slot);
?>
<div>
    <input type="hidden" name="attempt" value="<?php echo $attemptobj->get_attemptid(); ?>" />
    <input type="hidden" name="slot" value="<?php echo $slot; ?>" />
    <input type="hidden" name="slots" value="<?php echo $slot; ?>" />
    <input type="hidden" name="sesskey" value="<?php echo sesskey(); ?>" />
</div>
<fieldset class="hidden">
    <div>
        <div class="fitem fitem_actionbuttons fitem_fsubmit">
            <fieldset class="felement fsubmit">
                <input id="id_submitbutton" type="submit" name="submit" value="<?php
                        print_string('save', 'quiz'); ?>"/>
            </fieldset>
        </div>
    </div>
</fieldset>
<?php
echo '</form>';
$PAGE->requires->js_init_call('M.mod_quiz.init_comment_popup', null, false, quiz_get_js_module());

echo $output->footer();
