<?php



class Minify_HTML_Helper {
    public $rewriteWorks = true;
    public $minAppUri = '/min';
    public $groupsConfigFile = '';

    
    public static function getUri($keyOrFiles, $opts = array())
    {
        $opts = array_merge(array(             'farExpires' => true
            ,'debug' => false
            ,'charset' => 'UTF-8'
            ,'minAppUri' => '/min'
            ,'rewriteWorks' => true
            ,'groupsConfigFile' => ''
        ), $opts);
        $h = new self;
        $h->minAppUri = $opts['minAppUri'];
        $h->rewriteWorks = $opts['rewriteWorks'];
        $h->groupsConfigFile = $opts['groupsConfigFile'];
        if (is_array($keyOrFiles)) {
            $h->setFiles($keyOrFiles, $opts['farExpires']);
        } else {
            $h->setGroup($keyOrFiles, $opts['farExpires']);
        }
        $uri = $h->getRawUri($opts['farExpires'], $opts['debug']);
        return htmlspecialchars($uri, ENT_QUOTES, $opts['charset']);
    }

    
    public function getRawUri($farExpires = true, $debug = false)
    {
        $path = rtrim($this->minAppUri, '/') . '/';
        if (! $this->rewriteWorks) {
            $path .= '?';
        }
        if (null === $this->_groupKey) {
                        $path = self::_getShortestUri($this->_filePaths, $path);
        } else {
            $path .= "g=" . $this->_groupKey;
        }
        if ($debug) {
            $path .= "&debug";
        } elseif ($farExpires && $this->_lastModified) {
            $path .= "&" . $this->_lastModified;
        }
        return $path;
    }

    
    public function setFiles($files, $checkLastModified = true)
    {
        $this->_groupKey = null;
        if ($checkLastModified) {
            $this->_lastModified = self::getLastModified($files);
        }
                foreach ($files as $k => $file) {
            if (0 === strpos($file, '//')) {
                $file = substr($file, 2);
            } elseif (0 === strpos($file, '/')
                      || 1 === strpos($file, ':\\')) {
                $file = substr($file, strlen($_SERVER['DOCUMENT_ROOT']) + 1);
            }
            $file = strtr($file, '\\', '/');
            $files[$k] = $file;
        }
        $this->_filePaths = $files;
    }

    
    public function setGroup($key, $checkLastModified = true)
    {
        $this->_groupKey = $key;
        if ($checkLastModified) {
            if (! $this->groupsConfigFile) {
                $this->groupsConfigFile = dirname(dirname(dirname(dirname(__FILE__)))) . '/groupsConfig.php';
            }
            if (is_file($this->groupsConfigFile)) {
                $gc = (require $this->groupsConfigFile);
                $keys = explode(',', $key);
                foreach ($keys as $key) {
                    if (isset($gc[$key])) {
                        $this->_lastModified = self::getLastModified($gc[$key], $this->_lastModified);
                    }
                }
            }
        }
    }

    
    public static function getLastModified($sources, $lastModified = 0)
    {
        $max = $lastModified;
        foreach ((array)$sources as $source) {
            if (is_object($source) && isset($source->lastModified)) {
                $max = max($max, $source->lastModified);
            } elseif (is_string($source)) {
                if (0 === strpos($source, '//')) {
                    $source = $_SERVER['DOCUMENT_ROOT'] . substr($source, 1);
                }
                if (is_file($source)) {
                    $max = max($max, filemtime($source));
                }
            }
        }
        return $max;
    }

    protected $_groupKey = null;     protected $_filePaths = array();
    protected $_lastModified = null;

    
    
    protected static function _getCommonCharAtPos($arr, $pos) {
        if (!isset($arr[0][$pos])) {
            return '';
        }
        $c = $arr[0][$pos];
        $l = count($arr);
        if ($l === 1) {
            return $c;
        }
        for ($i = 1; $i < $l; ++$i) {
            if ($arr[$i][$pos] !== $c) {
                return '';
            }
        }
        return $c;
    }

    
    protected static function _getShortestUri($paths, $minRoot = '/min/') {
        $pos = 0;
        $base = '';
        while (true) {
            $c = self::_getCommonCharAtPos($paths, $pos);
            if ($c === '') {
                break;
            } else {
                $base .= $c;
            }
            ++$pos;
        }
        $base = preg_replace('@[^/]+$@', '', $base);
        $uri = $minRoot . 'f=' . implode(',', $paths);
        
        if (substr($base, -1) === '/') {
                        $basedPaths = $paths;
            $l = count($paths);
            for ($i = 0; $i < $l; ++$i) {
                $basedPaths[$i] = substr($paths[$i], strlen($base));
            }
            $base = substr($base, 0, strlen($base) - 1);
            $bUri = $minRoot . 'b=' . $base . '&f=' . implode(',', $basedPaths);

            $uri = strlen($uri) < strlen($bUri)
                ? $uri
                : $bUri;
        }
        return $uri;
    }
}
