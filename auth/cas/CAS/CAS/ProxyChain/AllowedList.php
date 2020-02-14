<?php






class CAS_ProxyChain_AllowedList
{

    private $_chains = array();

    
    public function isProxyingAllowed()
    {
        return (count($this->_chains) > 0);
    }

    
    public function allowProxyChain(CAS_ProxyChain_Interface $chain)
    {
        $this->_chains[] = $chain;
    }

    
    public function isProxyListAllowed(array $proxies)
    {
        phpCAS::traceBegin();
        if (empty($proxies)) {
            phpCAS::trace("No proxies were found in the response");
            phpCAS::traceEnd(true);
            return true;
        } elseif (!$this->isProxyingAllowed()) {
            phpCAS::trace("Proxies are not allowed");
            phpCAS::traceEnd(false);
            return false;
        } else {
            $res = $this->contains($proxies);
            phpCAS::traceEnd($res);
            return $res;
        }
    }

    
    public function contains(array $list)
    {
        phpCAS::traceBegin();
        $count = 0;
        foreach ($this->_chains as $chain) {
            phpCAS::trace("Checking chain ". $count++);
            if ($chain->matches($list)) {
                phpCAS::traceEnd(true);
                return true;
            }
        }
        phpCAS::trace("No proxy chain matches.");
        phpCAS::traceEnd(false);
        return false;
    }
}
?>
