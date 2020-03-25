<?php



class Horde_Imap_Client_Url implements Serializable
{
    
    public $auth = null;

    
    public $hostspec = null;

    
    public $mailbox = null;

    
    public $partial = null;

    
    public $port = null;

    
    public $protocol = null;

    
    public $search = null;

    
    public $section = null;

    
    public $username = null;

    
    public $uid = null;

    
    public $uidvalidity = null;

    
    public $urlauth = null;

    
    public function __construct($url = null)
    {
        if (!is_null($url)) {
            $this->_parse($url);
        }
    }

    
    public function __toString()
    {
        $url = '';

        if (!is_null($this->protocol)) {
            $url = $this->protocol . '://';

            if (!is_null($this->username)) {
                $url .= $this->username;
            }

            if (!is_null($this->auth)) {
                $url .= ';AUTH=' . $this->auth . '@';
            } elseif (!is_null($this->username)) {
                $url .= '@';
            }

            $url .= $this->hostspec;

            if (!is_null($this->port) && ($this->port != 143)) {
                $url .= ':' . $this->port;
            }
        }

        $url .= '/';

        if (is_null($this->protocol) || ($this->protocol == 'imap')) {
            $url .= urlencode($this->mailbox);

            if (!empty($this->uidvalidity)) {
                $url .= ';UIDVALIDITY=' . $this->uidvalidity;
            }

            if (!is_null($this->search)) {
                $url .= '?' . urlencode($this->search);
            } else {
                if (!is_null($this->uid)) {
                    $url .= '/;UID=' . $this->uid;
                }

                if (!is_null($this->section)) {
                    $url .= '/;SECTION=' . $this->section;
                }

                if (!is_null($this->partial)) {
                    $url .= '/;PARTIAL=' . $this->partial;
                }

                if (!is_null($this->urlauth)) {
                    $url .= '/;URLAUTH=' . $this->urlauth;
                }
            }
        }

        return $url;
    }

    
    public function __get($name)
    {
        switch ($name) {
        case 'relative':
            return (is_null($this->hostspec) &&
                is_null($this->port) &&
                is_null($this->protocol));
        }
    }

    
    protected function _parse($url)
    {
        $data = parse_url(trim($url));

        if (isset($data['scheme'])) {
            $protocol = strtolower($data['scheme']);
            if (!in_array($protocol, array('imap', 'pop'))) {
                return;
            }

            if (isset($data['host'])) {
                $this->hostspec = $data['host'];
            }
            $this->port = isset($data['port'])
                ? $data['port']
                : 143;
            $this->protocol = $protocol;
        }

        
        if (isset($data['user'])) {
            if (($pos = stripos($data['user'], ';AUTH=')) !== false) {
                $auth = substr($data['user'], $pos + 6);
                if ($auth !== '*') {
                    $this->auth = $auth;
                }
                $data['user'] = substr($data['user'], 0, $pos);
            }

            if (strlen($data['user'])) {
                $this->username = $data['user'];
            }
        }

        
        if (is_null($this->protocol) || ($this->protocol == 'imap')) {
            if (isset($data['path'])) {
                $data['path'] = ltrim($data['path'], '/');
                $parts = explode('/;', $data['path']);

                $mbox = array_shift($parts);
                if (($pos = stripos($mbox, ';UIDVALIDITY=')) !== false) {
                    $this->uidvalidity = intval(substr($mbox, $pos + 13));
                    $mbox = substr($mbox, 0, $pos);
                }
                $this->mailbox = urldecode($mbox);

            }

            if (count($parts)) {
                foreach ($parts as $val) {
                    list($k, $v) = explode('=', $val);
                    $property = strtolower($k);
                    $this->$property = $v;
                }
            } elseif (isset($data['query'])) {
                $this->search = urldecode($data['query']);
            }
        }
    }

    

    
    public function serialize()
    {
        return strval($this);
    }

    
    public function unserialize($data)
    {
        $this->_parse($data);
    }

}
