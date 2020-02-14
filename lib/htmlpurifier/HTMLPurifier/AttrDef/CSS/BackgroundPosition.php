<?php







class HTMLPurifier_AttrDef_CSS_BackgroundPosition extends HTMLPurifier_AttrDef
{

    
    protected $length;

    
    protected $percentage;

    public function __construct()
    {
        $this->length = new HTMLPurifier_AttrDef_CSS_Length();
        $this->percentage = new HTMLPurifier_AttrDef_CSS_Percentage();
    }

    
    public function validate($string, $config, $context)
    {
        $string = $this->parseCDATA($string);
        $bits = explode(' ', $string);

        $keywords = array();
        $keywords['h'] = false;         $keywords['v'] = false;         $keywords['ch'] = false;         $keywords['cv'] = false;         $measures = array();

        $i = 0;

        $lookup = array(
            'top' => 'v',
            'bottom' => 'v',
            'left' => 'h',
            'right' => 'h',
            'center' => 'c'
        );

        foreach ($bits as $bit) {
            if ($bit === '') {
                continue;
            }

                        $lbit = ctype_lower($bit) ? $bit : strtolower($bit);
            if (isset($lookup[$lbit])) {
                $status = $lookup[$lbit];
                if ($status == 'c') {
                    if ($i == 0) {
                        $status = 'ch';
                    } else {
                        $status = 'cv';
                    }
                }
                $keywords[$status] = $lbit;
                $i++;
            }

                        $r = $this->length->validate($bit, $config, $context);
            if ($r !== false) {
                $measures[] = $r;
                $i++;
            }

                        $r = $this->percentage->validate($bit, $config, $context);
            if ($r !== false) {
                $measures[] = $r;
                $i++;
            }
        }

        if (!$i) {
            return false;
        } 
        $ret = array();

                if ($keywords['h']) {
            $ret[] = $keywords['h'];
        } elseif ($keywords['ch']) {
            $ret[] = $keywords['ch'];
            $keywords['cv'] = false;         } elseif (count($measures)) {
            $ret[] = array_shift($measures);
        }

        if ($keywords['v']) {
            $ret[] = $keywords['v'];
        } elseif ($keywords['cv']) {
            $ret[] = $keywords['cv'];
        } elseif (count($measures)) {
            $ret[] = array_shift($measures);
        }

        if (empty($ret)) {
            return false;
        }
        return implode(' ', $ret);
    }
}

