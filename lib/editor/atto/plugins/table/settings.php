<?php



defined('MOODLE_INTERNAL') || die();

$ADMIN->add('editoratto', new admin_category('atto_table', new lang_string('pluginname', 'atto_table')));

$settings = new admin_settingpage('atto_table_settings', new lang_string('settings', 'atto_table'));
if ($ADMIN->fulltree) {
    $name = new lang_string('allowborder', 'atto_table');
    $desc = new lang_string('allowborder_desc', 'atto_table');
    $default = 0;

    $setting = new admin_setting_configcheckbox('atto_table/allowborders',
                                                $name,
                                                $desc,
                                                $default);
    $settings->add($setting);

    $name = new lang_string('allowbackgroundcolour', 'atto_table');
    $default = 0;

    $setting = new admin_setting_configcheckbox('atto_table/allowbackgroundcolour',
                                                $name,
                                                '',
                                                $default);
    $settings->add($setting);

    $name = new lang_string('allowwidth', 'atto_table');
    $default = 0;

    $setting = new admin_setting_configcheckbox('atto_table/allowwidth',
                                                $name,
                                                '',
                                                $default);
    $settings->add($setting);
}
