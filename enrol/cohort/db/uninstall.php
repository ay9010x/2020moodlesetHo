<?php



defined('MOODLE_INTERNAL') || die();

function xmldb_enrol_cohort_uninstall() {
    global $CFG, $DB;

    $cohort = enrol_get_plugin('cohort');
    $rs = $DB->get_recordset('enrol', array('enrol'=>'cohort'));
    foreach ($rs as $instance) {
        $cohort->delete_instance($instance);
    }
    $rs->close();

    role_unassign_all(array('component'=>'enrol_cohort'));

    return true;
}
