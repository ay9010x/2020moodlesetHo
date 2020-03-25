<?php




define('CLI_SCRIPT', true);
define('CACHE_DISABLE_ALL', true);

if (isset($_SERVER['REMOTE_ADDR'])) {
    exit(1);
}

if (function_exists('opcache_reset')) {
    opcache_reset();
}

$help =
"Advanced command line Moodle database installer.
Please note you must execute this script with the same uid as apache.

Site defaults may be changed via local/defaults.php.

Options:
--lang=CODE           Installation and default site language. Default is en.
--adminuser=USERNAME  Username for the moodle admin account. Default is admin.
--adminpass=PASSWORD  Password for the moodle admin account.
--adminemail=STRING   Email address for the moodle admin account.
--agree-license       Indicates agreement with software license.
--fullname=STRING     Name of the site
--shortname=STRING    Name of the site
-h, --help            Print out this help

Example:
\$sudo -u www-data /usr/bin/php admin/cli/install_database.php --lang=cs --adminpass=soMePass123 --agree-license
";

if (version_compare(phpversion(), "5.4.4") < 0) {
    $phpversion = phpversion();
        fwrite(STDERR, "Moodle 2.7 or later requires at least PHP 5.4.4 (currently using version $phpversion).\n");
    fwrite(STDERR, "Please upgrade your server software or install older Moodle version.\n");
    exit(1);
}

$configfile = dirname(dirname(dirname(__FILE__))).'/config.php';
if (!file_exists($configfile)) {
    fwrite(STDERR, 'config.php does not exist, can not continue');     fwrite(STDERR, "\n");
    exit(1);
}

require($configfile);

require_once($CFG->libdir.'/clilib.php');
require_once($CFG->libdir.'/installlib.php');
require_once($CFG->libdir.'/adminlib.php');
require_once($CFG->libdir.'/componentlib.class.php');

if ($DB->get_tables() ) {
    cli_error(get_string('clitablesexist', 'install'));
}

$CFG->early_install_lang = true;
get_string_manager(true);

raise_memory_limit(MEMORY_EXTRA);

list($options, $unrecognized) = cli_get_params(
    array(
        'lang'              => 'en',
        'adminuser'         => 'admin',
        'adminpass'         => '',
        'adminemail'        => '',
        'fullname'          => '',
        'shortname'         => '',
        'agree-license'     => false,
        'help'              => false
    ),
    array(
        'h' => 'help'
    )
);


if ($options['help']) {
    echo $help;
    die;
}

if (!$options['agree-license']) {
    cli_error('You have to agree to the license. --help prints out the help'); }

if ($options['adminpass'] === true or $options['adminpass'] === '') {
    cli_error('You have to specify admin password. --help prints out the help'); }

if (!empty($options['adminemail']) && !validate_email($options['adminemail'])) {
    $a = (object) array('option' => 'adminemail', 'value' => $options['adminemail']);
    cli_error(get_string('cliincorrectvalueerror', 'admin', $a));
}

$options['lang'] = clean_param($options['lang'], PARAM_SAFEDIR);
if (!file_exists($CFG->dirroot.'/install/lang/'.$options['lang'])) {
    $options['lang'] = 'en';
}
$CFG->lang = $options['lang'];

if ($CFG->lang !== 'en') {
    make_upload_directory('lang');
    $installer = new lang_installer($CFG->lang);
    $results = $installer->run();
    foreach ($results as $langcode => $langstatus) {
        if ($langstatus === lang_installer::RESULT_DOWNLOADERROR) {
            $a       = new stdClass();
            $a->url  = $installer->lang_pack_url($langcode);
            $a->dest = $CFG->dataroot.'/lang';
            cli_problem(get_string('remotedownloaderror', 'error', $a));
        }
    }
}

$CFG->early_install_lang = false;
get_string_manager(true);

require("$CFG->dirroot/version.php");

require_once($CFG->libdir . '/environmentlib.php');
list($envstatus, $environment_results) = check_moodle_environment(normalize_version($release), ENV_SELECT_RELEASE);
if (!$envstatus) {
    $errors = environment_get_errors($environment_results);
    cli_heading(get_string('environment', 'admin'));
    foreach ($errors as $error) {
        list($info, $report) = $error;
        echo "!! $info !!\n$report\n\n";
    }
    exit(1);
}

$failed = array();
if (!core_plugin_manager::instance()->all_plugins_ok($version, $failed)) {
    cli_problem(get_string('pluginscheckfailed', 'admin', array('pluginslist' => implode(', ', array_unique($failed)))));
    cli_error(get_string('pluginschecktodo', 'admin'));
}

install_cli_database($options, true);

echo get_string('cliinstallfinished', 'install')."\n";
exit(0); 