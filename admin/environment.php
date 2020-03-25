<?php




require_once('../config.php');
require_once($CFG->libdir.'/adminlib.php');
require_once($CFG->libdir.'/environmentlib.php');
require_once($CFG->libdir.'/componentlib.class.php');

$action  = optional_param('action', '', PARAM_ALPHANUMEXT);
$version = optional_param('version', '', PARAM_FILE); 
$extraurlparams = array();
if ($version) {
    $extraurlparams['version'] = $version;
}
admin_externalpage_setup('environment', '', $extraurlparams);

if ($action == 'updatecomponent' && confirm_sesskey()) {
        if ($cd = new component_installer('https://download.moodle.org',
                                      'environment',
                                      'environment.zip')) {
        $status = $cd->install();         switch ($status) {
            case COMPONENT_ERROR:
                if ($cd->get_error() == 'remotedownloaderror') {
                    $a = new stdClass();
                    $a->url  = 'https://download.moodle.org/environment/environment.zip';
                    $a->dest = $CFG->dataroot . '/';
                    print_error($cd->get_error(), 'error', $PAGE->url, $a);
                    die();

                } else {
                    print_error($cd->get_error(), 'error', $PAGE->url);
                    die();
                }

            case COMPONENT_UPTODATE:
                redirect($PAGE->url, get_string($cd->get_error(), 'error'));
                die;

            case COMPONENT_INSTALLED:
                redirect($PAGE->url, get_string('componentinstalled', 'admin'));
                die;
        }
    }
}

$current_version = $CFG->release;

$versions = array();
if ($contents = load_environment_xml()) {
    if ($env_versions = get_list_of_environment_versions($contents)) {
                $env_version = normalize_version($current_version);         $versions[$env_version] = $current_version;
                if (empty($version)) {
            $version =  $env_version;
        }
                foreach ($env_versions as $env_version) {
            if (version_compare(normalize_version($current_version), $env_version, '<')) {
                $versions[$env_version] = $env_version;
            }
        }
                $versions[$env_version] = $env_version.' '.get_string('upwards', 'admin');
    } else {
        $versions = array('error' => get_string('error'));
    }
}

list($envstatus, $environment_results) = check_moodle_environment($version, ENV_SELECT_NEWER);

$output = $PAGE->get_renderer('core', 'admin');
echo $output->environment_check_page($versions, $version, $envstatus, $environment_results);
