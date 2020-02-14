<?php



class Horde_Mail_Transport_Smtphorde extends Horde_Mail_Transport
{
    
    public $send8bit = false;

    
    protected $_smtp = null;

    
    public function __construct(array $params = array())
    {
        $this->_params = $params;

        
        $this->sep = "\r\n";
    }

    
    public function send($recipients, array $headers, $body)
    {
        
        $this->getSMTPObject();

        $headers = $this->_sanitizeHeaders($headers);
        list($from, $textHeaders) = $this->prepareHeaders($headers);
        $from = $this->_getFrom($from, $headers);

        $combine = Horde_Stream_Wrapper_Combine::getStream(array(
            rtrim($textHeaders, $this->sep),
            $this->sep . $this->sep,
            $body
        ));

        try {
            $this->_smtp->send($from, $recipients, $combine, array(
                '8bit' => $this->send8bit
            ));
        } catch (Horde_Smtp_Exception $e) {
            throw new Horde_Mail_Exception($e);
        }
    }

    
    public function getSMTPObject()
    {
        if (!$this->_smtp) {
            $this->_smtp = new Horde_Smtp($this->_params);
            try {
                $this->_smtp->login();
            } catch (Horde_Smtp_Exception $e) {
                throw new Horde_Mail_Exception($e);
            }
        }

        return $this->_smtp;
    }

}
