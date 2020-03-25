<?php



namespace core\update;

use core_component;
use coding_exception;
use moodle_exception;
use SplFileInfo;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;

defined('MOODLE_INTERNAL') || die();

require_once($CFG->libdir.'/filelib.php');


class code_manager {

    
    protected $dirroot;
    
    protected $temproot;

    
    public function __construct($dirroot=null, $temproot=null) {
        global $CFG;

        if (empty($dirroot)) {
            $dirroot = $CFG->dirroot;
        }

        if (empty($temproot)) {
                                                                                                            $temproot = make_temp_directory('core_plugin/code_manager');
        }

        $this->dirroot = $dirroot;
        $this->temproot = $temproot;

        $this->init_temp_directories();
    }

    
    public function get_remote_plugin_zip($url, $md5) {

                $url = str_replace(array("\r", "\n"), '', $url);

        if (!preg_match('|^https?://|i', $url)) {
            $this->debug('Error fetching plugin ZIP: unsupported transport protocol: '.$url);
            return false;
        }

                $distfile = $this->temproot.'/distfiles/'.$md5.'.zip';

        if (is_readable($distfile) and md5_file($distfile) === $md5) {
            return $distfile;
        } else {
            @unlink($distfile);
        }

                $tempdir = make_request_directory();
        $tempfile = $tempdir.'/plugin.zip';
        $result = $this->download_plugin_zip_file($url, $tempfile);

        if (!$result) {
            return false;
        }

        $actualmd5 = md5_file($tempfile);

                if ($actualmd5 !== $md5) {
            $this->debug('Error fetching plugin ZIP: md5 mismatch.');
            return false;
        }

                if ($actualmd5 === 'd41d8cd98f00b204e9800998ecf8427e') {
            return false;
        }

                if (!rename($tempfile, $distfile)) {
            return false;
        }

        return $distfile;
    }

    
    public function unzip_plugin_file($zipfilepath, $targetdir, $rootdir = '') {

                $fp = get_file_packer('application/zip');
        $tempdir = make_request_directory();
        $files = $fp->extract_to_pathname($zipfilepath, $tempdir);

        if (!$files) {
            return array();
        }

                if (!empty($rootdir)) {
            $files = $this->rename_extracted_rootdir($tempdir, $rootdir, $files);
        }

                foreach ($files as $path => $status) {
            if ($status !== true) {
                continue;
            }
            $parts = explode('/', trim($path, '/'));
            while (array_pop($parts)) {
                if (empty($parts)) {
                    break;
                }
                $dir = implode('/', $parts).'/';
                if (!isset($files[$dir])) {
                    $files[$dir] = true;
                }
            }
        }

                $this->move_extracted_plugin_files($tempdir, $targetdir, $files);

                $this->set_plugin_files_permissions($targetdir, $files);

        return $files;
    }

    
    public function zip_plugin_folder($folderpath, $targetzip) {

        if (file_exists($targetzip)) {
            throw new coding_exception('Attempting to create already existing ZIP file', $targetzip);
        }

        if (!is_writable(dirname($targetzip))) {
            throw new coding_exception('Target ZIP location not writable', dirname($targetzip));
        }

        if (!is_dir($folderpath)) {
            throw new coding_exception('Attempting to ZIP non-existing source directory', $folderpath);
        }

        $files = $this->list_plugin_folder_files($folderpath);
        $fp = get_file_packer('application/zip');
        return $fp->archive_to_pathname($files, $targetzip, false);
    }

    
    public function archive_plugin_version($folderpath, $component, $version, $overwrite=false) {

        if ($component !== clean_param($component, PARAM_SAFEDIR)) {
                        throw new moodle_exception('unexpected_plugin_component_format', 'core_plugin', '', null, $component);
        }

        if ((string)$version !== clean_param((string)$version, PARAM_FILE)) {
                        throw new moodle_exception('unexpected_plugin_version_format', 'core_plugin', '', null, $version);
        }

        if (empty($component) or empty($version)) {
            return false;
        }

        if (!is_dir($folderpath)) {
            return false;
        }

        $archzip = $this->temproot.'/archive/'.$component.'/'.$version.'.zip';

        if (file_exists($archzip) and !$overwrite) {
            return true;
        }

        $tmpzip = make_request_directory().'/'.$version.'.zip';
        $zipped = $this->zip_plugin_folder($folderpath, $tmpzip);

        if (!$zipped) {
            return false;
        }

                list($expectedtype, $expectedname) = core_component::normalize_component($component);
        $actualname = $this->get_plugin_zip_root_dir($tmpzip);
        if ($actualname !== $expectedname) {
                        throw new moodle_exception('unexpected_archive_structure', 'core_plugin');
        }

        make_writable_directory(dirname($archzip));
        return rename($tmpzip, $archzip);
    }

    
    public function get_archived_plugin_version($component, $version) {

        if (empty($component) or empty($version)) {
            return false;
        }

        $archzip = $this->temproot.'/archive/'.$component.'/'.$version.'.zip';

        if (file_exists($archzip)) {
            return $archzip;
        }

        return false;
    }

    
    public function list_plugin_folder_files($folderpath) {

        $folder = new RecursiveDirectoryIterator($folderpath);
        $iterator = new RecursiveIteratorIterator($folder);
        $folderpathinfo = new SplFileInfo($folderpath);
        $strip = strlen($folderpathinfo->getPathInfo()->getRealPath()) + 1;
        $files = array();
        foreach ($iterator as $fileinfo) {
            if ($fileinfo->getFilename() === '..') {
                continue;
            }
            if (strpos($fileinfo->getRealPath(), $folderpathinfo->getRealPath() !== 0)) {
                throw new moodle_exception('unexpected_filepath_mismatch', 'core_plugin');
            }
            $key = substr($fileinfo->getRealPath(), $strip);
            if ($fileinfo->isDir() and substr($key, -1) !== '/') {
                $key .= '/';
            }
            $files[str_replace(DIRECTORY_SEPARATOR, '/', $key)] = str_replace(DIRECTORY_SEPARATOR, '/', $fileinfo->getRealPath());
        }
        return $files;
    }

    
    public function get_plugin_zip_root_dir($zipfilepath) {

        $fp = get_file_packer('application/zip');
        $files = $fp->list_files($zipfilepath);

        if (empty($files)) {
            return false;
        }

        $rootdirname = null;
        foreach ($files as $file) {
            $pathnameitems = explode('/', $file->pathname);
            if (empty($pathnameitems)) {
                return false;
            }
                                    if ($rootdirname === null) {
                $rootdirname = $pathnameitems[0];
            }
                                    if ($rootdirname !== $pathnameitems[0]) {
                return false;
            }
        }

        return $rootdirname;
    }

    
    
