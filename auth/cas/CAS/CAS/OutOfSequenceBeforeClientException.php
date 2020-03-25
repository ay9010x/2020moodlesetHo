<?php




class CAS_OutOfSequenceBeforeClientException
extends CAS_OutOfSequenceException
implements CAS_Exception
{
    
    public function __construct ()
    {
        parent::__construct(
            'this method cannot be called before phpCAS::client() or phpCAS::proxy()'
        );
    }
}
