<?php


namespace core\plugininfo;

use moodle_url, part_of_admin_tree, admin_settingpage;

defined('MOODLE_INTERNAL') || die();


class mod extends base {
    
    public static function get_enabled_plugins() {
        global $DB;
        return $DB->get_records_menu('modules', array('visible'=>1), 'name ASC', 'name, name AS val');
    }

    
    public function __get($name) {
        if ($name === 'visible') {
            debugging('This is now an instance of plugininfo_mod, please use $module->is_enabled() instead of $module->visible', DEBUG_DEVELOPER);
            return ($this->is_enabled() !== false);
        }
        return parent::__get($name);
    }

    public function init_display_name() {
        if (get_string_manager()->string_exists('pluginname', $this->component)) {
            $this->displayname = get_string('pluginname', $this->component);
        } else {
            $this->displayname = get_string('modulename', $this->component);
        }
    }

    public function get_settings_section_name() {
        return 'modsetting' . $this->name;
    }

    public function load_settings(part_of_admin_tree $adminroot, $parentnodename, $hassiteconfig) {
        global $CFG, $USER, $DB, $OUTPUT, $PAGE;         $ADMIN = $adminroot;         $plugininfo = $this;         $module = $this;     
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
        if ($this->name === 'forum') {
            return false;
        } else {
            return true;
        }
    }

    
    public static function get_manage_url() {
        return new moodle_url('/admin/modules.php');
    }

    
    public function get_uninstall_extra_warning() {
        global $DB;

        if (!$module = $DB->get_record('modules', array('name'=>$this->name))) {
            return '';
        }

        if (!$count = $DB->count_records('course_modules', array('module'=>$module->id))) {
            return '';
        }

        $sql = "SELECT COUNT('x')
                  FROM (
                    SELECT course
                      FROM {course_modules}
                     WHERE module = :mid
                  GROUP BY course
                  ) c";
        $courses = $DB->count_records_sql($sql, array('mid'=>$module->id));

        return '<p>'.get_string('uninstallextraconfirmmod', 'core_plugin', array('instances'=>$count, 'courses'=>$courses)).'</p>';
    }

    
    public function uninstall_cleanup() {
        global $DB, $CFG;

        if (!$module = $DB->get_record('modules', array('name' => $this->name))) {
            parent::uninstall_cleanup();
            return;
        }

                if ($coursemods = $DB->get_records('course_modules', array('module' => $module->id))) {
            foreach ($coursemods as $coursemod) {
                                delete_mod_from_section($coursemod->id, $coursemod->section);
            }
        }

                        increment_revision_number('course', 'cacherev',
            "id IN (SELECT DISTINCT course
                      FROM {course_modules}
                     WHERE module=?)",
            array($module->id));

                $DB->delete_records('course_modules', array('module' => $module->id));

                if ($coursemods) {
            foreach ($coursemods as $coursemod) {
                \context_helper::delete_instance(CONTEXT_MODULE, $coursemod->id);
            }
        }

                $DB->delete_records('modules', array('name' => $module->name));

                require_once($CFG->libdir.'/gradelib.php');
        grade_uninstalled_module($module->name);

                
        parent::uninstall_cleanup();
    }
}
