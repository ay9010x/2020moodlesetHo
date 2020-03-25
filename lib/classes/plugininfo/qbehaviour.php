<?php


namespace core\plugininfo;

use moodle_url, core_plugin_manager;

defined('MOODLE_INTERNAL') || die();


class qbehaviour extends base {
    
    public static function get_enabled_plugins() {
        $plugins = core_plugin_manager::instance()->get_installed_plugins('qbehaviour');
        if (!$plugins) {
            return array();
        }
        if ($disabled = get_config('question', 'disabledbehaviours')) {
            $disabled = explode(',', $disabled);
        } else {
            $disabled = array();
        }

        $enabled = array();
        foreach ($plugins as $plugin => $version) {
            if (in_array($plugin, $disabled)) {
                continue;
            }
            $enabled[$plugin] = $plugin;
        }

        return $enabled;
    }

    public function is_uninstall_allowed() {
        global $DB;

        if ($this->name === 'missing') {
                        return false;
        }

        return !$DB->record_exists('question_attempts', array('behaviour' => $this->name));
    }

    
    public function uninstall_cleanup() {
        if ($disabledbehaviours = get_config('question', 'disabledbehaviours')) {
            $disabledbehaviours = explode(',', $disabledbehaviours);
            $disabledbehaviours = array_unique($disabledbehaviours);
        } else {
            $disabledbehaviours = array();
        }
        if (($key = array_search($this->name, $disabledbehaviours)) !== false) {
            unset($disabledbehaviours[$key]);
            set_config('disabledbehaviours', implode(',', $disabledbehaviours), 'question');
        }

        if ($behaviourorder = get_config('question', 'behavioursortorder')) {
            $behaviourorder = explode(',', $behaviourorder);
            $behaviourorder = array_unique($behaviourorder);
        } else {
            $behaviourorder = array();
        }
        if (($key = array_search($this->name, $behaviourorder)) !== false) {
            unset($behaviourorder[$key]);
            set_config('behavioursortorder', implode(',', $behaviourorder), 'question');
        }

        parent::uninstall_cleanup();
    }

    
    public static function get_manage_url() {
        return new moodle_url('/admin/qbehaviours.php');
    }
}

