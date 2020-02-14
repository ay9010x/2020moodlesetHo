<?php



class Horde_Imap_Client_Ids_Map implements Countable, IteratorAggregate, Serializable
{
    
    protected $_ids = array();

    
    protected $_sorted = true;

    
    public function __construct(array $ids = array())
    {
        $this->update($ids);
    }

    
    public function __get($name)
    {
        switch ($name) {
        case 'map':
            return $this->_ids;

        case 'seq':
            $this->sort();
            return new Horde_Imap_Client_Ids(array_keys($this->_ids), true);

        case 'uids':
            $this->sort();
            return new Horde_Imap_Client_Ids($this->_ids);
        }
    }

    
    public function update($ids)
    {
        if (empty($ids)) {
            return false;
        } elseif (empty($this->_ids)) {
            $this->_ids = $ids;
            $change = true;
        } else {
            $change = false;
            foreach ($ids as $k => $v) {
                if (!isset($this->_ids[$k]) || ($this->_ids[$k] != $v)) {
                    $this->_ids[$k] = $v;
                    $change = true;
                }
            }
        }

        if ($change) {
            $this->_sorted = false;
        }

        return $change;
    }

    
    public function lookup(Horde_Imap_Client_Ids $ids)
    {
        if ($ids->all) {
            return $this->_ids;
        } elseif ($ids->sequence) {
            return array_intersect_key($this->_ids, array_flip($ids->ids));
        }

        return array_intersect($this->_ids, $ids->ids);
    }

    
    public function remove(Horde_Imap_Client_Ids $ids)
    {
        
        if ($ids->sequence) {
            $remove = $ids->ids;
        } else {
            $ids->sort();
            $remove = array_reverse(array_keys($this->lookup($ids)));
        }

        if (empty($remove)) {
            return;
        }

        $this->sort();

        
        $first = min($remove);
        $edit = $newids = array();
        foreach (array_keys($this->_ids) as $i => $seq) {
            if ($seq >= $first) {
                $i += (($seq == $first) ? 0 : 1);
                $newids = array_slice($this->_ids, 0, $i, true);
                $edit = array_slice($this->_ids, $i + (($seq == $first) ? 0 : 1), null, true);
                break;
            }
        }

        if (!empty($edit)) {
            foreach ($remove as $val) {
                $found = false;
                $tmp = array();

                foreach (array_keys($edit) as $i => $seq) {
                    if ($found) {
                        $tmp[$seq - 1] = $edit[$seq];
                    } elseif ($seq >= $val) {
                        $tmp = array_slice($edit, 0, ($seq == $val) ? $i : $i + 1, true);
                        $found = true;
                    }
                }

                $edit = $tmp;
            }
        }

        $this->_ids = $newids + $edit;
    }

    
    public function sort()
    {
        if (!$this->_sorted) {
            ksort($this->_ids, SORT_NUMERIC);
            $this->_sorted = true;
        }
    }

    

    
    public function count()
    {
        return count($this->_ids);
    }

    

    
    public function getIterator()
    {
        return new ArrayIterator($this->_ids);
    }

    

    
    public function serialize()
    {
        
        $this->sort();

        return json_encode(array(
            strval(new Horde_Imap_Client_Ids(array_keys($this->_ids))),
            strval(new Horde_Imap_Client_Ids(array_values($this->_ids)))
        ));
    }

    
    public function unserialize($data)
    {
        $data = json_decode($data, true);

        $keys = new Horde_Imap_Client_Ids($data[0]);
        $vals = new Horde_Imap_Client_Ids($data[1]);
        $this->_ids = array_combine($keys->ids, $vals->ids);

        
        $this->_sorted = true;
    }

}
