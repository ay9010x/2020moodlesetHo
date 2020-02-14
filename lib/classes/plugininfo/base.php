<?php


namespace core\plugininfo;

use core_component, core_plugin_manager, moodle_url, coding_exception;

defined('MOODLE_INTERNAL') || die();



abstract class base {

    
    public $type;
    
    public $typerootdir;
    
    public $name;
    
    public $displayname;
    
    public $source;
    
    public $rootdir;
    
    public $versiondisk;
    
    public $versiondb;
    
    public $versionrequires;
    
    public $release;
    
    public $dependencies;
    
    public $instances;
    
    public $sortorder;
    
    public $pluginman;

    
    protected $availableupdates;

    
    public static function get_enabled_plugins() {
        return null;
    }

    
    public static function get_plugins($type, $typerootdir, $typeclass, $pluginman) {
                $plugins = core_component::get_plugin_list($type);
        $return = array();
        foreach ($plugins as $pluginname => $pluginrootdir) {
            $return[$pluginname] = self::make_plugin_instance($type, $typerootdir,
                $pluginname, $pluginrootdir, $typeclass, $pluginman);
        }

                $plugins = $pluginman->get_installed_plugins($type);

        foreach ($plugins as $name => $version) {
            if (isset($return[$name])) {
                continue;
            }
            $plugin              = new $typeclass();
            $plugin->type        = $type;
            $plugin->typerootdir = $typerootdir;
            $plugin->name        = $name;
            $plugin->rootdir     = null;
            $plugin->displayname = $name;
            $plugin->versiondb   = $version;
            $plugin->pluginman   = $pluginman;
            $plugin->init_is_standard();

            $return[$name] = $plugin;
        }

        return $return;
    }

    
    protected static function make_plugin_instance($type, $typerootdir, $name, $namerootdir, $typeclass, $pluginman) {
        $plugin              = new $typeclass();
        $plugin->type        = $type;
        $plugin->typerootdir = $typerootdir;
        $plugin->name        = $name;
        $plugin->rootdir     = $namerootdir;
        $plugin->pluginman   = $pluginman;

        $plugin->init_display_name();
        $plugin->load_disk_version();
        $plugin->load_db_version();
        $plugin->init_is_standard();

        return $plugin;
    }

    
    public function is_installed_and_upgraded() {
        if (!$this->rootdir) {
            return false;
        }
        if ($this->versiondb === null and $this->versiondisk === null) {
                        return false;
        }

        return ((float)$this->versiondb === (float)$this->versiondisk);
    }

    
    public function init_display_name() {
        if (!get_string_manager()->string_exists('pluginname', $this->component)) {
            $this->displayname = '[pluginname,' . $this->component . ']';
        } else {
            $this->displayname = get_string('pluginname', $this->component);
        }
    }

    
    public function __get($name) {
        switch ($name) {
            case 'component': return $this->type . '_' . $this->name;

            default:
                debugging('Invalid plugin property accessed! '.$name);
                return null;
        }
    }

    
    public function full_path($relativepath) {
        if (empty($this->rootdir)) {
            return '';
        }
        return $this->rootdir . '/' . $relativepath;
    }

    
    public function load_disk_version() {
        $versions = $this->pluginman->get_present_plugins($this->type);

        $this->versiondisk = null;
        $this->versionrequires = null;
        $this->dependencies = array();

        if (!isset($versions[$this->name])) {
            return;
        }

        $plugin = $versions[$this->name];

        if (isset($plugin->version)) {
            $this->versiondisk = $plugin->version;
        }
        if (isset($plugin->requires)) {
            $this->versionrequires = $plugin->requires;
        }
        if (isset($plugin->release)) {
            $this->release = $plugin->release;
        }
        if (isset($plugin->dependencies)) {
            $this->dependencies = $plugin->dependencies;
        }
    }

    
    public function get_other_required_plugins() {
        if (is_null($this->dependencies)) {
            $this->load_disk_version();
        }
        return $this->dependencies;
    }

    
    public function is_subplugin() {
        return ($this->get_parent_plugin() !== false);
    }

    
    public function get_parent_plugin() {
        return $this->pluginman->get_parent_of_subplugin($this->type);
    }

    
    public function load_db_version() {
        $versions = $this->pluginman->get_installed_plugins($this->type);

        if (isset($versions[$this->name])) {
            $this->versiondb = $versions[$this->name];
        } else {
            $this->versiondb = null;
        }
    }

    
    public function init_is_standard() {

        $pluginman = $this->pluginman;
        $standard = $pluginman::standard_plugins_list($this->type);

        if ($standard !== false) {
            $standard = array_flip($standard);
            if (isset($standard[$this->name])) {
                $this->source = core_plugin_manager::PLUGIN_SOURCE_STANDARD;
            } else if (!is_null($this->versiondb) and is_null($this->versiondisk)
                and $pluginman::is_deleted_standard_plugin($this->type, $this->name)) {
                $this->source = core_plugin_manager::PLUGIN_SOURCE_STANDARD;             } else {
                $this->source = core_plugin_manager::PLUGIN_SOURCE_EXTENSION;
            }
        }
    }

    
    public function is_standard() {
        return $this->source === core_plugin_manager::PLUGIN_SOURCE_STANDARD;
    }

    
    public function is_core_dependency_satisfied($moodleversion) {

        if (empty($this->versionrequires)) {
            return true;

        } else {
            return (double)$this->versionrequires <= (double)$moodleversion;
        }
    }

    
    public function get_status() {

        $pluginman = $this->pluginman;

        if (is_null($this->versiondb) and is_null($this->versiondisk)) {
            return core_plugin_manager::PLUGIN_STATUS_NODB;

        } else if (is_null($this->versiondb) and !is_null($this->versiondisk)) {
            return core_plugin_manager::PLUGIN_STATUS_NEW;

        } else if (!is_null($this->versiondb) and is_null($this->versiondisk)) {
            if ($pluginman::is_deleted_standard_plugin($this->type, $this->name)) {
                return core_plugin_manager::PLUGIN_STATUS_DELETE;
            } else {
                return core_plugin_manager::PLUGIN_STATUS_MISSING;
            }

        } else if ((float)$this->versiondb === (float)$this->versiondisk) {
                                    return core_plugin_manager::PLUGIN_STATUS_UPTODATE;

        } else if ($this->versiondb < $this->versiondisk) {
            return core_plugin_manager::PLUGIN_STATUS_UPGRADE;

        } else if ($this->versiondb > $this->versiondisk) {
            return core_plugin_manager::PLUGIN_STATUS_DOWNGRADE;

        } else {
                        throw new coding_exception('Unable to determine plugin state, check the plugin versions');
        }
    }

    
    public function is_enabled() {
        if (!$this->rootdir) {
                        return false;
        }

        $enabled = $this->pluginman->get_enabled_plugins($this->type);

        if (!is_array($enabled)) {
            return null;
        }

        return isset($enabled[$this->name]);
    }

    
    public function available_updates() {

        if ($this->availableupdates === null) {
                        $this->availableupdates = $this->pluginman->load_available_updates_for_plugin($this->component);
        }

        if (empty($this->availableupdates) or !is_array($this->availableupdates)) {
            $this->availableupdates = array();
            return null;
        }

        $updates = array();

        foreach ($this->availableupdates as $availableupdate) {
            if ($availableupdate->version > $this->versiondisk) {
                $updates[] = $availableupdate;
            }
        }

        if (empty($updates)) {
            return null;
        }

        return $updates;
    }

    
    public function get_settings_section_name() {
        return null;
    }

    
    public function get_settings_url() {
        $section = $this->get_settings_section_name();
        if ($section === null) {
            return null;
        }
        $settings = admin_get_root()->locate($section);
        if ($settings && $settings instanceof \admin_settingpage) {
            return new moodle_url('/admin/settings.php', array('section' => $section));
        } else if ($settings && $settings instanceof \admin_externalpage) {
            return new moodle_url($settings->url);
        } else {
            return null;
        }
    }

    
    public function load_settings(\part_of_admin_tree $adminroot, $parentnodename, $hassiteconfig) {
    }

    
    public function is_uninstall_allowed() {
        return false;
    }

    
    public function get_uninstall_extra_warning() {
        return '';
    }

    
    public function uninstall_cleanup() {
                    }

    
    public function get_dir() {
        global $CFG;

        return substr($this->rootdir, strlen($CFG->dirroot));
    }

    
    public function uninstall(\progress_trace $progress) {
        return true;
    }

    
    public function get_return_url_after_uninstall($return) {
        if ($return === 'manage') {
            if ($url = $this->get_manage_url()) {
                return $url;
            }
        }
        return new moodle_url('/admin/plugins.php#plugin_type_cell_'.$this->type);
    }

    
    public static function get_manage_url() {
        return null;
    }

    
    public final function get_default_uninstall_url($return = 'overview') {
        return new moodle_url('/admin/plugins.php', array(
            'sesskey' => sesskey(),
            'uninstall' => $this->component,
            'confirm' => 0,
            'return' => $return,
        ));
    }
}
