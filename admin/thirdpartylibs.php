<?php



require_once('../config.php');
require_once($CFG->libdir.'/adminlib.php');
require_once($CFG->libdir.'/tablelib.php');

admin_externalpage_setup('thirdpartylibs');

echo $OUTPUT->header();
echo $OUTPUT->heading(get_string('thirdpartylibs', 'core_admin'));

$files = array('core' => "$CFG->libdir/thirdpartylibs.xml");

$plugintypes = core_component::get_plugin_types();
foreach ($plugintypes as $type => $ignored) {
    $plugins = core_component::get_plugin_list_with_file($type, 'thirdpartylibs.xml', false);
    foreach ($plugins as $plugin => $path) {
        $files[$type.'_'.$plugin] = $path;
    }
}

$table = new html_table();
$table->head = array(
    get_string('thirdpartylibrary', 'core_admin'), get_string('version'),
    get_string('thirdpartylibrarylocation', 'core_admin'), get_string('license'));
$table->align = array('left', 'left', 'left', 'left');
$table->id = 'thirdpartylibs';
$table->attributes['class'] = 'admintable generaltable';
$table->data  = array();

foreach ($files as $component => $xmlpath) {
    $xml = simplexml_load_file($xmlpath);
    foreach ($xml as $lib) {
        $base = realpath(dirname($xmlpath));
        $location = substr($base, strlen($CFG->dirroot)).'/'.$lib->location;
        if (is_dir($CFG->dirroot.$location)) {
            $location .= '/';
        }
        $version = '';
        if (!empty($lib->version)) {
            $version = $lib->version;
        }
        $license = $lib->license;
        if (!empty($lib->licenseversion)) {
            $license .= ' '.$lib->licenseversion;
        }

        $table->data[] = new html_table_row(array($lib->name, $version, $location, $license));
    }
}

echo html_writer::table($table);

echo $OUTPUT->footer();
