<?php



defined('MOODLE_INTERNAL') || die();


class mod_forum_observer {

    
    public static function user_enrolment_deleted(\core\event\user_enrolment_deleted $event) {
        global $DB;

                        $cp = (object)$event->other['userenrolment'];
        if ($cp->lastenrol) {
            if (!$forums = $DB->get_records('forum', array('course' => $cp->courseid), '', 'id')) {
                return;
            }
            list($forumselect, $params) = $DB->get_in_or_equal(array_keys($forums), SQL_PARAMS_NAMED);
            $params['userid'] = $cp->userid;

            $DB->delete_records_select('forum_digests', 'userid = :userid AND forum '.$forumselect, $params);
            $DB->delete_records_select('forum_subscriptions', 'userid = :userid AND forum '.$forumselect, $params);
            $DB->delete_records_select('forum_track_prefs', 'userid = :userid AND forumid '.$forumselect, $params);
            $DB->delete_records_select('forum_read', 'userid = :userid AND forumid '.$forumselect, $params);
        }
    }

    
    public static function role_assigned(\core\event\role_assigned $event) {
        global $CFG, $DB;

        $context = context::instance_by_id($event->contextid, MUST_EXIST);

                        if ($context->contextlevel != CONTEXT_COURSE) {
            return;
        }

                require_once($CFG->dirroot . '/mod/forum/lib.php');

        $userid = $event->relateduserid;
        $sql = "SELECT f.id, f.course as course, cm.id AS cmid, f.forcesubscribe
                  FROM {forum} f
                  JOIN {course_modules} cm ON (cm.instance = f.id)
                  JOIN {modules} m ON (m.id = cm.module)
             LEFT JOIN {forum_subscriptions} fs ON (fs.forum = f.id AND fs.userid = :userid)
                 WHERE f.course = :courseid
                   AND f.forcesubscribe = :initial
                   AND m.name = 'forum'
                   AND fs.id IS NULL";
        $params = array('courseid' => $context->instanceid, 'userid' => $userid, 'initial' => FORUM_INITIALSUBSCRIBE);

        $forums = $DB->get_records_sql($sql, $params);
        foreach ($forums as $forum) {
                        $modcontext = context_module::instance($forum->cmid);
            if (has_capability('mod/forum:allowforcesubscribe', $modcontext, $userid)) {
                \mod_forum\subscriptions::subscribe_user($userid, $forum, $modcontext);
            }
        }
    }

    
    public static function course_module_created(\core\event\course_module_created $event) {
        global $CFG;

        if ($event->other['modulename'] === 'forum') {
                        require_once($CFG->dirroot . '/mod/forum/lib.php');

            $forum = $event->get_record_snapshot('forum', $event->other['instanceid']);
            forum_instance_created($event->get_context(), $forum);
        }
    }
}
