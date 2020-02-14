<?php





class CSSmin
{
    const NL = '___YUICSSMIN_PRESERVED_NL___';
    const TOKEN = '___YUICSSMIN_PRESERVED_TOKEN_';
    const COMMENT = '___YUICSSMIN_PRESERVE_CANDIDATE_COMMENT_';
    const CLASSCOLON = '___YUICSSMIN_PSEUDOCLASSCOLON___';
    const QUERY_FRACTION = '___YUICSSMIN_QUERY_FRACTION___';

    private $comments;
    private $preserved_tokens;
    private $memory_limit;
    private $max_execution_time;
    private $pcre_backtrack_limit;
    private $pcre_recursion_limit;
    private $raise_php_limits;

    
    public function __construct($raise_php_limits = TRUE)
    {
                $this->memory_limit = 128 * 1048576;         $this->max_execution_time = 60;         $this->pcre_backtrack_limit = 1000 * 1000;
        $this->pcre_recursion_limit =  500 * 1000;

        $this->raise_php_limits = (bool) $raise_php_limits;
    }

    
    public function run($css = '', $linebreak_pos = FALSE)
    {
        if (empty($css)) {
            return '';
        }

        if ($this->raise_php_limits) {
            $this->do_raise_php_limits();
        }

        $this->comments = array();
        $this->preserved_tokens = array();

        $start_index = 0;
        $length = strlen($css);

        $css = $this->extract_data_urls($css);

                while (($start_index = $this->index_of($css, '/*', $start_index)) >= 0) {
            $end_index = $this->index_of($css, '*/', $start_index + 2);
            if ($end_index < 0) {
                $end_index = $length;
            }
            $comment_found = $this->str_slice($css, $start_index + 2, $end_index);
            $this->comments[] = $comment_found;
            $comment_preserve_string = self::COMMENT . (count($this->comments) - 1) . '___';
            $css = $this->str_slice($css, 0, $start_index + 2) . $comment_preserve_string . $this->str_slice($css, $end_index);
                        $start_index = $end_index + 2 + strlen($comment_preserve_string) - strlen($comment_found);
        }

                $css = preg_replace_callback('/(?:"(?:[^\\\\"]|\\\\.|\\\\)*")|'."(?:'(?:[^\\\\']|\\\\.|\\\\)*')/S", array($this, 'replace_string'), $css);

                                                        $charset = '';
        $charset_regexp = '/(@charset)( [^;]+;)/i';
        $css_chunks = array();
        $css_chunk_length = 5000;         $start_index = 0;
        $i = $css_chunk_length;         $l = strlen($css);


                if ($l <= $css_chunk_length) {
            $css_chunks[] = $css;
        } else {
                        while ($i < $l) {
                $i += 50;                 if ($l - $start_index <= $css_chunk_length || $i >= $l) {
                    $css_chunks[] = $this->str_slice($css, $start_index);
                    break;
                }
                if ($css[$i - 1] === '}' && $i - $start_index > $css_chunk_length) {
                                                            $next_chunk = substr($css, $i);
                    if (preg_match('/^\s*\}/', $next_chunk)) {
                        $i = $i + $this->index_of($next_chunk, '}') + 1;
                    }

                    $css_chunks[] = $this->str_slice($css, $start_index, $i);
                    $start_index = $i;
                }
            }
        }

                for ($i = 0, $n = count($css_chunks); $i < $n; $i++) {
            $css_chunks[$i] = $this->minify($css_chunks[$i], $linebreak_pos);
                        if (empty($charset) && preg_match($charset_regexp, $css_chunks[$i], $matches)) {
                $charset = strtolower($matches[1]) . $matches[2];
            }
                        $css_chunks[$i] = preg_replace($charset_regexp, '', $css_chunks[$i]);
        }

                $css_chunks[0] = $charset . $css_chunks[0];

        return implode('', $css_chunks);
    }

    
    public function set_memory_limit($limit)
    {
        $this->memory_limit = $this->normalize_int($limit);
    }

    
    public function set_max_execution_time($seconds)
    {
        $this->max_execution_time = (int) $seconds;
    }

    
    public function set_pcre_backtrack_limit($limit)
    {
        $this->pcre_backtrack_limit = (int) $limit;
    }

    
    public function set_pcre_recursion_limit($limit)
    {
        $this->pcre_recursion_limit = (int) $limit;
    }

    
    private function do_raise_php_limits()
    {
        $php_limits = array(
            'memory_limit' => $this->memory_limit,
            'max_execution_time' => $this->max_execution_time,
            'pcre.backtrack_limit' => $this->pcre_backtrack_limit,
            'pcre.recursion_limit' =>  $this->pcre_recursion_limit
        );

                foreach ($php_limits as $name => $suggested) {
            $current = $this->normalize_int(ini_get($name));
                        if ($current > -1 && ($suggested == -1 || $current < $suggested)) {
                ini_set($name, $suggested);
            }
        }
    }

    
    private function minify($css, $linebreak_pos)
    {
                for ($i = 0, $max = count($this->comments); $i < $max; $i++) {

            $token = $this->comments[$i];
            $placeholder = '/' . self::COMMENT . $i . '___/';

                                    if (substr($token, 0, 1) === '!') {
                $this->preserved_tokens[] = $token;
                $token_tring = self::TOKEN . (count($this->preserved_tokens) - 1) . '___';
                $css = preg_replace($placeholder, $token_tring, $css, 1);
                                $css = preg_replace('/\s*[\n\r\f]+\s*(\/\*'. $token_tring .')/S', self::NL.'$1', $css);
                $css = preg_replace('/('. $token_tring .'\*\/)\s*[\n\r\f]+\s*/', '$1'.self::NL, $css);
                continue;
            }

                                    if (substr($token, (strlen($token) - 1), 1) === '\\') {
                $this->preserved_tokens[] = '\\';
                $css = preg_replace($placeholder,  self::TOKEN . (count($this->preserved_tokens) - 1) . '___', $css, 1);
                $i = $i + 1;                 $this->preserved_tokens[] = '';
                $css = preg_replace('/' . self::COMMENT . $i . '___/',  self::TOKEN . (count($this->preserved_tokens) - 1) . '___', $css, 1);
                continue;
            }

                                    if (strlen($token) === 0) {
                $start_index = $this->index_of($css, $this->str_slice($placeholder, 1, -1));
                if ($start_index > 2) {
                    if (substr($css, $start_index - 3, 1) === '>') {
                        $this->preserved_tokens[] = '';
                        $css = preg_replace($placeholder,  self::TOKEN . (count($this->preserved_tokens) - 1) . '___', $css, 1);
                    }
                }
            }

                        $css = preg_replace('/\/\*' . $this->str_slice($placeholder, 1, -1) . '\*\//', '', $css, 1);
        }


                $css = preg_replace('/\s+/', ' ', $css);

				$css = preg_replace_callback('/\s*filter\:\s*progid:DXImageTransform\.Microsoft\.Matrix\(([^\)]+)\)/', array($this, 'preserve_old_IE_specific_matrix_definition'), $css);

                $css = preg_replace_callback('/calc(\(((?:[^\(\)]+|(?1))*)\))/i', array($this, 'replace_calc'), $css);

                        $css = preg_replace('/((?<!\\\\)\:|\s)\+(\.?\d+)/S', '$1$2', $css);

                        $css = preg_replace('/((?<!\\\\)\:|\s)(\-?)0+(\.?\d+)/S', '$1$2$3', $css);

                        $css = preg_replace('/((?<!\\\\)\:|\s)(\-?)(\d?\.\d+?)0+([^\d])/S', '$1$2$3$4', $css);

                $css = preg_replace('/((?<!\\\\)\:|\s)(\-?\d+)\.0([^\d])/S', '$1$2$3', $css);

                $css = preg_replace('/((?<!\\\\)\:|\s)\-?\.?0+([^\d])/S', '${1}0$2', $css);

                                $css = preg_replace_callback('/(?:^|\})[^\{]*\s+\:/', array($this, 'replace_colon'), $css);

                $css = preg_replace('/\s+([\!\{\}\;\:\>\+\(\)\]\~\=,])/', '$1', $css);

                $css = preg_replace('/\!important/i', ' !important', $css);

                $css = preg_replace('/' . self::CLASSCOLON . '/', ':', $css);

                $css = preg_replace_callback('/\:first\-(line|letter)(\{|,)/i', array($this, 'lowercase_pseudo_first'), $css);

                $css = preg_replace('/\*\/ /', '*/', $css);

                $css = preg_replace_callback('/@(font-face|import|(?:-(?:atsc|khtml|moz|ms|o|wap|webkit)-)?keyframe|media|page|namespace)/i', array($this, 'lowercase_directives'), $css);

                $css = preg_replace_callback('/:(active|after|before|checked|disabled|empty|enabled|first-(?:child|of-type)|focus|hover|last-(?:child|of-type)|link|only-(?:child|of-type)|root|:selection|target|visited)/i', array($this, 'lowercase_pseudo_elements'), $css);

                $css = preg_replace_callback('/:(lang|not|nth-child|nth-last-child|nth-last-of-type|nth-of-type|(?:-(?:moz|webkit)-)?any)\(/i', array($this, 'lowercase_common_functions'), $css);

                        $css = preg_replace_callback('/([:,\( ]\s*)(attr|color-stop|from|rgba|to|url|(?:-(?:atsc|khtml|moz|ms|o|wap|webkit)-)?(?:calc|max|min|(?:repeating-)?(?:linear|radial)-gradient)|-webkit-gradient)/iS', array($this, 'lowercase_common_functions_values'), $css);

                        $css = preg_replace('/\band\(/i', 'and (', $css);

                $css = preg_replace('/([\!\{\}\:;\>\+\(\[\~\=,])\s+/S', '$1', $css);

                $css = preg_replace('/;+\}/', '}', $css);

                                $css = preg_replace('/(\*[a-z0-9\-]+\s*\:[^;\}]+)(\})/', '$1;$2', $css);

                                $css = preg_replace('/([^\\\\]\:|\s)0(?:em|ex|ch|rem|vw|vh|vm|vmin|cm|mm|in|px|pt|pc|%)/iS', '${1}0', $css);

				$css = preg_replace_callback('/(@[a-z\-]*?keyframes[^\{]+\{)(.*?)(\}\})/iS', array($this, 'replace_keyframe_zero'), $css);

                $css = preg_replace('/\:0(?: 0){1,3}(;|\}| \!)/', ':0$1', $css);

                        $css = preg_replace('/(text-shadow\:0)(;|\}| \!)/i', '$1 0 0$2', $css);

                                $css = preg_replace('/(background\-position|webkit-mask-position|(?:webkit|moz|o|ms|)\-?transform\-origin)\:0(;|\}| \!)/iS', '$1:0 0$2', $css);

                                $css = preg_replace_callback('/rgb\s*\(\s*([0-9,\s\-\.\%]+)\s*\)(.{1})/i', array($this, 'rgb_to_hex'), $css);
        $css = preg_replace_callback('/hsl\s*\(\s*([0-9,\s\-\.\%]+)\s*\)(.{1})/i', array($this, 'hsl_to_hex'), $css);

                $css = $this->compress_hex_colors($css);

                $css = preg_replace('/(border\-?(?:top|right|bottom|left|)|outline)\:none(;|\}| \!)/iS', '$1:0$2', $css);

                $css = preg_replace('/progid\:DXImageTransform\.Microsoft\.Alpha\(Opacity\=/i', 'alpha(opacity=', $css);

                        $css = preg_replace('/\(([a-z\-]+):([0-9]+)\/([0-9]+)\)/i', '($1:$2'. self::QUERY_FRACTION .'$3)', $css);

                $css = preg_replace('/[^\};\{\/]+\{\}/S', '', $css);

                $css = preg_replace('/'. self::QUERY_FRACTION .'/', '/', $css);

		                $css = preg_replace('/;;+/', ';', $css);

                $css = preg_replace('/'. self::NL .'/', "\n", $css);

                $css = preg_replace_callback('/(\{|\;)([A-Z\-]+)(\:)/', array($this, 'lowercase_properties'), $css);

                                if ($linebreak_pos !== FALSE && (int) $linebreak_pos >= 0) {
            $linebreak_pos = (int) $linebreak_pos;
            $start_index = $i = 0;
            while ($i < strlen($css)) {
                $i++;
                if ($css[$i - 1] === '}' && $i - $start_index > $linebreak_pos) {
                    $css = $this->str_slice($css, 0, $i) . "\n" . $this->str_slice($css, $i);
                    $start_index = $i;
                }
            }
        }

                for ($i = count($this->preserved_tokens) - 1; $i >= 0; $i--) {
            $css = preg_replace('/' . self::TOKEN . $i . '___/', $this->preserved_tokens[$i], $css, 1);
        }

                return trim($css);
    }

    
    private function extract_data_urls($css)
    {
                $max_index = strlen($css) - 1;
        $append_index = $index = $last_index = $offset = 0;
        $sb = array();
        $pattern = '/url\(\s*(["\']?)data\:/i';

                                
        while (preg_match($pattern, $css, $m, 0, $offset)) {
            $index = $this->index_of($css, $m[0], $offset);
            $last_index = $index + strlen($m[0]);
            $start_index = $index + 4;             $end_index = $last_index - 1;
            $terminator = $m[1];             $found_terminator = FALSE;

            if (strlen($terminator) === 0) {
                $terminator = ')';
            }

            while ($found_terminator === FALSE && $end_index+1 <= $max_index) {
                $end_index = $this->index_of($css, $terminator, $end_index + 1);

                                if ($end_index > 0 && substr($css, $end_index - 1, 1) !== '\\') {
                    $found_terminator = TRUE;
                    if (')' != $terminator) {
                        $end_index = $this->index_of($css, ')', $end_index);
                    }
                }
            }

                        $sb[] = $this->str_slice($css, $append_index, $index);

            if ($found_terminator) {
                $token = $this->str_slice($css, $start_index, $end_index);
                $token = preg_replace('/\s+/', '', $token);
                $this->preserved_tokens[] = $token;

                $preserver = 'url(' . self::TOKEN . (count($this->preserved_tokens) - 1) . '___)';
                $sb[] = $preserver;

                $append_index = $end_index + 1;
            } else {
                                $sb[] = $this->str_slice($css, $index, $last_index);
                $append_index = $last_index;
            }

            $offset = $last_index;
        }

        $sb[] = $this->str_slice($css, $append_index);

        return implode('', $sb);
    }

    
    private function compress_hex_colors($css)
    {
                $pattern = '/(\=\s*?["\']?)?#([0-9a-f])([0-9a-f])([0-9a-f])([0-9a-f])([0-9a-f])([0-9a-f])(\}|[^0-9a-f{][^{]*?\})/iS';
        $_index = $index = $last_index = $offset = 0;
        $sb = array();
                $short_safe = array(
            '#808080' => 'gray',
            '#008000' => 'green',
            '#800000' => 'maroon',
            '#000080' => 'navy',
            '#808000' => 'olive',
            '#ffa500' => 'orange',
            '#800080' => 'purple',
            '#c0c0c0' => 'silver',
            '#008080' => 'teal',
            '#f00' => 'red'
        );

        while (preg_match($pattern, $css, $m, 0, $offset)) {
            $index = $this->index_of($css, $m[0], $offset);
            $last_index = $index + strlen($m[0]);
            $is_filter = $m[1] !== null && $m[1] !== '';

            $sb[] = $this->str_slice($css, $_index, $index);

            if ($is_filter) {
                                $sb[] = $m[1] . '#' . $m[2] . $m[3] . $m[4] . $m[5] . $m[6] . $m[7];
            } else {
                if (strtolower($m[2]) == strtolower($m[3]) &&
                    strtolower($m[4]) == strtolower($m[5]) &&
                    strtolower($m[6]) == strtolower($m[7])) {
                                        $hex = '#' . strtolower($m[3] . $m[5] . $m[7]);
                } else {
                                        $hex = '#' . strtolower($m[2] . $m[3] . $m[4] . $m[5] . $m[6] . $m[7]);
                }
                                $sb[] = array_key_exists($hex, $short_safe) ? $short_safe[$hex] : $hex;
            }

            $_index = $offset = $last_index - strlen($m[8]);
        }

        $sb[] = $this->str_slice($css, $_index);

        return implode('', $sb);
    }

    

