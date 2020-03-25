<?php


namespace core\plugininfo;

use moodle_url, part_of_admin_tree, admin_settingpage, admin_externalpage;

defined('MOODLE_INTERNAL') || die();


class block extends base {
    
    public static function get_enabled_plugins() {
        global $DB;

        return $DB->get_records_menu('block', array('visible'=>1), 'name ASC', 'name, name AS val');
    }

    
    public function __get($name) {
        if ($name === 'visible') {
            debugging('This is now an instance of plugininfo_block, please use $block->is_enabled() instead of $block->visible', DEBUG_DEVELOPER);
            return ($this->is_enabled() !== false);
        }
        return parent::__get($name);
    }

    public function init_display_name() {

        if (get_string_manager()->string_exists('pluginname', 'block_' . $this->name)) {
            $this->displayname = get_string('pluginname', 'block_' . $this->name);

        } else if (($block = block_instance($this->name)) !== false) {
            $this->displayname = $block->get_title();

        } else {
            parent::init_display_name();
        }
    }

    public function get_settings_section_name() {
        return 'blocksetting' . $this->name;
    }

    public function load_settings(part_of_admin_tree $adminroot, $parentnodename, $hassiteconfig) {
        global $CFG, $USER, $DB, $OUTPUT, $PAGE;         $ADMIN = $adminroot;         $plugininfo = $this;         $block = $this;      
        if (!$this->is_installed_and_upgraded()) {
            return;
        }

        $section = $this->get_settings_section_name();

        if (!$hassiteconfig || (($blockinstance = block_instance($this->name)) === false)) {
            return;
        }

        $settings = null;
        if ($blockinstance->has_config()) {
            if (file_exists($this->full_path('settings.php'))) {
                $settings = new admin_settingpage($section, $this->displayname,
                    'moodle/site:config', $this->is_enabled() === false);
                include($this->full_path('settings.php'));             }
        }
        if ($settings) {
            $ADMIN->add($parentnodename, $settings);
        }
    }

    public function is_uninstall_allowed() {
        if ($this->name === 'settings' or $this->name === 'navigation') {
            return false;
        }
        return true;
    }

    
    public static function get_manage_url() {
        return new moodle_url('/admin/blocks.php');
    }

    
    public function get_uninstall_extra_warning() {
        global $DB;

        if (!$count = $DB->count_records('block_instances', array('blockname'=>$this->name))) {
            return '';
        }

        return '<p>'.get_string('uninstallextraconfirmblock', 'core_plugin', array('instances'=>$count)).'</p>';
    }

    
    public function uninstall_cleanup() {
        global $DB, $CFG;

        if ($block = $DB->get_record('block', array('name'=>$this->name))) {
                        if (file_exists("$CFG->dirroot/blocks/$block->name/block_$block->name.php")) {
                $blockobject = block_instance($block->name);
                if ($blockobject) {
                    $blockobject->before_delete();                  }
            }

                        $instances = $DB->get_records('block_instances', array('blockname' => $block->name));
            foreach ($instances as $instance) {
                blocks_delete_instance($instance);
            }

                        $DB->delete_records('block', array('id'=>$block->id));
        }

        parent::uninstall_cleanup();
    }
}
