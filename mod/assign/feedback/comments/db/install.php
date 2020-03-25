<?php



defined('MOODLE_INTERNAL') || die();


function xmldb_assignfeedback_comments_install() {
    global $CFG;

    require_once($CFG->dirroot . '/mod/assign/adminlib.php');

        $pluginmanager = new assign_plugin_manager('assignfeedback');
    $pluginmanager->move_plugin('comments', 'up');

    return true;
}
