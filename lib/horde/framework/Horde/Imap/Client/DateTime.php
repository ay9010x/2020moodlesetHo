<?php



class Horde_Imap_Client_DateTime extends DateTime
{
    
    public function __construct($time = null)
    {
        $tz = new DateTimeZone('UTC');

        try {
            parent::__construct($time, $tz);
            return;
        } catch (Exception $e) {}

        
        if (substr(rtrim($time), -3) === ' UT') {
            try {
                parent::__construct($time . 'C', $tz);
                return;
            } catch (Exception $e) {}
        }

        
        $date = preg_replace("/\s*\([^\)]+\)\s*$/", '', $time, -1, $i);
        if ($i) {
            try {
                parent::__construct($date, $tz);
                return;
            } catch (Exception $e) {}
        }

        parent::__construct('@-1', $tz);
    }

    
    public function __toString()
    {
        return $this->error()
            ? '0'
            : $this->format('U');
    }

    
    public function error()
    {
        return (intval($this->format('U')) === -1);
    }

}
