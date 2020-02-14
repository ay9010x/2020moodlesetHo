<?php



defined('MOODLE_INTERNAL') || die();

global $PAGE;

if ($hassiteconfig) {
    $settings = new admin_settingpage('tool_recyclebin', get_string('pluginname', 'tool_recyclebin'));
    $ADMIN->add('tools', $settings);

    $settings->add(new admin_setting_configcheckbox(
        'tool_recyclebin/coursebinenable',
        new lang_string('coursebinenable', 'tool_recyclebin'),
        '',
        1
    ));

    $settings->add(new admin_setting_configduration(
        'tool_recyclebin/coursebinexpiry',
        new lang_string('coursebinexpiry', 'tool_recyclebin'),
        new lang_string('coursebinexpiry_desc', 'tool_recyclebin'),
        WEEKSECS
    ));

    $settings->add(new admin_setting_configcheckbox(
        'tool_recyclebin/categorybinenable',
        new lang_string('categorybinenable', 'tool_recyclebin'),
        '',
        1
    ));

    $settings->add(new admin_setting_configduration(
        'tool_recyclebin/categorybinexpiry',
        new lang_string('categorybinexpiry', 'tool_recyclebin'),
        new lang_string('categorybinexpiry_desc', 'tool_recyclebin'),
        WEEKSECS
    ));

    $settings->add(new admin_setting_configcheckbox(
        'tool_recyclebin/autohide',
        new lang_string('autohide', 'tool_recyclebin'),
        new lang_string('autohide_desc', 'tool_recyclebin'),
        1
    ));
}
