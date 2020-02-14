<?php



defined('MOODLE_INTERNAL') || die();

require_once("$CFG->libdir/filestorage/file_archive.php");


class zip_archive extends file_archive {

    
    protected $archivepathname = null;

    
    protected $mode = null;

    
    protected $usedmem = 0;

    
    protected $pos = 0;

    
    protected $za;

    
    protected $modified = false;

    
    protected $namelookup = null;

    
    protected static $emptyzipcontent = 'UEsFBgAAAAAAAAAAAAAAAAAAAAAAAA==';

    
    protected $emptyziphack = false;

    
    public function __construct() {
        $this->encoding = null;     }

    
    public function open($archivepathname, $mode=file_archive::CREATE, $encoding=null) {
        $this->close();

        $this->usedmem  = 0;
        $this->pos      = 0;
        $this->encoding = $encoding;
        $this->mode     = $mode;

        $this->za = new ZipArchive();

        switch($mode) {
            case file_archive::OPEN:      $flags = 0; break;
            case file_archive::OVERWRITE: $flags = ZIPARCHIVE::CREATE | ZIPARCHIVE::OVERWRITE; break;             case file_archive::CREATE:
            default :                     $flags = ZIPARCHIVE::CREATE; break;
        }

        $result = $this->za->open($archivepathname, $flags);

        if ($flags == 0 and $result === ZIPARCHIVE::ER_NOZIP and filesize($archivepathname) === 22) {
                        if (file_get_contents($archivepathname) === base64_decode(self::$emptyzipcontent)) {
                if ($temp = make_temp_directory('zip')) {
                    $this->emptyziphack = tempnam($temp, 'zip');
                    $this->za = new ZipArchive();
                    $result = $this->za->open($this->emptyziphack, ZIPARCHIVE::CREATE);
                }
            }
        }

        if ($result === true) {
            if (file_exists($archivepathname)) {
                $this->archivepathname = realpath($archivepathname);
            } else {
                $this->archivepathname = $archivepathname;
            }
            return true;

        } else {
            $message = 'Unknown error.';
            switch ($result) {
                case ZIPARCHIVE::ER_EXISTS: $message = 'File already exists.'; break;
                case ZIPARCHIVE::ER_INCONS: $message = 'Zip archive inconsistent.'; break;
                case ZIPARCHIVE::ER_INVAL: $message = 'Invalid argument.'; break;
                case ZIPARCHIVE::ER_MEMORY: $message = 'Malloc failure.'; break;
                case ZIPARCHIVE::ER_NOENT: $message = 'No such file.'; break;
                case ZIPARCHIVE::ER_NOZIP: $message = 'Not a zip archive.'; break;
                case ZIPARCHIVE::ER_OPEN: $message = 'Can\'t open file.'; break;
                case ZIPARCHIVE::ER_READ: $message = 'Read error.'; break;
                case ZIPARCHIVE::ER_SEEK: $message = 'Seek error.'; break;
            }
            debugging($message.': '.$archivepathname, DEBUG_DEVELOPER);
            $this->za = null;
            $this->archivepathname = null;
            return false;
        }
    }

    
    protected function mangle_pathname($localname) {
        $result = str_replace('\\', '/', $localname);           $result = preg_replace('/\.\.+/', '', $result);         $result = ltrim($result, '/');                  
        if ($result === '.') {
            $result = '';
        }

        return $result;
    }

    
    protected function unmangle_pathname($localname) {
        $this->init_namelookup();

        if (!isset($this->namelookup[$localname])) {
            $name = $localname;
                        if (!empty($this->encoding) and $this->encoding !== 'utf-8') {
                $name = @core_text::convert($name, $this->encoding, 'utf-8');
            }
            $name = str_replace('\\', '/', $name);               $name = clean_param($name, PARAM_PATH);              return ltrim($name, '/');                        }

        return $this->namelookup[$localname];
    }

    
    public function close() {
        if (!isset($this->za)) {
            return false;
        }

        if ($this->emptyziphack) {
            @$this->za->close();
            $this->za = null;
            $this->mode = null;
            $this->namelookup = null;
            $this->modified = false;
            @unlink($this->emptyziphack);
            $this->emptyziphack = false;
            return true;

        } else if ($this->za->numFiles == 0) {
                        @$this->za->close();
            $this->za = null;
            $this->mode = null;
            $this->namelookup = null;
            $this->modified = false;
                                    if (@filesize($this->archivepathname) == 22 &&
                    @file_get_contents($this->archivepathname) === base64_decode(self::$emptyzipcontent)) {
                return true;
            }
            @unlink($this->archivepathname);
            $data = base64_decode(self::$emptyzipcontent);
            if (!file_put_contents($this->archivepathname, $data)) {
                return false;
            }
            return true;
        }

        $res = $this->za->close();
        $this->za = null;
        $this->mode = null;
        $this->namelookup = null;

        if ($this->modified) {
            $this->fix_utf8_flags();
            $this->modified = false;
        }

        return $res;
    }

    
    public function get_stream($index) {
        if (!isset($this->za)) {
            return false;
        }

        $name = $this->za->getNameIndex($index);
        if ($name === false) {
            return false;
        }

        return $this->za->getStream($name);
    }

    
    public function get_info($index) {
        if (!isset($this->za)) {
            return false;
        }

                if ($index < 0 or $index >=$this->za->numFiles) {
            return false;
        }

                        $result = $this->za->statIndex($index, 64);

        if ($result === false) {
            return false;
        }

        $info = new stdClass();
        $info->index             = $index;
        $info->original_pathname = $result['name'];
        $info->pathname          = $this->unmangle_pathname($result['name']);
        $info->mtime             = (int)$result['mtime'];

        if ($info->pathname[strlen($info->pathname)-1] === '/') {
            $info->is_directory = true;
            $info->size         = 0;
        } else {
            $info->is_directory = false;
            $info->size         = (int)$result['size'];
        }

        if ($this->is_system_file($info)) {
                        return false;
        }

        return $info;
    }

    
    public function list_files() {
        if (!isset($this->za)) {
            return false;
        }

        $infos = array();

        foreach ($this as $info) {
                        array_push($infos, $info);
        }

        return $infos;
    }

