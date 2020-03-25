<?php



defined('MOODLE_INTERNAL') || die();


class assignfeedback_file_zip_importer {

    
    public function is_valid_filename_for_import($assignment, $fileinfo, $participants, & $user, & $plugin, & $filename) {
        if ($fileinfo->is_directory()) {
            return false;
        }

                if (strpos($fileinfo->get_filename(), '.') === 0) {
            return false;
        }
                if (strpos($fileinfo->get_filename(), '~') === 0) {
            return false;
        }

        $info = explode('_', $fileinfo->get_filepath() . $fileinfo->get_filename(), 5);

        if (count($info) < 5) {
            return false;
        }

        $participantid = $info[1];
        $filename = $info[4];
        $plugin = $assignment->get_plugin_by_type($info[2], $info[3]);

        if (!is_numeric($participantid)) {
            return false;
        }

        if (!$plugin) {
            return false;
        }

                $participantid += 0;

        if (empty($participants[$participantid])) {
            return false;
        }

        $user = $participants[$participantid];
        return true;
    }

    
    public function is_file_modified($assignment, $user, $plugin, $filename, $fileinfo) {
        $sg = null;

        if ($plugin->get_subtype() == 'assignsubmission') {
            $sg = $assignment->get_user_submission($user->id, false);
        } else if ($plugin->get_subtype() == 'assignfeedback') {
            $sg = $assignment->get_user_grade($user->id, false);
        } else {
            return false;
        }

        if (!$sg) {
            return true;
        }
        foreach ($plugin->get_files($sg, $user) as $pluginfilename => $file) {
            if ($pluginfilename == $filename) {
                                $contenthash = '';
                if (is_array($file)) {
                    $content = reset($file);
                    $contenthash = sha1($content);
                } else {
                    $contenthash = $file->get_contenthash();
                }
                if ($contenthash != $fileinfo->get_contenthash()) {
                    return true;
                } else {
                    return false;
                }
            }
        }
        return true;
    }

    
    public function delete_import_files($contextid) {
        global $USER;

        $fs = get_file_storage();

        return $fs->delete_area_files($contextid,
                                      'assignfeedback_file',
                                      ASSIGNFEEDBACK_FILE_IMPORT_FILEAREA,
                                      $USER->id);
    }

    
    public function extract_files_from_zip($zipfile, $contextid) {
        global $USER;

        $feedbackfilesupdated = 0;
        $feedbackfilesadded = 0;
        $userswithnewfeedback = array();

                raise_memory_limit(MEMORY_EXTRA);

        $packer = get_file_packer('application/zip');
        core_php_time_limit::raise(ASSIGNFEEDBACK_FILE_MAXFILEUNZIPTIME);

        return $packer->extract_to_storage($zipfile,
                                    $contextid,
                                    'assignfeedback_file',
                                    ASSIGNFEEDBACK_FILE_IMPORT_FILEAREA,
                                    $USER->id,
                                    'import');

    }

    
    public function get_import_files($contextid) {
        global $USER;

        $fs = get_file_storage();
        $files = $fs->get_directory_files($contextid,
                                          'assignfeedback_file',
                                          ASSIGNFEEDBACK_FILE_IMPORT_FILEAREA,
                                          $USER->id,
                                          '/import/', true); 
        $keys = array_keys($files);

        return $files;
    }

    
    public function import_zip_files($assignment, $fileplugin) {
        global $CFG, $PAGE, $DB;

        core_php_time_limit::raise(ASSIGNFEEDBACK_FILE_MAXFILEUNZIPTIME);
        $packer = get_file_packer('application/zip');

        $feedbackfilesupdated = 0;
        $feedbackfilesadded = 0;
        $userswithnewfeedback = array();
        $contextid = $assignment->get_context()->id;

        $fs = get_file_storage();
        $files = $this->get_import_files($contextid);

        $currentgroup = groups_get_activity_group($assignment->get_course_module(), true);
        $allusers = $assignment->list_participants($currentgroup, false);
        $participants = array();
        foreach ($allusers as $user) {
            $participants[$assignment->get_uniqueid_for_user($user->id)] = $user;
        }

        foreach ($files as $unzippedfile) {
                        $user = null;
            $plugin = null;
            $filename = '';

            if ($this->is_valid_filename_for_import($assignment, $unzippedfile, $participants, $user, $plugin, $filename)) {
                if ($this->is_file_modified($assignment, $user, $plugin, $filename, $unzippedfile)) {
                    $grade = $assignment->get_user_grade($user->id, true);

                                                                                                                        $path = pathinfo($filename);
                    if ($path['dirname'] == '.') {                         $basename = $filename;
                        $dirname = "/";
                        $dirnamewslash = "/";
                    } else {
                        $basename = $path['basename'];
                        $dirname = $path['dirname'];
                        $dirnamewslash = $dirname . "/";
                    }

                    if ($oldfile = $fs->get_file($contextid,
                                                 'assignfeedback_file',
                                                 ASSIGNFEEDBACK_FILE_FILEAREA,
                                                 $grade->id,
                                                 $dirname,
                                                 $basename)) {
                                                $oldfile->replace_file_with($unzippedfile);
                        $feedbackfilesupdated++;
                    } else {
                                                $newfilerecord = new stdClass();
                        $newfilerecord->contextid = $contextid;
                        $newfilerecord->component = 'assignfeedback_file';
                        $newfilerecord->filearea = ASSIGNFEEDBACK_FILE_FILEAREA;
                        $newfilerecord->filename = $basename;
                        $newfilerecord->filepath = $dirnamewslash;
                        $newfilerecord->itemid = $grade->id;
                        $fs->create_file_from_storedfile($newfilerecord, $unzippedfile);
                        $feedbackfilesadded++;
                    }
                    $userswithnewfeedback[$user->id] = 1;

                                        $fileplugin->update_file_count($grade);

                                        $assignment->notify_grade_modified($grade);
                }
            }
        }

        require_once($CFG->dirroot . '/mod/assign/feedback/file/renderable.php');
        $importsummary = new assignfeedback_file_import_summary($assignment->get_course_module()->id,
                                                            count($userswithnewfeedback),
                                                            $feedbackfilesadded,
                                                            $feedbackfilesupdated);

        $assignrenderer = $assignment->get_renderer();
        $renderer = $PAGE->get_renderer('assignfeedback_file');

        $o = '';

        $o .= $assignrenderer->render(new assign_header($assignment->get_instance(),
                                                        $assignment->get_context(),
                                                        false,
                                                        $assignment->get_course_module()->id,
                                                        get_string('uploadzipsummary', 'assignfeedback_file')));

        $o .= $renderer->render($importsummary);

        $o .= $assignrenderer->render_footer();
        return $o;
    }

}
