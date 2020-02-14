<?php

/*if($forum->type == "snifs") {
	
			echo "123";
			echo "<script type='text/javascript'>";
			echo "window.location.href='iframe/index.php?id=".$id."'";
			echo "</script>";
            if (!empty($discussions) && count($discussions) > 1) {
                echo $OUTPUT->notification(get_string('warnformorepost', 'forum'));
            }
            if (! $post = forum_get_post_full($discussion->firstpost)) {
                print_error('cannotfindfirstpost', 'forum');
            }
            if ($mode) {
                set_user_preference("forum_displaymode", -1);
            }

			
            $canreply    = forum_user_can_post($forum, $discussion, $USER, $cm, $course, $context);
            $canrate     = has_capability('mod/forum:rate', $context);
            $displaymode = get_user_preferences("forum_displaymode", $CFG->forum_displaymode);
			
			forum_print_discussion($course, $cm, $forum, $discussion, $post, $displaymode, $canreply, $canrate);
            
}*/


require_once('../../config.php');

$d      = required_param('d', PARAM_INT);                
$parent = optional_param('parent', 0, PARAM_INT);        
$mode   = optional_param('mode', 0, PARAM_INT);          
$move   = optional_param('move', 0, PARAM_INT);          
$mark   = optional_param('mark', '', PARAM_ALPHA);       
$postid = optional_param('postid', 0, PARAM_INT);        
$pin    = optional_param('pin', -1, PARAM_INT);          
$url = new moodle_url('/mod/forum/discuss.php', array('d'=>$d));
if ($parent !== 0) {
    $url->param('parent', $parent);
}
$PAGE->set_url($url);

$discussion = $DB->get_record('forum_discussions', array('id' => $d), '*', MUST_EXIST);
$course = $DB->get_record('course', array('id' => $discussion->course), '*', MUST_EXIST);
$forum = $DB->get_record('forum', array('id' => $discussion->forum), '*', MUST_EXIST);
$cm = get_coursemodule_from_instance('forum', $forum->id, $course->id, false, MUST_EXIST);

require_course_login($course, true, $cm);

require_once($CFG->dirroot.'/mod/forum/lib.php');

$modcontext = context_module::instance($cm->id);
require_capability('mod/forum:viewdiscussion', $modcontext, NULL, true, 'noviewdiscussionspermission', 'forum');

if (!empty($CFG->enablerssfeeds) && !empty($CFG->forum_enablerssfeeds) && $forum->rsstype && $forum->rssarticles) {
    require_once("$CFG->libdir/rsslib.php");

    $rsstitle = format_string($course->shortname, true, array('context' => context_course::instance($course->id))) . ': ' . format_string($forum->name);
    rss_add_http_header($modcontext, 'mod_forum', $forum, $rsstitle);
}

