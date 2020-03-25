<?php



namespace mod_forum;

defined('MOODLE_INTERNAL') || die();


class subscriptions {

    
    const FORUM_DISCUSSION_UNSUBSCRIBED = -1;

    
    protected static $forumcache = array();

    
    protected static $fetchedforums = array();

    
    protected static $forumdiscussioncache = array();

    
    protected static $discussionfetchedforums = array();

    
    public static function is_subscribed($userid, $forum, $discussionid = null, $cm = null) {
                if (self::is_forcesubscribed($forum)) {
            if (!$cm) {
                $cm = get_fast_modinfo($forum->course)->instances['forum'][$forum->id];
            }
            if (has_capability('mod/forum:allowforcesubscribe', \context_module::instance($cm->id), $userid)) {
                return true;
            }
        }

        if ($discussionid === null) {
            return self::is_subscribed_to_forum($userid, $forum);
        }

        $subscriptions = self::fetch_discussion_subscription($forum->id, $userid);

                if (isset($subscriptions[$discussionid])) {
            return ($subscriptions[$discussionid] != self::FORUM_DISCUSSION_UNSUBSCRIBED);
        }

        return self::is_subscribed_to_forum($userid, $forum);
    }

    
    protected static function is_subscribed_to_forum($userid, $forum) {
        return self::fetch_subscription_cache($forum->id, $userid);
    }

    
    public static function is_forcesubscribed($forum) {
        return ($forum->forcesubscribe == FORUM_FORCESUBSCRIBE);
    }

    
    public static function subscription_disabled($forum) {
        return ($forum->forcesubscribe == FORUM_DISALLOWSUBSCRIBE);
    }

    
    public static function is_subscribable($forum) {
        return (!\mod_forum\subscriptions::is_forcesubscribed($forum) &&
                !\mod_forum\subscriptions::subscription_disabled($forum));
    }

    
    public static function set_subscription_mode($forumid, $status = 1) {
        global $DB;
        return $DB->set_field("forum", "forcesubscribe", $status, array("id" => $forumid));
    }

    
    public static function get_subscription_mode($forum) {
        return $forum->forcesubscribe;
    }

    
    public static function get_unsubscribable_forums() {
        global $USER, $DB;

                $courses = enrol_get_my_courses();
        if (empty($courses)) {
            return array();
        }

        $courseids = array();
        foreach($courses as $course) {
            $courseids[] = $course->id;
        }
        list($coursesql, $courseparams) = $DB->get_in_or_equal($courseids, SQL_PARAMS_NAMED, 'c');

                                $sql = "SELECT f.id, cm.id as cm, cm.visible, f.course
                FROM {forum} f
                JOIN {course_modules} cm ON cm.instance = f.id
                JOIN {modules} m ON m.name = :modulename AND m.id = cm.module
                LEFT JOIN {forum_subscriptions} fs ON (fs.forum = f.id AND fs.userid = :userid)
                WHERE f.forcesubscribe <> :forcesubscribe
                AND fs.id IS NOT NULL
                AND cm.course
                $coursesql";
        $params = array_merge($courseparams, array(
            'modulename'=>'forum',
            'userid' => $USER->id,
            'forcesubscribe' => FORUM_FORCESUBSCRIBE,
        ));
        $forums = $DB->get_recordset_sql($sql, $params);

        $unsubscribableforums = array();
        foreach($forums as $forum) {
            if (empty($forum->visible)) {
                                $context = \context_module::instance($forum->cm);
                if (!has_capability('moodle/course:viewhiddenactivities', $context)) {
                                        continue;
                }
            }

            $unsubscribableforums[] = $forum;
        }
        $forums->close();

        return $unsubscribableforums;
    }

    
    public static function get_potential_subscribers($context, $groupid, $fields, $sort = '') {
        global $DB;

                list($esql, $params) = get_enrolled_sql($context, 'mod/forum:allowforcesubscribe', $groupid, true);
        if (!$sort) {
            list($sort, $sortparams) = users_order_by_sql('u');
            $params = array_merge($params, $sortparams);
        }

        $sql = "SELECT $fields
                FROM {user} u
                JOIN ($esql) je ON je.id = u.id
            ORDER BY $sort";

        return $DB->get_records_sql($sql, $params);
    }

    
    public static function fetch_subscription_cache($forumid, $userid) {
        if (isset(self::$forumcache[$userid]) && isset(self::$forumcache[$userid][$forumid])) {
            return self::$forumcache[$userid][$forumid];
        }
        self::fill_subscription_cache($forumid, $userid);

        if (!isset(self::$forumcache[$userid]) || !isset(self::$forumcache[$userid][$forumid])) {
            return false;
        }

        return self::$forumcache[$userid][$forumid];
    }

    
    public static function fill_subscription_cache($forumid, $userid = null) {
        global $DB;

        if (!isset(self::$fetchedforums[$forumid])) {
                        if (isset($userid)) {
                if (!isset(self::$forumcache[$userid])) {
                    self::$forumcache[$userid] = array();
                }

                if (!isset(self::$forumcache[$userid][$forumid])) {
                    if ($DB->record_exists('forum_subscriptions', array(
                        'userid' => $userid,
                        'forum' => $forumid,
                    ))) {
                        self::$forumcache[$userid][$forumid] = true;
                    } else {
                        self::$forumcache[$userid][$forumid] = false;
                    }
                }
            } else {
                $subscriptions = $DB->get_recordset('forum_subscriptions', array(
                    'forum' => $forumid,
                ), '', 'id, userid');
                foreach ($subscriptions as $id => $data) {
                    if (!isset(self::$forumcache[$data->userid])) {
                        self::$forumcache[$data->userid] = array();
                    }
                    self::$forumcache[$data->userid][$forumid] = true;
                }
                self::$fetchedforums[$forumid] = true;
                $subscriptions->close();
            }
        }
    }

    
    public static function fill_subscription_cache_for_course($courseid, $userid) {
        global $DB;

        if (!isset(self::$forumcache[$userid])) {
            self::$forumcache[$userid] = array();
        }

        $sql = "SELECT
                    f.id AS forumid,
                    s.id AS subscriptionid
                FROM {forum} f
                LEFT JOIN {forum_subscriptions} s ON (s.forum = f.id AND s.userid = :userid)
                WHERE f.course = :course
                AND f.forcesubscribe <> :subscriptionforced";

        $subscriptions = $DB->get_recordset_sql($sql, array(
            'course' => $courseid,
            'userid' => $userid,
            'subscriptionforced' => FORUM_FORCESUBSCRIBE,
        ));

        foreach ($subscriptions as $id => $data) {
            self::$forumcache[$userid][$id] = !empty($data->subscriptionid);
        }
        $subscriptions->close();
    }

    
    public static function fetch_subscribed_users($forum, $groupid = 0, $context = null, $fields = null,
            $includediscussionsubscriptions = false) {
        global $CFG, $DB;

        if (empty($fields)) {
            $allnames = get_all_user_name_fields(true, 'u');
            $fields ="u.id,
                      u.username,
                      $allnames,
                      u.maildisplay,
                      u.mailformat,
                      u.maildigest,
                      u.imagealt,
                      u.email,
                      u.emailstop,
                      u.city,
                      u.country,
                      u.lastaccess,
                      u.lastlogin,
                      u.picture,
                      u.timezone,
                      u.theme,
                      u.lang,
                      u.trackforums,
                      u.mnethostid";
        }

                $context = forum_get_context($forum->id, $context);

        if (self::is_forcesubscribed($forum)) {
            $results = \mod_forum\subscriptions::get_potential_subscribers($context, $groupid, $fields, "u.email ASC");

        } else {
                        list($esql, $params) = get_enrolled_sql($context, '', $groupid, true);
            $params['forumid'] = $forum->id;

            if ($includediscussionsubscriptions) {
                $params['sforumid'] = $forum->id;
                $params['dsforumid'] = $forum->id;
                $params['unsubscribed'] = self::FORUM_DISCUSSION_UNSUBSCRIBED;

                $sql = "SELECT $fields
                        FROM (
                            SELECT userid FROM {forum_subscriptions} s
                            WHERE
                                s.forum = :sforumid
                                UNION
                            SELECT userid FROM {forum_discussion_subs} ds
                            WHERE
                                ds.forum = :dsforumid AND ds.preference <> :unsubscribed
                        ) subscriptions
                        JOIN {user} u ON u.id = subscriptions.userid
                        JOIN ($esql) je ON je.id = u.id
                        ORDER BY u.email ASC";

            } else {
                $sql = "SELECT $fields
                        FROM {user} u
                        JOIN ($esql) je ON je.id = u.id
                        JOIN {forum_subscriptions} s ON s.userid = u.id
                        WHERE
                          s.forum = :forumid
                        ORDER BY u.email ASC";
            }
            $results = $DB->get_records_sql($sql, $params);
        }

                unset($results[$CFG->siteguest]);

                $cm = get_coursemodule_from_instance('forum', $forum->id, $forum->course);
        $modinfo = get_fast_modinfo($forum->course);
        $info = new \core_availability\info_module($modinfo->get_cm($cm->id));
        $results = $info->filter_user_list($results);

        return $results;
    }

    
    public static function fetch_discussion_subscription($forumid, $userid = null) {
        self::fill_discussion_subscription_cache($forumid, $userid);

        if (!isset(self::$forumdiscussioncache[$userid]) || !isset(self::$forumdiscussioncache[$userid][$forumid])) {
            return array();
        }

        return self::$forumdiscussioncache[$userid][$forumid];
    }

    
    public static function fill_discussion_subscription_cache($forumid, $userid = null) {
        global $DB;

        if (!isset(self::$discussionfetchedforums[$forumid])) {
                        if (isset($userid)) {
                if (!isset(self::$forumdiscussioncache[$userid])) {
                    self::$forumdiscussioncache[$userid] = array();
                }

                if (!isset(self::$forumdiscussioncache[$userid][$forumid])) {
                    $subscriptions = $DB->get_recordset('forum_discussion_subs', array(
                        'userid' => $userid,
                        'forum' => $forumid,
                    ), null, 'id, discussion, preference');
                    foreach ($subscriptions as $id => $data) {
                        self::add_to_discussion_cache($forumid, $userid, $data->discussion, $data->preference);
                    }
                    $subscriptions->close();
                }
            } else {
                $subscriptions = $DB->get_recordset('forum_discussion_subs', array(
                    'forum' => $forumid,
                ), null, 'id, userid, discussion, preference');
                foreach ($subscriptions as $id => $data) {
                    self::add_to_discussion_cache($forumid, $data->userid, $data->discussion, $data->preference);
                }
                self::$discussionfetchedforums[$forumid] = true;
                $subscriptions->close();
            }
        }
    }

    
    protected static function add_to_discussion_cache($forumid, $userid, $discussion, $preference) {
        if (!isset(self::$forumdiscussioncache[$userid])) {
            self::$forumdiscussioncache[$userid] = array();
        }

        if (!isset(self::$forumdiscussioncache[$userid][$forumid])) {
            self::$forumdiscussioncache[$userid][$forumid] = array();
        }

        self::$forumdiscussioncache[$userid][$forumid][$discussion] = $preference;
    }

    
    public static function reset_discussion_cache() {
        self::$forumdiscussioncache = array();
        self::$discussionfetchedforums = array();
    }

    
    public static function reset_forum_cache() {
        self::$forumcache = array();
        self::$fetchedforums = array();
    }

    
    public static function subscribe_user($userid, $forum, $context = null, $userrequest = false) {
        global $DB;

        if (self::is_subscribed($userid, $forum)) {
            return true;
        }

        $sub = new \stdClass();
        $sub->userid  = $userid;
        $sub->forum = $forum->id;

        $result = $DB->insert_record("forum_subscriptions", $sub);

        if ($userrequest) {
            $discussionsubscriptions = $DB->get_recordset('forum_discussion_subs', array('userid' => $userid, 'forum' => $forum->id));
            $DB->delete_records_select('forum_discussion_subs',
                    'userid = :userid AND forum = :forumid AND preference <> :preference', array(
                        'userid' => $userid,
                        'forumid' => $forum->id,
                        'preference' => self::FORUM_DISCUSSION_UNSUBSCRIBED,
                    ));

                                    if (isset(self::$forumdiscussioncache[$userid]) && isset(self::$forumdiscussioncache[$userid][$forum->id])) {
                foreach (self::$forumdiscussioncache[$userid][$forum->id] as $discussionid => $preference) {
                    if ($preference != self::FORUM_DISCUSSION_UNSUBSCRIBED) {
                        unset(self::$forumdiscussioncache[$userid][$forum->id][$discussionid]);
                    }
                }
            }
        }

                self::$forumcache[$userid][$forum->id] = true;

        $context = forum_get_context($forum->id, $context);
        $params = array(
            'context' => $context,
            'objectid' => $result,
            'relateduserid' => $userid,
            'other' => array('forumid' => $forum->id),

        );
        $event  = event\subscription_created::create($params);
        if ($userrequest && $discussionsubscriptions) {
            foreach ($discussionsubscriptions as $subscription) {
                $event->add_record_snapshot('forum_discussion_subs', $subscription);
            }
            $discussionsubscriptions->close();
        }
        $event->trigger();

        return $result;
    }

    
    public static function unsubscribe_user($userid, $forum, $context = null, $userrequest = false) {
        global $DB;

        $sqlparams = array(
            'userid' => $userid,
            'forum' => $forum->id,
        );
        $DB->delete_records('forum_digests', $sqlparams);

        if ($forumsubscription = $DB->get_record('forum_subscriptions', $sqlparams)) {
            $DB->delete_records('forum_subscriptions', array('id' => $forumsubscription->id));

            if ($userrequest) {
                $discussionsubscriptions = $DB->get_recordset('forum_discussion_subs', $sqlparams);
                $DB->delete_records('forum_discussion_subs',
                        array('userid' => $userid, 'forum' => $forum->id, 'preference' => self::FORUM_DISCUSSION_UNSUBSCRIBED));

                                if (isset(self::$forumdiscussioncache[$userid]) && isset(self::$forumdiscussioncache[$userid][$forum->id])) {
                    self::$forumdiscussioncache[$userid][$forum->id] = array();
                }
            }

                        self::$forumcache[$userid][$forum->id] = false;

            $context = forum_get_context($forum->id, $context);
            $params = array(
                'context' => $context,
                'objectid' => $forumsubscription->id,
                'relateduserid' => $userid,
                'other' => array('forumid' => $forum->id),

            );
            $event = event\subscription_deleted::create($params);
            $event->add_record_snapshot('forum_subscriptions', $forumsubscription);
            if ($userrequest && $discussionsubscriptions) {
                foreach ($discussionsubscriptions as $subscription) {
                    $event->add_record_snapshot('forum_discussion_subs', $subscription);
                }
                $discussionsubscriptions->close();
            }
            $event->trigger();
        }

        return true;
    }

    
    public static function subscribe_user_to_discussion($userid, $discussion, $context = null) {
        global $DB;

                $subscription = $DB->get_record('forum_discussion_subs', array('userid' => $userid, 'discussion' => $discussion->id));
        if ($subscription) {
            if ($subscription->preference != self::FORUM_DISCUSSION_UNSUBSCRIBED) {
                                return false;
            }
        }
                if ($DB->record_exists('forum_subscriptions', array('userid' => $userid, 'forum' => $discussion->forum))) {
            if ($subscription && $subscription->preference == self::FORUM_DISCUSSION_UNSUBSCRIBED) {
                                $DB->delete_records('forum_discussion_subs', array('id' => $subscription->id));
                unset(self::$forumdiscussioncache[$userid][$discussion->forum][$discussion->id]);
            } else {
                                return false;
            }
        } else {
            if ($subscription) {
                $subscription->preference = time();
                $DB->update_record('forum_discussion_subs', $subscription);
            } else {
                $subscription = new \stdClass();
                $subscription->userid  = $userid;
                $subscription->forum = $discussion->forum;
                $subscription->discussion = $discussion->id;
                $subscription->preference = time();

                $subscription->id = $DB->insert_record('forum_discussion_subs', $subscription);
                self::$forumdiscussioncache[$userid][$discussion->forum][$discussion->id] = $subscription->preference;
            }
        }

        $context = forum_get_context($discussion->forum, $context);
        $params = array(
            'context' => $context,
            'objectid' => $subscription->id,
            'relateduserid' => $userid,
            'other' => array(
                'forumid' => $discussion->forum,
                'discussion' => $discussion->id,
            ),

        );
        $event  = event\discussion_subscription_created::create($params);
        $event->trigger();

        return true;
    }
    
