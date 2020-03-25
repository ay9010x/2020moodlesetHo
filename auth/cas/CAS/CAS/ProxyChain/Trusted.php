<?php




class CAS_ProxyChain_Trusted
extends CAS_ProxyChain
implements CAS_ProxyChain_Interface
{

    
    protected function isSizeValid (array $list)
    {
        return (sizeof($this->chain) <= sizeof($list));
    }

}
