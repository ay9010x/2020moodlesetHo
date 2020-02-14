<?php



defined('MOODLE_INTERNAL') || die();

require_once("$CFG->libdir/adminlib.php");



class enrol_flatfile_role_setting extends admin_setting_configtext {

    public function __construct($role) {
        parent::__construct('enrol_flatfile/map_'.$role->id, $role->localname, '', $role->shortname);
    }

    public function config_read($name) {
        $value = parent::config_read($name);
        if (is_null($value)) {
                                    $value = '';
        }
        return $value;
    }

    public function config_write($name, $value) {
        if ($value === '') {
                                    $value = null;
        }
        return parent::config_write($name, $value);
    }
}
