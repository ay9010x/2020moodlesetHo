<?php



class Horde_Imap_Client_Fetch_Query implements ArrayAccess, Countable, Iterator
{
    
    protected $_data = array();

    
    public function fullText(array $opts = array())
    {
        $this->_data[Horde_Imap_Client::FETCH_FULLMSG] = $opts;
    }

    
    public function headerText(array $opts = array())
    {
        $id = isset($opts['id'])
            ? $opts['id']
            : 0;
        $this->_data[Horde_Imap_Client::FETCH_HEADERTEXT][$id] = $opts;
    }

    
    public function bodyText(array $opts = array())
    {
        $id = isset($opts['id'])
            ? $opts['id']
            : 0;
        $this->_data[Horde_Imap_Client::FETCH_BODYTEXT][$id] = $opts;
    }

    
    public function mimeHeader($id, array $opts = array())
    {
        $this->_data[Horde_Imap_Client::FETCH_MIMEHEADER][$id] = $opts;
    }

    
    public function bodyPart($id, array $opts = array())
    {
        $this->_data[Horde_Imap_Client::FETCH_BODYPART][$id] = $opts;
    }

    
    public function bodyPartSize($id)
    {
        $this->_data[Horde_Imap_Client::FETCH_BODYPARTSIZE][$id] = true;
    }

    
    public function headers($label, $search, array $opts = array())
    {
        $this->_data[Horde_Imap_Client::FETCH_HEADERS][$label] = array_merge($opts, array(
            'headers' => $search
        ));
    }

    
    public function structure()
    {
        $this->_data[Horde_Imap_Client::FETCH_STRUCTURE] = true;
    }

    
    public function envelope()
    {
        $this->_data[Horde_Imap_Client::FETCH_ENVELOPE] = true;
    }

    
    public function flags()
    {
        $this->_data[Horde_Imap_Client::FETCH_FLAGS] = true;
    }

    
    public function imapDate()
    {
        $this->_data[Horde_Imap_Client::FETCH_IMAPDATE] = true;
    }

    
    public function size()
    {
        $this->_data[Horde_Imap_Client::FETCH_SIZE] = true;
    }

    
    public function uid()
    {
        $this->_data[Horde_Imap_Client::FETCH_UID] = true;
    }

    
    public function seq()
    {
        $this->_data[Horde_Imap_Client::FETCH_SEQ] = true;
    }

    
    public function modseq()
    {
        $this->_data[Horde_Imap_Client::FETCH_MODSEQ] = true;
    }

    
    public function contains($criteria)
    {
        return isset($this->_data[$criteria]);
    }

    
    public function remove($criteria, $key)
    {
        if (isset($this->_data[$criteria]) &&
            is_array($this->_data[$criteria])) {
            unset($this->_data[$criteria][$key]);
            if (empty($this->_data[$criteria])) {
                unset($this->_data[$criteria]);
            }
        }
    }

    
    public function hash()
    {
        return hash(
            (PHP_MINOR_VERSION >= 4) ? 'fnv132' : 'sha1',
            serialize($this)
        );
    }

    

    
    public function offsetExists($offset)
    {
        return isset($this->_data[$offset]);
    }

    
    public function offsetGet($offset)
    {
        return isset($this->_data[$offset])
            ? $this->_data[$offset]
            : null;
    }

    
    public function offsetSet($offset, $value)
    {
        $this->_data[$offset] = $value;
    }

    
    public function offsetUnset($offset)
    {
        unset($this->_data[$offset]);
    }

    

    
    public function count()
    {
        return count($this->_data);
    }

    

    
    public function current()
    {
        $opts = current($this->_data);

        return (!empty($opts) && ($this->key() == Horde_Imap_Client::FETCH_BODYPARTSIZE))
            ? array_keys($opts)
            : $opts;
    }

    
    public function key()
    {
        return key($this->_data);
    }

    
    public function next()
    {
        next($this->_data);
    }

    
    public function rewind()
    {
        reset($this->_data);
    }

    
    public function valid()
    {
        return !is_null($this->key());
    }

}
