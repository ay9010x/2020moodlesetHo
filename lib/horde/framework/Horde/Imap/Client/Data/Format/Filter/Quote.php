<?php



class Horde_Imap_Client_Data_Format_Filter_Quote extends php_user_filter
{
    
    public function filter($in, $out, &$consumed, $closing)
    {
        stream_bucket_append($out, stream_bucket_new($this->stream, '"'));

        while ($bucket = stream_bucket_make_writeable($in)) {
            $consumed += $bucket->datalen;
            $bucket->data = addcslashes($bucket->data, '"\\');
            stream_bucket_append($out, $bucket);
        }

        stream_bucket_append($out, stream_bucket_new($this->stream, '"'));

        return PSFS_PASS_ON;
    }

}
