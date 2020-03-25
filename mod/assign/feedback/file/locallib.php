<?php



defined('MOODLE_INTERNAL') || die();

define('ASSIGNFEEDBACK_FILE_FILEAREA', 'feedback_files');
define('ASSIGNFEEDBACK_FILE_BATCH_FILEAREA', 'feedback_files_batch');
define('ASSIGNFEEDBACK_FILE_IMPORT_FILEAREA', 'feedback_files_import');
define('ASSIGNFEEDBACK_FILE_MAXSUMMARYFILES', 5);
define('ASSIGNFEEDBACK_FILE_MAXSUMMARYUSERS', 5);
define('ASSIGNFEEDBACK_FILE_MAXFILEUNZIPTIME', 120);


class assign_feedback_file extends assign_feedback_plugin {

    
    public function get_name() {
        return get_string('file', 'assignfeedback_file');
    }

    
    public function get_file_feedback($gradeid) {
        global $DB;
        return $DB->get_record('assignfeedback_file', array('grade'=>$gradeid));
    }

    
    private function get_file_options() {
        global $COURSE;

        $fileoptions = array('subdirs'=>1,
                             'maxbytes'=>$COURSE->maxbytes,
                             'accepted_types'=>'*',
                             'return_types'=>FILE_INTERNAL);
        return $fileoptions;
    }

    
    public function is_feedback_modified(stdClass $grade, stdClass $data) {
        global $USER;

        $filekey = null;
        $draftareainfo = null;
        foreach ($data as $key => $value) {
            if (strpos($key, 'files_') === 0 && strpos($key, '_filemanager')) {
                $filekey = $key;
            }
        }
        if (isset($filekey)) {
            $draftareainfo = file_get_draft_area_info($data->$filekey);
            $filecount = $this->count_files($grade->id, ASSIGNFEEDBACK_FILE_FILEAREA);
            if ($filecount != $draftareainfo['filecount']) {
                return true;
            } else {
                                $usercontext = context_user::instance($USER->id);
                $fs = get_file_storage();
                $draftfiles = $fs->get_area_files($usercontext->id, 'user', 'draft', $data->$filekey, 'id', true);
                $files = $fs->get_area_files($this->assignment->get_context()->id,
                                     'assignfeedback_file',
                                     ASSIGNFEEDBACK_FILE_FILEAREA,
                                     $grade->id,
                                     'id',
                                     false);
                foreach ($files as $key => $file) {
                                        $matchflag = false;
                    foreach ($draftfiles as $draftkey => $draftfile) {
                        if (!$file->is_directory()) {
                                                        if ($draftfile->get_filename() == $file->get_filename()) {
                                                                                                if ($draftfile->get_contenthash() != $file->get_contenthash() ||
                                        $draftfile->get_filepath() != $file->get_filepath()) {
                                    return true;
                                }
                                                                $matchflag = true;
                                                                                                break;
                            }
                        }
                    }
                                        if (!$matchflag) {
                        return true;
                    }
                }
            }
        }
        return false;
    }

    
    private function copy_area_files(file_storage $fs,
                                     $fromcontextid,
                                     $fromcomponent,
                                     $fromfilearea,
                                     $fromitemid,
                                     $tocontextid,
                                     $tocomponent,
                                     $tofilearea,
                                     $toitemid) {

        $newfilerecord = new stdClass();
        $newfilerecord->contextid = $tocontextid;
        $newfilerecord->component = $tocomponent;
        $newfilerecord->filearea = $tofilearea;
        $newfilerecord->itemid = $toitemid;

        if ($files = $fs->get_area_files($fromcontextid, $fromcomponent, $fromfilearea, $fromitemid)) {
            foreach ($files as $file) {
                if ($file->is_directory() and $file->get_filepath() === '/') {
                                                                                continue;
                }
                $newfile = $fs->create_file_from_storedfile($newfilerecord, $file);
            }
        }
        return true;
    }

    
    public function get_form_elements_for_user($grade, MoodleQuickForm $mform, stdClass $data, $userid) {

        $fileoptions = $this->get_file_options();
        $gradeid = $grade ? $grade->id : 0;
        $elementname = 'files_' . $userid;

        $data = file_prepare_standard_filemanager($data,
                                                  $elementname,
                                                  $fileoptions,
                                                  $this->assignment->get_context(),
                                                  'assignfeedback_file',
                                                  ASSIGNFEEDBACK_FILE_FILEAREA,
                                                  $gradeid);
        $mform->addElement('filemanager', $elementname . '_filemanager', $this->get_name(), null, $fileoptions);

        return true;
    }

    
    private function count_files($gradeid, $area) {

        $fs = get_file_storage();
        $files = $fs->get_area_files($this->assignment->get_context()->id,
                                     'assignfeedback_file',
                                     $area,
                                     $gradeid,
                                     'id',
                                     false);

        return count($files);
    }

    
    public function update_file_count($grade) {
        global $DB;

        $filefeedback = $this->get_file_feedback($grade->id);
        if ($filefeedback) {
            $filefeedback->numfiles = $this->count_files($grade->id, ASSIGNFEEDBACK_FILE_FILEAREA);
            return $DB->update_record('assignfeedback_file', $filefeedback);
        } else {
            $filefeedback = new stdClass();
            $filefeedback->numfiles = $this->count_files($grade->id, ASSIGNFEEDBACK_FILE_FILEAREA);
            $filefeedback->grade = $grade->id;
            $filefeedback->assignment = $this->assignment->get_instance()->id;
            return $DB->insert_record('assignfeedback_file', $filefeedback) > 0;
        }
    }

    
    public function save(stdClass $grade, stdClass $data) {
        $fileoptions = $this->get_file_options();

                foreach ($data as $key => $value) {
            if (strpos($key, 'files_') === 0 && strpos($key, '_filemanager')) {
                $elementname = substr($key, 0, strpos($key, '_filemanager'));
            }
        }

        $data = file_postupdate_standard_filemanager($data,
                                                     $elementname,
                                                     $fileoptions,
                                                     $this->assignment->get_context(),
                                                     'assignfeedback_file',
                                                     ASSIGNFEEDBACK_FILE_FILEAREA,
                                                     $grade->id);

        return $this->update_file_count($grade);
    }

    
    public function view_summary(stdClass $grade, & $showviewlink) {

        $count = $this->count_files($grade->id, ASSIGNFEEDBACK_FILE_FILEAREA);

                $showviewlink = $count > ASSIGNFEEDBACK_FILE_MAXSUMMARYFILES;

        if ($count <= ASSIGNFEEDBACK_FILE_MAXSUMMARYFILES) {
            return $this->assignment->render_area_files('assignfeedback_file',
                                                        ASSIGNFEEDBACK_FILE_FILEAREA,
                                                        $grade->id);
        } else {
            return get_string('countfiles', 'assignfeedback_file', $count);
        }
    }

    
    public function view(stdClass $grade) {
        return $this->assignment->render_area_files('assignfeedback_file',
                                                    ASSIGNFEEDBACK_FILE_FILEAREA,
                                                    $grade->id);
    }

    
    public function delete_instance() {
        global $DB;
                $DB->delete_records('assignfeedback_file',
                            array('assignment'=>$this->assignment->get_instance()->id));

        return true;
    }

    
    public function is_empty(stdClass $grade) {
        return $this->count_files($grade->id, ASSIGNFEEDBACK_FILE_FILEAREA) == 0;
    }

    
    public function get_file_areas() {
        return array(ASSIGNFEEDBACK_FILE_FILEAREA=>$this->get_name());
    }

    
    public function can_upgrade($type, $version) {
        if (($type == 'upload' || $type == 'uploadsingle') && $version >= 2011112900) {
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
                            stdClass $grade,
                            & $log) {
        global $DB;

                $this->assignment->copy_area_files_for_upgrade($oldcontext->id,
                                                        'mod_assignment',
                                                        'response',
                                                        $oldsubmission->id,
                                                        $this->assignment->get_context()->id,
                                                        'assignfeedback_file',
                                                        ASSIGNFEEDBACK_FILE_FILEAREA,
                                                        $grade->id);

                $filefeedback = new stdClass();
        $filefeedback->numfiles = $this->count_files($grade->id, ASSIGNFEEDBACK_FILE_FILEAREA);
        $filefeedback->grade = $grade->id;
        $filefeedback->assignment = $this->assignment->get_instance()->id;
        if (!$DB->insert_record('assignfeedback_file', $filefeedback) > 0) {
            $log .= get_string('couldnotconvertgrade', 'mod_assign', $grade->userid);
            return false;
        }
        return true;
    }

    
    public function get_grading_batch_operations() {
        return array('uploadfiles'=>get_string('uploadfiles', 'assignfeedback_file'));
    }

    
    public function view_batch_upload_files($users) {
        global $CFG, $DB, $USER;

        require_capability('mod/assign:grade', $this->assignment->get_context());
        require_once($CFG->dirroot . '/mod/assign/feedback/file/batchuploadfilesform.php');
        require_once($CFG->dirroot . '/mod/assign/renderable.php');

        $formparams = array('cm'=>$this->assignment->get_course_module()->id,
                            'users'=>$users,
                            'context'=>$this->assignment->get_context());

        $usershtml = '';

        $usercount = 0;
        foreach ($users as $userid) {
            if ($usercount >= ASSIGNFEEDBACK_FILE_MAXSUMMARYUSERS) {
                $moreuserscount = count($users) - ASSIGNFEEDBACK_FILE_MAXSUMMARYUSERS;
                $usershtml .= get_string('moreusers', 'assignfeedback_file', $moreuserscount);
                break;
            }
            $user = $DB->get_record('user', array('id'=>$userid), '*', MUST_EXIST);

            $usersummary = new assign_user_summary($user,
                                                   $this->assignment->get_course()->id,
                                                   has_capability('moodle/site:viewfullnames',
                                                   $this->assignment->get_course_context()),
                                                   $this->assignment->is_blind_marking(),
                                                   $this->assignment->get_uniqueid_for_user($user->id),
                                                   get_extra_user_fields($this->assignment->get_context()));
            $usershtml .= $this->assignment->get_renderer()->render($usersummary);
            $usercount += 1;
        }

        $formparams['usershtml'] = $usershtml;

        $mform = new assignfeedback_file_batch_upload_files_form(null, $formparams);

        if ($mform->is_cancelled()) {
            redirect(new moodle_url('view.php',
                                    array('id'=>$this->assignment->get_course_module()->id,
                                          'action'=>'grading')));
            return;
        } else if ($data = $mform->get_data()) {
                        $data = file_postupdate_standard_filemanager($data,
                                                         'files',
                                                         $this->get_file_options(),
                                                         $this->assignment->get_context(),
                                                         'assignfeedback_file',
                                                         ASSIGNFEEDBACK_FILE_BATCH_FILEAREA,
                                                         $USER->id);
            $fs = get_file_storage();

                        foreach ($users as $userid) {
                $grade = $this->assignment->get_user_grade($userid, true);
                $this->assignment->notify_grade_modified($grade);

                $this->copy_area_files($fs,
                                       $this->assignment->get_context()->id,
                                       'assignfeedback_file',
                                       ASSIGNFEEDBACK_FILE_BATCH_FILEAREA,
                                       $USER->id,
                                       $this->assignment->get_context()->id,
                                       'assignfeedback_file',
                                       ASSIGNFEEDBACK_FILE_FILEAREA,
                                       $grade->id);

                $filefeedback = $this->get_file_feedback($grade->id);
                if ($filefeedback) {
                    $filefeedback->numfiles = $this->count_files($grade->id,
                                                                 ASSIGNFEEDBACK_FILE_FILEAREA);
                    $DB->update_record('assignfeedback_file', $filefeedback);
                } else {
                    $filefeedback = new stdClass();
                    $filefeedback->numfiles = $this->count_files($grade->id,
                                                                 ASSIGNFEEDBACK_FILE_FILEAREA);
                    $filefeedback->grade = $grade->id;
                    $filefeedback->assignment = $this->assignment->get_instance()->id;
                    $DB->insert_record('assignfeedback_file', $filefeedback);
                }
            }

                        $fs->delete_area_files($this->assignment->get_context()->id,
                                   'assignfeedback_file',
                                   ASSIGNFEEDBACK_FILE_BATCH_FILEAREA,
                                   $USER->id);

            redirect(new moodle_url('view.php',
                                    array('id'=>$this->assignment->get_course_module()->id,
                                          'action'=>'grading')));
            return;
        } else {

            $header = new assign_header($this->assignment->get_instance(),
                                        $this->assignment->get_context(),
                                        false,
                                        $this->assignment->get_course_module()->id,
                                        get_string('batchuploadfiles', 'assignfeedback_file'));
            $o = '';
            $o .= $this->assignment->get_renderer()->render($header);
            $o .= $this->assignment->get_renderer()->render(new assign_form('batchuploadfiles', $mform));
            $o .= $this->assignment->get_renderer()->render_footer();
        }

        return $o;
    }

    
    public function grading_batch_operation($action, $users) {

        if ($action == 'uploadfiles') {
            return $this->view_batch_upload_files($users);
        }
        return '';
    }

    
    public function view_upload_zip() {
        global $CFG, $USER;

        require_capability('mod/assign:grade', $this->assignment->get_context());
        require_once($CFG->dirroot . '/mod/assign/feedback/file/uploadzipform.php');
        require_once($CFG->dirroot . '/mod/assign/feedback/file/importziplib.php');
        require_once($CFG->dirroot . '/mod/assign/feedback/file/importzipform.php');

        $formparams = array('context'=>$this->assignment->get_context(),
                            'cm'=>$this->assignment->get_course_module()->id);
        $mform = new assignfeedback_file_upload_zip_form(null, $formparams);

        $o = '';

        $confirm = optional_param('confirm', 0, PARAM_BOOL);
        $renderer = $this->assignment->get_renderer();

                $importer = new assignfeedback_file_zip_importer();
        $contextid = $this->assignment->get_context()->id;

        if ($mform->is_cancelled()) {
            $importer->delete_import_files($contextid);
            $urlparams = array('id'=>$this->assignment->get_course_module()->id,
                               'action'=>'grading');
            $url = new moodle_url('view.php', $urlparams);
            redirect($url);
            return;
        } else if ($confirm) {
            $params = array('assignment'=>$this->assignment, 'importer'=>$importer);

            $mform = new assignfeedback_file_import_zip_form(null, $params);
            if ($mform->is_cancelled()) {
                $importer->delete_import_files($contextid);
                $urlparams = array('id'=>$this->assignment->get_course_module()->id,
                                   'action'=>'grading');
                $url = new moodle_url('view.php', $urlparams);
                redirect($url);
                return;
            }

            $o .= $importer->import_zip_files($this->assignment, $this);
            $importer->delete_import_files($contextid);
        } else if (($data = $mform->get_data()) &&
                   ($zipfile = $mform->save_stored_file('feedbackzip',
                                                        $contextid,
                                                        'assignfeedback_file',
                                                        ASSIGNFEEDBACK_FILE_IMPORT_FILEAREA,
                                                        $USER->id,
                                                        '/',
                                                        'import.zip',
                                                        true))) {

            $importer->extract_files_from_zip($zipfile, $contextid);

            $params = array('assignment'=>$this->assignment, 'importer'=>$importer);

            $mform = new assignfeedback_file_import_zip_form(null, $params);

            $header = new assign_header($this->assignment->get_instance(),
                                        $this->assignment->get_context(),
                                        false,
                                        $this->assignment->get_course_module()->id,
                                        get_string('confirmuploadzip', 'assignfeedback_file'));
            $o .= $renderer->render($header);
            $o .= $renderer->render(new assign_form('confirmimportzip', $mform));
            $o .= $renderer->render_footer();

        } else {

            $header = new assign_header($this->assignment->get_instance(),
                                        $this->assignment->get_context(),
                                        false,
                                        $this->assignment->get_course_module()->id,
                                        get_string('uploadzip', 'assignfeedback_file'));
            $o .= $renderer->render($header);
            $o .= $renderer->render(new assign_form('uploadfeedbackzip', $mform));
            $o .= $renderer->render_footer();
        }

        return $o;
    }

    
    public function view_page($action) {
        if ($action == 'uploadfiles') {
            $users = required_param('selectedusers', PARAM_SEQUENCE);
            return $this->view_batch_upload_files(explode(',', $users));
        }
        if ($action == 'uploadzip') {
            return $this->view_upload_zip();
        }

        return '';
    }

    
    public function get_grading_actions() {
        return array('uploadzip'=>get_string('uploadzip', 'assignfeedback_file'));
    }

    
    public function get_external_parameters() {
        return array(
            'files_filemanager' => new external_value(
                PARAM_INT,
                'The id of a draft area containing files for this feedback.',
                VALUE_OPTIONAL
            )
        );
    }

}
