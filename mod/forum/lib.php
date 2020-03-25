<?php



defined('MOODLE_INTERNAL') || die();


require_once(__DIR__ . '/deprecatedlib.php');
require_once($CFG->libdir.'/filelib.php');
require_once($CFG->libdir.'/eventslib.php');


define('FORUM_MODE_FLATOLDEST', 1);
define('FORUM_MODE_FLATNEWEST', -1);
define('FORUM_MODE_THREADED', 2);
define('FORUM_MODE_NESTED', 3);

define('FORUM_CHOOSESUBSCRIBE', 0);
define('FORUM_FORCESUBSCRIBE', 1);
define('FORUM_INITIALSUBSCRIBE', 2);
define('FORUM_DISALLOWSUBSCRIBE',3);


define('FORUM_TRACKING_OFF', 0);


define('FORUM_TRACKING_OPTIONAL', 1);


define('FORUM_TRACKING_FORCED', 2);

define('FORUM_MAILED_PENDING', 0);
define('FORUM_MAILED_SUCCESS', 1);
define('FORUM_MAILED_ERROR', 2);

if (!defined('FORUM_CRON_USER_CACHE')) {

    define('FORUM_CRON_USER_CACHE', 5000);
}


define('FORUM_POSTS_ALL_USER_GROUPS', -2);

define('FORUM_DISCUSSION_PINNED', 1);
define('FORUM_DISCUSSION_UNPINNED', 0);



function forum_add_instance($forum, $mform = null) {
    global $CFG, $DB;

	/*$tmp=$forum->type;

	if($forum->type == 'snifs'){
		$forum->type = 'general';
	}*/

    $forum->timemodified = time();

    if (empty($forum->assessed)) {
        $forum->assessed = 0;
    }

    if (empty($forum->ratingtime) or empty($forum->assessed)) {
        $forum->assesstimestart  = 0;
        $forum->assesstimefinish = 0;
    }

    $forum->id = $DB->insert_record('forum', $forum);
    $modcontext = context_module::instance($forum->coursemodule);


    if ($forum->type == 'single') {
		$discussion = new stdClass();
        $discussion->course        = $forum->course;
        $discussion->forum         = $forum->id;
        $discussion->name          = $forum->name;
        $discussion->assessed      = $forum->assessed;
        $discussion->message       = $forum->intro;
        $discussion->messageformat = $forum->introformat;
        $discussion->messagetrust  = trusttext_trusted(context_course::instance($forum->course));
        $discussion->mailnow       = false;
        $discussion->groupid       = -1;

        $message = '';

        $discussion->id = forum_add_discussion($discussion, null, $message);

        if ($mform and $draftid = file_get_submitted_draft_itemid('introeditor')) {
                        $discussion = $DB->get_record('forum_discussions', array('id'=>$discussion->id), '*', MUST_EXIST);
            $post = $DB->get_record('forum_posts', array('id'=>$discussion->firstpost), '*', MUST_EXIST);

            $options = array('subdirs'=>true);             $post->message = file_save_draft_area_files($draftid, $modcontext->id, 'mod_forum', 'post', $post->id, $options, $post->message);
            $DB->set_field('forum_posts', 'message', $post->message, array('id'=>$post->id));
        }
    }

    forum_grade_item_update($forum);

	/*if($tmp == 'snifs'){
		$dbhost = 'localhost:3306';
		$dbuser = 'root';
		$dbpass = 'la2391';
		$dbname = 'moodle';
		$conn = mysql_connect($dbhost, $dbuser, $dbpass) ;
		mysql_query("SET NAMES 'UTF8'");
		mysql_select_db($dbname);


		//$sql="UPDATE mdl_forum SET type='1234' WHERE id =".$forum->id;

		//$sql="UPDATE mdl_forum SET type='".$forum->id."' WHERE id =56";
		//mysql_query($sql);
		$sql="UPDATE mdl_forum SET type='snifs' WHERE id =80";
		mysql_query($sql);


	}*/
    return $forum->id;
}


function forum_instance_created($context, $forum) {
    if ($forum->forcesubscribe == FORUM_INITIALSUBSCRIBE) {
        $users = \mod_forum\subscriptions::get_potential_subscribers($context, 0, 'u.id, u.email');
        foreach ($users as $user) {
            \mod_forum\subscriptions::subscribe_user($user->id, $forum, $context);
        }
    }
}


function forum_update_instance($forum, $mform) {
    global $DB, $OUTPUT, $USER;

    $forum->timemodified = time();
    $forum->id           = $forum->instance;

    if (empty($forum->assessed)) {
        $forum->assessed = 0;
    }

    if (empty($forum->ratingtime) or empty($forum->assessed)) {
        $forum->assesstimestart  = 0;
        $forum->assesstimefinish = 0;
    }

    $oldforum = $DB->get_record('forum', array('id'=>$forum->id));

                if (($oldforum->assessed<>$forum->assessed) or ($oldforum->scale<>$forum->scale)) {
        forum_update_grades($forum);     }

    if ($forum->type == 'single') {          $discussions = $DB->get_records('forum_discussions', array('forum'=>$forum->id), 'timemodified ASC');
        if (!empty($discussions)) {
            if (count($discussions) > 1) {
                echo $OUTPUT->notification(get_string('warnformorepost', 'forum'));
            }
            $discussion = array_pop($discussions);
        } else {
                        $discussion = new stdClass();
            $discussion->course          = $forum->course;
            $discussion->forum           = $forum->id;
            $discussion->name            = $forum->name;
            $discussion->assessed        = $forum->assessed;
            $discussion->message         = $forum->intro;
            $discussion->messageformat   = $forum->introformat;
            $discussion->messagetrust    = true;
            $discussion->mailnow         = false;
            $discussion->groupid         = -1;

            $message = '';

            forum_add_discussion($discussion, null, $message);

            if (! $discussion = $DB->get_record('forum_discussions', array('forum'=>$forum->id))) {
                print_error('cannotadd', 'forum');
            }
        }
        if (! $post = $DB->get_record('forum_posts', array('id'=>$discussion->firstpost))) {
            print_error('cannotfindfirstpost', 'forum');
        }

        $cm         = get_coursemodule_from_instance('forum', $forum->id);
        $modcontext = context_module::instance($cm->id, MUST_EXIST);

        $post = $DB->get_record('forum_posts', array('id'=>$discussion->firstpost), '*', MUST_EXIST);
        $post->subject       = $forum->name;
        $post->message       = $forum->intro;
        $post->messageformat = $forum->introformat;
        $post->messagetrust  = trusttext_trusted($modcontext);
        $post->modified      = $forum->timemodified;
        $post->userid        = $USER->id;
        if ($mform and $draftid = file_get_submitted_draft_itemid('introeditor')) {
                        $options = array('subdirs'=>true);             $post->message = file_save_draft_area_files($draftid, $modcontext->id, 'mod_forum', 'post', $post->id, $options, $post->message);
        }

        $DB->update_record('forum_posts', $post);
        $discussion->name = $forum->name;
        $DB->update_record('forum_discussions', $discussion);
    }

    $DB->update_record('forum', $forum);

    $modcontext = context_module::instance($forum->coursemodule);
    if (($forum->forcesubscribe == FORUM_INITIALSUBSCRIBE) && ($oldforum->forcesubscribe <> $forum->forcesubscribe)) {
        $users = \mod_forum\subscriptions::get_potential_subscribers($modcontext, 0, 'u.id, u.email', '');
        foreach ($users as $user) {
            \mod_forum\subscriptions::subscribe_user($user->id, $forum, $modcontext);
        }
    }

    forum_grade_item_update($forum);

    return true;
}



function forum_delete_instance($id) {
    global $DB;

    if (!$forum = $DB->get_record('forum', array('id'=>$id))) {
        return false;
    }
    if (!$cm = get_coursemodule_from_instance('forum', $forum->id)) {
        return false;
    }
    if (!$course = $DB->get_record('course', array('id'=>$cm->course))) {
        return false;
    }

    $context = context_module::instance($cm->id);

        $fs = get_file_storage();
    $fs->delete_area_files($context->id);

    $result = true;

        $DB->delete_records('forum_digests', array('forum' => $forum->id));
    $DB->delete_records('forum_subscriptions', array('forum'=>$forum->id));
    $DB->delete_records('forum_discussion_subs', array('forum' => $forum->id));

    if ($discussions = $DB->get_records('forum_discussions', array('forum'=>$forum->id))) {
        foreach ($discussions as $discussion) {
            if (!forum_delete_discussion($discussion, true, $course, $cm, $forum)) {
                $result = false;
            }
        }
    }

    forum_tp_delete_read_records(-1, -1, -1, $forum->id);

    if (!$DB->delete_records('forum', array('id'=>$forum->id))) {
        $result = false;
    }

    forum_grade_item_delete($forum);

    return $result;
}



function forum_supports($feature) {
    switch($feature) {
        case FEATURE_GROUPS:                  return true;
        case FEATURE_GROUPINGS:               return true;
        case FEATURE_MOD_INTRO:               return true;
        case FEATURE_COMPLETION_TRACKS_VIEWS: return true;
        case FEATURE_COMPLETION_HAS_RULES:    return true;
        case FEATURE_GRADE_HAS_GRADE:         return true;
        case FEATURE_GRADE_OUTCOMES:          return true;
        case FEATURE_RATE:                    return true;
        case FEATURE_BACKUP_MOODLE2:          return true;
        case FEATURE_SHOW_DESCRIPTION:        return true;
        case FEATURE_PLAGIARISM:              return true;

        default: return null;
    }
}



function forum_get_completion_state($course,$cm,$userid,$type) {
    global $CFG,$DB;

        if (!($forum=$DB->get_record('forum',array('id'=>$cm->instance)))) {
        throw new Exception("Can't find forum {$cm->instance}");
    }

    $result=$type;
    $postcountparams=array('userid'=>$userid,'forumid'=>$forum->id);
    $postcountsql="
SELECT
    COUNT(1)
FROM
    {forum_posts} fp
    INNER JOIN {forum_discussions} fd ON fp.discussion=fd.id
WHERE
    fp.userid=:userid AND fd.forum=:forumid";

    if ($forum->completiondiscussions) {
        $value = $forum->completiondiscussions <=
                 $DB->count_records('forum_discussions',array('forum'=>$forum->id,'userid'=>$userid));
        if ($type == COMPLETION_AND) {
            $result = $result && $value;
        } else {
            $result = $result || $value;
        }
    }
    if ($forum->completionreplies) {
        $value = $forum->completionreplies <=
                 $DB->get_field_sql( $postcountsql.' AND fp.parent<>0',$postcountparams);
        if ($type==COMPLETION_AND) {
            $result = $result && $value;
        } else {
            $result = $result || $value;
        }
    }
    if ($forum->completionposts) {
        $value = $forum->completionposts <= $DB->get_field_sql($postcountsql,$postcountparams);
        if ($type == COMPLETION_AND) {
            $result = $result && $value;
        } else {
            $result = $result || $value;
        }
    }

    return $result;
}


function forum_get_email_message_id($postid, $usertoid) {
    return generate_email_messageid(hash('sha256', $postid . 'to' . $usertoid));
}


function forum_cron_minimise_user_record(stdClass $user) {


    unset($user->institution);
    unset($user->department);
    unset($user->address);
    unset($user->city);
    unset($user->url);
    unset($user->currentlogin);
    unset($user->description);
    unset($user->descriptionformat);
}


function forum_cron() {
    global $CFG, $USER, $DB, $PAGE;

    $site = get_site();

        $htmlout = $PAGE->get_renderer('mod_forum', 'email', 'htmlemail');
    $textout = $PAGE->get_renderer('mod_forum', 'email', 'textemail');
    $htmldigestfullout = $PAGE->get_renderer('mod_forum', 'emaildigestfull', 'htmlemail');
    $textdigestfullout = $PAGE->get_renderer('mod_forum', 'emaildigestfull', 'textemail');
    $htmldigestbasicout = $PAGE->get_renderer('mod_forum', 'emaildigestbasic', 'htmlemail');
    $textdigestbasicout = $PAGE->get_renderer('mod_forum', 'emaildigestbasic', 'textemail');

                $users = array();
    $userscount = 0;
        $mailcount  = array();
    $errorcount = array();

        $discussions        = array();
    $forums             = array();
    $courses            = array();
    $coursemodules      = array();
    $subscribedusers    = array();
    $messageinboundhandlers = array();

                $timenow   = time();
    $endtime   = $timenow - $CFG->maxeditingtime;
    $starttime = $endtime - 48 * 3600;
        $digestsset = $DB->get_recordset('forum_digests', null, '', 'id, userid, forum, maildigest');
    $digests = array();
    foreach ($digestsset as $thisrow) {
        if (!isset($digests[$thisrow->forum])) {
            $digests[$thisrow->forum] = array();
        }
        $digests[$thisrow->forum][$thisrow->userid] = $thisrow->maildigest;
    }
    $digestsset->close();

        $messageinboundgenerator = new \core\message\inbound\address_manager();
    $messageinboundgenerator->set_handler('\mod_forum\message\inbound\reply_handler');

    if ($posts = forum_get_unmailed_posts($starttime, $endtime, $timenow)) {

        if (!forum_mark_old_posts_as_mailed($endtime)) {
            mtrace('Errors occurred while trying to mark some posts as being mailed.');
            return false;          }

                foreach ($posts as $pid => $post) {

            $discussionid = $post->discussion;
            if (!isset($discussions[$discussionid])) {
                if ($discussion = $DB->get_record('forum_discussions', array('id'=> $post->discussion))) {
                    $discussions[$discussionid] = $discussion;
                    \mod_forum\subscriptions::fill_subscription_cache($discussion->forum);
                    \mod_forum\subscriptions::fill_discussion_subscription_cache($discussion->forum);

                } else {
                    mtrace('Could not find discussion ' . $discussionid);
                    unset($posts[$pid]);
                    continue;
                }
            }
            $forumid = $discussions[$discussionid]->forum;
            if (!isset($forums[$forumid])) {
                if ($forum = $DB->get_record('forum', array('id' => $forumid))) {
                    $forums[$forumid] = $forum;
                } else {
                    mtrace('Could not find forum '.$forumid);
                    unset($posts[$pid]);
                    continue;
                }
            }
            $courseid = $forums[$forumid]->course;
            if (!isset($courses[$courseid])) {
                if ($course = $DB->get_record('course', array('id' => $courseid))) {
                    $courses[$courseid] = $course;
                } else {
                    mtrace('Could not find course '.$courseid);
                    unset($posts[$pid]);
                    continue;
                }
            }
            if (!isset($coursemodules[$forumid])) {
                if ($cm = get_coursemodule_from_instance('forum', $forumid, $courseid)) {
                    $coursemodules[$forumid] = $cm;
                } else {
                    mtrace('Could not find course module for forum '.$forumid);
                    unset($posts[$pid]);
                    continue;
                }
            }

                        $messageinboundgenerator->set_data($pid);
            $messageinboundhandlers[$pid] = $messageinboundgenerator->fetch_data_key();

                        if (!isset($subscribedusers[$forumid])) {
                $modcontext = context_module::instance($coursemodules[$forumid]->id);
                if ($subusers = \mod_forum\subscriptions::fetch_subscribed_users($forums[$forumid], 0, $modcontext, 'u.*', true)) {

                    foreach ($subusers as $postuser) {
                                                $subscribedusers[$forumid][$postuser->id] = $postuser->id;
                        $userscount++;
                        if ($userscount > FORUM_CRON_USER_CACHE) {
                                                        $minuser = new stdClass();
                            $minuser->id = $postuser->id;
                            $users[$postuser->id] = $minuser;
                        } else {
                                                        forum_cron_minimise_user_record($postuser);
                            $users[$postuser->id] = $postuser;
                        }
                    }
                                        unset($subusers);
                    unset($postuser);
                }
            }
            $mailcount[$pid] = 0;
            $errorcount[$pid] = 0;
        }
    }

    if ($users && $posts) {

        foreach ($users as $userto) {
                        core_php_time_limit::raise(120);

            mtrace('Processing user ' . $userto->id);

                        if (isset($userto->username)) {
                $userto = clone($userto);
            } else {
                $userto = $DB->get_record('user', array('id' => $userto->id));
                forum_cron_minimise_user_record($userto);
            }
            $userto->viewfullnames = array();
            $userto->canpost       = array();
            $userto->markposts     = array();

                        cron_setup_user($userto);

                        foreach ($coursemodules as $forumid => $unused) {
                $coursemodules[$forumid]->cache       = new stdClass();
                $coursemodules[$forumid]->cache->caps = array();
                unset($coursemodules[$forumid]->uservisible);
            }

            foreach ($posts as $pid => $post) {
                $discussion = $discussions[$post->discussion];
                $forum      = $forums[$discussion->forum];
                $course     = $courses[$forum->course];
                $cm         =& $coursemodules[$forum->id];


                                                if (!isset($subscribedusers[$forum->id][$userto->id])) {
                                        continue;
                }

                if (!\mod_forum\subscriptions::is_subscribed($userto->id, $forum, $post->discussion, $coursemodules[$forum->id])) {
                                        continue;
                }

                if ($subscriptiontime = \mod_forum\subscriptions::fetch_discussion_subscription($forum->id, $userto->id)) {
                                        if (isset($subscriptiontime[$post->discussion]) && ($subscriptiontime[$post->discussion] > $post->created)) {
                        continue;
                    }
                }

                                                if ($forum->type == 'qanda' && !forum_get_user_posted_time($discussion->id, $userto->id) && $pid != $discussion->firstpost) {
                    mtrace('Did not email ' . $userto->id.' because user has not posted in discussion');
                    continue;
                }

                                if (array_key_exists($post->userid, $users)) {
                                        $userfrom = $users[$post->userid];
                    if (!isset($userfrom->idnumber)) {
                                                $userfrom = $DB->get_record('user', array('id' => $userfrom->id));
                        forum_cron_minimise_user_record($userfrom);
                    }

                } else if ($userfrom = $DB->get_record('user', array('id' => $post->userid))) {
                    forum_cron_minimise_user_record($userfrom);
                                        if ($userscount <= FORUM_CRON_USER_CACHE) {
                        $userscount++;
                        $users[$userfrom->id] = $userfrom;
                    }
                } else {
                    mtrace('Could not find user ' . $post->userid . ', author of post ' . $post->id . '. Unable to send message.');
                    continue;
                }


                                cron_setup_user($userto, $course);

                                if (!isset($userto->viewfullnames[$forum->id])) {
                    $modcontext = context_module::instance($cm->id);
                    $userto->viewfullnames[$forum->id] = has_capability('moodle/site:viewfullnames', $modcontext);
                }
                if (!isset($userto->canpost[$discussion->id])) {
                    $modcontext = context_module::instance($cm->id);
                    $userto->canpost[$discussion->id] = forum_user_can_post($forum, $discussion, $userto, $cm, $course, $modcontext);
                }
                if (!isset($userfrom->groups[$forum->id])) {
                    if (!isset($userfrom->groups)) {
                        $userfrom->groups = array();
                        if (isset($users[$userfrom->id])) {
                            $users[$userfrom->id]->groups = array();
                        }
                    }
                    $userfrom->groups[$forum->id] = groups_get_all_groups($course->id, $userfrom->id, $cm->groupingid);
                    if (isset($users[$userfrom->id])) {
                        $users[$userfrom->id]->groups[$forum->id] = $userfrom->groups[$forum->id];
                    }
                }

                                if ($discussion->groupid > 0 and $groupmode = groups_get_activity_groupmode($cm, $course)) {
                                        if (!groups_group_exists($discussion->groupid)) {
                                                continue;
                    }

                    if (!groups_is_member($discussion->groupid) and !has_capability('moodle/site:accessallgroups', $modcontext)) {
                                                continue;
                    }
                }

                                if (!forum_user_can_see_post($forum, $discussion, $post, null, $cm)) {
                    mtrace('User ' . $userto->id .' can not see ' . $post->id . '. Not sending message.');
                    continue;
                }


                                $maildigest = forum_get_user_maildigest_bulk($digests, $userto, $forum->id);

                if ($maildigest > 0) {
                                        $queue = new stdClass();
                    $queue->userid       = $userto->id;
                    $queue->discussionid = $discussion->id;
                    $queue->postid       = $post->id;
                    $queue->timemodified = $post->created;
                    $DB->insert_record('forum_queue', $queue);
                    continue;
                }


                $cleanforumname = str_replace('"', "'", strip_tags(format_string($forum->name)));

                $userfrom->customheaders = array (
                                        'List-Id: "'        . $cleanforumname . '" ' . generate_email_messageid('moodleforum' . $forum->id),
                    'List-Help: '       . $CFG->wwwroot . '/mod/forum/view.php?f=' . $forum->id,
                    'Message-ID: '      . forum_get_email_message_id($post->id, $userto->id),
                    'X-Course-Id: '     . $course->id,
                    'X-Course-Name: '   . format_string($course->fullname, true),

                                        'Precedence: Bulk',
                    'X-Auto-Response-Suppress: All',
                    'Auto-Submitted: auto-generated',
                );

                $shortname = format_string($course->shortname, true, array('context' => context_course::instance($course->id)));

                                $replyaddress = null;
                if ($userto->canpost[$discussion->id] && array_key_exists($post->id, $messageinboundhandlers)) {
                    $messageinboundgenerator->set_data($post->id, $messageinboundhandlers[$post->id]);
                    $replyaddress = $messageinboundgenerator->generate($userto->id);
                }

                if (!isset($userto->canpost[$discussion->id])) {
                    $canreply = forum_user_can_post($forum, $discussion, $userto, $cm, $course, $modcontext);
                } else {
                    $canreply = $userto->canpost[$discussion->id];
                }

                $data = new \mod_forum\output\forum_post_email(
                        $course,
                        $cm,
                        $forum,
                        $discussion,
                        $post,
                        $userfrom,
                        $userto,
                        $canreply
                    );

                $userfrom->customheaders[] = sprintf('List-Unsubscribe: <%s>',
                    $data->get_unsubscribediscussionlink());

                if (!isset($userto->viewfullnames[$forum->id])) {
                    $data->viewfullnames = has_capability('moodle/site:viewfullnames', $modcontext, $userto->id);
                } else {
                    $data->viewfullnames = $userto->viewfullnames[$forum->id];
                }

                                                $a = new stdClass();
                $a->subject = $data->get_subject();
                $a->forumname = $cleanforumname;
                $a->sitefullname = format_string($site->fullname);
                $a->siteshortname = format_string($site->shortname);
                $a->courseidnumber = $data->get_courseidnumber();
                $a->coursefullname = $data->get_coursefullname();
                $a->courseshortname = $data->get_coursename();
                $postsubject = html_to_text(get_string('postmailsubject', 'forum', $a), 0);

                $rootid = forum_get_email_message_id($discussion->firstpost, $userto->id);

                if ($post->parent) {
                                        $parentid = forum_get_email_message_id($post->parent, $userto->id);
                    $userfrom->customheaders[] = "In-Reply-To: $parentid";

                                                                                if ($post->parent != $discussion->firstpost) {
                        $userfrom->customheaders[] = "References: $rootid $parentid";
                    } else {
                        $userfrom->customheaders[] = "References: $parentid";
                    }
                }

                                                $a->subject = $discussion->name;
                $threadtopic = html_to_text(get_string('postmailsubject', 'forum', $a), 0);
                $userfrom->customheaders[] = "Thread-Topic: $threadtopic";
                $userfrom->customheaders[] = "Thread-Index: " . substr($rootid, 1, 28);

                                mtrace('Sending ', '');

                $eventdata = new \core\message\message();
                $eventdata->component           = 'mod_forum';
                $eventdata->name                = 'posts';
                $eventdata->userfrom            = $userfrom;
                $eventdata->userto              = $userto;
                $eventdata->subject             = $postsubject;
                $eventdata->fullmessage         = $textout->render($data);
                $eventdata->fullmessageformat   = FORMAT_PLAIN;
                $eventdata->fullmessagehtml     = $htmlout->render($data);
                $eventdata->notification        = 1;
                $eventdata->replyto             = $replyaddress;
                if (!empty($replyaddress)) {
                                        $textfooter = "\n\n" . get_string('replytopostbyemail', 'mod_forum');
                    $htmlfooter = html_writer::tag('p', get_string('replytopostbyemail', 'mod_forum'));
                    $additionalcontent = array('fullmessage' => array('footer' => $textfooter),
                                     'fullmessagehtml' => array('footer' => $htmlfooter));
                    $eventdata->set_additional_content('email', $additionalcontent);
                }

                                if (empty($CFG->forum_replytouser)) {
                    $eventdata->userfrom = core_user::get_noreply_user();
                }

                $smallmessagestrings = new stdClass();
                $smallmessagestrings->user          = fullname($userfrom);
                $smallmessagestrings->forumname     = "$shortname: " . format_string($forum->name, true) . ": " . $discussion->name;
                $smallmessagestrings->message       = $post->message;

                                $eventdata->smallmessage = get_string_manager()->get_string('smallmessage', 'forum', $smallmessagestrings, $userto->lang);

                $contexturl = new moodle_url('/mod/forum/discuss.php', array('d' => $discussion->id), 'p' . $post->id);
                $eventdata->contexturl = $contexturl->out();
                $eventdata->contexturlname = $discussion->name;

                $mailresult = message_send($eventdata);
                if (!$mailresult) {
                    mtrace("Error: mod/forum/lib.php forum_cron(): Could not send out mail for id $post->id to user $userto->id".
                            " ($userto->email) .. not trying again.");
                    $errorcount[$post->id]++;
                } else {
                    $mailcount[$post->id]++;

                                        if (!$CFG->forum_usermarksread) {
                        $userto->markposts[$post->id] = $post->id;
                    }
                }

                mtrace('post ' . $post->id . ': ' . $post->subject);
            }

                        forum_tp_mark_posts_read($userto, $userto->markposts);
            unset($userto);
        }
    }

    if ($posts) {
        foreach ($posts as $post) {
            mtrace($mailcount[$post->id]." users were sent post $post->id, '$post->subject'");
            if ($errorcount[$post->id]) {
                $DB->set_field('forum_posts', 'mailed', FORUM_MAILED_ERROR, array('id' => $post->id));
            }
        }
    }

        unset($subscribedusers);
    unset($mailcount);
    unset($errorcount);

    cron_setup_user();

    $sitetimezone = core_date::get_server_timezone();


    mtrace('Starting digest processing...');

    core_php_time_limit::raise(300);
    if (!isset($CFG->digestmailtimelast)) {            set_config('digestmailtimelast', 0);
    }

    $timenow = time();
    $digesttime = usergetmidnight($timenow, $sitetimezone) + ($CFG->digestmailtime * 3600);

        $weekago = $timenow - (7 * 24 * 3600);
    $DB->delete_records_select('forum_queue', "timemodified < ?", array($weekago));
    mtrace ('Cleaned old digest records');

    if ($CFG->digestmailtimelast < $digesttime and $timenow > $digesttime) {

        mtrace('Sending forum digests: '.userdate($timenow, '', $sitetimezone));

        $digestposts_rs = $DB->get_recordset_select('forum_queue', "timemodified < ?", array($digesttime));

        if ($digestposts_rs->valid()) {

                        $usermailcount = 0;

                        $discussionposts = array();
            $userdiscussions = array();

            foreach ($digestposts_rs as $digestpost) {
                if (!isset($posts[$digestpost->postid])) {
                    if ($post = $DB->get_record('forum_posts', array('id' => $digestpost->postid))) {
                        $posts[$digestpost->postid] = $post;
                    } else {
                        continue;
                    }
                }
                $discussionid = $digestpost->discussionid;
                if (!isset($discussions[$discussionid])) {
                    if ($discussion = $DB->get_record('forum_discussions', array('id' => $discussionid))) {
                        $discussions[$discussionid] = $discussion;
                    } else {
                        continue;
                    }
                }
                $forumid = $discussions[$discussionid]->forum;
                if (!isset($forums[$forumid])) {
                    if ($forum = $DB->get_record('forum', array('id' => $forumid))) {
                        $forums[$forumid] = $forum;
                    } else {
                        continue;
                    }
                }

                $courseid = $forums[$forumid]->course;
                if (!isset($courses[$courseid])) {
                    if ($course = $DB->get_record('course', array('id' => $courseid))) {
                        $courses[$courseid] = $course;
                    } else {
                        continue;
                    }
                }

                if (!isset($coursemodules[$forumid])) {
                    if ($cm = get_coursemodule_from_instance('forum', $forumid, $courseid)) {
                        $coursemodules[$forumid] = $cm;
                    } else {
                        continue;
                    }
                }
                $userdiscussions[$digestpost->userid][$digestpost->discussionid] = $digestpost->discussionid;
                $discussionposts[$digestpost->discussionid][$digestpost->postid] = $digestpost->postid;
            }
            $digestposts_rs->close();
                        foreach ($userdiscussions as $userid => $thesediscussions) {

                core_php_time_limit::raise(120);
                cron_setup_user();

                mtrace(get_string('processingdigest', 'forum', $userid), '... ');

                                $DB->delete_records_select('forum_queue', "userid = ? AND timemodified < ?", array($userid, $digesttime));

                                                if (array_key_exists($userid, $users) and isset($users[$userid]->username)) {
                    $userto = clone($users[$userid]);
                } else {
                    $userto = $DB->get_record('user', array('id' => $userid));
                    forum_cron_minimise_user_record($userto);
                }
                $userto->viewfullnames = array();
                $userto->canpost       = array();
                $userto->markposts     = array();

                                                cron_setup_user($userto);

                $postsubject = get_string('digestmailsubject', 'forum', format_string($site->shortname, true));

                $headerdata = new stdClass();
                $headerdata->sitename = format_string($site->fullname, true);
                $headerdata->userprefs = $CFG->wwwroot.'/user/forum.php?id='.$userid.'&amp;course='.$site->id;

                $posttext = get_string('digestmailheader', 'forum', $headerdata)."\n\n";
                $headerdata->userprefs = '<a target="_blank" href="'.$headerdata->userprefs.'">'.get_string('digestmailprefs', 'forum').'</a>';

                $posthtml = '<p>'.get_string('digestmailheader', 'forum', $headerdata).'</p>'
                    . '<br /><hr size="1" noshade="noshade" />';

                foreach ($thesediscussions as $discussionid) {

                    core_php_time_limit::raise(120);
                    $discussion = $discussions[$discussionid];
                    $forum      = $forums[$discussion->forum];
                    $course     = $courses[$forum->course];
                    $cm         = $coursemodules[$forum->id];

                                        cron_setup_user($userto, $course);

                                        if (!isset($userto->viewfullnames[$forum->id])) {
                        $modcontext = context_module::instance($cm->id);
                        $userto->viewfullnames[$forum->id] = has_capability('moodle/site:viewfullnames', $modcontext);
                    }
                    if (!isset($userto->canpost[$discussion->id])) {
                        $modcontext = context_module::instance($cm->id);
                        $userto->canpost[$discussion->id] = forum_user_can_post($forum, $discussion, $userto, $cm, $course, $modcontext);
                    }

                    $strforums      = get_string('forums', 'forum');
                    $canunsubscribe = ! \mod_forum\subscriptions::is_forcesubscribed($forum);
                    $canreply       = $userto->canpost[$discussion->id];
                    $shortname = format_string($course->shortname, true, array('context' => context_course::instance($course->id)));

                    $posttext .= "\n \n";
                    $posttext .= '=====================================================================';
                    $posttext .= "\n \n";
                    $posttext .= "$shortname -> $strforums -> ".format_string($forum->name,true);
                    if ($discussion->name != $forum->name) {
                        $posttext  .= " -> ".format_string($discussion->name,true);
                    }
                    $posttext .= "\n";
                    $posttext .= $CFG->wwwroot.'/mod/forum/discuss.php?d='.$discussion->id;
                    $posttext .= "\n";

                    $posthtml .= "<p><font face=\"sans-serif\">".
                    "<a target=\"_blank\" href=\"$CFG->wwwroot/course/view.php?id=$course->id\">$shortname</a> -> ".
                    "<a target=\"_blank\" href=\"$CFG->wwwroot/mod/forum/index.php?id=$course->id\">$strforums</a> -> ".
                    "<a target=\"_blank\" href=\"$CFG->wwwroot/mod/forum/view.php?f=$forum->id\">".format_string($forum->name,true)."</a>";
                    if ($discussion->name == $forum->name) {
                        $posthtml .= "</font></p>";
                    } else {
                        $posthtml .= " -> <a target=\"_blank\" href=\"$CFG->wwwroot/mod/forum/discuss.php?d=$discussion->id\">".format_string($discussion->name,true)."</a></font></p>";
                    }
                    $posthtml .= '<p>';

                    $postsarray = $discussionposts[$discussionid];
                    sort($postsarray);
                    $sentcount = 0;

                    foreach ($postsarray as $postid) {
                        $post = $posts[$postid];

                        if (array_key_exists($post->userid, $users)) {                             $userfrom = $users[$post->userid];
                            if (!isset($userfrom->idnumber)) {
                                $userfrom = $DB->get_record('user', array('id' => $userfrom->id));
                                forum_cron_minimise_user_record($userfrom);
                            }

                        } else if ($userfrom = $DB->get_record('user', array('id' => $post->userid))) {
                            forum_cron_minimise_user_record($userfrom);
                            if ($userscount <= FORUM_CRON_USER_CACHE) {
                                $userscount++;
                                $users[$userfrom->id] = $userfrom;
                            }

                        } else {
                            mtrace('Could not find user '.$post->userid);
                            continue;
                        }

                        if (!isset($userfrom->groups[$forum->id])) {
                            if (!isset($userfrom->groups)) {
                                $userfrom->groups = array();
                                if (isset($users[$userfrom->id])) {
                                    $users[$userfrom->id]->groups = array();
                                }
                            }
                            $userfrom->groups[$forum->id] = groups_get_all_groups($course->id, $userfrom->id, $cm->groupingid);
                            if (isset($users[$userfrom->id])) {
                                $users[$userfrom->id]->groups[$forum->id] = $userfrom->groups[$forum->id];
                            }
                        }

                                                $userfrom->customheaders = array(
                                "Precedence: Bulk",
                                'X-Auto-Response-Suppress: All',
                                'Auto-Submitted: auto-generated',
                            );

                        $maildigest = forum_get_user_maildigest_bulk($digests, $userto, $forum->id);
                        if (!isset($userto->canpost[$discussion->id])) {
                            $canreply = forum_user_can_post($forum, $discussion, $userto, $cm, $course, $modcontext);
                        } else {
                            $canreply = $userto->canpost[$discussion->id];
                        }

                        $data = new \mod_forum\output\forum_post_email(
                                $course,
                                $cm,
                                $forum,
                                $discussion,
                                $post,
                                $userfrom,
                                $userto,
                                $canreply
                            );

                        if (!isset($userto->viewfullnames[$forum->id])) {
                            $data->viewfullnames = has_capability('moodle/site:viewfullnames', $modcontext, $userto->id);
                        } else {
                            $data->viewfullnames = $userto->viewfullnames[$forum->id];
                        }

                        if ($maildigest == 2) {
                                                        $posttext .= $textdigestbasicout->render($data);
                            $posthtml .= $htmldigestbasicout->render($data);
                        } else {
                                                        $posttext .= $textdigestfullout->render($data);
                            $posthtml .= $htmldigestfullout->render($data);

                                                        if (!$CFG->forum_usermarksread) {
                                $userto->markposts[$post->id] = $post->id;
                            }
                        }
                        $sentcount++;
                    }
                    $footerlinks = array();
                    if ($canunsubscribe) {
                        $footerlinks[] = "<a href=\"$CFG->wwwroot/mod/forum/subscribe.php?id=$forum->id\">" . get_string("unsubscribe", "forum") . "</a>";
                    } else {
                        $footerlinks[] = get_string("everyoneissubscribed", "forum");
                    }
                    $footerlinks[] = "<a href='{$CFG->wwwroot}/mod/forum/index.php?id={$forum->course}'>" . get_string("digestmailpost", "forum") . '</a>';
                    $posthtml .= "\n<div class='mdl-right'><font size=\"1\">" . implode('&nbsp;', $footerlinks) . '</font></div>';
                    $posthtml .= '<hr size="1" noshade="noshade" /></p>';
                }

                if (empty($userto->mailformat) || $userto->mailformat != 1) {
                                        $posthtml = '';
                }

                $eventdata = new \core\message\message();
                $eventdata->component           = 'mod_forum';
                $eventdata->name                = 'digests';
                $eventdata->userfrom            = core_user::get_noreply_user();
                $eventdata->userto              = $userto;
                $eventdata->subject             = $postsubject;
                $eventdata->fullmessage         = $posttext;
                $eventdata->fullmessageformat   = FORMAT_PLAIN;
                $eventdata->fullmessagehtml     = $posthtml;
                $eventdata->notification        = 1;
                $eventdata->smallmessage        = get_string('smallmessagedigest', 'forum', $sentcount);
                $mailresult = message_send($eventdata);

                if (!$mailresult) {
                    mtrace("ERROR: mod/forum/cron.php: Could not send out digest mail to user $userto->id ".
                        "($userto->email)... not trying again.");
                } else {
                    mtrace("success.");
                    $usermailcount++;

                                        forum_tp_mark_posts_read($userto, $userto->markposts);
                }
            }
        }
            set_config('digestmailtimelast', $timenow);
    }

    cron_setup_user();

    if (!empty($usermailcount)) {
        mtrace(get_string('digestsentusers', 'forum', $usermailcount));
    }

    if (!empty($CFG->forum_lastreadclean)) {
        $timenow = time();
        if ($CFG->forum_lastreadclean + (24*3600) < $timenow) {
            set_config('forum_lastreadclean', $timenow);
            mtrace('Removing old forum read tracking info...');
            forum_tp_clean_read_records();
        }
    } else {
        set_config('forum_lastreadclean', time());
    }

    return true;
}


