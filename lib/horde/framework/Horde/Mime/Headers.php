<?php

class Horde_Mime_Headers implements Serializable
{
    
    const VERSION = 2;

    
    const VALUE_STRING = 1;
    const VALUE_BASE = 2;
    const VALUE_PARAMS = 3;

    
    static public $defaultCharset = 'us-ascii';

    
    protected $_headers = array();

    
    protected $_eol = "\n";

    
    protected $_agent = null;

    
    protected $_singleFields = array(
                'to', 'from', 'cc', 'bcc', 'date', 'sender', 'reply-to',
        'message-id', 'in-reply-to', 'references', 'subject',
                'content-md5',
                'mime-version', 'content-type', 'content-transfer-encoding',
        'content-id', 'content-description',
                'content-base',
                'content-disposition',
                'content-duration',
                'content-location',
                'content-features',
                'content-language',
                'content-alternative',
                'importance',
                        'x-priority'
    );

    
    public function toArray(array $opts = array())
    {
        $address_keys = $this->addressFields();
        $charset = array_key_exists('charset', $opts)
            ? (empty($opts['charset']) ? 'UTF-8' : $opts['charset'])
            : null;
        $eol = empty($opts['canonical'])
            ? $this->_eol
            : "\r\n";
        $mime = $this->mimeParamFields();
        $ret = array();

        foreach ($this->_headers as $header => $ob) {
            $val = is_array($ob['v']) ? $ob['v'] : array($ob['v']);

            foreach (array_keys($val) as $key) {
                if (in_array($header, $address_keys) ) {
                    
                    $rfc822 = new Horde_Mail_Rfc822();
                    $text = $rfc822->parseAddressList($val[$key], array(
                        'default_domain' => empty($opts['defserver']) ? null : $opts['defserver']
                    ))->writeAddress(array(
                        'encode' => $charset,
                        'idn' => true
                    ));
                } elseif (in_array($header, $mime) && !empty($ob['p'])) {
                    
                    $text = $val[$key];
                    foreach ($ob['p'] as $name => $param) {
                        foreach (Horde_Mime::encodeParam($name, $param, array('charset' => $charset, 'escape' => true)) as $name2 => $param2) {
                            $text .= '; ' . $name2 . '=' . $param2;
                        }
                    }
                } else {
                    $text = is_null($charset)
                        ? $val[$key]
                        : Horde_Mime::encode($val[$key], $charset);
                }

                if (empty($opts['nowrap'])) {
                    
                    $header_text = $ob['h'] . ': ';
                    $text = ltrim(substr(wordwrap($header_text . strtr(trim($text), array("\r" => '', "\n" => '')), 76, $eol . ' '), strlen($header_text)));
                }

                $val[$key] = $text;
            }

            $ret[$ob['h']] = (count($val) == 1) ? reset($val) : $val;
        }

        return $ret;
    }

    
    public function toString(array $opts = array())
    {
        $eol = empty($opts['canonical'])
            ? $this->_eol
            : "\r\n";
        $text = '';

        foreach ($this->toArray($opts) as $key => $val) {
            if (!is_array($val)) {
                $val = array($val);
            }
            foreach ($val as $entry) {
                $text .= $key . ': ' . $entry . $eol;
            }
        }

        return $text . $eol;
    }

    
    public function addReceivedHeader(array $opts = array())
    {
        $old_error = error_reporting(0);
        if (isset($_SERVER['HTTP_X_FORWARDED_FOR'])) {
            
            $remote_path = explode(',', $_SERVER['HTTP_X_FORWARDED_FOR']);
            $remote_addr = $remote_path[0];
            if (!empty($opts['dns'])) {
                $remote = $remote_addr;
                try {
                    if ($response = $opts['dns']->query($remote_addr, 'PTR')) {
                        foreach ($response->answer as $val) {
                            if (isset($val->ptrdname)) {
                                $remote = $val->ptrdname;
                                break;
                            }
                        }
                    }
                } catch (Net_DNS2_Exception $e) {}
            } else {
                $remote = gethostbyaddr($remote_addr);
            }
        } else {
            $remote_addr = $_SERVER['REMOTE_ADDR'];
            if (empty($_SERVER['REMOTE_HOST'])) {
                if (!empty($opts['dns'])) {
                    $remote = $remote_addr;
                    try {
                        if ($response = $opts['dns']->query($remote_addr, 'PTR')) {
                            foreach ($response->answer as $val) {
                                if (isset($val->ptrdname)) {
                                    $remote = $val->ptrdname;
                                    break;
                                }
                            }
                        }
                    } catch (Net_DNS2_Exception $e) {}
                } else {
                    $remote = gethostbyaddr($remote_addr);
                }
            } else {
                $remote = $_SERVER['REMOTE_HOST'];
            }
        }
        error_reporting($old_error);

        if (!empty($_SERVER['REMOTE_IDENT'])) {
            $remote_ident = $_SERVER['REMOTE_IDENT'] . '@' . $remote . ' ';
        } elseif ($remote != $_SERVER['REMOTE_ADDR']) {
            $remote_ident = $remote . ' ';
        } else {
            $remote_ident = '';
        }

        if (!empty($opts['server'])) {
            $server_name = $opts['server'];
        } elseif (!empty($_SERVER['SERVER_NAME'])) {
            $server_name = $_SERVER['SERVER_NAME'];
        } elseif (!empty($_SERVER['HTTP_HOST'])) {
            $server_name = $_SERVER['HTTP_HOST'];
        } else {
            $server_name = 'unknown';
        }

        $received = 'from ' . $remote . ' (' . $remote_ident .
            '[' . $remote_addr . ']) ' .
            'by ' . $server_name . ' (Horde Framework) with HTTP; ' .
            date('r');

        $this->addHeader('Received', $received);
    }

    
    public function addMessageIdHeader()
    {
        $this->addHeader('Message-ID', Horde_Mime::generateMessageId());
    }

    
    public function addUserAgentHeader()
    {
        $this->addHeader('User-Agent', $this->getUserAgent());
    }

    
    public function getUserAgent()
    {
        if (is_null($this->_agent)) {
            $this->_agent = 'Horde Application Framework 5';
        }
        return $this->_agent;
    }

    
    public function setUserAgent($agent)
    {
        $this->_agent = $agent;
    }

    
    public function addHeader($header, $value, array $opts = array())
    {
        $header = trim($header);
        $lcHeader = Horde_String::lower($header);

        if (!isset($this->_headers[$lcHeader])) {
            $this->_headers[$lcHeader] = array(
                'h' => $header
            );
        }
        $ptr = &$this->_headers[$lcHeader];

        if (!empty($opts['sanity_check'])) {
            $value = $this->_sanityCheck($value);
        }

                if (in_array($lcHeader, $this->addressFields())) {
            $rfc822 = new Horde_Mail_Rfc822();
            $addr_list = $rfc822->parseAddressList($value);

            switch ($lcHeader) {
            case 'bcc':
            case 'cc':
            case 'from':
            case 'to':
                
                if ((count($addr_list) == 1) &&
                    preg_match("/^\s*undisclosed-recipients:?\s*$/i", $addr_list[0]->bare_address)) {
                    $addr_list = new Horde_Mail_Rfc822_List('undisclosed-recipients:;');
                }
                break;
            }
            $value = strval($addr_list);
        } else {
            $value = Horde_Mime::decode($value);
        }

        if (isset($ptr['v'])) {
            if (!is_array($ptr['v'])) {
                $ptr['v'] = array($ptr['v']);
            }
            $ptr['v'][] = $value;
        } else {
            $ptr['v'] = $value;
        }

        if (!empty($opts['params'])) {
            $ptr['p'] = $opts['params'];
        }
    }

    
    public function removeHeader($header)
    {
        unset($this->_headers[Horde_String::lower(trim($header))]);
    }

    
    public function replaceHeader($header, $value, array $opts = array())
    {
        $this->removeHeader($header);
        $this->addHeader($header, $value, $opts);
    }

    
    public function getString($header)
    {
        $lcHeader = Horde_String::lower($header);
        return (isset($this->_headers[$lcHeader]))
            ? $this->_headers[$lcHeader]['h']
            : null;
    }

    
    public function getValue($header, $type = self::VALUE_STRING)
    {
        $header = Horde_String::lower($header);

        if (!isset($this->_headers[$header])) {
            return null;
        }

        $ptr = &$this->_headers[$header];
        if (is_array($ptr['v']) &&
            in_array($header, $this->singleFields(true))) {
            if (in_array($header, $this->addressFields())) {
                $base = str_replace(';,', ';', implode(', ', $ptr['v']));
            } else {
                $base = $ptr['v'][0];
            }
        } else {
            $base = $ptr['v'];
        }
        $params = isset($ptr['p']) ? $ptr['p'] : array();

        switch ($type) {
        case self::VALUE_BASE:
            return $base;

        case self::VALUE_PARAMS:
            return $params;

        case self::VALUE_STRING:
            foreach ($params as $key => $val) {
                $base .= '; ' . $key . '=' . $val;
            }
            return $base;
        }
    }

    
    static public function addressFields()
    {
        return array(
            'from', 'to', 'cc', 'bcc', 'reply-to', 'resent-to', 'resent-cc',
            'resent-bcc', 'resent-from', 'sender'
        );
    }

    
    public function singleFields($list = true)
    {
        return $list
            ? array_merge($this->_singleFields, array_keys($this->listHeaders()))
            : $this->_singleFields;
    }

    
    static public function mimeParamFields()
    {
        return array('content-type', 'content-disposition');
    }

    
    static public function listHeaders()
    {
        return array(
            
            'list-help'         =>  Horde_Mime_Translation::t("List-Help"),
            'list-unsubscribe'  =>  Horde_Mime_Translation::t("List-Unsubscribe"),
            'list-subscribe'    =>  Horde_Mime_Translation::t("List-Subscribe"),
            'list-owner'        =>  Horde_Mime_Translation::t("List-Owner"),
            'list-post'         =>  Horde_Mime_Translation::t("List-Post"),
            'list-archive'      =>  Horde_Mime_Translation::t("List-Archive"),
            
            'list-id'           =>  Horde_Mime_Translation::t("List-Id")
        );
    }

    
    public function listHeadersExist()
    {
        return (bool)count(array_intersect(array_keys($this->listHeaders()), array_keys($this->_headers)));
    }

    
    public function setEOL($eol)
    {
        $this->_eol = $eol;
    }

    
    public function getEOL()
    {
        return $this->_eol;
    }

    
    public function getOb($field)
    {
        if (($value = $this->getValue($field)) === null) {
            return null;
        }

        $rfc822 = new Horde_Mail_Rfc822();
        return $rfc822->parseAddressList($value);
    }

    
    protected function _sanityCheck($data)
    {
        $charset_test = array(
            'windows-1252',
            self::$defaultCharset
        );

        if (!Horde_String::validUtf8($data)) {
            
            $data = substr($data, 0);

            
            foreach ($charset_test as $charset) {
                $tmp = Horde_String::convertCharset($data, $charset, 'UTF-8');
                if (Horde_String::validUtf8($tmp)) {
                    return $tmp;
                }
            }
        }

        return $data;
    }

    

    
    static public function parseHeaders($text)
    {
        $currheader = $currtext = null;
        $mime = self::mimeParamFields();
        $to_process = array();

        if ($text instanceof Horde_Stream) {
            $stream = $text;
            $stream->rewind();
        } else {
            $stream = new Horde_Stream_Temp();
            $stream->add($text, true);
        }

        while (!$stream->eof()) {
            if (!($val = rtrim($stream->getToChar("\n", false), "\r"))) {
                break;
            }

            if (($val[0] == ' ') || ($val[0] == "\t")) {
                $currtext .= ' ' . ltrim($val);
            } else {
                if (!is_null($currheader)) {
                    $to_process[] = array($currheader, rtrim($currtext));
                }

                $pos = strpos($val, ':');
                $currheader = substr($val, 0, $pos);
                $currtext = ltrim(substr($val, $pos + 1));
            }
        }

        if (!is_null($currheader)) {
            $to_process[] = array($currheader, $currtext);
        }

        $headers = new Horde_Mime_Headers();

        reset($to_process);
        while (list(,$val) = each($to_process)) {
            
            if (!strlen($val[1])) {
                continue;
            }

            if (in_array(Horde_String::lower($val[0]), $mime)) {
                $res = Horde_Mime::decodeParam($val[0], $val[1]);
                $headers->addHeader($val[0], $res['val'], array(
                    'params' => $res['params'],
                    'sanity_check' => true
                ));
            } else {
                $headers->addHeader($val[0], $val[1], array(
                    'sanity_check' => true
                ));
            }
        }

        return $headers;
    }

    

    
    public function serialize()
    {
        $data = array(
                        self::VERSION,
            $this->_headers,
            $this->_eol
        );

        if (!is_null($this->_agent)) {
            $data[] = $this->_agent;
        }

        return serialize($data);
    }

    
    public function unserialize($data)
    {
        $data = @unserialize($data);
        if (!is_array($data) ||
            !isset($data[0]) ||
            ($data[0] != self::VERSION)) {
            throw new Horde_Mime_Exception('Cache version change');
        }

        $this->_headers = $data[1];
        $this->_eol = $data[2];
        if (isset($data[3])) {
            $this->_agent = $data[3];
        }
    }

}
