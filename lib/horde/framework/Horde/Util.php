<?php

class Horde_Util
{
    
    static public $patterns = array(
        "\x55", "\xaa", "\x92\x49\x24", "\x49\x24\x92", "\x24\x92\x49",
        "\x00", "\x11", "\x22", "\x33", "\x44", "\x55", "\x66", "\x77",
        "\x88", "\x99", "\xaa", "\xbb", "\xcc", "\xdd", "\xee", "\xff",
        "\x92\x49\x24", "\x49\x24\x92", "\x24\x92\x49", "\x6d\xb6\xdb",
        "\xb6\xdb\x6d", "\xdb\x6d\xb6"
    );

    
    static protected $_magicquotes = null;

    
    static protected $_shutdowndata = array(
        'dirs' => array(),
        'files' => array(),
        'secure' => array()
    );

    
    static protected $_shutdownreg = false;

    
    static protected $_cache = array();

    
    static public function nonInputVar($varname, $default = null)
    {
        return (isset($_GET[$varname]) || isset($_POST[$varname]) || isset($_COOKIE[$varname]))
            ? $default
            : (isset($GLOBALS[$varname]) ? $GLOBALS[$varname] : $default);
    }

    
    static public function formInput($append_session = 0)
    {
        return (($append_session == 1) || !isset($_COOKIE[session_name()]))
            ? '<input type="hidden" name="' . htmlspecialchars(session_name()) . '" value="' . htmlspecialchars(session_id()) . "\" />\n"
            : '';
    }

    
    static public function pformInput($append_session = 0)
    {
        echo self::formInput($append_session);
    }

    
    static public function dispelMagicQuotes($var)
    {
        if (is_null(self::$_magicquotes)) {
            self::$_magicquotes = get_magic_quotes_gpc();
        }

        if (self::$_magicquotes) {
            $var = is_array($var)
                ? array_map(array(__CLASS__, 'dispelMagicQuotes'), $var)
                : stripslashes($var);
        }

        return $var;
    }

    
    static public function getFormData($var, $default = null)
    {
        return (($val = self::getPost($var)) !== null)
            ? $val
            : self::getGet($var, $default);
    }

    
    static public function getGet($var, $default = null)
    {
        return (isset($_GET[$var]))
            ? self::dispelMagicQuotes($_GET[$var])
            : $default;
    }

    
    static public function getPost($var, $default = null)
    {
        return (isset($_POST[$var]))
            ? self::dispelMagicQuotes($_POST[$var])
            : $default;
    }

    
    static public function getTempFile($prefix = '', $delete = true, $dir = '',
                                       $secure = false)
    {
        $tempDir = (empty($dir) || !is_dir($dir))
            ? sys_get_temp_dir()
            : $dir;

        $tempFile = tempnam($tempDir, $prefix);

                if (empty($tempFile)) {
            return false;
        }

        if ($delete) {
            self::deleteAtShutdown($tempFile, true, $secure);
        }

        return $tempFile;
    }

    
    static public function getTempFileWithExtension($extension = '.tmp',
                                                    $prefix = '',
                                                    $delete = true, $dir = '',
                                                    $secure = false)
    {
        $tempDir = (empty($dir) || !is_dir($dir))
            ? sys_get_temp_dir()
            : $dir;

        if (empty($tempDir)) {
            return false;
        }

        $windows = substr(PHP_OS, 0, 3) == 'WIN';
        $tries = 1;
        do {
                        $sysFileName = tempnam($tempDir, $prefix);
            if ($sysFileName === false) {
                return false;
            }

                        $tmpFileName = $sysFileName . $extension;
            if ($sysFileName == $tmpFileName) {
                return $sysFileName;
            }

                                                $fileCreated = ($windows ? @rename($sysFileName, $tmpFileName) : @link($sysFileName, $tmpFileName));
            if ($fileCreated) {
                if (!$windows) {
                    unlink($sysFileName);
                }

                if ($delete) {
                    self::deleteAtShutdown($tmpFileName, true, $secure);
                }

                return $tmpFileName;
            }

            unlink($sysFileName);
        } while (++$tries <= 5);

        return false;
    }

    
    static public function createTempDir($delete = true, $temp_dir = null)
    {
        if (is_null($temp_dir)) {
            $temp_dir = sys_get_temp_dir();
        }

        if (empty($temp_dir)) {
            return false;
        }

        
        do {
            $new_dir = $temp_dir . '/' . substr(base_convert(uniqid(mt_rand()), 10, 36), 0, 8);
        } while (file_exists($new_dir));

        $old_umask = umask(0000);
        if (!mkdir($new_dir, 0700)) {
            $new_dir = false;
        } elseif ($delete) {
            self::deleteAtShutdown($new_dir);
        }
        umask($old_umask);

        return $new_dir;
    }

    
    static public function realPath($path)
    {
        
        if (!strncasecmp(PHP_OS, 'WIN', 3)) {
            $path = str_replace('\\', '/', $path);
        }

        
        $path = preg_replace(array("|/+|", "@(/\.)+(/|\Z(?!\n))@"), array('/', '/'), $path);

        
        if ($path != './') {
            $path = preg_replace("|^(\./)+|", '', $path);
        }

        
        $path = preg_replace("|^/(\.\./?)+|", '/', $path);

        
        if ($path != '/') {
            $path = preg_replace("|/\Z(?!\n)|", '', $path);
        }

        
        while (strpos($path, '/..') !== false) {
            $path = preg_replace("|/[^/]+/\.\.|", '', $path);
        }

        return empty($path) ? '/' : $path;
    }

    
    static public function deleteAtShutdown($filename, $register = true,
                                            $secure = false)
    {
        
        if (!self::$_shutdownreg) {
            register_shutdown_function(array(__CLASS__, 'shutdown'));
            self::$_shutdownreg = true;
        }

        $ptr = &self::$_shutdowndata;
        if ($register) {
            if (@is_dir($filename)) {
                $ptr['dirs'][$filename] = true;
            } else {
                $ptr['files'][$filename] = true;
            }

            if ($secure) {
                $ptr['secure'][$filename] = true;
            }
        } else {
            unset($ptr['dirs'][$filename], $ptr['files'][$filename], $ptr['secure'][$filename]);
        }
    }

    
    static public function shutdown()
    {
        $ptr = &self::$_shutdowndata;

        foreach ($ptr['files'] as $file => $val) {
            
            if ($val && file_exists($file)) {
                
                if (isset($ptr['secure'][$file])) {
                    $filesize = filesize($file);
                    $fp = fopen($file, 'r+');
                    foreach (self::$patterns as $pattern) {
                        $pattern = substr(str_repeat($pattern, floor($filesize / strlen($pattern)) + 1), 0, $filesize);
                        fwrite($fp, $pattern);
                        fseek($fp, 0);
                    }
                    fclose($fp);
                }
                @unlink($file);
            }
        }

        foreach ($ptr['dirs'] as $dir => $val) {
            
            if ($val && file_exists($dir)) {
                
                $dir_class = dir($dir);
                while (false !== ($entry = $dir_class->read())) {
                    if ($entry != '.' && $entry != '..') {
                        @unlink($dir . '/' . $entry);
                    }
                }
                $dir_class->close();
                @rmdir($dir);
            }
        }
    }

    
    static public function extensionExists($ext)
    {
        if (!isset(self::$_cache[$ext])) {
            self::$_cache[$ext] = extension_loaded($ext);
        }

        return self::$_cache[$ext];
    }

    
    static public function loadExtension($ext)
    {
        
        if (self::extensionExists($ext)) {
            return true;
        }

        
        if ((ini_get('enable_dl') != 1) ||
            (ini_get('safe_mode') == 1) ||
            !function_exists('dl')) {
            return false;
        }

        if (!strncasecmp(PHP_OS, 'WIN', 3)) {
            $suffix = 'dll';
        } else {
            switch (PHP_OS) {
            case 'HP-UX':
                $suffix = 'sl';
                break;

            case 'AIX':
                $suffix = 'a';
                break;

            case 'OSX':
                $suffix = 'bundle';
                break;

            default:
                $suffix = 'so';
            }
        }

        return dl($ext . '.' . $suffix) || dl('php_' . $ext . '.' . $suffix);
    }

    
    static public function getPathInfo()
    {
        if (isset($_SERVER['PATH_INFO']) &&
            (strpos($_SERVER['SERVER_SOFTWARE'], 'lighttpd') === false)) {
            return $_SERVER['PATH_INFO'];
        } elseif (isset($_SERVER['REQUEST_URI']) &&
                  isset($_SERVER['SCRIPT_NAME'])) {
            $search = Horde_String::common($_SERVER['SCRIPT_NAME'], $_SERVER['REQUEST_URI']);
            if (substr($search, -1) == '/') {
                $search = substr($search, 0, -1);
            }
            $search = array($search);
            if (!empty($_SERVER['QUERY_STRING'])) {
                                                                $url = parse_url($_SERVER['REQUEST_URI']);
                if (!empty($url['query'])) {
                    $search[] = '?' . $url['query'];
                }
            }
            $path = str_replace($search, '', $_SERVER['REQUEST_URI']);
            if ($path == '/') {
                $path = '';
            }
            return $path;
        }

        return '';
    }

}
