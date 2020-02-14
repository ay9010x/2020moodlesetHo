<?php



class Horde_Support_StringStream implements Horde_Stream_Wrapper_StringStream
{
    
    const WNAME = 'horde-string';

    
    protected $_string;

    
    public function __construct(&$string)
    {
        $this->installWrapper();
        $this->_string =& $string;
    }

    
    public function fopen()
    {
        return fopen(
            self::WNAME . '://' . spl_object_hash($this),
            'rb',
            false,
            stream_context_create(array(
                self::WNAME => array(
                    'string' => $this
                )
            ))
        );
    }

    
    public function getFileObject()
    {
        return new SplFileObject(
            self::WNAME . '://' . spl_object_hash($this),
            'rb',
            false,
            stream_context_create(array(
                self::WNAME => array(
                    'string' => $this
                )
            ))
        );
    }

    
    public function installWrapper()
    {
        if (!in_array(self::WNAME, stream_get_wrappers()) &&
            !stream_wrapper_register(self::WNAME, 'Horde_Stream_Wrapper_String')) {
            throw new Exception('Unable to register stream wrapper.');
        }
    }

    
    public function &getString()
    {
        return $this->_string;
    }

}
