<?php



defined('MOODLE_INTERNAL') || die();

if (!defined('THEME_DESIGNER_CACHE_LIFETIME')) {
            define('THEME_DESIGNER_CACHE_LIFETIME', 10);
}


function css_store_css(theme_config $theme, $csspath, $csscontent, $chunk = false, $chunkurl = null) {
    global $CFG;

    clearstatcache();
    if (!file_exists(dirname($csspath))) {
        @mkdir(dirname($csspath), $CFG->directorypermissions, true);
    }

            ignore_user_abort(true);

        css_write_file($csspath, $csscontent);

    if ($chunk) {
                $css = css_chunk_by_selector_count($csscontent, $chunkurl);
        $files = count($css);
        $count = 1;
        foreach ($css as $content) {
            if ($count === $files) {
                                $filename = preg_replace('#\.css$#', '.0.css', $csspath);
            } else {
                                $filename = preg_replace('#\.css$#', '.'.$count.'.css', $csspath);
            }
            $count++;
            css_write_file($filename, $content);
        }
    }

    ignore_user_abort(false);
    if (connection_aborted()) {
        die;
    }
}


function css_write_file($filename, $content) {
    global $CFG;
    if ($fp = fopen($filename.'.tmp', 'xb')) {
        fwrite($fp, $content);
        fclose($fp);
        rename($filename.'.tmp', $filename);
        @chmod($filename, $CFG->filepermissions);
        @unlink($filename.'.tmp');     }
}


function css_chunk_by_selector_count($css, $importurl, $maxselectors = 4095, $buffer = 50) {

        $count = substr_count($css, ',') + substr_count($css, '{');
    if ($count < $maxselectors) {
                return array($css);
    }

    $chunks = array();                      $offsets = array();                     $offset = 0;                            $selectorcount = 0;                     $lastvalidoffset = 0;                   $lastvalidoffsetselectorcount = 0;      $inrule = 0;                            $inmedia = false;                       $mediacoming = false;                   $currentoffseterror = null;             $offseterrors = array();            
        $css = preg_replace('#/\*(.*?)\*/#s', '', $css);
    $strlen = strlen($css);

        for ($i = 1; $i <= $strlen; $i++) {
        $char = $css[$i - 1];
        $offset = $i;

                if ($char === '@') {
            if (!$inmedia && substr($css, $offset, 5) === 'media') {
                $mediacoming = true;
            }
        }

                if ($char === '{') {
            if ($mediacoming) {
                $inmedia = true;
                $mediacoming = false;
            } else {
                $inrule++;
                $selectorcount++;
            }
        }

                        if (!$mediacoming && !$inrule && $char === ',') {
            $selectorcount++;
        }

                if ($char === '}') {
                        if ($inmedia) {
                if (!$inrule) {
                                        $inmedia = false;
                } else {
                                        $inrule--;
                }
            } else {
                $inrule--;
                                                                if ($inrule < 0) {
                    $inrule = 0;
                }
            }

                        if (!$inmedia && !$inrule) {
                $lastvalidoffset = $offset;
                $lastvalidoffsetselectorcount = $selectorcount;
            }
        }

                if ($selectorcount > $maxselectors) {
            if (!$lastvalidoffset) {
                                                                                if ($currentoffseterror === null) {
                    $currentoffseterror = $offset;
                    $offseterrors[] = $currentoffseterror;
                }
            } else {
                                $offsets[] = $lastvalidoffset;
                $selectorcount = $selectorcount - $lastvalidoffsetselectorcount;
                $lastvalidoffset = 0;
                $currentoffseterror = null;
            }
        }
    }

        if (!empty($offseterrors)) {
        debugging('Could not find a safe place to split at offset(s): ' . implode(', ', $offseterrors) . '. Those were ignored.',
            DEBUG_DEVELOPER);
    }

        $offsetcount = count($offsets);
    foreach ($offsets as $key => $index) {
        $start = 0;
        if ($key > 0) {
            $start = $offsets[$key - 1];
        }
                $chunks[] = substr($css, $start, $index - $start);
    }
        if (end($offsets) != $strlen) {
        $chunks[] = substr($css, end($offsets));
    }

                                        $importcss = '';
    $slashargs = strpos($importurl, '.php?') === false;
    $parts = count($chunks);
    for ($i = 1; $i < $parts; $i++) {
        if ($slashargs) {
            $importcss .= "@import url({$importurl}/chunk{$i});\n";
        } else {
            $importcss .= "@import url({$importurl}&chunk={$i});\n";
        }
    }
    $importcss .= end($chunks);
    $chunks[key($chunks)] = $importcss;

    return $chunks;
}


function css_send_cached_css($csspath, $etag) {
        $lifetime = 60*60*24*60;

    header('Etag: "'.$etag.'"');
    header('Content-Disposition: inline; filename="styles.php"');
    header('Last-Modified: '. gmdate('D, d M Y H:i:s', filemtime($csspath)) .' GMT');
    header('Expires: '. gmdate('D, d M Y H:i:s', time() + $lifetime) .' GMT');
    header('Pragma: ');
    header('Cache-Control: public, max-age='.$lifetime);
    header('Accept-Ranges: none');
    header('Content-Type: text/css; charset=utf-8');
    if (!min_enable_zlib_compression()) {
        header('Content-Length: '.filesize($csspath));
    }

    readfile($csspath);
    die;
}


function css_send_cached_css_content($csscontent, $etag) {
        $lifetime = 60*60*24*60;

    header('Etag: "'.$etag.'"');
    header('Content-Disposition: inline; filename="styles.php"');
    header('Last-Modified: '. gmdate('D, d M Y H:i:s', time()) .' GMT');
    header('Expires: '. gmdate('D, d M Y H:i:s', time() + $lifetime) .' GMT');
    header('Pragma: ');
    header('Cache-Control: public, max-age='.$lifetime);
    header('Accept-Ranges: none');
    header('Content-Type: text/css; charset=utf-8');
    if (!min_enable_zlib_compression()) {
        header('Content-Length: '.strlen($csscontent));
    }

    echo($csscontent);
    die;
}


function css_send_uncached_css($css) {
    header('Content-Disposition: inline; filename="styles_debug.php"');
    header('Last-Modified: '. gmdate('D, d M Y H:i:s', time()) .' GMT');
    header('Expires: '. gmdate('D, d M Y H:i:s', time() + THEME_DESIGNER_CACHE_LIFETIME) .' GMT');
    header('Pragma: ');
    header('Accept-Ranges: none');
    header('Content-Type: text/css; charset=utf-8');

    if (is_array($css)) {
        $css = implode("\n\n", $css);
    }
    echo $css;
    die;
}


function css_send_unmodified($lastmodified, $etag) {
        $lifetime = 60*60*24*60;
    header('HTTP/1.1 304 Not Modified');
    header('Expires: '. gmdate('D, d M Y H:i:s', time() + $lifetime) .' GMT');
    header('Cache-Control: public, max-age='.$lifetime);
    header('Content-Type: text/css; charset=utf-8');
    header('Etag: "'.$etag.'"');
    if ($lastmodified) {
        header('Last-Modified: '. gmdate('D, d M Y H:i:s', $lastmodified) .' GMT');
    }
    die;
}


function css_send_css_not_found() {
    header('HTTP/1.0 404 not found');
    die('CSS was not found, sorry.');
}


function css_is_colour($value) {
    $value = trim($value);

    $hex  = '/^#([a-fA-F0-9]{1,3}|[a-fA-F0-9]{6})$/';
    $rgb  = '#^rgb\s*\(\s*(\d{1,3})\s*,\s*(\d{1,3})\s*,\s*(\d{1,3})\s*\)$#i';
    $rgba = '#^rgba\s*\(\s*(\d{1,3})\s*,\s*(\d{1,3})\s*,\s*(\d{1,3})\s*,\s*(\d{1}(\.\d+)?)\s*\)$#i';
    $hsl  = '#^hsl\s*\(\s*(\d{1,3})\s*,\s*(\d{1,3})\%\s*,\s*(\d{1,3})\%\s*\)$#i';
    $hsla = '#^hsla\s*\(\s*(\d{1,3})\s*,\s*(\d{1,3})\%\s*,\s*(\d{1,3})\%\s*,\s*(\d{1}(\.\d+)?)\s*\)$#i';

    if (in_array(strtolower($value), array('inherit'))) {
        return true;
    } else if (preg_match($hex, $value)) {
        return true;
    } else if (in_array(strtolower($value), array_keys(css_optimiser::$htmlcolours))) {
        return true;
    } else if (preg_match($rgb, $value, $m) && $m[1] < 256 && $m[2] < 256 && $m[3] < 256) {
                return true;
    } else if (preg_match($rgba, $value, $m) && $m[1] < 256 && $m[2] < 256 && $m[3] < 256) {
                return true;
    } else if (preg_match($hsl, $value, $m) && $m[1] <= 360 && $m[2] <= 100 && $m[3] <= 100) {
                return true;
    } else if (preg_match($hsla, $value, $m) && $m[1] <= 360 && $m[2] <= 100 && $m[3] <= 100) {
                return true;
    }
        return false;
}


function css_is_width($value) {
    $value = trim($value);
    if (in_array(strtolower($value), array('auto', 'inherit'))) {
        return true;
    }
    if ((string)$value === '0' || preg_match('#^(\-\s*)?(\d*\.)?(\d+)\s*(em|px|pt|\%|in|cm|mm|ex|pc)$#i', $value)) {
        return true;
    }
    return false;
}


function css_sort_by_count(array $a, array $b) {
    $a = count($a);
    $b = count($b);
    if ($a == $b) {
        return 0;
    }
    return ($a > $b) ? -1 : 1;
}


