<?php


class HTMLPurifier_AttrDef_CSS_FontFamily extends HTMLPurifier_AttrDef
{

    protected $mask = null;

    public function __construct()
    {
        $this->mask = '_- ';
        for ($c = 'a'; $c <= 'z'; $c++) {
            $this->mask .= $c;
        }
        for ($c = 'A'; $c <= 'Z'; $c++) {
            $this->mask .= $c;
        }
        for ($c = '0'; $c <= '9'; $c++) {
            $this->mask .= $c;
        }                 for ($i = 0x80; $i <= 0xFF; $i++) {
                                                $this->mask .= chr($i);
        }

        
            }

    
    public function validate($string, $config, $context)
    {
        static $generic_names = array(
            'serif' => true,
            'sans-serif' => true,
            'monospace' => true,
            'fantasy' => true,
            'cursive' => true
        );
        $allowed_fonts = $config->get('CSS.AllowedFonts');

                $fonts = explode(',', $string);
        $final = '';
        foreach ($fonts as $font) {
            $font = trim($font);
            if ($font === '') {
                continue;
            }
                        if (isset($generic_names[$font])) {
                if ($allowed_fonts === null || isset($allowed_fonts[$font])) {
                    $final .= $font . ', ';
                }
                continue;
            }
                        if ($font[0] === '"' || $font[0] === "'") {
                $length = strlen($font);
                if ($length <= 2) {
                    continue;
                }
                $quote = $font[0];
                if ($font[$length - 1] !== $quote) {
                    continue;
                }
                $font = substr($font, 1, $length - 2);
            }

            $font = $this->expandCSSEscape($font);

            
            if ($allowed_fonts !== null && !isset($allowed_fonts[$font])) {
                continue;
            }

            if (ctype_alnum($font) && $font !== '') {
                                $final .= $font . ', ';
                continue;
            }

                                    $font = str_replace(array("\n", "\t", "\r", "\x0C"), ' ', $font);

                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                    
                                                if (strspn($font, $this->mask) !== strlen($font)) {
                continue;
            }

                                                                                                                                    
                        $final .= "'$font', ";
        }
        $final = rtrim($final, ', ');
        if ($final === '') {
            return false;
        }
        return $final;
    }

}

