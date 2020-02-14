<?php



defined('MOODLE_INTERNAL') || die();

$ADMIN->add('editorsettings', new admin_category('editoratto', $editor->displayname, $editor->is_enabled() === false));

$settings = new admin_settingpage('editorsettingsatto', new lang_string('settings', 'editor_atto'));
if ($ADMIN->fulltree) {
    require_once(__DIR__ . '/adminlib.php');
    $settings->add(new editor_atto_subplugins_setting());
    $name = new lang_string('toolbarconfig', 'editor_atto');
    $desc = new lang_string('toolbarconfig_desc', 'editor_atto');
    $default = 'collapse = collapse
style1 = title, bold, italic
list = unorderedlist, orderedlist
links = link
files = image, media, managefiles
style2 = underline, strike, subscript, superscript
align = align
indent = indent
insert = equation, charmap, table, clear
undo = undo
accessibility = accessibilitychecker, accessibilityhelper
other = html';
    $setting = new editor_atto_toolbar_setting('editor_atto/toolbar', $name, $desc, $default);

    $settings->add($setting);
}

$name = new lang_string('autosavefrequency', 'editor_atto');
$desc = new lang_string('autosavefrequency_desc', 'editor_atto');
$default = 60;
$setting = new admin_setting_configduration('editor_atto/autosavefrequency', $name, $desc, $default);
$settings->add($setting);

$ADMIN->add('editoratto', $settings);

foreach (core_plugin_manager::instance()->get_plugins_of_type('atto') as $plugin) {
    
    $plugin->load_settings($ADMIN, 'editoratto', $hassiteconfig);
}

unset($settings);
$settings = null;

