<?php




defined('MOODLE_INTERNAL') || die();

require_once("$CFG->libdir/filestorage/stored_file.php");


class file_storage {
    
    private $filedir;
    
    private $trashdir;
    
    private $tempdir;
    
    private $dirpermissions;
    
    private $filepermissions;
    
    private $unoconvformats;

        
    const UNOCONVPATH_OK = 'ok';
    
    const UNOCONVPATH_EMPTY = 'empty';
    
    const UNOCONVPATH_DOESNOTEXIST = 'doesnotexist';
    
    const UNOCONVPATH_ISDIR = 'isdir';
    
    const UNOCONVPATH_NOTEXECUTABLE = 'notexecutable';
    
    const UNOCONVPATH_NOTESTFILE = 'notestfile';
    
    const UNOCONVPATH_VERSIONNOTSUPPORTED = 'versionnotsupported';
    
    const UNOCONVPATH_ERROR = 'error';


    
    public function __construct($filedir, $trashdir, $tempdir, $dirpermissions, $filepermissions) {
        global $CFG;

        $this->filedir         = $filedir;
        $this->trashdir        = $trashdir;
        $this->tempdir         = $tempdir;
        $this->dirpermissions  = $dirpermissions;
        $this->filepermissions = $filepermissions;

                if (!is_dir($this->filedir)) {
            if (!mkdir($this->filedir, $this->dirpermissions, true)) {
                throw new file_exception('storedfilecannotcreatefiledirs');             }
                        if (!file_exists($this->filedir.'/warning.txt')) {
                file_put_contents($this->filedir.'/warning.txt',
                                  'This directory contains the content of uploaded files and is controlled by Moodle code. Do not manually move, change or rename any of the files and subdirectories here.');
                chmod($this->filedir.'/warning.txt', $CFG->filepermissions);
            }
        }
                if (!is_dir($this->trashdir)) {
            if (!mkdir($this->trashdir, $this->dirpermissions, true)) {
                throw new file_exception('storedfilecannotcreatefiledirs');             }
        }
    }

    
    public static function get_pathname_hash($contextid, $component, $filearea, $itemid, $filepath, $filename) {
        return sha1("/$contextid/$component/$filearea/$itemid".$filepath.$filename);
    }

    
    public function file_exists($contextid, $component, $filearea, $itemid, $filepath, $filename) {
        $filepath = clean_param($filepath, PARAM_PATH);
        $filename = clean_param($filename, PARAM_FILE);

        if ($filename === '') {
            $filename = '.';
        }

        $pathnamehash = $this->get_pathname_hash($contextid, $component, $filearea, $itemid, $filepath, $filename);
        return $this->file_exists_by_hash($pathnamehash);
    }

    
    public function file_exists_by_hash($pathnamehash) {
        global $DB;

        return $DB->record_exists('files', array('pathnamehash'=>$pathnamehash));
    }

    
    public function get_file_instance(stdClass $filerecord) {
        $storedfile = new stored_file($this, $filerecord, $this->filedir);
        return $storedfile;
    }

    
    public function get_converted_document(stored_file $file, $format, $forcerefresh = false) {

        $context = context_system::instance();
        $path = '/' . $format . '/';
        $conversion = $this->get_file($context->id, 'core', 'documentconversion', 0, $path, $file->get_contenthash());

        if (!$conversion || $forcerefresh) {
            $conversion = $this->create_converted_document($file, $format, $forcerefresh);
            if (!$conversion) {
                return false;
            }
        }

        return $conversion;
    }

    
    protected function is_format_supported_by_unoconv($format) {
        global $CFG;

        if (!isset($this->unoconvformats)) {
                        $cmd = escapeshellcmd(trim($CFG->pathtounoconv)) . ' --show';
            $pipes = array();
            $pipesspec = array(2 => array('pipe', 'w'));
            $proc = proc_open($cmd, $pipesspec, $pipes);
            $programoutput = stream_get_contents($pipes[2]);
            fclose($pipes[2]);
            proc_close($proc);
            $matches = array();
            preg_match_all('/\[\.(.*)\]/', $programoutput, $matches);

            $this->unoconvformats = $matches[1];
            $this->unoconvformats = array_unique($this->unoconvformats);
        }

        $sanitized = trim(core_text::strtolower($format));
        return in_array($sanitized, $this->unoconvformats);
    }

    
    public static function can_convert_documents() {
        global $CFG;
        $currentversion = 0;
        $supportedversion = 0.7;
        $unoconvbin = \escapeshellarg($CFG->pathtounoconv);
        $command = "$unoconvbin --version";
        exec($command, $output);
                if ($output) {
            foreach ($output as $response) {
                if (preg_match('/unoconv (\\d+\\.\\d+)/', $response, $matches)) {
                    $currentversion = (float)$matches[1];
                }
            }
            if ($currentversion < $supportedversion) {
                return false;
            }
            return true;
        }
        return false;
    }

    
    public static function send_test_pdf() {
        global $CFG;
        require_once($CFG->libdir . '/filelib.php');

        $filerecord = array(
            'contextid' => \context_system::instance()->id,
            'component' => 'test',
            'filearea' => 'assignfeedback_editpdf',
            'itemid' => 0,
            'filepath' => '/',
            'filename' => 'unoconv_test.docx'
        );

                $fs = get_file_storage();
        $fixturefile = $CFG->libdir . '/tests/fixtures/unoconv-source.docx';
        $fixturedata = file_get_contents($fixturefile);
        $testdocx = $fs->get_file($filerecord['contextid'], $filerecord['component'], $filerecord['filearea'],
                $filerecord['itemid'], $filerecord['filepath'], $filerecord['filename']);
        if (!$testdocx) {
            $testdocx = $fs->create_file_from_string($filerecord, $fixturedata);

        }

                $result = $fs->get_converted_document($testdocx, 'pdf', true);
        readfile_accel($result, 'application/pdf', true);
    }

    
    public static function test_unoconv_path() {
        global $CFG;
        $unoconvpath = $CFG->pathtounoconv;

        $ret = new \stdClass();
        $ret->status = self::UNOCONVPATH_OK;
        $ret->message = null;

        if (empty($unoconvpath)) {
            $ret->status = self::UNOCONVPATH_EMPTY;
            return $ret;
        }
        if (!file_exists($unoconvpath)) {
            $ret->status = self::UNOCONVPATH_DOESNOTEXIST;
            return $ret;
        }
        if (is_dir($unoconvpath)) {
            $ret->status = self::UNOCONVPATH_ISDIR;
            return $ret;
        }
        if (!file_is_executable($unoconvpath)) {
            $ret->status = self::UNOCONVPATH_NOTEXECUTABLE;
            return $ret;
        }
        if (!\file_storage::can_convert_documents()) {
            $ret->status = self::UNOCONVPATH_VERSIONNOTSUPPORTED;
            return $ret;
        }

        return $ret;
    }

    
    protected function create_converted_document(stored_file $file, $format, $forcerefresh = false) {
        global $CFG;

        if (empty($CFG->pathtounoconv) || !file_is_executable(trim($CFG->pathtounoconv))) {
                        return false;
        }

        $fileextension = core_text::strtolower(pathinfo($file->get_filename(), PATHINFO_EXTENSION));
        if (!self::is_format_supported_by_unoconv($fileextension)) {
            return false;
        }

        if (!self::is_format_supported_by_unoconv($format)) {
            return false;
        }

                $uniqdir = "core_file/conversions/" . uniqid($file->get_id() . "-", true);
        $tmp = make_temp_directory($uniqdir);
        $ext = pathinfo($file->get_filename(), PATHINFO_EXTENSION);
                $localfilename = $file->get_id() . '.' . $ext;

        $filename = $tmp . '/' . $localfilename;
        try {
                        if ($file->copy_content_to($filename) === false) {
                throw new file_exception('storedfileproblem', 'Could not copy file contents to temp file.');
            }
        } catch (file_exception $fe) {
            remove_dir($tmp);
            throw $fe;
        }

        $newtmpfile = pathinfo($filename, PATHINFO_FILENAME) . '.' . $format;

                $newtmpfile = $tmp . '/' . clean_param($newtmpfile, PARAM_FILE);

        $cmd = escapeshellcmd(trim($CFG->pathtounoconv)) . ' ' .
               escapeshellarg('-f') . ' ' .
               escapeshellarg($format) . ' ' .
               escapeshellarg('-o') . ' ' .
               escapeshellarg($newtmpfile) . ' ' .
               escapeshellarg($filename);

        $output = null;
        $currentdir = getcwd();
        chdir($tmp);
        $result = exec($cmd, $output);
        chdir($currentdir);
        touch($newtmpfile);
        if (filesize($newtmpfile) === 0) {
            remove_dir($tmp);
                        return false;
        }

        $context = context_system::instance();
        $path = '/' . $format . '/';
        $record = array(
            'contextid' => $context->id,
            'component' => 'core',
            'filearea'  => 'documentconversion',
            'itemid'    => 0,
            'filepath'  => $path,
            'filename'  => $file->get_contenthash(),
        );

        if ($forcerefresh) {
            $existing = $this->get_file($context->id, 'core', 'documentconversion', 0, $path, $file->get_contenthash());
            if ($existing) {
                $existing->delete();
            }
        }

        $convertedfile = $this->create_file_from_pathname($record, $newtmpfile);
                remove_dir($tmp);
        return $convertedfile;
    }

    
    public function get_file_preview(stored_file $file, $mode) {

        $context = context_system::instance();
        $path = '/' . trim($mode, '/') . '/';
        $preview = $this->get_file($context->id, 'core', 'preview', 0, $path, $file->get_contenthash());

        if (!$preview) {
            $preview = $this->create_file_preview($file, $mode);
            if (!$preview) {
                return false;
            }
        }

        return $preview;
    }

    
    public function get_unused_filename($contextid, $component, $filearea, $itemid, $filepath, $filename) {
        global $DB;

                if ($filename == '.' || (empty($filename) && !is_numeric($filename))) {
            throw new coding_exception('Invalid file name passed', $filename);
        }

                if (!$this->file_exists($contextid, $component, $filearea, $itemid, $filepath, $filename)) {
            return $filename;
        }

                $pathinfo = pathinfo($filename);
        $basename = $pathinfo['filename'];
        $matches = array();
        if (preg_match('~^(.+) \(([0-9]+)\)$~', $basename, $matches)) {
            $basename = $matches[1];
        }

        $filenamelike = $DB->sql_like_escape($basename) . ' (%)';
        if (isset($pathinfo['extension'])) {
            $filenamelike .= '.' . $DB->sql_like_escape($pathinfo['extension']);
        }

        $filenamelikesql = $DB->sql_like('f.filename', ':filenamelike');
        $filenamelen = $DB->sql_length('f.filename');
        $sql = "SELECT filename
                FROM {files} f
                WHERE
                    f.contextid = :contextid AND
                    f.component = :component AND
                    f.filearea = :filearea AND
                    f.itemid = :itemid AND
                    f.filepath = :filepath AND
                    $filenamelikesql
                ORDER BY
                    $filenamelen DESC,
                    f.filename DESC";
        $params = array('contextid' => $contextid, 'component' => $component, 'filearea' => $filearea, 'itemid' => $itemid,
                'filepath' => $filepath, 'filenamelike' => $filenamelike);
        $results = $DB->get_fieldset_sql($sql, $params, IGNORE_MULTIPLE);

                        $number = 1;
        foreach ($results as $result) {
            $resultbasename = pathinfo($result, PATHINFO_FILENAME);
            $matches = array();
            if (preg_match('~^(.+) \(([0-9]+)\)$~', $resultbasename, $matches)) {
                $number = $matches[2] + 1;
                break;
            }
        }

                $newfilename = $basename . ' (' . $number . ')';
        if (isset($pathinfo['extension'])) {
            $newfilename .= '.' . $pathinfo['extension'];
        }

        return $newfilename;
    }

    
    public function get_unused_dirname($contextid, $component, $filearea, $itemid, $suggestedpath) {
        global $DB;

                $suggestedpath = rtrim($suggestedpath, '/'). '/';

                if (!$this->file_exists($contextid, $component, $filearea, $itemid, $suggestedpath, '.')) {
            return $suggestedpath;
        }

                if (preg_match('~^(/.+) \(([0-9]+)\)/$~', $suggestedpath, $matches)) {
            $suggestedpath = $matches[1]. '/';
        }

        $filepathlike = $DB->sql_like_escape(rtrim($suggestedpath, '/')) . ' (%)/';

        $filepathlikesql = $DB->sql_like('f.filepath', ':filepathlike');
        $filepathlen = $DB->sql_length('f.filepath');
        $sql = "SELECT filepath
                FROM {files} f
                WHERE
                    f.contextid = :contextid AND
                    f.component = :component AND
                    f.filearea = :filearea AND
                    f.itemid = :itemid AND
                    f.filename = :filename AND
                    $filepathlikesql
                ORDER BY
                    $filepathlen DESC,
                    f.filepath DESC";
        $params = array('contextid' => $contextid, 'component' => $component, 'filearea' => $filearea, 'itemid' => $itemid,
                'filename' => '.', 'filepathlike' => $filepathlike);
        $results = $DB->get_fieldset_sql($sql, $params, IGNORE_MULTIPLE);

                        $number = 1;
        foreach ($results as $result) {
            if (preg_match('~ \(([0-9]+)\)/$~', $result, $matches)) {
                $number = (int)($matches[1]) + 1;
                break;
            }
        }

        return rtrim($suggestedpath, '/'). ' (' . $number . ')/';
    }

    
    protected function create_file_preview(stored_file $file, $mode) {

        $mimetype = $file->get_mimetype();

        if ($mimetype === 'image/gif' or $mimetype === 'image/jpeg' or $mimetype === 'image/png') {
                        $data = $this->create_imagefile_preview($file, $mode);

        } else {
                        return false;
        }

        if (empty($data)) {
            return false;
        }

        $context = context_system::instance();
        $record = array(
            'contextid' => $context->id,
            'component' => 'core',
            'filearea'  => 'preview',
            'itemid'    => 0,
            'filepath'  => '/' . trim($mode, '/') . '/',
            'filename'  => $file->get_contenthash(),
        );

        $imageinfo = getimagesizefromstring($data);
        if ($imageinfo) {
            $record['mimetype'] = $imageinfo['mime'];
        }

        return $this->create_file_from_string($record, $data);
    }

    
    protected function create_imagefile_preview(stored_file $file, $mode) {
        global $CFG;
        require_once($CFG->libdir.'/gdlib.php');

        if ($mode === 'tinyicon') {
            $data = $file->generate_image_thumbnail(24, 24);

        } else if ($mode === 'thumb') {
            $data = $file->generate_image_thumbnail(90, 90);

        } else if ($mode === 'bigthumb') {
            $data = $file->generate_image_thumbnail(250, 250);

        } else {
            throw new file_exception('storedfileproblem', 'Invalid preview mode requested');
        }

        return $data;
    }

    
    public function get_file_by_id($fileid) {
        global $DB;

        $sql = "SELECT ".self::instance_sql_fields('f', 'r')."
                  FROM {files} f
             LEFT JOIN {files_reference} r
                       ON f.referencefileid = r.id
                 WHERE f.id = ?";
        if ($filerecord = $DB->get_record_sql($sql, array($fileid))) {
            return $this->get_file_instance($filerecord);
        } else {
            return false;
        }
    }

    
    public function get_file_by_hash($pathnamehash) {
        global $DB;

        $sql = "SELECT ".self::instance_sql_fields('f', 'r')."
                  FROM {files} f
             LEFT JOIN {files_reference} r
                       ON f.referencefileid = r.id
                 WHERE f.pathnamehash = ?";
        if ($filerecord = $DB->get_record_sql($sql, array($pathnamehash))) {
            return $this->get_file_instance($filerecord);
        } else {
            return false;
        }
    }

    
    public function get_file($contextid, $component, $filearea, $itemid, $filepath, $filename) {
        $filepath = clean_param($filepath, PARAM_PATH);
        $filename = clean_param($filename, PARAM_FILE);

        if ($filename === '') {
            $filename = '.';
        }

        $pathnamehash = $this->get_pathname_hash($contextid, $component, $filearea, $itemid, $filepath, $filename);
        return $this->get_file_by_hash($pathnamehash);
    }

    
    public function is_area_empty($contextid, $component, $filearea, $itemid = false, $ignoredirs = true) {
        global $DB;

        $params = array('contextid'=>$contextid, 'component'=>$component, 'filearea'=>$filearea);
        $where = "contextid = :contextid AND component = :component AND filearea = :filearea";

        if ($itemid !== false) {
            $params['itemid'] = $itemid;
            $where .= " AND itemid = :itemid";
        }

        if ($ignoredirs) {
            $sql = "SELECT 'x'
                      FROM {files}
                     WHERE $where AND filename <> '.'";
        } else {
            $sql = "SELECT 'x'
                      FROM {files}
                     WHERE $where AND (filename <> '.' OR filepath <> '/')";
        }

        return !$DB->record_exists_sql($sql, $params);
    }

    
    public function get_external_files($repositoryid, $sort = '') {
        global $DB;
        $sql = "SELECT ".self::instance_sql_fields('f', 'r')."
                  FROM {files} f
             LEFT JOIN {files_reference} r
                       ON f.referencefileid = r.id
                 WHERE r.repositoryid = ?";
        if (!empty($sort)) {
            $sql .= " ORDER BY {$sort}";
        }

        $result = array();
        $filerecords = $DB->get_records_sql($sql, array($repositoryid));
        foreach ($filerecords as $filerecord) {
            $result[$filerecord->pathnamehash] = $this->get_file_instance($filerecord);
        }
        return $result;
    }

    
    public function get_area_files($contextid, $component, $filearea, $itemid = false, $sort = "itemid, filepath, filename", $includedirs = true) {
        global $DB;

        $conditions = array('contextid'=>$contextid, 'component'=>$component, 'filearea'=>$filearea);
        if ($itemid !== false) {
            $itemidsql = ' AND f.itemid = :itemid ';
            $conditions['itemid'] = $itemid;
        } else {
            $itemidsql = '';
        }

        $sql = "SELECT ".self::instance_sql_fields('f', 'r')."
                  FROM {files} f
             LEFT JOIN {files_reference} r
                       ON f.referencefileid = r.id
                 WHERE f.contextid = :contextid
                       AND f.component = :component
                       AND f.filearea = :filearea
                       $itemidsql";
        if (!empty($sort)) {
            $sql .= " ORDER BY {$sort}";
        }

        $result = array();
        $filerecords = $DB->get_records_sql($sql, $conditions);
        foreach ($filerecords as $filerecord) {
            if (!$includedirs and $filerecord->filename === '.') {
                continue;
            }
            $result[$filerecord->pathnamehash] = $this->get_file_instance($filerecord);
        }
        return $result;
    }

    
    public function get_area_tree($contextid, $component, $filearea, $itemid) {
        $result = array('dirname'=>'', 'dirfile'=>null, 'subdirs'=>array(), 'files'=>array());
        $files = $this->get_area_files($contextid, $component, $filearea, $itemid, '', true);
                foreach ($files as $hash=>$dir) {
            if (!$dir->is_directory()) {
                continue;
            }
            unset($files[$hash]);
            if ($dir->get_filepath() === '/') {
                $result['dirfile'] = $dir;
                continue;
            }
            $parts = explode('/', trim($dir->get_filepath(),'/'));
            $pointer =& $result;
            foreach ($parts as $part) {
                if ($part === '') {
                    continue;
                }
                if (!isset($pointer['subdirs'][$part])) {
                    $pointer['subdirs'][$part] = array('dirname'=>$part, 'dirfile'=>null, 'subdirs'=>array(), 'files'=>array());
                }
                $pointer =& $pointer['subdirs'][$part];
            }
            $pointer['dirfile'] = $dir;
            unset($pointer);
        }
        foreach ($files as $hash=>$file) {
            $parts = explode('/', trim($file->get_filepath(),'/'));
            $pointer =& $result;
            foreach ($parts as $part) {
                if ($part === '') {
                    continue;
                }
                $pointer =& $pointer['subdirs'][$part];
            }
            $pointer['files'][$file->get_filename()] = $file;
            unset($pointer);
        }
        $result = $this->sort_area_tree($result);
        return $result;
    }

    
    protected function sort_area_tree($tree) {
        foreach ($tree as $key => &$value) {
            if ($key == 'subdirs') {
                core_collator::ksort($value, core_collator::SORT_NATURAL);
                foreach ($value as $subdirname => &$subtree) {
                    $subtree = $this->sort_area_tree($subtree);
                }
            } else if ($key == 'files') {
                core_collator::ksort($value, core_collator::SORT_NATURAL);
            }
        }
        return $tree;
    }

    
    public function get_directory_files($contextid, $component, $filearea, $itemid, $filepath, $recursive = false, $includedirs = true, $sort = "filepath, filename") {
        global $DB;

        if (!$directory = $this->get_file($contextid, $component, $filearea, $itemid, $filepath, '.')) {
            return array();
        }

        $orderby = (!empty($sort)) ? " ORDER BY {$sort}" : '';

        if ($recursive) {

            $dirs = $includedirs ? "" : "AND filename <> '.'";
            $length = core_text::strlen($filepath);

            $sql = "SELECT ".self::instance_sql_fields('f', 'r')."
                      FROM {files} f
                 LEFT JOIN {files_reference} r
                           ON f.referencefileid = r.id
                     WHERE f.contextid = :contextid AND f.component = :component AND f.filearea = :filearea AND f.itemid = :itemid
                           AND ".$DB->sql_substr("f.filepath", 1, $length)." = :filepath
                           AND f.id <> :dirid
                           $dirs
                           $orderby";
            $params = array('contextid'=>$contextid, 'component'=>$component, 'filearea'=>$filearea, 'itemid'=>$itemid, 'filepath'=>$filepath, 'dirid'=>$directory->get_id());

            $files = array();
            $dirs  = array();
            $filerecords = $DB->get_records_sql($sql, $params);
            foreach ($filerecords as $filerecord) {
                if ($filerecord->filename == '.') {
                    $dirs[$filerecord->pathnamehash] = $this->get_file_instance($filerecord);
                } else {
                    $files[$filerecord->pathnamehash] = $this->get_file_instance($filerecord);
                }
            }
            $result = array_merge($dirs, $files);

        } else {
            $result = array();
            $params = array('contextid'=>$contextid, 'component'=>$component, 'filearea'=>$filearea, 'itemid'=>$itemid, 'filepath'=>$filepath, 'dirid'=>$directory->get_id());

            $length = core_text::strlen($filepath);

            if ($includedirs) {
                $sql = "SELECT ".self::instance_sql_fields('f', 'r')."
                          FROM {files} f
                     LEFT JOIN {files_reference} r
                               ON f.referencefileid = r.id
                         WHERE f.contextid = :contextid AND f.component = :component AND f.filearea = :filearea
                               AND f.itemid = :itemid AND f.filename = '.'
                               AND ".$DB->sql_substr("f.filepath", 1, $length)." = :filepath
                               AND f.id <> :dirid
                               $orderby";
                $reqlevel = substr_count($filepath, '/') + 1;
                $filerecords = $DB->get_records_sql($sql, $params);
                foreach ($filerecords as $filerecord) {
                    if (substr_count($filerecord->filepath, '/') !== $reqlevel) {
                        continue;
                    }
                    $result[$filerecord->pathnamehash] = $this->get_file_instance($filerecord);
                }
            }

            $sql = "SELECT ".self::instance_sql_fields('f', 'r')."
                      FROM {files} f
                 LEFT JOIN {files_reference} r
                           ON f.referencefileid = r.id
                     WHERE f.contextid = :contextid AND f.component = :component AND f.filearea = :filearea AND f.itemid = :itemid
                           AND f.filepath = :filepath AND f.filename <> '.'
                           $orderby";

            $filerecords = $DB->get_records_sql($sql, $params);
            foreach ($filerecords as $filerecord) {
                $result[$filerecord->pathnamehash] = $this->get_file_instance($filerecord);
            }
        }

        return $result;
    }

    
    public function delete_area_files($contextid, $component = false, $filearea = false, $itemid = false) {
        global $DB;

        $conditions = array('contextid'=>$contextid);
        if ($component !== false) {
            $conditions['component'] = $component;
        }
        if ($filearea !== false) {
            $conditions['filearea'] = $filearea;
        }
        if ($itemid !== false) {
            $conditions['itemid'] = $itemid;
        }

        $filerecords = $DB->get_records('files', $conditions);
        foreach ($filerecords as $filerecord) {
            $this->get_file_instance($filerecord)->delete();
        }

        return true;     }

    
    public function delete_area_files_select($contextid, $component,
            $filearea, $itemidstest, array $params = null) {
        global $DB;

        $where = "contextid = :contextid
                AND component = :component
                AND filearea = :filearea
                AND itemid $itemidstest";
        $params['contextid'] = $contextid;
        $params['component'] = $component;
        $params['filearea'] = $filearea;

        $filerecords = $DB->get_recordset_select('files', $where, $params);
        foreach ($filerecords as $filerecord) {
            $this->get_file_instance($filerecord)->delete();
        }
        $filerecords->close();
    }

    
    public function delete_component_files($component) {
        global $DB;

        $filerecords = $DB->get_recordset('files', array('component' => $component));
        foreach ($filerecords as $filerecord) {
            $this->get_file_instance($filerecord)->delete();
        }
        $filerecords->close();
    }

    
    public function move_area_files_to_new_context($oldcontextid, $newcontextid, $component, $filearea, $itemid = false) {
                                $count = 0;

        $oldfiles = $this->get_area_files($oldcontextid, $component, $filearea, $itemid, 'id', false);
        foreach ($oldfiles as $oldfile) {
            $filerecord = new stdClass();
            $filerecord->contextid = $newcontextid;
            $this->create_file_from_storedfile($filerecord, $oldfile);
            $count += 1;
        }

        if ($count) {
            $this->delete_area_files($oldcontextid, $component, $filearea, $itemid);
        }

        return $count;
    }

    
    public function create_directory($contextid, $component, $filearea, $itemid, $filepath, $userid = null) {
        global $DB;

                if (!is_number($contextid) or $contextid < 1) {
            throw new file_exception('storedfileproblem', 'Invalid contextid');
        }

        $component = clean_param($component, PARAM_COMPONENT);
        if (empty($component)) {
            throw new file_exception('storedfileproblem', 'Invalid component');
        }

        $filearea = clean_param($filearea, PARAM_AREA);
        if (empty($filearea)) {
            throw new file_exception('storedfileproblem', 'Invalid filearea');
        }

        if (!is_number($itemid) or $itemid < 0) {
            throw new file_exception('storedfileproblem', 'Invalid itemid');
        }

        $filepath = clean_param($filepath, PARAM_PATH);
        if (strpos($filepath, '/') !== 0 or strrpos($filepath, '/') !== strlen($filepath)-1) {
                        throw new file_exception('storedfileproblem', 'Invalid file path');
        }

        $pathnamehash = $this->get_pathname_hash($contextid, $component, $filearea, $itemid, $filepath, '.');

        if ($dir_info = $this->get_file_by_hash($pathnamehash)) {
            return $dir_info;
        }

        static $contenthash = null;
        if (!$contenthash) {
            $this->add_string_to_pool('');
            $contenthash = sha1('');
        }

        $now = time();

        $dir_record = new stdClass();
        $dir_record->contextid = $contextid;
        $dir_record->component = $component;
        $dir_record->filearea  = $filearea;
        $dir_record->itemid    = $itemid;
        $dir_record->filepath  = $filepath;
        $dir_record->filename  = '.';
        $dir_record->contenthash  = $contenthash;
        $dir_record->filesize  = 0;

        $dir_record->timecreated  = $now;
        $dir_record->timemodified = $now;
        $dir_record->mimetype     = null;
        $dir_record->userid       = $userid;

        $dir_record->pathnamehash = $pathnamehash;

        $DB->insert_record('files', $dir_record);
        $dir_info = $this->get_file_by_hash($pathnamehash);

        if ($filepath !== '/') {
                        $filepath = trim($filepath, '/');
            $filepath = explode('/', $filepath);
            array_pop($filepath);
            $filepath = implode('/', $filepath);
            $filepath = ($filepath === '') ? '/' : "/$filepath/";
            $this->create_directory($contextid, $component, $filearea, $itemid, $filepath, $userid);
        }

        return $dir_info;
    }

    
    public function create_file_from_storedfile($filerecord, $fileorid) {
        global $DB;

        if ($fileorid instanceof stored_file) {
            $fid = $fileorid->get_id();
        } else {
            $fid = $fileorid;
        }

        $filerecord = (array)$filerecord; 
        unset($filerecord['id']);
        unset($filerecord['filesize']);
        unset($filerecord['contenthash']);
        unset($filerecord['pathnamehash']);

        $sql = "SELECT ".self::instance_sql_fields('f', 'r')."
                  FROM {files} f
             LEFT JOIN {files_reference} r
                       ON f.referencefileid = r.id
                 WHERE f.id = ?";

        if (!$newrecord = $DB->get_record_sql($sql, array($fid))) {
            throw new file_exception('storedfileproblem', 'File does not exist');
        }

        unset($newrecord->id);

        foreach ($filerecord as $key => $value) {
                        if ($key == 'contextid' and (!is_number($value) or $value < 1)) {
                throw new file_exception('storedfileproblem', 'Invalid contextid');
            }

            if ($key == 'component') {
                $value = clean_param($value, PARAM_COMPONENT);
                if (empty($value)) {
                    throw new file_exception('storedfileproblem', 'Invalid component');
                }
            }

            if ($key == 'filearea') {
                $value = clean_param($value, PARAM_AREA);
                if (empty($value)) {
                    throw new file_exception('storedfileproblem', 'Invalid filearea');
                }
            }

            if ($key == 'itemid' and (!is_number($value) or $value < 0)) {
                throw new file_exception('storedfileproblem', 'Invalid itemid');
            }


            if ($key == 'filepath') {
                $value = clean_param($value, PARAM_PATH);
                if (strpos($value, '/') !== 0 or strrpos($value, '/') !== strlen($value)-1) {
                                        throw new file_exception('storedfileproblem', 'Invalid file path');
                }
            }

            if ($key == 'filename') {
                $value = clean_param($value, PARAM_FILE);
                if ($value === '') {
                                        throw new file_exception('storedfileproblem', 'Invalid file name');
                }
            }

            if ($key === 'timecreated' or $key === 'timemodified') {
                if (!is_number($value)) {
                    throw new file_exception('storedfileproblem', 'Invalid file '.$key);
                }
                if ($value < 0) {
                                        $value = 0;
                }
            }

            if ($key == 'referencefileid' or $key == 'referencelastsync') {
                $value = clean_param($value, PARAM_INT);
            }

            $newrecord->$key = $value;
        }

        $newrecord->pathnamehash = $this->get_pathname_hash($newrecord->contextid, $newrecord->component, $newrecord->filearea, $newrecord->itemid, $newrecord->filepath, $newrecord->filename);

        if ($newrecord->filename === '.') {
                        $directory = $this->create_directory($newrecord->contextid, $newrecord->component, $newrecord->filearea, $newrecord->itemid, $newrecord->filepath, $newrecord->userid);
                        $newrecord->id = $directory->get_id();
            $DB->update_record('files', $newrecord);
            return $this->get_file_instance($newrecord);
        }

                                if (!empty($newrecord->repositoryid)) {
            if ($newrecord->referencefileid != $this->get_referencefileid($newrecord->repositoryid, $newrecord->reference, MUST_EXIST)) {
                throw new file_reference_exception($newrecord->repositoryid, $newrecord->reference, $newrecord->referencefileid);
            }
        }

        try {
            $newrecord->id = $DB->insert_record('files', $newrecord);
        } catch (dml_exception $e) {
            throw new stored_file_creation_exception($newrecord->contextid, $newrecord->component, $newrecord->filearea, $newrecord->itemid,
                                                     $newrecord->filepath, $newrecord->filename, $e->debuginfo);
        }


        $this->create_directory($newrecord->contextid, $newrecord->component, $newrecord->filearea, $newrecord->itemid, $newrecord->filepath, $newrecord->userid);

        return $this->get_file_instance($newrecord);
    }

    
    public function create_file_from_url($filerecord, $url, array $options = null, $usetempfile = false) {

        $filerecord = (array)$filerecord;          $filerecord = (object)$filerecord; 
        $headers        = isset($options['headers'])        ? $options['headers'] : null;
        $postdata       = isset($options['postdata'])       ? $options['postdata'] : null;
        $fullresponse   = isset($options['fullresponse'])   ? $options['fullresponse'] : false;
        $timeout        = isset($options['timeout'])        ? $options['timeout'] : 300;
        $connecttimeout = isset($options['connecttimeout']) ? $options['connecttimeout'] : 20;
        $skipcertverify = isset($options['skipcertverify']) ? $options['skipcertverify'] : false;
        $calctimeout    = isset($options['calctimeout'])    ? $options['calctimeout'] : false;

        if (!isset($filerecord->filename)) {
            $parts = explode('/', $url);
            $filename = array_pop($parts);
            $filerecord->filename = clean_param($filename, PARAM_FILE);
        }
        $source = !empty($filerecord->source) ? $filerecord->source : $url;
        $filerecord->source = clean_param($source, PARAM_URL);

        if ($usetempfile) {
            check_dir_exists($this->tempdir);
            $tmpfile = tempnam($this->tempdir, 'newfromurl');
            $content = download_file_content($url, $headers, $postdata, $fullresponse, $timeout, $connecttimeout, $skipcertverify, $tmpfile, $calctimeout);
            if ($content === false) {
                throw new file_exception('storedfileproblem', 'Can not fetch file form URL');
            }
            try {
                $newfile = $this->create_file_from_pathname($filerecord, $tmpfile);
                @unlink($tmpfile);
                return $newfile;
            } catch (Exception $e) {
                @unlink($tmpfile);
                throw $e;
            }

        } else {
            $content = download_file_content($url, $headers, $postdata, $fullresponse, $timeout, $connecttimeout, $skipcertverify, NULL, $calctimeout);
            if ($content === false) {
                throw new file_exception('storedfileproblem', 'Can not fetch file form URL');
            }
            return $this->create_file_from_string($filerecord, $content);
        }
    }

    
    public function create_file_from_pathname($filerecord, $pathname) {
        global $DB;

        $filerecord = (array)$filerecord;          $filerecord = (object)$filerecord; 
                if (!is_number($filerecord->contextid) or $filerecord->contextid < 1) {
            throw new file_exception('storedfileproblem', 'Invalid contextid');
        }

        $filerecord->component = clean_param($filerecord->component, PARAM_COMPONENT);
        if (empty($filerecord->component)) {
            throw new file_exception('storedfileproblem', 'Invalid component');
        }

        $filerecord->filearea = clean_param($filerecord->filearea, PARAM_AREA);
        if (empty($filerecord->filearea)) {
            throw new file_exception('storedfileproblem', 'Invalid filearea');
        }

        if (!is_number($filerecord->itemid) or $filerecord->itemid < 0) {
            throw new file_exception('storedfileproblem', 'Invalid itemid');
        }

        if (!empty($filerecord->sortorder)) {
            if (!is_number($filerecord->sortorder) or $filerecord->sortorder < 0) {
                $filerecord->sortorder = 0;
            }
        } else {
            $filerecord->sortorder = 0;
        }

        $filerecord->filepath = clean_param($filerecord->filepath, PARAM_PATH);
        if (strpos($filerecord->filepath, '/') !== 0 or strrpos($filerecord->filepath, '/') !== strlen($filerecord->filepath)-1) {
                        throw new file_exception('storedfileproblem', 'Invalid file path');
        }

        $filerecord->filename = clean_param($filerecord->filename, PARAM_FILE);
        if ($filerecord->filename === '') {
                        throw new file_exception('storedfileproblem', 'Invalid file name');
        }

        $now = time();
        if (isset($filerecord->timecreated)) {
            if (!is_number($filerecord->timecreated)) {
                throw new file_exception('storedfileproblem', 'Invalid file timecreated');
            }
            if ($filerecord->timecreated < 0) {
                                $filerecord->timecreated = 0;
            }
        } else {
            $filerecord->timecreated = $now;
        }

        if (isset($filerecord->timemodified)) {
            if (!is_number($filerecord->timemodified)) {
                throw new file_exception('storedfileproblem', 'Invalid file timemodified');
            }
            if ($filerecord->timemodified < 0) {
                                $filerecord->timemodified = 0;
            }
        } else {
            $filerecord->timemodified = $now;
        }

        $newrecord = new stdClass();

        $newrecord->contextid = $filerecord->contextid;
        $newrecord->component = $filerecord->component;
        $newrecord->filearea  = $filerecord->filearea;
        $newrecord->itemid    = $filerecord->itemid;
        $newrecord->filepath  = $filerecord->filepath;
        $newrecord->filename  = $filerecord->filename;

        $newrecord->timecreated  = $filerecord->timecreated;
        $newrecord->timemodified = $filerecord->timemodified;
        $newrecord->mimetype     = empty($filerecord->mimetype) ? $this->mimetype($pathname, $filerecord->filename) : $filerecord->mimetype;
        $newrecord->userid       = empty($filerecord->userid) ? null : $filerecord->userid;
        $newrecord->source       = empty($filerecord->source) ? null : $filerecord->source;
        $newrecord->author       = empty($filerecord->author) ? null : $filerecord->author;
        $newrecord->license      = empty($filerecord->license) ? null : $filerecord->license;
        $newrecord->status       = empty($filerecord->status) ? 0 : $filerecord->status;
        $newrecord->sortorder    = $filerecord->sortorder;

        list($newrecord->contenthash, $newrecord->filesize, $newfile) = $this->add_file_to_pool($pathname);

        $newrecord->pathnamehash = $this->get_pathname_hash($newrecord->contextid, $newrecord->component, $newrecord->filearea, $newrecord->itemid, $newrecord->filepath, $newrecord->filename);

        try {
            $newrecord->id = $DB->insert_record('files', $newrecord);
        } catch (dml_exception $e) {
            if ($newfile) {
                $this->deleted_file_cleanup($newrecord->contenthash);
            }
            throw new stored_file_creation_exception($newrecord->contextid, $newrecord->component, $newrecord->filearea, $newrecord->itemid,
                                                    $newrecord->filepath, $newrecord->filename, $e->debuginfo);
        }

        $this->create_directory($newrecord->contextid, $newrecord->component, $newrecord->filearea, $newrecord->itemid, $newrecord->filepath, $newrecord->userid);

        return $this->get_file_instance($newrecord);
    }

    
    public function create_file_from_string($filerecord, $content) {
        global $DB;

        $filerecord = (array)$filerecord;          $filerecord = (object)$filerecord; 
                if (!is_number($filerecord->contextid) or $filerecord->contextid < 1) {
            throw new file_exception('storedfileproblem', 'Invalid contextid');
        }

        $filerecord->component = clean_param($filerecord->component, PARAM_COMPONENT);
        if (empty($filerecord->component)) {
            throw new file_exception('storedfileproblem', 'Invalid component');
        }

        $filerecord->filearea = clean_param($filerecord->filearea, PARAM_AREA);
        if (empty($filerecord->filearea)) {
            throw new file_exception('storedfileproblem', 'Invalid filearea');
        }

        if (!is_number($filerecord->itemid) or $filerecord->itemid < 0) {
            throw new file_exception('storedfileproblem', 'Invalid itemid');
        }

        if (!empty($filerecord->sortorder)) {
            if (!is_number($filerecord->sortorder) or $filerecord->sortorder < 0) {
                $filerecord->sortorder = 0;
            }
        } else {
            $filerecord->sortorder = 0;
        }

        $filerecord->filepath = clean_param($filerecord->filepath, PARAM_PATH);
        if (strpos($filerecord->filepath, '/') !== 0 or strrpos($filerecord->filepath, '/') !== strlen($filerecord->filepath)-1) {
                        throw new file_exception('storedfileproblem', 'Invalid file path');
        }

        $filerecord->filename = clean_param($filerecord->filename, PARAM_FILE);
        if ($filerecord->filename === '') {
                        throw new file_exception('storedfileproblem', 'Invalid file name');
        }

        $now = time();
        if (isset($filerecord->timecreated)) {
            if (!is_number($filerecord->timecreated)) {
                throw new file_exception('storedfileproblem', 'Invalid file timecreated');
            }
            if ($filerecord->timecreated < 0) {
                                $filerecord->timecreated = 0;
            }
        } else {
            $filerecord->timecreated = $now;
        }

        if (isset($filerecord->timemodified)) {
            if (!is_number($filerecord->timemodified)) {
                throw new file_exception('storedfileproblem', 'Invalid file timemodified');
            }
            if ($filerecord->timemodified < 0) {
                                $filerecord->timemodified = 0;
            }
        } else {
            $filerecord->timemodified = $now;
        }

        $newrecord = new stdClass();

        $newrecord->contextid = $filerecord->contextid;
        $newrecord->component = $filerecord->component;
        $newrecord->filearea  = $filerecord->filearea;
        $newrecord->itemid    = $filerecord->itemid;
        $newrecord->filepath  = $filerecord->filepath;
        $newrecord->filename  = $filerecord->filename;

        $newrecord->timecreated  = $filerecord->timecreated;
        $newrecord->timemodified = $filerecord->timemodified;
        $newrecord->userid       = empty($filerecord->userid) ? null : $filerecord->userid;
        $newrecord->source       = empty($filerecord->source) ? null : $filerecord->source;
        $newrecord->author       = empty($filerecord->author) ? null : $filerecord->author;
        $newrecord->license      = empty($filerecord->license) ? null : $filerecord->license;
        $newrecord->status       = empty($filerecord->status) ? 0 : $filerecord->status;
        $newrecord->sortorder    = $filerecord->sortorder;

        list($newrecord->contenthash, $newrecord->filesize, $newfile) = $this->add_string_to_pool($content);
        $filepathname = $this->path_from_hash($newrecord->contenthash) . '/' . $newrecord->contenthash;
                $newrecord->mimetype = empty($filerecord->mimetype) ? $this->mimetype($filepathname, $filerecord->filename) : $filerecord->mimetype;

        $newrecord->pathnamehash = $this->get_pathname_hash($newrecord->contextid, $newrecord->component, $newrecord->filearea, $newrecord->itemid, $newrecord->filepath, $newrecord->filename);

        try {
            $newrecord->id = $DB->insert_record('files', $newrecord);
        } catch (dml_exception $e) {
            if ($newfile) {
                $this->deleted_file_cleanup($newrecord->contenthash);
            }
            throw new stored_file_creation_exception($newrecord->contextid, $newrecord->component, $newrecord->filearea, $newrecord->itemid,
                                                    $newrecord->filepath, $newrecord->filename, $e->debuginfo);
        }

        $this->create_directory($newrecord->contextid, $newrecord->component, $newrecord->filearea, $newrecord->itemid, $newrecord->filepath, $newrecord->userid);

        return $this->get_file_instance($newrecord);
    }

    
    public function create_file_from_reference($filerecord, $repositoryid, $reference, $options = array()) {
        global $DB;

        $filerecord = (array)$filerecord;          $filerecord = (object)$filerecord; 
                if (!is_number($filerecord->contextid) or $filerecord->contextid < 1) {
            throw new file_exception('storedfileproblem', 'Invalid contextid');
        }

        $filerecord->component = clean_param($filerecord->component, PARAM_COMPONENT);
        if (empty($filerecord->component)) {
            throw new file_exception('storedfileproblem', 'Invalid component');
        }

        $filerecord->filearea = clean_param($filerecord->filearea, PARAM_AREA);
        if (empty($filerecord->filearea)) {
            throw new file_exception('storedfileproblem', 'Invalid filearea');
        }

        if (!is_number($filerecord->itemid) or $filerecord->itemid < 0) {
            throw new file_exception('storedfileproblem', 'Invalid itemid');
        }

        if (!empty($filerecord->sortorder)) {
            if (!is_number($filerecord->sortorder) or $filerecord->sortorder < 0) {
                $filerecord->sortorder = 0;
            }
        } else {
            $filerecord->sortorder = 0;
        }

        $filerecord->mimetype          = empty($filerecord->mimetype) ? $this->mimetype($filerecord->filename) : $filerecord->mimetype;
        $filerecord->userid            = empty($filerecord->userid) ? null : $filerecord->userid;
        $filerecord->source            = empty($filerecord->source) ? null : $filerecord->source;
        $filerecord->author            = empty($filerecord->author) ? null : $filerecord->author;
        $filerecord->license           = empty($filerecord->license) ? null : $filerecord->license;
        $filerecord->status            = empty($filerecord->status) ? 0 : $filerecord->status;
        $filerecord->filepath          = clean_param($filerecord->filepath, PARAM_PATH);
        if (strpos($filerecord->filepath, '/') !== 0 or strrpos($filerecord->filepath, '/') !== strlen($filerecord->filepath)-1) {
                        throw new file_exception('storedfileproblem', 'Invalid file path');
        }

        $filerecord->filename = clean_param($filerecord->filename, PARAM_FILE);
        if ($filerecord->filename === '') {
                        throw new file_exception('storedfileproblem', 'Invalid file name');
        }

        $now = time();
        if (isset($filerecord->timecreated)) {
            if (!is_number($filerecord->timecreated)) {
                throw new file_exception('storedfileproblem', 'Invalid file timecreated');
            }
            if ($filerecord->timecreated < 0) {
                                $filerecord->timecreated = 0;
            }
        } else {
            $filerecord->timecreated = $now;
        }

        if (isset($filerecord->timemodified)) {
            if (!is_number($filerecord->timemodified)) {
                throw new file_exception('storedfileproblem', 'Invalid file timemodified');
            }
            if ($filerecord->timemodified < 0) {
                                $filerecord->timemodified = 0;
            }
        } else {
            $filerecord->timemodified = $now;
        }

        $transaction = $DB->start_delegated_transaction();

        try {
            $filerecord->referencefileid = $this->get_or_create_referencefileid($repositoryid, $reference);
        } catch (Exception $e) {
            throw new file_reference_exception($repositoryid, $reference, null, null, $e->getMessage());
        }

        if (isset($filerecord->contenthash) && $this->content_exists($filerecord->contenthash)) {
                        if (empty($filerecord->filesize)) {
                $filepathname = $this->path_from_hash($filerecord->contenthash) . '/' . $filerecord->contenthash;
                $filerecord->filesize = filesize($filepathname);
            } else {
                $filerecord->filesize = clean_param($filerecord->filesize, PARAM_INT);
            }
        } else {
                        $lastcontent = $DB->get_record('files', array('referencefileid' => $filerecord->referencefileid),
                    'id, contenthash, filesize', IGNORE_MULTIPLE);
            if ($lastcontent) {
                $filerecord->contenthash = $lastcontent->contenthash;
                $filerecord->filesize = $lastcontent->filesize;
            } else {
                                                list($filerecord->contenthash, $filerecord->filesize, $newfile) = $this->add_string_to_pool(null);
            }
        }

        $filerecord->pathnamehash = $this->get_pathname_hash($filerecord->contextid, $filerecord->component, $filerecord->filearea, $filerecord->itemid, $filerecord->filepath, $filerecord->filename);

        try {
            $filerecord->id = $DB->insert_record('files', $filerecord);
        } catch (dml_exception $e) {
            if (!empty($newfile)) {
                $this->deleted_file_cleanup($filerecord->contenthash);
            }
            throw new stored_file_creation_exception($filerecord->contextid, $filerecord->component, $filerecord->filearea, $filerecord->itemid,
                                                    $filerecord->filepath, $filerecord->filename, $e->debuginfo);
        }

        $this->create_directory($filerecord->contextid, $filerecord->component, $filerecord->filearea, $filerecord->itemid, $filerecord->filepath, $filerecord->userid);

        $transaction->allow_commit();

                return $this->get_file_by_id($filerecord->id);
    }

    
    public function convert_image($filerecord, $fid, $newwidth = null, $newheight = null, $keepaspectratio = true, $quality = null) {
        if (!function_exists('imagecreatefromstring')) {
                                    throw new file_exception('storedfileproblem', 'imagecreatefromstring() doesnt exist. The PHP extension "GD" must be installed for image conversion.');
        }

        if ($fid instanceof stored_file) {
            $fid = $fid->get_id();
        }

        $filerecord = (array)$filerecord; 
        if (!$file = $this->get_file_by_id($fid)) {             throw new file_exception('storedfileproblem', 'File does not exist');
        }

        if (!$imageinfo = $file->get_imageinfo()) {
            throw new file_exception('storedfileproblem', 'File is not an image');
        }

        if (!isset($filerecord['filename'])) {
            $filerecord['filename'] = $file->get_filename();
        }

        if (!isset($filerecord['mimetype'])) {
            $filerecord['mimetype'] = $imageinfo['mimetype'];
        }

        $width    = $imageinfo['width'];
        $height   = $imageinfo['height'];

        if ($keepaspectratio) {
            if (0 >= $newwidth and 0 >= $newheight) {
                                $newwidth  = $width;
                $newheight = $height;

            } else if (0 < $newwidth and 0 < $newheight) {
                $xheight = ($newwidth*($height/$width));
                if ($xheight < $newheight) {
                    $newheight = (int)$xheight;
                } else {
                    $newwidth = (int)($newheight*($width/$height));
                }

            } else if (0 < $newwidth) {
                $newheight = (int)($newwidth*($height/$width));

            } else {                 $newwidth = (int)($newheight*($width/$height));
            }

        } else {
            if (0 >= $newwidth) {
                $newwidth = $width;
            }
            if (0 >= $newheight) {
                $newheight = $height;
            }
        }

                $img = imagecreatefromstring($file->get_content());

                $newimg = imagecreatetruecolor($newwidth, $newheight);

                $hasalpha = $filerecord['mimetype'] == 'image/png' || $filerecord['mimetype'] == 'image/gif';

                if ($hasalpha) {
            imagealphablending($newimg, true);

                        $colour = imagecolortransparent($img);
            if ($colour == -1) {
                                $colour = imagecolorallocatealpha($newimg, 255, 255, 255, 127);
                                imagesavealpha($newimg, true);
            }
            imagecolortransparent($newimg, $colour);
            imagefill($newimg, 0, 0, $colour);
        }

                if ($height != $newheight or $width != $newwidth) {
                        if (!imagecopyresampled($newimg, $img, 0, 0, 0, 0, $newwidth, $newheight, $width, $height)) {
                                throw new file_exception('storedfileproblem', 'Can not resize image');
            }
            imagedestroy($img);
            $img = $newimg;

        } else if ($hasalpha) {
                        if (!imagecopy($newimg, $img, 0, 0, 0, 0, $width, $height)) {
                                throw new file_exception('storedfileproblem', 'Can not copy image');
            }
            imagedestroy($img);
            $img = $newimg;

        } else {
                        imagedestroy($newimg);
        }

        ob_start();
        switch ($filerecord['mimetype']) {
            case 'image/gif':
                imagegif($img);
                break;

            case 'image/jpeg':
                if (is_null($quality)) {
                    imagejpeg($img);
                } else {
                    imagejpeg($img, NULL, $quality);
                }
                break;

            case 'image/png':
                $quality = (int)$quality;

                                                                $quality = $quality > 9 ? (int)(max(1.0, (float)$quality / 100.0) * 9.0) : $quality;
                imagepng($img, NULL, $quality, NULL);
                break;

            default:
                throw new file_exception('storedfileproblem', 'Unsupported mime type');
        }

        $content = ob_get_contents();
        ob_end_clean();
        imagedestroy($img);

        if (!$content) {
            throw new file_exception('storedfileproblem', 'Can not convert image');
        }

        return $this->create_file_from_string($filerecord, $content);
    }

    
    public function add_file_to_pool($pathname, $contenthash = NULL) {
        global $CFG;

        if (!is_readable($pathname)) {
            throw new file_exception('storedfilecannotread', '', $pathname);
        }

        $filesize = filesize($pathname);
        if ($filesize === false) {
            throw new file_exception('storedfilecannotread', '', $pathname);
        }

        if (is_null($contenthash)) {
            $contenthash = sha1_file($pathname);
        } else if ($CFG->debugdeveloper) {
            $filehash = sha1_file($pathname);
            if ($filehash === false) {
                throw new file_exception('storedfilecannotread', '', $pathname);
            }
            if ($filehash !== $contenthash) {
                                debugging("Invalid contenthash submitted for file $pathname", DEBUG_DEVELOPER);
                $contenthash = $filehash;
            }
        }
        if ($contenthash === false) {
            throw new file_exception('storedfilecannotread', '', $pathname);
        }

        if ($filesize > 0 and $contenthash === sha1('')) {
                        clearstatcache();
            $contenthash = sha1_file($pathname);
            $filesize = filesize($pathname);

            if ($contenthash === false or $filesize === false) {
                throw new file_exception('storedfilecannotread', '', $pathname);
            }
            if ($filesize > 0 and $contenthash === sha1('')) {
                                throw new file_exception('storedfilecannotread', '', $pathname);
            }
        }

        $hashpath = $this->path_from_hash($contenthash);
        $hashfile = "$hashpath/$contenthash";

        $newfile = true;

        if (file_exists($hashfile)) {
            if (filesize($hashfile) === $filesize) {
                return array($contenthash, $filesize, false);
            }
            if (sha1_file($hashfile) === $contenthash) {
                                mkdir("$this->filedir/jackpot/", $this->dirpermissions, true);
                copy($pathname, "$this->filedir/jackpot/{$contenthash}_1");
                copy($hashfile, "$this->filedir/jackpot/{$contenthash}_2");
                throw new file_pool_content_exception($contenthash);
            }
            debugging("Replacing invalid content file $contenthash");
            unlink($hashfile);
            $newfile = false;
        }

        if (!is_dir($hashpath)) {
            if (!mkdir($hashpath, $this->dirpermissions, true)) {
                                throw new file_exception('storedfilecannotcreatefiledirs');
            }
        }

        
        $prev = ignore_user_abort(true);
        @unlink($hashfile.'.tmp');
        if (!copy($pathname, $hashfile.'.tmp')) {
                        ignore_user_abort($prev);
            throw new file_exception('storedfilecannotcreatefile');
        }
        if (filesize($hashfile.'.tmp') !== $filesize) {
                        unlink($hashfile.'.tmp');
            ignore_user_abort($prev);
            throw new file_exception('storedfilecannotcreatefile');
        }
        rename($hashfile.'.tmp', $hashfile);
        chmod($hashfile, $this->filepermissions);         @unlink($hashfile.'.tmp');         ignore_user_abort($prev);

        return array($contenthash, $filesize, $newfile);
    }

    
    public function add_string_to_pool($content) {
        global $CFG;

        $contenthash = sha1($content);
        $filesize = strlen($content); 
        $hashpath = $this->path_from_hash($contenthash);
        $hashfile = "$hashpath/$contenthash";

        $newfile = true;

        if (file_exists($hashfile)) {
            if (filesize($hashfile) === $filesize) {
                return array($contenthash, $filesize, false);
            }
            if (sha1_file($hashfile) === $contenthash) {
                                mkdir("$this->filedir/jackpot/", $this->dirpermissions, true);
                copy($hashfile, "$this->filedir/jackpot/{$contenthash}_1");
                file_put_contents("$this->filedir/jackpot/{$contenthash}_2", $content);
                throw new file_pool_content_exception($contenthash);
            }
            debugging("Replacing invalid content file $contenthash");
            unlink($hashfile);
            $newfile = false;
        }

        if (!is_dir($hashpath)) {
            if (!mkdir($hashpath, $this->dirpermissions, true)) {
                                throw new file_exception('storedfilecannotcreatefiledirs');
            }
        }

        
        $prev = ignore_user_abort(true);

        if (!empty($CFG->preventfilelocking)) {
            $newsize = file_put_contents($hashfile.'.tmp', $content);
        } else {
            $newsize = file_put_contents($hashfile.'.tmp', $content, LOCK_EX);
        }

        if ($newsize === false) {
                        ignore_user_abort($prev);
            throw new file_exception('storedfilecannotcreatefile');
        }
        if (filesize($hashfile.'.tmp') !== $filesize) {
                        unlink($hashfile.'.tmp');
            ignore_user_abort($prev);
            throw new file_exception('storedfilecannotcreatefile');
        }
        rename($hashfile.'.tmp', $hashfile);
        chmod($hashfile, $this->filepermissions);         @unlink($hashfile.'.tmp');         ignore_user_abort($prev);

        return array($contenthash, $filesize, $newfile);
    }

    
    public function xsendfile($contenthash) {
        global $CFG;
        require_once("$CFG->libdir/xsendfilelib.php");

        $hashpath = $this->path_from_hash($contenthash);
        return xsendfile("$hashpath/$contenthash");
    }

    
    public function content_exists($contenthash) {
        $dir = $this->path_from_hash($contenthash);
        $filepath = $dir . '/' . $contenthash;
        return file_exists($filepath);
    }

    
    protected function path_from_hash($contenthash) {
        $l1 = $contenthash[0].$contenthash[1];
        $l2 = $contenthash[2].$contenthash[3];
        return "$this->filedir/$l1/$l2";
    }

    
    protected function trash_path_from_hash($contenthash) {
        $l1 = $contenthash[0].$contenthash[1];
        $l2 = $contenthash[2].$contenthash[3];
        return "$this->trashdir/$l1/$l2";
    }

    
    public function try_content_recovery($file) {
        $contenthash = $file->get_contenthash();
        $trashfile = $this->trash_path_from_hash($contenthash).'/'.$contenthash;
        if (!is_readable($trashfile)) {
            if (!is_readable($this->trashdir.'/'.$contenthash)) {
                return false;
            }
                        $trashfile = $this->trashdir.'/'.$contenthash;
        }
        if (filesize($trashfile) != $file->get_filesize() or sha1_file($trashfile) != $contenthash) {
                        return false;
        }
        $contentdir  = $this->path_from_hash($contenthash);
        $contentfile = $contentdir.'/'.$contenthash;
        if (file_exists($contentfile)) {
                        return true;
        }
        if (!is_dir($contentdir)) {
            if (!mkdir($contentdir, $this->dirpermissions, true)) {
                return false;
            }
        }
        return rename($trashfile, $contentfile);
    }

    
    public function deleted_file_cleanup($contenthash) {
        global $DB;

        if ($contenthash === sha1('')) {
                        return;
        }

                        if ($DB->record_exists('files', array('contenthash'=>$contenthash))) {
                        return;
        }
                $contentfile = $this->path_from_hash($contenthash).'/'.$contenthash;
        if (!file_exists($contentfile)) {
                        return;
        }
        $trashpath = $this->trash_path_from_hash($contenthash);
        $trashfile = $trashpath.'/'.$contenthash;
        if (file_exists($trashfile)) {
                        unlink($contentfile);
            return;
        }
        if (!is_dir($trashpath)) {
            mkdir($trashpath, $this->dirpermissions, true);
        }
        rename($contentfile, $trashfile);
        chmod($trashfile, $this->filepermissions);     }

    
    public static function pack_reference($params) {
        $params = (array)$params;
        $reference = array();
        $reference['contextid'] = is_null($params['contextid']) ? null : clean_param($params['contextid'], PARAM_INT);
        $reference['component'] = is_null($params['component']) ? null : clean_param($params['component'], PARAM_COMPONENT);
        $reference['itemid']    = is_null($params['itemid'])    ? null : clean_param($params['itemid'],    PARAM_INT);
        $reference['filearea']  = is_null($params['filearea'])  ? null : clean_param($params['filearea'],  PARAM_AREA);
        $reference['filepath']  = is_null($params['filepath'])  ? null : clean_param($params['filepath'],  PARAM_PATH);
        $reference['filename']  = is_null($params['filename'])  ? null : clean_param($params['filename'],  PARAM_FILE);
        return base64_encode(serialize($reference));
    }

    
    public static function unpack_reference($str, $cleanparams = false) {
        $decoded = base64_decode($str, true);
        if ($decoded === false) {
            throw new file_reference_exception(null, $str, null, null, 'Invalid base64 format');
        }
        $params = @unserialize($decoded);         if ($params === false) {
            throw new file_reference_exception(null, $decoded, null, null, 'Not an unserializeable value');
        }
        if (is_array($params) && $cleanparams) {
            $params = array(
                'component' => is_null($params['component']) ? ''   : clean_param($params['component'], PARAM_COMPONENT),
                'filearea'  => is_null($params['filearea'])  ? ''   : clean_param($params['filearea'], PARAM_AREA),
                'itemid'    => is_null($params['itemid'])    ? 0    : clean_param($params['itemid'], PARAM_INT),
                'filename'  => is_null($params['filename'])  ? null : clean_param($params['filename'], PARAM_FILE),
                'filepath'  => is_null($params['filepath'])  ? null : clean_param($params['filepath'], PARAM_PATH),
                'contextid' => is_null($params['contextid']) ? null : clean_param($params['contextid'], PARAM_INT)
            );
        }
        return $params;
    }

    
    public function search_server_files($query, $from = 0, $limit = 20, $count = false) {
        global $DB;
        $params = array(
            'contextlevel' => CONTEXT_USER,
            'directory' => '.',
            'query' => $query
        );

        if ($count) {
            $select = 'COUNT(1)';
        } else {
            $select = self::instance_sql_fields('f', 'r');
        }
        $like = $DB->sql_like('f.filename', ':query', false);

        $sql = "SELECT $select
                  FROM {files} f
             LEFT JOIN {files_reference} r
                    ON f.referencefileid = r.id
                  JOIN {context} c
                    ON f.contextid = c.id
                 WHERE c.contextlevel <> :contextlevel
                   AND f.filename <> :directory
                   AND " . $like . "";

        if ($count) {
            return $DB->count_records_sql($sql, $params);
        }

        $sql .= " ORDER BY f.filename";

        $result = array();
        $filerecords = $DB->get_recordset_sql($sql, $params, $from, $limit);
        foreach ($filerecords as $filerecord) {
            $result[$filerecord->pathnamehash] = $this->get_file_instance($filerecord);
        }
        $filerecords->close();

        return $result;
    }

    
    public function search_references($reference) {
        global $DB;

        if (is_null($reference)) {
            throw new coding_exception('NULL is not a valid reference to an external file');
        }

                        self::unpack_reference($reference);

        $referencehash = sha1($reference);

        $sql = "SELECT ".self::instance_sql_fields('f', 'r')."
                  FROM {files} f
                  JOIN {files_reference} r ON f.referencefileid = r.id
                  JOIN {repository_instances} ri ON r.repositoryid = ri.id
                 WHERE r.referencehash = ?
                       AND (f.component <> ? OR f.filearea <> ?)";

        $rs = $DB->get_recordset_sql($sql, array($referencehash, 'user', 'draft'));
        $files = array();
        foreach ($rs as $filerecord) {
            $files[$filerecord->pathnamehash] = $this->get_file_instance($filerecord);
        }

        return $files;
    }

    
    public function search_references_count($reference) {
        global $DB;

        if (is_null($reference)) {
            throw new coding_exception('NULL is not a valid reference to an external file');
        }

                        self::unpack_reference($reference);

        $referencehash = sha1($reference);

        $sql = "SELECT COUNT(f.id)
                  FROM {files} f
                  JOIN {files_reference} r ON f.referencefileid = r.id
                  JOIN {repository_instances} ri ON r.repositoryid = ri.id
                 WHERE r.referencehash = ?
                       AND (f.component <> ? OR f.filearea <> ?)";

        return (int)$DB->count_records_sql($sql, array($referencehash, 'user', 'draft'));
    }

    
    public function get_references_by_storedfile(stored_file $storedfile) {
        global $DB;

        $params = array();
        $params['contextid'] = $storedfile->get_contextid();
        $params['component'] = $storedfile->get_component();
        $params['filearea']  = $storedfile->get_filearea();
        $params['itemid']    = $storedfile->get_itemid();
        $params['filename']  = $storedfile->get_filename();
        $params['filepath']  = $storedfile->get_filepath();

        return $this->search_references(self::pack_reference($params));
    }

    
    public function get_references_count_by_storedfile(stored_file $storedfile) {
        global $DB;

        $params = array();
        $params['contextid'] = $storedfile->get_contextid();
        $params['component'] = $storedfile->get_component();
        $params['filearea']  = $storedfile->get_filearea();
        $params['itemid']    = $storedfile->get_itemid();
        $params['filename']  = $storedfile->get_filename();
        $params['filepath']  = $storedfile->get_filepath();

        return $this->search_references_count(self::pack_reference($params));
    }

    
    public function update_references_to_storedfile(stored_file $storedfile) {
        global $CFG, $DB;
        $params = array();
        $params['contextid'] = $storedfile->get_contextid();
        $params['component'] = $storedfile->get_component();
        $params['filearea']  = $storedfile->get_filearea();
        $params['itemid']    = $storedfile->get_itemid();
        $params['filename']  = $storedfile->get_filename();
        $params['filepath']  = $storedfile->get_filepath();
        $reference = self::pack_reference($params);
        $referencehash = sha1($reference);

        $sql = "SELECT repositoryid, id FROM {files_reference}
                 WHERE referencehash = ?";
        $rs = $DB->get_recordset_sql($sql, array($referencehash));

        $now = time();
        foreach ($rs as $record) {
            $this->update_references($record->id, $now, null,
                    $storedfile->get_contenthash(), $storedfile->get_filesize(), 0, $storedfile->get_timemodified());
        }
        $rs->close();
    }

    
    public function import_external_file(stored_file $storedfile, $maxbytes = 0) {
        global $CFG;
        $storedfile->import_external_file_contents($maxbytes);
        $storedfile->delete_reference();
        return $storedfile;
    }

    
    public static function mimetype($pathname, $filename = null) {
        if (empty($filename)) {
            $filename = $pathname;
        }
        $type = mimeinfo('type', $filename);
        if ($type === 'document/unknown' && class_exists('finfo') && file_exists($pathname)) {
            $finfo = new finfo(FILEINFO_MIME_TYPE);
            $type = mimeinfo_from_type('type', $finfo->file($pathname));
        }
        return $type;
    }

    
    public function cron() {
        global $CFG, $DB;
        require_once($CFG->libdir.'/cronlib.php');

                        mtrace('Deleting old draft files... ', '');
        cron_trace_time_and_memory();
        $old = time() - 60*60*24*4;
        $sql = "SELECT *
                  FROM {files}
                 WHERE component = 'user' AND filearea = 'draft' AND filepath = '/' AND filename = '.'
                       AND timecreated < :old";
        $rs = $DB->get_recordset_sql($sql, array('old'=>$old));
        foreach ($rs as $dir) {
            $this->delete_area_files($dir->contextid, $dir->component, $dir->filearea, $dir->itemid);
        }
        $rs->close();
        mtrace('done.');

                        mtrace('Deleting orphaned preview files... ', '');
        cron_trace_time_and_memory();
        $sql = "SELECT p.*
                  FROM {files} p
             LEFT JOIN {files} o ON (p.filename = o.contenthash)
                 WHERE p.contextid = ? AND p.component = 'core' AND p.filearea = 'preview' AND p.itemid = 0
                       AND o.id IS NULL";
        $syscontext = context_system::instance();
        $rs = $DB->get_recordset_sql($sql, array($syscontext->id));
        foreach ($rs as $orphan) {
            $file = $this->get_file_instance($orphan);
            if (!$file->is_directory()) {
                $file->delete();
            }
        }
        $rs->close();
        mtrace('done.');

                        mtrace('Deleting orphaned document conversion files... ', '');
        cron_trace_time_and_memory();
        $sql = "SELECT p.*
                  FROM {files} p
             LEFT JOIN {files} o ON (p.filename = o.contenthash)
                 WHERE p.contextid = ? AND p.component = 'core' AND p.filearea = 'documentconversion' AND p.itemid = 0
                       AND o.id IS NULL";
        $syscontext = context_system::instance();
        $rs = $DB->get_recordset_sql($sql, array($syscontext->id));
        foreach ($rs as $orphan) {
            $file = $this->get_file_instance($orphan);
            if (!$file->is_directory()) {
                $file->delete();
            }
        }
        $rs->close();
        mtrace('done.');

                        if (empty($CFG->fileslastcleanup) or $CFG->fileslastcleanup < time() - 60*60*24) {
            require_once($CFG->libdir.'/filelib.php');
                        mtrace('Cleaning up files from deleted contexts... ', '');
            cron_trace_time_and_memory();
            $sql = "SELECT DISTINCT f.contextid
                    FROM {files} f
                    LEFT OUTER JOIN {context} c ON f.contextid = c.id
                    WHERE c.id IS NULL";
            $rs = $DB->get_recordset_sql($sql);
            if ($rs->valid()) {
                $fs = get_file_storage();
                foreach ($rs as $ctx) {
                    $fs->delete_area_files($ctx->contextid);
                }
            }
            $rs->close();
            mtrace('done.');

            mtrace('Deleting trash files... ', '');
            cron_trace_time_and_memory();
            fulldelete($this->trashdir);
            set_config('fileslastcleanup', time());
            mtrace('done.');
        }
    }

    
    private static function instance_sql_fields($filesprefix, $filesreferenceprefix) {
                        $filefields = array('contenthash', 'pathnamehash', 'contextid', 'component', 'filearea',
            'itemid', 'filepath', 'filename', 'userid', 'filesize', 'mimetype', 'status', 'source',
            'author', 'license', 'timecreated', 'timemodified', 'sortorder', 'referencefileid');

        $referencefields = array('repositoryid' => 'repositoryid',
            'reference' => 'reference',
            'lastsync' => 'referencelastsync');

                $fields = array();
        $fields[] = $filesprefix.'.id AS id';
        foreach ($filefields as $field) {
            $fields[] = "{$filesprefix}.{$field}";
        }

        foreach ($referencefields as $field => $alias) {
            $fields[] = "{$filesreferenceprefix}.{$field} AS {$alias}";
        }

        return implode(', ', $fields);
    }

    
    private function get_or_create_referencefileid($repositoryid, $reference, $lastsync = null, $lifetime = null) {
        global $DB;

        $id = $this->get_referencefileid($repositoryid, $reference, IGNORE_MISSING);

        if ($id !== false) {
                        return $id;
        }

                try {
            $id = $DB->insert_record('files_reference', array(
                'repositoryid'  => $repositoryid,
                'reference'     => $reference,
                'referencehash' => sha1($reference),
                'lastsync'      => $lastsync));
        } catch (dml_exception $e) {
                                                $id = $this->get_referencefileid($repositoryid, $reference, MUST_EXIST);
        }

        return $id;
    }

    
    private function get_referencefileid($repositoryid, $reference, $strictness) {
        global $DB;

        return $DB->get_field('files_reference', 'id',
            array('repositoryid' => $repositoryid, 'referencehash' => sha1($reference)), $strictness);
    }

    
    public function update_references($referencefileid, $lastsync, $lifetime, $contenthash, $filesize, $status, $timemodified = null) {
        global $DB;
        $referencefileid = clean_param($referencefileid, PARAM_INT);
        $lastsync = clean_param($lastsync, PARAM_INT);
        validate_param($contenthash, PARAM_TEXT, NULL_NOT_ALLOWED);
        $filesize = clean_param($filesize, PARAM_INT);
        $status = clean_param($status, PARAM_INT);
        $params = array('contenthash' => $contenthash,
                    'filesize' => $filesize,
                    'status' => $status,
                    'referencefileid' => $referencefileid,
                    'timemodified' => $timemodified);
        $DB->execute('UPDATE {files} SET contenthash = :contenthash, filesize = :filesize,
            status = :status ' . ($timemodified ? ', timemodified = :timemodified' : '') . '
            WHERE referencefileid = :referencefileid', $params);
        $data = array('id' => $referencefileid, 'lastsync' => $lastsync);
        $DB->update_record('files_reference', (object)$data);
    }
}
