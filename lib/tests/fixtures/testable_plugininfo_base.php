<?php



defined('MOODLE_INTERNAL') || die();


class testable_plugininfo_base extends \core\plugininfo\base {

    public static function fake_plugin_instance($type, $typerootdir, $name, $namerootdir, $typeclass, $pluginman) {
        return self::make_plugin_instance($type, $typerootdir, $name, $namerootdir, $typeclass, $pluginman);
    }

    public function init_display_name() {
        $this->displayname = 'Testable fake pluginfo instance';
    }

    public function load_disk_version() {
        $this->versiondisk = null;
        $this->versionrequires = null;
        $this->dependencies = array();
    }

    public function load_db_version() {
        $this->versiondb = null;
    }

    public function init_is_standard() {
        $this->source = core_plugin_manager::PLUGIN_SOURCE_EXTENSION;
    }
}
