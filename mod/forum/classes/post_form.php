<?php




defined('MOODLE_INTERNAL') || die();
require_once($CFG->libdir . '/formslib.php');
require_once($CFG->dirroot . '/repository/lib.php');


class mod_forum_post_form extends moodleform {

    
    public static function attachment_options($forum) {
        global $COURSE, $PAGE, $CFG;
        $maxbytes = get_user_max_upload_file_size($PAGE->context, $CFG->maxbytes, $COURSE->maxbytes, $forum->maxbytes);
        return array(
            'subdirs' => 0,
            'maxbytes' => $maxbytes,
            'maxfiles' => $forum->maxattachments,
            'accepted_types' => '*',
            'return_types' => FILE_INTERNAL
        );
    }

    
    public static function editor_options(context_module $context, $postid) {
        global $COURSE, $PAGE, $CFG;
                $maxbytes = get_user_max_upload_file_size($PAGE->context, $CFG->maxbytes, $COURSE->maxbytes);
        return array(
            'maxfiles' => EDITOR_UNLIMITED_FILES,
            'maxbytes' => $maxbytes,
            'trusttext'=> true,
            'return_types'=> FILE_INTERNAL | FILE_EXTERNAL,
            'subdirs' => file_area_contains_subdirs($context, 'mod_forum', 'post', $postid)
        );
    }

    
    function definition() {
        global $CFG, $OUTPUT;

        $mform =& $this->_form;

        $course = $this->_customdata['course'];
        $cm = $this->_customdata['cm'];
        $coursecontext = $this->_customdata['coursecontext'];
        $modcontext = $this->_customdata['modcontext'];
        $forum = $this->_customdata['forum'];
        $post = $this->_customdata['post'];
        $subscribe = $this->_customdata['subscribe'];
        $edit = $this->_customdata['edit'];
        $thresholdwarning = $this->_customdata['thresholdwarning'];

        $mform->addElement('header', 'general', '');
                if (!empty($thresholdwarning) && !$edit) {
                        if ($thresholdwarning->canpost) {
                $message = get_string($thresholdwarning->errorcode, $thresholdwarning->module, $thresholdwarning->additional);
                $mform->addElement('html', $OUTPUT->notification($message));
            }
        }

        $mform->addElement('text', 'subject', get_string('subject', 'forum'), 'size="48"');
        $mform->setType('subject', PARAM_TEXT);
        $mform->addRule('subject', get_string('required'), 'required', null, 'client');
        $mform->addRule('subject', get_string('maximumchars', '', 255), 'maxlength', 255, 'client');

        $mform->addElement('editor', 'message', get_string('message', 'forum'), null, self::editor_options($modcontext, (empty($post->id) ? null : $post->id)));
        $mform->setType('message', PARAM_RAW);
        $mform->addRule('message', get_string('required'), 'required', null, 'client');

        $manageactivities = has_capability('moodle/course:manageactivities', $coursecontext);

        if (\mod_forum\subscriptions::is_forcesubscribed($forum)) {
            $mform->addElement('checkbox', 'discussionsubscribe', get_string('discussionsubscription', 'forum'));
            $mform->freeze('discussionsubscribe');
            $mform->setDefaults('discussionsubscribe', 0);
            $mform->addHelpButton('discussionsubscribe', 'forcesubscribed', 'forum');

        } else if (\mod_forum\subscriptions::subscription_disabled($forum) && !$manageactivities) {
            $mform->addElement('checkbox', 'discussionsubscribe', get_string('discussionsubscription', 'forum'));
            $mform->freeze('discussionsubscribe');
            $mform->setDefaults('discussionsubscribe', 0);
            $mform->addHelpButton('discussionsubscribe', 'disallowsubscription', 'forum');

        } else {
            $mform->addElement('checkbox', 'discussionsubscribe', get_string('discussionsubscription', 'forum'));
            $mform->addHelpButton('discussionsubscribe', 'discussionsubscription', 'forum');
        }

        if (!empty($forum->maxattachments) && $forum->maxbytes != 1 && has_capability('mod/forum:createattachment', $modcontext))  {              $mform->addElement('filemanager', 'attachments', get_string('attachment', 'forum'), null, self::attachment_options($forum));
            $mform->addHelpButton('attachments', 'attachment', 'forum');
        }

        if (!$post->parent && has_capability('mod/forum:pindiscussions', $modcontext)) {
            $mform->addElement('checkbox', 'pinned', get_string('discussionpinned', 'forum'));
            $mform->addHelpButton('pinned', 'discussionpinned', 'forum');
        }

        if (empty($post->id) && $manageactivities) {
            $mform->addElement('checkbox', 'mailnow', get_string('mailnow', 'forum'));
        }

        if ($groupmode = groups_get_activity_groupmode($cm, $course)) {
            $groupdata = groups_get_activity_allowed_groups($cm);

            $groupinfo = array();
            foreach ($groupdata as $groupid => $group) {
                                                if (forum_user_can_post_discussion($forum, $groupid, null, $cm, $modcontext)) {
                                        $groupinfo[$groupid] = $group->name;
                } else {
                    unset($groupdata[$groupid]);
                }
            }
            $groupcount = count($groupinfo);

            
                                                $canposttoowngroups = empty($post->edit) && $groupcount > 1;

                        $canposttoowngroups = $canposttoowngroups && empty($post->parent);

                        $canposttoowngroups = $canposttoowngroups && has_capability('mod/forum:canposttomygroups', $modcontext);
            if ($canposttoowngroups) {
                                                                $mform->addElement('checkbox', 'posttomygroups', get_string('posttomygroups', 'forum'));
                $mform->addHelpButton('posttomygroups', 'posttomygroups', 'forum');
                $mform->disabledIf('groupinfo', 'posttomygroups', 'checked');
            }

                                                if (forum_user_can_post_discussion($forum, -1, null, $cm, $modcontext)) {
                                $groupinfo = array_reverse($groupinfo, true );
                $groupinfo[-1] = get_string('allparticipants');
                $groupinfo = array_reverse($groupinfo, true );
                $groupcount++;
            }

                                    $canselectgroupfornew = empty($post->edit) && $groupcount > 1;

                                                $canselectgroupformove = $groupcount && !empty($post->edit) && has_capability('mod/forum:movediscussions', $modcontext);

                        $canselectgroup = empty($post->parent) && ($canselectgroupfornew || $canselectgroupformove);

            if ($canselectgroup) {
                $mform->addElement('select','groupinfo', get_string('group'), $groupinfo);
                $mform->setDefault('groupinfo', $post->groupid);
                $mform->setType('groupinfo', PARAM_INT);
            } else {
                if (empty($post->groupid)) {
                    $groupname = get_string('allparticipants');
                } else {
                    $groupname = format_string($groupdata[$post->groupid]->name);
                }
                $mform->addElement('static', 'groupinfo', get_string('group'), $groupname);
            }
        }

        if (!empty($CFG->forum_enabletimedposts) && !$post->parent && has_capability('mod/forum:viewhiddentimedposts', $coursecontext)) {
            $mform->addElement('header', 'displayperiod', get_string('displayperiod', 'forum'));

            $mform->addElement('date_time_selector', 'timestart', get_string('displaystart', 'forum'), array('optional' => true));
            $mform->addHelpButton('timestart', 'displaystart', 'forum');

            $mform->addElement('date_time_selector', 'timeend', get_string('displayend', 'forum'), array('optional' => true));
            $mform->addHelpButton('timeend', 'displayend', 'forum');

        } else {
            $mform->addElement('hidden', 'timestart');
            $mform->setType('timestart', PARAM_INT);
            $mform->addElement('hidden', 'timeend');
            $mform->setType('timeend', PARAM_INT);
            $mform->setConstants(array('timestart' => 0, 'timeend' => 0));
        }

                        if (isset($post->edit)) {             $submit_string = get_string('savechanges');
        } else {
            $submit_string = get_string('posttoforum', 'forum');
        }

        $this->add_action_buttons(true, $submit_string);

        $mform->addElement('hidden', 'course');
        $mform->setType('course', PARAM_INT);

        $mform->addElement('hidden', 'forum');
        $mform->setType('forum', PARAM_INT);

        $mform->addElement('hidden', 'discussion');
        $mform->setType('discussion', PARAM_INT);

        $mform->addElement('hidden', 'parent');
        $mform->setType('parent', PARAM_INT);

        $mform->addElement('hidden', 'groupid');
        $mform->setType('groupid', PARAM_INT);

        $mform->addElement('hidden', 'edit');
        $mform->setType('edit', PARAM_INT);

        $mform->addElement('hidden', 'reply');
        $mform->setType('reply', PARAM_INT);
    }

    
    function validation($data, $files) {
        $errors = parent::validation($data, $files);
        if (($data['timeend']!=0) && ($data['timestart']!=0) && $data['timeend'] <= $data['timestart']) {
            $errors['timeend'] = get_string('timestartenderror', 'forum');
        }
        if (empty($data['message']['text'])) {
            $errors['message'] = get_string('erroremptymessage', 'forum');
        }
        if (empty($data['subject'])) {
            $errors['subject'] = get_string('erroremptysubject', 'forum');
        }
        return $errors;
    }
}
