<?php


namespace core\plugininfo;

use moodle_url, part_of_admin_tree, admin_settingpage, admin_externalpage;

defined('MOODLE_INTERNAL') || die();


class filter extends base {

    public function init_display_name() {
        if (!get_string_manager()->string_exists('filtername', $this->component)) {
            $this->displayname = '[filtername,' . $this->component . ']';
        } else {
            $this->displayname = get_string('filtername', $this->component);
        }
    }

    
    public static function get_enabled_plugins() {
        global $DB, $CFG;
        require_once("$CFG->libdir/filterlib.php");

        $enabled = array();
        $filters = $DB->get_records_select('filter_active', "active <> :disabled AND contextid = :contextid", array(
            'disabled' => TEXTFILTER_DISABLED, 'contextid' => \context_system::instance()->id), 'filter ASC', 'id, filter');
        foreach ($filters as $filter) {
            $enabled[$filter->filter] = $filter->filter;
        }

        return $enabled;
    }

    public function get_settings_section_name() {
        return 'filtersetting' . $this->name;
    }

    public function load_settings(part_of_admin_tree $adminroot, $parentnodename, $hassiteconfig) {
        global $CFG, $USER, $DB, $OUTPUT, $PAGE;         $ADMIN = $adminroot;         $plugininfo = $this;         $filter = $this;     
        if (!$this->is_installed_and_upgraded()) {
            return;
        }

        if (!$hassiteconfig) {
            return;
        }
        if (file_exists($this->full_path('settings.php'))) {
            $fullpath = $this->full_path('settings.php');
        } else if (file_exists($this->full_path('filtersettings.php'))) {
            $fullpath = $this->full_path('filtersettings.php');
        } else {
            return;
        }

        $section = $this->get_settings_section_name();
        $settings = new admin_settingpage($section, $this->displayname, 'moodle/site:config', $this->is_enabled() === false);
        include($fullpath); 
        if ($settings) {
            $ADMIN->add($parentnodename, $settings);
        }
    }

    public function is_uninstall_allowed() {
        return true;
    }

    
    public static function get_manage_url() {
        return new moodle_url('/admin/filters.php');
    }

    
    public function uninstall_cleanup() {
        global $DB, $CFG;

        $DB->delete_records('filter_active', array('filter' => $this->name));
        $DB->delete_records('filter_config', array('filter' => $this->name));

        if (empty($CFG->filterall)) {
            $stringfilters = array();
        } else if (!empty($CFG->stringfilters)) {
            $stringfilters = explode(',', $CFG->stringfilters);
            $stringfilters = array_combine($stringfilters, $stringfilters);
        } else {
            $stringfilters = array();
        }

        unset($stringfilters[$this->name]);

        set_config('stringfilters', implode(',', $stringfilters));
        set_config('filterall', !empty($stringfilters));

        parent::uninstall_cleanup();
    }
}
