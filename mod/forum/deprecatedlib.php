<?php



defined('MOODLE_INTERNAL') || die();



function forum_count_unrated_posts($discussionid, $userid) {
    global $CFG, $DB;
    debugging('forum_count_unrated_posts() is deprecated and will not be replaced.', DEBUG_DEVELOPER);

    $sql = "SELECT COUNT(*) as num
              FROM {forum_posts}
             WHERE parent > 0
               AND discussion = :discussionid
               AND userid <> :userid";
    $params = array('discussionid' => $discussionid, 'userid' => $userid);
    $posts = $DB->get_record_sql($sql, $params);
    if ($posts) {
        $sql = "SELECT count(*) as num
                  FROM {forum_posts} p,
                       {rating} r
                 WHERE p.discussion = :discussionid AND
                       p.id = r.itemid AND
                       r.userid = userid AND
                       r.component = 'mod_forum' AND
                       r.ratingarea = 'post'";
        $rated = $DB->get_record_sql($sql, $params);
        if ($rated) {
            if ($posts->num > $rated->num) {
                return $posts->num - $rated->num;
            } else {
                return 0;                }
        } else {
            return $posts->num;
        }
    } else {
        return 0;
    }
}




function forum_tp_count_discussion_read_records($userid, $discussionid) {
    debugging('forum_tp_count_discussion_read_records() is deprecated and will not be replaced.', DEBUG_DEVELOPER);

    global $CFG, $DB;

    $cutoffdate = isset($CFG->forum_oldpostdays) ? (time() - ($CFG->forum_oldpostdays*24*60*60)) : 0;

    $sql = 'SELECT COUNT(DISTINCT p.id) '.
           'FROM {forum_discussions} d '.
           'LEFT JOIN {forum_read} r ON d.id = r.discussionid AND r.userid = ? '.
           'LEFT JOIN {forum_posts} p ON p.discussion = d.id '.
                'AND (p.modified < ? OR p.id = r.postid) '.
           'WHERE d.id = ? ';

    return ($DB->count_records_sql($sql, array($userid, $cutoffdate, $discussionid)));
}


