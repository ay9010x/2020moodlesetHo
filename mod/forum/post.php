<?php




require_once('../../config.php');
require_once('lib.php');
require_once($CFG->libdir.'/completionlib.php');

$reply   = optional_param('reply', 0, PARAM_INT);
$forum   = optional_param('forum', 0, PARAM_INT);
$edit    = optional_param('edit', 0, PARAM_INT);
$delete  = optional_param('delete', 0, PARAM_INT);
$prune   = optional_param('prune', 0, PARAM_INT);
$name    = optional_param('name', '', PARAM_CLEAN);
$confirm = optional_param('confirm', 0, PARAM_INT);
$groupid = optional_param('groupid', null, PARAM_INT);

$PAGE->set_url('/mod/forum/post.php', array(
        'reply' => $reply,
        'forum' => $forum,
        'edit'  => $edit,
        'delete'=> $delete,
        'prune' => $prune,
        'name'  => $name,
        'confirm'=>$confirm,
        'groupid'=>$groupid,
        ));
$page_params = array('reply'=>$reply, 'forum'=>$forum, 'edit'=>$edit);

$sitecontext = context_system::instance();

if (!isloggedin() or isguestuser()) {

    if (!isloggedin() and !get_local_referer()) {
                require_login();
    }

    if (!empty($forum)) {              if (! $forum = $DB->get_record('forum', array('id' => $forum))) {
            print_error('invalidforumid', 'forum');
        }
    } else if (!empty($reply)) {              if (! $parent = forum_get_post_full($reply)) {
            print_error('invalidparentpostid', 'forum');
        }
        if (! $discussion = $DB->get_record('forum_discussions', array('id' => $parent->discussion))) {
            print_error('notpartofdiscussion', 'forum');
        }
        if (! $forum = $DB->get_record('forum', array('id' => $discussion->forum))) {
            print_error('invalidforumid');
        }
    }
    if (! $course = $DB->get_record('course', array('id' => $forum->course))) {
        print_error('invalidcourseid');
    }

    if (!$cm = get_coursemodule_from_instance('forum', $forum->id, $course->id)) {         print_error('invalidcoursemodule');
    } else {
        $modcontext = context_module::instance($cm->id);
    }

    $PAGE->set_cm($cm, $course, $forum);
    $PAGE->set_context($modcontext);
    $PAGE->set_title($course->shortname);
    $PAGE->set_heading($course->fullname);
    $referer = get_local_referer(false);

    echo $OUTPUT->header();
    echo $OUTPUT->confirm(get_string('noguestpost', 'forum').'<br /><br />'.get_string('liketologin'), get_login_url(), $referer);
    echo $OUTPUT->footer();
    exit;
}

