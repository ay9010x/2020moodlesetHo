<?php




require_once(dirname(dirname(__FILE__)) . '/config.php');
require_once($CFG->libdir.'/adminlib.php');
require_once($CFG->libdir.'/tablelib.php');

admin_externalpage_setup('managelocalplugins');

echo $OUTPUT->header();
echo $OUTPUT->heading(get_string('localplugins'));


$table = new flexible_table('localplugins_administration_table');
$table->define_columns(array('name', 'version', 'uninstall'));
$table->define_headers(array(get_string('plugin'), get_string('version'), get_string('uninstallplugin', 'core_admin')));
$table->define_baseurl($PAGE->url);
$table->set_attribute('id', 'localplugins');
$table->set_attribute('class', 'admintable generaltable');
$table->setup();

$plugins = array();
foreach (core_component::get_plugin_list('local') as $plugin => $plugindir) {
    if (get_string_manager()->string_exists('pluginname', 'local_' . $plugin)) {
        $strpluginname = get_string('pluginname', 'local_' . $plugin);
    } else {
        $strpluginname = $plugin;
    }
    $plugins[$plugin] = $strpluginname;
}
core_collator::asort($plugins);

foreach ($plugins as $plugin => $name) {
    $uninstall = '';
    if ($uninstallurl = core_plugin_manager::instance()->get_uninstall_url('local_'.$plugin, 'manage')) {
        $uninstall = html_writer::link($uninstallurl, get_string('uninstallplugin', 'core_admin'));
    }

    $version = get_config('local_' . $plugin);
    if (!empty($version->version)) {
        $version = $version->version;
    } else {
        $version = '?';
    }

    $table->add_data(array($name, $version, $uninstall));
}

$table->print_html();

echo $OUTPUT->footer();
