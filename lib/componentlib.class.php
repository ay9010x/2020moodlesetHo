<?php




defined('MOODLE_INTERNAL') || die();

 
global $CFG;
require_once($CFG->libdir.'/filelib.php');

define('COMPONENT_ERROR',           0);
define('COMPONENT_UPTODATE',        1);
define('COMPONENT_NEEDUPDATE',      2);
define('COMPONENT_INSTALLED',       3);


class component_installer {
    
    var $sourcebase;       var $zippath;                                 var $zipfilename;      var $md5filename;      var $componentname;                                                                         var $destpath;                                var $errorstring;      var $extramd5info;     var $requisitesok;     
    var $cachedmd5components;                               
    
    public function __construct($sourcebase, $zippath, $zipfilename, $md5filename='', $destpath='') {

        $this->sourcebase   = $sourcebase;
        $this->zippath      = $zippath;
        $this->zipfilename  = $zipfilename;
        $this->md5filename  = $md5filename;
        $this->componentname= '';
        $this->destpath     = $destpath;
        $this->errorstring  = '';
        $this->extramd5info = '';
        $this->requisitesok = false;
        $this->cachedmd5components = array();

        $this->check_requisites();
    }

    
    public function component_installer($sourcebase, $zippath, $zipfilename, $md5filename='', $destpath='') {
        debugging('Use of class name as constructor is deprecated', DEBUG_DEVELOPER);
        self::__construct($sourcebase, $zippath, $zipfilename, $md5filename, $destpath);
    }

    
    function check_requisites() {
        global $CFG;

        $this->requisitesok = false;

            if (empty($this->sourcebase) || empty($this->zipfilename)) {
            $this->errorstring='missingrequiredfield';
            return false;
        }
            if (!PHPUNIT_TEST and $this->sourcebase != 'https://download.moodle.org') {
            $this->errorstring='wrongsourcebase';
            return false;
        }
            if (stripos($this->zipfilename, '.zip') === false) {
            $this->errorstring='wrongzipfilename';
            return false;
        }
            if (!empty($this->destpath)) {
            if (!file_exists($CFG->dataroot.'/'.$this->destpath)) {
                $this->errorstring='wrongdestpath';
                return false;
            }
        }
            $pos = stripos($this->zipfilename, '.zip');
        $this->componentname = substr($this->zipfilename, 0, $pos);
            if (empty($this->md5filename)) {
            $this->md5filename = $this->componentname.'.md5';
        }
            $this->requisitesok = true;
        return true;
    }

    
    function install() {

        global $CFG;

            if (!$this->requisitesok) {
            return COMPONENT_ERROR;
        }
            if ($this->need_upgrade() === COMPONENT_ERROR) {
            return COMPONENT_ERROR;
        } else if ($this->need_upgrade() === COMPONENT_UPTODATE) {
            $this->errorstring='componentisuptodate';
            return COMPONENT_UPTODATE;
        }
            if (!make_temp_directory('', false)) {
             $this->errorstring='cannotcreatetempdir';
             return COMPONENT_ERROR;
        }
            if ($this->zippath) {
            $source = $this->sourcebase.'/'.$this->zippath.'/'.$this->zipfilename;
        } else {
            $source = $this->sourcebase.'/'.$this->zipfilename;
        }

        $zipfile= $CFG->tempdir.'/'.$this->zipfilename;

        if($contents = download_file_content($source)) {
            if ($file = fopen($zipfile, 'w')) {
                if (!fwrite($file, $contents)) {
                    fclose($file);
                    $this->errorstring='cannotsavezipfile';
                    return COMPONENT_ERROR;
                }
            } else {
                $this->errorstring='cannotsavezipfile';
                return COMPONENT_ERROR;
            }
            fclose($file);
        } else {
            $this->errorstring='cannotdownloadzipfile';
            return COMPONENT_ERROR;
        }
            $new_md5 = md5($contents);
            if (!$remote_md5 = $this->get_component_md5()) {
            return COMPONENT_ERROR;
        }
        if ($new_md5 != $remote_md5) {
            $this->errorstring='downloadedfilecheckfailed';
            return COMPONENT_ERROR;
        }
    		                $destinationdir = $CFG->dirroot.'/'.$this->destpath;
        $destinationcomponent = $destinationdir.'/'.$this->componentname;
        @remove_dir($destinationcomponent.'_old');     
                @rename($destinationcomponent, $destinationcomponent.'_old');

            if (!unzip_file($zipfile, $destinationdir, false)) {
                    @remove_dir($destinationcomponent);
            @rename ($destinationcomponent.'_old', $destinationcomponent);
            $this->errorstring='cannotunzipfile';
            return COMPONENT_ERROR;
        }
            @remove_dir($destinationcomponent.'_old');
            if ($file = fopen($destinationcomponent.'/'.$this->componentname.'.md5', 'w')) {
            if (!fwrite($file, $new_md5)) {
                fclose($file);
                $this->errorstring='cannotsavemd5file';
                return COMPONENT_ERROR;
            }
        } else  {
            $this->errorstring='cannotsavemd5file';
            return COMPONENT_ERROR;
        }
        fclose($file);
            @unlink($zipfile);

        return COMPONENT_INSTALLED;
    }

    
    function need_upgrade() {

            if (!$this->requisitesok) {
            return COMPONENT_ERROR;
        }
            $local_md5 = $this->get_local_md5();
            if (!$remote_md5 = $this->get_component_md5()) {
            return COMPONENT_ERROR;
        }
           if ($local_md5 == $remote_md5) {
           return COMPONENT_UPTODATE;
       } else {
           return COMPONENT_NEEDUPDATE;
       }
    }

    
    function change_zip_file($newzipfilename) {

        $this->zipfilename = $newzipfilename;
        return $this->check_requisites();
    }

    
    function get_local_md5() {
        global $CFG;

            if (!$this->requisitesok) {
            return false;
        }

        $return_value = 'needtobeinstalled';   
           $source = $CFG->dataroot.'/'.$this->destpath.'/'.$this->componentname.'/'.$this->componentname.'.md5';
           if (file_exists($source)) {
           if ($temp = file_get_contents($source)) {
               $return_value = $temp;
           }
        }
        return $return_value;
    }

    
    function get_component_md5() {

            if (!$this->requisitesok) {
            return false;
        }
            if (!$comp_arr = $this->get_all_components_md5()) {
            if (empty($this->errorstring)) {
                $this->errorstring='cannotdownloadcomponents';
            }
            return false;
        }
            if (empty($comp_arr[$this->componentname]) || !$component = $comp_arr[$this->componentname]) {
             $this->errorstring='cannotfindcomponent';
             return false;
        }
            if (empty($component[1]) || strlen($component[1]) != 32) {
            $this->errorstring='invalidmd5';
            return false;
        }
            if (!empty($component[2])) {
            $this->extramd5info = $component[2];
        }
        return $component[1];
    }

    
    function get_all_components_md5() {

            if (!$this->requisitesok) {
            return false;
        }

            $comp_arr = array();

            if ($this->zippath) {
            $source = $this->sourcebase.'/'.$this->zippath.'/'.$this->md5filename;
        } else {
            $source = $this->sourcebase.'/'.$this->md5filename;
        }

            if (!empty($this->cachedmd5components[$source])) {
            $comp_arr = $this->cachedmd5components[$source];
        } else {
                    $availablecomponents = array();

            if ($contents = download_file_content($source)) {
                            $lines=preg_split('/\r?\n/',$contents);
                            foreach($lines as $line) {
                    $availablecomponents[] = explode(',', $line);
                }
                            if (empty($availablecomponents)) {
                    $this->errorstring='cannotdownloadcomponents';
                    return false;
                }
                                        $comp_arr = array();
                foreach ($availablecomponents as $component) {
                                    if (empty($component[0])) {
                        continue;
                    }
                    $component[0]=trim($component[0]);
                    if (!empty($component[1])) {
                        $component[1]=trim($component[1]);
                    }
                    if (!empty($component[2])) {
                        $component[2]=trim($component[2]);
                    }
                    $comp_arr[$component[0]] = $component;
                }
                            $this->cachedmd5components[$source] = $comp_arr;
            } else {
                            $this->errorstring='remotedownloaderror';
                return false;
            }
        }
            if (!empty($this->errorstring)) {
             return false;

        } else if (empty($comp_arr)) {
             $this->errorstring='cannotdownloadcomponents';
             return false;
        }
        return $comp_arr;
    }

    
    function get_error() {
        return $this->errorstring;
    }

    
    function get_extra_md5_field() {
        return $this->extramd5info;
    }

} 


