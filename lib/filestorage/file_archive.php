<?php




defined('MOODLE_INTERNAL') || die();


abstract class file_archive implements Iterator {

    
    const OPEN      = 1;

    
    const CREATE    = 2;

    
    const OVERWRITE = 4;

    
    protected $encoding = 'utf-8';

    
    public abstract function open($archivepathname, $mode=file_archive::CREATE, $encoding='utf-8');

    
    public abstract function close();

    
    public abstract function get_stream($index);

    
    public abstract function get_info($index);

    
    public abstract function list_files();

    
    public abstract function count();

    
    public abstract function add_file_from_pathname($localname, $pathname);

    
    public abstract function add_file_from_string($localname, $contents);

    
    public abstract function add_directory($localname);

    
    protected function mangle_pathname($localname) {
        if ($this->encoding === 'utf-8') {
            return $localname;
        }

        $converted = core_text::convert($localname, 'utf-8', $this->encoding);
        $original  = core_text::convert($converted, $this->encoding, 'utf-8');

        if ($original === $localname) {
            $result = $converted;

        } else {
                        $converted2 = core_text::specialtoascii($localname);
            $converted2 = core_text::convert($converted2, 'utf-8', $this->encoding);
            $original2  = core_text::convert($converted, $this->encoding, 'utf-8');

            if ($original2 === $localname) {
                                $result = $converted2;
            } else {
                                $result = $converted;
            }
        }

        $result = preg_replace('/\.\.+/', '', $result);
        $result = ltrim($result); 
        if ($result === '.') {
            $result = '';
        }

        return $result;
    }

    
    protected function unmangle_pathname($localname) {
        $result = str_replace('\\', '/', $localname);         $result = ltrim($result, '/');                
        if ($this->encoding !== 'utf-8') {
            $result = core_text::convert($result, $this->encoding, 'utf-8');
        }

        return clean_param($result, PARAM_PATH);
    }

    
    
    
    
    
    
    
    
    
    
}
