<?php



class Horde_Mail_Rfc822_Identification extends Horde_Mail_Rfc822
{
    
    public $ids = array();

    
    public function __construct($value = null)
    {
        $this->parse($value);
    }

    
    public function parse($value)
    {
        if (!strlen($value)) {
            return;
        }

        $this->_data = $value;
        $this->_datalen = strlen($value);
        $this->_params['validate'] = true;
        $this->_ptr = 0;

        $this->_rfc822SkipLwsp();

        while ($this->_curr() !== false) {
            try {
                $this->ids[] = $this->_parseMessageId();
            } catch (Horde_Mail_Exception $e) {
                break;
            }

                        if ($this->_curr() == ',') {
                $this->_rfc822SkipLwsp(true);
            }
        }
    }

    
    private function _parseMessageId()
    {
        $bracket = ($this->_curr(true) === '<');
        $str = '<';

        while (($chr = $this->_curr(true)) !== false) {
            if ($bracket) {
                $str .= $chr;
                if ($chr == '>') {
                    $this->_rfc822SkipLwsp();
                    return $str;
                }
            } else {
                if (!strcspn($chr, " \n\r\t,")) {
                    $this->_rfc822SkipLwsp();
                    return $str;
                }
                $str .= $chr;
            }
        }

        if (!$bracket) {
            return $str;
        }

        throw new Horde_Mail_Exception('Invalid Message-ID.');
    }

}