require_login(0, false);
if (!empty($forum)) {          if (! $forum = $DB->get_record("forum", array("id" => $forum))) {
        print_error('invalidforumid', 'forum');
    }
    if (! $course = $DB->get_record("course", array("id" => $forum->course))) {
        print_error('invalidcourseid');
    }
    if (! $cm = get_coursemodule_from_instance("forum", $forum->id, $course->id)) {
        print_error("invalidcoursemodule");
    }

        $modcontext    = context_module::instance($cm->id);
    $coursecontext = context_course::instance($course->id);

    if (! forum_user_can_post_discussion($forum, $groupid, -1, $cm)) {
        if (!isguestuser()) {
            if (!is_enrolled($coursecontext)) {
                if (enrol_selfenrol_available($course->id)) {
                    $SESSION->wantsurl = qualified_me();
                    $SESSION->enrolcancel = get_local_referer(false);
                    redirect(new moodle_url('/enrol/index.php', array('id' => $course->id,
                        'returnurl' => '/mod/forum/view.php?f=' . $forum->id)),
                        get_string('youneedtoenrol'));
                }
            }
        }
        print_error('nopostforum', 'forum');
    }

    if (!$cm->visible and !has_capability('moodle/course:viewhiddenactivities', $modcontext)) {
        print_error("activityiscurrentlyhidden");
    }

    $SESSION->fromurl = get_local_referer(false);


    $post = new stdClass();
    $post->course        = $course->id;
    $post->forum         = $forum->id;
    $post->discussion    = 0;               $post->parent        = 0;
    $post->subject       = '';
    $post->userid        = $USER->id;
    $post->message       = '';
    $post->messageformat = editors_get_preferred_format();
    $post->messagetrust  = 0;

    if (isset($groupid)) {
        $post->groupid = $groupid;
    } else {
        $post->groupid = groups_get_activity_group($cm);
    }

        unset($SESSION->fromdiscussion);

} else if (!empty($reply)) {
    if (! $parent = forum_get_post_full($reply)) {
        print_error('invalidparentpostid', 'forum');
    }
    if (! $discussion = $DB->get_record("forum_discussions", array("id" => $parent->discussion))) {
        print_error('notpartofdiscussion', 'forum');
    }
    if (! $forum = $DB->get_record("forum", array("id" => $discussion->forum))) {
        print_error('invalidforumid', 'forum');
    }
    if (! $course = $DB->get_record("course", array("id" => $discussion->course))) {
        print_error('invalidcourseid');
    }
    if (! $cm = get_coursemodule_from_instance("forum", $forum->id, $course->id)) {
        print_error('invalidcoursemodule');
    }

        $PAGE->set_cm($cm, $course, $forum);

        $modcontext    = context_module::instance($cm->id);
    $coursecontext = context_course::instance($course->id);

    if (! forum_user_can_post($forum, $discussion, $USER, $cm, $course, $modcontext)) {
        if (!isguestuser()) {
            if (!is_enrolled($coursecontext)) {                  $SESSION->wantsurl = qualified_me();
                $SESSION->enrolcancel = get_local_referer(false);
                redirect(new moodle_url('/enrol/index.php', array('id' => $course->id,
                    'returnurl' => '/mod/forum/view.php?f=' . $forum->id)),
                    get_string('youneedtoenrol'));
            }
        }
        print_error('nopostforum', 'forum');
    }

        if (isset($cm->groupmode) && empty($course->groupmodeforce)) {
        $groupmode =  $cm->groupmode;
    } else {
        $groupmode = $course->groupmode;
    }
    if ($groupmode == SEPARATEGROUPS and !has_capability('moodle/site:accessallgroups', $modcontext)) {
        if ($discussion->groupid == -1) {
            print_error('nopostforum', 'forum');
        } else {
            if (!groups_is_member($discussion->groupid)) {
                print_error('nopostforum', 'forum');
            }
        }
    }

    if (!$cm->visible and !has_capability('moodle/course:viewhiddenactivities', $modcontext)) {
        print_error("activityiscurrentlyhidden");
    }


    $post = new stdClass();
    $post->course      = $course->id;
    $post->forum       = $forum->id;
    $post->discussion  = $parent->discussion;
    $post->parent      = $parent->id;
    $post->subject     = $parent->subject;
    $post->userid      = $USER->id;
    $post->message     = '';

    $post->groupid = ($discussion->groupid == -1) ? 0 : $discussion->groupid;

    $strre = get_string('re', 'forum');
    if (!(substr($post->subject, 0, strlen($strre)) == $strre)) {
        $post->subject = $strre.' '.$post->subject;
    }

        unset($SESSION->fromdiscussion);

} else if (!empty($edit)) {
    if (! $post = forum_get_post_full($edit)) {
        print_error('invalidpostid', 'forum');
    }
    if ($post->parent) {
        if (! $parent = forum_get_post_full($post->parent)) {
            print_error('invalidparentpostid', 'forum');
        }
    }

    if (! $discussion = $DB->get_record("forum_discussions", array("id" => $post->discussion))) {
        print_error('notpartofdiscussion', 'forum');
    }
    if (! $forum = $DB->get_record("forum", array("id" => $discussion->forum))) {
        print_error('invalidforumid', 'forum');
    }
    if (! $course = $DB->get_record("course", array("id" => $discussion->course))) {
        print_error('invalidcourseid');
    }
    if (!$cm = get_coursemodule_from_instance("forum", $forum->id, $course->id)) {
        print_error('invalidcoursemodule');
    } else {
        $modcontext = context_module::instance($cm->id);
    }

    $PAGE->set_cm($cm, $course, $forum);

    if (!($forum->type == 'news' && !$post->parent && $discussion->timestart > time())) {
        if (((time() - $post->created) > $CFG->maxeditingtime) and
                    !has_capability('mod/forum:editanypost', $modcontext)) {
            print_error('maxtimehaspassed', 'forum', '', format_time($CFG->maxeditingtime));
        }
    }
    if (($post->userid <> $USER->id) and
                !has_capability('mod/forum:editanypost', $modcontext)) {
        print_error('cannoteditposts', 'forum');
    }


        $post->edit   = $edit;
    $post->course = $course->id;
    $post->forum  = $forum->id;
    $post->groupid = ($discussion->groupid == -1) ? 0 : $discussion->groupid;

    $post = trusttext_pre_edit($post, 'message', $modcontext);

        unset($SESSION->fromdiscussion);

}else if (!empty($delete)) {
    if (! $post = forum_get_post_full($delete)) {
        print_error('invalidpostid', 'forum');
    }
    if (! $discussion = $DB->get_record("forum_discussions", array("id" => $post->discussion))) {
        print_error('notpartofdiscussion', 'forum');
    }
    if (! $forum = $DB->get_record("forum", array("id" => $discussion->forum))) {
        print_error('invalidforumid', 'forum');
    }
    if (!$cm = get_coursemodule_from_instance("forum", $forum->id, $forum->course)) {
        print_error('invalidcoursemodule');
    }
    if (!$course = $DB->get_record('course', array('id' => $forum->course))) {
        print_error('invalidcourseid');
    }

    require_login($course, false, $cm);
    $modcontext = context_module::instance($cm->id);

    if ( !(($post->userid == $USER->id && has_capability('mod/forum:deleteownpost', $modcontext))
                || has_capability('mod/forum:deleteanypost', $modcontext)) ) {
        print_error('cannotdeletepost', 'forum');
    }


    $replycount = forum_count_replies($post);

    if (!empty($confirm) && confirm_sesskey()) {                    $timepassed = time() - $post->created;
        if (($timepassed > $CFG->maxeditingtime) && !has_capability('mod/forum:deleteanypost', $modcontext)) {
            print_error("cannotdeletepost", "forum",
                        forum_go_back_to(new moodle_url("/mod/forum/discuss.php", array('d' => $post->discussion))));
        }

        if ($post->totalscore) {
            notice(get_string('couldnotdeleteratings', 'rating'),
                   forum_go_back_to(new moodle_url("/mod/forum/discuss.php", array('d' => $post->discussion))));

        } else if ($replycount && !has_capability('mod/forum:deleteanypost', $modcontext)) {
            print_error("couldnotdeletereplies", "forum",
                        forum_go_back_to(new moodle_url("/mod/forum/discuss.php", array('d' => $post->discussion))));

        } else {
            if (! $post->parent) {                  if ($forum->type == 'single') {
                    notice("Sorry, but you are not allowed to delete that discussion!",
                           forum_go_back_to(new moodle_url("/mod/forum/discuss.php", array('d' => $post->discussion))));
                }
                forum_delete_discussion($discussion, false, $course, $cm, $forum);

                $params = array(
                    'objectid' => $discussion->id,
                    'context' => $modcontext,
                    'other' => array(
                        'forumid' => $forum->id,
                    )
                );

                $event = \mod_forum\event\discussion_deleted::create($params);
                $event->add_record_snapshot('forum_discussions', $discussion);
                $event->trigger();

                redirect("view.php?f=$discussion->forum");

            } else if (forum_delete_post($post, has_capability('mod/forum:deleteanypost', $modcontext),
                $course, $cm, $forum)) {

                if ($forum->type == 'single') {
                                                                                $discussionurl = new moodle_url("/mod/forum/view.php", array('f' => $forum->id));
                } else {
                    $discussionurl = new moodle_url("/mod/forum/discuss.php", array('d' => $discussion->id));
                }

                redirect(forum_go_back_to($discussionurl));
            } else {
                print_error('errorwhiledelete', 'forum');
            }
        }


    } else {
        forum_set_return();
        $PAGE->navbar->add(get_string('delete', 'forum'));
        $PAGE->set_title($course->shortname);
        $PAGE->set_heading($course->fullname);

        if ($replycount) {
            if (!has_capability('mod/forum:deleteanypost', $modcontext)) {
                print_error("couldnotdeletereplies", "forum",
                      forum_go_back_to(new moodle_url('/mod/forum/discuss.php', array('d' => $post->discussion), 'p'.$post->id)));
            }
            echo $OUTPUT->header();
            echo $OUTPUT->heading(format_string($forum->name), 2);
            echo $OUTPUT->confirm(get_string("deletesureplural", "forum", $replycount+1),
                         "post.php?delete=$delete&confirm=$delete",
                         $CFG->wwwroot.'/mod/forum/discuss.php?d='.$post->discussion.'#p'.$post->id);

            forum_print_post($post, $discussion, $forum, $cm, $course, false, false, false);

            if (empty($post->edit)) {
                $forumtracked = forum_tp_is_tracked($forum);
                $posts = forum_get_all_discussion_posts($discussion->id, "created ASC", $forumtracked);
                forum_print_posts_nested($course, $cm, $forum, $discussion, $post, false, false, $forumtracked, $posts);
            }
        } else {
            echo $OUTPUT->header();
            echo $OUTPUT->heading(format_string($forum->name), 2);
            echo $OUTPUT->confirm(get_string("deletesure", "forum", $replycount),
                         "post.php?delete=$delete&confirm=$delete",
                         $CFG->wwwroot.'/mod/forum/discuss.php?d='.$post->discussion.'#p'.$post->id);
            forum_print_post($post, $discussion, $forum, $cm, $course, false, false, false);
        }

    }
    echo $OUTPUT->footer();
    die;


} else if (!empty($prune)) {
    if (!$post = forum_get_post_full($prune)) {
        print_error('invalidpostid', 'forum');
    }
    if (!$discussion = $DB->get_record("forum_discussions", array("id" => $post->discussion))) {
        print_error('notpartofdiscussion', 'forum');
    }
    if (!$forum = $DB->get_record("forum", array("id" => $discussion->forum))) {
        print_error('invalidforumid', 'forum');
    }
    if ($forum->type == 'single') {
        print_error('cannotsplit', 'forum');
    }
    if (!$post->parent) {
        print_error('alreadyfirstpost', 'forum');
    }
    if (!$cm = get_coursemodule_from_instance("forum", $forum->id, $forum->course)) {         print_error('invalidcoursemodule');
    } else {
        $modcontext = context_module::instance($cm->id);
    }
    if (!has_capability('mod/forum:splitdiscussions', $modcontext)) {
        print_error('cannotsplit', 'forum');
    }

    $PAGE->set_cm($cm);
    $PAGE->set_context($modcontext);

    $prunemform = new mod_forum_prune_form(null, array('prune' => $prune, 'confirm' => $prune));


    if ($prunemform->is_cancelled()) {
        redirect(forum_go_back_to(new moodle_url("/mod/forum/discuss.php", array('d' => $post->discussion))));
    } else if ($fromform = $prunemform->get_data()) {
                $newdiscussion = new stdClass();
        $newdiscussion->course       = $discussion->course;
        $newdiscussion->forum        = $discussion->forum;
        $newdiscussion->name         = $name;
        $newdiscussion->firstpost    = $post->id;
        $newdiscussion->userid       = $discussion->userid;
        $newdiscussion->groupid      = $discussion->groupid;
        $newdiscussion->assessed     = $discussion->assessed;
        $newdiscussion->usermodified = $post->userid;
        $newdiscussion->timestart    = $discussion->timestart;
        $newdiscussion->timeend      = $discussion->timeend;

        $newid = $DB->insert_record('forum_discussions', $newdiscussion);

        $newpost = new stdClass();
        $newpost->id      = $post->id;
        $newpost->parent  = 0;
        $newpost->subject = $name;

        $DB->update_record("forum_posts", $newpost);

        forum_change_discussionid($post->id, $newid);

                forum_discussion_update_last_post($discussion->id);
        forum_discussion_update_last_post($newid);

                $params = array(
            'context' => $modcontext,
            'objectid' => $discussion->id,
            'other' => array(
                'forumid' => $forum->id,
            )
        );
        $event = \mod_forum\event\discussion_updated::create($params);
        $event->trigger();

        $params = array(
            'context' => $modcontext,
            'objectid' => $newid,
            'other' => array(
                'forumid' => $forum->id,
            )
        );
        $event = \mod_forum\event\discussion_created::create($params);
        $event->trigger();

        $params = array(
            'context' => $modcontext,
            'objectid' => $post->id,
            'other' => array(
                'discussionid' => $newid,
                'forumid' => $forum->id,
                'forumtype' => $forum->type,
            )
        );
        $event = \mod_forum\event\post_updated::create($params);
        $event->add_record_snapshot('forum_discussions', $discussion);
        $event->trigger();

        redirect(forum_go_back_to(new moodle_url("/mod/forum/discuss.php", array('d' => $newid))));

    } else {
                $course = $DB->get_record('course', array('id' => $forum->course));
        $PAGE->navbar->add(format_string($post->subject, true), new moodle_url('/mod/forum/discuss.php', array('d'=>$discussion->id)));
        $PAGE->navbar->add(get_string("prune", "forum"));
        $PAGE->set_title(format_string($discussion->name).": ".format_string($post->subject));
        $PAGE->set_heading($course->fullname);
        echo $OUTPUT->header();
        echo $OUTPUT->heading(format_string($forum->name), 2);
        echo $OUTPUT->heading(get_string('pruneheading', 'forum'), 3);

        $prunemform->display();

        forum_print_post($post, $discussion, $forum, $cm, $course, false, false, false);
    }

    echo $OUTPUT->footer();
    die;
} else {
    print_error('unknowaction');

}

