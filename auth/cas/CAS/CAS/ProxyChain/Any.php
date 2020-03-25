<?php




class CAS_ProxyChain_Any
implements CAS_ProxyChain_Interface
{

    
    public function matches(array $list)
    {
        phpCAS::trace("Using CAS_ProxyChain_Any. No proxy validation is performed.");
        return true;
    }

}