class lang_installer {

    
    const RESULT_INSTALLED      = 'installed';
    
    const RESULT_UPTODATE       = 'uptodate';
    
    const RESULT_DOWNLOADERROR  = 'downloaderror';

    
    protected $queue = array();
    
    protected $current;
    
    protected $done = array();
    
    protected $version;

    
    public function __construct($langcode = '') {
        global $CFG;

        $this->set_queue($langcode);
        $this->version = moodle_major_version(true);

        if (!empty($CFG->langotherroot) and $CFG->langotherroot !== $CFG->dataroot . '/lang') {
            debugging('The in-built language pack installer does not support alternative location ' .
                'of languages root directory. You are supposed to install and update your language '.
                'packs on your own.');
        }
    }

    
    public function set_queue($langcodes) {
        if (is_array($langcodes)) {
            $this->queue = $langcodes;
        } else if (!empty($langcodes)) {
            $this->queue = array($langcodes);
        }
    }

    
    public function run() {

        $results = array();

        while ($this->current = array_shift($this->queue)) {

            if ($this->was_processed($this->current)) {
                                continue;
            }

            if ($this->current === 'en') {
                $this->mark_processed($this->current);
                continue;
            }

            $results[$this->current] = $this->install_language_pack($this->current);

            if (in_array($results[$this->current], array(self::RESULT_INSTALLED, self::RESULT_UPTODATE))) {
                if ($parentlang = $this->get_parent_language($this->current)) {
                    if (!$this->is_queued($parentlang) and !$this->was_processed($parentlang)) {
                        $this->add_to_queue($parentlang);
                    }
                }
            }

            $this->mark_processed($this->current);
        }

        return $results;
    }

    
    public function lang_pack_url($langcode = '') {

        if (empty($langcode)) {
            return 'https://download.moodle.org/langpack/'.$this->version.'/';
        } else {
            return 'https://download.moodle.org/download.php/langpack/'.$this->version.'/'.$langcode.'.zip';
        }
    }

    
    public function get_remote_list_of_languages() {
        $source = 'https://download.moodle.org/langpack/' . $this->version . '/languages.md5';
        $availablelangs = array();

        if ($content = download_file_content($source)) {
            $alllines = explode("\n", $content);
            foreach($alllines as $line) {
                if (!empty($line)){
                    $availablelangs[] = explode(',', $line);
                }
            }
            return $availablelangs;

        } else {
            return false;
        }
    }

    
    
