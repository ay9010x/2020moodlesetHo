<?php



class Horde_Imap_Client_Socket_Connection_Base extends Horde\Socket\Client
{
    
    protected $_protocol = 'imap';

    
    protected function _connect($host, $port, $timeout, $secure, $retries = 0)
    {
        if ($retries || !$this->_params['debug']->debug) {
            $timer = null;
        } else {
            $url = new Horde_Imap_Client_Url();
            $url->hostspec = $host;
            $url->port = $port;
            $url->protocol = $this->_protocol;
            $this->_params['debug']->info(sprintf(
                'Connection to: %s',
                strval($url)
            ));

            $timer = new Horde_Support_Timer();
            $timer->push();
        }

        parent::_connect($host, $port, $timeout, $secure, $retries);

        if ($timer) {
            $this->_params['debug']->info(sprintf(
                'Server connection took %s seconds.',
                round($timer->pop(), 4)
            ));
        }
    }

}
