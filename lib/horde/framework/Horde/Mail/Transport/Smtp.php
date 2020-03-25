<?php



class Horde_Mail_Transport_Smtp extends Horde_Mail_Transport
{
    
    const ERROR_CREATE = 10000;

    
    const ERROR_CONNECT = 10001;

    
    const ERROR_AUTH = 10002;

    
    const ERROR_FROM = 10003;

    
    const ERROR_SENDER = 10004;

    
    const ERROR_RECIPIENT = 10005;

    
    const ERROR_DATA = 10006;

    
    public $greeting = null;

    
    public $queuedAs = null;

    
    protected $_smtp = null;

    
    protected $_extparams = array();

    
    public function __construct(array $params = array())
    {
        $this->_params = array_merge(array(
            'auth' => false,
            'debug' => false,
            'host' => 'localhost',
            'localhost' => 'localhost',
            'password' => '',
            'persist' => false,
            'pipelining' => false,
            'port' => 25,
            'timeout' => null,
            'username' => ''
        ), $params);

        
        register_shutdown_function(array($this, 'disconnect'));

        
        $this->sep = "\r\n";
    }

    
    public function send($recipients, array $headers, $body)
    {
        
        $this->getSMTPObject();

        $headers = $this->_sanitizeHeaders($headers);

        
        if (is_resource($body)) {
            fseek($body, -1, SEEK_END);
            switch (fgetc($body)) {
            case "\r":
                if (fgetc($body) != "\n") {
                    fputs($body, "\n");
                }
                break;

            default:
                fputs($body, "\r\n");
                break;
            }
            rewind($body);
        } elseif (substr($body, -2, 0) != "\r\n") {
            $body .= "\r\n";
        }

        try {
            list($from, $textHeaders) = $this->prepareHeaders($headers);
        } catch (Horde_Mail_Exception $e) {
            $this->_smtp->rset();
            throw $e;
        }

        try {
            $from = $this->_getFrom($from, $headers);
        } catch (Horde_Mail_Exception $e) {
            $this->_smtp->rset();
            throw new Horde_Mail_Exception('No From: address has been provided', self::ERROR_FROM);
        }

        $params = '';
        foreach ($this->_extparams as $key => $val) {
            $params .= ' ' . $key . (is_null($val) ? '' : '=' . $val);
        }

        $res = $this->_smtp->mailFrom($from, ltrim($params));
        if ($res instanceof PEAR_Error) {
            $this->_error(sprintf("Failed to set sender: %s", $from), $res, self::ERROR_SENDER);
        }

        try {
            $recipients = $this->parseRecipients($recipients);
        } catch (Horde_Mail_Exception $e) {
            $this->_smtp->rset();
            throw $e;
        }

        foreach ($recipients as $recipient) {
            $res = $this->_smtp->rcptTo($recipient);
            if ($res instanceof PEAR_Error) {
                $this->_error("Failed to add recipient: $recipient", $res, self::ERROR_RECIPIENT);
            }
        }

        
        $res = $this->_smtp->data($body, $textHeaders);
        list(,$args) = $this->_smtp->getResponse();

        if (preg_match("/Ok: queued as (.*)/", $args, $queued)) {
            $this->queuedAs = $queued[1];
        }

        
        $this->greeting = $this->_smtp->getGreeting();

        if ($res instanceof PEAR_Error) {
            $this->_error('Failed to send data', $res, self::ERROR_DATA);
        }

        
        if (!$this->_params['persist']) {
            $this->disconnect();
        }
    }

    
    public function getSMTPObject()
    {
        if ($this->_smtp) {
            return $this->_smtp;
        }

        $this->_smtp = new Net_SMTP(
            $this->_params['host'],
            $this->_params['port'],
            $this->_params['localhost']
        );

        
        if ($this->_params['pipelining']) {
            $this->_smtp->pipelining = true;
        }

        
        if (!($this->_smtp instanceof Net_SMTP)) {
            throw new Horde_Mail_Exception('Failed to create a Net_SMTP object', self::ERROR_CREATE);
        }

        
        if ($this->_params['debug']) {
            $this->_smtp->setDebug(true);
        }

        
        $res = $this->_smtp->connect($this->_params['timeout']);
        if ($res instanceof PEAR_Error) {
            $this->_error('Failed to connect to ' . $this->_params['host'] . ':' . $this->_params['port'], $res, self::ERROR_CONNECT);
        }

        
        if ($this->_params['auth']) {
            $method = is_string($this->_params['auth'])
                ? $this->_params['auth']
                : '';

            $res = $this->_smtp->auth($this->_params['username'], $this->_params['password'], $method);
            if ($res instanceof PEAR_Error) {
                $this->_error("$method authentication failure", $res, self::ERROR_AUTH);
            }
        }

        return $this->_smtp;
    }

    
    public function addServiceExtensionParameter($keyword, $value = null)
    {
        $this->_extparams[$keyword] = $value;
    }

    
    public function disconnect()
    {
        
        if (is_object($this->_smtp) && $this->_smtp->disconnect()) {
            $this->_smtp = null;
        }

        
        return ($this->_smtp === null);
    }

    
    protected function _error($text, $error, $e_code)
    {
        
        list($code, $response) = $this->_smtp->getResponse();

        
        $this->_smtp->rset();

        
        throw new Horde_Mail_Exception($text . ' [SMTP: ' . $error->getMessage() . " (code: $code, response: $response)]", $e_code);
    }

}
