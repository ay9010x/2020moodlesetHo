<?php



defined('MOODLE_INTERNAL') || die('Direct access to this script is forbidden.');

require_once($CFG->libdir.'/formslib.php');
require_once($CFG->dirroot.'/mod/assign/feedback/file/importziplib.php');


class assignfeedback_file_import_zip_form extends moodleform implements renderable {

    
    public function definition() {
        global $CFG, $PAGE;

        $mform = $this->_form;
        $params = $this->_customdata;

        $renderer = $PAGE->get_renderer('assign');

                $assignment = $params['assignment'];
        $contextid = $assignment->get_context()->id;
        $importer = $params['importer'];
        $update = false;

        if (!$importer) {
            print_error('invalidarguments');
            return;
        }

        $files = $importer->get_import_files($contextid);

        $mform->addElement('header', 'uploadzip', get_string('confirmuploadzip', 'assignfeedback_file'));

        $currentgroup = groups_get_activity_group($assignment->get_course_module(), true);
        $allusers = $assignment->list_participants($currentgroup, false);
        $participants = array();
        foreach ($allusers as $user) {
            $participants[$assignment->get_uniqueid_for_user($user->id)] = $user;
        }

        $fs = get_file_storage();

        $updates = array();
        foreach ($files as $unzippedfile) {
            $user = null;
            $plugin = null;
            $filename = '';

            if ($importer->is_valid_filename_for_import($assignment, $unzippedfile, $participants, $user, $plugin, $filename)) {
                if ($importer->is_file_modified($assignment, $user, $plugin, $filename, $unzippedfile)) {
                                        $userdesc = fullname($user);
                    $path = pathinfo($filename);
                    if ($assignment->is_blind_marking()) {
                        $userdesc = get_string('hiddenuser', 'assign') .
                                    $assignment->get_uniqueid_for_user($user->id);
                    }
                    $grade = $assignment->get_user_grade($user->id, false);

                    $exists = false;
                    if ($grade) {
                        $exists = $fs->file_exists($contextid,
                                                   'assignfeedback_file',
                                                   ASSIGNFEEDBACK_FILE_FILEAREA,
                                                   $grade->id,
                                                   $path['dirname'],
                                                   $path['basename']);
                    }

                    if (!$grade || !$exists) {
                        $updates[] = get_string('feedbackfileadded', 'assignfeedback_file',
                                            array('filename'=>$filename, 'student'=>$userdesc));
                    } else {
                        $updates[] = get_string('feedbackfileupdated', 'assignfeedback_file',
                                            array('filename'=>$filename, 'student'=>$userdesc));
                    }
                }
            }
        }

        if (count($updates)) {
            $mform->addElement('html', $renderer->list_block_contents(array(), $updates));
        } else {
            $mform->addElement('html', get_string('nochanges', 'assignfeedback_file'));
        }

        $mform->addElement('hidden', 'id', $assignment->get_course_module()->id);
        $mform->setType('id', PARAM_INT);
        $mform->addElement('hidden', 'action', 'viewpluginpage');
        $mform->setType('action', PARAM_ALPHA);
        $mform->addElement('hidden', 'confirm', 'true');
        $mform->setType('confirm', PARAM_BOOL);
        $mform->addElement('hidden', 'plugin', 'file');
        $mform->setTYpe('plugin', PARAM_PLUGIN);
        $mform->addElement('hidden', 'pluginsubtype', 'assignfeedback');
        $mform->setTYpe('pluginsubtype', PARAM_PLUGIN);
        $mform->addElement('hidden', 'pluginaction', 'uploadzip');
        $mform->setType('pluginaction', PARAM_ALPHA);
        if (count($updates)) {
            $this->add_action_buttons(true, get_string('confirm'));
        } else {
            $mform->addElement('cancel');
            $mform->closeHeaderBefore('cancel');
        }
    }
}

