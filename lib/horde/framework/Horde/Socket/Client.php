<?php

namespace Horde\Socket;




class Client
{
    
    protected $_connected = false;

    
    protected $_params;

    
    protected $_secure = false;

    
    public function __construct(
        $host, $port, $timeout = 30, $secure = false, array $params = array()
    )
    {
        if ($secure && !extension_loaded('openssl')) {
            if ($secure !== true) {
                throw new \InvalidArgumentException('Secure connections require the PHP openssl extension.');
            }
            $secure = false;
        }

        $this->_params = $params;

        $this->_connect($host, $port, $timeout, $secure);
    }

    
    public function __get($name)
    {
        switch ($name) {
        case 'connected':
            return $this->_connected;

        case 'secure':
            return $this->_secure;
        }
    }

    
    public function __clone()
    {
        throw new \LogicException('Object cannot be cloned.');
    }

    
    public function __sleep()
    {
        throw new \LogicException('Object can not be serialized.');
    }

    
    public function startTls()
    {
        if ($this->connected &&
            !$this->secure &&
            (@stream_socket_enable_crypto($this->_stream, true, STREAM_CRYPTO_METHOD_TLS_CLIENT) === true)) {
            $this->_secure = true;
            return true;
        }

        return false;
    }

    
    public function close()
    {
        if ($this->connected) {
            @fclose($this->_stream);
            $this->_connected = $this->_secure = false;
            $this->_stream = null;
        }
    }

    

    
    protected function _connect($host, $port, $timeout, $secure, $retries = 0)
    {
        switch (strval($secure)) {
        case 'ssl':
        case 'sslv2':
        case 'sslv3':
            $conn = $secure . '://';
            $this->_secure = true;
            break;

        case 'tlsv1':
            $conn = 'tls://';
            $this->_secure = true;
            break;

        case 'tls':
        default:
            $conn = 'tcp://';
            break;
        }

        $this->_stream = @stream_socket_client(
            $conn . $host . ':' . $port,
            $error_number,
            $error_string,
            $timeout
        );

        if ($this->_stream === false) {
            
            if (!$error_number && ($retries < 3)) {
                return $this->_connect($host, $port, $timeout, $secure, ++$retries);
            }

            $e = new Client\Exception(
                'Error connecting to server.'
            );
            $e->details = sprintf("[%u] %s", $error_number, $error_string);
            throw $e;
        }

        stream_set_timeout($this->_stream, $timeout);

        if (function_exists('stream_set_read_buffer')) {
            stream_set_read_buffer($this->_stream, 0);
        }
        stream_set_write_buffer($this->_stream, 0);

        $this->_connected = true;
    }

}
