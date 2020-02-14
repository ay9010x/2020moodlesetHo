<?php



defined('MOODLE_INTERNAL') || die();

require_once("$CFG->libdir/filestorage/file_packer.php");


class mbz_packer extends file_packer {
    
    public function archive_to_storage(array $files, $contextid,
            $component, $filearea, $itemid, $filepath, $filename,
            $userid = null, $ignoreinvalidfiles = true, file_progress $progress = null) {
        return $this->get_packer_for_archive_operation()->archive_to_storage($files,
                $contextid, $component, $filearea, $itemid, $filepath, $filename,
                $userid, $ignoreinvalidfiles, $progress);
    }

    
    public function archive_to_pathname(array $files, $archivefile,
            $ignoreinvalidfiles=true, file_progress $progress = null) {
        return $this->get_packer_for_archive_operation()->archive_to_pathname($files,
                $archivefile, $ignoreinvalidfiles, $progress);
    }

    
    public function extract_to_pathname($archivefile, $pathname,
            array $onlyfiles = null, file_progress $progress = null) {
        return $this->get_packer_for_read_operation($archivefile)->extract_to_pathname(
                $archivefile, $pathname, $onlyfiles, $progress);
    }

    
    public function extract_to_storage($archivefile, $contextid,
            $component, $filearea, $itemid, $pathbase, $userid = null,
            file_progress $progress = null) {
        return $this->get_packer_for_read_operation($archivefile)->extract_to_storage(
                $archivefile, $contextid, $component, $filearea, $itemid, $pathbase,
                $userid, $progress);
    }

    
    public function list_files($archivefile) {
        return $this->get_packer_for_read_operation($archivefile)->list_files($archivefile);
    }

    
    protected function get_packer_for_archive_operation() {
        global $CFG;
        require_once($CFG->dirroot . '/lib/filestorage/tgz_packer.php');

        if (!empty($CFG->usezipbackups)) {
                        return get_file_packer('application/zip');
        } else {
            return get_file_packer('application/x-gzip');
        }
    }

    
    protected function get_packer_for_read_operation($archivefile) {
        global $CFG;
        require_once($CFG->dirroot . '/lib/filestorage/tgz_packer.php');

        if (tgz_packer::is_tgz_file($archivefile)) {
            return get_file_packer('application/x-gzip');
        } else {
            return get_file_packer('application/zip');
        }
    }
}
