<?php



class Horde_Stream implements Serializable
{
    
    public $stream;

    
    protected $_params;

    
    protected $_utf8_char = false;

    
    public function __construct(array $opts = array())
    {
        $this->_params = $opts;
        $this->_init();
    }

    
    protected function _init()
    {
                if (!$this->stream) {
            $this->stream = @fopen('php://temp', 'r+');
        }
    }

    
    public function __get($name)
    {
        switch ($name) {
        case 'utf8_char':
            return $this->_utf8_char;
        }
    }

    
    public function __set($name, $value)
    {
        switch ($name) {
        case 'utf8_char':
            $this->_utf8_char = (bool)$value;
            break;
        }
    }

    
    public function __clone()
    {
        $data = strval($this);
        $this->stream = null;
        $this->_init();
        $this->add($data);
    }

    
    public function __toString()
    {
        $this->rewind();
        return $this->substring();
    }

    
    public function add($data, $reset = false)
    {
        if ($reset) {
            $pos = $this->pos();
        }

        if (is_resource($data)) {
            $dpos = ftell($data);
            while (!feof($data)) {
                $this->add(fread($data, 8192));
            }
            fseek($data, $dpos);
        } elseif ($data instanceof Horde_Stream) {
            $dpos = $data->pos();
            while (!$data->eof()) {
                $this->add($data->substring(0, 65536));
            }
            $data->seek($dpos, false);
        } else {
            fwrite($this->stream, $data);
        }

        if ($reset) {
            $this->seek($pos, false);
        }
    }

    
    public function length($utf8 = false)
    {
        $pos = $this->pos();

        if ($utf8 && $this->_utf8_char) {
            $this->rewind();
            $len = 0;
            while ($this->getChar() !== false) {
                ++$len;
            }
        } elseif (!$this->end()) {
            throw new Horde_Stream_Exception('ERROR');
        } else {
            $len = $this->pos();
        }

        if (!$this->seek($pos, false)) {
            throw new Horde_Stream_Exception('ERROR');
        }

        return $len;
    }

    
    public function getToChar($end, $all = true)
    {
        if (($len = strlen($end)) === 1) {
            $out = '';
            do {
                if (($tmp = stream_get_line($this->stream, 8192, $end)) === false) {
                    return $out;
                }

                $out .= $tmp;
                if ((strlen($tmp) < 8192) || ($this->peek(-1) == $end)) {
                    break;
                }
            } while (true);
        } else {
            $res = $this->search($end);

            if (is_null($res)) {
                return $this->substring();
            }

            $out = substr($this->getString(null, $res + $len - 1), 0, $len * -1);
        }

        
        if ($all) {
            while ($this->peek($len) == $end) {
                $this->seek($len);
            }
        }

        return $out;
    }

    
    public function peek($length = 1)
    {
        $out = '';

        for ($i = 0; $i < $length; ++$i) {
            if (($c = $this->getChar()) === false) {
                break;
            }
            $out .= $c;
        }

        $this->seek(strlen($out) * -1);

        return $out;
    }

    
    public function search($char, $reverse = false, $reset = true)
    {
        $found_pos = null;

        if ($len = strlen($char)) {
            $pos = $this->pos();
            $single_char = ($len === 1);

            do {
                if ($reverse) {
                    for ($i = $pos - 1; $i >= 0; --$i) {
                        $this->seek($i, false);
                        $c = $this->peek();
                        if ($c == ($single_char ? $char : substr($char, 0, strlen($c)))) {
                            $found_pos = $i;
                            break;
                        }
                    }
                } else {
                    
                    $fgetc = ($single_char && !$this->_utf8_char);

                    while (($c = ($fgetc ? fgetc($this->stream) : $this->getChar())) !== false) {
                        if ($c == ($single_char ? $char : substr($char, 0, strlen($c)))) {
                            $found_pos = $this->pos() - ($single_char ? 1 : strlen($c));
                            break;
                        }
                    }
                }

                if ($single_char ||
                    is_null($found_pos) ||
                    ($this->getString($found_pos, $found_pos + $len - 1) == $char)) {
                    break;
                }

                $this->seek($found_pos + ($reverse ? 0 : 1), false);
                $found_pos = null;
            } while (true);

            $this->seek(
                ($reset || is_null($found_pos)) ? $pos : $found_pos,
                false
            );
        }

        return $found_pos;
    }

    
    public function getString($start = null, $end = null)
    {
        if (!is_null($start) && ($start >= 0)) {
            $this->seek($start, false);
            $start = 0;
        }

        if (is_null($end)) {
            $len = null;
        } else {
            $end = ($end >= 0)
                ? $end - $this->pos() + 1
                : $this->length() - $this->pos() + $end;
            $len = max($end, 0);
        }

        return $this->substring($start, $len);
    }

    
    public function substring($start = 0, $length = null, $char = false)
    {
        if ($start !== 0) {
            $this->seek($start, true, $char);
        }

        $out = '';
        $to_end = is_null($length);

        
        if ($char &&
            $this->_utf8_char &&
            !$to_end &&
            ($length >= 0) &&
            ($length < ($this->length() - $this->pos()))) {
            while ($length-- && (($char = $this->getChar()) !== false)) {
                $out .= $char;
            }
            return $out;
        }

        if (!$to_end && ($length < 0)) {
            $pos = $this->pos();
            $this->end();
            $this->seek($length, true, $char);
            $length = $this->pos() - $pos;
            $this->seek($pos, false);
            if ($length < 0) {
                return '';
            }
        }

        while (!feof($this->stream) && ($to_end || $length)) {
            $read = fread($this->stream, $to_end ? 16384 : $length);
            $out .= $read;
            if (!$to_end) {
                $length -= strlen($read);
            }
        }

        return $out;
    }

    
    public function getEOL()
    {
        $pos = $this->pos();

        $this->rewind();
        $pos2 = $this->search("\n", false, false);
        if ($pos2) {
            $this->seek(-1);
            $eol = ($this->getChar() == "\r")
                ? "\r\n"
                : "\n";
        } else {
            $eol = is_null($pos2)
                ? null
                : "\n";
        }

        $this->seek($pos, false);

        return $eol;
    }

    
    public function getChar()
    {
        $char = fgetc($this->stream);
        if (!$this->_utf8_char) {
            return $char;
        }

        $c = ord($char);
        if ($c < 0x80) {
            return $char;
        }

        if ($c < 0xe0) {
            $n = 1;
        } elseif ($c < 0xf0) {
            $n = 2;
        } elseif ($c < 0xf8) {
            $n = 3;
        } else {
            throw new Horde_Stream_Exception('ERROR');
        }

        for ($i = 0; $i < $n; ++$i) {
            if (($c = fgetc($this->stream)) === false) {
                throw new Horde_Stream_Exception('ERROR');
            }
            $char .= $c;
        }

        return $char;
    }

    
    public function pos()
    {
        return ftell($this->stream);
    }

    
    public function rewind()
    {
        return rewind($this->stream);
    }

    
    public function seek($offset = 0, $curr = true, $char = false)
    {
        if (!$offset) {
            return (bool)$curr ?: $this->rewind();
        }

        if ($offset < 0) {
            if (!$curr) {
                return true;
            } elseif (abs($offset) > $this->pos()) {
                return $this->rewind();
            }
        }

        if ($char && $this->_utf8_char) {
            if ($offset > 0) {
                if (!$curr) {
                    $this->rewind();
                }

                do {
                    $this->getChar();
                } while (--$offset);
            } else {
                $pos = $this->pos();
                $offset = abs($offset);

                while ($pos-- && $offset) {
                    fseek($this->stream, -1, SEEK_CUR);
                    if ((ord($this->peek()) & 0xC0) != 0x80) {
                        --$offset;
                    }
                }
            }

            return true;
        }

        return (fseek($this->stream, $offset, $curr ? SEEK_CUR : SEEK_SET) === 0);
    }

    
    public function end($offset = 0)
    {
        return (fseek($this->stream, $offset, SEEK_END) === 0);
    }

    
    public function eof()
    {
        return feof($this->stream);
    }

    
    public function close()
    {
        if ($this->stream) {
            fclose($this->stream);
        }
    }

    

    
    public function serialize()
    {
        $this->_params['_pos'] = $this->pos();

        return json_encode(array(
            strval($this),
            $this->_params
        ));
    }

    
    public function unserialize($data)
    {
        $this->_init();

        $data = json_decode($data, true);
        $this->add($data[0]);
        $this->seek($data[1]['_pos'], false);
        unset($data[1]['_pos']);
        $this->_params = $data[1];
    }

}
