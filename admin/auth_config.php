<?php


require_once '../config.php';
require_once $CFG->libdir.'/adminlib.php';

$auth = required_param('auth', PARAM_PLUGIN);
$PAGE->set_pagetype('admin-auth-' . $auth);

admin_externalpage_setup('authsetting'.$auth);

$authplugin = get_auth_plugin($auth);
$err = array();

$returnurl = "$CFG->wwwroot/$CFG->admin/settings.php?section=manageauths";

if ($frm = data_submitted() and confirm_sesskey()) {

    $authplugin->validate_form($frm, $err);

    if (count($err) == 0) {

                if ($authplugin->process_config($frm)) {

                        foreach ($frm as $name => $value) {
                if (preg_match('/^lockconfig_(.+?)$/', $name, $matches)) {
                    $plugin = "auth/$auth";
                    $name   = $matches[1];
                    set_config($name, $value, $plugin);
                }
            }
            redirect($returnurl);
            exit;
        }
    } else {
        foreach ($err as $key => $value) {
            $focus = "form.$key";
        }
    }
} else {
    $frmlegacystyle = get_config('auth/'.$auth);
    $frmnewstyle    = get_config('auth_'.$auth);
    $frm = (object)array_merge((array)$frmlegacystyle, (array)$frmnewstyle);
}

$user_fields = $authplugin->userfields;

    $authtitle = $authplugin->get_title();
    $authdescription = $authplugin->get_description();

echo $OUTPUT->header();

echo "<form id=\"authmenu\" method=\"post\" action=\"auth_config.php\">\n";
echo "<div>\n";
echo "<input type=\"hidden\" name=\"sesskey\" value=\"".sesskey()."\" />\n";
echo "<input type=\"hidden\" name=\"auth\" value=\"".$auth."\" />\n";
echo prevent_form_autofill_password();

echo $OUTPUT->box_start();
echo $OUTPUT->heading($authtitle);
echo $OUTPUT->box_start('informationbox');
echo $authdescription;
echo $OUTPUT->box_end();
echo "<hr />\n";
$authplugin->config_form($frm, $err, $user_fields);
echo $OUTPUT->box_end();
echo '<p style="text-align: center"><input type="submit" value="' . get_string("savechanges") . "\" /></p>\n";
echo "</div>\n";
echo "</form>\n";

$PAGE->requires->string_for_js('unmaskpassword', 'core_form');
$PAGE->requires->yui_module('moodle-auth-passwordunmask', 'M.auth.passwordunmask');

echo $OUTPUT->footer();
exit;


function print_auth_lock_options($auth, $user_fields, $helptext, $retrieveopts, $updateopts, $customfields = array()) {
    global $DB, $OUTPUT;
    echo '<tr><td colspan="3">';
    if ($retrieveopts) {
        echo $OUTPUT->heading(get_string('auth_data_mapping', 'auth'));
    } else {
        echo $OUTPUT->heading(get_string('auth_fieldlocks', 'auth'));
    }
    echo '</td></tr>';

    $lockoptions = array ('unlocked'        => get_string('unlocked', 'auth'),
                          'unlockedifempty' => get_string('unlockedifempty', 'auth'),
                          'locked'          => get_string('locked', 'auth'));
    $updatelocaloptions = array('oncreate'  => get_string('update_oncreate', 'auth'),
                                'onlogin'   => get_string('update_onlogin', 'auth'));
    $updateextoptions = array('0'  => get_string('update_never', 'auth'),
                              '1'   => get_string('update_onupdate', 'auth'));

    $pluginconfig = get_config("auth/$auth");

        if (empty($helptext)) {
        $helptext = '&nbsp;';
    }

        if (!empty($customfields)) {
        $user_fields = array_merge($user_fields, $customfields);
    }

    if (!empty($customfields)) {
        $customfieldname = $DB->get_records('user_info_field', null, '', 'shortname, name');
    }
    foreach ($user_fields as $field) {
                if (!isset($pluginconfig->{"field_map_$field"})) {
            $pluginconfig->{"field_map_$field"} = '';
        }
        if (!isset($pluginconfig->{"field_updatelocal_$field"})) {
            $pluginconfig->{"field_updatelocal_$field"} = '';
        }
        if (!isset($pluginconfig->{"field_updateremote_$field"})) {
            $pluginconfig->{"field_updateremote_$field"} = '';
        }
        if (!isset($pluginconfig->{"field_lock_$field"})) {
            $pluginconfig->{"field_lock_$field"} = '';
        }

                $fieldname = $field;
        if ($fieldname === 'lang') {
            $fieldname = get_string('language');
        } elseif (!empty($customfields) && in_array($field, $customfields)) {
                        $fieldshortname = str_replace('profile_field_', '', $fieldname);
            $fieldname = $customfieldname[$fieldshortname]->name;
        } elseif ($fieldname == 'url') {
            $fieldname = get_string('webpage');
        } else {
            $fieldname = get_string($fieldname);
        }
        if ($retrieveopts) {
            $varname = 'field_map_' . $field;

            echo '<tr valign="top"><td align="right">';
            echo '<label for="lockconfig_'.$varname.'">'.$fieldname.'</label>';
            echo '</td><td>';

            echo "<input id=\"lockconfig_{$varname}\" name=\"lockconfig_{$varname}\" type=\"text\" size=\"30\" value=\"{$pluginconfig->$varname}\" />";
            echo '<div style="text-align: right">';
            echo '<label for="menulockconfig_field_updatelocal_'.$field.'">'.get_string('auth_updatelocal', 'auth') . '</label>&nbsp;';
            echo html_writer::select($updatelocaloptions, "lockconfig_field_updatelocal_{$field}", $pluginconfig->{"field_updatelocal_$field"}, false);
            echo '<br />';
            if ($updateopts) {
                echo '<label for="menulockconfig_field_updateremote_'.$field.'">'.get_string('auth_updateremote', 'auth') . '</label>&nbsp;';
                echo html_writer::select($updateextoptions, "lockconfig_field_updateremote_{$field}", $pluginconfig->{"field_updateremote_$field"}, false);
                echo '<br />';
            }
            echo '<label for="menulockconfig_field_lock_'.$field.'">'.get_string('auth_fieldlock', 'auth') . '</label>&nbsp;';
            echo html_writer::select($lockoptions, "lockconfig_field_lock_{$field}", $pluginconfig->{"field_lock_$field"}, false);
            echo '</div>';
        } else {
            echo '<tr valign="top"><td align="right">';
            echo '<label for="menulockconfig_field_lock_'.$field.'">'.$fieldname.'</label>';
            echo '</td><td>';
            echo html_writer::select($lockoptions, "lockconfig_field_lock_{$field}", $pluginconfig->{"field_lock_$field"}, false);
        }
        echo '</td>';
        if (!empty($helptext)) {
            echo '<td rowspan="' . count($user_fields) . '">' . $helptext . '</td>';
            $helptext = '';
        }
        echo '</tr>';
    }
}
