<?php


class Minify_Cache_File {
    
    public function __construct($path = '', $fileLocking = false)
    {
        if (! $path) {
            $path = self::tmp();
        }
        $this->_locking = $fileLocking;
        $this->_path = $path;
    }

    
    public function store($id, $data)
    {
        $flag = $this->_locking
            ? LOCK_EX
            : null;
        $file = $this->_path . '/' . $id;
        if (! @file_put_contents($file, $data, $flag)) {
            $this->_log("Minify_Cache_File: Write failed to '$file'");
        }
                if ($data !== $this->fetch($id)) {
            @unlink($file);
            $this->_log("Minify_Cache_File: Post-write read failed for '$file'");
            return false;
        }
        return true;
    }
    
    
    public function getSize($id)
    {
        return filesize($this->_path . '/' . $id);
    }
    
    
    public function isValid($id, $srcMtime)
    {
        $file = $this->_path . '/' . $id;
        return (is_file($file) && (filemtime($file) >= $srcMtime));
    }
    
    
    public function display($id)
    {
        if ($this->_locking) {
            $fp = fopen($this->_path . '/' . $id, 'rb');
            flock($fp, LOCK_SH);
            fpassthru($fp);
            flock($fp, LOCK_UN);
            fclose($fp);
        } else {
            readfile($this->_path . '/' . $id);            
        }
    }
    
	
    public function fetch($id)
    {
        if ($this->_locking) {
            $fp = fopen($this->_path . '/' . $id, 'rb');
            if (!$fp) {
                return false;
            }
            flock($fp, LOCK_SH);
            $ret = stream_get_contents($fp);
            flock($fp, LOCK_UN);
            fclose($fp);
            return $ret;
        } else {
            return file_get_contents($this->_path . '/' . $id);
        }
    }
    
    
    public function getPath()
    {
        return $this->_path;
    }

    
    public static function tmp()
    {
        static $tmp = null;
        if (! $tmp) {
            $tmp = function_exists('sys_get_temp_dir')
                ? sys_get_temp_dir()
                : self::_tmp();
            $tmp = rtrim($tmp, DIRECTORY_SEPARATOR);
        }
        return $tmp;
    }

    
    protected static function _tmp()
    {
                if (strtolower(substr(PHP_OS, 0, 3)) != 'win') {
            $tmp = empty($_ENV['TMPDIR']) ? getenv('TMPDIR') : $_ENV['TMPDIR'];
            if ($tmp) {
                return $tmp;
            } else {
                return '/tmp';
            }
        }
                $tmp = empty($_ENV['TEMP']) ? getenv('TEMP') : $_ENV['TEMP'];
        if ($tmp) {
            return $tmp;
        }
                $tmp = empty($_ENV['TMP']) ? getenv('TMP') : $_ENV['TMP'];
        if ($tmp) {
            return $tmp;
        }
                $tmp = empty($_ENV['windir']) ? getenv('windir') : $_ENV['windir'];
        if ($tmp) {
            return $tmp;
        }
                return getenv('SystemRoot') . '\\temp';
    }

    
    protected function _log($msg)
    {
        Minify_Logger::log($msg);
    }
    
    private $_path = null;
    private $_locking = null;
}
