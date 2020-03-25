<?php




interface CAS_Request_RequestInterface
{

    

    
    public function setUrl ($url);

    
    public function addCookie ($name, $value);

    
    public function addCookies (array $cookies);

    
    public function addHeader ($header);

    
    public function addHeaders (array $headers);

    
    public function makePost ();

    
    public function setPostBody ($body);


    
    public function setSslCaCert ($caCertPath, $validate_cn = true);



    

    
    public function send ();

    

    
    public function getResponseHeaders ();

    
    public function getResponseStatusCode ();

    
    public function getResponseBody ();

    
    public function getErrorMessage ();
}
