<?php



defined('MOODLE_INTERNAL') || die();

require_once($CFG->libdir.'/outputcomponents.php');
require_once($CFG->libdir.'/outputactions.php');
require_once($CFG->libdir.'/outputfactories.php');
require_once($CFG->libdir.'/outputrenderers.php');
require_once($CFG->libdir.'/outputrequirementslib.php');


function theme_reset_all_caches() {
    global $CFG, $PAGE;

    $next = time();
    if (isset($CFG->themerev) and $next <= $CFG->themerev and $CFG->themerev - $next < 60*60) {
                                $next = $CFG->themerev+1;
    }

    set_config('themerev', $next); 
    if (!empty($CFG->themedesignermode)) {
        $cache = cache::make_from_params(cache_store::MODE_APPLICATION, 'core', 'themedesigner');
        $cache->purge();
    }

    if ($PAGE) {
        $PAGE->reload_theme();
    }
}


function theme_set_designer_mod($state) {
    set_config('themedesignermode', (int)!empty($state));
        theme_reset_all_caches();
}


function theme_get_revision() {
    global $CFG;

    if (empty($CFG->themedesignermode)) {
        if (empty($CFG->themerev)) {
            return -1;
        } else {
            return $CFG->themerev;
        }

    } else {
        return -1;
    }
}


function theme_is_device_locked($device) {
    global $CFG;
    $themeconfigname = core_useragent::get_device_type_cfg_var_name($device);
    return isset($CFG->config_php_settings[$themeconfigname]);
}


function theme_get_locked_theme_for_device($device) {
    global $CFG;

    if (!theme_is_device_locked($device)) {
        return null;
    }

    $themeconfigname = core_useragent::get_device_type_cfg_var_name($device);
    return $CFG->config_php_settings[$themeconfigname];
}


class theme_config {

    
    const DEFAULT_THEME = 'clean';

    
    public $parents;

    
    public $sheets = array();

    
    public $parents_exclude_sheets = null;

    
    public $plugins_exclude_sheets = null;

    
    public $editor_sheets = array();

    
    public $javascripts = array();

    
    public $javascripts_footer = array();

    
    public $parents_exclude_javascripts = null;

    
    public $layouts = array();

    
    public $rendererfactory = 'standard_renderer_factory';

    
    public $csspostprocess = null;

    
    public $rarrow = null;

    
    public $larrow = null;

    
    public $uarrow = null;

    
    public $enablecourseajax = true;

    
    public $doctype = 'html5';

    
    
