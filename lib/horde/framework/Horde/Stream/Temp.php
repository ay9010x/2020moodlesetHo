<?php



class Horde_Stream_Temp extends Horde_Stream
{
    
    public function __construct(array $opts = array())
    {
        parent::__construct($opts);
    }

    
    protected function _init()
    {
        $cmd = 'php://temp';
        if (isset($this->_params['max_memory'])) {
            $cmd .= '/maxmemory:' . intval($this->_params['max_memory']);
        }

        if (($this->stream = @fopen($cmd, 'r+')) === false) {
            throw new Horde_Stream_Exception('Failed to open temporary memory stream.');
        }
    }

}