class css_optimiser {

    
    const PROCESSING_START = 0;

    
    const PROCESSING_SELECTORS = 0;

    
    const PROCESSING_STYLES = 1;

    
    const PROCESSING_COMMENT = 2;

    
    const PROCESSING_ATRULE = 3;

    
    protected $rawstrlen = 0;

    
    protected $commentsincss = 0;

    
    protected $rawrules = 0;

    
    protected $rawselectors = 0;

    
    protected $optimisedstrlen = 0;

    
    protected $optimisedrules = 0;

    
    protected $optimisedselectors = 0;

    
    protected $timestart = 0;

    
    protected $timecomplete = 0;

    
    protected $errors = array();

    
    public function process($css) {
                $css = trim($css);

        $this->reset_stats();
        $this->timestart = microtime(true);
        $this->rawstrlen = strlen($css);

                                if ($this->rawstrlen === 0) {
            $this->errors[] = 'Skipping file as it has no content.';
            return '';
        }

                                $css = preg_replace('#\r?\n#', ' ', $css);

                        $css = preg_replace('#/\*(.*?)\*/#m', '', $css, -1, $this->commentsincss);

        $medias = array(
            'all' => new css_media()
        );
        $imports = array();
        $charset = false;
                $keyframes = array();

        $currentprocess = self::PROCESSING_START;
        $currentrule = css_rule::init();
        $currentselector = css_selector::init();
        $inquotes = false;              $inbraces = false;              $inbrackets = false;            $inparenthesis = false;         
        $currentmedia = $medias['all'];
        $currentatrule = null;
        $suspectatrule = false;

        $buffer = '';
        $char = null;

                        for ($i = 0; $i < $this->rawstrlen; $i++) {
            $lastchar = $char;
            $char = substr($css, $i, 1);
            if ($char == '@' && $buffer == '') {
                $suspectatrule = true;
            }
            switch ($currentprocess) {
                                case self::PROCESSING_ATRULE:
                    switch ($char) {
                        case ';':
                            if (!$inbraces) {
                                $buffer .= $char;
                                if ($currentatrule == 'import') {
                                    $imports[] = $buffer;
                                    $currentprocess = self::PROCESSING_SELECTORS;
                                } else if ($currentatrule == 'charset') {
                                    $charset = $buffer;
                                    $currentprocess = self::PROCESSING_SELECTORS;
                                }
                            }
                            if ($currentatrule !== 'media') {
                                $buffer = '';
                                $currentatrule = false;
                            }
                                                                                                                continue 3;
                        case '{':
                            $regexmediabasic = '#\s*@media\s*([a-zA-Z0-9]+(\s*,\s*[a-zA-Z0-9]+)*)\s*{#';
                            $regexadvmedia = '#\s*@media\s*([^{]+)#';
                            $regexkeyframes = '#@((\-moz\-|\-webkit\-|\-ms\-|\-o\-)?keyframes)\s*([^\s]+)#';

                            if ($currentatrule == 'media' && preg_match($regexmediabasic, $buffer, $matches)) {
                                                                $mediatypes = str_replace(' ', '', $matches[1]);
                                if (!array_key_exists($mediatypes, $medias)) {
                                    $medias[$mediatypes] = new css_media($mediatypes);
                                }
                                $currentmedia = $medias[$mediatypes];
                                $currentprocess = self::PROCESSING_SELECTORS;
                                $buffer = '';
                            } else if ($currentatrule == 'media' && preg_match($regexadvmedia, $buffer, $matches)) {
                                                                $mediatypes = $matches[1];
                                $hash = md5($mediatypes);
                                $medias[$hash] = new css_media($mediatypes);
                                $currentmedia = $medias[$hash];
                                $currentprocess = self::PROCESSING_SELECTORS;
                                $buffer = '';
                            } else if ($currentatrule == 'keyframes' && preg_match($regexkeyframes, $buffer, $matches)) {
                                                                                                $keyframefor = $matches[1];
                                $keyframename = $matches[3];
                                $keyframe = new css_keyframe($keyframefor, $keyframename);
                                $keyframes[] = $keyframe;
                                $currentmedia = $keyframe;
                                $currentprocess = self::PROCESSING_SELECTORS;
                                $buffer = '';
                            }
                                                                                                                continue 3;
                    }
                    break;
                                case self::PROCESSING_START:
                case self::PROCESSING_SELECTORS:
                    $regexatrule = '#@(media|import|charset|(\-moz\-|\-webkit\-|\-ms\-|\-o\-)?(keyframes))\s*#';
                    switch ($char) {
                        case '[':
                            $inbrackets ++;
                            $buffer .= $char;
                                                                                                                continue 3;
                        case ']':
                            $inbrackets --;
                            $buffer .= $char;
                                                                                                                continue 3;
                        case ' ':
                            if ($inbrackets) {
                                                                                                                                continue 3;
                            }
                            if (!empty($buffer)) {
                                                                if ($suspectatrule && preg_match($regexatrule, $buffer, $matches)) {
                                    $currentatrule = (!empty($matches[3]))?$matches[3]:$matches[1];
                                    $currentprocess = self::PROCESSING_ATRULE;
                                    $buffer .= $char;
                                } else {
                                    $currentselector->add($buffer);
                                    $buffer = '';
                                }
                            }
                            $suspectatrule = false;
                                                                                                                continue 3;
                        case '{':
                            if ($inbrackets) {
                                                                                                                                continue 3;
                            }
                                                        if ($suspectatrule && preg_match($regexatrule, $buffer, $matches)) {
                                                                $currentatrule = (!empty($matches[3]))?$matches[3]:$matches[1];
                                $currentprocess = self::PROCESSING_ATRULE;
                                $i--;
                                $suspectatrule = false;
                                                                                                                                continue 3;
                            }
                            if ($buffer !== '') {
                                $currentselector->add($buffer);
                            }
                            $currentrule->add_selector($currentselector);
                            $currentselector = css_selector::init();
                            $currentprocess = self::PROCESSING_STYLES;

                            $buffer = '';
                                                                                                                continue 3;
                        case '}':
                            if ($inbrackets) {
                                                                                                                                continue 3;
                            }
                            if ($currentatrule == 'media') {
                                $currentmedia = $medias['all'];
                                $currentatrule = false;
                                $buffer = '';
                            } else if (strpos($currentatrule, 'keyframes') !== false) {
                                $currentmedia = $medias['all'];
                                $currentatrule = false;
                                $buffer = '';
                            }
                                                                                                                continue 3;
                        case ',':
                            if ($inbrackets) {
                                                                                                                                continue 3;
                            }
                            $currentselector->add($buffer);
                            $currentrule->add_selector($currentselector);
                            $currentselector = css_selector::init();
                            $buffer = '';
                                                                                                                continue 3;
                    }
                    break;
                                case self::PROCESSING_STYLES:
                    if ($char == '"' || $char == "'") {
                        if ($inquotes === false) {
                            $inquotes = $char;
                        }
                        if ($inquotes === $char && $lastchar !== '\\') {
                            $inquotes = false;
                        }
                    }
                    if ($inquotes) {
                        $buffer .= $char;
                        continue 2;
                    }
                    switch ($char) {
                        case ';':
                            if ($inparenthesis) {
                                $buffer .= $char;
                                                                                                                                continue 3;
                            }
                            $currentrule->add_style($buffer);
                            $buffer = '';
                            $inquotes = false;
                                                                                                                continue 3;
                        case '}':
                            $currentrule->add_style($buffer);
                            $this->rawselectors += $currentrule->get_selector_count();

                            $currentmedia->add_rule($currentrule);

                            $currentrule = css_rule::init();
                            $currentprocess = self::PROCESSING_SELECTORS;
                            $this->rawrules++;
                            $buffer = '';
                            $inquotes = false;
                            $inparenthesis = false;
                                                                                                                continue 3;
                        case '(':
                            $inparenthesis = true;
                            $buffer .= $char;
                                                                                                                continue 3;
                        case ')':
                            $inparenthesis = false;
                            $buffer .= $char;
                                                                                                                continue 3;
                    }
                    break;
            }
            $buffer .= $char;
        }

        foreach ($medias as $media) {
            $this->optimise($media);
        }
        $css = $this->produce_css($charset, $imports, $medias, $keyframes);

        $this->timecomplete = microtime(true);
        return trim($css);
    }

    
    protected function produce_css($charset, array $imports, array $medias, array $keyframes) {
        $css = '';
        if (!empty($charset)) {
            $imports[] = $charset;
        }
        if (!empty($imports)) {
            $css .= implode("\n", $imports);
            $css .= "\n\n";
        }

        $cssreset = array();
        $cssstandard = array();
        $csskeyframes = array();

                foreach ($medias as $media) {
                        if (in_array('all', $media->get_types())) {
                                                                $resetrules = $media->get_reset_rules(true);
                if (!empty($resetrules)) {
                    $cssreset[] = css_writer::media('all', $resetrules);
                }
            }
                        $cssstandard[] = $media->out();
        }

                if (count($keyframes) > 0) {
            foreach ($keyframes as $keyframe) {
                $this->optimisedrules += $keyframe->count_rules();
                $this->optimisedselectors +=  $keyframe->count_selectors();
                if ($keyframe->has_errors()) {
                    $this->errors += $keyframe->get_errors();
                }
                $csskeyframes[] = $keyframe->out();
            }
        }

                $css .= join('', $cssreset);
        $css .= join('', $cssstandard);
        $css .= join('', $csskeyframes);

                $this->optimisedstrlen = strlen($css);

                return $css;
    }

    
    protected function optimise(css_rule_collection $media) {
        $media->organise_rules_by_selectors();
        $this->optimisedrules += $media->count_rules();
        $this->optimisedselectors +=  $media->count_selectors();
        if ($media->has_errors()) {
            $this->errors += $media->get_errors();
        }
    }

    
    public function get_stats() {
        $stats = array(
            'timestart'             => $this->timestart,
            'timecomplete'          => $this->timecomplete,
            'timetaken'             => round($this->timecomplete - $this->timestart, 4),
            'commentsincss'         => $this->commentsincss,
            'rawstrlen'             => $this->rawstrlen,
            'rawselectors'          => $this->rawselectors,
            'rawrules'              => $this->rawrules,
            'optimisedstrlen'       => $this->optimisedstrlen,
            'optimisedrules'        => $this->optimisedrules,
            'optimisedselectors'    => $this->optimisedselectors,
            'improvementstrlen'     => '-',
            'improvementrules'     => '-',
            'improvementselectors'     => '-',
        );
                if ($this->rawstrlen > 0) {
            $stats['improvementstrlen'] = round(100 - ($this->optimisedstrlen / $this->rawstrlen) * 100, 1).'%';
        }
        if ($this->rawrules > 0) {
            $stats['improvementrules'] = round(100 - ($this->optimisedrules / $this->rawrules) * 100, 1).'%';
        }
        if ($this->rawselectors > 0) {
            $stats['improvementselectors'] = round(100 - ($this->optimisedselectors / $this->rawselectors) * 100, 1).'%';
        }
        return $stats;
    }

    
    public function has_errors() {
        return !empty($this->errors);
    }

    
    public function get_errors($clear = false) {
        $errors = $this->errors;
        if ($clear) {
                        $this->errors = array();
        }
        return $errors;
    }

    
    public function output_errors_css() {
        $computedcss  = "/****************************************\n";
        $computedcss .= " *--- Errors found during processing ----\n";
        foreach ($this->errors as $error) {
            $computedcss .= preg_replace('#^#m', '* ', $error);
        }
        $computedcss .= " ****************************************/\n\n";
        return $computedcss;
    }

    
    public function output_stats_css() {

        $computedcss  = "/****************************************\n";
        $computedcss .= " *------- CSS Optimisation stats --------\n";

        if ($this->rawstrlen === 0) {
            $computedcss .= " File not processed as it has no content /\n\n";
            $computedcss .= " ****************************************/\n\n";
            return $computedcss;
        } else if ($this->rawrules === 0) {
            $computedcss .= " File contained no rules to be processed /\n\n";
            $computedcss .= " ****************************************/\n\n";
            return $computedcss;
        }

        $stats = $this->get_stats();

        $computedcss .= " *  ".date('r')."\n";
        $computedcss .= " *  {$stats['commentsincss']}  \t comments removed\n";
        $computedcss .= " *  Optimisation took {$stats['timetaken']} seconds\n";
        $computedcss .= " *--------------- before ----------------\n";
        $computedcss .= " *  {$stats['rawstrlen']}  \t chars read in\n";
        $computedcss .= " *  {$stats['rawrules']}  \t rules read in\n";
        $computedcss .= " *  {$stats['rawselectors']}  \t total selectors\n";
        $computedcss .= " *---------------- after ----------------\n";
        $computedcss .= " *  {$stats['optimisedstrlen']}  \t chars once optimized\n";
        $computedcss .= " *  {$stats['optimisedrules']}  \t optimized rules\n";
        $computedcss .= " *  {$stats['optimisedselectors']}  \t total selectors once optimized\n";
        $computedcss .= " *---------------- stats ----------------\n";
        $computedcss .= " *  {$stats['improvementstrlen']}  \t reduction in chars\n";
        $computedcss .= " *  {$stats['improvementrules']}  \t reduction in rules\n";
        $computedcss .= " *  {$stats['improvementselectors']}  \t reduction in selectors\n";
        $computedcss .= " ****************************************/\n\n";

        return $computedcss;
    }

    
    public function reset_stats() {
        $this->commentsincss = 0;
        $this->optimisedrules = 0;
        $this->optimisedselectors = 0;
        $this->optimisedstrlen = 0;
        $this->rawrules = 0;
        $this->rawselectors = 0;
        $this->rawstrlen = 0;
        $this->timecomplete = 0;
        $this->timestart = 0;
    }

    
    public static $htmlcolours = array(
        'aliceblue' => '#F0F8FF',
        'antiquewhite' => '#FAEBD7',
        'aqua' => '#00FFFF',
        'aquamarine' => '#7FFFD4',
        'azure' => '#F0FFFF',
        'beige' => '#F5F5DC',
        'bisque' => '#FFE4C4',
        'black' => '#000000',
        'blanchedalmond' => '#FFEBCD',
        'blue' => '#0000FF',
        'blueviolet' => '#8A2BE2',
        'brown' => '#A52A2A',
        'burlywood' => '#DEB887',
        'cadetblue' => '#5F9EA0',
        'chartreuse' => '#7FFF00',
        'chocolate' => '#D2691E',
        'coral' => '#FF7F50',
        'cornflowerblue' => '#6495ED',
        'cornsilk' => '#FFF8DC',
        'crimson' => '#DC143C',
        'cyan' => '#00FFFF',
        'darkblue' => '#00008B',
        'darkcyan' => '#008B8B',
        'darkgoldenrod' => '#B8860B',
        'darkgray' => '#A9A9A9',
        'darkgrey' => '#A9A9A9',
        'darkgreen' => '#006400',
        'darkKhaki' => '#BDB76B',
        'darkmagenta' => '#8B008B',
        'darkolivegreen' => '#556B2F',
        'arkorange' => '#FF8C00',
        'darkorchid' => '#9932CC',
        'darkred' => '#8B0000',
        'darksalmon' => '#E9967A',
        'darkseagreen' => '#8FBC8F',
        'darkslateblue' => '#483D8B',
        'darkslategray' => '#2F4F4F',
        'darkslategrey' => '#2F4F4F',
        'darkturquoise' => '#00CED1',
        'darkviolet' => '#9400D3',
        'deeppink' => '#FF1493',
        'deepskyblue' => '#00BFFF',
        'dimgray' => '#696969',
        'dimgrey' => '#696969',
        'dodgerblue' => '#1E90FF',
        'firebrick' => '#B22222',
        'floralwhite' => '#FFFAF0',
        'forestgreen' => '#228B22',
        'fuchsia' => '#FF00FF',
        'gainsboro' => '#DCDCDC',
        'ghostwhite' => '#F8F8FF',
        'gold' => '#FFD700',
        'goldenrod' => '#DAA520',
        'gray' => '#808080',
        'grey' => '#808080',
        'green' => '#008000',
        'greenyellow' => '#ADFF2F',
        'honeydew' => '#F0FFF0',
        'hotpink' => '#FF69B4',
        'indianred ' => '#CD5C5C',
        'indigo ' => '#4B0082',
        'ivory' => '#FFFFF0',
        'khaki' => '#F0E68C',
        'lavender' => '#E6E6FA',
        'lavenderblush' => '#FFF0F5',
        'lawngreen' => '#7CFC00',
        'lemonchiffon' => '#FFFACD',
        'lightblue' => '#ADD8E6',
        'lightcoral' => '#F08080',
        'lightcyan' => '#E0FFFF',
        'lightgoldenrodyellow' => '#FAFAD2',
        'lightgray' => '#D3D3D3',
        'lightgrey' => '#D3D3D3',
        'lightgreen' => '#90EE90',
        'lightpink' => '#FFB6C1',
        'lightsalmon' => '#FFA07A',
        'lightseagreen' => '#20B2AA',
        'lightskyblue' => '#87CEFA',
        'lightslategray' => '#778899',
        'lightslategrey' => '#778899',
        'lightsteelblue' => '#B0C4DE',
        'lightyellow' => '#FFFFE0',
        'lime' => '#00FF00',
        'limegreen' => '#32CD32',
        'linen' => '#FAF0E6',
        'magenta' => '#FF00FF',
        'maroon' => '#800000',
        'mediumaquamarine' => '#66CDAA',
        'mediumblue' => '#0000CD',
        'mediumorchid' => '#BA55D3',
        'mediumpurple' => '#9370D8',
        'mediumseagreen' => '#3CB371',
        'mediumslateblue' => '#7B68EE',
        'mediumspringgreen' => '#00FA9A',
        'mediumturquoise' => '#48D1CC',
        'mediumvioletred' => '#C71585',
        'midnightblue' => '#191970',
        'mintcream' => '#F5FFFA',
        'mistyrose' => '#FFE4E1',
        'moccasin' => '#FFE4B5',
        'navajowhite' => '#FFDEAD',
        'navy' => '#000080',
        'oldlace' => '#FDF5E6',
        'olive' => '#808000',
        'olivedrab' => '#6B8E23',
        'orange' => '#FFA500',
        'orangered' => '#FF4500',
        'orchid' => '#DA70D6',
        'palegoldenrod' => '#EEE8AA',
        'palegreen' => '#98FB98',
        'paleturquoise' => '#AFEEEE',
        'palevioletred' => '#D87093',
        'papayawhip' => '#FFEFD5',
        'peachpuff' => '#FFDAB9',
        'peru' => '#CD853F',
        'pink' => '#FFC0CB',
        'plum' => '#DDA0DD',
        'powderblue' => '#B0E0E6',
        'purple' => '#800080',
        'red' => '#FF0000',
        'rosybrown' => '#BC8F8F',
        'royalblue' => '#4169E1',
        'saddlebrown' => '#8B4513',
        'salmon' => '#FA8072',
        'sandybrown' => '#F4A460',
        'seagreen' => '#2E8B57',
        'seashell' => '#FFF5EE',
        'sienna' => '#A0522D',
        'silver' => '#C0C0C0',
        'skyblue' => '#87CEEB',
        'slateblue' => '#6A5ACD',
        'slategray' => '#708090',
        'slategrey' => '#708090',
        'snow' => '#FFFAFA',
        'springgreen' => '#00FF7F',
        'steelblue' => '#4682B4',
        'tan' => '#D2B48C',
        'teal' => '#008080',
        'thistle' => '#D8BFD8',
        'tomato' => '#FF6347',
        'transparent' => 'transparent',
        'turquoise' => '#40E0D0',
        'violet' => '#EE82EE',
        'wheat' => '#F5DEB3',
        'white' => '#FFFFFF',
        'whitesmoke' => '#F5F5F5',
        'yellow' => '#FFFF00',
        'yellowgreen' => '#9ACD32'
    );
}


