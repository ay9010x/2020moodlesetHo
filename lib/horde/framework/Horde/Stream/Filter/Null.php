<?php

class Horde_Stream_Filter_Null extends php_user_filter
{
    
    protected $_search = "\0";

    
    protected $_replace;

    
    public function onCreate()
    {
        $this->_replace = isset($this->params->replace)
            ? $this->params->replace
            : '';

        return true;
    }

    
    public function filter($in, $out, &$consumed, $closing)
    {
        while ($bucket = stream_bucket_make_writeable($in)) {
            $bucket->data = str_replace($this->_search, $this->_replace, $bucket->data);
            $consumed += $bucket->datalen;
            stream_bucket_append($out, $bucket);
        }

        return PSFS_PASS_ON;
    }

}
