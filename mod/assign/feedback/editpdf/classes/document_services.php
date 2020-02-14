<?php



namespace assignfeedback_editpdf;

use DOMDocument;


class document_services {

    
    const FINAL_PDF_FILEAREA = 'download';
    
    const COMBINED_PDF_FILEAREA = 'combined';
    
    const IMPORT_HTML_FILEAREA = 'importhtml';
    
    const PAGE_IMAGE_FILEAREA = 'pages';
    
    const PAGE_IMAGE_READONLY_FILEAREA = 'readonlypages';
    
    const STAMPS_FILEAREA = 'stamps';
    
    const COMBINED_PDF_FILENAME = 'combined.pdf';

    
    const BLANK_PDF_BASE64 = <<<EOD
JVBERi0xLjQKJcOkw7zDtsOfCjIgMCBvYmoKPDwvTGVuZ3RoIDMgMCBSL0ZpbHRlci9GbGF0ZURl
Y29kZT4+CnN0cmVhbQp4nDPQM1Qo5ypUMFAwALJMLU31jBQsTAz1LBSKUrnCtRTyuAIVAIcdB3IK
ZW5kc3RyZWFtCmVuZG9iagoKMyAwIG9iago0MgplbmRvYmoKCjUgMCBvYmoKPDwKPj4KZW5kb2Jq
Cgo2IDAgb2JqCjw8L0ZvbnQgNSAwIFIKL1Byb2NTZXRbL1BERi9UZXh0XQo+PgplbmRvYmoKCjEg
MCBvYmoKPDwvVHlwZS9QYWdlL1BhcmVudCA0IDAgUi9SZXNvdXJjZXMgNiAwIFIvTWVkaWFCb3hb
MCAwIDU5NSA4NDJdL0dyb3VwPDwvUy9UcmFuc3BhcmVuY3kvQ1MvRGV2aWNlUkdCL0kgdHJ1ZT4+
L0NvbnRlbnRzIDIgMCBSPj4KZW5kb2JqCgo0IDAgb2JqCjw8L1R5cGUvUGFnZXMKL1Jlc291cmNl
cyA2IDAgUgovTWVkaWFCb3hbIDAgMCA1OTUgODQyIF0KL0tpZHNbIDEgMCBSIF0KL0NvdW50IDE+
PgplbmRvYmoKCjcgMCBvYmoKPDwvVHlwZS9DYXRhbG9nL1BhZ2VzIDQgMCBSCi9PcGVuQWN0aW9u
WzEgMCBSIC9YWVogbnVsbCBudWxsIDBdCi9MYW5nKGVuLUFVKQo+PgplbmRvYmoKCjggMCBvYmoK
PDwvQ3JlYXRvcjxGRUZGMDA1NzAwNzIwMDY5MDA3NDAwNjUwMDcyPgovUHJvZHVjZXI8RkVGRjAw
NEMwMDY5MDA2MjAwNzIwMDY1MDA0RjAwNjYwMDY2MDA2OTAwNjMwMDY1MDAyMDAwMzQwMDJFMDAz
ND4KL0NyZWF0aW9uRGF0ZShEOjIwMTYwMjI2MTMyMzE0KzA4JzAwJyk+PgplbmRvYmoKCnhyZWYK
MCA5CjAwMDAwMDAwMDAgNjU1MzUgZiAKMDAwMDAwMDIyNiAwMDAwMCBuIAowMDAwMDAwMDE5IDAw
MDAwIG4gCjAwMDAwMDAxMzIgMDAwMDAgbiAKMDAwMDAwMDM2OCAwMDAwMCBuIAowMDAwMDAwMTUx
IDAwMDAwIG4gCjAwMDAwMDAxNzMgMDAwMDAgbiAKMDAwMDAwMDQ2NiAwMDAwMCBuIAowMDAwMDAw
NTYyIDAwMDAwIG4gCnRyYWlsZXIKPDwvU2l6ZSA5L1Jvb3QgNyAwIFIKL0luZm8gOCAwIFIKL0lE
IFsgPEJDN0REQUQwRDQyOTQ1OTQ2OUU4NzJCMjI1ODUyNkU4Pgo8QkM3RERBRDBENDI5NDU5NDY5
RTg3MkIyMjU4NTI2RTg+IF0KL0RvY0NoZWNrc3VtIC9BNTYwMEZCMDAzRURCRTg0MTNBNTk3RTZF
MURDQzJBRgo+PgpzdGFydHhyZWYKNzM2CiUlRU9GCg==
EOD;

    
    private static function get_assignment_from_param($assignment) {
        global $CFG;

        require_once($CFG->dirroot . '/mod/assign/locallib.php');

        if (!is_object($assignment)) {
            $cm = \get_coursemodule_from_instance('assign', $assignment, 0, false, MUST_EXIST);
            $context = \context_module::instance($cm->id);

            $assignment = new \assign($context, null, null);
        }
        return $assignment;
    }

    
    private static function hash($assignment, $userid, $attemptnumber) {
        if (is_object($assignment)) {
            $assignmentid = $assignment->get_instance()->id;
        } else {
            $assignmentid = $assignment;
        }
        return sha1($assignmentid . '_' . $userid . '_' . $attemptnumber);
    }

    
    protected static function strip_images($html) {
        $dom = new DOMDocument();
        $dom->loadHTML("<?xml version=\"1.0\" encoding=\"UTF-8\" ?>" . $html);
        $images = $dom->getElementsByTagName('img');
        $i = 0;

        for ($i = ($images->length - 1); $i >= 0; $i--) {
            $node = $images->item($i);

            if ($node->hasAttribute('alt')) {
                $replacement = ' [ ' . $node->getAttribute('alt') . ' ] ';
            } else {
                $replacement = ' ';
            }

            $text = $dom->createTextNode($replacement);
            $node->parentNode->replaceChild($text, $node);
        }
        $count = 1;
        return str_replace("<?xml version=\"1.0\" encoding=\"UTF-8\" ?>", "", $dom->saveHTML(), $count);
    }

    
    public static function list_compatible_submission_files_for_attempt($assignment, $userid, $attemptnumber) {
        global $USER, $DB;

        $assignment = self::get_assignment_from_param($assignment);

                if (!$assignment->can_view_submission($userid)) {
            \print_error('nopermission');
        }

        $files = array();

        if ($assignment->get_instance()->teamsubmission) {
            $submission = $assignment->get_group_submission($userid, 0, false, $attemptnumber);
        } else {
            $submission = $assignment->get_user_submission($userid, false, $attemptnumber);
        }
        $user = $DB->get_record('user', array('id' => $userid));

                if (!$submission) {
            return $files;
        }

        $fs = get_file_storage();
                foreach ($assignment->get_submission_plugins() as $plugin) {
            if ($plugin->is_enabled() && $plugin->is_visible()) {
                $pluginfiles = $plugin->get_files($submission, $user);
                foreach ($pluginfiles as $filename => $file) {
                    if ($file instanceof \stored_file) {
                        if ($file->get_mimetype() === 'application/pdf') {
                            $files[$filename] = $file;
                        } else if ($convertedfile = $fs->get_converted_document($file, 'pdf')) {
                            $files[$filename] = $convertedfile;
                        }
                    } else {
                                                $file = reset($file);
                                                $file = self::strip_images($file);
                        $record = new \stdClass();
                        $record->contextid = $assignment->get_context()->id;
                        $record->component = 'assignfeedback_editpdf';
                        $record->filearea = self::IMPORT_HTML_FILEAREA;
                        $record->itemid = $submission->id;
                        $record->filepath = '/';
                        $record->filename = $plugin->get_type() . '-' . $filename;

                        $htmlfile = $fs->create_file_from_string($record, $file);
                        $convertedfile = $fs->get_converted_document($htmlfile, 'pdf');
                        $htmlfile->delete();
                        if ($convertedfile) {
                            $files[$filename] = $convertedfile;
                        }
                    }
                }
            }
        }
        return $files;
    }

    
    public static function get_combined_pdf_for_attempt($assignment, $userid, $attemptnumber) {

        global $USER, $DB;

        $assignment = self::get_assignment_from_param($assignment);

                if (!$assignment->can_view_submission($userid)) {
            \print_error('nopermission');
        }

        $grade = $assignment->get_user_grade($userid, true, $attemptnumber);
        if ($assignment->get_instance()->teamsubmission) {
            $submission = $assignment->get_group_submission($userid, 0, false, $attemptnumber);
        } else {
            $submission = $assignment->get_user_submission($userid, false, $attemptnumber);
        }

        $contextid = $assignment->get_context()->id;
        $component = 'assignfeedback_editpdf';
        $filearea = self::COMBINED_PDF_FILEAREA;
        $itemid = $grade->id;
        $filepath = '/';
        $filename = self::COMBINED_PDF_FILENAME;
        $fs = \get_file_storage();

        $combinedpdf = $fs->get_file($contextid, $component, $filearea, $itemid, $filepath, $filename);
        if (!$combinedpdf ||
                ($submission && ($combinedpdf->get_timemodified() < $submission->timemodified))) {
            return self::generate_combined_pdf_for_attempt($assignment, $userid, $attemptnumber);
        }
        return $combinedpdf;
    }

    
    public static function generate_combined_pdf_for_attempt($assignment, $userid, $attemptnumber) {
        global $CFG;

        require_once($CFG->libdir . '/pdflib.php');

        $assignment = self::get_assignment_from_param($assignment);

        if (!$assignment->can_view_submission($userid)) {
            \print_error('nopermission');
        }

        $files = self::list_compatible_submission_files_for_attempt($assignment, $userid, $attemptnumber);

        $pdf = new pdf();
        if ($files) {
                        $compatiblepdfs = array();
            foreach ($files as $file) {
                $compatiblepdf = pdf::ensure_pdf_compatible($file);
                if ($compatiblepdf) {
                    array_push($compatiblepdfs, $compatiblepdf);
                }
            }

            $tmpdir = \make_temp_directory('assignfeedback_editpdf/combined/' . self::hash($assignment, $userid, $attemptnumber));
            $tmpfile = $tmpdir . '/' . self::COMBINED_PDF_FILENAME;

            @unlink($tmpfile);
            try {
                $pagecount = $pdf->combine_pdfs($compatiblepdfs, $tmpfile);
            } catch (\Exception $e) {
                debugging('TCPDF could not process the pdf files:' . $e->getMessage(), DEBUG_DEVELOPER);
                                $pagecount = 0;
            }
            if ($pagecount == 0) {
                                debugging('TCPDF did not produce a valid pdf:' . $tmpfile . '. Replacing with a blank pdf.', DEBUG_DEVELOPER);
                @unlink($tmpfile);
                $files = false;
            }
        }
        $pdf->Close(); 
        $grade = $assignment->get_user_grade($userid, true, $attemptnumber);
        $record = new \stdClass();

        $record->contextid = $assignment->get_context()->id;
        $record->component = 'assignfeedback_editpdf';
        $record->filearea = self::COMBINED_PDF_FILEAREA;
        $record->itemid = $grade->id;
        $record->filepath = '/';
        $record->filename = self::COMBINED_PDF_FILENAME;
        $fs = \get_file_storage();

        $fs->delete_area_files($record->contextid, $record->component, $record->filearea, $record->itemid);

                if ($files) {
            $verifypdf = new pdf();
            $pagecount = $verifypdf->load_pdf($tmpfile);
            if ($pagecount <= 0) {
                $files = false;
            }
            $verifypdf->Close();         }

        if (!$files) {
            $file = $fs->create_file_from_string($record, base64_decode(self::BLANK_PDF_BASE64));
        } else {
                        $file = $fs->create_file_from_pathname($record, $tmpfile);
            @unlink($tmpfile);

                        $compatiblepdf = pdf::ensure_pdf_compatible($file);
        }

        return $file;
    }

    
    public static function page_number_for_attempt($assignment, $userid, $attemptnumber, $readonly = false) {
        global $CFG;

        require_once($CFG->libdir . '/pdflib.php');

        $assignment = self::get_assignment_from_param($assignment);

        if (!$assignment->can_view_submission($userid)) {
            \print_error('nopermission');
        }

                        if ($readonly) {
            $grade = $assignment->get_user_grade($userid, true, $attemptnumber);
            $fs = get_file_storage();
            $files = $fs->get_directory_files($assignment->get_context()->id, 'assignfeedback_editpdf',
                self::PAGE_IMAGE_READONLY_FILEAREA, $grade->id, '/');
            $pagecount = count($files);
            if ($pagecount > 0) {
                return $pagecount;
            }
        }

                $file = self::get_combined_pdf_for_attempt($assignment, $userid, $attemptnumber);
        if (!$file) {
            \print_error('Could not generate combined pdf.');
        }

                $tmpdir = \make_temp_directory('assignfeedback_editpdf/pagetotal/'
            . self::hash($assignment, $userid, $attemptnumber));
        $combined = $tmpdir . '/' . self::COMBINED_PDF_FILENAME;
        $file->copy_content_to($combined); 
                $pdf = new pdf();
        $pagecount = $pdf->set_pdf($combined);
        $pdf->Close(); 
                @unlink($combined);
        @rmdir($tmpdir);

        return $pagecount;
    }

    
    public static function generate_page_images_for_attempt($assignment, $userid, $attemptnumber) {
        global $CFG;

        require_once($CFG->libdir . '/pdflib.php');

        $assignment = self::get_assignment_from_param($assignment);

        if (!$assignment->can_view_submission($userid)) {
            \print_error('nopermission');
        }

                $file = self::get_combined_pdf_for_attempt($assignment, $userid, $attemptnumber);
        if (!$file) {
            throw new \moodle_exception('Could not generate combined pdf.');
        }

        $tmpdir = \make_temp_directory('assignfeedback_editpdf/pageimages/' . self::hash($assignment, $userid, $attemptnumber));
        $combined = $tmpdir . '/' . self::COMBINED_PDF_FILENAME;
        $file->copy_content_to($combined); 
        $pdf = new pdf();

        $pdf->set_image_folder($tmpdir);
        $pagecount = $pdf->set_pdf($combined);

        $grade = $assignment->get_user_grade($userid, true, $attemptnumber);

        $record = new \stdClass();
        $record->contextid = $assignment->get_context()->id;
        $record->component = 'assignfeedback_editpdf';
        $record->filearea = self::PAGE_IMAGE_FILEAREA;
        $record->itemid = $grade->id;
        $record->filepath = '/';
        $fs = \get_file_storage();

                $fs->delete_area_files($record->contextid, $record->component, $record->filearea, $record->itemid);

        $files = array();
        for ($i = 0; $i < $pagecount; $i++) {
            $image = $pdf->get_image($i);
            $record->filename = basename($image);
            $files[$i] = $fs->create_file_from_pathname($record, $tmpdir . '/' . $image);
            @unlink($tmpdir . '/' . $image);
        }
        $pdf->Close(); 
        @unlink($combined);
        @rmdir($tmpdir);

        return $files;
    }

    
    public static function get_page_images_for_attempt($assignment, $userid, $attemptnumber, $readonly = false) {

        $assignment = self::get_assignment_from_param($assignment);

        if (!$assignment->can_view_submission($userid)) {
            \print_error('nopermission');
        }

        if ($assignment->get_instance()->teamsubmission) {
            $submission = $assignment->get_group_submission($userid, 0, false, $attemptnumber);
        } else {
            $submission = $assignment->get_user_submission($userid, false, $attemptnumber);
        }
        $grade = $assignment->get_user_grade($userid, true, $attemptnumber);

        $contextid = $assignment->get_context()->id;
        $component = 'assignfeedback_editpdf';
        $itemid = $grade->id;
        $filepath = '/';
        $filearea = self::PAGE_IMAGE_FILEAREA;

        $fs = \get_file_storage();

                if ($readonly) {
            $filearea = self::PAGE_IMAGE_READONLY_FILEAREA;
            if ($fs->is_area_empty($contextid, $component, $filearea, $itemid)) {
                                                self::generate_page_images_for_attempt($assignment, $userid, $attemptnumber);
                self::copy_pages_to_readonly_area($assignment, $grade);
            }
        }

        $files = $fs->get_directory_files($contextid, $component, $filearea, $itemid, $filepath);

        $pages = array();
        if (!empty($files)) {
            $first = reset($files);
            if (!$readonly && $first->get_timemodified() < $submission->timemodified) {
                                                $fs->delete_area_files($contextid, $component, $filearea, $itemid);
                page_editor::delete_draft_content($itemid);
                $files = array();
            } else {

                                                foreach($files as $file) {
                                        preg_match('/page([\d]+)\./', $file->get_filename(), $matches);
                    if (empty($matches) or !is_numeric($matches[1])) {
                        throw new \coding_exception("'" . $file->get_filename()
                            . "' file hasn't the expected format filename: image_pageXXXX.png.");
                    }
                    $pagenumber = (int)$matches[1];

                                        $pages[$pagenumber] = $file;
                }
                ksort($pages);
            }
        }

        if (empty($pages)) {
            if ($readonly) {
                                                throw new \moodle_exception('Could not find readonly pages for grade ' . $grade->id);
            }
            $pages = self::generate_page_images_for_attempt($assignment, $userid, $attemptnumber);
        }

        return $pages;
    }

    
    protected static function get_downloadable_feedback_filename($assignment, $userid, $attemptnumber) {
        global $DB;

        $assignment = self::get_assignment_from_param($assignment);

        $groupmode = groups_get_activity_groupmode($assignment->get_course_module());
        $groupname = '';
        if ($groupmode) {
            $groupid = groups_get_activity_group($assignment->get_course_module(), true);
            $groupname = groups_get_group_name($groupid).'-';
        }
        if ($groupname == '-') {
            $groupname = '';
        }
        $grade = $assignment->get_user_grade($userid, true, $attemptnumber);
        $user = $DB->get_record('user', array('id'=>$userid), '*', MUST_EXIST);

        if ($assignment->is_blind_marking()) {
            $prefix = $groupname . get_string('participant', 'assign');
            $prefix = str_replace('_', ' ', $prefix);
            $prefix = clean_filename($prefix . '_' . $assignment->get_uniqueid_for_user($userid) . '_');
        } else {
            $prefix = $groupname . fullname($user);
            $prefix = str_replace('_', ' ', $prefix);
            $prefix = clean_filename($prefix . '_' . $assignment->get_uniqueid_for_user($userid) . '_');
        }
        $prefix .= $grade->attemptnumber;

        return $prefix . '.pdf';
    }

    
    public static function generate_feedback_document($assignment, $userid, $attemptnumber) {

        $assignment = self::get_assignment_from_param($assignment);

        if (!$assignment->can_view_submission($userid)) {
            \print_error('nopermission');
        }
        if (!$assignment->can_grade()) {
            \print_error('nopermission');
        }

                $file = self::get_combined_pdf_for_attempt($assignment, $userid, $attemptnumber);
        if (!$file) {
            throw new \moodle_exception('Could not generate combined pdf.');
        }

        $tmpdir = \make_temp_directory('assignfeedback_editpdf/final/' . self::hash($assignment, $userid, $attemptnumber));
        $combined = $tmpdir . '/' . self::COMBINED_PDF_FILENAME;
        $file->copy_content_to($combined); 
        $pdf = new pdf();

        $fs = \get_file_storage();
        $stamptmpdir = \make_temp_directory('assignfeedback_editpdf/stamps/' . self::hash($assignment, $userid, $attemptnumber));
        $grade = $assignment->get_user_grade($userid, true, $attemptnumber);
                if ($files = $fs->get_area_files($assignment->get_context()->id,
                                         'assignfeedback_editpdf',
                                         'stamps',
                                         $grade->id,
                                         "filename",
                                         false)) {
            foreach ($files as $file) {
                $filename = $stamptmpdir . '/' . $file->get_filename();
                $file->copy_content_to($filename);             }
        }

        $pagecount = $pdf->set_pdf($combined);
        $grade = $assignment->get_user_grade($userid, true, $attemptnumber);
        page_editor::release_drafts($grade->id);

        for ($i = 0; $i < $pagecount; $i++) {
            $pdf->copy_page();
            $comments = page_editor::get_comments($grade->id, $i, false);
            $annotations = page_editor::get_annotations($grade->id, $i, false);

            foreach ($comments as $comment) {
                $pdf->add_comment($comment->rawtext,
                                  $comment->x,
                                  $comment->y,
                                  $comment->width,
                                  $comment->colour);
            }

            foreach ($annotations as $annotation) {
                $pdf->add_annotation($annotation->x,
                                     $annotation->y,
                                     $annotation->endx,
                                     $annotation->endy,
                                     $annotation->colour,
                                     $annotation->type,
                                     $annotation->path,
                                     $stamptmpdir);
            }
        }

        fulldelete($stamptmpdir);

        $filename = self::get_downloadable_feedback_filename($assignment, $userid, $attemptnumber);
        $filename = clean_param($filename, PARAM_FILE);

        $generatedpdf = $tmpdir . '/' . $filename;
        $pdf->save_pdf($generatedpdf);


        $record = new \stdClass();

        $record->contextid = $assignment->get_context()->id;
        $record->component = 'assignfeedback_editpdf';
        $record->filearea = self::FINAL_PDF_FILEAREA;
        $record->itemid = $grade->id;
        $record->filepath = '/';
        $record->filename = $filename;


                $fs->delete_area_files($record->contextid, $record->component, $record->filearea, $record->itemid);

        $file = $fs->create_file_from_pathname($record, $generatedpdf);

                @unlink($generatedpdf);
        @unlink($combined);
        @rmdir($tmpdir);

        self::copy_pages_to_readonly_area($assignment, $grade);

        return $file;
    }

    
    public static function copy_pages_to_readonly_area($assignment, $grade) {
        $fs = get_file_storage();
        $assignment = self::get_assignment_from_param($assignment);
        $contextid = $assignment->get_context()->id;
        $component = 'assignfeedback_editpdf';
        $itemid = $grade->id;

                $originalfiles = $fs->get_area_files($contextid, $component, self::PAGE_IMAGE_FILEAREA, $itemid);
        if (empty($originalfiles)) {
                        return;
        }

                $fs->delete_area_files($contextid, $component, self::PAGE_IMAGE_READONLY_FILEAREA, $itemid);

                foreach ($originalfiles as $originalfile) {
            $fs->create_file_from_storedfile(array('filearea' => self::PAGE_IMAGE_READONLY_FILEAREA), $originalfile);
        }
    }

    
    public static function get_feedback_document($assignment, $userid, $attemptnumber) {

        $assignment = self::get_assignment_from_param($assignment);

        if (!$assignment->can_view_submission($userid)) {
            \print_error('nopermission');
        }

        $grade = $assignment->get_user_grade($userid, true, $attemptnumber);

        $contextid = $assignment->get_context()->id;
        $component = 'assignfeedback_editpdf';
        $filearea = self::FINAL_PDF_FILEAREA;
        $itemid = $grade->id;
        $filepath = '/';

        $fs = \get_file_storage();
        $files = $fs->get_area_files($contextid,
                                     $component,
                                     $filearea,
                                     $itemid,
                                     "itemid, filepath, filename",
                                     false);
        if ($files) {
            return reset($files);
        }
        return false;
    }

    
    public static function delete_feedback_document($assignment, $userid, $attemptnumber) {

        $assignment = self::get_assignment_from_param($assignment);

        if (!$assignment->can_view_submission($userid)) {
            \print_error('nopermission');
        }
        if (!$assignment->can_grade()) {
            \print_error('nopermission');
        }

        $grade = $assignment->get_user_grade($userid, true, $attemptnumber);

        $contextid = $assignment->get_context()->id;
        $component = 'assignfeedback_editpdf';
        $filearea = self::FINAL_PDF_FILEAREA;
        $itemid = $grade->id;

        $fs = \get_file_storage();
        return $fs->delete_area_files($contextid, $component, $filearea, $itemid);
    }

}