abstract class css_writer {

    
    protected static $indent = 0;

    
    protected static function is_pretty() {
        global $CFG;
        return (!empty($CFG->cssoptimiserpretty));
    }

    
    protected static function get_indent() {
        if (self::is_pretty()) {
            return str_repeat("  ", self::$indent);
        }
        return '';
    }

    
    protected static function increase_indent() {
        self::$indent++;
    }

    
    protected static function decrease_indent() {
        self::$indent--;
    }

    
    protected static function get_separator() {
        return (self::is_pretty())?"\n":' ';
    }

    
    public static function media($typestring, array &$rules) {
        $nl = self::get_separator();

        $output = '';
        if ($typestring !== 'all') {
            $output .= "\n@media {$typestring} {".$nl;
            self::increase_indent();
        }
        foreach ($rules as $rule) {
            $output .= $rule->out().$nl;
        }
        if ($typestring !== 'all') {
            self::decrease_indent();
            $output .= '}';
        }
        return $output;
    }

    
    public static function keyframe($for, $name, array &$rules) {
        $output = "\n@{$for} {$name} {";
        foreach ($rules as $rule) {
            $output .= $rule->out();
        }
        $output .= '}';
        return $output;
    }

    
    public static function rule($selector, $styles) {
        $css = self::get_indent()."{$selector}{{$styles}}";
        return $css;
    }

    
    public static function selectors(array $selectors) {
        $nl = self::get_separator();
        $selectorstrings = array();
        foreach ($selectors as $selector) {
            $selectorstrings[] = $selector->out();
        }
        return join(','.$nl, $selectorstrings);
    }

    
    public static function selector(array $components) {
        return trim(join(' ', $components));
    }

    
    public static function styles(array $styles) {
        $bits = array();
        foreach ($styles as $style) {
                                                if (is_array($style)) {
                
                foreach ($style as $advstyle) {
                    $bits[] = $advstyle->out();
                }
                continue;
            }
            $bits[] = $style->out();
        }
        return join('', $bits);
    }

    
    public static function style($name, $value, $important = false) {
        $value = trim($value);
        if ($important && strpos($value, '!important') === false) {
            $value .= ' !important';
        }
        return "{$name}:{$value};";
    }
}


interface core_css_consolidatable_style {
    
    public static function consolidate(array $styles);
}


class css_selector {

    
    protected $selectors = array();

    
    protected $count = 0;

    
    protected $isbasic = null;

    
    public static function init() {
        return new css_selector();
    }

    
    protected function __construct() {
            }

    
    public function add($selector) {
        $selector = trim($selector);
        $count = 0;
        $count += preg_match_all('/(\.|#)/', $selector, $matchesarray);
        if (strpos($selector, '.') !== 0 && strpos($selector, '#') !== 0) {
            $count ++;
        }
                if ($this->isbasic !== false) {
                        if ($count > 1) {
                $this->isbasic = false;
            } else {
                                $this->isbasic = (bool)preg_match('#^[a-z]+(:[a-zA-Z]+)?$#', $selector);
            }
        }
        $this->count = $count;
        $this->selectors[] = $selector;
    }
    
