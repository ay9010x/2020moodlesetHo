<?php



defined('MOODLE_INTERNAL') || die();
define('ASSIGNSUBMISSION_ONLINETEXT_FILEAREA', 'submissions_onlinetext');


class assign_submission_onlinetext extends assign_submission_plugin {

    
    public function get_name() {
        return get_string('onlinetext', 'assignsubmission_onlinetext');
    }


    
    private function get_onlinetext_submission($submissionid) {
        global $DB;

        return $DB->get_record('assignsubmission_onlinetext', array('submission'=>$submissionid));
    }

    
    public function get_settings(MoodleQuickForm $mform) {
        global $CFG, $COURSE;

        $defaultwordlimit = $this->get_config('wordlimit') == 0 ? '' : $this->get_config('wordlimit');
        $defaultwordlimitenabled = $this->get_config('wordlimitenabled');

        $options = array('size' => '6', 'maxlength' => '6');
        $name = get_string('wordlimit', 'assignsubmission_onlinetext');

                $wordlimitgrp = array();
        $wordlimitgrp[] = $mform->createElement('text', 'assignsubmission_onlinetext_wordlimit', '', $options);
        $wordlimitgrp[] = $mform->createElement('checkbox', 'assignsubmission_onlinetext_wordlimit_enabled',
                '', get_string('enable'));
        $mform->addGroup($wordlimitgrp, 'assignsubmission_onlinetext_wordlimit_group', $name, ' ', false);
        $mform->addHelpButton('assignsubmission_onlinetext_wordlimit_group',
                              'wordlimit',
                              'assignsubmission_onlinetext');
        $mform->disabledIf('assignsubmission_onlinetext_wordlimit',
                           'assignsubmission_onlinetext_wordlimit_enabled',
                           'notchecked');

                $wordlimitgrprules = array();
        $wordlimitgrprules['assignsubmission_onlinetext_wordlimit'][] = array(null, 'numeric', null, 'client');
        $mform->addGroupRule('assignsubmission_onlinetext_wordlimit_group', $wordlimitgrprules);

                $mform->setDefault('assignsubmission_onlinetext_wordlimit', $defaultwordlimit);
        $mform->setDefault('assignsubmission_onlinetext_wordlimit_enabled', $defaultwordlimitenabled);
        $mform->setType('assignsubmission_onlinetext_wordlimit', PARAM_INT);
        $mform->disabledIf('assignsubmission_onlinetext_wordlimit_group',
                           'assignsubmission_onlinetext_enabled',
                           'notchecked');
    }

    
    public function save_settings(stdClass $data) {
        if (empty($data->assignsubmission_onlinetext_wordlimit) || empty($data->assignsubmission_onlinetext_wordlimit_enabled)) {
            $wordlimit = 0;
            $wordlimitenabled = 0;
        } else {
            $wordlimit = $data->assignsubmission_onlinetext_wordlimit;
            $wordlimitenabled = 1;
        }

        $this->set_config('wordlimit', $wordlimit);
        $this->set_config('wordlimitenabled', $wordlimitenabled);

        return true;
    }

    
    public function get_form_elements($submission, MoodleQuickForm $mform, stdClass $data) {
        $elements = array();

        $editoroptions = $this->get_edit_options();
        $submissionid = $submission ? $submission->id : 0;

        if (!isset($data->onlinetext)) {
            $data->onlinetext = '';
        }
        if (!isset($data->onlinetextformat)) {
            $data->onlinetextformat = editors_get_preferred_format();
        }

        if ($submission) {
            $onlinetextsubmission = $this->get_onlinetext_submission($submission->id);
            if ($onlinetextsubmission) {
                $data->onlinetext = $onlinetextsubmission->onlinetext;
                $data->onlinetextformat = $onlinetextsubmission->onlineformat;
            }

        }

        $data = file_prepare_standard_editor($data,
                                             'onlinetext',
                                             $editoroptions,
                                             $this->assignment->get_context(),
                                             'assignsubmission_onlinetext',
                                             ASSIGNSUBMISSION_ONLINETEXT_FILEAREA,
                                             $submissionid);
        $mform->addElement('editor', 'onlinetext_editor', $this->get_name(), null, $editoroptions);

        return true;
    }

    
    private function get_edit_options() {
         $editoroptions = array(
           'noclean' => false,
           'maxfiles' => EDITOR_UNLIMITED_FILES,
           'maxbytes' => $this->assignment->get_course()->maxbytes,
           'context' => $this->assignment->get_context(),
           'return_types' => FILE_INTERNAL | FILE_EXTERNAL
        );
        return $editoroptions;
    }

    
    public function save(stdClass $submission, stdClass $data) {
        global $USER, $DB;

        $editoroptions = $this->get_edit_options();

        $data = file_postupdate_standard_editor($data,
                                                'onlinetext',
                                                $editoroptions,
                                                $this->assignment->get_context(),
                                                'assignsubmission_onlinetext',
                                                ASSIGNSUBMISSION_ONLINETEXT_FILEAREA,
                                                $submission->id);

        $onlinetextsubmission = $this->get_onlinetext_submission($submission->id);

        $fs = get_file_storage();

        $files = $fs->get_area_files($this->assignment->get_context()->id,
                                     'assignsubmission_onlinetext',
                                     ASSIGNSUBMISSION_ONLINETEXT_FILEAREA,
                                     $submission->id,
                                     'id',
                                     false);

                $exceeded = $this->check_word_count(trim($data->onlinetext));
        if ($exceeded) {
            $this->set_error($exceeded);
            return false;
        }

        $params = array(
            'context' => context_module::instance($this->assignment->get_course_module()->id),
            'courseid' => $this->assignment->get_course()->id,
            'objectid' => $submission->id,
            'other' => array(
                'pathnamehashes' => array_keys($files),
                'content' => trim($data->onlinetext),
                'format' => $data->onlinetext_editor['format']
            )
        );
        if (!empty($submission->userid) && ($submission->userid != $USER->id)) {
            $params['relateduserid'] = $submission->userid;
        }
        $event = \assignsubmission_onlinetext\event\assessable_uploaded::create($params);
        $event->trigger();

        $groupname = null;
        $groupid = 0;
                if (empty($submission->userid) && !empty($submission->groupid)) {
            $groupname = $DB->get_field('groups', 'name', array('id' => $submission->groupid), '*', MUST_EXIST);
            $groupid = $submission->groupid;
        } else {
            $params['relateduserid'] = $submission->userid;
        }

        $count = count_words($data->onlinetext);

                unset($params['objectid']);
        unset($params['other']);
        $params['other'] = array(
            'submissionid' => $submission->id,
            'submissionattempt' => $submission->attemptnumber,
            'submissionstatus' => $submission->status,
            'onlinetextwordcount' => $count,
            'groupid' => $groupid,
            'groupname' => $groupname
        );

        if ($onlinetextsubmission) {

            $onlinetextsubmission->onlinetext = $data->onlinetext;
            $onlinetextsubmission->onlineformat = $data->onlinetext_editor['format'];
            $params['objectid'] = $onlinetextsubmission->id;
            $updatestatus = $DB->update_record('assignsubmission_onlinetext', $onlinetextsubmission);
            $event = \assignsubmission_onlinetext\event\submission_updated::create($params);
            $event->set_assign($this->assignment);
            $event->trigger();
            return $updatestatus;
        } else {

            $onlinetextsubmission = new stdClass();
            $onlinetextsubmission->onlinetext = $data->onlinetext;
            $onlinetextsubmission->onlineformat = $data->onlinetext_editor['format'];

            $onlinetextsubmission->submission = $submission->id;
            $onlinetextsubmission->assignment = $this->assignment->get_instance()->id;
            $onlinetextsubmission->id = $DB->insert_record('assignsubmission_onlinetext', $onlinetextsubmission);
            $params['objectid'] = $onlinetextsubmission->id;
            $event = \assignsubmission_onlinetext\event\submission_created::create($params);
            $event->set_assign($this->assignment);
            $event->trigger();
            return $onlinetextsubmission->id > 0;
        }
    }

    
    public function get_editor_fields() {
        return array('onlinetext' => get_string('pluginname', 'assignsubmission_comments'));
    }

    
    public function get_editor_text($name, $submissionid) {
        if ($name == 'onlinetext') {
            $onlinetextsubmission = $this->get_onlinetext_submission($submissionid);
            if ($onlinetextsubmission) {
                return $onlinetextsubmission->onlinetext;
            }
        }

        return '';
    }

    
    public function get_editor_format($name, $submissionid) {
        if ($name == 'onlinetext') {
            $onlinetextsubmission = $this->get_onlinetext_submission($submissionid);
            if ($onlinetextsubmission) {
                return $onlinetextsubmission->onlineformat;
            }
        }

        return 0;
    }


     
    public function view_summary(stdClass $submission, & $showviewlink) {
        global $CFG;

        $onlinetextsubmission = $this->get_onlinetext_submission($submission->id);
                $showviewlink = true;

        if ($onlinetextsubmission) {
            $text = $this->assignment->render_editor_content(ASSIGNSUBMISSION_ONLINETEXT_FILEAREA,
                                                             $onlinetextsubmission->submission,
                                                             $this->get_type(),
                                                             'onlinetext',
                                                             'assignsubmission_onlinetext');

            $shorttext = shorten_text($text, 140);
            $plagiarismlinks = '';

            if (!empty($CFG->enableplagiarism)) {
                require_once($CFG->libdir . '/plagiarismlib.php');

                $plagiarismlinks .= plagiarism_get_links(array('userid' => $submission->userid,
                    'content' => trim($text),
                    'cmid' => $this->assignment->get_course_module()->id,
                    'course' => $this->assignment->get_course()->id,
                    'assignment' => $submission->assignment));
            }
            if ($text != $shorttext) {
                $wordcount = get_string('numwords', 'assignsubmission_onlinetext', count_words($text));

                return $plagiarismlinks . $wordcount . $shorttext;
            } else {
                return $plagiarismlinks . $shorttext;
            }
        }
        return '';
    }

    
    public function get_files(stdClass $submission, stdClass $user) {
        global $DB;

        $files = array();
        $onlinetextsubmission = $this->get_onlinetext_submission($submission->id);

        if ($onlinetextsubmission) {
            $finaltext = $this->assignment->download_rewrite_pluginfile_urls($onlinetextsubmission->onlinetext, $user, $this);
            $formattedtext = format_text($finaltext,
                                         $onlinetextsubmission->onlineformat,
                                         array('context'=>$this->assignment->get_context()));
            $head = '<head><meta charset="UTF-8"></head>';
            $submissioncontent = '<!DOCTYPE html><html>' . $head . '<body>'. $formattedtext . '</body></html>';

            $filename = get_string('onlinetextfilename', 'assignsubmission_onlinetext');
            $files[$filename] = array($submissioncontent);

            $fs = get_file_storage();

            $fsfiles = $fs->get_area_files($this->assignment->get_context()->id,
                                           'assignsubmission_onlinetext',
                                           ASSIGNSUBMISSION_ONLINETEXT_FILEAREA,
                                           $submission->id,
                                           'timemodified',
                                           false);

            foreach ($fsfiles as $file) {
                $files[$file->get_filename()] = $file;
            }
        }

        return $files;
    }

    
    public function view(stdClass $submission) {
        global $CFG;
        $result = '';

        $onlinetextsubmission = $this->get_onlinetext_submission($submission->id);

        if ($onlinetextsubmission) {

                        $result .= $this->assignment->render_editor_content(ASSIGNSUBMISSION_ONLINETEXT_FILEAREA,
                                                                $onlinetextsubmission->submission,
                                                                $this->get_type(),
                                                                'onlinetext',
                                                                'assignsubmission_onlinetext');

            $plagiarismlinks = '';

            if (!empty($CFG->enableplagiarism)) {
                require_once($CFG->libdir . '/plagiarismlib.php');

                $plagiarismlinks .= plagiarism_get_links(array('userid' => $submission->userid,
                    'content' => trim($result),
                    'cmid' => $this->assignment->get_course_module()->id,
                    'course' => $this->assignment->get_course()->id,
                    'assignment' => $submission->assignment));
            }
        }

        return $plagiarismlinks . $result;
    }

    
    public function can_upgrade($type, $version) {
        if ($type == 'online' && $version >= 2011112900) {
            return true;
        }
        return false;
    }


    
    public function upgrade_settings(context $oldcontext, stdClass $oldassignment, & $log) {
                return true;
    }

    
    public function upgrade(context $oldcontext,
                            stdClass $oldassignment,
                            stdClass $oldsubmission,
                            stdClass $submission,
                            & $log) {
        global $DB;

        $onlinetextsubmission = new stdClass();
        $onlinetextsubmission->onlinetext = $oldsubmission->data1;
        $onlinetextsubmission->onlineformat = $oldsubmission->data2;

        $onlinetextsubmission->submission = $submission->id;
        $onlinetextsubmission->assignment = $this->assignment->get_instance()->id;

        if ($onlinetextsubmission->onlinetext === null) {
            $onlinetextsubmission->onlinetext = '';
        }

        if ($onlinetextsubmission->onlineformat === null) {
            $onlinetextsubmission->onlineformat = editors_get_preferred_format();
        }

        if (!$DB->insert_record('assignsubmission_onlinetext', $onlinetextsubmission) > 0) {
            $log .= get_string('couldnotconvertsubmission', 'mod_assign', $submission->userid);
            return false;
        }

                $this->assignment->copy_area_files_for_upgrade($oldcontext->id,
                                                        'mod_assignment',
                                                        'submission',
                                                        $oldsubmission->id,
                                                        $this->assignment->get_context()->id,
                                                        'assignsubmission_onlinetext',
                                                        ASSIGNSUBMISSION_ONLINETEXT_FILEAREA,
                                                        $submission->id);
        return true;
    }

    
    public function format_for_log(stdClass $submission) {
                $onlinetextsubmission = $this->get_onlinetext_submission($submission->id);
        $onlinetextloginfo = '';
        $onlinetextloginfo .= get_string('numwordsforlog',
                                         'assignsubmission_onlinetext',
                                         count_words($onlinetextsubmission->onlinetext));

        return $onlinetextloginfo;
    }

    
    public function delete_instance() {
        global $DB;
        $DB->delete_records('assignsubmission_onlinetext',
                            array('assignment'=>$this->assignment->get_instance()->id));

        return true;
    }

    
    public function is_empty(stdClass $submission) {
        $onlinetextsubmission = $this->get_onlinetext_submission($submission->id);

        return empty($onlinetextsubmission->onlinetext);
    }

    
    public function get_file_areas() {
        return array(ASSIGNSUBMISSION_ONLINETEXT_FILEAREA=>$this->get_name());
    }

    
    public function copy_submission(stdClass $sourcesubmission, stdClass $destsubmission) {
        global $DB;

                $contextid = $this->assignment->get_context()->id;
        $fs = get_file_storage();
        $files = $fs->get_area_files($contextid, 'assignsubmission_onlinetext',
                                     ASSIGNSUBMISSION_ONLINETEXT_FILEAREA, $sourcesubmission->id, 'id', false);
        foreach ($files as $file) {
            $fieldupdates = array('itemid' => $destsubmission->id);
            $fs->create_file_from_storedfile($fieldupdates, $file);
        }

                $onlinetextsubmission = $this->get_onlinetext_submission($sourcesubmission->id);
        if ($onlinetextsubmission) {
            unset($onlinetextsubmission->id);
            $onlinetextsubmission->submission = $destsubmission->id;
            $DB->insert_record('assignsubmission_onlinetext', $onlinetextsubmission);
        }
        return true;
    }

    
    public function get_external_parameters() {
        $editorparams = array('text' => new external_value(PARAM_RAW, 'The text for this submission.'),
                              'format' => new external_value(PARAM_INT, 'The format for this submission'),
                              'itemid' => new external_value(PARAM_INT, 'The draft area id for files attached to the submission'));
        $editorstructure = new external_single_structure($editorparams, 'Editor structure', VALUE_OPTIONAL);
        return array('onlinetext_editor' => $editorstructure);
    }

    
    public function check_word_count($submissiontext) {
        global $OUTPUT;

        $wordlimitenabled = $this->get_config('wordlimitenabled');
        $wordlimit = $this->get_config('wordlimit');

        if ($wordlimitenabled == 0) {
            return null;
        }

                $wordcount = count_words($submissiontext);
        if ($wordcount <= $wordlimit) {
            return null;
        } else {
            $errormsg = get_string('wordlimitexceeded', 'assignsubmission_onlinetext',
                    array('limit' => $wordlimit, 'count' => $wordcount));
            return $OUTPUT->error_text($errormsg);
        }
    }

}


