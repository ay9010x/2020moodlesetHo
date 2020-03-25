<?php



require_once(dirname(__FILE__) . '/../config.php');
require_once($CFG->libdir.'/adminlib.php');
require_once($CFG->libdir.'/tablelib.php');

admin_externalpage_setup('managetools');

echo $OUTPUT->header();
echo $OUTPUT->heading(get_string('tools', 'admin'));


$struninstall = get_string('uninstallplugin', 'core_admin');

$table = new flexible_table('toolplugins_administration_table');
$table->define_columns(array('name', 'version', 'uninstall'));
$table->define_headers(array(get_string('plugin'), get_string('version'), $struninstall));
$table->define_baseurl($PAGE->url);
$table->set_attribute('id', 'toolplugins');
$table->set_attribute('class', 'admintable generaltable');
$table->setup();

$plugins = array();
foreach (core_component::get_plugin_list('tool') as $plugin => $plugindir) {
    if (get_string_manager()->string_exists('pluginname', 'tool_' . $plugin)) {
        $strpluginname = get_string('pluginname', 'tool_' . $plugin);
    } else {
        $strpluginname = $plugin;
    }
    $plugins[$plugin] = $strpluginname;
}
core_collator::asort($plugins);

$like = $DB->sql_like('plugin', '?', true, true, false, '|');
$params = array('tool|_%');
$installed = $DB->get_records_select('config_plugins', "$like AND name = 'version'", $params);
$versions = array();
foreach ($installed as $config) {
    $name = preg_replace('/^tool_/', '', $config->plugin);
    $versions[$name] = $config->value;
    if (!isset($plugins[$name])) {
        $plugins[$name] = $name;
    }
}

foreach ($plugins as $plugin => $name) {
    $uninstall = '';
    if ($uninstallurl = core_plugin_manager::instance()->get_uninstall_url('tool_'.$plugin, 'manage')) {
        $uninstall = html_writer::link($uninstallurl, $struninstall);
    }

    if (!isset($versions[$plugin])) {
        if (file_exists("$CFG->dirroot/$CFG->admin/tool/$plugin/version.php")) {
                        $version = '?';
        } else {
                        $version = '-';
        }
    } else {
        $version = $versions[$plugin];
        if (file_exists("$CFG->dirroot/$CFG->admin/tool/$plugin")) {
            $version = $versions[$plugin];
        } else {
                        $name = '<span class="notifyproblem">'.$name.' ('.get_string('missingfromdisk').')</span>';
            $version = $versions[$plugin];
        }
    }

    $table->add_data(array($name, $version, $uninstall));
}

$table->print_html();

echo $OUTPUT->footer();