    public function is_system_file($fileinfo) {
        if (substr($fileinfo->pathname, 0, 8) === '__MACOSX' or substr($fileinfo->pathname, -9) === '.DS_Store') {
                        return true;
        }
        if (substr($fileinfo->pathname, -9) === 'Thumbs.db') {
            $stream = $this->za->getStream($fileinfo->pathname);
            $info = base64_encode(fread($stream, 8));
            fclose($stream);
            if ($info === '0M8R4KGxGuE=') {
                                return true;
            }
        }
        return false;
    }

    
    public function count() {
        if (!isset($this->za)) {
            return false;
        }

        return count($this->list_files());
    }

    
    public function estimated_count() {
        if (!isset($this->za)) {
            return false;
        }

        return $this->za->numFiles;
    }

    
    public function add_file_from_pathname($localname, $pathname) {
        if ($this->emptyziphack) {
            $this->close();
            $this->open($this->archivepathname, file_archive::OVERWRITE, $this->encoding);
        }

        if (!isset($this->za)) {
            return false;
        }

        if ($this->archivepathname === realpath($pathname)) {
                        return false;
        }

        if (!is_readable($pathname) or is_dir($pathname)) {
            return false;
        }

        if (is_null($localname)) {
            $localname = clean_param($pathname, PARAM_PATH);
        }
        $localname = trim($localname, '/');         $localname = $this->mangle_pathname($localname);

        if ($localname === '') {
                        return false;
        }

        if (!$this->za->addFile($pathname, $localname)) {
            return false;
        }
        $this->modified = true;
        return true;
    }

    
    public function add_file_from_string($localname, $contents) {
        if ($this->emptyziphack) {
            $this->close();
            $this->open($this->archivepathname, file_archive::OVERWRITE, $this->encoding);
        }

        if (!isset($this->za)) {
            return false;
        }

        $localname = trim($localname, '/');         $localname = $this->mangle_pathname($localname);

        if ($localname === '') {
                        return false;
        }

        if ($this->usedmem > 2097151) {
                        $this->close();
            $res = $this->open($this->archivepathname, file_archive::OPEN, $this->encoding);
            if ($res !== true) {
                print_error('cannotopenzip');
            }
        }
        $this->usedmem += strlen($contents);

        if (!$this->za->addFromString($localname, $contents)) {
            return false;
        }
        $this->modified = true;
        return true;
    }

    
    public function add_directory($localname) {
        if ($this->emptyziphack) {
            $this->close();
            $this->open($this->archivepathname, file_archive::OVERWRITE, $this->encoding);
        }

        if (!isset($this->za)) {
            return false;
        }
        $localname = trim($localname, '/'). '/';
        $localname = $this->mangle_pathname($localname);

        if ($localname === '/') {
                        return false;
        }

        if ($localname !== '') {
            if (!$this->za->addEmptyDir($localname)) {
                return false;
            }
            $this->modified = true;
        }
        return true;
    }

    
    public function current() {
        if (!isset($this->za)) {
            return false;
        }

        return $this->get_info($this->pos);
    }

    
    public function key() {
        return $this->pos;
    }

    
    public function next() {
        $this->pos++;
    }

    
    public function rewind() {
        $this->pos = 0;
    }

    
    public function valid() {
        if (!isset($this->za)) {
            return false;
        }

                while (!$this->get_info($this->pos) && $this->pos < $this->za->numFiles) {
            $this->next();
        }

                if ($this->pos >= $this->za->numFiles) {
            return false;
        }

        return true;
    }

    
    protected function init_namelookup() {
        if ($this->emptyziphack) {
            $this->namelookup = array();
            return;
        }

        if (!isset($this->za)) {
            return;
        }
        if (isset($this->namelookup)) {
            return;
        }

        $this->namelookup = array();

        if ($this->mode != file_archive::OPEN) {
                        return;
        }

        if (!file_exists($this->archivepathname)) {
            return;
        }

        if (!$fp = fopen($this->archivepathname, 'rb')) {
            return;
        }
        if (!$filesize = filesize($this->archivepathname)) {
            return;
        }

        $centralend = self::zip_get_central_end($fp, $filesize);

        if ($centralend === false or $centralend['disk'] !== 0 or $centralend['disk_start'] !== 0 or $centralend['offset'] === 0xFFFFFFFF) {
                        fclose($fp);
            return;
        }

        fseek($fp, $centralend['offset']);
        $data = fread($fp, $centralend['size']);
        $pos = 0;
        $files = array();
        for($i=0; $i<$centralend['entries']; $i++) {
            $file = self::zip_parse_file_header($data, $centralend, $pos);
            if ($file === false) {
                                fclose($fp);
                return;
            }
            $files[] = $file;
        }
        fclose($fp);

        foreach ($files as $file) {
            $name = $file['name'];
            if (preg_match('/^[a-zA-Z0-9_\-\.]*$/', $file['name'])) {
                                $name = fix_utf8($name);

            } else if (!($file['general'] & pow(2, 11))) {
                                $found = false;
                foreach($file['extra'] as $extra) {
                    if ($extra['id'] === 0x7075) {
                        $data = unpack('cversion/Vcrc', substr($extra['data'], 0, 5));
                        if ($data['crc'] === crc32($name)) {
                            $found = true;
                            $name = substr($extra['data'], 5);
                        }
                    }
                }
                if (!$found and !empty($this->encoding) and $this->encoding !== 'utf-8') {
                                        $newname = @core_text::convert($name, $this->encoding, 'utf-8');
                    $original  = core_text::convert($newname, 'utf-8', $this->encoding);
                    if ($original === $name) {
                        $found = true;
                        $name = $newname;
                    }
                }
                if (!$found and $file['version'] === 0x315) {
                                        $newname = fix_utf8($name);
                    if ($newname === $name) {
                        $found = true;
                        $name = $newname;
                    }
                }
                if (!$found and $file['version'] === 0) {
                                        $newname = fix_utf8($name);
                    if ($newname === $name) {
                        $found = true;
                        $name = $newname;
                    }
                }
                if (!$found and $encoding = get_string('oldcharset', 'langconfig')) {
                                        $windows = true;
                    foreach($file['extra'] as $extra) {
                                                $windows = false;
                        if ($extra['id'] === 0x000a) {
                            $windows = true;
                            break;
                        }
                    }

                    if ($windows === true) {
                        switch(strtoupper($encoding)) {
                            case 'ISO-8859-1': $encoding = 'CP850'; break;
                            case 'ISO-8859-2': $encoding = 'CP852'; break;
                            case 'ISO-8859-4': $encoding = 'CP775'; break;
                            case 'ISO-8859-5': $encoding = 'CP866'; break;
                            case 'ISO-8859-6': $encoding = 'CP720'; break;
                            case 'ISO-8859-7': $encoding = 'CP737'; break;
                            case 'ISO-8859-8': $encoding = 'CP862'; break;
                            case 'EUC-JP':
                            case 'UTF-8':
                                if ($winchar = get_string('localewincharset', 'langconfig')) {
                                                                                                            $encoding = $winchar;
                                }
                                break;
                        }
                    }
                    $newname = @core_text::convert($name, $encoding, 'utf-8');
                    $original  = core_text::convert($newname, 'utf-8', $encoding);

                    if ($original === $name) {
                        $name = $newname;
                    }
                }
            }
            $name = str_replace('\\', '/', $name);              $name = clean_param($name, PARAM_PATH);             $name = ltrim($name, '/');              
            if (function_exists('normalizer_normalize')) {
                $name = normalizer_normalize($name, Normalizer::FORM_C);
            }

            $this->namelookup[$file['name']] = $name;
        }
    }

    
    protected function fix_utf8_flags() {
        if ($this->emptyziphack) {
            return true;
        }

        if (!file_exists($this->archivepathname)) {
            return true;
        }

                if (!$fp = fopen($this->archivepathname, 'rb+')) {
            return false;
        }
        if (!$filesize = filesize($this->archivepathname)) {
            return false;
        }

        $centralend = self::zip_get_central_end($fp, $filesize);

        if ($centralend === false or $centralend['disk'] !== 0 or $centralend['disk_start'] !== 0 or $centralend['offset'] === 0xFFFFFFFF) {
                        fclose($fp);
            return false;
        }

        fseek($fp, $centralend['offset']);
        $data = fread($fp, $centralend['size']);
        $pos = 0;
        $files = array();
        for($i=0; $i<$centralend['entries']; $i++) {
            $file = self::zip_parse_file_header($data, $centralend, $pos);
            if ($file === false) {
                                fclose($fp);
                return false;
            }

            $newgeneral = $file['general'] | pow(2, 11);
            if ($newgeneral === $file['general']) {
                                continue;
            }

            if (preg_match('/^[a-zA-Z0-9_\-\.]*$/', $file['name'])) {
                                continue;
            }
            if ($file['extra']) {
                                continue;
            }
            if (fix_utf8($file['name']) !== $file['name']) {
                                continue;
            }

                        fseek($fp, $file['local_offset']);
            $localfile = unpack('Vsig/vversion_req/vgeneral/vmethod/vmtime/vmdate/Vcrc/Vsize_compressed/Vsize/vname_length/vextra_length', fread($fp, 30));
            if ($localfile['sig'] !== 0x04034b50) {
                                fclose($fp);
                return false;
            }

            $file['local'] = $localfile;
            $files[] = $file;
        }

        foreach ($files as $file) {
            $localfile = $file['local'];
                        fseek($fp, $file['central_offset'] + 8);
            if (ftell($fp) === $file['central_offset'] + 8) {
                $newgeneral = $file['general'] | pow(2, 11);
                fwrite($fp, pack('v', $newgeneral));
            }
                        fseek($fp, $file['local_offset'] + 6);
            if (ftell($fp) === $file['local_offset'] + 6) {
                $newgeneral = $localfile['general'] | pow(2, 11);
                fwrite($fp, pack('v', $newgeneral));
            }
        }

        fclose($fp);
        return true;
    }

    
    public static function zip_get_central_end($fp, $filesize) {
                fseek($fp, $filesize - 22);
        $info = unpack('Vsig', fread($fp, 4));
        if ($info['sig'] === 0x06054b50) {
                        fseek($fp, $filesize - 22);
            $data = fread($fp, 22);
        } else {
                        fseek($fp, $filesize - 65557);
            $data = fread($fp, 65557);
        }

        $pos = strpos($data, pack('V', 0x06054b50));
        if ($pos === false) {
                        return false;
        }
        $centralend = unpack('Vsig/vdisk/vdisk_start/vdisk_entries/ventries/Vsize/Voffset/vcomment_length', substr($data, $pos, 22));
        if ($centralend['comment_length']) {
            $centralend['comment'] = substr($data, 22, $centralend['comment_length']);
        } else {
            $centralend['comment'] = '';
        }

        return $centralend;
    }

    
    public static function zip_parse_file_header($data, $centralend, &$pos) {
        $file = unpack('Vsig/vversion/vversion_req/vgeneral/vmethod/Vmodified/Vcrc/Vsize_compressed/Vsize/vname_length/vextra_length/vcomment_length/vdisk/vattr/Vattrext/Vlocal_offset', substr($data, $pos, 46));
        $file['central_offset'] = $centralend['offset'] + $pos;
        $pos = $pos + 46;
        if ($file['sig'] !== 0x02014b50) {
                        return false;
        }
        $file['name'] = substr($data, $pos, $file['name_length']);
        $pos = $pos + $file['name_length'];
        $file['extra'] = array();
        $file['extra_data'] = '';
        if ($file['extra_length']) {
            $extradata = substr($data, $pos, $file['extra_length']);
            $file['extra_data'] = $extradata;
            while (strlen($extradata) > 4) {
                $extra = unpack('vid/vsize', substr($extradata, 0, 4));
                $extra['data'] = substr($extradata, 4, $extra['size']);
                $extradata = substr($extradata, 4+$extra['size']);
                $file['extra'][] = $extra;
            }
            $pos = $pos + $file['extra_length'];
        }
        if ($file['comment_length']) {
            $pos = $pos + $file['comment_length'];
            $file['comment'] = substr($data, $pos, $file['comment_length']);
        } else {
            $file['comment'] = '';
        }
        return $file;
    }
}
