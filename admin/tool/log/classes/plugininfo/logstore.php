<?php


namespace tool_log\plugininfo;

use core\plugininfo\base, moodle_url, part_of_admin_tree, admin_settingpage;

defined('MOODLE_INTERNAL') || die();


class logstore extends base {

    public function is_enabled() {
        $enabled = get_config('tool_log', 'enabled_stores');
        if (!$enabled) {
            return false;
        }

        $enabled = array_flip(explode(',', $enabled));
        return isset($enabled['logstore_' . $this->name]);
    }

    public function get_settings_section_name() {
        return 'logsetting' . $this->name;
    }

    public function load_settings(part_of_admin_tree $adminroot, $parentnodename, $hassiteconfig) {
        global $CFG, $USER, $DB, $OUTPUT, $PAGE;         $ADMIN = $adminroot;         $section = $this->get_settings_section_name();

        if (!$this->is_installed_and_upgraded()) {
            return;
        }

        if (!$hassiteconfig or !file_exists($this->full_path('settings.php'))) {
            return;
        }

        $settings = new admin_settingpage($section, $this->displayname, 'moodle/site:config', $this->is_enabled() === false);
        include($this->full_path('settings.php'));

        if ($settings) {
            $ADMIN->add($parentnodename, $settings);
        }
    }

    public static function get_manage_url() {
        return new moodle_url('/admin/settings.php', array('section' => 'managelogging'));
    }

    public function is_uninstall_allowed() {
        return true;
    }

    public function uninstall_cleanup() {
        $enabled = get_config('tool_log', 'enabled_stores');
        if ($enabled) {
            $enabled = array_flip(explode(',', $enabled));
            unset($enabled['logstore_' . $this->name]);
            $enabled = array_flip($enabled);
            set_config('enabled_stores', implode(',', $enabled), 'tool_log');
        }

        parent::uninstall_cleanup();
    }
}