function forum_user_outline($course, $user, $mod, $forum) {
    global $CFG;
    require_once("$CFG->libdir/gradelib.php");
    $grades = grade_get_grades($course->id, 'mod', 'forum', $forum->id, $user->id);
    if (empty($grades->items[0]->grades)) {
        $grade = false;
    } else {
        $grade = reset($grades->items[0]->grades);
    }

    $count = forum_count_user_posts($forum->id, $user->id);

    if ($count && $count->postcount > 0) {
        $result = new stdClass();
        $result->info = get_string("numposts", "forum", $count->postcount);
        $result->time = $count->lastpost;
        if ($grade) {
            $result->info .= ', ' . get_string('grade') . ': ' . $grade->str_long_grade;
        }
        return $result;
    } else if ($grade) {
        $result = new stdClass();
        $result->info = get_string('grade') . ': ' . $grade->str_long_grade;

                                if ($grade->usermodified == $user->id || empty($grade->datesubmitted)) {
            $result->time = $grade->dategraded;
        } else {
            $result->time = $grade->datesubmitted;
        }

        return $result;
    }
    return NULL;
}



function forum_user_complete($course, $user, $mod, $forum) {
    global $CFG,$USER, $OUTPUT;
    require_once("$CFG->libdir/gradelib.php");

    $grades = grade_get_grades($course->id, 'mod', 'forum', $forum->id, $user->id);
    if (!empty($grades->items[0]->grades)) {
        $grade = reset($grades->items[0]->grades);
        echo $OUTPUT->container(get_string('grade').': '.$grade->str_long_grade);
        if ($grade->str_feedback) {
            echo $OUTPUT->container(get_string('feedback').': '.$grade->str_feedback);
        }
    }

    if ($posts = forum_get_user_posts($forum->id, $user->id)) {

        if (!$cm = get_coursemodule_from_instance('forum', $forum->id, $course->id)) {
            print_error('invalidcoursemodule');
        }
        $discussions = forum_get_user_involved_discussions($forum->id, $user->id);

        foreach ($posts as $post) {
            if (!isset($discussions[$post->discussion])) {
                continue;
            }
            $discussion = $discussions[$post->discussion];

            forum_print_post($post, $discussion, $forum, $cm, $course, false, false, false);
        }
    } else {
        echo "<p>".get_string("noposts", "forum")."</p>";
    }
}


function forum_filter_user_groups_discussions($discussions) {

        $filteredforums = array();

        foreach ($discussions as $discussion) {

                $instances = get_fast_modinfo($discussion->course)->get_instances();
        $forum = $instances['forum'][$discussion->forum];

                if (!forum_is_user_group_discussion($forum, $discussion->groupid)) {
            continue;
        }

                if (empty($filteredforums[$forum->instance])) {
            $filteredforums[$forum->instance] = new stdClass();
            $filteredforums[$forum->instance]->id = $forum->id;
            $filteredforums[$forum->instance]->count = 0;
        }
        $filteredforums[$forum->instance]->count += $discussion->count;

    }

    return $filteredforums;
}


function forum_is_user_group_discussion(cm_info $cm, $discussiongroupid) {

    if ($discussiongroupid == -1 || $cm->effectivegroupmode != SEPARATEGROUPS) {
        return true;
    }

    if (isguestuser()) {
        return false;
    }

    if (has_capability('moodle/site:accessallgroups', context_module::instance($cm->id)) ||
            in_array($discussiongroupid, $cm->get_modinfo()->get_groups($cm->groupingid))) {
        return true;
    }

    return false;
}


function forum_print_overview($courses,&$htmlarray) {
    global $USER, $CFG, $DB, $SESSION;

    if (empty($courses) || !is_array($courses) || count($courses) == 0) {
        return array();
    }

    if (!$forums = get_all_instances_in_courses('forum',$courses)) {
        return;
    }

        $coursessqls = array();
    $params = array();
    foreach ($courses as $course) {

                if ($course->lastaccess == 0) {
            $coursessqls[] = '(d.course = ?)';
            $params[] = $course->id;

                } else {
            $coursessqls[] = '(d.course = ? AND p.created > ?)';
            $params[] = $course->id;
            $params[] = $course->lastaccess;
        }
    }
    $params[] = $USER->id;
    $coursessql = implode(' OR ', $coursessqls);

    $sql = "SELECT d.id, d.forum, d.course, d.groupid, COUNT(*) as count "
                .'FROM {forum_discussions} d '
                .'JOIN {forum_posts} p ON p.discussion = d.id '
                ."WHERE ($coursessql) "
                .'AND p.userid != ? '
                .'AND (d.timestart <= ? AND (d.timeend = 0 OR d.timeend > ?)) '
                .'GROUP BY d.id, d.forum, d.course, d.groupid '
                .'ORDER BY d.course, d.forum';
    $params[] = time();
    $params[] = time();

        if (!$discussions = $DB->get_records_sql($sql, $params)) {
        $discussions = array();
    }

    $forumsnewposts = forum_filter_user_groups_discussions($discussions);

        $trackingforums = array();
    foreach ($forums as $forum) {
        if (forum_tp_can_track_forums($forum)) {
            $trackingforums[$forum->id] = $forum;
        }
    }

    if (count($trackingforums) > 0) {
        $cutoffdate = isset($CFG->forum_oldpostdays) ? (time() - ($CFG->forum_oldpostdays*24*60*60)) : 0;
        $sql = 'SELECT d.forum,d.course,COUNT(p.id) AS count '.
            ' FROM {forum_posts} p '.
            ' JOIN {forum_discussions} d ON p.discussion = d.id '.
            ' LEFT JOIN {forum_read} r ON r.postid = p.id AND r.userid = ? WHERE (';
        $params = array($USER->id);

        foreach ($trackingforums as $track) {
            $sql .= '(d.forum = ? AND (d.groupid = -1 OR d.groupid = 0 OR d.groupid = ?)) OR ';
            $params[] = $track->id;
            if (isset($SESSION->currentgroup[$track->course])) {
                $groupid =  $SESSION->currentgroup[$track->course];
            } else {
                                $groupids = groups_get_all_groups($track->course, $USER->id);
                if ($groupids) {
                    reset($groupids);
                    $groupid = key($groupids);
                    $SESSION->currentgroup[$track->course] = $groupid;
                } else {
                    $groupid = 0;
                }
                unset($groupids);
            }
            $params[] = $groupid;
        }
        $sql = substr($sql,0,-3);         $sql .= ') AND p.modified >= ? AND r.id is NULL ';
        $sql .= 'AND (d.timestart < ? AND (d.timeend = 0 OR d.timeend > ?)) ';
        $sql .= 'GROUP BY d.forum,d.course';
        $params[] = $cutoffdate;
        $params[] = time();
        $params[] = time();

        if (!$unread = $DB->get_records_sql($sql, $params)) {
            $unread = array();
        }
    } else {
        $unread = array();
    }

    if (empty($unread) and empty($forumsnewposts)) {
        return;
    }

    $strforum = get_string('modulename','forum');

    foreach ($forums as $forum) {
        $str = '';
        $count = 0;
        $thisunread = 0;
        $showunread = false;
                if (array_key_exists($forum->id, $forumsnewposts) && !empty($forumsnewposts[$forum->id])) {
            $count = $forumsnewposts[$forum->id]->count;
        }
        if (array_key_exists($forum->id,$unread)) {
            $thisunread = $unread[$forum->id]->count;
            $showunread = true;
        }
        if ($count > 0 || $thisunread > 0) {
            $str .= '<div class="overview forum"><div class="name">'.$strforum.': <a title="'.$strforum.'" href="'.$CFG->wwwroot.'/mod/forum/view.php?f='.$forum->id.'">'.
                $forum->name.'</a></div>';
            $str .= '<div class="info"><span class="postsincelogin">';
            $str .= get_string('overviewnumpostssince', 'forum', $count)."</span>";
            if (!empty($showunread)) {
                $str .= '<div class="unreadposts">'.get_string('overviewnumunread', 'forum', $thisunread).'</div>';
            }
            $str .= '</div></div>';
        }
        if (!empty($str)) {
            if (!array_key_exists($forum->course,$htmlarray)) {
                $htmlarray[$forum->course] = array();
            }
            if (!array_key_exists('forum',$htmlarray[$forum->course])) {
                $htmlarray[$forum->course]['forum'] = '';             }
            $htmlarray[$forum->course]['forum'] .= $str;
        }
    }
}


