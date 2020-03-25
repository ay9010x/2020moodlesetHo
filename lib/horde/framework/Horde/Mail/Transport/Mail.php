<?php



class Horde_Mail_Transport_Mail extends Horde_Mail_Transport
{
    
    public function __construct(array $params = array())
    {
        $this->_params = array_merge($this->_params, $params);
    }

    
    public function send($recipients, array $headers, $body)
    {
        $headers = $this->_sanitizeHeaders($headers);
        $recipients = $this->parseRecipients($recipients);
        $subject = '';

        foreach (array_keys($headers) as $hdr) {
            if (strcasecmp($hdr, 'Subject') === 0) {
                                                $subject = $headers[$hdr];
                unset($headers[$hdr]);
            } elseif (strcasecmp($hdr, 'To') === 0) {
                                                unset($headers[$hdr]);
            }
        }

                list(, $text_headers) = $this->prepareHeaders($headers);

                        if (is_resource($body)) {
            $body_str = '';

            stream_filter_register('horde_eol', 'Horde_Stream_Filter_Eol');
            stream_filter_append($body, 'horde_eol', STREAM_FILTER_READ, array('eol' => $this->sep));

            rewind($body);
            while (!feof($body)) {
                $body_str .= fread($body, 8192);
            }
            $body = $body_str;
        } else {
                        $body = $this->_normalizeEOL($body);
        }

                        if (empty($this->_params) || ini_get('safe_mode')) {
            $result = mail($recipients, $subject, $body, $text_headers);
        } else {
            $result = mail($recipients, $subject, $body, $text_headers, isset($this->_params['args']) ? $this->_params['args'] : '');
        }

                        if ($result === false) {
            throw new Horde_Mail_Exception('mail() returned failure.');
        }
    }

}
