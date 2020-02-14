<?php



defined('MOODLE_INTERNAL') || die;

$capabilities = [
    'moodle/cohort:view',
    'moodle/role:manage'
];

$context = context_system::instance();
$hasaccess = has_all_capabilities($capabilities, $context);

if ($hasaccess) {
    $str = get_string('managecohortroles', 'tool_cohortroles');
    $url = new moodle_url('/admin/tool/cohortroles/index.php');
    $ADMIN->add('roles', new admin_externalpage('toolcohortroles', $str, $url, $capabilities));
}
