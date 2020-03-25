<?php



require(__DIR__.'../../config.php');
require_once("$CFG->libdir/adminlib.php");

$auth = optional_param('auth', '', PARAM_RAW);
if (!core_component::is_valid_plugin_name('auth', $auth)) {
    $auth = '';
} else if (!file_exists("$CFG->dirroot/auth/$auth/auth.php")) {
    $auth = '';
}

require_login();
require_capability('moodle/site:config', context_system::instance());

navigation_node::override_active_url(new moodle_url('/admin/settings.php', array('section'=>'manageauths')));
admin_externalpage_setup('authtestsettings');

$returnurl = new moodle_url('/admin/settings.php', array('section'=>'manageauths'));

echo $OUTPUT->header();

if (!$auth) {
    $options = array();
    $plugins = core_component::get_plugin_list('auth');
    foreach ($plugins as $name => $fulldir) {
        $plugin = get_auth_plugin($name);
        if (!$plugin or !method_exists($plugin, 'test_settings')) {
            continue;
        }
        $options[$name] = get_string('pluginname', 'auth_'.$name);
    }

    if (!$options) {
        redirect($returnurl);
    }

    echo $OUTPUT->heading(get_string('testsettings', 'core_auth'));

    $url = new moodle_url('/auth/test_settings.php', array('sesskey'=>sesskey()));
    echo $OUTPUT->single_select($url, 'auth', $options);

    echo $OUTPUT->footer();
}

$plugin = get_auth_plugin($auth);
if (!$plugin or !method_exists($plugin, 'test_settings')) {
    redirect($returnurl);
}

echo $OUTPUT->heading(get_string('testsettingsheading', 'core_auth', get_string('pluginname', 'auth_'.$auth)));

$plugin->test_settings();

echo $OUTPUT->continue_button($returnurl);
echo $OUTPUT->footer();
