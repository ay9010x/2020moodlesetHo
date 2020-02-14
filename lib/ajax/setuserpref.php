<?php



require_once(dirname(__FILE__) . '/../../config.php');

if (!confirm_sesskey()) {
    print_error('invalidsesskey');
}

$name = required_param('pref', PARAM_RAW);
if (!isset($USER->ajax_updatable_user_prefs[$name])) {
    print_error('notallowedtoupdateprefremotely');
}

$value = required_param('value', $USER->ajax_updatable_user_prefs[$name]);

if (!set_user_preference($name, $value)) {
    print_error('errorsettinguserpref');
}

echo 'OK';