if (!isset($coursecontext)) {
        $coursecontext = context_course::instance($forum->course);
}



if (!$cm = get_coursemodule_from_instance('forum', $forum->id, $course->id)) {     print_error('invalidcoursemodule');
}
$modcontext = context_module::instance($cm->id);
require_login($course, false, $cm);

if (isguestuser()) {
        print_error('noguest');
}

if (!isset($forum->maxattachments)) {      $forum->maxattachments = 3;
}

$thresholdwarning = forum_check_throttling($forum, $cm);
$mform_post = new mod_forum_post_form('post.php', array('course' => $course,
                                                        'cm' => $cm,
                                                        'coursecontext' => $coursecontext,
                                                        'modcontext' => $modcontext,
                                                        'forum' => $forum,
                                                        'post' => $post,
                                                        'subscribe' => \mod_forum\subscriptions::is_subscribed($USER->id, $forum,
                                                                null, $cm),
                                                        'thresholdwarning' => $thresholdwarning,
                                                        'edit' => $edit), 'post', '', array('id' => 'mformforum'));

$draftitemid = file_get_submitted_draft_itemid('attachments');
file_prepare_draft_area($draftitemid, $modcontext->id, 'mod_forum', 'attachment', empty($post->id)?null:$post->id, mod_forum_post_form::attachment_options($forum));