    protected function init_temp_directories() {
        make_writable_directory($this->temproot.'/distfiles');
        make_writable_directory($this->temproot.'/archive');
    }

    
    protected function debug($msg) {
        debugging($msg, DEBUG_DEVELOPER);
    }

    
    protected function download_plugin_zip_file($url, $tofile) {

        if (file_exists($tofile)) {
            $this->debug('Error fetching plugin ZIP: target location exists.');
            return false;
        }

        $status = $this->download_file_content($url, $tofile);

        if (!$status) {
            $this->debug('Error fetching plugin ZIP.');
            @unlink($tofile);
            return false;
        }

        return true;
    }

    
    protected function download_file_content($url, $tofile) {

                $headers = null;
        $postdata = null;
        $fullresponse = false;
        $timeout = 300;
        $connecttimeout = 20;
        $skipcertverify = false;
        $tofile = $tofile;
        $calctimeout = false;

        return download_file_content($url, $headers, $postdata, $fullresponse, $timeout,
            $connecttimeout, $skipcertverify, $tofile, $calctimeout);
    }

    
    protected function rename_extracted_rootdir($dirname, $rootdir, array $files) {

        if (!is_dir($dirname)) {
            $this->debug('Unable to rename rootdir of non-existing content');
            return $files;
        }

        if (file_exists($dirname.'/'.$rootdir)) {
                        return $files;
        }

        $found = null;         foreach (scandir($dirname) as $item) {
            if (substr($item, 0, 1) === '.') {
                continue;
            }
            if (is_dir($dirname.'/'.$item)) {
                if ($found !== null and $found !== $item) {
                                        throw new moodle_exception('unexpected_archive_structure', 'core_plugin');
                }
                $found = $item;
            }
        }

        if (!is_null($found)) {
            if (rename($dirname.'/'.$found, $dirname.'/'.$rootdir)) {
                $newfiles = array();
                foreach ($files as $filepath => $status) {
                    $newpath = preg_replace('~^'.preg_quote($found.'/').'~', preg_quote($rootdir.'/'), $filepath);
                    $newfiles[$newpath] = $status;
                }
                return $newfiles;
            }
        }

        return $files;
    }

    
    protected function set_plugin_files_permissions($targetdir, array $files) {

        $dirpermissions = fileperms($targetdir);
        $filepermissions = ($dirpermissions & 0666);

        foreach ($files as $subpath => $notusedhere) {
            $path = $targetdir.'/'.$subpath;
            if (is_dir($path)) {
                @chmod($path, $dirpermissions);
            } else {
                @chmod($path, $filepermissions);
            }
        }
    }

    
    protected function move_extracted_plugin_files($sourcedir, $targetdir, array $files) {
        global $CFG;

        foreach ($files as $file => $status) {
            if ($status !== true) {
                throw new moodle_exception('corrupted_archive_structure', 'core_plugin', '', $file, $status);
            }

            $source = $sourcedir.'/'.$file;
            $target = $targetdir.'/'.$file;

            if (is_dir($source)) {
                continue;

            } else {
                if (!is_dir(dirname($target))) {
                    mkdir(dirname($target), $CFG->directorypermissions, true);
                }
                rename($source, $target);
            }
        }
    }
}
