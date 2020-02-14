<?php



class Horde_Mail_Transport_Mock extends Horde_Mail_Transport
{
    
    public $sentMessages = array();

    
    protected $_preSendCallback;

    
    protected $_postSendCallback;

    
    public function __construct(array $params = array())
    {
        if (isset($params['preSendCallback']) &&
            is_callable($params['preSendCallback'])) {
            $this->_preSendCallback = $params['preSendCallback'];
        }

        if (isset($params['postSendCallback']) &&
            is_callable($params['postSendCallback'])) {
            $this->_postSendCallback = $params['postSendCallback'];
        }
    }

    
    public function send($recipients, array $headers, $body)
    {
        if ($this->_preSendCallback) {
            call_user_func_array($this->_preSendCallback, array($this, $recipients, $headers, $body));
        }

        $headers = $this->_sanitizeHeaders($headers);
        list($from, $text_headers) = $this->prepareHeaders($headers);

        if (is_resource($body)) {
            stream_filter_register('horde_eol', 'Horde_Stream_Filter_Eol');
            stream_filter_append($body, 'horde_eol', STREAM_FILTER_READ, array('eol' => $this->sep));

            rewind($body);
            $body_txt = stream_get_contents($body);
        } else {
            $body_txt = $this->_normalizeEOL($body);
        }

        $from = $this->_getFrom($from, $headers);
        $recipients = $this->parseRecipients($recipients);

        $this->sentMessages[] = array(
            'body' => $body_txt,
            'from' => $from,
            'headers' => $headers,
            'header_text' => $text_headers,
            'recipients' => $recipients
        );

        if ($this->_postSendCallback) {
            call_user_func_array($this->_postSendCallback, array($this, $recipients, $headers, $body_txt));
        }
    }

}