    public function get_selector_count() {
        return $this->count;
    }

    
    public function out() {
        return css_writer::selector($this->selectors);
    }

    
    public function is_basic() {
        return ($this->isbasic === true);
    }
}


class css_rule {

    
    protected $selectors = array();

    
    protected $styles = array();

    
    public static function init() {
        return new css_rule();
    }

    
    protected function __construct($selector = null, array $styles = array()) {
        if ($selector != null) {
            if (is_array($selector)) {
                $this->selectors = $selector;
            } else {
                $this->selectors = array($selector);
            }
            $this->add_styles($styles);
        }
    }

    
    public function add_selector(css_selector $selector) {
        $this->selectors[] = $selector;
    }

    
    public function add_style($style) {
        if (is_string($style)) {
            $style = trim($style);
            if (empty($style)) {
                return;
            }
            $bits = explode(':', $style, 2);
            if (count($bits) == 2) {
                list($name, $value) = array_map('trim', $bits);
            }
            if (isset($name) && isset($value) && $name !== '' && $value !== '') {
                $style = css_style::init_automatic($name, $value);
            }
        } else if ($style instanceof css_style) {
                                                $style = clone($style);
        }
        if ($style instanceof css_style) {
            $name = $style->get_name();
            $exists = array_key_exists($name, $this->styles);
                                                if ($style->allows_multiple_values() || ($exists && is_array($this->styles[$name]))) {
                if (!$exists) {
                    $this->styles[$name] = array();
                } else if ($this->styles[$name] instanceof css_style) {
                    $this->styles[$name] = array($this->styles[$name]);
                }
                $this->styles[$name][] = $style;
            } else if ($exists) {
                $this->styles[$name]->set_value($style->get_value());
            } else {
                $this->styles[$name] = $style;
            }
        } else if (is_array($style)) {
                                    foreach ($style as $astyle) {
                $this->add_style($astyle);
            }
        }
    }

    
    public function add_styles(array $styles) {
        foreach ($styles as $style) {
            $this->add_style($style);
        }
    }

    
    public function get_selectors() {
        return $this->selectors;
    }

    
    public function get_styles() {
        return $this->styles;
    }

    
    public function out() {
        $selectors = css_writer::selectors($this->selectors);
        $styles = css_writer::styles($this->get_consolidated_styles());
        return css_writer::rule($selectors, $styles);
    }

    
    public function get_consolidated_styles() {
        
        $organisedstyles = array();
        
        $finalstyles = array();
        
        $consolidate = array();
        
        $advancedstyles = array();
        foreach ($this->styles as $style) {
                                    if (is_array($style)) {
                $single = null;
                $count = 0;
                foreach ($style as $advstyle) {
                    
                    $key = $count++;
                    $advancedstyles[$key] = $advstyle;
                    if (!$advstyle->allows_multiple_values()) {
                        if (!is_null($single)) {
                            unset($advancedstyles[$single]);
                        }
                        $single = $key;
                    }
                }
                if (!is_null($single)) {
                    $style = $advancedstyles[$single];

                    $consolidatetoclass = $style->consolidate_to();
                    if (($style->is_valid() || $style->is_special_empty_value()) && !empty($consolidatetoclass) &&
                            class_exists('css_style_'.$consolidatetoclass)) {
                        $class = 'css_style_'.$consolidatetoclass;
                        if (!array_key_exists($class, $consolidate)) {
                            $consolidate[$class] = array();
                            $organisedstyles[$class] = true;
                        }
                        $consolidate[$class][] = $style;
                        unset($advancedstyles[$single]);
                    }
                }

                continue;
            }
            $consolidatetoclass = $style->consolidate_to();
            if (($style->is_valid() || $style->is_special_empty_value()) && !empty($consolidatetoclass) &&
                    class_exists('css_style_'.$consolidatetoclass)) {
                $class = 'css_style_'.$consolidatetoclass;
                if (!array_key_exists($class, $consolidate)) {
                    $consolidate[$class] = array();
                    $organisedstyles[$class] = true;
                }
                $consolidate[$class][] = $style;
            } else {
                $organisedstyles[$style->get_name()] = $style;
            }
        }

        foreach ($consolidate as $class => $styles) {
            $organisedstyles[$class] = call_user_func(array($class, 'consolidate'), $styles);
        }

        foreach ($organisedstyles as $style) {
            if (is_array($style)) {
                foreach ($style as $s) {
                    $finalstyles[] = $s;
                }
            } else {
                $finalstyles[] = $style;
            }
        }
        $finalstyles = array_merge($finalstyles, $advancedstyles);
        return $finalstyles;
    }

    
    public function split_by_selector() {
        $return = array();
        foreach ($this->selectors as $selector) {
            $return[] = new css_rule($selector, $this->styles);
        }
        return $return;
    }

    
    public function split_by_style() {
        $return = array();
        foreach ($this->styles as $style) {
            if (is_array($style)) {
                $return[] = new css_rule($this->selectors, $style);
                continue;
            }
            $return[] = new css_rule($this->selectors, array($style));
        }
        return $return;
    }

    
    public function get_style_hash() {
        return md5(css_writer::styles($this->styles));
    }

    
    public function get_selector_hash() {
        return md5(css_writer::selectors($this->selectors));
    }

    
    public function get_selector_count() {
        $count = 0;
        foreach ($this->selectors as $selector) {
            $count += $selector->get_selector_count();
        }
        return $count;
    }

    
    public function has_errors() {
        foreach ($this->styles as $style) {
            if (is_array($style)) {
                
                foreach ($style as $advstyle) {
                    if ($advstyle->has_error()) {
                        return true;
                    }
                }
                continue;
            }
            if ($style->has_error()) {
                return true;
            }
        }
        return false;
    }

    
    public function get_error_string() {
        $css = $this->out();
        $errors = array();
        foreach ($this->styles as $style) {
            if (is_array($style)) {
                
                foreach ($style as $advstyle) {
                    if ($advstyle instanceof css_style && $advstyle->has_error()) {
                        $errors[] = "  * ".$advstyle->get_last_error();
                    }
                }
            } else if ($style instanceof css_style && $style->has_error()) {
                $errors[] = "  * ".$style->get_last_error();
            }
        }
        return $css." has the following errors:\n".join("\n", $errors);
    }

    
    public function is_reset_rule() {
        foreach ($this->selectors as $selector) {
            if (!$selector->is_basic()) {
                return false;
            }
        }
        return true;
    }
}


abstract class css_rule_collection {
    
    protected $rules = array();

    
    abstract public function out();

    
    public function add_rule(css_rule $newrule) {
        foreach ($newrule->split_by_selector() as $rule) {
            $hash = $rule->get_selector_hash();
            if (!array_key_exists($hash, $this->rules)) {
                $this->rules[$hash] = $rule;
            } else {
                $this->rules[$hash]->add_styles($rule->get_styles());
            }
        }
    }

    
    public function get_rules() {
        return $this->rules;
    }

    
    public function organise_rules_by_selectors() {
        
        $optimisedrules = array();
        $beforecount = count($this->rules);
        $lasthash = null;
        
        $lastrule = null;
        foreach ($this->rules as $rule) {
            $hash = $rule->get_style_hash();
            if ($lastrule !== null && $lasthash !== null && $hash === $lasthash) {
                foreach ($rule->get_selectors() as $selector) {
                    $lastrule->add_selector($selector);
                }
                continue;
            }
            $lastrule = clone($rule);
            $lasthash = $hash;
            $optimisedrules[] = $lastrule;
        }
        $this->rules = array();
        foreach ($optimisedrules as $optimised) {
            $this->rules[$optimised->get_selector_hash()] = $optimised;
        }
        $aftercount = count($this->rules);
        return ($beforecount < $aftercount);
    }

    
    public function count_rules() {
        return count($this->rules);
    }

    
    public function count_selectors() {
        $count = 0;
        foreach ($this->rules as $rule) {
            $count += $rule->get_selector_count();
        }
        return $count;
    }

    
    public function has_errors() {
        foreach ($this->rules as $rule) {
            if ($rule->has_errors()) {
                return true;
            }
        }
        return false;
    }

    
    public function get_errors() {
        $errors = array();
        foreach ($this->rules as $rule) {
            if ($rule->has_errors()) {
                $errors[] = $rule->get_error_string();
            }
        }
        return $errors;
    }
}


class css_media extends css_rule_collection {

    
    protected $types = array();

    
    public function __construct($for = 'all') {
        $types = explode(',', $for);
        $this->types = array_map('trim', $types);
    }

    
    public function out() {
        return css_writer::media(join(',', $this->types), $this->rules);
    }

    
    public function get_types() {
        return $this->types;
    }

    
    public function get_reset_rules($remove = false) {
        $resetrules = array();
        foreach ($this->rules as $key => $rule) {
            if ($rule->is_reset_rule()) {
                $resetrules[] = clone $rule;
                if ($remove) {
                    unset($this->rules[$key]);
                }
            }
        }
        return $resetrules;
    }
}


class css_keyframe extends css_rule_collection {

    
    protected $for;

    
    protected $name;
    
    public function __construct($for, $name) {
        $this->for = $for;
        $this->name = $name;
    }
    
    public function get_for() {
        return $this->for;
    }
    
    public function get_name() {
        return $this->name;
    }
    
    public function out() {
        return css_writer::keyframe($this->for, $this->name, $this->rules);
    }
}


abstract class css_style {

    
    const NULL_VALUE = '@@$NULL$@@';

    
    protected $name;

    
    protected $value;

    
    protected $important = false;

    
    protected $error = false;

    
    protected $errormessage = null;

    
    public static function init_automatic($name, $value) {
        $cleanedname = preg_replace('#[^a-zA-Z0-9]+#', '', $name);
        $specificclass = 'css_style_'.$cleanedname;
        if (class_exists($specificclass)) {
            $style = call_user_func(array($specificclass, 'init'), $value);
            if ($cleanedname !== $name && !is_array($style)) {
                $style->set_actual_name($name);
            }
            return $style;
        }
        return new css_style_generic($name, $value);
    }

    
    protected function __construct($name, $value) {
        $this->name = $name;
        $this->set_value($value);
    }

    
    final public function set_value($value) {
        $value = trim($value);
        $important = preg_match('#(\!important\s*;?\s*)$#', $value, $matches);
        if ($important) {
            $value = substr($value, 0, -(strlen($matches[1])));
            $value = rtrim($value);
        }
        if (!$this->important || $important) {
            $this->value = $this->clean_value($value);
            $this->important = $important;
        }
        if (!$this->is_valid()) {
            $this->set_error('Invalid value for '.$this->name);
        }
    }

    
    public function is_valid() {
        return true;
    }

    
    public function get_name() {
        return $this->name;
    }

    
    public function get_value($includeimportant = true) {
        $value = $this->value;
        if ($includeimportant && $this->important) {
            $value .= ' !important';
        }
        return $value;
    }

    
    public function out($value = null) {
        if (is_null($value)) {
            $value = $this->get_value();
        }
        return css_writer::style($this->name, $value, $this->important);
    }

    
    protected function clean_value($value) {
        return $value;
    }

    
    public function consolidate_to() {
        return null;
    }

    
    protected function set_error($message) {
        $this->error = true;
        $this->errormessage = $message;
    }

    
    public function has_error() {
        return $this->error;
    }

    
    public function get_last_error() {
        return $this->errormessage;
    }

    
    public function is_special_empty_value() {
        return false;
    }

    
    public function allows_multiple_values() {
        return false;
    }

    
    public function is_important() {
        return !empty($this->important);
    }

    
    public function set_important($important = true) {
        $this->important = (bool) $important;
    }

    
    public function set_actual_name($name) {
        $this->name = $name;
    }
}


