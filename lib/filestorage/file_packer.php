<?php



defined('MOODLE_INTERNAL') || die();


abstract class file_packer {
    
    public abstract function archive_to_storage(array $files, $contextid,
            $component, $filearea, $itemid, $filepath, $filename,
            $userid = NULL, $ignoreinvalidfiles=true, file_progress $progress = null);

    
    public abstract function archive_to_pathname(array $files, $archivefile,
            $ignoreinvalidfiles=true, file_progress $progress = null);

    
    public abstract function extract_to_pathname($archivefile, $pathname,
            array $onlyfiles = NULL, file_progress $progress = null);

    
    public abstract function extract_to_storage($archivefile, $contextid,
            $component, $filearea, $itemid, $pathbase, $userid = NULL,
            file_progress $progress = null);

    
    public abstract function list_files($archivefile);
}
