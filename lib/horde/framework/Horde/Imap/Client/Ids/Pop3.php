<?php



class Horde_Imap_Client_Ids_Pop3 extends Horde_Imap_Client_Ids
{
    
    protected function _toSequenceString($sort = true)
    {
        
        return implode(' ', count($this->_ids) > 25000 ? array_unique($this->_ids) : array_keys(array_flip($this->_ids)));
    }

    
    protected function _fromSequenceString($str)
    {
        return explode(' ', trim($str));
    }

}
