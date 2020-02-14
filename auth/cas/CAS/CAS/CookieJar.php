<?php




class CAS_CookieJar
{

    private $_cookies;

    
    public function __construct (array &$storageArray)
    {
        $this->_cookies =& $storageArray;
    }

    
    public function storeCookies ($request_url, $response_headers)
    {
        $urlParts = parse_url($request_url);
        $defaultDomain = $urlParts['host'];

        $cookies = $this->parseCookieHeaders($response_headers, $defaultDomain);

                foreach ($cookies as $cookie) {
                                    if (!$this->cookieMatchesTarget($cookie, $urlParts)) {
                continue;
            }

                        $this->storeCookie($cookie);

            phpCAS::trace($cookie['name'].' -> '.$cookie['value']);
        }
    }

    
    public function getCookies ($request_url)
    {
        if (!count($this->_cookies)) {
            return array();
        }

                $target = parse_url($request_url);
        if ($target === false) {
            return array();
        }

        $this->expireCookies();

        $matching_cookies = array();
        foreach ($this->_cookies as $key => $cookie) {
            if ($this->cookieMatchesTarget($cookie, $target)) {
                $matching_cookies[$cookie['name']] = $cookie['value'];
            }
        }
        return $matching_cookies;
    }


    
    protected function parseCookieHeaders( $header, $defaultDomain )
    {
        phpCAS::traceBegin();
        $cookies = array();
        foreach ( $header as $line ) {
            if ( preg_match('/^Set-Cookie2?: /i', $line)) {
                $cookies[] = $this->parseCookieHeader($line, $defaultDomain);
            }
        }

        phpCAS::traceEnd($cookies);
        return $cookies;
    }

    
    protected function parseCookieHeader ($line, $defaultDomain)
    {
        if (!$defaultDomain) {
            throw new CAS_InvalidArgumentException(
                '$defaultDomain was not provided.'
            );
        }

                $cookie = array(
            'domain' => $defaultDomain,
            'path' => '/',
            'secure' => false,
        );

        $line = preg_replace('/^Set-Cookie2?: /i', '', trim($line));

                $line = trim($line, ';');

        phpCAS::trace("Cookie Line: $line");

                                                $attributeStrings = explode(';', $line);

        foreach ( $attributeStrings as $attributeString ) {
                        $attributeParts = explode('=', $attributeString, 2);

            $attributeName = trim($attributeParts[0]);
            $attributeNameLC = strtolower($attributeName);

            if (isset($attributeParts[1])) {
                $attributeValue = trim($attributeParts[1]);
                                if (strpos($attributeValue, '"') === 0) {
                    $attributeValue = trim($attributeValue, '"');
                                        $attributeValue = str_replace('\"', '"', $attributeValue);
                }
            } else {
                $attributeValue = null;
            }

            switch ($attributeNameLC) {
            case 'expires':
                $cookie['expires'] = strtotime($attributeValue);
                break;
            case 'max-age':
                $cookie['max-age'] = (int)$attributeValue;
                                if ($cookie['max-age']) {
                    $cookie['expires'] = time() + $cookie['max-age'];
                } else {
                                                            $cookie['expires'] = time() - 1;
                }
                break;
            case 'secure':
                $cookie['secure'] = true;
                break;
            case 'domain':
            case 'path':
            case 'port':
            case 'version':
            case 'comment':
            case 'commenturl':
            case 'discard':
            case 'httponly':
                $cookie[$attributeNameLC] = $attributeValue;
                break;
            default:
                $cookie['name'] = $attributeName;
                $cookie['value'] = $attributeValue;
            }
        }

        return $cookie;
    }

    
    protected function storeCookie ($cookie)
    {
                $this->discardCookie($cookie);
        $this->_cookies[] = $cookie;

    }

    
    protected function discardCookie ($cookie)
    {
        if (!isset($cookie['domain'])
            || !isset($cookie['path'])
            || !isset($cookie['path'])
        ) {
            throw new CAS_InvalidArgumentException('Invalid Cookie array passed.');
        }

        foreach ($this->_cookies as $key => $old_cookie) {
            if ( $cookie['domain'] == $old_cookie['domain']
                && $cookie['path'] == $old_cookie['path']
                && $cookie['name'] == $old_cookie['name']
            ) {
                unset($this->_cookies[$key]);
            }
        }
    }

    
    protected function expireCookies ()
    {
        foreach ($this->_cookies as $key => $cookie) {
            if (isset($cookie['expires']) && $cookie['expires'] < time()) {
                unset($this->_cookies[$key]);
            }
        }
    }

    
    protected function cookieMatchesTarget ($cookie, $target)
    {
        if (!is_array($target)) {
            throw new CAS_InvalidArgumentException(
                '$target must be an array of URL attributes as generated by parse_url().'
            );
        }
        if (!isset($target['host'])) {
            throw new CAS_InvalidArgumentException(
                '$target must be an array of URL attributes as generated by parse_url().'
            );
        }

                if ($cookie['secure'] && $target['scheme'] != 'https') {
            return false;
        }

                        if (strpos($cookie['domain'], '.') === 0) {
                        if (substr($cookie['domain'], 1) == $target['host']) {
                            } else {
                                                $pos = strripos($target['host'], $cookie['domain']);
                if (!$pos) {
                    return false;
                }
                                if ($pos + strlen($cookie['domain']) != strlen($target['host'])) {
                    return false;
                }
                                                                $hostname = substr($target['host'], 0, $pos);
                if (strpos($hostname, '.') !== false) {
                    return false;
                }
            }
        } else {
                                    if (strcasecmp($target['host'], $cookie['domain']) !== 0) {
                return false;
            }
        }

                if (isset($cookie['ports'])
            && !in_array($target['port'], $cookie['ports'])
        ) {
            return false;
        }

                if (strpos($target['path'], $cookie['path']) !== 0) {
            return false;
        }

        return true;
    }

}

?>
