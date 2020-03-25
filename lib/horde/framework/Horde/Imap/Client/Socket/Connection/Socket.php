<?php



class Horde_Imap_Client_Socket_Connection_Socket
extends Horde_Imap_Client_Socket_Connection_Base
{
    
    protected $_buffer = '';

    
    public function write($data, $eol = false)
    {
        if ($eol) {
            $buffer = $this->_buffer;
            $this->_buffer = '';

            if (fwrite($this->_stream, $buffer . $data . ($eol ? "\r\n" : '')) === false) {
                throw new Horde_Imap_Client_Exception(
                    Horde_Imap_Client_Translation::r("Server write error."),
                    Horde_Imap_Client_Exception::SERVER_WRITEERROR
                );
            }

            $this->_params['debug']->client($buffer . $data);
        } else {
            $this->_buffer .= $data;
        }
    }

    
    public function writeLiteral($data, $length, $binary = false)
    {
        $this->_buffer = '';

        if ($data instanceof Horde_Stream) {
            $data = $data->stream;
        }

        if (!rewind($data)) {
            throw new Horde_Imap_Client_Exception(
                Horde_Imap_Client_Translation::r("Server write error."),
                Horde_Imap_Client_Exception::SERVER_WRITEERROR
            );
        }

        while (!feof($data)) {
            if ((($read_data = fread($data, 8192)) === false) ||
                (fwrite($this->_stream, $read_data) === false)) {
                throw new Horde_Imap_Client_Exception(
                    Horde_Imap_Client_Translation::r("Server write error."),
                    Horde_Imap_Client_Exception::SERVER_WRITEERROR
                );
            }
        }

        if (!empty($this->_params['debugliteral'])) {
            rewind($data);
            while (!feof($data)) {
                $this->_params['debug']->raw(fread($data, 8192));
            }
        } else {
            $this->_params['debug']->client('[' . ($binary ? 'BINARY' : 'LITERAL') . ' DATA: ' . $length . ' bytes]');
        }
    }

    
    public function read()
    {
        $got_data = false;
        $literal_len = null;
        $token = new Horde_Imap_Client_Tokenize();

        do {
            if (feof($this->_stream)) {
                $this->close();
                $this->_params['debug']->info(
                    'ERROR: Server closed the connection.'
                );
                throw new Horde_Imap_Client_Exception(
                    Horde_Imap_Client_Translation::r("Mail server closed the connection unexpectedly."),
                    Horde_Imap_Client_Exception::DISCONNECT
                );
            }

            if (is_null($literal_len)) {
                $buffer = '';

                while (($in = fgets($this->_stream)) !== false) {
                    $got_data = true;

                    if (substr($in, -1) === "\n") {
                        $in = rtrim($in);
                        $this->_params['debug']->server($buffer . $in);
                        $token->add($in);
                        break;
                    }

                    $buffer .= $in;
                    $token->add($in);
                }

                
                if (is_null($len = $token->getLiteralLength())) {
                    break;
                }

                                if ($len['length']) {
                    $binary = $len['binary'];
                    $literal_len = $len['length'];
                }

                continue;
            }

            $old_len = $literal_len;

            while (($literal_len > 0) && !feof($this->_stream)) {
                $in = fread($this->_stream, min($literal_len, 8192));
                $token->add($in);
                if (!empty($this->_params['debugliteral'])) {
                    $this->_params['debug']->raw($in);
                }

                $got_data = true;
                $literal_len -= strlen($in);
            }

            $literal_len = null;

            if (empty($this->_params['debugliteral'])) {
                $this->_params['debug']->server('[' . ($binary ? 'BINARY' : 'LITERAL') . ' DATA: ' . $old_len . ' bytes]');
            }
        } while (true);

        if (!$got_data) {
            $this->_params['debug']->info('ERROR: read/timeout error.');
            throw new Horde_Imap_Client_Exception(
                Horde_Imap_Client_Translation::r("Error when communicating with the mail server."),
                Horde_Imap_Client_Exception::SERVER_READERROR
            );
        }

        return $token;
    }

}
