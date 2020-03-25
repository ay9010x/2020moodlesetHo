<?php



namespace mod_forum\message\inbound;

defined('MOODLE_INTERNAL') || die();

require_once($CFG->dirroot . '/mod/forum/lib.php');
require_once($CFG->dirroot . '/repository/lib.php');
require_once($CFG->libdir . '/completionlib.php');


class reply_handler extends \core\message\inbound\handler {

    
    public function get_description() {
        return get_string('reply_handler', 'mod_forum');
    }

    
    public function get_name() {
        return get_string('reply_handler_name', 'mod_forum');
    }

    
    public function process_message(\stdClass $record, \stdClass $messagedata) {
        global $DB, $USER;

                $post = $DB->get_record('forum_posts', array('id' => $record->datavalue));
        if (!$post) {
            mtrace("--> Unable to find a post matching with id {$record->datavalue}");
            return false;
        }

                $discussion = $DB->get_record('forum_discussions', array('id' => $post->discussion));
        if (!$post) {
            mtrace("--> Unable to find the discussion for post {$record->datavalue}");
            return false;
        }

                $forum = $DB->get_record('forum', array('id' => $discussion->forum));
        $course = $DB->get_record('course', array('id' => $forum->course));
        $cm = get_fast_modinfo($course->id)->instances['forum'][$forum->id];
        $modcontext = \context_module::instance($cm->id);
        $usercontext = \context_user::instance($USER->id);

                $canpost = true;
        if (!forum_user_can_post($forum, $discussion, $USER, $cm, $course, $modcontext)) {
            $canpost = false;
        }

        if (isset($cm->groupmode) && empty($course->groupmodeforce)) {
            $groupmode = $cm->groupmode;
        } else {
            $groupmode = $course->groupmode;
        }
        if ($groupmode == SEPARATEGROUPS and !has_capability('moodle/site:accessallgroups', $modcontext)) {
            if ($discussion->groupid == -1) {
                $canpost = false;
            } else {
                if (!groups_is_member($discussion->groupid)) {
                    $canpost = false;
                }
            }
        }

        if (!$canpost) {
            $data = new \stdClass();
            $data->forum = $forum;
            throw new \core\message\inbound\processing_failed_exception('messageinboundnopostforum', 'mod_forum', $data);
        }

                if (!\core_availability\info_module::is_user_visible($cm)) {
            $data = new \stdClass();
            $data->forum = $forum;
            throw new \core\message\inbound\processing_failed_exception('messageinboundforumhidden', 'mod_forum', $data);
        }

                        $thresholdwarning = forum_check_throttling($forum, $cm);
        if (!empty($thresholdwarning) && !$thresholdwarning->canpost) {
            $data = new \stdClass();
            $data->forum = $forum;
            $data->message = get_string($thresholdwarning->errorcode, $thresholdwarning->module, $thresholdwarning->additional);
            throw new \core\message\inbound\processing_failed_exception('messageinboundthresholdhit', 'mod_forum', $data);
        }

        $subject = clean_param($messagedata->envelope->subject, PARAM_TEXT);
        $restring = get_string('re', 'forum');
        if (strpos($subject, $discussion->name)) {
                                    $newsubject = $restring . ' ' . $discussion->name;
            mtrace("--> Note: Post subject matched discussion name. Optimising from {$subject} to {$newsubject}");
            $subject = $newsubject;
        } else if (strpos($subject, $post->subject)) {
                                    $newsubject = $post->subject;
            if (!strpos($restring, $post->subject)) {
                                $newsubject = $restring . ' ' . $newsubject;
            }
            mtrace("--> Note: Post subject matched original post subject. Optimising from {$subject} to {$newsubject}");
            $subject = $newsubject;
        }

        $addpost = new \stdClass();
        $addpost->course       = $course->id;
        $addpost->forum        = $forum->id;
        $addpost->discussion   = $discussion->id;
        $addpost->modified     = $messagedata->timestamp;
        $addpost->subject      = $subject;
        $addpost->parent       = $post->id;
        $addpost->itemid       = file_get_unused_draft_itemid();

        list ($message, $format) = self::remove_quoted_text($messagedata);
        $addpost->message = $message;
        $addpost->messageformat = $format;

                $addpost->messagetrust = false;

                if (!empty($messagedata->attachments['attachment']) && count($messagedata->attachments['attachment'])) {
            $attachmentcount = count($messagedata->attachments['attachment']);
            if (empty($forum->maxattachments) || $forum->maxbytes == 1 ||
                    !has_capability('mod/forum:createattachment', $modcontext)) {
                                mtrace("--> User does not have permission to attach files in this forum. Rejecting e-mail.");

                $data = new \stdClass();
                $data->forum = $forum;
                $data->attachmentcount = $attachmentcount;
                throw new \core\message\inbound\processing_failed_exception('messageinboundattachmentdisallowed', 'mod_forum', $data);
            }

            if ($forum->maxattachments < $attachmentcount) {
                                mtrace("--> User attached {$attachmentcount} files when only {$forum->maxattachments} where allowed. "
                     . " Rejecting e-mail.");

                $data = new \stdClass();
                $data->forum = $forum;
                $data->attachmentcount = $attachmentcount;
                throw new \core\message\inbound\processing_failed_exception('messageinboundfilecountexceeded', 'mod_forum', $data);
            }

            $filesize = 0;
            $addpost->attachments  = file_get_unused_draft_itemid();
            foreach ($messagedata->attachments['attachment'] as $attachment) {
                mtrace("--> Processing {$attachment->filename} as an attachment.");
                $this->process_attachment('*', $usercontext, $addpost->attachments, $attachment);
                $filesize += $attachment->filesize;
            }

            if ($forum->maxbytes < $filesize) {
                                mtrace("--> User attached {$filesize} bytes of files when only {$forum->maxbytes} where allowed. "
                     . "Rejecting e-mail.");
                $data = new \stdClass();
                $data->forum = $forum;
                $data->maxbytes = display_size($forum->maxbytes);
                $data->filesize = display_size($filesize);
                throw new \core\message\inbound\processing_failed_exception('messageinboundfilesizeexceeded', 'mod_forum', $data);
            }
        }

                if (!empty($messagedata->attachments['inline'])) {
            foreach ($messagedata->attachments['inline'] as $attachment) {
                mtrace("--> Processing {$attachment->filename} as an inline attachment.");
                $this->process_attachment('*', $usercontext, $addpost->itemid, $attachment);

                                $draftfile = \moodle_url::make_draftfile_url($addpost->itemid, '/', $attachment->filename);
                $addpost->message = preg_replace('/cid:' . $attachment->contentid . '/', $draftfile, $addpost->message);
            }
        }

                $addpost->id = forum_add_new_post($addpost, true);

                $params = array(
            'context' => $modcontext,
            'objectid' => $addpost->id,
            'other' => array(
                'discussionid'  => $discussion->id,
                'forumid'       => $forum->id,
                'forumtype'     => $forum->type,
            )
        );
        $event = \mod_forum\event\post_created::create($params);
        $event->add_record_snapshot('forum_posts', $addpost);
        $event->add_record_snapshot('forum_discussions', $discussion);
        $event->trigger();

                $completion = new \completion_info($course);
        if ($completion->is_enabled($cm) && ($forum->completionreplies || $forum->completionposts)) {
            $completion->update_state($cm, COMPLETION_COMPLETE);

            mtrace("--> Updating completion status for user {$USER->id} in forum {$forum->id} for post {$addpost->id}.");
        }

        mtrace("--> Created a post {$addpost->id} in {$discussion->id}.");
        return $addpost;
    }

    
    protected function process_attachment($acceptedtypes, \context_user $context, $itemid, \stdClass $attachment) {
        global $USER, $CFG;

                $record = new \stdClass();
        $record->filearea   = 'draft';
        $record->component  = 'user';

        $record->itemid     = $itemid;
        $record->license    = $CFG->sitedefaultlicense;
        $record->author     = fullname($USER);
        $record->contextid  = $context->id;
        $record->userid     = $USER->id;

                $record->filepath   = '/';

        $record->filename = $attachment->filename;

        mtrace("--> Attaching {$record->filename} to " .
               "/{$record->contextid}/{$record->component}/{$record->filearea}/" .
               "{$record->itemid}{$record->filepath}{$record->filename}");

        $fs = get_file_storage();
        return $fs->create_file_from_string($record, $attachment->content);
    }

    
    public function get_success_message(\stdClass $messagedata, $handlerresult) {
        $a = new \stdClass();
        $a->subject = $handlerresult->subject;
        $discussionurl = new \moodle_url('/mod/forum/discuss.php', array('d' => $handlerresult->discussion));
        $discussionurl->set_anchor('p' . $handlerresult->id);
        $a->discussionurl = $discussionurl->out();

        $message = new \stdClass();
        $message->plain = get_string('postbymailsuccess', 'mod_forum', $a);
        $message->html = get_string('postbymailsuccess_html', 'mod_forum', $a);
        return $message;
    }
}
