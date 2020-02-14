<?php



defined('MOODLE_INTERNAL') || die();

function xmldb_enrol_flatfile_install() {
    global $CFG, $DB;

        $roles = get_all_roles();
    foreach ($roles as $role) {
        set_config('map_'.$role->id, $role->shortname, 'enrol_flatfile');
    }
}
