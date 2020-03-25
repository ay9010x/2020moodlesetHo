<?php



defined('MOODLE_INTERNAL') || die();

function xmldb_enrol_meta_uninstall() {
    global $CFG, $DB;

    $meta = enrol_get_plugin('meta');
    $rs = $DB->get_recordset('enrol', array('enrol'=>'meta'));
    foreach ($rs as $instance) {
        $meta->delete_instance($instance);
    }
    $rs->close();

    role_unassign_all(array('component'=>'enrol_meta'));

    return true;
}
