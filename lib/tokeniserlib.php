<?php





define ('MINIMUM_WORD_SIZE',  3); define ('MAXIMUM_WORD_SIZE', 50); 
define ('START_DELIM',  "\xce\xa9\xc3\x91\xc3\x91"); define ('CENTER_DELIM', "\xc3\x91\xce\xa9\xc3\x91"); define ('END_DELIM',    "\xc3\x91\xc3\x91\xce\xa9"); 

define('PREG_CLASS_SEARCH_EXCLUDE',
'\x{0}-\x{2f}\x{3a}-\x{40}\x{5b}-\x{60}\x{7b}-\x{bf}\x{d7}\x{f7}\x{2b0}-'.
'\x{385}\x{387}\x{3f6}\x{482}-\x{489}\x{559}-\x{55f}\x{589}-\x{5c7}\x{5f3}-'.
'\x{61f}\x{640}\x{64b}-\x{65e}\x{66a}-\x{66d}\x{670}\x{6d4}\x{6d6}-\x{6ed}'.
'\x{6fd}\x{6fe}\x{700}-\x{70f}\x{711}\x{730}-\x{74a}\x{7a6}-\x{7b0}\x{901}-'.
'\x{903}\x{93c}\x{93e}-\x{94d}\x{951}-\x{954}\x{962}-\x{965}\x{970}\x{981}-'.
'\x{983}\x{9bc}\x{9be}-\x{9cd}\x{9d7}\x{9e2}\x{9e3}\x{9f2}-\x{a03}\x{a3c}-'.
'\x{a4d}\x{a70}\x{a71}\x{a81}-\x{a83}\x{abc}\x{abe}-\x{acd}\x{ae2}\x{ae3}'.
'\x{af1}-\x{b03}\x{b3c}\x{b3e}-\x{b57}\x{b70}\x{b82}\x{bbe}-\x{bd7}\x{bf0}-'.
'\x{c03}\x{c3e}-\x{c56}\x{c82}\x{c83}\x{cbc}\x{cbe}-\x{cd6}\x{d02}\x{d03}'.
'\x{d3e}-\x{d57}\x{d82}\x{d83}\x{dca}-\x{df4}\x{e31}\x{e34}-\x{e3f}\x{e46}-'.
'\x{e4f}\x{e5a}\x{e5b}\x{eb1}\x{eb4}-\x{ebc}\x{ec6}-\x{ecd}\x{f01}-\x{f1f}'.
'\x{f2a}-\x{f3f}\x{f71}-\x{f87}\x{f90}-\x{fd1}\x{102c}-\x{1039}\x{104a}-'.
'\x{104f}\x{1056}-\x{1059}\x{10fb}\x{10fc}\x{135f}-\x{137c}\x{1390}-\x{1399}'.
'\x{166d}\x{166e}\x{1680}\x{169b}\x{169c}\x{16eb}-\x{16f0}\x{1712}-\x{1714}'.
'\x{1732}-\x{1736}\x{1752}\x{1753}\x{1772}\x{1773}\x{17b4}-\x{17db}\x{17dd}'.
'\x{17f0}-\x{180e}\x{1843}\x{18a9}\x{1920}-\x{1945}\x{19b0}-\x{19c0}\x{19c8}'.
'\x{19c9}\x{19de}-\x{19ff}\x{1a17}-\x{1a1f}\x{1d2c}-\x{1d61}\x{1d78}\x{1d9b}-'.
'\x{1dc3}\x{1fbd}\x{1fbf}-\x{1fc1}\x{1fcd}-\x{1fcf}\x{1fdd}-\x{1fdf}\x{1fed}-'.
'\x{1fef}\x{1ffd}-\x{2070}\x{2074}-\x{207e}\x{2080}-\x{2101}\x{2103}-\x{2106}'.
'\x{2108}\x{2109}\x{2114}\x{2116}-\x{2118}\x{211e}-\x{2123}\x{2125}\x{2127}'.
'\x{2129}\x{212e}\x{2132}\x{213a}\x{213b}\x{2140}-\x{2144}\x{214a}-\x{2b13}'.
'\x{2ce5}-\x{2cff}\x{2d6f}\x{2e00}-\x{3005}\x{3007}-\x{303b}\x{303d}-\x{303f}'.
'\x{3099}-\x{309e}\x{30a0}\x{30fb}-\x{30fe}\x{3190}-\x{319f}\x{31c0}-\x{31cf}'.
'\x{3200}-\x{33ff}\x{4dc0}-\x{4dff}\x{a015}\x{a490}-\x{a716}\x{a802}\x{a806}'.
'\x{a80b}\x{a823}-\x{a82b}\x{d800}-\x{f8ff}\x{fb1e}\x{fb29}\x{fd3e}\x{fd3f}'.
'\x{fdfc}-\x{fe6b}\x{feff}-\x{ff0f}\x{ff1a}-\x{ff20}\x{ff3b}-\x{ff40}\x{ff5b}-'.
'\x{ff65}\x{ff70}\x{ff9e}\x{ff9f}\x{ffe0}-\x{fffd}');