class css_style_generic extends css_style {

    
    protected function clean_value($value) {
        if (trim($value) == '0px') {
            $value = 0;
        } else if (preg_match('/^#([a-fA-F0-9]{3,6})/', $value, $matches)) {
            $value = '#'.strtoupper($matches[1]);
        }
        return $value;
    }
}


class css_style_color extends css_style {

    
    public static function init($value) {
        return new css_style_color('color', $value);
    }

    
    protected function clean_value($value) {
        $value = trim($value);
        if (css_is_colour($value)) {
            if (preg_match('/#([a-fA-F0-9]{6})/', $value, $matches)) {
                $value = '#'.strtoupper($matches[1]);
            } else if (preg_match('/#([a-fA-F0-9])([a-fA-F0-9])([a-fA-F0-9])/', $value, $matches)) {
                $value = $matches[1] . $matches[1] . $matches[2] . $matches[2] . $matches[3] . $matches[3];
                $value = '#'.strtoupper($value);
            } else if (array_key_exists(strtolower($value), css_optimiser::$htmlcolours)) {
                $value = css_optimiser::$htmlcolours[strtolower($value)];
            }
        }
        return $value;
    }

    
    public function out($overridevalue = null) {
        if ($overridevalue === null) {
            $overridevalue = $this->value;
        }
        return parent::out(self::shrink_value($overridevalue));
    }

    
    public static function shrink_value($value) {
        if (preg_match('/#([a-fA-F0-9])\1([a-fA-F0-9])\2([a-fA-F0-9])\3/', $value, $matches)) {
            return '#'.$matches[1].$matches[2].$matches[3];
        }
        return $value;
    }

    
    public function is_valid() {
        return css_is_colour($this->value);
    }
}


class css_style_width extends css_style {

    
    public function is_valid() {
        return css_is_width($this->value);
    }

    
    protected function clean_value($value) {
        if (!css_is_width($value)) {
                                    $this->set_error('Invalid width specified for '.$this->name);
        } else if (preg_match('#^0\D+$#', $value)) {
            $value = 0;
        }
        return trim($value);
    }

    
    public static function init($value) {
        return new css_style_width('width', $value);
    }
}


class css_style_margin extends css_style_width implements core_css_consolidatable_style {

    
    public static function init($value) {
        $important = '';
        if (strpos($value, '!important') !== false) {
            $important = ' !important';
            $value = str_replace('!important', '', $value);
        }

        $value = preg_replace('#\s+#', ' ', trim($value));
        $bits = explode(' ', $value, 4);

        $top = $right = $bottom = $left = null;
        if (count($bits) > 0) {
            $top = $right = $bottom = $left = array_shift($bits);
        }
        if (count($bits) > 0) {
            $right = $left = array_shift($bits);
        }
        if (count($bits) > 0) {
            $bottom = array_shift($bits);
        }
        if (count($bits) > 0) {
            $left = array_shift($bits);
        }
        return array(
            new css_style_margintop('margin-top', $top.$important),
            new css_style_marginright('margin-right', $right.$important),
            new css_style_marginbottom('margin-bottom', $bottom.$important),
            new css_style_marginleft('margin-left', $left.$important)
        );
    }

    
    public static function consolidate(array $styles) {
        if (count($styles) != 4) {
            return $styles;
        }

        $someimportant = false;
        $allimportant = null;
        $notimportantequal = null;
        $firstvalue = null;
        foreach ($styles as $style) {
            if ($style->is_important()) {
                $someimportant = true;
                if ($allimportant === null) {
                    $allimportant = true;
                }
            } else {
                if ($allimportant === true) {
                    $allimportant = false;
                }
                if ($firstvalue == null) {
                    $firstvalue = $style->get_value(false);
                    $notimportantequal = true;
                } else if ($notimportantequal && $firstvalue !== $style->get_value(false)) {
                    $notimportantequal = false;
                }
            }
        }

        if ($someimportant && !$allimportant && !$notimportantequal) {
            return $styles;
        }

        if ($someimportant && !$allimportant && $notimportantequal) {
            $return = array(
                new css_style_margin('margin', $firstvalue)
            );
            foreach ($styles as $style) {
                if ($style->is_important()) {
                    $return[] = $style;
                }
            }
            return $return;
        } else {
            $top = null;
            $right = null;
            $bottom = null;
            $left = null;
            foreach ($styles as $style) {
                switch ($style->get_name()) {
                    case 'margin-top' :
                        $top = $style->get_value(false);
                        break;
                    case 'margin-right' :
                        $right = $style->get_value(false);
                        break;
                    case 'margin-bottom' :
                        $bottom = $style->get_value(false);
                        break;
                    case 'margin-left' :
                        $left = $style->get_value(false);
                        break;
                }
            }
            if ($top == $bottom && $left == $right) {
                if ($top == $left) {
                    $returnstyle = new css_style_margin('margin', $top);
                } else {
                    $returnstyle = new css_style_margin('margin', "{$top} {$left}");
                }
            } else if ($left == $right) {
                $returnstyle = new css_style_margin('margin', "{$top} {$right} {$bottom}");
            } else {
                $returnstyle = new css_style_margin('margin', "{$top} {$right} {$bottom} {$left}");
            }
            if ($allimportant) {
                $returnstyle->set_important();
            }
            return array($returnstyle);
        }
    }
}


class css_style_margintop extends css_style_margin {

    
    public static function init($value) {
        return new css_style_margintop('margin-top', $value);
    }

    
    public function consolidate_to() {
        return 'margin';
    }
}


class css_style_marginright extends css_style_margin {

    
    public static function init($value) {
        return new css_style_marginright('margin-right', $value);
    }

    
    public function consolidate_to() {
        return 'margin';
    }
}


class css_style_marginbottom extends css_style_margin {

    
    public static function init($value) {
        return new css_style_marginbottom('margin-bottom', $value);
    }

    
    public function consolidate_to() {
        return 'margin';
    }
}


class css_style_marginleft extends css_style_margin {

    
    public static function init($value) {
        return new css_style_marginleft('margin-left', $value);
    }

    
    public function consolidate_to() {
        return 'margin';
    }
}