    private function replace_string($matches)
    {
        $match = $matches[0];
        $quote = substr($match, 0, 1);
                $match = addcslashes($this->str_slice($match, 1, -1), '\\');

                        if (($pos = $this->index_of($match, self::COMMENT)) >= 0) {
            for ($i = 0, $max = count($this->comments); $i < $max; $i++) {
                $match = preg_replace('/' . self::COMMENT . $i . '___/', $this->comments[$i], $match, 1);
            }
        }

                $match = preg_replace('/progid\:DXImageTransform\.Microsoft\.Alpha\(Opacity\=/i', 'alpha(opacity=', $match);

        $this->preserved_tokens[] = $match;
        return $quote . self::TOKEN . (count($this->preserved_tokens) - 1) . '___' . $quote;
    }

    private function replace_colon($matches)
    {
        return preg_replace('/\:/', self::CLASSCOLON, $matches[0]);
    }

    private function replace_calc($matches)
    {
        $this->preserved_tokens[] = trim(preg_replace('/\s*([\*\/\(\),])\s*/', '$1', $matches[2]));
        return 'calc('. self::TOKEN . (count($this->preserved_tokens) - 1) . '___' . ')';
    }

	private function preserve_old_IE_specific_matrix_definition($matches)
	{
		$this->preserved_tokens[] = $matches[1];
		return 'filter:progid:DXImageTransform.Microsoft.Matrix(' . self::TOKEN . (count($this->preserved_tokens) - 1) . '___' . ')';
    }

