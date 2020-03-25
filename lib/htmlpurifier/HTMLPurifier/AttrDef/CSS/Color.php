<?php


class HTMLPurifier_AttrDef_CSS_Color extends HTMLPurifier_AttrDef
{

    
    public function validate($color, $config, $context)
    {
        static $colors = null;
        if ($colors === null) {
            $colors = $config->get('Core.ColorKeywords');
        }

        $color = trim($color);
        if ($color === '') {
            return false;
        }

        $lower = strtolower($color);
        if (isset($colors[$lower])) {
            return $colors[$lower];
        }

        if (strpos($color, 'rgb(') !== false) {
                        $length = strlen($color);
            if (strpos($color, ')') !== $length - 1) {
                return false;
            }
            $triad = substr($color, 4, $length - 4 - 1);
            $parts = explode(',', $triad);
            if (count($parts) !== 3) {
                return false;
            }
            $type = false;             $new_parts = array();
            foreach ($parts as $part) {
                $part = trim($part);
                if ($part === '') {
                    return false;
                }
                $length = strlen($part);
                if ($part[$length - 1] === '%') {
                                        if (!$type) {
                        $type = 'percentage';
                    } elseif ($type !== 'percentage') {
                        return false;
                    }
                    $num = (float)substr($part, 0, $length - 1);
                    if ($num < 0) {
                        $num = 0;
                    }
                    if ($num > 100) {
                        $num = 100;
                    }
                    $new_parts[] = "$num%";
                } else {
                                        if (!$type) {
                        $type = 'integer';
                    } elseif ($type !== 'integer') {
                        return false;
                    }
                    $num = (int)$part;
                    if ($num < 0) {
                        $num = 0;
                    }
                    if ($num > 255) {
                        $num = 255;
                    }
                    $new_parts[] = (string)$num;
                }
            }
            $new_triad = implode(',', $new_parts);
            $color = "rgb($new_triad)";
        } else {
                        if ($color[0] === '#') {
                $hex = substr($color, 1);
            } else {
                $hex = $color;
                $color = '#' . $color;
            }
            $length = strlen($hex);
            if ($length !== 3 && $length !== 6) {
                return false;
            }
            if (!ctype_xdigit($hex)) {
                return false;
            }
        }
        return $color;
    }
}