if ($move > 0 and confirm_sesskey()) {
    $return = $CFG->wwwroot.'/mod/forum/discuss.php?d='.$discussion->id;

    if (!$forumto = $DB->get_record('forum', array('id' => $move))) {
        print_error('cannotmovetonotexist', 'forum', $return);
    }

    require_capability('mod/forum:movediscussions', $modcontext);

    if ($forum->type == 'single') {
        print_error('cannotmovefromsingleforum', 'forum', $return);
    }

    if (!$forumto = $DB->get_record('forum', array('id' => $move))) {
        print_error('cannotmovetonotexist', 'forum', $return);
    }

    if ($forumto->type == 'single') {
        print_error('cannotmovetosingleforum', 'forum', $return);
    }

        $modinfo = get_fast_modinfo($course);
    $forums = $modinfo->get_instances_of('forum');
    if (!array_key_exists($forumto->id, $forums)) {
        print_error('cannotmovetonotfound', 'forum', $return);
    }
    $cmto = $forums[$forumto->id];
    if (!$cmto->uservisible) {
        print_error('cannotmovenotvisible', 'forum', $return);
    }

    $destinationctx = context_module::instance($cmto->id);
    require_capability('mod/forum:startdiscussion', $destinationctx);

    if (!forum_move_attachments($discussion, $forum->id, $forumto->id)) {
        echo $OUTPUT->notification("Errors occurred while moving attachment directories - check your file permissions");
    }
        $discussiongroup = $discussion->groupid == -1 ? 0 : $discussion->groupid;
    $potentialsubscribers = \mod_forum\subscriptions::fetch_subscribed_users(
        $forum,
        $discussiongroup,
        $modcontext,
        'u.id',
        true
    );

            \mod_forum\subscriptions::fill_subscription_cache($forumto->id);
        \mod_forum\subscriptions::fill_subscription_cache($forum->id);
    $subscriptionchanges = array();
    $subscriptiontime = time();
    foreach ($potentialsubscribers as $subuser) {
        $userid = $subuser->id;
        $targetsubscription = \mod_forum\subscriptions::is_subscribed($userid, $forumto, null, $cmto);
        $discussionsubscribed = \mod_forum\subscriptions::is_subscribed($userid, $forum, $discussion->id);
        $forumsubscribed = \mod_forum\subscriptions::is_subscribed($userid, $forum);

        if ($forumsubscribed && !$discussionsubscribed && $targetsubscription) {
                                    $subscriptionchanges[$userid] = \mod_forum\subscriptions::FORUM_DISCUSSION_UNSUBSCRIBED;
        } else if (!$forumsubscribed && $discussionsubscribed && !$targetsubscription) {
                                    $subscriptionchanges[$userid] = $subscriptiontime;
        }
    }

    $DB->set_field('forum_discussions', 'forum', $forumto->id, array('id' => $discussion->id));
    $DB->set_field('forum_read', 'forumid', $forumto->id, array('discussionid' => $discussion->id));

        $DB->delete_records('forum_discussion_subs', array('discussion' => $discussion->id));
    $newdiscussion = clone $discussion;
    $newdiscussion->forum = $forumto->id;
    foreach ($subscriptionchanges as $userid => $preference) {
        if ($preference != \mod_forum\subscriptions::FORUM_DISCUSSION_UNSUBSCRIBED) {
                        if (has_capability('mod/forum:viewdiscussion', $destinationctx, $userid)) {
                \mod_forum\subscriptions::subscribe_user_to_discussion($userid, $newdiscussion, $destinationctx);
            }
        } else {
            \mod_forum\subscriptions::unsubscribe_user_from_discussion($userid, $newdiscussion, $destinationctx);
        }
    }

    $params = array(
        'context' => $destinationctx,
        'objectid' => $discussion->id,
        'other' => array(
            'fromforumid' => $forum->id,
            'toforumid' => $forumto->id,
        )
    );
    $event = \mod_forum\event\discussion_moved::create($params);
    $event->add_record_snapshot('forum_discussions', $discussion);
    $event->add_record_snapshot('forum', $forum);
    $event->add_record_snapshot('forum', $forumto);
    $event->trigger();

        require_once($CFG->dirroot.'/mod/forum/rsslib.php');
    forum_rss_delete_file($forum);
    forum_rss_delete_file($forumto);

    redirect($return.'&move=-1&sesskey='.sesskey());
}
if ($pin !== -1 && confirm_sesskey()) {
    require_capability('mod/forum:pindiscussions', $modcontext);

    $params = array('context' => $modcontext, 'objectid' => $discussion->id, 'other' => array('forumid' => $forum->id));

    switch ($pin) {
        case FORUM_DISCUSSION_PINNED:
                        forum_discussion_pin($modcontext, $forum, $discussion);
            break;
        case FORUM_DISCUSSION_UNPINNED:
                        forum_discussion_unpin($modcontext, $forum, $discussion);
            break;
        default:
            echo $OUTPUT->notification("Invalid value when attempting to pin/unpin discussion");
            break;
    }

    redirect(new moodle_url('/mod/forum/discuss.php', array('d' => $discussion->id)));
}

forum_discussion_view($modcontext, $forum, $discussion);

unset($SESSION->fromdiscussion);

