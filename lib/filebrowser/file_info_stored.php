<?php




defined('MOODLE_INTERNAL') || die();


class file_info_stored extends file_info {
    
    protected $lf;
    
    protected $urlbase;
    
    protected $topvisiblename;
    
    protected $itemidused;
    
    protected $readaccess;
    
    protected $writeaccess;
    
    protected $areaonly;

    
    public function __construct(file_browser $browser, $context, $storedfile, $urlbase, $topvisiblename, $itemidused, $readaccess, $writeaccess, $areaonly) {
        parent::__construct($browser, $context);

        $this->lf             = $storedfile;
        $this->urlbase        = $urlbase;
        $this->topvisiblename = $topvisiblename;
        $this->itemidused     = $itemidused;
        $this->readaccess     = $readaccess;
        $this->writeaccess    = $writeaccess;
        $this->areaonly       = $areaonly;
    }

    
    public function get_params() {
        return array('contextid'=>$this->context->id,
                     'component'=>$this->lf->get_component(),
                     'filearea' =>$this->lf->get_filearea(),
                     'itemid'   =>$this->lf->get_itemid(),
                     'filepath' =>$this->lf->get_filepath(),
                     'filename' =>$this->lf->get_filename());
    }

    
    public function get_visible_name() {
        $filename = $this->lf->get_filename();
        $filepath = $this->lf->get_filepath();

        if ($filename !== '.') {
            return $filename;

        } else {
            $dir = trim($filepath, '/');
            $dir = explode('/', $dir);
            $dir = array_pop($dir);
            if ($dir === '') {
                return $this->topvisiblename;
            } else {
                return $dir;
            }
        }
    }

    
    public function get_readable_fullname() {
        global $CFG;
                $fpath = array();
        for ($parent = $this; $parent && $parent->get_parent(); $parent = $parent->get_parent()) {
            array_unshift($fpath, $parent->get_visible_name());
        }

        if ($this->lf->get_component() == 'user' && $this->lf->get_filearea() == 'private') {
                        $username = array_shift($fpath);
            array_shift($fpath);             return get_string('privatefilesof', 'repository', $username). ': '. join('/', $fpath);
        } else {
            
                        static $replocalname = null;
            if ($replocalname === null) {
                require_once($CFG->dirroot . "/repository/lib.php");
                $instances = repository::get_instances(array('type' => 'local'));
                if (count($instances)) {
                    $firstinstance = reset($instances);
                    $replocalname = $firstinstance->get_name();
                } else if (get_string_manager()->string_exists('pluginname', 'repository_local')) {
                    $replocalname = get_string('pluginname', 'repository_local');
                } else {
                    $replocalname = get_string('arearoot', 'repository');
                }
            }

            return $replocalname. ': '. join('/', $fpath);
        }
    }

    
    public function get_url($forcedownload=false, $https=false) {
        if (!$this->is_readable()) {
            return null;
        }

        if ($this->is_directory()) {
            return null;
        }

        $this->urlbase;
        $contextid = $this->lf->get_contextid();
        $component = $this->lf->get_component();
        $filearea  = $this->lf->get_filearea();
        $itemid    = $this->lf->get_itemid();
        $filepath  = $this->lf->get_filepath();
        $filename  = $this->lf->get_filename();

        if ($this->itemidused) {
            $path = '/'.$contextid.'/'.$component.'/'.$filearea.'/'.$itemid.$filepath.$filename;
        } else {
            $path = '/'.$contextid.'/'.$component.'/'.$filearea.$filepath.$filename;
        }
        return file_encode_url($this->urlbase, $path, $forcedownload, $https);
    }

    
    public function is_readable() {
        return $this->readaccess;
    }

    
    public function is_writable() {
        return $this->writeaccess;
    }

    
    public function is_empty_area() {
        if ($this->lf->get_filepath() === '/' and $this->lf->get_filename() === '.') {
                        $fs = get_file_storage();
            return $fs->is_area_empty($this->lf->get_contextid(), $this->lf->get_component(), $this->lf->get_filearea(), $this->lf->get_itemid());
        } else {
            return false;
        }
    }

    
    public function get_filesize() {
        return $this->lf->get_filesize();
    }

    
    public function get_imageinfo() {
        return $this->lf->get_imageinfo();
    }

    
    public function get_mimetype() {
        return $this->lf->get_mimetype();
    }

    
    public function get_timecreated() {
        return $this->lf->get_timecreated();
    }

    
    public function get_timemodified() {
        return $this->lf->get_timemodified();
    }

    
    public function is_directory() {
        return $this->lf->is_directory();
    }

    
    public function get_license() {
        return $this->lf->get_license();
    }

    
    public function get_author() {
        return $this->lf->get_author();
    }

    
    public function get_source() {
        return $this->lf->get_source();
    }

    
    public function get_sortorder() {
        return $this->lf->get_sortorder();
    }

    
    public function is_external_file() {
        return $this->lf->is_external_file();
    }

    
    public function get_status() {
        return $this->lf->get_status();
    }

    
    public function get_children() {
        if (!$this->lf->is_directory()) {
            return array();
        }

        $result = array();
        $fs = get_file_storage();

        $storedfiles = $fs->get_directory_files($this->context->id, $this->lf->get_component(), $this->lf->get_filearea(), $this->lf->get_itemid(),
                                                $this->lf->get_filepath(), false, true, "filepath, filename");
        foreach ($storedfiles as $file) {
            $result[] = new file_info_stored($this->browser, $this->context, $file, $this->urlbase, $this->topvisiblename,
                                             $this->itemidused, $this->readaccess, $this->writeaccess, false);
        }

        return $result;
    }

    
    public function get_non_empty_children($extensions = '*') {
        $result = array();
        if (!$this->lf->is_directory()) {
            return $result;
        }

        $fs = get_file_storage();

        $storedfiles = $fs->get_directory_files($this->context->id, $this->lf->get_component(), $this->lf->get_filearea(), $this->lf->get_itemid(),
                                                $this->lf->get_filepath(), false, true, "filepath, filename");
        foreach ($storedfiles as $file) {
            $extension = core_text::strtolower(pathinfo($file->get_filename(), PATHINFO_EXTENSION));
            if ($file->is_directory() || $extensions === '*' || (!empty($extension) && in_array('.'.$extension, $extensions))) {
                $fileinfo = new file_info_stored($this->browser, $this->context, $file, $this->urlbase, $this->topvisiblename,
                                                 $this->itemidused, $this->readaccess, $this->writeaccess, false);
                if (!$file->is_directory() || $fileinfo->count_non_empty_children($extensions)) {
                    $result[] = $fileinfo;
                }
            }
        }

        return $result;
    }

    
    public function count_non_empty_children($extensions = '*', $limit = 1) {
        global $DB;
        if (!$this->lf->is_directory()) {
            return 0;
        }

        $filepath = $this->lf->get_filepath();
        $length = core_text::strlen($filepath);
        $sql = "SELECT filepath, filename
                  FROM {files} f
                 WHERE f.contextid = :contextid AND f.component = :component AND f.filearea = :filearea AND f.itemid = :itemid
                       AND ".$DB->sql_substr("f.filepath", 1, $length)." = :filepath
                       AND filename <> '.' ";
        $params = array('contextid' => $this->context->id,
            'component' => $this->lf->get_component(),
            'filearea' => $this->lf->get_filearea(),
            'itemid' => $this->lf->get_itemid(),
            'filepath' => $filepath);
        list($sql2, $params2) = $this->build_search_files_sql($extensions);
        $rs = $DB->get_recordset_sql($sql.' '.$sql2, array_merge($params, $params2));
        $children = array();
        foreach ($rs as $record) {
                        if ($record->filepath === $filepath) {
                $children[] = $record->filename;
            } else {
                $path = explode('/', core_text::substr($record->filepath, $length));
                if (!in_array($path[0], $children)) {
                    $children[] = $path[0];
                }
            }
            if (count($children) >= $limit) {
                break;
            }
        }
        $rs->close();
        return count($children);
    }

    
    public function get_parent() {
        if ($this->lf->get_filepath() === '/' and $this->lf->is_directory()) {
            if ($this->areaonly) {
                return null;
            } else if ($this->itemidused) {
                return $this->browser->get_file_info($this->context, $this->lf->get_component(), $this->lf->get_filearea());
            } else {
                return $this->browser->get_file_info($this->context);
            }
        }

        if (!$this->lf->is_directory()) {
            return $this->browser->get_file_info($this->context, $this->lf->get_component(), $this->lf->get_filearea(), $this->lf->get_itemid(), $this->lf->get_filepath(), '.');
        }

        $filepath = $this->lf->get_filepath();
        $filepath = trim($filepath, '/');
        $dirs = explode('/', $filepath);
        array_pop($dirs);
        $filepath = implode('/', $dirs);
        $filepath = ($filepath === '') ? '/' : "/$filepath/";

        return $this->browser->get_file_info($this->context, $this->lf->get_component(), $this->lf->get_filearea(), $this->lf->get_itemid(), $filepath, '.');
    }

    
    public function create_directory($newdirname, $userid = NULL) {
        if (!$this->is_writable() or !$this->lf->is_directory()) {
            return null;
        }

        $newdirname = clean_param($newdirname, PARAM_FILE);
        if ($newdirname === '') {
            return null;
        }

        $filepath = $this->lf->get_filepath().'/'.$newdirname.'/';

        $fs = get_file_storage();

        if ($file = $fs->create_directory($this->lf->get_contextid(), $this->lf->get_component(), $this->lf->get_filearea(), $this->lf->get_itemid(), $filepath, $userid)) {
            return $this->browser->get_file_info($this->context, $this->lf->get_component(), $file->get_filearea(), $file->get_itemid(), $file->get_filepath(), $file->get_filename());
        }
        return null;
    }


    
    public function create_file_from_string($newfilename, $content, $userid = NULL) {
        if (!$this->is_writable() or !$this->lf->is_directory()) {
            return null;
        }

        $newfilename = clean_param($newfilename, PARAM_FILE);
        if ($newfilename === '') {
            return null;
        }

        $fs = get_file_storage();

        $now = time();

        $newrecord = new stdClass();
        $newrecord->contextid = $this->lf->get_contextid();
        $newrecord->component = $this->lf->get_component();
        $newrecord->filearea  = $this->lf->get_filearea();
        $newrecord->itemid    = $this->lf->get_itemid();
        $newrecord->filepath  = $this->lf->get_filepath();
        $newrecord->filename  = $newfilename;

        if ($fs->file_exists($newrecord->contextid, $newrecord->component, $newrecord->filearea, $newrecord->itemid, $newrecord->filepath, $newrecord->filename)) {
                        return null;
        }

        $newrecord->timecreated  = $now;
        $newrecord->timemodified = $now;
        $newrecord->mimetype     = mimeinfo('type', $newfilename);
        $newrecord->userid       = $userid;

        if ($file = $fs->create_file_from_string($newrecord, $content)) {
            return $this->browser->get_file_info($this->context, $file->get_component(), $file->get_filearea(), $file->get_itemid(), $file->get_filepath(), $file->get_filename());
        }
        return null;
    }

    
    public function create_file_from_pathname($newfilename, $pathname, $userid = NULL) {
        if (!$this->is_writable() or !$this->lf->is_directory()) {
            return null;
        }

        $newfilename = clean_param($newfilename, PARAM_FILE);
        if ($newfilename === '') {
            return null;
        }

        $fs = get_file_storage();

        $now = time();

        $newrecord = new stdClass();
        $newrecord->contextid = $this->lf->get_contextid();
        $newrecord->component = $this->lf->get_component();
        $newrecord->filearea  = $this->lf->get_filearea();
        $newrecord->itemid    = $this->lf->get_itemid();
        $newrecord->filepath  = $this->lf->get_filepath();
        $newrecord->filename  = $newfilename;

        if ($fs->file_exists($newrecord->contextid, $newrecord->component, $newrecord->filearea, $newrecord->itemid, $newrecord->filepath, $newrecord->filename)) {
                        return null;
        }

        $newrecord->timecreated  = $now;
        $newrecord->timemodified = $now;
        $newrecord->mimetype     = mimeinfo('type', $newfilename);
        $newrecord->userid       = $userid;

        if ($file = $fs->create_file_from_pathname($newrecord, $pathname)) {
            return $this->browser->get_file_info($this->context, $file->get_component(), $file->get_filearea(), $file->get_itemid(), $file->get_filepath(), $file->get_filename());
        }
        return null;
    }

    
    public function create_file_from_storedfile($newfilename, $fid, $userid = NULL) {
        if (!$this->is_writable() or $this->lf->get_filename() !== '.') {
            return null;
        }

        $newfilename = clean_param($newfilename, PARAM_FILE);
        if ($newfilename === '') {
            return null;
        }

        $fs = get_file_storage();

        $now = time();

        $newrecord = new stdClass();
        $newrecord->contextid = $this->lf->get_contextid();
        $newrecord->component = $this->lf->get_component();
        $newrecord->filearea  = $this->lf->get_filearea();
        $newrecord->itemid    = $this->lf->get_itemid();
        $newrecord->filepath  = $this->lf->get_filepath();
        $newrecord->filename  = $newfilename;

        if ($fs->file_exists($newrecord->contextid, $newrecord->component, $newrecord->filearea, $newrecord->itemid, $newrecord->filepath, $newrecord->filename)) {
                        return null;
        }

        $newrecord->timecreated  = $now;
        $newrecord->timemodified = $now;
        $newrecord->mimetype     = mimeinfo('type', $newfilename);
        $newrecord->userid       = $userid;

        if ($file = $fs->create_file_from_storedfile($newrecord, $fid)) {
            return $this->browser->get_file_info($this->context, $file->get_component(), $file->get_filearea(), $file->get_itemid(), $file->get_filepath(), $file->get_filename());
        }
        return null;
    }

    
    public function delete() {
        if (!$this->is_writable()) {
            return false;
        }

        if ($this->is_directory()) {
            $filepath = $this->lf->get_filepath();
            $fs = get_file_storage();
            $storedfiles = $fs->get_area_files($this->context->id, $this->get_component(), $this->lf->get_filearea(), $this->lf->get_itemid());
            foreach ($storedfiles as $file) {
                if (strpos($file->get_filepath(), $filepath) === 0) {
                    $file->delete();
                }
            }
        }

        return $this->lf->delete();
    }

    
    public function copy_to_storage($filerecord) {
        if (!$this->is_readable() or $this->is_directory()) {
            return false;
        }
        $filerecord = (array)$filerecord;

        $fs = get_file_storage();
        if ($existing = $fs->get_file($filerecord['contextid'], $filerecord['component'], $filerecord['filearea'], $filerecord['itemid'], $filerecord['filepath'], $filerecord['filename'])) {
            $existing->delete();
        }
        $fs->create_file_from_storedfile($filerecord, $this->lf);

        return true;
    }

    
    public function copy_to_pathname($pathname) {
        if (!$this->is_readable() or $this->is_directory()) {
            return false;
        }

        if (file_exists($pathname)) {
            if (!unlink($pathname)) {
                return false;
            }
        }

        $this->lf->copy_content_to($pathname);

        return true;
    }
}
