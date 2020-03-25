<?php

class Horde_Mime_Mail
{
    
    protected $_headers;

    
    protected $_base;

    
    protected $_body;

    
    protected $_htmlBody;

    
    protected $_recipients;

    
    protected $_bcc;

    
    protected $_parts = array();

    
    protected $_mailer_driver = 'smtp';

    
    protected $_charset = 'UTF-8';

    
    protected $_mailer_params = array();

    
    public function __construct($params = array())
    {
        
        if (!isset($_SERVER['SERVER_NAME'])) {
            $_SERVER['SERVER_NAME'] = php_uname('n');
        }

        $this->_headers = new Horde_Mime_Headers();

        if (isset($params['charset'])) {
            $this->_charset = $params['charset'];
            unset($params['charset']);
        }

        if (isset($params['body'])) {
            $this->setBody($params['body'], $this->_charset);
            unset($params['body']);
        }

        $this->addHeaders($params);

        $this->clearRecipients();
    }

    
    public function addHeaders($headers = array())
    {
        foreach ($headers as $header => $value) {
            $this->addHeader($header, $value);
        }
    }

    
    public function addHeader($header, $value, $overwrite = null)
    {
        $lc_header = Horde_String::lower($header);

        if (is_null($overwrite) &&
            in_array($lc_header, $this->_headers->singleFields(true))) {
            $overwrite = true;
        }

        if ($overwrite) {
            $this->_headers->removeHeader($header);
        }

        if ($lc_header === 'bcc') {
            $this->_bcc = $value;
        } else {
            $this->_headers->addHeader($header, $value);
        }
    }

    
    public function removeHeader($header)
    {
        if (Horde_String::lower($header) === 'bcc') {
            unset($this->_bcc);
        } else {
            $this->_headers->removeHeader($header);
        }
    }

    
    public function setBody($body, $charset = null, $wrap = false)
    {
        if (!$charset) {
            $charset = $this->_charset;
        }
        $body = Horde_String::convertCharset($body, 'UTF-8', $charset);
        if ($wrap) {
            $body = Horde_String::wrap($body, $wrap === true ? 76 : $wrap);
        }
        $this->_body = new Horde_Mime_Part();
        $this->_body->setType('text/plain');
        $this->_body->setCharset($charset);
        $this->_body->setContents($body);
        $this->_base = null;
    }

    
    public function setHtmlBody($body, $charset = null, $alternative = true)
    {
        if (!$charset) {
            $charset = $this->_charset;
        }
        $this->_htmlBody = new Horde_Mime_Part();
        $this->_htmlBody->setType('text/html');
        $this->_htmlBody->setCharset($charset);
        $this->_htmlBody->setContents($body);
        if ($alternative) {
            $this->setBody(Horde_Text_Filter::filter($body, 'Html2text', array('charset' => $charset, 'wrap' => false)), $charset);
        }
        $this->_base = null;
    }

    
    public function addPart($mime_type, $content, $charset = 'us-ascii',
                            $disposition = null)
    {
        $part = new Horde_Mime_Part();
        $part->setType($mime_type);
        $part->setCharset($charset);
        $part->setDisposition($disposition);
        $part->setContents($content);
        return $this->addMimePart($part);
    }

    
    public function addMimePart($part)
    {
        $this->_parts[] = $part;
        return count($this->_parts) - 1;
    }

    
    public function setBasePart($part)
    {
        $this->_base = $part;
    }

    
    public function addAttachment($file, $name = null, $type = null,
                                  $charset = 'us-ascii')
    {
        if (empty($name)) {
            $name = basename($file);
        }

        if (empty($type)) {
            $type = Horde_Mime_Magic::filenameToMime($file, false);
        }

        $num = $this->addPart($type, file_get_contents($file), $charset, 'attachment');
        $this->_parts[$num]->setName($name);
        return $num;
    }

    
    public function removePart($part)
    {
        if (isset($this->_parts[$part])) {
            unset($this->_parts[$part]);
        }
    }

    
    public function clearParts()
    {
        $this->_parts = array();
    }

    
    public function addRecipients($recipients)
    {
        $this->_recipients->add($recipients);
    }

    
    public function removeRecipients($recipients)
    {
        $this->_recipients->remove($recipients);
    }

    
    public function clearRecipients()
    {
        $this->_recipients = new Horde_Mail_Rfc822_List();
    }

    
    public function send($mailer, $resend = false, $flowed = true)
    {
        
        $has_header = $this->_headers->getValue('Message-ID');
        if (!$resend || !$has_header) {
            if ($has_header) {
                $this->_headers->removeHeader('Message-ID');
            }
            $this->_headers->addMessageIdHeader();
        }
        if (!$this->_headers->getValue('User-Agent')) {
            $this->_headers->addUserAgentHeader();
        }
        $has_header = $this->_headers->getValue('Date');
        if (!$resend || !$has_header) {
            if ($has_header) {
                $this->_headers->removeHeader('Date');
            }
            $this->_headers->addHeader('Date', date('r'));
        }

        if (isset($this->_base)) {
            $basepart = $this->_base;
        } else {
            
            if ($flowed && !empty($this->_body)) {
                $flowed = new Horde_Text_Flowed($this->_body->getContents(), $this->_body->getCharset());
                $flowed->setDelSp(true);
                $this->_body->setContentTypeParameter('format', 'flowed');
                $this->_body->setContentTypeParameter('DelSp', 'Yes');
                $this->_body->setContents($flowed->toFlowed());
            }

            
            $body = new Horde_Mime_Part();
            if (!empty($this->_body) && !empty($this->_htmlBody)) {
                $body->setType('multipart/alternative');
                $this->_body->setDescription(Horde_Mime_Translation::t("Plaintext Version of Message"));
                $body->addPart($this->_body);
                $this->_htmlBody->setDescription(Horde_Mime_Translation::t("HTML Version of Message"));
                $body->addPart($this->_htmlBody);
            } elseif (!empty($this->_htmlBody)) {
                $body = $this->_htmlBody;
            } elseif (!empty($this->_body)) {
                $body = $this->_body;
            }
            if (count($this->_parts)) {
                $basepart = new Horde_Mime_Part();
                $basepart->setType('multipart/mixed');
                $basepart->isBasePart(true);
                if ($body) {
                    $basepart->addPart($body);
                }
                foreach ($this->_parts as $mime_part) {
                    $basepart->addPart($mime_part);
                }
            } else {
                $basepart = $body;
                $basepart->isBasePart(true);
            }
        }
        $basepart->setHeaderCharset($this->_charset);

        
        $recipients = clone $this->_recipients;
        foreach (array('to', 'cc') as $header) {
            $recipients->add($this->_headers->getOb($header));
        }
        if ($this->_bcc) {
            $recipients->add($this->_bcc);
        }

        
        $this->_headers->removeHeader('MIME-Version');

        
        $recipients->unique();
        $basepart->send($recipients->writeAddress(), $this->_headers, $mailer);

        
        $this->_base = $basepart;
    }

    
    public function getRaw($stream = true)
    {
        if ($stream) {
            $hdr = new Horde_Stream();
            $hdr->add($this->_headers->toString(), true);
            return Horde_Stream_Wrapper_Combine::getStream(array($hdr->stream, $this->getBasePart()->toString(array('stream' => true))));
        }

        return $this->_headers->toString() . $this->_getBasePart->toString();
    }

    
    public function getBasePart()
    {
        if (empty($this->_base)) {
            throw new Horde_Mail_Exception('No base part set.');
        }

        return $this->_base;
    }

}
