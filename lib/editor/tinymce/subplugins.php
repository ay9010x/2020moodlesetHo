<?php



require(__DIR__ . '/../../../config.php');
require_once($CFG->libdir.'/adminlib.php');

$disable = optional_param('disable', '', PARAM_PLUGIN);
$enable  = optional_param('enable', '', PARAM_PLUGIN);
$return  = optional_param('return', 'overview', PARAM_ALPHA);

$PAGE->set_context(context_system::instance());
$PAGE->set_url('/lib/editor/tinymce/subplugins.php');

require_login();
require_capability('moodle/site:config', context_system::instance());
require_sesskey();

if ($return === 'settings') {
    $returnurl = new moodle_url('/admin/settings.php', array('section'=>'editorsettingstinymce'));
} else {
    $returnurl = new moodle_url('/admin/plugins.php');
}

$disabled = array();
$disabledsubplugins = get_config('editor_tinymce', 'disabledsubplugins');
if ($disabledsubplugins) {
    $disabledsubplugins = explode(',', $disabledsubplugins);
    foreach ($disabledsubplugins as $sp) {
        $sp = trim($sp);
        if ($sp !== '') {
            $disabled[$sp] = $sp;
        }
    }
}

if ($disable) {
    $disabled[$disable] = $disable;
} else if ($enable) {
    unset($disabled[$enable]);
}

set_config('disabledsubplugins', implode(',', $disabled), 'editor_tinymce');
core_plugin_manager::reset_caches();

redirect($returnurl);
