<?php


namespace mod_assign\plugininfo;

use core\plugininfo\base, core_plugin_manager, moodle_url;

defined('MOODLE_INTERNAL') || die();


class assignfeedback extends base {
    
    public static function get_enabled_plugins() {
        global $DB;

        $plugins = core_plugin_manager::instance()->get_installed_plugins('assignfeedback');
        if (!$plugins) {
            return array();
        }
        $installed = array();
        foreach ($plugins as $plugin => $version) {
            $installed[] = 'assignfeedback_'.$plugin;
        }

        list($installed, $params) = $DB->get_in_or_equal($installed, SQL_PARAMS_NAMED);
        $disabled = $DB->get_records_select('config_plugins', "plugin $installed AND name = 'disabled'", $params, 'plugin ASC');
        foreach ($disabled as $conf) {
            if (empty($conf->value)) {
                continue;
            }
            list($type, $name) = explode('_', $conf->plugin, 2);
            unset($plugins[$name]);
        }

        $enabled = array();
        foreach ($plugins as $plugin => $version) {
            $enabled[$plugin] = $plugin;
        }

        return $enabled;
    }

    public function is_uninstall_allowed() {
        return true;
    }

    
    public static function get_manage_url() {
        return new moodle_url('/mod/assign/adminmanageplugins.php', array('subtype'=>'assignfeedback'));
    }

    
    public function uninstall_cleanup() {
        global $DB;

        $DB->delete_records('assign_plugin_config', array('plugin'=>$this->name, 'subtype'=>'assignfeedback'));

        parent::uninstall_cleanup();
    }

    public function get_settings_section_name() {
        return $this->type . '_' . $this->name;
    }

    
    public function load_settings(\part_of_admin_tree $adminroot, $parentnodename, $hassiteconfig) {
        global $CFG, $USER, $DB, $OUTPUT, $PAGE;         $ADMIN = $adminroot;         $plugininfo = $this; 
        if (!$this->is_installed_and_upgraded()) {
            return;
        }

        if (!$hassiteconfig or !file_exists($this->full_path('settings.php'))) {
            return;
        }

        $section = $this->get_settings_section_name();

        $settings = new \admin_settingpage($section, $this->displayname, 'moodle/site:config', $this->is_enabled() === false);

        if ($adminroot->fulltree) {
            $shortsubtype = substr($this->type, strlen('assign'));
            include($this->full_path('settings.php'));
        }

        $adminroot->add($this->type . 'plugins', $settings);
    }
}
