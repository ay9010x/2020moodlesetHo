<?php


namespace core\plugininfo;

use part_of_admin_tree, admin_settingpage;

defined('MOODLE_INTERNAL') || die();


class calendartype extends base {

    public function is_uninstall_allowed() {
                                if ($this->name !== 'gregorian') {
            return true;
        }

        return false;
    }

    public function get_settings_section_name() {
        return 'calendartype_' . $this->name . '_settings';
    }

    public function load_settings(part_of_admin_tree $adminroot, $parentnodename, $hassiteconfig) {
        global $CFG, $USER, $DB, $OUTPUT, $PAGE;         $ADMIN = $adminroot;         $plugininfo = $this;         $qtype = $this;      
        if (!$this->is_installed_and_upgraded()) {
            return;
        }

        $section = $this->get_settings_section_name();

        $settings = null;
        $systemcontext = \context_system::instance();
        if (($hassiteconfig) &&
            file_exists($this->full_path('settings.php'))) {
            $settings = new admin_settingpage($section, $this->displayname,
                'moodle/site:config', $this->is_enabled() === false);
            include($this->full_path('settings.php'));         }
        if ($settings) {
            $ADMIN->add($parentnodename, $settings);
        }
    }
}
