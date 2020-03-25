<?php



class Horde_Imap_Client_Data_Fetch
{
    
    const HEADER_PARSE = 1;
    const HEADER_STREAM = 2;

    
    protected $_data = array();

    
    public function setFullMsg($msg)
    {
        $this->_data[Horde_Imap_Client::FETCH_FULLMSG] = $msg;
    }

    
    public function getFullMsg($stream = false)
    {
        return $this->_msgText($stream, isset($this->_data[Horde_Imap_Client::FETCH_FULLMSG]) ? $this->_data[Horde_Imap_Client::FETCH_FULLMSG] : null);
    }

    
    public function setStructure(Horde_Mime_Part $structure)
    {
        $this->_data[Horde_Imap_Client::FETCH_STRUCTURE] = $structure;
    }

    
    public function getStructure()
    {
        return isset($this->_data[Horde_Imap_Client::FETCH_STRUCTURE])
            ? clone $this->_data[Horde_Imap_Client::FETCH_STRUCTURE]
            : new Horde_Mime_Part();
    }

    
    public function setHeaders($label, $data)
    {
        $this->_data[Horde_Imap_Client::FETCH_HEADERS][$label] = $data;
    }

    
    public function getHeaders($label, $format = 0)
    {
        return $this->_getHeaders($label, $format, Horde_Imap_Client::FETCH_HEADERS);
    }

    
    public function setHeaderText($id, $text)
    {
        $this->_data[Horde_Imap_Client::FETCH_HEADERTEXT][$id] = $text;
    }

    
    public function getHeaderText($id = 0, $format = 0)
    {
        return $this->_getHeaders($id, $format, Horde_Imap_Client::FETCH_HEADERTEXT);
    }

    
    public function setMimeHeader($id, $text)
    {
        $this->_data[Horde_Imap_Client::FETCH_MIMEHEADER][$id] = $text;
    }

    
    public function getMimeHeader($id, $format = 0)
    {
        return $this->_getHeaders($id, $format, Horde_Imap_Client::FETCH_MIMEHEADER);
    }

    
    public function setBodyPart($id, $text, $decode = null)
    {
        $this->_data[Horde_Imap_Client::FETCH_BODYPART][$id] = array(
            'd' => $decode,
            't' => $text
        );
    }

    
    public function setBodyPartSize($id, $size)
    {
        $this->_data[Horde_Imap_Client::FETCH_BODYPARTSIZE][$id] = intval($size);
    }

    
    public function getBodyPart($id, $stream = false)
    {
        return $this->_msgText($stream, isset($this->_data[Horde_Imap_Client::FETCH_BODYPART][$id]) ? $this->_data[Horde_Imap_Client::FETCH_BODYPART][$id]['t'] : null);
    }

    
    public function getBodyPartDecode($id)
    {
        return isset($this->_data[Horde_Imap_Client::FETCH_BODYPART][$id])
            ? $this->_data[Horde_Imap_Client::FETCH_BODYPART][$id]['d']
            : null;
    }

    
    public function getBodyPartSize($id)
    {
        return isset($this->_data[Horde_Imap_Client::FETCH_BODYPARTSIZE][$id])
            ? $this->_data[Horde_Imap_Client::FETCH_BODYPARTSIZE][$id]
            : null;
    }

    
    public function setBodyText($id, $text)
    {
        $this->_data[Horde_Imap_Client::FETCH_BODYTEXT][$id] = $text;
    }

    
    public function getBodyText($id = 0, $stream = false)
    {
        return $this->_msgText($stream, isset($this->_data[Horde_Imap_Client::FETCH_BODYTEXT][$id]) ? $this->_data[Horde_Imap_Client::FETCH_BODYTEXT][$id] : null);
    }

    
    public function setEnvelope($data)
    {
        $this->_data[Horde_Imap_Client::FETCH_ENVELOPE] = is_array($data)
            ? new Horde_Imap_Client_Data_Envelope($data)
            : $data;
    }

    
    public function getEnvelope()
    {
        return isset($this->_data[Horde_Imap_Client::FETCH_ENVELOPE])
            ? clone $this->_data[Horde_Imap_Client::FETCH_ENVELOPE]
            : new Horde_Imap_Client_Data_Envelope();
    }

    
    public function setFlags(array $flags)
    {
        $this->_data[Horde_Imap_Client::FETCH_FLAGS] = array_map('strtolower', $flags);
    }

    
    public function getFlags()
    {
        return isset($this->_data[Horde_Imap_Client::FETCH_FLAGS])
            ? $this->_data[Horde_Imap_Client::FETCH_FLAGS]
            : array();
    }

    
    public function setImapDate($date)
    {
        $this->_data[Horde_Imap_Client::FETCH_IMAPDATE] = is_object($date)
            ? $date
            : new Horde_Imap_Client_DateTime($date);
    }

    
    public function getImapDate()
    {
        return isset($this->_data[Horde_Imap_Client::FETCH_IMAPDATE])
            ? clone $this->_data[Horde_Imap_Client::FETCH_IMAPDATE]
            : new Horde_Imap_Client_DateTime();
    }

    
    public function setSize($size)
    {
        $this->_data[Horde_Imap_Client::FETCH_SIZE] = intval($size);
    }

    
    public function getSize()
    {
        return isset($this->_data[Horde_Imap_Client::FETCH_SIZE])
            ? $this->_data[Horde_Imap_Client::FETCH_SIZE]
            : 0;
    }

    
    public function setUid($uid)
    {
        $this->_data[Horde_Imap_Client::FETCH_UID] = intval($uid);
    }

    
    public function getUid()
    {
        return isset($this->_data[Horde_Imap_Client::FETCH_UID])
            ? $this->_data[Horde_Imap_Client::FETCH_UID]
            : null;
    }

    
    public function setSeq($seq)
    {
        $this->_data[Horde_Imap_Client::FETCH_SEQ] = intval($seq);
    }

    
    public function getSeq()
    {
        return isset($this->_data[Horde_Imap_Client::FETCH_SEQ])
            ? $this->_data[Horde_Imap_Client::FETCH_SEQ]
            : null;
    }

    
    public function setModSeq($modseq)
    {
        $this->_data[Horde_Imap_Client::FETCH_MODSEQ] = intval($modseq);
    }

    
    public function getModSeq()
    {
        return isset($this->_data[Horde_Imap_Client::FETCH_MODSEQ])
            ? $this->_data[Horde_Imap_Client::FETCH_MODSEQ]
            : null;
    }

    
    public function setDowngraded($downgraded)
    {
        if ($downgraded) {
            $this->_data[Horde_Imap_Client::FETCH_DOWNGRADED] = true;
        } else {
            unset($this->_data[Horde_Imap_Client::FETCH_DOWNGRADED]);
        }
    }

    
    public function isDowngraded()
    {
        return !empty($this->_data[Horde_Imap_Client::FETCH_DOWNGRADED]);
    }

    
    public function getRawData()
    {
        return $this->_data;
    }

    
    public function merge(Horde_Imap_Client_Data_Fetch $data)
    {
        $this->_data = array_replace_recursive($this->_data, $data->getRawData());
    }

    
    public function exists($type)
    {
        return isset($this->_data[$type]);
    }

    
    public function isDefault()
    {
        return empty($this->_data);
    }

    
    protected function _msgText($stream, $data)
    {
        if ($stream) {
            if (is_resource($data)) {
                rewind($data);
                return $data;
            }

            $tmp = fopen('php://temp', 'w+');

            if (!is_null($data)) {
                fwrite($tmp, $data);
                rewind($tmp);
            }

            return $tmp;
        }

        if (is_resource($data)) {
            rewind($data);
            return stream_get_contents($data);
        }

        return strval($data);
    }

    
    protected function _getHeaders($id, $format, $key)
    {
        switch ($format) {
        case self::HEADER_STREAM:
            if (!isset($this->_data[$key][$id])) {
                return $this->_msgText(true, null);
            } elseif (is_object($this->_data[$key][$id])) {
                return $this->_getHeaders($id, 0, $key);
            }
            return $this->_msgText(true, $this->_data[$key][$id]);

        case self::HEADER_PARSE:
            if (!isset($this->_data[$key][$id])) {
                return new Horde_Mime_Headers();
            } elseif (is_object($this->_data[$key][$id])) {
                return clone $this->_data[$key][$id];
            }
            return Horde_Mime_Headers::parseHeaders($this->_getHeaders($id, self::HEADER_STREAM, $key));
        }

        if (!isset($this->_data[$key][$id])) {
            return '';
        }

        return is_object($this->_data[$key][$id])
            ? $this->_data[$key][$id]->toString(array('nowrap' => true))
            : $this->_msgText(false, $this->_data[$key][$id]);
    }

}
