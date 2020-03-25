<?php




abstract class CAS_ProxiedService_Http_Abstract extends
CAS_ProxiedService_Abstract implements CAS_ProxiedService_Http
{
    
    protected $requestHandler;

    
    private $_cookieJar;

    
    public function __construct(CAS_Request_RequestInterface $requestHandler,
        CAS_CookieJar $cookieJar
    ) {
        $this->requestHandler = $requestHandler;
        $this->_cookieJar = $cookieJar;
    }

    
    private $_url;

    
    public function getServiceUrl()
    {
        if (empty($this->_url)) {
            throw new CAS_ProxiedService_Exception(
                'No URL set via ' . get_class($this) . '->setUrl($url).'
            );
        }

        return $this->_url;
    }

    

    
    public function setUrl($url)
    {
        if ($this->hasBeenSent()) {
            throw new CAS_OutOfSequenceException(
                'Cannot set the URL, request already sent.'
            );
        }
        if (!is_string($url)) {
            throw new CAS_InvalidArgumentException('$url must be a string.');
        }

        $this->_url = $url;
    }

    

    
    public function send()
    {
        if ($this->hasBeenSent()) {
            throw new CAS_OutOfSequenceException(
                'Cannot send, request already sent.'
            );
        }

        phpCAS::traceBegin();

                $this->initializeProxyTicket();
        $url = $this->getServiceUrl();
        if (strstr($url, '?') === false) {
            $url = $url . '?ticket=' . $this->getProxyTicket();
        } else {
            $url = $url . '&ticket=' . $this->getProxyTicket();
        }

        try {
            $this->makeRequest($url);
        } catch (Exception $e) {
            phpCAS::traceEnd();
            throw $e;
        }
    }

    
    private $_numRequests = 0;

    
    private $_responseHeaders = array();

    
    private $_responseStatusCode = '';

    
    private $_responseBody = '';

    
    protected function makeRequest($url)
    {
                $this->_numRequests++;
        if ($this->_numRequests > 4) {
            $message = 'Exceeded the maximum number of redirects (3) in proxied service request.';
            phpCAS::trace($message);
            throw new CAS_ProxiedService_Exception($message);
        }

                $request = clone $this->requestHandler;
        $request->setUrl($url);

                $request->addCookies($this->_cookieJar->getCookies($url));

                $this->populateRequest($request);

                phpCAS::trace('Performing proxied service request to \'' . $url . '\'');
        if (!$request->send()) {
            $message = 'Could not perform proxied service request to URL`'
            . $url . '\'. ' . $request->getErrorMessage();
            phpCAS::trace($message);
            throw new CAS_ProxiedService_Exception($message);
        }

                $this->_cookieJar->storeCookies($url, $request->getResponseHeaders());

                if ($redirectUrl = $this->getRedirectUrl($request->getResponseHeaders())
        ) {
            phpCAS::trace('Found redirect:' . $redirectUrl);
            $this->makeRequest($redirectUrl);
        } else {

            $this->_responseHeaders = $request->getResponseHeaders();
            $this->_responseBody = $request->getResponseBody();
            $this->_responseStatusCode = $request->getResponseStatusCode();
        }
    }

    
    abstract protected function populateRequest(
        CAS_Request_RequestInterface $request
    );

    
    protected function getRedirectUrl(array $responseHeaders)
    {
                foreach ($responseHeaders as $header) {
            if ( preg_match('/^(Location:|URI:)\s*([^\s]+.*)$/', $header, $matches)
            ) {
                return trim(array_pop($matches));
            }
        }
        return null;
    }

    

    
    protected function hasBeenSent()
    {
        return ($this->_numRequests > 0);
    }

    
    public function getResponseHeaders()
    {
        if (!$this->hasBeenSent()) {
            throw new CAS_OutOfSequenceException(
                'Cannot access response, request not sent yet.'
            );
        }

        return $this->_responseHeaders;
    }

    
    public function getResponseStatusCode()
    {
        if (!$this->hasBeenSent()) {
            throw new CAS_OutOfSequenceException(
                'Cannot access response, request not sent yet.'
            );
        }

        return $this->_responseStatusCode;
    }

    
    public function getResponseBody()
    {
        if (!$this->hasBeenSent()) {
            throw new CAS_OutOfSequenceException(
                'Cannot access response, request not sent yet.'
            );
        }

        return $this->_responseBody;
    }

    
    public function getCookies()
    {
        return $this->_cookieJar->getCookies($this->getServiceUrl());
    }

}
?>