if ($mode) {
    set_user_preference('forum_displaymode', $mode);
}

$displaymode = get_user_preferences('forum_displaymode', $CFG->forum_displaymode);

if ($parent) {
        if ($displaymode == FORUM_MODE_FLATOLDEST or $displaymode == FORUM_MODE_FLATNEWEST) {
        $displaymode = FORUM_MODE_NESTED;
    }
} else {
    $parent = $discussion->firstpost;
}

if (! $post = forum_get_post_full($parent)) {
    print_error("notexists", 'forum', "$CFG->wwwroot/mod/forum/view.php?f=$forum->id");
}

if (!forum_user_can_see_post($forum, $discussion, $post, null, $cm)) {
    print_error('noviewdiscussionspermission', 'forum', "$CFG->wwwroot/mod/forum/view.php?id=$forum->id");
}

if ($mark == 'read' or $mark == 'unread') {
    if ($CFG->forum_usermarksread && forum_tp_can_track_forums($forum) && forum_tp_is_tracked($forum)) {
        if ($mark == 'read') {
            forum_tp_add_read_record($USER->id, $postid);
        } else {
                        forum_tp_delete_read_records($USER->id, $postid);
        }
    }
}

$searchform = forum_search_form($course);

$forumnode = $PAGE->navigation->find($cm->id, navigation_node::TYPE_ACTIVITY);
if (empty($forumnode)) {
    $forumnode = $PAGE->navbar;
} else {
    $forumnode->make_active();
}
$node = $forumnode->add(format_string($discussion->name), new moodle_url('/mod/forum/discuss.php', array('d'=>$discussion->id)));
$node->display = false;
if ($node && $post->id != $discussion->firstpost) {
    $node->add(format_string($post->subject), $PAGE->url);
}

$PAGE->set_title("$course->shortname: ".format_string($discussion->name));
$PAGE->set_heading($course->fullname);
$PAGE->set_button($searchform);
$renderer = $PAGE->get_renderer('mod_forum');

echo $OUTPUT->header();

echo $OUTPUT->heading(format_string($forum->name), 2);
echo $OUTPUT->heading(format_string($discussion->name), 3, 'discussionname');

if ((!is_guest($modcontext, $USER) && isloggedin()) && has_capability('mod/forum:viewdiscussion', $modcontext)) {
        if (\mod_forum\subscriptions::is_subscribable($forum)) {
        echo html_writer::div(
            forum_get_discussion_subscription_icon($forum, $post->discussion, null, true),
            'discussionsubscription'
        );
        echo forum_get_discussion_subscription_icon_preloaders();
    }
}



$canreply = forum_user_can_post($forum, $discussion, $USER, $cm, $course, $modcontext);
if (!$canreply and $forum->type !== 'news') {
    if (isguestuser() or !isloggedin()) {
        $canreply = true;
    }
    if (!is_enrolled($modcontext) and !is_viewing($modcontext)) {
                        $canreply = enrol_selfenrol_available($course->id);
    }
}

$neighbours = forum_get_discussion_neighbours($cm, $discussion, $forum);
$neighbourlinks = $renderer->neighbouring_discussion_navigation($neighbours['prev'], $neighbours['next']);
echo $neighbourlinks;

if($forum->type == "snifs") {
			
			echo "<script type='text/javascript'>";
			echo "window.location.href='iframe/index.php?d=".$d."'";
			echo "</script>";
            
}

echo '<div class="discussioncontrols clearfix">';
echo '<div class="controlscontainer">';

if (!empty($CFG->enableportfolios) && has_capability('mod/forum:exportdiscussion', $modcontext)) {
    require_once($CFG->libdir.'/portfoliolib.php');
    $button = new portfolio_add_button();
    $button->set_callback_options('forum_portfolio_caller', array('discussionid' => $discussion->id), 'mod_forum');
    $button = $button->to_html(PORTFOLIO_ADD_FULL_FORM, get_string('exportdiscussion', 'mod_forum'));
    $buttonextraclass = '';
    if (empty($button)) {
                $button = '&nbsp;';
        $buttonextraclass = ' noavailable';
    }
    echo html_writer::tag('div', $button, array('class' => 'discussioncontrol exporttoportfolio'.$buttonextraclass));
} else {
    echo html_writer::tag('div', '&nbsp;', array('class'=>'discussioncontrol nullcontrol'));
}

