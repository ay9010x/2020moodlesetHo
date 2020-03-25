<?php





class CAS_ProxyChain
implements CAS_ProxyChain_Interface
{

    protected $chain = array();

    
    public function __construct(array $chain)
    {
                $this->chain = array_values($chain);
    }

    
    public function matches(array $list)
    {
        $list = array_values($list);          if ($this->isSizeValid($list)) {
            $mismatch = false;
            foreach ($this->chain as $i => $search) {
                $proxy_url = $list[$i];
                if (preg_match('/^\/.*\/[ixASUXu]*$/s', $search)) {
                    if (preg_match($search, $proxy_url)) {
                        phpCAS::trace(
                            "Found regexp " .  $search . " matching " . $proxy_url
                        );
                    } else {
                        phpCAS::trace(
                            "No regexp match " .  $search . " != " . $proxy_url
                        );
                        $mismatch = true;
                        break;
                    }
                } else {
                    if (strncasecmp($search, $proxy_url, strlen($search)) == 0) {
                        phpCAS::trace(
                            "Found string " .  $search . " matching " . $proxy_url
                        );
                    } else {
                        phpCAS::trace(
                            "No match " .  $search . " != " . $proxy_url
                        );
                        $mismatch = true;
                        break;
                    }
                }
            }
            if (!$mismatch) {
                phpCAS::trace("Proxy chain matches");
                return true;
            }
        } else {
            phpCAS::trace("Proxy chain skipped: size mismatch");
        }
        return false;
    }

    
    protected function isSizeValid (array $list)
    {
        return (sizeof($this->chain) == sizeof($list));
    }
}
