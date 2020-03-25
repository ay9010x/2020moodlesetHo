<?php




defined('MOODLE_INTERNAL') || die();

require_once($CFG->dirroot . '/lib/formslib.php');

class workshop_submission_form extends moodleform {

    function definition() {
        $mform = $this->_form;

        $current        = $this->_customdata['current'];
        $workshop       = $this->_customdata['workshop'];
        $contentopts    = $this->_customdata['contentopts'];
        $attachmentopts = $this->_customdata['attachmentopts'];

        $mform->addElement('header', 'general', get_string('submission', 'workshop'));

        $mform->addElement('text', 'title', get_string('submissiontitle', 'workshop'));
        $mform->setType('title', PARAM_TEXT);
        $mform->addRule('title', null, 'required', null, 'client');

        $mform->addElement('editor', 'content_editor', get_string('submissioncontent', 'workshop'), null, $contentopts);
        $mform->setType('content', PARAM_RAW);

        if ($workshop->nattachments > 0) {
            $mform->addElement('static', 'filemanagerinfo', get_string('nattachments', 'workshop'), $workshop->nattachments);
            $mform->addElement('filemanager', 'attachment_filemanager', get_string('submissionattachment', 'workshop'),
                                null, $attachmentopts);
        }

        $mform->addElement('hidden', 'id', $current->id);
        $mform->setType('id', PARAM_INT);

        $mform->addElement('hidden', 'cmid', $workshop->cm->id);
        $mform->setType('cmid', PARAM_INT);

        $mform->addElement('hidden', 'edit', 1);
        $mform->setType('edit', PARAM_INT);

        $mform->addElement('hidden', 'example', 0);
        $mform->setType('example', PARAM_INT);

        $this->add_action_buttons();

        $this->set_data($current);
    }

    function validation($data, $files) {
        global $CFG, $USER, $DB;

        $errors = parent::validation($data, $files);

        if (empty($data['id']) and empty($data['example'])) {
                        $sql = "SELECT COUNT(s.id)
                      FROM {workshop_submissions} s
                      JOIN {workshop} w ON (s.workshopid = w.id)
                      JOIN {course_modules} cm ON (w.id = cm.instance)
                      JOIN {modules} m ON (m.name = 'workshop' AND m.id = cm.module)
                     WHERE cm.id = ? AND s.authorid = ? AND s.example = 0";

            if ($DB->count_records_sql($sql, array($data['cmid'], $USER->id))) {
                $errors['title'] = get_string('err_multiplesubmissions', 'mod_workshop');
            }
        }

        if (isset($data['attachment_filemanager']) and isset($this->_customdata['workshop']->submissionfiletypes)) {
            $whitelist = workshop::normalize_file_extensions($this->_customdata['workshop']->submissionfiletypes);
            if ($whitelist) {
                $draftfiles = file_get_drafarea_files($data['attachment_filemanager']);
                if ($draftfiles) {
                    $wrongfiles = array();
                    foreach ($draftfiles->list as $file) {
                        if (!workshop::is_allowed_file_type($file->filename, $whitelist)) {
                            $wrongfiles[] = $file->filename;
                        }
                    }
                    if ($wrongfiles) {
                        $a = array(
                            'whitelist' => workshop::clean_file_extensions($whitelist),
                            'wrongfiles' => implode(', ', $wrongfiles),
                        );
                        $errors['attachment_filemanager'] = get_string('err_wrongfileextension', 'mod_workshop', $a);
                    }
                }
            }
        }

        return $errors;
    }
}
