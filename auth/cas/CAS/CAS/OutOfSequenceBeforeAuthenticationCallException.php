<?php




class CAS_OutOfSequenceBeforeAuthenticationCallException
extends CAS_OutOfSequenceException
implements CAS_Exception
{
    
    public function __construct ()
    {
        parent::__construct('An authentication call hasn\'t happened yet.');
    }
}
