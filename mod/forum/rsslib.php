<?php





require_once($CFG->libdir.'/rsslib.php');


function forum_rss_get_feed($context, $args) {
    global $CFG, $DB, $USER;

    $status = true;

        if (empty($CFG->forum_enablerssfeeds)) {
        debugging('DISABLED (module configuration)');
        return null;
    }

    $forumid  = clean_param($args[3], PARAM_INT);
    $cm = get_coursemodule_from_instance('forum', $forumid, 0, false, MUST_EXIST);
    $modcontext = context_module::instance($cm->id);

        if ($context->id != $modcontext->id || !has_capability('mod/forum:viewdiscussion', $modcontext)) {
        return null;
    }

    $forum = $DB->get_record('forum', array('id' => $forumid), '*', MUST_EXIST);
    if (!rss_enabled_for_mod('forum', $forum)) {
        return null;
    }

        list($sql, $params) = forum_rss_get_sql($forum, $cm);

        $filename = rss_get_file_name($forum, $sql, $params);
    $cachedfilepath = rss_get_file_full_name('mod_forum', $filename);

        $cachedfilelastmodified = 0;
    if (file_exists($cachedfilepath)) {
        $cachedfilelastmodified = filemtime($cachedfilepath);
    }
        $dontrecheckcutoff = time() - 60; 
            if (($cachedfilelastmodified == 0) || (($dontrecheckcutoff > $cachedfilelastmodified) &&
        forum_rss_newstuff($forum, $cm, $cachedfilelastmodified))) {
                $result = forum_rss_feed_contents($forum, $sql, $params, $modcontext);
        $status = rss_save_file('mod_forum', $filename, $result);
    }

        return $cachedfilepath;
}


function forum_rss_delete_file($forum) {
    rss_delete_file('mod_forum', $forum);
}



function forum_rss_newstuff($forum, $cm, $time) {
    global $DB;

    list($sql, $params) = forum_rss_get_sql($forum, $cm, $time);

    return $DB->record_exists_sql($sql, $params);
}


function forum_rss_get_sql($forum, $cm, $time=0) {
    if ($forum->rsstype == 1) {         return forum_rss_feed_discussions_sql($forum, $cm, $time);
    } else {         return forum_rss_feed_posts_sql($forum, $cm, $time);
    }
}


function forum_rss_feed_discussions_sql($forum, $cm, $newsince=0) {
    global $CFG, $DB, $USER;

    $timelimit = '';

    $modcontext = null;

    $now = round(time(), -2);
    $params = array();

    $modcontext = context_module::instance($cm->id);

    if (!empty($CFG->forum_enabletimedposts)) {         if (!has_capability('mod/forum:viewhiddentimedposts', $modcontext)) {
            $timelimit = " AND ((d.timestart <= :now1 AND (d.timeend = 0 OR d.timeend > :now2))";
            $params['now1'] = $now;
            $params['now2'] = $now;
            if (isloggedin()) {
                $timelimit .= " OR d.userid = :userid";
                $params['userid'] = $USER->id;
            }
            $timelimit .= ")";
        }
    }

        if ($newsince) {
        $params['newsince'] = $newsince;
        $newsince = " AND p.modified > :newsince";
    } else {
        $newsince = '';
    }

        $groupmode = groups_get_activity_groupmode($cm);
    $currentgroup = groups_get_activity_group($cm);
    list($groupselect, $groupparams) = forum_rss_get_group_sql($cm, $groupmode, $currentgroup, $modcontext);

        $params = array_merge($params, $groupparams);

    $forumsort = "d.timemodified DESC";
    $postdata = "p.id AS postid, p.subject, p.created as postcreated, p.modified, p.discussion, p.userid, p.message as postmessage, p.messageformat AS postformat, p.messagetrust AS posttrust";
    $userpicturefields = user_picture::fields('u', null, 'userid');

    $sql = "SELECT $postdata, d.id as discussionid, d.name as discussionname, d.timemodified, d.usermodified, d.groupid,
                   d.timestart, d.timeend, $userpicturefields
              FROM {forum_discussions} d
                   JOIN {forum_posts} p ON p.discussion = d.id
                   JOIN {user} u ON p.userid = u.id
             WHERE d.forum = {$forum->id} AND p.parent = 0
                   $timelimit $groupselect $newsince
          ORDER BY $forumsort";
    return array($sql, $params);
}


