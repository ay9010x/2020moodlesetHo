<?php



defined('MOODLE_INTERNAL') || die();


class assign_feedback_comments extends assign_feedback_plugin {

    
    public function get_name() {
        return get_string('pluginname', 'assignfeedback_comments');
    }

    
    public function get_feedback_comments($gradeid) {
        global $DB;
        return $DB->get_record('assignfeedback_comments', array('grade'=>$gradeid));
    }

    
    public function get_quickgrading_html($userid, $grade) {
        $commenttext = '';
        if ($grade) {
            $feedbackcomments = $this->get_feedback_comments($grade->id);
            if ($feedbackcomments) {
                $commenttext = $feedbackcomments->commenttext;
            }
        }

        $pluginname = get_string('pluginname', 'assignfeedback_comments');
        $labeloptions = array('for'=>'quickgrade_comments_' . $userid,
                              'class'=>'accesshide');
        $textareaoptions = array('name'=>'quickgrade_comments_' . $userid,
                                 'id'=>'quickgrade_comments_' . $userid,
                                 'class'=>'quickgrade');
        return html_writer::tag('label', $pluginname, $labeloptions) .
               html_writer::tag('textarea', $commenttext, $textareaoptions);
    }

    
    public function is_quickgrading_modified($userid, $grade) {
        $commenttext = '';
        if ($grade) {
            $feedbackcomments = $this->get_feedback_comments($grade->id);
            if ($feedbackcomments) {
                $commenttext = $feedbackcomments->commenttext;
            }
        }
                        $newvalue = optional_param('quickgrade_comments_' . $userid, false, PARAM_RAW);
        return ($newvalue !== false) && ($newvalue != $commenttext);
    }

    
    public function is_feedback_modified(stdClass $grade, stdClass $data) {
        $commenttext = '';
        if ($grade) {
            $feedbackcomments = $this->get_feedback_comments($grade->id);
            if ($feedbackcomments) {
                $commenttext = $feedbackcomments->commenttext;
            }
        }

        if ($commenttext == $data->assignfeedbackcomments_editor['text']) {
            return false;
        } else {
            return true;
        }
    }


    
    public function supports_quickgrading() {
        return true;
    }

    
    public function get_editor_fields() {
        return array('comments' => get_string('pluginname', 'assignfeedback_comments'));
    }

    
    public function get_editor_text($name, $gradeid) {
        if ($name == 'comments') {
            $feedbackcomments = $this->get_feedback_comments($gradeid);
            if ($feedbackcomments) {
                return $feedbackcomments->commenttext;
            }
        }

        return '';
    }

    
    public function set_editor_text($name, $value, $gradeid) {
        global $DB;

        if ($name == 'comments') {
            $feedbackcomment = $this->get_feedback_comments($gradeid);
            if ($feedbackcomment) {
                $feedbackcomment->commenttext = $value;
                return $DB->update_record('assignfeedback_comments', $feedbackcomment);
            } else {
                $feedbackcomment = new stdClass();
                $feedbackcomment->commenttext = $value;
                $feedbackcomment->commentformat = FORMAT_HTML;
                $feedbackcomment->grade = $gradeid;
                $feedbackcomment->assignment = $this->assignment->get_instance()->id;
                return $DB->insert_record('assignfeedback_comments', $feedbackcomment) > 0;
            }
        }

        return false;
    }

    
    public function save_quickgrading_changes($userid, $grade) {
        global $DB;
        $feedbackcomment = $this->get_feedback_comments($grade->id);
        $quickgradecomments = optional_param('quickgrade_comments_' . $userid, null, PARAM_RAW);
        if (!$quickgradecomments) {
            return true;
        }
        if ($feedbackcomment) {
            $feedbackcomment->commenttext = $quickgradecomments;
            return $DB->update_record('assignfeedback_comments', $feedbackcomment);
        } else {
            $feedbackcomment = new stdClass();
            $feedbackcomment->commenttext = $quickgradecomments;
            $feedbackcomment->commentformat = FORMAT_HTML;
            $feedbackcomment->grade = $grade->id;
            $feedbackcomment->assignment = $this->assignment->get_instance()->id;
            return $DB->insert_record('assignfeedback_comments', $feedbackcomment) > 0;
        }
    }

    
    public function save_settings(stdClass $data) {
        $this->set_config('commentinline', !empty($data->assignfeedback_comments_commentinline));
        return true;
    }

    
    public function get_settings(MoodleQuickForm $mform) {
        $default = $this->get_config('commentinline');
        if ($default === false) {
                        $default = get_config('assignfeedback_comments', 'inline');
        }
        $mform->addElement('selectyesno',
                           'assignfeedback_comments_commentinline',
                           get_string('commentinline', 'assignfeedback_comments'));
        $mform->addHelpButton('assignfeedback_comments_commentinline', 'commentinline', 'assignfeedback_comments');
        $mform->setDefault('assignfeedback_comments_commentinline', $default);
                $mform->disabledIf('assignfeedback_comments_commentinline', 'assignfeedback_comments_enabled', 'notchecked');
   }

    
    protected function convert_submission_text_to_feedback($submission, $data) {
        $format = false;
        $text = '';

        foreach ($this->assignment->get_submission_plugins() as $plugin) {
            $fields = $plugin->get_editor_fields();
            if ($plugin->is_enabled() && $plugin->is_visible() && !$plugin->is_empty($submission) && !empty($fields)) {
                foreach ($fields as $key => $description) {
                    $rawtext = strip_pluginfile_content($plugin->get_editor_text($key, $submission->id));

                    $newformat = $plugin->get_editor_format($key, $submission->id);

                    if ($format !== false && $newformat != $format) {
                                                $format = FORMAT_PLAIN;
                    } else {
                        $format = $newformat;
                    }
                    $text .= $rawtext;
                }
            }
        }

        if ($format === false) {
            $format = FORMAT_HTML;
        }
        $data->assignfeedbackcomments_editor['text'] = $text;
        $data->assignfeedbackcomments_editor['format'] = $format;

        return true;
    }

    
    public function get_form_elements_for_user($grade, MoodleQuickForm $mform, stdClass $data, $userid) {
        $commentinlinenabled = $this->get_config('commentinline');
        $submission = $this->assignment->get_user_submission($userid, false);
        $feedbackcomments = false;

        if ($grade) {
            $feedbackcomments = $this->get_feedback_comments($grade->id);
        }

        if ($feedbackcomments && !empty($feedbackcomments->commenttext)) {
            $data->assignfeedbackcomments_editor['text'] = $feedbackcomments->commenttext;
            $data->assignfeedbackcomments_editor['format'] = $feedbackcomments->commentformat;
        } else {
                        if (!empty($commentinlinenabled) && $submission) {
                $this->convert_submission_text_to_feedback($submission, $data);
            }
        }

        $mform->addElement('editor', 'assignfeedbackcomments_editor', $this->get_name(), null, null);

        return true;
    }

    
    public function save(stdClass $grade, stdClass $data) {
        global $DB;
        $feedbackcomment = $this->get_feedback_comments($grade->id);
        if ($feedbackcomment) {
            $feedbackcomment->commenttext = $data->assignfeedbackcomments_editor['text'];
            $feedbackcomment->commentformat = $data->assignfeedbackcomments_editor['format'];
            return $DB->update_record('assignfeedback_comments', $feedbackcomment);
        } else {
            $feedbackcomment = new stdClass();
            $feedbackcomment->commenttext = $data->assignfeedbackcomments_editor['text'];
            $feedbackcomment->commentformat = $data->assignfeedbackcomments_editor['format'];
            $feedbackcomment->grade = $grade->id;
            $feedbackcomment->assignment = $this->assignment->get_instance()->id;
            return $DB->insert_record('assignfeedback_comments', $feedbackcomment) > 0;
        }
    }

    
    public function view_summary(stdClass $grade, & $showviewlink) {
        $feedbackcomments = $this->get_feedback_comments($grade->id);
        if ($feedbackcomments) {
            $text = format_text($feedbackcomments->commenttext,
                                $feedbackcomments->commentformat,
                                array('context' => $this->assignment->get_context()));
            $short = shorten_text($text, 140);

                        $showviewlink = $short != $text;
            return $short;
        }
        return '';
    }

    
    public function view(stdClass $grade) {
        $feedbackcomments = $this->get_feedback_comments($grade->id);
        if ($feedbackcomments) {
            return format_text($feedbackcomments->commenttext,
                               $feedbackcomments->commentformat,
                               array('context' => $this->assignment->get_context()));
        }
        return '';
    }

    
    public function can_upgrade($type, $version) {

        if (($type == 'upload' || $type == 'uploadsingle' ||
             $type == 'online' || $type == 'offline') && $version >= 2011112900) {
            return true;
        }
        return false;
    }

    
    public function upgrade_settings(context $oldcontext, stdClass $oldassignment, & $log) {
        if ($oldassignment->assignmenttype == 'online') {
            $this->set_config('commentinline', $oldassignment->var1);
            return true;
        }
        return true;
    }

    
    public function upgrade(context $oldcontext,
                            stdClass $oldassignment,
                            stdClass $oldsubmission,
                            stdClass $grade,
                            & $log) {
        global $DB;

        $feedbackcomments = new stdClass();
        $feedbackcomments->commenttext = $oldsubmission->submissioncomment;
        $feedbackcomments->commentformat = FORMAT_HTML;

        $feedbackcomments->grade = $grade->id;
        $feedbackcomments->assignment = $this->assignment->get_instance()->id;
        if (!$DB->insert_record('assignfeedback_comments', $feedbackcomments) > 0) {
            $log .= get_string('couldnotconvertgrade', 'mod_assign', $grade->userid);
            return false;
        }

        return true;
    }

    
    public function format_for_gradebook(stdClass $grade) {
        $feedbackcomments = $this->get_feedback_comments($grade->id);
        if ($feedbackcomments) {
            return $feedbackcomments->commentformat;
        }
        return FORMAT_MOODLE;
    }

    
    public function text_for_gradebook(stdClass $grade) {
        $feedbackcomments = $this->get_feedback_comments($grade->id);
        if ($feedbackcomments) {
            return $feedbackcomments->commenttext;
        }
        return '';
    }

    
    public function delete_instance() {
        global $DB;
                $DB->delete_records('assignfeedback_comments',
                            array('assignment'=>$this->assignment->get_instance()->id));
        return true;
    }

    
    public function is_empty(stdClass $grade) {
        return $this->view($grade) == '';
    }

    
    public function get_external_parameters() {
        $editorparams = array('text' => new external_value(PARAM_RAW, 'The text for this feedback.'),
                              'format' => new external_value(PARAM_INT, 'The format for this feedback'));
        $editorstructure = new external_single_structure($editorparams, 'Editor structure', VALUE_OPTIONAL);
        return array('assignfeedbackcomments_editor' => $editorstructure);
    }

}
