<?php


namespace core\plugininfo;

use moodle_url, part_of_admin_tree, admin_externalpage;

defined('MOODLE_INTERNAL') || die();


class plagiarism extends base {

    public function get_settings_section_name() {
        return 'plagiarism'. $this->name;
    }

    public function load_settings(part_of_admin_tree $adminroot, $parentnodename, $hassiteconfig) {
        if (!$this->is_installed_and_upgraded()) {
            return;
        }

        if (!$hassiteconfig or !file_exists($this->full_path('settings.php'))) {
            return;
        }

                $section = $this->get_settings_section_name();
        $settingsurl = new moodle_url($this->get_dir().'/settings.php');
        $settings = new admin_externalpage($section, $this->displayname, $settingsurl,
            'moodle/site:config', $this->is_enabled() === false);
        $adminroot->add($parentnodename, $settings);
    }

    public function is_uninstall_allowed() {
        return true;
    }

    
    public static function get_manage_url() {
        global $CFG;
        return !empty($CFG->enableplagiarism) ? new moodle_url('/admin/plagiarism.php') : null;
    }
}
