<?php



abstract class Horde_Mail_Rfc822_Object
{
    
    public function __toString()
    {
        return $this->writeAddress();
    }

    
    public function writeAddress($opts = array())
    {
        if ($opts === true) {
            $opts = array(
                'encode' => 'UTF-8',
                'idn' => true
            );
        } elseif (!empty($opts['encode']) && ($opts['encode'] === true)) {
            $opts['encode'] = 'UTF-8';
        }

        return $this->_writeAddress($opts);
    }

    
    abstract protected function _writeAddress($opts);

    
    abstract public function match($ob);

}
