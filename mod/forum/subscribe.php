<?php




require_once(dirname(dirname(dirname(__FILE__))).'/config.php');
require_once($CFG->dirroot.'/mod/forum/lib.php');

$id             = required_param('id', PARAM_INT);             $mode           = optional_param('mode', null, PARAM_INT);     $user           = optional_param('user', 0, PARAM_INT);        $discussionid   = optional_param('d', null, PARAM_INT);        $sesskey        = optional_param('sesskey', null, PARAM_RAW);
$returnurl      = optional_param('returnurl', null, PARAM_RAW);

$url = new moodle_url('/mod/forum/subscribe.php', array('id'=>$id));
if (!is_null($mode)) {
    $url->param('mode', $mode);
}
if ($user !== 0) {
    $url->param('user', $user);
}
if (!is_null($sesskey)) {
    $url->param('sesskey', $sesskey);
}
if (!is_null($discussionid)) {
    $url->param('d', $discussionid);
    if (!$discussion = $DB->get_record('forum_discussions', array('id' => $discussionid, 'forum' => $id))) {
        print_error('invaliddiscussionid', 'forum');
    }
}
$PAGE->set_url($url);

$forum   = $DB->get_record('forum', array('id' => $id), '*', MUST_EXIST);
$course  = $DB->get_record('course', array('id' => $forum->course), '*', MUST_EXIST);
$cm      = get_coursemodule_from_instance('forum', $forum->id, $course->id, false, MUST_EXIST);
$context = context_module::instance($cm->id);

if ($user) {
    require_sesskey();
    if (!has_capability('mod/forum:managesubscriptions', $context)) {
        print_error('nopermissiontosubscribe', 'forum');
    }
    $user = $DB->get_record('user', array('id' => $user), '*', MUST_EXIST);
} else {
    $user = $USER;
}

if (isset($cm->groupmode) && empty($course->groupmodeforce)) {
    $groupmode = $cm->groupmode;
} else {
    $groupmode = $course->groupmode;
}

$issubscribed = \mod_forum\subscriptions::is_subscribed($user->id, $forum, $discussionid, $cm);

if ($groupmode && !$issubscribed && !has_capability('moodle/site:accessallgroups', $context)) {
    if (!groups_get_all_groups($course->id, $USER->id)) {
        print_error('cannotsubscribe', 'forum');
    }
}

require_login($course, false, $cm);

if (is_null($mode) and !is_enrolled($context, $USER, '', true)) {       $PAGE->set_title($course->shortname);
    $PAGE->set_heading($course->fullname);
    if (isguestuser()) {
        echo $OUTPUT->header();
        echo $OUTPUT->confirm(get_string('subscribeenrolledonly', 'forum').'<br /><br />'.get_string('liketologin'),
                     get_login_url(), new moodle_url('/mod/forum/view.php', array('f'=>$id)));
        echo $OUTPUT->footer();
        exit;
    } else {
                redirect(
                new moodle_url('/mod/forum/view.php', array('f'=>$id)),
                get_string('subscribeenrolledonly', 'forum'),
                null,
                \core\output\notification::NOTIFY_ERROR
            );
    }
}

$returnto = optional_param('backtoindex',0,PARAM_INT)
    ? "index.php?id=".$course->id
    : "view.php?f=$id";

if ($returnurl) {
    $returnto = $returnurl;
}

if (!is_null($mode) and has_capability('mod/forum:managesubscriptions', $context)) {
    require_sesskey();
    switch ($mode) {
        case FORUM_CHOOSESUBSCRIBE :             \mod_forum\subscriptions::set_subscription_mode($forum->id, FORUM_CHOOSESUBSCRIBE);
            redirect(
                    $returnto,
                    get_string('everyonecannowchoose', 'forum'),
                    null,
                    \core\output\notification::NOTIFY_SUCCESS
                );
            break;
        case FORUM_FORCESUBSCRIBE :             \mod_forum\subscriptions::set_subscription_mode($forum->id, FORUM_FORCESUBSCRIBE);
            redirect(
                    $returnto,
                    get_string('everyoneisnowsubscribed', 'forum'),
                    null,
                    \core\output\notification::NOTIFY_SUCCESS
                );
            break;
        case FORUM_INITIALSUBSCRIBE :             if ($forum->forcesubscribe <> FORUM_INITIALSUBSCRIBE) {
                $users = \mod_forum\subscriptions::get_potential_subscribers($context, 0, 'u.id, u.email', '');
                foreach ($users as $user) {
                    \mod_forum\subscriptions::subscribe_user($user->id, $forum, $context);
                }
            }
            \mod_forum\subscriptions::set_subscription_mode($forum->id, FORUM_INITIALSUBSCRIBE);
            redirect(
                    $returnto,
                    get_string('everyoneisnowsubscribed', 'forum'),
                    null,
                    \core\output\notification::NOTIFY_SUCCESS
                );
            break;
        case FORUM_DISALLOWSUBSCRIBE :             \mod_forum\subscriptions::set_subscription_mode($forum->id, FORUM_DISALLOWSUBSCRIBE);
            redirect(
                    $returnto,
                    get_string('noonecansubscribenow', 'forum'),
                    null,
                    \core\output\notification::NOTIFY_SUCCESS
                );
            break;
        default:
            print_error(get_string('invalidforcesubscribe', 'forum'));
    }
}

