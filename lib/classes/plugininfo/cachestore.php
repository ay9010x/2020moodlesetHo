<?php


namespace core\plugininfo;

defined('MOODLE_INTERNAL') || die();


class cachestore extends base {

    public function is_uninstall_allowed() {
        $instance = \cache_config::instance();
        foreach ($instance->get_all_stores() as $store) {
            if ($store['plugin'] == $this->name) {
                return false;
            }
        }
        return true;
    }
}
