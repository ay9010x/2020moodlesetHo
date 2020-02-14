<?php




class CAS_Request_CurlMultiRequest
implements CAS_Request_MultiRequestInterface
{
    private $_requests = array();
    private $_sent = false;

    

    
    public function addRequest (CAS_Request_RequestInterface $request)
    {
        if ($this->_sent) {
            throw new CAS_OutOfSequenceException(
                'Request has already been sent cannot '.__METHOD__
            );
        }
        if (!$request instanceof CAS_Request_CurlRequest) {
            throw new CAS_InvalidArgumentException(
                'As a CAS_Request_CurlMultiRequest, I can only work with CAS_Request_CurlRequest objects.'
            );
        }

        $this->_requests[] = $request;
    }

    
    public function getNumRequests()
    {
        if ($this->_sent) {
            throw new CAS_OutOfSequenceException(
                'Request has already been sent cannot '.__METHOD__
            );
        }
        return count($this->_requests);
    }

    

    
    public function send ()
    {
        if ($this->_sent) {
            throw new CAS_OutOfSequenceException(
                'Request has already been sent cannot send again.'
            );
        }
        if (!count($this->_requests)) {
            throw new CAS_OutOfSequenceException(
                'At least one request must be added via addRequest() before the multi-request can be sent.'
            );
        }

        $this->_sent = true;

                $handles = array();
        $multiHandle = curl_multi_init();
        foreach ($this->_requests as $i => $request) {
            $handle = $request->_initAndConfigure();
            curl_setopt($handle, CURLOPT_RETURNTRANSFER, true);
            $handles[$i] = $handle;
            curl_multi_add_handle($multiHandle, $handle);
        }

                do {
            curl_multi_exec($multiHandle, $running);
        } while ($running > 0);

                foreach ($this->_requests as $i => $request) {
            $buf = curl_multi_getcontent($handles[$i]);
            $request->_storeResponseBody($buf);
            curl_multi_remove_handle($multiHandle, $handles[$i]);
            curl_close($handles[$i]);
        }

        curl_multi_close($multiHandle);
    }
}
