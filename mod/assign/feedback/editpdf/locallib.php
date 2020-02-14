<?php



defined('MOODLE_INTERNAL') || die();

use \assignfeedback_editpdf\document_services;
use \assignfeedback_editpdf\page_editor;


class assign_feedback_editpdf extends assign_feedback_plugin {

    
    private $enabledcache = null;

    
    public function get_name() {
        return get_string('pluginname', 'assignfeedback_editpdf');
    }

    
    public function get_widget($userid, $grade, $readonly) {
        $attempt = -1;
        if ($grade) {
            $attempt = $grade->attemptnumber;
        } else {
            $grade = $this->assignment->get_user_grade($userid, true);
        }

        $feedbackfile = document_services::get_feedback_document($this->assignment->get_instance()->id,
                                                                 $userid,
                                                                 $attempt);

        $stampfiles = array();
        $fs = get_file_storage();
        $syscontext = context_system::instance();

                if ($files = $fs->get_area_files($syscontext->id,
                                         'assignfeedback_editpdf',
                                         'stamps',
                                         0,
                                         "filename",
                                         false)) {
            foreach ($files as $file) {
                $filename = $file->get_filename();
                if ($filename !== '.') {

                    $existingfile = $fs->get_file($this->assignment->get_context()->id,
                                                  'assignfeedback_editpdf',
                                                  'stamps',
                                                  $grade->id,
                                                  '/',
                                                  $file->get_filename());
                    if (!$existingfile) {
                        $newrecord = new stdClass();
                        $newrecord->contextid = $this->assignment->get_context()->id;
                        $newrecord->itemid = $grade->id;
                        $fs->create_file_from_storedfile($newrecord, $file);
                    }
                }
            }
        }

                if ($files = $fs->get_area_files($this->assignment->get_context()->id,
                                         'assignfeedback_editpdf',
                                         'stamps',
                                         $grade->id,
                                         "filename",
                                         false)) {
            foreach ($files as $file) {
                $filename = $file->get_filename();
                if ($filename !== '.') {
                    $url = moodle_url::make_pluginfile_url($this->assignment->get_context()->id,
                                                   'assignfeedback_editpdf',
                                                   'stamps',
                                                   $grade->id,
                                                   '/',
                                                   $file->get_filename(),
                                                   false);
                    array_push($stampfiles, $url->out());
                }
            }
        }

        $url = false;
        $filename = '';
        if ($feedbackfile) {
            $url = moodle_url::make_pluginfile_url($this->assignment->get_context()->id,
                                                   'assignfeedback_editpdf',
                                                   document_services::FINAL_PDF_FILEAREA,
                                                   $grade->id,
                                                   '/',
                                                   $feedbackfile->get_filename(),
                                                   false);
           $filename = $feedbackfile->get_filename();
        }

                $pagetotal = document_services::page_number_for_attempt($this->assignment->get_instance()->id,
                $userid,
                $attempt,
                $readonly);

        $widget = new assignfeedback_editpdf_widget($this->assignment->get_instance()->id,
                                                    $userid,
                                                    $attempt,
                                                    $url,
                                                    $filename,
                                                    $stampfiles,
                                                    $readonly,
                                                    $pagetotal);
        return $widget;
    }

    
    public function get_form_elements_for_user($grade, MoodleQuickForm $mform, stdClass $data, $userid) {
        global $PAGE;

        $attempt = -1;
        if ($grade) {
            $attempt = $grade->attemptnumber;
        }

        $renderer = $PAGE->get_renderer('assignfeedback_editpdf');

        $widget = $this->get_widget($userid, $grade, false);

        $html = $renderer->render($widget);
        $mform->addElement('static', 'editpdf', get_string('editpdf', 'assignfeedback_editpdf'), $html);
        $mform->addHelpButton('editpdf', 'editpdf', 'assignfeedback_editpdf');
        $mform->addElement('hidden', 'editpdf_source_userid', $userid);
        $mform->setType('editpdf_source_userid', PARAM_INT);
        $mform->setConstant('editpdf_source_userid', $userid);
    }

    
    public function is_feedback_modified(stdClass $grade, stdClass $data) {
                                        if (!empty($data->editpdf_source_userid)) {
            $sourceuserid = $data->editpdf_source_userid;
                        $sourcegrade = $this->assignment->get_user_grade($sourceuserid, true, $grade->attemptnumber);
            $pagenumbercount = document_services::page_number_for_attempt($this->assignment, $sourceuserid, $sourcegrade->attemptnumber);
            for ($i = 0; $i < $pagenumbercount; $i++) {
                                $draftannotations = page_editor::get_annotations($sourcegrade->id, $i, true);
                $nondraftannotations = page_editor::get_annotations($grade->id, $i, false);
                                if (count($draftannotations) != count($nondraftannotations)) {
                                        return true;
                } else {
                    $matches = 0;
                                        foreach ($nondraftannotations as $ndannotation) {
                        foreach ($draftannotations as $dannotation) {
                            foreach ($ndannotation as $key => $value) {
                                if ($key != 'id' && $value != $dannotation->{$key}) {
                                    continue 2;
                                }
                            }
                            $matches++;
                        }
                    }
                    if ($matches !== count($nondraftannotations)) {
                        return true;
                    }
                }
                                $draftcomments = page_editor::get_comments($sourcegrade->id, $i, true);
                $nondraftcomments = page_editor::get_comments($grade->id, $i, false);
                if (count($draftcomments) != count($nondraftcomments)) {
                    return true;
                } else {
                                        $matches = 0;
                    foreach ($nondraftcomments as $ndcomment) {
                        foreach ($draftcomments as $dcomment) {
                            foreach ($ndcomment as $key => $value) {
                                if ($key != 'id' && $value != $dcomment->{$key}) {
                                    continue 2;
                                }
                            }
                            $matches++;
                        }
                    }
                    if ($matches !== count($nondraftcomments)) {
                        return true;
                    }
                }
            }
        }
        return false;
    }

    
    public function save(stdClass $grade, stdClass $data) {
                if (!empty($data->editpdf_source_userid)) {
            $sourceuserid = $data->editpdf_source_userid;
                        if ($sourceuserid != $grade->userid) {
                page_editor::copy_drafts_from_to($this->assignment, $grade, $sourceuserid);
            }
        }
        if (page_editor::has_annotations_or_comments($grade->id, true)) {
            document_services::generate_feedback_document($this->assignment, $grade->userid, $grade->attemptnumber);
        }

        return true;
    }

    
    public function view_summary(stdClass $grade, & $showviewlink) {
        $showviewlink = false;
        return $this->view($grade);
    }

    
    public function view(stdClass $grade) {
        global $PAGE;
        $html = '';
                if (page_editor::has_annotations_or_comments($grade->id, false)) {
            $html = $this->assignment->render_area_files('assignfeedback_editpdf',
                                                         document_services::FINAL_PDF_FILEAREA,
                                                         $grade->id);

                        $renderer = $PAGE->get_renderer('assignfeedback_editpdf');
            $widget = $this->get_widget($grade->userid, $grade, true);

            $html .= $renderer->render($widget);
        }
        return $html;
    }

    
    public function is_empty(stdClass $grade) {
        global $DB;

        $comments = $DB->count_records('assignfeedback_editpdf_cmnt', array('gradeid'=>$grade->id, 'draft'=>0));
        $annotations = $DB->count_records('assignfeedback_editpdf_annot', array('gradeid'=>$grade->id, 'draft'=>0));
        return $comments == 0 && $annotations == 0;
    }

    
    public function delete_instance() {
        global $DB;
        $grades = $DB->get_records('assign_grades', array('assignment'=>$this->assignment->get_instance()->id), '', 'id');
        if ($grades) {
            list($gradeids, $params) = $DB->get_in_or_equal(array_keys($grades), SQL_PARAMS_NAMED);
            $DB->delete_records_select('assignfeedback_editpdf_annot', 'gradeid ' . $gradeids, $params);
            $DB->delete_records_select('assignfeedback_editpdf_cmnt', 'gradeid ' . $gradeids, $params);
        }
        return true;
    }

    
    public function is_enabled() {
        if ($this->enabledcache === null) {
            $testpath = assignfeedback_editpdf\pdf::test_gs_path(false);
            $this->enabledcache = ($testpath->status == assignfeedback_editpdf\pdf::GSPATH_OK);
        }
        return $this->enabledcache;
    }
    
    public function is_configurable() {
        return false;
    }

    
    public function get_file_areas() {
        return array(document_services::FINAL_PDF_FILEAREA => $this->get_name());
    }

    
    public function supports_review_panel() {
        return true;
    }
}
