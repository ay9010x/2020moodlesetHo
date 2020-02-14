<?php




defined('MOODLE_INTERNAL') || die();



function xmldb_assignsubmission_comments_install() {
    global $CFG;

    require_once($CFG->dirroot . '/mod/assign/adminlib.php');
        $pluginmanager = new assign_plugin_manager('assignsubmission');

    $pluginmanager->move_plugin('comments', 'down');
    $pluginmanager->move_plugin('comments', 'down');

    return true;
}