class css_style_border extends css_style implements core_css_consolidatable_style {

    
    public static function init($value) {
        $value = preg_replace('#\s+#', ' ', $value);
        $bits = explode(' ', $value, 3);

        $return = array();
        if (count($bits) > 0) {
            $width = array_shift($bits);
            if (!css_style_borderwidth::is_border_width($width)) {
                $width = '0';
            }
            $return[] = css_style_bordertopwidth::init($width);
            $return[] = css_style_borderrightwidth::init($width);
            $return[] = css_style_borderbottomwidth::init($width);
            $return[] = css_style_borderleftwidth::init($width);
        }
        if (count($bits) > 0) {
            $style = array_shift($bits);
            $return[] = css_style_bordertopstyle::init($style);
            $return[] = css_style_borderrightstyle::init($style);
            $return[] = css_style_borderbottomstyle::init($style);
            $return[] = css_style_borderleftstyle::init($style);
        }
        if (count($bits) > 0) {
            $colour = array_shift($bits);
            $return[] = css_style_bordertopcolor::init($colour);
            $return[] = css_style_borderrightcolor::init($colour);
            $return[] = css_style_borderbottomcolor::init($colour);
            $return[] = css_style_borderleftcolor::init($colour);
        }
        return $return;
    }

    
    public static function consolidate(array $styles) {

        $borderwidths = array('top' => null, 'right' => null, 'bottom' => null, 'left' => null);
        $borderstyles = array('top' => null, 'right' => null, 'bottom' => null, 'left' => null);
        $bordercolors = array('top' => null, 'right' => null, 'bottom' => null, 'left' => null);

        foreach ($styles as $style) {
            switch ($style->get_name()) {
                case 'border-top-width':
                    $borderwidths['top'] = $style->get_value();
                    break;
                case 'border-right-width':
                    $borderwidths['right'] = $style->get_value();
                    break;
                case 'border-bottom-width':
                    $borderwidths['bottom'] = $style->get_value();
                    break;
                case 'border-left-width':
                    $borderwidths['left'] = $style->get_value();
                    break;

                case 'border-top-style':
                    $borderstyles['top'] = $style->get_value();
                    break;
                case 'border-right-style':
                    $borderstyles['right'] = $style->get_value();
                    break;
                case 'border-bottom-style':
                    $borderstyles['bottom'] = $style->get_value();
                    break;
                case 'border-left-style':
                    $borderstyles['left'] = $style->get_value();
                    break;

                case 'border-top-color':
                    $bordercolors['top'] = css_style_color::shrink_value($style->get_value());
                    break;
                case 'border-right-color':
                    $bordercolors['right'] = css_style_color::shrink_value($style->get_value());
                    break;
                case 'border-bottom-color':
                    $bordercolors['bottom'] = css_style_color::shrink_value($style->get_value());
                    break;
                case 'border-left-color':
                    $bordercolors['left'] = css_style_color::shrink_value($style->get_value());
                    break;
            }
        }

        $uniquewidths = count(array_unique($borderwidths));
        $uniquestyles = count(array_unique($borderstyles));
        $uniquecolors = count(array_unique($bordercolors));

        $nullwidths = in_array(null, $borderwidths, true);
        $nullstyles = in_array(null, $borderstyles, true);
        $nullcolors = in_array(null, $bordercolors, true);

        $allwidthsthesame = ($uniquewidths === 1)?1:0;
        $allstylesthesame = ($uniquestyles === 1)?1:0;
        $allcolorsthesame = ($uniquecolors === 1)?1:0;

        $allwidthsnull = $allwidthsthesame && $nullwidths;
        $allstylesnull = $allstylesthesame && $nullstyles;
        $allcolorsnull = $allcolorsthesame && $nullcolors;

        
        $return = array();
        if ($allwidthsnull && $allstylesnull && $allcolorsnull) {
                        return array(new css_style_border('border', ''));

        } else if ($allwidthsnull && $allstylesnull) {

            self::consolidate_styles_by_direction($return, 'css_style_bordercolor', 'border-color', $bordercolors);
            return $return;

        } else if ($allwidthsnull && $allcolorsnull) {

            self::consolidate_styles_by_direction($return, 'css_style_borderstyle', 'border-style', $borderstyles);
            return $return;

        } else if ($allcolorsnull && $allstylesnull) {

            self::consolidate_styles_by_direction($return, 'css_style_borderwidth', 'border-width', $borderwidths);
            return $return;

        }

        if ($allwidthsthesame + $allstylesthesame + $allcolorsthesame == 3) {

            $return[] = new css_style_border('border', $borderwidths['top'].' '.$borderstyles['top'].' '.$bordercolors['top']);

        } else if ($allwidthsthesame + $allstylesthesame + $allcolorsthesame == 2) {

            if ($allwidthsthesame && $allstylesthesame && !$nullwidths && !$nullstyles) {

                $return[] = new css_style_border('border', $borderwidths['top'].' '.$borderstyles['top']);
                self::consolidate_styles_by_direction($return, 'css_style_bordercolor', 'border-color', $bordercolors);

            } else if ($allwidthsthesame && $allcolorsthesame && !$nullwidths && !$nullcolors) {

                $return[] = new css_style_border('border', $borderwidths['top'].' solid '.$bordercolors['top']);
                self::consolidate_styles_by_direction($return, 'css_style_borderstyle', 'border-style', $borderstyles);

            } else if ($allstylesthesame && $allcolorsthesame && !$nullstyles && !$nullcolors) {

                $return[] = new css_style_border('border', '1px '.$borderstyles['top'].' '.$bordercolors['top']);
                self::consolidate_styles_by_direction($return, 'css_style_borderwidth', 'border-width', $borderwidths);

            } else {
                self::consolidate_styles_by_direction($return, 'css_style_borderwidth', 'border-width', $borderwidths);
                self::consolidate_styles_by_direction($return, 'css_style_borderstyle', 'border-style', $borderstyles);
                self::consolidate_styles_by_direction($return, 'css_style_bordercolor', 'border-color', $bordercolors);
            }

        } else if (!$nullwidths && !$nullcolors && !$nullstyles &&
            max(array_count_values($borderwidths)) == 3 &&
            max(array_count_values($borderstyles)) == 3 &&
            max(array_count_values($bordercolors)) == 3) {

            $widthkeys = array();
            $stylekeys = array();
            $colorkeys = array();

            foreach ($borderwidths as $key => $value) {
                if (!array_key_exists($value, $widthkeys)) {
                    $widthkeys[$value] = array();
                }
                $widthkeys[$value][] = $key;
            }
            usort($widthkeys, 'css_sort_by_count');
            $widthkeys = array_values($widthkeys);

            foreach ($borderstyles as $key => $value) {
                if (!array_key_exists($value, $stylekeys)) {
                    $stylekeys[$value] = array();
                }
                $stylekeys[$value][] = $key;
            }
            usort($stylekeys, 'css_sort_by_count');
            $stylekeys = array_values($stylekeys);

            foreach ($bordercolors as $key => $value) {
                if (!array_key_exists($value, $colorkeys)) {
                    $colorkeys[$value] = array();
                }
                $colorkeys[$value][] = $key;
            }
            usort($colorkeys, 'css_sort_by_count');
            $colorkeys = array_values($colorkeys);

            if ($widthkeys == $stylekeys && $stylekeys == $colorkeys) {
                $key = $widthkeys[0][0];
                self::build_style_string($return, 'css_style_border', 'border',
                    $borderwidths[$key], $borderstyles[$key], $bordercolors[$key]);
                $key = $widthkeys[1][0];
                self::build_style_string($return, 'css_style_border'.$key, 'border-'.$key,
                    $borderwidths[$key], $borderstyles[$key], $bordercolors[$key]);
            } else {
                self::build_style_string($return, 'css_style_bordertop', 'border-top',
                    $borderwidths['top'], $borderstyles['top'], $bordercolors['top']);
                self::build_style_string($return, 'css_style_borderright', 'border-right',
                    $borderwidths['right'], $borderstyles['right'], $bordercolors['right']);
                self::build_style_string($return, 'css_style_borderbottom', 'border-bottom',
                    $borderwidths['bottom'], $borderstyles['bottom'], $bordercolors['bottom']);
                self::build_style_string($return, 'css_style_borderleft', 'border-left',
                    $borderwidths['left'], $borderstyles['left'], $bordercolors['left']);
            }
        } else {
            self::build_style_string($return, 'css_style_bordertop', 'border-top',
                $borderwidths['top'], $borderstyles['top'], $bordercolors['top']);
            self::build_style_string($return, 'css_style_borderright', 'border-right',
                $borderwidths['right'], $borderstyles['right'], $bordercolors['right']);
            self::build_style_string($return, 'css_style_borderbottom', 'border-bottom',
                $borderwidths['bottom'], $borderstyles['bottom'], $bordercolors['bottom']);
            self::build_style_string($return, 'css_style_borderleft', 'border-left',
                $borderwidths['left'], $borderstyles['left'], $bordercolors['left']);
        }
        foreach ($return as $key => $style) {
            if ($style->get_value() == '') {
                unset($return[$key]);
            }
        }
        return $return;
    }

    
    public function consolidate_to() {
        return 'border';
    }

    
    public static function consolidate_styles_by_direction(&$array, $class, $style,
                                                           $top, $right = null, $bottom = null, $left = null) {
        if (is_array($top)) {
            $right = $top['right'];
            $bottom = $top['bottom'];
            $left = $top['left'];
            $top = $top['top'];
        }

        if ($top == $bottom && $left == $right && $top == $left) {
            if (is_null($top)) {
                $array[] = new $class($style, '');
            } else {
                $array[] =  new $class($style, $top);
            }
        } else if ($top == null || $right == null || $bottom == null || $left == null) {
            if ($top !== null) {
                $array[] = new $class(str_replace('border-', 'border-top-', $style), $top);
            }
            if ($right !== null) {
                $array[] = new $class(str_replace('border-', 'border-right-', $style), $right);
            }
            if ($bottom !== null) {
                $array[] = new $class(str_replace('border-', 'border-bottom-', $style), $bottom);
            }
            if ($left !== null) {
                $array[] = new $class(str_replace('border-', 'border-left-', $style), $left);
            }
        } else if ($top == $bottom && $left == $right) {
            $array[] = new $class($style, $top.' '.$right);
        } else if ($left == $right) {
            $array[] = new $class($style, $top.' '.$right.' '.$bottom);
        } else {
            $array[] = new $class($style, $top.' '.$right.' '.$bottom.' '.$left);
        }
        return true;
    }

    
    public static function build_style_string(&$array, $class, $cssstyle, $width = null, $style = null, $color = null) {
        if (!is_null($width) && !is_null($style) && !is_null($color)) {
            $array[] = new $class($cssstyle, $width.' '.$style.' '.$color);
        } else if (!is_null($width) && !is_null($style) && is_null($color)) {
            $array[] = new $class($cssstyle, $width.' '.$style);
        } else if (!is_null($width) && is_null($style) && is_null($color)) {
            $array[] = new $class($cssstyle, $width);
        } else {
            if (!is_null($width)) {
                $array[] = new $class($cssstyle, $width);
            }
            if (!is_null($style)) {
                $array[] = new $class($cssstyle, $style);
            }
            if (!is_null($color)) {
                $array[] = new $class($cssstyle, $color);
            }
        }
        return true;
    }
}


class css_style_bordercolor extends css_style_color {

    
    public static function init($value) {
        $value = preg_replace('#\s+#', ' ', $value);
        $bits = explode(' ', $value, 4);

        $top = $right = $bottom = $left = null;
        if (count($bits) > 0) {
            $top = $right = $bottom = $left = array_shift($bits);
        }
        if (count($bits) > 0) {
            $right = $left = array_shift($bits);
        }
        if (count($bits) > 0) {
            $bottom = array_shift($bits);
        }
        if (count($bits) > 0) {
            $left = array_shift($bits);
        }
        return array(
            css_style_bordertopcolor::init($top),
            css_style_borderrightcolor::init($right),
            css_style_borderbottomcolor::init($bottom),
            css_style_borderleftcolor::init($left)
        );
    }

    
    public function consolidate_to() {
        return 'border';
    }

    
    protected function clean_value($value) {
        $values = explode(' ', $value);
        $values = array_map('parent::clean_value', $values);
        return join (' ', $values);
    }

    
    public function out($overridevalue = null) {
        if ($overridevalue === null) {
            $overridevalue = $this->value;
        }
        $values = explode(' ', $overridevalue);
        $values = array_map('css_style_color::shrink_value', $values);
        return parent::out(join (' ', $values));
    }
}


class css_style_borderleft extends css_style_generic {

    
    public static function init($value) {
        $value = preg_replace('#\s+#', ' ', $value);
        $bits = explode(' ', $value, 3);

        $return = array();
        if (count($bits) > 0) {
            $return[] = css_style_borderleftwidth::init(array_shift($bits));
        }
        if (count($bits) > 0) {
            $return[] = css_style_borderleftstyle::init(array_shift($bits));
        }
        if (count($bits) > 0) {
            $return[] = css_style_borderleftcolor::init(array_shift($bits));
        }
        return $return;
    }

    
    public function consolidate_to() {
        return 'border';
    }
}


class css_style_borderright extends css_style_generic {

    
    public static function init($value) {
        $value = preg_replace('#\s+#', ' ', $value);
        $bits = explode(' ', $value, 3);

        $return = array();
        if (count($bits) > 0) {
            $return[] = css_style_borderrightwidth::init(array_shift($bits));
        }
        if (count($bits) > 0) {
            $return[] = css_style_borderrightstyle::init(array_shift($bits));
        }
        if (count($bits) > 0) {
            $return[] = css_style_borderrightcolor::init(array_shift($bits));
        }
        return $return;
    }

    
    public function consolidate_to() {
        return 'border';
    }
}


class css_style_bordertop extends css_style_generic {

    
    public static function init($value) {
        $value = preg_replace('#\s+#', ' ', $value);
        $bits = explode(' ', $value, 3);

        $return = array();
        if (count($bits) > 0) {
            $return[] = css_style_bordertopwidth::init(array_shift($bits));
        }
        if (count($bits) > 0) {
            $return[] = css_style_bordertopstyle::init(array_shift($bits));
        }
        if (count($bits) > 0) {
            $return[] = css_style_bordertopcolor::init(array_shift($bits));
        }
        return $return;
    }

    
    public function consolidate_to() {
        return 'border';
    }
}


class css_style_borderbottom extends css_style_generic {

    
    public static function init($value) {
        $value = preg_replace('#\s+#', ' ', $value);
        $bits = explode(' ', $value, 3);

        $return = array();
        if (count($bits) > 0) {
            $return[] = css_style_borderbottomwidth::init(array_shift($bits));
        }
        if (count($bits) > 0) {
            $return[] = css_style_borderbottomstyle::init(array_shift($bits));
        }
        if (count($bits) > 0) {
            $return[] = css_style_borderbottomcolor::init(array_shift($bits));
        }
        return $return;
    }

    
    public function consolidate_to() {
        return 'border';
    }
}


