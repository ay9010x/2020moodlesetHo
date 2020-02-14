<?php



require_once('../config.php');
require_once($CFG->libdir.'/adminlib.php');
require_once($CFG->libdir.'/tablelib.php');

require_login();
require_capability('moodle/site:config', context_system::instance());

$returnurl = new moodle_url('/admin/settings.php', array('section'=>'manageauths'));

$PAGE->set_url($returnurl);

$action = optional_param('action', '', PARAM_ALPHANUMEXT);
$auth   = optional_param('auth', '', PARAM_PLUGIN);

get_enabled_auth_plugins(true); if (empty($CFG->auth)) {
    $authsenabled = array();
} else {
    $authsenabled = explode(',', $CFG->auth);
}

if (!empty($auth) and !exists_auth_plugin($auth)) {
    print_error('pluginnotinstalled', 'auth', $returnurl, $auth);
}


if (!confirm_sesskey()) {
    redirect($returnurl);
}

switch ($action) {
    case 'disable':
                $key = array_search($auth, $authsenabled);
        if ($key !== false) {
            unset($authsenabled[$key]);
            set_config('auth', implode(',', $authsenabled));
        }

        if ($auth == $CFG->registerauth) {
            set_config('registerauth', '');
        }
        \core\session\manager::gc();         core_plugin_manager::reset_caches();
        break;

    case 'enable':
                if (!in_array($auth, $authsenabled)) {
            $authsenabled[] = $auth;
            $authsenabled = array_unique($authsenabled);
            set_config('auth', implode(',', $authsenabled));
        }
        \core\session\manager::gc();         core_plugin_manager::reset_caches();
        break;

    case 'down':
        $key = array_search($auth, $authsenabled);
                if ($key === false) {
            print_error('pluginnotenabled', 'auth', $returnurl, $auth);
        }
                if ($key < (count($authsenabled) - 1)) {
            $fsave = $authsenabled[$key];
            $authsenabled[$key] = $authsenabled[$key + 1];
            $authsenabled[$key + 1] = $fsave;
            set_config('auth', implode(',', $authsenabled));
        }
        break;

    case 'up':
        $key = array_search($auth, $authsenabled);
                if ($key === false) {
            print_error('pluginnotenabled', 'auth', $returnurl, $auth);
        }
                if ($key >= 1) {
            $fsave = $authsenabled[$key];
            $authsenabled[$key] = $authsenabled[$key - 1];
            $authsenabled[$key - 1] = $fsave;
            set_config('auth', implode(',', $authsenabled));
        }
        break;

    default:
        break;
}

redirect($returnurl);

