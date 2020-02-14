<?php



class Horde_Mail_Transport_Lmtphorde extends Horde_Mail_Transport_Smtphorde
{
    
    public function getSMTPObject()
    {
        if (!$this->_smtp) {
            $this->_smtp = new Horde_Smtp_Lmtp($this->_params);
            try {
                $this->_smtp->login();
            } catch (Horde_Smtp_Exception $e) {
                throw new Horde_Mail_Exception($e);
            }
        }

        return $this->_smtp;
    }

}