if ($USER->id != $post->userid) {       $data = new stdClass();
    $data->date = userdate($post->modified);
    if ($post->messageformat == FORMAT_HTML) {
        $data->name = '<a href="'.$CFG->wwwroot.'/user/view.php?id='.$USER->id.'&course='.$post->course.'">'.
                       fullname($USER).'</a>';
        $post->message .= '<p><span class="edited">('.get_string('editedby', 'forum', $data).')</span></p>';
    } else {
        $data->name = fullname($USER);
        $post->message .= "\n\n(".get_string('editedby', 'forum', $data).')';
    }
    unset($data);
}

$formheading = '';
if (!empty($parent)) {
    $heading = get_string("yourreply", "forum");
    $formheading = get_string('reply', 'forum');
} else {
    if ($forum->type == 'qanda') {
        $heading = get_string('yournewquestion', 'forum');
    } else {
        $heading = get_string('yournewtopic', 'forum');
    }
}

$postid = empty($post->id) ? null : $post->id;
$draftid_editor = file_get_submitted_draft_itemid('message');
$currenttext = file_prepare_draft_area($draftid_editor, $modcontext->id, 'mod_forum', 'post', $postid, mod_forum_post_form::editor_options($modcontext, $postid), $post->message);

$manageactivities = has_capability('moodle/course:manageactivities', $coursecontext);
if (\mod_forum\subscriptions::subscription_disabled($forum) && !$manageactivities) {
        $discussionsubscribe = false;
} else if (\mod_forum\subscriptions::is_forcesubscribed($forum)) {
        $discussionsubscribe = true;
} else {
    if (isset($discussion) && \mod_forum\subscriptions::is_subscribed($USER->id, $forum, $discussion->id, $cm)) {
                $discussionsubscribe = true;
    } else if (!isset($discussion) && \mod_forum\subscriptions::is_subscribed($USER->id, $forum, null, $cm)) {
                $discussionsubscribe = true;
    } else {
                $discussionsubscribe = $USER->autosubscribe;
    }
}

