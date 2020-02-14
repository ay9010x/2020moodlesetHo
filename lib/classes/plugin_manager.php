<?php



defined('MOODLE_INTERNAL') || die();


class core_plugin_manager {

    
    const PLUGIN_SOURCE_STANDARD    = 'std';
    
    const PLUGIN_SOURCE_EXTENSION   = 'ext';

    
    const PLUGIN_STATUS_NODB        = 'nodb';
    
    const PLUGIN_STATUS_UPTODATE    = 'uptodate';
    
    const PLUGIN_STATUS_NEW         = 'new';
    
    const PLUGIN_STATUS_UPGRADE     = 'upgrade';
    
    const PLUGIN_STATUS_DELETE     = 'delete';
    
    const PLUGIN_STATUS_DOWNGRADE   = 'downgrade';
    
    const PLUGIN_STATUS_MISSING     = 'missing';

    
    const REQUIREMENT_STATUS_OK = 'ok';
    
    const REQUIREMENT_STATUS_OUTDATED = 'outdated';
    
    const REQUIREMENT_STATUS_MISSING = 'missing';

    
    const REQUIREMENT_AVAILABLE = 'available';
    
    const REQUIREMENT_UNAVAILABLE = 'unavailable';

    
    protected static $singletoninstance;
    
    protected $pluginsinfo = null;
    
    protected $subpluginsinfo = null;
    
    protected $remotepluginsinfoatleast = null;
    
    protected $remotepluginsinfoexact = null;
    
    protected $installedplugins = null;
    
    protected $enabledplugins = null;
    
    protected $presentplugins = null;
    
    protected $plugintypes = null;
    
    protected $codemanager = null;
    
