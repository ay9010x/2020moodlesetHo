<?php


namespace core\plugininfo;

use moodle_url, part_of_admin_tree, admin_settingpage, core_plugin_manager;

defined('MOODLE_INTERNAL') || die();


class format extends base {
    
    public static function get_enabled_plugins() {
        global $DB;

        $plugins = core_plugin_manager::instance()->get_installed_plugins('format');
        if (!$plugins) {
            return array();
        }
        $installed = array();
        foreach ($plugins as $plugin => $version) {
            $installed[] = 'format_'.$plugin;
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

    
    public static function get_plugins($type, $typerootdir, $typeclass, $pluginman) {
        global $CFG;
        require_once($CFG->dirroot.'/course/lib.php');

        $formats = parent::get_plugins($type, $typerootdir, $typeclass, $pluginman);
        $order = get_sorted_course_formats();
        $sortedformats = array();
        foreach ($order as $formatname) {
            $sortedformats[$formatname] = $formats[$formatname];
        }
        return $sortedformats;
    }

    public function get_settings_section_name() {
        return 'formatsetting' . $this->name;
    }

    public function load_settings(part_of_admin_tree $adminroot, $parentnodename, $hassiteconfig) {
        global $CFG, $USER, $DB, $OUTPUT, $PAGE;         $ADMIN = $adminroot;         $plugininfo = $this; 
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
        if ($this->name !== get_config('moodlecourse', 'format') && $this->name !== 'site') {
            return true;
        } else {
            return false;
        }
    }

    
    public static function get_manage_url() {
        return new moodle_url('/admin/settings.php', array('section'=>'manageformats'));
    }

    public function get_uninstall_extra_warning() {
        global $DB;

        $coursecount = $DB->count_records('course', array('format' => $this->name));

        if (!$coursecount) {
            return '';
        }

        $defaultformat = $this->pluginman->plugin_name('format_'.get_config('moodlecourse', 'format'));
        $message = get_string(
            'formatuninstallwithcourses', 'core_admin',
            (object)array('count' => $coursecount, 'format' => $this->displayname,
                'defaultformat' => $defaultformat));

        return $message;
    }

    
    public function uninstall_cleanup() {
        global $DB;

        if (($defaultformat = get_config('moodlecourse', 'format')) && $defaultformat !== $this->name) {
            $courses = $DB->get_records('course', array('format' => $this->name), 'id');
            $data = (object)array('id' => null, 'format' => $defaultformat);
            foreach ($courses as $record) {
                $data->id = $record->id;
                update_course($data);
            }
        }

        $DB->delete_records('course_format_options', array('format' => $this->name));

        parent::uninstall_cleanup();
    }
}
