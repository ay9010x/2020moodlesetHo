<?php



class Horde_Imap_Client_Data_Thread implements Countable, Serializable
{
    
    protected $_thread = array();

    
    protected $_type;

    
    public function __construct($data, $type)
    {
        $this->_thread = $data;
        $this->_type = $type;
    }

    
    public function getType()
    {
        return $this->_type;
    }

    
    public function messageList()
    {
        return new Horde_Imap_Client_Ids($this->_getAllIndices(), $this->getType() == 'sequence');
    }

    
    public function getThread($index)
    {
        reset($this->_thread);
        while (list(,$v) = each($this->_thread)) {
            if (isset($v[$index])) {
                reset($v);

                $ob = new stdClass;
                $ob->base = (count($v) > 1) ? key($v) : null;
                $ob->last = false;

                $levels = $out = array();
                $last = 0;

                while (list($k2, $v2) = each($v)) {
                    $ob2 = clone $ob;
                    $ob2->level = $v2;
                    $out[$k2] = $ob2;

                    if (($last < $v2) && isset($levels[$v2])) {
                        $out[$levels[$v2]]->last = true;
                    }
                    $levels[$v2] = $k2;
                    $last = $v2;
                }

                foreach ($levels as $v) {
                    $out[$v]->last = true;
                }

                return $out;
            }
        }

        return array();
    }

    

    
    public function count()
    {
        return count($this->_getAllIndices());
    }

    

    
    public function serialize()
    {
        return json_encode(array(
            $this->_thread,
            $this->_type
        ));
    }

    
    public function unserialize($data)
    {
        list($this->_thread, $this->_type) = json_decode($data, true);
    }

    

    
    protected function _getAllIndices()
    {
        $out = array();

        reset($this->_thread);
        while (list(,$v) = each($this->_thread)) {
            $out = array_merge($out, array_keys($v));
        }

        return $out;
    }

}