$mform_post->set_data(array(        'attachments'=>$draftitemid,
                                    'general'=>$heading,
                                    'subject'=>$post->subject,
                                    'message'=>array(
                                        'text'=>$currenttext,
                                        'format'=>empty($post->messageformat) ? editors_get_preferred_format() : $post->messageformat,
                                        'itemid'=>$draftid_editor
                                    ),
                                    'discussionsubscribe' => $discussionsubscribe,
                                    'mailnow'=>!empty($post->mailnow),
                                    'userid'=>$post->userid,
                                    'parent'=>$post->parent,
                                    'discussion'=>$post->discussion,
                                    'course'=>$course->id) +
                                    $page_params +

                            (isset($post->format)?array(
                                    'format'=>$post->format):
                                array())+

                            (isset($discussion->timestart)?array(
                                    'timestart'=>$discussion->timestart):
                                array())+

                            (isset($discussion->timeend)?array(
                                    'timeend'=>$discussion->timeend):
                                array())+

                            (isset($discussion->pinned) ? array(
                                     'pinned' => $discussion->pinned) :
                                array()) +

                            (isset($post->groupid)?array(
                                    'groupid'=>$post->groupid):
                                array())+

                            (isset($discussion->id)?
                                    array('discussion'=>$discussion->id):
                                    array()));

