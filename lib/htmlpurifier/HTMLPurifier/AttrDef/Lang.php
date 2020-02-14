<?php


class HTMLPurifier_AttrDef_Lang extends HTMLPurifier_AttrDef
{

    
    public function validate($string, $config, $context)
    {
        $string = trim($string);
        if (!$string) {
            return false;
        }

        $subtags = explode('-', $string);
        $num_subtags = count($subtags);

        if ($num_subtags == 0) {             return false;
        }

                $length = strlen($subtags[0]);
        switch ($length) {
            case 0:
                return false;
            case 1:
                if (!($subtags[0] == 'x' || $subtags[0] == 'i')) {
                    return false;
                }
                break;
            case 2:
            case 3:
                if (!ctype_alpha($subtags[0])) {
                    return false;
                } elseif (!ctype_lower($subtags[0])) {
                    $subtags[0] = strtolower($subtags[0]);
                }
                break;
            default:
                return false;
        }

        $new_string = $subtags[0];
        if ($num_subtags == 1) {
            return $new_string;
        }

                $length = strlen($subtags[1]);
        if ($length == 0 || ($length == 1 && $subtags[1] != 'x') || $length > 8 || !ctype_alnum($subtags[1])) {
            return $new_string;
        }
        if (!ctype_lower($subtags[1])) {
            $subtags[1] = strtolower($subtags[1]);
        }

        $new_string .= '-' . $subtags[1];
        if ($num_subtags == 2) {
            return $new_string;
        }

                for ($i = 2; $i < $num_subtags; $i++) {
            $length = strlen($subtags[$i]);
            if ($length == 0 || $length > 8 || !ctype_alnum($subtags[$i])) {
                return $new_string;
            }
            if (!ctype_lower($subtags[$i])) {
                $subtags[$i] = strtolower($subtags[$i]);
            }
            $new_string .= '-' . $subtags[$i];
        }
        return $new_string;
    }
}

