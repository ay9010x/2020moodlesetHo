<?php

 

class HTTP_Encoder {

    
    public static $encodeToIe6 = true;
    
    
    
    public static $compressionLevel = 6;
    

    
    public function __construct($spec) 
    {
        $this->_useMbStrlen = (function_exists('mb_strlen')
                               && (ini_get('mbstring.func_overload') !== '')
                               && ((int)ini_get('mbstring.func_overload') & 2));
        $this->_content = $spec['content'];
        $this->_headers['Content-Length'] = $this->_useMbStrlen
            ? (string)mb_strlen($this->_content, '8bit')
            : (string)strlen($this->_content);
        if (isset($spec['type'])) {
            $this->_headers['Content-Type'] = $spec['type'];
        }
        if (isset($spec['method'])
            && in_array($spec['method'], array('gzip', 'deflate', 'compress', '')))
        {
            $this->_encodeMethod = array($spec['method'], $spec['method']);
        } else {
            $this->_encodeMethod = self::getAcceptedEncoding();
        }
    }

    
    public function getContent() 
    {
        return $this->_content;
    }
    
    
    public function getHeaders()
    {
        return $this->_headers;
    }

    
    public function sendHeaders()
    {
        foreach ($this->_headers as $name => $val) {
            header($name . ': ' . $val);
        }
    }
    
    
    public function sendAll()
    {
        $this->sendHeaders();
        echo $this->_content;
    }

    
    public static function getAcceptedEncoding($allowCompress = true, $allowDeflate = true)
    {
                
        if (! isset($_SERVER['HTTP_ACCEPT_ENCODING'])
            || self::isBuggyIe())
        {
            return array('', '');
        }
        $ae = $_SERVER['HTTP_ACCEPT_ENCODING'];
                if (0 === strpos($ae, 'gzip,')                         || 0 === strpos($ae, 'deflate, gzip,')         ) {
            return array('gzip', 'gzip');
        }
                if (preg_match(
                '@(?:^|,)\\s*((?:x-)?gzip)\\s*(?:$|,|;\\s*q=(?:0\\.|1))@'
                ,$ae
                ,$m)) {
            return array('gzip', $m[1]);
        }
        if ($allowDeflate) {
                        $aeRev = strrev($ae);
            if (0 === strpos($aeRev, 'etalfed ,')                 || 0 === strpos($aeRev, 'etalfed,')                 || 0 === strpos($ae, 'deflate,')                                 || preg_match(
                    '@(?:^|,)\\s*deflate\\s*(?:$|,|;\\s*q=(?:0\\.|1))@', $ae)) {
                return array('deflate', 'deflate');
            }
        }
        if ($allowCompress && preg_match(
                '@(?:^|,)\\s*((?:x-)?compress)\\s*(?:$|,|;\\s*q=(?:0\\.|1))@'
                ,$ae
                ,$m)) {
            return array('compress', $m[1]);
        }
        return array('', '');
    }

    
    public function encode($compressionLevel = null)
    {
        if (! self::isBuggyIe()) {
            $this->_headers['Vary'] = 'Accept-Encoding';
        }
        if (null === $compressionLevel) {
            $compressionLevel = self::$compressionLevel;
        }
        if ('' === $this->_encodeMethod[0]
            || ($compressionLevel == 0)
            || !extension_loaded('zlib'))
        {
            return false;
        }
        if ($this->_encodeMethod[0] === 'deflate') {
            $encoded = gzdeflate($this->_content, $compressionLevel);
        } elseif ($this->_encodeMethod[0] === 'gzip') {
            $encoded = gzencode($this->_content, $compressionLevel);
        } else {
            $encoded = gzcompress($this->_content, $compressionLevel);
        }
        if (false === $encoded) {
            return false;
        }
        $this->_headers['Content-Length'] = $this->_useMbStrlen
            ? (string)mb_strlen($encoded, '8bit')
            : (string)strlen($encoded);
        $this->_headers['Content-Encoding'] = $this->_encodeMethod[1];
        $this->_content = $encoded;
        return true;
    }
    
    
    public static function output($content, $compressionLevel = null)
    {
        if (null === $compressionLevel) {
            $compressionLevel = self::$compressionLevel;
        }
        $he = new HTTP_Encoder(array('content' => $content));
        $ret = $he->encode($compressionLevel);
        $he->sendAll();
        return $ret;
    }

    
    public static function isBuggyIe()
    {
        if (empty($_SERVER['HTTP_USER_AGENT'])) {
            return false;
        }
        $ua = $_SERVER['HTTP_USER_AGENT'];
                if (0 !== strpos($ua, 'Mozilla/4.0 (compatible; MSIE ')
            || false !== strpos($ua, 'Opera')) {
            return false;
        }
                $version = (float)substr($ua, 30);
        return self::$encodeToIe6
            ? ($version < 6 || ($version == 6 && false === strpos($ua, 'SV1')))
            : ($version < 7);
    }
    
    protected $_content = '';
    protected $_headers = array();
    protected $_encodeMethod = array('', '');
    protected $_useMbStrlen = false;
}
