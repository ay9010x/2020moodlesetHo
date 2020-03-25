<?php



abstract class Horde_Mail_Transport
{
    
    public $sep = PHP_EOL;

    
    protected $_params = array();

    
    abstract public function send($recipients, array $headers, $body);

    
    public function prepareHeaders(array $headers)
    {
        $from = null;
        $lines = array();
        $raw = isset($headers['_raw'])
            ? $headers['_raw']
            : null;

        foreach ($headers as $key => $value) {
            if (strcasecmp($key, 'From') === 0) {
                $parser = new Horde_Mail_Rfc822();
                $addresses = $parser->parseAddressList($value, array(
                    'validate' => true
                ));
                $from = $addresses[0]->bare_address;

                                if (strstr($from, ' ')) {
                    return false;
                }

                $lines[] = $key . ': ' . $this->_normalizeEOL($value);
            } elseif (!$raw && (strcasecmp($key, 'Received') === 0)) {
                $received = array();
                if (!is_array($value)) {
                    $value = array($value);
                }

                foreach ($value as $line) {
                    $received[] = $key . ': ' . $this->_normalizeEOL($line);
                }

                                                                $lines = array_merge($received, $lines);
            } elseif (!$raw) {
                                                if (is_array($value)) {
                    $value = implode(', ', $value);
                }
                $lines[] = $key . ': ' . $this->_normalizeEOL($value);
            }
        }

        return array($from, $raw ? $raw : implode($this->sep, $lines));
    }

    
    public function parseRecipients($recipients)
    {
                                $rfc822 = new Horde_Mail_Rfc822();
        return $rfc822->parseAddressList($recipients, array(
            'validate' => true
        ))->bare_addresses_idn;
    }

    
    protected function _sanitizeHeaders($headers)
    {
        foreach (array_diff(array_keys($headers), array('_raw')) as $key) {
            $headers[$key] = preg_replace('=((<CR>|<LF>|0x0A/%0A|0x0D/%0D|\\n|\\r)\S).*=i', null, $headers[$key]);
        }

        return $headers;
    }

    
    protected function _normalizeEOL($data)
    {
        return strtr($data, array(
            "\r\n" => $this->sep,
            "\r" => $this->sep,
            "\n" => $this->sep
        ));
    }

    
    protected function _getFrom($from, $headers)
    {
        
        foreach (array_keys($headers) as $hdr) {
            if (strcasecmp($hdr, 'Return-Path') === 0) {
                $from = $headers[$hdr];
                break;
            }
        }

        if (!strlen($from)) {
            throw new Horde_Mail_Exception('No from address provided.');
        }

        $from = new Horde_Mail_Rfc822_Address($from);

        return $from->bare_address_idn;
    }

}
