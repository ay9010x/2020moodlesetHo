<?php

class Horde_Mime_Mdn
{
    
    const MDN_HEADER = 'Disposition-Notification-To';

    
    protected $_headers;

    
    protected $_msgtext = false;

    
    public function __construct(Horde_Mime_Headers $headers)
    {
        $this->_headers = $headers;
    }

    
    public function getMdnReturnAddr()
    {
        
        return $this->_headers->getValue(self::MDN_HEADER);
    }

    
    public function userConfirmationNeeded()
    {
        $return_path = $this->_headers->getValue('Return-Path');

        
        if (empty($return_path) || is_array($return_path)) {
            return true;
        }

        
        $rfc822 = new Horde_Mail_Rfc822();
        $addr_ob = $rfc822->parseAddressList($this->getMdnReturnAddr());

        switch (count($addr_ob)) {
        case 0:
            return false;

        case 1:
                        break;

        default:
            return true;
        }

        
        $ret_ob = new Horde_Mail_Rfc822_Address($return_path);

        return ($ret_ob->valid &&
                ($addr_ob->bare_address == $ret_ob->bare_address));
    }

    
    public function originalMessageText($text)
    {
        $this->_msgtext = $text;
    }

    
    public function generate($action, $sending, $type, $name, $mailer,
                             array $opts = array(), array $mod = array(),
                             array $err = array())
    {
        $opts = array_merge(array(
            'charset' => null,
            'from_addr' => null
        ), $opts);

        $to = $this->getMdnReturnAddr();
        $ua = $this->_headers->getUserAgent();

        $orig_recip = $this->_headers->getValue('Original-Recipient');
        if (!empty($orig_recip) && is_array($orig_recip)) {
            $orig_recip = $orig_recip[0];
        }

        $msg_id = $this->_headers->getValue('Message-ID');

        
        $dispo = 'Disposition: ' .
                 (($action) ? 'manual-action' : 'automatic-action') .
                 '/' .
                 (($sending) ? 'MDN-sent-manually' : 'MDN-sent-automatically') .
                 '; ' .
                 $type;
        if (!empty($mod)) {
            $dispo .= '/' . implode(', ', $mod);
        }

        
        $msg_headers = new Horde_Mime_Headers();
        $msg_headers->addMessageIdHeader();
        $msg_headers->addUserAgentHeader($ua);
        $msg_headers->addHeader('Date', date('r'));
        if ($opts['from_addr']) {
            $msg_headers->addHeader('From', $opts['from_addr']);
        }
        $msg_headers->addHeader('To', $this->getMdnReturnAddr());
        $msg_headers->addHeader('Subject', Horde_Mime_Translation::t("Disposition Notification"));

        
        $msg = new Horde_Mime_Part();
        $msg->setType('multipart/report');
        $msg->setContentTypeParameter('report-type', 'disposition-notification');

        
        $part_one = new Horde_Mime_Part();
        $part_one->setType('text/plain');
        $part_one->setCharset($opts['charset']);
        if ($type == 'displayed') {
            $contents = sprintf(Horde_Mime_Translation::t("The message sent on %s to %s with subject \"%s\" has been displayed.\n\nThis is no guarantee that the message has been read or understood."), $this->_headers->getValue('Date'), $this->_headers->getValue('To'), $this->_headers->getValue('Subject'));
            $flowed = new Horde_Text_Flowed($contents, $opts['charset']);
            $flowed->setDelSp(true);
            $part_one->setContentTypeParameter('format', 'flowed');
            $part_one->setContentTypeParameter('DelSp', 'Yes');
            $part_one->setContents($flowed->toFlowed());
        }
                $msg->addPart($part_one);

        
        $part_two = new Horde_Mime_Part();
        $part_two->setType('message/disposition-notification');
        $part_two_text = array('Reporting-UA: ' . $name . '; ' . $ua . "\n");
        if (!empty($orig_recip)) {
            $part_two_text[] = 'Original-Recipient: rfc822;' . $orig_recip . "\n";
        }
        if ($opts['from_addr']) {
            $part_two_text[] = 'Final-Recipient: rfc822;' . $opts['from_addr'] . "\n";
        }
        if (!empty($msg_id)) {
            $part_two_text[] = 'Original-Message-ID: rfc822;' . $msg_id . "\n";
        }
        $part_two_text[] = $dispo . "\n";
        if (in_array('error', $mod) && isset($err['error'])) {
            $part_two_text[] = 'Error: ' . $err['error'] . "\n";
        }
        $part_two->setContents($part_two_text);
        $msg->addPart($part_two);

        
        $part_three = new Horde_Mime_Part();
        $part_three->setType('message/rfc822');
        $part_three_text = array($this->_headers->toString());
        if (!empty($this->_msgtext)) {
            $part_three_text[] = $part_three->getEOL() . $this->_msgtext;
        }
        $part_three->setContents($part_three_text);
        $msg->addPart($part_three);

        return $msg->send($to, $msg_headers, $mailer);
    }

    
    public function addMdnRequestHeaders($to)
    {
        
        $this->_headers->addHeader(self::MDN_HEADER, $to);
    }

}
