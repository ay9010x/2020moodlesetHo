<?php


namespace core\plugininfo;

use part_of_admin_tree, admin_settingpage;

defined('MOODLE_INTERNAL') || die();


class webservice extends base {
    
    public static function get_enabled_plugins() {
        global $CFG;

        if (empty($CFG->enablewebservices) or empty($CFG->webserviceprotocols)) {
            return array();
        }

        $enabled = array();
        foreach (explode(',', $CFG->webserviceprotocols) as $protocol) {
            $enabled[$protocol] = $protocol;
        }

        return $enabled;
    }

    public function get_settings_section_name() {
        return 'webservicesetting' . $this->name;
    }

    public function load_settings(part_of_admin_tree $adminroot, $parentnodename, $hassiteconfig) {
        global $CFG, $USER, $DB, $OUTPUT, $PAGE;         $ADMIN = $adminroot;         $plugininfo = $this;         $webservice = $this; 
        if (!$this->is_installed_and_upgraded()) {
            return;
        }

        if (!$hassiteconfig or !file_exists($this->full_path('settings.php'))) {
            return;
        }

        $section = $this->get_settings_section_name();

        $settings = new admin_settingpage($section, $this->displayname, 'moodle/site:config', $this->is_enabled() === false);
        include($this->full_path('settings.php')); 
        if ($settings) {
            $ADMIN->add($parentnodename, $settings);
        }
    }

    public function is_uninstall_allowed() {
        return false;
    }
}
