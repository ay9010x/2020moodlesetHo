<?php





abstract class CAS_PGTStorage_AbstractStorage
{
    

            
    
    function __construct($cas_parent)
    {
        phpCAS::traceBegin();
        if ( !$cas_parent->isProxy() ) {
            phpCAS::error(
                'defining PGT storage makes no sense when not using a CAS proxy'
            );
        }
        phpCAS::traceEnd();
    }

            
    
    function getStorageType()
    {
        phpCAS::error(__CLASS__.'::'.__FUNCTION__.'() should never be called');
    }

    
    function getStorageInfo()
    {
        phpCAS::error(__CLASS__.'::'.__FUNCTION__.'() should never be called');
    }

            
    
    var $_error_message=false;

    
    function setErrorMessage($error_message)
    {
        $this->_error_message = $error_message;
    }

    
    function getErrorMessage()
    {
        return $this->_error_message;
    }

            
    
    var $_initialized = false;

    
    function isInitialized()
    {
        return $this->_initialized;
    }

    
    function init()
    {
        $this->_initialized = true;
    }

            
    
    function write($pgt,$pgt_iou)
    {
        phpCAS::error(__CLASS__.'::'.__FUNCTION__.'() should never be called');
    }

    
    function read($pgt_iou)
    {
        phpCAS::error(__CLASS__.'::'.__FUNCTION__.'() should never be called');
    }

    

}

?>
