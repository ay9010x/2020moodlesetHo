<?php



class Horde_Support_CombineStream implements Horde_Stream_Wrapper_CombineStream
{
    
    protected $_data;

    
    public function __construct($data)
    {
        $this->installWrapper();
        $this->_data = $data;
    }

    
    public function fopen()
    {
        $context = stream_context_create(array('horde-combine' => array('data' => $this)));
        return fopen('horde-combine://' . spl_object_hash($this), 'rb', false, $context);
    }

    
    public function getFileObject()
    {
        $context = stream_context_create(array('horde-combine' => array('data' => $this)));
        return new SplFileObject('horde-combine://' . spl_object_hash($this), 'rb', false, $context);
    }

    
    public function installWrapper()
    {
        if (!in_array('horde-combine', stream_get_wrappers()) &&
            !stream_wrapper_register('horde-combine', 'Horde_Stream_Wrapper_Combine')) {
            throw new Exception('Unable to register horde-combine stream wrapper.');
        }
    }

    
    public function getData()
    {
        return $this->_data;
    }

}
