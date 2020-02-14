<?php



require_once(dirname(__FILE__) . '/../../config.php');
require_once($CFG->libdir.'/adminlib.php');

$action = required_param('action', PARAM_ALPHA);
if (!in_array($action, array('add', 'export', 'edit', 'reset', 'view'))) {
    throw new moodle_exception('invalidaccess');
}
if ($action != 'add') {
    $roleid = required_param('roleid', PARAM_INT);
} else {
    $roleid = 0;
}
$resettype = optional_param('resettype', '', PARAM_RAW);
$return = optional_param('return', 'manage', PARAM_ALPHA);

$baseurl = new moodle_url('/admin/roles/define.php', array('action'=>$action, 'roleid'=>$roleid));
$manageurl = new moodle_url('/admin/roles/manage.php');
if ($return === 'manage') {
    $returnurl = $manageurl;
} else {
    $returnurl = new moodle_url('/admin/roles/define.php', array('action'=>'view', 'roleid'=>$roleid));;
}

$systemcontext = context_system::instance();
require_login();
require_capability('moodle/role:manage', $systemcontext);
admin_externalpage_setup('defineroles', '', array('action' => $action, 'roleid' => $roleid), new moodle_url('/admin/roles/define.php'));

if ($action === 'export') {
    core_role_preset::send_export_xml($roleid);
    die;
}

$showadvanced = get_user_preferences('definerole_showadvanced', false);
if (optional_param('toggleadvanced', false, PARAM_BOOL)) {
    $showadvanced = !$showadvanced;
    set_user_preference('definerole_showadvanced', $showadvanced);
}

$roles = get_all_roles();
$rolenames = role_fix_names($roles, $systemcontext, ROLENAME_ORIGINAL);
$rolescount = count($roles);

if ($action === 'add') {
    $title = get_string('addinganewrole', 'core_role');
} else if ($action == 'view') {
    $title = get_string('viewingdefinitionofrolex', 'core_role', $rolenames[$roleid]->localname);
} else if ($action == 'reset') {
    $title = get_string('resettingrole', 'core_role', $rolenames[$roleid]->localname);
} else {
    $title = get_string('editingrolex', 'core_role', $rolenames[$roleid]->localname);
}

if ($action === 'add' and $resettype !== 'none') {
    $mform = new core_role_preset_form(null, array('action'=>'add', 'roleid'=>0, 'resettype'=>'0', 'return'=>'manage'));
    if ($mform->is_cancelled()) {
        redirect($manageurl);

    } else if ($data = $mform->get_data()) {
        $resettype = $data->resettype;
        $options = array(
            'shortname'     => 1,
            'name'          => 1,
            'description'   => 1,
            'permissions'   => 1,
            'archetype'     => 1,
            'contextlevels' => 1,
            'allowassign'   => 1,
            'allowoverride' => 1,
            'allowswitch'   => 1);
        if ($showadvanced) {
            $definitiontable = new core_role_define_role_table_advanced($systemcontext, 0);
        } else {
            $definitiontable = new core_role_define_role_table_basic($systemcontext, 0);
        }
        if (is_number($resettype)) {
                        $definitiontable->force_duplicate($resettype, $options);
        } else {
                        $definitiontable->force_archetype($resettype, $options);
        }

        if ($xml = $mform->get_file_content('rolepreset')) {
            $definitiontable->force_preset($xml, $options);
        }

    } else {
        echo $OUTPUT->header();
        echo $OUTPUT->heading_with_help($title, 'roles', 'core_role');
        $mform->display();
        echo $OUTPUT->footer();
        die;
    }

} else if ($action === 'reset' and $resettype !== 'none') {
    if (!$role = $DB->get_record('role', array('id'=>$roleid))) {
        redirect($manageurl);
    }
    $resettype = empty($role->archetype) ? '0' : $role->archetype;
    $mform = new core_role_preset_form(null,
        array('action'=>'reset', 'roleid'=>$roleid, 'resettype'=>$resettype , 'permissions'=>1, 'archetype'=>1, 'contextlevels'=>1, 'return'=>$return));
    if ($mform->is_cancelled()) {
        redirect($returnurl);

    } else if ($data = $mform->get_data()) {
        $resettype = $data->resettype;
        $options = array(
            'shortname'     => $data->shortname,
            'name'          => $data->name,
            'description'   => $data->description,
            'permissions'   => $data->permissions,
            'archetype'     => $data->archetype,
            'contextlevels' => $data->contextlevels,
            'allowassign'   => $data->allowassign,
            'allowoverride' => $data->allowoverride,
            'allowswitch'   => $data->allowswitch);
        if ($showadvanced) {
            $definitiontable = new core_role_define_role_table_advanced($systemcontext, $roleid);
        } else {
            $definitiontable = new core_role_define_role_table_basic($systemcontext, $roleid);
        }
        if (is_number($resettype)) {
                        $definitiontable->force_duplicate($resettype, $options);
        } else {
                        $definitiontable->force_archetype($resettype, $options);
        }

        if ($xml = $mform->get_file_content('rolepreset')) {
            $definitiontable->force_preset($xml, $options);
        }

    } else {
        echo $OUTPUT->header();
        echo $OUTPUT->heading_with_help($title, 'roles', 'core_role');
        $mform->display();
        echo $OUTPUT->footer();
        die;
    }

} else {
        if ($action === 'view') {
        $definitiontable = new core_role_view_role_definition_table($systemcontext, $roleid);
    } else if ($showadvanced) {
        $definitiontable = new core_role_define_role_table_advanced($systemcontext, $roleid);
    } else {
        $definitiontable = new core_role_define_role_table_basic($systemcontext, $roleid);
    }
    $definitiontable->read_submitted_permissions();
}

