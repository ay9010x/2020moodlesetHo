<?php

class Horde_Mime_Magic
{
    
    static protected $_map = null;

    
    static protected function _getMimeExtensionMap()
    {
        if (is_null(self::$_map)) {
            require __DIR__ . '/mime.mapping.php';
            self::$_map = $mime_extension_map;
        }

        return self::$_map;
    }

    
    static public function extToMime($ext)
    {
        if (empty($ext)) {
            return 'application/octet-stream';
        }

        $ext = Horde_String::lower($ext);
        $map = self::_getMimeExtensionMap();
        $pos = 0;

        while (!isset($map[$ext])) {
            if (($pos = strpos($ext, '.')) === false) {
                break;
            }
            $ext = substr($ext, $pos + 1);
        }

        return isset($map[$ext])
            ? $map[$ext]
            : 'x-extension/' . $ext;
    }

    
    static public function filenameToMime($filename, $unknown = true)
    {
        $pos = strlen($filename) + 1;
        $type = '';

        $map = self::_getMimeExtensionMap();
        for ($i = 0; $i <= $map['__MAXPERIOD__']; ++$i) {
            $npos = strrpos(substr($filename, 0, $pos - 1), '.');
            if ($npos === false) {
                break;
            }
            $pos = $npos + 1;
        }

        $type = ($pos === false) ? '' : self::extToMime(substr($filename, $pos));

        return (empty($type) || (!$unknown && (strpos($type, 'x-extension') !== false)))
            ? 'application/octet-stream'
            : $type;
    }

    
    static public function mimeToExt($type)
    {
        if (empty($type)) {
            return false;
        }

        if (($key = array_search($type, self::_getMimeExtensionMap())) === false) {
            list($major, $minor) = explode('/', $type);
            if ($major == 'x-extension') {
                return $minor;
            }
            if (strpos($minor, 'x-') === 0) {
                return substr($minor, 2);
            }
            return false;
        }

        return $key;
    }

    
    static public function analyzeFile($path, $magic_db = null,
                                       $opts = array())
    {
        if (Horde_Util::extensionExists('fileinfo')) {
            $res = empty($magic_db)
                ? finfo_open(FILEINFO_MIME)
                : finfo_open(FILEINFO_MIME, $magic_db);

            if ($res) {
                $type = trim(finfo_file($res, $path));
                finfo_close($res);

                
                if (empty($opts['nostrip'])) {
                    foreach (array(';', ',', '\\0') as $separator) {
                        if (($pos = strpos($type, $separator)) !== false) {
                            $type = rtrim(substr($type, 0, $pos));
                        }
                    }

                    if (preg_match('|^[a-z0-9]+/[.-a-z0-9]+$|i', $type)) {
                        return $type;
                    }
                } else {
                    return $type;
                }
            }
        }

        return false;
    }

    
    static public function analyzeData($data, $magic_db = null,
                                       $opts = array())
    {
        
        if (Horde_Util::extensionExists('fileinfo')) {
            $res = empty($magic_db)
                ? @finfo_open(FILEINFO_MIME)
                : @finfo_open(FILEINFO_MIME, $magic_db);

            if (!$res) {
                return false;
            }

            $type = trim(finfo_buffer($res, $data));
            finfo_close($res);

            
            if (empty($opts['nostrip'])) {
                if (($pos = strpos($type, ';')) !== false) {
                    $type = rtrim(substr($type, 0, $pos));
                }

                if (($pos = strpos($type, ',')) !== false) {
                    $type = rtrim(substr($type, 0, $pos));
                }
            }

            return $type;
        }

        return false;
    }

}
