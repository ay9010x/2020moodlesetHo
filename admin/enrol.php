<?php



define('NO_OUTPUT_BUFFERING', true);

require_once('../config.php');
require_once($CFG->libdir.'/adminlib.php');

$action  = required_param('action', PARAM_ALPHANUMEXT);
$enrol   = required_param('enrol', PARAM_PLUGIN);
$confirm = optional_param('confirm', 0, PARAM_BOOL);

$PAGE->set_url('/admin/enrol.php');
$PAGE->set_context(context_system::instance());

require_login();
require_capability('moodle/site:config', context_system::instance());
require_sesskey();

$enabled = enrol_get_plugins(true);
$all     = enrol_get_plugins(false);

$return = new moodle_url('/admin/settings.php', array('section'=>'manageenrols'));

$syscontext = context_system::instance();

switch ($action) {
    case 'disable':
        unset($enabled[$enrol]);
        set_config('enrol_plugins_enabled', implode(',', array_keys($enabled)));
        core_plugin_manager::reset_caches();
        $syscontext->mark_dirty();         break;

    case 'enable':
        if (!isset($all[$enrol])) {
            break;
        }
        $enabled = array_keys($enabled);
        $enabled[] = $enrol;
        set_config('enrol_plugins_enabled', implode(',', $enabled));
        core_plugin_manager::reset_caches();
        $syscontext->mark_dirty();         break;

    case 'up':
        if (!isset($enabled[$enrol])) {
            break;
        }
        $enabled = array_keys($enabled);
        $enabled = array_flip($enabled);
        $current = $enabled[$enrol];
        if ($current == 0) {
            break;         }
        $enabled = array_flip($enabled);
        $enabled[$current] = $enabled[$current - 1];
        $enabled[$current - 1] = $enrol;
        set_config('enrol_plugins_enabled', implode(',', $enabled));
        break;

    case 'down':
        if (!isset($enabled[$enrol])) {
            break;
        }
        $enabled = array_keys($enabled);
        $enabled = array_flip($enabled);
        $current = $enabled[$enrol];
        if ($current == count($enabled) - 1) {
            break;         }
        $enabled = array_flip($enabled);
        $enabled[$current] = $enabled[$current + 1];
        $enabled[$current + 1] = $enrol;
        set_config('enrol_plugins_enabled', implode(',', $enabled));
        break;

    case 'migrate':
        if (get_string_manager()->string_exists('pluginname', 'enrol_'.$enrol)) {
            $strplugin = get_string('pluginname', 'enrol_'.$enrol);
        } else {
            $strplugin = $enrol;
        }

        $PAGE->set_title($strplugin);
        echo $OUTPUT->header();

                core_php_time_limit::raise();

                unset($enabled[$enrol]);
        set_config('enrol_plugins_enabled', implode(',', array_keys($enabled)));

        echo $OUTPUT->heading(get_string('uninstallmigrating', 'enrol', 'enrol_'.$enrol));

        require_once("$CFG->dirroot/enrol/manual/locallib.php");
        enrol_manual_migrate_plugin_enrolments($enrol);

        echo $OUTPUT->notification(get_string('success'), 'notifysuccess');

        if (!$return = core_plugin_manager::instance()->get_uninstall_url('enrol_'.$enrol, 'manage')) {
            $return = new moodle_url('/admin/plugins.php');
        }
        echo $OUTPUT->continue_button($return);
        echo $OUTPUT->footer();
        exit;
}


redirect($return);