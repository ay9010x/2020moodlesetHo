<?PHP



class Horde_Mail_Transport_Smtpmx extends Horde_Mail_Transport
{
    
    protected $_smtp = null;

    
    protected $_resolver;

    
    protected $_errorCode = array(
        'not_connected' => array(
            'code' => 1,
            'msg' => 'Could not connect to any mail server ({HOST}) at port {PORT} to send mail to {RCPT}.'
        ),
        'failed_vrfy_rcpt' => array(
            'code' => 2,
            'msg' => 'Recipient "{RCPT}" could not be veryfied.'
        ),
        'failed_set_from' => array(
            'code' => 3,
            'msg' => 'Failed to set sender: {FROM}.'
        ),
        'failed_set_rcpt' => array(
            'code' => 4,
            'msg' => 'Failed to set recipient: {RCPT}.'
        ),
        'failed_send_data' => array(
            'code' => 5,
            'msg' => 'Failed to send mail to: {RCPT}.'
        ),
        'no_from' => array(
            'code' => 5,
            'msg' => 'No from address has be provided.'
        ),
        'send_data' => array(
            'code' => 7,
            'msg' => 'Failed to create Net_SMTP object.'
        ),
        'no_mx' => array(
            'code' => 8,
            'msg' => 'No MX-record for {RCPT} found.'
        ),
        'no_resolver' => array(
            'code' => 9,
            'msg' => 'Could not start resolver! Install PEAR:Net_DNS2 or switch off "netdns"'
        ),
        'failed_rset' => array(
            'code' => 10,
            'msg' => 'RSET command failed, SMTP-connection corrupt.'
        )
    );

    
    public function __construct(array $params = array())
    {
        
        if (!isset($params['mailname']) && function_exists('posix_uname')) {
            $uname = posix_uname();
            $params['mailname'] = $uname['nodename'];
        }

        if (!isset($params['port'])) {
            $params['port'] = getservbyname('smtp', 'tcp');
        }

        $this->_params = array_merge(array(
            'debug' => false,
            'mailname' => 'localhost',
            'netdns' => true,
            'port' => 25,
            'test' => false,
            'timeout' => 10,
            'verp' => false,
            'vrfy' => false
        ), $params);

        
        $this->sep = "\r\n";
    }

    
    public function __destruct()
    {
        if (is_object($this->_smtp)) {
            $this->_smtp->disconnect();
            $this->_smtp = null;
        }
    }

    
    public function send($recipients, array $headers, $body)
    {
        $headers = $this->_sanitizeHeaders($headers);

                list($from, $textHeaders) = $this->prepareHeaders($headers);

        try {
            $from = $this->_getFrom($from, $headers);
        } catch (Horde_Mail_Exception $e) {
            $this->_error('no_from');
        }

                foreach ($this->parseRecipients($recipients) as $rcpt) {
            list(,$host) = explode('@', $rcpt);

            $mx = $this->_getMx($host);
            if (!$mx) {
                $this->_error('no_mx', array('rcpt' => $rcpt));
            }

            $connected = false;
            foreach (array_keys($mx) as $mserver) {
                $this->_smtp = new Net_SMTP($mserver, $this->_params['port'], $this->_params['mailname']);

                                if ($this->_params['debug']) {
                    $this->_smtp->setDebug(true);
                }

                                $res = $this->_smtp->connect($this->_params['timeout']);
                if ($res instanceof PEAR_Error) {
                    $this->_smtp = null;
                    continue;
                }

                                if ($res) {
                    $connected = true;
                    break;
                }
            }

            if (!$connected) {
                $this->_error('not_connected', array(
                    'host' => implode(', ', array_keys($mx)),
                    'port' => $this->_params['port'],
                    'rcpt' => $rcpt
                ));
            }

                        if ($this->_params['vrfy']) {
                $res = $this->_smtp->vrfy($rcpt);
                if ($res instanceof PEAR_Error) {
                    $this->_error('failed_vrfy_rcpt', array('rcpt' => $rcpt));
                }
            }

                        $args['verp'] = $this->_params['verp'];
            $res = $this->_smtp->mailFrom($from, $args);
            if ($res instanceof PEAR_Error) {
                $this->_error('failed_set_from', array('from' => $from));
            }

                        $res = $this->_smtp->rcptTo($rcpt);
            if ($res instanceof PEAR_Error) {
                $this->_error('failed_set_rcpt', array('rcpt' => $rcpt));
            }

                        if ($this->_params['test']) {
                $res = $this->_smtp->rset();
                if ($res instanceof PEAR_Error) {
                    $this->_error('failed_rset');
                }

                $this->_smtp->disconnect();
                $this->_smtp = null;
                return;
            }

                        $res = $this->_smtp->data($body, $textHeaders);
            if ($res instanceof PEAR_Error) {
                $this->_error('failed_send_data', array('rcpt' => $rcpt));
            }

            $this->_smtp->disconnect();
            $this->_smtp = null;
        }
    }

    
    protected function _getMx($host)
    {
        $mx = array();

        if ($this->params['netdns']) {
            $this->_loadNetDns();

            try {
                $response = $this->_resolver->query($host, 'MX');
                if (!$response) {
                    return false;
                }
            } catch (Exception $e) {
                throw new Horde_Mail_Exception($e);
            }

            foreach ($response->answer as $rr) {
                if ($rr->type == 'MX') {
                    $mx[$rr->exchange] = $rr->preference;
                }
            }
        } else {
            $mxHost = $mxWeight = array();

            if (!getmxrr($host, $mxHost, $mxWeight)) {
                return false;
            }

            for ($i = 0; $i < count($mxHost); ++$i) {
                $mx[$mxHost[$i]] = $mxWeight[$i];
            }
        }

        asort($mx);

        return $mx;
    }

    
    protected function _loadNetDns()
    {
        if (!$this->_resolver) {
            if (!class_exists('Net_DNS2_Resolver')) {
                $this->_error('no_resolver');
            }
            $this->_resolver = new Net_DNS2_Resolver();
        }
    }

    
    protected function _error($id, $info = array())
    {
        $msg = $this->_errorCode[$id]['msg'];

                if (!empty($info)) {
            $replace = $search = array();

            foreach ($info as $key => $value) {
                $search[] = '{' . strtoupper($key) . '}';
                $replace[] = $value;
            }

            $msg = str_replace($search, $replace, $msg);
        }

        throw new Horde_Mail_Exception($msg, $this->_errorCode[$id]['code']);
    }

}
