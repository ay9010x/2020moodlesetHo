<?php



defined('MOODLE_INTERNAL') || die();


class page_requirements_manager {

    
    protected $stringsforjs = array();

    
    protected $stringsforjs_as = array();

    
    protected $jsinitvariables = array('head'=>array(), 'footer'=>array());

    
    protected $jsincludes = array('head'=>array(), 'footer'=>array());

    
    protected $amdjscode = array('');

    
    protected $jscalls = array('normal'=>array(), 'ondomready'=>array());

    
    protected $skiplinks = array();

    
    protected $jsinitcode = array();

    
    protected $cssthemeurls = array();

    
    protected $cssurls = array();

    
    protected $eventhandlers = array();

    
    protected $extramodules = array();

    
    protected $onetimeitemsoutput = array();

    
    protected $headdone = false;

    
    protected $topofbodydone = false;

    
    protected $yui3loader;

    
    protected $YUI_config;

    
    protected $yuicssmodules = array();

    
    protected $M_cfg;

    
    protected $jqueryplugins = array();

    
    protected $jquerypluginoverrides = array();

    
    public function __construct() {
        global $CFG;

                $sep = empty($CFG->yuislasharguments) ? '?' : '/';

        $this->yui3loader = new stdClass();
        $this->YUI_config = new YUI_config();

        if (is_https()) {
                                    $CFG->useexternalyui = 0;
        }

                $this->yui3loader->local_base = $CFG->httpswwwroot . '/lib/yuilib/'. $CFG->yui3version . '/';
        $this->yui3loader->local_comboBase = $CFG->httpswwwroot . '/theme/yui_combo.php'.$sep;

        if (!empty($CFG->useexternalyui)) {
            $this->yui3loader->base = 'http://yui.yahooapis.com/' . $CFG->yui3version . '/';
            $this->yui3loader->comboBase = 'http://yui.yahooapis.com/combo?';
        } else {
            $this->yui3loader->base = $this->yui3loader->local_base;
            $this->yui3loader->comboBase = $this->yui3loader->local_comboBase;
        }

                $this->yui3loader->combine = !empty($CFG->yuicomboloading);

        $jsrev = $this->get_jsrev();

                $this->YUI_config->base         = $this->yui3loader->base;
        $this->YUI_config->comboBase    = $this->yui3loader->comboBase;
        $this->YUI_config->combine      = $this->yui3loader->combine;

                        if (!empty($CFG->yuipatchedmodules) && !empty($CFG->yuipatchlevel)) {
            $this->YUI_config->define_patched_core_modules($this->yui3loader->local_comboBase,
                    $CFG->yui3version,
                    $CFG->yuipatchlevel,
                    $CFG->yuipatchedmodules);
        }

        $configname = $this->YUI_config->set_config_source('lib/yui/config/yui2.js');
        $this->YUI_config->add_group('yui2', array(
                        'base' => $CFG->httpswwwroot . '/lib/yuilib/2in3/' . $CFG->yui2version . '/build/',
            'comboBase' => $CFG->httpswwwroot . '/theme/yui_combo.php'.$sep,
            'combine' => $this->yui3loader->combine,
            'ext' => false,
            'root' => '2in3/' . $CFG->yui2version .'/build/',
            'patterns' => array(
                'yui2-' => array(
                    'group' => 'yui2',
                    'configFn' => $configname,
                )
            )
        ));
        $configname = $this->YUI_config->set_config_source('lib/yui/config/moodle.js');
        $this->YUI_config->add_group('moodle', array(
            'name' => 'moodle',
            'base' => $CFG->httpswwwroot . '/theme/yui_combo.php' . $sep . 'm/' . $jsrev . '/',
            'combine' => $this->yui3loader->combine,
            'comboBase' => $CFG->httpswwwroot . '/theme/yui_combo.php'.$sep,
            'ext' => false,
            'root' => 'm/'.$jsrev.'/',             'patterns' => array(
                'moodle-' => array(
                    'group' => 'moodle',
                    'configFn' => $configname,
                )
            )
        ));

        $this->YUI_config->add_group('gallery', array(
            'name' => 'gallery',
            'base' => $CFG->httpswwwroot . '/lib/yuilib/gallery/',
            'combine' => $this->yui3loader->combine,
            'comboBase' => $CFG->httpswwwroot . '/theme/yui_combo.php' . $sep,
            'ext' => false,
            'root' => 'gallery/' . $jsrev . '/',
            'patterns' => array(
                'gallery-' => array(
                    'group' => 'gallery',
                )
            )
        ));

                if ($CFG->debugdeveloper) {
                                                            $this->YUI_config->filter = 'RAW';
            $this->YUI_config->groups['moodle']['filter'] = 'DEBUG';

                        $this->yui3loader->filter = $this->YUI_config->filter;
            $this->YUI_config->debug = true;
        } else {
            $this->yui3loader->filter = null;
            $this->YUI_config->groups['moodle']['filter'] = null;
            $this->YUI_config->debug = false;
        }

                if (!empty($CFG->yuilogexclude) && is_array($CFG->yuilogexclude)) {
            $this->YUI_config->logExclude = $CFG->yuilogexclude;
        }
        if (!empty($CFG->yuiloginclude) && is_array($CFG->yuiloginclude)) {
            $this->YUI_config->logInclude = $CFG->yuiloginclude;
        }
        if (!empty($CFG->yuiloglevel)) {
            $this->YUI_config->logLevel = $CFG->yuiloglevel;
        }

                $this->YUI_config->add_moodle_metadata();

                $this->js_module($this->find_module('core_filepicker'));
        $this->js_module($this->find_module('core_comment'));
    }

    
    public function get_config_for_javascript(moodle_page $page, renderer_base $renderer) {
        global $CFG;

        if (empty($this->M_cfg)) {
                                    
            $this->M_cfg = array(
                'wwwroot'             => $CFG->httpswwwroot,                 'sesskey'             => sesskey(),
                'loadingicon'         => $renderer->pix_url('i/loading_small', 'moodle')->out(false),
                'themerev'            => theme_get_revision(),
                'slasharguments'      => (int)(!empty($CFG->slasharguments)),
                'theme'               => $page->theme->name,
                'jsrev'               => $this->get_jsrev(),
                'admin'               => $CFG->admin,
                'svgicons'            => $page->theme->use_svg_icons()
            );
            if ($CFG->debugdeveloper) {
                $this->M_cfg['developerdebug'] = true;
            }
            if (defined('BEHAT_SITE_RUNNING')) {
                $this->M_cfg['behatsiterunning'] = true;
            }

        }
        return $this->M_cfg;
    }

    
    protected function init_requirements_data(moodle_page $page, core_renderer $renderer) {
        global $CFG;

                $this->get_config_for_javascript($page, $renderer);

                $this->skip_link_to('maincontent', get_string('tocontent', 'access'));

                $this->string_for_js('confirmation', 'admin');
        $this->string_for_js('cancel', 'moodle');
        $this->string_for_js('yes', 'moodle');

                if ($page->pagelayout === 'frametop') {
            $this->js_init_call('M.util.init_frametop');
        }

                if ($page->user_is_editing()) {
            $params = array(
                'courseid' => $page->course->id,
                'pagetype' => $page->pagetype,
                'pagelayout' => $page->pagelayout,
                'subpage' => $page->subpage,
                'regions' => $page->blocks->get_regions(),
                'contextid' => $page->context->id,
            );
            if (!empty($page->cm->id)) {
                $params['cmid'] = $page->cm->id;
            }
                        $this->strings_for_js(array('movecontent',
                                        'tocontent',
                                        'emptydragdropregion'),
                                  'moodle');
            $page->requires->yui_module('moodle-core-blocks', 'M.core_blocks.init_dragdrop', array($params), null, true);
        }

                $page->requires->set_yuicssmodules($page->theme->yuicssmodules);
    }

    
    protected function get_jsrev() {
        global $CFG;

        if (empty($CFG->cachejs)) {
            $jsrev = -1;
        } else if (empty($CFG->jsrev)) {
            $jsrev = 1;
        } else {
            $jsrev = $CFG->jsrev;
        }

        return $jsrev;
    }

    
    public function js($url, $inhead = false) {
        $url = $this->js_fix_url($url);
        $where = $inhead ? 'head' : 'footer';
        $this->jsincludes[$where][$url->out()] = $url;
    }

    
    public function jquery() {
        $this->jquery_plugin('jquery');
    }

    
    public function jquery_plugin($plugin, $component = 'core') {
        global $CFG;

        if ($this->headdone) {
            debugging('Can not add jQuery plugins after starting page output!');
            return false;
        }

        if ($component !== 'core' and in_array($plugin, array('jquery', 'ui', 'ui-css', 'migrate'))) {
            debugging("jQuery plugin '$plugin' is included in Moodle core, other components can not use the same name.", DEBUG_DEVELOPER);
            $component = 'core';
        } else if ($component !== 'core' and strpos($component, '_') === false) {
                        $component = 'mod_' . $component;
        }

        if (empty($this->jqueryplugins) and ($component !== 'core' or $plugin !== 'jquery')) {
                                    $this->jquery_plugin('jquery', 'core');
        }

        if (isset($this->jqueryplugins[$plugin])) {
                        return true;
        }

        $componentdir = core_component::get_component_directory($component);
        if (!file_exists($componentdir) or !file_exists("$componentdir/jquery/plugins.php")) {
            debugging("Can not load jQuery plugin '$plugin', missing plugins.php in component '$component'.", DEBUG_DEVELOPER);
            return false;
        }

        $plugins = array();
        require("$componentdir/jquery/plugins.php");

        if (!isset($plugins[$plugin])) {
            debugging("jQuery plugin '$plugin' can not be found in component '$component'.", DEBUG_DEVELOPER);
            return false;
        }

        $this->jqueryplugins[$plugin] = new stdClass();
        $this->jqueryplugins[$plugin]->plugin    = $plugin;
        $this->jqueryplugins[$plugin]->component = $component;
        $this->jqueryplugins[$plugin]->urls      = array();

        foreach ($plugins[$plugin]['files'] as $file) {
            if ($CFG->debugdeveloper) {
                if (!file_exists("$componentdir/jquery/$file")) {
                    debugging("Invalid file '$file' specified in jQuery plugin '$plugin' in component '$component'");
                    continue;
                }
                $file = str_replace('.min.css', '.css', $file);
                $file = str_replace('.min.js', '.js', $file);
            }
            if (!file_exists("$componentdir/jquery/$file")) {
                debugging("Invalid file '$file' specified in jQuery plugin '$plugin' in component '$component'");
                continue;
            }
            if (!empty($CFG->slasharguments)) {
                $url = new moodle_url("$CFG->httpswwwroot/theme/jquery.php");
                $url->set_slashargument("/$component/$file");

            } else {
                                $path = realpath("$componentdir/jquery/$file");
                if (strpos($path, $CFG->dirroot) === 0) {
                    $url = $CFG->httpswwwroot.preg_replace('/^'.preg_quote($CFG->dirroot, '/').'/', '', $path);
                                        $url = str_replace('\\', '/', $url);
                    $url = new moodle_url($url);
                } else {
                                        debugging("Moodle jQuery integration requires 'slasharguments' setting to be enabled.");
                    continue;
                }
            }
            $this->jqueryplugins[$plugin]->urls[] = $url;
        }

        return true;
    }

    
    public function jquery_override_plugin($oldplugin, $newplugin) {
        if ($this->headdone) {
            debugging('Can not override jQuery plugins after starting page output!');
            return;
        }
        $this->jquerypluginoverrides[$oldplugin] = $newplugin;
    }

    
    protected function get_jquery_headcode() {
        if (empty($this->jqueryplugins['jquery'])) {
                                    return '';
        }

        $included = array();
        $urls = array();

        foreach ($this->jqueryplugins as $name => $unused) {
            if (isset($included[$name])) {
                continue;
            }
            if (array_key_exists($name, $this->jquerypluginoverrides)) {
                                                                $cyclic = true;
                $oldname = $name;
                for ($i=0; $i<100; $i++) {
                    $name = $this->jquerypluginoverrides[$name];
                    if (!array_key_exists($name, $this->jquerypluginoverrides)) {
                        $cyclic = false;
                        break;
                    }
                }
                if ($cyclic) {
                                        $name = $oldname;
                    debugging("Cyclic overrides detected for jQuery plugin '$name'");

                } else if (empty($name)) {
                                        continue;

                } else if (!isset($this->jqueryplugins[$name])) {
                    debugging("Unknown jQuery override plugin '$name' detected");
                    $name = $oldname;

                } else if (isset($included[$name])) {
                                        continue;
                }
            }

            $plugin = $this->jqueryplugins[$name];
            $urls = array_merge($urls, $plugin->urls);
            $included[$name] = true;
        }

        $output = '';
        $attributes = array('rel' => 'stylesheet', 'type' => 'text/css');
        foreach ($urls as $url) {
            if (preg_match('/\.js$/', $url)) {
                $output .= html_writer::script('', $url);
            } else if (preg_match('/\.css$/', $url)) {
                $attributes['href'] = $url;
                $output .= html_writer::empty_tag('link', $attributes) . "\n";
            }
        }

        return $output;
    }

    
    protected function js_fix_url($url) {
        global $CFG;

        if ($url instanceof moodle_url) {
            return $url;
        } else if (strpos($url, '/') === 0) {
                        if ($CFG->admin !== 'admin') {
                if (strpos($url, "/admin/") === 0) {
                    $url = preg_replace("|^/admin/|", "/$CFG->admin/", $url);
                }
            }
            if (debugging()) {
                                if (!file_exists($CFG->dirroot . strtok($url, '?'))) {
                    throw new coding_exception('Attempt to require a JavaScript file that does not exist.', $url);
                }
            }
            if (substr($url, -3) === '.js') {
                $jsrev = $this->get_jsrev();
                if (empty($CFG->slasharguments)) {
                    return new moodle_url($CFG->httpswwwroot.'/lib/javascript.php', array('rev'=>$jsrev, 'jsfile'=>$url));
                } else {
                    $returnurl = new moodle_url($CFG->httpswwwroot.'/lib/javascript.php');
                    $returnurl->set_slashargument('/'.$jsrev.$url);
                    return $returnurl;
                }
            } else {
                return new moodle_url($CFG->httpswwwroot.$url);
            }
        } else {
            throw new coding_exception('Invalid JS url, it has to be shortened url starting with / or moodle_url instance.', $url);
        }
    }

    
    protected function find_module($component) {
        global $CFG, $PAGE;

        $module = null;

        if (strpos($component, 'core_') === 0) {
                                    switch($component) {
                case 'core_filepicker':
                    $module = array('name'     => 'core_filepicker',
                                    'fullpath' => '/repository/filepicker.js',
                                    'requires' => array('base', 'node', 'node-event-simulate', 'json', 'async-queue', 'io-base', 'io-upload-iframe', 'io-form', 'yui2-treeview', 'panel', 'cookie', 'datatable', 'datatable-sort', 'resize-plugin', 'dd-plugin', 'escape', 'moodle-core_filepicker'),
                                    'strings'  => array(array('lastmodified', 'moodle'), array('name', 'moodle'), array('type', 'repository'), array('size', 'repository'),
                                                        array('invalidjson', 'repository'), array('error', 'moodle'), array('info', 'moodle'),
                                                        array('nofilesattached', 'repository'), array('filepicker', 'repository'), array('logout', 'repository'),
                                                        array('nofilesavailable', 'repository'), array('norepositoriesavailable', 'repository'),
                                                        array('fileexistsdialogheader', 'repository'), array('fileexistsdialog_editor', 'repository'),
                                                        array('fileexistsdialog_filemanager', 'repository'), array('renameto', 'repository'),
                                                        array('referencesexist', 'repository'), array('select', 'repository')
                                                    ));
                    break;
                case 'core_comment':
                    $module = array('name'     => 'core_comment',
                                    'fullpath' => '/comment/comment.js',
                                    'requires' => array('base', 'io-base', 'node', 'json', 'yui2-animation', 'overlay'),
                                    'strings' => array(array('confirmdeletecomments', 'admin'), array('yes', 'moodle'), array('no', 'moodle'))
                                );
                    break;
                case 'core_role':
                    $module = array('name'     => 'core_role',
                                    'fullpath' => '/admin/roles/module.js',
                                    'requires' => array('node', 'cookie'));
                    break;
                case 'core_completion':
                    $module = array('name'     => 'core_completion',
                                    'fullpath' => '/course/completion.js');
                    break;
                case 'core_message':
                    $module = array('name'     => 'core_message',
                                    'requires' => array('base', 'node', 'event', 'node-event-simulate'),
                                    'fullpath' => '/message/module.js');
                    break;
                case 'core_group':
                    $module = array('name'     => 'core_group',
                                    'fullpath' => '/group/module.js',
                                    'requires' => array('node', 'overlay', 'event-mouseenter'));
                    break;
                case 'core_question_engine':
                    $module = array('name'     => 'core_question_engine',
                                    'fullpath' => '/question/qengine.js',
                                    'requires' => array('node', 'event'));
                    break;
                case 'core_rating':
                    $module = array('name'     => 'core_rating',
                                    'fullpath' => '/rating/module.js',
                                    'requires' => array('node', 'event', 'overlay', 'io-base', 'json'));
                    break;
                case 'core_dndupload':
                    $module = array('name'     => 'core_dndupload',
                                    'fullpath' => '/lib/form/dndupload.js',
                                    'requires' => array('node', 'event', 'json', 'core_filepicker'),
                                    'strings'  => array(array('uploadformlimit', 'moodle'), array('droptoupload', 'moodle'), array('maxfilesreached', 'moodle'),
                                                        array('dndenabled_inbox', 'moodle'), array('fileexists', 'moodle'), array('maxbytesfile', 'error'),
                                                        array('sizegb', 'moodle'), array('sizemb', 'moodle'), array('sizekb', 'moodle'), array('sizeb', 'moodle'),
                                                        array('maxareabytesreached', 'moodle'), array('serverconnection', 'error'),
                                                    ));
                    break;
            }

        } else {
            if ($dir = core_component::get_component_directory($component)) {
                if (file_exists("$dir/module.js")) {
                    if (strpos($dir, $CFG->dirroot.'/') === 0) {
                        $dir = substr($dir, strlen($CFG->dirroot));
                        $module = array('name'=>$component, 'fullpath'=>"$dir/module.js", 'requires' => array());
                    }
                }
            }
        }

        return $module;
    }

    
    public function js_module($module) {
        global $CFG;

        if (empty($module)) {
            throw new coding_exception('Missing YUI3 module name or full description.');
        }

        if (is_string($module)) {
            $module = $this->find_module($module);
        }

        if (empty($module) or empty($module['name']) or empty($module['fullpath'])) {
            throw new coding_exception('Missing YUI3 module details.');
        }

        $module['fullpath'] = $this->js_fix_url($module['fullpath'])->out(false);
                if (!empty($module['strings'])) {
            foreach ($module['strings'] as $string) {
                $identifier = $string[0];
                $component = isset($string[1]) ? $string[1] : 'moodle';
                $a = isset($string[2]) ? $string[2] : null;
                $this->string_for_js($identifier, $component, $a);
            }
        }
        unset($module['strings']);

                        if (!empty($module['requires'])){
            foreach ($module['requires'] as $requirement) {
                $rmodule = $this->find_module($requirement);
                if (is_array($rmodule)) {
                    $this->js_module($rmodule);
                }
            }
        }

        if ($this->headdone) {
            $this->extramodules[$module['name']] = $module;
        } else {
            $this->YUI_config->add_module_config($module['name'], $module);
        }
    }

    
    protected function js_module_loaded($module) {
        if (is_string($module)) {
            $modulename = $module;
        } else {
            $modulename = $module['name'];
        }
        return array_key_exists($modulename, $this->YUI_config->modules) ||
               array_key_exists($modulename, $this->extramodules);
    }

    
    public function css($stylesheet) {
        global $CFG;

        if ($this->headdone) {
            throw new coding_exception('Cannot require a CSS file after &lt;head> has been printed.', $stylesheet);
        }

        if ($stylesheet instanceof moodle_url) {
                    } else if (strpos($stylesheet, '/') === 0) {
            $stylesheet = new moodle_url($CFG->httpswwwroot.$stylesheet);
        } else {
            throw new coding_exception('Invalid stylesheet parameter.', $stylesheet);
        }

        $this->cssurls[$stylesheet->out()] = $stylesheet;
    }

    
    public function css_theme(moodle_url $stylesheet) {
        $this->cssthemeurls[] = $stylesheet;
    }

    
    public function skip_link_to($target, $linktext) {
        if ($this->topofbodydone) {
            debugging('Page header already printed, can not add skip links any more, code needs to be fixed.');
            return;
        }
        $this->skiplinks[$target] = $linktext;
    }

    
    public function js_function_call($function, array $arguments = null, $ondomready = false, $delay = 0) {
        $where = $ondomready ? 'ondomready' : 'normal';
        $this->jscalls[$where][] = array($function, $arguments, $delay);
    }

    
    public function js_amd_inline($code) {
        $this->amdjscode[] = $code;
    }

    
    public function js_call_amd($fullmodule, $func, $params = array()) {
        global $CFG;

        list($component, $module) = explode('/', $fullmodule, 2);

        $component = clean_param($component, PARAM_COMPONENT);
        $module = clean_param($module, PARAM_ALPHANUMEXT);
        $func = clean_param($func, PARAM_ALPHANUMEXT);

        $jsonparams = array();
        foreach ($params as $param) {
            $jsonparams[] = json_encode($param);
        }
        $strparams = implode(', ', $jsonparams);
        if ($CFG->debugdeveloper) {
            $toomanyparamslimit = 1024;
            if (strlen($strparams) > $toomanyparamslimit) {
                debugging('Too many params passed to js_call_amd("' . $fullmodule . '", "' . $func . '")', DEBUG_DEVELOPER);
            }
        }

        $js = 'require(["' . $component . '/' . $module . '"], function(amd) { amd.' . $func . '(' . $strparams . '); });';

        $this->js_amd_inline($js);
    }

    
    public function yui_module($modules, $function, array $arguments = null, $galleryversion = null, $ondomready = false) {
        if (!is_array($modules)) {
            $modules = array($modules);
        }

        if ($galleryversion != null) {
            debugging('The galleryversion parameter to yui_module has been deprecated since Moodle 2.3.');
        }

        $jscode = 'Y.use('.join(',', array_map('json_encode', convert_to_array($modules))).',function() {'.js_writer::function_call($function, $arguments).'});';
        if ($ondomready) {
            $jscode = "Y.on('domready', function() { $jscode });";
        }
        $this->jsinitcode[] = $jscode;
    }

    
    public function set_yuicssmodules(array $modules = array()) {
        $this->yuicssmodules = $modules;
    }

    
    public function js_init_call($function, array $extraarguments = null, $ondomready = false, array $module = null) {
        $jscode = js_writer::function_call_with_Y($function, $extraarguments);
        if (!$module) {
                        if (preg_match('/M\.([a-z0-9]+_[^\.]+)/', $function, $matches)) {
                $module = $this->find_module($matches[1]);
            }
        }

        $this->js_init_code($jscode, $ondomready, $module);
    }

    
    public function js_init_code($jscode, $ondomready = false, array $module = null) {
        $jscode = trim($jscode, " ;\n"). ';';

        $uniqid = html_writer::random_id();
        $startjs = " M.util.js_pending('" . $uniqid . "');";
        $endjs = " M.util.js_complete('" . $uniqid . "');";

        if ($module) {
            $this->js_module($module);
            $modulename = $module['name'];
            $jscode = "$startjs Y.use('$modulename', function(Y) { $jscode $endjs });";
        }

        if ($ondomready) {
            $jscode = "$startjs Y.on('domready', function() { $jscode $endjs });";
        }

        $this->jsinitcode[] = $jscode;
    }

    
    public function string_for_js($identifier, $component, $a = null) {
        if (!$component) {
            throw new coding_exception('The $component parameter is required for page_requirements_manager::string_for_js().');
        }
        if (isset($this->stringsforjs_as[$component][$identifier]) and $this->stringsforjs_as[$component][$identifier] !== $a) {
            throw new coding_exception("Attempt to re-define already required string '$identifier' " .
                    "from lang file '$component' with different \$a parameter?");
        }
        if (!isset($this->stringsforjs[$component][$identifier])) {
            $this->stringsforjs[$component][$identifier] = new lang_string($identifier, $component, $a);
            $this->stringsforjs_as[$component][$identifier] = $a;
        }
    }

    
    public function strings_for_js($identifiers, $component, $a = null) {
        foreach ($identifiers as $key => $identifier) {
            if (is_array($a) && array_key_exists($key, $a)) {
                $extra = $a[$key];
            } else {
                $extra = $a;
            }
            $this->string_for_js($identifier, $component, $extra);
        }
    }

    
    public function data_for_js($variable, $data, $inhead=false) {
        $where = $inhead ? 'head' : 'footer';
        $this->jsinitvariables[$where][] = array($variable, $data);
    }

    
    public function event_handler($selector, $event, $function, array $arguments = null) {
        $this->eventhandlers[] = array('selector'=>$selector, 'event'=>$event, 'function'=>$function, 'arguments'=>$arguments);
    }

    
    protected function get_event_handler_code() {
        $output = '';
        foreach ($this->eventhandlers as $h) {
            $output .= js_writer::event_handler($h['selector'], $h['event'], $h['function'], $h['arguments']);
        }
        return $output;
    }

    
    protected function get_javascript_code($ondomready) {
        $where = $ondomready ? 'ondomready' : 'normal';
        $output = '';
        if ($this->jscalls[$where]) {
            foreach ($this->jscalls[$where] as $data) {
                $output .= js_writer::function_call($data[0], $data[1], $data[2]);
            }
            if (!empty($ondomready)) {
                $output = "    Y.on('domready', function() {\n$output\n});";
            }
        }
        return $output;
    }

    
    protected function get_javascript_init_code() {
        if (count($this->jsinitcode)) {
            return implode("\n", $this->jsinitcode) . "\n";
        }
        return '';
    }

    
    protected function get_amd_footercode() {
        global $CFG;
        $output = '';
        $jsrev = $this->get_jsrev();

        $jsloader = new moodle_url($CFG->httpswwwroot . '/lib/javascript.php');
        $jsloader->set_slashargument('/' . $jsrev . '/');
        $requirejsloader = new moodle_url($CFG->httpswwwroot . '/lib/requirejs.php');
        $requirejsloader->set_slashargument('/' . $jsrev . '/');

        $requirejsconfig = file_get_contents($CFG->dirroot . '/lib/requirejs/moodle-config.js');

                $jsextension = '.js';
        if (!empty($CFG->slasharguments)) {
            $jsextension = '';
        }

        $requirejsconfig = str_replace('[BASEURL]', $requirejsloader, $requirejsconfig);
        $requirejsconfig = str_replace('[JSURL]', $jsloader, $requirejsconfig);
        $requirejsconfig = str_replace('[JSEXT]', $jsextension, $requirejsconfig);

        $output .= html_writer::script($requirejsconfig);
        if ($CFG->debugdeveloper) {
            $output .= html_writer::script('', $this->js_fix_url('/lib/requirejs/require.js'));
        } else {
            $output .= html_writer::script('', $this->js_fix_url('/lib/requirejs/require.min.js'));
        }

                $prefix = "require(['core/first'], function() {\n";
        $suffix = "\n});";
        $output .= html_writer::script($prefix . implode(";\n", $this->amdjscode) . $suffix);
        return $output;
    }

    
    protected function get_yui3lib_headcss() {
        global $CFG;

        $yuiformat = '-min';
        if ($this->yui3loader->filter === 'RAW') {
            $yuiformat = '';
        }

        $code = '';
        if ($this->yui3loader->combine) {
            if (!empty($this->yuicssmodules)) {
                $modules = array();
                foreach ($this->yuicssmodules as $module) {
                    $modules[] = "$CFG->yui3version/$module/$module-min.css";
                }
                $code .= '<link rel="stylesheet" type="text/css" href="'.$this->yui3loader->comboBase.implode('&amp;', $modules).'" />';
            }
            $code .= '<link rel="stylesheet" type="text/css" href="'.$this->yui3loader->local_comboBase.'rollup/'.$CFG->yui3version.'/yui-moodlesimple' . $yuiformat . '.css" />';

        } else {
            if (!empty($this->yuicssmodules)) {
                foreach ($this->yuicssmodules as $module) {
                    $code .= '<link rel="stylesheet" type="text/css" href="'.$this->yui3loader->base.$module.'/'.$module.'-min.css" />';
                }
            }
            $code .= '<link rel="stylesheet" type="text/css" href="'.$this->yui3loader->local_comboBase.'rollup/'.$CFG->yui3version.'/yui-moodlesimple' . $yuiformat . '.css" />';
        }

        if ($this->yui3loader->filter === 'RAW') {
            $code = str_replace('-min.css', '.css', $code);
        } else if ($this->yui3loader->filter === 'DEBUG') {
            $code = str_replace('-min.css', '.css', $code);
        }
        return $code;
    }

    
    protected function get_yui3lib_headcode() {
        global $CFG;

        $jsrev = $this->get_jsrev();

        $yuiformat = '-min';
        if ($this->yui3loader->filter === 'RAW') {
            $yuiformat = '';
        }

        $format = '-min';
        if ($this->YUI_config->groups['moodle']['filter'] === 'DEBUG') {
            $format = '-debug';
        }

        $rollupversion = $CFG->yui3version;
        if (!empty($CFG->yuipatchlevel)) {
            $rollupversion .= '_' . $CFG->yuipatchlevel;
        }

        $baserollups = array(
            'rollup/' . $rollupversion . "/yui-moodlesimple{$yuiformat}.js",
            'rollup/' . $jsrev . "/mcore{$format}.js",
        );

        if ($this->yui3loader->combine) {
            return '<script type="text/javascript" src="' .
                    $this->yui3loader->local_comboBase .
                    implode('&amp;', $baserollups) .
                    '"></script>';
        } else {
            $code = '';
            foreach ($baserollups as $rollup) {
                $code .= '<script type="text/javascript" src="'.$this->yui3loader->local_comboBase.$rollup.'"></script>';
            }
            return $code;
        }

    }

    
    protected function get_css_code() {
                                        $attributes = array('rel'=>'stylesheet', 'type'=>'text/css');

                $code = $this->get_yui3lib_headcss();

                                                $code .= html_writer::tag('script', '/** Required in order to fix style inclusion problems in IE with YUI **/', array('id'=>'firstthemesheet', 'type'=>'text/css'));

        $urls = $this->cssthemeurls + $this->cssurls;
        foreach ($urls as $url) {
            $attributes['href'] = $url;
            $code .= html_writer::empty_tag('link', $attributes) . "\n";
                        unset($attributes['id']);
        }

        return $code;
    }

    
    protected function get_extra_modules_code() {
        if (empty($this->extramodules)) {
            return '';
        }
        return html_writer::script(js_writer::function_call('M.yui.add_module', array($this->extramodules)));
    }

    
    public function get_head_code(moodle_page $page, core_renderer $renderer) {
        global $CFG;

                        $this->init_requirements_data($page, $renderer);

        $output = '';

                $output .= $this->get_css_code();

                $js = "var M = {}; M.yui = {};\n";

                                $js .= "M.pageloadstarttime = new Date();\n";

                $js .= js_writer::set_variable('M.cfg', $this->M_cfg, false);

                                $js .= $this->YUI_config->get_config_functions();
        $js .= js_writer::set_variable('YUI_config', $this->YUI_config, false) . "\n";
        $js .= "M.yui.loader = {modules: {}};\n";         $js = $this->YUI_config->update_header_js($js);

        $output .= html_writer::script($js);

                if ($this->jsinitvariables['head']) {
            $js = '';
            foreach ($this->jsinitvariables['head'] as $data) {
                list($var, $value) = $data;
                $js .= js_writer::set_variable($var, $value, true);
            }
            $output .= html_writer::script($js);
        }

                $this->headdone = true;

        return $output;
    }

    
    public function get_top_of_body_code() {
                $links = '';
        $attributes = array('class' => 'skip');
        foreach ($this->skiplinks as $url => $text) {
            $links .= html_writer::link('#'.$url, $text, $attributes);
        }
        $output = html_writer::tag('div', $links, array('class'=>'skiplinks')) . "\n";
        $this->js_init_call('M.util.init_skiplink');

                $output .= $this->get_yui3lib_headcode();

                $output .= $this->get_jquery_headcode();

                $output .= html_writer::script('', $this->js_fix_url('/lib/javascript-static.js'));

                if ($this->jsincludes['head']) {
            foreach ($this->jsincludes['head'] as $url) {
                $output .= html_writer::script('', $url);
            }
        }

                $output .= html_writer::script("document.body.className += ' jsenabled';") . "\n";
        $this->topofbodydone = true;
        return $output;
    }

    
    public function get_end_code() {
        global $CFG;
        $output = '';

                $logconfig = new stdClass();
        $logconfig->level = 'warn';
        if ($CFG->debugdeveloper) {
            $logconfig->level = 'trace';
        }
        $this->js_call_amd('core/log', 'setConfig', array($logconfig));

                $output .= $this->get_amd_footercode();

                $output .= $this->get_extra_modules_code();

        $this->js_init_code('M.util.js_complete("init");', true);

                if ($this->jsincludes['footer']) {
            foreach ($this->jsincludes['footer'] as $url) {
                $output .= html_writer::script('', $url);
            }
        }

                        $this->strings_for_js(array(
            'confirm',
            'yes',
            'no',
            'areyousure',
            'closebuttontitle',
            'unknownerror',
        ), 'moodle');
        if (!empty($this->stringsforjs)) {
            $strings = array();
            foreach ($this->stringsforjs as $component=>$v) {
                foreach($v as $indentifier => $langstring) {
                    $strings[$component][$indentifier] = $langstring->out();
                }
            }
            $output .= html_writer::script(js_writer::set_variable('M.str', $strings));
        }

                if ($this->jsinitvariables['footer']) {
            $js = '';
            foreach ($this->jsinitvariables['footer'] as $data) {
                list($var, $value) = $data;
                $js .= js_writer::set_variable($var, $value, true);
            }
            $output .= html_writer::script($js);
        }

        $inyuijs = $this->get_javascript_code(false);
        $ondomreadyjs = $this->get_javascript_code(true);
        $jsinit = $this->get_javascript_init_code();
        $handlersjs = $this->get_event_handler_code();

                $js = "(function() {{$inyuijs}{$ondomreadyjs}{$jsinit}{$handlersjs}})();";

        $output .= html_writer::script($js);

        return $output;
    }

    
    public function is_head_done() {
        return $this->headdone;
    }

    
    public function is_top_of_body_done() {
        return $this->topofbodydone;
    }

    
    public function should_create_one_time_item_now($thing) {
        if ($this->has_one_time_item_been_created($thing)) {
            return false;
        }

        $this->set_one_time_item_created($thing);
        return true;
    }

    
    public function has_one_time_item_been_created($thing) {
        return isset($this->onetimeitemsoutput[$thing]);
    }

    
    public function set_one_time_item_created($thing) {
        if ($this->has_one_time_item_been_created($thing)) {
            throw new coding_exception($thing . ' is only supposed to be ouput ' .
                    'once per page, but it seems to be being output again.');
        }
        return $this->onetimeitemsoutput[$thing] = true;
    }
}


