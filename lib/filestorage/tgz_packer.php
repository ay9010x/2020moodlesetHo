<?php



defined('MOODLE_INTERNAL') || die();

require_once("$CFG->libdir/filestorage/file_packer.php");
require_once("$CFG->libdir/filestorage/tgz_extractor.php");


class tgz_packer extends file_packer {
    
    const DEFAULT_TIMESTAMP = 1356998400;

    
    const ARCHIVE_INDEX_FILE = '.ARCHIVE_INDEX';

    
    const ARCHIVE_INDEX_COUNT_PREFIX = 'Moodle archive file index. Count: ';

    
    protected $includeindex = true;

    
    const PROGRESS_MAX = 1000000;

    
    const TAR_BLOCK_SIZE = 512;

    
    public function archive_to_storage(array $files, $contextid,
            $component, $filearea, $itemid, $filepath, $filename,
            $userid = null, $ignoreinvalidfiles = true, file_progress $progress = null) {
        global $CFG;

                $tempfolder = $CFG->tempdir . '/core_files';
        check_dir_exists($tempfolder);
        $tempfile = tempnam($tempfolder, '.tgz');

                if ($result = $this->archive_to_pathname($files, $tempfile, $ignoreinvalidfiles, $progress)) {
                        $fs = get_file_storage();
            if ($existing = $fs->get_file($contextid, $component, $filearea, $itemid, $filepath, $filename)) {
                $existing->delete();
            }
            $filerecord = array('contextid' => $contextid, 'component' => $component,
                    'filearea' => $filearea, 'itemid' => $itemid, 'filepath' => $filepath,
                    'filename' => $filename, 'userid' => $userid, 'mimetype' => 'application/x-tgz');
            self::delete_existing_file_record($fs, $filerecord);
            $result = $fs->create_file_from_pathname($filerecord, $tempfile);
        }

                @unlink($tempfile);
        return $result;
    }

    
    public static function delete_existing_file_record(file_storage $fs, array $filerecord) {
        if ($existing = $fs->get_file($filerecord['contextid'], $filerecord['component'],
                $filerecord['filearea'], $filerecord['itemid'], $filerecord['filepath'],
                $filerecord['filename'])) {
            $existing->delete();
        }
    }

    
    public function set_include_index($includeindex) {
        $this->includeindex = $includeindex;
    }

    
    public function archive_to_pathname(array $files, $archivefile,
            $ignoreinvalidfiles=true, file_progress $progress = null) {
                if (!($gz = gzopen($archivefile, 'wb'))) {
            return false;
        }
        try {
                                                                                    if ($files) {
                $progressperfile = (int)(self::PROGRESS_MAX / (count($files) * 10));
            } else {
                                $progressperfile = 1;
            }
            $done = 0;

                        $expandedfiles = array();
            foreach ($files as $archivepath => $file) {
                                if ($progress) {
                    $progress->progress($done, self::PROGRESS_MAX);
                }
                $done += $progressperfile;

                if (is_null($file)) {
                                        if (!preg_match('~/$~', $archivepath)) {
                        $archivepath .= '/';
                    }
                    $expandedfiles[$archivepath] = null;
                } else if (is_string($file)) {
                                        if (!$this->list_files_path($expandedfiles, $archivepath, $file,
                            $progress, $done)) {
                        gzclose($gz);
                        unlink($archivefile);
                        return false;
                    }
                } else if (is_array($file)) {
                                        $expandedfiles[$archivepath] = $file;
                } else {
                                        $this->list_files_stored($expandedfiles, $archivepath, $file);
                }
            }

                                                $list = self::ARCHIVE_INDEX_COUNT_PREFIX . count($expandedfiles) . "\n";
            $sizes = array();
            $mtimes = array();
            foreach ($expandedfiles as $archivepath => $file) {
                                if (!preg_match('~^[\x00-\xff]*$~', $archivepath)) {
                    throw new coding_exception(
                            'Non-ASCII paths not supported: ' . $archivepath);
                }

                                $type = 'f';
                $mtime = '?';
                if (is_null($file)) {
                    $type = 'd';
                    $size = 0;
                } else if (is_string($file)) {
                    $stat = stat($file);
                    $mtime = (int)$stat['mtime'];
                    $size = (int)$stat['size'];
                } else if (is_array($file)) {
                    $size = (int)strlen(reset($file));
                } else {
                    $mtime = (int)$file->get_timemodified();
                    $size = (int)$file->get_filesize();
                }
                $sizes[$archivepath] = $size;
                $mtimes[$archivepath] = $mtime;

                                $list .= "$archivepath\t$type\t$size\t$mtime\n";
            }

                        if ($this->includeindex) {
                                $this->write_tar_entry($gz, self::ARCHIVE_INDEX_FILE, null, strlen($list), '?', $list);
            }

                        $done = (int)(self::PROGRESS_MAX / 10);
            if ($progress) {
                $progress->progress($done, self::PROGRESS_MAX);
            }
            if ($expandedfiles) {
                                $progressperfile = (int)((9 * self::PROGRESS_MAX) / (10 * count($expandedfiles)));
            } else {
                $progressperfile = 1;
            }

                        foreach ($expandedfiles as $archivepath => $file) {
                if (is_null($file)) {
                                        $this->write_tar_entry($gz, $archivepath, null,
                            $sizes[$archivepath], $mtimes[$archivepath]);
                } else if (is_string($file)) {
                                        $this->write_tar_entry($gz, $archivepath, $file,
                            $sizes[$archivepath], $mtimes[$archivepath], null, $progress, $done);
                } else if (is_array($file)) {
                                        $data = reset($file);
                    $this->write_tar_entry($gz, $archivepath, null,
                            $sizes[$archivepath], $mtimes[$archivepath], $data, $progress, $done);
                } else {
                                        $this->write_tar_entry($gz, $archivepath, $file->get_content_file_handle(),
                            $sizes[$archivepath], $mtimes[$archivepath], null, $progress, $done);
                }
                $done += $progressperfile;
                if ($progress) {
                    $progress->progress($done, self::PROGRESS_MAX);
                }
            }

                        gzwrite($gz, str_pad('', 2 * self::TAR_BLOCK_SIZE, "\x00"));
            gzclose($gz);
            return true;
        } catch (Exception $e) {
                        gzclose($gz);
            unlink($archivefile);
            throw $e;
        }
    }

    
    protected function write_tar_entry($gz, $archivepath, $file, $size, $mtime, $content = null,
            file_progress $progress = null, $done = 0) {
                
                $directory = false;
        if ($size === 0 && is_null($file)) {
            $directory = true;
            if (!preg_match('~/$~', $archivepath)) {
                $archivepath .= '/';
            }
            $mode = '755';
        } else {
            $mode = '644';
        }

                $name = $archivepath;
        $prefix = '';
        while (strlen($name) > 100) {
            $slash = strpos($name, '/');
            if ($slash === false) {
                throw new coding_exception(
                        'Name cannot fit length restrictions (> 100 characters): ' . $archivepath);
            }

            if ($prefix !== '') {
                $prefix .= '/';
            }
            $prefix .= substr($name, 0, $slash);
            $name = substr($name, $slash + 1);
            if (strlen($prefix) > 155) {
                throw new coding_exception(
                        'Name cannot fit length restrictions (path too long): ' . $archivepath);
            }
        }

                                                $forchecksum = $name;

                        $header = str_pad($name, 100, "\x00");

                                $header .= '0000' . $mode . "\x000000000\x000000000\x00";
        $forchecksum .= $mode;

                $octalsize = decoct($size);
        if (strlen($octalsize) > 11) {
            throw new coding_exception(
                    'File too large for .tar file: ' . $archivepath . ' (' . $size . ' bytes)');
        }
        $paddedsize = str_pad($octalsize, 11, '0', STR_PAD_LEFT);
        $forchecksum .= $paddedsize;
        $header .= $paddedsize . "\x00";

                if ($mtime === '?') {
                                    $mtime = self::DEFAULT_TIMESTAMP;
        }
        $octaltime = decoct($mtime);
        $paddedtime = str_pad($octaltime, 11, '0', STR_PAD_LEFT);
        $forchecksum .= $paddedtime;
        $header .= $paddedtime . "\x00";

                        $header .= '        ';

                $typeflag = $directory ? '5' : '0';
        $forchecksum .= $typeflag;
        $header .= $typeflag;

                $header .= str_pad('', 100, "\x00");

                        $header .= "ustar\x0000";

                                        $header .= str_pad('', 80, "\x00");

                        $header .= str_pad($prefix, 167, "\x00");
        $forchecksum .= $prefix;

        
                        
                                                                $checksum = 1775 + self::calculate_checksum($forchecksum);

        $octalchecksum = str_pad(decoct($checksum), 6, '0', STR_PAD_LEFT) . "\x00 ";

                $header = substr($header, 0, 148) . $octalchecksum . substr($header, 156);

        if (strlen($header) != self::TAR_BLOCK_SIZE) {
            throw new coding_exception('Header block wrong size!!!!!');
        }

                gzwrite($gz, $header);

                if (is_string($file)) {
            $file = fopen($file, 'rb');
            if (!$file) {
                return false;
            }
        }

        if ($content !== null) {
                        if (strlen($content) !== $size) {
                throw new coding_exception('Mismatch between provided sizes: ' . $archivepath);
            }
            gzwrite($gz, $content);
        } else if ($file !== null) {
                        $written = 0;
            $chunks = 0;
            while (true) {
                $data = fread($file, 65536);
                if ($data === false || strlen($data) == 0) {
                    break;
                }
                $written += gzwrite($gz, $data);

                                                $chunks++;
                if ($chunks == 16) {
                    $chunks = 0;
                    if ($progress) {
                                                                                                $progress->progress($done, self::PROGRESS_MAX);
                    }
                }
            }
            fclose($file);

            if ($written !== $size) {
                throw new coding_exception('Mismatch between provided sizes: ' . $archivepath .
                        ' (was ' . $written . ', expected ' . $size . ')');
            }
        } else if ($size != 0) {
            throw new coding_exception('Missing data file handle for non-empty file');
        }

                $leftover = self::TAR_BLOCK_SIZE - ($size % self::TAR_BLOCK_SIZE);
        if ($leftover == 512) {
            $leftover = 0;
        } else {
            gzwrite($gz, str_pad('', $leftover, "\x00"));
        }

        return true;
    }

    
    protected static function calculate_checksum($str) {
        $checksum = 0;
        $checklength = strlen($str);
        for ($i = 0; $i < $checklength; $i++) {
            $checksum += ord($str[$i]);
        }
        return $checksum;
    }

    
    protected function list_files_path(array &$expandedfiles, $archivepath, $path,
            file_progress $progress = null, $done) {
        if (is_dir($path)) {
                                    if ($archivepath != '') {
                                $expandedfiles[$archivepath . '/'] = null;
            }

                        if (!$handle = opendir($path)) {
                return false;
            }
            while (false !== ($entry = readdir($handle))) {
                if ($entry === '.' || $entry === '..') {
                    continue;
                }
                $result = $this->list_files_path($expandedfiles,
                        $archivepath . '/' . $entry, $path . '/' . $entry,
                        $progress, $done);
                if (!$result) {
                    return false;
                }
                if ($progress) {
                    $progress->progress($done, self::PROGRESS_MAX);
                }
            }
            closedir($handle);
        } else {
                        $expandedfiles[$archivepath] = $path;
        }
        return true;
    }

    
    protected function list_files_stored(array &$expandedfiles, $archivepath, stored_file $file) {
        if ($file->is_directory()) {
                        $expandedfiles[$archivepath . '/'] = null;

                                    $fs = get_file_storage();
            $baselength = strlen($file->get_filepath());
            $files = $fs->get_directory_files(
                    $file->get_contextid(), $file->get_component(), $file->get_filearea(), $file->get_itemid(),
                    $file->get_filepath(), true, true);
            foreach ($files as $childfile) {
                                $path = $childfile->get_filepath();
                $path = substr($path, $baselength);
                $path = $archivepath . '/' . $path;
                if ($childfile->is_directory()) {
                    $childfile = null;
                } else {
                    $path .= $childfile->get_filename();
                }
                $expandedfiles[$path] = $childfile;
            }
        } else {
                        $expandedfiles[$archivepath] = $file;
        }
    }

    
    public function extract_to_pathname($archivefile, $pathname,
            array $onlyfiles = null, file_progress $progress = null) {
        $extractor = new tgz_extractor($archivefile);
        return $extractor->extract(
                new tgz_packer_extract_to_pathname($pathname, $onlyfiles), $progress);
    }

    
    public function extract_to_storage($archivefile, $contextid,
            $component, $filearea, $itemid, $pathbase, $userid = null,
            file_progress $progress = null) {
        $extractor = new tgz_extractor($archivefile);
        return $extractor->extract(
                new tgz_packer_extract_to_storage($contextid, $component,
                    $filearea, $itemid, $pathbase, $userid), $progress);
    }

    
    public function list_files($archivefile) {
        $extractor = new tgz_extractor($archivefile);
        return $extractor->list_files();
    }

    
    public static function is_tgz_file($archivefile) {
        if (is_a($archivefile, 'stored_file')) {
            $fp = $archivefile->get_content_file_handle();
        } else {
            $fp = fopen($archivefile, 'rb');
        }
        $firstbytes = fread($fp, 2);
        fclose($fp);
        return ($firstbytes[0] == "\x1f" && $firstbytes[1] == "\x8b");
    }

    
    public static function has_required_extension() {
        return extension_loaded('zlib');
    }
}



