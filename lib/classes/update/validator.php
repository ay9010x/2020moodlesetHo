<?php



namespace core\update;

use core_component;
use core_plugin_manager;
use help_icon;
use coding_exception;

defined('MOODLE_INTERNAL') || die();

if (!defined('T_ML_COMMENT')) {
    define('T_ML_COMMENT', T_COMMENT);
} else {
    define('T_DOC_COMMENT', T_ML_COMMENT);
}


class validator {

    
    const ERROR     = 'error';

    
    const WARNING   = 'warning';

    
    const INFO      = 'info';

    
    const DEBUG     = 'debug';

    
    protected $extractdir = null;

    
    protected $extractfiles = null;

    
    protected $result = null;

    
    protected $rootdir = null;

    
    protected $assertions = null;

    
    protected $messages = array();

    
    protected $versionphp = null;

    
    protected $langfilename = null;

    
    public static function instance($zipcontentpath, array $zipcontentfiles) {
        return new static($zipcontentpath, $zipcontentfiles);
    }

    
    public function assert_plugin_type($required) {
        $this->assertions['plugintype'] = $required;
    }

    
    public function assert_moodle_version($required) {
        $this->assertions['moodleversion'] = $required;
    }

    
    public function execute() {

        $this->result = (
                $this->validate_files_layout()
            and $this->validate_version_php()
            and $this->validate_language_pack()
            and $this->validate_target_location()
        );

        return $this->result;
    }

    
    public function get_result() {
        return $this->result;
    }

    
    public function get_messages() {
        return $this->messages;
    }

    
    public function message_level_name($level) {
        return get_string('validationmsglevel_'.$level, 'core_plugin');
    }

    
    public function message_code_name($msgcode) {

        $stringman = get_string_manager();

        if ($stringman->string_exists('validationmsg_'.$msgcode, 'core_plugin')) {
            return get_string('validationmsg_'.$msgcode, 'core_plugin');
        }

        return $msgcode;
    }

    
    public function message_help_icon($msgcode) {

        $stringman = get_string_manager();

        if ($stringman->string_exists('validationmsg_'.$msgcode.'_help', 'core_plugin')) {
            return new help_icon('validationmsg_'.$msgcode, 'core_plugin');
        }

        return false;
    }

    
    public function message_code_info($msgcode, $addinfo) {

        $stringman = get_string_manager();

        if ($addinfo !== null and $stringman->string_exists('validationmsg_'.$msgcode.'_info', 'core_plugin')) {
            return get_string('validationmsg_'.$msgcode.'_info', 'core_plugin', $addinfo);
        }

        return '';
    }

    
    public function get_versionphp_info() {
        return $this->versionphp;
    }

    
    public function get_language_file_name() {
        return $this->langfilename;
    }

    
    public function get_rootdir() {
        return $this->rootdir;
    }

    
    
    protected function __construct($zipcontentpath, array $zipcontentfiles) {
        $this->extractdir = $zipcontentpath;
        $this->extractfiles = $zipcontentfiles;
    }

    
    