	private function replace_keyframe_zero($matches)
    {
        return $matches[1] . preg_replace('/0(\{|,[^\)\{]+\{)/', '0%$1', $matches[2]) . $matches[3];
    }

    private function rgb_to_hex($matches)
    {
                if ($this->index_of($matches[1], '%') >= 0){
            $rgbcolors = explode(',', str_replace('%', '', $matches[1]));
            for ($i = 0; $i < count($rgbcolors); $i++) {
                $rgbcolors[$i] = $this->round_number(floatval($rgbcolors[$i]) * 2.55);
            }
        } else {
            $rgbcolors = explode(',', $matches[1]);
        }

                for ($i = 0; $i < count($rgbcolors); $i++) {
            $rgbcolors[$i] = $this->clamp_number(intval($rgbcolors[$i], 10), 0, 255);
            $rgbcolors[$i] = sprintf("%02x", $rgbcolors[$i]);
        }

                if (!preg_match('/[\s\,\);\}]/', $matches[2])){
            $matches[2] = ' ' . $matches[2];
        }

        return '#' . implode('', $rgbcolors) . $matches[2];
    }

    private function hsl_to_hex($matches)
    {
        $values = explode(',', str_replace('%', '', $matches[1]));
        $h = floatval($values[0]);
        $s = floatval($values[1]);
        $l = floatval($values[2]);

                $h = ((($h % 360) + 360) % 360) / 360;
        $s = $this->clamp_number($s, 0, 100) / 100;
        $l = $this->clamp_number($l, 0, 100) / 100;

        if ($s == 0) {
            $r = $g = $b = $this->round_number(255 * $l);
        } else {
            $v2 = $l < 0.5 ? $l * (1 + $s) : ($l + $s) - ($s * $l);
            $v1 = (2 * $l) - $v2;
            $r = $this->round_number(255 * $this->hue_to_rgb($v1, $v2, $h + (1/3)));
            $g = $this->round_number(255 * $this->hue_to_rgb($v1, $v2, $h));
            $b = $this->round_number(255 * $this->hue_to_rgb($v1, $v2, $h - (1/3)));
        }

        return $this->rgb_to_hex(array('', $r.','.$g.','.$b, $matches[2]));
    }