if (optional_param('cancel', false, PARAM_BOOL)) {
    redirect($returnurl);
}

if (optional_param('savechanges', false, PARAM_BOOL) && confirm_sesskey() && $definitiontable->is_submission_valid()) {
    $definitiontable->save_changes();
    $tableroleid = $definitiontable->get_role_id();
        $event = \core\event\role_capabilities_updated::create(
        array(
            'context' => $systemcontext,
            'objectid' => $tableroleid
        )
    );
    $event->set_legacy_logdata(array(SITEID, 'role', $action, 'admin/roles/define.php?action=view&roleid=' . $tableroleid,
        $definitiontable->get_role_name(), '', $USER->id));
    if (!empty($role)) {
        $event->add_record_snapshot('role', $role);
    }
    $event->trigger();

    if ($action === 'add') {
        redirect(new moodle_url('/admin/roles/define.php', array('action'=>'view', 'roleid'=>$definitiontable->get_role_id())));
    } else {
        redirect($returnurl);
    }
}

echo $OUTPUT->header();

$currenttab = 'manage';
require('managetabs.php');

echo $OUTPUT->heading_with_help($title, 'roles', 'core_role');

if ($action === 'add') {
    $submitlabel = get_string('createthisrole', 'core_role');
} else {
    $submitlabel = get_string('savechanges');
}

if ($action === 'view') {
    echo $OUTPUT->container_start('buttons');
    $url = new moodle_url('/admin/roles/define.php', array('action'=>'edit', 'roleid'=>$roleid, 'return'=>'define'));
    echo $OUTPUT->single_button(new moodle_url($url), get_string('edit'));
    $url = new moodle_url('/admin/roles/define.php', array('action'=>'reset', 'roleid'=>$roleid, 'return'=>'define'));
    echo $OUTPUT->single_button(new moodle_url($url), get_string('resetrole', 'core_role'));
    $url = new moodle_url('/admin/roles/define.php', array('action'=>'export', 'roleid'=>$roleid));
    echo $OUTPUT->single_button(new moodle_url($url), get_string('export', 'core_role'));
    echo $OUTPUT->single_button($manageurl, get_string('listallroles', 'core_role'));
    echo $OUTPUT->container_end();
}

echo $OUTPUT->box_start('generalbox');
if ($action === 'view') {
    echo '<div class="mform">';
} else {
    ?>
<form id="rolesform" class="mform" action="<?php p($baseurl->out(false)); ?>" method="post"><div>
<input type="hidden" name="sesskey" value="<?php p(sesskey()) ?>" />
<input type="hidden" name="return" value="<?php p($return); ?>" />
<input type="hidden" name="resettype" value="none" />
<div class="submit buttons">
    <input type="submit" name="savechanges" value="<?php p($submitlabel); ?>" />
    <input type="submit" name="cancel" value="<?php print_string('cancel'); ?>" />
</div>
    <?php
}

$definitiontable->display();

if ($action === 'view') {
    echo '</div>';
} else {
    ?>
<div class="submit buttons">
    <input type="submit" name="savechanges" value="<?php p($submitlabel); ?>" />
    <input type="submit" name="cancel" value="<?php print_string('cancel'); ?>" />
</div>
</div></form>
<?php
}
echo $OUTPUT->box_end();

echo '<div class="backlink">';
echo '<p><a href="' . s($manageurl->out(false)) . '">' . get_string('backtoallroles', 'core_role') . '</a></p>';
echo '</div>';

echo $OUTPUT->footer();
