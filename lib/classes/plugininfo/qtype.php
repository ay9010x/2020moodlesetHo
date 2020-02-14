<?php


namespace core\plugininfo;

use moodle_url, part_of_admin_tree, admin_settingpage, core_plugin_manager;

defined('MOODLE_INTERNAL') || die();


class qtype extends base {
    
    public static function get_enabled_plugins() {
        global $DB;

        $plugins = core_plugin_manager::instance()->get_installed_plugins('qtype');
        if (!$plugins) {
            return array();
        }
        $installed = array();
        foreach ($plugins as $plugin => $version) {
            $installed[] = $plugin.'_disabled';
        }

        list($installed, $params) = $DB->get_in_or_equal($installed, SQL_PARAMS_NAMED);
        $disabled = $DB->get_records_select('config_plugins', "name $installed AND plugin = 'question'", $params, 'plugin ASC');
        foreach ($disabled as $conf) {
            if (empty($conf->value)) {
                continue;
            }
            $name = substr($conf->name, 0, -9);
            unset($plugins[$name]);
        }

        $enabled = array();
        foreach ($plugins as $plugin => $version) {
            $enabled[$plugin] = $plugin;
        }

        return $enabled;
    }

    public function is_uninstall_allowed() {
        global $DB;

        if ($this->name === 'missingtype') {
                        return false;
        }

        return !$DB->record_exists('question', array('qtype' => $this->name));
    }

    
    public static function get_manage_url() {
        return new moodle_url('/admin/qtypes.php');
    }

    
    public function uninstall_cleanup() {
                unset_config($this->name . '_disabled', 'question');
        unset_config($this->name . '_sortorder', 'question');

        parent::uninstall_cleanup();
    }

    public function get_settings_section_name() {
        return 'qtypesetting' . $this->name;
    }

    public function load_settings(part_of_admin_tree $adminroot, $parentnodename, $hassiteconfig) {
        global $CFG, $USER, $DB, $OUTPUT, $PAGE;         $ADMIN = $adminroot;         $plugininfo = $this;         $qtype = $this;      
        if (!$this->is_installed_and_upgraded()) {
            return;
        }

        $section = $this->get_settings_section_name();

        $settings = null;
        $systemcontext = \context_system::instance();
        if (($hassiteconfig || has_capability('moodle/question:config', $systemcontext)) &&
            file_exists($this->full_path('settings.php'))) {
            $settings = new admin_settingpage($section, $this->displayname,
                'moodle/question:config', $this->is_enabled() === false);
            include($this->full_path('settings.php'));         }
        if ($settings) {
            $ADMIN->add($parentnodename, $settings);
        }
    }
}
