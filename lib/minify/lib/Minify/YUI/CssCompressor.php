<?php



class Minify_YUI_CssCompressor {

    
    public function compress($css, $linebreakpos = 0)
    {
        $css = str_replace("\r\n", "\n", $css);

        

                $css = preg_replace('@\s+@', ' ', $css);

                $css = preg_replace("@\"\\\\\"}\\\\\"\"@", "___PSEUDOCLASSBMH___", $css);

                                $css = preg_replace_callback("@(^|\\})(([^\\{:])+:)+([^\\{]*\\{)@", array($this, '_removeSpacesCB'), $css);

        $css = preg_replace("@\\s+([!{};:>+\\(\\)\\],])@", "$1", $css);
        $css = str_replace("___PSEUDOCLASSCOLON___", ":", $css);

                $css = preg_replace("@([!{}:;>+\\(\\[,])\\s+@", "$1", $css);

                $css = preg_replace("@([^;\\}])}@", "$1;}", $css);

                $css = preg_replace("@([\\s:])(0)(px|em|%|in|cm|mm|pc|pt|ex)@", "$1$2", $css);

                $css = str_replace(":0 0 0 0;", ":0;", $css);
        $css = str_replace(":0 0 0;", ":0;", $css);
        $css = str_replace(":0 0;", ":0;", $css);

                $css = str_replace("background-position:0;", "background-position:0 0;", $css);

                $css = preg_replace("@(:|\\s)0+\\.(\\d+)@", "$1.$2", $css);

                        $css = preg_replace_callback("@rgb\\s*\\(\\s*([0-9,\\s]+)\\s*\\)@", array($this, '_shortenRgbCB'), $css);

                                                        $css = preg_replace_callback("@([^\"'=\\s])(\\s*)#([0-9a-fA-F])([0-9a-fA-F])([0-9a-fA-F])([0-9a-fA-F])([0-9a-fA-F])([0-9a-fA-F])@", array($this, '_shortenHexCB'), $css);

                $css = preg_replace("@[^\\}]+\\{;\\}@", "", $css);

        $linebreakpos = isset($this->_options['linebreakpos'])
            ? $this->_options['linebreakpos']
            : 0;

        if ($linebreakpos > 0) {
                                                $i = 0;
            $linestartpos = 0;
            $sb = $css;

                        $mbIntEnc = null;
            if (function_exists('mb_strlen') && ((int)ini_get('mbstring.func_overload') & 2)) {
                $mbIntEnc = mb_internal_encoding();
                mb_internal_encoding('8bit');
            }
            $sbLength = strlen($css);
            while ($i < $sbLength) {
                $c = $sb[$i++];
                if ($c === '}' && $i - $linestartpos > $linebreakpos) {
                    $sb = substr_replace($sb, "\n", $i, 0);
                    $sbLength++;
                    $linestartpos = $i;
                }
            }
            $css = $sb;

                        if ($mbIntEnc !== null) {
                mb_internal_encoding($mbIntEnc);
            }
        }

                $css = str_replace("___PSEUDOCLASSBMH___", "\"\\\\\"}\\\\\"\"", $css);

                        $css = preg_replace("@;;+@", ";", $css);

                $css = preg_replace('/:first-l(etter|ine)\\{/', ':first-l$1 {', $css);

                $css = trim($css);

        return $css;
    }

    protected function _removeSpacesCB($m)
    {
        return str_replace(':', '___PSEUDOCLASSCOLON___', $m[0]);
    }

    protected function _shortenRgbCB($m)
    {
        $rgbcolors = explode(',', $m[1]);
        $hexcolor = '#';
        for ($i = 0; $i < count($rgbcolors); $i++) {
            $val = round($rgbcolors[$i]);
            if ($val < 16) {
                $hexcolor .= '0';
            }
            $hexcolor .= dechex($val);
        }
        return $hexcolor;
    }

    protected function _shortenHexCB($m)
    {
                if ((strtolower($m[3])===strtolower($m[4])) &&
                (strtolower($m[5])===strtolower($m[6])) &&
                (strtolower($m[7])===strtolower($m[8]))) {
            return $m[1] . $m[2] . "#" . $m[3] . $m[5] . $m[7];
        } else {
            return $m[0];
        }
    }
}