    protected function validate_files_layout() {

        if (!is_array($this->extractfiles) or count($this->extractfiles) < 4) {
                        $this->add_message(self::ERROR, 'filesnumber');
            return false;
        }

        foreach ($this->extractfiles as $filerelname => $filestatus) {
            if ($filestatus !== true) {
                $this->add_message(self::ERROR, 'filestatus', array('file' => $filerelname, 'status' => $filestatus));
                return false;
            }
        }

        foreach (array_keys($this->extractfiles) as $filerelname) {
            if (!file_exists($this->extractdir.'/'.$filerelname)) {
                $this->add_message(self::ERROR, 'filenotexists', array('file' => $filerelname));
                return false;
            }
        }

        foreach (array_keys($this->extractfiles) as $filerelname) {
            $matches = array();
            if (!preg_match("#^([^/]+)/#", $filerelname, $matches)
                    or (!is_null($this->rootdir) and $this->rootdir !== $matches[1])) {
                $this->add_message(self::ERROR, 'onedir');
                return false;
            }
            $this->rootdir = $matches[1];
        }

        if ($this->rootdir !== clean_param($this->rootdir, PARAM_PLUGIN)) {
            $this->add_message(self::ERROR, 'rootdirinvalid', $this->rootdir);
            return false;
        } else {
            $this->add_message(self::INFO, 'rootdir', $this->rootdir);
        }

        return is_dir($this->extractdir.'/'.$this->rootdir);
    }

    
    protected function validate_version_php() {

        if (!isset($this->assertions['plugintype'])) {
            throw new coding_exception('Required plugin type must be set before calling this');
        }

        if (!isset($this->assertions['moodleversion'])) {
            throw new coding_exception('Required Moodle version must be set before calling this');
        }

        $fullpath = $this->extractdir.'/'.$this->rootdir.'/version.php';

        if (!file_exists($fullpath)) {
                        if ($this->assertions['plugintype'] === 'theme') {
                $this->add_message(self::DEBUG, 'missingversionphp');
                return true;
            } else {
                $this->add_message(self::ERROR, 'missingversionphp');
                return false;
            }
        }

        $this->versionphp = array();
        $info = $this->parse_version_php($fullpath);

        if (isset($info['module->version'])) {
            $this->add_message(self::ERROR, 'versionphpsyntax', '$module');
            return false;
        }

        if (isset($info['plugin->version'])) {
            $this->versionphp['version'] = $info['plugin->version'];
            $this->add_message(self::INFO, 'pluginversion', $this->versionphp['version']);
        } else {
            $this->add_message(self::ERROR, 'missingversion');
            return false;
        }

        if (isset($info['plugin->requires'])) {
            $this->versionphp['requires'] = $info['plugin->requires'];
            if ($this->versionphp['requires'] > $this->assertions['moodleversion']) {
                $this->add_message(self::ERROR, 'requiresmoodle', $this->versionphp['requires']);
                return false;
            }
            $this->add_message(self::INFO, 'requiresmoodle', $this->versionphp['requires']);
        }

        if (!isset($info['plugin->component'])) {
            $this->add_message(self::ERROR, 'missingcomponent');
            return false;
        }

        $this->versionphp['component'] = $info['plugin->component'];
        list($reqtype, $reqname) = core_component::normalize_component($this->versionphp['component']);
        if ($reqtype !== $this->assertions['plugintype']) {
            $this->add_message(self::ERROR, 'componentmismatchtype', array(
                'expected' => $this->assertions['plugintype'],
                'found' => $reqtype));
            return false;
        }
        if ($reqname !== $this->rootdir) {
            $this->add_message(self::ERROR, 'componentmismatchname', $reqname);
            return false;
        }
        $this->add_message(self::INFO, 'componentmatch', $this->versionphp['component']);

        if (isset($info['plugin->maturity'])) {
            $this->versionphp['maturity'] = $info['plugin->maturity'];
            if ($this->versionphp['maturity'] === 'MATURITY_STABLE') {
                $this->add_message(self::INFO, 'maturity', $this->versionphp['maturity']);
            } else {
                $this->add_message(self::WARNING, 'maturity', $this->versionphp['maturity']);
            }
        }

        if (isset($info['plugin->release'])) {
            $this->versionphp['release'] = $info['plugin->release'];
            $this->add_message(self::INFO, 'release', $this->versionphp['release']);
        }

        return true;
    }

    
    protected function validate_language_pack() {

        if (!isset($this->assertions['plugintype'])) {
            throw new coding_exception('Required plugin type must be set before calling this');
        }

        if (!isset($this->extractfiles[$this->rootdir.'/lang/en/'])
                or $this->extractfiles[$this->rootdir.'/lang/en/'] !== true
                or !is_dir($this->extractdir.'/'.$this->rootdir.'/lang/en')) {
            $this->add_message(self::ERROR, 'missinglangenfolder');
            return false;
        }

        $langfiles = array();
        foreach (array_keys($this->extractfiles) as $extractfile) {
            $matches = array();
            if (preg_match('#^'.preg_quote($this->rootdir).'/lang/en/([^/]+).php?$#i', $extractfile, $matches)) {
                $langfiles[] = $matches[1];
            }
        }

        if (empty($langfiles)) {
            $this->add_message(self::ERROR, 'missinglangenfile');
            return false;
        } else if (count($langfiles) > 1) {
            $this->add_message(self::WARNING, 'multiplelangenfiles');
        } else {
            $this->langfilename = $langfiles[0];
            $this->add_message(self::DEBUG, 'foundlangfile', $this->langfilename);
        }

        if ($this->assertions['plugintype'] === 'mod') {
            $expected = $this->rootdir.'.php';
        } else {
            $expected = $this->assertions['plugintype'].'_'.$this->rootdir.'.php';
        }

        if (!isset($this->extractfiles[$this->rootdir.'/lang/en/'.$expected])
                or $this->extractfiles[$this->rootdir.'/lang/en/'.$expected] !== true
                or !is_file($this->extractdir.'/'.$this->rootdir.'/lang/en/'.$expected)) {
            $this->add_message(self::ERROR, 'missingexpectedlangenfile', $expected);
            return false;
        }

        return true;
    }

    
    public function validate_target_location() {

        if (!isset($this->assertions['plugintype'])) {
            throw new coding_exception('Required plugin type must be set before calling this');
        }

        $plugintypepath = $this->get_plugintype_location($this->assertions['plugintype']);

        if (is_null($plugintypepath)) {
            $this->add_message(self::ERROR, 'unknowntype', $this->assertions['plugintype']);
            return false;
        }

        if (!is_dir($plugintypepath)) {
            throw new coding_exception('Plugin type location does not exist!');
        }

                if (!is_writable($plugintypepath)) {
            $this->add_message(self::ERROR, 'pathwritable', $plugintypepath);
            return false;
        } else {
            $this->add_message(self::INFO, 'pathwritable', $plugintypepath);
        }

                                        $target = $plugintypepath.'/'.$this->rootdir;
        if (file_exists($target)) {
            if (!is_dir($target)) {
                $this->add_message(self::ERROR, 'targetnotdir', $target);
                return false;
            }
            $this->add_message(self::WARNING, 'targetexists', $target);
            if ($this->get_plugin_manager()->is_directory_removable($target)) {
                $this->add_message(self::INFO, 'pathwritable', $target);
            } else {
                $this->add_message(self::ERROR, 'pathwritable', $target);
                return false;
            }
        }

        return true;
    }

    
    
