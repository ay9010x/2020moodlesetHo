<?php



require_once(dirname(dirname(__FILE__)) . '/config.php');
require_once($CFG->libdir.'/adminlib.php');
require_once($CFG->libdir.'/tablelib.php');


admin_externalpage_setup('manageplagiarismplugins');

echo $OUTPUT->header();


$txt = get_strings(array('settings', 'name', 'version'));
$txt->uninstall = get_string('uninstallplugin', 'core_admin');

$plagiarismplugins = core_component::get_plugin_list('plagiarism');
if (empty($plagiarismplugins)) {
    echo $OUTPUT->notification(get_string('nopluginsinstalled', 'plagiarism'));
    echo $OUTPUT->footer();
    exit;
}

echo $OUTPUT->heading(get_string('availableplugins', 'plagiarism'), 3, 'main');
echo $OUTPUT->box_start('generalbox authsui');

$table = new html_table();
$table->head  = array($txt->name, $txt->version, $txt->uninstall, $txt->settings);
$table->colclasses = array('mdl-left', 'mdl-align', 'mdl-align', 'mdl-align');
$table->data  = array();
$table->attributes['class'] = 'manageplagiarismtable generaltable';

$authcount = count($plagiarismplugins);
foreach ($plagiarismplugins as $plugin => $dir) {
    if (file_exists($dir.'/settings.php')) {
        $displayname = "<span>".get_string($plugin, 'plagiarism_'.$plugin)."</span>";
                $url = new moodle_url("/plagiarism/$plugin/settings.php");
        $settings = html_writer::link($url, $txt->settings);
                $version = get_config('plagiarism_' . $plugin);
        if (!empty($version->version)) {
            $version = $version->version;
        } else {
            $version = '?';
        }
                $uninstall = '';
        if ($uninstallurl = core_plugin_manager::instance()->get_uninstall_url('plagiarism_'.$plugin, 'manage')) {
            $uninstall = html_writer::link($uninstallurl, $txt->uninstall);
        }
        $table->data[] = array($displayname, $version, $uninstall, $settings);
    }
}
echo html_writer::table($table);
echo get_string('configplagiarismplugins', 'plagiarism');
echo $OUTPUT->box_end();

echo $OUTPUT->footer();
