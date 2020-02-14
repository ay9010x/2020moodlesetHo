<?php




abstract class CAS_ProxiedService_Abstract
implements CAS_ProxiedService, CAS_ProxiedService_Testable
{

    
    private $_proxyTicket;

    
    public function setProxyTicket ($proxyTicket)
    {
        if (empty($proxyTicket)) {
            throw new CAS_InvalidArgumentException(
                'Trying to initialize with an empty proxy ticket.'
            );
        }
        if (!empty($this->_proxyTicket)) {
            throw new CAS_OutOfSequenceException(
                'Already initialized, cannot change the proxy ticket.'
            );
        }
        $this->_proxyTicket = $proxyTicket;
    }

    
    protected function getProxyTicket ()
    {
        if (empty($this->_proxyTicket)) {
            throw new CAS_OutOfSequenceException(
                'No proxy ticket yet. Call $this->initializeProxyTicket() to aquire the proxy ticket.'
            );
        }

        return $this->_proxyTicket;
    }

    
    private $_casClient;

    
    public function setCasClient (CAS_Client $casClient)
    {
        if (!empty($this->_proxyTicket)) {
            throw new CAS_OutOfSequenceException(
                'Already initialized, cannot change the CAS_Client.'
            );
        }

        $this->_casClient = $casClient;
    }

    
    protected function initializeProxyTicket()
    {
        if (!empty($this->_proxyTicket)) {
            throw new CAS_OutOfSequenceException(
                'Already initialized, cannot initialize again.'
            );
        }
                if (empty($this->_casClient)) {
            phpCAS::initializeProxiedService($this);
        } else {
            $this->_casClient->initializeProxiedService($this);
        }
    }

}
?>
