<?php



class Bennu {
    static function timestamp_to_datetime($t = NULL) {
        if($t === NULL) {
            $t = time();
        }
        return gmstrftime('%Y%m%dT%H%M%SZ', $t);
    }

    static function timestamp_to_date($t = NULL) {
        if ($t === NULL) {
            $t = time();
        }
        return gmstrftime('%Y%m%d', $t);
    }

    static function generate_guid() {
        
                $time_hi_and_version       = sprintf('%02x', (1 << 6) + mt_rand(0, 15));         $clock_seq_hi_and_reserved = sprintf('%02x', (1 << 7) + mt_rand(0, 63)); 
                $pool = '';
        for($i = 0; $i < 7; ++$i) {
            $pool .= sprintf('%04x', mt_rand(0, 65535));
        }

                $random  = substr($pool, 0, 8).'-';

                $random .= substr($pool, 8, 4).'-';

                $random .= $time_hi_and_version.substr($pool, 12, 2).'-';

                $random .= $clock_seq_hi_and_reserved;

                $random .= substr($pool, 13, 2).'-';

                $random .= substr($pool, 14, 12);

        return $random;
    }
}

