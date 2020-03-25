<?php



defined('MOODLE_INTERNAL') || die();


class tgz_extractor {
    
    const WRITE_BLOCK_SIZE = 65536;
    
    const READ_BLOCK_SIZE = 65536;
    
    protected $storedfile;
    
    protected $ospath;
    
    protected $numfiles;
    
    protected $donefiles;
    
    protected $currentarchivepath;
    
    protected $currentfile;
    
    protected $currentfilesize;
    
    protected $currentfileprocessed;
    
    protected $currentfp;
    
    protected $currentmtime;
    
    protected $filebuffer;
    
    protected $filebufferlength;
    
    protected $results;

    
    protected $listresults = null;

    
    protected $mode = self::MODE_EXTRACT;

    
    const MODE_EXTRACT = 0;

    
    const MODE_LIST = 1;

    
    const MODE_LIST_COMPLETE = 2;

    
    public function __construct($archivefile) {
        if (is_a($archivefile, 'stored_file')) {
            $this->storedfile = $archivefile;
        } else {
            $this->ospath = $archivefile;
        }
    }

    
    public function extract(tgz_extractor_handler $handler, file_progress $progress = null) {
        $this->mode = self::MODE_EXTRACT;
        $this->extract_or_list($handler, $progress);
        $results = $this->results;
        unset($this->results);
        return $results;
    }

    
    protected function extract_or_list(tgz_extractor_handler $handler = null, file_progress $progress = null) {
                if ($this->storedfile) {
            $gz = $this->storedfile->get_content_file_handle(stored_file::FILE_HANDLE_GZOPEN);
                                                $estimatedbuffers = ($this->storedfile->get_filesize() * 2 / self::READ_BLOCK_SIZE) + 1;
        } else {
            $gz = gzopen($this->ospath, 'rb');
            $estimatedbuffers = (filesize($this->ospath) * 2 / self::READ_BLOCK_SIZE) + 1;
        }
        if (!$gz) {
            throw new moodle_exception('errorprocessingarchive', '', '', null,
                    'Failed to open gzip file');
        }

                $progressperbuffer = (int)(tgz_packer::PROGRESS_MAX / $estimatedbuffers);

                $buffer = '';
        $bufferpos = 0;
        $bufferlength = 0;
        $this->numfiles = -1;
        $read = 0;
        $done = 0;
        $beforeprogress = -1;
        while (true) {
            if ($bufferpos == $bufferlength) {
                $buffer = gzread($gz, self::READ_BLOCK_SIZE);
                $bufferpos = 0;
                $bufferlength = strlen($buffer);
                if ($bufferlength == 0) {
                                        break;
                }

                                if ($progress) {
                    if ($this->numfiles === -1) {
                                                                        $done += $progressperbuffer;
                        if ($done >= tgz_packer::PROGRESS_MAX) {
                            $done = tgz_packer::PROGRESS_MAX - 1;
                        }
                        $progress->progress($done, tgz_packer::PROGRESS_MAX);
                    } else {
                                                if ($beforeprogress === -1) {
                            $beforeprogress = $done;
                        }
                                                                                                                        $done = $beforeprogress + (int)(($this->donefiles / $this->numfiles) *
                                (tgz_packer::PROGRESS_MAX - $beforeprogress));
                    }
                    $progress->progress($done, tgz_packer::PROGRESS_MAX);
                }
            }

            $block = substr($buffer, $bufferpos, tgz_packer::TAR_BLOCK_SIZE);
            if ($this->currentfile) {
                $this->process_file_block($block, $handler);
            } else {
                $this->process_header($block, $handler);
            }

                        if ($this->mode === self::MODE_LIST_COMPLETE) {
                break;
            }

            $bufferpos += tgz_packer::TAR_BLOCK_SIZE;
            $read++;
        }

                gzclose($gz);
    }

    
    public function list_files() {
        $this->listresults = array();
        $this->mode = self::MODE_LIST;
        $this->extract_or_list();
        $listresults = $this->listresults;
        $this->listresults = null;
        return $listresults;
    }

    
    protected function process_header($block, $handler) {
                        if ($block === str_pad('', tgz_packer::TAR_BLOCK_SIZE, "\0")) {
            return;
        }

                        $name = rtrim(substr($block, 0, 100), "\0");

                                        $filesize = octdec(substr($block, 124, 11));

                $mtime = octdec(substr($block, 136, 11));

                        $typeflag = substr($block, 156, 1);

                        $magic = substr($block, 257, 6);
        if ($magic !== "ustar\0" && $magic !== "ustar ") {
                                    throw new moodle_exception('errorprocessingarchive', '', '', null,
                    'Header does not have POSIX ustar magic string');
        }

                                                        $prefix = rtrim(substr($block, 345, 155), "\0");

                
        $archivepath = ltrim($prefix . '/' . $name, '/');

                $archivepath = clean_param($archivepath, PARAM_PATH);

                switch ($typeflag) {
            case '1' :
            case '2' :
            case '3' :
            case '4' :
            case '6' :
            case '7' :
                                break;

            case '5' :
                                if ($this->mode === self::MODE_LIST) {
                    $this->listresults[] = (object)array(
                            'original_pathname' => $archivepath,
                            'pathname' => $archivepath,
                            'mtime' => $mtime,
                            'is_directory' => true,
                            'size' => 0);
                } else if ($handler->tgz_directory($archivepath, $mtime)) {
                    $this->results[$archivepath] = true;
                }
                break;

            default:
                                $this->start_current_file($archivepath, $filesize, $mtime, $handler);
                break;
        }
    }

    
    protected function process_file_block($block, tgz_extractor_handler $handler = null) {
                $blocksize = tgz_packer::TAR_BLOCK_SIZE;
        if ($this->currentfileprocessed + tgz_packer::TAR_BLOCK_SIZE > $this->currentfilesize) {
                        $blocksize = $this->currentfilesize - $this->currentfileprocessed;
            $this->filebuffer .= substr($block, 0, $blocksize);
        } else {
                        $this->filebuffer .= $block;
        }
        $this->filebufferlength += $blocksize;
        $this->currentfileprocessed += $blocksize;

                $eof = $this->currentfileprocessed == $this->currentfilesize;
        if ($this->filebufferlength >= self::WRITE_BLOCK_SIZE || $eof) {
                        if ($this->currentfile !== true) {
                if (!fwrite($this->currentfp, $this->filebuffer)) {
                    throw new moodle_exception('errorprocessingarchive', '', '', null,
                            'Failed to write buffer to output file: ' . $this->currentfile);
                }
            }
            $this->filebuffer = '';
            $this->filebufferlength = 0;
        }

                if ($eof) {
            $this->close_current_file($handler);
        }
    }

    
    protected function start_current_file($archivepath, $filesize, $mtime,
            tgz_extractor_handler $handler = null) {
        global $CFG;

        $this->currentarchivepath = $archivepath;
        $this->currentmtime = $mtime;
        $this->currentfilesize = $filesize;
        $this->currentfileprocessed = 0;

        if ($archivepath === tgz_packer::ARCHIVE_INDEX_FILE) {
                        $tempfolder = $CFG->tempdir . '/core_files';
            check_dir_exists($tempfolder);
            $this->currentfile = tempnam($tempfolder, '.index');
        } else {
            if ($this->mode === self::MODE_LIST) {
                                $this->listresults[] = (object)array(
                        'original_pathname' => $archivepath,
                        'pathname' => $archivepath,
                        'mtime' => $mtime,
                        'is_directory' => false,
                        'size' => $filesize);

                                $this->currentfile = true;
            } else {
                                $this->currentfile = $handler->tgz_start_file($archivepath);
                if ($this->currentfile === null) {
                                        $this->currentfile = true;
                }
            }
        }
        $this->filebuffer = '';
        $this->filebufferlength = 0;

                if ($this->currentfile !== true) {
            $this->currentfp = fopen($this->currentfile, 'wb');
            if (!$this->currentfp) {
                throw new moodle_exception('errorprocessingarchive', '', '', null,
                        'Failed to open output file: ' . $this->currentfile);
            }
        } else {
            $this->currentfp = null;
        }

                if ($filesize == 0) {
            $this->close_current_file($handler);
        }
    }

    
    protected function close_current_file($handler) {
        if ($this->currentfp !== null) {
            if (!fclose($this->currentfp)) {
                throw new moodle_exception('errorprocessingarchive', '', '', null,
                        'Failed to close output file: ' .  $this->currentfile);
            }

                                                        }

        if ($this->currentarchivepath === tgz_packer::ARCHIVE_INDEX_FILE) {
            if ($this->mode === self::MODE_LIST) {
                                $index = file($this->currentfile);
                $ok = true;
                foreach ($index as $num => $value) {
                                        if ($num == 0) {
                        if (preg_match('~^' . preg_quote(tgz_packer::ARCHIVE_INDEX_COUNT_PREFIX) . '~', $value)) {
                            continue;
                        } else {
                                                        $ok = false;
                            break;
                        }
                    }
                                        $values = explode("\t", trim($value));
                    $this->listresults[] = (object)array(
                        'original_pathname' => $values[0],
                        'pathname' => $values[0],
                        'mtime' => ($values[3] === '?' ? tgz_packer::DEFAULT_TIMESTAMP : (int)$values[3]),
                        'is_directory' => $values[1] === 'd',
                        'size' => (int)$values[2]);
                }
                if ($ok) {
                    $this->mode = self::MODE_LIST_COMPLETE;
                }
                unlink($this->currentfile);
            } else {
                                $contents = file_get_contents($this->currentfile, null, null, null, 128);
                $matches = array();
                if (preg_match('~^' . preg_quote(tgz_packer::ARCHIVE_INDEX_COUNT_PREFIX) .
                        '([0-9]+)~', $contents, $matches)) {
                    $this->numfiles = (int)$matches[1];
                }
                unlink($this->currentfile);
            }
        } else {
                        if ($this->currentfp !== null) {
                $handler->tgz_end_file($this->currentarchivepath, $this->currentfile);
                $this->results[$this->currentarchivepath] = true;
            }
            $this->donefiles++;
        }

                $this->currentfp = null;
        $this->currentfile = null;
        $this->currentarchivepath = null;
    }

}


interface tgz_extractor_handler {
    
    public function tgz_start_file($archivepath);

    
    public function tgz_end_file($archivepath, $realpath);

    
    public function tgz_directory($archivepath, $mtime);
}
