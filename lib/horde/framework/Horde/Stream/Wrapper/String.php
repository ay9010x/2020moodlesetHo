<?php



class Horde_Stream_Wrapper_String
{
    
    const WRAPPER_NAME = 'horde-stream-wrapper-string';

    
    public $context;

    
    protected $_pos;

    
    protected $_string;

    
    static private $_id = 0;

    
    static public function getStream(&$string)
    {
        if (!self::$_id) {
            stream_wrapper_register(self::WRAPPER_NAME, __CLASS__);
        }

        
        $ob = new stdClass;
        $ob->string = &$string;

        return fopen(
            self::WRAPPER_NAME . '://' . ++self::$_id,
            'wb',
            false,
            stream_context_create(array(
                self::WRAPPER_NAME => array(
                    'string' => $ob
                )
            ))
        );
    }

    
    public function stream_open($path, $mode, $options, &$opened_path)
    {
        $opts = stream_context_get_options($this->context);

        if (isset($opts[self::WRAPPER_NAME]['string'])) {
            $this->_string =& $opts[self::WRAPPER_NAME]['string']->string;
        } elseif (isset($opts['horde-string']['string'])) {
                        $this->_string =& $opts['horde-string']['string']->getString();
        } else {
            throw new Exception('Use ' . __CLASS__ . '::getStream() to initialize the stream.');
        }

        if (is_null($this->_string)) {
            return false;
        }

        $this->_pos = 0;

        return true;
    }

    
    public function stream_close()
    {
        $this->_string = '';
        $this->_pos = 0;
    }

    
    public function stream_read($count)
    {
        $curr = $this->_pos;
        $this->_pos += $count;
        return substr($this->_string, $curr, $count);
    }

    
    public function stream_write($data)
    {
        $len = strlen($data);

        $this->_string = substr_replace($this->_string, $data, $this->_pos, $len);
        $this->_pos += $len;

        return $len;
    }

    
    public function stream_tell()
    {
        return $this->_pos;
    }

    
    public function stream_eof()
    {
        return ($this->_pos > strlen($this->_string));
    }

    
    public function stream_stat()
    {
        return array(
            'dev' => 0,
            'ino' => 0,
            'mode' => 0,
            'nlink' => 0,
            'uid' => 0,
            'gid' => 0,
            'rdev' => 0,
            'size' => strlen($this->_string),
            'atime' => 0,
            'mtime' => 0,
            'ctime' => 0,
            'blksize' => 0,
            'blocks' => 0
        );
    }

    
    public function stream_seek($offset, $whence)
    {
        switch ($whence) {
        case SEEK_SET:
            $pos = $offset;
            break;

        case SEEK_CUR:
            $pos = $this->_pos + $offset;
            break;

        case SEEK_END:
            $pos = strlen($this->_string) + $offset;
            break;
        }

        if (($pos < 0) || ($pos > strlen($this->_string))) {
            return false;
        }

        $this->_pos = $pos;

        return true;
    }

}
