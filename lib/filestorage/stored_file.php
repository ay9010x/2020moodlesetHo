<?php




defined('MOODLE_INTERNAL') || die();

require_once($CFG->dirroot . '/lib/filestorage/file_progress.php');


class stored_file {
    
    private $fs;
    
    private $file_record;
    
    private $filedir;
    
    private $repository;

    
    const FILE_HANDLE_FOPEN = 0;

    
    const FILE_HANDLE_GZOPEN = 1;


    
    public function __construct(file_storage $fs, stdClass $file_record, $filedir) {
        global $DB, $CFG;
        $this->fs          = $fs;
        $this->file_record = clone($file_record);         $this->filedir     = $filedir; 
        if (!empty($file_record->repositoryid)) {
            require_once("$CFG->dirroot/repository/lib.php");
            $this->repository = repository::get_repository_by_id($file_record->repositoryid, SYSCONTEXTID);
            if ($this->repository->supported_returntypes() & FILE_REFERENCE != FILE_REFERENCE) {
                                throw new moodle_exception('error');
            }
        } else {
            $this->repository = null;
        }
                foreach (array('referencelastsync', 'referencefileid', 'reference', 'repositoryid') as $key) {
            if (empty($this->file_record->$key)) {
                $this->file_record->$key = null;
            }
        }
    }

    
    public function is_external_file() {
        return !empty($this->repository);
    }

    
    protected function update($dataobject) {
        global $DB;
        $updatereferencesneeded = false;
        $keys = array_keys((array)$this->file_record);
        foreach ($dataobject as $field => $value) {
            if (in_array($field, $keys)) {
                if ($field == 'contextid' and (!is_number($value) or $value < 1)) {
                    throw new file_exception('storedfileproblem', 'Invalid contextid');
                }

                if ($field == 'component') {
                    $value = clean_param($value, PARAM_COMPONENT);
                    if (empty($value)) {
                        throw new file_exception('storedfileproblem', 'Invalid component');
                    }
                }

                if ($field == 'filearea') {
                    $value = clean_param($value, PARAM_AREA);
                    if (empty($value)) {
                        throw new file_exception('storedfileproblem', 'Invalid filearea');
                    }
                }

                if ($field == 'itemid' and (!is_number($value) or $value < 0)) {
                    throw new file_exception('storedfileproblem', 'Invalid itemid');
                }


                if ($field == 'filepath') {
                    $value = clean_param($value, PARAM_PATH);
                    if (strpos($value, '/') !== 0 or strrpos($value, '/') !== strlen($value)-1) {
                                                throw new file_exception('storedfileproblem', 'Invalid file path');
                    }
                }

                if ($field == 'filename') {
                                        if ($value != '.') {
                        $value = clean_param($value, PARAM_FILE);
                    }
                    if ($value === '') {
                        throw new file_exception('storedfileproblem', 'Invalid file name');
                    }
                }

                if ($field === 'timecreated' or $field === 'timemodified') {
                    if (!is_number($value)) {
                        throw new file_exception('storedfileproblem', 'Invalid timestamp');
                    }
                    if ($value < 0) {
                        $value = 0;
                    }
                }

                if ($field === 'referencefileid') {
                    if (!is_null($value) and !is_number($value)) {
                        throw new file_exception('storedfileproblem', 'Invalid reference info');
                    }
                }

                if (($field == 'contenthash' || $field == 'filesize') && $this->file_record->$field != $value) {
                    $updatereferencesneeded = true;
                }

                                $this->file_record->$field = $value;
            } else {
                throw new coding_exception("Invalid field name, $field doesn't exist in file record");
            }
        }
                        $pathname = $this->get_pathname_by_contenthash();
                if (!is_readable($pathname)) {
            if (!$this->fs->try_content_recovery($this) or !is_readable($pathname)) {
                throw new file_exception('storedfilecannotread', '', $pathname);
            }
        }
        $mimetype = $this->fs->mimetype($pathname, $this->file_record->filename);
        $this->file_record->mimetype = $mimetype;

        $DB->update_record('files', $this->file_record);
        if ($updatereferencesneeded) {
                        $this->fs->update_references_to_storedfile($this);
        }
    }

    
    public function rename($filepath, $filename) {
        if ($this->fs->file_exists($this->get_contextid(), $this->get_component(), $this->get_filearea(), $this->get_itemid(), $filepath, $filename)) {
            $a = new stdClass();
            $a->contextid = $this->get_contextid();
            $a->component = $this->get_component();
            $a->filearea  = $this->get_filearea();
            $a->itemid    = $this->get_itemid();
            $a->filepath  = $filepath;
            $a->filename  = $filename;
            throw new file_exception('storedfilenotcreated', $a, 'file exists, cannot rename');
        }
        $filerecord = new stdClass;
        $filerecord->filepath = $filepath;
        $filerecord->filename = $filename;
                $filerecord->pathnamehash = $this->fs->get_pathname_hash($this->file_record->contextid, $this->file_record->component, $this->file_record->filearea, $this->file_record->itemid, $filepath, $filename);
        $this->update($filerecord);
    }

    
    public function replace_content_with(stored_file $storedfile) {
        throw new coding_exception('Function stored_file::replace_content_with() can not be used any more . ' .
            'Please use stored_file::replace_file_with()');
    }

    
    public function replace_file_with(stored_file $newfile) {
        if ($newfile->get_referencefileid() &&
                $this->fs->get_references_count_by_storedfile($this)) {
                                                throw new moodle_exception('errordoublereference', 'repository');
        }

        $filerecord = new stdClass;
        $contenthash = $newfile->get_contenthash();
        if ($this->fs->content_exists($contenthash)) {
            $filerecord->contenthash = $contenthash;
        } else {
            throw new file_exception('storedfileproblem', 'Invalid contenthash, content must be already in filepool', $contenthash);
        }
        $filerecord->filesize = $newfile->get_filesize();
        $filerecord->referencefileid = $newfile->get_referencefileid();
        $filerecord->userid = $newfile->get_userid();
        $this->update($filerecord);
    }

    
    public function delete_reference() {
        global $DB;

        if (!$this->is_external_file()) {
            throw new coding_exception('An attempt to unlink a non-reference file.');
        }

        $transaction = $DB->start_delegated_transaction();

                                $countlinks = $DB->count_records('files',
            array('referencefileid' => $this->file_record->referencefileid));
        if ($countlinks == 1) {
            $DB->delete_records('files_reference', array('id' => $this->file_record->referencefileid));
        }

                $update = new stdClass();
        $update->referencefileid = null;
        $this->update($update);

        $transaction->allow_commit();

                $this->repository = null;
        $this->file_record->repositoryid = null;
        $this->file_record->reference = null;
        $this->file_record->referencefileid = null;
        $this->file_record->referencelastsync = null;
    }

    
    public function is_directory() {
        return ($this->file_record->filename === '.');
    }

    
    public function delete() {
        global $DB;

        if ($this->is_directory()) {
                        $DB->delete_records('files', array('id'=>$this->file_record->id));

        } else {
            $transaction = $DB->start_delegated_transaction();

                        if ($files = $this->fs->get_references_by_storedfile($this)) {
                foreach ($files as $file) {
                    $this->fs->import_external_file($file);
                }
            }

                        if ($this->is_external_file()) {
                $this->delete_reference();
            }

                        $DB->delete_records('files', array('id'=>$this->file_record->id));

            $transaction->allow_commit();
        }

                $this->fs->deleted_file_cleanup($this->file_record->contenthash);
        return true;     }

    
    protected function get_pathname_by_contenthash() {
                $contenthash = $this->file_record->contenthash;
        $l1 = $contenthash[0].$contenthash[1];
        $l2 = $contenthash[2].$contenthash[3];
        return "$this->filedir/$l1/$l2/$contenthash";
    }

    
    protected function get_content_file_location() {
        $this->sync_external_file();
        return $this->get_pathname_by_contenthash();
    }

    
    public function add_to_curl_request(&$curlrequest, $key) {
        if (function_exists('curl_file_create')) {
                        $value = curl_file_create($this->get_content_file_location());
        } else {
            $value = '@' . $this->get_content_file_location();
        }
        $curlrequest->_tmp_file_post_params[$key] = $value;
    }

    
    public function get_content_file_handle($type = self::FILE_HANDLE_FOPEN) {
        $path = $this->get_content_file_location();
        if (!is_readable($path)) {
            if (!$this->fs->try_content_recovery($this) or !is_readable($path)) {
                throw new file_exception('storedfilecannotread', '', $path);
            }
        }
        switch ($type) {
            case self::FILE_HANDLE_FOPEN:
                                return fopen($path, 'rb');
            case self::FILE_HANDLE_GZOPEN:
                                return gzopen($path, 'rb');
            default:
                throw new coding_exception('Unexpected file handle type');
        }
    }

    
    public function readfile() {
        $path = $this->get_content_file_location();
        if (!is_readable($path)) {
            if (!$this->fs->try_content_recovery($this) or !is_readable($path)) {
                throw new file_exception('storedfilecannotread', '', $path);
            }
        }
        readfile_allow_large($path, $this->get_filesize());
    }

    
    public function get_content() {
        $path = $this->get_content_file_location();
        if (!is_readable($path)) {
            if (!$this->fs->try_content_recovery($this) or !is_readable($path)) {
                throw new file_exception('storedfilecannotread', '', $path);
            }
        }
        return file_get_contents($this->get_content_file_location());
    }

    
    public function copy_content_to($pathname) {
        $path = $this->get_content_file_location();
        if (!is_readable($path)) {
            if (!$this->fs->try_content_recovery($this) or !is_readable($path)) {
                throw new file_exception('storedfilecannotread', '', $path);
            }
        }
        return copy($path, $pathname);
    }

    
    public function copy_content_to_temp($dir = 'files', $fileprefix = 'tempup_') {
        $tempfile = false;
        if (!$dir = make_temp_directory($dir)) {
            return false;
        }
        if (!$tempfile = tempnam($dir, $fileprefix)) {
            return false;
        }
        if (!$this->copy_content_to($tempfile)) {
                        @unlink($tempfile);
            return false;
        }
        return $tempfile;
    }

    
    public function list_files(file_packer $packer) {
        $archivefile = $this->get_content_file_location();
        return $packer->list_files($archivefile);
    }

    
    public function extract_to_pathname(file_packer $packer, $pathname,
            file_progress $progress = null) {
        $archivefile = $this->get_content_file_location();
        return $packer->extract_to_pathname($archivefile, $pathname, null, $progress);
    }

    
    public function extract_to_storage(file_packer $packer, $contextid,
            $component, $filearea, $itemid, $pathbase, $userid = null, file_progress $progress = null) {
        $archivefile = $this->get_content_file_location();
        return $packer->extract_to_storage($archivefile, $contextid,
                $component, $filearea, $itemid, $pathbase, $userid, $progress);
    }

    
    public function archive_file(file_archive $filearch, $archivepath) {
        if ($this->is_directory()) {
            return $filearch->add_directory($archivepath);
        } else {
            $path = $this->get_content_file_location();
            if (!is_readable($path)) {
                return false;
            }
            return $filearch->add_file_from_pathname($archivepath, $path);
        }
    }

    
    public function get_imageinfo() {
        $path = $this->get_content_file_location();
        if (!is_readable($path)) {
            if (!$this->fs->try_content_recovery($this) or !is_readable($path)) {
                throw new file_exception('storedfilecannotread', '', $path);
            }
        }
        $mimetype = $this->get_mimetype();
        if (!preg_match('|^image/|', $mimetype) || !filesize($path) || !($imageinfo = getimagesize($path))) {
            return false;
        }
        $image = array('width'=>$imageinfo[0], 'height'=>$imageinfo[1], 'mimetype'=>image_type_to_mime_type($imageinfo[2]));
        if (empty($image['width']) or empty($image['height']) or empty($image['mimetype'])) {
                        return false;
        }
        return $image;
    }

    
    public function is_valid_image() {
        $mimetype = $this->get_mimetype();
        if (!file_mimetype_in_typegroup($mimetype, 'web_image')) {
            return false;
        }
        if (!$info = $this->get_imageinfo()) {
            return false;
        }
        if ($info['mimetype'] !== $mimetype) {
            return false;
        }
                return true;
    }

    
    public function get_parent_directory() {
        if ($this->file_record->filepath === '/' and $this->file_record->filename === '.') {
                        return null;
        }

        if ($this->file_record->filename !== '.') {
            return $this->fs->create_directory($this->file_record->contextid, $this->file_record->component, $this->file_record->filearea, $this->file_record->itemid, $this->file_record->filepath);
        }

        $filepath = $this->file_record->filepath;
        $filepath = trim($filepath, '/');
        $dirs = explode('/', $filepath);
        array_pop($dirs);
        $filepath = implode('/', $dirs);
        $filepath = ($filepath === '') ? '/' : "/$filepath/";

        return $this->fs->create_directory($this->file_record->contextid, $this->file_record->component, $this->file_record->filearea, $this->file_record->itemid, $filepath);
    }

    
    public function sync_external_file() {
        if (!empty($this->repository)) {
            $this->repository->sync_reference($this);
        }
    }

    
    public function get_contextid() {
        return $this->file_record->contextid;
    }

    
    public function get_component() {
        return $this->file_record->component;
    }

    
    public function get_filearea() {
        return $this->file_record->filearea;
    }

    
    public function get_itemid() {
        return $this->file_record->itemid;
    }

    
    public function get_filepath() {
        return $this->file_record->filepath;
    }

    
    public function get_filename() {
        return $this->file_record->filename;
    }

    
    public function get_userid() {
        return $this->file_record->userid;
    }

    
    public function get_filesize() {
        $this->sync_external_file();
        return $this->file_record->filesize;
    }

     
    public function set_filesize($filesize) {
        throw new coding_exception('Function stored_file::set_filesize() can not be used any more. ' .
            'Please use stored_file::replace_file_with()');
    }

    
    public function get_mimetype() {
        return $this->file_record->mimetype;
    }

    
    public function get_timecreated() {
        return $this->file_record->timecreated;
    }

    
    public function get_timemodified() {
        $this->sync_external_file();
        return $this->file_record->timemodified;
    }

    
    public function set_timemodified($timemodified) {
        $filerecord = new stdClass;
        $filerecord->timemodified = $timemodified;
        $this->update($filerecord);
    }

    
    public function get_status() {
        return $this->file_record->status;
    }

    
    public function get_id() {
        return $this->file_record->id;
    }

    
    public function get_contenthash() {
        $this->sync_external_file();
        return $this->file_record->contenthash;
    }

    
    public function get_pathnamehash() {
        return $this->file_record->pathnamehash;
    }

    
    public function get_license() {
        return $this->file_record->license;
    }

    
    public function set_license($license) {
        $filerecord = new stdClass;
        $filerecord->license = $license;
        $this->update($filerecord);
    }

    
    public function get_author() {
        return $this->file_record->author;
    }

    
    public function set_author($author) {
        $filerecord = new stdClass;
        $filerecord->author = $author;
        $this->update($filerecord);
    }

    
    public function get_source() {
        return $this->file_record->source;
    }

    
    public function set_source($source) {
        $filerecord = new stdClass;
        $filerecord->source = $source;
        $this->update($filerecord);
    }


    
    public function get_sortorder() {
        return $this->file_record->sortorder;
    }

    
    public function set_sortorder($sortorder) {
        $filerecord = new stdClass;
        $filerecord->sortorder = $sortorder;
        $this->update($filerecord);
    }

    
    public function get_repository_id() {
        if (!empty($this->repository)) {
            return $this->repository->id;
        } else {
            return null;
        }
    }

    
    public function get_referencefileid() {
        return $this->file_record->referencefileid;
    }

    
    public function get_referencelastsync() {
        return $this->file_record->referencelastsync;
    }

    
    public function get_referencelifetime() {
        throw new coding_exception('Function stored_file::get_referencelifetime() can not be used any more. ' .
            'See repository::sync_reference().');
    }
    
