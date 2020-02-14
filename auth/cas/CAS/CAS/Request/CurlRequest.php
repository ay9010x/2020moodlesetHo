<?php




class CAS_Request_CurlRequest
extends CAS_Request_AbstractRequest
implements CAS_Request_RequestInterface
{

    
    public function setCurlOptions (array $options)
    {
        $this->_curlOptions = $options;
    }
    private $_curlOptions = array();

    
    protected function sendRequest ()
    {
        phpCAS::traceBegin();

        
        $ch = $this->_initAndConfigure();

        
        $buf = curl_exec($ch);
        if ( $buf === false ) {
            phpCAS::trace('curl_exec() failed');
            $this->storeErrorMessage(
                'CURL error #'.curl_errno($ch).': '.curl_error($ch)
            );
            $res = false;
        } else {
            $this->storeResponseBody($buf);
            phpCAS::trace("Response Body: \n".$buf."\n");
            $res = true;

        }
                curl_close($ch);

        phpCAS::traceEnd($res);
        return $res;
    }

    
    private function _initAndConfigure()
    {
        
        $ch = curl_init($this->url);

        if (version_compare(PHP_VERSION, '5.1.3', '>=')) {
                        curl_setopt_array($ch, $this->_curlOptions);
        } else {
            foreach ($this->_curlOptions as $key => $value) {
                curl_setopt($ch, $key, $value);
            }
        }

        
        if ($this->caCertPath) {
            if ($this->validateCN) {
                curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 2);
            } else {
                curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
            }
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 1);
            curl_setopt($ch, CURLOPT_CAINFO, $this->caCertPath);
            phpCAS::trace('CURL: Set CURLOPT_CAINFO ' . $this->caCertPath);
        } else {
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
            curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
        }

        
                curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);

                curl_setopt($ch, CURLOPT_HEADERFUNCTION, array($this, '_curlReadHeaders'));

        
        if (count($this->cookies)) {
            $cookieStrings = array();
            foreach ($this->cookies as $name => $val) {
                $cookieStrings[] = $name.'='.$val;
            }
            curl_setopt($ch, CURLOPT_COOKIE, implode(';', $cookieStrings));
        }

        
        if (count($this->headers)) {
            curl_setopt($ch, CURLOPT_HTTPHEADER, $this->headers);
        }

        
        if ($this->isPost) {
            curl_setopt($ch, CURLOPT_POST, 1);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $this->postBody);
        }

        return $ch;
    }

    
    private function _storeResponseBody ($body)
    {
        $this->storeResponseBody($body);
    }

    
    private function _curlReadHeaders ($ch, $header)
    {
        $this->storeResponseHeader($header);
        return strlen($header);
    }
}