function forum_rss_feed_posts_sql($forum, $cm, $newsince=0) {
    $modcontext = context_module::instance($cm->id);

        $groupmode = groups_get_activity_groupmode($cm);
    $currentgroup = groups_get_activity_group($cm);
    $params = array();

    list($groupselect, $groupparams) = forum_rss_get_group_sql($cm, $groupmode, $currentgroup, $modcontext);

        $params = array_merge($params, $groupparams);

        if ($newsince) {
        $params['newsince'] = $newsince;
        $newsince = " AND p.modified > :newsince";
    } else {
        $newsince = '';
    }

    $usernamefields = get_all_user_name_fields(true, 'u');
    $sql = "SELECT p.id AS postid,
                 d.id AS discussionid,
                 d.name AS discussionname,
                 d.groupid,
                 d.timestart,
                 d.timeend,
                 u.id AS userid,
                 $usernamefields,
                 p.subject AS postsubject,
                 p.message AS postmessage,
                 p.created AS postcreated,
                 p.messageformat AS postformat,
                 p.messagetrust AS posttrust,
                 p.parent as postparent
            FROM {forum_discussions} d,
               {forum_posts} p,
               {user} u
            WHERE d.forum = {$forum->id} AND
                p.discussion = d.id AND
                u.id = p.userid $newsince
                $groupselect
            ORDER BY p.created desc";

    return array($sql, $params);
}


function forum_rss_get_group_sql($cm, $groupmode, $currentgroup, $modcontext=null) {
    $groupselect = '';
    $params = array();

    if ($groupmode) {
        if ($groupmode == VISIBLEGROUPS or has_capability('moodle/site:accessallgroups', $modcontext)) {
            if ($currentgroup) {
                $groupselect = "AND (d.groupid = :groupid OR d.groupid = -1)";
                $params['groupid'] = $currentgroup;
            }
        } else {
                        if ($currentgroup) {
                $groupselect = "AND (d.groupid = :groupid OR d.groupid = -1)";
                $params['groupid'] = $currentgroup;
            } else {
                $groupselect = "AND d.groupid = -1";
            }
        }
    }

    return array($groupselect, $params);
}



function forum_rss_feed_contents($forum, $sql, $params, $context) {
    global $CFG, $DB, $USER;

    $status = true;

    $recs = $DB->get_recordset_sql($sql, $params, 0, $forum->rssarticles);

        $isdiscussion = true;
    if (!empty($forum->rsstype) && $forum->rsstype!=1) {
        $isdiscussion = false;
    }

    if (!$cm = get_coursemodule_from_instance('forum', $forum->id, $forum->course)) {
        print_error('invalidcoursemodule');
    }

    $formatoptions = new stdClass();
    $items = array();
    foreach ($recs as $rec) {
            $item = new stdClass();

            $discussion = new stdClass();
            $discussion->id = $rec->discussionid;
            $discussion->groupid = $rec->groupid;
            $discussion->timestart = $rec->timestart;
            $discussion->timeend = $rec->timeend;

            $post = null;
            if (!$isdiscussion) {
                $post = new stdClass();
                $post->id = $rec->postid;
                $post->parent = $rec->postparent;
                $post->userid = $rec->userid;
            }

            if ($isdiscussion && !forum_user_can_see_discussion($forum, $discussion, $context)) {
                                $item->title = get_string('forumsubjecthidden', 'forum');
                $message = get_string('forumbodyhidden', 'forum');
                $item->author = get_string('forumauthorhidden', 'forum');
            } else if (!$isdiscussion && !forum_user_can_see_post($forum, $discussion, $post, $USER, $cm)) {
                                $item->title = get_string('forumsubjecthidden', 'forum');
                $message = get_string('forumbodyhidden', 'forum');
                $item->author = get_string('forumauthorhidden', 'forum');
            } else {
                                if ($isdiscussion && !empty($rec->discussionname)) {
                    $item->title = format_string($rec->discussionname);
                } else if (!empty($rec->postsubject)) {
                    $item->title = format_string($rec->postsubject);
                } else {
                                        $item->title = format_string($forum->name.' '.userdate($rec->postcreated,get_string('strftimedatetimeshort', 'langconfig')));
                }
                $item->author = fullname($rec);
                $message = file_rewrite_pluginfile_urls($rec->postmessage, 'pluginfile.php', $context->id,
                        'mod_forum', 'post', $rec->postid);
                $formatoptions->trusted = $rec->posttrust;
            }

            if ($isdiscussion) {
                $item->link = $CFG->wwwroot."/mod/forum/discuss.php?d=".$rec->discussionid;
            } else {
                $item->link = $CFG->wwwroot."/mod/forum/discuss.php?d=".$rec->discussionid."&parent=".$rec->postid;
            }

            $formatoptions->trusted = $rec->posttrust;
            $item->description = format_text($message, $rec->postformat, $formatoptions, $forum->course);

                        
            $item->pubdate = $rec->postcreated;

            $items[] = $item;
        }
    $recs->close();

        $header = rss_standard_header(strip_tags(format_string($forum->name,true)),
                                  $CFG->wwwroot."/mod/forum/view.php?f=".$forum->id,
                                  format_string($forum->intro,true));         $articles = '';
    if (!empty($items)) {
        $articles = rss_add_items($items);
    }
        $footer = rss_standard_footer();

    return $header . $articles . $footer;
}