    public function get_reference() {
        return $this->file_record->reference;
    }

    
    public function get_reference_details() {
        return $this->repository->get_reference_details($this->get_reference(), $this->get_status());
    }

    
    public function set_synchronized($contenthash, $filesize, $status = 0, $timemodified = null) {
        if (!$this->is_external_file()) {
            return;
        }
        $now = time();
        if ($contenthash === null) {
            $contenthash = $this->file_record->contenthash;
        }
        if ($contenthash != $this->file_record->contenthash) {
            $oldcontenthash = $this->file_record->contenthash;
        }
                $this->fs->update_references($this->file_record->referencefileid, $now, null, $contenthash, $filesize, $status, $timemodified);
                $this->file_record->contenthash = $contenthash;
        $this->file_record->filesize = $filesize;
        $this->file_record->status = $status;
        $this->file_record->referencelastsync = $now;
        if ($timemodified) {
            $this->file_record->timemodified = $timemodified;
        }
        if (isset($oldcontenthash)) {
            $this->fs->deleted_file_cleanup($oldcontenthash);
        }
    }

    
    public function set_missingsource() {
        $this->set_synchronized($this->file_record->contenthash, $this->file_record->filesize, 666);
    }

    
    public function send_file($lifetime, $filter, $forcedownload, $options) {
        $this->repository->send_file($this, $lifetime, $filter, $forcedownload, $options);
    }

    
    public function import_external_file_contents($maxbytes = 0) {
        if ($this->repository) {
            $this->repository->import_external_file_contents($this, $maxbytes);
        }
    }

    
    public function send_relative_file($relativepath) {
        if ($this->repository && $this->repository->supports_relative_file()) {
            $relativepath = clean_param($relativepath, PARAM_PATH);
            $this->repository->send_relative_file($this, $relativepath);
        } else {
            send_file_not_found();
        }
    }

    
    public function generate_image_thumbnail($width, $height) {
        if (empty($width) or empty($height)) {
            return false;
        }

                $imageinfo = @getimagesizefromstring($this->get_content());
        if (empty($imageinfo)) {
            return false;
        }

                $original = @imagecreatefromstring($this->get_content());

                return generate_image_thumbnail_from_image($original, $imageinfo, $width, $height);
    }
}
