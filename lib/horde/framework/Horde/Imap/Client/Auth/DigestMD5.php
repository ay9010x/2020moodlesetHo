<?php



class Horde_Imap_Client_Auth_DigestMD5
{
    
    protected $_response;

    
    public function __construct($id, $pass, $challenge, $hostname, $service)
    {
        $challenge = $this->_parseChallenge($challenge);
        $cnonce = $this->_getCnonce();
        $digest_uri = sprintf('%s/%s', $service, $hostname);

        
        $A1 = sprintf('%s:%s:%s', pack('H32', hash('md5', sprintf('%s:%s:%s', $id, $challenge['realm'], $pass))), $challenge['nonce'], $cnonce);
        $A2 = 'AUTHENTICATE:' . $digest_uri;
        $response_value = hash('md5', sprintf('%s:%s:00000001:%s:auth:%s', hash('md5', $A1), $challenge['nonce'], $cnonce, hash('md5', $A2)));

        $this->_response = array(
            'cnonce' => '"' . $cnonce . '"',
            'digest-uri' => '"' . $digest_uri . '"',
            'maxbuf' => $challenge['maxbuf'],
            'nc' => '00000001',
            'nonce' => '"' . $challenge['nonce'] . '"',
            'qop' => 'auth',
            'response' => $response_value,
            'username' => '"' . $id . '"'
        );

        if (strlen($challenge['realm'])) {
            $this->_response['realm'] = '"' . $challenge['realm'] . '"';
        }
    }

    
    public function __toString()
    {
        $out = array();
        foreach ($this->_response as $key => $val) {
            $out[] = $key . '=' . $val;
        }
        return implode(',', $out);
    }

    
    public function __get($name)
    {
        return isset($this->_response[$name])
            ? $this->_response[$name]
            : null;
    }

    
    protected function _parseChallenge($challenge)
    {
        $tokens = array(
            'maxbuf' => 65536,
            'realm' => ''
        );

        preg_match_all('/([a-z-]+)=("[^"]+(?<!\\\)"|[^,]+)/i', $challenge, $matches, PREG_SET_ORDER);

        foreach ($matches as $val) {
            $tokens[$val[1]] = trim($val[2], '"');
        }

                if (!isset($tokens['nonce']) || !isset($tokens['algorithm'])) {
            throw new Horde_Imap_Client_Exception(
                Horde_Imap_Client_Translation::r("Authentication failure."),
                Horde_Imap_Client_Exception::SERVER_CONNECT
            );
        }

        return $tokens;
    }

    
    protected function _getCnonce()
    {
        if ((@is_readable('/dev/urandom') &&
             ($fd = @fopen('/dev/urandom', 'r'))) ||
            (@is_readable('/dev/random') &&
             ($fd = @fopen('/dev/random', 'r')))) {
            $str = fread($fd, 32);
            fclose($fd);
        } else {
            $str = '';
            for ($i = 0; $i < 32; ++$i) {
                $str .= chr(mt_rand(0, 255));
            }
        }

        return base64_encode($str);
    }

}