function forum_get_user_discussions($courseid, $userid, $groupid=0) {
    debugging('forum_get_user_discussions() is deprecated and will not be replaced.', DEBUG_DEVELOPER);

    global $CFG, $DB;
    $params = array($courseid, $userid);
    if ($groupid) {
        $groupselect = " AND d.groupid = ? ";
        $params[] = $groupid;
    } else  {
        $groupselect = "";
    }

    $allnames = get_all_user_name_fields(true, 'u');
    return $DB->get_records_sql("SELECT p.*, d.groupid, $allnames, u.email, u.picture, u.imagealt,
                                   f.type as forumtype, f.name as forumname, f.id as forumid
                              FROM {forum_discussions} d,
                                   {forum_posts} p,
                                   {user} u,
                                   {forum} f
                             WHERE d.course = ?
                               AND p.discussion = d.id
                               AND p.parent = 0
                               AND p.userid = u.id
                               AND u.id = ?
                               AND d.forum = f.id $groupselect
                          ORDER BY p.created DESC", $params);
}




function forum_tp_count_forum_posts($forumid, $groupid=false) {
    debugging('forum_tp_count_forum_posts() is deprecated and will not be replaced.', DEBUG_DEVELOPER);

    global $CFG, $DB;
    $params = array($forumid);
    $sql = 'SELECT COUNT(*) '.
           'FROM {forum_posts} fp,{forum_discussions} fd '.
           'WHERE fd.forum = ? AND fp.discussion = fd.id';
    if ($groupid !== false) {
        $sql .= ' AND (fd.groupid = ? OR fd.groupid = -1)';
        $params[] = $groupid;
    }
    $count = $DB->count_records_sql($sql, $params);


    return $count;
}


function forum_tp_count_forum_read_records($userid, $forumid, $groupid=false) {
    debugging('forum_tp_count_forum_read_records() is deprecated and will not be replaced.', DEBUG_DEVELOPER);

    global $CFG, $DB;

    $cutoffdate = time() - ($CFG->forum_oldpostdays*24*60*60);

    $groupsel = '';
    $params = array($userid, $forumid, $cutoffdate);
    if ($groupid !== false) {
        $groupsel = "AND (d.groupid = ? OR d.groupid = -1)";
        $params[] = $groupid;
    }

    $sql = "SELECT COUNT(p.id)
              FROM  {forum_posts} p
                    JOIN {forum_discussions} d ON d.id = p.discussion
                    LEFT JOIN {forum_read} r   ON (r.postid = p.id AND r.userid= ?)
              WHERE d.forum = ?
                    AND (p.modified < $cutoffdate OR (p.modified >= ? AND r.id IS NOT NULL))
                    $groupsel";

    return $DB->get_field_sql($sql, $params);
}




function forum_get_open_modes() {
    debugging('forum_get_open_modes() is deprecated and will not be replaced.', DEBUG_DEVELOPER);
    return array();
}




function forum_get_child_posts($parent, $forumid) {
    debugging('forum_get_child_posts() is deprecated.', DEBUG_DEVELOPER);

    global $CFG, $DB;

    $allnames = get_all_user_name_fields(true, 'u');
    return $DB->get_records_sql("SELECT p.*, $forumid AS forum, $allnames, u.email, u.picture, u.imagealt
                              FROM {forum_posts} p
                         LEFT JOIN {user} u ON p.userid = u.id
                             WHERE p.parent = ?
                          ORDER BY p.created ASC", array($parent));
}


function forum_get_discussion_posts($discussion, $sort, $forumid) {
    debugging('forum_get_discussion_posts() is deprecated.', DEBUG_DEVELOPER);

    global $CFG, $DB;

    $allnames = get_all_user_name_fields(true, 'u');
    return $DB->get_records_sql("SELECT p.*, $forumid AS forum, $allnames, u.email, u.picture, u.imagealt
                              FROM {forum_posts} p
                         LEFT JOIN {user} u ON p.userid = u.id
                             WHERE p.discussion = ?
                               AND p.parent > 0 $sort", array($discussion));
}




function forum_get_ratings($context, $postid, $sort = "u.firstname ASC") {
    debugging('forum_get_ratings() is deprecated.', DEBUG_DEVELOPER);
    $options = new stdClass;
    $options->context = $context;
    $options->component = 'mod_forum';
    $options->ratingarea = 'post';
    $options->itemid = $postid;
    $options->sort = "ORDER BY $sort";

    $rm = new rating_manager();
    return $rm->get_all_ratings_for_item($options);
}


function forum_get_tracking_link($forum, $messages=array(), $fakelink=true) {
    debugging('forum_get_tracking_link() is deprecated.', DEBUG_DEVELOPER);

    global $CFG, $USER, $PAGE, $OUTPUT;

    static $strnotrackforum, $strtrackforum;

    if (isset($messages['trackforum'])) {
         $strtrackforum = $messages['trackforum'];
    }
    if (isset($messages['notrackforum'])) {
         $strnotrackforum = $messages['notrackforum'];
    }
    if (empty($strtrackforum)) {
        $strtrackforum = get_string('trackforum', 'forum');
    }
    if (empty($strnotrackforum)) {
        $strnotrackforum = get_string('notrackforum', 'forum');
    }

    if (forum_tp_is_tracked($forum)) {
        $linktitle = $strnotrackforum;
        $linktext = $strnotrackforum;
    } else {
        $linktitle = $strtrackforum;
        $linktext = $strtrackforum;
    }

    $link = '';
    if ($fakelink) {
        $PAGE->requires->js('/mod/forum/forum.js');
        $PAGE->requires->js_function_call('forum_produce_tracking_link', Array($forum->id, $linktext, $linktitle));
                $link .= '<noscript>';
    }
    $url = new moodle_url('/mod/forum/settracking.php', array(
            'id' => $forum->id,
            'sesskey' => sesskey(),
        ));
    $link .= $OUTPUT->single_button($url, $linktext, 'get', array('title'=>$linktitle));

    if ($fakelink) {
        $link .= '</noscript>';
    }

    return $link;
}


function forum_tp_count_discussion_unread_posts($userid, $discussionid) {
    debugging('forum_tp_count_discussion_unread_posts() is deprecated.', DEBUG_DEVELOPER);
    global $CFG, $DB;

    $cutoffdate = isset($CFG->forum_oldpostdays) ? (time() - ($CFG->forum_oldpostdays*24*60*60)) : 0;

    $sql = 'SELECT COUNT(p.id) '.
           'FROM {forum_posts} p '.
           'LEFT JOIN {forum_read} r ON r.postid = p.id AND r.userid = ? '.
           'WHERE p.discussion = ? '.
                'AND p.modified >= ? AND r.id is NULL';

    return $DB->count_records_sql($sql, array($userid, $discussionid, $cutoffdate));
}


function forum_convert_to_roles() {
    debugging('forum_convert_to_roles() is deprecated and will not be replaced.', DEBUG_DEVELOPER);
}


function forum_tp_get_read_records($userid=-1, $postid=-1, $discussionid=-1, $forumid=-1) {
    debugging('forum_tp_get_read_records() is deprecated and will not be replaced.', DEBUG_DEVELOPER);

    global $DB;
    $select = '';
    $params = array();

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

    return $DB->get_records_select('forum_read', $select, $params);
}


function forum_tp_get_discussion_read_records($userid, $discussionid) {
    debugging('forum_tp_get_discussion_read_records() is deprecated and will not be replaced.', DEBUG_DEVELOPER);

    global $DB;
    $select = 'userid = ? AND discussionid = ?';
    $fields = 'postid, firstread, lastread';
    return $DB->get_records_select('forum_read', $select, array($userid, $discussionid), '', $fields);
}



function forum_user_enrolled($cp) {
    debugging('forum_user_enrolled() is deprecated. Please use forum_user_role_assigned instead.', DEBUG_DEVELOPER);
    global $DB;

            
    $sql = "SELECT f.id
              FROM {forum} f
         LEFT JOIN {forum_subscriptions} fs ON (fs.forum = f.id AND fs.userid = :userid)
             WHERE f.course = :courseid AND f.forcesubscribe = :initial AND fs.id IS NULL";
    $params = array('courseid'=>$cp->courseid, 'userid'=>$cp->userid, 'initial'=>FORUM_INITIALSUBSCRIBE);

    $forums = $DB->get_records_sql($sql, $params);
    foreach ($forums as $forum) {
        \mod_forum\subscriptions::subscribe_user($cp->userid, $forum);
    }
}




function forum_user_can_view_post($post, $course, $cm, $forum, $discussion, $user=null){
    debugging('forum_user_can_view_post() is deprecated. Please use forum_user_can_see_post() instead.', DEBUG_DEVELOPER);
    return forum_user_can_see_post($forum, $discussion, $post, $user, $cm);
}




define('FORUM_TRACKING_ON', 2);


function forum_shorten_post($message) {
    throw new coding_exception('forum_shorten_post() can not be used any more. Please use shorten_text($message, $CFG->forum_shortpost) instead.');
}



function forum_is_subscribed($userid, $forum) {
    global $DB;
    debugging("forum_is_subscribed() has been deprecated, please use \\mod_forum\\subscriptions::is_subscribed() instead.",
            DEBUG_DEVELOPER);

        if (is_numeric($forum)) {
        $forum = $DB->get_record('forum', array('id' => $forum));
    }

    return mod_forum\subscriptions::is_subscribed($userid, $forum);
}


function forum_subscribe($userid, $forumid, $context = null, $userrequest = false) {
    global $DB;
    debugging("forum_subscribe() has been deprecated, please use \\mod_forum\\subscriptions::subscribe_user() instead.",
            DEBUG_DEVELOPER);

        $forum = $DB->get_record('forum', array('id' => $forumid));
    \mod_forum\subscriptions::subscribe_user($userid, $forum, $context, $userrequest);
}


function forum_unsubscribe($userid, $forumid, $context = null, $userrequest = false) {
    global $DB;
    debugging("forum_unsubscribe() has been deprecated, please use \\mod_forum\\subscriptions::unsubscribe_user() instead.",
            DEBUG_DEVELOPER);

        $forum = $DB->get_record('forum', array('id' => $forumid));
    \mod_forum\subscriptions::unsubscribe_user($userid, $forum, $context, $userrequest);
}


function forum_subscribed_users($course, $forum, $groupid = 0, $context = null, $fields = null) {
    debugging("forum_subscribed_users() has been deprecated, please use \\mod_forum\\subscriptions::fetch_subscribed_users() instead.",
            DEBUG_DEVELOPER);

    \mod_forum\subscriptions::fetch_subscribed_users($forum, $groupid, $context, $fields);
}


function forum_is_forcesubscribed($forum) {
    debugging("forum_is_forcesubscribed() has been deprecated, please use \\mod_forum\\subscriptions::is_forcesubscribed() instead.",
            DEBUG_DEVELOPER);

    global $DB;
    if (!isset($forum->forcesubscribe)) {
       $forum = $DB->get_field('forum', 'forcesubscribe', array('id' => $forum));
    }

    return \mod_forum\subscriptions::is_forcesubscribed($forum);
}


function forum_forcesubscribe($forumid, $value = 1) {
    debugging("forum_forcesubscribe() has been deprecated, please use \\mod_forum\\subscriptions::set_subscription_mode() instead.",
            DEBUG_DEVELOPER);

    return \mod_forum\subscriptions::set_subscription_mode($forumid, $value);
}


function forum_get_forcesubscribed($forum) {
    debugging("forum_get_forcesubscribed() has been deprecated, please use \\mod_forum\\subscriptions::get_subscription_mode() instead.",
            DEBUG_DEVELOPER);

    global $DB;
    if (!isset($forum->forcesubscribe)) {
       $forum = $DB->get_field('forum', 'forcesubscribe', array('id' => $forum));
    }

    return \mod_forum\subscriptions::get_subscription_mode($forumid, $value);
}


function forum_get_subscribed_forums($course) {
    debugging("forum_get_subscribed_forums() has been deprecated, please see " .
              "\\mod_forum\\subscriptions::is_subscribed::() " .
              " and \\mod_forum\\subscriptions::fill_subscription_cache_for_course instead.",
              DEBUG_DEVELOPER);

    global $USER, $CFG, $DB;
    $sql = "SELECT f.id
              FROM {forum} f
                   LEFT JOIN {forum_subscriptions} fs ON (fs.forum = f.id AND fs.userid = ?)
             WHERE f.course = ?
                   AND f.forcesubscribe <> ".FORUM_DISALLOWSUBSCRIBE."
                   AND (f.forcesubscribe = ".FORUM_FORCESUBSCRIBE." OR fs.id IS NOT NULL)";
    if ($subscribed = $DB->get_records_sql($sql, array($USER->id, $course->id))) {
        foreach ($subscribed as $s) {
            $subscribed[$s->id] = $s->id;
        }
        return $subscribed;
    } else {
        return array();
    }
}


function forum_get_optional_subscribed_forums() {
    debugging("forum_get_optional_subscribed_forums() has been deprecated, please use \\mod_forum\\subscriptions::get_unsubscribable_forums() instead.",
            DEBUG_DEVELOPER);

    return \mod_forum\subscriptions::get_unsubscribable_forums();
}


function forum_get_potential_subscribers($forumcontext, $groupid, $fields, $sort = '') {
    debugging("forum_get_potential_subscribers() has been deprecated, please use \\mod_forum\\subscriptions::get_potential_subscribers() instead.",
            DEBUG_DEVELOPER);

    \mod_forum\subscriptions::get_potential_subscribers($forumcontext, $groupid, $fields, $sort);
}


function forum_make_mail_text($course, $cm, $forum, $discussion, $post, $userfrom, $userto, $bare = false, $replyaddress = null) {
    global $PAGE;
    $renderable = new \mod_forum\output\forum_post_email(
        $course,
        $cm,
        $forum,
        $discussion,
        $post,
        $userfrom,
        $userto,
        forum_user_can_post($forum, $discussion, $userto, $cm, $course)
        );

    $modcontext = context_module::instance($cm->id);
    $renderable->viewfullnames = has_capability('moodle/site:viewfullnames', $modcontext, $userto->id);

    if ($bare) {
        $renderer = $PAGE->get_renderer('mod_forum', 'emaildigestfull', 'textemail');
    } else {
        $renderer = $PAGE->get_renderer('mod_forum', 'email', 'textemail');
    }

    debugging("forum_make_mail_text() has been deprecated, please use the \mod_forum\output\forum_post_email renderable instead.",
            DEBUG_DEVELOPER);

    return $renderer->render($renderable);
}


function forum_make_mail_html($course, $cm, $forum, $discussion, $post, $userfrom, $userto, $replyaddress = null) {
    return forum_make_mail_post($course,
        $cm,
        $forum,
        $discussion,
        $post,
        $userfrom,
        $userto,
        forum_user_can_post($forum, $discussion, $userto, $cm, $course)
    );
}


function forum_make_mail_post($course, $cm, $forum, $discussion, $post, $userfrom, $userto,
                              $ownpost=false, $reply=false, $link=false, $rate=false, $footer="") {
    global $PAGE;
    $renderable = new \mod_forum\output\forum_post_email(
        $course,
        $cm,
        $forum,
        $discussion,
        $post,
        $userfrom,
        $userto,
        $reply);

    $modcontext = context_module::instance($cm->id);
    $renderable->viewfullnames = has_capability('moodle/site:viewfullnames', $modcontext, $userto->id);

        $renderer = $PAGE->get_renderer('mod_forum', 'email', 'htmlemail');

    debugging("forum_make_mail_post() has been deprecated, please use the \mod_forum\output\forum_post_email renderable instead.",
            DEBUG_DEVELOPER);

    return $renderer->render($renderable);
}
