<?php


namespace core\plugininfo;

use moodle_url, part_of_admin_tree, admin_settingpage;

defined('MOODLE_INTERNAL') || die();


class message extends base {
    
    public static function get_enabled_plugins() {
        global $DB;
        return $DB->get_records_menu('message_processors', array('enabled'=>1), 'name ASC', 'name, name AS val');
    }

    public function get_settings_section_name() {
        return 'messagesetting' . $this->name;
    }

    public function load_settings(part_of_admin_tree $adminroot, $parentnodename, $hassiteconfig) {
        global $CFG, $USER, $DB, $OUTPUT, $PAGE;         $ADMIN = $adminroot;         $plugininfo = $this; 
        if (!$this->is_installed_and_upgraded()) {
            return;
        }

        if (!$hassiteconfig) {
            return;
        }
        $section = $this->get_settings_section_name();

        $settings = null;
        $processors = get_message_processors();
        if (isset($processors[$this->name])) {
            $processor = $processors[$this->name];
            if ($processor->available && $processor->hassettings) {
                $settings = new admin_settingpage($section, $this->displayname,
                    'moodle/site:config', $this->is_enabled() === false);
                include($this->full_path('settings.php'));             }
        }
        if ($settings) {
            $ADMIN->add($parentnodename, $settings);
        }
    }

    
    public static function get_manage_url() {
        return new moodle_url('/admin/message.php');
    }

    public function is_uninstall_allowed() {
        return true;
    }

    
    public function uninstall_cleanup() {
        global $CFG;

        require_once($CFG->libdir.'/messagelib.php');
        message_processor_uninstall($this->name);

        parent::uninstall_cleanup();
    }
}
