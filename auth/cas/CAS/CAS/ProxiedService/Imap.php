<?php




class CAS_ProxiedService_Imap
extends CAS_ProxiedService_Abstract
{

    
    private $_username;

    
    public function __construct ($username)
    {
        if (!is_string($username) || !strlen($username)) {
            throw new CAS_InvalidArgumentException('Invalid username.');
        }

        $this->_username = $username;
    }

    
    private $_url;

    
    public function getServiceUrl ()
    {
        if (empty($this->_url)) {
            throw new CAS_ProxiedService_Exception(
                'No URL set via '.get_class($this).'->getServiceUrl($url).'
            );
        }

        return $this->_url;
    }

    

    
    public function setServiceUrl ($url)
    {
        if ($this->hasBeenOpened()) {
            throw new CAS_OutOfSequenceException(
                'Cannot set the URL, stream already opened.'
            );
        }
        if (!is_string($url) || !strlen($url)) {
            throw new CAS_InvalidArgumentException('Invalid url.');
        }

        $this->_url = $url;
    }

    
    private $_mailbox;

    
    public function setMailbox ($mailbox)
    {
        if ($this->hasBeenOpened()) {
            throw new CAS_OutOfSequenceException(
                'Cannot set the mailbox, stream already opened.'
            );
        }
        if (!is_string($mailbox) || !strlen($mailbox)) {
            throw new CAS_InvalidArgumentException('Invalid mailbox.');
        }

        $this->_mailbox = $mailbox;
    }

    
    private $_options = null;

    
    public function setOptions ($options)
    {
        if ($this->hasBeenOpened()) {
            throw new CAS_OutOfSequenceException(
                'Cannot set options, stream already opened.'
            );
        }
        if (!is_int($options)) {
            throw new CAS_InvalidArgumentException('Invalid options.');
        }

        $this->_options = $options;
    }

    

    
    public function open ()
    {
        if ($this->hasBeenOpened()) {
            throw new CAS_OutOfSequenceException('Stream already opened.');
        }
        if (empty($this->_mailbox)) {
            throw new CAS_ProxiedService_Exception(
                'You must specify a mailbox via '.get_class($this)
                .'->setMailbox($mailbox)'
            );
        }

        phpCAS::traceBegin();

                $this->initializeProxyTicket();
        phpCAS::trace('opening IMAP mailbox `'.$this->_mailbox.'\'...');
        $this->_stream = @imap_open(
            $this->_mailbox, $this->_username, $this->getProxyTicket(),
            $this->_options
        );
        if ($this->_stream) {
            phpCAS::trace('ok');
        } else {
            phpCAS::trace('could not open mailbox');
                        $message = 'IMAP Error: '.$this->_url.' '. var_export(imap_errors(), true);
            phpCAS::trace($message);
            throw new CAS_ProxiedService_Exception($message);
        }

        phpCAS::traceEnd();
        return $this->_stream;
    }

    
    protected function hasBeenOpened ()
    {
        return !empty($this->_stream);
    }

    
    
    private $_stream;

    
    public function getStream ()
    {
        if (!$this->hasBeenOpened()) {
            throw new CAS_OutOfSequenceException(
                'Cannot access stream, not opened yet.'
            );
        }
        return $this->_stream;
    }

    
    public function getImapProxyTicket ()
    {
        if (!$this->hasBeenOpened()) {
            throw new CAS_OutOfSequenceException(
                'Cannot access errors, stream not opened yet.'
            );
        }
        return $this->getProxyTicket();
    }
}
?>
