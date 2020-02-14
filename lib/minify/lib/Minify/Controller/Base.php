<?php



abstract class Minify_Controller_Base {
    
    
    abstract public function setupSources($options);
    
    
    public function getDefaultMinifyOptions() {
        return array(
            'isPublic' => true
            ,'encodeOutput' => function_exists('gzdeflate')
            ,'encodeMethod' => null             ,'encodeLevel' => 9
            ,'minifierOptions' => array()             ,'contentTypeCharset' => 'utf-8'
            ,'maxAge' => 1800             ,'rewriteCssUris' => true
            ,'bubbleCssImports' => false
            ,'quiet' => false             ,'debug' => false
            
                                    ,'badRequestHeader' => 'HTTP/1.0 400 Bad Request'
            ,'errorHeader'      => 'HTTP/1.0 500 Internal Server Error'
            
                        ,'postprocessor' => null
                        ,'postprocessorRequire' => null
        );
    }  

    
    public function getDefaultMinifers() {
        $ret[Minify::TYPE_JS] = array('JSMin', 'minify');
        $ret[Minify::TYPE_CSS] = array('Minify_CSS', 'minify');
        $ret[Minify::TYPE_HTML] = array('Minify_HTML', 'minify');
        return $ret;
    }
    
    
    public static function _fileIsSafe($file, $safeDirs)
    {
        $pathOk = false;
        foreach ((array)$safeDirs as $safeDir) {
            if (strpos($file, $safeDir) === 0) {
                $pathOk = true;
                break;
            }
        }
        $base = basename($file);
        if (! $pathOk || ! is_file($file) || $base[0] === '.') {
            return false;
        }
        list($revExt) = explode('.', strrev($base));
        return in_array(strrev($revExt), array('js', 'css', 'html', 'txt'));
    }

    
    public static function checkAllowDirs($file, $allowDirs, $uri)
    {
        foreach ((array)$allowDirs as $allowDir) {
            if (strpos($file, $allowDir) === 0) {
                return true;
            }
        }
        throw new Exception("File '$file' is outside \$allowDirs. If the path is"
            . " resolved via an alias/symlink, look into the \$min_symlinks option."
            . " E.g. \$min_symlinks['/" . dirname($uri) . "'] = '" . dirname($file) . "';");
    }

    
    public static function checkNotHidden($file)
    {
        $b = basename($file);
        if (0 === strpos($b, '.')) {
            throw new Exception("Filename '$b' starts with period (may be hidden)");
        }
    }

    
    public $sources = array();
    
    
    public $selectionId = '';

    
    public final function mixInDefaultOptions($options)
    {
        $ret = array_merge(
            $this->getDefaultMinifyOptions(), $options
        );
        if (! isset($options['minifiers'])) {
            $options['minifiers'] = array();
        }
        $ret['minifiers'] = array_merge(
            $this->getDefaultMinifers(), $options['minifiers']
        );
        return $ret;
    }
    
    
    public final function analyzeSources($options = array()) 
    {
        if ($this->sources) {
            if (! isset($options['contentType'])) {
                $options['contentType'] = Minify_Source::getContentType($this->sources);
            }
                        if (! isset($options['lastModifiedTime'])) {
                $max = 0;
                foreach ($this->sources as $source) {
                    $max = max($source->lastModified, $max);
                }
                $options['lastModifiedTime'] = $max;
            }    
        }
        return $options;
    }

    
    public function log($msg) {
        Minify_Logger::log($msg);
    }
}
