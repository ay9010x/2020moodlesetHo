<?php



class Horde_Imap_Client_Data_Format_Filter_String extends php_user_filter
{
    
    public function onCreate()
    {
        $this->params->binary = false;
        $this->params->literal = false;
                $this->params->quoted = false;

        return true;
    }

    
    public function filter($in, $out, &$consumed, $closing)
    {
        $p = $this->params;
        $skip = false;

        while ($bucket = stream_bucket_make_writeable($in)) {
            if (!$skip) {
                $len = $bucket->datalen;
                $str = $bucket->data;

                for ($i = 0; $i < $len; ++$i) {
                    $chr = ord($str[$i]);

                    switch ($chr) {
                    case 0:                         $p->binary = true;
                        $p->literal = true;

                                                $skip = true;
                        break 2;

                    case 10:                     case 13:                         $p->literal = true;
                        break;

                    case 32:                     case 34:                     case 40:                     case 41:                     case 92:                     case 123:                     case 127:                                                 $p->quoted = true;
                        break;

                    case 37:                     case 42:                                                 if (empty($p->no_quote_list)) {
                            $p->quoted = true;
                        }
                        break;

                    default:
                        if ($chr < 32) {
                                                        $p->quoted = true;
                        } elseif ($chr > 127) {
                                                        $p->literal = true;
                        }
                        break;
                    }
                }
            }

            $consumed += $bucket->datalen;
            stream_bucket_append($out, $bucket);
        }

        if ($p->literal) {
            $p->quoted = false;
        }

        return PSFS_PASS_ON;
    }

}
