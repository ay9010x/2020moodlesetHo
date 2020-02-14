<?php



class Horde_Imap_Client_Tokenize implements Iterator
{
    
    protected $_current = false;

    
    protected $_key = false;

    
    protected $_level = false;

    
    protected $_nextModify = array();

    
    protected $_stream;

    
    public function __construct($data = null)
    {
        $this->_stream = new Horde_Stream_Temp();

        if (!is_null($data)) {
            $this->add($data);
        }
    }

    
    public function __get($name)
    {
        switch ($name) {
        case 'eos':
            return $this->_stream->eof();
        }
    }

    
    public function __sleep()
    {
        throw new LogicException('Object can not be serialized.');
    }

    
    public function __toString()
    {
        $pos = $this->_stream->pos();
        $out = $this->_current . ' ' . $this->_stream->getString();
        $this->_stream->seek($pos, false);
        return $out;
    }

    
    public function add($data)
    {
        $this->_stream->add($data);
    }

    
    public function flushIterator($return = true, $sublevel = true)
    {
        $out = array();

        if ($return) {
            $this->_nextModify = array(
                'level' => $sublevel ? $this->_level : 0,
                'out' => array()
            );
            $this->next();
            $out = $this->_nextModify['out'];
            $this->_nextModify = array();
        } elseif ($sublevel && $this->_level) {
            $this->_nextModify = array(
                'level' => $this->_level
            );
            $this->next();
            $this->_nextModify = array();
        } else {
            $this->_stream->end();
            $this->_stream->getChar();
            $this->_current = $this->_key = $this->_level = false;
        }

        return $out;
    }

    
    public function getLiteralLength()
    {
        $this->_stream->end(-1);
        if ($this->_stream->peek() === '}') {
            $literal_data = $this->_stream->getString($this->_stream->search('{', true) - 1);
            $literal_len = substr($literal_data, 2, -1);

            if (is_numeric($literal_len)) {
                return array(
                    'binary' => ($literal_data[0] === '~'),
                    'length' => intval($literal_len)
                );
            }
        }

        return null;
    }

    

    
    public function current()
    {
        return $this->_current;
    }

    
    public function key()
    {
        return $this->_key;
    }

    
    public function next()
    {
        $level = isset($this->_nextModify['level'])
            ? $this->_nextModify['level']
            : null;
        
        $stream = $this->_stream->stream;

        do {
            $check_len = true;
            $in_quote = $text = false;

            while (($c = fgetc($stream)) !== false) {
                switch ($c) {
                case '\\':
                    $text .= $in_quote
                        ? fgetc($stream)
                        : $c;
                    break;

                case '"':
                    if ($in_quote) {
                        $check_len = false;
                        break 2;
                    }
                    $in_quote = true;
                    
                    $text = '';
                    break;

                default:
                    if ($in_quote) {
                        $text .= $c;
                        break;
                    }

                    switch ($c) {
                    case '(':
                        ++$this->_level;
                        $check_len = false;
                        $text = true;
                        break 3;

                    case ')':
                        if ($text === false) {
                            --$this->_level;
                            $check_len = $text = false;
                        } else {
                            $this->_stream->seek(-1);
                        }
                        break 3;

                    case '~':
                                                                        break;

                    case '{':
                        $text = $this->_stream->substring(
                            0,
                            intval($this->_stream->getToChar('}'))
                        );
                        $check_len = false;
                        break 3;

                    case ' ':
                        if ($text !== false) {
                            break 3;
                        }
                        break;

                    default:
                        $text .= $c;
                        break;
                    }
                    break;
                }
            }

            if ($check_len) {
                switch (strlen($text)) {
                case 0:
                    $text = false;
                    break;

                case 3:
                    if (($text === 'NIL') || (strcasecmp($text, 'NIL') === 0)) {
                        $text = null;
                    }
                    break;
                }
            }

            if (($text === false) && feof($stream)) {
                $this->_key = $this->_level = false;
                break;
            }

            ++$this->_key;

            if (is_null($level) || ($level > $this->_level)) {
                break;
            }

            if (($level === $this->_level) && !is_bool($text)) {
                $this->_nextModify['out'][] = $text;
            }
        } while (true);

        $this->_current = $text;

        return $text;
    }

    
    public function rewind()
    {
        $this->_stream->rewind();
        $this->_current = false;
        $this->_key = -1;
        $this->_level = 0;
    }

    
    public function valid()
    {
        return ($this->_level !== false);
    }

}