    public static function unsubscribe_user_from_discussion($userid, $discussion, $context = null) {
        global $DB;

                $subscription = $DB->get_record('forum_discussion_subs', array('userid' => $userid, 'discussion' => $discussion->id));
        if ($subscription) {
            if ($subscription->preference == self::FORUM_DISCUSSION_UNSUBSCRIBED) {
                                return false;
            }
        }
                if (!$DB->record_exists('forum_subscriptions', array('userid' => $userid, 'forum' => $discussion->forum))) {
            if ($subscription && $subscription->preference != self::FORUM_DISCUSSION_UNSUBSCRIBED) {
                                $DB->delete_records('forum_discussion_subs', array('id' => $subscription->id));
                unset(self::$forumdiscussioncache[$userid][$discussion->forum][$discussion->id]);
            } else {
                                return false;
            }
        } else {
            if ($subscription) {
                $subscription->preference = self::FORUM_DISCUSSION_UNSUBSCRIBED;
                $DB->update_record('forum_discussion_subs', $subscription);
            } else {
                $subscription = new \stdClass();
                $subscription->userid  = $userid;
                $subscription->forum = $discussion->forum;
                $subscription->discussion = $discussion->id;
                $subscription->preference = self::FORUM_DISCUSSION_UNSUBSCRIBED;

                $subscription->id = $DB->insert_record('forum_discussion_subs', $subscription);
            }
            self::$forumdiscussioncache[$userid][$discussion->forum][$discussion->id] = $subscription->preference;
        }

        $context = forum_get_context($discussion->forum, $context);
        $params = array(
            'context' => $context,
            'objectid' => $subscription->id,
            'relateduserid' => $userid,
            'other' => array(
                'forumid' => $discussion->forum,
                'discussion' => $discussion->id,
            ),

        );
        $event  = event\discussion_subscription_deleted::create($params);
        $event->trigger();

        return true;
    }

}
