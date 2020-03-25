<?php



class Minify_Source {

    
    public $lastModified = null;
    
    
    public $minifier = null;
    
    
    public $minifyOptions = null;

    
    public $filepath = null;
    
    
    public $contentType = null;
    
    
    public function __construct($spec)
    {
        if (isset($spec['filepath'])) {
            if (0 === strpos($spec['filepath'], '//')) {
                $spec['filepath'] = $_SERVER['DOCUMENT_ROOT'] . substr($spec['filepath'], 1);
            }
            $segments = explode('.', $spec['filepath']);
            $ext = strtolower(array_pop($segments));
            switch ($ext) {
            case 'js'   : $this->contentType = 'application/x-javascript';
                          break;
            case 'css'  : $this->contentType = 'text/css';
                          break;
            case 'htm'  :             case 'html' : $this->contentType = 'text/html';
                          break;
            }
            $this->filepath = $spec['filepath'];
            $this->_id = $spec['filepath'];
            $this->lastModified = filemtime($spec['filepath'])
                                + round(Minify::$uploaderHoursBehind * 3600);
        } elseif (isset($spec['id'])) {
            $this->_id = 'id::' . $spec['id'];
            if (isset($spec['content'])) {
                $this->_content = $spec['content'];
            } else {
                $this->_getContentFunc = $spec['getContentFunc'];
            }
            $this->lastModified = isset($spec['lastModified'])
                ? $spec['lastModified']
                : time();
        }
        if (isset($spec['contentType'])) {
            $this->contentType = $spec['contentType'];
        }
        if (isset($spec['minifier'])) {
            $this->minifier = $spec['minifier'];
        }
        if (isset($spec['minifyOptions'])) {
            $this->minifyOptions = $spec['minifyOptions'];
        }
    }
    
    
    public function getContent()
    {
        $content = (null !== $this->filepath)
            ? file_get_contents($this->filepath)
            : ((null !== $this->_content)
                ? $this->_content
                : call_user_func($this->_getContentFunc, $this->_id)
            );
                return (pack("CCC",0xef,0xbb,0xbf) === substr($content, 0, 3))
            ? substr($content, 3)
            : $content;
    }
    
    
    public function getId()
    {
        return $this->_id;
    }
    
    
    public static function haveNoMinifyPrefs($sources)
    {
        foreach ($sources as $source) {
            if (null !== $source->minifier
                || null !== $source->minifyOptions) {
                return false;
            }
        }
        return true;
    }
    
    
    public static function getDigest($sources)
    {
        foreach ($sources as $source) {
            $info[] = array(
                $source->_id, $source->minifier, $source->minifyOptions
            );
        }
        return md5(serialize($info));
    }
    
    
    public static function getContentType($sources)
    {
        foreach ($sources as $source) {
            if ($source->contentType !== null) {
                return $source->contentType;
            }
        }
        return 'text/plain';
    }
    
    protected $_content = null;
    protected $_getContentFunc = null;
    protected $_id = null;
}

