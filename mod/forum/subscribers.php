<?php




require_once("../../config.php");
require_once("lib.php");

$id    = required_param('id',PARAM_INT);           $group = optional_param('group',0,PARAM_INT);      $edit  = optional_param('edit',-1,PARAM_BOOL);     
$url = new moodle_url('/mod/forum/subscribers.php', array('id'=>$id));
if ($group !== 0) {
    $url->param('group', $group);
}
if ($edit !== 0) {
    $url->param('edit', $edit);
}
$PAGE->set_url($url);

$forum = $DB->get_record('forum', array('id'=>$id), '*', MUST_EXIST);
$course = $DB->get_record('course', array('id'=>$forum->course), '*', MUST_EXIST);
if (! $cm = get_coursemodule_from_instance('forum', $forum->id, $course->id)) {
    $cm->id = 0;
}

require_login($course, false, $cm);

$context = context_module::instance($cm->id);
if (!has_capability('mod/forum:viewsubscribers', $context)) {
    print_error('nopermissiontosubscribe', 'forum');
}

unset($SESSION->fromdiscussion);

$params = array(
    'context' => $context,
    'other' => array('forumid' => $forum->id),
);
$event = \mod_forum\event\subscribers_viewed::create($params);
$event->trigger();

$forumoutput = $PAGE->get_renderer('mod_forum');
$currentgroup = groups_get_activity_group($cm);
$options = array('forumid'=>$forum->id, 'currentgroup'=>$currentgroup, 'context'=>$context);
$existingselector = new mod_forum_existing_subscriber_selector('existingsubscribers', $options);
$subscriberselector = new mod_forum_potential_subscriber_selector('potentialsubscribers', $options);
$subscriberselector->set_existing_subscribers($existingselector->find_users(''));

if (data_submitted()) {
    require_sesskey();
    $subscribe = (bool)optional_param('subscribe', false, PARAM_RAW);
    $unsubscribe = (bool)optional_param('unsubscribe', false, PARAM_RAW);
    
    if (!($subscribe xor $unsubscribe)) {
        print_error('invalidaction');
    }
    if ($subscribe) {
        $users = $subscriberselector->get_selected_users();
        foreach ($users as $user) {
            if (!\mod_forum\subscriptions::subscribe_user($user->id, $forum)) {
                print_error('cannotaddsubscriber', 'forum', '', $user->id);
            }
        }
    } else if ($unsubscribe) {
        $users = $existingselector->get_selected_users();
        foreach ($users as $user) {
            if (!\mod_forum\subscriptions::unsubscribe_user($user->id, $forum)) {
                print_error('cannotremovesubscriber', 'forum', '', $user->id);
            }
        }
    }
    $subscriberselector->invalidate_selected_users();
    $existingselector->invalidate_selected_users();
    $subscriberselector->set_existing_subscribers($existingselector->find_users(''));
}

$strsubscribers = get_string("subscribers", "forum");
$PAGE->navbar->add($strsubscribers);
$PAGE->set_title($strsubscribers);
$PAGE->set_heading($COURSE->fullname);
if (has_capability('mod/forum:managesubscriptions', $context) && \mod_forum\subscriptions::is_forcesubscribed($forum) === false) {
    if ($edit != -1) {
        $USER->subscriptionsediting = $edit;
    }
    $PAGE->set_button(forum_update_subscriptions_button($course->id, $id));
} else {
    unset($USER->subscriptionsediting);
}
echo $OUTPUT->header();
echo $OUTPUT->heading(get_string('forum', 'forum').' '.$strsubscribers);
if (empty($USER->subscriptionsediting)) {
    $subscribers = \mod_forum\subscriptions::fetch_subscribed_users($forum, $currentgroup, $context);
    if (\mod_forum\subscriptions::is_forcesubscribed($forum)) {
        $subscribers = mod_forum_filter_hidden_users($cm, $context, $subscribers);
    }
    echo $forumoutput->subscriber_overview($subscribers, $forum, $course);
} else {
    echo $forumoutput->subscriber_selection_form($existingselector, $subscriberselector);
}
echo $OUTPUT->footer();


function mod_forum_filter_hidden_users(stdClass $cm, context_module $context, array $users) {
    if ($cm->visible) {
        return $users;
    } else {
                $filteredusers = array();
        $hiddenviewers = get_users_by_capability($context, 'moodle/course:viewhiddenactivities');
        foreach ($hiddenviewers as $hiddenviewer) {
            if (array_key_exists($hiddenviewer->id, $users)) {
                $filteredusers[$hiddenviewer->id] = $users[$hiddenviewer->id];
            }
        }
        return $filteredusers;
    }
}