define('PREG_CLASS_NUMBERS',
'\x{30}-\x{39}\x{b2}\x{b3}\x{b9}\x{bc}-\x{be}\x{660}-\x{669}\x{6f0}-\x{6f9}'.
'\x{966}-\x{96f}\x{9e6}-\x{9ef}\x{9f4}-\x{9f9}\x{a66}-\x{a6f}\x{ae6}-\x{aef}'.
'\x{b66}-\x{b6f}\x{be7}-\x{bf2}\x{c66}-\x{c6f}\x{ce6}-\x{cef}\x{d66}-\x{d6f}'.
'\x{e50}-\x{e59}\x{ed0}-\x{ed9}\x{f20}-\x{f33}\x{1040}-\x{1049}\x{1369}-'.
'\x{137c}\x{16ee}-\x{16f0}\x{17e0}-\x{17e9}\x{17f0}-\x{17f9}\x{1810}-\x{1819}'.
'\x{1946}-\x{194f}\x{2070}\x{2074}-\x{2079}\x{2080}-\x{2089}\x{2153}-\x{2183}'.
'\x{2460}-\x{249b}\x{24ea}-\x{24ff}\x{2776}-\x{2793}\x{3007}\x{3021}-\x{3029}'.
'\x{3038}-\x{303a}\x{3192}-\x{3195}\x{3220}-\x{3229}\x{3251}-\x{325f}\x{3280}-'.
'\x{3289}\x{32b1}-\x{32bf}\x{ff10}-\x{ff19}');


define('PREG_CLASS_PUNCTUATION',
'\x{21}-\x{23}\x{25}-\x{2a}\x{2c}-\x{2f}\x{3a}\x{3b}\x{3f}\x{40}\x{5b}-\x{5d}'.
'\x{5f}\x{7b}\x{7d}\x{a1}\x{ab}\x{b7}\x{bb}\x{bf}\x{37e}\x{387}\x{55a}-\x{55f}'.
'\x{589}\x{58a}\x{5be}\x{5c0}\x{5c3}\x{5f3}\x{5f4}\x{60c}\x{60d}\x{61b}\x{61f}'.
'\x{66a}-\x{66d}\x{6d4}\x{700}-\x{70d}\x{964}\x{965}\x{970}\x{df4}\x{e4f}'.
'\x{e5a}\x{e5b}\x{f04}-\x{f12}\x{f3a}-\x{f3d}\x{f85}\x{104a}-\x{104f}\x{10fb}'.
'\x{1361}-\x{1368}\x{166d}\x{166e}\x{169b}\x{169c}\x{16eb}-\x{16ed}\x{1735}'.
'\x{1736}\x{17d4}-\x{17d6}\x{17d8}-\x{17da}\x{1800}-\x{180a}\x{1944}\x{1945}'.
'\x{2010}-\x{2027}\x{2030}-\x{2043}\x{2045}-\x{2051}\x{2053}\x{2054}\x{2057}'.
'\x{207d}\x{207e}\x{208d}\x{208e}\x{2329}\x{232a}\x{23b4}-\x{23b6}\x{2768}-'.
'\x{2775}\x{27e6}-\x{27eb}\x{2983}-\x{2998}\x{29d8}-\x{29db}\x{29fc}\x{29fd}'.
'\x{3001}-\x{3003}\x{3008}-\x{3011}\x{3014}-\x{301f}\x{3030}\x{303d}\x{30a0}'.
'\x{30fb}\x{fd3e}\x{fd3f}\x{fe30}-\x{fe52}\x{fe54}-\x{fe61}\x{fe63}\x{fe68}'.
'\x{fe6a}\x{fe6b}\x{ff01}-\x{ff03}\x{ff05}-\x{ff0a}\x{ff0c}-\x{ff0f}\x{ff1a}'.
'\x{ff1b}\x{ff1f}\x{ff20}\x{ff3b}-\x{ff3d}\x{ff3f}\x{ff5b}\x{ff5d}\x{ff5f}-'.
'\x{ff65}');


define('PREG_CLASS_CJK', '\x{3041}-\x{30ff}\x{31f0}-\x{31ff}\x{3400}-\x{4db5}'.
'\x{4e00}-\x{9fbb}\x{f900}-\x{fad9}');