    protected function parse_version_php($fullpath) {

        $content = $this->get_stripped_file_contents($fullpath);

        preg_match_all('#\$((plugin|module)\->(version|maturity|release|requires))=()(\d+(\.\d+)?);#m', $content, $matches1);
        preg_match_all('#\$((plugin|module)\->(maturity))=()(MATURITY_\w+);#m', $content, $matches2);
        preg_match_all('#\$((plugin|module)\->(release))=([\'"])(.*?)\4;#m', $content, $matches3);
        preg_match_all('#\$((plugin|module)\->(component))=([\'"])(.+?_.+?)\4;#m', $content, $matches4);

        if (count($matches1[1]) + count($matches2[1]) + count($matches3[1]) + count($matches4[1])) {
            $info = array_combine(
                array_merge($matches1[1], $matches2[1], $matches3[1], $matches4[1]),
                array_merge($matches1[5], $matches2[5], $matches3[5], $matches4[5])
            );

        } else {
            $info = array();
        }

        return $info;
    }

    
    protected function add_message($level, $msgcode, $a = null) {
        $msg = (object)array(
            'level'     => $level,
            'msgcode'   => $msgcode,
            'addinfo'   => $a,
        );
        $this->messages[] = $msg;
    }

    
    protected function get_stripped_file_contents($fullpath) {

        $source = file_get_contents($fullpath);
        $tokens = token_get_all($source);
        $output = '';
        $doprocess = false;
        foreach ($tokens as $token) {
            if (is_string($token)) {
                                $id = -1;
                $text = $token;
            } else {
                                list($id, $text) = $token;
            }
            switch ($id) {
                case T_WHITESPACE:
                case T_COMMENT:
                case T_ML_COMMENT:
                case T_DOC_COMMENT:
                                        break;
                case T_OPEN_TAG:
                                        $doprocess = true;
                    break;
                case T_CLOSE_TAG:
                                        $doprocess = false;
                    break;
                default:
                                        if ($doprocess) {
                        $output .= $text;
                        if ($text === 'function') {
                                                        $output .= ' ';
                        }
                    }
                    break;
            }
        }

        return $output;
    }

    
    public function get_plugintype_location($plugintype) {
        return $this->get_plugin_manager()->get_plugintype_root($plugintype);
    }

    
    protected function get_plugin_manager() {
        return core_plugin_manager::instance();
    }
}