if ($mform_post->is_cancelled()) {
    if (!isset($discussion->id) || $forum->type === 'qanda') {
                redirect(new moodle_url('/mod/forum/view.php', array('f' => $forum->id)));
    } else {
        redirect(new moodle_url('/mod/forum/discuss.php', array('d' => $discussion->id)));
    }
} else if ($fromform = $mform_post->get_data()) {

    if (empty($SESSION->fromurl)) {
        $errordestination = "$CFG->wwwroot/mod/forum/view.php?f=$forum->id";
    } else {
        $errordestination = $SESSION->fromurl;
    }

    $fromform->itemid        = $fromform->message['itemid'];
    $fromform->messageformat = $fromform->message['format'];
    $fromform->message       = $fromform->message['text'];
        $fromform->messagetrust  = trusttext_trusted($modcontext);

    if ($fromform->edit) {                   unset($fromform->groupid);
        $fromform->id = $fromform->edit;
        $message = '';

                if (!$realpost = $DB->get_record('forum_posts', array('id' => $fromform->id))) {
            $realpost = new stdClass();
            $realpost->userid = -1;
        }


                                        if ( !(($realpost->userid == $USER->id && (has_capability('mod/forum:replypost', $modcontext)
                            || has_capability('mod/forum:startdiscussion', $modcontext))) ||
                            has_capability('mod/forum:editanypost', $modcontext)) ) {
            print_error('cannotupdatepost', 'forum');
        }

                if (isset($fromform->groupinfo) && has_capability('mod/forum:movediscussions', $modcontext)) {
            if (empty($fromform->groupinfo)) {
                $fromform->groupinfo = -1;
            }

            if (!forum_user_can_post_discussion($forum, $fromform->groupinfo, null, $cm, $modcontext)) {
                print_error('cannotupdatepost', 'forum');
            }

            $DB->set_field('forum_discussions' ,'groupid' , $fromform->groupinfo, array('firstpost' => $fromform->id));
        }
                if (!$fromform->parent) {
            if (has_capability('mod/forum:pindiscussions', $modcontext)) {
                                $fromform->pinned = !empty($fromform->pinned) ? FORUM_DISCUSSION_PINNED : FORUM_DISCUSSION_UNPINNED;
            } else {
                                unset($fromform->pinned);
            }
        }
        $updatepost = $fromform;         $updatepost->forum = $forum->id;
        if (!forum_update_post($updatepost, $mform_post)) {
            print_error("couldnotupdate", "forum", $errordestination);
        }

                if (($forum->type == 'single') && ($updatepost->parent == '0')){             $forum->intro = $updatepost->message;
            $forum->timemodified = time();
            $DB->update_record("forum", $forum);
        }

        if ($realpost->userid == $USER->id) {
            $message .= get_string("postupdated", "forum");
        } else {
            $realuser = $DB->get_record('user', array('id' => $realpost->userid));
            $message .= get_string("editedpostupdated", "forum", fullname($realuser));
        }

        $subscribemessage = forum_post_subscription($fromform, $forum, $discussion);
        if ($forum->type == 'single') {
                                                $discussionurl = new moodle_url("/mod/forum/view.php", array('f' => $forum->id));
        } else {
            $discussionurl = new moodle_url("/mod/forum/discuss.php", array('d' => $discussion->id), 'p' . $fromform->id);
        }

        $params = array(
            'context' => $modcontext,
            'objectid' => $fromform->id,
            'other' => array(
                'discussionid' => $discussion->id,
                'forumid' => $forum->id,
                'forumtype' => $forum->type,
            )
        );

        if ($realpost->userid !== $USER->id) {
            $params['relateduserid'] = $realpost->userid;
        }

        $event = \mod_forum\event\post_updated::create($params);
        $event->add_record_snapshot('forum_discussions', $discussion);
        $event->trigger();

        redirect(
                forum_go_back_to($discussionurl),
                $message . $subscribemessage,
                null,
                \core\output\notification::NOTIFY_SUCCESS
            );

    } else if ($fromform->discussion) {                 forum_check_blocking_threshold($thresholdwarning);

        unset($fromform->groupid);
        $message = '';
        $addpost = $fromform;
        $addpost->forum=$forum->id;
        if ($fromform->id = forum_add_new_post($addpost, $mform_post)) {
            $subscribemessage = forum_post_subscription($fromform, $forum, $discussion);

            if (!empty($fromform->mailnow)) {
                $message .= get_string("postmailnow", "forum");
            } else {
                $message .= '<p>'.get_string("postaddedsuccess", "forum") . '</p>';
                $message .= '<p>'.get_string("postaddedtimeleft", "forum", format_time($CFG->maxeditingtime)) . '</p>';
            }

            if ($forum->type == 'single') {
                                                                $discussionurl = new moodle_url("/mod/forum/view.php", array('f' => $forum->id), 'p'.$fromform->id);
            } else {
                $discussionurl = new moodle_url("/mod/forum/discuss.php", array('d' => $discussion->id), 'p'.$fromform->id);
            }

            $params = array(
                'context' => $modcontext,
                'objectid' => $fromform->id,
                'other' => array(
                    'discussionid' => $discussion->id,
                    'forumid' => $forum->id,
                    'forumtype' => $forum->type,
                )
            );
            $event = \mod_forum\event\post_created::create($params);
            $event->add_record_snapshot('forum_posts', $fromform);
            $event->add_record_snapshot('forum_discussions', $discussion);
            $event->trigger();

                        $completion=new completion_info($course);
            if($completion->is_enabled($cm) &&
                ($forum->completionreplies || $forum->completionposts)) {
                $completion->update_state($cm,COMPLETION_COMPLETE);
            }

            redirect(
                    forum_go_back_to($discussionurl),
                    $message . $subscribemessage,
                    null,
                    \core\output\notification::NOTIFY_SUCCESS
                );

        } else {
            print_error("couldnotadd", "forum", $errordestination);
        }
        exit;

    } else {                 $redirectto = new moodle_url('view.php', array('f' => $fromform->forum));

        $fromform->mailnow = empty($fromform->mailnow) ? 0 : 1;

        $discussion = $fromform;
        $discussion->name = $fromform->subject;

        $newstopic = false;
        if ($forum->type == 'news' && !$fromform->parent) {
            $newstopic = true;
        }
        $discussion->timestart = $fromform->timestart;
        $discussion->timeend = $fromform->timeend;

        if (has_capability('mod/forum:pindiscussions', $modcontext) && !empty($fromform->pinned)) {
            $discussion->pinned = FORUM_DISCUSSION_PINNED;
        } else {
            $discussion->pinned = FORUM_DISCUSSION_UNPINNED;
        }

        $allowedgroups = array();
        $groupstopostto = array();

                if (isset($fromform->posttomygroups)) {
                        require_capability('mod/forum:canposttomygroups', $modcontext);

                                    $allowedgroups = groups_get_activity_allowed_groups($cm);
            foreach ($allowedgroups as $groupid => $group) {
                if (forum_user_can_post_discussion($forum, $groupid, -1, $cm, $modcontext)) {
                    $groupstopostto[] = $groupid;
                }
            }
        } else if (isset($fromform->groupinfo)) {
                        $groupstopostto[] = $fromform->groupinfo;
            $redirectto->param('group', $fromform->groupinfo);
        } else if (isset($fromform->groupid) && !empty($fromform->groupid)) {
                        $groupstopostto[] = $fromform->groupid;
            $redirectto->param('group', $fromform->groupid);
        } else {
                        $groupstopostto[] = -1;
        }

                forum_check_blocking_threshold($thresholdwarning);

        foreach ($groupstopostto as $group) {
            if (!forum_user_can_post_discussion($forum, $group, -1, $cm, $modcontext)) {
                print_error('cannotcreatediscussion', 'forum');
            }

            $discussion->groupid = $group;
            $message = '';
            if ($discussion->id = forum_add_discussion($discussion, $mform_post)) {

                $params = array(
                    'context' => $modcontext,
                    'objectid' => $discussion->id,
                    'other' => array(
                        'forumid' => $forum->id,
                    )
                );
                $event = \mod_forum\event\discussion_created::create($params);
                $event->add_record_snapshot('forum_discussions', $discussion);
                $event->trigger();

                if ($fromform->mailnow) {
                    $message .= get_string("postmailnow", "forum");
                } else {
                    $message .= '<p>'.get_string("postaddedsuccess", "forum") . '</p>';
                    $message .= '<p>'.get_string("postaddedtimeleft", "forum", format_time($CFG->maxeditingtime)) . '</p>';
                }

                $subscribemessage = forum_post_subscription($fromform, $forum, $discussion);
            } else {
                print_error("couldnotadd", "forum", $errordestination);
            }
        }

                $completion = new completion_info($course);
        if ($completion->is_enabled($cm) &&
                ($forum->completiondiscussions || $forum->completionposts)) {
            $completion->update_state($cm, COMPLETION_COMPLETE);
        }

                redirect(
                forum_go_back_to($redirectto->out()),
                $message . $subscribemessage,
                null,
                \core\output\notification::NOTIFY_SUCCESS
            );
    }
}





