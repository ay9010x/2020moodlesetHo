<?php



class Horde_Imap_Client_Base_Debug
{
    
    const SLOW_CMD = 5;

    
    public $debug = true;

    
    protected $_stream;

    
    protected $_time = null;

    
    public function __construct($debug)
    {
        $this->_stream = is_resource($debug)
            ? $debug
            : @fopen($debug, 'a');
        register_shutdown_function(array($this, 'shutdown'));
    }

    
    public function shutdown()
    {
        if (is_resource($this->_stream)) {
            fflush($this->_stream);
            fclose($this->_stream);
            $this->_stream = null;
        }
    }

    
    public function client($msg)
    {
        $this->_write($msg . "\n", 'C: ');
    }

    
    public function info($msg)
    {
        $this->_write($msg . "\n", '>> ');
    }

    
    public function raw($msg)
    {
        $this->_write($msg);
    }

    
    public function server($msg)
    {
        $this->_write($msg . "\n", 'S: ');
    }

    
    protected function _write($msg, $pre = null)
    {
        if (!$this->debug || !$this->_stream) {
            return;
        }

        if (!is_null($pre)) {
            $new_time = microtime(true);

            if (is_null($this->_time)) {
                fwrite(
                    $this->_stream,
                    str_repeat('-', 30) . "\n" . '>> ' . date('r') . "\n"
                );
            } elseif (($diff = ($new_time - $this->_time)) > self::SLOW_CMD) {
                fwrite(
                    $this->_stream,
                    '>> Slow Command: ' . round($diff, 3) . " seconds\n"
                );
            }

            $this->_time = $new_time;
        }

        fwrite($this->_stream, $pre . $msg);
    }

}
