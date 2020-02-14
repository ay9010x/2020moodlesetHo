<?php



defined('MOODLE_INTERNAL') || die();



define('MATURITY_ALPHA',    50);

define('MATURITY_BETA',     100);

define('MATURITY_RC',       150);

define('MATURITY_STABLE',   200);

define('ANY_VERSION', 'any');



class core_component {
    
    protected static $ignoreddirs = array('CVS'=>true, '_vti_cnf'=>true, 'simpletest'=>true, 'db'=>true, 'yui'=>true, 'tests'=>true, 'classes'=>true, 'fonts'=>true);
    
    protected static $supportsubplugins = array('mod', 'editor', 'tool', 'local');

    
    protected static $plugintypes = null;
    
    protected static $plugins = null;
    
    protected static $subsystems = null;
    
    protected static $parents = null;
    
    protected static $subplugins = null;
    
    protected static $classmap = null;
    
    protected static $classmaprenames = null;
    
    protected static $filemap = null;
    
    protected static $version = null;
    
    protected static $filestomap = array('lib.php', 'settings.php');
    
    protected static $psrclassmap = null;

    
    public static function classloader($classname) {
        self::init();

        if (isset(self::$classmap[$classname])) {
                        global $CFG;
                        include_once(self::$classmap[$classname]);
            return;
        }
        if (isset(self::$classmaprenames[$classname]) && isset(self::$classmap[self::$classmaprenames[$classname]])) {
            $newclassname = self::$classmaprenames[$classname];
            $debugging = "Class '%s' has been renamed for the autoloader and is now deprecated. Please use '%s' instead.";
            debugging(sprintf($debugging, $classname, $newclassname), DEBUG_DEVELOPER);
            if (PHP_VERSION_ID >= 70000 && preg_match('#\\\null(\\\|$)#', $classname)) {
                throw new \coding_exception("Cannot alias $classname to $newclassname");
            }
            class_alias($newclassname, $classname);
            return;
        }

                $normalizedclassname = str_replace(array('/', '\\'), '_', $classname);
        if (isset(self::$psrclassmap[$normalizedclassname])) {
                        include_once(self::$psrclassmap[$normalizedclassname]);
            return;
        }
    }

    
    protected static function init() {
        global $CFG;

                if (isset(self::$plugintypes)) {
            return;
        }

        if (defined('IGNORE_COMPONENT_CACHE') and IGNORE_COMPONENT_CACHE) {
            self::fill_all_caches();
            return;
        }

        if (!empty($CFG->alternative_component_cache)) {
                        $cachefile = $CFG->alternative_component_cache;

            if (file_exists($cachefile)) {
                if (CACHE_DISABLE_ALL) {
                                        $content = self::get_cache_content();
                    if (sha1_file($cachefile) !== sha1($content)) {
                        die('Outdated component cache file defined in $CFG->alternative_component_cache, can not continue');
                    }
                    return;
                }
                $cache = array();
                include($cachefile);
                self::$plugintypes      = $cache['plugintypes'];
                self::$plugins          = $cache['plugins'];
                self::$subsystems       = $cache['subsystems'];
                self::$parents          = $cache['parents'];
                self::$subplugins       = $cache['subplugins'];
                self::$classmap         = $cache['classmap'];
                self::$classmaprenames  = $cache['classmaprenames'];
                self::$filemap          = $cache['filemap'];
                self::$psrclassmap      = $cache['psrclassmap'];
                return;
            }

            if (!is_writable(dirname($cachefile))) {
                die('Can not create alternative component cache file defined in $CFG->alternative_component_cache, can not continue');
            }

            
        } else {
                                    $cachefile = "$CFG->cachedir/core_component.php";
        }

        if (!CACHE_DISABLE_ALL and !self::is_developer()) {
                                    if (is_readable($cachefile)) {
                $cache = false;
                include($cachefile);
                if (!is_array($cache)) {
                                    } else if (!isset($cache['version'])) {
                                    } else if ((float) $cache['version'] !== (float) self::fetch_core_version()) {
                                        error_log('Resetting core_component cache after core upgrade to version ' . self::fetch_core_version());
                } else if ($cache['plugintypes']['mod'] !== "$CFG->dirroot/mod") {
                                    } else {
                                        self::$plugintypes      = $cache['plugintypes'];
                    self::$plugins          = $cache['plugins'];
                    self::$subsystems       = $cache['subsystems'];
                    self::$parents          = $cache['parents'];
                    self::$subplugins       = $cache['subplugins'];
                    self::$classmap         = $cache['classmap'];
                    self::$classmaprenames  = $cache['classmaprenames'];
                    self::$filemap          = $cache['filemap'];
                    self::$psrclassmap      = $cache['psrclassmap'];
                    return;
                }
                                            }
        }

        if (!isset(self::$plugintypes)) {
            
            $content = self::get_cache_content();
            if (file_exists($cachefile)) {
                if (sha1_file($cachefile) === sha1($content)) {
                    return;
                }
                                unlink($cachefile);
            }

                        $dirpermissions = !isset($CFG->directorypermissions) ? 02777 : $CFG->directorypermissions;
            $filepermissions = !isset($CFG->filepermissions) ? ($dirpermissions & 0666) : $CFG->filepermissions;

            clearstatcache();
            $cachedir = dirname($cachefile);
            if (!is_dir($cachedir)) {
                mkdir($cachedir, $dirpermissions, true);
            }

            if ($fp = @fopen($cachefile.'.tmp', 'xb')) {
                fwrite($fp, $content);
                fclose($fp);
                @rename($cachefile.'.tmp', $cachefile);
                @chmod($cachefile, $filepermissions);
            }
            @unlink($cachefile.'.tmp');             self::invalidate_opcode_php_cache($cachefile);
        }
    }

    
    protected static function is_developer() {
        global $CFG;

                if (isset($CFG->config_php_settings['debug'])) {
            $debug = (int)$CFG->config_php_settings['debug'];
        } else {
            return false;
        }

        if ($debug & E_ALL and $debug & E_STRICT) {
            return true;
        }

        return false;
    }

    
    public static function get_cache_content() {
        if (!isset(self::$plugintypes)) {
            self::fill_all_caches();
        }

        $cache = array(
            'subsystems'        => self::$subsystems,
            'plugintypes'       => self::$plugintypes,
            'plugins'           => self::$plugins,
            'parents'           => self::$parents,
            'subplugins'        => self::$subplugins,
            'classmap'          => self::$classmap,
            'classmaprenames'   => self::$classmaprenames,
            'filemap'           => self::$filemap,
            'version'           => self::$version,
            'psrclassmap'       => self::$psrclassmap,
        );

        return '<?php
$cache = '.var_export($cache, true).';
';
    }

    
    protected static function fill_all_caches() {
        self::$subsystems = self::fetch_subsystems();

        list(self::$plugintypes, self::$parents, self::$subplugins) = self::fetch_plugintypes();

        self::$plugins = array();
        foreach (self::$plugintypes as $type => $fulldir) {
            self::$plugins[$type] = self::fetch_plugins($type, $fulldir);
        }

        self::fill_classmap_cache();
        self::fill_classmap_renames_cache();
        self::fill_filemap_cache();
        self::fill_psr_cache();
        self::fetch_core_version();
    }

    
    protected static function fetch_core_version() {
        global $CFG;
        if (self::$version === null) {
            $version = null;             require($CFG->dirroot . '/version.php');
            self::$version = $version;
        }
        return self::$version;
    }

    
    protected static function fetch_subsystems() {
        global $CFG;

        
        $info = array(
            'access'      => null,
            'admin'       => $CFG->dirroot.'/'.$CFG->admin,
            'antivirus'   => $CFG->dirroot . '/lib/antivirus',
            'auth'        => $CFG->dirroot.'/auth',
            'availability' => $CFG->dirroot . '/availability',
            'backup'      => $CFG->dirroot.'/backup/util/ui',
            'badges'      => $CFG->dirroot.'/badges',
            'block'       => $CFG->dirroot.'/blocks',
            'blog'        => $CFG->dirroot.'/blog',
            'bulkusers'   => null,
            'cache'       => $CFG->dirroot.'/cache',
            'calendar'    => $CFG->dirroot.'/calendar',
            'cohort'      => $CFG->dirroot.'/cohort',
            'comment'     => $CFG->dirroot.'/comment',
            'competency'  => $CFG->dirroot.'/competency',
            'completion'  => $CFG->dirroot.'/completion',
            'countries'   => null,
            'course'      => $CFG->dirroot.'/course',
            'currencies'  => null,
            'dbtransfer'  => null,
            'debug'       => null,
            'editor'      => $CFG->dirroot.'/lib/editor',
            'edufields'   => null,
            'enrol'       => $CFG->dirroot.'/enrol',
            'error'       => null,
            'filepicker'  => null,
            'files'       => $CFG->dirroot.'/files',
            'filters'     => null,
                        'form'        => $CFG->dirroot.'/lib/form',
            'grades'      => $CFG->dirroot.'/grade',
            'grading'     => $CFG->dirroot.'/grade/grading',
            'group'       => $CFG->dirroot.'/group',
            'help'        => null,
            'hub'         => null,
            'imscc'       => null,
            'install'     => null,
            'iso6392'     => null,
            'langconfig'  => null,
            'license'     => null,
            'mathslib'    => null,
            'media'       => null,
            'message'     => $CFG->dirroot.'/message',
            'mimetypes'   => null,
            'mnet'        => $CFG->dirroot.'/mnet',
                        'my'          => $CFG->dirroot.'/my',
            'notes'       => $CFG->dirroot.'/notes',
            'pagetype'    => null,
            'pix'         => null,
            'plagiarism'  => $CFG->dirroot.'/plagiarism',
            'plugin'      => null,
            'portfolio'   => $CFG->dirroot.'/portfolio',
            'publish'     => $CFG->dirroot.'/course/publish',
            'question'    => $CFG->dirroot.'/question',
            'rating'      => $CFG->dirroot.'/rating',
            'register'    => $CFG->dirroot.'/'.$CFG->admin.'/registration',             'repository'  => $CFG->dirroot.'/repository',
            'rss'         => $CFG->dirroot.'/rss',
            'role'        => $CFG->dirroot.'/'.$CFG->admin.'/roles',
            'search'      => $CFG->dirroot.'/search',
            'table'       => null,
            'tag'         => $CFG->dirroot.'/tag',
            'timezones'   => null,
            'user'        => $CFG->dirroot.'/user',
            'userkey'     => null,
            'webservice'  => $CFG->dirroot.'/webservice',
        );

        return $info;
    }

    
    protected static function fetch_plugintypes() {
        global $CFG;

        $types = array(
            'antivirus'     => $CFG->dirroot . '/lib/antivirus',
            'availability'  => $CFG->dirroot . '/availability/condition',
            'qtype'         => $CFG->dirroot.'/question/type',
            'mod'           => $CFG->dirroot.'/mod',
            'auth'          => $CFG->dirroot.'/auth',
            'calendartype'  => $CFG->dirroot.'/calendar/type',
            'enrol'         => $CFG->dirroot.'/enrol',
            'message'       => $CFG->dirroot.'/message/output',
            'block'         => $CFG->dirroot.'/blocks',
            'filter'        => $CFG->dirroot.'/filter',
            'editor'        => $CFG->dirroot.'/lib/editor',
            'format'        => $CFG->dirroot.'/course/format',
            'dataformat'    => $CFG->dirroot.'/dataformat',
            'profilefield'  => $CFG->dirroot.'/user/profile/field',
            'report'        => $CFG->dirroot.'/report',
            'coursereport'  => $CFG->dirroot.'/course/report',             'gradeexport'   => $CFG->dirroot.'/grade/export',
            'gradeimport'   => $CFG->dirroot.'/grade/import',
            'gradereport'   => $CFG->dirroot.'/grade/report',
            'gradingform'   => $CFG->dirroot.'/grade/grading/form',
            'mnetservice'   => $CFG->dirroot.'/mnet/service',
            'webservice'    => $CFG->dirroot.'/webservice',
            'repository'    => $CFG->dirroot.'/repository',
            'portfolio'     => $CFG->dirroot.'/portfolio',
            'search'        => $CFG->dirroot.'/search/engine',
            'qbehaviour'    => $CFG->dirroot.'/question/behaviour',
            'qformat'       => $CFG->dirroot.'/question/format',
            'plagiarism'    => $CFG->dirroot.'/plagiarism',
            'tool'          => $CFG->dirroot.'/'.$CFG->admin.'/tool',
            'cachestore'    => $CFG->dirroot.'/cache/stores',
            'cachelock'     => $CFG->dirroot.'/cache/locks',
        );
        $parents = array();
        $subplugins = array();

        if (!empty($CFG->themedir) and is_dir($CFG->themedir) ) {
            $types['theme'] = $CFG->themedir;
        } else {
            $types['theme'] = $CFG->dirroot.'/theme';
        }

        foreach (self::$supportsubplugins as $type) {
            if ($type === 'local') {
                                continue;
            }
            $plugins = self::fetch_plugins($type, $types[$type]);
            foreach ($plugins as $plugin => $fulldir) {
                $subtypes = self::fetch_subtypes($fulldir);
                if (!$subtypes) {
                    continue;
                }
                $subplugins[$type.'_'.$plugin] = array();
                foreach($subtypes as $subtype => $subdir) {
                    if (isset($types[$subtype])) {
                        error_log("Invalid subtype '$subtype', duplicate detected.");
                        continue;
                    }
                    $types[$subtype] = $subdir;
                    $parents[$subtype] = $type.'_'.$plugin;
                    $subplugins[$type.'_'.$plugin][$subtype] = array_keys(self::fetch_plugins($subtype, $subdir));
                }
            }
        }
                $types['local'] = $CFG->dirroot.'/local';

        if (in_array('local', self::$supportsubplugins)) {
            $type = 'local';
            $plugins = self::fetch_plugins($type, $types[$type]);
            foreach ($plugins as $plugin => $fulldir) {
                $subtypes = self::fetch_subtypes($fulldir);
                if (!$subtypes) {
                    continue;
                }
                $subplugins[$type.'_'.$plugin] = array();
                foreach($subtypes as $subtype => $subdir) {
                    if (isset($types[$subtype])) {
                        error_log("Invalid subtype '$subtype', duplicate detected.");
                        continue;
                    }
                    $types[$subtype] = $subdir;
                    $parents[$subtype] = $type.'_'.$plugin;
                    $subplugins[$type.'_'.$plugin][$subtype] = array_keys(self::fetch_plugins($subtype, $subdir));
                }
            }
        }

        return array($types, $parents, $subplugins);
    }

    
    protected static function fetch_subtypes($ownerdir) {
        global $CFG;

        $types = array();
        if (file_exists("$ownerdir/db/subplugins.php")) {
            $subplugins = array();
            include("$ownerdir/db/subplugins.php");
            foreach ($subplugins as $subtype => $dir) {
                if (!preg_match('/^[a-z][a-z0-9]*$/', $subtype)) {
                    error_log("Invalid subtype '$subtype'' detected in '$ownerdir', invalid characters present.");
                    continue;
                }
                if (isset(self::$subsystems[$subtype])) {
                    error_log("Invalid subtype '$subtype'' detected in '$ownerdir', duplicates core subsystem.");
                    continue;
                }
                if ($CFG->admin !== 'admin' and strpos($dir, 'admin/') === 0) {
                    $dir = preg_replace('|^admin/|', "$CFG->admin/", $dir);
                }
                if (!is_dir("$CFG->dirroot/$dir")) {
                    error_log("Invalid subtype directory '$dir' detected in '$ownerdir'.");
                    continue;
                }
                $types[$subtype] = "$CFG->dirroot/$dir";
            }
        }
        return $types;
    }

    
    protected static function fetch_plugins($plugintype, $fulldir) {
        global $CFG;

        $fulldirs = (array)$fulldir;
        if ($plugintype === 'theme') {
            if (realpath($fulldir) !== realpath($CFG->dirroot.'/theme')) {
                                array_unshift($fulldirs, $CFG->dirroot.'/theme');
            }
        }

        $result = array();

        foreach ($fulldirs as $fulldir) {
            if (!is_dir($fulldir)) {
                continue;
            }
            $items = new \DirectoryIterator($fulldir);
            foreach ($items as $item) {
                if ($item->isDot() or !$item->isDir()) {
                    continue;
                }
                $pluginname = $item->getFilename();
                if ($plugintype === 'auth' and $pluginname === 'db') {
                                    } else if (isset(self::$ignoreddirs[$pluginname])) {
                    continue;
                }
                if (!self::is_valid_plugin_name($plugintype, $pluginname)) {
                                        continue;
                }
                $result[$pluginname] = $fulldir.'/'.$pluginname;
                unset($item);
            }
            unset($items);
        }

        ksort($result);
        return $result;
    }

    
    protected static function fill_classmap_cache() {
        global $CFG;

        self::$classmap = array();

        self::load_classes('core', "$CFG->dirroot/lib/classes");

        foreach (self::$subsystems as $subsystem => $fulldir) {
            if (!$fulldir) {
                continue;
            }
            self::load_classes('core_'.$subsystem, "$fulldir/classes");
        }

        foreach (self::$plugins as $plugintype => $plugins) {
            foreach ($plugins as $pluginname => $fulldir) {
                self::load_classes($plugintype.'_'.$pluginname, "$fulldir/classes");
            }
        }
        ksort(self::$classmap);
    }

    
    protected static function fill_filemap_cache() {
        global $CFG;

        self::$filemap = array();

        foreach (self::$filestomap as $file) {
            if (!isset(self::$filemap[$file])) {
                self::$filemap[$file] = array();
            }
            foreach (self::$plugins as $plugintype => $plugins) {
                if (!isset(self::$filemap[$file][$plugintype])) {
                    self::$filemap[$file][$plugintype] = array();
                }
                foreach ($plugins as $pluginname => $fulldir) {
                    if (file_exists("$fulldir/$file")) {
                        self::$filemap[$file][$plugintype][$pluginname] = "$fulldir/$file";
                    }
                }
            }
        }
    }

    
    protected static function load_classes($component, $fulldir, $namespace = '') {
        if (!is_dir($fulldir)) {
            return;
        }

        if (!is_readable($fulldir)) {
                                                return;
        }

        $items = new \DirectoryIterator($fulldir);
        foreach ($items as $item) {
            if ($item->isDot()) {
                continue;
            }
            if ($item->isDir()) {
                $dirname = $item->getFilename();
                self::load_classes($component, "$fulldir/$dirname", $namespace.'\\'.$dirname);
                continue;
            }

            $filename = $item->getFilename();
            $classname = preg_replace('/\.php$/', '', $filename);

            if ($filename === $classname) {
                                continue;
            }
            if ($namespace === '') {
                                self::$classmap[$component.'_'.$classname] = "$fulldir/$filename";
            }
                        self::$classmap[$component.$namespace.'\\'.$classname] = "$fulldir/$filename";
        }
        unset($item);
        unset($items);
    }

    
    protected static function fill_psr_cache() {
        global $CFG;

        $psrsystems = array(
            'Horde' => 'horde/framework',
        );
        self::$psrclassmap = array();

        foreach ($psrsystems as $system => $fulldir) {
            if (!$fulldir) {
                continue;
            }
            self::load_psr_classes($CFG->libdir . DIRECTORY_SEPARATOR . $fulldir);
        }
    }

    
    protected static function load_psr_classes($basedir, $subdir = null) {
        if ($subdir) {
            $fulldir = realpath($basedir . DIRECTORY_SEPARATOR . $subdir);
            $classnameprefix = preg_replace('#' . preg_quote(DIRECTORY_SEPARATOR) . '#', '_', $subdir);
        } else {
            $fulldir = $basedir;
        }
        if (!$fulldir || !is_dir($fulldir)) {
            return;
        }

        $items = new \DirectoryIterator($fulldir);
        foreach ($items as $item) {
            if ($item->isDot()) {
                continue;
            }
            if ($item->isDir()) {
                $dirname = $item->getFilename();
                $newsubdir = $dirname;
                if ($subdir) {
                    $newsubdir = implode(DIRECTORY_SEPARATOR, array($subdir, $dirname));
                }
                self::load_psr_classes($basedir, $newsubdir);
                continue;
            }

            $filename = $item->getFilename();
            $classname = preg_replace('/\.php$/', '', $filename);

            if ($filename === $classname) {
                                continue;
            }

            if ($classnameprefix) {
                $classname = $classnameprefix . '_' . $classname;
            }

            self::$psrclassmap[$classname] = $fulldir . DIRECTORY_SEPARATOR . $filename;
        }
        unset($item);
        unset($items);
    }

    
    public static function get_core_subsystems() {
        self::init();
        return self::$subsystems;
    }

    
    public static function get_plugin_types() {
        self::init();
        return self::$plugintypes;
    }

    
    public static function get_plugin_list($plugintype) {
        self::init();

        if (!isset(self::$plugins[$plugintype])) {
            return array();
        }
        return self::$plugins[$plugintype];
    }

    
    public static function get_plugin_list_with_class($plugintype, $class, $file = null) {
        global $CFG; 
        if ($class) {
            $suffix = '_' . $class;
        } else {
            $suffix = '';
        }

        $pluginclasses = array();
        $plugins = self::get_plugin_list($plugintype);
        foreach ($plugins as $plugin => $fulldir) {
                        if ($class) {
                $classname = '\\' . $plugintype . '_' . $plugin . '\\' . $class;
                if (class_exists($classname, true)) {
                    $pluginclasses[$plugintype . '_' . $plugin] = $classname;
                    continue;
                }
            }

                        $classname = $plugintype . '_' . $plugin . $suffix;
            if (class_exists($classname, true)) {
                $pluginclasses[$plugintype . '_' . $plugin] = $classname;
                continue;
            }

                        if ($file and file_exists("$fulldir/$file")) {
                include_once("$fulldir/$file");
                if (class_exists($classname, false)) {
                    $pluginclasses[$plugintype . '_' . $plugin] = $classname;
                    continue;
                }
            }
        }

        return $pluginclasses;
    }

    
    public static function get_plugin_list_with_file($plugintype, $file, $include = false) {
        global $CFG;         $pluginfiles = array();

        if (isset(self::$filemap[$file])) {
                        if (isset(self::$filemap[$file][$plugintype])) {
                $pluginfiles = self::$filemap[$file][$plugintype];
            }
        } else {
                        $plugins = self::get_plugin_list($plugintype);
            foreach ($plugins as $plugin => $fulldir) {
                $path = $fulldir . '/' . $file;
                if (file_exists($path)) {
                    $pluginfiles[$plugin] = $path;
                }
            }
        }

        if ($include) {
            foreach ($pluginfiles as $path) {
                include_once($path);
            }
        }

        return $pluginfiles;
    }

    
    public static function get_component_classes_in_namespace($component, $namespace = '') {

        $component = self::normalize_componentname($component);

        if ($namespace) {

                        $namespace = trim($namespace, '\\');

                        $namespace = implode('\\\\', explode('\\', $namespace));
            $namespace = $namespace . '\\\\';
        }

        $regex = '|^' . $component . '\\\\' . $namespace . '|';
        $it = new RegexIterator(new ArrayIterator(self::$classmap), $regex, RegexIterator::GET_MATCH, RegexIterator::USE_KEY);

                $classes = array();
        foreach ($it as $classname => $classpath) {
            if (class_exists($classname)) {
                $classes[$classname] = $classpath;
            }
        }

        return $classes;
    }

    
    public static function get_plugin_directory($plugintype, $pluginname) {
        if (empty($pluginname)) {
                        return null;
        }

        self::init();

        if (!isset(self::$plugins[$plugintype][$pluginname])) {
            return null;
        }
        return self::$plugins[$plugintype][$pluginname];
    }

    
    public static function get_subsystem_directory($subsystem) {
        self::init();

        if (!isset(self::$subsystems[$subsystem])) {
            return null;
        }
        return self::$subsystems[$subsystem];
    }

    
    public static function is_valid_plugin_name($plugintype, $pluginname) {
        if ($plugintype === 'mod') {
                        if (!isset(self::$subsystems)) {
                                self::init();
            }
            if (isset(self::$subsystems[$pluginname])) {
                return false;
            }
                                    return (bool)preg_match('/^[a-z][a-z0-9]*$/', $pluginname);

        } else {
            return (bool)preg_match('/^[a-z](?:[a-z0-9_](?!__))*[a-z0-9]+$/', $pluginname);
        }
    }

    
    public static function normalize_componentname($componentname) {
        list($plugintype, $pluginname) = self::normalize_component($componentname);
        if ($plugintype === 'core' && is_null($pluginname)) {
            return $plugintype;
        }
        return $plugintype . '_' . $pluginname;
    }

    
    public static function normalize_component($component) {
        if ($component === 'moodle' or $component === 'core' or $component === '') {
            return array('core', null);
        }

        if (strpos($component, '_') === false) {
            self::init();
            if (array_key_exists($component, self::$subsystems)) {
                $type   = 'core';
                $plugin = $component;
            } else {
                                $type   = 'mod';
                $plugin = $component;
            }

        } else {
            list($type, $plugin) = explode('_', $component, 2);
            if ($type === 'moodle') {
                $type = 'core';
            }
                    }

        return array($type, $plugin);
    }

    
    public static function get_component_directory($component) {
        global $CFG;

        list($type, $plugin) = self::normalize_component($component);

        if ($type === 'core') {
            if ($plugin === null) {
                return $path = $CFG->libdir;
            }
            return self::get_subsystem_directory($plugin);
        }

        return self::get_plugin_directory($type, $plugin);
    }

    
    public static function get_plugin_types_with_subplugins() {
        self::init();

        $return = array();
        foreach (self::$supportsubplugins as $type) {
            $return[$type] = self::$plugintypes[$type];
        }
        return $return;
    }

    
    public static function get_subtype_parent($type) {
        self::init();

        if (isset(self::$parents[$type])) {
            return self::$parents[$type];
        }

        return null;
    }

    
    public static function get_subplugins($component) {
        self::init();

        if (isset(self::$subplugins[$component])) {
            return self::$subplugins[$component];
        }

        return null;
    }

    
    public static function get_all_versions_hash() {
        global $CFG;

        self::init();

        $versions = array();

                $versions['core'] = self::fetch_core_version();

                        $usecache = false;
        if (CACHE_DISABLE_ALL or (defined('IGNORE_COMPONENT_CACHE') and IGNORE_COMPONENT_CACHE)) {
            $usecache = true;
        }

                $plugintypes = core_component::get_plugin_types();
        foreach ($plugintypes as $type => $typedir) {
            if ($usecache) {
                $plugs = core_component::get_plugin_list($type);
            } else {
                $plugs = self::fetch_plugins($type, $typedir);
            }
            foreach ($plugs as $plug => $fullplug) {
                $plugin = new stdClass();
                $plugin->version = null;
                $module = $plugin;
                include($fullplug.'/version.php');
                $versions[$type.'_'.$plug] = $plugin->version;
            }
        }

        return sha1(serialize($versions));
    }

    
    public static function invalidate_opcode_php_cache($file) {
        if (function_exists('opcache_invalidate')) {
            if (!file_exists($file)) {
                return;
            }
            opcache_invalidate($file, true);
        }
    }

    
    public static function is_core_subsystem($subsystemname) {
        return isset(self::$subsystems[$subsystemname]);
    }

    
    protected static function fill_classmap_renames_cache() {
        global $CFG;

        self::$classmaprenames = array();

        self::load_renamed_classes("$CFG->dirroot/lib/");

        foreach (self::$subsystems as $subsystem => $fulldir) {
            self::load_renamed_classes($fulldir);
        }

        foreach (self::$plugins as $plugintype => $plugins) {
            foreach ($plugins as $pluginname => $fulldir) {
                self::load_renamed_classes($fulldir);
            }
        }
    }

    
    protected static function load_renamed_classes($fulldir) {
        $file = $fulldir . '/db/renamedclasses.php';
        if (is_readable($file)) {
            $renamedclasses = null;
            require($file);
            if (is_array($renamedclasses)) {
                foreach ($renamedclasses as $oldclass => $newclass) {
                    self::$classmaprenames[(string)$oldclass] = (string)$newclass;
                }
            }
        }
    }
}
