<?php



class Horde_Support_Numerizer_Locale_Base
{
    public $DIRECT_NUMS = array(
        'eleven' => '11',
        'twelve' => '12',
        'thirteen' => '13',
        'fourteen' => '14',
        'fifteen' => '15',
        'sixteen' => '16',
        'seventeen' => '17',
        'eighteen' => '18',
        'nineteen' => '19',
        'ninteen' => '19',              'zero' => '0',
        'one' => '1',
        'two' => '2',
        'three' => '3',
        'four(\W|$)' => '4$1',          'five' => '5',
        'six(\W|$)' => '6$1',
        'seven(\W|$)' => '7$1',
        'eight(\W|$)' => '8$1',
        'nine(\W|$)' => '9$1',
        'ten' => '10',
        '\ba[\b^$]' => '1',         );

    public $TEN_PREFIXES = array(
        'twenty' => 20,
        'thirty' => 30,
        'forty' => 40,
        'fourty' => 40,         'fifty' => 50,
        'sixty' => 60,
        'seventy' => 70,
        'eighty' => 80,
        'ninety' => 90,
        'ninty' => 90,     );

    public $BIG_PREFIXES = array(
        'hundred' => 100,
        'thousand' => 1000,
        'million' => 1000000,
        'billion' => 1000000000,
        'trillion' => 1000000000000,
    );

    public function numerize($string)
    {
                $string = $this->_splitHyphenatedWords($string);
        $string = $this->_hideAHalf($string);

        $string = $this->_directReplacements($string);
        $string = $this->_replaceTenPrefixes($string);
        $string = $this->_replaceBigPrefixes($string);
        $string = $this->_fractionalAddition($string);

        return $string;
    }

    
    protected function _splitHyphenatedWords($string)
    {
        return preg_replace('/ +|([^\d])-([^d])/', '$1 $2', $string);
    }

    
    protected function _hideAHalf($string)
    {
        return str_replace('a half', 'haAlf', $string);
    }

    
    protected function _directReplacements($string)
    {
        foreach ($this->DIRECT_NUMS as $dn => $dn_replacement) {
            $string = preg_replace("/$dn/i", $dn_replacement, $string);
        }
        return $string;
    }

    
    protected function _replaceTenPrefixes($string)
    {
        foreach ($this->TEN_PREFIXES as $tp => $tp_replacement) {
            $string = preg_replace_callback(
                "/(?:$tp)( *\d(?=[^\d]|\$))*/i",
                create_function(
                    '$m',
                    'return ' . $tp_replacement . ' + (isset($m[1]) ? (int)$m[1] : 0);'
                ),
                $string);
        }
        return $string;
    }

    
    protected function _replaceBigPrefixes($string)
    {
        foreach ($this->BIG_PREFIXES as $bp => $bp_replacement) {
            $string = preg_replace_callback(
                '/(\d*) *' . $bp . '/i',
                create_function(
                    '$m',
                    'return ' . $bp_replacement . ' * (int)$m[1];'
                ),
                $string);
            $string = $this->_andition($string);
        }
        return $string;
    }

    protected function _andition($string)
    {
        while (true) {
            if (preg_match('/(\d+)( | and )(\d+)(?=[^\w]|$)/i', $string, $sc, PREG_OFFSET_CAPTURE)) {
                if (preg_match('/and/', $sc[2][0]) || (strlen($sc[1][0]) > strlen($sc[3][0]))) {
                    $string = substr($string, 0, $sc[1][1]) . ((int)$sc[1][0] + (int)$sc[3][0]) . substr($string, $sc[3][1] + strlen($sc[3][0]));
                    continue;
                }
            }
            break;
        }
        return $string;
    }

    protected function _fractionalAddition($string)
    {
        return preg_replace_callback(
            '/(\d+)(?: | and |-)*haAlf/i',
            create_function(
                '$m',
                'return (string)((float)$m[1] + 0.5);'
            ),
            $string);
    }

}
