<?php



class Horde_Imap_Client_Data_Format_List extends Horde_Imap_Client_Data_Format implements Countable, IteratorAggregate
{
    
    public function __construct($data = null)
    {
        parent::__construct(array());

        if (!is_null($data)) {
            $this->add($data);
        }
    }

    
    public function add($data, $merge = false)
    {
        if (is_array($data) || ($merge && ($data instanceof Traversable))) {
            foreach ($data as $val) {
                $this->add($val);
            }
        } elseif (is_object($data)) {
            $this->_data[] = $data;
        } elseif (!is_null($data)) {
            $this->_data[] = new Horde_Imap_Client_Data_Format_Atom($data);
        }

        return $this;
    }

    
    public function __toString()
    {
        $out = '';

        foreach ($this as $val) {
            if ($val instanceof $this) {
                $out .= '(' . $val->escape() . ') ';
            } elseif (($val instanceof Horde_Imap_Client_Data_Format_String) &&
                      $val->literal()) {
                throw new Horde_Imap_Client_Data_Format_Exception('Requires literal output.');
            } else {
                $out .= $val->escape() . ' ';
            }
        }

        return rtrim($out);
    }

    

    
    public function count()
    {
        return count($this->_data);
    }

    

    
    public function getIterator()
    {
        return new ArrayIterator($this->_data);
    }

}