if ($post->discussion) {
    if (! $toppost = $DB->get_record("forum_posts", array("discussion" => $post->discussion, "parent" => 0))) {
        print_error('cannotfindparentpost', 'forum', '', $post->id);
    }
} else {
    $toppost = new stdClass();
    $toppost->subject = ($forum->type == "news") ? get_string("addanewtopic", "forum") :
                                                   get_string("addanewdiscussion", "forum");
}

if (empty($post->edit)) {
    $post->edit = '';
}

if (empty($discussion->name)) {
    if (empty($discussion)) {
        $discussion = new stdClass();
    }
    $discussion->name = $forum->name;
}
if ($forum->type == 'single') {
                $strdiscussionname = '';
} else {
        $strdiscussionname = format_string($discussion->name).':';
}

$forcefocus = empty($reply) ? NULL : 'message';

if (!empty($discussion->id)) {
    $PAGE->navbar->add(format_string($toppost->subject, true), "discuss.php?d=$discussion->id");
}

if ($post->parent) {
    $PAGE->navbar->add(get_string('reply', 'forum'));
}

if ($edit) {
    $PAGE->navbar->add(get_string('edit', 'forum'));
}

$PAGE->set_title("$course->shortname: $strdiscussionname ".format_string($toppost->subject));
$PAGE->set_heading($course->fullname);

