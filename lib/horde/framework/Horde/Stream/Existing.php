<?php



class Horde_Stream_Existing extends Horde_Stream
{
    
    public function __construct(array $opts = array())
    {
        if (!isset($opts['stream']) || !is_resource($opts['stream'])) {
            throw new Horde_Stream_Exception('Need a stream resource.');
        }

        $this->stream = $opts['stream'];
        unset($opts['stream']);

        parent::__construct($opts);
    }

}
