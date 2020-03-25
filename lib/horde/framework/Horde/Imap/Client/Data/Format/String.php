<?php



class Horde_Imap_Client_Data_Format_String extends Horde_Imap_Client_Data_Format
{
    
    protected $_filter;

    
    public function __construct($data, array $opts = array())
    {
        
        $this->_data = new Horde_Stream_Temp();

        $this->_filter = $this->_filterParams();

        if (empty($opts['skipscan'])) {
            stream_filter_register('horde_imap_client_string', 'Horde_Imap_Client_Data_Format_Filter_String');
            $res = stream_filter_append($this->_data->stream, 'horde_imap_client_string', STREAM_FILTER_WRITE, $this->_filter);
        } else {
            $res = null;
        }

        if (empty($opts['eol'])) {
            $res2 = null;
        } else {
            stream_filter_register('horde_eol', 'Horde_Stream_Filter_Eol');
            $res2 = stream_filter_append($this->_data->stream, 'horde_eol', STREAM_FILTER_WRITE);
        }

        $this->_data->add($data);

        if (!is_null($res)) {
            stream_filter_remove($res);
        }
        if (!is_null($res2)) {
            stream_filter_remove($res2);
        }
    }

    
    protected function _filterParams()
    {
        return new stdClass;
    }

    
    public function __toString()
    {
        return $this->_data->getString(0);
    }

    
    public function escape()
    {
        if ($this->literal()) {
            throw new Horde_Imap_Client_Data_Format_Exception('String requires literal to output.');
        }

        return $this->quoted()
            ? stream_get_contents($this->escapeStream())
            : $this->_data->getString(0);
    }

    
    public function escapeStream()
    {
        if ($this->literal()) {
            throw new Horde_Imap_Client_Data_Format_Exception('String requires literal to output.');
        }

        rewind($this->_data->stream);

        $stream = new Horde_Stream_Temp();
        $stream->add($this->_data, true);

        stream_filter_register('horde_imap_client_string_quote', 'Horde_Imap_Client_Data_Format_Filter_Quote');
        stream_filter_append($stream->stream, 'horde_imap_client_string_quote', STREAM_FILTER_READ);

        return $stream->stream;
    }

    
    public function quoted()
    {
        
        return (!isset($this->_filter) || !$this->_filter->literal);
    }

    
    public function forceQuoted()
    {
        $this->_filter = $this->_filterParams();
        $this->_filter->binary = false;
        $this->_filter->literal = false;
        $this->_filter->quoted = true;
    }

    
    public function literal()
    {
        return (isset($this->_filter) && $this->_filter->literal);
    }

    
    public function forceLiteral()
    {
        $this->_filter = $this->_filterParams();
                $this->_filter->literal = true;
        $this->_filter->quoted = false;
    }

    
    public function binary()
    {
        return (isset($this->_filter) && !empty($this->_filter->binary));
    }

    
    public function forceBinary()
    {
        $this->_filter = $this->_filterParams();
        $this->_filter->binary = true;
        $this->_filter->literal = true;
        $this->_filter->quoted = false;
    }

    
    public function length()
    {
        return $this->_data->length();
    }

    
    public function getStream()
    {
        return $this->_data;
    }

}