echo $OUTPUT->header();
echo $OUTPUT->heading(format_string($forum->name), 2);

if (!empty($parent) && !forum_user_can_see_post($forum, $discussion, $post, null, $cm)) {
    print_error('cannotreply', 'forum');
}
if (empty($parent) && empty($edit) && !forum_user_can_post_discussion($forum, $groupid, -1, $cm, $modcontext)) {
    print_error('cannotcreatediscussion', 'forum');
}

if ($forum->type == 'qanda'
            && !has_capability('mod/forum:viewqandawithoutposting', $modcontext)
            && !empty($discussion->id)
            && !forum_user_has_posted($forum->id, $discussion->id, $USER->id)) {
    echo $OUTPUT->notification(get_string('qandanotify','forum'));
}

if (!empty($thresholdwarning) && !$edit) {
        forum_check_blocking_threshold($thresholdwarning);
}

if (!empty($parent)) {
    if (!$discussion = $DB->get_record('forum_discussions', array('id' => $parent->discussion))) {
        print_error('notpartofdiscussion', 'forum');
    }

    forum_print_post($parent, $discussion, $forum, $cm, $course, false, false, false);
    if (empty($post->edit)) {
        if ($forum->type != 'qanda' || forum_user_can_see_discussion($forum, $discussion, $modcontext)) {
            $forumtracked = forum_tp_is_tracked($forum);
            $posts = forum_get_all_discussion_posts($discussion->id, "created ASC", $forumtracked);
            forum_print_posts_threaded($course, $cm, $forum, $discussion, $parent, 0, false, $forumtracked, $posts);
        }
    }
} else {
    if (!empty($forum->intro)) {
        echo $OUTPUT->box(format_module_intro('forum', $forum, $cm->id), 'generalbox', 'intro');

        if (!empty($CFG->enableplagiarism)) {
            require_once($CFG->libdir.'/plagiarismlib.php');
            echo plagiarism_print_disclosure($cm->id);
        }
    }
}

if (!empty($formheading)) {
    echo $OUTPUT->heading($formheading, 2, array('class' => 'accesshide'));
}
$mform_post->display();

echo $OUTPUT->footer();
