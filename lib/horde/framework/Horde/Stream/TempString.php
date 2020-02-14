<?php



class Horde_Stream_TempString extends Horde_Stream_Temp
{
    
    protected $_string;

    
    public function __construct(array $opts = array())
    {
        parent::__construct($opts);

        $temp = '';
        $this->_string = new Horde_Stream_String(array(
            'string' => $temp
        ));
    }

    
    protected function _init()
    {
        if (!isset($this->_params['max_memory'])) {
            $this->_params['max_memory'] = 2097152;
        }

        if (!$this->_string) {
            parent::_init();
        }
    }

    
    public function __get($name)
    {
        switch ($name) {
        case 'stream':
            if ($this->_string) {
                return $this->_string->stream;
            }
            break;

        case 'use_stream':
            return !(bool)$this->_string;
        }

        return parent::__get($name);
    }

    
    public function __set($name, $value)
    {
        switch ($name) {
        case 'utf8_char':
            if ($this->_string) {
                $this->_string->utf8_char = $value;
            }
            break;
        }

        parent::__set($name, $value);
    }

    
    public function __clone()
    {
        if ($this->_string) {
            $this->_string = clone $this->_string;
        } else {
            parent::__clone();
        }
    }

    
    public function __toString()
    {
        return $this->_string
            ? strval($this->_string)
            : parent::__toString();
    }

    
    public function add($data, $reset = false)
    {
        if ($this->_string && is_string($data)) {
            if ((strlen($data) + $this->_string->length()) < $this->_params['max_memory']) {
                $this->_string->add($data, $reset);
                return;
            }

            parent::_init();
            parent::add(strval($this->_string));
            $this->seek($this->_string->pos(), false);
            unset($this->_string);
        }

        parent::add($data, $reset);
    }

    
    public function length($utf8 = false)
    {
        return $this->_string
            ? $this->_string->length($utf8)
            : parent::length($utf8);
    }

    
    public function getToChar($end, $all = true)
    {
        return $this->_string
            ? $this->_string->getToChar($end, $all)
            : parent::getToChar($end, $all);
    }


    
    public function peek($length = 1)
    {
        return $this->_string
            ? $this->_string->peek($length)
            : parent::peek($length);
    }

    
    public function search($char, $reverse = false, $reset = true)
    {
        return $this->_string
            ? $this->_string->search($char, $reverse, $reset)
            : parent::search($char, $reverse, $reset);
    }

    
    public function getString($start = null, $end = null)
    {
        return $this->_string
            ? $this->_string->getString($start, $end)
            : parent::getString($start, $end);
    }

    
    public function substring($start = 0, $length = null, $char = false)
    {
        return $this->_string
            ? $this->_string->substring($start, $length, $char)
            : parent::substring($start, $length, $char);
    }

    
    public function getChar()
    {
        return $this->_string
            ? $this->_string->getChar()
            : parent::getChar();
    }

    
    public function pos()
    {
        return $this->_string
            ? $this->_string->pos()
            : parent::pos();
    }

    
    public function rewind()
    {
        return $this->_string
            ? $this->_string->rewind()
            : parent::rewind();
    }

    
    public function seek($offset = 0, $curr = true, $char = false)
    {
        return $this->_string
            ? $this->_string->seek($offset, $curr, $char)
            : parent::seek($offset, $curr, $char);
    }

    
    public function end($offset = 0)
    {
        return $this->_string
            ? $this->_string->end($offset)
            : parent::end($offset);
    }

    
    public function eof()
    {
        return $this->_string
            ? $this->_string->eof()
            : parent::eof();
    }

    

    
    public function serialize()
    {
        if ($this->_string) {
            $data = array(
                $this->_string,
                $this->_params
            );
        } else {
            $data = parent::serialize();
        }

        return serialize($data);
    }

    
    public function unserialize($data)
    {
        $data = unserialize($data);
        if ($data[0] instanceof Horde_Stream_String) {
            $this->_string = $data[0];
            $this->_params = $data[1];
        } else {
            parent::unserialize($data);
        }
    }

}
