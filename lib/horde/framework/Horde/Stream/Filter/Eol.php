<?php



class Horde_Stream_Filter_Eol extends php_user_filter
{
    
    protected $_replace;

    
    protected $_search;

    
    protected $_split = null;

    
    public function onCreate()
    {
        $eol = isset($this->params['eol'])
            ? $this->params['eol']
            : "\r\n";

        if (!strlen($eol)) {
            $this->_search = array("\r", "\n");
            $this->_replace = '';
        } elseif (in_array($eol, array("\r", "\n"))) {
            $this->_search = array("\r\n", ($eol == "\r") ? "\n" : "\r");
            $this->_replace = $eol;
        } else {
            $this->_search = array("\r\n", "\r", "\n");
            $this->_replace = array("\n", "\n", $eol);
            if (strlen($eol) > 1) {
                $this->_split = $eol[0];
            }
        }

        return true;
    }

    
    public function filter($in, $out, &$consumed, $closing)
    {
        while ($bucket = stream_bucket_make_writeable($in)) {
            if (!is_null($this->_split) &&
                ($bucket->data[$bucket->datalen - 1] == $this->_split)) {
                $bucket->data = substr($bucket->data, 0, -1);
            }

            $bucket->data = str_replace($this->_search, $this->_replace, $bucket->data);
            $consumed += $bucket->datalen;
            stream_bucket_append($out, $bucket);
        }

        return PSFS_PASS_ON;
    }

}