class css_style_borderwidth extends css_style_width {

    
    public static function init($value) {
        $value = preg_replace('#\s+#', ' ', $value);
        $bits = explode(' ', $value, 4);

        $top = $right = $bottom = $left = null;
        if (count($bits) > 0) {
            $top = $right = $bottom = $left = array_shift($bits);
        }
        if (count($bits) > 0) {
            $right = $left = array_shift($bits);
        }
        if (count($bits) > 0) {
            $bottom = array_shift($bits);
        }
        if (count($bits) > 0) {
            $left = array_shift($bits);
        }
        return array(
            css_style_bordertopwidth::init($top),
            css_style_borderrightwidth::init($right),
            css_style_borderbottomwidth::init($bottom),
            css_style_borderleftwidth::init($left)
        );
    }

    
    public function consolidate_to() {
        return 'border';
    }

    
    public function is_valid() {
        return self::is_border_width($this->value);
    }

    
    protected function clean_value($value) {
        $isvalid = self::is_border_width($value);
        if (!$isvalid) {
            $this->set_error('Invalid width specified for '.$this->name);
        } else if (preg_match('#^0\D+$#', $value)) {
            return '0';
        }
        return trim($value);
    }

    
    public static function is_border_width($value) {
        $altwidthvalues = array('thin', 'medium', 'thick');
        return css_is_width($value) || in_array($value, $altwidthvalues);
    }
}


class css_style_borderstyle extends css_style_generic {

    
    public static function init($value) {
        $value = preg_replace('#\s+#', ' ', $value);
        $bits = explode(' ', $value, 4);

        $top = $right = $bottom = $left = null;
        if (count($bits) > 0) {
            $top = $right = $bottom = $left = array_shift($bits);
        }
        if (count($bits) > 0) {
            $right = $left = array_shift($bits);
        }
        if (count($bits) > 0) {
            $bottom = array_shift($bits);
        }
        if (count($bits) > 0) {
            $left = array_shift($bits);
        }
        return array(
            css_style_bordertopstyle::init($top),
            css_style_borderrightstyle::init($right),
            css_style_borderbottomstyle::init($bottom),
            css_style_borderleftstyle::init($left)
        );
    }

    
    public function consolidate_to() {
        return 'border';
    }
}


class css_style_bordertopcolor extends css_style_bordercolor {

    
    public static function init($value) {
        return new css_style_bordertopcolor('border-top-color', $value);
    }

    
    public function consolidate_to() {
        return 'border';
    }
}


class css_style_borderleftcolor extends css_style_bordercolor {

    
    public static function init($value) {
        return new css_style_borderleftcolor('border-left-color', $value);
    }

    
    public function consolidate_to() {
        return 'border';
    }
}


class css_style_borderrightcolor extends css_style_bordercolor {

    
    public static function init($value) {
        return new css_style_borderrightcolor('border-right-color', $value);
    }

    
    public function consolidate_to() {
        return 'border';
    }
}


class css_style_borderbottomcolor extends css_style_bordercolor {

    
    public static function init($value) {
        return new css_style_borderbottomcolor('border-bottom-color', $value);
    }

    
    public function consolidate_to() {
        return 'border';
    }
}


class css_style_bordertopwidth extends css_style_borderwidth {

    
    public static function init($value) {
        return new css_style_bordertopwidth('border-top-width', $value);
    }

    
    public function consolidate_to() {
        return 'border';
    }
}


class css_style_borderleftwidth extends css_style_borderwidth {

    
    public static function init($value) {
        return new css_style_borderleftwidth('border-left-width', $value);
    }

    
    public function consolidate_to() {
        return 'border';
    }
}


class css_style_borderrightwidth extends css_style_borderwidth {

    
    public static function init($value) {
        return new css_style_borderrightwidth('border-right-width', $value);
    }

    
    public function consolidate_to() {
        return 'border';
    }
}


class css_style_borderbottomwidth extends css_style_borderwidth {

    
    public static function init($value) {
        return new css_style_borderbottomwidth('border-bottom-width', $value);
    }

    
    public function consolidate_to() {
        return 'border';
    }
}


class css_style_bordertopstyle extends css_style_borderstyle {

    
    public static function init($value) {
        return new css_style_bordertopstyle('border-top-style', $value);
    }

    
    public function consolidate_to() {
        return 'border';
    }
}


class css_style_borderleftstyle extends css_style_borderstyle {

    
    public static function init($value) {
        return new css_style_borderleftstyle('border-left-style', $value);
    }

    
    public function consolidate_to() {
        return 'border';
    }
}


class css_style_borderrightstyle extends css_style_borderstyle {

    
    public static function init($value) {
        return new css_style_borderrightstyle('border-right-style', $value);
    }

    
    public function consolidate_to() {
        return 'border';
    }
}


class css_style_borderbottomstyle extends css_style_borderstyle {

    
    public static function init($value) {
        return new css_style_borderbottomstyle('border-bottom-style', $value);
    }

    
    public function consolidate_to() {
        return 'border';
    }
}


class css_style_background extends css_style implements core_css_consolidatable_style {

    
    public static function init($value) {
                $imageurl = null;
        if (preg_match('#url\(([^\)]+)\)#', $value, $matches)) {
            $imageurl = trim($matches[1]);
            $value = str_replace($matches[1], '', $value);
        }

                $brackets = array();
        $bracketcount = 0;
        while (preg_match('#\([^\)\(]+\)#', $value, $matches)) {
            $key = "##BRACKET-{$bracketcount}##";
            $bracketcount++;
            $brackets[$key] = $matches[0];
            $value = str_replace($matches[0], $key, $value);
        }

        $important = (stripos($value, '!important') !== false);
        if ($important) {
                        $value = str_replace('!important', '', $value);
        }

        $value = preg_replace('#\s+#', ' ', $value);
        $bits = explode(' ', $value);

        foreach ($bits as $key => $bit) {
            $bits[$key] = self::replace_bracket_placeholders($bit, $brackets);
        }
        unset($bracketcount);
        unset($brackets);

        $repeats = array('repeat', 'repeat-x', 'repeat-y', 'no-repeat', 'inherit');
        $attachments = array('scroll' , 'fixed', 'inherit');
        $positions = array('top', 'left', 'bottom', 'right', 'center');

        
        $return = array();
        $unknownbits = array();

        $color = self::NULL_VALUE;
        if (count($bits) > 0 && css_is_colour(reset($bits))) {
            $color = array_shift($bits);
        }

        $image = self::NULL_VALUE;
        if (count($bits) > 0 && preg_match('#^\s*(none|inherit|url\(\))\s*$#', reset($bits))) {
            $image = array_shift($bits);
            if ($image == 'url()') {
                $image = "url({$imageurl})";
            }
        }

        $repeat = self::NULL_VALUE;
        if (count($bits) > 0 && in_array(reset($bits), $repeats)) {
            $repeat = array_shift($bits);
        }

        $attachment = self::NULL_VALUE;
        if (count($bits) > 0 && in_array(reset($bits), $attachments)) {
                        $attachment = array_shift($bits);
        }

        $position = self::NULL_VALUE;
        if (count($bits) > 0) {
            $widthbits = array();
            foreach ($bits as $bit) {
                if (in_array($bit, $positions) || css_is_width($bit)) {
                    $widthbits[] = $bit;
                } else {
                    $unknownbits[] = $bit;
                }
            }
            if (count($widthbits)) {
                $position = join(' ', $widthbits);
            }
        }

        if (count($unknownbits)) {
            foreach ($unknownbits as $bit) {
                $bit = trim($bit);
                if ($color === self::NULL_VALUE && css_is_colour($bit)) {
                    $color = $bit;
                } else if ($repeat === self::NULL_VALUE && in_array($bit, $repeats)) {
                    $repeat = $bit;
                } else if ($attachment === self::NULL_VALUE && in_array($bit, $attachments)) {
                    $attachment = $bit;
                } else if ($bit !== '') {
                    $advanced = css_style_background_advanced::init($bit);
                    if ($important) {
                        $advanced->set_important();
                    }
                    $return[] = $advanced;
                }
            }
        }

        if ($color === self::NULL_VALUE &&
            $image === self::NULL_VALUE &&
            $repeat === self::NULL_VALUE && $attachment === self::NULL_VALUE &&
            $position === self::NULL_VALUE) {
                        return $return;
        }

        $return[] = css_style_backgroundcolor::init($color);
        $return[] = css_style_backgroundimage::init($image);
        $return[] = css_style_backgroundrepeat::init($repeat);
        $return[] = css_style_backgroundattachment::init($attachment);
        $return[] = css_style_backgroundposition::init($position);

        if ($important) {
            foreach ($return as $style) {
                $style->set_important();
            }
        }

        return $return;
    }

    
    protected static function replace_bracket_placeholders($value, array $placeholders) {
        while (preg_match('/##BRACKET-\d+##/', $value, $matches)) {
            $value = str_replace($matches[0], $placeholders[$matches[0]], $value);
        }
        return $value;
    }

    
    public static function consolidate(array $styles) {

        if (empty($styles)) {
            return $styles;
        }

        $color = null;
        $image = null;
        $repeat = null;
        $attachment = null;
        $position = null;
        $size = null;
        $origin = null;
        $clip = null;

        $someimportant = false;
        $allimportant = null;
        foreach ($styles as $style) {
            if ($style instanceof css_style_backgroundimage_advanced) {
                continue;
            }
            if ($style->is_important()) {
                $someimportant = true;
                if ($allimportant === null) {
                    $allimportant = true;
                }
            } else if ($allimportant === true) {
                $allimportant = false;
            }
        }

        
        $organisedstyles = array();
        
        $advancedstyles = array();
        
        $importantstyles = array();
        foreach ($styles as $style) {
            if ($style instanceof css_style_backgroundimage_advanced) {
                $advancedstyles[] = $style;
                continue;
            }
            if ($someimportant && !$allimportant && $style->is_important()) {
                $importantstyles[] = $style;
                continue;
            }
            $organisedstyles[$style->get_name()] = $style;
            switch ($style->get_name()) {
                case 'background-color' :
                    $color = css_style_color::shrink_value($style->get_value(false));
                    break;
                case 'background-image' :
                    $image = $style->get_value(false);
                    break;
                case 'background-repeat' :
                    $repeat = $style->get_value(false);
                    break;
                case 'background-attachment' :
                    $attachment = $style->get_value(false);
                    break;
                case 'background-position' :
                    $position = $style->get_value(false);
                    break;
                case 'background-clip' :
                    $clip = $style->get_value();
                    break;
                case 'background-origin' :
                    $origin = $style->get_value();
                    break;
                case 'background-size' :
                    $size = $style->get_value();
                    break;
            }
        }

        
        $consolidatetosingle = array();
        if (!is_null($color) && !is_null($image) && !is_null($repeat) && !is_null($attachment) && !is_null($position)) {
                        if (!$organisedstyles['background-color']->is_special_empty_value()) {
                $consolidatetosingle[] = $color;
            }
            if (!$organisedstyles['background-image']->is_special_empty_value()) {
                $consolidatetosingle[] = $image;
            }
            if (!$organisedstyles['background-repeat']->is_special_empty_value()) {
                $consolidatetosingle[] = $repeat;
            }
            if (!$organisedstyles['background-attachment']->is_special_empty_value()) {
                $consolidatetosingle[] = $attachment;
            }
            if (!$organisedstyles['background-position']->is_special_empty_value()) {
                $consolidatetosingle[] = $position;
            }
                        $color = null;
            $image = null;
            $repeat = null;
            $attachment = null;
            $position = null;
        }

        $return = array();
                if (count($consolidatetosingle) > 0) {
            $returnstyle = new css_style_background('background', join(' ', $consolidatetosingle));
            if ($allimportant) {
                $returnstyle->set_important();
            }
            $return[] = $returnstyle;
        }
        foreach ($styles as $style) {
            $value = null;
            switch ($style->get_name()) {
                case 'background-color' :
                    $value = $color;
                    break;
                case 'background-image' :
                    $value = $image;
                    break;
                case 'background-repeat' :
                    $value = $repeat;
                    break;
                case 'background-attachment' :
                    $value = $attachment;
                    break;
                case 'background-position' :
                    $value = $position;
                    break;
                case 'background-clip' :
                    $value = $clip;
                    break;
                case 'background-origin':
                    $value = $origin;
                    break;
                case 'background-size':
                    $value = $size;
                    break;
            }
            if (!is_null($value)) {
                $return[] = $style;
            }
        }
        $return = array_merge($return, $importantstyles, $advancedstyles);
        return $return;
    }
}


