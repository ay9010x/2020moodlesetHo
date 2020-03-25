<?php



class Minify_Controller_Version1 extends Minify_Controller_Base {
    
    
    public function setupSources($options) {
                if (isset($_GET['files'])) {
            $_GET['files'] = str_replace("\x00", '', (string)$_GET['files']);
        }

        self::_setupDefines();
        if (MINIFY_USE_CACHE) {
            $cacheDir = defined('MINIFY_CACHE_DIR')
                ? MINIFY_CACHE_DIR
                : '';
            Minify::setCache($cacheDir);
        }
        $options['badRequestHeader'] = 'HTTP/1.0 404 Not Found';
        $options['contentTypeCharset'] = MINIFY_ENCODING;

                        if (! isset($_GET['files'])
                                    || ! preg_match('/^[^,]+\\.(css|js)(,[^,]+\\.\\1)*$/', $_GET['files'], $m)
                        || strpos($_GET['files'], '//') !== false
                        || strpos($_GET['files'], '\\') !== false
                        || preg_match('/(?:^|[^\\.])\\.\\//', $_GET['files'])
        ) {
            return $options;
        }

        $files = explode(',', $_GET['files']);
        if (count($files) > MINIFY_MAX_FILES) {
            return $options;
        }
        
                $prependRelPaths = dirname($_SERVER['SCRIPT_FILENAME'])
            . DIRECTORY_SEPARATOR;
        $prependAbsPaths = $_SERVER['DOCUMENT_ROOT'];
        
        $goodFiles = array();
        $hasBadSource = false;
        
        $allowDirs = isset($options['allowDirs'])
            ? $options['allowDirs']
            : MINIFY_BASE_DIR;
        
        foreach ($files as $file) {
                        $file = ($file[0] === '/' ? $prependAbsPaths : $prependRelPaths) . $file;
                        $file = realpath($file);
                        if (parent::_fileIsSafe($file, $allowDirs) 
                && !in_array($file, $goodFiles)) 
            {
                $goodFiles[] = $file;
                $srcOptions = array(
                    'filepath' => $file
                );
                $this->sources[] = new Minify_Source($srcOptions);
            } else {
                $hasBadSource = true;
                break;
            }
        }
        if ($hasBadSource) {
            $this->sources = array();
        }
        if (! MINIFY_REWRITE_CSS_URLS) {
            $options['rewriteCssUris'] = false;
        }
        return $options;
    }
    
    private static function _setupDefines()
    {
        $defaults = array(
            'MINIFY_BASE_DIR' => realpath($_SERVER['DOCUMENT_ROOT'])
            ,'MINIFY_ENCODING' => 'utf-8'
            ,'MINIFY_MAX_FILES' => 16
            ,'MINIFY_REWRITE_CSS_URLS' => true
            ,'MINIFY_USE_CACHE' => true
        );
        foreach ($defaults as $const => $val) {
            if (! defined($const)) {
                define($const, $val);
            }
        }
    }
}