if (\mod_forum\subscriptions::is_forcesubscribed($forum)) {
    redirect(
            $returnto,
            get_string('everyoneisnowsubscribed', 'forum'),
            null,
            \core\output\notification::NOTIFY_SUCCESS
        );
}

$info = new stdClass();
$info->name  = fullname($user);
$info->forum = format_string($forum->name);

if ($issubscribed) {
    if (is_null($sesskey)) {
                $PAGE->set_title($course->shortname);
        $PAGE->set_heading($course->fullname);
        echo $OUTPUT->header();

        $viewurl = new moodle_url('/mod/forum/view.php', array('f' => $id));
        if ($discussionid) {
            $a = new stdClass();
            $a->forum = format_string($forum->name);
            $a->discussion = format_string($discussion->name);
            echo $OUTPUT->confirm(get_string('confirmunsubscribediscussion', 'forum', $a),
                    $PAGE->url, $viewurl);
        } else {
            echo $OUTPUT->confirm(get_string('confirmunsubscribe', 'forum', format_string($forum->name)),
                    $PAGE->url, $viewurl);
        }
        echo $OUTPUT->footer();
        exit;
    }
    require_sesskey();
    if ($discussionid === null) {
        if (\mod_forum\subscriptions::unsubscribe_user($user->id, $forum, $context, true)) {
            redirect(
                    $returnto,
                    get_string('nownotsubscribed', 'forum', $info),
                    null,
                    \core\output\notification::NOTIFY_SUCCESS
                );
        } else {
            print_error('cannotunsubscribe', 'forum', get_local_referer(false));
        }
    } else {
        if (\mod_forum\subscriptions::unsubscribe_user_from_discussion($user->id, $discussion, $context)) {
            $info->discussion = $discussion->name;
            redirect(
                    $returnto,
                    get_string('discussionnownotsubscribed', 'forum', $info),
                    null,
                    \core\output\notification::NOTIFY_SUCCESS
                );
        } else {
            print_error('cannotunsubscribe', 'forum', get_local_referer(false));
        }
    }

} else {      if (\mod_forum\subscriptions::subscription_disabled($forum) && !has_capability('mod/forum:managesubscriptions', $context)) {
        print_error('disallowsubscribe', 'forum', get_local_referer(false));
    }
    if (!has_capability('mod/forum:viewdiscussion', $context)) {
        print_error('noviewdiscussionspermission', 'forum', get_local_referer(false));
    }
    if (is_null($sesskey)) {
                $PAGE->set_title($course->shortname);
        $PAGE->set_heading($course->fullname);
        echo $OUTPUT->header();

        $viewurl = new moodle_url('/mod/forum/view.php', array('f' => $id));
        if ($discussionid) {
            $a = new stdClass();
            $a->forum = format_string($forum->name);
            $a->discussion = format_string($discussion->name);
            echo $OUTPUT->confirm(get_string('confirmsubscribediscussion', 'forum', $a),
                    $PAGE->url, $viewurl);
        } else {
            echo $OUTPUT->confirm(get_string('confirmsubscribe', 'forum', format_string($forum->name)),
                    $PAGE->url, $viewurl);
        }
        echo $OUTPUT->footer();
        exit;
    }
    require_sesskey();
    if ($discussionid == null) {
        \mod_forum\subscriptions::subscribe_user($user->id, $forum, $context, true);
        redirect(
                $returnto,
                get_string('nowsubscribed', 'forum', $info),
                null,
                \core\output\notification::NOTIFY_SUCCESS
            );
    } else {
        $info->discussion = $discussion->name;
        \mod_forum\subscriptions::subscribe_user_to_discussion($user->id, $discussion, $context);
        redirect(
                $returnto,
                get_string('discussionnowsubscribed', 'forum', $info),
                null,
                \core\output\notification::NOTIFY_SUCCESS
            );
    }
}
