<?php


namespace core\plugininfo;

use moodle_url, part_of_admin_tree, admin_settingpage;

defined('MOODLE_INTERNAL') || die();



class editor extends base {
    
    public static function get_enabled_plugins() {
        global $CFG;

        if (empty($CFG->texteditors)) {
            return array('atto'=>'atto', 'tinymce'=>'tinymce', 'textarea'=>'textarea');
        }

        $enabled = array();
        foreach (explode(',', $CFG->texteditors) as $editor) {
            $enabled[$editor] = $editor;
        }

        return $enabled;
    }

    public function get_settings_section_name() {
        return 'editorsettings' . $this->name;
    }

    public function load_settings(part_of_admin_tree $adminroot, $parentnodename, $hassiteconfig) {
        global $CFG, $USER, $DB, $OUTPUT, $PAGE;         $ADMIN = $adminroot;         $plugininfo = $this;         $editor = $this;     
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
        if ($this->name === 'textarea') {
            return false;
        } else {
            return true;
        }
    }

    
    public static function get_manage_url() {
        return new moodle_url('/admin/settings.php', array('section'=>'manageeditors'));
    }
}
