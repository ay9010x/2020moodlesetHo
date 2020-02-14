<?php



defined('MOODLE_INTERNAL') || die;

require_once($CFG->libdir . '/externallib.php');
require_once($CFG->dirroot . '/mod/chat/lib.php');


class mod_chat_external extends external_api {

    
    public static function login_user_parameters() {
        return new external_function_parameters(
            array(
                'chatid' => new external_value(PARAM_INT, 'chat instance id'),
                'groupid' => new external_value(PARAM_INT, 'group id, 0 means that the function will determine the user group',
                                                VALUE_DEFAULT, 0),
            )
        );
    }

    
    public static function login_user($chatid, $groupid = 0) {
        global $DB;

        $params = self::validate_parameters(self::login_user_parameters(),
                                            array(
                                                'chatid' => $chatid,
                                                'groupid' => $groupid
                                            ));
        $warnings = array();

                $chat = $DB->get_record('chat', array('id' => $params['chatid']), '*', MUST_EXIST);
        list($course, $cm) = get_course_and_cm_from_instance($chat, 'chat');

        $context = context_module::instance($cm->id);
        self::validate_context($context);

        require_capability('mod/chat:chat', $context);

        if (!empty($params['groupid'])) {
            $groupid = $params['groupid'];
                        if (!groups_group_visible($groupid, $course, $cm)) {
                throw new moodle_exception('notingroup');
            }
        } else {
                        if ($groupmode = groups_get_activity_groupmode($cm)) {
                $groupid = groups_get_activity_group($cm);
                                if (!groups_group_visible($groupid, $course, $cm)) {
                    throw new moodle_exception('notingroup');
                }
            } else {
                $groupid = 0;
            }
        }

                        if (!$chatsid = chat_login_user($chat->id, 'ajax', $groupid, $course)) {
            throw moodle_exception('cantlogin', 'chat');
        }

        $result = array();
        $result['chatsid'] = $chatsid;
        $result['warnings'] = $warnings;
        return $result;
    }

    
    public static function login_user_returns() {
        return new external_single_structure(
            array(
                'chatsid' => new external_value(PARAM_ALPHANUM, 'unique chat session id'),
                'warnings' => new external_warnings()
            )
        );
    }

    
    public static function get_chat_users_parameters() {
        return new external_function_parameters(
            array(
                'chatsid' => new external_value(PARAM_ALPHANUM, 'chat session id (obtained via mod_chat_login_user)')
            )
        );
    }

    
    public static function get_chat_users($chatsid) {
        global $DB, $PAGE;

        $params = self::validate_parameters(self::get_chat_users_parameters(),
                                            array(
                                                'chatsid' => $chatsid
                                            ));
        $warnings = array();

                if (!$chatuser = $DB->get_record('chat_users', array('sid' => $params['chatsid']))) {
            throw new moodle_exception('notlogged', 'chat');
        }
        $chat = $DB->get_record('chat', array('id' => $chatuser->chatid), '*', MUST_EXIST);
        list($course, $cm) = get_course_and_cm_from_instance($chat, 'chat');

        $context = context_module::instance($cm->id);
        self::validate_context($context);

        require_capability('mod/chat:chat', $context);

                chat_delete_old_users();

        $users = chat_get_users($chatuser->chatid, $chatuser->groupid, $cm->groupingid);
        $returnedusers = array();

        foreach ($users as $user) {

            $userpicture = new user_picture($user);
            $userpicture->size = 1;             $profileimageurl = $userpicture->get_url($PAGE)->out(false);

            $returnedusers[] = array(
                'id' => $user->id,
                'fullname' => fullname($user),
                'profileimageurl' => $profileimageurl
            );
        }

        $result = array();
        $result['users'] = $returnedusers;
        $result['warnings'] = $warnings;
        return $result;
    }

    
    public static function get_chat_users_returns() {
        return new external_single_structure(
            array(
                'users' => new external_multiple_structure(
                    new external_single_structure(
                        array(
                            'id' => new external_value(PARAM_INT, 'user id'),
                            'fullname' => new external_value(PARAM_NOTAGS, 'user full name'),
                            'profileimageurl' => new external_value(PARAM_URL, 'user picture URL'),
                        )
                    ),
                    'list of users'
                ),
                'warnings' => new external_warnings()
            )
        );
    }

    
    public static function send_chat_message_parameters() {
        return new external_function_parameters(
            array(
                'chatsid' => new external_value(PARAM_ALPHANUM, 'chat session id (obtained via mod_chat_login_user)'),
                'messagetext' => new external_value(PARAM_RAW, 'the message text'),
                'beepid' => new external_value(PARAM_RAW, 'the beep id', VALUE_DEFAULT, ''),

            )
        );
    }

    
    public static function send_chat_message($chatsid, $messagetext, $beepid = '') {
        global $DB;

        $params = self::validate_parameters(self::send_chat_message_parameters(),
                                            array(
                                                'chatsid' => $chatsid,
                                                'messagetext' => $messagetext,
                                                'beepid' => $beepid
                                            ));
        $warnings = array();

                if (!$chatuser = $DB->get_record('chat_users', array('sid' => $params['chatsid']))) {
            throw new moodle_exception('notlogged', 'chat');
        }
        $chat = $DB->get_record('chat', array('id' => $chatuser->chatid), '*', MUST_EXIST);
        list($course, $cm) = get_course_and_cm_from_instance($chat, 'chat');

        $context = context_module::instance($cm->id);
        self::validate_context($context);

        require_capability('mod/chat:chat', $context);

        $chatmessage = clean_text($params['messagetext'], FORMAT_MOODLE);

        if (!empty($params['beepid'])) {
            $chatmessage = 'beep ' . $params['beepid'];
        }

        if (!empty($chatmessage)) {
                        $messageid = chat_send_chatmessage($chatuser, $chatmessage, 0, $cm);
                        $chatuser->lastmessageping = time() - 2;
            $DB->update_record('chat_users', $chatuser);
        } else {
            $messageid = 0;
        }

        $result = array();
        $result['messageid'] = $messageid;
        $result['warnings'] = $warnings;
        return $result;
    }

    
    public static function send_chat_message_returns() {
        return new external_single_structure(
            array(
                'messageid' => new external_value(PARAM_INT, 'message sent id'),
                'warnings' => new external_warnings()
            )
        );
    }

    
    public static function get_chat_latest_messages_parameters() {
        return new external_function_parameters(
            array(
                'chatsid' => new external_value(PARAM_ALPHANUM, 'chat session id (obtained via mod_chat_login_user)'),
                'chatlasttime' => new external_value(PARAM_INT, 'last time messages were retrieved (epoch time)', VALUE_DEFAULT, 0)
            )
        );
    }

    
    public static function get_chat_latest_messages($chatsid, $chatlasttime = 0) {
        global $DB, $CFG;

        $params = self::validate_parameters(self::get_chat_latest_messages_parameters(),
                                            array(
                                                'chatsid' => $chatsid,
                                                'chatlasttime' => $chatlasttime
                                            ));
        $warnings = array();

                if (!$chatuser = $DB->get_record('chat_users', array('sid' => $params['chatsid']))) {
            throw new moodle_exception('notlogged', 'chat');
        }
        $chat = $DB->get_record('chat', array('id' => $chatuser->chatid), '*', MUST_EXIST);
        list($course, $cm) = get_course_and_cm_from_instance($chat, 'chat');

        $context = context_module::instance($cm->id);
        self::validate_context($context);

        require_capability('mod/chat:chat', $context);

        $chatlasttime = $params['chatlasttime'];
        if ((time() - $chatlasttime) > $CFG->chat_old_ping) {
            chat_delete_old_users();
        }

                if ($chatlasttime == 0) {
            $chatlasttime = time() - $CFG->chat_old_ping;
        }

        if ($latestmessage = chat_get_latest_message($chatuser->chatid, $chatuser->groupid)) {
            $chatnewlasttime = $latestmessage->timestamp;
        } else {
            $chatnewlasttime = 0;
        }

        $messages = chat_get_latest_messages($chatuser, $chatlasttime);
        $returnedmessages = array();

        foreach ($messages as $message) {

                        list($messageformatted, $format) = external_format_text($message->message, FORMAT_MOODLE, $context->id, 'mod_chat',
                                                                    '', 0);

            $returnedmessages[] = array(
                'id' => $message->id,
                'userid' => $message->userid,
                'system' => (bool) $message->system,
                'message' => $messageformatted,
                'timestamp' => $message->timestamp,
            );
        }

                $DB->set_field('chat_users', 'lastping', time(), array('id' => $chatuser->id));

        $result = array();
        $result['messages'] = $returnedmessages;
        $result['chatnewlasttime'] = $chatnewlasttime;
        $result['warnings'] = $warnings;
        return $result;
    }

    
    public static function get_chat_latest_messages_returns() {
        return new external_single_structure(
            array(
                'messages' => new external_multiple_structure(
                    new external_single_structure(
                        array(
                            'id' => new external_value(PARAM_INT, 'message id'),
                            'userid' => new external_value(PARAM_INT, 'user id'),
                            'system' => new external_value(PARAM_BOOL, 'true if is a system message (like user joined)'),
                            'message' => new external_value(PARAM_RAW, 'message text'),
                            'timestamp' => new external_value(PARAM_INT, 'timestamp for the message'),
                        )
                    ),
                    'list of users'
                ),
                'chatnewlasttime' => new external_value(PARAM_INT, 'new last time'),
                'warnings' => new external_warnings()
            )
        );
    }

    
    public static function view_chat_parameters() {
        return new external_function_parameters(
            array(
                'chatid' => new external_value(PARAM_INT, 'chat instance id')
            )
        );
    }

    
    public static function view_chat($chatid) {
        global $DB, $CFG;

        $params = self::validate_parameters(self::view_chat_parameters(),
                                            array(
                                                'chatid' => $chatid
                                            ));
        $warnings = array();

                $chat = $DB->get_record('chat', array('id' => $params['chatid']), '*', MUST_EXIST);
        list($course, $cm) = get_course_and_cm_from_instance($chat, 'chat');

        $context = context_module::instance($cm->id);
        self::validate_context($context);

        require_capability('mod/chat:chat', $context);

                chat_view($chat, $course, $cm, $context);

        $result = array();
        $result['status'] = true;
        $result['warnings'] = $warnings;
        return $result;
    }

    
    public static function view_chat_returns() {
        return new external_single_structure(
            array(
                'status' => new external_value(PARAM_BOOL, 'status: true if success'),
                'warnings' => new external_warnings()
            )
        );
    }


    
    public static function get_chats_by_courses_parameters() {
        return new external_function_parameters (
            array(
                'courseids' => new external_multiple_structure(
                    new external_value(PARAM_INT, 'course id'), 'Array of course ids', VALUE_DEFAULT, array()
                ),
            )
        );
    }

    
    public static function get_chats_by_courses($courseids = array()) {
        global $CFG;

        $returnedchats = array();
        $warnings = array();

        $params = self::validate_parameters(self::get_chats_by_courses_parameters(), array('courseids' => $courseids));

        $courses = array();
        if (empty($params['courseids'])) {
            $courses = enrol_get_my_courses();
            $params['courseids'] = array_keys($courses);
        }

                if (!empty($params['courseids'])) {

            list($courses, $warnings) = external_util::validate_courses($params['courseids'], $courses);

                                    $chats = get_all_instances_in_courses("chat", $courses);
            foreach ($chats as $chat) {
                $chatcontext = context_module::instance($chat->coursemodule);
                                $chatdetails = array();
                                $chatdetails['id'] = $chat->id;
                $chatdetails['coursemodule']      = $chat->coursemodule;
                $chatdetails['course']            = $chat->course;
                $chatdetails['name']              = external_format_string($chat->name, $chatcontext->id);
                                list($chatdetails['intro'], $chatdetails['introformat']) =
                    external_format_text($chat->intro, $chat->introformat, $chatcontext->id, 'mod_chat', 'intro', null);

                if (has_capability('mod/chat:chat', $chatcontext)) {
                    $chatdetails['chatmethod']    = $CFG->chat_method;
                    $chatdetails['keepdays']      = $chat->keepdays;
                    $chatdetails['studentlogs']   = $chat->studentlogs;
                    $chatdetails['chattime']      = $chat->chattime;
                    $chatdetails['schedule']      = $chat->schedule;
                }

                if (has_capability('moodle/course:manageactivities', $chatcontext)) {
                    $chatdetails['timemodified']  = $chat->timemodified;
                    $chatdetails['section']       = $chat->section;
                    $chatdetails['visible']       = $chat->visible;
                    $chatdetails['groupmode']     = $chat->groupmode;
                    $chatdetails['groupingid']    = $chat->groupingid;
                }
                $returnedchats[] = $chatdetails;
            }
        }
        $result = array();
        $result['chats'] = $returnedchats;
        $result['warnings'] = $warnings;
        return $result;
    }

    
    public static function get_chats_by_courses_returns() {
        return new external_single_structure(
            array(
                'chats' => new external_multiple_structure(
                    new external_single_structure(
                        array(
                            'id' => new external_value(PARAM_INT, 'Chat id'),
                            'coursemodule' => new external_value(PARAM_INT, 'Course module id'),
                            'course' => new external_value(PARAM_INT, 'Course id'),
                            'name' => new external_value(PARAM_RAW, 'Chat name'),
                            'intro' => new external_value(PARAM_RAW, 'The Chat intro'),
                            'introformat' => new external_format_value('intro'),
                            'chatmethod' => new external_value(PARAM_ALPHA, 'chat method (sockets, daemon)', VALUE_OPTIONAL),
                            'keepdays' => new external_value(PARAM_INT, 'keep days', VALUE_OPTIONAL),
                            'studentlogs' => new external_value(PARAM_INT, 'student logs visible to everyone', VALUE_OPTIONAL),
                            'chattime' => new external_value(PARAM_INT, 'chat time', VALUE_OPTIONAL),
                            'schedule' => new external_value(PARAM_INT, 'schedule type', VALUE_OPTIONAL),
                            'timemodified' => new external_value(PARAM_INT, 'time of last modification', VALUE_OPTIONAL),
                            'section' => new external_value(PARAM_INT, 'course section id', VALUE_OPTIONAL),
                            'visible' => new external_value(PARAM_BOOL, 'visible', VALUE_OPTIONAL),
                            'groupmode' => new external_value(PARAM_INT, 'group mode', VALUE_OPTIONAL),
                            'groupingid' => new external_value(PARAM_INT, 'group id', VALUE_OPTIONAL),
                        ), 'Chats'
                    )
                ),
                'warnings' => new external_warnings(),
            )
        );
    }
}
