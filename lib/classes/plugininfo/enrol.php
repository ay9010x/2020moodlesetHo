<?php


namespace core\plugininfo;

use moodle_url, part_of_admin_tree, admin_settingpage, admin_externalpage;

defined('MOODLE_INTERNAL') || die();


class enrol extends base {
    
    public static function get_enabled_plugins() {
        global $CFG;

        $enabled = array();
        foreach (explode(',', $CFG->enrol_plugins_enabled) as $enrol) {
            $enabled[$enrol] = $enrol;
        }

        return $enabled;
    }

    public function get_settings_section_name() {
        if (file_exists($this->full_path('settings.php'))) {
            return 'enrolsettings' . $this->name;
        } else {
            return null;
        }
    }

    public function load_settings(part_of_admin_tree $adminroot, $parentnodename, $hassiteconfig) {
        global $CFG, $USER, $DB, $OUTPUT, $PAGE;         $ADMIN = $adminroot;         $plugininfo = $this;         $enrol = $this;      
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
        if ($this->name === 'manual') {
            return false;
        }
        return true;
    }

    
    public static function get_manage_url() {
        return new moodle_url('/admin/settings.php', array('section'=>'manageenrols'));
    }

    
    public function get_uninstall_extra_warning() {
        global $DB, $OUTPUT;

        $sql = "SELECT COUNT('x')
                  FROM {user_enrolments} ue
                  JOIN {enrol} e ON e.id = ue.enrolid
                 WHERE e.enrol = :plugin";
        $count = $DB->count_records_sql($sql, array('plugin'=>$this->name));

        if (!$count) {
            return '';
        }

        $migrateurl = new moodle_url('/admin/enrol.php', array('action'=>'migrate', 'enrol'=>$this->name, 'sesskey'=>sesskey()));
        $migrate = new \single_button($migrateurl, get_string('migratetomanual', 'core_enrol'));
        $button = $OUTPUT->render($migrate);

        $result = '<p>'.get_string('uninstallextraconfirmenrol', 'core_plugin', array('enrolments'=>$count)).'</p>';
        $result .= $button;

        return $result;
    }

    
    public function uninstall_cleanup() {
        global $DB, $CFG;

        
                role_unassign_all(array('component'=>'enrol_'.$this->name));

                $DB->delete_records_select('user_enrolments', "enrolid IN (SELECT id FROM {enrol} WHERE enrol = ?)", array($this->name));

                $DB->delete_records('enrol', array('enrol'=>$this->name));

                if (!empty($CFG->enrol_plugins_enabled)) {
            $enabledenrols = explode(',', $CFG->enrol_plugins_enabled);
            $enabledenrols = array_unique($enabledenrols);
            $enabledenrols = array_flip($enabledenrols);
            unset($enabledenrols[$this->name]);
            $enabledenrols = array_flip($enabledenrols);
            if (is_array($enabledenrols)) {
                set_config('enrol_plugins_enabled', implode(',', $enabledenrols));
            }
        }

        parent::uninstall_cleanup();
    }
}
