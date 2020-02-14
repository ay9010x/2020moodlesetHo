<?php


namespace tool_templatelibrary;

use core_component;
use core\output\mustache_template_finder;
use coding_exception;
use moodle_exception;
use required_capability_exception;
use stdClass;


class api {

    
    public static function list_templates($component = '', $search = '', $themename = '') {
        global $CFG, $PAGE;

        if (empty($themename)) {
            $themename = $PAGE->theme->name;
        }
        $themeconfig = \theme_config::load($themename);

        $templatedirs = array();
        $results = array();

        if ($component !== '') {
                        $dirs = mustache_template_finder::get_template_directories_for_component($component, $themename);

            $templatedirs[$component] = $dirs;
        } else {

                        $templatedirs['core'] = mustache_template_finder::get_template_directories_for_component('core', $themename);

                        $subsystems = core_component::get_core_subsystems();
            foreach ($subsystems as $subsystem => $dir) {
                $dir .= '/templates';
                if (is_dir($dir)) {
                    $dirs = mustache_template_finder::get_template_directories_for_component('core_' . $subsystem, $themename);
                    $templatedirs['core_' . $subsystem] = $dirs;
                }
            }

                        $plugintypes = core_component::get_plugin_types();
            foreach ($plugintypes as $type => $dir) {
                $plugins = core_component::get_plugin_list_with_file($type, 'templates', false);
                foreach ($plugins as $plugin => $dir) {
                    if ($type == 'theme' && $plugin != $themename && !in_array($plugin, $themeconfig->parents)) {
                        continue;
                    }
                    if (!empty($dir) && is_dir($dir)) {
                        $pluginname = $type . '_' . $plugin;
                        $dirs = mustache_template_finder::get_template_directories_for_component($pluginname, $themename);
                        $templatedirs[$pluginname] = $dirs;
                    }
                }
            }
        }

        foreach ($templatedirs as $templatecomponent => $dirs) {
            foreach ($dirs as $dir) {
                                $files = glob($dir . '/*.mustache');

                foreach ($files as $file) {
                    $templatename = basename($file, '.mustache');
                    if ($search == '' || strpos($templatename, $search) !== false) {
                        $results[$templatecomponent . '/' . $templatename] = 1;
                    }
                }
            }
        }
        $results = array_keys($results);
        sort($results);
        return $results;
    }

    
    public static function load_canonical_template($component, $template) {
                $dirs = mustache_template_finder::get_template_directories_for_component($component);
        $filename = false;
        $themedir = core_component::get_plugin_types()['theme'];

        foreach ($dirs as $dir) {
                        if (strpos($dir, $themedir) === 0) {
                continue;
            }

            $candidate = $dir . $template . '.mustache';
            if (file_exists($candidate)) {
                $filename = $candidate;
                break;
            }
        }

        if ($filename === false) {
                        return false;
        }

        $templatestr = file_get_contents($filename);
        return $templatestr;
    }


}
