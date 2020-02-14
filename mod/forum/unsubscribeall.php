<?php




require_once("../../config.php");
require_once("lib.php");

$confirm = optional_param('confirm', false, PARAM_BOOL);

$PAGE->set_url('/mod/forum/unsubscribeall.php');

require_login(null, false);
$PAGE->set_context(context_user::instance($USER->id));

$return = $CFG->wwwroot.'/';

if (isguestuser()) {
    redirect($return);
}

$strunsubscribeall = get_string('unsubscribeall', 'forum');
$PAGE->navbar->add(get_string('modulename', 'forum'));
$PAGE->navbar->add($strunsubscribeall);
$PAGE->set_title($strunsubscribeall);
$PAGE->set_heading($COURSE->fullname);
echo $OUTPUT->header();
echo $OUTPUT->heading($strunsubscribeall);

if (data_submitted() and $confirm and confirm_sesskey()) {
    $forums = \mod_forum\subscriptions::get_unsubscribable_forums();

    foreach($forums as $forum) {
        \mod_forum\subscriptions::unsubscribe_user($USER->id, $forum, context_module::instance($forum->cm), true);
    }
    $DB->delete_records('forum_discussion_subs', array('userid' => $USER->id));
    $DB->set_field('user', 'autosubscribe', 0, array('id'=>$USER->id));

    echo $OUTPUT->box(get_string('unsubscribealldone', 'forum'));
    echo $OUTPUT->continue_button($return);
    echo $OUTPUT->footer();
    die;

} else {
    $count = new stdClass();
    $count->forums = count(\mod_forum\subscriptions::get_unsubscribable_forums());
    $count->discussions = $DB->count_records('forum_discussion_subs', array('userid' => $USER->id));

    if ($count->forums || $count->discussions) {
        if ($count->forums && $count->discussions) {
            $msg = get_string('unsubscribeallconfirm', 'forum', $count);
        } else if ($count->forums) {
            $msg = get_string('unsubscribeallconfirmforums', 'forum', $count);
        } else if ($count->discussions) {
            $msg = get_string('unsubscribeallconfirmdiscussions', 'forum', $count);
        }
        echo $OUTPUT->confirm($msg, new moodle_url('unsubscribeall.php', array('confirm'=>1)), $return);
        echo $OUTPUT->footer();
        die;

    } else {
        echo $OUTPUT->box(get_string('unsubscribeallempty', 'forum'));
        echo $OUTPUT->continue_button($return);
        echo $OUTPUT->footer();
        die;
    }
}
