<?php




abstract class CAS_Request_AbstractRequest
implements CAS_Request_RequestInterface
{

    protected $url = null;
    protected $cookies = array();
    protected $headers = array();
    protected $isPost = false;
    protected $postBody = null;
    protected $caCertPath = null;
    protected $validateCN = true;
    private $_sent = false;
    private $_responseHeaders = array();
    private $_responseBody = null;
    private $_errorMessage = '';

    

    
    public function setUrl ($url)
    {
        if ($this->_sent) {
            throw new CAS_OutOfSequenceException(
                'Request has already been sent cannot '.__METHOD__
            );
        }

        $this->url = $url;
    }

    
    public function addCookie ($name, $value)
    {
        if ($this->_sent) {
            throw new CAS_OutOfSequenceException(
                'Request has already been sent cannot '.__METHOD__
            );
        }

        $this->cookies[$name] = $value;
    }

    
    public function addCookies (array $cookies)
    {
        if ($this->_sent) {
            throw new CAS_OutOfSequenceException(
                'Request has already been sent cannot '.__METHOD__
            );
        }

        $this->cookies = array_merge($this->cookies, $cookies);
    }

    
    public function addHeader ($header)
    {
        if ($this->_sent) {
            throw new CAS_OutOfSequenceException(
                'Request has already been sent cannot '.__METHOD__
            );
        }

        $this->headers[] = $header;
    }

    
    public function addHeaders (array $headers)
    {
        if ($this->_sent) {
            throw new CAS_OutOfSequenceException(
                'Request has already been sent cannot '.__METHOD__
            );
        }

        $this->headers = array_merge($this->headers, $headers);
    }

    
    public function makePost ()
    {
        if ($this->_sent) {
            throw new CAS_OutOfSequenceException(
                'Request has already been sent cannot '.__METHOD__
            );
        }

        $this->isPost = true;
    }

    
    public function setPostBody ($body)
    {
        if ($this->_sent) {
            throw new CAS_OutOfSequenceException(
                'Request has already been sent cannot '.__METHOD__
            );
        }
        if (!$this->isPost) {
            throw new CAS_OutOfSequenceException(
                'Cannot add a POST body to a GET request, use makePost() first.'
            );
        }

        $this->postBody = $body;
    }

    
    public function setSslCaCert ($caCertPath,$validate_cn=true)
    {
        if ($this->_sent) {
            throw new CAS_OutOfSequenceException(
                'Request has already been sent cannot '.__METHOD__
            );
        }
        $this->caCertPath = $caCertPath;
        $this->validateCN = $validate_cn;
    }

    

    
    public function send ()
    {
        if ($this->_sent) {
            throw new CAS_OutOfSequenceException(
                'Request has already been sent cannot send again.'
            );
        }
        if (is_null($this->url) || !$this->url) {
            throw new CAS_OutOfSequenceException(
                'A url must be specified via setUrl() before the request can be sent.'
            );
        }
        $this->_sent = true;
        return $this->sendRequest();
    }

    
    abstract protected function sendRequest ();

    
    protected function storeResponseHeaders (array $headers)
    {
        $this->_responseHeaders = array_merge($this->_responseHeaders, $headers);
    }

    
    protected function storeResponseHeader ($header)
    {
        $this->_responseHeaders[] = $header;
    }

    
    protected function storeResponseBody ($body)
    {
        $this->_responseBody = $body;
    }

    
    protected function storeErrorMessage ($message)
    {
        $this->_errorMessage .= $message;
    }

    

    
    public function getResponseHeaders ()
    {
        if (!$this->_sent) {
            throw new CAS_OutOfSequenceException(
                'Request has not been sent yet. Cannot '.__METHOD__
            );
        }
        return $this->_responseHeaders;
    }

    
    public function getResponseStatusCode ()
    {
        if (!$this->_sent) {
            throw new CAS_OutOfSequenceException(
                'Request has not been sent yet. Cannot '.__METHOD__
            );
        }

        if (!preg_match(
            '/HTTP\/[0-9.]+\s+([0-9]+)\s*(.*)/',
            $this->_responseHeaders[0], $matches
        )
        ) {
            throw new CAS_Request_Exception(
                'Bad response, no status code was found in the first line.'
            );
        }

        return intval($matches[1]);
    }

    
    public function getResponseBody ()
    {
        if (!$this->_sent) {
            throw new CAS_OutOfSequenceException(
                'Request has not been sent yet. Cannot '.__METHOD__
            );
        }

        return $this->_responseBody;
    }

    
    public function getErrorMessage ()
    {
        if (!$this->_sent) {
            throw new CAS_OutOfSequenceException(
                'Request has not been sent yet. Cannot '.__METHOD__
            );
        }
        return $this->_errorMessage;
    }
}
