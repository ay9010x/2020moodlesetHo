<?php



defined('MOODLE_INTERNAL') || die();



class core_string_manager_standard implements core_string_manager {
    
    protected $otherroot;
    
    protected $localroot;
    
    protected $cache;
    
    protected $countgetstring = 0;
    
    protected $translist;
    
    protected $menucache;
    
    protected $cacheddeprecated;

    
    public function __construct($otherroot, $localroot, $translist) {
        $this->otherroot    = $otherroot;
        $this->localroot    = $localroot;
        if ($translist) {
            $this->translist = array_combine($translist, $translist);
        } else {
            $this->translist = array();
        }

        if ($this->get_revision() > 0) {
                        $this->cache = cache::make('core', 'string');
            $this->menucache = cache::make('core', 'langmenu');
        } else {
                        $options = array(
                'simplekeys' => true,
                'simpledata' => true
            );
            $this->cache = cache::make_from_params(cache_store::MODE_REQUEST, 'core', 'string', array(), $options);
            $this->menucache = cache::make_from_params(cache_store::MODE_REQUEST, 'core', 'langmenu', array(), $options);
        }
    }

    
    public function get_language_dependencies($lang) {
        return $this->populate_parent_languages($lang);
    }

    
    public function load_component_strings($component, $lang, $disablecache = false, $disablelocal = false) {
        global $CFG;

        list($plugintype, $pluginname) = core_component::normalize_component($component);
        if ($plugintype === 'core' and is_null($pluginname)) {
            $component = 'core';
        } else {
            $component = $plugintype . '_' . $pluginname;
        }

        $cachekey = $lang.'_'.$component.'_'.$this->get_key_suffix();

        $cachedstring = $this->cache->get($cachekey);
        if (!$disablecache and !$disablelocal) {
            if ($cachedstring !== false) {
                return $cachedstring;
            }
        }

                if ($plugintype === 'core') {
            $file = $pluginname;
            if ($file === null) {
                $file = 'moodle';
            }
            $string = array();
                        if (!file_exists("$CFG->dirroot/lang/en/$file.php")) {
                return array();
            }
            include("$CFG->dirroot/lang/en/$file.php");
            $enstring = $string;

                        if (!$disablelocal and file_exists("$this->localroot/en_local/$file.php")) {
                include("$this->localroot/en_local/$file.php");
            }
                        $deps = $this->get_language_dependencies($lang);
            foreach ($deps as $dep) {
                                if (file_exists("$this->otherroot/$dep/$file.php")) {
                    include("$this->otherroot/$dep/$file.php");
                }
                if (!$disablelocal and file_exists("$this->localroot/{$dep}_local/$file.php")) {
                    include("$this->localroot/{$dep}_local/$file.php");
                }
            }

        } else {
            if (!$location = core_component::get_plugin_directory($plugintype, $pluginname) or !is_dir($location)) {
                return array();
            }
            if ($plugintype === 'mod') {
                                $file = $pluginname;
            } else {
                $file = $plugintype . '_' . $pluginname;
            }
            $string = array();
                        if (!file_exists("$location/lang/en/$file.php")) {
                                return array();
            }
            include("$location/lang/en/$file.php");
            $enstring = $string;
                        if (!$disablelocal and file_exists("$this->localroot/en_local/$file.php")) {
                include("$this->localroot/en_local/$file.php");
            }

                        $deps = $this->get_language_dependencies($lang);
            foreach ($deps as $dep) {
                                if (file_exists("$location/lang/$dep/$file.php")) {
                    include("$location/lang/$dep/$file.php");
                }
                                if (file_exists("$this->otherroot/$dep/$file.php")) {
                    include("$this->otherroot/$dep/$file.php");
                }
                                if (!$disablelocal and file_exists("$this->localroot/{$dep}_local/$file.php")) {
                    include("$this->localroot/{$dep}_local/$file.php");
                }
            }
        }

                $string = array_intersect_key($string, $enstring);

        if (!$disablelocal) {
                                    if ($cachedstring === false) {
                $this->cache->set($cachekey, $string);
            }
        }
        return $string;
    }

    
    protected function load_deprecated_strings() {
        global $CFG;

        if ($this->cacheddeprecated !== null) {
            return $this->cacheddeprecated;
        }

        $this->cacheddeprecated = array();
        $content = '';
        $filename = $CFG->dirroot . '/lang/en/deprecated.txt';
        if (file_exists($filename)) {
            $content .= file_get_contents($filename);
        }
        foreach (core_component::get_plugin_types() as $plugintype => $plugintypedir) {
            foreach (core_component::get_plugin_list($plugintype) as $pluginname => $plugindir) {
                $filename = $plugindir.'/lang/en/deprecated.txt';
                if (file_exists($filename)) {
                    $content .= "\n". file_get_contents($filename);
                }
            }
        }

        $strings = preg_split('/\s*\n\s*/', $content, -1, PREG_SPLIT_NO_EMPTY);
        $this->cacheddeprecated = array_flip($strings);

        return $this->cacheddeprecated;
    }

    
    public function string_deprecated($identifier, $component) {
        $deprecated = $this->load_deprecated_strings();
        list($plugintype, $pluginname) = core_component::normalize_component($component);
        $normcomponent = $pluginname ? ($plugintype . '_' . $pluginname) : $plugintype;
        return isset($deprecated[$identifier . ',' . $normcomponent]);
    }

    
    public function string_exists($identifier, $component) {
        $lang = current_language();
        $string = $this->load_component_strings($component, $lang);
        return isset($string[$identifier]);
    }

    
    public function get_string($identifier, $component = '', $a = null, $lang = null) {
        global $CFG;

        $this->countgetstring++;
                        static $langconfigstrs = array(
            'strftimedate' => 1,
            'strftimedatefullshort' => 1,
            'strftimedateshort' => 1,
            'strftimedatetime' => 1,
            'strftimedatetimeshort' => 1,
            'strftimedaydate' => 1,
            'strftimedaydatetime' => 1,
            'strftimedayshort' => 1,
            'strftimedaytime' => 1,
            'strftimemonthyear' => 1,
            'strftimerecent' => 1,
            'strftimerecentfull' => 1,
            'strftimetime' => 1);

        if (empty($component)) {
            if (isset($langconfigstrs[$identifier])) {
                $component = 'langconfig';
            } else {
                $component = 'moodle';
            }
        }

        if ($lang === null) {
            $lang = current_language();
        }

        $string = $this->load_component_strings($component, $lang);

        if (!isset($string[$identifier])) {
            if ($component === 'pix' or $component === 'core_pix') {
                                return '';
            }
            if ($identifier === 'parentlanguage' and ($component === 'langconfig' or $component === 'core_langconfig')) {
                                return 'en';
            }
                                    if (!isset($string[$identifier])) {
                                if ($CFG->debugdeveloper) {
                    list($plugintype, $pluginname) = core_component::normalize_component($component);
                    if ($plugintype === 'core') {
                        $file = "lang/en/{$component}.php";
                    } else if ($plugintype == 'mod') {
                        $file = "mod/{$pluginname}/lang/en/{$pluginname}.php";
                    } else {
                        $path = core_component::get_plugin_directory($plugintype, $pluginname);
                        $file = "{$path}/lang/en/{$plugintype}_{$pluginname}.php";
                    }
                    debugging("Invalid get_string() identifier: '{$identifier}' or component '{$component}'. " .
                    "Perhaps you are missing \$string['{$identifier}'] = ''; in {$file}?", DEBUG_DEVELOPER);
                }
                return "[[$identifier]]";
            }
        }

        $string = $string[$identifier];

        if ($a !== null) {
                        if (is_array($a) or (is_object($a) && !($a instanceof lang_string))) {
                $a = (array)$a;
                $search = array();
                $replace = array();
                foreach ($a as $key => $value) {
                    if (is_int($key)) {
                                                continue;
                    }
                    if (is_array($value) or (is_object($value) && !($value instanceof lang_string))) {
                                                continue;
                    }
                    $search[]  = '{$a->'.$key.'}';
                    $replace[] = (string)$value;
                }
                if ($search) {
                    $string = str_replace($search, $replace, $string);
                }
            } else {
                $string = str_replace('{$a}', (string)$a, $string);
            }
        }

        if ($CFG->debugdeveloper) {
                        if ($this->string_deprecated($identifier, $component)) {
                list($plugintype, $pluginname) = core_component::normalize_component($component);
                $normcomponent = $pluginname ? ($plugintype . '_' . $pluginname) : $plugintype;
                debugging("String [{$identifier},{$normcomponent}] is deprecated. ".
                    'Either you should no longer be using that string, or the string has been incorrectly deprecated, in which case you should report this as a bug. '.
                    'Please refer to https://docs.moodle.org/dev/String_deprecation', DEBUG_DEVELOPER);
            }
        }

        return $string;
    }

    
    public function get_performance_summary() {
        return array(array(
            'langcountgetstring' => $this->countgetstring,
        ), array(
            'langcountgetstring' => 'get_string calls',
        ));
    }

    
    public function get_list_of_countries($returnall = false, $lang = null) {
        global $CFG;

        if ($lang === null) {
            $lang = current_language();
        }

        $countries = $this->load_component_strings('core_countries', $lang);
        core_collator::asort($countries);
        if (!$returnall and !empty($CFG->allcountrycodes)) {
            $enabled = explode(',', $CFG->allcountrycodes);
            $return = array();
            foreach ($enabled as $c) {
                if (isset($countries[$c])) {
                    $return[$c] = $countries[$c];
                }
            }
            return $return;
        }

        return $countries;
    }

    
    public function get_list_of_languages($lang = null, $standard = 'iso6391') {
        if ($lang === null) {
            $lang = current_language();
        }

        if ($standard === 'iso6392') {
            $langs = $this->load_component_strings('core_iso6392', $lang);
            ksort($langs);
            return $langs;

        } else if ($standard === 'iso6391') {
            $langs2 = $this->load_component_strings('core_iso6392', $lang);
            static $mapping = array('aar' => 'aa', 'abk' => 'ab', 'afr' => 'af', 'aka' => 'ak', 'sqi' => 'sq', 'amh' => 'am', 'ara' => 'ar', 'arg' => 'an', 'hye' => 'hy',
                'asm' => 'as', 'ava' => 'av', 'ave' => 'ae', 'aym' => 'ay', 'aze' => 'az', 'bak' => 'ba', 'bam' => 'bm', 'eus' => 'eu', 'bel' => 'be', 'ben' => 'bn', 'bih' => 'bh',
                'bis' => 'bi', 'bos' => 'bs', 'bre' => 'br', 'bul' => 'bg', 'mya' => 'my', 'cat' => 'ca', 'cha' => 'ch', 'che' => 'ce', 'zho' => 'zh', 'chu' => 'cu', 'chv' => 'cv',
                'cor' => 'kw', 'cos' => 'co', 'cre' => 'cr', 'ces' => 'cs', 'dan' => 'da', 'div' => 'dv', 'nld' => 'nl', 'dzo' => 'dz', 'eng' => 'en', 'epo' => 'eo', 'est' => 'et',
                'ewe' => 'ee', 'fao' => 'fo', 'fij' => 'fj', 'fin' => 'fi', 'fra' => 'fr', 'fry' => 'fy', 'ful' => 'ff', 'kat' => 'ka', 'deu' => 'de', 'gla' => 'gd', 'gle' => 'ga',
                'glg' => 'gl', 'glv' => 'gv', 'ell' => 'el', 'grn' => 'gn', 'guj' => 'gu', 'hat' => 'ht', 'hau' => 'ha', 'heb' => 'he', 'her' => 'hz', 'hin' => 'hi', 'hmo' => 'ho',
                'hrv' => 'hr', 'hun' => 'hu', 'ibo' => 'ig', 'isl' => 'is', 'ido' => 'io', 'iii' => 'ii', 'iku' => 'iu', 'ile' => 'ie', 'ina' => 'ia', 'ind' => 'id', 'ipk' => 'ik',
                'ita' => 'it', 'jav' => 'jv', 'jpn' => 'ja', 'kal' => 'kl', 'kan' => 'kn', 'kas' => 'ks', 'kau' => 'kr', 'kaz' => 'kk', 'khm' => 'km', 'kik' => 'ki', 'kin' => 'rw',
                'kir' => 'ky', 'kom' => 'kv', 'kon' => 'kg', 'kor' => 'ko', 'kua' => 'kj', 'kur' => 'ku', 'lao' => 'lo', 'lat' => 'la', 'lav' => 'lv', 'lim' => 'li', 'lin' => 'ln',
                'lit' => 'lt', 'ltz' => 'lb', 'lub' => 'lu', 'lug' => 'lg', 'mkd' => 'mk', 'mah' => 'mh', 'mal' => 'ml', 'mri' => 'mi', 'mar' => 'mr', 'msa' => 'ms', 'mlg' => 'mg',
                'mlt' => 'mt', 'mon' => 'mn', 'nau' => 'na', 'nav' => 'nv', 'nbl' => 'nr', 'nde' => 'nd', 'ndo' => 'ng', 'nep' => 'ne', 'nno' => 'nn', 'nob' => 'nb', 'nor' => 'no',
                'nya' => 'ny', 'oci' => 'oc', 'oji' => 'oj', 'ori' => 'or', 'orm' => 'om', 'oss' => 'os', 'pan' => 'pa', 'fas' => 'fa', 'pli' => 'pi', 'pol' => 'pl', 'por' => 'pt',
                'pus' => 'ps', 'que' => 'qu', 'roh' => 'rm', 'ron' => 'ro', 'run' => 'rn', 'rus' => 'ru', 'sag' => 'sg', 'san' => 'sa', 'sin' => 'si', 'slk' => 'sk', 'slv' => 'sl',
                'sme' => 'se', 'smo' => 'sm', 'sna' => 'sn', 'snd' => 'sd', 'som' => 'so', 'sot' => 'st', 'spa' => 'es', 'srd' => 'sc', 'srp' => 'sr', 'ssw' => 'ss', 'sun' => 'su',
                'swa' => 'sw', 'swe' => 'sv', 'tah' => 'ty', 'tam' => 'ta', 'tat' => 'tt', 'tel' => 'te', 'tgk' => 'tg', 'tgl' => 'tl', 'tha' => 'th', 'bod' => 'bo', 'tir' => 'ti',
                'ton' => 'to', 'tsn' => 'tn', 'tso' => 'ts', 'tuk' => 'tk', 'tur' => 'tr', 'twi' => 'tw', 'uig' => 'ug', 'ukr' => 'uk', 'urd' => 'ur', 'uzb' => 'uz', 'ven' => 've',
                'vie' => 'vi', 'vol' => 'vo', 'cym' => 'cy', 'wln' => 'wa', 'wol' => 'wo', 'xho' => 'xh', 'yid' => 'yi', 'yor' => 'yo', 'zha' => 'za', 'zul' => 'zu');
            $langs1 = array();
            foreach ($mapping as $c2 => $c1) {
                $langs1[$c1] = $langs2[$c2];
            }
            ksort($langs1);
            return $langs1;

        } else {
            debugging('Unsupported $standard parameter in get_list_of_languages() method: '.$standard);
        }

        return array();
    }

    
    public function translation_exists($lang, $includeall = true) {
        $translations = $this->get_list_of_translations($includeall);
        return isset($translations[$lang]);
    }

    
    public function get_list_of_translations($returnall = false) {
        global $CFG;

        $languages = array();

        $cachekey = 'list_'.$this->get_key_suffix();
        $cachedlist = $this->menucache->get($cachekey);
        if ($cachedlist !== false) {
                        if ($returnall or empty($this->translist)) {
                return $cachedlist;
            }
                        foreach ($cachedlist as $langcode => $langname) {
                if (isset($this->translist[$langcode])) {
                    $languages[$langcode] = $langname;
                }
            }
            return $languages;
        }

                $langdirs = get_list_of_plugins('', 'en', $this->otherroot);
        $langdirs["$CFG->dirroot/lang/en"] = 'en';

                                $lrm = json_decode('"\u200E"');

                foreach ($langdirs as $lang) {
            if (strrpos($lang, '_local') !== false) {
                                continue;
            }
            if (strrpos($lang, '_utf8') !== false) {
                                continue;
            }
            if ($lang !== clean_param($lang, PARAM_SAFEDIR)) {
                                continue;
            }
            $string = $this->load_component_strings('langconfig', $lang);
            if (!empty($string['thislanguage'])) {
                $languages[$lang] = $string['thislanguage'].' '.$lrm.'('. $lang .')'.$lrm;
            }
        }

        core_collator::asort($languages);

                $this->menucache->set($cachekey, $languages);

        if ($returnall or empty($this->translist)) {
            return $languages;
        }

        $cachedlist = $languages;

                $languages = array();
        foreach ($cachedlist as $langcode => $langname) {
            if (isset($this->translist[$langcode])) {
                $languages[$langcode] = $langname;
            }
        }

        return $languages;
    }

    
    public function get_list_of_currencies($lang = null) {
        if ($lang === null) {
            $lang = current_language();
        }

        $currencies = $this->load_component_strings('core_currencies', $lang);
        asort($currencies);

        return $currencies;
    }

    
    public function reset_caches($phpunitreset = false) {
                $this->cache->purge();
        $this->menucache->purge();

        if (!$phpunitreset) {
                        $langrev = get_config('core', 'langrev');
            $next = time();
            if ($langrev !== false and $next <= $langrev and $langrev - $next < 60*60) {
                                                                $next = $langrev+1;
            }
            set_config('langrev', $next);
        }

                if (function_exists('opcache_reset')) {
            opcache_reset();
        }
    }

    
    protected function get_key_suffix() {
        $rev = $this->get_revision();
        if ($rev < 0) {
                        $rev = 0;
        }

        return $rev;
    }

    
    public function get_revision() {
        global $CFG;
        if (empty($CFG->langstringcache)) {
            return -1;
        }
        if (isset($CFG->langrev)) {
            return (int)$CFG->langrev;
        } else {
            return -1;
        }
    }

    
    protected function populate_parent_languages($lang, array $stack = array()) {

                if ($lang === 'en') {
            return $stack;
        }

                if (in_array($lang, $stack)) {
            return $stack;
        }

                if (!file_exists("$this->otherroot/$lang/langconfig.php")) {
            return $stack;
        }
        $string = array();
        include("$this->otherroot/$lang/langconfig.php");

        if (empty($string['parentlanguage']) or $string['parentlanguage'] === 'en') {
            return array_merge(array($lang), $stack);

        }

        $parentlang = $string['parentlanguage'];
        return $this->populate_parent_languages($parentlang, array_merge(array($lang), $stack));
    }
}
