<?php



require(__DIR__.'../../config.php');
require_once("$CFG->libdir/adminlib.php");

$enrol = optional_param('enrol', '', PARAM_RAW);
if (!core_component::is_valid_plugin_name('enrol', $enrol)) {
    $enrol = '';
} else if (!file_exists("$CFG->dirroot/enrol/$enrol/lib.php")) {
    $enrol = '';
}

require_login();
require_capability('moodle/site:config', context_system::instance());

navigation_node::override_active_url(new moodle_url('/admin/settings.php', array('section'=>'manageenrols')));
admin_externalpage_setup('enroltestsettings');

$returnurl = new moodle_url('/admin/settings.php', array('section'=>'manageenrols'));

echo $OUTPUT->header();

if (!$enrol) {
    $options = array();
    $plugins = core_component::get_plugin_list('enrol');
    foreach ($plugins as $name => $fulldir) {
        $plugin = enrol_get_plugin($name);
        if (!$plugin or !method_exists($plugin, 'test_settings')) {
            continue;
        }
        $options[$name] = get_string('pluginname', 'enrol_'.$name);
    }

    if (!$options) {
        redirect($returnurl);
    }

    echo $OUTPUT->heading(get_string('testsettings', 'core_enrol'));

    $url = new moodle_url('/enrol/test_settings.php', array('sesskey'=>sesskey()));
    echo $OUTPUT->single_select($url, 'enrol', $options);

    echo $OUTPUT->footer();
}

$plugin = enrol_get_plugin($enrol);
if (!$plugin or !method_exists($plugin, 'test_settings')) {
    redirect($returnurl);
}

echo $OUTPUT->heading(get_string('testsettingsheading', 'core_enrol', get_string('pluginname', 'enrol_'.$enrol)));

$plugin->test_settings();

echo $OUTPUT->continue_button($returnurl);
echo $OUTPUT->footer();
