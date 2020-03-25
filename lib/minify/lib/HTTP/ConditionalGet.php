<?php



class HTTP_ConditionalGet {

    
    public $cacheIsValid = null;

    
    public function __construct($spec)
    {
        $scope = (isset($spec['isPublic']) && $spec['isPublic'])
            ? 'public'
            : 'private';
        $maxAge = 0;
                if (isset($spec['setExpires']) 
            && is_numeric($spec['setExpires'])
            && ! isset($spec['maxAge'])) {
            $spec['maxAge'] = $spec['setExpires'] - $_SERVER['REQUEST_TIME'];
        }
        if (isset($spec['maxAge'])) {
            $maxAge = $spec['maxAge'];
            $this->_headers['Expires'] = self::gmtDate(
                $_SERVER['REQUEST_TIME'] + $spec['maxAge'] 
            );
        }
        $etagAppend = '';
        if (isset($spec['encoding'])) {
            $this->_stripEtag = true;
            $this->_headers['Vary'] = 'Accept-Encoding';
            if ('' !== $spec['encoding']) {
                if (0 === strpos($spec['encoding'], 'x-')) {
                    $spec['encoding'] = substr($spec['encoding'], 2);
                }
                $etagAppend = ';' . substr($spec['encoding'], 0, 2);
            }
        }
        if (isset($spec['lastModifiedTime'])) {
            $this->_setLastModified($spec['lastModifiedTime']);
            if (isset($spec['eTag'])) {                 $this->_setEtag($spec['eTag'], $scope);
            } else {                 $this->_setEtag($spec['lastModifiedTime'] . $etagAppend, $scope);
            }
        } elseif (isset($spec['eTag'])) {             $this->_setEtag($spec['eTag'], $scope);
        } elseif (isset($spec['contentHash'])) {             $this->_setEtag($spec['contentHash'] . $etagAppend, $scope);
        }
        $privacy = ($scope === 'private')
            ? ', private'
            : '';
        $this->_headers['Cache-Control'] = "max-age={$maxAge}{$privacy}";
                $this->cacheIsValid = (isset($spec['invalidate']) && $spec['invalidate'])
            ? false
            : $this->_isCacheValid();
    }
    
    
    public function getHeaders()
    {
        return $this->_headers;
    }

    
    public function setContentLength($bytes)
    {
        return $this->_headers['Content-Length'] = $bytes;
    }

    
    public function sendHeaders()
    {
        $headers = $this->_headers;
        if (array_key_exists('_responseCode', $headers)) {
                        list(, $code) = explode(' ', $headers['_responseCode'], 3);
            header($headers['_responseCode'], true, $code);
            unset($headers['_responseCode']);
        }
        foreach ($headers as $name => $val) {
            header($name . ': ' . $val);
        }
    }
    
    
    public static function check($lastModifiedTime = null, $isPublic = false, $options = array())
    {
        if (null !== $lastModifiedTime) {
            $options['lastModifiedTime'] = (int)$lastModifiedTime;
        }
        $options['isPublic'] = (bool)$isPublic;
        $cg = new HTTP_ConditionalGet($options);
        $cg->sendHeaders();
        if ($cg->cacheIsValid) {
            exit();
        }
    }
    
    
    
    public static function gmtDate($time)
    {
        return gmdate('D, d M Y H:i:s \G\M\T', $time);
    }
    
    protected $_headers = array();
    protected $_lmTime = null;
    protected $_etag = null;
    protected $_stripEtag = false;

    
    protected function _setEtag($hash, $scope)
    {
        $this->_etag = '"' . substr($scope, 0, 3) . $hash . '"';
        $this->_headers['ETag'] = $this->_etag;
    }

    
    protected function _setLastModified($time)
    {
        $this->_lmTime = (int)$time;
        $this->_headers['Last-Modified'] = self::gmtDate($time);
    }

    
    protected function _isCacheValid()
    {
        if (null === $this->_etag) {
                                                return false;
        }
        $isValid = ($this->resourceMatchedEtag() || $this->resourceNotModified());
        if ($isValid) {
            $this->_headers['_responseCode'] = 'HTTP/1.0 304 Not Modified';
        }
        return $isValid;
    }

    
    protected function resourceMatchedEtag()
    {
        if (!isset($_SERVER['HTTP_IF_NONE_MATCH'])) {
            return false;
        }
        $clientEtagList = get_magic_quotes_gpc()
            ? stripslashes($_SERVER['HTTP_IF_NONE_MATCH'])
            : $_SERVER['HTTP_IF_NONE_MATCH'];
        $clientEtags = explode(',', $clientEtagList);
        
        $compareTo = $this->normalizeEtag($this->_etag);
        foreach ($clientEtags as $clientEtag) {
            if ($this->normalizeEtag($clientEtag) === $compareTo) {
                                                $this->_headers['ETag'] = trim($clientEtag);
                return true;
            }
        }
        return false;
    }

    
    protected function normalizeEtag($etag) {
        $etag = trim($etag);
        return $this->_stripEtag
            ? preg_replace('/;\\w\\w"$/', '"', $etag)
            : $etag;
    }

    
    protected function resourceNotModified()
    {
        if (!isset($_SERVER['HTTP_IF_MODIFIED_SINCE'])) {
            return false;
        }
                list($ifModifiedSince) = explode(';', $_SERVER['HTTP_IF_MODIFIED_SINCE'], 2);
        if (strtotime($ifModifiedSince) >= $this->_lmTime) {
                                    $this->_headers['ETag'] = $this->normalizeEtag($this->_etag);
            return true;
        }
        return false;
    }
}
