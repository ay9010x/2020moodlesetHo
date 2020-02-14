<?php


class HTMLPurifier_AttrDef_CSS_Font extends HTMLPurifier_AttrDef
{

    
    protected $info = array();

    
    public function __construct($config)
    {
        $def = $config->getCSSDefinition();
        $this->info['font-style'] = $def->info['font-style'];
        $this->info['font-variant'] = $def->info['font-variant'];
        $this->info['font-weight'] = $def->info['font-weight'];
        $this->info['font-size'] = $def->info['font-size'];
        $this->info['line-height'] = $def->info['line-height'];
        $this->info['font-family'] = $def->info['font-family'];
    }

    
    public function validate($string, $config, $context)
    {
        static $system_fonts = array(
            'caption' => true,
            'icon' => true,
            'menu' => true,
            'message-box' => true,
            'small-caption' => true,
            'status-bar' => true
        );

                $string = $this->parseCDATA($string);
        if ($string === '') {
            return false;
        }

                $lowercase_string = strtolower($string);
        if (isset($system_fonts[$lowercase_string])) {
            return $lowercase_string;
        }

        $bits = explode(' ', $string);         $stage = 0;         $caught = array();         $stage_1 = array('font-style', 'font-variant', 'font-weight');
        $final = ''; 
        for ($i = 0, $size = count($bits); $i < $size; $i++) {
            if ($bits[$i] === '') {
                continue;
            }
            switch ($stage) {
                case 0:                     foreach ($stage_1 as $validator_name) {
                        if (isset($caught[$validator_name])) {
                            continue;
                        }
                        $r = $this->info[$validator_name]->validate(
                            $bits[$i],
                            $config,
                            $context
                        );
                        if ($r !== false) {
                            $final .= $r . ' ';
                            $caught[$validator_name] = true;
                            break;
                        }
                    }
                                        if (count($caught) >= 3) {
                        $stage = 1;
                    }
                    if ($r !== false) {
                        break;
                    }
                case 1:                     $found_slash = false;
                    if (strpos($bits[$i], '/') !== false) {
                        list($font_size, $line_height) =
                            explode('/', $bits[$i]);
                        if ($line_height === '') {
                                                        $line_height = false;
                            $found_slash = true;
                        }
                    } else {
                        $font_size = $bits[$i];
                        $line_height = false;
                    }
                    $r = $this->info['font-size']->validate(
                        $font_size,
                        $config,
                        $context
                    );
                    if ($r !== false) {
                        $final .= $r;
                                                if ($line_height === false) {
                                                        for ($j = $i + 1; $j < $size; $j++) {
                                if ($bits[$j] === '') {
                                    continue;
                                }
                                if ($bits[$j] === '/') {
                                    if ($found_slash) {
                                        return false;
                                    } else {
                                        $found_slash = true;
                                        continue;
                                    }
                                }
                                $line_height = $bits[$j];
                                break;
                            }
                        } else {
                                                        $found_slash = true;
                            $j = $i;
                        }
                        if ($found_slash) {
                            $i = $j;
                            $r = $this->info['line-height']->validate(
                                $line_height,
                                $config,
                                $context
                            );
                            if ($r !== false) {
                                $final .= '/' . $r;
                            }
                        }
                        $final .= ' ';
                        $stage = 2;
                        break;
                    }
                    return false;
                case 2:                     $font_family =
                        implode(' ', array_slice($bits, $i, $size - $i));
                    $r = $this->info['font-family']->validate(
                        $font_family,
                        $config,
                        $context
                    );
                    if ($r !== false) {
                        $final .= $r . ' ';
                                                return rtrim($final);
                    }
                    return false;
            }
        }
        return false;
    }
}

