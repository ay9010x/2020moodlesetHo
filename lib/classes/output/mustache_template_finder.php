<?php



namespace core\output;

use coding_exception;
use moodle_exception;
use core_component;
use theme_config;


class mustache_template_finder {

    
    public static function get_template_directories_for_component($component, $themename = '') {
        global $CFG, $PAGE;

                if ($themename == '') {
            $themename = $PAGE->theme->name;
        }

                $component = clean_param($component, PARAM_COMPONENT);
        $themename = clean_param($themename, PARAM_COMPONENT);

                $dirs = array();
        $compdirectory = core_component::get_component_directory($component);
        if (!$compdirectory) {
            throw new coding_exception("Component was not valid: " . s($component));
        }

                $parents = array();
        if ($themename === $PAGE->theme->name) {
            $parents = $PAGE->theme->parents;
        } else {
            $themeconfig = theme_config::load($themename);
            $parents = $themeconfig->parents;
        }

                $dirs[] = $CFG->dirroot . '/theme/' . $themename . '/templates/' . $component . '/';
        if (isset($CFG->themedir)) {
            $dirs[] = $CFG->themedir . '/' . $themename . '/templates/' . $component . '/';
        }
                        foreach ($parents as $parent) {
            $dirs[] = $CFG->dirroot . '/theme/' . $parent . '/templates/' . $component . '/';
            if (isset($CFG->themedir)) {
                $dirs[] = $CFG->themedir . '/' . $parent . '/templates/' . $component . '/';
            }
        }

        $dirs[] = $compdirectory . '/templates/';

        return $dirs;
    }

    
    public static function get_template_filepath($name, $themename = '') {
        global $CFG, $PAGE;

        if (strpos($name, '/') === false) {
            throw new coding_exception('Templates names must be specified as "componentname/templatename"' .
                                       ' (' . s($name) . ' requested) ');
        }
        list($component, $templatename) = explode('/', $name, 2);
        $component = clean_param($component, PARAM_COMPONENT);
        if (strpos($templatename, '/') !== false) {
            throw new coding_exception('Templates cannot be placed in sub directories (' . s($name) . ' requested)');
        }

        $dirs = self::get_template_directories_for_component($component, $themename);

        foreach ($dirs as $dir) {
            $candidate = $dir . $templatename . '.mustache';
            if (file_exists($candidate)) {
                return $candidate;
            }
        }

        throw new moodle_exception('filenotfound', 'error');
    }
}