function tokenise_text($text, $stop_words = array(), $overlap_cjk = false, $join_numbers = false) {

            $tags = array('h1' => 25,
                  'h2' => 18,
                  'h3' => 15,
                  'h4' => 12,
                  'h5' => 9,
                  'h6' => 6,
                  'u' => 3,
                  'b' => 3,
                  'i' => 3,
                  'strong' => 3,
                  'em' => 3,
                  'a' => 10);

            $text = str_replace(array('<', '>'), array(' <', '> '), $text);
    $text = strip_tags($text, '<'. implode('><', array_keys($tags)) .'>');

        $split = preg_split('/\s*<([^>]+?)>\s*/', $text, -1, PREG_SPLIT_DELIM_CAPTURE);
        
    $tag = FALSE;     $score = 1;     $accum = ' ';     $tagstack = array();     $tagwords = 0;     $focus = 1; 
    $results = array(0 => array()); 
    foreach ($split as $value) {
        if ($tag) {
                        list($tagname) = explode(' ', $value, 2);
            $tagname = core_text::strtolower($tagname);
                        if ($tagname[0] == '/') {
                $tagname = substr($tagname, 1);
                                if (!count($tagstack) || $tagstack[0] != $tagname) {
                    $tagstack = array();
                    $score = 1;
                }
                else {
                                        $score = max(1, $score - $tags[array_shift($tagstack)]);
                }
            }
            else {
                if (isset($tagstack[0]) && $tagstack[0] == $tagname) {
                                                            $tagstack = array();
                    $score = 1;
                }
                else {
                                        array_unshift($tagstack, $tagname);
                    $score += $tags[$tagname];
                }
            }
                        $tagwords = 0;
        }
        else {
                        if ($value != '') {
                $words = tokenise_split($value, $stop_words, $overlap_cjk, $join_numbers);
                foreach ($words as $word) {
                                        $accum .= $word .' ';
                    $num = is_numeric($word);
                                        if ($num || core_text::strlen($word) >= MINIMUM_WORD_SIZE) {
                                                if ($num && $join_numbers) {
                            $word = (int)ltrim($word, '-0');
                        }

                        if (!isset($results[0][$word])) {
                            $results[0][$word] = 0;
                        }

                        $results[0][$word] += $score * $focus;
                                                                        $focus = min(1, .01 + 3.5 / (2 + count($results[0]) * .015));
                    }
                    $tagwords++;
                                        if (count($tagstack) && $tagwords >= 15) {
                        $tagstack = array();
                        $score = 1;
                    }
                }
            }
        }
        $tag = !$tag;
    }

    $res = array();

    if (isset($results[0]) && count($results[0]) > 0) {
        $res = $results[0];
        arsort($res, SORT_NUMERIC);
    }

    return $res;
}



function tokenise_split($text, $stop_words, $overlap_cjk, $join_numbers) {
    static $last = NULL;
    static $lastsplit = NULL;

    if ($last == $text) {
        return $lastsplit;
    }
        $text = tokenise_simplify($text, $overlap_cjk, $join_numbers);
    $words = explode(' ', $text);
        array_walk($words, 'tokenise_truncate_word');

        if (is_array($stop_words) && !empty($stop_words)) {
                $simp_stop_words = explode(' ', tokenise_simplify(implode(' ', $stop_words), $overlap_cjk, $join_numbers));
                $words = array_diff($words, $simp_stop_words);
    }
        $last = $text;
    $lastsplit = $words;

    return $words;
}


function tokenise_simplify($text, $overlap_cjk, $join_numbers) {

        $text = core_text::entities_to_utf8($text, true);

        $text = core_text::strtolower($text);

        if ($overlap_cjk) {
        $text = preg_replace_callback('/['. PREG_CLASS_CJK .']+/u', 'tokenise_expand_cjk', $text);
    }

                            if ($join_numbers) {
        $text = preg_replace('/(['. PREG_CLASS_NUMBERS .']+)['. PREG_CLASS_PUNCTUATION .']+(?=['. PREG_CLASS_NUMBERS .'])/u', '\1', $text);
    } else {
            preg_match_all('/['. PREG_CLASS_NUMBERS .']+['. PREG_CLASS_PUNCTUATION . PREG_CLASS_NUMBERS .']+/u', $text, $foundseqs);
        $storedseqs = array();
        foreach (array_unique($foundseqs[0]) as $ntkey => $value) {
            $prefix = (string)(count($storedseqs) + 1);
            $storedseqs[START_DELIM.$prefix.CENTER_DELIM.$ntkey.END_DELIM] = $value;
        }
        if (!empty($storedseqs)) {
            $text = str_replace($storedseqs, array_keys($storedseqs), $text);
        }
    }

            $text = preg_replace('/[._-]+/', '', $text);

            $text = preg_replace('/['. PREG_CLASS_SEARCH_EXCLUDE .']+/u', ' ', $text);

        if (!$join_numbers) {
        if (!empty($storedseqs)) {
            $text = str_replace(array_keys($storedseqs), $storedseqs, $text);
        }
    }

    return $text;
}


function tokenise_expand_cjk($matches) {

    $str = $matches[0];
    $l = core_text::strlen($str);
        if ($l <= MINIMUM_WORD_SIZE) {
        return ' '. $str .' ';
    }
    $tokens = ' ';
        $chars = array();
        for ($i = 0; $i < $l; ++$i) {
                $current = core_text::substr($str, 0, 1);
        $str = substr($str, strlen($current));
        $chars[] = $current;
        if ($i >= MINIMUM_WORD_SIZE - 1) {
            $tokens .= implode('', $chars) .' ';
            array_shift($chars);
        }
    }
    return $tokens;
}


function tokenise_truncate_word(&$text) {

    $text = core_text::substr($text, 0, MAXIMUM_WORD_SIZE);
}
