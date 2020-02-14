<?php


namespace core\plugininfo;

use moodle_url, part_of_admin_tree, admin_externalpage;

defined('MOODLE_INTERNAL') || die();


class repository extends base {
    
    public static function get_enabled_plugins() {
        global $DB;
        return $DB->get_records_menu('repository', array('visible'=>1), 'type ASC', 'type, type AS val');
    }

    public function get_settings_section_name() {
        return 'repositorysettings'.$this->name;
    }

    public function load_settings(part_of_admin_tree $adminroot, $parentnodename, $hassiteconfig) {
        if (!$this->is_installed_and_upgraded()) {
            return;
        }

        if ($hassiteconfig && $this->is_enabled()) {
                        $sectionname = $this->get_settings_section_name();
            $settingsurl = new moodle_url('/admin/repository.php',
                array('sesskey' => sesskey(), 'action' => 'edit', 'repos' => $this->name));
            $settings = new admin_externalpage($sectionname, $this->displayname,
                $settingsurl, 'moodle/site:config', false);
            $adminroot->add($parentnodename, $settings);
        }
    }

    
    public static function get_manage_url() {
        return new moodle_url('/admin/repository.php');
    }

    
    public function is_uninstall_allowed() {
        if ($this->name === 'upload' || $this->name === 'coursefiles' || $this->name === 'user' || $this->name === 'recent') {
            return false;
        } else {
            return true;
        }
    }

    
    public function uninstall_cleanup() {
        global $CFG;
        require_once($CFG->dirroot.'/repository/lib.php');

        $repo = \repository::get_type_by_typename($this->name);
        if ($repo) {
            $repo->delete(true);
        }

        parent::uninstall_cleanup();
    }
}