    public $name;

    
    public $dir;

    
    public $setting = null;

    
    public $enable_dock = false;

    
    public $hidefromselector = false;

    
    public $yuicssmodules = array('cssreset', 'cssfonts', 'cssgrids', 'cssbase');

    
    public $blockrtlmanipulations = array();

    
    protected $rf = null;

    
    protected $parent_configs = array();

    
    public $supportscssoptimisation = true;

    
    private $usesvg = null;

    
    public $lessfile = false;

    
    public $extralesscallback = null;

    
    public $lessvariablescallback = null;

    
    public $blockrendermethod = null;

    
    public static function load($themename) {
        global $CFG;

                try {
            $settings = get_config('theme_'.$themename);
        } catch (dml_exception $e) {
                        $settings = new stdClass();
        }

        if ($config = theme_config::find_theme_config($themename, $settings)) {
            return new theme_config($config);

        } else if ($themename == theme_config::DEFAULT_THEME) {
            throw new coding_exception('Default theme '.theme_config::DEFAULT_THEME.' not available or broken!');

        } else if ($config = theme_config::find_theme_config($CFG->theme, $settings)) {
            return new theme_config($config);

        } else {
                        return new theme_config(theme_config::find_theme_config(theme_config::DEFAULT_THEME, $settings));
        }
    }

    
    public static function diagnose($themename) {
                return array();
    }

    
    private function __construct($config) {
        global $CFG; 
        $this->settings = $config->settings;
        $this->name     = $config->name;
        $this->dir      = $config->dir;

        if ($this->name != 'base') {
            $baseconfig = theme_config::find_theme_config('base', $this->settings);
        } else {
            $baseconfig = $config;
        }

        $configurable = array(
            'parents', 'sheets', 'parents_exclude_sheets', 'plugins_exclude_sheets',
            'javascripts', 'javascripts_footer', 'parents_exclude_javascripts',
            'layouts', 'enable_dock', 'enablecourseajax', 'supportscssoptimisation',
            'rendererfactory', 'csspostprocess', 'editor_sheets', 'rarrow', 'larrow', 'uarrow',
            'hidefromselector', 'doctype', 'yuicssmodules', 'blockrtlmanipulations',
            'lessfile', 'extralesscallback', 'lessvariablescallback', 'blockrendermethod');

        foreach ($config as $key=>$value) {
            if (in_array($key, $configurable)) {
                $this->$key = $value;
            }
        }

                foreach ($this->parents as $parent) {
            if ($parent == 'base') {
                $parent_config = $baseconfig;
            } else if (!$parent_config = theme_config::find_theme_config($parent, $this->settings)) {
                                continue;
            }
            $libfile = $parent_config->dir.'/lib.php';
            if (is_readable($libfile)) {
                                include_once($libfile);
            }
            $renderersfile = $parent_config->dir.'/renderers.php';
            if (is_readable($renderersfile)) {
                                include_once($renderersfile);
            }
            $this->parent_configs[$parent] = $parent_config;
        }
        $libfile = $this->dir.'/lib.php';
        if (is_readable($libfile)) {
                        include_once($libfile);
        }
        $rendererfile = $this->dir.'/renderers.php';
        if (is_readable($rendererfile)) {
                        include_once($rendererfile);
        } else {
                        if (is_readable($this->dir.'/renderer.php')) {
                debugging('Developer hint: '.$this->dir.'/renderer.php should be renamed to ' . $this->dir."/renderers.php.
                    See: http://docs.moodle.org/dev/Output_renderers#Theme_renderers.", DEBUG_DEVELOPER);
            }
        }

                foreach ($baseconfig->layouts as $layout=>$value) {
            if (!isset($this->layouts[$layout])) {
                foreach ($this->parent_configs as $parent_config) {
                    if (isset($parent_config->layouts[$layout])) {
                        $this->layouts[$layout] = $parent_config->layouts[$layout];
                        continue 2;
                    }
                }
                $this->layouts[$layout] = $value;
            }
        }

                $this->check_theme_arrows();
    }

    
    public function init_page(moodle_page $page) {
        $themeinitfunction = 'theme_'.$this->name.'_page_init';
        if (function_exists($themeinitfunction)) {
            $themeinitfunction($page);
        }
    }

    
    private function check_theme_arrows() {
        if (!isset($this->rarrow) and !isset($this->larrow)) {
                                    $this->rarrow = '&#x25BA;';
            $this->larrow = '&#x25C4;';
            $this->uarrow = '&#x25B2;';
            if (empty($_SERVER['HTTP_USER_AGENT'])) {
                $uagent = '';
            } else {
                $uagent = $_SERVER['HTTP_USER_AGENT'];
            }
            if (false !== strpos($uagent, 'Opera')
                || false !== strpos($uagent, 'Mac')) {
                                                $this->rarrow = '&#x25B6;&#xFE0E;';
                $this->larrow = '&#x25C0;&#xFE0E;';
            }
            elseif ((false !== strpos($uagent, 'Konqueror'))
                || (false !== strpos($uagent, 'Android')))  {
                                                $this->rarrow = '&rarr;';
                $this->larrow = '&larr;';
                $this->uarrow = '&uarr;';
            }
            elseif (isset($_SERVER['HTTP_ACCEPT_CHARSET'])
                && false === stripos($_SERVER['HTTP_ACCEPT_CHARSET'], 'utf-8')) {
                                                $this->rarrow = '&gt;';
                $this->larrow = '&lt;';
                $this->uarrow = '^';
            }

                        if (right_to_left()) {
                $t = $this->rarrow;
                $this->rarrow = $this->larrow;
                $this->larrow = $t;
            }
        }
    }

    
    public function renderer_prefixes() {
        global $CFG; 
        $prefixes = array('theme_'.$this->name);

        foreach ($this->parent_configs as $parent) {
            $prefixes[] = 'theme_'.$parent->name;
        }

        return $prefixes;
    }

    
    public function editor_css_url($encoded=true) {
        global $CFG;
        $rev = theme_get_revision();
        if ($rev > -1) {
            $url = new moodle_url("$CFG->httpswwwroot/theme/styles.php");
            if (!empty($CFG->slasharguments)) {
                $url->set_slashargument('/'.$this->name.'/'.$rev.'/editor', 'noparam', true);
            } else {
                $url->params(array('theme'=>$this->name,'rev'=>$rev, 'type'=>'editor'));
            }
        } else {
            $params = array('theme'=>$this->name, 'type'=>'editor');
            $url = new moodle_url($CFG->httpswwwroot.'/theme/styles_debug.php', $params);
        }
        return $url;
    }

    
    public function editor_css_files() {
        $files = array();

                $plugins = core_component::get_plugin_list('editor');
        foreach ($plugins as $plugin=>$fulldir) {
            $sheetfile = "$fulldir/editor_styles.css";
            if (is_readable($sheetfile)) {
                $files['plugin_'.$plugin] = $sheetfile;
            }
        }
                foreach (array_reverse($this->parent_configs) as $parent_config) {
            if (empty($parent_config->editor_sheets)) {
                continue;
            }
            foreach ($parent_config->editor_sheets as $sheet) {
                $sheetfile = "$parent_config->dir/style/$sheet.css";
                if (is_readable($sheetfile)) {
                    $files['parent_'.$parent_config->name.'_'.$sheet] = $sheetfile;
                }
            }
        }
                if (!empty($this->editor_sheets)) {
            foreach ($this->editor_sheets as $sheet) {
                $sheetfile = "$this->dir/style/$sheet.css";
                if (is_readable($sheetfile)) {
                    $files['theme_'.$sheet] = $sheetfile;
                }
            }
        }

        return $files;
    }

    
    public function css_urls(moodle_page $page) {
        global $CFG;

        $rev = theme_get_revision();

        $urls = array();

        $svg = $this->use_svg_icons();
        $separate = (core_useragent::is_ie() && !core_useragent::check_ie_version('10'));

        if ($rev > -1) {
            $url = new moodle_url("$CFG->httpswwwroot/theme/styles.php");
            if (!empty($CFG->slasharguments)) {
                $slashargs = '';
                if (!$svg) {
                                                            $slashargs .= '/_s'.$slashargs;
                }
                $slashargs .= '/'.$this->name.'/'.$rev.'/all';
                if ($separate) {
                    $slashargs .= '/chunk0';
                }
                $url->set_slashargument($slashargs, 'noparam', true);
            } else {
                $params = array('theme' => $this->name,'rev' => $rev, 'type' => 'all');
                if (!$svg) {
                                                            $params['svg'] = '0';
                }
                if ($separate) {
                    $params['chunk'] = '0';
                }
                $url->params($params);
            }
            $urls[] = $url;

        } else {
            $baseurl = new moodle_url($CFG->httpswwwroot.'/theme/styles_debug.php');

            $css = $this->get_css_files(true);
            if (!$svg) {
                                                $baseurl->param('svg', '0');
            }
            if ($separate) {
                                $baseurl->param('chunk', '0');
            }
            if (core_useragent::is_ie()) {
                                $urls[] = new moodle_url($baseurl, array('theme'=>$this->name, 'type'=>'ie', 'subtype'=>'plugins'));
                foreach ($css['parents'] as $parent=>$sheets) {
                                        $urls[] = new moodle_url($baseurl, array('theme'=>$this->name,'type'=>'ie', 'subtype'=>'parents', 'sheet'=>$parent));
                }
                if (!empty($this->lessfile)) {
                                        $urls[] = new moodle_url($baseurl, array('theme' => $this->name, 'type' => 'less'));
                }
                $urls[] = new moodle_url($baseurl, array('theme'=>$this->name, 'type'=>'ie', 'subtype'=>'theme'));

            } else {
                foreach ($css['plugins'] as $plugin=>$unused) {
                    $urls[] = new moodle_url($baseurl, array('theme'=>$this->name,'type'=>'plugin', 'subtype'=>$plugin));
                }
                foreach ($css['parents'] as $parent=>$sheets) {
                    foreach ($sheets as $sheet=>$unused2) {
                        $urls[] = new moodle_url($baseurl, array('theme'=>$this->name,'type'=>'parent', 'subtype'=>$parent, 'sheet'=>$sheet));
                    }
                }
                foreach ($css['theme'] as $sheet => $filename) {
                    if ($sheet === $this->lessfile) {
                                                $urls[] = new moodle_url($baseurl, array('theme' => $this->name, 'type' => 'less'));
                    } else {
                                                $urls[] = new moodle_url($baseurl, array('sheet'=>$sheet, 'theme'=>$this->name, 'type'=>'theme'));
                    }
                }
            }
        }

        return $urls;
    }

    
    public function get_css_content() {
        global $CFG;
        require_once($CFG->dirroot.'/lib/csslib.php');

        $csscontent = '';
        foreach ($this->get_css_files(false) as $type => $value) {
            foreach ($value as $identifier => $val) {
                if (is_array($val)) {
                    foreach ($val as $v) {
                        $csscontent .= file_get_contents($v) . "\n";
                    }
                } else {
                    if ($type === 'theme' && $identifier === $this->lessfile) {
                                                $csscontent .= $this->get_css_content_from_less(false);
                    } else {
                        $csscontent .= file_get_contents($val) . "\n";
                    }
                }
            }
        }
        $csscontent = $this->post_process($csscontent);

        if (!empty($CFG->enablecssoptimiser) && $this->supportscssoptimisation) {
                                                                        $optimiser = new css_optimiser();
            $csscontent = $optimiser->process($csscontent);

        } else {
            $csscontent = core_minify::css($csscontent);
        }

        return $csscontent;
    }

    
    public function get_css_content_debug($type, $subtype, $sheet) {
        global $CFG;
        require_once($CFG->dirroot.'/lib/csslib.php');

                if ($type === 'less') {
            $csscontent = $this->get_css_content_from_less(true);
            if ($csscontent !== false) {
                return $csscontent;
            }
            return '';
        }

        $optimiser = null;
        if (!empty($CFG->enablecssoptimiser) && $this->supportscssoptimisation) {
                                                                        $optimiser = new css_optimiser();
        }

        $cssfiles = array();
        $css = $this->get_css_files(true);

        if ($type === 'ie') {
                        if ($subtype === 'plugins') {
                $cssfiles = $css['plugins'];

            } else if ($subtype === 'parents') {
                if (empty($sheet)) {
                                    } else {
                                        foreach ($css[$subtype][$sheet] as $parent => $css) {
                        $cssfiles[] = $css;
                    }
                }
            } else if ($subtype === 'theme') {
                $cssfiles = $css['theme'];
                foreach ($cssfiles as $key => $value) {
                    if ($this->lessfile && $key === $this->lessfile) {
                                                                        unset($cssfiles[$key]);
                    }
                }
            }

        } else if ($type === 'plugin') {
            if (isset($css['plugins'][$subtype])) {
                $cssfiles[] = $css['plugins'][$subtype];
            }

        } else if ($type === 'parent') {
            if (isset($css['parents'][$subtype][$sheet])) {
                $cssfiles[] = $css['parents'][$subtype][$sheet];
            }

        } else if ($type === 'theme') {
            if (isset($css['theme'][$sheet])) {
                $cssfiles[] = $css['theme'][$sheet];
            }
        }

        $csscontent = '';
        foreach ($cssfiles as $file) {
            $contents = file_get_contents($file);
            $contents = $this->post_process($contents);
            $comment = "/** Path: $type $subtype $sheet.' **/\n";
            $stats = '';
            if ($optimiser) {
                $contents = $optimiser->process($contents);
                if (!empty($CFG->cssoptimiserstats)) {
                    $stats = $optimiser->output_stats_css();
                }
            }
            $csscontent .= $comment.$stats.$contents."\n\n";
        }

        return $csscontent;
    }

    
    public function get_css_content_editor() {
                $cssfiles = $this->editor_css_files();
        $css = '';
        foreach ($cssfiles as $file) {
            $css .= file_get_contents($file)."\n";
        }
        return $this->post_process($css);
    }

    
    protected function get_css_files($themedesigner) {
        global $CFG;

        $cache = null;
        $cachekey = 'cssfiles';
        if ($themedesigner) {
            require_once($CFG->dirroot.'/lib/csslib.php');
                                    $cache = cache::make_from_params(cache_store::MODE_APPLICATION, 'core', 'themedesigner', array('theme' => $this->name));
            if ($files = $cache->get($cachekey)) {
                if ($files['created'] > time() - THEME_DESIGNER_CACHE_LIFETIME) {
                    unset($files['created']);
                    return $files;
                }
            }
        }

        $cssfiles = array('plugins'=>array(), 'parents'=>array(), 'theme'=>array());

                $excludes = $this->resolve_excludes('plugins_exclude_sheets');
        if ($excludes !== true) {
            foreach (core_component::get_plugin_types() as $type=>$unused) {
                if ($type === 'theme' || (!empty($excludes[$type]) and $excludes[$type] === true)) {
                    continue;
                }
                $plugins = core_component::get_plugin_list($type);
                foreach ($plugins as $plugin=>$fulldir) {
                    if (!empty($excludes[$type]) and is_array($excludes[$type])
                            and in_array($plugin, $excludes[$type])) {
                        continue;
                    }

                                        $sheetfile = "$fulldir/styles.css";
                    if (is_readable($sheetfile)) {
                        $cssfiles['plugins'][$type.'_'.$plugin] = $sheetfile;
                    }

                                        $candidates = array();
                    foreach (array_reverse($this->parent_configs) as $parent_config) {
                        $candidates[] = $parent_config->name;
                    }
                    $candidates[] = $this->name;

                                        foreach ($candidates as $candidate) {
                        $sheetthemefile = "$fulldir/styles_{$candidate}.css";
                        if (is_readable($sheetthemefile)) {
                            $cssfiles['plugins'][$type.'_'.$plugin.'_'.$candidate] = $sheetthemefile;
                        }
                    }
                }
            }
        }

                $excludes = $this->resolve_excludes('parents_exclude_sheets');
        if ($excludes !== true) {
            foreach (array_reverse($this->parent_configs) as $parent_config) {                 $parent = $parent_config->name;
                if (empty($parent_config->sheets) || (!empty($excludes[$parent]) and $excludes[$parent] === true)) {
                    continue;
                }
                foreach ($parent_config->sheets as $sheet) {
                    if (!empty($excludes[$parent]) && is_array($excludes[$parent])
                            && in_array($sheet, $excludes[$parent])) {
                        continue;
                    }

                                        $sheetfile = "$parent_config->dir/style/$sheet.css";
                    if (is_readable($sheetfile)) {
                        $cssfiles['parents'][$parent][$sheet] = $sheetfile;
                    }
                }
            }
        }

                                        if (!empty($this->lessfile)) {
            $sheetfile = "{$this->dir}/less/{$this->lessfile}.less";
            if (is_readable($sheetfile)) {
                $cssfiles['theme'][$this->lessfile] = $sheetfile;
            }
        }
        if (is_array($this->sheets)) {
            foreach ($this->sheets as $sheet) {
                $sheetfile = "$this->dir/style/$sheet.css";
                if (is_readable($sheetfile) && !isset($cssfiles['theme'][$sheet])) {
                    $cssfiles['theme'][$sheet] = $sheetfile;
                }
            }
        }

        if ($cache) {
            $files = $cssfiles;
            $files['created'] = time();
            $cache->set($cachekey, $files);
        }
        return $cssfiles;
    }

    
    protected function get_css_content_from_less($themedesigner) {
        global $CFG;

        $lessfile = $this->lessfile;
        if (!$lessfile || !is_readable($this->dir . '/less/' . $lessfile . '.less')) {
            throw new coding_exception('The theme did not define a LESS file, or it is not readable.');
        }

                raise_memory_limit(MEMORY_EXTRA);

                $files = $this->get_css_files($themedesigner);

                $themelessfile = $files['theme'][$lessfile];

                $options = array(
                        'import_dirs' => array(dirname($themelessfile) => '/'),
                        'cache_method' => false,
                        'relativeUrls' => false,
        );

        if ($themedesigner) {
                        $options['sourceMap'] = true;
            $options['sourceMapBasepath'] = $CFG->dirroot;
            $options['sourceMapRootpath'] = $CFG->wwwroot;
        }

                $compiler = new core_lessc($options);

        try {
            $compiler->parse_file_content($themelessfile);

                        $compiler->parse($this->get_extra_less_code());
            $compiler->ModifyVars($this->get_less_variables());

                        $compiled = $compiler->getCss();

                        $compiled = $this->post_process($compiled);
        } catch (Less_Exception_Parser $e) {
            $compiled = false;
            debugging('Error while compiling LESS ' . $lessfile . ' file: ' . $e->getMessage(), DEBUG_DEVELOPER);
        }

                $compiler = null;
        unset($compiler);

        return $compiled;
    }

    
    protected function get_less_variables() {
        $variables = array();

                $candidates = array();
        foreach ($this->parent_configs as $parent_config) {
            if (!isset($parent_config->lessvariablescallback)) {
                continue;
            }
            $candidates[] = $parent_config->lessvariablescallback;
        }
        $candidates[] = $this->lessvariablescallback;

                foreach ($candidates as $function) {
            if (function_exists($function)) {
                $vars = $function($this);
                if (!is_array($vars)) {
                    debugging('Callback ' . $function . ' did not return an array() as expected', DEBUG_DEVELOPER);
                    continue;
                }
                $variables = array_merge($variables, $vars);
            }
        }

        return $variables;
    }

    
    protected function get_extra_less_code() {
        $content = '';

                $candidates = array();
        foreach ($this->parent_configs as $parent_config) {
            if (!isset($parent_config->extralesscallback)) {
                continue;
            }
            $candidates[] = $parent_config->extralesscallback;
        }
        $candidates[] = $this->extralesscallback;

                foreach ($candidates as $function) {
            if (function_exists($function)) {
                $content .= "\n/** Extra LESS from $function **/\n" . $function($this) . "\n";
            }
        }

        return $content;
    }

    
    public function javascript_url($inhead) {
        global $CFG;

        $rev = theme_get_revision();
        $params = array('theme'=>$this->name,'rev'=>$rev);
        $params['type'] = $inhead ? 'head' : 'footer';

                if (count($this->javascript_files($params['type'])) === 0) {
            return null;
        }

        if (!empty($CFG->slasharguments) and $rev > 0) {
            $url = new moodle_url("$CFG->httpswwwroot/theme/javascript.php");
            $url->set_slashargument('/'.$this->name.'/'.$rev.'/'.$params['type'], 'noparam', true);
            return $url;
        } else {
            return new moodle_url($CFG->httpswwwroot.'/theme/javascript.php', $params);
        }
    }

    
    public function javascript_files($type) {
        if ($type === 'footer') {
            $type = 'javascripts_footer';
        } else {
            $type = 'javascripts';
        }

        $js = array();
                $excludes = $this->resolve_excludes('parents_exclude_javascripts');
        if ($excludes !== true) {
            foreach (array_reverse($this->parent_configs) as $parent_config) {                 $parent = $parent_config->name;
                if (empty($parent_config->$type)) {
                    continue;
                }
                if (!empty($excludes[$parent]) and $excludes[$parent] === true) {
                    continue;
                }
                foreach ($parent_config->$type as $javascript) {
                    if (!empty($excludes[$parent]) and is_array($excludes[$parent])
                        and in_array($javascript, $excludes[$parent])) {
                        continue;
                    }
                    $javascriptfile = "$parent_config->dir/javascript/$javascript.js";
                    if (is_readable($javascriptfile)) {
                        $js[] = $javascriptfile;
                    }
                }
            }
        }

                if (is_array($this->$type)) {
            foreach ($this->$type as $javascript) {
                $javascriptfile = "$this->dir/javascript/$javascript.js";
                if (is_readable($javascriptfile)) {
                    $js[] = $javascriptfile;
                }
            }
        }
        return $js;
    }

    
    protected function resolve_excludes($variable, $default = null) {
        $setting = $default;
        if (is_array($this->{$variable}) or $this->{$variable} === true) {
            $setting = $this->{$variable};
        } else {
            foreach ($this->parent_configs as $parent_config) {                 if (!isset($parent_config->{$variable})) {
                    continue;
                }
                if (is_array($parent_config->{$variable}) or $parent_config->{$variable} === true) {
                    $setting = $parent_config->{$variable};
                    break;
                }
            }
        }
        return $setting;
    }

    
    public function javascript_content($type) {
        $jsfiles = $this->javascript_files($type);
        $js = '';
        foreach ($jsfiles as $jsfile) {
            $js .= file_get_contents($jsfile)."\n";
        }
        return $js;
    }

    
    public function post_process($css) {
                if (preg_match_all('/\[\[pix:([a-z0-9_]+\|)?([^\]]+)\]\]/', $css, $matches, PREG_SET_ORDER)) {
            $replaced = array();
            foreach ($matches as $match) {
                if (isset($replaced[$match[0]])) {
                    continue;
                }
                $replaced[$match[0]] = true;
                $imagename = $match[2];
                $component = rtrim($match[1], '|');
                $imageurl = $this->pix_url($imagename, $component)->out(false);
                                 $imageurl = preg_replace('|^http.?://[^/]+|', '', $imageurl);
                $css = str_replace($match[0], $imageurl, $css);
            }
        }

                if (preg_match_all('/\[\[font:([a-z0-9_]+\|)?([^\]]+)\]\]/', $css, $matches, PREG_SET_ORDER)) {
            $replaced = array();
            foreach ($matches as $match) {
                if (isset($replaced[$match[0]])) {
                    continue;
                }
                $replaced[$match[0]] = true;
                $fontname = $match[2];
                $component = rtrim($match[1], '|');
                $fonturl = $this->font_url($fontname, $component)->out(false);
                                $fonturl = preg_replace('|^http.?://[^/]+|', '', $fonturl);
                $css = str_replace($match[0], $fonturl, $css);
            }
        }

                $csspostprocess = $this->csspostprocess;
        if (function_exists($csspostprocess)) {
            $css = $csspostprocess($css, $this);
        }

        return $css;
    }

    
    public function pix_url($imagename, $component) {
        global $CFG;

        $params = array('theme'=>$this->name);
        $svg = $this->use_svg_icons();

        if (empty($component) or $component === 'moodle' or $component === 'core') {
            $params['component'] = 'core';
        } else {
            $params['component'] = $component;
        }

        $rev = theme_get_revision();
        if ($rev != -1) {
            $params['rev'] = $rev;
        }

        $params['image'] = $imagename;

        $url = new moodle_url("$CFG->httpswwwroot/theme/image.php");
        if (!empty($CFG->slasharguments) and $rev > 0) {
            $path = '/'.$params['theme'].'/'.$params['component'].'/'.$params['rev'].'/'.$params['image'];
            if (!$svg) {
                                                $path = '/_s'.$path;
            }
            $url->set_slashargument($path, 'noparam', true);
        } else {
            if (!$svg) {
                                                $params['svg'] = '0';
            }
            $url->params($params);
        }

        return $url;
    }

    
    public function font_url($font, $component) {
        global $CFG;

        $params = array('theme'=>$this->name);

        if (empty($component) or $component === 'moodle' or $component === 'core') {
            $params['component'] = 'core';
        } else {
            $params['component'] = $component;
        }

        $rev = theme_get_revision();
        if ($rev != -1) {
            $params['rev'] = $rev;
        }

        $params['font'] = $font;

        $url = new moodle_url("$CFG->httpswwwroot/theme/font.php");
        if (!empty($CFG->slasharguments) and $rev > 0) {
            $path = '/'.$params['theme'].'/'.$params['component'].'/'.$params['rev'].'/'.$params['font'];
            $url->set_slashargument($path, 'noparam', true);
        } else {
            $url->params($params);
        }

        return $url;
    }

    
    public function setting_file_url($setting, $filearea) {
        global $CFG;

        if (empty($this->settings->$setting)) {
            return null;
        }

        $component = 'theme_'.$this->name;
        $itemid = theme_get_revision();
        $filepath = $this->settings->$setting;
        $syscontext = context_system::instance();

        $url = moodle_url::make_file_url("$CFG->wwwroot/pluginfile.php", "/$syscontext->id/$component/$filearea/$itemid".$filepath);

                
        $url = preg_replace('|^https?://|i', '//', $url->out(false));

        return $url;
    }

    
    public function setting_file_serve($filearea, $args, $forcedownload, $options) {
        global $CFG;
        require_once("$CFG->libdir/filelib.php");

        $syscontext = context_system::instance();
        $component = 'theme_'.$this->name;

        $revision = array_shift($args);
        if ($revision < 0) {
            $lifetime = 0;
        } else {
            $lifetime = 60*60*24*60;
                        if (!array_key_exists('cacheability', $options)) {
                $options['cacheability'] = 'public';
            }
        }

        $fs = get_file_storage();
        $relativepath = implode('/', $args);

        $fullpath = "/{$syscontext->id}/{$component}/{$filearea}/0/{$relativepath}";
        $fullpath = rtrim($fullpath, '/');
        if ($file = $fs->get_file_by_hash(sha1($fullpath))) {
            send_stored_file($file, $lifetime, 0, $forcedownload, $options);
            return true;
        } else {
            send_file_not_found();
        }
    }

    
    public function resolve_image_location($image, $component, $svg = false) {
        global $CFG;

        if (!is_bool($svg)) {
                        $svg = $this->use_svg_icons();
        }

        if ($component === 'moodle' or $component === 'core' or empty($component)) {
            if ($imagefile = $this->image_exists("$this->dir/pix_core/$image", $svg)) {
                return $imagefile;
            }
            foreach (array_reverse($this->parent_configs) as $parent_config) {                 if ($imagefile = $this->image_exists("$parent_config->dir/pix_core/$image", $svg)) {
                    return $imagefile;
                }
            }
            if ($imagefile = $this->image_exists("$CFG->dataroot/pix/$image", $svg)) {
                return $imagefile;
            }
            if ($imagefile = $this->image_exists("$CFG->dirroot/pix/$image", $svg)) {
                return $imagefile;
            }
            return null;

        } else if ($component === 'theme') {             if ($image === 'favicon') {
                return "$this->dir/pix/favicon.ico";
            }
            if ($imagefile = $this->image_exists("$this->dir/pix/$image", $svg)) {
                return $imagefile;
            }
            foreach (array_reverse($this->parent_configs) as $parent_config) {                 if ($imagefile = $this->image_exists("$parent_config->dir/pix/$image", $svg)) {
                    return $imagefile;
                }
            }
            return null;

        } else {
            if (strpos($component, '_') === false) {
                $component = 'mod_'.$component;
            }
            list($type, $plugin) = explode('_', $component, 2);

            if ($imagefile = $this->image_exists("$this->dir/pix_plugins/$type/$plugin/$image", $svg)) {
                return $imagefile;
            }
            foreach (array_reverse($this->parent_configs) as $parent_config) {                 if ($imagefile = $this->image_exists("$parent_config->dir/pix_plugins/$type/$plugin/$image", $svg)) {
                    return $imagefile;
                }
            }
            if ($imagefile = $this->image_exists("$CFG->dataroot/pix_plugins/$type/$plugin/$image", $svg)) {
                return $imagefile;
            }
            $dir = core_component::get_plugin_directory($type, $plugin);
            if ($imagefile = $this->image_exists("$dir/pix/$image", $svg)) {
                return $imagefile;
            }
            return null;
        }
    }

    
    public function resolve_font_location($font, $component) {
        global $CFG;

        if ($component === 'moodle' or $component === 'core' or empty($component)) {
            if (file_exists("$this->dir/fonts_core/$font")) {
                return "$this->dir/fonts_core/$font";
            }
            foreach (array_reverse($this->parent_configs) as $parent_config) {                 if (file_exists("$parent_config->dir/fonts_core/$font")) {
                    return "$parent_config->dir/fonts_core/$font";
                }
            }
            if (file_exists("$CFG->dataroot/fonts/$font")) {
                return "$CFG->dataroot/fonts/$font";
            }
            if (file_exists("$CFG->dirroot/lib/fonts/$font")) {
                return "$CFG->dirroot/lib/fonts/$font";
            }
            return null;

        } else if ($component === 'theme') {             if (file_exists("$this->dir/fonts/$font")) {
                return "$this->dir/fonts/$font";
            }
            foreach (array_reverse($this->parent_configs) as $parent_config) {                 if (file_exists("$parent_config->dir/fonts/$font")) {
                    return "$parent_config->dir/fonts/$font";
                }
            }
            return null;

        } else {
            if (strpos($component, '_') === false) {
                $component = 'mod_'.$component;
            }
            list($type, $plugin) = explode('_', $component, 2);

            if (file_exists("$this->dir/fonts_plugins/$type/$plugin/$font")) {
                return "$this->dir/fonts_plugins/$type/$plugin/$font";
            }
            foreach (array_reverse($this->parent_configs) as $parent_config) {                 if (file_exists("$parent_config->dir/fonts_plugins/$type/$plugin/$font")) {
                    return "$parent_config->dir/fonts_plugins/$type/$plugin/$font";
                }
            }
            if (file_exists("$CFG->dataroot/fonts_plugins/$type/$plugin/$font")) {
                return "$CFG->dataroot/fonts_plugins/$type/$plugin/$font";
            }
            $dir = core_component::get_plugin_directory($type, $plugin);
            if (file_exists("$dir/fonts/$font")) {
                return "$dir/fonts/$font";
            }
            return null;
        }
    }

    
    public function use_svg_icons() {
        global $CFG;
        if ($this->usesvg === null) {

            if (!isset($CFG->svgicons)) {
                $this->usesvg = core_useragent::supports_svg();
            } else {
                                $this->usesvg = (bool)$CFG->svgicons;
            }
        }
        return $this->usesvg;
    }

    
    public function force_svg_use($setting) {
        $this->usesvg = (bool)$setting;
    }

    
    private static function image_exists($filepath, $svg = false) {
        if ($svg && file_exists("$filepath.svg")) {
            return "$filepath.svg";
        } else  if (file_exists("$filepath.png")) {
            return "$filepath.png";
        } else if (file_exists("$filepath.gif")) {
            return "$filepath.gif";
        } else  if (file_exists("$filepath.jpg")) {
            return "$filepath.jpg";
        } else  if (file_exists("$filepath.jpeg")) {
            return "$filepath.jpeg";
        } else {
            return false;
        }
    }

    
    private static function find_theme_config($themename, $settings, $parentscheck = true) {
                
        if (!$dir = theme_config::find_theme_location($themename)) {
            return null;
        }

        $THEME = new stdClass();
        $THEME->name     = $themename;
        $THEME->dir      = $dir;
        $THEME->settings = $settings;

        global $CFG;         include("$THEME->dir/config.php");

                if (!is_array($THEME->parents)) {
                        return null;
        } else {
                        if ($parentscheck) {
                                foreach ($THEME->parents as $parent) {
                    $parentconfig = theme_config::find_theme_config($parent, $settings, false);
                    if (empty($parentconfig)) {
                        return null;
                    }
                }
            }
        }

        return $THEME;
    }

    
    private static function find_theme_location($themename) {
        global $CFG;

        if (file_exists("$CFG->dirroot/theme/$themename/config.php")) {
            $dir = "$CFG->dirroot/theme/$themename";

        } else if (!empty($CFG->themedir) and file_exists("$CFG->themedir/$themename/config.php")) {
            $dir = "$CFG->themedir/$themename";

        } else {
            return null;
        }

        if (file_exists("$dir/styles.php")) {
                        return null;
        }

        return $dir;
    }

    
    public function get_renderer(moodle_page $page, $component, $subtype = null, $target = null) {
        if (is_null($this->rf)) {
            $classname = $this->rendererfactory;
            $this->rf = new $classname($this);
        }

        return $this->rf->get_renderer($page, $component, $subtype, $target);
    }

    
    protected function layout_info_for_page($pagelayout) {
        if (array_key_exists($pagelayout, $this->layouts)) {
            return $this->layouts[$pagelayout];
        } else {
            debugging('Invalid page layout specified: ' . $pagelayout);
            return $this->layouts['standard'];
        }
    }

    
    public function layout_file($pagelayout) {
        global $CFG;

        $layoutinfo = $this->layout_info_for_page($pagelayout);
        $layoutfile = $layoutinfo['file'];

        if (array_key_exists('theme', $layoutinfo)) {
            $themes = array($layoutinfo['theme']);
        } else {
            $themes = array_merge(array($this->name),$this->parents);
        }

        foreach ($themes as $theme) {
            if ($dir = $this->find_theme_location($theme)) {
                $path = "$dir/layout/$layoutfile";

                                if (is_readable($path)) {
                    return $path;
                }
            }
        }

        debugging('Can not find layout file for: ' . $pagelayout);
                return "$CFG->dirroot/theme/base/layout/general.php";
    }

    
    public function pagelayout_options($pagelayout) {
        $info = $this->layout_info_for_page($pagelayout);
        if (!empty($info['options'])) {
            return $info['options'];
        }
        return array();
    }

    
    public function setup_blocks($pagelayout, $blockmanager) {
        $layoutinfo = $this->layout_info_for_page($pagelayout);
        if (!empty($layoutinfo['regions'])) {
            $blockmanager->add_regions($layoutinfo['regions'], false);
            $blockmanager->set_default_region($layoutinfo['defaultregion']);
        }
    }

    
    protected function get_region_name($region, $theme) {
        $regionstring = get_string('region-' . $region, 'theme_' . $theme);
                if (substr($regionstring, 0, 1) != '[') {
            return $regionstring;
        }

                        foreach ($this->parents as $parentthemename) {
            $regionstring = get_string('region-' . $region, 'theme_' . $parentthemename);
            if (substr($regionstring, 0, 1) != '[') {
                return $regionstring;
            }
        }

                return get_string('region-' . $region, 'theme_base');
    }

    
    public function get_all_block_regions() {
        $regions = array();
        foreach ($this->layouts as $layoutinfo) {
            foreach ($layoutinfo['regions'] as $region) {
                $regions[$region] = $this->get_region_name($region, $this->name);
            }
        }
        return $regions;
    }

    
    public function get_theme_name() {
        return get_string('pluginname', 'theme_'.$this->name);
    }

    
    public function get_block_render_method() {
        if ($this->blockrendermethod) {
                        return $this->blockrendermethod;
        }
                foreach ($this->parent_configs as $config) {
            if (isset($config->blockrendermethod)) {
                return $config->blockrendermethod;
            }
        }
                return 'blocks';
    }
}


class xhtml_container_stack {

    
    protected $opencontainers = array();

    
    protected $log = array();

    
    protected $isdebugging;

    
    public function __construct() {
        global $CFG;
        $this->isdebugging = $CFG->debugdeveloper;
    }

    
    public function push($type, $closehtml) {
        $container = new stdClass;
        $container->type = $type;
        $container->closehtml = $closehtml;
        if ($this->isdebugging) {
            $this->log('Open', $type);
        }
        array_push($this->opencontainers, $container);
    }

    
    public function pop($type) {
        if (empty($this->opencontainers)) {
            debugging('<p>There are no more open containers. This suggests there is a nesting problem.</p>' .
                    $this->output_log(), DEBUG_DEVELOPER);
            return;
        }

        $container = array_pop($this->opencontainers);
        if ($container->type != $type) {
            debugging('<p>The type of container to be closed (' . $container->type .
                    ') does not match the type of the next open container (' . $type .
                    '). This suggests there is a nesting problem.</p>' .
                    $this->output_log(), DEBUG_DEVELOPER);
        }
        if ($this->isdebugging) {
            $this->log('Close', $type);
        }
        return $container->closehtml;
    }

    
    public function pop_all_but_last($shouldbenone = false) {
        if ($shouldbenone && count($this->opencontainers) != 1) {
            debugging('<p>Some HTML tags were opened in the body of the page but not closed.</p>' .
                    $this->output_log(), DEBUG_DEVELOPER);
        }
        $output = '';
        while (count($this->opencontainers) > 1) {
            $container = array_pop($this->opencontainers);
            $output .= $container->closehtml;
        }
        return $output;
    }

    
    public function discard() {
        $this->opencontainers = null;
    }

    
    protected function log($action, $type) {
        $this->log[] = '<li>' . $action . ' ' . $type . ' at:' .
                format_backtrace(debug_backtrace()) . '</li>';
    }

    
    protected function output_log() {
        return '<ul>' . implode("\n", $this->log) . '</ul>';
    }
}
