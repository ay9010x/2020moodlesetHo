<?php


namespace core\plugininfo;

use moodle_url, part_of_admin_tree, admin_settingpage, admin_externalpage;

defined('MOODLE_INTERNAL') || die();


class auth extends base {
    public function is_uninstall_allowed() {
        global $DB;

        if (in_array($this->name, array('manual', 'nologin', 'webservice', 'mnet'))) {
            return false;
        }

        return !$DB->record_exists('user', array('auth'=>$this->name));
    }

    
    public static function get_enabled_plugins() {
        global $CFG;

                $enabled = array('nologin'=>'nologin', 'manual'=>'manual');
        foreach (explode(',', $CFG->auth) as $auth) {
            $enabled[$auth] = $auth;
        }

        return $enabled;
    }

    public function get_settings_section_name() {
        return 'authsetting' . $this->name;
    }

    public function load_settings(part_of_admin_tree $adminroot, $parentnodename, $hassiteconfig) {
        global $CFG, $USER, $DB, $OUTPUT, $PAGE;         $ADMIN = $adminroot;         $plugininfo = $this;         $auth = $this;       
        if (!$this->is_installed_and_upgraded()) {
            return;
        }

        if (!$hassiteconfig) {
            return;
        }

        $section = $this->get_settings_section_name();

        $settings = null;
        if (file_exists($this->full_path('settings.php'))) {
                        $settings = new admin_settingpage($section, $this->displayname,
                'moodle/site:config', $this->is_enabled() === false);
            include($this->full_path('settings.php'));         } else {
            $settingsurl = new moodle_url('/admin/auth_config.php', array('auth' => $this->name));
            $settings = new admin_externalpage($section, $this->displayname,
                $settingsurl, 'moodle/site:config', $this->is_enabled() === false);
        }

        if ($settings) {
            $ADMIN->add($parentnodename, $settings);
        }
    }

    
    public static function get_manage_url() {
        return new moodle_url('/admin/settings.php', array('section'=>'manageauths'));
    }

    
    public function uninstall_cleanup() {
        global $CFG;

        if (!empty($CFG->auth)) {
            $auths = explode(',', $CFG->auth);
            $auths = array_unique($auths);
        } else {
            $auths = array();
        }
        if (($key = array_search($this->name, $auths)) !== false) {
            unset($auths[$key]);
            set_config('auth', implode(',', $auths));
        }

        if (!empty($CFG->registerauth) and $CFG->registerauth === $this->name) {
            unset_config('registerauth');
        }

        parent::uninstall_cleanup();
    }
}
