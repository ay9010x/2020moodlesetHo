<?php



class Horde_Imap_Client_Data_Format_Atom extends Horde_Imap_Client_Data_Format
{
    
    public function escape()
    {
        return strlen($this->_data)
            ? parent::escape()
            : '""';
    }

    
    public function verify()
    {
        if (strlen($this->_data) !== strlen($this->stripNonAtomCharacters())) {
            throw new Horde_Imap_Client_Data_Format_Exception('Illegal character in IMAP atom.');
        }
    }

    
    public function stripNonAtomCharacters()
    {
        return str_replace(
            array('(', ')', '{', ' ', '%', '*', '"', '\\', ']'),
            '',
            preg_replace('/[^\x20-\x7e]/', '', $this->_data)
        );
    }

}
