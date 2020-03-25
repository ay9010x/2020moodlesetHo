<?php



defined('MOODLE_INTERNAL') || die();

require_once($CFG->dirroot.'/grade/grading/lib.php');


class assign_feedback_offline extends assign_feedback_plugin {

    
    private $enabledcache = null;

    
    public function get_name() {
        return get_string('pluginname', 'assignfeedback_offline');
    }

    
    public function get_form_elements($grade, MoodleQuickForm $mform, stdClass $data) {
        return false;
    }

    
    public function is_empty(stdClass $grade) {
        return true;
    }

    
    public function is_feedback_modified(stdClass $grade, stdClass $data) {
        return false;
    }

    
    public function process_import_grades($draftid, $importid, $ignoremodified, $encoding = 'utf-8', $separator = 'comma') {
        global $USER, $DB;

        require_sesskey();
        require_capability('mod/assign:grade', $this->assignment->get_context());

        $gradeimporter = new assignfeedback_offline_grade_importer($importid, $this->assignment, $encoding, $separator);

        $context = context_user::instance($USER->id);
        $fs = get_file_storage();
        if (!$files = $fs->get_area_files($context->id, 'user', 'draft', $draftid, 'id DESC', false)) {
            redirect(new moodle_url('view.php',
                                array('id'=>$this->assignment->get_course_module()->id,
                                      'action'=>'grading')));
            return;
        }
        $file = reset($files);

        $csvdata = $file->get_content();

        if ($csvdata) {
            $gradeimporter->parsecsv($csvdata);
        }
        if (!$gradeimporter->init()) {
            $thisurl = new moodle_url('/mod/assign/view.php', array('action'=>'viewpluginpage',
                                                                     'pluginsubtype'=>'assignfeedback',
                                                                     'plugin'=>'offline',
                                                                     'pluginaction'=>'uploadgrades',
                                                                     'id' => $this->assignment->get_course_module()->id));
            print_error('invalidgradeimport', 'assignfeedback_offline', $thisurl);
            return;
        }
                $scaleoptions = null;
        if ($this->assignment->get_instance()->grade < 0) {
            if ($scale = $DB->get_record('scale', array('id'=>-($this->assignment->get_instance()->grade)))) {
                $scaleoptions = make_menu_from_list($scale->scale);
            }
        }
                $adminconfig = $this->assignment->get_admin_config();
        $gradebookplugin = $adminconfig->feedback_plugin_for_gradebook;

        $updatecount = 0;
        while ($record = $gradeimporter->next()) {
            $user = $record->user;
            $modified = $record->modified;
            $userdesc = fullname($user);
            $usergrade = $this->assignment->get_user_grade($user->id, false);

            if (!empty($scaleoptions)) {
                                $scaleindex = array_search($record->grade, $scaleoptions);
                if ($scaleindex !== false) {
                    $record->grade = $scaleindex;
                } else {
                    $record->grade = '';
                }
            } else {
                $record->grade = unformat_float($record->grade);
            }

                        $skip = false;
            $stalemodificationdate = ($usergrade && $usergrade->timemodified > ($modified + 60));

            if ($usergrade && $usergrade->grade == $record->grade) {
                                $skip = true;
            } else if (!isset($record->grade) || $record->grade === '' || $record->grade < 0) {
                                $skip = true;
            } else if (!$ignoremodified && $stalemodificationdate) {
                                $skip = true;
            } else if ($this->assignment->grading_disabled($record->user->id)) {
                                $skip = true;
            } else if (($this->assignment->get_instance()->grade > -1) &&
                      (($record->grade < 0) || ($record->grade > $this->assignment->get_instance()->grade))) {
                                $skip = true;
            }

            if (!$skip) {
                $grade = $this->assignment->get_user_grade($record->user->id, true);

                $grade->grade = $record->grade;
                $grade->grader = $USER->id;
                if ($this->assignment->update_grade($grade)) {
                    $this->assignment->notify_grade_modified($grade);
                    $updatecount += 1;
                }
            }

            if ($ignoremodified || !$stalemodificationdate) {
                foreach ($record->feedback as $feedback) {
                    $plugin = $feedback['plugin'];
                    $field = $feedback['field'];
                    $newvalue = $feedback['value'];
                    $description = $feedback['description'];
                    $oldvalue = '';
                    if ($usergrade) {
                        $oldvalue = $plugin->get_editor_text($field, $usergrade->id);
                        if (empty($oldvalue)) {
                            $oldvalue = '';
                        }
                    }
                    if ($newvalue != $oldvalue) {
                        $updatecount += 1;
                        $grade = $this->assignment->get_user_grade($record->user->id, true);
                        $this->assignment->notify_grade_modified($grade);
                        $plugin->set_editor_text($field, $newvalue, $grade->id);

                                                if (($plugin->get_subtype() . '_' . $plugin->get_type()) == $gradebookplugin) {
                            $grade->feedbacktext = $plugin->text_for_gradebook($grade);
                            $grade->feedbackformat = $plugin->format_for_gradebook($grade);
                            $this->assignment->update_grade($grade);
                        }
                    }
                }
            }
        }
        $gradeimporter->close(true);

        $renderer = $this->assignment->get_renderer();
        $o = '';

        $o .= $renderer->render(new assign_header($this->assignment->get_instance(),
                                                  $this->assignment->get_context(),
                                                  false,
                                                  $this->assignment->get_course_module()->id,
                                                  get_string('importgrades', 'assignfeedback_offline')));
        $o .= $renderer->box(get_string('updatedgrades', 'assignfeedback_offline', $updatecount));
        $url = new moodle_url('view.php',
                              array('id'=>$this->assignment->get_course_module()->id,
                                    'action'=>'grading'));
        $o .= $renderer->continue_button($url);
        $o .= $renderer->render_footer();
        return $o;
    }

    
    public function upload_grades() {
        global $CFG, $USER;

        require_capability('mod/assign:grade', $this->assignment->get_context());
        require_once($CFG->dirroot . '/mod/assign/feedback/offline/uploadgradesform.php');
        require_once($CFG->dirroot . '/mod/assign/feedback/offline/importgradesform.php');
        require_once($CFG->dirroot . '/mod/assign/feedback/offline/importgradeslib.php');
        require_once($CFG->libdir . '/csvlib.class.php');

        $mform = new assignfeedback_offline_upload_grades_form(null,
                                                              array('context'=>$this->assignment->get_context(),
                                                                    'cm'=>$this->assignment->get_course_module()->id));

        $o = '';

        $confirm = optional_param('confirm', 0, PARAM_BOOL);
        $renderer = $this->assignment->get_renderer();

        if ($mform->is_cancelled()) {
            redirect(new moodle_url('view.php',
                                    array('id'=>$this->assignment->get_course_module()->id,
                                          'action'=>'grading')));
            return;
        } else if (($data = $mform->get_data()) &&
                   ($csvdata = $mform->get_file_content('gradesfile'))) {

            $importid = csv_import_reader::get_new_iid('assignfeedback_offline');
            $gradeimporter = new assignfeedback_offline_grade_importer($importid, $this->assignment,
                    $data->encoding, $data->separator);
                        $ignoremodified = !empty($data->ignoremodified);

            $draftid = $data->gradesfile;

            
            $mform = new assignfeedback_offline_import_grades_form(null, array('assignment'=>$this->assignment,
                                                                       'csvdata'=>$csvdata,
                                                                       'ignoremodified'=>$ignoremodified,
                                                                       'gradeimporter'=>$gradeimporter,
                                                                       'draftid'=>$draftid));

            $o .= $renderer->render(new assign_header($this->assignment->get_instance(),
                                                            $this->assignment->get_context(),
                                                            false,
                                                            $this->assignment->get_course_module()->id,
                                                            get_string('confirmimport', 'assignfeedback_offline')));
            $o .= $renderer->render(new assign_form('confirmimport', $mform));
            $o .= $renderer->render_footer();
        } else if ($confirm) {
            $importid = optional_param('importid', 0, PARAM_INT);
            $draftid = optional_param('draftid', 0, PARAM_INT);
            $encoding = optional_param('encoding', 'utf-8', PARAM_ALPHAEXT);
            $separator = optional_param('separator', 'comma', PARAM_ALPHA);
            $ignoremodified = optional_param('ignoremodified', 0, PARAM_BOOL);
            $gradeimporter = new assignfeedback_offline_grade_importer($importid, $this->assignment, $encoding, $separator);
            $mform = new assignfeedback_offline_import_grades_form(null, array('assignment'=>$this->assignment,
                                                                       'csvdata'=>'',
                                                                       'ignoremodified'=>$ignoremodified,
                                                                       'gradeimporter'=>$gradeimporter,
                                                                       'draftid'=>$draftid));
            if ($mform->is_cancelled()) {
                redirect(new moodle_url('view.php',
                                        array('id'=>$this->assignment->get_course_module()->id,
                                              'action'=>'grading')));
                return;
            }

            $o .= $this->process_import_grades($draftid, $importid, $ignoremodified, $encoding, $separator);
        } else {

            $o .= $renderer->render(new assign_header($this->assignment->get_instance(),
                                                            $this->assignment->get_context(),
                                                            false,
                                                            $this->assignment->get_course_module()->id,
                                                            get_string('uploadgrades', 'assignfeedback_offline')));
            $o .= $renderer->render(new assign_form('batchuploadfiles', $mform));
            $o .= $renderer->render_footer();
        }

        return $o;
    }

    
    public function download_grades() {
        global $CFG;

        require_capability('mod/assign:grade', $this->assignment->get_context());
        require_once($CFG->dirroot . '/mod/assign/gradingtable.php');

        $groupmode = groups_get_activity_groupmode($this->assignment->get_course_module());
                $groupid = 0;
        $groupname = '';
        if ($groupmode) {
            $groupid = groups_get_activity_group($this->assignment->get_course_module(), true);
            $groupname = groups_get_group_name($groupid) . '-';
        }
        $filename = clean_filename(get_string('offlinegradingworksheet', 'assignfeedback_offline') . '-' .
                                   $this->assignment->get_course()->shortname . '-' .
                                   $this->assignment->get_instance()->name . '-' .
                                   $groupname .
                                   $this->assignment->get_course_module()->id);

        $table = new assign_grading_table($this->assignment, 0, '', 0, false, $filename);

        $table->out(0, false);
        return;
    }

    
    public function view_page($action) {
        if ($action == 'downloadgrades') {
            return $this->download_grades();
        } else if ($action == 'uploadgrades') {
            return $this->upload_grades();
        }

        return '';
    }

    
    public function get_grading_actions() {
        return array('uploadgrades'=>get_string('uploadgrades', 'assignfeedback_offline'),
                    'downloadgrades'=>get_string('downloadgrades', 'assignfeedback_offline'));
    }

    
    public function is_enabled() {
        if ($this->enabledcache === null) {
            $gradingmanager = get_grading_manager($this->assignment->get_context(), 'mod_assign', 'submissions');
            $controller = $gradingmanager->get_active_controller();
            $active = !empty($controller);

            if ($active) {
                $this->enabledcache = false;
            } else {
                $this->enabledcache = parent::is_enabled();
            }
        }
        return $this->enabledcache;
    }

    
    public function has_user_summary() {
        return false;
    }

}
