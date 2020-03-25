<?php



namespace core\message\inbound;

defined('MOODLE_INTERNAL') || die();


class private_files_handler extends handler {

    
    public function can_change_defaultexpiration() {
        return false;
    }

    
    public function get_description() {
        return get_string('private_files_handler', 'moodle');
    }

    
    public function get_name() {
        return get_string('private_files_handler_name', 'moodle');
    }

    
    public function process_message(\stdClass $record, \stdClass $data) {
        global $USER, $CFG;

        $context = \context_user::instance($USER->id);

        if (!has_capability('moodle/user:manageownfiles', $context)) {
            throw new \core\message\inbound\processing_failed_exception('emailtoprivatefilesdenied', 'moodle', $data);
        }

                $component  = 'user';
        $filearea   = 'private';
        $itemid     = 0;
        $license    = $CFG->sitedefaultlicense;
        $author     = fullname($USER);

                $maxbytes = $CFG->userquota;
        if (has_capability('moodle/user:ignoreuserquota', $context)) {
            $maxbytes = USER_CAN_IGNORE_FILE_SIZE_LIMITS;
        }

                $skippedfiles   = array();
        $uploadedfiles  = array();
        $failedfiles    = array();

        $fs = get_file_storage();
        foreach ($data->attachments as $attachmenttype => $attachments) {
            foreach ($attachments as $attachment) {
                mtrace("--- Processing attachment '{$attachment->filename}'");

                if (file_is_draft_area_limit_reached($itemid, $maxbytes, $attachment->filesize)) {
                                        $skippedfiles[] = $attachment;
                    mtrace("---- Skipping attacment. User will be over quota.");
                    continue;
                }

                                $record = new \stdClass();
                $record->filearea   = $filearea;
                $record->component  = $component;
                $record->filepath   = '/';
                $record->itemid     = $itemid;
                $record->license    = $license;
                $record->author     = $author;
                $record->contextid  = $context->id;
                $record->userid     = $USER->id;

                $record->filename = $fs->get_unused_filename($context->id, $record->component, $record->filearea,
                        $record->itemid, $record->filepath, $attachment->filename);

                mtrace("--> Attaching {$record->filename} to " .
                       "/{$record->contextid}/{$record->component}/{$record->filearea}/" .
                       "{$record->itemid}{$record->filepath}{$record->filename}");

                if ($fs->create_file_from_string($record, $attachment->content)) {
                                        mtrace("---- File uploaded successfully as {$record->filename}.");
                    $uploadedfiles[] = $attachment;
                } else {
                    mtrace("---- Skipping attacment. Unknown failure during creation.");
                    $failedfiles[] = $attachment;
                }
            }
        }

                
        return true;
    }
}