class css_style_background_advanced extends css_style_generic {
    
    public static function init($value) {
        $value = preg_replace('#\s+#', ' ', $value);
        return new css_style_background_advanced('background', $value);
    }

    
    public function allows_multiple_values() {
        return true;
    }
}


class css_style_backgroundcolor extends css_style_color {

    
    public static function init($value) {
        return new css_style_backgroundcolor('background-color', $value);
    }

    
    public function consolidate_to() {
        return 'background';
    }

    
    public function is_special_empty_value() {
        return ($this->value === self::NULL_VALUE);
    }

    
    public function is_valid() {
        return $this->is_special_empty_value() || parent::is_valid();
    }
}


class css_style_backgroundimage extends css_style_generic {

    
    public static function init($value) {
        if ($value !== self::NULL_VALUE && !preg_match('#^\s*(none|inherit|url\()#i', $value)) {
            return css_style_backgroundimage_advanced::init($value);
        }
        return new css_style_backgroundimage('background-image', $value);
    }

    
    public function consolidate_to() {
        return 'background';
    }

    
    public function is_special_empty_value() {
        return ($this->value === self::NULL_VALUE);
    }

    
    public function is_valid() {
        return $this->is_special_empty_value() || parent::is_valid();
    }
}


class css_style_backgroundimage_advanced extends css_style_generic {
    
    public static function init($value) {
        $value = preg_replace('#\s+#', ' ', $value);
        return new css_style_backgroundimage_advanced('background-image', $value);
    }

    
    public function allows_multiple_values() {
        return true;
    }
}


class css_style_backgroundrepeat extends css_style_generic {

    
    public static function init($value) {
        return new css_style_backgroundrepeat('background-repeat', $value);
    }

    
    public function consolidate_to() {
        return 'background';
    }

    
    public function is_special_empty_value() {
        return ($this->value === self::NULL_VALUE);
    }

    
    public function is_valid() {
        return $this->is_special_empty_value() || parent::is_valid();
    }
}


class css_style_backgroundattachment extends css_style_generic {

    
    public static function init($value) {
        return new css_style_backgroundattachment('background-attachment', $value);
    }

    
    public function consolidate_to() {
        return 'background';
    }

    
    public function is_special_empty_value() {
        return ($this->value === self::NULL_VALUE);
    }

    
    public function is_valid() {
        return $this->is_special_empty_value() || parent::is_valid();
    }
}


class css_style_backgroundposition extends css_style_generic {

    
    public static function init($value) {
        return new css_style_backgroundposition('background-position', $value);
    }

    
    public function consolidate_to() {
        return 'background';
    }

    
    public function is_special_empty_value() {
        return ($this->value === self::NULL_VALUE);
    }

    
    public function is_valid() {
        return $this->is_special_empty_value() || parent::is_valid();
    }
}


class css_style_backgroundsize extends css_style_generic {

    
    public static function init($value) {
        return new css_style_backgroundsize('background-size', $value);
    }

    
    public function consolidate_to() {
        return 'background';
    }
}


class css_style_backgroundclip extends css_style_generic {

    
    public static function init($value) {
        return new css_style_backgroundclip('background-clip', $value);
    }

    
    public function consolidate_to() {
        return 'background';
    }
}


class css_style_backgroundorigin extends css_style_generic {

    
    public static function init($value) {
        return new css_style_backgroundorigin('background-origin', $value);
    }

    
    public function consolidate_to() {
        return 'background';
    }
}


class css_style_padding extends css_style_width implements core_css_consolidatable_style {

    
    public static function init($value) {
        $important = '';
        if (strpos($value, '!important') !== false) {
            $important = ' !important';
            $value = str_replace('!important', '', $value);
        }

        $value = preg_replace('#\s+#', ' ', trim($value));
        $bits = explode(' ', $value, 4);

        $top = $right = $bottom = $left = null;
        if (count($bits) > 0) {
            $top = $right = $bottom = $left = array_shift($bits);
        }
        if (count($bits) > 0) {
            $right = $left = array_shift($bits);
        }
        if (count($bits) > 0) {
            $bottom = array_shift($bits);
        }
        if (count($bits) > 0) {
            $left = array_shift($bits);
        }
        return array(
            new css_style_paddingtop('padding-top', $top.$important),
            new css_style_paddingright('padding-right', $right.$important),
            new css_style_paddingbottom('padding-bottom', $bottom.$important),
            new css_style_paddingleft('padding-left', $left.$important)
        );
    }

    
    public static function consolidate(array $styles) {
        if (count($styles) != 4) {
            return $styles;
        }

        $someimportant = false;
        $allimportant = null;
        $notimportantequal = null;
        $firstvalue = null;
        foreach ($styles as $style) {
            if ($style->is_important()) {
                $someimportant = true;
                if ($allimportant === null) {
                    $allimportant = true;
                }
            } else {
                if ($allimportant === true) {
                    $allimportant = false;
                }
                if ($firstvalue == null) {
                    $firstvalue = $style->get_value(false);
                    $notimportantequal = true;
                } else if ($notimportantequal && $firstvalue !== $style->get_value(false)) {
                    $notimportantequal = false;
                }
            }
        }

        if ($someimportant && !$allimportant && !$notimportantequal) {
            return $styles;
        }

        if ($someimportant && !$allimportant && $notimportantequal) {
            $return = array(
                new css_style_padding('padding', $firstvalue)
            );
            foreach ($styles as $style) {
                if ($style->is_important()) {
                    $return[] = $style;
                }
            }
            return $return;
        } else {
            $top = null;
            $right = null;
            $bottom = null;
            $left = null;
            foreach ($styles as $style) {
                switch ($style->get_name()) {
                    case 'padding-top' :
                        $top = $style->get_value(false);
                        break;
                    case 'padding-right' :
                        $right = $style->get_value(false);
                        break;
                    case 'padding-bottom' :
                        $bottom = $style->get_value(false);
                        break;
                    case 'padding-left' :
                        $left = $style->get_value(false);
                        break;
                }
            }
            if ($top == $bottom && $left == $right) {
                if ($top == $left) {
                    $returnstyle = new css_style_padding('padding', $top);
                } else {
                    $returnstyle = new css_style_padding('padding', "{$top} {$left}");
                }
            } else if ($left == $right) {
                $returnstyle = new css_style_padding('padding', "{$top} {$right} {$bottom}");
            } else {
                $returnstyle = new css_style_padding('padding', "{$top} {$right} {$bottom} {$left}");
            }
            if ($allimportant) {
                $returnstyle->set_important();
            }
            return array($returnstyle);
        }
    }
}


class css_style_paddingtop extends css_style_padding {

    
    public static function init($value) {
        return new css_style_paddingtop('padding-top', $value);
    }

    
    public function consolidate_to() {
        return 'padding';
    }
}


class css_style_paddingright extends css_style_padding {

    
    public static function init($value) {
        return new css_style_paddingright('padding-right', $value);
    }

    
    public function consolidate_to() {
        return 'padding';
    }
}


class css_style_paddingbottom extends css_style_padding {

    
    public static function init($value) {
        return new css_style_paddingbottom('padding-bottom', $value);
    }

    
    public function consolidate_to() {
        return 'padding';
    }
}


class css_style_paddingleft extends css_style_padding {

    
    public static function init($value) {
        return new css_style_paddingleft('padding-left', $value);
    }

    
    public function consolidate_to() {
        return 'padding';
    }
}


class css_style_cursor extends css_style_generic {
    
    public static function init($value) {
        return new css_style_cursor('cursor', $value);
    }
    
    protected function clean_value($value) {
                $allowed = array('auto', 'crosshair', 'default', 'e-resize', 'help', 'move', 'n-resize', 'ne-resize', 'nw-resize',
                         'pointer', 'progress', 's-resize', 'se-resize', 'sw-resize', 'text', 'w-resize', 'wait', 'inherit');
                if (!in_array($value, $allowed) && !preg_match('#\.[a-zA-Z0-9_\-]{1,5}$#', $value)) {
            $this->set_error('Invalid or unexpected cursor value specified: '.$value);
        }
        return trim($value);
    }
}


class css_style_verticalalign extends css_style_generic {
    
    public static function init($value) {
        return new css_style_verticalalign('vertical-align', $value);
    }
    
    protected function clean_value($value) {
        $allowed = array('baseline', 'sub', 'super', 'top', 'text-top', 'middle', 'bottom', 'text-bottom', 'inherit');
        if (!css_is_width($value) && !in_array($value, $allowed)) {
            $this->set_error('Invalid vertical-align value specified: '.$value);
        }
        return trim($value);
    }
}


class css_style_float extends css_style_generic {
    
    public static function init($value) {
        return new css_style_float('float', $value);
    }
    
    protected function clean_value($value) {
        $allowed = array('left', 'right', 'none', 'inherit');
        if (!css_is_width($value) && !in_array($value, $allowed)) {
            $this->set_error('Invalid float value specified: '.$value);
        }
        return trim($value);
    }
}
