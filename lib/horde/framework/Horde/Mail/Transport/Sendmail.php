<?php



class Horde_Mail_Transport_Sendmail extends Horde_Mail_Transport
{
    
    protected $_sendmailArgs = '-i';

    
    protected $_sendmailPath = '/usr/sbin/sendmail';

    
    public function __construct(array $params = array())
    {
        if (isset($params['sendmail_args'])) {
            $this->_sendmailArgs = $params['sendmail_args'];
        }

        if (isset($params['sendmail_path'])) {
            $this->_sendmailPath = $params['sendmail_path'];
        }
    }

    
    public function send($recipients, array $headers, $body)
    {
        $recipients = implode(' ', array_map('escapeshellarg', $this->parseRecipients($recipients)));

        $headers = $this->_sanitizeHeaders($headers);
        list($from, $text_headers) = $this->prepareHeaders($headers);
        $from = $this->_getFrom($from, $headers);

        $mail = @popen($this->_sendmailPath . (empty($this->_sendmailArgs) ? '' : ' ' . $this->_sendmailArgs) . ' -f ' . escapeshellarg($from) . ' -- ' . $recipients, 'w');
        if (!$mail) {
            throw new Horde_Mail_Exception('Failed to open sendmail [' . $this->_sendmailPath . '] for execution.');
        }

                        fputs($mail, $text_headers . $this->sep . $this->sep);

        if (is_resource($body)) {
            stream_filter_register('horde_eol', 'Horde_Stream_Filter_Eol');
            stream_filter_append($body, 'horde_eol', STREAM_FILTER_READ, array('eol' => $this->sep));

            rewind($body);
            while (!feof($body)) {
                fputs($mail, fread($body, 8192));
            }
        } else {
            fputs($mail, $this->_normalizeEOL($body));
        }
        $result = pclose($mail);

        if (!$result) {
            return;
        }

        switch ($result) {
        case 64:             $msg = 'command line usage error';
            break;

        case 65:             $msg =  'data format error';
            break;

        case 66:             $msg = 'cannot open input';
            break;

        case 67:             $msg = 'addressee unknown';
            break;

        case 68:             $msg = 'host name unknown';
            break;

        case 69:             $msg = 'service unavailable';
            break;

        case 70:             $msg = 'internal software error';
            break;

        case 71:             $msg = 'system error';
            break;

        case 72:             $msg = 'critical system file missing';
            break;

        case 73:             $msg = 'cannot create output file';
            break;

        case 74:             $msg = 'input/output error';

        case 75:             $msg = 'temporary failure';
            break;

        case 76:             $msg = 'remote error in protocol';
            break;

        case 77:             $msg = 'permission denied';
            break;

        case 78:             $msg = 'configuration error';
            break;

        case 79:             $msg = 'entry not found';
            break;

        default:
            $msg = 'unknown error';
            break;
        }

        throw new Horde_Mail_Exception('sendmail: ' . $msg . ' (' . $result . ')', $result);
    }

}