    protected $updateapiclient = null;

    
    protected function __construct() {
    }

    
    protected function __clone() {
    }

    
    public static function instance() {
        if (is_null(static::$singletoninstance)) {
            static::$singletoninstance = new static();
        }
        return static::$singletoninstance;
    }

    
    public static function reset_caches($phpunitreset = false) {
        if ($phpunitreset) {
            static::$singletoninstance = null;
        } else {
            if (static::$singletoninstance) {
                static::$singletoninstance->pluginsinfo = null;
                static::$singletoninstance->subpluginsinfo = null;
                static::$singletoninstance->remotepluginsinfoatleast = null;
                static::$singletoninstance->remotepluginsinfoexact = null;
                static::$singletoninstance->installedplugins = null;
                static::$singletoninstance->enabledplugins = null;
                static::$singletoninstance->presentplugins = null;
                static::$singletoninstance->plugintypes = null;
                static::$singletoninstance->codemanager = null;
                static::$singletoninstance->updateapiclient = null;
            }
        }
        $cache = cache::make('core', 'plugin_manager');
        $cache->purge();
    }

    
    public function get_plugin_types() {
        if (func_num_args() > 0) {
            if (!func_get_arg(0)) {
                throw coding_exception('core_plugin_manager->get_plugin_types() does not support relative paths.');
            }
        }
        if ($this->plugintypes) {
            return $this->plugintypes;
        }

        $this->plugintypes = $this->reorder_plugin_types(core_component::get_plugin_types());
        return $this->plugintypes;
    }

    
    protected function load_installed_plugins() {
        global $DB, $CFG;

        if ($this->installedplugins) {
            return;
        }

        if (empty($CFG->version)) {
                        $this->installedplugins = array();
            return;
        }

        $cache = cache::make('core', 'plugin_manager');
        $installed = $cache->get('installed');

        if (is_array($installed)) {
            $this->installedplugins = $installed;
            return;
        }

        $this->installedplugins = array();

                if ($CFG->version < 2013092001.02) {
                        $modules = $DB->get_records('modules', array(), 'name ASC', 'id, name, version');
            foreach ($modules as $module) {
                $this->installedplugins['mod'][$module->name] = $module->version;
            }
            $blocks = $DB->get_records('block', array(), 'name ASC', 'id, name, version');
            foreach ($blocks as $block) {
                $this->installedplugins['block'][$block->name] = $block->version;
            }
        }

        $versions = $DB->get_records('config_plugins', array('name'=>'version'));
        foreach ($versions as $version) {
            $parts = explode('_', $version->plugin, 2);
            if (!isset($parts[1])) {
                                continue;
            }
                        $this->installedplugins[$parts[0]][$parts[1]] = $version->value;
        }

        foreach ($this->installedplugins as $key => $value) {
            ksort($this->installedplugins[$key]);
        }

        $cache->set('installed', $this->installedplugins);
    }

    
    public function get_installed_plugins($type) {
        $this->load_installed_plugins();
        if (isset($this->installedplugins[$type])) {
            return $this->installedplugins[$type];
        }
        return array();
    }

    
    protected function load_enabled_plugins() {
        global $CFG;

        if ($this->enabledplugins) {
            return;
        }

        if (empty($CFG->version)) {
            $this->enabledplugins = array();
            return;
        }

        $cache = cache::make('core', 'plugin_manager');
        $enabled = $cache->get('enabled');

        if (is_array($enabled)) {
            $this->enabledplugins = $enabled;
            return;
        }

        $this->enabledplugins = array();

        require_once($CFG->libdir.'/adminlib.php');

        $plugintypes = core_component::get_plugin_types();
        foreach ($plugintypes as $plugintype => $fulldir) {
            $plugininfoclass = static::resolve_plugininfo_class($plugintype);
            if (class_exists($plugininfoclass)) {
                $enabled = $plugininfoclass::get_enabled_plugins();
                if (!is_array($enabled)) {
                    continue;
                }
                $this->enabledplugins[$plugintype] = $enabled;
            }
        }

        $cache->set('enabled', $this->enabledplugins);
    }

    
    public function get_enabled_plugins($type) {
        $this->load_enabled_plugins();
        if (isset($this->enabledplugins[$type])) {
            return $this->enabledplugins[$type];
        }
        return null;
    }

    
    protected function load_present_plugins() {
        if ($this->presentplugins) {
            return;
        }

        $cache = cache::make('core', 'plugin_manager');
        $present = $cache->get('present');

        if (is_array($present)) {
            $this->presentplugins = $present;
            return;
        }

        $this->presentplugins = array();

        $plugintypes = core_component::get_plugin_types();
        foreach ($plugintypes as $type => $typedir) {
            $plugs = core_component::get_plugin_list($type);
            foreach ($plugs as $plug => $fullplug) {
                $module = new stdClass();
                $plugin = new stdClass();
                $plugin->version = null;
                include($fullplug.'/version.php');

                                if (!is_object($module) or (count((array)$module) > 0)) {
                    debugging('Unsupported $module syntax detected in version.php of the '.$type.'_'.$plug.' plugin.');
                    $skipcache = true;
                }

                                if (empty($plugin->component) or ($plugin->component !== $type.'_'.$plug)) {
                    debugging('Plugin '.$type.'_'.$plug.' does not declare valid $plugin->component in its version.php.');
                    $skipcache = true;
                }

                $this->presentplugins[$type][$plug] = $plugin;
            }
        }

        if (empty($skipcache)) {
            $cache->set('present', $this->presentplugins);
        }
    }

    
    public function get_present_plugins($type) {
        $this->load_present_plugins();
        if (isset($this->presentplugins[$type])) {
            return $this->presentplugins[$type];
        }
        return null;
    }

    
    public function get_plugins() {
        $this->init_pluginsinfo_property();

                foreach ($this->pluginsinfo as $plugintype => $list) {
            if ($list === null) {
                $this->get_plugins_of_type($plugintype);
            }
        }

        return $this->pluginsinfo;
    }

    
    public function get_plugins_of_type($type) {
        global $CFG;

        $this->init_pluginsinfo_property();

        if (!array_key_exists($type, $this->pluginsinfo)) {
            return array();
        }

        if (is_array($this->pluginsinfo[$type])) {
            return $this->pluginsinfo[$type];
        }

        $types = core_component::get_plugin_types();

        if (!isset($types[$type])) {
                        $plugintypeclass = static::resolve_plugininfo_class($type);
            $this->pluginsinfo[$type] = $plugintypeclass::get_plugins($type, null, $plugintypeclass, $this);
            return $this->pluginsinfo[$type];
        }

        
        $plugintypeclass = static::resolve_plugininfo_class($type);
        $plugins = $plugintypeclass::get_plugins($type, $types[$type], $plugintypeclass, $this);
        $this->pluginsinfo[$type] = $plugins;

        return $this->pluginsinfo[$type];
    }

    
    protected function init_pluginsinfo_property() {
        if (is_array($this->pluginsinfo)) {
            return;
        }
        $this->pluginsinfo = array();

        $plugintypes = $this->get_plugin_types();

        foreach ($plugintypes as $plugintype => $plugintyperootdir) {
            $this->pluginsinfo[$plugintype] = null;
        }

                $this->load_installed_plugins();
        foreach ($this->installedplugins as $plugintype => $unused) {
            if (!isset($plugintypes[$plugintype])) {
                $this->pluginsinfo[$plugintype] = null;
            }
        }
    }

    
    public static function resolve_plugininfo_class($type) {
        $plugintypes = core_component::get_plugin_types();
        if (!isset($plugintypes[$type])) {
            return '\core\plugininfo\orphaned';
        }

        $parent = core_component::get_subtype_parent($type);

        if ($parent) {
            $class = '\\'.$parent.'\plugininfo\\' . $type;
            if (class_exists($class)) {
                $plugintypeclass = $class;
            } else {
                if ($dir = core_component::get_component_directory($parent)) {
                                        if (file_exists("$dir/adminlib.php")) {
                        global $CFG;
                        include_once("$dir/adminlib.php");
                    }
                    if (class_exists('plugininfo_' . $type)) {
                        $plugintypeclass = 'plugininfo_' . $type;
                        debugging('Class "'.$plugintypeclass.'" is deprecated, migrate to "'.$class.'"', DEBUG_DEVELOPER);
                    } else {
                        debugging('Subplugin type "'.$type.'" should define class "'.$class.'"', DEBUG_DEVELOPER);
                        $plugintypeclass = '\core\plugininfo\general';
                    }
                } else {
                    $plugintypeclass = '\core\plugininfo\general';
                }
            }
        } else {
            $class = '\core\plugininfo\\' . $type;
            if (class_exists($class)) {
                $plugintypeclass = $class;
            } else {
                debugging('All standard types including "'.$type.'" should have plugininfo class!', DEBUG_DEVELOPER);
                $plugintypeclass = '\core\plugininfo\general';
            }
        }

        if (!in_array('core\plugininfo\base', class_parents($plugintypeclass))) {
            throw new coding_exception('Class ' . $plugintypeclass . ' must extend \core\plugininfo\base');
        }

        return $plugintypeclass;
    }

    
    public function get_subplugins_of_plugin($component) {

        $pluginfo = $this->get_plugin_info($component);

        if (is_null($pluginfo)) {
            return array();
        }

        $subplugins = $this->get_subplugins();

        if (!isset($subplugins[$pluginfo->component])) {
            return array();
        }

        $list = array();

        foreach ($subplugins[$pluginfo->component] as $subdata) {
            foreach ($this->get_plugins_of_type($subdata->type) as $subpluginfo) {
                $list[$subpluginfo->component] = $subpluginfo;
            }
        }

        return $list;
    }

    
    public function get_subplugins() {

        if (is_array($this->subpluginsinfo)) {
            return $this->subpluginsinfo;
        }

        $plugintypes = core_component::get_plugin_types();

        $this->subpluginsinfo = array();
        foreach (core_component::get_plugin_types_with_subplugins() as $type => $ignored) {
            foreach (core_component::get_plugin_list($type) as $plugin => $componentdir) {
                $component = $type.'_'.$plugin;
                $subplugins = core_component::get_subplugins($component);
                if (!$subplugins) {
                    continue;
                }
                $this->subpluginsinfo[$component] = array();
                foreach ($subplugins as $subplugintype => $ignored) {
                    $subplugin = new stdClass();
                    $subplugin->type = $subplugintype;
                    $subplugin->typerootdir = $plugintypes[$subplugintype];
                    $this->subpluginsinfo[$component][$subplugintype] = $subplugin;
                }
            }
        }
        return $this->subpluginsinfo;
    }

    
    public function get_parent_of_subplugin($subplugintype) {
        $parent = core_component::get_subtype_parent($subplugintype);
        if (!$parent) {
            return false;
        }
        return $parent;
    }

    
    public function plugin_name($component) {

        $pluginfo = $this->get_plugin_info($component);

        if (is_null($pluginfo)) {
            throw new moodle_exception('err_unknown_plugin', 'core_plugin', '', array('plugin' => $component));
        }

        return $pluginfo->displayname;
    }

    
    public function plugintype_name($type) {

        if (get_string_manager()->string_exists('type_' . $type, 'core_plugin')) {
                        return get_string('type_' . $type, 'core_plugin');

        } else if ($parent = $this->get_parent_of_subplugin($type)) {
                        if (get_string_manager()->string_exists('subplugintype_' . $type, $parent)) {
                return $this->plugin_name($parent) . ' / ' . get_string('subplugintype_' . $type, $parent);
            } else {
                return $this->plugin_name($parent) . ' / ' . $type;
            }

        } else {
            return $type;
        }
    }

    
    public function plugintype_name_plural($type) {

        if (get_string_manager()->string_exists('type_' . $type . '_plural', 'core_plugin')) {
                        return get_string('type_' . $type . '_plural', 'core_plugin');

        } else if ($parent = $this->get_parent_of_subplugin($type)) {
                        if (get_string_manager()->string_exists('subplugintype_' . $type . '_plural', $parent)) {
                return $this->plugin_name($parent) . ' / ' . get_string('subplugintype_' . $type . '_plural', $parent);
            } else {
                return $this->plugin_name($parent) . ' / ' . $type;
            }

        } else {
            return $type;
        }
    }

    
    public function get_plugin_info($component) {
        list($type, $name) = core_component::normalize_component($component);
        $plugins = $this->get_plugins_of_type($type);
        if (isset($plugins[$name])) {
            return $plugins[$name];
        } else {
            return null;
        }
    }

    
    public function plugin_external_source($component) {

        $plugininfo = $this->get_plugin_info($component);

        if (is_null($plugininfo)) {
            return false;
        }

        $pluginroot = $plugininfo->rootdir;

        if (is_dir($pluginroot.'/.git')) {
            return 'git';
        }

        if (is_file($pluginroot.'/.git')) {
            return 'git-submodule';
        }

        if (is_dir($pluginroot.'/CVS')) {
            return 'cvs';
        }

        if (is_dir($pluginroot.'/.svn')) {
            return 'svn';
        }

        if (is_dir($pluginroot.'/.hg')) {
            return 'mercurial';
        }

        return false;
    }

    
    public function other_plugins_that_require($component) {
        $others = array();
        foreach ($this->get_plugins() as $type => $plugins) {
            foreach ($plugins as $plugin) {
                $required = $plugin->get_other_required_plugins();
                if (isset($required[$component])) {
                    $others[] = $plugin->component;
                }
            }
        }
        return $others;
    }

    
    public function are_dependencies_satisfied($dependencies) {
        foreach ($dependencies as $component => $requiredversion) {
            $otherplugin = $this->get_plugin_info($component);
            if (is_null($otherplugin)) {
                return false;
            }

            if ($requiredversion != ANY_VERSION and $otherplugin->versiondisk < $requiredversion) {
                return false;
            }
        }

        return true;
    }

    
    public function all_plugins_ok($moodleversion, &$failedplugins = array()) {

        $return = true;
        foreach ($this->get_plugins() as $type => $plugins) {
            foreach ($plugins as $plugin) {

                if (!$plugin->is_core_dependency_satisfied($moodleversion)) {
                    $return = false;
                    $failedplugins[] = $plugin->component;
                }

                if (!$this->are_dependencies_satisfied($plugin->get_other_required_plugins())) {
                    $return = false;
                    $failedplugins[] = $plugin->component;
                }
            }
        }

        return $return;
    }

    
    public function resolve_requirements(\core\plugininfo\base $plugin, $moodleversion=null, $moodlebranch=null) {
        global $CFG;

        if ($plugin->versiondisk === null) {
                        return array();
        }

        if ($moodleversion === null) {
            $moodleversion = $CFG->version;
        }

        if ($moodlebranch === null) {
            $moodlebranch = $CFG->branch;
        }

        $reqs = array();
        $reqcore = $this->resolve_core_requirements($plugin, $moodleversion);

        if (!empty($reqcore)) {
            $reqs['core'] = $reqcore;
        }

        foreach ($plugin->get_other_required_plugins() as $reqplug => $reqver) {
            $reqs[$reqplug] = $this->resolve_dependency_requirements($plugin, $reqplug, $reqver, $moodlebranch);
        }

        return $reqs;
    }

    
    protected function resolve_core_requirements(\core\plugininfo\base $plugin, $moodleversion) {

        $reqs = (object)array(
            'hasver' => null,
            'reqver' => null,
            'status' => null,
            'availability' => null,
        );

        $reqs->hasver = $moodleversion;

        if (empty($plugin->versionrequires)) {
            $reqs->reqver = ANY_VERSION;
        } else {
            $reqs->reqver = $plugin->versionrequires;
        }

        if ($plugin->is_core_dependency_satisfied($moodleversion)) {
            $reqs->status = self::REQUIREMENT_STATUS_OK;
        } else {
            $reqs->status = self::REQUIREMENT_STATUS_OUTDATED;
        }

        return $reqs;
    }

    
    protected function resolve_dependency_requirements(\core\plugininfo\base $plugin, $otherpluginname,
            $requiredversion, $moodlebranch) {

        $reqs = (object)array(
            'hasver' => null,
            'reqver' => null,
            'status' => null,
            'availability' => null,
        );

        $otherplugin = $this->get_plugin_info($otherpluginname);

        if ($otherplugin !== null) {
                        $reqs->hasver = $otherplugin->versiondisk;
            $reqs->reqver = $requiredversion;
                        if ($requiredversion == ANY_VERSION or $otherplugin->versiondisk >= $requiredversion) {
                $reqs->status = self::REQUIREMENT_STATUS_OK;
            } else {
                $reqs->status = self::REQUIREMENT_STATUS_OUTDATED;
            }

        } else {
                        $reqs->hasver = null;
            $reqs->reqver = $requiredversion;
            $reqs->status = self::REQUIREMENT_STATUS_MISSING;
        }

        if ($reqs->status !== self::REQUIREMENT_STATUS_OK) {
            if ($this->is_remote_plugin_available($otherpluginname, $requiredversion, false)) {
                $reqs->availability = self::REQUIREMENT_AVAILABLE;
            } else {
                $reqs->availability = self::REQUIREMENT_UNAVAILABLE;
            }
        }

        return $reqs;
    }

    
    public function is_remote_plugin_available($component, $version, $exactmatch) {

        $info = $this->get_remote_plugin_info($component, $version, $exactmatch);

        if (empty($info)) {
                        return false;
        }

        if (empty($info->version)) {
                        return false;
        }

        return true;
    }

    
    public function is_remote_plugin_installable($component, $version, &$reason=null) {
        global $CFG;

                if (!empty($CFG->disableupdateautodeploy)) {
            $reason = 'disabled';
            return false;
        }

                if (!$this->is_remote_plugin_available($component, $version, true)) {
            $reason = 'remoteunavailable';
            return false;
        }

                list($plugintype, $pluginname) = core_component::normalize_component($component);
        if (!$this->is_plugintype_writable($plugintype)) {
            $reason = 'notwritableplugintype';
            return false;
        }

        $remoteinfo = $this->get_remote_plugin_info($component, $version, true);
        $localinfo = $this->get_plugin_info($component);

        if ($localinfo) {
                        if ($localinfo->versiondb > $remoteinfo->version->version) {
                $reason = 'cannotdowngrade';
                return false;
            }

                        if (is_dir($localinfo->rootdir)) {
                if (!$this->is_plugin_folder_removable($component)) {
                    $reason = 'notwritableplugin';
                    return false;
                }
            }
        }

                return true;
    }

    
    public function filter_installable($remoteinfos) {
        global $CFG;

        if (!empty($CFG->disableupdateautodeploy)) {
            return array();
        }
        if (empty($remoteinfos)) {
            return array();
        }
        $installable = array();
        foreach ($remoteinfos as $index => $remoteinfo) {
            if ($this->is_remote_plugin_installable($remoteinfo->component, $remoteinfo->version->version)) {
                $installable[$index] = $remoteinfo;
            }
        }
        return $installable;
    }

    
    public function get_remote_plugin_info($component, $version, $exactmatch) {

        if ($exactmatch and $version == ANY_VERSION) {
            throw new coding_exception('Invalid request for exactly any version, it does not make sense.');
        }

        $client = $this->get_update_api_client();

        if ($exactmatch) {
                        if (!isset($this->remotepluginsinfoexact[$component][$version])) {
                $this->remotepluginsinfoexact[$component][$version] = $client->get_plugin_info($component, $version);
            }
            return $this->remotepluginsinfoexact[$component][$version];

        } else {
                        if (!isset($this->remotepluginsinfoatleast[$component][$version])) {
                $this->remotepluginsinfoatleast[$component][$version] = $client->find_plugin($component, $version);
            }
            return $this->remotepluginsinfoatleast[$component][$version];
        }
    }

    
    public function get_remote_plugin_zip($url, $md5) {
        global $CFG;

        if (!empty($CFG->disableupdateautodeploy)) {
            return false;
        }
        return $this->get_code_manager()->get_remote_plugin_zip($url, $md5);
    }

    
    public function unzip_plugin_file($zipfilepath, $targetdir, $rootdir = '') {
        return $this->get_code_manager()->unzip_plugin_file($zipfilepath, $targetdir, $rootdir);
    }

    
    public function get_plugin_zip_root_dir($zipfilepath) {
        return $this->get_code_manager()->get_plugin_zip_root_dir($zipfilepath);
    }

    
    public function missing_dependencies($availableonly=false) {

        $dependencies = array();

        foreach ($this->get_plugins() as $plugintype => $pluginfos) {
            foreach ($pluginfos as $pluginname => $pluginfo) {
                foreach ($this->resolve_requirements($pluginfo) as $reqname => $reqinfo) {
                    if ($reqname === 'core') {
                        continue;
                    }
                    if ($reqinfo->status != self::REQUIREMENT_STATUS_OK) {
                        if ($reqinfo->availability == self::REQUIREMENT_AVAILABLE) {
                            $remoteinfo = $this->get_remote_plugin_info($reqname, $reqinfo->reqver, false);

                            if (empty($dependencies[$reqname])) {
                                $dependencies[$reqname] = $remoteinfo;
                            } else {
                                                                                                                                                                if ($remoteinfo->version->version > $dependencies[$reqname]->version->version) {
                                    $dependencies[$reqname] = $remoteinfo;
                                }
                            }

                        } else {
                            if (!isset($dependencies[$reqname])) {
                                                                $dependencies[$reqname] = false;
                            }
                        }
                    }
                }
            }
        }

        if ($availableonly) {
            foreach ($dependencies as $component => $info) {
                if (empty($info) or empty($info->version)) {
                    unset($dependencies[$component]);
                }
            }
        }

        return $dependencies;
    }

    
    public function can_uninstall_plugin($component) {

        $pluginfo = $this->get_plugin_info($component);

        if (is_null($pluginfo)) {
            return false;
        }

        if (!$this->common_uninstall_check($pluginfo)) {
            return false;
        }

                $subplugins = $this->get_subplugins_of_plugin($pluginfo->component);
        foreach ($subplugins as $subpluginfo) {
                                    foreach ($this->other_plugins_that_require($subpluginfo->component) as $requiresme) {
                $ismyparent = ($pluginfo->component === $requiresme);
                $ismysibling = in_array($requiresme, array_keys($subplugins));
                if (!$ismyparent and !$ismysibling) {
                    return false;
                }
            }
        }

                        foreach ($this->other_plugins_that_require($pluginfo->component) as $requiresme) {
            $ismysubplugin = in_array($requiresme, array_keys($subplugins));
            if (!$ismysubplugin) {
                return false;
            }
        }

        return true;
    }

    
    public function install_plugins(array $plugins, $confirmed, $silent) {
        global $CFG, $OUTPUT;

        if (!empty($CFG->disableupdateautodeploy)) {
            return false;
        }

        if (empty($plugins)) {
            return false;
        }

        $ok = get_string('ok', 'core');

                $silent or $this->mtrace(get_string('packagesdebug', 'core_plugin'), PHP_EOL, DEBUG_NORMAL);

                $zips = array();
        foreach ($plugins as $plugin) {
            if ($plugin instanceof \core\update\remote_info) {
                $zips[$plugin->component] = $this->get_remote_plugin_zip($plugin->version->downloadurl,
                    $plugin->version->downloadmd5);
                $silent or $this->mtrace(get_string('packagesdownloading', 'core_plugin', $plugin->component), ' ... ');
                $silent or $this->mtrace(PHP_EOL.' <- '.$plugin->version->downloadurl, '', DEBUG_DEVELOPER);
                $silent or $this->mtrace(PHP_EOL.' -> '.$zips[$plugin->component], ' ... ', DEBUG_DEVELOPER);
                if (!$zips[$plugin->component]) {
                    $silent or $this->mtrace(get_string('error'));
                    return false;
                }
                $silent or $this->mtrace($ok);
            } else {
                if (empty($plugin->zipfilepath)) {
                    throw new coding_exception('Unexpected data structure provided');
                }
                $zips[$plugin->component] = $plugin->zipfilepath;
                $silent or $this->mtrace('ZIP '.$plugin->zipfilepath, PHP_EOL, DEBUG_DEVELOPER);
            }
        }

                foreach ($plugins as $plugin) {
            $zipfile = $zips[$plugin->component];
            $silent or $this->mtrace(get_string('packagesvalidating', 'core_plugin', $plugin->component), ' ... ');
            list($plugintype, $pluginname) = core_component::normalize_component($plugin->component);
            $tmp = make_request_directory();
            $zipcontents = $this->unzip_plugin_file($zipfile, $tmp, $pluginname);
            if (empty($zipcontents)) {
                $silent or $this->mtrace(get_string('error'));
                $silent or $this->mtrace('Unable to unzip '.$zipfile, PHP_EOL, DEBUG_DEVELOPER);
                return false;
            }

            $validator = \core\update\validator::instance($tmp, $zipcontents);
            $validator->assert_plugin_type($plugintype);
            $validator->assert_moodle_version($CFG->version);
                        $result = $validator->execute();
            if (!$silent) {
                $result ? $this->mtrace($ok) : $this->mtrace(get_string('error'));
                foreach ($validator->get_messages() as $message) {
                    if ($message->level === $validator::INFO) {
                                                $level = DEBUG_NORMAL;
                    } else if ($message->level === $validator::DEBUG) {
                                                $level = DEBUG_ALL;
                    } else {
                                                $level = null;
                    }
                    if ($message->level === $validator::WARNING and !CLI_SCRIPT) {
                        $this->mtrace('  <strong>['.$validator->message_level_name($message->level).']</strong>', ' ', $level);
                    } else {
                        $this->mtrace('  ['.$validator->message_level_name($message->level).']', ' ', $level);
                    }
                    $this->mtrace($validator->message_code_name($message->msgcode), ' ', $level);
                    $info = $validator->message_code_info($message->msgcode, $message->addinfo);
                    if ($info) {
                        $this->mtrace('['.s($info).']', ' ', $level);
                    } else if (is_string($message->addinfo)) {
                        $this->mtrace('['.s($message->addinfo, true).']', ' ', $level);
                    } else {
                        $this->mtrace('['.s(json_encode($message->addinfo, true)).']', ' ', $level);
                    }
                    if ($icon = $validator->message_help_icon($message->msgcode)) {
                        if (CLI_SCRIPT) {
                            $this->mtrace(PHP_EOL.'  ^^^ '.get_string('help').': '.
                                get_string($icon->identifier.'_help', $icon->component), '', $level);
                        } else {
                            $this->mtrace($OUTPUT->render($icon), ' ', $level);
                        }
                    }
                    $this->mtrace(PHP_EOL, '', $level);
                }
            }
            if (!$result) {
                $silent or $this->mtrace(get_string('packagesvalidatingfailed', 'core_plugin'));
                return false;
            }
        }
        $silent or $this->mtrace(PHP_EOL.get_string('packagesvalidatingok', 'core_plugin'));

        if (!$confirmed) {
            return true;
        }

                foreach ($plugins as $plugin) {
            $silent or $this->mtrace(get_string('packagesextracting', 'core_plugin', $plugin->component), ' ... ');
            $zipfile = $zips[$plugin->component];
            list($plugintype, $pluginname) = core_component::normalize_component($plugin->component);
            $target = $this->get_plugintype_root($plugintype);
            if (file_exists($target.'/'.$pluginname)) {
                $this->remove_plugin_folder($this->get_plugin_info($plugin->component));
            }
            if (!$this->unzip_plugin_file($zipfile, $target, $pluginname)) {
                $silent or $this->mtrace(get_string('error'));
                $silent or $this->mtrace('Unable to unzip '.$zipfile, PHP_EOL, DEBUG_DEVELOPER);
                if (function_exists('opcache_reset')) {
                    opcache_reset();
                }
                return false;
            }
            $silent or $this->mtrace($ok);
        }
        if (function_exists('opcache_reset')) {
            opcache_reset();
        }

        return true;
    }

    
    protected function mtrace($msg, $eol=PHP_EOL, $debug=null) {
        global $CFG;

        if ($debug !== null and !debugging(null, $debug)) {
            return;
        }

        mtrace($msg, $eol);
    }

    
    public function get_uninstall_url($component, $return = 'overview') {
        if (!$this->can_uninstall_plugin($component)) {
            return null;
        }

        $pluginfo = $this->get_plugin_info($component);

        if (is_null($pluginfo)) {
            return null;
        }

        if (method_exists($pluginfo, 'get_uninstall_url')) {
            debugging('plugininfo method get_uninstall_url() is deprecated, all plugins should be uninstalled via standard URL only.');
            return $pluginfo->get_uninstall_url($return);
        }

        return $pluginfo->get_default_uninstall_url($return);
    }

    
    public function uninstall_plugin($component, progress_trace $progress) {

        $pluginfo = $this->get_plugin_info($component);

        if (is_null($pluginfo)) {
            return false;
        }

                $result = $pluginfo->uninstall($progress);
        if (!$result) {
            return false;
        }

                ob_start();
        uninstall_plugin($pluginfo->type, $pluginfo->name);
        $progress->output(ob_get_clean());

        return true;
    }

    
    public function some_plugins_updatable() {
        foreach ($this->get_plugins() as $type => $plugins) {
            foreach ($plugins as $plugin) {
                if ($plugin->available_updates()) {
                    return true;
                }
            }
        }

        return false;
    }

    
    public function load_available_updates_for_plugin($component) {
        global $CFG;

        $provider = \core\update\checker::instance();

        if (!$provider->enabled() or during_initial_install()) {
            return null;
        }

        if (isset($CFG->updateminmaturity)) {
            $minmaturity = $CFG->updateminmaturity;
        } else {
                        $minmaturity = MATURITY_STABLE;
        }

        return $provider->get_update_info($component, array('minmaturity' => $minmaturity));
    }

    
    public function available_updates() {

        $updates = array();

        foreach ($this->get_plugins() as $type => $plugins) {
            foreach ($plugins as $plugin) {
                $availableupdates = $plugin->available_updates();
                if (empty($availableupdates)) {
                    continue;
                }
                foreach ($availableupdates as $update) {
                    if (empty($updates[$plugin->component])) {
                        $updates[$plugin->component] = $update;
                        continue;
                    }
                    $maturitycurrent = $updates[$plugin->component]->maturity;
                    if (empty($maturitycurrent)) {
                        $maturitycurrent = MATURITY_STABLE - 25;
                    }
                    $maturityremote = $update->maturity;
                    if (empty($maturityremote)) {
                        $maturityremote = MATURITY_STABLE - 25;
                    }
                    if ($maturityremote < $maturitycurrent) {
                        continue;
                    }
                    if ($maturityremote > $maturitycurrent) {
                        $updates[$plugin->component] = $update;
                        continue;
                    }
                    if ($update->version > $updates[$plugin->component]->version) {
                        $updates[$plugin->component] = $update;
                        continue;
                    }
                }
            }
        }

        foreach ($updates as $component => $update) {
            $remoteinfo = $this->get_remote_plugin_info($component, $update->version, true);
            if (empty($remoteinfo) or empty($remoteinfo->version)) {
                unset($updates[$component]);
            } else {
                $updates[$component] = $remoteinfo;
            }
        }

        return $updates;
    }

    
    public function is_plugin_folder_removable($component) {

        $pluginfo = $this->get_plugin_info($component);

        if (is_null($pluginfo)) {
            return false;
        }

                if (!is_writable(dirname($pluginfo->rootdir))) {
            return false;
        }

                return $this->is_directory_removable($pluginfo->rootdir);
    }

    
    public function is_plugintype_writable($plugintype) {

        $plugintypepath = $this->get_plugintype_root($plugintype);

        if (is_null($plugintypepath)) {
            throw new coding_exception('Unknown plugin type: '.$plugintype);
        }

        if ($plugintypepath === false) {
            throw new coding_exception('Plugin type location does not exist: '.$plugintype);
        }

        return is_writable($plugintypepath);
    }

    
    public function get_plugintype_root($plugintype) {

        $plugintypepath = null;
        foreach (core_component::get_plugin_types() as $type => $fullpath) {
            if ($type === $plugintype) {
                $plugintypepath = $fullpath;
                break;
            }
        }
        if (is_null($plugintypepath)) {
            return null;
        }
        if (!is_dir($plugintypepath)) {
            return false;
        }

        return $plugintypepath;
    }

    
    public static function is_deleted_standard_plugin($type, $name) {
                                        $plugins = array(
            'qformat' => array('blackboard', 'learnwise'),
            'enrol' => array('authorize'),
            'tinymce' => array('dragmath'),
            'tool' => array('bloglevelupgrade', 'qeupgradehelper', 'timezoneimport'),
            'theme' => array('mymobile', 'afterburner', 'anomaly', 'arialist', 'binarius', 'boxxie', 'brick', 'formal_white',
                'formfactor', 'fusion', 'leatherbound', 'magazine', 'nimble', 'nonzero', 'overlay', 'serenity', 'sky_high',
                'splash', 'standard', 'standardold'),
            'webservice' => array('amf'),
        );

        if (!isset($plugins[$type])) {
            return false;
        }
        return in_array($name, $plugins[$type]);
    }

    
    public static function standard_plugins_list($type) {

        $standard_plugins = array(

            'antivirus' => array(
                'clamav'
            ),

            'atto' => array(
                'accessibilitychecker', 'accessibilityhelper', 'align',
                'backcolor', 'bold', 'charmap', 'clear', 'collapse', 'emoticon',
                'equation', 'fontcolor', 'html', 'image', 'indent', 'italic',
                'link', 'managefiles', 'media', 'noautolink', 'orderedlist',
                'rtl', 'strike', 'subscript', 'superscript', 'table', 'title',
                'underline', 'undo', 'unorderedlist'
            ),

            'assignment' => array(
                'offline', 'online', 'upload', 'uploadsingle'
            ),

            'assignsubmission' => array(
                'comments', 'file', 'onlinetext'
            ),

            'assignfeedback' => array(
                'comments', 'file', 'offline', 'editpdf'
            ),

            'auth' => array(
                'cas', 'db', 'email', 'fc', 'imap', 'ldap', 'lti', 'manual', 'mnet',
                'nntp', 'nologin', 'none', 'pam', 'pop3', 'radius',
                'shibboleth', 'webservice'
            ),

            'availability' => array(
                'completion', 'date', 'grade', 'group', 'grouping', 'profile'
            ),

            'block' => array(
                'activity_modules', 'activity_results', 'admin_bookmarks', 'badges',
                'blog_menu', 'blog_recent', 'blog_tags', 'calendar_month',
                'calendar_upcoming', 'comments', 'community',
                'completionstatus', 'course_list', 'course_overview',
                'course_summary', 'feedback', 'globalsearch', 'glossary_random', 'html',
                'login', 'lp', 'mentees', 'messages', 'mnet_hosts', 'myprofile',
                'navigation', 'news_items', 'online_users', 'participants',
                'private_files', 'quiz_results', 'recent_activity',
                'rss_client', 'search_forums', 'section_links',
                'selfcompletion', 'settings', 'site_main_menu',
                'social_activities', 'tag_flickr', 'tag_youtube', 'tags'
            ),

            'booktool' => array(
                'exportimscp', 'importhtml', 'print'
            ),

            'cachelock' => array(
                'file'
            ),

            'cachestore' => array(
                'file', 'memcache', 'memcached', 'mongodb', 'session', 'static'
            ),

            'calendartype' => array(
                'gregorian'
            ),

            'coursereport' => array(
                            ),

            'datafield' => array(
                'checkbox', 'date', 'file', 'latlong', 'menu', 'multimenu',
                'number', 'picture', 'radiobutton', 'text', 'textarea', 'url'
            ),

            'dataformat' => array(
                'html', 'csv', 'json', 'excel', 'ods',
            ),

            'datapreset' => array(
                'imagegallery'
            ),

            'editor' => array(
                'atto', 'textarea', 'tinymce'
            ),

            'enrol' => array(
                'category', 'cohort', 'database', 'flatfile',
                'guest', 'imsenterprise', 'ldap', 'lti', 'manual', 'meta', 'mnet',
                'paypal', 'self'
            ),

            'filter' => array(
                'activitynames', 'algebra', 'censor', 'emailprotect',
                'emoticon', 'mathjaxloader', 'mediaplugin', 'multilang', 'tex', 'tidy',
                'urltolink', 'data', 'glossary'
            ),

            'format' => array(
                'singleactivity', 'social', 'topics', 'weeks'
            ),

            'gradeexport' => array(
                'ods', 'txt', 'xls', 'xml'
            ),

            'gradeimport' => array(
                'csv', 'direct', 'xml'
            ),

            'gradereport' => array(
                'grader', 'history', 'outcomes', 'overview', 'user', 'singleview'
            ),

            'gradingform' => array(
                'rubric', 'guide'
            ),

            'local' => array(
            ),

            'logstore' => array(
                'database', 'legacy', 'standard',
            ),

            'ltiservice' => array(
                'memberships', 'profile', 'toolproxy', 'toolsettings'
            ),

            'message' => array(
                'airnotifier', 'email', 'jabber', 'popup'
            ),

            'mnetservice' => array(
                'enrol'
            ),

            'mod' => array(
                'assign', 'assignment', 'book', 'chat', 'choice', 'data', 'feedback', 'folder',
                'forum', 'glossary', 'imscp', 'label', 'lesson', 'lti', 'page',
                'quiz', 'resource', 'scorm', 'survey', 'url', 'wiki', 'workshop'
            ),

            'plagiarism' => array(
            ),

            'portfolio' => array(
                'boxnet', 'download', 'flickr', 'googledocs', 'mahara', 'picasa'
            ),

            'profilefield' => array(
                'checkbox', 'datetime', 'menu', 'text', 'textarea'
            ),

            'qbehaviour' => array(
                'adaptive', 'adaptivenopenalty', 'deferredcbm',
                'deferredfeedback', 'immediatecbm', 'immediatefeedback',
                'informationitem', 'interactive', 'interactivecountback',
                'manualgraded', 'missing'
            ),

            'qformat' => array(
                'aiken', 'blackboard_six', 'examview', 'gift',
                'missingword', 'multianswer', 'webct',
                'xhtml', 'xml'
            ),

            'qtype' => array(
                'calculated', 'calculatedmulti', 'calculatedsimple',
                'ddimageortext', 'ddmarker', 'ddwtos', 'description',
                'essay', 'gapselect', 'match', 'missingtype', 'multianswer',
                'multichoice', 'numerical', 'random', 'randomsamatch',
                'shortanswer', 'truefalse'
            ),

            'quiz' => array(
                'grading', 'overview', 'responses', 'statistics'
            ),

            'quizaccess' => array(
                'delaybetweenattempts', 'ipaddress', 'numattempts', 'openclosedate',
                'password', 'safebrowser', 'securewindow', 'timelimit'
            ),

            'report' => array(
                'backups', 'competency', 'completion', 'configlog', 'courseoverview', 'eventlist',
                'log', 'loglive', 'outline', 'participation', 'progress', 'questioninstances', 'search',
                'security', 'stats', 'performance', 'usersessions'
            ),

            'repository' => array(
                'alfresco', 'areafiles', 'boxnet', 'coursefiles', 'dropbox', 'equella', 'filesystem',
                'flickr', 'flickr_public', 'googledocs', 'local', 'merlot',
                'picasa', 'recent', 'skydrive', 's3', 'upload', 'url', 'user', 'webdav',
                'wikimedia', 'youtube'
            ),

            'search' => array(
                'solr'
            ),

            'scormreport' => array(
                'basic',
                'interactions',
                'graphs',
                'objectives'
            ),

            'tinymce' => array(
                'ctrlhelp', 'managefiles', 'moodleemoticon', 'moodleimage',
                'moodlemedia', 'moodlenolink', 'pdw', 'spellchecker', 'wrap'
            ),

            'theme' => array(
                'base', 'bootstrapbase', 'canvas', 'clean', 'more'
            ),

            'tool' => array(
                'assignmentupgrade', 'availabilityconditions', 'behat', 'capability', 'cohortroles', 'customlang',
                'dbtransfer', 'filetypes', 'generator', 'health', 'innodb', 'installaddon',
                'langimport', 'log', 'lp', 'lpmigrate', 'messageinbound', 'mobile', 'multilangupgrade', 'monitor',
                'phpunit', 'profiling', 'recyclebin', 'replace', 'spamcleaner', 'task', 'templatelibrary',
                'unittest', 'uploadcourse', 'uploaduser', 'unsuproles', 'xmldb'
            ),

            'webservice' => array(
                'rest', 'soap', 'xmlrpc'
            ),

            'workshopallocation' => array(
                'manual', 'random', 'scheduled'
            ),

            'workshopeval' => array(
                'best'
            ),

            'workshopform' => array(
                'accumulative', 'comments', 'numerrors', 'rubric'
            )
        );

        if (isset($standard_plugins[$type])) {
            return $standard_plugins[$type];
        } else {
            return false;
        }
    }

    
    public function remove_plugin_folder(\core\plugininfo\base $plugin) {

        if (!$this->is_plugin_folder_removable($plugin->component)) {
            throw new moodle_exception('err_removing_unremovable_folder', 'core_plugin', '',
                array('plugin' => $pluginfo->component, 'rootdir' => $pluginfo->rootdir),
                'plugin root folder is not removable as expected');
        }

        if ($plugin->get_status() === self::PLUGIN_STATUS_UPTODATE or $plugin->get_status() === self::PLUGIN_STATUS_NEW) {
            $this->archive_plugin_version($plugin);
        }

        remove_dir($plugin->rootdir);
        clearstatcache();
        if (function_exists('opcache_reset')) {
            opcache_reset();
        }
    }

    
    public function can_cancel_plugin_installation(\core\plugininfo\base $plugin) {
        global $CFG;

        if (!empty($CFG->disableupdateautodeploy)) {
            return false;
        }

        if (empty($plugin) or $plugin->is_standard() or $plugin->is_subplugin()
                or !$this->is_plugin_folder_removable($plugin->component)) {
            return false;
        }

        if ($plugin->get_status() === self::PLUGIN_STATUS_NEW) {
            return true;
        }

        return false;
    }

    
    public function can_cancel_plugin_upgrade(\core\plugininfo\base $plugin) {
        global $CFG;

        if (!empty($CFG->disableupdateautodeploy)) {
                                    return false;
        }

        if (empty($plugin) or $plugin->is_standard() or $plugin->is_subplugin()
                or !$this->is_plugin_folder_removable($plugin->component)) {
            return false;
        }

        if ($plugin->get_status() === self::PLUGIN_STATUS_UPGRADE) {
            if ($this->get_code_manager()->get_archived_plugin_version($plugin->component, $plugin->versiondb)) {
                return true;
            }
        }

        return false;
    }

    
    public function cancel_plugin_installation($component) {
        global $CFG;

        if (!empty($CFG->disableupdateautodeploy)) {
            return false;
        }

        $plugin = $this->get_plugin_info($component);

        if ($this->can_cancel_plugin_installation($plugin)) {
            $this->remove_plugin_folder($plugin);
        }

        return false;
    }

    
    public function list_cancellable_installations() {
        global $CFG;

        if (!empty($CFG->disableupdateautodeploy)) {
            return array();
        }

        $cancellable = array();
        foreach ($this->get_plugins() as $type => $plugins) {
            foreach ($plugins as $plugin) {
                if ($this->can_cancel_plugin_installation($plugin)) {
                    $cancellable[$plugin->component] = $plugin;
                }
            }
        }

        return $cancellable;
    }

    
    public function archive_plugin_version(\core\plugininfo\base $plugin) {
        return $this->get_code_manager()->archive_plugin_version($plugin->rootdir, $plugin->component, $plugin->versiondisk);
    }

    
    public function list_restorable_archives() {
        global $CFG;

        if (!empty($CFG->disableupdateautodeploy)) {
            return false;
        }

        $codeman = $this->get_code_manager();
        $restorable = array();
        foreach ($this->get_plugins() as $type => $plugins) {
            foreach ($plugins as $plugin) {
                if ($this->can_cancel_plugin_upgrade($plugin)) {
                    $restorable[$plugin->component] = (object)array(
                        'component' => $plugin->component,
                        'zipfilepath' => $codeman->get_archived_plugin_version($plugin->component, $plugin->versiondb)
                    );
                }
            }
        }

        return $restorable;
    }

    
    protected function reorder_plugin_types(array $types) {
        $fix = array('mod' => $types['mod']);
        foreach (core_component::get_plugin_list('mod') as $plugin => $fulldir) {
            if (!$subtypes = core_component::get_subplugins('mod_'.$plugin)) {
                continue;
            }
            foreach ($subtypes as $subtype => $ignored) {
                $fix[$subtype] = $types[$subtype];
            }
        }

        $fix['mod']        = $types['mod'];
        $fix['block']      = $types['block'];
        $fix['qtype']      = $types['qtype'];
        $fix['qbehaviour'] = $types['qbehaviour'];
        $fix['qformat']    = $types['qformat'];
        $fix['filter']     = $types['filter'];

        $fix['editor']     = $types['editor'];
        foreach (core_component::get_plugin_list('editor') as $plugin => $fulldir) {
            if (!$subtypes = core_component::get_subplugins('editor_'.$plugin)) {
                continue;
            }
            foreach ($subtypes as $subtype => $ignored) {
                $fix[$subtype] = $types[$subtype];
            }
        }

        $fix['enrol'] = $types['enrol'];
        $fix['auth']  = $types['auth'];
        $fix['tool']  = $types['tool'];
        foreach (core_component::get_plugin_list('tool') as $plugin => $fulldir) {
            if (!$subtypes = core_component::get_subplugins('tool_'.$plugin)) {
                continue;
            }
            foreach ($subtypes as $subtype => $ignored) {
                $fix[$subtype] = $types[$subtype];
            }
        }

        foreach ($types as $type => $path) {
            if (!isset($fix[$type])) {
                $fix[$type] = $path;
            }
        }
        return $fix;
    }

    
    public function is_directory_removable($fullpath) {

        if (!is_writable($fullpath)) {
            return false;
        }

        if (is_dir($fullpath)) {
            $handle = opendir($fullpath);
        } else {
            return false;
        }

        $result = true;

        while ($filename = readdir($handle)) {

            if ($filename === '.' or $filename === '..') {
                continue;
            }

            $subfilepath = $fullpath.'/'.$filename;

            if (is_dir($subfilepath)) {
                $result = $result && $this->is_directory_removable($subfilepath);

            } else {
                $result = $result && is_writable($subfilepath);
            }
        }

        closedir($handle);

        return $result;
    }

    
    protected function common_uninstall_check(\core\plugininfo\base $pluginfo) {

        if (!$pluginfo->is_uninstall_allowed()) {
                        return false;
        }

        if ($pluginfo->get_status() === static::PLUGIN_STATUS_NEW) {
                                    return false;
        }

        if (method_exists($pluginfo, 'get_uninstall_url') and is_null($pluginfo->get_uninstall_url())) {
                        debugging('\core\plugininfo\base subclasses should use is_uninstall_allowed() instead of returning null in get_uninstall_url()',
                DEBUG_DEVELOPER);
            return false;
        }

        return true;
    }

    
    protected function get_code_manager() {

        if ($this->codemanager === null) {
            $this->codemanager = new \core\update\code_manager();
        }

        return $this->codemanager;
    }

    
    protected function get_update_api_client() {

        if ($this->updateapiclient === null) {
            $this->updateapiclient = \core\update\api::client();
        }

        return $this->updateapiclient;
    }
}
