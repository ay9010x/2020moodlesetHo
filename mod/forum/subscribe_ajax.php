<?php



define('AJAX_SCRIPT', true);
require_once(dirname(dirname(__DIR__)) . '/config.php');
require_once($CFG->dirroot . '/mod/forum/lib.php');

$forumid        = required_param('forumid', PARAM_INT);             $discussionid   = optional_param('discussionid', null, PARAM_INT);  $includetext    = optional_param('includetext', false, PARAM_BOOL);

$forum          = $DB->get_record('forum', array('id' => $forumid), '*', MUST_EXIST);
$course         = $DB->get_record('course', array('id' => $forum->course), '*', MUST_EXIST);
if (!$discussion = $DB->get_record('forum_discussions', array('id' => $discussionid, 'forum' => $forumid))) {
    print_error('invaliddiscussionid', 'forum');
}
$cm             = get_coursemodule_from_instance('forum', $forum->id, $course->id, false, MUST_EXIST);
$context        = context_module::instance($cm->id);

require_sesskey();
require_login($course, false, $cm);
require_capability('mod/forum:viewdiscussion', $context);

$return = new stdClass();

if (is_guest($context, $USER)) {
            throw new moodle_exception('noguestsubscribe', 'mod_forum');
}

if (!\mod_forum\subscriptions::is_subscribable($forum)) {
        echo json_encode($return);
    die;
}

if (\mod_forum\subscriptions::is_subscribed($USER->id, $forum, $discussion->id, $cm)) {
        \mod_forum\subscriptions::unsubscribe_user_from_discussion($USER->id, $discussion, $context);
} else {
        \mod_forum\subscriptions::subscribe_user_to_discussion($USER->id, $discussion, $context);
}

$return->icon = forum_get_discussion_subscription_icon($forum, $discussion->id, null, $includetext);
echo json_encode($return);
die;
