<?php



defined('MOODLE_INTERNAL') || die('Direct access to this script is forbidden.');

require_once($CFG->libdir.'/formslib.php');
require_once($CFG->dirroot.'/mod/assign/feedback/offline/importgradeslib.php');


class assignfeedback_offline_import_grades_form extends moodleform implements renderable {

    
    public function definition() {
        global $CFG, $PAGE, $DB;

        $mform = $this->_form;
        $params = $this->_customdata;

        $renderer = $PAGE->get_renderer('assign');

                $assignment = $params['assignment'];
        $csvdata = $params['csvdata'];
        $gradeimporter = $params['gradeimporter'];
        $update = false;

        $ignoremodified = $params['ignoremodified'];
        $draftid = $params['draftid'];

        if (!$gradeimporter) {
            print_error('invalidarguments');
            return;
        }

        if ($csvdata) {
            $gradeimporter->parsecsv($csvdata);
        }

        $scaleoptions = null;
        if ($assignment->get_instance()->grade < 0) {
            if ($scale = $DB->get_record('scale', array('id'=>-($assignment->get_instance()->grade)))) {
                $scaleoptions = make_menu_from_list($scale->scale);
            }
        }
        if (!$gradeimporter->init()) {
            $thisurl = new moodle_url('/mod/assign/view.php', array('action'=>'viewpluginpage',
                                                                     'pluginsubtype'=>'assignfeedback',
                                                                     'plugin'=>'offline',
                                                                     'pluginaction'=>'uploadgrades',
                                                                     'id'=>$assignment->get_course_module()->id));
            print_error('invalidgradeimport', 'assignfeedback_offline', $thisurl);
            return;
        }

        $mform->addElement('header', 'importgrades', get_string('importgrades', 'assignfeedback_offline'));

        $updates = array();
        while ($record = $gradeimporter->next()) {
            $user = $record->user;
            $grade = $record->grade;
            $modified = $record->modified;
            $userdesc = fullname($user);
            if ($assignment->is_blind_marking()) {
                $userdesc = get_string('hiddenuser', 'assign') . $assignment->get_uniqueid_for_user($user->id);
            }

            $usergrade = $assignment->get_user_grade($user->id, false);
                        $skip = false;

            $stalemodificationdate = ($usergrade && $usergrade->timemodified > ($modified + 60));

            if (!empty($scaleoptions)) {
                                $scaleindex = array_search($grade, $scaleoptions);
                if ($scaleindex !== false) {
                    $grade = $scaleindex;
                } else {
                    $grade = '';
                }
            } else {
                $grade = unformat_float($grade);
            }

            if ($usergrade && $usergrade->grade == $grade) {
                                $skip = true;
            } else if (!isset($grade) || $grade === '' || $grade < 0) {
                                $skip = true;
            } else if (!$ignoremodified && $stalemodificationdate) {
                                $skip = true;
            } else if ($assignment->grading_disabled($user->id)) {
                                $skip = true;
            } else if (($assignment->get_instance()->grade > -1) &&
                      (($grade < 0) || ($grade > $assignment->get_instance()->grade))) {
                                $skip = true;
            }

            if (!$skip) {
                $update = true;
                if (!empty($scaleoptions)) {
                    $formattedgrade = $scaleoptions[$grade];
                } else {
                    $gradeitem = $assignment->get_grade_item();
                    $formattedgrade = format_float($grade, $gradeitem->get_decimals());
                }
                $updates[] = get_string('gradeupdate', 'assignfeedback_offline',
                                            array('grade'=>$formattedgrade, 'student'=>$userdesc));
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
                    }
                    if ($newvalue != $oldvalue) {
                        $update = true;
                        $updates[] = get_string('feedbackupdate', 'assignfeedback_offline',
                                                    array('text'=>$newvalue, 'field'=>$description, 'student'=>$userdesc));
                    }
                }
            }

        }
        $gradeimporter->close(false);

        if ($update) {
            $mform->addElement('html', $renderer->list_block_contents(array(), $updates));
        } else {
            $mform->addElement('html', get_string('nochanges', 'assignfeedback_offline'));
        }

        $mform->addElement('hidden', 'id', $assignment->get_course_module()->id);
        $mform->setType('id', PARAM_INT);
        $mform->addElement('hidden', 'action', 'viewpluginpage');
        $mform->setType('action', PARAM_ALPHA);
        $mform->addElement('hidden', 'confirm', 'true');
        $mform->setType('confirm', PARAM_BOOL);
        $mform->addElement('hidden', 'plugin', 'offline');
        $mform->setType('plugin', PARAM_PLUGIN);
        $mform->addElement('hidden', 'pluginsubtype', 'assignfeedback');
        $mform->setType('pluginsubtype', PARAM_PLUGIN);
        $mform->addElement('hidden', 'pluginaction', 'uploadgrades');
        $mform->setType('pluginaction', PARAM_ALPHA);
        $mform->addElement('hidden', 'importid', $gradeimporter->importid);
        $mform->setType('importid', PARAM_INT);

        $mform->addElement('hidden', 'encoding', $gradeimporter->get_encoding());
        $mform->setType('encoding', PARAM_ALPHAEXT);
        $mform->addElement('hidden', 'separator', $gradeimporter->get_separator());
        $mform->setType('separator', PARAM_ALPHA);

        $mform->addElement('hidden', 'ignoremodified', $ignoremodified);
        $mform->setType('ignoremodified', PARAM_BOOL);
        $mform->addElement('hidden', 'draftid', $draftid);
        $mform->setType('draftid', PARAM_INT);
        if ($update) {
            $this->add_action_buttons(true, get_string('confirm'));
        } else {
            $mform->addElement('cancel');
            $mform->closeHeaderBefore('cancel');
        }

    }
}

