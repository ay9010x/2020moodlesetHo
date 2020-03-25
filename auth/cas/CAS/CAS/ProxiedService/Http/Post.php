<?php




class CAS_ProxiedService_Http_Post
extends CAS_ProxiedService_Http_Abstract
{

    
    private $_contentType;

    
    private $_body;

    
    public function setContentType ($contentType)
    {
        if ($this->hasBeenSent()) {
            throw new CAS_OutOfSequenceException(
                'Cannot set the content type, request already sent.'
            );
        }

        $this->_contentType = $contentType;
    }

    
    public function setBody ($body)
    {
        if ($this->hasBeenSent()) {
            throw new CAS_OutOfSequenceException(
                'Cannot set the body, request already sent.'
            );
        }

        $this->_body = $body;
    }

    
    protected function populateRequest (CAS_Request_RequestInterface $request)
    {
        if (empty($this->_contentType) && !empty($this->_body)) {
            throw new CAS_ProxiedService_Exception(
                "If you pass a POST body, you must specify a content type via "
                .get_class($this).'->setContentType($contentType).'
            );
        }

        $request->makePost();
        if (!empty($this->_body)) {
            $request->addHeader('Content-Type: '.$this->_contentType);
            $request->addHeader('Content-Length: '.strlen($this->_body));
            $request->setPostBody($this->_body);
        }
    }


}
?>
