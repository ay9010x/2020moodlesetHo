<?php




interface CAS_ProxiedService_Http
{

    

    
    public function setUrl ($url);

    

    
    public function send ();

    

    
    public function getResponseHeaders ();

    
    public function getResponseBody ();

}
?>
