<?php


namespace core\plugininfo;

use core_component, core_plugin_manager, moodle_url, coding_exception;

defined('MOODLE_INTERNAL') || die();


class portfolio extends base {
    
    public static function get_enabled_plugins() {
        global $DB;

        $enabled = array();
        $rs = $DB->get_recordset('portfolio_instance', array('visible'=>1), 'plugin ASC', 'plugin');
        foreach ($rs as $repository) {
            $enabled[$repository->plugin] = $repository->plugin;
        }

        return $enabled;
    }

    
    public static function get_manage_url() {
        return new moodle_url('/admin/portfolio.php');
    }

    
    public function is_uninstall_allowed() {
        return true;
    }

    
    public function uninstall_cleanup() {
        global $DB;

                $count = $DB->count_records('portfolio_instance', array('plugin' => $this->name));
        if ($count > 0) {
                        $rec = $DB->get_record('portfolio_instance', array('plugin' => $this->name));

                        $DB->delete_records('portfolio_instance_config', array('instance' => $rec->id));
                        $DB->delete_records('portfolio_instance_user', array('instance' => $rec->id));
                        $DB->delete_records('portfolio_log', array('portfolio' => $rec->id));
                        $DB->delete_records('portfolio_tempdata', array('instance' => $rec->id));

                        $DB->delete_records('portfolio_instance', array('id' => $rec->id));
        }

        parent::uninstall_cleanup();
    }
}