function forum_print_recent_activity($course, $viewfullnames, $timestart) {
    global $CFG, $USER, $DB, $OUTPUT;


    $allnamefields = user_picture::fields('u', null, 'duserid');
    if (!$posts = $DB->get_records_sql("SELECT p.*, f.type AS forumtype, d.forum, d.groupid,
                                              d.timestart, d.timeend, $allnamefields
                                         FROM {forum_posts} p
                                              JOIN {forum_discussions} d ON d.id = p.discussion
                                              JOIN {forum} f             ON f.id = d.forum
                                              JOIN {user} u              ON u.id = p.userid
                                        WHERE p.created > ? AND f.course = ?
                                     ORDER BY p.id ASC", array($timestart, $course->id))) {          return false;
    }

    $modinfo = get_fast_modinfo($course);

    $groupmodes = array();
    $cms    = array();

    $strftimerecent = get_string('strftimerecent');

    $printposts = array();
    foreach ($posts as $post) {
        if (!isset($modinfo->instances['forum'][$post->forum])) {
                        continue;
        }
        $cm = $modinfo->instances['forum'][$post->forum];
        if (!$cm->uservisible) {
            continue;
        }
        $context = context_module::instance($cm->id);

        if (!has_capability('mod/forum:viewdiscussion', $context)) {
            continue;
        }

        if (!empty($CFG->forum_enabletimedposts) and $USER->id != $post->duserid
          and (($post->timestart > 0 and $post->timestart > time()) or ($post->timeend > 0 and $post->timeend < time()))) {
            if (!has_capability('mod/forum:viewhiddentimedposts', $context)) {
                continue;
            }
        }

                if (forum_is_user_group_discussion($cm, $post->groupid)) {
            $printposts[] = $post;
        }

    }
    unset($posts);

    if (!$printposts) {
        return false;
    }

    echo $OUTPUT->heading(get_string('newforumposts', 'forum').':', 3);
    echo "\n<ul class='unlist'>\n";

    foreach ($printposts as $post) {
        $subjectclass = empty($post->parent) ? ' bold' : '';

        echo '<li><div class="head">'.
               '<div class="date">'.userdate($post->modified, $strftimerecent).'</div>'.
               '<div class="name">'.fullname($post, $viewfullnames).'</div>'.
             '</div>';
        echo '<div class="info'.$subjectclass.'">';
        if (empty($post->parent)) {
            echo '"<a href="'.$CFG->wwwroot.'/mod/forum/discuss.php?d='.$post->discussion.'">';
        } else {
            echo '"<a href="'.$CFG->wwwroot.'/mod/forum/discuss.php?d='.$post->discussion.'&amp;parent='.$post->parent.'#p'.$post->id.'">';
        }
        $post->subject = break_up_long_words(format_string($post->subject, true));
        echo $post->subject;
        echo "</a>\"</div></li>\n";
    }

    echo "</ul>\n";

    return true;
}


function forum_get_user_grades($forum, $userid = 0) {
    global $CFG;

    require_once($CFG->dirroot.'/rating/lib.php');

    $ratingoptions = new stdClass;
    $ratingoptions->component = 'mod_forum';
    $ratingoptions->ratingarea = 'post';

        $ratingoptions->modulename = 'forum';
    $ratingoptions->moduleid   = $forum->id;
    $ratingoptions->userid = $userid;
    $ratingoptions->aggregationmethod = $forum->assessed;
    $ratingoptions->scaleid = $forum->scale;
    $ratingoptions->itemtable = 'forum_posts';
    $ratingoptions->itemtableusercolumn = 'userid';

    $rm = new rating_manager();
    return $rm->get_user_grades($ratingoptions);
}


function forum_update_grades($forum, $userid=0, $nullifnone=true) {
    global $CFG, $DB;
    require_once($CFG->libdir.'/gradelib.php');

    if (!$forum->assessed) {
        forum_grade_item_update($forum);

    } else if ($grades = forum_get_user_grades($forum, $userid)) {
        forum_grade_item_update($forum, $grades);

    } else if ($userid and $nullifnone) {
        $grade = new stdClass();
        $grade->userid   = $userid;
        $grade->rawgrade = NULL;
        forum_grade_item_update($forum, $grade);

    } else {
        forum_grade_item_update($forum);
    }
}


function forum_grade_item_update($forum, $grades=NULL) {
    global $CFG;
    if (!function_exists('grade_update')) {         require_once($CFG->libdir.'/gradelib.php');
    }

    $params = array('itemname'=>$forum->name, 'idnumber'=>$forum->cmidnumber);

    if (!$forum->assessed or $forum->scale == 0) {
        $params['gradetype'] = GRADE_TYPE_NONE;

    } else if ($forum->scale > 0) {
        $params['gradetype'] = GRADE_TYPE_VALUE;
        $params['grademax']  = $forum->scale;
        $params['grademin']  = 0;

    } else if ($forum->scale < 0) {
        $params['gradetype'] = GRADE_TYPE_SCALE;
        $params['scaleid']   = -$forum->scale;
    }

    if ($grades  === 'reset') {
        $params['reset'] = true;
        $grades = NULL;
    }

    return grade_update('mod/forum', $forum->course, 'mod', 'forum', $forum->id, 0, $grades, $params);
}


function forum_grade_item_delete($forum) {
    global $CFG;
    require_once($CFG->libdir.'/gradelib.php');

    return grade_update('mod/forum', $forum->course, 'mod', 'forum', $forum->id, 0, NULL, array('deleted'=>1));
}



function forum_scale_used ($forumid,$scaleid) {
    global $DB;
    $return = false;

    $rec = $DB->get_record("forum",array("id" => "$forumid","scale" => "-$scaleid"));

    if (!empty($rec) && !empty($scaleid)) {
        $return = true;
    }

    return $return;
}


function forum_scale_used_anywhere($scaleid) {
    global $DB;
    if ($scaleid and $DB->record_exists('forum', array('scale' => -$scaleid))) {
        return true;
    } else {
        return false;
    }
}



function forum_get_post_full($postid) {
    global $CFG, $DB;

    $allnames = get_all_user_name_fields(true, 'u');
    return $DB->get_record_sql("SELECT p.*, d.forum, $allnames, u.email, u.picture, u.imagealt
                             FROM {forum_posts} p
                                  JOIN {forum_discussions} d ON p.discussion = d.id
                                  LEFT JOIN {user} u ON p.userid = u.id
                            WHERE p.id = ?", array($postid));
}


function forum_get_all_discussion_posts($discussionid, $sort, $tracking=false) {
    global $CFG, $DB, $USER;

    $tr_sel  = "";
    $tr_join = "";
    $params = array();

    if ($tracking) {
        $tr_sel  = ", fr.id AS postread";
        $tr_join = "LEFT JOIN {forum_read} fr ON (fr.postid = p.id AND fr.userid = ?)";
        $params[] = $USER->id;
    }

    $allnames = get_all_user_name_fields(true, 'u');
    $params[] = $discussionid;
    if (!$posts = $DB->get_records_sql("SELECT p.*, $allnames, u.email, u.picture, u.imagealt $tr_sel
                                     FROM {forum_posts} p
                                          LEFT JOIN {user} u ON p.userid = u.id
                                          $tr_join
                                    WHERE p.discussion = ?
                                 ORDER BY $sort", $params)) {
        return array();
    }

    foreach ($posts as $pid=>$p) {
        if ($tracking) {
            if (forum_tp_is_post_old($p)) {
                 $posts[$pid]->postread = true;
            }
        }
        if (!$p->parent) {
            continue;
        }
        if (!isset($posts[$p->parent])) {
            continue;         }
        if (!isset($posts[$p->parent]->children)) {
            $posts[$p->parent]->children = array();
        }
        $posts[$p->parent]->children[$pid] =& $posts[$pid];
    }

        $post = &$posts[reset($posts)->id];

    $lastpost = false;
    while (!$lastpost) {
        if (!isset($post->children)) {
            $post->lastpost = true;
            $lastpost = true;
        } else {
                         $post = &$posts[end($post->children)->id];
        }
    }

    return $posts;
}


function forum_get_readable_forums($userid, $courseid=0) {

    global $CFG, $DB, $USER;
    require_once($CFG->dirroot.'/course/lib.php');

    if (!$forummod = $DB->get_record('modules', array('name' => 'forum'))) {
        print_error('notinstalled', 'forum');
    }

    if ($courseid) {
        $courses = $DB->get_records('course', array('id' => $courseid));
    } else {
                $courses1 = $DB->get_records('course', array('id' => SITEID));
        $courses2 = enrol_get_users_courses($userid, true, array('modinfo'));
        $courses = array_merge($courses1, $courses2);
    }
    if (!$courses) {
        return array();
    }

    $readableforums = array();

    foreach ($courses as $course) {

        $modinfo = get_fast_modinfo($course);

        if (empty($modinfo->instances['forum'])) {
                        continue;
        }

        $courseforums = $DB->get_records('forum', array('course' => $course->id));

        foreach ($modinfo->instances['forum'] as $forumid => $cm) {
            if (!$cm->uservisible or !isset($courseforums[$forumid])) {
                continue;
            }
            $context = context_module::instance($cm->id);
            $forum = $courseforums[$forumid];
            $forum->context = $context;
            $forum->cm = $cm;

            if (!has_capability('mod/forum:viewdiscussion', $context)) {
                continue;
            }

                     if (groups_get_activity_groupmode($cm, $course) == SEPARATEGROUPS and !has_capability('moodle/site:accessallgroups', $context)) {

                $forum->onlygroups = $modinfo->get_groups($cm->groupingid);
                $forum->onlygroups[] = -1;
            }

                    $forum->viewhiddentimedposts = true;
            if (!empty($CFG->forum_enabletimedposts)) {
                if (!has_capability('mod/forum:viewhiddentimedposts', $context)) {
                    $forum->viewhiddentimedposts = false;
                }
            }

                    if ($forum->type == 'qanda'
                    && !has_capability('mod/forum:viewqandawithoutposting', $context)) {

                                $forum->onlydiscussions = array();                                                                      if ($discussionspostedin = forum_discussions_user_has_posted_in($forum->id, $USER->id)) {
                    foreach ($discussionspostedin as $d) {
                        $forum->onlydiscussions[] = $d->id;
                    }
                }
            }

            $readableforums[$forum->id] = $forum;
        }

        unset($modinfo);

    }
    return $readableforums;
}


function forum_search_posts($searchterms, $courseid=0, $limitfrom=0, $limitnum=50,
                            &$totalcount, $extrasql='') {
    global $CFG, $DB, $USER;
    require_once($CFG->libdir.'/searchlib.php');

    $forums = forum_get_readable_forums($USER->id, $courseid);

    if (count($forums) == 0) {
        $totalcount = 0;
        return false;
    }

    $now = round(time(), -2);
    $fullaccess = array();
    $where = array();
    $params = array();

    foreach ($forums as $forumid => $forum) {
        $select = array();

        if (!$forum->viewhiddentimedposts) {
            $select[] = "(d.userid = :userid{$forumid} OR (d.timestart < :timestart{$forumid} AND (d.timeend = 0 OR d.timeend > :timeend{$forumid})))";
            $params = array_merge($params, array('userid'.$forumid=>$USER->id, 'timestart'.$forumid=>$now, 'timeend'.$forumid=>$now));
        }

        $cm = $forum->cm;
        $context = $forum->context;

        if ($forum->type == 'qanda'
            && !has_capability('mod/forum:viewqandawithoutposting', $context)) {
            if (!empty($forum->onlydiscussions)) {
                list($discussionid_sql, $discussionid_params) = $DB->get_in_or_equal($forum->onlydiscussions, SQL_PARAMS_NAMED, 'qanda'.$forumid.'_');
                $params = array_merge($params, $discussionid_params);
                $select[] = "(d.id $discussionid_sql OR p.parent = 0)";
            } else {
                $select[] = "p.parent = 0";
            }
        }

        if (!empty($forum->onlygroups)) {
            list($groupid_sql, $groupid_params) = $DB->get_in_or_equal($forum->onlygroups, SQL_PARAMS_NAMED, 'grps'.$forumid.'_');
            $params = array_merge($params, $groupid_params);
            $select[] = "d.groupid $groupid_sql";
        }

        if ($select) {
            $selects = implode(" AND ", $select);
            $where[] = "(d.forum = :forum{$forumid} AND $selects)";
            $params['forum'.$forumid] = $forumid;
        } else {
            $fullaccess[] = $forumid;
        }
    }

    if ($fullaccess) {
        list($fullid_sql, $fullid_params) = $DB->get_in_or_equal($fullaccess, SQL_PARAMS_NAMED, 'fula');
        $params = array_merge($params, $fullid_params);
        $where[] = "(d.forum $fullid_sql)";
    }

    $selectdiscussion = "(".implode(" OR ", $where).")";

    $messagesearch = '';
    $searchstring = '';

        foreach($searchterms as $searchterm){
        if ($searchstring != '') {
            $searchstring .= ' ';
        }
        $searchstring .= $searchterm;
    }

            $searchstring = str_replace("\\\"","\"",$searchstring);
    $parser = new search_parser();
    $lexer = new search_lexer($parser);

    if ($lexer->parse($searchstring)) {
        $parsearray = $parser->get_parsed_array();
        list($messagesearch, $msparams) = search_generate_SQL($parsearray, 'p.message', 'p.subject',
                                                              'p.userid', 'u.id', 'u.firstname',
                                                              'u.lastname', 'p.modified', 'd.forum');
        $params = array_merge($params, $msparams);
    }

    $fromsql = "{forum_posts} p,
                  {forum_discussions} d,
                  {user} u";

    $selectsql = " $messagesearch
               AND p.discussion = d.id
               AND p.userid = u.id
               AND $selectdiscussion
                   $extrasql";

    $countsql = "SELECT COUNT(*)
                   FROM $fromsql
                  WHERE $selectsql";

    $allnames = get_all_user_name_fields(true, 'u');
    $searchsql = "SELECT p.*,
                         d.forum,
                         $allnames,
                         u.email,
                         u.picture,
                         u.imagealt
                    FROM $fromsql
                   WHERE $selectsql
                ORDER BY p.modified DESC";

    $totalcount = $DB->count_records_sql($countsql, $params);

    return $DB->get_records_sql($searchsql, $params, $limitfrom, $limitnum);
}


function forum_get_unmailed_posts($starttime, $endtime, $now=null) {
    global $CFG, $DB;

    $params = array();
    $params['mailed'] = FORUM_MAILED_PENDING;
    $params['ptimestart'] = $starttime;
    $params['ptimeend'] = $endtime;
    $params['mailnow'] = 1;

    if (!empty($CFG->forum_enabletimedposts)) {
        if (empty($now)) {
            $now = time();
        }
        $selectsql = "AND (p.created >= :ptimestart OR d.timestart >= :pptimestart)";
        $params['pptimestart'] = $starttime;
        $timedsql = "AND (d.timestart < :dtimestart AND (d.timeend = 0 OR d.timeend > :dtimeend))";
        $params['dtimestart'] = $now;
        $params['dtimeend'] = $now;
    } else {
        $timedsql = "";
        $selectsql = "AND p.created >= :ptimestart";
    }

    return $DB->get_records_sql("SELECT p.*, d.course, d.forum
                                 FROM {forum_posts} p
                                 JOIN {forum_discussions} d ON d.id = p.discussion
                                 WHERE p.mailed = :mailed
                                 $selectsql
                                 AND (p.created < :ptimeend OR p.mailnow = :mailnow)
                                 $timedsql
                                 ORDER BY p.modified ASC", $params);
}


function forum_mark_old_posts_as_mailed($endtime, $now=null) {
    global $CFG, $DB;

    if (empty($now)) {
        $now = time();
    }

    $params = array();
    $params['mailedsuccess'] = FORUM_MAILED_SUCCESS;
    $params['now'] = $now;
    $params['endtime'] = $endtime;
    $params['mailnow'] = 1;
    $params['mailedpending'] = FORUM_MAILED_PENDING;

    if (empty($CFG->forum_enabletimedposts)) {
        return $DB->execute("UPDATE {forum_posts}
                             SET mailed = :mailedsuccess
                             WHERE (created < :endtime OR mailnow = :mailnow)
                             AND mailed = :mailedpending", $params);
    } else {
        return $DB->execute("UPDATE {forum_posts}
                             SET mailed = :mailedsuccess
                             WHERE discussion NOT IN (SELECT d.id
                                                      FROM {forum_discussions} d
                                                      WHERE d.timestart > :now)
                             AND (created < :endtime OR mailnow = :mailnow)
                             AND mailed = :mailedpending", $params);
    }
}


function forum_get_user_posts($forumid, $userid) {
    global $CFG, $DB;

    $timedsql = "";
    $params = array($forumid, $userid);

    if (!empty($CFG->forum_enabletimedposts)) {
        $cm = get_coursemodule_from_instance('forum', $forumid);
        if (!has_capability('mod/forum:viewhiddentimedposts' , context_module::instance($cm->id))) {
            $now = time();
            $timedsql = "AND (d.timestart < ? AND (d.timeend = 0 OR d.timeend > ?))";
            $params[] = $now;
            $params[] = $now;
        }
    }

    $allnames = get_all_user_name_fields(true, 'u');
    return $DB->get_records_sql("SELECT p.*, d.forum, $allnames, u.email, u.picture, u.imagealt
                              FROM {forum} f
                                   JOIN {forum_discussions} d ON d.forum = f.id
                                   JOIN {forum_posts} p       ON p.discussion = d.id
                                   JOIN {user} u              ON u.id = p.userid
                             WHERE f.id = ?
                                   AND p.userid = ?
                                   $timedsql
                          ORDER BY p.modified ASC", $params);
}


function forum_get_user_involved_discussions($forumid, $userid) {
    global $CFG, $DB;

    $timedsql = "";
    $params = array($forumid, $userid);
    if (!empty($CFG->forum_enabletimedposts)) {
        $cm = get_coursemodule_from_instance('forum', $forumid);
        if (!has_capability('mod/forum:viewhiddentimedposts' , context_module::instance($cm->id))) {
            $now = time();
            $timedsql = "AND (d.timestart < ? AND (d.timeend = 0 OR d.timeend > ?))";
            $params[] = $now;
            $params[] = $now;
        }
    }

    return $DB->get_records_sql("SELECT DISTINCT d.*
                              FROM {forum} f
                                   JOIN {forum_discussions} d ON d.forum = f.id
                                   JOIN {forum_posts} p       ON p.discussion = d.id
                             WHERE f.id = ?
                                   AND p.userid = ?
                                   $timedsql", $params);
}


function forum_count_user_posts($forumid, $userid) {
    global $CFG, $DB;

    $timedsql = "";
    $params = array($forumid, $userid);
    if (!empty($CFG->forum_enabletimedposts)) {
        $cm = get_coursemodule_from_instance('forum', $forumid);
        if (!has_capability('mod/forum:viewhiddentimedposts' , context_module::instance($cm->id))) {
            $now = time();
            $timedsql = "AND (d.timestart < ? AND (d.timeend = 0 OR d.timeend > ?))";
            $params[] = $now;
            $params[] = $now;
        }
    }

    return $DB->get_record_sql("SELECT COUNT(p.id) AS postcount, MAX(p.modified) AS lastpost
                             FROM {forum} f
                                  JOIN {forum_discussions} d ON d.forum = f.id
                                  JOIN {forum_posts} p       ON p.discussion = d.id
                                  JOIN {user} u              ON u.id = p.userid
                            WHERE f.id = ?
                                  AND p.userid = ?
                                  $timedsql", $params);
}


function forum_get_post_from_log($log) {
    global $CFG, $DB;

    $allnames = get_all_user_name_fields(true, 'u');
    if ($log->action == "add post") {

        return $DB->get_record_sql("SELECT p.*, f.type AS forumtype, d.forum, d.groupid, $allnames, u.email, u.picture
                                 FROM {forum_discussions} d,
                                      {forum_posts} p,
                                      {forum} f,
                                      {user} u
                                WHERE p.id = ?
                                  AND d.id = p.discussion
                                  AND p.userid = u.id
                                  AND u.deleted <> '1'
                                  AND f.id = d.forum", array($log->info));


    } else if ($log->action == "add discussion") {

        return $DB->get_record_sql("SELECT p.*, f.type AS forumtype, d.forum, d.groupid, $allnames, u.email, u.picture
                                 FROM {forum_discussions} d,
                                      {forum_posts} p,
                                      {forum} f,
                                      {user} u
                                WHERE d.id = ?
                                  AND d.firstpost = p.id
                                  AND p.userid = u.id
                                  AND u.deleted <> '1'
                                  AND f.id = d.forum", array($log->info));
    }
    return NULL;
}


function forum_get_firstpost_from_discussion($discussionid) {
    global $CFG, $DB;

    return $DB->get_record_sql("SELECT p.*
                             FROM {forum_discussions} d,
                                  {forum_posts} p
                            WHERE d.id = ?
                              AND d.firstpost = p.id ", array($discussionid));
}


function forum_count_discussion_replies($forumid, $forumsort="", $limit=-1, $page=-1, $perpage=0) {
    global $CFG, $DB;

    if ($limit > 0) {
        $limitfrom = 0;
        $limitnum  = $limit;
    } else if ($page != -1) {
        $limitfrom = $page*$perpage;
        $limitnum  = $perpage;
    } else {
        $limitfrom = 0;
        $limitnum  = 0;
    }

    if ($forumsort == "") {
        $orderby = "";
        $groupby = "";

    } else {
        $orderby = "ORDER BY $forumsort";
        $groupby = ", ".strtolower($forumsort);
        $groupby = str_replace('desc', '', $groupby);
        $groupby = str_replace('asc', '', $groupby);
    }

    if (($limitfrom == 0 and $limitnum == 0) or $forumsort == "") {
        $sql = "SELECT p.discussion, COUNT(p.id) AS replies, MAX(p.id) AS lastpostid
                  FROM {forum_posts} p
                       JOIN {forum_discussions} d ON p.discussion = d.id
                 WHERE p.parent > 0 AND d.forum = ?
              GROUP BY p.discussion";
        return $DB->get_records_sql($sql, array($forumid));

    } else {
        $sql = "SELECT p.discussion, (COUNT(p.id) - 1) AS replies, MAX(p.id) AS lastpostid
                  FROM {forum_posts} p
                       JOIN {forum_discussions} d ON p.discussion = d.id
                 WHERE d.forum = ?
              GROUP BY p.discussion $groupby $orderby";
        return $DB->get_records_sql($sql, array($forumid), $limitfrom, $limitnum);
    }
}


function forum_count_discussions($forum, $cm, $course) {
    global $CFG, $DB, $USER;

    static $cache = array();

    $now = round(time(), -2);
    $params = array($course->id);

    if (!isset($cache[$course->id])) {
        if (!empty($CFG->forum_enabletimedposts)) {
            $timedsql = "AND d.timestart < ? AND (d.timeend = 0 OR d.timeend > ?)";
            $params[] = $now;
            $params[] = $now;
        } else {
            $timedsql = "";
        }

        $sql = "SELECT f.id, COUNT(d.id) as dcount
                  FROM {forum} f
                       JOIN {forum_discussions} d ON d.forum = f.id
                 WHERE f.course = ?
                       $timedsql
              GROUP BY f.id";

        if ($counts = $DB->get_records_sql($sql, $params)) {
            foreach ($counts as $count) {
                $counts[$count->id] = $count->dcount;
            }
            $cache[$course->id] = $counts;
        } else {
            $cache[$course->id] = array();
        }
    }

    if (empty($cache[$course->id][$forum->id])) {
        return 0;
    }

    $groupmode = groups_get_activity_groupmode($cm, $course);

    if ($groupmode != SEPARATEGROUPS) {
        return $cache[$course->id][$forum->id];
    }

    if (has_capability('moodle/site:accessallgroups', context_module::instance($cm->id))) {
        return $cache[$course->id][$forum->id];
    }

    require_once($CFG->dirroot.'/course/lib.php');

    $modinfo = get_fast_modinfo($course);

    $mygroups = $modinfo->get_groups($cm->groupingid);

        $mygroups[-1] = -1;

    list($mygroups_sql, $params) = $DB->get_in_or_equal($mygroups);
    $params[] = $forum->id;

    if (!empty($CFG->forum_enabletimedposts)) {
        $timedsql = "AND d.timestart < $now AND (d.timeend = 0 OR d.timeend > $now)";
        $params[] = $now;
        $params[] = $now;
    } else {
        $timedsql = "";
    }

    $sql = "SELECT COUNT(d.id)
              FROM {forum_discussions} d
             WHERE d.groupid $mygroups_sql AND d.forum = ?
                   $timedsql";

    return $DB->get_field_sql($sql, $params);
}


function forum_get_discussions($cm, $forumsort="", $fullpost=true, $unused=-1, $limit=-1,
                                $userlastmodified=false, $page=-1, $perpage=0, $groupid = -1) {
    global $CFG, $DB, $USER;

    $timelimit = '';

    $now = round(time(), -2);
    $params = array($cm->instance);

    $modcontext = context_module::instance($cm->id);

    if (!has_capability('mod/forum:viewdiscussion', $modcontext)) {         return array();
    }

    if (!empty($CFG->forum_enabletimedposts)) {
        if (!has_capability('mod/forum:viewhiddentimedposts', $modcontext)) {
            $timelimit = " AND ((d.timestart <= ? AND (d.timeend = 0 OR d.timeend > ?))";
            $params[] = $now;
            $params[] = $now;
            if (isloggedin()) {
                $timelimit .= " OR d.userid = ?";
                $params[] = $USER->id;
            }
            $timelimit .= ")";
        }
    }

    if ($limit > 0) {
        $limitfrom = 0;
        $limitnum  = $limit;
    } else if ($page != -1) {
        $limitfrom = $page*$perpage;
        $limitnum  = $perpage;
    } else {
        $limitfrom = 0;
        $limitnum  = 0;
    }

    $groupmode    = groups_get_activity_groupmode($cm);

    if ($groupmode) {

        if (empty($modcontext)) {
            $modcontext = context_module::instance($cm->id);
        }

                if ($groupid > 0) {
            $course = get_course($cm->course);
            if (!groups_group_visible($groupid, $course, $cm)) {
                                return array();
            }
            $currentgroup = $groupid;
        } else if ($groupid === -1) {
            $currentgroup = groups_get_activity_group($cm);
        } else {
                        $currentgroup = null;
        }

        if ($groupmode == VISIBLEGROUPS or has_capability('moodle/site:accessallgroups', $modcontext)) {
            if ($currentgroup) {
                $groupselect = "AND (d.groupid = ? OR d.groupid = -1)";
                $params[] = $currentgroup;
            } else {
                $groupselect = "";
            }

        } else {

                        if ($currentgroup === null) {
                $mygroups = array_keys(groups_get_all_groups($cm->course, $USER->id, $cm->groupingid, 'g.id'));
                if (empty($mygroups)) {
                     $groupselect = "AND d.groupid = -1";
                } else {
                    list($insqlgroups, $inparamsgroups) = $DB->get_in_or_equal($mygroups);
                    $groupselect = "AND (d.groupid = -1 OR d.groupid $insqlgroups)";
                    $params = array_merge($params, $inparamsgroups);
                }
            } else if ($currentgroup) {
                $groupselect = "AND (d.groupid = ? OR d.groupid = -1)";
                $params[] = $currentgroup;
            } else {
                $groupselect = "AND d.groupid = -1";
            }
        }
    } else {
        $groupselect = "";
    }
    if (empty($forumsort)) {
        $forumsort = forum_get_default_sort_order();
    }
    if (empty($fullpost)) {
        $postdata = "p.id,p.subject,p.modified,p.discussion,p.userid";
    } else {
        $postdata = "p.*";
    }

    if (empty($userlastmodified)) {          $umfields = "";
        $umtable  = "";
    } else {
        $umfields = ', ' . get_all_user_name_fields(true, 'um', null, 'um') . ', um.email AS umemail, um.picture AS umpicture,
                        um.imagealt AS umimagealt';
        $umtable  = " LEFT JOIN {user} um ON (d.usermodified = um.id)";
    }

    $allnames = get_all_user_name_fields(true, 'u');
    $sql = "SELECT $postdata, d.name, d.timemodified, d.usermodified, d.groupid, d.timestart, d.timeend, d.pinned, $allnames,
                   u.email, u.picture, u.imagealt $umfields
              FROM {forum_discussions} d
                   JOIN {forum_posts} p ON p.discussion = d.id
                   JOIN {user} u ON p.userid = u.id
                   $umtable
             WHERE d.forum = ? AND p.parent = 0
                   $timelimit $groupselect
          ORDER BY $forumsort, d.id DESC";
    return $DB->get_records_sql($sql, $params, $limitfrom, $limitnum);
}


function forum_get_discussion_neighbours($cm, $discussion, $forum) {
    global $CFG, $DB, $USER;

    if ($cm->instance != $discussion->forum or $discussion->forum != $forum->id or $forum->id != $cm->instance) {
        throw new coding_exception('Discussion is not part of the same forum.');
    }

    $neighbours = array('prev' => false, 'next' => false);
    $now = round(time(), -2);
    $params = array();

    $modcontext = context_module::instance($cm->id);
    $groupmode    = groups_get_activity_groupmode($cm);
    $currentgroup = groups_get_activity_group($cm);

        $timelimit = '';
    if (!empty($CFG->forum_enabletimedposts)) {
        if (!has_capability('mod/forum:viewhiddentimedposts', $modcontext)) {
            $timelimit = ' AND ((d.timestart <= :tltimestart AND (d.timeend = 0 OR d.timeend > :tltimeend))';
            $params['tltimestart'] = $now;
            $params['tltimeend'] = $now;
            if (isloggedin()) {
                $timelimit .= ' OR d.userid = :tluserid';
                $params['tluserid'] = $USER->id;
            }
            $timelimit .= ')';
        }
    }

        $groupselect = '';
    if ($groupmode) {
        if ($groupmode == VISIBLEGROUPS || has_capability('moodle/site:accessallgroups', $modcontext)) {
            if ($currentgroup) {
                $groupselect = 'AND (d.groupid = :groupid OR d.groupid = -1)';
                $params['groupid'] = $currentgroup;
            }
        } else {
            if ($currentgroup) {
                $groupselect = 'AND (d.groupid = :groupid OR d.groupid = -1)';
                $params['groupid'] = $currentgroup;
            } else {
                $groupselect = 'AND d.groupid = -1';
            }
        }
    }

    $params['forumid'] = $cm->instance;
    $params['discid1'] = $discussion->id;
    $params['discid2'] = $discussion->id;
    $params['discid3'] = $discussion->id;
    $params['discid4'] = $discussion->id;
    $params['disctimecompare1'] = $discussion->timemodified;
    $params['disctimecompare2'] = $discussion->timemodified;
    $params['pinnedstate1'] = (int) $discussion->pinned;
    $params['pinnedstate2'] = (int) $discussion->pinned;
    $params['pinnedstate3'] = (int) $discussion->pinned;
    $params['pinnedstate4'] = (int) $discussion->pinned;

    $sql = "SELECT d.id, d.name, d.timemodified, d.groupid, d.timestart, d.timeend
              FROM {forum_discussions} d
              JOIN {forum_posts} p ON d.firstpost = p.id
             WHERE d.forum = :forumid
               AND d.id <> :discid1
                   $timelimit
                   $groupselect";
    $comparefield = "d.timemodified";
    $comparevalue = ":disctimecompare1";
    $comparevalue2  = ":disctimecompare2";
    if (!empty($CFG->forum_enabletimedposts)) {
                                                                $comparefield = "CASE WHEN d.timemodified < d.timestart
                                THEN d.timestart ELSE d.timemodified END";
        if ($discussion->timemodified < $discussion->timestart) {
                                                            $params['disctimecompare1'] = $discussion->timestart;
            $params['disctimecompare2'] = $discussion->timestart;
        }
    }
    $orderbydesc = forum_get_default_sort_order(true, $comparefield, 'd', false);
    $orderbyasc = forum_get_default_sort_order(false, $comparefield, 'd', false);

    if ($forum->type === 'blog') {
         $subselect = "SELECT pp.created
                   FROM {forum_discussions} dd
                   JOIN {forum_posts} pp ON dd.firstpost = pp.id ";

         $subselectwhere1 = " WHERE dd.id = :discid3";
         $subselectwhere2 = " WHERE dd.id = :discid4";

         $comparefield = "p.created";

         $sub1 = $subselect.$subselectwhere1;
         $comparevalue = "($sub1)";

         $sub2 = $subselect.$subselectwhere2;
         $comparevalue2 = "($sub2)";

         $orderbydesc = "d.pinned, p.created DESC";
         $orderbyasc = "d.pinned, p.created ASC";
    }

    $prevsql = $sql . " AND ( (($comparefield < $comparevalue) AND :pinnedstate1 = d.pinned)
                         OR ($comparefield = $comparevalue2 AND (d.pinned = 0 OR d.pinned = :pinnedstate4) AND d.id < :discid2)
                         OR (d.pinned = 0 AND d.pinned <> :pinnedstate2))
                   ORDER BY CASE WHEN d.pinned = :pinnedstate3 THEN 1 ELSE 0 END DESC, $orderbydesc, d.id DESC";

    $nextsql = $sql . " AND ( (($comparefield > $comparevalue) AND :pinnedstate1 = d.pinned)
                         OR ($comparefield = $comparevalue2 AND (d.pinned = 1 OR d.pinned = :pinnedstate4) AND d.id > :discid2)
                         OR (d.pinned = 1 AND d.pinned <> :pinnedstate2))
                   ORDER BY CASE WHEN d.pinned = :pinnedstate3 THEN 1 ELSE 0 END DESC, $orderbyasc, d.id ASC";

    $neighbours['prev'] = $DB->get_record_sql($prevsql, $params, IGNORE_MULTIPLE);
    $neighbours['next'] = $DB->get_record_sql($nextsql, $params, IGNORE_MULTIPLE);
    return $neighbours;
}


function forum_get_default_sort_order($desc = true, $compare = 'd.timemodified', $prefix = 'd', $pinned = true) {
    global $CFG;

    if (!empty($prefix)) {
        $prefix .= '.';
    }

    $dir = $desc ? 'DESC' : 'ASC';

    if ($pinned == true) {
        $pinned = "{$prefix}pinned DESC,";
    } else {
        $pinned = '';
    }

    $sort = "{$prefix}timemodified";
    if (!empty($CFG->forum_enabletimedposts)) {
        $sort = "CASE WHEN {$compare} < {$prefix}timestart
                 THEN {$prefix}timestart
                 ELSE {$compare}
                 END";
    }
    return "$pinned $sort $dir";
}


function forum_get_discussions_unread($cm) {
    global $CFG, $DB, $USER;

    $now = round(time(), -2);
    $cutoffdate = $now - ($CFG->forum_oldpostdays*24*60*60);

    $params = array();
    $groupmode    = groups_get_activity_groupmode($cm);
    $currentgroup = groups_get_activity_group($cm);

    if ($groupmode) {
        $modcontext = context_module::instance($cm->id);

        if ($groupmode == VISIBLEGROUPS or has_capability('moodle/site:accessallgroups', $modcontext)) {
            if ($currentgroup) {
                $groupselect = "AND (d.groupid = :currentgroup OR d.groupid = -1)";
                $params['currentgroup'] = $currentgroup;
            } else {
                $groupselect = "";
            }

        } else {
                        if ($currentgroup) {
                $groupselect = "AND (d.groupid = :currentgroup OR d.groupid = -1)";
                $params['currentgroup'] = $currentgroup;
            } else {
                $groupselect = "AND d.groupid = -1";
            }
        }
    } else {
        $groupselect = "";
    }

    if (!empty($CFG->forum_enabletimedposts)) {
        $timedsql = "AND d.timestart < :now1 AND (d.timeend = 0 OR d.timeend > :now2)";
        $params['now1'] = $now;
        $params['now2'] = $now;
    } else {
        $timedsql = "";
    }

    $sql = "SELECT d.id, COUNT(p.id) AS unread
              FROM {forum_discussions} d
                   JOIN {forum_posts} p     ON p.discussion = d.id
                   LEFT JOIN {forum_read} r ON (r.postid = p.id AND r.userid = $USER->id)
             WHERE d.forum = {$cm->instance}
                   AND p.modified >= :cutoffdate AND r.id is NULL
                   $groupselect
                   $timedsql
          GROUP BY d.id";
    $params['cutoffdate'] = $cutoffdate;

    if ($unreads = $DB->get_records_sql($sql, $params)) {
        foreach ($unreads as $unread) {
            $unreads[$unread->id] = $unread->unread;
        }
        return $unreads;
    } else {
        return array();
    }
}


function forum_get_discussions_count($cm) {
    global $CFG, $DB, $USER;

    $now = round(time(), -2);
    $params = array($cm->instance);
    $groupmode    = groups_get_activity_groupmode($cm);
    $currentgroup = groups_get_activity_group($cm);

    if ($groupmode) {
        $modcontext = context_module::instance($cm->id);

        if ($groupmode == VISIBLEGROUPS or has_capability('moodle/site:accessallgroups', $modcontext)) {
            if ($currentgroup) {
                $groupselect = "AND (d.groupid = ? OR d.groupid = -1)";
                $params[] = $currentgroup;
            } else {
                $groupselect = "";
            }

        } else {
                        if ($currentgroup) {
                $groupselect = "AND (d.groupid = ? OR d.groupid = -1)";
                $params[] = $currentgroup;
            } else {
                $groupselect = "AND d.groupid = -1";
            }
        }
    } else {
        $groupselect = "";
    }

    $timelimit = "";

    if (!empty($CFG->forum_enabletimedposts)) {

        $modcontext = context_module::instance($cm->id);

        if (!has_capability('mod/forum:viewhiddentimedposts', $modcontext)) {
            $timelimit = " AND ((d.timestart <= ? AND (d.timeend = 0 OR d.timeend > ?))";
            $params[] = $now;
            $params[] = $now;
            if (isloggedin()) {
                $timelimit .= " OR d.userid = ?";
                $params[] = $USER->id;
            }
            $timelimit .= ")";
        }
    }

    $sql = "SELECT COUNT(d.id)
              FROM {forum_discussions} d
                   JOIN {forum_posts} p ON p.discussion = d.id
             WHERE d.forum = ? AND p.parent = 0
                   $groupselect $timelimit";

    return $DB->get_field_sql($sql, $params);
}





function forum_get_course_forum($courseid, $type) {
    global $CFG, $DB, $OUTPUT, $USER;

    if ($forums = $DB->get_records_select("forum", "course = ? AND type = ?", array($courseid, $type), "id ASC")) {
                        foreach ($forums as $forum) {
            return $forum;           }
    }

        $forum = new stdClass();
    $forum->course = $courseid;
    $forum->type = "$type";
    if (!empty($USER->htmleditor)) {
        $forum->introformat = $USER->htmleditor;
    }
    switch ($forum->type) {
        case "news":
            $forum->name  = get_string("namenews", "forum");
            $forum->intro = get_string("intronews", "forum");
            $forum->forcesubscribe = FORUM_FORCESUBSCRIBE;
            $forum->assessed = 0;
            if ($courseid == SITEID) {
                $forum->name  = get_string("sitenews");
                $forum->forcesubscribe = 0;
            }
            break;
        case "social":
            $forum->name  = get_string("namesocial", "forum");
            $forum->intro = get_string("introsocial", "forum");
            $forum->assessed = 0;
            $forum->forcesubscribe = 0;
            break;
        case "blog":
            $forum->name = get_string('blogforum', 'forum');
            $forum->intro = get_string('introblog', 'forum');
            $forum->assessed = 0;
            $forum->forcesubscribe = 0;
            break;
        default:
            echo $OUTPUT->notification("That forum type doesn't exist!");
            return false;
            break;
    }

    $forum->timemodified = time();
    $forum->id = $DB->insert_record("forum", $forum);

    if (! $module = $DB->get_record("modules", array("name" => "forum"))) {
        echo $OUTPUT->notification("Could not find forum module!!");
        return false;
    }
    $mod = new stdClass();
    $mod->course = $courseid;
    $mod->module = $module->id;
    $mod->instance = $forum->id;
    $mod->section = 0;
    include_once("$CFG->dirroot/course/lib.php");
    if (! $mod->coursemodule = add_course_module($mod) ) {
        echo $OUTPUT->notification("Could not add a new course module to the course '" . $courseid . "'");
        return false;
    }
    $sectionid = course_add_cm_to_section($courseid, $mod->coursemodule, 0);
    return $DB->get_record("forum", array("id" => "$forum->id"));
}


function forum_print_post($post, $discussion, $forum, &$cm, $course, $ownpost=false, $reply=false, $link=false,
                          $footer="", $highlight="", $postisread=null, $dummyifcantsee=true, $istracked=null, $return=false) {
    global $USER, $CFG, $OUTPUT;

    require_once($CFG->libdir . '/filelib.php');

        static $str;
                        static $firstunreadanchorprinted = false;

    $modcontext = context_module::instance($cm->id);

    $post->course = $course->id;
    $post->forum  = $forum->id;
    $post->message = file_rewrite_pluginfile_urls($post->message, 'pluginfile.php', $modcontext->id, 'mod_forum', 'post', $post->id);
    if (!empty($CFG->enableplagiarism)) {
        require_once($CFG->libdir.'/plagiarismlib.php');
        $post->message .= plagiarism_get_links(array('userid' => $post->userid,
            'content' => $post->message,
            'cmid' => $cm->id,
            'course' => $post->course,
            'forum' => $post->forum));
    }

        if (!isset($cm->cache)) {
        $cm->cache = new stdClass;
    }

    if (!isset($cm->cache->caps)) {
        $cm->cache->caps = array();
        $cm->cache->caps['mod/forum:viewdiscussion']   = has_capability('mod/forum:viewdiscussion', $modcontext);
        $cm->cache->caps['moodle/site:viewfullnames']  = has_capability('moodle/site:viewfullnames', $modcontext);
        $cm->cache->caps['mod/forum:editanypost']      = has_capability('mod/forum:editanypost', $modcontext);
        $cm->cache->caps['mod/forum:splitdiscussions'] = has_capability('mod/forum:splitdiscussions', $modcontext);
        $cm->cache->caps['mod/forum:deleteownpost']    = has_capability('mod/forum:deleteownpost', $modcontext);
        $cm->cache->caps['mod/forum:deleteanypost']    = has_capability('mod/forum:deleteanypost', $modcontext);
        $cm->cache->caps['mod/forum:viewanyrating']    = has_capability('mod/forum:viewanyrating', $modcontext);
        $cm->cache->caps['mod/forum:exportpost']       = has_capability('mod/forum:exportpost', $modcontext);
        $cm->cache->caps['mod/forum:exportownpost']    = has_capability('mod/forum:exportownpost', $modcontext);
    }

    if (!isset($cm->uservisible)) {
        $cm->uservisible = \core_availability\info_module::is_user_visible($cm, 0, false);
    }

    if ($istracked && is_null($postisread)) {
        $postisread = forum_tp_is_post_read($USER->id, $post);
    }

    if (!forum_user_can_see_post($forum, $discussion, $post, NULL, $cm)) {
        $output = '';
        if (!$dummyifcantsee) {
            if ($return) {
                return $output;
            }
            echo $output;
            return;
        }
        $output .= html_writer::tag('a', '', array('id'=>'p'.$post->id));
        $output .= html_writer::start_tag('div', array('class'=>'forumpost clearfix',
                                                       'role' => 'region',
                                                       'aria-label' => get_string('hiddenforumpost', 'forum')));
        $output .= html_writer::start_tag('div', array('class'=>'row header'));
        $output .= html_writer::tag('div', '', array('class'=>'left picture'));         if ($post->parent) {
            $output .= html_writer::start_tag('div', array('class'=>'topic'));
        } else {
            $output .= html_writer::start_tag('div', array('class'=>'topic starter'));
        }
        $output .= html_writer::tag('div', get_string('forumsubjecthidden','forum'), array('class' => 'subject',
                                                                                           'role' => 'header'));         $output .= html_writer::tag('div', get_string('forumauthorhidden', 'forum'), array('class' => 'author',
                                                                                           'role' => 'header'));         $output .= html_writer::end_tag('div');
        $output .= html_writer::end_tag('div');         $output .= html_writer::start_tag('div', array('class'=>'row'));
        $output .= html_writer::tag('div', '&nbsp;', array('class'=>'left side'));         $output .= html_writer::tag('div', get_string('forumbodyhidden','forum'), array('class'=>'content'));         $output .= html_writer::end_tag('div');         $output .= html_writer::end_tag('div');
        if ($return) {
            return $output;
        }
        echo $output;
        return;
    }

    if (empty($str)) {
        $str = new stdClass;
        $str->edit         = get_string('edit', 'forum');
        $str->delete       = get_string('delete', 'forum');
        $str->reply        = get_string('reply', 'forum');
        $str->parent       = get_string('parent', 'forum');
        $str->pruneheading = get_string('pruneheading', 'forum');
        $str->prune        = get_string('prune', 'forum');
        $str->displaymode     = get_user_preferences('forum_displaymode', $CFG->forum_displaymode);
        $str->markread     = get_string('markread', 'forum');
        $str->markunread   = get_string('markunread', 'forum');
    }

    $discussionlink = new moodle_url('/mod/forum/discuss.php', array('d'=>$post->discussion));

        $postuser = new stdClass;
    $postuserfields = explode(',', user_picture::fields());
    $postuser = username_load_fields_from_object($postuser, $post, null, $postuserfields);
    $postuser->id = $post->userid;
    $postuser->fullname    = fullname($postuser, $cm->cache->caps['moodle/site:viewfullnames']);
    $postuser->profilelink = new moodle_url('/user/view.php', array('id'=>$post->userid, 'course'=>$course->id));

        if (isset($cm->cache->usersgroups)) {
        $groups = array();
        if (isset($cm->cache->usersgroups[$post->userid])) {
            foreach ($cm->cache->usersgroups[$post->userid] as $gid) {
                $groups[$gid] = $cm->cache->groups[$gid];
            }
        }
    } else {
        $groups = groups_get_all_groups($course->id, $post->userid, $cm->groupingid);
    }

        list($attachments, $attachedimages) = forum_print_attachments($post, $cm, 'separateimages');

        $shortenpost = ($link && (strlen(strip_tags($post->message)) > $CFG->forum_longpost));


        $commands = array();

        $permalink = new moodle_url($discussionlink);
    $permalink->set_anchor('p' . $post->id);
    $commands[] = array('url' => $permalink, 'text' => get_string('permalink', 'forum'));

            if ($istracked && $CFG->forum_usermarksread && isloggedin()) {
        $url = new moodle_url($discussionlink, array('postid'=>$post->id, 'mark'=>'unread'));
        $text = $str->markunread;
        if (!$postisread) {
            $url->param('mark', 'read');
            $text = $str->markread;
        }
        if ($str->displaymode == FORUM_MODE_THREADED) {
            $url->param('parent', $post->parent);
        } else {
            $url->set_anchor('p'.$post->id);
        }
        $commands[] = array('url'=>$url, 'text'=>$text);
    }

        if ($post->parent) {
        $url = new moodle_url($discussionlink);
        if ($str->displaymode == FORUM_MODE_THREADED) {
            $url->param('parent', $post->parent);
        } else {
            $url->set_anchor('p'.$post->parent);
        }
        $commands[] = array('url'=>$url, 'text'=>$str->parent);
    }

        $age = time() - $post->created;
    if (!$post->parent && $forum->type == 'news' && $discussion->timestart > time()) {
        $age = 0;
    }


    if ($forum->type == 'single' and $discussion->firstpost == $post->id) {
        if (has_capability('moodle/course:manageactivities', $modcontext)) {
                        $commands[] = array('url'=>new moodle_url('/course/modedit.php', array('update'=>$cm->id, 'sesskey'=>sesskey(), 'return'=>1)), 'text'=>$str->edit);
        }
    } else if (($ownpost && $age < $CFG->maxeditingtime) || $cm->cache->caps['mod/forum:editanypost']) {
        $commands[] = array('url'=>new moodle_url('/mod/forum/post.php', array('edit'=>$post->id)), 'text'=>$str->edit);
    }

    if ($cm->cache->caps['mod/forum:splitdiscussions'] && $post->parent && $forum->type != 'single') {
        $commands[] = array('url'=>new moodle_url('/mod/forum/post.php', array('prune'=>$post->id)), 'text'=>$str->prune, 'title'=>$str->pruneheading);
    }

    if ($forum->type == 'single' and $discussion->firstpost == $post->id) {
            } else if (($ownpost && $age < $CFG->maxeditingtime && $cm->cache->caps['mod/forum:deleteownpost']) || $cm->cache->caps['mod/forum:deleteanypost']) {
        $commands[] = array('url'=>new moodle_url('/mod/forum/post.php', array('delete'=>$post->id)), 'text'=>$str->delete);
    }

    if ($reply) {
        $commands[] = array('url'=>new moodle_url('/mod/forum/post.php#mformforum', array('reply'=>$post->id)), 'text'=>$str->reply);
    }

    if ($CFG->enableportfolios && ($cm->cache->caps['mod/forum:exportpost'] || ($ownpost && $cm->cache->caps['mod/forum:exportownpost']))) {
        $p = array('postid' => $post->id);
        require_once($CFG->libdir.'/portfoliolib.php');
        $button = new portfolio_add_button();
        $button->set_callback_options('forum_portfolio_caller', array('postid' => $post->id), 'mod_forum');
        if (empty($attachments)) {
            $button->set_formats(PORTFOLIO_FORMAT_PLAINHTML);
        } else {
            $button->set_formats(PORTFOLIO_FORMAT_RICHHTML);
        }

        $porfoliohtml = $button->to_html(PORTFOLIO_ADD_TEXT_LINK);
        if (!empty($porfoliohtml)) {
            $commands[] = $porfoliohtml;
        }
    }



    $output  = '';

    if ($istracked) {
        if ($postisread) {
            $forumpostclass = ' read';
        } else {
            $forumpostclass = ' unread';
                        if (!$firstunreadanchorprinted) {
                $output .= html_writer::tag('a', '', array('id' => 'unread'));
                $firstunreadanchorprinted = true;
            }
        }
    } else {
                $forumpostclass = '';
    }

    $topicclass = '';
    if (empty($post->parent)) {
        $topicclass = ' firstpost starter';
    }

    if (!empty($post->lastpost)) {
        $forumpostclass .= ' lastpost';
    }

    $postbyuser = new stdClass;
    $postbyuser->post = $post->subject;
    $postbyuser->user = $postuser->fullname;
    $discussionbyuser = get_string('postbyuser', 'forum', $postbyuser);
    $output .= html_writer::tag('a', '', array('id'=>'p'.$post->id));
    $output .= html_writer::start_tag('div', array('class'=>'forumpost clearfix'.$forumpostclass.$topicclass,
                                                   'role' => 'region',
                                                   'aria-label' => $discussionbyuser));
    $output .= html_writer::start_tag('div', array('class'=>'row header clearfix'));
    $output .= html_writer::start_tag('div', array('class'=>'left picture'));
    $output .= $OUTPUT->user_picture($postuser, array('courseid'=>$course->id));
    $output .= html_writer::end_tag('div');


    $output .= html_writer::start_tag('div', array('class'=>'topic'.$topicclass));

    $postsubject = $post->subject;
    if (empty($post->subjectnoformat)) {
        $postsubject = format_string($postsubject);
    }
    $output .= html_writer::tag('div', $postsubject, array('class'=>'subject',
                                                           'role' => 'heading',
                                                           'aria-level' => '2'));

    $by = new stdClass();
        $by->name = $postuser->fullname;
    $by->date = userdate($post->modified);
    $output .= html_writer::tag('div', get_string('bynameondate', 'forum', $by), array('class'=>'author',
                                                                                       'role' => 'heading',
                                                                                       'aria-level' => '2'));

    $output .= html_writer::end_tag('div');     $output .= html_writer::end_tag('div');
    $output .= html_writer::start_tag('div', array('class'=>'row maincontent clearfix'));
    $output .= html_writer::start_tag('div', array('class'=>'left'));

    $groupoutput = '';
    if ($groups) {
        $groupoutput = print_group_picture($groups, $course->id, false, true, true);
    }
    if (empty($groupoutput)) {
        $groupoutput = '&nbsp;';
    }
    $output .= html_writer::tag('div', $groupoutput, array('class'=>'grouppictures'));

    $output .= html_writer::end_tag('div');     $output .= html_writer::start_tag('div', array('class'=>'no-overflow'));
    $output .= html_writer::start_tag('div', array('class'=>'content'));

    $options = new stdClass;
    $options->para    = false;
    $options->trusted = $post->messagetrust;
    $options->context = $modcontext;
    if ($shortenpost) {
                $postclass    = 'shortenedpost';
        $postcontent  = format_text($post->message, $post->messageformat, $options);
        $postcontent  = shorten_text($postcontent, $CFG->forum_shortpost);
        $postcontent .= html_writer::link($discussionlink, get_string('readtherest', 'forum'));
        $postcontent .= html_writer::tag('div', '('.get_string('numwords', 'moodle', count_words($post->message)).')',
            array('class'=>'post-word-count'));
    } else {
                $postclass    = 'fullpost';
        $postcontent  = format_text($post->message, $post->messageformat, $options, $course->id);
        if (!empty($highlight)) {
            $postcontent = highlight($highlight, $postcontent);
        }
        if (!empty($forum->displaywordcount)) {
            $postcontent .= html_writer::tag('div', get_string('numwords', 'moodle', count_words($post->message)),
                array('class'=>'post-word-count'));
        }
        $postcontent .= html_writer::tag('div', $attachedimages, array('class'=>'attachedimages'));
    }

        $output .= html_writer::tag('div', $postcontent, array('class'=>'posting '.$postclass));
    $output .= html_writer::end_tag('div');     $output .= html_writer::end_tag('div');     $output .= html_writer::end_tag('div');
    $output .= html_writer::start_tag('div', array('class'=>'row side'));
    $output .= html_writer::tag('div','&nbsp;', array('class'=>'left'));
    $output .= html_writer::start_tag('div', array('class'=>'options clearfix'));

    if (!empty($attachments)) {
        $output .= html_writer::tag('div', $attachments, array('class' => 'attachments'));
    }

        if (!empty($post->rating)) {
        $output .= html_writer::tag('div', $OUTPUT->render($post->rating), array('class'=>'forum-post-rating'));
    }

        $commandhtml = array();
    foreach ($commands as $command) {
        if (is_array($command)) {
            $commandhtml[] = html_writer::link($command['url'], $command['text']);
        } else {
            $commandhtml[] = $command;
        }
    }
    $output .= html_writer::tag('div', implode(' | ', $commandhtml), array('class'=>'commands'));

        if ($link && forum_user_can_post($forum, $discussion, $USER, $cm, $course, $modcontext)) {
        if ($post->replies == 1) {
            $replystring = get_string('repliesone', 'forum', $post->replies);
        } else {
            $replystring = get_string('repliesmany', 'forum', $post->replies);
        }
        if (!empty($discussion->unread) && $discussion->unread !== '-') {
            $replystring .= ' <span class="sep">/</span> <span class="unread">';
            if ($discussion->unread == 1) {
                $replystring .= get_string('unreadpostsone', 'forum');
            } else {
                $replystring .= get_string('unreadpostsnumber', 'forum', $discussion->unread);
            }
            $replystring .= '</span>';
        }

        $output .= html_writer::start_tag('div', array('class'=>'link'));
        $output .= html_writer::link($discussionlink, get_string('discussthistopic', 'forum'));
        $output .= '&nbsp;('.$replystring.')';
        $output .= html_writer::end_tag('div');     }

        if ($footer) {
        $output .= html_writer::tag('div', $footer, array('class'=>'footer'));
    }

        $output .= html_writer::end_tag('div');     $output .= html_writer::end_tag('div');     $output .= html_writer::end_tag('div');
        if ($istracked && !$CFG->forum_usermarksread && !$postisread) {
        forum_tp_mark_post_read($USER->id, $post, $forum->id);
    }

    if ($return) {
        return $output;
    }
    echo $output;
    return;
}


function forum_rating_permissions($contextid, $component, $ratingarea) {
    $context = context::instance_by_id($contextid, MUST_EXIST);
    if ($component != 'mod_forum' || $ratingarea != 'post') {
                        return null;
    }
    return array(
        'view'    => has_capability('mod/forum:viewrating', $context),
        'viewany' => has_capability('mod/forum:viewanyrating', $context),
        'viewall' => has_capability('mod/forum:viewallratings', $context),
        'rate'    => has_capability('mod/forum:rate', $context)
    );
}


function forum_rating_validate($params) {
    global $DB, $USER;

        if ($params['component'] != 'mod_forum') {
        throw new rating_exception('invalidcomponent');
    }

        if ($params['ratingarea'] != 'post') {
        throw new rating_exception('invalidratingarea');
    }

        if ($params['rateduserid'] == $USER->id) {
        throw new rating_exception('nopermissiontorate');
    }

        $post = $DB->get_record('forum_posts', array('id' => $params['itemid'], 'userid' => $params['rateduserid']), '*', MUST_EXIST);
    $discussion = $DB->get_record('forum_discussions', array('id' => $post->discussion), '*', MUST_EXIST);
    $forum = $DB->get_record('forum', array('id' => $discussion->forum), '*', MUST_EXIST);
    $course = $DB->get_record('course', array('id' => $forum->course), '*', MUST_EXIST);
    $cm = get_coursemodule_from_instance('forum', $forum->id, $course->id , false, MUST_EXIST);
    $context = context_module::instance($cm->id);

        if ($context->id != $params['context']->id) {
        throw new rating_exception('invalidcontext');
    }

    if ($forum->scale != $params['scaleid']) {
                throw new rating_exception('invalidscaleid');
    }

        if (!empty($forum->assesstimestart) && !empty($forum->assesstimefinish)) {
        if ($post->created < $forum->assesstimestart || $post->created > $forum->assesstimefinish) {
            throw new rating_exception('notavailable');
        }
    }


        if ($params['rating'] < 0  && $params['rating'] != RATING_UNSET_RATING) {
        throw new rating_exception('invalidnum');
    }

        if ($forum->scale < 0) {
                $scalerecord = $DB->get_record('scale', array('id' => -$forum->scale));
        if ($scalerecord) {
            $scalearray = explode(',', $scalerecord->scale);
            if ($params['rating'] > count($scalearray)) {
                throw new rating_exception('invalidnum');
            }
        } else {
            throw new rating_exception('invalidscaleid');
        }
    } else if ($params['rating'] > $forum->scale) {
                throw new rating_exception('invalidnum');
    }

        if ($discussion->groupid > 0 and $groupmode = groups_get_activity_groupmode($cm, $course)) {           if (!groups_group_exists($discussion->groupid)) {             throw new rating_exception('cannotfindgroup');        }

        if (!groups_is_member($discussion->groupid) and !has_capability('moodle/site:accessallgroups', $context)) {
                        throw new rating_exception('notmemberofgroup');
        }
    }

        if (!forum_user_can_see_post($forum, $discussion, $post, $USER, $cm)) {
        throw new rating_exception('nopermissiontorate');
    }

    return true;
}


function mod_forum_rating_can_see_item_ratings($params) {
    global $DB, $USER;

        if (!isset($params['component']) || $params['component'] != 'mod_forum') {
        throw new rating_exception('invalidcomponent');
    }

        if (!isset($params['ratingarea']) || $params['ratingarea'] != 'post') {
        throw new rating_exception('invalidratingarea');
    }

    if (!isset($params['itemid'])) {
        throw new rating_exception('invaliditemid');
    }

    $post = $DB->get_record('forum_posts', array('id' => $params['itemid']), '*', MUST_EXIST);
    $discussion = $DB->get_record('forum_discussions', array('id' => $post->discussion), '*', MUST_EXIST);
    $forum = $DB->get_record('forum', array('id' => $discussion->forum), '*', MUST_EXIST);
    $course = $DB->get_record('course', array('id' => $forum->course), '*', MUST_EXIST);
    $cm = get_coursemodule_from_instance('forum', $forum->id, $course->id , false, MUST_EXIST);

        if (!forum_user_can_see_post($forum, $discussion, $post, $USER, $cm)) {
        return false;
    }
    return true;
}


function forum_print_discussion_header(&$post, $forum, $group = -1, $datestring = "",
                                        $cantrack = true, $forumtracked = true, $canviewparticipants = true, $modcontext = null,
                                        $canviewhiddentimedposts = false) {

    global $COURSE, $USER, $CFG, $OUTPUT, $PAGE;

    static $rowcount;
    static $strmarkalldread;

    if (empty($modcontext)) {
        if (!$cm = get_coursemodule_from_instance('forum', $forum->id, $forum->course)) {
            print_error('invalidcoursemodule');
        }
        $modcontext = context_module::instance($cm->id);
    }

    if (!isset($rowcount)) {
        $rowcount = 0;
        $strmarkalldread = get_string('markalldread', 'forum');
    } else {
        $rowcount = ($rowcount + 1) % 2;
    }

    $post->subject = format_string($post->subject,true);

    $timeddiscussion = !empty($CFG->forum_enabletimedposts) && ($post->timestart || $post->timeend);
    $timedoutsidewindow = '';
    if ($timeddiscussion && ($post->timestart > time() || ($post->timeend != 0 && $post->timeend < time()))) {
        $timedoutsidewindow = ' dimmed_text';
    }

    echo "\n\n";
    echo '<tr class="discussion r'.$rowcount.$timedoutsidewindow.'">';

    $topicclass = 'topic starter';
    if (FORUM_DISCUSSION_PINNED == $post->pinned) {
        $topicclass .= ' pinned';
    }
    echo '<td class="'.$topicclass.'">';
    if (FORUM_DISCUSSION_PINNED == $post->pinned) {
        echo $OUTPUT->pix_icon('i/pinned', get_string('discussionpinned', 'forum'), 'mod_forum');
    }
    $canalwaysseetimedpost = $USER->id == $post->userid || $canviewhiddentimedposts;
    if ($timeddiscussion && $canalwaysseetimedpost) {
        echo $PAGE->get_renderer('mod_forum')->timed_discussion_tooltip($post, empty($timedoutsidewindow));
    }

    echo '<a href="'.$CFG->wwwroot.'/mod/forum/discuss.php?d='.$post->discussion.'">'.$post->subject.'</a>';
    echo "</td>\n";

        $postuser = new stdClass();
    $postuserfields = explode(',', user_picture::fields());
    $postuser = username_load_fields_from_object($postuser, $post, null, $postuserfields);
    $postuser->id = $post->userid;
    echo '<td class="picture">';
    echo $OUTPUT->user_picture($postuser, array('courseid'=>$forum->course));
    echo "</td>\n";

        $fullname = fullname($postuser, has_capability('moodle/site:viewfullnames', $modcontext));
    echo '<td class="author">';
    echo '<a href="'.$CFG->wwwroot.'/user/view.php?id='.$post->userid.'&amp;course='.$forum->course.'">'.$fullname.'</a>';
    echo "</td>\n";

        if ($group !== -1) {          echo '<td class="picture group">';
        if (!empty($group->picture) and empty($group->hidepicture)) {
            if ($canviewparticipants && $COURSE->groupmode) {
                $picturelink = true;
            } else {
                $picturelink = false;
            }
            print_group_picture($group, $forum->course, false, false, $picturelink);
        } else if (isset($group->id)) {
            if ($canviewparticipants && $COURSE->groupmode) {
                echo '<a href="'.$CFG->wwwroot.'/user/index.php?id='.$forum->course.'&amp;group='.$group->id.'">'.$group->name.'</a>';
            } else {
                echo $group->name;
            }
        }
        echo "</td>\n";
    }

    if (has_capability('mod/forum:viewdiscussion', $modcontext)) {           echo '<td class="replies">';
        echo '<a href="'.$CFG->wwwroot.'/mod/forum/discuss.php?d='.$post->discussion.'">';
        echo $post->replies.'</a>';
        echo "</td>\n";

        if ($cantrack) {
            echo '<td class="replies">';
            if ($forumtracked) {
                if ($post->unread > 0) {
                    echo '<span class="unread">';
                    echo '<a href="'.$CFG->wwwroot.'/mod/forum/discuss.php?d='.$post->discussion.'#unread">';
                    echo $post->unread;
                    echo '</a>';
                    echo '<a title="'.$strmarkalldread.'" href="'.$CFG->wwwroot.'/mod/forum/markposts.php?f='.
                         $forum->id.'&amp;d='.$post->discussion.'&amp;mark=read&amp;returnpage=view.php&amp;sesskey=' . sesskey() . '">' .
                         '<img src="'.$OUTPUT->pix_url('t/markasread') . '" class="iconsmall" alt="'.$strmarkalldread.'" /></a>';
                    echo '</span>';
                } else {
                    echo '<span class="read">';
                    echo $post->unread;
                    echo '</span>';
                }
            } else {
                echo '<span class="read">';
                echo '-';
                echo '</span>';
            }
            echo "</td>\n";
        }
    }

    echo '<td class="lastpost">';
    $usedate = (empty($post->timemodified)) ? $post->modified : $post->timemodified;      $parenturl = '';
    $usermodified = new stdClass();
    $usermodified->id = $post->usermodified;
    $usermodified = username_load_fields_from_object($usermodified, $post, 'um');

        if ($forum->type !== 'qanda' || $canviewparticipants) {
        echo '<a href="'.$CFG->wwwroot.'/user/view.php?id='.$post->usermodified.'&amp;course='.$forum->course.'">'.
             fullname($usermodified).'</a><br />';
        $parenturl = (empty($post->lastpostid)) ? '' : '&amp;parent='.$post->lastpostid;
    }

    echo '<a href="'.$CFG->wwwroot.'/mod/forum/discuss.php?d='.$post->discussion.$parenturl.'">'.
          userdate($usedate, $datestring).'</a>';
    echo "</td>\n";

            if ((!is_guest($modcontext, $USER) && isloggedin()) && has_capability('mod/forum:viewdiscussion', $modcontext)) {
                if (\mod_forum\subscriptions::is_subscribable($forum)) {
            echo '<td class="discussionsubscription">';
            echo forum_get_discussion_subscription_icon($forum, $post->discussion);
            echo '</td>';
        }
    }

    echo "</tr>\n\n";

}

function forum_print_discussion_newsheader(&$post, $forum, $group = -1, $datestring = "",
                                        $cantrack = true, $forumtracked = true, $canviewparticipants = true, $modcontext = null,
                                        $canviewhiddentimedposts = false) {

    global $COURSE, $USER, $CFG, $OUTPUT, $PAGE;

    static $rowcount;
    static $strmarkalldread;

    if (empty($modcontext)) {
        if (!$cm = get_coursemodule_from_instance('forum', $forum->id, $forum->course)) {
            print_error('invalidcoursemodule');
        }
        $modcontext = context_module::instance($cm->id);
    }

    if (!isset($rowcount)) {
        $rowcount = 0;
        $strmarkalldread = get_string('markalldread', 'forum');
    } else {
        $rowcount = ($rowcount + 1) % 2;
    }

    $post->subject = format_string($post->subject,true);

    $timeddiscussion = !empty($CFG->forum_enabletimedposts) && ($post->timestart || $post->timeend);
    $timedoutsidewindow = '';
    if ($timeddiscussion && ($post->timestart > time() || ($post->timeend != 0 && $post->timeend < time()))) {
        $timedoutsidewindow = ' dimmed_text';
    }

    echo "\n\n";
    echo '<tr class="discussion r'.$rowcount.$timedoutsidewindow.'">';

    $topicclass = 'topic starter';
    if (FORUM_DISCUSSION_PINNED == $post->pinned) {
        $topicclass .= ' pinned';
    }
    echo '<td class="'.$topicclass.'">';
    if (FORUM_DISCUSSION_PINNED == $post->pinned) {
        echo $OUTPUT->pix_icon('i/pinned', get_string('discussionpinned', 'forum'), 'mod_forum');
    }
    $canalwaysseetimedpost = $USER->id == $post->userid || $canviewhiddentimedposts;
    if ($timeddiscussion && $canalwaysseetimedpost) {
        echo $PAGE->get_renderer('mod_forum')->timed_discussion_tooltip($post, empty($timedoutsidewindow));
    }

    echo '<a href="'.$CFG->wwwroot.'/mod/forum/discuss.php?d='.$post->discussion.'">'.$post->subject.'</a>';
    echo "</td>\n";

    echo '<td class="lastpost">';
    $usedate = (empty($post->timemodified)) ? $post->modified : $post->timemodified;      $parenturl = '';

    echo '<a href="'.$CFG->wwwroot.'/mod/forum/discuss.php?d='.$post->discussion.$parenturl.'">'.
          userdate($usedate, $datestring).'</a>';
    echo "</td>\n";
    echo "</tr>\n\n";

}

function forum_get_discussion_subscription_icon($forum, $discussionid, $returnurl = null, $includetext = false) {
    global $USER, $OUTPUT, $PAGE;

    if ($returnurl === null && $PAGE->url) {
        $returnurl = $PAGE->url->out();
    }

    $o = '';
    $subscriptionstatus = \mod_forum\subscriptions::is_subscribed($USER->id, $forum, $discussionid);
    $subscriptionlink = new moodle_url('/mod/forum/subscribe.php', array(
        'sesskey' => sesskey(),
        'id' => $forum->id,
        'd' => $discussionid,
        'returnurl' => $returnurl,
    ));

    if ($includetext) {
        $o .= $subscriptionstatus ? get_string('subscribed', 'mod_forum') : get_string('notsubscribed', 'mod_forum');
    }

    if ($subscriptionstatus) {
        $output = $OUTPUT->pix_icon('t/subscribed', get_string('clicktounsubscribe', 'forum'), 'mod_forum');
        if ($includetext) {
            $output .= get_string('subscribed', 'mod_forum');
        }

        return html_writer::link($subscriptionlink, $output, array(
                'title' => get_string('clicktounsubscribe', 'forum'),
                'class' => 'discussiontoggle iconsmall',
                'data-forumid' => $forum->id,
                'data-discussionid' => $discussionid,
                'data-includetext' => $includetext,
            ));

    } else {
        $output = $OUTPUT->pix_icon('t/unsubscribed', get_string('clicktosubscribe', 'forum'), 'mod_forum');
        if ($includetext) {
            $output .= get_string('notsubscribed', 'mod_forum');
        }

        return html_writer::link($subscriptionlink, $output, array(
                'title' => get_string('clicktosubscribe', 'forum'),
                'class' => 'discussiontoggle iconsmall',
                'data-forumid' => $forum->id,
                'data-discussionid' => $discussionid,
                'data-includetext' => $includetext,
            ));
    }
}


function forum_get_discussion_subscription_icon_preloaders() {
    $o = '';
    $o .= html_writer::span('&nbsp;', 'preload-subscribe');
    $o .= html_writer::span('&nbsp;', 'preload-unsubscribe');
    return $o;
}


function forum_print_mode_form($id, $mode, $forumtype='') {
    global $OUTPUT;

    if ($forumtype == 'single') {
        $select = new single_select(new moodle_url("/mod/forum/view.php", array('f'=>$id)), 'mode', forum_get_layout_modes(), $mode, null, "mode");
        $select->set_label(get_string('displaymode', 'forum'), array('class' => 'accesshide'));
        $select->class = "forummode";
    } else {
        $select = new single_select(new moodle_url("/mod/forum/discuss.php", array('d'=>$id)), 'mode', forum_get_layout_modes(), $mode, null, "mode");
        $select->set_label(get_string('displaymode', 'forum'), array('class' => 'accesshide'));
    }
    echo $OUTPUT->render($select);
}


function forum_search_form($course, $search='') {
    global $CFG, $OUTPUT;

    $output  = '<div class="forumsearch">';
    $output .= '<form action="'.$CFG->wwwroot.'/mod/forum/search.php" style="display:inline">';
    $output .= '<fieldset class="invisiblefieldset">';
    $output .= $OUTPUT->help_icon('search');
    $output .= '<label class="accesshide" for="search" >'.get_string('search', 'forum').'</label>';
    $output .= '<input id="search" name="search" type="text" size="18" value="'.s($search, true).'" />';
    $output .= '<label class="accesshide" for="searchforums" >'.get_string('searchforums', 'forum').'</label>';
    $output .= '<input id="searchforums" value="'.get_string('searchforums', 'forum').'" type="submit" />';
    $output .= '<input name="id" type="hidden" value="'.$course->id.'" />';
    $output .= '</fieldset>';
    $output .= '</form>';
    $output .= '</div>';

    return $output;
}



function forum_set_return() {
    global $CFG, $SESSION;

    if (! isset($SESSION->fromdiscussion)) {
        $referer = get_local_referer(false);
                if (! strncasecmp("$CFG->wwwroot/login", $referer, 300)) {
            $SESSION->fromdiscussion = $referer;
        }
    }
}



function forum_go_back_to($default) {
    global $SESSION;

    if (!empty($SESSION->fromdiscussion)) {
        $returnto = $SESSION->fromdiscussion;
        unset($SESSION->fromdiscussion);
        return $returnto;
    } else {
        return $default;
    }
}


function forum_move_attachments($discussion, $forumfrom, $forumto) {
    global $DB;

    $fs = get_file_storage();

    $newcm = get_coursemodule_from_instance('forum', $forumto);
    $oldcm = get_coursemodule_from_instance('forum', $forumfrom);

    $newcontext = context_module::instance($newcm->id);
    $oldcontext = context_module::instance($oldcm->id);

        if ($posts = $DB->get_records('forum_posts', array('discussion'=>$discussion->id), '', 'id, attachment')) {
        foreach ($posts as $post) {
            $fs->move_area_files_to_new_context($oldcontext->id,
                    $newcontext->id, 'mod_forum', 'post', $post->id);
            $attachmentsmoved = $fs->move_area_files_to_new_context($oldcontext->id,
                    $newcontext->id, 'mod_forum', 'attachment', $post->id);
            if ($attachmentsmoved > 0 && $post->attachment != '1') {
                                $post->attachment = '1';
                $DB->update_record('forum_posts', $post);
            } else if ($attachmentsmoved == 0 && $post->attachment != '') {
                                $post->attachment = '';
                $DB->update_record('forum_posts', $post);
            }
        }
    }

    return true;
}


function forum_print_attachments($post, $cm, $type) {
    global $CFG, $DB, $USER, $OUTPUT;

    if (empty($post->attachment)) {
        return $type !== 'separateimages' ? '' : array('', '');
    }

    if (!in_array($type, array('separateimages', 'html', 'text'))) {
        return $type !== 'separateimages' ? '' : array('', '');
    }

    if (!$context = context_module::instance($cm->id)) {
        return $type !== 'separateimages' ? '' : array('', '');
    }
    $strattachment = get_string('attachment', 'forum');

    $fs = get_file_storage();

    $imagereturn = '';
    $output = '';

    $canexport = !empty($CFG->enableportfolios) && (has_capability('mod/forum:exportpost', $context) || ($post->userid == $USER->id && has_capability('mod/forum:exportownpost', $context)));

    if ($canexport) {
        require_once($CFG->libdir.'/portfoliolib.php');
    }

            $files = $fs->get_area_files($context->id, 'mod_forum', 'attachment', $post->id, "filename", false);
    if ($files) {
        if ($canexport) {
            $button = new portfolio_add_button();
        }
        foreach ($files as $file) {
            $filename = $file->get_filename();
            $mimetype = $file->get_mimetype();
            $iconimage = $OUTPUT->pix_icon(file_file_icon($file), get_mimetype_description($file), 'moodle', array('class' => 'icon'));
            $path = file_encode_url($CFG->wwwroot.'/pluginfile.php', '/'.$context->id.'/mod_forum/attachment/'.$post->id.'/'.$filename);

            if ($type == 'html') {
                $output .= "<a href=\"$path\">$iconimage</a> ";
                $output .= "<a href=\"$path\">".s($filename)."</a>";
                if ($canexport) {
                    $button->set_callback_options('forum_portfolio_caller', array('postid' => $post->id, 'attachment' => $file->get_id()), 'mod_forum');
                    $button->set_format_by_file($file);
                    $output .= $button->to_html(PORTFOLIO_ADD_ICON_LINK);
                }
                $output .= "<br />";

            } else if ($type == 'text') {
                $output .= "$strattachment ".s($filename).":\n$path\n";

            } else {                 if (in_array($mimetype, array('image/gif', 'image/jpeg', 'image/png'))) {
                                        $imagereturn .= "<br /><img src=\"$path\" alt=\"\" />";
                    if ($canexport) {
                        $button->set_callback_options('forum_portfolio_caller', array('postid' => $post->id, 'attachment' => $file->get_id()), 'mod_forum');
                        $button->set_format_by_file($file);
                        $imagereturn .= $button->to_html(PORTFOLIO_ADD_ICON_LINK);
                    }
                } else {
                    $output .= "<a href=\"$path\">$iconimage</a> ";
                    $output .= format_text("<a href=\"$path\">".s($filename)."</a>", FORMAT_HTML, array('context'=>$context));
                    if ($canexport) {
                        $button->set_callback_options('forum_portfolio_caller', array('postid' => $post->id, 'attachment' => $file->get_id()), 'mod_forum');
                        $button->set_format_by_file($file);
                        $output .= $button->to_html(PORTFOLIO_ADD_ICON_LINK);
                    }
                    $output .= '<br />';
                }
            }

            if (!empty($CFG->enableplagiarism)) {
                require_once($CFG->libdir.'/plagiarismlib.php');
                $output .= plagiarism_get_links(array('userid' => $post->userid,
                    'file' => $file,
                    'cmid' => $cm->id,
                    'course' => $cm->course,
                    'forum' => $cm->instance));
                $output .= '<br />';
            }
        }
    }

    if ($type !== 'separateimages') {
        return $output;

    } else {
        return array($output, $imagereturn);
    }
}



function forum_get_file_areas($course, $cm, $context) {
    return array(
        'attachment' => get_string('areaattachment', 'mod_forum'),
        'post' => get_string('areapost', 'mod_forum'),
    );
}


function forum_get_file_info($browser, $areas, $course, $cm, $context, $filearea, $itemid, $filepath, $filename) {
    global $CFG, $DB, $USER;

    if ($context->contextlevel != CONTEXT_MODULE) {
        return null;
    }

        if (!isset($areas[$filearea])) {
        return null;
    }

                if (!has_capability('mod/forum:viewdiscussion', $context)) {
        return null;
    }

    if (is_null($itemid)) {
        require_once($CFG->dirroot.'/mod/forum/locallib.php');
        return new forum_file_info_container($browser, $course, $cm, $context, $areas, $filearea);
    }

    static $cached = array();
            if (!isset($cached['sesskey']) || $cached['sesskey'] != sesskey()) {
        $cached = array('sesskey' => sesskey());
    }

    if (isset($cached['post']) && $cached['post']->id == $itemid) {
        $post = $cached['post'];
    } else if ($post = $DB->get_record('forum_posts', array('id' => $itemid))) {
        $cached['post'] = $post;
    } else {
        return null;
    }

    if (isset($cached['discussion']) && $cached['discussion']->id == $post->discussion) {
        $discussion = $cached['discussion'];
    } else if ($discussion = $DB->get_record('forum_discussions', array('id' => $post->discussion))) {
        $cached['discussion'] = $discussion;
    } else {
        return null;
    }

    if (isset($cached['forum']) && $cached['forum']->id == $cm->instance) {
        $forum = $cached['forum'];
    } else if ($forum = $DB->get_record('forum', array('id' => $cm->instance))) {
        $cached['forum'] = $forum;
    } else {
        return null;
    }

    $fs = get_file_storage();
    $filepath = is_null($filepath) ? '/' : $filepath;
    $filename = is_null($filename) ? '.' : $filename;
    if (!($storedfile = $fs->get_file($context->id, 'mod_forum', $filearea, $itemid, $filepath, $filename))) {
        return null;
    }

            if (!has_capability('moodle/course:managefiles', $context) && $storedfile->get_userid() != $USER->id) {
        return null;
    }
        if ($discussion->groupid > 0 && !has_capability('moodle/site:accessallgroups', $context)) {
        $groupmode = groups_get_activity_groupmode($cm, $course);
        if ($groupmode == SEPARATEGROUPS && !groups_is_member($discussion->groupid)) {
            return null;
        }
    }

        if (!forum_user_can_see_post($forum, $discussion, $post, NULL, $cm)) {
        return null;
    }

    $urlbase = $CFG->wwwroot.'/pluginfile.php';
    return new file_info_stored($browser, $context, $storedfile, $urlbase, $itemid, true, true, false, false);
}


function forum_pluginfile($course, $cm, $context, $filearea, $args, $forcedownload, array $options=array()) {
    global $CFG, $DB;

    if ($context->contextlevel != CONTEXT_MODULE) {
        return false;
    }

    require_course_login($course, true, $cm);

    $areas = forum_get_file_areas($course, $cm, $context);

        if (!isset($areas[$filearea])) {
        return false;
    }

    $postid = (int)array_shift($args);

    if (!$post = $DB->get_record('forum_posts', array('id'=>$postid))) {
        return false;
    }

    if (!$discussion = $DB->get_record('forum_discussions', array('id'=>$post->discussion))) {
        return false;
    }

    if (!$forum = $DB->get_record('forum', array('id'=>$cm->instance))) {
        return false;
    }

    $fs = get_file_storage();
    $relativepath = implode('/', $args);
    $fullpath = "/$context->id/mod_forum/$filearea/$postid/$relativepath";
    if (!$file = $fs->get_file_by_hash(sha1($fullpath)) or $file->is_directory()) {
        return false;
    }

        if ($discussion->groupid > 0) {
        $groupmode = groups_get_activity_groupmode($cm, $course);
        if ($groupmode == SEPARATEGROUPS) {
            if (!groups_is_member($discussion->groupid) and !has_capability('moodle/site:accessallgroups', $context)) {
                return false;
            }
        }
    }

        if (!forum_user_can_see_post($forum, $discussion, $post, NULL, $cm)) {
        return false;
    }

        send_stored_file($file, 0, 0, true, $options); }


function forum_add_attachment($post, $forum, $cm, $mform=null, $unused=null) {
    global $DB;

    if (empty($mform)) {
        return false;
    }

    if (empty($post->attachments)) {
        return true;       }

    $context = context_module::instance($cm->id);

    $info = file_get_draft_area_info($post->attachments);
    $present = ($info['filecount']>0) ? '1' : '';
    file_save_draft_area_files($post->attachments, $context->id, 'mod_forum', 'attachment', $post->id,
            mod_forum_post_form::attachment_options($forum));

    $DB->set_field('forum_posts', 'attachment', $present, array('id'=>$post->id));

    return true;
}


function forum_add_new_post($post, $mform, $unused = null) {
    global $USER, $DB;

    $discussion = $DB->get_record('forum_discussions', array('id' => $post->discussion));
    $forum      = $DB->get_record('forum', array('id' => $discussion->forum));
    $cm         = get_coursemodule_from_instance('forum', $forum->id);
    $context    = context_module::instance($cm->id);

    $post->created    = $post->modified = time();
    $post->mailed     = FORUM_MAILED_PENDING;
    $post->userid     = $USER->id;
    $post->attachment = "";
    if (!isset($post->totalscore)) {
        $post->totalscore = 0;
    }
    if (!isset($post->mailnow)) {
        $post->mailnow    = 0;
    }

    $post->id = $DB->insert_record("forum_posts", $post);
    $post->message = file_save_draft_area_files($post->itemid, $context->id, 'mod_forum', 'post', $post->id,
            mod_forum_post_form::editor_options($context, null), $post->message);
    $DB->set_field('forum_posts', 'message', $post->message, array('id'=>$post->id));
    forum_add_attachment($post, $forum, $cm, $mform);

        $DB->set_field("forum_discussions", "timemodified", $post->modified, array("id" => $post->discussion));
    $DB->set_field("forum_discussions", "usermodified", $post->userid, array("id" => $post->discussion));

    if (forum_tp_can_track_forums($forum) && forum_tp_is_tracked($forum)) {
        forum_tp_mark_post_read($post->userid, $post, $post->forum);
    }

        forum_trigger_content_uploaded_event($post, $cm, 'forum_add_new_post');

    return $post->id;
}


function forum_update_post($newpost, $mform, $unused = null) {
    global $DB, $USER;

    $post       = $DB->get_record('forum_posts', array('id' => $newpost->id));
    $discussion = $DB->get_record('forum_discussions', array('id' => $post->discussion));
    $forum      = $DB->get_record('forum', array('id' => $discussion->forum));
    $cm         = get_coursemodule_from_instance('forum', $forum->id);
    $context    = context_module::instance($cm->id);

        $modifiablefields = [
        'subject',
        'message',
        'messageformat',
        'messagetrust',
        'timestart',
        'timeend',
        'pinned',
        'attachments',
    ];
    foreach ($modifiablefields as $field) {
        if (isset($newpost->{$field})) {
            $post->{$field} = $newpost->{$field};
        }
    }
    $post->modified = time();

        $discussion->timemodified = $post->modified;
    $discussion->usermodified = $USER->id;

    if (!$post->parent) {           $discussion->name      = $post->subject;
        $discussion->timestart = $post->timestart;
        $discussion->timeend   = $post->timeend;

        if (isset($post->pinned)) {
            $discussion->pinned = $post->pinned;
        }
    }
    $post->message = file_save_draft_area_files($newpost->itemid, $context->id, 'mod_forum', 'post', $post->id,
            mod_forum_post_form::editor_options($context, $post->id), $post->message);
    $DB->update_record('forum_posts', $post);
    $DB->update_record('forum_discussions', $discussion);

    forum_add_attachment($post, $forum, $cm, $mform);

    if (forum_tp_can_track_forums($forum) && forum_tp_is_tracked($forum)) {
        forum_tp_mark_post_read($USER->id, $post, $post->forum);
    }

        forum_trigger_content_uploaded_event($post, $cm, 'forum_update_post');

    return true;
}


function forum_add_discussion($discussion, $mform=null, $unused=null, $userid=null) {
    global $USER, $CFG, $DB;

    $timenow = isset($discussion->timenow) ? $discussion->timenow : time();

    if (is_null($userid)) {
        $userid = $USER->id;
    }


    $forum = $DB->get_record('forum', array('id'=>$discussion->forum));
    $cm    = get_coursemodule_from_instance('forum', $forum->id);
	if($forum->type=="snifs"||'ipcs'){
		$dbhost = 'localhost:3306';
		$dbuser = 'root';
		$dbpass = 'la2391';
		$dbname = 'moodle';
		$conn = mysql_connect($dbhost, $dbuser, $dbpass) ;
		mysql_query("SET NAMES 'UTF8'");
		mysql_select_db($dbname);

		//找該堂課的分組
		$sql = "SELECT id,name FROM mdl_groups WHERE courseid =".$forum->course;
		$result = mysql_query($sql);
		$num = mysql_num_rows($result);
		for($i = 0; $i < $num; $i++){
			$row_groupid = mysql_fetch_row($result);

			$post = new stdClass();
			$post->discussion    = 0;
			$post->parent        = 0;
			$post->userid        = $userid;
			$post->created       = $timenow;
			$post->modified      = $timenow;
			$post->mailed        = FORUM_MAILED_PENDING;
			$post->subject       = $discussion->name."(".$row_groupid[1].")";
			$post->message       = $discussion->message;
			$post->messageformat = $discussion->messageformat;
			$post->messagetrust  = $discussion->messagetrust;
			$post->attachments   = isset($discussion->attachments) ? $discussion->attachments : null;
			$post->forum         = $forum->id;
			$post->course        = $forum->course;
			$post->mailnow       = $discussion->mailnow;

			$post->id = $DB->insert_record("forum_posts", $post);

			if (!empty($cm->id) && !empty($discussion->itemid)) {
				$context = context_module::instance($cm->id);
				$text = file_save_draft_area_files($discussion->itemid, $context->id, 'mod_forum', 'post', $post->id,
                mod_forum_post_form::editor_options($context, null), $post->message);
				$DB->set_field('forum_posts', 'message', $text, array('id'=>$post->id));
			}

			$discussion->firstpost    = $post->id;
			$discussion->timemodified = $timenow;
			$discussion->usermodified = $post->userid;
			$discussion->userid       = $userid;
			$discussion->assessed     = 0;

			$post->discussion = $DB->insert_record("forum_discussions", $discussion);

			$DB->set_field("forum_posts", "discussion", $post->discussion, array("id"=>$post->id));

			if (!empty($cm->id)) {
				forum_add_attachment($post, $forum, $cm, $mform, $unused);
			}

			if (forum_tp_can_track_forums($forum) && forum_tp_is_tracked($forum)) {
				forum_tp_mark_post_read($post->userid, $post, $post->forum);
			}

			if (!empty($cm->id)) {
				forum_trigger_content_uploaded_event($post, $cm, 'forum_add_discussion');
			}


			$sql="SELECT name from mdl_forum_discussions WHERE id ='".$post->discussion."'";
			$result_name = mysql_query($sql);
			$row_name = mysql_fetch_row($result_name);
			$name = $row_name[0]."(".$row_groupid[1].")";
			$sql="UPDATE mdl_forum_discussions SET groupid='".$row_groupid[0]."', name='".$name."' WHERE id ='".$post->discussion."'";
			//$sql="UPDATE mdl_forum SET type='".$row_groupid[0]."' WHERE id =79";
			mysql_query($sql);
		}

	}else{
		$post = new stdClass();
		$post->discussion    = 0;
		$post->parent        = 0;
		$post->userid        = $userid;
		$post->created       = $timenow;
		$post->modified      = $timenow;
		$post->mailed        = FORUM_MAILED_PENDING;
		$post->subject       = $discussion->name;
		$post->message       = $discussion->message;
		$post->messageformat = $discussion->messageformat;
		$post->messagetrust  = $discussion->messagetrust;
		$post->attachments   = isset($discussion->attachments) ? $discussion->attachments : null;
		$post->forum         = $forum->id;
		$post->course        = $forum->course;
		$post->mailnow       = $discussion->mailnow;

		$post->id = $DB->insert_record("forum_posts", $post);

		if (!empty($cm->id) && !empty($discussion->itemid)) {
			$context = context_module::instance($cm->id);
			$text = file_save_draft_area_files($discussion->itemid, $context->id, 'mod_forum', 'post', $post->id,
            mod_forum_post_form::editor_options($context, null), $post->message);
			$DB->set_field('forum_posts', 'message', $text, array('id'=>$post->id));
		}

		$discussion->firstpost    = $post->id;
		$discussion->timemodified = $timenow;
		$discussion->usermodified = $post->userid;
		$discussion->userid       = $userid;
		$discussion->assessed     = 0;

		$post->discussion = $DB->insert_record("forum_discussions", $discussion);

		$DB->set_field("forum_posts", "discussion", $post->discussion, array("id"=>$post->id));

		if (!empty($cm->id)) {
			forum_add_attachment($post, $forum, $cm, $mform, $unused);
		}

		if (forum_tp_can_track_forums($forum) && forum_tp_is_tracked($forum)) {
			forum_tp_mark_post_read($post->userid, $post, $post->forum);
		}

		if (!empty($cm->id)) {
			forum_trigger_content_uploaded_event($post, $cm, 'forum_add_discussion');
		}
	}

    return $post->discussion;
}

function forum_delete_discussion($discussion, $fulldelete, $course, $cm, $forum) {
    global $DB, $CFG;
    require_once($CFG->libdir.'/completionlib.php');

    $result = true;

    if ($posts = $DB->get_records("forum_posts", array("discussion" => $discussion->id))) {
        foreach ($posts as $post) {
            $post->course = $discussion->course;
            $post->forum  = $discussion->forum;
            if (!forum_delete_post($post, 'ignore', $course, $cm, $forum, $fulldelete)) {
                $result = false;
            }
        }
    }

    forum_tp_delete_read_records(-1, -1, $discussion->id);

        $DB->delete_records('forum_discussion_subs', array('discussion' => $discussion->id));
    if (!$DB->delete_records("forum_discussions", array("id" => $discussion->id))) {
        $result = false;
    }

            if (!$fulldelete) {
        $completion = new completion_info($course);
        if ($completion->is_enabled($cm) == COMPLETION_TRACKING_AUTOMATIC &&
           ($forum->completiondiscussions || $forum->completionreplies || $forum->completionposts)) {
            $completion->update_state($cm, COMPLETION_INCOMPLETE, $discussion->userid);
        }
    }

    return $result;
}



function forum_delete_post($post, $children, $course, $cm, $forum, $skipcompletion=false) {
    global $DB, $CFG, $USER;
    require_once($CFG->libdir.'/completionlib.php');

    $context = context_module::instance($cm->id);

    if ($children !== 'ignore' && ($childposts = $DB->get_records('forum_posts', array('parent'=>$post->id)))) {
       if ($children) {
           foreach ($childposts as $childpost) {
               forum_delete_post($childpost, true, $course, $cm, $forum, $skipcompletion);
           }
       } else {
           return false;
       }
    }

        require_once($CFG->dirroot.'/rating/lib.php');
    $delopt = new stdClass;
    $delopt->contextid = $context->id;
    $delopt->component = 'mod_forum';
    $delopt->ratingarea = 'post';
    $delopt->itemid = $post->id;
    $rm = new rating_manager();
    $rm->delete_ratings($delopt);

        $fs = get_file_storage();
    $fs->delete_area_files($context->id, 'mod_forum', 'attachment', $post->id);
    $fs->delete_area_files($context->id, 'mod_forum', 'post', $post->id);

        if (!empty($CFG->enablerssfeeds)) {
        require_once($CFG->dirroot.'/mod/forum/rsslib.php');
        forum_rss_delete_file($forum);
    }

    if ($DB->delete_records("forum_posts", array("id" => $post->id))) {

        forum_tp_delete_read_records(-1, $post->id);

            forum_discussion_update_last_post($post->discussion);


        if (!$skipcompletion) {
            $completion = new completion_info($course);
            if ($completion->is_enabled($cm) == COMPLETION_TRACKING_AUTOMATIC &&
               ($forum->completiondiscussions || $forum->completionreplies || $forum->completionposts)) {
                $completion->update_state($cm, COMPLETION_INCOMPLETE, $post->userid);
            }
        }

        $params = array(
            'context' => $context,
            'objectid' => $post->id,
            'other' => array(
                'discussionid' => $post->discussion,
                'forumid' => $forum->id,
                'forumtype' => $forum->type,
            )
        );
        if ($post->userid !== $USER->id) {
            $params['relateduserid'] = $post->userid;
        }
        $event = \mod_forum\event\post_deleted::create($params);
        $event->add_record_snapshot('forum_posts', $post);
        $event->trigger();

        return true;
    }
    return false;
}


function forum_trigger_content_uploaded_event($post, $cm, $name) {
    $context = context_module::instance($cm->id);
    $fs = get_file_storage();
    $files = $fs->get_area_files($context->id, 'mod_forum', 'attachment', $post->id, "timemodified", false);
    $params = array(
        'context' => $context,
        'objectid' => $post->id,
        'other' => array(
            'content' => $post->message,
            'pathnamehashes' => array_keys($files),
            'discussionid' => $post->discussion,
            'triggeredfrom' => $name,
        )
    );
    $event = \mod_forum\event\assessable_uploaded::create($params);
    $event->trigger();
    return true;
}


function forum_count_replies($post, $children=true) {
    global $DB;
    $count = 0;

    if ($children) {
        if ($childposts = $DB->get_records('forum_posts', array('parent' => $post->id))) {
           foreach ($childposts as $childpost) {
               $count ++;                                  $count += forum_count_replies($childpost, true);
           }
        }
    } else {
        $count += $DB->count_records('forum_posts', array('parent' => $post->id));
    }

    return $count;
}


function forum_post_subscription($fromform, $forum, $discussion) {
    global $USER;

    if (\mod_forum\subscriptions::is_forcesubscribed($forum)) {
        return "";
    } else if (\mod_forum\subscriptions::subscription_disabled($forum)) {
        $subscribed = \mod_forum\subscriptions::is_subscribed($USER->id, $forum);
        if ($subscribed && !has_capability('moodle/course:manageactivities', context_course::instance($forum->course), $USER->id)) {
                        \mod_forum\subscriptions::unsubscribe_user($USER->id, $forum);
        }
        return "";
    }

    $info = new stdClass();
    $info->name  = fullname($USER);
    $info->discussion = format_string($discussion->name);
    $info->forum = format_string($forum->name);

    if (isset($fromform->discussionsubscribe) && $fromform->discussionsubscribe) {
        if ($result = \mod_forum\subscriptions::subscribe_user_to_discussion($USER->id, $discussion)) {
            return html_writer::tag('p', get_string('discussionnowsubscribed', 'forum', $info));
        }
    } else {
        if ($result = \mod_forum\subscriptions::unsubscribe_user_from_discussion($USER->id, $discussion)) {
            return html_writer::tag('p', get_string('discussionnownotsubscribed', 'forum', $info));
        }
    }

    return '';
}


function forum_get_subscribe_link($forum, $context, $messages = array(), $cantaccessagroup = false, $fakelink=true, $backtoindex=false, $subscribed_forums=null) {
    global $CFG, $USER, $PAGE, $OUTPUT;
    $defaultmessages = array(
        'subscribed' => get_string('unsubscribe', 'forum'),
        'unsubscribed' => get_string('subscribe', 'forum'),
        'cantaccessgroup' => get_string('no'),
        'forcesubscribed' => get_string('everyoneissubscribed', 'forum'),
        'cantsubscribe' => get_string('disallowsubscribe','forum')
    );
    $messages = $messages + $defaultmessages;

    if (\mod_forum\subscriptions::is_forcesubscribed($forum)) {
        return $messages['forcesubscribed'];
    } else if (\mod_forum\subscriptions::subscription_disabled($forum) &&
            !has_capability('mod/forum:managesubscriptions', $context)) {
        return $messages['cantsubscribe'];
    } else if ($cantaccessagroup) {
        return $messages['cantaccessgroup'];
    } else {
        if (!is_enrolled($context, $USER, '', true)) {
            return '';
        }

        $subscribed = \mod_forum\subscriptions::is_subscribed($USER->id, $forum);
        if ($subscribed) {
            $linktext = $messages['subscribed'];
            $linktitle = get_string('subscribestop', 'forum');
        } else {
            $linktext = $messages['unsubscribed'];
            $linktitle = get_string('subscribestart', 'forum');
        }

        $options = array();
        if ($backtoindex) {
            $backtoindexlink = '&amp;backtoindex=1';
            $options['backtoindex'] = 1;
        } else {
            $backtoindexlink = '';
        }
        $link = '';

        if ($fakelink) {
            $PAGE->requires->js('/mod/forum/forum.js');
            $PAGE->requires->js_function_call('forum_produce_subscribe_link', array($forum->id, $backtoindexlink, $linktext, $linktitle));
            $link = "<noscript>";
        }
        $options['id'] = $forum->id;
        $options['sesskey'] = sesskey();
        $url = new moodle_url('/mod/forum/subscribe.php', $options);
        $link .= $OUTPUT->single_button($url, $linktext, 'get', array('title'=>$linktitle));
        if ($fakelink) {
            $link .= '</noscript>';
        }

        return $link;
    }
}


function forum_user_has_posted_discussion($forumid, $userid, $groupid = null) {
    global $CFG, $DB;

    $sql = "SELECT 'x'
              FROM {forum_discussions} d, {forum_posts} p
             WHERE d.forum = ? AND p.discussion = d.id AND p.parent = 0 AND p.userid = ?";

    $params = [$forumid, $userid];

    if ($groupid) {
        $sql .= " AND d.groupid = ?";
        $params[] = $groupid;
    }

    return $DB->record_exists_sql($sql, $params);
}


function forum_discussions_user_has_posted_in($forumid, $userid) {
    global $CFG, $DB;

    $haspostedsql = "SELECT d.id AS id,
                            d.*
                       FROM {forum_posts} p,
                            {forum_discussions} d
                      WHERE p.discussion = d.id
                        AND d.forum = ?
                        AND p.userid = ?";

    return $DB->get_records_sql($haspostedsql, array($forumid, $userid));
}


function forum_user_has_posted($forumid, $did, $userid) {
    global $DB;

    if (empty($did)) {
                $sql = "SELECT 'x'
                  FROM {forum_posts} p
                  JOIN {forum_discussions} d ON d.id = p.discussion
                 WHERE p.userid = :userid AND d.forum = :forumid";
        return $DB->record_exists_sql($sql, array('forumid'=>$forumid,'userid'=>$userid));
    } else {
        return $DB->record_exists('forum_posts', array('discussion'=>$did,'userid'=>$userid));
    }
}


function forum_get_user_posted_time($did, $userid) {
    global $DB;

    $posttime = $DB->get_field('forum_posts', 'MIN(created)', array('userid'=>$userid, 'discussion'=>$did));
    if (empty($posttime)) {
        return false;
    }
    return $posttime;
}


function forum_user_can_post_discussion($forum, $currentgroup=null, $unused=-1, $cm=NULL, $context=NULL) {
    global $USER;

        if (isguestuser() or !isloggedin()) {
        return false;
    }

    if (!$cm) {
        debugging('missing cm', DEBUG_DEVELOPER);
        if (!$cm = get_coursemodule_from_instance('forum', $forum->id, $forum->course)) {
            print_error('invalidcoursemodule');
        }
    }

    if (!$context) {
        $context = context_module::instance($cm->id);
    }

    if ($currentgroup === null) {
        $currentgroup = groups_get_activity_group($cm);
    }

    $groupmode = groups_get_activity_groupmode($cm);

    if ($forum->type == 'news') {
        $capname = 'mod/forum:addnews';
    } else if ($forum->type == 'qanda') {
        $capname = 'mod/forum:addquestion';
    } else {
        $capname = 'mod/forum:startdiscussion';
    }

    if (!has_capability($capname, $context)) {
        return false;
    }



    if ($forum->type == 'single') {
        return false;
    }

    if ($forum->type == 'eachuser') {
        if (forum_user_has_posted_discussion($forum->id, $USER->id, $currentgroup)) {
            return false;
        }
    }

    if (!$groupmode or has_capability('moodle/site:accessallgroups', $context)) {
        return true;
    }

    if ($currentgroup) {
        return groups_is_member($currentgroup);
    } else {
                        return false;
    }
}


function forum_user_can_post($forum, $discussion, $user=NULL, $cm=NULL, $course=NULL, $context=NULL) {
    global $USER, $DB;
    if (empty($user)) {
        $user = $USER;
    }

        if (isguestuser($user) or empty($user->id)) {
        return false;
    }

    if (!isset($discussion->groupid)) {
        debugging('incorrect discussion parameter', DEBUG_DEVELOPER);
        return false;
    }

    if (!$cm) {
        debugging('missing cm', DEBUG_DEVELOPER);
        if (!$cm = get_coursemodule_from_instance('forum', $forum->id, $forum->course)) {
            print_error('invalidcoursemodule');
        }
    }

    if (!$course) {
        debugging('missing course', DEBUG_DEVELOPER);
        if (!$course = $DB->get_record('course', array('id' => $forum->course))) {
            print_error('invalidcourseid');
        }
    }

    if (!$context) {
        $context = context_module::instance($cm->id);
    }

        if (!is_viewing($context, $user->id) and !is_enrolled($context, $user->id, '', true)) {
        return false;
    }

    if ($forum->type == 'news') {
        $capname = 'mod/forum:replynews';
    } else {
        $capname = 'mod/forum:replypost';
    }

    if (!has_capability($capname, $context, $user->id)) {
        return false;
    }

    if (!$groupmode = groups_get_activity_groupmode($cm, $course)) {
        return true;
    }

    if (has_capability('moodle/site:accessallgroups', $context)) {
        return true;
    }

    if ($groupmode == VISIBLEGROUPS) {
        if ($discussion->groupid == -1) {
                        return true;
        }
        return groups_is_member($discussion->groupid);

    } else {
                if ($discussion->groupid == -1) {
            return false;
        }
        return groups_is_member($discussion->groupid);
    }
}


function forum_user_can_see_timed_discussion($discussion, $user, $context) {
    global $CFG;

        if (!empty($CFG->forum_enabletimedposts)) {
        $time = time();
        if (($discussion->timestart != 0 && $discussion->timestart > $time)
            || ($discussion->timeend != 0 && $discussion->timeend < $time)) {
            if (!has_capability('mod/forum:viewhiddentimedposts', $context, $user->id)) {
                return false;
            }
        }
    }

    return true;
}


function forum_user_can_see_group_discussion($discussion, $cm, $context) {

        if ($discussion->groupid > 0) {
        $groupmode = groups_get_activity_groupmode($cm);
        if ($groupmode == SEPARATEGROUPS) {
            return groups_is_member($discussion->groupid) || has_capability('moodle/site:accessallgroups', $context);
        }
    }

    return true;
}


function forum_user_can_see_discussion($forum, $discussion, $context, $user=NULL) {
    global $USER, $DB;

    if (empty($user) || empty($user->id)) {
        $user = $USER;
    }

        if (is_numeric($forum)) {
        debugging('missing full forum', DEBUG_DEVELOPER);
        if (!$forum = $DB->get_record('forum',array('id'=>$forum))) {
            return false;
        }
    }
    if (is_numeric($discussion)) {
        debugging('missing full discussion', DEBUG_DEVELOPER);
        if (!$discussion = $DB->get_record('forum_discussions',array('id'=>$discussion))) {
            return false;
        }
    }
    if (!$cm = get_coursemodule_from_instance('forum', $forum->id, $forum->course)) {
        print_error('invalidcoursemodule');
    }

    if (!has_capability('mod/forum:viewdiscussion', $context)) {
        return false;
    }

    if (!forum_user_can_see_timed_discussion($discussion, $user, $context)) {
        return false;
    }

    if (!forum_user_can_see_group_discussion($discussion, $cm, $context)) {
        return false;
    }

    return true;
}


function forum_user_can_see_post($forum, $discussion, $post, $user=NULL, $cm=NULL) {
    global $CFG, $USER, $DB;

        $modcontext = context_module::instance($cm->id);

        if (is_numeric($forum)) {
        debugging('missing full forum', DEBUG_DEVELOPER);
        if (!$forum = $DB->get_record('forum',array('id'=>$forum))) {
            return false;
        }
    }

    if (is_numeric($discussion)) {
        debugging('missing full discussion', DEBUG_DEVELOPER);
        if (!$discussion = $DB->get_record('forum_discussions',array('id'=>$discussion))) {
            return false;
        }
    }
    if (is_numeric($post)) {
        debugging('missing full post', DEBUG_DEVELOPER);
        if (!$post = $DB->get_record('forum_posts',array('id'=>$post))) {
            return false;
        }
    }

    if (!isset($post->id) && isset($post->parent)) {
        $post->id = $post->parent;
    }

    if (!$cm) {
        debugging('missing cm', DEBUG_DEVELOPER);
        if (!$cm = get_coursemodule_from_instance('forum', $forum->id, $forum->course)) {
            print_error('invalidcoursemodule');
        }
    }

    if (empty($user) || empty($user->id)) {
        $user = $USER;
    }

    $canviewdiscussion = !empty($cm->cache->caps['mod/forum:viewdiscussion']) || has_capability('mod/forum:viewdiscussion', $modcontext, $user->id);
    if (!$canviewdiscussion && !has_all_capabilities(array('moodle/user:viewdetails', 'moodle/user:readuserposts'), context_user::instance($post->userid))) {
        return false;
    }

    if (isset($cm->uservisible)) {
        if (!$cm->uservisible) {
            return false;
        }
    } else {
        if (!\core_availability\info_module::is_user_visible($cm, $user->id, false)) {
            return false;
        }
    }

    if (!forum_user_can_see_timed_discussion($discussion, $user, $modcontext)) {
        return false;
    }

    if (!forum_user_can_see_group_discussion($discussion, $cm, $modcontext)) {
        return false;
    }

    if ($forum->type == 'qanda') {
        $firstpost = forum_get_firstpost_from_discussion($discussion->id);
        $userfirstpost = forum_get_user_posted_time($discussion->id, $user->id);

        return (($userfirstpost !== false && (time() - $userfirstpost >= $CFG->maxeditingtime)) ||
                $firstpost->id == $post->id || $post->userid == $user->id || $firstpost->userid == $user->id ||
                has_capability('mod/forum:viewqandawithoutposting', $modcontext, $user->id));
    }
    return true;
}



function forum_print_latest_discussions($course, $forum, $maxdiscussions = -1, $displayformat = 'plain', $sort = '',
                                        $currentgroup = -1, $groupmode = -1, $page = -1, $perpage = 100, $cm = null) {
    global $CFG, $USER, $OUTPUT;

    if (!$cm) {
        if (!$cm = get_coursemodule_from_instance('forum', $forum->id, $forum->course)) {
            print_error('invalidcoursemodule');
        }
    }
    $context = context_module::instance($cm->id);

    if (empty($sort)) {
        $sort = forum_get_default_sort_order();
    }

    $olddiscussionlink = false;

     if ($perpage <= 0) {
        $perpage = 0;
        $page    = -1;
    }

    if ($maxdiscussions == 0) {
                $page    = -1;
        $perpage = 0;
        if ($displayformat == 'plain') {
            $displayformat = 'header';          }

    } else if ($maxdiscussions > 0) {
        $page    = -1;
        $perpage = $maxdiscussions;
    }

    $fullpost = false;
    if ($displayformat == 'plain') {
        $fullpost = true;
    }



    if ($currentgroup == -1 or $groupmode == -1) {
        $groupmode    = groups_get_activity_groupmode($cm, $course);
        $currentgroup = groups_get_activity_group($cm);
    }

    $groups = array();

    $canstart = forum_user_can_post_discussion($forum, $currentgroup, $groupmode, $cm, $context);
    if (!$canstart and $forum->type !== 'news') {
        if (isguestuser() or !isloggedin()) {
            $canstart = true;
        }
        if (!is_enrolled($context) and !is_viewing($context)) {
                                                $canstart = enrol_selfenrol_available($course->id);
        }
    }

    if ($canstart) {
        echo '<div class="singlebutton forumaddnew">';
        echo "<form id=\"newdiscussionform\" method=\"get\" action=\"$CFG->wwwroot/mod/forum/post.php\">";
        echo '<div>';
        echo "<input type=\"hidden\" name=\"forum\" value=\"$forum->id\" />";
        switch ($forum->type) {
            case 'news':
            case 'blog':
                $buttonadd = get_string('addanewtopic', 'forum');
                break;
            case 'qanda':
                $buttonadd = get_string('addanewquestion', 'forum');
                break;
            default:
				$buttonadd = get_string('addanewdiscussion', 'forum');
                break;
        }
		if($forum->type=="snifs"||"ipcs"){
			$dbhost = 'localhost:3306';
			$dbuser = 'root';
			$dbpass = 'la2391';
			$dbname = 'moodle';
			$conn = mysql_connect($dbhost, $dbuser, $dbpass) ;
			mysql_query("SET NAMES 'UTF8'");
			mysql_select_db($dbname);
			$sql = "SELECT id FROM mdl_forum_discussions WHERE forum =".$forum->id;
			$result = mysql_query($sql);
			$num = mysql_num_rows($result);
      //console($forum->type);
			if($num == 0){
				echo '<input type="submit" value="新增'.$forum->type.'討論板" />';
			}

		}else{
			echo '<input type="submit" value="'.$buttonadd.'" />';
		}
        echo '</div>';
        echo '</form>';
        echo "</div>\n";

    } else if (isguestuser() or !isloggedin() or $forum->type == 'news' or
        $forum->type == 'qanda' and !has_capability('mod/forum:addquestion', $context) or
        $forum->type != 'qanda' and !has_capability('mod/forum:startdiscussion', $context)) {

    } else if ($groupmode and !has_capability('moodle/site:accessallgroups', $context)) {
                if (!$currentgroup) {
            echo $OUTPUT->notification(get_string('cannotadddiscussionall', 'forum'));
        } else if (!groups_is_member($currentgroup)) {
            echo $OUTPUT->notification(get_string('cannotadddiscussion', 'forum'));
        }
    }


    $getuserlastmodified = ($displayformat == 'header');

    if (! $discussions = forum_get_discussions($cm, $sort, $fullpost, null, $maxdiscussions, $getuserlastmodified, $page, $perpage) ) {
        echo '<div class="forumnodiscuss">';
        if ($forum->type == 'news') {
            echo '('.get_string('nonews', 'forum').')';
        } else if ($forum->type == 'qanda') {
            echo '('.get_string('noquestions','forum').')';
        } else {
            echo '('.get_string('nodiscussions', 'forum').')';
        }
        echo "</div>\n";
        return;
    }

    if ($page != -1) {
                $numdiscussions = forum_get_discussions_count($cm);

                echo $OUTPUT->paging_bar($numdiscussions, $page, $perpage, "view.php?f=$forum->id");
        if ($numdiscussions > 1000) {
                        $replies = forum_count_discussion_replies($forum->id, $sort, $maxdiscussions, $page, $perpage);
        } else {
            $replies = forum_count_discussion_replies($forum->id);
        }

    } else {
        $replies = forum_count_discussion_replies($forum->id);

        if ($maxdiscussions > 0 and $maxdiscussions <= count($discussions)) {
            $olddiscussionlink = true;
        }
    }

    $canviewparticipants = has_capability('moodle/course:viewparticipants',$context);
    $canviewhiddentimedposts = has_capability('mod/forum:viewhiddentimedposts', $context);

    $strdatestring = get_string('strftimerecentfull');

        if ($cantrack = forum_tp_can_track_forums($forum)) {
        $forumtracked = forum_tp_is_tracked($forum);
    } else {
        $forumtracked = false;
    }

    if ($forumtracked) {
        $unreads = forum_get_discussions_unread($cm);
    } else {
        $unreads = array();
    }

    if ($displayformat == 'header') {//畫討論串表格(表頭) note by廢物和20200226
        echo '<table cellspacing="0" class="forumheaderlist">';
        echo '<thead>';
        echo '<tr>';
        echo '<th class="header topic" scope="col">'.get_string('discussion', 'forum').'</th>';
        echo '<th class="header author" colspan="2" scope="col">'.get_string('startedby', 'forum').'</th>';
        if ($groupmode > 0) {
            echo '<th class="header group" scope="col">'.get_string('group').'</th>';
        }
        if (has_capability('mod/forum:viewdiscussion', $context)) {
            echo '<th class="header replies" scope="col">'.get_string('replies', 'forum').'</th>';
                        if ($cantrack) {
                echo '<th class="header replies" scope="col">'.get_string('unread', 'forum');
                if ($forumtracked) {
                    echo '<a title="'.get_string('markallread', 'forum').
                         '" href="'.$CFG->wwwroot.'/mod/forum/markposts.php?f='.
                         $forum->id.'&amp;mark=read&amp;returnpage=view.php&amp;sesskey=' . sesskey() . '">'.
                         '<img src="'.$OUTPUT->pix_url('t/markasread') . '" class="iconsmall" alt="'.get_string('markallread', 'forum').'" /></a>';
                }
                echo '</th>';
            }
        }
        echo '<th class="header lastpost" scope="col">'.get_string('lastpost', 'forum').'</th>';
        if ((!is_guest($context, $USER) && isloggedin()) && has_capability('mod/forum:viewdiscussion', $context)) {
            if (\mod_forum\subscriptions::is_subscribable($forum)) {
                echo '<th class="header discussionsubscription" scope="col">';
                echo forum_get_discussion_subscription_icon_preloaders();
                echo '</th>';
            }
        }
        echo '</tr>';
        echo '</thead>';
        echo '<tbody>';//畫討論串表格(表身) by廢物和20200226
    }else if ($displayformat == 'newsheader') {
        echo '<table cellspacing="0" class="forumheaderlist">';
        echo '<thead>';
        echo '<tr>';
        echo '<th class="header topic" scope="col">'.get_string('discussion', 'forum').'</th>';
        echo '<th class="header lastpost" scope="col">'.get_string('lastpost', 'forum').'</th>';
        echo '</tr>';
        echo '</thead>';
        echo '<tbody>';
    }

    foreach ($discussions as $discussion) {
        if ($forum->type == 'qanda' && !has_capability('mod/forum:viewqandawithoutposting', $context) &&
            !forum_user_has_posted($forum->id, $discussion->discussion, $USER->id)) {
            $canviewparticipants = false;
        }

        if (!empty($replies[$discussion->discussion])) {
            $discussion->replies = $replies[$discussion->discussion]->replies;
            $discussion->lastpostid = $replies[$discussion->discussion]->lastpostid;
        } else {
            $discussion->replies = 0;
        }

                        if (!$forumtracked) {
            $discussion->unread = '-';
        } else if (empty($USER)) {
            $discussion->unread = 0;
        } else {
            if (empty($unreads[$discussion->discussion])) {
                $discussion->unread = 0;
            } else {
                $discussion->unread = $unreads[$discussion->discussion];
            }
        }

        if (isloggedin()) {
            $ownpost = ($discussion->userid == $USER->id);
        } else {
            $ownpost=false;
        }
                $discussion->subject = $discussion->name;

        switch ($displayformat) {
            case 'header':
                if ($groupmode > 0) {
                    if (isset($groups[$discussion->groupid])) {
                        $group = $groups[$discussion->groupid];
                    } else {
                        $group = $groups[$discussion->groupid] = groups_get_group($discussion->groupid);
                    }
                } else {
                    $group = -1;
                }
                forum_print_discussion_header($discussion, $forum, $group, $strdatestring, $cantrack, $forumtracked,
                    $canviewparticipants, $context, $canviewhiddentimedposts);
            break;
            case 'newsheader':
                forum_print_discussion_newsheader($discussion, $forum, '', $strdatestring, $cantrack, $forumtracked,
                    $canviewparticipants, $context, $canviewhiddentimedposts);
            break;
            default:
                $link = false;

                if ($discussion->replies) {
                    $link = true;
                } else {
                    $modcontext = context_module::instance($cm->id);
                    $link = forum_user_can_see_discussion($forum, $discussion, $modcontext, $USER);
                }

                $discussion->forum = $forum->id;

                forum_print_post($discussion, $discussion, $forum, $cm, $course, $ownpost, 0, $link, false,
                        '', null, true, $forumtracked);
            break;
        }
    }

    if ($displayformat == "header" || $displayformat == "newsheader") {
        echo '</tbody>';
        echo '</table>';
    }

    if ($olddiscussionlink) {
        if ($forum->type == 'news') {
            $strolder = get_string('oldertopics', 'forum');
        } else {
            $strolder = get_string('olderdiscussions', 'forum');
        }
        echo '<div class="forumolddiscuss">';
        echo '<a href="'.$CFG->wwwroot.'/mod/forum/view.php?f='.$forum->id.'&amp;showall=1">';
        echo $strolder.'</a> ...</div>';
    }

    if ($page != -1) {         echo $OUTPUT->paging_bar($numdiscussions, $page, $perpage, "view.php?f=$forum->id");
    }
}



function forum_print_discussion($course, $cm, $forum, $discussion, $post, $mode, $canreply=NULL, $canrate=false) {
    global $USER, $CFG;

    require_once($CFG->dirroot.'/rating/lib.php');

    $ownpost = (isloggedin() && $USER->id == $post->userid);

    $modcontext = context_module::instance($cm->id);
    if ($canreply === NULL) {
        $reply = forum_user_can_post($forum, $discussion, $USER, $cm, $course, $modcontext);
    } else {
        $reply = $canreply;
    }

        $cm->cache = new stdClass;
    $cm->cache->groups      = groups_get_all_groups($course->id, 0, $cm->groupingid);
    $cm->cache->usersgroups = array();

    $posters = array();

        if ($mode == FORUM_MODE_FLATNEWEST) {
        $sort = "p.created DESC";
    } else {
        $sort = "p.created ASC";
    }

    $forumtracked = forum_tp_is_tracked($forum);
    $posts = forum_get_all_discussion_posts($discussion->id, $sort, $forumtracked);
    $post = $posts[$post->id];

    foreach ($posts as $pid=>$p) {
        $posters[$p->userid] = $p->userid;
    }

        if ($postersgroups = groups_get_all_groups($course->id, $posters, $cm->groupingid, 'gm.id, gm.groupid, gm.userid')) {
        foreach($postersgroups as $pg) {
            if (!isset($cm->cache->usersgroups[$pg->userid])) {
                $cm->cache->usersgroups[$pg->userid] = array();
            }
            $cm->cache->usersgroups[$pg->userid][$pg->groupid] = $pg->groupid;
        }
        unset($postersgroups);
    }

        if ($forum->assessed != RATING_AGGREGATE_NONE) {
        $ratingoptions = new stdClass;
        $ratingoptions->context = $modcontext;
        $ratingoptions->component = 'mod_forum';
        $ratingoptions->ratingarea = 'post';
        $ratingoptions->items = $posts;
        $ratingoptions->aggregate = $forum->assessed;        $ratingoptions->scaleid = $forum->scale;
        $ratingoptions->userid = $USER->id;


        if ($forum->type == 'single' or !$discussion->id) {
            $ratingoptions->returnurl = "$CFG->wwwroot/mod/forum/view.php?id=$cm->id";
        } else {
            $ratingoptions->returnurl = "$CFG->wwwroot/mod/forum/discuss.php?d=$discussion->id";
        }
        $ratingoptions->assesstimestart = $forum->assesstimestart;
        $ratingoptions->assesstimefinish = $forum->assesstimefinish;

        $rm = new rating_manager();
        $posts = $rm->get_ratings($ratingoptions);
    }


    $post->forum = $forum->id;       $post->forumtype = $forum->type;

    $post->subject = format_string($post->subject);

    $postread = !empty($post->postread);

    forum_print_post($post, $discussion, $forum, $cm, $course, $ownpost, $reply, false,
                         '', '', $postread, true, $forumtracked);

    switch ($mode) {
        case FORUM_MODE_FLATOLDEST :
        case FORUM_MODE_FLATNEWEST :
        default:
            forum_print_posts_flat($course, $cm, $forum, $discussion, $post, $mode, $reply, $forumtracked, $posts);
            break;

        case FORUM_MODE_THREADED :
            forum_print_posts_threaded($course, $cm, $forum, $discussion, $post, 0, $reply, $forumtracked, $posts);
            break;

        case FORUM_MODE_NESTED :
            forum_print_posts_nested($course, $cm, $forum, $discussion, $post, $reply, $forumtracked, $posts);
            break;
    }
}



function forum_print_posts_flat($course, &$cm, $forum, $discussion, $post, $mode, $reply, $forumtracked, $posts) {
    global $USER, $CFG;

    $link  = false;

    if ($mode == FORUM_MODE_FLATNEWEST) {
        $sort = "ORDER BY created DESC";
    } else {
        $sort = "ORDER BY created ASC";
    }

    foreach ($posts as $post) {
        if (!$post->parent) {
            continue;
        }
        $post->subject = format_string($post->subject);
        $ownpost = ($USER->id == $post->userid);

        $postread = !empty($post->postread);

        forum_print_post($post, $discussion, $forum, $cm, $course, $ownpost, $reply, $link,
                             '', '', $postread, true, $forumtracked);
    }
}


function forum_print_posts_threaded($course, &$cm, $forum, $discussion, $parent, $depth, $reply, $forumtracked, $posts) {
    global $USER, $CFG;

    $link  = false;

    if (!empty($posts[$parent->id]->children)) {
        $posts = $posts[$parent->id]->children;

        $modcontext       = context_module::instance($cm->id);
        $canviewfullnames = has_capability('moodle/site:viewfullnames', $modcontext);

        foreach ($posts as $post) {

            echo '<div class="indent">';
            if ($depth > 0) {
                $ownpost = ($USER->id == $post->userid);
                $post->subject = format_string($post->subject);

                $postread = !empty($post->postread);

                forum_print_post($post, $discussion, $forum, $cm, $course, $ownpost, $reply, $link,
                                     '', '', $postread, true, $forumtracked);
            } else {
                if (!forum_user_can_see_post($forum, $discussion, $post, NULL, $cm)) {
                    echo "</div>\n";
                    continue;
                }
                $by = new stdClass();
                $by->name = fullname($post, $canviewfullnames);
                $by->date = userdate($post->modified);

                if ($forumtracked) {
                    if (!empty($post->postread)) {
                        $style = '<span class="forumthread read">';
                    } else {
                        $style = '<span class="forumthread unread">';
                    }
                } else {
                    $style = '<span class="forumthread">';
                }
                echo $style."<a name=\"$post->id\"></a>".
                     "<a href=\"discuss.php?d=$post->discussion&amp;parent=$post->id\">".format_string($post->subject,true)."</a> ";
                print_string("bynameondate", "forum", $by);
                echo "</span>";
            }

            forum_print_posts_threaded($course, $cm, $forum, $discussion, $post, $depth-1, $reply, $forumtracked, $posts);
            echo "</div>\n";
        }
    }
}


function forum_print_posts_nested($course, &$cm, $forum, $discussion, $parent, $reply, $forumtracked, $posts) {
    global $USER, $CFG;

    $link  = false;

    if (!empty($posts[$parent->id]->children)) {
        $posts = $posts[$parent->id]->children;

        foreach ($posts as $post) {

            echo '<div class="indent">';
            if (!isloggedin()) {
                $ownpost = false;
            } else {
                $ownpost = ($USER->id == $post->userid);
            }

            $post->subject = format_string($post->subject);
            $postread = !empty($post->postread);

            forum_print_post($post, $discussion, $forum, $cm, $course, $ownpost, $reply, $link,
                                 '', '', $postread, true, $forumtracked);
            forum_print_posts_nested($course, $cm, $forum, $discussion, $post, $reply, $forumtracked, $posts);
            echo "</div>\n";
        }
    }
}


function forum_get_recent_mod_activity(&$activities, &$index, $timestart, $courseid, $cmid, $userid=0, $groupid=0)  {
    global $CFG, $COURSE, $USER, $DB;

    if ($COURSE->id == $courseid) {
        $course = $COURSE;
    } else {
        $course = $DB->get_record('course', array('id' => $courseid));
    }

    $modinfo = get_fast_modinfo($course);

    $cm = $modinfo->cms[$cmid];
    $params = array($timestart, $cm->instance);

    if ($userid) {
        $userselect = "AND u.id = ?";
        $params[] = $userid;
    } else {
        $userselect = "";
    }

    if ($groupid) {
        $groupselect = "AND d.groupid = ?";
        $params[] = $groupid;
    } else {
        $groupselect = "";
    }

    $allnames = get_all_user_name_fields(true, 'u');
    if (!$posts = $DB->get_records_sql("SELECT p.*, f.type AS forumtype, d.forum, d.groupid,
                                              d.timestart, d.timeend, d.userid AS duserid,
                                              $allnames, u.email, u.picture, u.imagealt, u.email
                                         FROM {forum_posts} p
                                              JOIN {forum_discussions} d ON d.id = p.discussion
                                              JOIN {forum} f             ON f.id = d.forum
                                              JOIN {user} u              ON u.id = p.userid
                                        WHERE p.created > ? AND f.id = ?
                                              $userselect $groupselect
                                     ORDER BY p.id ASC", $params)) {          return;
    }

    $groupmode       = groups_get_activity_groupmode($cm, $course);
    $cm_context      = context_module::instance($cm->id);
    $viewhiddentimed = has_capability('mod/forum:viewhiddentimedposts', $cm_context);
    $accessallgroups = has_capability('moodle/site:accessallgroups', $cm_context);

    $printposts = array();
    foreach ($posts as $post) {

        if (!empty($CFG->forum_enabletimedposts) and $USER->id != $post->duserid
          and (($post->timestart > 0 and $post->timestart > time()) or ($post->timeend > 0 and $post->timeend < time()))) {
            if (!$viewhiddentimed) {
                continue;
            }
        }

        if ($groupmode) {
            if ($post->groupid == -1 or $groupmode == VISIBLEGROUPS or $accessallgroups) {
                            } else {
                                if (isguestuser()) {
                                        continue;
                }

                if (!in_array($post->groupid, $modinfo->get_groups($cm->groupingid))) {
                    continue;
                }
            }
        }

        $printposts[] = $post;
    }

    if (!$printposts) {
        return;
    }

    $aname = format_string($cm->name,true);

    foreach ($printposts as $post) {
        $tmpactivity = new stdClass();

        $tmpactivity->type         = 'forum';
        $tmpactivity->cmid         = $cm->id;
        $tmpactivity->name         = $aname;
        $tmpactivity->sectionnum   = $cm->sectionnum;
        $tmpactivity->timestamp    = $post->modified;

        $tmpactivity->content = new stdClass();
        $tmpactivity->content->id         = $post->id;
        $tmpactivity->content->discussion = $post->discussion;
        $tmpactivity->content->subject    = format_string($post->subject);
        $tmpactivity->content->parent     = $post->parent;

        $tmpactivity->user = new stdClass();
        $additionalfields = array('id' => 'userid', 'picture', 'imagealt', 'email');
        $additionalfields = explode(',', user_picture::fields());
        $tmpactivity->user = username_load_fields_from_object($tmpactivity->user, $post, null, $additionalfields);
        $tmpactivity->user->id = $post->userid;

        $activities[$index++] = $tmpactivity;
    }

    return;
}


function forum_print_recent_mod_activity($activity, $courseid, $detail, $modnames, $viewfullnames) {
    global $CFG, $OUTPUT;

    if ($activity->content->parent) {
        $class = 'reply';
    } else {
        $class = 'discussion';
    }

    echo '<table border="0" cellpadding="3" cellspacing="0" class="forum-recent">';

    echo "<tr><td class=\"userpicture\" valign=\"top\">";
    echo $OUTPUT->user_picture($activity->user, array('courseid'=>$courseid));
    echo "</td><td class=\"$class\">";

    if ($activity->content->parent) {
        $class = 'title';
    } else {
                $class = 'title bold';
    }
    echo "<div class=\"{$class}\">";
    if ($detail) {
        $aname = s($activity->name);
        echo "<img src=\"" . $OUTPUT->pix_url('icon', $activity->type) . "\" ".
             "class=\"icon\" alt=\"{$aname}\" />";
    }
    echo "<a href=\"$CFG->wwwroot/mod/forum/discuss.php?d={$activity->content->discussion}"
         ."#p{$activity->content->id}\">{$activity->content->subject}</a>";
    echo '</div>';

    echo '<div class="user">';
    $fullname = fullname($activity->user, $viewfullnames);
    echo "<a href=\"$CFG->wwwroot/user/view.php?id={$activity->user->id}&amp;course=$courseid\">"
         ."{$fullname}</a> - ".userdate($activity->timestamp);
    echo '</div>';
      echo "</td></tr></table>";

    return;
}


function forum_change_discussionid($postid, $discussionid) {
    global $DB;
    $DB->set_field('forum_posts', 'discussion', $discussionid, array('id' => $postid));
    if ($posts = $DB->get_records('forum_posts', array('parent' => $postid))) {
        foreach ($posts as $post) {
            forum_change_discussionid($post->id, $discussionid);
        }
    }
    return true;
}


function forum_update_subscriptions_button($courseid, $forumid) {
    global $CFG, $USER;

    if (!empty($USER->subscriptionsediting)) {
        $string = get_string('turneditingoff');
        $edit = "off";
    } else {
        $string = get_string('turneditingon');
        $edit = "on";
    }

    return "<form method=\"get\" action=\"$CFG->wwwroot/mod/forum/subscribers.php\">".
           "<input type=\"hidden\" name=\"id\" value=\"$forumid\" />".
           "<input type=\"hidden\" name=\"edit\" value=\"$edit\" />".
           "<input type=\"submit\" value=\"$string\" /></form>";
}



function forum_tp_mark_posts_read($user, $postids) {
    global $CFG, $DB;

    if (!forum_tp_can_track_forums(false, $user)) {
        return true;
    }

    $status = true;

    $now = time();
    $cutoffdate = $now - ($CFG->forum_oldpostdays * 24 * 3600);

    if (empty($postids)) {
        return true;

    } else if (count($postids) > 200) {
        while ($part = array_splice($postids, 0, 200)) {
            $status = forum_tp_mark_posts_read($user, $part) && $status;
        }
        return $status;
    }

    list($usql, $postidparams) = $DB->get_in_or_equal($postids, SQL_PARAMS_NAMED, 'postid');

    $insertparams = array(
        'userid1' => $user->id,
        'userid2' => $user->id,
        'userid3' => $user->id,
        'firstread' => $now,
        'lastread' => $now,
        'cutoffdate' => $cutoffdate,
    );
    $params = array_merge($postidparams, $insertparams);

    if ($CFG->forum_allowforcedreadtracking) {
        $trackingsql = "AND (f.trackingtype = ".FORUM_TRACKING_FORCED."
                        OR (f.trackingtype = ".FORUM_TRACKING_OPTIONAL." AND tf.id IS NULL))";
    } else {
        $trackingsql = "AND ((f.trackingtype = ".FORUM_TRACKING_OPTIONAL."  OR f.trackingtype = ".FORUM_TRACKING_FORCED.")
                            AND tf.id IS NULL)";
    }

        $sql = "INSERT INTO {forum_read} (userid, postid, discussionid, forumid, firstread, lastread)

            SELECT :userid1, p.id, p.discussion, d.forum, :firstread, :lastread
                FROM {forum_posts} p
                    JOIN {forum_discussions} d       ON d.id = p.discussion
                    JOIN {forum} f                   ON f.id = d.forum
                    LEFT JOIN {forum_track_prefs} tf ON (tf.userid = :userid2 AND tf.forumid = f.id)
                    LEFT JOIN {forum_read} fr        ON (
                            fr.userid = :userid3
                        AND fr.postid = p.id
                        AND fr.discussionid = d.id
                        AND fr.forumid = f.id
                    )
                WHERE p.id $usql
                    AND p.modified >= :cutoffdate
                    $trackingsql
                    AND fr.id IS NULL";

    $status = $DB->execute($sql, $params) && $status;

        $updateparams = array(
        'userid' => $user->id,
        'lastread' => $now,
    );
    $params = array_merge($postidparams, $updateparams);
    $status = $DB->set_field_select('forum_read', 'lastread', $now, '
                userid      =  :userid
            AND lastread    <> :lastread
            AND postid      ' . $usql,
            $params) && $status;

    return $status;
}


function forum_tp_add_read_record($userid, $postid) {
    global $CFG, $DB;

    $now = time();
    $cutoffdate = $now - ($CFG->forum_oldpostdays * 24 * 3600);

    if (!$DB->record_exists('forum_read', array('userid' => $userid, 'postid' => $postid))) {
        $sql = "INSERT INTO {forum_read} (userid, postid, discussionid, forumid, firstread, lastread)

                SELECT ?, p.id, p.discussion, d.forum, ?, ?
                  FROM {forum_posts} p
                       JOIN {forum_discussions} d ON d.id = p.discussion
                 WHERE p.id = ? AND p.modified >= ?";
        return $DB->execute($sql, array($userid, $now, $now, $postid, $cutoffdate));

    } else {
        $sql = "UPDATE {forum_read}
                   SET lastread = ?
                 WHERE userid = ? AND postid = ?";
        return $DB->execute($sql, array($now, $userid, $userid));
    }
}


function forum_tp_mark_post_read($userid, $post, $forumid) {
    if (!forum_tp_is_post_old($post)) {
        return forum_tp_add_read_record($userid, $post->id);
    } else {
        return true;
    }
}


function forum_tp_mark_forum_read($user, $forumid, $groupid=false) {
    global $CFG, $DB;

    $cutoffdate = time() - ($CFG->forum_oldpostdays*24*60*60);

    $groupsel = "";
    $params = array($user->id, $forumid, $cutoffdate);

    if ($groupid !== false) {
        $groupsel = " AND (d.groupid = ? OR d.groupid = -1)";
        $params[] = $groupid;
    }

    $sql = "SELECT p.id
              FROM {forum_posts} p
                   LEFT JOIN {forum_discussions} d ON d.id = p.discussion
                   LEFT JOIN {forum_read} r        ON (r.postid = p.id AND r.userid = ?)
             WHERE d.forum = ?
                   AND p.modified >= ? AND r.id is NULL
                   $groupsel";

    if ($posts = $DB->get_records_sql($sql, $params)) {
        $postids = array_keys($posts);
        return forum_tp_mark_posts_read($user, $postids);
    }

    return true;
}


function forum_tp_mark_discussion_read($user, $discussionid) {
    global $CFG, $DB;

    $cutoffdate = time() - ($CFG->forum_oldpostdays*24*60*60);

    $sql = "SELECT p.id
              FROM {forum_posts} p
                   LEFT JOIN {forum_read} r ON (r.postid = p.id AND r.userid = ?)
             WHERE p.discussion = ?
                   AND p.modified >= ? AND r.id is NULL";

    if ($posts = $DB->get_records_sql($sql, array($user->id, $discussionid, $cutoffdate))) {
        $postids = array_keys($posts);
        return forum_tp_mark_posts_read($user, $postids);
    }

    return true;
}


function forum_tp_is_post_read($userid, $post) {
    global $DB;
    return (forum_tp_is_post_old($post) ||
            $DB->record_exists('forum_read', array('userid' => $userid, 'postid' => $post->id)));
}


function forum_tp_is_post_old($post, $time=null) {
    global $CFG;

    if (is_null($time)) {
        $time = time();
    }
    return ($post->modified < ($time - ($CFG->forum_oldpostdays * 24 * 3600)));
}


function forum_tp_get_course_unread_posts($userid, $courseid) {
    global $CFG, $DB;

    $now = round(time(), -2);     $cutoffdate = $now - ($CFG->forum_oldpostdays * 24 * 60 * 60);
    $params = array($userid, $userid, $courseid, $cutoffdate, $userid);

    if (!empty($CFG->forum_enabletimedposts)) {
        $timedsql = "AND d.timestart < ? AND (d.timeend = 0 OR d.timeend > ?)";
        $params[] = $now;
        $params[] = $now;
    } else {
        $timedsql = "";
    }

    if ($CFG->forum_allowforcedreadtracking) {
        $trackingsql = "AND (f.trackingtype = ".FORUM_TRACKING_FORCED."
                            OR (f.trackingtype = ".FORUM_TRACKING_OPTIONAL." AND tf.id IS NULL
                                AND (SELECT trackforums FROM {user} WHERE id = ?) = 1))";
    } else {
        $trackingsql = "AND ((f.trackingtype = ".FORUM_TRACKING_OPTIONAL." OR f.trackingtype = ".FORUM_TRACKING_FORCED.")
                            AND tf.id IS NULL
                            AND (SELECT trackforums FROM {user} WHERE id = ?) = 1)";
    }

    $sql = "SELECT f.id, COUNT(p.id) AS unread
              FROM {forum_posts} p
                   JOIN {forum_discussions} d       ON d.id = p.discussion
                   JOIN {forum} f                   ON f.id = d.forum
                   JOIN {course} c                  ON c.id = f.course
                   LEFT JOIN {forum_read} r         ON (r.postid = p.id AND r.userid = ?)
                   LEFT JOIN {forum_track_prefs} tf ON (tf.userid = ? AND tf.forumid = f.id)
             WHERE f.course = ?
                   AND p.modified >= ? AND r.id is NULL
                   $trackingsql
                   $timedsql
          GROUP BY f.id";

    if ($return = $DB->get_records_sql($sql, $params)) {
        return $return;
    }

    return array();
}


function forum_tp_count_forum_unread_posts($cm, $course) {
    global $CFG, $USER, $DB;

    static $readcache = array();

    $forumid = $cm->instance;

    if (!isset($readcache[$course->id])) {
        $readcache[$course->id] = array();
        if ($counts = forum_tp_get_course_unread_posts($USER->id, $course->id)) {
            foreach ($counts as $count) {
                $readcache[$course->id][$count->id] = $count->unread;
            }
        }
    }

    if (empty($readcache[$course->id][$forumid])) {
                return 0;
    }

    $groupmode = groups_get_activity_groupmode($cm, $course);

    if ($groupmode != SEPARATEGROUPS) {
        return $readcache[$course->id][$forumid];
    }

    if (has_capability('moodle/site:accessallgroups', context_module::instance($cm->id))) {
        return $readcache[$course->id][$forumid];
    }

    require_once($CFG->dirroot.'/course/lib.php');

    $modinfo = get_fast_modinfo($course);

    $mygroups = $modinfo->get_groups($cm->groupingid);

        $mygroups[-1] = -1;

    list ($groups_sql, $groups_params) = $DB->get_in_or_equal($mygroups);

    $now = round(time(), -2);     $cutoffdate = $now - ($CFG->forum_oldpostdays*24*60*60);
    $params = array($USER->id, $forumid, $cutoffdate);

    if (!empty($CFG->forum_enabletimedposts)) {
        $timedsql = "AND d.timestart < ? AND (d.timeend = 0 OR d.timeend > ?)";
        $params[] = $now;
        $params[] = $now;
    } else {
        $timedsql = "";
    }

    $params = array_merge($params, $groups_params);

    $sql = "SELECT COUNT(p.id)
              FROM {forum_posts} p
                   JOIN {forum_discussions} d ON p.discussion = d.id
                   LEFT JOIN {forum_read} r   ON (r.postid = p.id AND r.userid = ?)
             WHERE d.forum = ?
                   AND p.modified >= ? AND r.id is NULL
                   $timedsql
                   AND d.groupid $groups_sql";

    return $DB->get_field_sql($sql, $params);
}


function forum_tp_delete_read_records($userid=-1, $postid=-1, $discussionid=-1, $forumid=-1) {
    global $DB;
    $params = array();

    $select = '';
    if ($userid > -1) {
        if ($select != '') $select .= ' AND ';
        $select .= 'userid = ?';
        $params[] = $userid;
    }
    if ($postid > -1) {
        if ($select != '') $select .= ' AND ';
        $select .= 'postid = ?';
        $params[] = $postid;
    }
    if ($discussionid > -1) {
        if ($select != '') $select .= ' AND ';
        $select .= 'discussionid = ?';
        $params[] = $discussionid;
    }
    if ($forumid > -1) {
        if ($select != '') $select .= ' AND ';
        $select .= 'forumid = ?';
        $params[] = $forumid;
    }
    if ($select == '') {
        return false;
    }
    else {
        return $DB->delete_records_select('forum_read', $select, $params);
    }
}

function forum_tp_get_untracked_forums($userid, $courseid) {
    global $CFG, $DB;

    if ($CFG->forum_allowforcedreadtracking) {
        $trackingsql = "AND (f.trackingtype = ".FORUM_TRACKING_OFF."
                            OR (f.trackingtype = ".FORUM_TRACKING_OPTIONAL." AND (ft.id IS NOT NULL
                                OR (SELECT trackforums FROM {user} WHERE id = ?) = 0)))";
    } else {
        $trackingsql = "AND (f.trackingtype = ".FORUM_TRACKING_OFF."
                            OR ((f.trackingtype = ".FORUM_TRACKING_OPTIONAL." OR f.trackingtype = ".FORUM_TRACKING_FORCED.")
                                AND (ft.id IS NOT NULL
                                    OR (SELECT trackforums FROM {user} WHERE id = ?) = 0)))";
    }

    $sql = "SELECT f.id
              FROM {forum} f
                   LEFT JOIN {forum_track_prefs} ft ON (ft.forumid = f.id AND ft.userid = ?)
             WHERE f.course = ?
                   $trackingsql";

    if ($forums = $DB->get_records_sql($sql, array($userid, $courseid, $userid))) {
        foreach ($forums as $forum) {
            $forums[$forum->id] = $forum;
        }
        return $forums;

    } else {
        return array();
    }
}


function forum_tp_can_track_forums($forum=false, $user=false) {
    global $USER, $CFG, $DB;

            if (empty($CFG->forum_trackreadposts)) {
        return false;
    }

    if ($user === false) {
        $user = $USER;
    }

    if (isguestuser($user) or empty($user->id)) {
        return false;
    }

    if ($forum === false) {
        if ($CFG->forum_allowforcedreadtracking) {
                        return true;
        } else {
            return (bool)$user->trackforums;
        }
    }

        if (is_numeric($forum)) {
        debugging('Better use proper forum object.', DEBUG_DEVELOPER);
        $forum = $DB->get_record('forum', array('id' => $forum), '', 'id,trackingtype');
    }

    $forumallows = ($forum->trackingtype == FORUM_TRACKING_OPTIONAL);
    $forumforced = ($forum->trackingtype == FORUM_TRACKING_FORCED);

    if ($CFG->forum_allowforcedreadtracking) {
                return ($forumforced || ($forumallows  && (!empty($user->trackforums) && (bool)$user->trackforums)));
    } else {
                return ($forumforced || $forumallows)  && !empty($user->trackforums);
    }
}


function forum_tp_is_tracked($forum, $user=false) {
    global $USER, $CFG, $DB;

    if ($user === false) {
        $user = $USER;
    }

    if (isguestuser($user) or empty($user->id)) {
        return false;
    }

        if (is_numeric($forum)) {
        debugging('Better use proper forum object.', DEBUG_DEVELOPER);
        $forum = $DB->get_record('forum', array('id' => $forum));
    }

    if (!forum_tp_can_track_forums($forum, $user)) {
        return false;
    }

    $forumallows = ($forum->trackingtype == FORUM_TRACKING_OPTIONAL);
    $forumforced = ($forum->trackingtype == FORUM_TRACKING_FORCED);
    $userpref = $DB->get_record('forum_track_prefs', array('userid' => $user->id, 'forumid' => $forum->id));

    if ($CFG->forum_allowforcedreadtracking) {
        return $forumforced || ($forumallows && $userpref === false);
    } else {
        return  ($forumallows || $forumforced) && $userpref === false;
    }
}


function forum_tp_start_tracking($forumid, $userid=false) {
    global $USER, $DB;

    if ($userid === false) {
        $userid = $USER->id;
    }

    return $DB->delete_records('forum_track_prefs', array('userid' => $userid, 'forumid' => $forumid));
}


function forum_tp_stop_tracking($forumid, $userid=false) {
    global $USER, $DB;

    if ($userid === false) {
        $userid = $USER->id;
    }

    if (!$DB->record_exists('forum_track_prefs', array('userid' => $userid, 'forumid' => $forumid))) {
        $track_prefs = new stdClass();
        $track_prefs->userid = $userid;
        $track_prefs->forumid = $forumid;
        $DB->insert_record('forum_track_prefs', $track_prefs);
    }

    return forum_tp_delete_read_records($userid, -1, -1, $forumid);
}



function forum_tp_clean_read_records() {
    global $CFG, $DB;

    if (!isset($CFG->forum_oldpostdays)) {
        return;
    }
    $cutoffdate = time() - ($CFG->forum_oldpostdays*24*60*60);

        $sql = "SELECT MIN(fp.modified) AS first
              FROM {forum_posts} fp
                   JOIN {forum_read} fr ON fr.postid=fp.id";
    if (!$first = $DB->get_field_sql($sql)) {
                return;
    }

        $sql = "DELETE
              FROM {forum_read}
             WHERE postid IN (SELECT fp.id
                                FROM {forum_posts} fp
                               WHERE fp.modified >= ? AND fp.modified < ?)";
    $DB->execute($sql, array($first, $cutoffdate));
}


function forum_discussion_update_last_post($discussionid) {
    global $CFG, $DB;

    if (!$DB->record_exists('forum_discussions', array('id' => $discussionid))) {
        return false;
    }

    $sql = "SELECT id, userid, modified
              FROM {forum_posts}
             WHERE discussion=?
             ORDER BY modified DESC";

    if (($lastposts = $DB->get_records_sql($sql, array($discussionid), 0, 1))) {
        $lastpost = reset($lastposts);
        $discussionobject = new stdClass();
        $discussionobject->id           = $discussionid;
        $discussionobject->usermodified = $lastpost->userid;
        $discussionobject->timemodified = $lastpost->modified;
        $DB->update_record('forum_discussions', $discussionobject);
        return $lastpost->id;
    }

    return false;
}



function forum_get_view_actions() {
    return array('view discussion', 'search', 'forum', 'forums', 'subscribers', 'view forum');
}


function forum_get_post_actions() {
    return array('add discussion','add post','delete discussion','delete post','move discussion','prune post','update post');
}


function forum_check_throttling($forum, $cm = null) {
    global $CFG, $DB, $USER;

    if (is_numeric($forum)) {
        $forum = $DB->get_record('forum', array('id' => $forum), '*', MUST_EXIST);
    }

    if (!is_object($forum)) {
        return false;     }

    if (!$cm) {
        $cm = get_coursemodule_from_instance('forum', $forum->id, $forum->course, false, MUST_EXIST);
    }

    if (empty($forum->blockafter)) {
        return false;
    }

    if (empty($forum->blockperiod)) {
        return false;
    }

    $modcontext = context_module::instance($cm->id);
    if (has_capability('mod/forum:postwithoutthrottling', $modcontext)) {
        return false;
    }

        $timenow = time();
    $timeafter = $timenow - $forum->blockperiod;
    $numposts = $DB->count_records_sql('SELECT COUNT(p.id) FROM {forum_posts} p
                                        JOIN {forum_discussions} d
                                        ON p.discussion = d.id WHERE d.forum = ?
                                        AND p.userid = ? AND p.created > ?', array($forum->id, $USER->id, $timeafter));

    $a = new stdClass();
    $a->blockafter = $forum->blockafter;
    $a->numposts = $numposts;
    $a->blockperiod = get_string('secondstotime'.$forum->blockperiod);

    if ($forum->blockafter <= $numposts) {
        $warning = new stdClass();
        $warning->canpost = false;
        $warning->errorcode = 'forumblockingtoomanyposts';
        $warning->module = 'error';
        $warning->additional = $a;
        $warning->link = $CFG->wwwroot . '/mod/forum/view.php?f=' . $forum->id;

        return $warning;
    }

    if ($forum->warnafter <= $numposts) {
        $warning = new stdClass();
        $warning->canpost = true;
        $warning->errorcode = 'forumblockingalmosttoomanyposts';
        $warning->module = 'forum';
        $warning->additional = $a;
        $warning->link = null;

        return $warning;
    }
}


function forum_check_blocking_threshold($thresholdwarning) {
    if (!empty($thresholdwarning) && !$thresholdwarning->canpost) {
        print_error($thresholdwarning->errorcode,
                    $thresholdwarning->module,
                    $thresholdwarning->link,
                    $thresholdwarning->additional);
    }
}



function forum_reset_gradebook($courseid, $type='') {
    global $CFG, $DB;

    $wheresql = '';
    $params = array($courseid);
    if ($type) {
        $wheresql = "AND f.type=?";
        $params[] = $type;
    }

    $sql = "SELECT f.*, cm.idnumber as cmidnumber, f.course as courseid
              FROM {forum} f, {course_modules} cm, {modules} m
             WHERE m.name='forum' AND m.id=cm.module AND cm.instance=f.id AND f.course=? $wheresql";

    if ($forums = $DB->get_records_sql($sql, $params)) {
        foreach ($forums as $forum) {
            forum_grade_item_update($forum, 'reset');
        }
    }
}


function forum_reset_userdata($data) {
    global $CFG, $DB;
    require_once($CFG->dirroot.'/rating/lib.php');

    $componentstr = get_string('modulenameplural', 'forum');
    $status = array();

    $params = array($data->courseid);

    $removeposts = false;
    $typesql     = "";
    if (!empty($data->reset_forum_all)) {
        $removeposts = true;
        $typesstr    = get_string('resetforumsall', 'forum');
        $types       = array();
    } else if (!empty($data->reset_forum_types)){
        $removeposts = true;
        $types       = array();
        $sqltypes    = array();
        $forum_types_all = forum_get_forum_types_all();
        foreach ($data->reset_forum_types as $type) {
            if (!array_key_exists($type, $forum_types_all)) {
                continue;
            }
            $types[] = $forum_types_all[$type];
            $sqltypes[] = $type;
        }
        if (!empty($sqltypes)) {
            list($typesql, $typeparams) = $DB->get_in_or_equal($sqltypes);
            $typesql = " AND f.type " . $typesql;
            $params = array_merge($params, $typeparams);
        }
        $typesstr = get_string('resetforums', 'forum').': '.implode(', ', $types);
    }
    $alldiscussionssql = "SELECT fd.id
                            FROM {forum_discussions} fd, {forum} f
                           WHERE f.course=? AND f.id=fd.forum";

    $allforumssql      = "SELECT f.id
                            FROM {forum} f
                           WHERE f.course=?";

    $allpostssql       = "SELECT fp.id
                            FROM {forum_posts} fp, {forum_discussions} fd, {forum} f
                           WHERE f.course=? AND f.id=fd.forum AND fd.id=fp.discussion";

    $forumssql = $forums = $rm = null;

    if( $removeposts || !empty($data->reset_forum_ratings) ) {
        $forumssql      = "$allforumssql $typesql";
        $forums = $forums = $DB->get_records_sql($forumssql, $params);
        $rm = new rating_manager();
        $ratingdeloptions = new stdClass;
        $ratingdeloptions->component = 'mod_forum';
        $ratingdeloptions->ratingarea = 'post';
    }

    if ($removeposts) {
        $discussionssql = "$alldiscussionssql $typesql";
        $postssql       = "$allpostssql $typesql";

                $fs = get_file_storage();
        if ($forums) {
            foreach ($forums as $forumid=>$unused) {
                if (!$cm = get_coursemodule_from_instance('forum', $forumid)) {
                    continue;
                }
                $context = context_module::instance($cm->id);
                $fs->delete_area_files($context->id, 'mod_forum', 'attachment');
                $fs->delete_area_files($context->id, 'mod_forum', 'post');

                                $ratingdeloptions->contextid = $context->id;
                $rm->delete_ratings($ratingdeloptions);
            }
        }

                $DB->delete_records_select('forum_read', "forumid IN ($forumssql)", $params);

                $DB->delete_records_select('forum_track_prefs', "forumid IN ($forumssql)", $params);

                $DB->delete_records_select('forum_queue', "discussionid IN ($discussionssql)", $params);

                $DB->delete_records_select('forum_posts', "discussion IN ($discussionssql) AND parent <> 0", $params);         $DB->delete_records_select('forum_posts', "discussion IN ($discussionssql AND f.type <> 'single') AND parent = 0", $params);
                $DB->delete_records_select('forum_discussions', "forum IN ($forumssql AND f.type <> 'single')", $params);

                if (empty($data->reset_gradebook_grades)) {
            if (empty($types)) {
                forum_reset_gradebook($data->courseid);
            } else {
                foreach ($types as $type) {
                    forum_reset_gradebook($data->courseid, $type);
                }
            }
        }

        $status[] = array('component'=>$componentstr, 'item'=>$typesstr, 'error'=>false);
    }

        if (!empty($data->reset_forum_ratings)) {
        if ($forums) {
            foreach ($forums as $forumid=>$unused) {
                if (!$cm = get_coursemodule_from_instance('forum', $forumid)) {
                    continue;
                }
                $context = context_module::instance($cm->id);

                                $ratingdeloptions->contextid = $context->id;
                $rm->delete_ratings($ratingdeloptions);
            }
        }

                if (empty($data->reset_gradebook_grades)) {
            forum_reset_gradebook($data->courseid);
        }
    }

        if (!empty($data->reset_forum_digests)) {
        $DB->delete_records_select('forum_digests', "forum IN ($allforumssql)", $params);
        $status[] = array('component' => $componentstr, 'item' => get_string('resetdigests', 'forum'), 'error' => false);
    }

        if (!empty($data->reset_forum_subscriptions)) {
        $DB->delete_records_select('forum_subscriptions', "forum IN ($allforumssql)", $params);
        $DB->delete_records_select('forum_discussion_subs', "forum IN ($allforumssql)", $params);
        $status[] = array('component' => $componentstr, 'item' => get_string('resetsubscriptions', 'forum'), 'error' => false);
    }

        if (!empty($data->reset_forum_track_prefs)) {
        $DB->delete_records_select('forum_track_prefs', "forumid IN ($allforumssql)", $params);
        $status[] = array('component'=>$componentstr, 'item'=>get_string('resettrackprefs','forum'), 'error'=>false);
    }

        if ($data->timeshift) {
        shift_course_mod_dates('forum', array('assesstimestart', 'assesstimefinish'), $data->timeshift, $data->courseid);
        $status[] = array('component'=>$componentstr, 'item'=>get_string('datechanged'), 'error'=>false);
    }

    return $status;
}


function forum_reset_course_form_definition(&$mform) {
    $mform->addElement('header', 'forumheader', get_string('modulenameplural', 'forum'));

    $mform->addElement('checkbox', 'reset_forum_all', get_string('resetforumsall','forum'));

    $mform->addElement('select', 'reset_forum_types', get_string('resetforums', 'forum'), forum_get_forum_types_all(), array('multiple' => 'multiple'));
    $mform->setAdvanced('reset_forum_types');
    $mform->disabledIf('reset_forum_types', 'reset_forum_all', 'checked');

    $mform->addElement('checkbox', 'reset_forum_digests', get_string('resetdigests','forum'));
    $mform->setAdvanced('reset_forum_digests');

    $mform->addElement('checkbox', 'reset_forum_subscriptions', get_string('resetsubscriptions','forum'));
    $mform->setAdvanced('reset_forum_subscriptions');

    $mform->addElement('checkbox', 'reset_forum_track_prefs', get_string('resettrackprefs','forum'));
    $mform->setAdvanced('reset_forum_track_prefs');
    $mform->disabledIf('reset_forum_track_prefs', 'reset_forum_all', 'checked');

    $mform->addElement('checkbox', 'reset_forum_ratings', get_string('deleteallratings'));
    $mform->disabledIf('reset_forum_ratings', 'reset_forum_all', 'checked');
}


function forum_reset_course_form_defaults($course) {
    return array('reset_forum_all'=>1, 'reset_forum_digests' => 0, 'reset_forum_subscriptions'=>0, 'reset_forum_track_prefs'=>0, 'reset_forum_ratings'=>1);
}


function forum_get_layout_modes() {
    return array (FORUM_MODE_FLATOLDEST => get_string('modeflatoldestfirst', 'forum'),
                  FORUM_MODE_FLATNEWEST => get_string('modeflatnewestfirst', 'forum'),
                  FORUM_MODE_THREADED   => get_string('modethreaded', 'forum'),
                  FORUM_MODE_NESTED     => get_string('modenested', 'forum'));
}


function forum_get_forum_types() {
    return array ('general'  => get_string('generalforum', 'forum'),
                  'eachuser' => get_string('eachuserforum', 'forum'),
                  'single'   => get_string('singleforum', 'forum'),
                  'qanda'    => get_string('qandaforum', 'forum'),
                  'blog'     => get_string('blogforum', 'forum'));

}


function forum_get_forum_types_all() {
    return array ('news'     => get_string('namenews','forum'),
                  'social'   => get_string('namesocial','forum'),
                  'general'  => get_string('generalforum', 'forum'),
                  'eachuser' => get_string('eachuserforum', 'forum'),
                  'single'   => get_string('singleforum', 'forum'),
                  'qanda'    => get_string('qandaforum', 'forum'),
                  'blog'     => get_string('blogforum', 'forum'));
}


function forum_get_extra_capabilities() {
    return array('moodle/site:accessallgroups', 'moodle/site:viewfullnames', 'moodle/site:trustcontent', 'moodle/rating:view', 'moodle/rating:viewany', 'moodle/rating:viewall', 'moodle/rating:rate');
}


function forum_extend_settings_navigation(settings_navigation $settingsnav, navigation_node $forumnode) {
    global $USER, $PAGE, $CFG, $DB, $OUTPUT;

    $forumobject = $DB->get_record("forum", array("id" => $PAGE->cm->instance));
    if (empty($PAGE->cm->context)) {
        $PAGE->cm->context = context_module::instance($PAGE->cm->instance);
    }

    $params = $PAGE->url->params();
    if (!empty($params['d'])) {
        $discussionid = $params['d'];
    }

        $enrolled = is_enrolled($PAGE->cm->context, $USER, '', false);
    $activeenrolled = is_enrolled($PAGE->cm->context, $USER, '', true);

    $canmanage  = has_capability('mod/forum:managesubscriptions', $PAGE->cm->context);
    $subscriptionmode = \mod_forum\subscriptions::get_subscription_mode($forumobject);
    $cansubscribe = $activeenrolled && !\mod_forum\subscriptions::is_forcesubscribed($forumobject) &&
            (!\mod_forum\subscriptions::subscription_disabled($forumobject) || $canmanage);

    if ($canmanage) {
        $mode = $forumnode->add(get_string('subscriptionmode', 'forum'), null, navigation_node::TYPE_CONTAINER);

        $allowchoice = $mode->add(get_string('subscriptionoptional', 'forum'), new moodle_url('/mod/forum/subscribe.php', array('id'=>$forumobject->id, 'mode'=>FORUM_CHOOSESUBSCRIBE, 'sesskey'=>sesskey())), navigation_node::TYPE_SETTING);
        $forceforever = $mode->add(get_string("subscriptionforced", "forum"), new moodle_url('/mod/forum/subscribe.php', array('id'=>$forumobject->id, 'mode'=>FORUM_FORCESUBSCRIBE, 'sesskey'=>sesskey())), navigation_node::TYPE_SETTING);
        $forceinitially = $mode->add(get_string("subscriptionauto", "forum"), new moodle_url('/mod/forum/subscribe.php', array('id'=>$forumobject->id, 'mode'=>FORUM_INITIALSUBSCRIBE, 'sesskey'=>sesskey())), navigation_node::TYPE_SETTING);
        $disallowchoice = $mode->add(get_string('subscriptiondisabled', 'forum'), new moodle_url('/mod/forum/subscribe.php', array('id'=>$forumobject->id, 'mode'=>FORUM_DISALLOWSUBSCRIBE, 'sesskey'=>sesskey())), navigation_node::TYPE_SETTING);

        switch ($subscriptionmode) {
            case FORUM_CHOOSESUBSCRIBE :                 $allowchoice->action = null;
                $allowchoice->add_class('activesetting');
                break;
            case FORUM_FORCESUBSCRIBE :                 $forceforever->action = null;
                $forceforever->add_class('activesetting');
                break;
            case FORUM_INITIALSUBSCRIBE :                 $forceinitially->action = null;
                $forceinitially->add_class('activesetting');
                break;
            case FORUM_DISALLOWSUBSCRIBE :                 $disallowchoice->action = null;
                $disallowchoice->add_class('activesetting');
                break;
        }

    } else if ($activeenrolled) {

        switch ($subscriptionmode) {
            case FORUM_CHOOSESUBSCRIBE :                 $notenode = $forumnode->add(get_string('subscriptionoptional', 'forum'));
                break;
            case FORUM_FORCESUBSCRIBE :                 $notenode = $forumnode->add(get_string('subscriptionforced', 'forum'));
                break;
            case FORUM_INITIALSUBSCRIBE :                 $notenode = $forumnode->add(get_string('subscriptionauto', 'forum'));
                break;
            case FORUM_DISALLOWSUBSCRIBE :                 $notenode = $forumnode->add(get_string('subscriptiondisabled', 'forum'));
                break;
        }
    }

    if ($cansubscribe) {
        if (\mod_forum\subscriptions::is_subscribed($USER->id, $forumobject, null, $PAGE->cm)) {
            $linktext = get_string('unsubscribe', 'forum');
        } else {
            $linktext = get_string('subscribe', 'forum');
        }
        $url = new moodle_url('/mod/forum/subscribe.php', array('id'=>$forumobject->id, 'sesskey'=>sesskey()));
        $forumnode->add($linktext, $url, navigation_node::TYPE_SETTING);

        if (isset($discussionid)) {
            if (\mod_forum\subscriptions::is_subscribed($USER->id, $forumobject, $discussionid, $PAGE->cm)) {
                $linktext = get_string('unsubscribediscussion', 'forum');
            } else {
                $linktext = get_string('subscribediscussion', 'forum');
            }
            $url = new moodle_url('/mod/forum/subscribe.php', array(
                    'id' => $forumobject->id,
                    'sesskey' => sesskey(),
                    'd' => $discussionid,
                    'returnurl' => $PAGE->url->out(),
                ));
            $forumnode->add($linktext, $url, navigation_node::TYPE_SETTING);
        }
    }

    if (has_capability('mod/forum:viewsubscribers', $PAGE->cm->context)){
        $url = new moodle_url('/mod/forum/subscribers.php', array('id'=>$forumobject->id));
        $forumnode->add(get_string('showsubscribers', 'forum'), $url, navigation_node::TYPE_SETTING);
    }

    if ($enrolled && forum_tp_can_track_forums($forumobject)) {         if ($forumobject->trackingtype == FORUM_TRACKING_OPTIONAL
                || ((!$CFG->forum_allowforcedreadtracking) && $forumobject->trackingtype == FORUM_TRACKING_FORCED)) {
            if (forum_tp_is_tracked($forumobject)) {
                $linktext = get_string('notrackforum', 'forum');
            } else {
                $linktext = get_string('trackforum', 'forum');
            }
            $url = new moodle_url('/mod/forum/settracking.php', array(
                    'id' => $forumobject->id,
                    'sesskey' => sesskey(),
                ));
            $forumnode->add($linktext, $url, navigation_node::TYPE_SETTING);
        }
    }

    if (!isloggedin() && $PAGE->course->id == SITEID) {
        $userid = guest_user()->id;
    } else {
        $userid = $USER->id;
    }

    $hascourseaccess = ($PAGE->course->id == SITEID) || can_access_course($PAGE->course, $userid);
    $enablerssfeeds = !empty($CFG->enablerssfeeds) && !empty($CFG->forum_enablerssfeeds);

    if ($enablerssfeeds && $forumobject->rsstype && $forumobject->rssarticles && $hascourseaccess) {

        if (!function_exists('rss_get_url')) {
            require_once("$CFG->libdir/rsslib.php");
        }

        if ($forumobject->rsstype == 1) {
            $string = get_string('rsssubscriberssdiscussions','forum');
        } else {
            $string = get_string('rsssubscriberssposts','forum');
        }

        $url = new moodle_url(rss_get_url($PAGE->cm->context->id, $userid, "mod_forum", $forumobject->id));
        $forumnode->add($string, $url, settings_navigation::TYPE_SETTING, null, null, new pix_icon('i/rss', ''));
    }
}


function forum_cm_info_view(cm_info $cm) {
    global $CFG;

    if (forum_tp_can_track_forums()) {
        if ($unread = forum_tp_count_forum_unread_posts($cm, $cm->get_course())) {
            $out = '<span class="unread"> <a href="' . $cm->url . '">';
            if ($unread == 1) {
                $out .= get_string('unreadpostsone', 'forum');
            } else {
                $out .= get_string('unreadpostsnumber', 'forum', $unread);
            }
            $out .= '</a></span>';
            $cm->set_after_link($out);
        }
    }
}


function forum_page_type_list($pagetype, $parentcontext, $currentcontext) {
    $forum_pagetype = array(
        'mod-forum-*'=>get_string('page-mod-forum-x', 'forum'),
        'mod-forum-view'=>get_string('page-mod-forum-view', 'forum'),
        'mod-forum-discuss'=>get_string('page-mod-forum-discuss', 'forum')
    );
    return $forum_pagetype;
}


function forum_get_courses_user_posted_in($user, $discussionsonly = false, $includecontexts = true, $limitfrom = null, $limitnum = null) {
    global $DB;

                if (!$discussionsonly) {
        $subquery = "(SELECT DISTINCT fd.course
                         FROM {forum_discussions} fd
                         JOIN {forum_posts} fp ON fp.discussion = fd.id
                        WHERE fp.userid = :userid )";
    } else {
        $subquery= "(SELECT DISTINCT fd.course
                         FROM {forum_discussions} fd
                        WHERE fd.userid = :userid )";
    }

    $params = array('userid' => $user->id);

        if ($includecontexts) {
        $ctxselect = ', ' . context_helper::get_preload_record_columns_sql('ctx');
        $ctxjoin = "LEFT JOIN {context} ctx ON (ctx.instanceid = c.id AND ctx.contextlevel = :contextlevel)";
        $params['contextlevel'] = CONTEXT_COURSE;
    } else {
        $ctxselect = '';
        $ctxjoin = '';
    }

            $sql = "SELECT c.* $ctxselect
            FROM {course} c
            $ctxjoin
            WHERE c.id IN ($subquery)";
    $courses = $DB->get_records_sql($sql, $params, $limitfrom, $limitnum);
    if ($includecontexts) {
        array_map('context_helper::preload_from_record', $courses);
    }
    return $courses;
}


function forum_get_forums_user_posted_in($user, array $courseids = null, $discussionsonly = false, $limitfrom = null, $limitnum = null) {
    global $DB;

    if (!is_null($courseids)) {
        list($coursewhere, $params) = $DB->get_in_or_equal($courseids, SQL_PARAMS_NAMED, 'courseid');
        $coursewhere = ' AND f.course '.$coursewhere;
    } else {
        $coursewhere = '';
        $params = array();
    }
    $params['userid'] = $user->id;
    $params['forum'] = 'forum';

    if ($discussionsonly) {
        $join = 'JOIN {forum_discussions} ff ON ff.forum = f.id';
    } else {
        $join = 'JOIN {forum_discussions} fd ON fd.forum = f.id
                 JOIN {forum_posts} ff ON ff.discussion = fd.id';
    }

    $sql = "SELECT f.*, cm.id AS cmid
              FROM {forum} f
              JOIN {course_modules} cm ON cm.instance = f.id
              JOIN {modules} m ON m.id = cm.module
              JOIN (
                  SELECT f.id
                    FROM {forum} f
                    {$join}
                   WHERE ff.userid = :userid
                GROUP BY f.id
                   ) j ON j.id = f.id
             WHERE m.name = :forum
                 {$coursewhere}";

    $courseforums = $DB->get_records_sql($sql, $params, $limitfrom, $limitnum);
    return $courseforums;
}


function forum_get_posts_by_user($user, array $courses, $musthaveaccess = false, $discussionsonly = false, $limitfrom = 0, $limitnum = 50) {
    global $DB, $USER, $CFG;

    $return = new stdClass;
    $return->totalcount = 0;        $return->courses = array();     $return->forums = array();      $return->posts = array();
            if (empty($courses)) {
        return $return;
    }

        $isloggedin = isloggedin();
    $isguestuser = $isloggedin && isguestuser();
    $iscurrentuser = $isloggedin && $USER->id == $user->id;

                $usercontext = context_user::instance($user->id, MUST_EXIST);
    $hascapsonuser = !$iscurrentuser && $DB->record_exists('role_assignments', array('userid' => $USER->id, 'contextid' => $usercontext->id));
    $hascapsonuser = $hascapsonuser && has_all_capabilities(array('moodle/user:viewdetails', 'moodle/user:readuserposts'), $usercontext);

                foreach ($courses as $course) {
        $coursecontext = context_course::instance($course->id, MUST_EXIST);
        if ($iscurrentuser || $hascapsonuser) {
                                                                                                            if (!is_viewing($coursecontext, $user) && !is_enrolled($coursecontext, $user)) {
                                if ($musthaveaccess) {
                    print_error('errorenrolmentrequired', 'forum');
                }
                continue;
            }
        } else {
                                    if (!can_access_course($course)) {
                if ($musthaveaccess) {
                    print_error('errorenrolmentrequired', 'forum');
                }
                continue;
            }

                                                                        if (groups_get_course_groupmode($course) == SEPARATEGROUPS && $course->groupmodeforce
              && !has_capability('moodle/site:accessallgroups', $coursecontext) && !has_capability('moodle/site:accessallgroups', $coursecontext, $user->id)) {
                                if (!$isloggedin or $isguestuser) {
                                        if ($musthaveaccess) {
                        redirect(get_login_url());
                    }
                    continue;
                }
                                $mygroups = array_keys(groups_get_all_groups($course->id, $USER->id, $course->defaultgroupingid, 'g.id, g.name'));
                                $usergroups = array_keys(groups_get_all_groups($course->id, $user->id, $course->defaultgroupingid, 'g.id, g.name'));
                                $intersect = array_intersect($mygroups, $usergroups);
                if (empty($intersect)) {
                                                            if ($musthaveaccess) {
                        print_error("groupnotamember", '', $CFG->wwwroot."/course/view.php?id=$course->id");
                    }
                    continue;
                }
            }
        }
                                $return->courses[$course->id] = $course;
    }
        unset($courses);

        if (empty($return->courses)) {
                                if ($musthaveaccess) {
            print_error('permissiondenied');
        } else {
            return $return;
        }
    }

                $forums = forum_get_forums_user_posted_in($user, array_keys($return->courses), $discussionsonly);

        $forumsearchwhere = array();
        $forumsearchparams = array();
        $forumsearchfullaccess = array();
        $now = round(time(), -2);
                foreach ($return->courses as $course) {
                $modinfo = get_fast_modinfo($course);
        if (empty($modinfo->instances['forum'])) {
                        continue;
        }
                foreach ($modinfo->get_instances_of('forum') as $forumid => $cm) {
            if (!$cm->uservisible or !isset($forums[$forumid])) {
                continue;
            }
                        $forum = $forums[$forumid];

                                                $forum->cm = new stdClass();
            foreach ($cm as $key => $value) {
                $forum->cm->$key = $value;
            }

                                                if (!has_capability('mod/forum:viewdiscussion', $cm->context) && !($hascapsonuser && has_capability('mod/forum:viewdiscussion', $cm->context, $user->id))) {
                continue;
            }

                        $forumsearchselect = array();
            if (!$iscurrentuser && !$hascapsonuser) {
                                if (groups_get_activity_groupmode($cm, $course) == SEPARATEGROUPS and !has_capability('moodle/site:accessallgroups', $cm->context)) {
                    $groups = $modinfo->get_groups($cm->groupingid);
                    $groups[] = -1;
                    list($groupid_sql, $groupid_params) = $DB->get_in_or_equal($groups, SQL_PARAMS_NAMED, 'grps'.$forumid.'_');
                    $forumsearchparams = array_merge($forumsearchparams, $groupid_params);
                    $forumsearchselect[] = "d.groupid $groupid_sql";
                }

                                if (!empty($CFG->forum_enabletimedposts) && !has_capability('mod/forum:viewhiddentimedposts', $cm->context)) {
                    $forumsearchselect[] = "(d.userid = :userid{$forumid} OR (d.timestart < :timestart{$forumid} AND (d.timeend = 0 OR d.timeend > :timeend{$forumid})))";
                    $forumsearchparams['userid'.$forumid] = $user->id;
                    $forumsearchparams['timestart'.$forumid] = $now;
                    $forumsearchparams['timeend'.$forumid] = $now;
                }

                                if ($forum->type == 'qanda' && !has_capability('mod/forum:viewqandawithoutposting', $cm->context)) {
                                        $discussionspostedin = forum_discussions_user_has_posted_in($forum->id, $user->id);
                    if (!empty($discussionspostedin)) {
                        $forumonlydiscussions = array();                          foreach ($discussionspostedin as $d) {
                            $forumonlydiscussions[] = $d->id;
                        }
                        list($discussionid_sql, $discussionid_params) = $DB->get_in_or_equal($forumonlydiscussions, SQL_PARAMS_NAMED, 'qanda'.$forumid.'_');
                        $forumsearchparams = array_merge($forumsearchparams, $discussionid_params);
                        $forumsearchselect[] = "(d.id $discussionid_sql OR p.parent = 0)";
                    } else {
                        $forumsearchselect[] = "p.parent = 0";
                    }

                }

                if (count($forumsearchselect) > 0) {
                    $forumsearchwhere[] = "(d.forum = :forum{$forumid} AND ".implode(" AND ", $forumsearchselect).")";
                    $forumsearchparams['forum'.$forumid] = $forumid;
                } else {
                    $forumsearchfullaccess[] = $forumid;
                }
            } else {
                                $forumsearchfullaccess[] = $forumid;
            }
        }
    }

            if (empty($forumsearchwhere) && empty($forumsearchfullaccess)) {
        return $return;
    }

        if (count($forumsearchfullaccess) > 0) {
        list($fullidsql, $fullidparams) = $DB->get_in_or_equal($forumsearchfullaccess, SQL_PARAMS_NAMED, 'fula');
        $forumsearchparams = array_merge($forumsearchparams, $fullidparams);
        $forumsearchwhere[] = "(d.forum $fullidsql)";
    }

                $userfields = user_picture::fields('u', null, 'useridx');
    $countsql = 'SELECT COUNT(*) ';
    $selectsql = 'SELECT p.*, d.forum, d.name AS discussionname, '.$userfields.' ';
    $wheresql = implode(" OR ", $forumsearchwhere);

    if ($discussionsonly) {
        if ($wheresql == '') {
            $wheresql = 'p.parent = 0';
        } else {
            $wheresql = 'p.parent = 0 AND ('.$wheresql.')';
        }
    }

    $sql = "FROM {forum_posts} p
            JOIN {forum_discussions} d ON d.id = p.discussion
            JOIN {user} u ON u.id = p.userid
           WHERE ($wheresql)
             AND p.userid = :userid ";
    $orderby = "ORDER BY p.modified DESC";
    $forumsearchparams['userid'] = $user->id;

        $return->totalcount = $DB->count_records_sql($countsql.$sql, $forumsearchparams);
        $return->posts = $DB->get_records_sql($selectsql.$sql.$orderby, $forumsearchparams, $limitfrom, $limitnum);

                    foreach ($return->posts as $post) {
        if (!array_key_exists($post->forum, $return->forums)) {
            $return->forums[$post->forum] = $forums[$post->forum];
        }
    }

    return $return;
}


function forum_set_user_maildigest($forum, $maildigest, $user = null) {
    global $DB, $USER;

    if (is_number($forum)) {
        $forum = $DB->get_record('forum', array('id' => $forum));
    }

    if ($user === null) {
        $user = $USER;
    }

    $course  = $DB->get_record('course', array('id' => $forum->course), '*', MUST_EXIST);
    $cm      = get_coursemodule_from_instance('forum', $forum->id, $course->id, false, MUST_EXIST);
    $context = context_module::instance($cm->id);

        require_capability('mod/forum:viewdiscussion', $context, $user->id);

        $digestoptions = forum_get_user_digest_options($user);

    if (!isset($digestoptions[$maildigest])) {
        throw new moodle_exception('invaliddigestsetting', 'mod_forum');
    }

        $subscription = $DB->get_record('forum_digests', array(
        'userid' => $user->id,
        'forum' => $forum->id,
    ));

        if ($subscription) {
        if ($maildigest == -1) {
            $DB->delete_records('forum_digests', array('forum' => $forum->id, 'userid' => $user->id));
        } else if ($maildigest !== $subscription->maildigest) {

            $subscription->maildigest = $maildigest;
            $DB->update_record('forum_digests', $subscription);
        }
    } else {
        if ($maildigest != -1) {

            $subscription = new stdClass();
            $subscription->forum = $forum->id;
            $subscription->userid = $user->id;
            $subscription->maildigest = $maildigest;
            $subscription->id = $DB->insert_record('forum_digests', $subscription);
        }
    }
}


function forum_get_user_maildigest_bulk($digests, $user, $forumid) {
    if (isset($digests[$forumid]) && isset($digests[$forumid][$user->id])) {
        $maildigest = $digests[$forumid][$user->id];
        if ($maildigest === -1) {
            $maildigest = $user->maildigest;
        }
    } else {
        $maildigest = $user->maildigest;
    }
    return $maildigest;
}


function forum_get_user_digest_options($user = null) {
    global $USER;

        if ($user === null) {
        $user = $USER;
    }

    $digestoptions = array();
    $digestoptions['0']  = get_string('emaildigestoffshort', 'mod_forum');
    $digestoptions['1']  = get_string('emaildigestcompleteshort', 'mod_forum');
    $digestoptions['2']  = get_string('emaildigestsubjectsshort', 'mod_forum');

            $digestoptions['-1'] = get_string('emaildigestdefault', 'mod_forum',
            $digestoptions[$user->maildigest]);

        ksort($digestoptions);

    return $digestoptions;
}


function forum_get_context($forumid, $context = null) {
    global $PAGE;

    if (!$context || !($context instanceof context_module)) {
                if ($PAGE->cm && $PAGE->cm->modname === 'forum' && $PAGE->cm->instance == $forumid
                && $PAGE->context->contextlevel == CONTEXT_MODULE && $PAGE->context->instanceid == $PAGE->cm->id) {
            $context = $PAGE->context;
        } else {
            $cm = get_coursemodule_from_instance('forum', $forumid);
            $context = \context_module::instance($cm->id);
        }
    }

    return $context;
}


function forum_view($forum, $course, $cm, $context) {

        $completion = new completion_info($course);
    $completion->set_module_viewed($cm);


    $params = array(
        'context' => $context,
        'objectid' => $forum->id
    );

    $event = \mod_forum\event\course_module_viewed::create($params);
    $event->add_record_snapshot('course_modules', $cm);
    $event->add_record_snapshot('course', $course);
    $event->add_record_snapshot('forum', $forum);
    $event->trigger();
}


function forum_discussion_view($modcontext, $forum, $discussion) {
    $params = array(
        'context' => $modcontext,
        'objectid' => $discussion->id,
    );

    $event = \mod_forum\event\discussion_viewed::create($params);
    $event->add_record_snapshot('forum_discussions', $discussion);
    $event->add_record_snapshot('forum', $forum);
    $event->trigger();
}


function forum_discussion_pin($modcontext, $forum, $discussion) {
    global $DB;

    $DB->set_field('forum_discussions', 'pinned', FORUM_DISCUSSION_PINNED, array('id' => $discussion->id));

    $params = array(
        'context' => $modcontext,
        'objectid' => $discussion->id,
        'other' => array('forumid' => $forum->id)
    );

    $event = \mod_forum\event\discussion_pinned::create($params);
    $event->add_record_snapshot('forum_discussions', $discussion);
    $event->trigger();
}


function forum_discussion_unpin($modcontext, $forum, $discussion) {
    global $DB;

    $DB->set_field('forum_discussions', 'pinned', FORUM_DISCUSSION_UNPINNED, array('id' => $discussion->id));

    $params = array(
        'context' => $modcontext,
        'objectid' => $discussion->id,
        'other' => array('forumid' => $forum->id)
    );

    $event = \mod_forum\event\discussion_unpinned::create($params);
    $event->add_record_snapshot('forum_discussions', $discussion);
    $event->trigger();
}


function mod_forum_myprofile_navigation(core_user\output\myprofile\tree $tree, $user, $iscurrentuser, $course) {
    if (isguestuser($user)) {
                        return false;
    }
    $postsurl = new moodle_url('/mod/forum/user.php', array('id' => $user->id));
    if (!empty($course)) {
        $postsurl->param('course', $course->id);
    }
    $string = get_string('forumposts', 'mod_forum');
    $node = new core_user\output\myprofile\node('miscellaneous', 'forumposts', $string, null, $postsurl);
    $tree->add_node($node);

    $discussionssurl = new moodle_url('/mod/forum/user.php', array('id' => $user->id, 'mode' => 'discussions'));
    if (!empty($course)) {
        $discussionssurl->param('course', $course->id);
    }
    $string = get_string('myprofileotherdis', 'mod_forum');
    $node = new core_user\output\myprofile\node('miscellaneous', 'forumdiscussions', $string, null,
        $discussionssurl);
    $tree->add_node($node);

    return true;
}
