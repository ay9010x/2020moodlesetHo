<?php


defined('MOODLE_INTERNAL') || die();



function xmldb_assignsubmission_onlinetext_install() {
    global $CFG;

        require_once($CFG->dirroot . '/mod/assign/adminlib.php');
    $pluginmanager = new assign_plugin_manager('assignsubmission');

    $pluginmanager->move_plugin('onlinetext', 'up');
    $pluginmanager->move_plugin('onlinetext', 'up');

    return true;
}
