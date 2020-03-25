<?php
	require_once('../../../config.php');
    require_once('../lib.php');
    require_once($CFG->libdir.'/completionlib.php');
	require("php/my_functions.php");

	//資料庫連接 (MySQL _ Moodle)
	$dbhost = 'localhost:3306';
	$dbuser = 'root';
	$dbpass = 'la2391';
	$dbname = 'moodle';

	$conn = mysql_connect($dbhost, $dbuser, $dbpass) ;
	mysql_query("SET NAMES 'UTF8'");
	mysql_select_db($dbname);

	if (!$conn) {
		die(' 連線失敗，輸出錯誤訊息 : ' . mysql_error());
	}

$d      = required_param('d', PARAM_INT);
$parent = optional_param('parent', 0, PARAM_INT);
$mode   = optional_param('mode', 0, PARAM_INT);
$move   = optional_param('move', 0, PARAM_INT);
$mark   = optional_param('mark', '', PARAM_ALPHA);
$postid = optional_param('postid', 0, PARAM_INT);
$pin    = optional_param('pin', -1, PARAM_INT);
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

?>
<!DOCTYPE html>
<html>

<head>

    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />

    <!-- Script Here -->
    <script src="jquery-3.3.1.min.js" type="text/javascript"></script>
    <script src="/google-analytics/config/exp-snifs-2018.js" type="text/javascript"></script>
<script type="text/javascript">

function refreshFrame(){
    document.getElementById('snifs').contentWindow.location.reload(true);
}

</script>
    <!-- CSS Here -->
    <link rel="stylesheet" href="css/main.css" />
	<link rel="stylesheet" href="css/article.css" />
	<link rel="stylesheet" href="css/google-custom-search.css" />
    <link rel="stylesheet" type="text/css" href="semantic-ui/semantic.min.css">
    <!-- bootstrap Here -->
	<!--
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.1.0/css/bootstrap.min.css" integrity="sha384-9gVQ4dYFwwWSjIDZnLEWnxCjeSWFphJiwGPXr1jddIhOegiu1FwO5qRGvFXOdJZ4" crossorigin="anonymous">
	-->
</head>

<body>
	<div class="ui stackable menu">
	<div class="item">
		<h1 class="ui header" ondblclick="show_submit_report();">SNIFS</h1>
	</div>
	<!--div class="item">
		<?php echo $username ?>
	</div-->


	<div class="item">
		<!--
		/snifs-personal-layout/snifs-personal-layout.php?layout=group
		<button id="btnDiscuss" class="ui left attached button">課程討論</button>
		<button id="btnTop" class="right attached ui button">跳到頁首</button>
		-->
		<button id="btnRe" onclick="javascript:refreshFrame();"  class="ui button">回饋圖重新整理</button>
	</div>

</div>

    <div class="MainContainer ui grid">
			<div class="eight wide column">
				<?php
				$src="mutirain.gif";
				//$forum->id討論板ID,$d討論主題ID;//左邊的圖
				//找討論內容
				/*$content= "SELECT message FROM mdl_forum_posts WHERE discussion =".$d;
				$result_content = mysql_query($content);
				$num_content = mysql_num_rows($result_content);

				for ($i = 0; $i < $num_content; $i++){
					$row_content= mysql_fetch_row($result_content);
					echo $row_content[0];
				}
				*/?>

				<iframe name="snifs" class="frameBox" id="snifs" src=<?php echo $src; ?> scrolling="yes"></iframe>
			</div>

			<div class="nine wide column">
				<?php
					echo '<div class="discussioncontrol displaymode">';
					forum_print_mode_form($discussion->id, $displaymode);
					echo "</div>";
					forum_print_discussion($course, $cm, $forum, $discussion, $post, $displaymode, $canreply, $canrate);
					//右邊的討論區
					/*$discussion = NULL;
					$discussions = $DB->get_records('forum_discussions', array('forum'=>$forum->id), 'timemodified ASC');

					if (!empty($discussions)) {
						$discussion = array_pop($discussions);
					}

					if ($discussion) {
						if ($mode) {
							set_user_preference("forum_displaymode", $mode);
						}

						$displaymode = get_user_preferences("forum_displaymode", $CFG->forum_displaymode);
						forum_print_mode_form($forum->id, $displaymode, $forum->type);
					}


				if (!empty($discussions) && count($discussions) > 1) {
					echo $OUTPUT->notification(get_string('warnformorepost', 'forum'));
				}
				if (! $post = forum_get_post_full($discussion->firstpost)) {
					print_error('cannotfindfirstpost', 'forum');
				}
				if ($mode) {
					set_user_preference("forum_displaymode", -1);
				}


				$canreply    = forum_user_can_post($forum, 	$discussion, $USER, $cm, $course, $context);
				$canrate     = has_capability('mod/forum:rate', $context);
				$displaymode = get_user_preferences("forum_displaymode", $CFG->forum_displaymode);



				forum_print_discussion($course, $cm, $forum, $discussion, $post, $displaymode, $canreply, $canrate);*/
			?>
			</div>

    </div>




	<!-- ------------------------------------ -->
	<script type="text/javascript" src="js/script.js"></script>
    <script src="semantic-ui/semantic.min.js" type="text/javascript"></script>
</body>
<?php
$PAGE->requires->yui_module('moodle-mod_forum-subscriptiontoggle', 'Y.M.mod_forum.subscriptiontoggle.init');

    echo $OUTPUT->footer($course);
?>
</html>