    private function lowercase_pseudo_first($matches)
    {
        return ':first-'. strtolower($matches[1]) .' '. $matches[2];
    }

    private function lowercase_directives($matches)
    {
        return '@'. strtolower($matches[1]);
    }

    private function lowercase_pseudo_elements($matches)
    {
        return ':'. strtolower($matches[1]);
    }

    private function lowercase_common_functions($matches)
    {
        return ':'. strtolower($matches[1]) .'(';
    }

    private function lowercase_common_functions_values($matches)
    {
        return $matches[1] . strtolower($matches[2]);
    }

    private function lowercase_properties($matches)
    {
        return $matches[1].strtolower($matches[2]).$matches[3];
    }

    

    private function hue_to_rgb($v1, $v2, $vh)
    {
        $vh = $vh < 0 ? $vh + 1 : ($vh > 1 ? $vh - 1 : $vh);
        if ($vh * 6 < 1) return $v1 + ($v2 - $v1) * 6 * $vh;
        if ($vh * 2 < 1) return $v2;
        if ($vh * 3 < 2) return $v1 + ($v2 - $v1) * ((2/3) - $vh) * 6;
        return $v1;
    }

    private function round_number($n)
    {
        return intval(floor(floatval($n) + 0.5), 10);
    }

    private function clamp_number($n, $min, $max)
    {
        return min(max($n, $min), $max);
    }

    
    private function index_of($haystack, $needle, $offset = 0)
    {
        $index = strpos($haystack, $needle, $offset);

        return ($index !== FALSE) ? $index : -1;
    }

    
    private function str_slice($str, $start = 0, $end = FALSE)
    {
        if ($end !== FALSE && ($start < 0 || $end <= 0)) {
            $max = strlen($str);

            if ($start < 0) {
                if (($start = $max + $start) < 0) {
                    return '';
                }
            }

            if ($end < 0) {
                if (($end = $max + $end) < 0) {
                    return '';
                }
            }

            if ($end <= $start) {
                return '';
            }
        }

        $slice = ($end === FALSE) ? substr($str, $start) : substr($str, $start, $end - $start);
        return ($slice === FALSE) ? '' : $slice;
    }

    
    private function normalize_int($size)
    {
        if (is_string($size)) {
            switch (substr($size, -1)) {
                case 'M': case 'm': return $size * 1048576;
                case 'K': case 'k': return $size * 1024;
                case 'G': case 'g': return $size * 1073741824;
            }
        }

        return (int) $size;
    }
}