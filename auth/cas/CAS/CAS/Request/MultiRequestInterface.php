<?php




interface CAS_Request_MultiRequestInterface
{

    

    
    public function addRequest (CAS_Request_RequestInterface $request);

    
    public function getNumRequests ();

    

    
    public function send ();
}
