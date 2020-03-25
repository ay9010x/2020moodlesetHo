<?php



class Horde_Stream_Wrapper_Combine
{
    
    const WRAPPER_NAME = 'horde-stream-wrapper-combine';

    
    public $context;

    
    protected $_data = array();

    
    protected $_length = 0;

    
    protected $_position = 0;

    
    protected $_datapos = 0;

    
    protected $_ateof = false;

    
    static private $_id = 0;

    
    static public function getStream($data)
    {
        if (!self::$_id) {
            stream_wrapper_register(self::WRAPPER_NAME, __CLASS__);
        }

        return fopen(
            self::WRAPPER_NAME . '://' . ++self::$_id,
            'wb',
            false,
            stream_context_create(array(
                self::WRAPPER_NAME => array(
                    'data' => $data
                )
            ))
        );
    }
    
    public function stream_open($path, $mode, $options, &$opened_path)
    {
        $opts = stream_context_get_options($this->context);

        if (isset($opts[self::WRAPPER_NAME]['data'])) {
            $data = $opts[self::WRAPPER_NAME]['data'];
        } elseif (isset($opts['horde-combine']['data'])) {
                        $data = $opts['horde-combine']['data']->getData();
        } else {
            throw new Exception('Use ' . __CLASS__ . '::getStream() to initialize the stream.');
        }

        reset($data);
        while (list(,$val) = each($data)) {
            if (is_string($val)) {
                $fp = fopen('php://temp', 'r+');
                fwrite($fp, $val);
            } else {
                $fp = $val;
            }

            fseek($fp, 0, SEEK_END);
            $length = ftell($fp);
            rewind($fp);

            $this->_data[] = array(
                'fp' => $fp,
                'l' => $length,
                'p' => 0
            );

            $this->_length += $length;
        }

        return true;
    }

    
    public function stream_read($count)
    {
        if ($this->stream_eof()) {
            return false;
        }

        $out = '';

        while ($count) {
            $tmp = &$this->_data[$this->_datapos];
            $curr_read = min($count, $tmp['l'] - $tmp['p']);
            $out .= fread($tmp['fp'], $curr_read);
            $count -= $curr_read;
            $this->_position += $curr_read;

            if ($this->_position == $this->_length) {
                if ($count) {
                    $this->_ateof = true;
                    break;
                } else {
                    $tmp['p'] += $curr_read;
                }
            } elseif ($count) {
                $tmp = &$this->_data[++$this->_datapos];
                rewind($tmp['fp']);
                $tmp['p'] = 0;
            } else {
                $tmp['p'] += $curr_read;
            }
        }

        return $out;
    }

    
    public function stream_write($data)
    {
        $tmp = &$this->_data[$this->_datapos];

        $oldlen = $tmp['l'];
        $res = fwrite($tmp['fp'], $data);
        if ($res === false) {
            return false;
        }

        $tmp['p'] = ftell($tmp['fp']);
        if ($tmp['p'] > $oldlen) {
            $tmp['l'] = $tmp['p'];
            $this->_length += ($tmp['l'] - $oldlen);
        }

        return $res;
    }

    
    public function stream_tell()
    {
        return $this->_position;
    }

    
    public function stream_eof()
    {
        return $this->_ateof;
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
            'size' => $this->_length,
            'atime' => 0,
            'mtime' => 0,
            'ctime' => 0,
            'blksize' => 0,
            'blocks' => 0
        );
    }

    
    public function stream_seek($offset, $whence)
    {
        $oldpos = $this->_position;
        $this->_ateof = false;

        switch ($whence) {
        case SEEK_SET:
            $offset = $offset;
            break;

        case SEEK_CUR:
            $offset = $this->_position + $offset;
            break;

        case SEEK_END:
            $offset = $this->_length + $offset;
            break;

        default:
            return false;
        }

        $count = $this->_position = min($this->_length, $offset);

        foreach ($this->_data as $key => $val) {
            if ($count < $val['l']) {
                $this->_datapos = $key;
                $val['p'] = $count;
                fseek($val['fp'], $count, SEEK_SET);
                break;
            }
            $count -= $val['l'];
        }

        return ($oldpos != $this->_position);
    }

}
