<?php



require_once('../config.php');
require_once($CFG->libdir.'/adminlib.php');
require_once($CFG->libdir.'/tablelib.php');

$action  = required_param('action', PARAM_ALPHANUMEXT);
$editor  = required_param('editor', PARAM_PLUGIN);
$confirm = optional_param('confirm', 0, PARAM_BOOL);

$PAGE->set_url('/admin/editors.php', array('action'=>$action, 'editor'=>$editor));
$PAGE->set_context(context_system::instance());

require_login();
require_capability('moodle/site:config', context_system::instance());

$returnurl = "$CFG->wwwroot/$CFG->admin/settings.php?section=manageeditors";

$available_editors = editors_get_available();
if (!empty($editor) and empty($available_editors[$editor])) {
    redirect ($returnurl);
}

$active_editors = explode(',', $CFG->texteditors);
foreach ($active_editors as $key=>$active) {
    if (empty($available_editors[$active])) {
        unset($active_editors[$key]);
    }
}


if (!confirm_sesskey()) {
    redirect($returnurl);
}


$return = true;
switch ($action) {
    case 'disable':
                $key = array_search($editor, $active_editors);
        unset($active_editors[$key]);
        break;

    case 'enable':
                if (!in_array($editor, $active_editors)) {
            $active_editors[] = $editor;
            $active_editors = array_unique($active_editors);
        }
        break;

    case 'down':
        $key = array_search($editor, $active_editors);
                if ($key !== false) {
                        if ($key < (count($active_editors) - 1)) {
                $fsave = $active_editors[$key];
                $active_editors[$key] = $active_editors[$key + 1];
                $active_editors[$key + 1] = $fsave;
            }
        }
        break;

    case 'up':
        $key = array_search($editor, $active_editors);
                if ($key !== false) {
                        if ($key >= 1) {
                $fsave = $active_editors[$key];
                $active_editors[$key] = $active_editors[$key - 1];
                $active_editors[$key - 1] = $fsave;
            }
        }
        break;

    default:
        break;
}

if (empty($active_editors)) {
    $active_editors = array('textarea');
}

set_config('texteditors', implode(',', $active_editors));
core_plugin_manager::reset_caches();

if ($return) {
    redirect ($returnurl);
}