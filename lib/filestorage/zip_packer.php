<?php



defined('MOODLE_INTERNAL') || die();

require_once("$CFG->libdir/filestorage/file_packer.php");
require_once("$CFG->libdir/filestorage/zip_archive.php");


class zip_packer extends file_packer {

    
    public function archive_to_storage(array $files, $contextid,
            $component, $filearea, $itemid, $filepath, $filename,
            $userid = NULL, $ignoreinvalidfiles=true, file_progress $progress = null) {
        global $CFG;

        $fs = get_file_storage();

        check_dir_exists($CFG->tempdir.'/zip');
        $tmpfile = tempnam($CFG->tempdir.'/zip', 'zipstor');

        if ($result = $this->archive_to_pathname($files, $tmpfile, $ignoreinvalidfiles, $progress)) {
            if ($file = $fs->get_file($contextid, $component, $filearea, $itemid, $filepath, $filename)) {
                if (!$file->delete()) {
                    @unlink($tmpfile);
                    return false;
                }
            }
            $file_record = new stdClass();
            $file_record->contextid = $contextid;
            $file_record->component = $component;
            $file_record->filearea  = $filearea;
            $file_record->itemid    = $itemid;
            $file_record->filepath  = $filepath;
            $file_record->filename  = $filename;
            $file_record->userid    = $userid;
            $file_record->mimetype  = 'application/zip';

            $result = $fs->create_file_from_pathname($file_record, $tmpfile);
        }
        @unlink($tmpfile);
        return $result;
    }

    
    public function archive_to_pathname(array $files, $archivefile,
            $ignoreinvalidfiles=true, file_progress $progress = null) {
        $ziparch = new zip_archive();
        if (!$ziparch->open($archivefile, file_archive::OVERWRITE)) {
            return false;
        }

        $abort = false;
        foreach ($files as $archivepath => $file) {
            $archivepath = trim($archivepath, '/');

                        if ($progress) {
                $progress->progress();
            }

            if (is_null($file)) {
                                if (!$ziparch->add_directory($archivepath.'/')) {
                    debugging("Can not zip '$archivepath' directory", DEBUG_DEVELOPER);
                    if (!$ignoreinvalidfiles) {
                        $abort = true;
                        break;
                    }
                }

            } else if (is_string($file)) {
                if (!$this->archive_pathname($ziparch, $archivepath, $file, $progress)) {
                    debugging("Can not zip '$archivepath' file", DEBUG_DEVELOPER);
                    if (!$ignoreinvalidfiles) {
                        $abort = true;
                        break;
                    }
                }

            } else if (is_array($file)) {
                $content = reset($file);
                if (!$ziparch->add_file_from_string($archivepath, $content)) {
                    debugging("Can not zip '$archivepath' file", DEBUG_DEVELOPER);
                    if (!$ignoreinvalidfiles) {
                        $abort = true;
                        break;
                    }
                }

            } else {
                if (!$this->archive_stored($ziparch, $archivepath, $file, $progress)) {
                    debugging("Can not zip '$archivepath' file", DEBUG_DEVELOPER);
                    if (!$ignoreinvalidfiles) {
                        $abort = true;
                        break;
                    }
                }
            }
        }

        if (!$ziparch->close()) {
            @unlink($archivefile);
            return false;
        }

        if ($abort) {
            @unlink($archivefile);
            return false;
        }

        return true;
    }

    
    private function archive_stored($ziparch, $archivepath, $file, file_progress $progress = null) {
        $result = $file->archive_file($ziparch, $archivepath);
        if (!$result) {
            return false;
        }

        if (!$file->is_directory()) {
            return true;
        }

        $baselength = strlen($file->get_filepath());
        $fs = get_file_storage();
        $files = $fs->get_directory_files($file->get_contextid(), $file->get_component(), $file->get_filearea(), $file->get_itemid(),
                                          $file->get_filepath(), true, true);
        foreach ($files as $file) {
                        if ($progress) {
                $progress->progress();
            }

            $path = $file->get_filepath();
            $path = substr($path, $baselength);
            $path = $archivepath.'/'.$path;
            if (!$file->is_directory()) {
                $path = $path.$file->get_filename();
            }
                        $file->archive_file($ziparch, $path);
        }

        return true;
    }

    
    private function archive_pathname($ziparch, $archivepath, $file,
            file_progress $progress = null) {
                if ($progress) {
            $progress->progress();
        }

        if (!file_exists($file)) {
            return false;
        }

        if (is_file($file)) {
            if (!is_readable($file)) {
                return false;
            }
            return $ziparch->add_file_from_pathname($archivepath, $file);
        }
        if (is_dir($file)) {
            if ($archivepath !== '') {
                $ziparch->add_directory($archivepath);
            }
            $files = new DirectoryIterator($file);
            foreach ($files as $file) {
                if ($file->isDot()) {
                    continue;
                }
                $newpath = $archivepath.'/'.$file->getFilename();
                $this->archive_pathname($ziparch, $newpath, $file->getPathname(), $progress);
            }
            unset($files);             return true;
        }
    }

    
    public function extract_to_pathname($archivefile, $pathname,
            array $onlyfiles = null, file_progress $progress = null) {
        global $CFG;

        if (!is_string($archivefile)) {
            return $archivefile->extract_to_pathname($this, $pathname, $progress);
        }

        $processed = array();

        $pathname = rtrim($pathname, '/');
        if (!is_readable($archivefile)) {
            return false;
        }
        $ziparch = new zip_archive();
        if (!$ziparch->open($archivefile, file_archive::OPEN)) {
            return false;
        }

                if ($progress) {
            $approxmax = $ziparch->estimated_count();
            $done = 0;
        }

        foreach ($ziparch as $info) {
                        if ($progress) {
                $progress->progress($done, $approxmax);
                $done++;
            }

            $size = $info->size;
            $name = $info->pathname;

            if ($name === '' or array_key_exists($name, $processed)) {
                                continue;
            } else if (is_array($onlyfiles) && !in_array($name, $onlyfiles)) {
                                continue;
            }

            if ($info->is_directory) {
                $newdir = "$pathname/$name";
                                if (is_file($newdir) and !unlink($newdir)) {
                    $processed[$name] = 'Can not create directory, file already exists';                     continue;
                }
                if (is_dir($newdir)) {
                                        $processed[$name] = true;
                } else {
                    if (mkdir($newdir, $CFG->directorypermissions, true)) {
                        $processed[$name] = true;
                    } else {
                        $processed[$name] = 'Can not create directory';                     }
                }
                continue;
            }

            $parts = explode('/', trim($name, '/'));
            $filename = array_pop($parts);
            $newdir = rtrim($pathname.'/'.implode('/', $parts), '/');

            if (!is_dir($newdir)) {
                if (!mkdir($newdir, $CFG->directorypermissions, true)) {
                    $processed[$name] = 'Can not create directory';                     continue;
                }
            }

            $newfile = "$newdir/$filename";
            if (!$fp = fopen($newfile, 'wb')) {
                $processed[$name] = 'Can not write target file';                 continue;
            }
            if (!$fz = $ziparch->get_stream($info->index)) {
                $processed[$name] = 'Can not read file from zip archive';                 fclose($fp);
                continue;
            }

            while (!feof($fz)) {
                $content = fread($fz, 262143);
                fwrite($fp, $content);
            }
            fclose($fz);
            fclose($fp);
            if (filesize($newfile) !== $size) {
                $processed[$name] = 'Unknown error during zip extraction';                                 @unlink($newfile);
                continue;
            }
            $processed[$name] = true;
        }
        $ziparch->close();
        return $processed;
    }

    
    public function extract_to_storage($archivefile, $contextid,
            $component, $filearea, $itemid, $pathbase, $userid = NULL,
            file_progress $progress = null) {
        global $CFG;

        if (!is_string($archivefile)) {
            return $archivefile->extract_to_storage($this, $contextid, $component,
                    $filearea, $itemid, $pathbase, $userid, $progress);
        }

        check_dir_exists($CFG->tempdir.'/zip');

        $pathbase = trim($pathbase, '/');
        $pathbase = ($pathbase === '') ? '/' : '/'.$pathbase.'/';
        $fs = get_file_storage();

        $processed = array();

        $ziparch = new zip_archive();
        if (!$ziparch->open($archivefile, file_archive::OPEN)) {
            return false;
        }

                if ($progress) {
            $approxmax = $ziparch->estimated_count();
            $done = 0;
        }

        foreach ($ziparch as $info) {
                        if ($progress) {
                $progress->progress($done, $approxmax);
                $done++;
            }

            $size = $info->size;
            $name = $info->pathname;

            if ($name === '' or array_key_exists($name, $processed)) {
                                continue;
            }

            if ($info->is_directory) {
                $newfilepath = $pathbase.$name.'/';
                $fs->create_directory($contextid, $component, $filearea, $itemid, $newfilepath, $userid);
                $processed[$name] = true;
                continue;
            }

            $parts = explode('/', trim($name, '/'));
            $filename = array_pop($parts);
            $filepath = $pathbase;
            if ($parts) {
                $filepath .= implode('/', $parts).'/';
            }

            if ($size < 2097151) {
                                if (!$fz = $ziparch->get_stream($info->index)) {
                    $processed[$name] = 'Can not read file from zip archive';                     continue;
                }
                $content = '';
                while (!feof($fz)) {
                    $content .= fread($fz, 262143);
                }
                fclose($fz);
                if (strlen($content) !== $size) {
                    $processed[$name] = 'Unknown error during zip extraction';                                         unset($content);
                    continue;
                }

                if ($file = $fs->get_file($contextid, $component, $filearea, $itemid, $filepath, $filename)) {
                    if (!$file->delete()) {
                        $processed[$name] = 'Can not delete existing file';                         continue;
                    }
                }
                $file_record = new stdClass();
                $file_record->contextid = $contextid;
                $file_record->component = $component;
                $file_record->filearea  = $filearea;
                $file_record->itemid    = $itemid;
                $file_record->filepath  = $filepath;
                $file_record->filename  = $filename;
                $file_record->userid    = $userid;
                if ($fs->create_file_from_string($file_record, $content)) {
                    $processed[$name] = true;
                } else {
                    $processed[$name] = 'Unknown error during zip extraction';                 }
                unset($content);
                continue;

            } else {
                                $tmpfile = tempnam($CFG->tempdir.'/zip', 'unzip');
                if (!$fp = fopen($tmpfile, 'wb')) {
                    @unlink($tmpfile);
                    $processed[$name] = 'Can not write temp file';                     continue;
                }
                if (!$fz = $ziparch->get_stream($info->index)) {
                    @unlink($tmpfile);
                    $processed[$name] = 'Can not read file from zip archive';                     continue;
                }
                while (!feof($fz)) {
                    $content = fread($fz, 262143);
                    fwrite($fp, $content);
                }
                fclose($fz);
                fclose($fp);
                if (filesize($tmpfile) !== $size) {
                    $processed[$name] = 'Unknown error during zip extraction';                                         @unlink($tmpfile);
                    continue;
                }

                if ($file = $fs->get_file($contextid, $component, $filearea, $itemid, $filepath, $filename)) {
                    if (!$file->delete()) {
                        @unlink($tmpfile);
                        $processed[$name] = 'Can not delete existing file';                         continue;
                    }
                }
                $file_record = new stdClass();
                $file_record->contextid = $contextid;
                $file_record->component = $component;
                $file_record->filearea  = $filearea;
                $file_record->itemid    = $itemid;
                $file_record->filepath  = $filepath;
                $file_record->filename  = $filename;
                $file_record->userid    = $userid;
                if ($fs->create_file_from_pathname($file_record, $tmpfile)) {
                    $processed[$name] = true;
                } else {
                    $processed[$name] = 'Unknown error during zip extraction';                 }
                @unlink($tmpfile);
                continue;
            }
        }
        $ziparch->close();
        return $processed;
    }

    
    public function list_files($archivefile) {
        if (!is_string($archivefile)) {
            return $archivefile->list_files();
        }

        $ziparch = new zip_archive();
        if (!$ziparch->open($archivefile, file_archive::OPEN)) {
            return false;
        }
        $list = $ziparch->list_files();
        $ziparch->close();
        return $list;
    }

}
