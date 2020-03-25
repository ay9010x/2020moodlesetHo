<?php



namespace tool_langimport;

defined('MOODLE_INTERNAL') || die();
require_once($CFG->libdir.'/filelib.php');
require_once($CFG->libdir.'/componentlib.class.php');


class controller {
    
    public $info;
    
    public $errors;
    
    private $installer;
    
    public $availablelangs;

    
    public function __construct() {
        make_temp_directory('');
        make_upload_directory('lang');

        $this->info = array();
        $this->errors = array();
        $this->installer = new \lang_installer();

        $this->availablelangs = $this->installer->get_remote_list_of_languages();
    }

    
    public function install_languagepacks($langs, $updating = false) {
        global $CFG;

        $this->installer->set_queue($langs);
        $results = $this->installer->run();

        $updatedpacks = 0;

        foreach ($results as $langcode => $langstatus) {
            switch ($langstatus) {
                case \lang_installer::RESULT_DOWNLOADERROR:
                    $a       = new \stdClass();
                    $a->url  = $this->installer->lang_pack_url($langcode);
                    $a->dest = $CFG->dataroot.'/lang';
                    $this->errors[] = get_string('remotedownloaderror', 'error', $a);
                    throw new \moodle_exception('remotedownloaderror', 'error', $a);
                    break;
                case \lang_installer::RESULT_INSTALLED:
                    $updatedpacks++;
                    if ($updating) {
                        event\langpack_updated::event_with_langcode($langcode)->trigger();
                        $this->info[] = get_string('langpackupdated', 'tool_langimport', $langcode);
                    } else {
                        $this->info[] = get_string('langpackinstalled', 'tool_langimport', $langcode);
                        event\langpack_imported::event_with_langcode($langcode)->trigger();
                    }
                    break;
                case \lang_installer::RESULT_UPTODATE:
                    $this->info[] = get_string('langpackuptodate', 'tool_langimport', $langcode);
                    break;
            }
        }

        return $updatedpacks;
    }

    
    public function uninstall_language($lang) {
        global $CFG;

        $dest1 = $CFG->dataroot.'/lang/'.$lang;
        $dest2 = $CFG->dirroot.'/lang/'.$lang;
        $rm1 = false;
        $rm2 = false;
        if (file_exists($dest1)) {
            $rm1 = remove_dir($dest1);
        }
        if (file_exists($dest2)) {
            $rm2 = remove_dir($dest2);
        }

        if ($rm1 or $rm2) {
            $this->info[] = get_string('langpackremoved', 'tool_langimport', $lang);
            event\langpack_removed::event_with_langcode($lang)->trigger();
            return true;
        } else {                $this->errors[] = get_string('langpacknotremoved', 'tool_langimport', $lang);
            return false;
        }
    }

    
    public function update_all_installed_languages() {
        global $CFG;

        if (!$availablelangs = $this->installer->get_remote_list_of_languages()) {
            $this->errors[] = get_string('cannotdownloadlanguageupdatelist', 'error');
            return false;
        }

        $md5array = array();            foreach ($availablelangs as $alang) {
            $md5array[$alang[0]] = $alang[1];
        }

                $currentlangs = array_keys(get_string_manager()->get_list_of_translations(true));
        $updateablelangs = array();
        foreach ($currentlangs as $clang) {
            if (!array_key_exists($clang, $md5array)) {
                $this->info[] = get_string('langpackupdateskipped', 'tool_langimport', $clang);
                continue;
            }
            $dest1 = $CFG->dataroot.'/lang/'.$clang;
            $dest2 = $CFG->dirroot.'/lang/'.$clang;

            if (file_exists($dest1.'/langconfig.php') || file_exists($dest2.'/langconfig.php')) {
                $updateablelangs[] = $clang;
            }
        }

                $neededlangs = array();
        foreach ($updateablelangs as $ulang) {
            if (!$this->is_installed_lang($ulang, $md5array[$ulang])) {
                $neededlangs[] = $ulang;
            }
        }

        try {
            $updated = $this->install_languagepacks($neededlangs, true);
        } catch (\moodle_exception $e) {
            $this->errors[] = 'An exception occurred while installing language packs: ' . $e->getMessage();
            return false;
        }

        if ($updated) {
            $this->info[] = get_string('langupdatecomplete', 'tool_langimport');
                        get_string_manager()->reset_caches();
        } else {
            $this->info[] = get_string('nolangupdateneeded', 'tool_langimport');
        }

        return true;
    }

    
    public function is_installed_lang($lang, $md5check) {
        global $CFG;
        $md5file = $CFG->dataroot.'/lang/'.$lang.'/'.$lang.'.md5';
        if (file_exists($md5file)) {
            return (file_get_contents($md5file) == $md5check);
        }
        return false;
    }
}