    protected function add_to_queue($langcodes) {
        if (is_array($langcodes)) {
            $this->queue = array_merge($this->queue, $langcodes);
        } else if (!empty($langcodes)) {
            $this->queue[] = $langcodes;
        }
    }

    
    protected function is_queued($langcode = '') {

        if (empty($langcode)) {
            return !empty($this->queue);

        } else {
            return in_array($langcode, $this->queue);
        }
    }

    
    protected function was_processed($langcode) {
        return isset($this->done[$langcode]);
    }

    
    protected function mark_processed($langcode) {
        $this->done[$langcode] = 1;
    }

    
    protected function get_parent_language($langcode) {
        return get_parent_language($langcode);
    }

    
    protected function install_language_pack($langcode) {

                $installer = new component_installer('https://download.moodle.org', 'download.php/direct/langpack/' . $this->version,
            $langcode . '.zip', 'languages.md5', 'lang');

        if (!$installer->requisitesok) {
            throw new lang_installer_exception('installer_requisites_check_failed');
        }

        $status = $installer->install();

        if ($status == COMPONENT_ERROR) {
            if ($installer->get_error() === 'remotedownloaderror') {
                return self::RESULT_DOWNLOADERROR;
            } else {
                throw new lang_installer_exception($installer->get_error(), $langcode);
            }

        } else if ($status == COMPONENT_UPTODATE) {
            return self::RESULT_UPTODATE;

        } else if ($status == COMPONENT_INSTALLED) {
            return self::RESULT_INSTALLED;

        } else {
            throw new lang_installer_exception('unexpected_installer_result', $status);
        }
    }
}



class lang_installer_exception extends moodle_exception {

    public function __construct($errorcode, $debuginfo = null) {
        parent::__construct($errorcode, 'error', '', null, $debuginfo);
    }
}