class YUI_config {
    
    public $debug = false;
    public $base;
    public $comboBase;
    public $combine;
    public $filter = null;
    public $insertBefore = 'firstthemesheet';
    public $groups = array();
    public $modules = array();

    
    protected $jsconfigfunctions = array();

    
    public function add_group($name, $config) {
        if (isset($this->groups[$name])) {
            throw new coding_exception("A YUI configuration group for '{$name}' already exists. To make changes to this group use YUI_config->update_group().");
        }
        $this->groups[$name] = $config;
    }

    
    public function update_group($name, $config) {
        if (!isset($this->groups[$name])) {
            throw new coding_exception('The Moodle YUI module does not exist. You must define the moodle module config using YUI_config->add_module_config first.');
        }
        $this->groups[$name] = $config;
    }

    
    public function set_config_function($function) {
        $configname = 'yui' . (count($this->jsconfigfunctions) + 1) . 'ConfigFn';
        if (isset($this->jsconfigfunctions[$configname])) {
            throw new coding_exception("A YUI config function with this name already exists. Config function names must be unique.");
        }
        $this->jsconfigfunctions[$configname] = $function;
        return '@' . $configname . '@';
    }

    
    public function set_config_source($file) {
        global $CFG;
        $cache = cache::make('core', 'yuimodules');

                $keyname = 'configfn_' . $file;
        $fullpath = $CFG->dirroot . '/' . $file;
        if (!isset($CFG->jsrev) || $CFG->jsrev == -1) {
            $cache->delete($keyname);
            $configfn = file_get_contents($fullpath);
        } else {
            $configfn = $cache->get($keyname);
            if ($configfn === false) {
                require_once($CFG->libdir . '/jslib.php');
                $configfn = core_minify::js_files(array($fullpath));
                $cache->set($keyname, $configfn);
            }
        }
        return $this->set_config_function($configfn);
    }

    
    public function get_config_functions() {
        $configfunctions = '';
        foreach ($this->jsconfigfunctions as $functionname => $function) {
            $configfunctions .= "var {$functionname} = function(me) {";
            $configfunctions .= $function;
            $configfunctions .= "};\n";
        }
        return $configfunctions;
    }

    
    public function update_header_js($js) {
                                foreach ($this->jsconfigfunctions as $functionname => $function) {
            $js = str_replace('"@' . $functionname . '@"', $functionname, $js);
        }
        return $js;
    }

    
    public function add_module_config($name, $config, $group = null) {
        if ($group) {
            if (!isset($this->groups[$name])) {
                throw new coding_exception('The Moodle YUI module does not exist. You must define the moodle module config using YUI_config->add_module_config first.');
            }
            if (!isset($this->groups[$group]['modules'])) {
                $this->groups[$group]['modules'] = array();
            }
            $modules = &$this->groups[$group]['modules'];
        } else {
            $modules = &$this->modules;
        }
        $modules[$name] = $config;
    }

    
    public function add_moodle_metadata() {
        global $CFG;
        if (!isset($this->groups['moodle'])) {
            throw new coding_exception('The Moodle YUI module does not exist. You must define the moodle module config using YUI_config->add_module_config first.');
        }

        if (!isset($this->groups['moodle']['modules'])) {
            $this->groups['moodle']['modules'] = array();
        }

        $cache = cache::make('core', 'yuimodules');
        if (!isset($CFG->jsrev) || $CFG->jsrev == -1) {
            $metadata = array();
            $metadata = $this->get_moodle_metadata();
            $cache->delete('metadata');
        } else {
                        if (!$metadata = $cache->get('metadata')) {
                $metadata = $this->get_moodle_metadata();
                $cache->set('metadata', $metadata);
            }
        }

                $this->groups['moodle']['modules'] = array_merge($this->groups['moodle']['modules'],
                $metadata);
    }

    
    private function get_moodle_metadata() {
        $moodlemodules = array();
                if ($module = $this->get_moodle_path_metadata(core_component::get_component_directory('core'))) {
            $moodlemodules = array_merge($moodlemodules, $module);
        }

                $subsystems = core_component::get_core_subsystems();
        foreach ($subsystems as $subsystem => $path) {
            if (is_null($path)) {
                continue;
            }
            if ($module = $this->get_moodle_path_metadata($path)) {
                $moodlemodules = array_merge($moodlemodules, $module);
            }
        }

                $plugintypes = core_component::get_plugin_types();
        foreach ($plugintypes as $plugintype => $pathroot) {
            $pluginlist = core_component::get_plugin_list($plugintype);
            foreach ($pluginlist as $plugin => $path) {
                if ($module = $this->get_moodle_path_metadata($path)) {
                    $moodlemodules = array_merge($moodlemodules, $module);
                }
            }
        }

        return $moodlemodules;
    }

    
    private function get_moodle_path_metadata($path) {
                $baseyui = $path . '/yui/src';
        $modules = array();
        if (is_dir($baseyui)) {
            $items = new DirectoryIterator($baseyui);
            foreach ($items as $item) {
                if ($item->isDot() or !$item->isDir()) {
                    continue;
                }
                $metafile = realpath($baseyui . '/' . $item . '/meta/' . $item . '.json');
                if (!is_readable($metafile)) {
                    continue;
                }
                $metadata = file_get_contents($metafile);
                $modules = array_merge($modules, (array) json_decode($metadata));
            }
        }
        return $modules;
    }

    
    public function define_patched_core_modules($combobase, $yuiversion, $patchlevel, $patchedmodules) {
                $subversion = $yuiversion . '_' . $patchlevel;

        if ($this->comboBase == $combobase) {
                                                $patterns = array();
            $modules = array();
            foreach ($patchedmodules as $modulename) {
                                                                $patterns[$modulename] = array(
                    'group' => 'yui-patched',
                );
                $modules[$modulename] = array();
            }

                        $this->add_group('yui-patched', array(
                'combine' => true,
                'root' => $subversion . '/',
                'patterns' => $patterns,
                'modules' => $modules,
            ));

        } else {
                                                $fullpathbase = $combobase . $subversion . '/';
            foreach ($patchedmodules as $modulename) {
                $this->modules[$modulename] = array(
                    'fullpath' => $fullpathbase . $modulename . '/' . $modulename . '-min.js'
                );
            }
        }
    }
}


function js_reset_all_caches() {
    global $CFG;

    $next = time();
    if (isset($CFG->jsrev) and $next <= $CFG->jsrev and $CFG->jsrev - $next < 60*60) {
                                $next = $CFG->jsrev+1;
    }

    set_config('jsrev', $next);
}
