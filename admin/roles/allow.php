<?php



require_once(dirname(__FILE__) . '/../../config.php');
require_once($CFG->libdir . '/adminlib.php');

$mode = required_param('mode', PARAM_ALPHANUMEXT);
$classformode = array(
    'assign' => 'core_role_allow_assign_page',
    'override' => 'core_role_allow_override_page',
    'switch' => 'core_role_allow_switch_page'
);
if (!isset($classformode[$mode])) {
    print_error('invalidmode', '', '', $mode);
}

$baseurl = new moodle_url('/admin/roles/allow.php', array('mode'=>$mode));
admin_externalpage_setup('defineroles', '', array(), $baseurl);

$syscontext = context_system::instance();
require_capability('moodle/role:manage', $syscontext);

$controller = new $classformode[$mode]();

if (optional_param('submit', false, PARAM_BOOL) && data_submitted() && confirm_sesskey()) {
    $controller->process_submission();
    $syscontext->mark_dirty();
    $event = null;
        switch ($mode) {
        case 'assign':
            $event = \core\event\role_allow_assign_updated::create(array('context' => $syscontext));
            break;
        case 'override':
            $event = \core\event\role_allow_override_updated::create(array('context' => $syscontext));
            break;
        case 'switch':
            $event = \core\event\role_allow_switch_updated::create(array('context' => $syscontext));
            break;
    }
    if ($event) {
        $event->trigger();
    }
    redirect($baseurl);
}

$controller->load_current_settings();

echo $OUTPUT->header();

$currenttab = $mode;
require('managetabs.php');

$table = $controller->get_table();

echo $OUTPUT->box($controller->get_intro_text());

echo '<form action="' . $baseurl . '" method="post">';
echo '<input type="hidden" name="sesskey" value="' . sesskey() . '" />';
echo html_writer::table($table);
echo '<div class="buttons"><input type="submit" name="submit" value="'.get_string('savechanges').'"/>';
echo '</div></form>';

echo $OUTPUT->footer();
