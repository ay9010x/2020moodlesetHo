<?php



class Horde_Stream_String extends Horde_Stream
{
    
    public function __construct(array $opts = array())
    {
        if (!isset($opts['string']) || !is_string($opts['string'])) {
            throw new Horde_Stream_Exception('Need a PHP string.');
        }

        $this->stream = Horde_Stream_Wrapper_String::getStream($opts['string']);
        unset($opts['string']);

        parent::__construct($opts);
    }

}