echo '<div class="discussioncontrol displaymode">';
forum_print_mode_form($discussion->id, $displaymode);
echo "</div>";

if ($forum->type != 'single' && has_capability('mod/forum:movediscussions', $modcontext)) {

    echo '<div class="discussioncontrol movediscussion">';
            $modinfo = get_fast_modinfo($course);
    if (isset($modinfo->instances['forum'])) {
        $forummenu = array();
                $forumcheck = $DB->get_records('forum', array('course' => $course->id),'', 'id, type');
        foreach ($modinfo->instances['forum'] as $forumcm) {
            if (!$forumcm->uservisible || !has_capability('mod/forum:startdiscussion',
                context_module::instance($forumcm->id))) {
                continue;
            }
            $section = $forumcm->sectionnum;
            $sectionname = get_section_name($course, $section);
            if (empty($forummenu[$section])) {
                $forummenu[$section] = array($sectionname => array());
            }
            $forumidcompare = $forumcm->instance != $forum->id;
            $forumtypecheck = $forumcheck[$forumcm->instance]->type !== 'single';
            if ($forumidcompare and $forumtypecheck) {
                $url = "/mod/forum/discuss.php?d=$discussion->id&move=$forumcm->instance&sesskey=".sesskey();
                $forummenu[$section][$sectionname][$url] = format_string($forumcm->name);
            }
        }
        if (!empty($forummenu)) {
            echo '<div class="movediscussionoption">';
            $select = new url_select($forummenu, '',
                    array('/mod/forum/discuss.php?d=' . $discussion->id => get_string("movethisdiscussionto", "forum")),
                    'forummenu', get_string('move'));
            echo $OUTPUT->render($select);
            echo "</div>";
        }
    }
	
    echo "</div>";
}

if (has_capability('mod/forum:pindiscussions', $modcontext)) {
    if ($discussion->pinned == FORUM_DISCUSSION_PINNED) {
        $pinlink = FORUM_DISCUSSION_UNPINNED;
        $pintext = get_string('discussionunpin', 'forum');
    } else {
        $pinlink = FORUM_DISCUSSION_PINNED;
        $pintext = get_string('discussionpin', 'forum');
    }
    $button = new single_button(new moodle_url('discuss.php', array('pin' => $pinlink, 'd' => $discussion->id)), $pintext, 'post');
    echo html_writer::tag('div', $OUTPUT->render($button), array('class' => 'discussioncontrol pindiscussion'));
}


echo "</div></div>";


if (!empty($forum->blockafter) && !empty($forum->blockperiod)) {
    $a = new stdClass();
    $a->blockafter  = $forum->blockafter;
    $a->blockperiod = get_string('secondstotime'.$forum->blockperiod);
    echo $OUTPUT->notification(get_string('thisforumisthrottled','forum',$a));
}

if ($forum->type == 'qanda' && !has_capability('mod/forum:viewqandawithoutposting', $modcontext) &&
            !forum_user_has_posted($forum->id,$discussion->id,$USER->id)) {
    echo $OUTPUT->notification(get_string('qandanotify', 'forum'));
}

if ($move == -1 and confirm_sesskey()) {
    echo $OUTPUT->notification(get_string('discussionmoved', 'forum', format_string($forum->name,true)), 'notifysuccess');
}

$canrate = has_capability('mod/forum:rate', $modcontext);
forum_print_discussion($course, $cm, $forum, $discussion, $post, $displaymode, $canreply, $canrate);

echo $neighbourlinks;

$PAGE->requires->yui_module('moodle-mod_forum-subscriptiontoggle', 'Y.M.mod_forum.subscriptiontoggle.init');

echo $OUTPUT->footer();