class tgz_packer_extract_to_pathname implements tgz_extractor_handler {
    
    protected $pathname;
    
    protected $onlyfiles;

    
    public function __construct($pathname, array $onlyfiles = null) {
        $this->pathname = $pathname;
        $this->onlyfiles = $onlyfiles;
    }

    
    public function tgz_start_file($archivepath) {
                if ($this->onlyfiles !== null && !in_array($archivepath, $this->onlyfiles)) {
            return null;
        }
                $fullpath = $this->pathname . '/' . $archivepath;
        check_dir_exists(dirname($fullpath));
        return $fullpath;
    }

    
    public function tgz_end_file($archivepath, $realpath) {
            }

    
    public function tgz_directory($archivepath, $mtime) {
                if ($this->onlyfiles !== null && !in_array($archivepath, $this->onlyfiles)) {
            return false;
        }
                $fullpath = $this->pathname . '/' . $archivepath;
        check_dir_exists($fullpath);
        return true;
    }
}



class tgz_packer_extract_to_storage implements tgz_extractor_handler {
    
    protected $tempfile;

    
    protected $contextid;
    
    protected $component;
    
    protected $filearea;
    
    protected $itemid;
    
    protected $pathbase;
    
    protected $userid;

    
    public function __construct($contextid, $component, $filearea, $itemid, $pathbase, $userid) {
        global $CFG;

                $this->contextid = $contextid;
        $this->component = $component;
        $this->filearea = $filearea;
        $this->itemid = $itemid;
        $this->pathbase = $pathbase;
        $this->userid = $userid;

                $tempfolder = $CFG->tempdir . '/core_files';
        check_dir_exists($tempfolder);
        $this->tempfile = tempnam($tempfolder, '.dat');
    }

    
    public function tgz_start_file($archivepath) {
                return $this->tempfile;
    }

    
    public function tgz_end_file($archivepath, $realpath) {
                $fs = get_file_storage();
        $filerecord = array('contextid' => $this->contextid, 'component' => $this->component,
                'filearea' => $this->filearea, 'itemid' => $this->itemid);
        $filerecord['filepath'] = $this->pathbase . dirname($archivepath) . '/';
        $filerecord['filename'] = basename($archivepath);
        if ($this->userid) {
            $filerecord['userid'] = $this->userid;
        }
                tgz_packer::delete_existing_file_record($fs, $filerecord);
        $fs->create_file_from_pathname($filerecord, $this->tempfile);
        unlink($this->tempfile);
    }

    
    public function tgz_directory($archivepath, $mtime) {
                if (!preg_match('~/$~', $archivepath)) {
            $archivepath .= '/';
        }
                $fs = get_file_storage();
        if (!$fs->file_exists($this->contextid, $this->component, $this->filearea, $this->itemid,
                $this->pathbase . $archivepath, '.')) {
            $fs->create_directory($this->contextid, $this->component, $this->filearea, $this->itemid,
                    $this->pathbase . $archivepath);
        }
        return true;
    }
}
