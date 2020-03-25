<?php



class Horde_Imap_Client_Socket_Connection_Pop3
extends Horde_Imap_Client_Socket_Connection_Base
{
    
    protected $_protocol = 'pop3';

    
    public function write($data)
    {
        if (fwrite($this->_stream, $data . "\r\n") === false) {
            throw new Horde_Imap_Client_Exception(
                Horde_Imap_Client_Translation::r("Server write error."),
                Horde_Imap_Client_Exception::SERVER_WRITEERROR
            );
        }

        $this->_params['debug']->client($data);
    }

    
    public function read()
    {
        if (feof($this->_stream)) {
            $this->close();
            $this->_params['debug']->info(
                'ERROR: Server closed the connection.'
            );
            throw new Horde_Imap_Client_Exception(
                Horde_Imap_Client_Translation::r("Server closed the connection unexpectedly."),
                Horde_Imap_Client_Exception::DISCONNECT
            );
        }

        if (($read = fgets($this->_stream)) === false) {
            $this->_params['debug']->info('ERROR: read/timeout error.');
            throw new Horde_Imap_Client_Exception(
                Horde_Imap_Client_Translation::r("Error when communicating with the mail server."),
                Horde_Imap_Client_Exception::SERVER_READERROR
            );
        }

        $this->_params['debug']->server(rtrim($read, "\r\n"));

        return $read;
    }

}
