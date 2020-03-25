<?php


namespace core\plugininfo;

use moodle_url;

defined('MOODLE_INTERNAL') || die();


class theme extends base {
    public function is_uninstall_allowed() {
        global $CFG;

        if ($this->name === 'base' or $this->name === 'bootstrapbase') {
                        return false;
        }

        if (!empty($CFG->theme) and $CFG->theme === $this->name) {
                        return false;
        }

        return true;
    }

    
    public function uninstall_cleanup() {
        global $DB;

        $DB->set_field('course', 'theme', '', array('theme'=>$this->name));
        $DB->set_field('course_categories', 'theme', '', array('theme'=>$this->name));
        $DB->set_field('user', 'theme', '', array('theme'=>$this->name));
        $DB->set_field('mnet_host', 'theme', '', array('theme'=>$this->name));

        if (get_config('core', 'thememobile') === $this->name) {
            unset_config('thememobile');
        }
        if (get_config('core', 'themetablet') === $this->name) {
            unset_config('themetablet');
        }
        if (get_config('core', 'themelegacy') === $this->name) {
            unset_config('themelegacy');
        }

        $themelist = get_config('core', 'themelist');
        if (!empty($themelist)) {
            $themes = explode(',', $themelist);
            $key = array_search($this->name, $themes);
            if ($key !== false) {
                unset($themes[$key]);
                set_config('themelist', implode(',', $themes));
            }
        }

        parent::uninstall_cleanup();
    }

    
    public static function get_manage_url() {
        return new moodle_url('/theme/index.php');
    }
}
