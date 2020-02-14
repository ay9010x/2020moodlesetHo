<?php



defined('MOODLE_INTERNAL') || die();

$ADMIN->add('editoratto', new admin_category('atto_collapse', new lang_string('pluginname', 'atto_collapse')));

$settings = new admin_settingpage('atto_collapse_settings', new lang_string('settings', 'atto_collapse'));
if ($ADMIN->fulltree) {
        $name = new lang_string('showgroups', 'atto_collapse');
    $desc = new lang_string('showgroups_desc', 'atto_collapse');
    $default = 5;
    $options = array_combine(range(1, 20), range(1, 20));

    $setting = new admin_setting_configselect('atto_collapse/showgroups',
                                              $name,
                                              $desc,
                                              $default,
                                              $options);
    $settings->add($setting);
}
