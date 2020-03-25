<?php



require_once(dirname(__FILE__) . '/../../config.php');
require_once($CFG->libdir.'/adminlib.php');
require_once($CFG->dirroot . '/' . $CFG->admin . '/roles/lib.php');

$action = optional_param('action', '', PARAM_ALPHA);
if ($action) {
    $roleid = required_param('roleid', PARAM_INT);
} else {
    $roleid = 0;
}

$baseurl = $CFG->wwwroot . '/' . $CFG->admin . '/roles/manage.php';
$defineurl = $CFG->wwwroot . '/' . $CFG->admin . '/roles/define.php';

$systemcontext = context_system::instance();
require_login();
require_capability('moodle/role:manage', $systemcontext);
admin_externalpage_setup('defineroles');

$roles = role_fix_names(get_all_roles(), $systemcontext, ROLENAME_ORIGINAL);

$undeletableroles = array();
$undeletableroles[$CFG->notloggedinroleid] = 1;
$undeletableroles[$CFG->guestroleid] = 1;
$undeletableroles[$CFG->defaultuserroleid] = 1;

$confirmed = (optional_param('confirm', false, PARAM_BOOL) && data_submitted() && confirm_sesskey());
switch ($action) {
    case 'delete':
        if (isset($undeletableroles[$roleid])) {
            print_error('cannotdeletethisrole', '', $baseurl);
        }
        if (!$confirmed) {
                        echo $OUTPUT->header();
            $optionsyes = array('action'=>'delete', 'roleid'=>$roleid, 'sesskey'=>sesskey(), 'confirm'=>1);
            $a = new stdClass();
            $a->id = $roleid;
            $a->name = $roles[$roleid]->name;
            $a->shortname = $roles[$roleid]->shortname;
            $a->count = $DB->count_records_select('role_assignments',
                'roleid = ?', array($roleid), 'COUNT(DISTINCT userid)');

            $formcontinue = new single_button(new moodle_url($baseurl, $optionsyes), get_string('yes'));
            $formcancel = new single_button(new moodle_url($baseurl), get_string('no'), 'get');
            echo $OUTPUT->confirm(get_string('deleterolesure', 'core_role', $a), $formcontinue, $formcancel);
            echo $OUTPUT->footer();
            die;
        }
        if (!delete_role($roleid)) {
                        $systemcontext->mark_dirty();
            print_error('cannotdeleterolewithid', 'error', $baseurl, $roleid);
        }
                $systemcontext->mark_dirty();
        redirect($baseurl);
        break;

    case 'moveup':
        if (confirm_sesskey()) {
            $prevrole = null;
            $thisrole = null;
            foreach ($roles as $role) {
                if ($role->id == $roleid) {
                    $thisrole = $role;
                    break;
                } else {
                    $prevrole = $role;
                }
            }
            if (is_null($thisrole) || is_null($prevrole)) {
                print_error('cannotmoverolewithid', 'error', '', $roleid);
            }
            if (!switch_roles($thisrole, $prevrole)) {
                print_error('cannotmoverolewithid', 'error', '', $roleid);
            }
        }

        redirect($baseurl);
        break;

    case 'movedown':
        if (confirm_sesskey()) {
            $thisrole = null;
            $nextrole = null;
            foreach ($roles as $role) {
                if ($role->id == $roleid) {
                    $thisrole = $role;
                } else if (!is_null($thisrole)) {
                    $nextrole = $role;
                    break;
                }
            }
            if (is_null($nextrole)) {
                print_error('cannotmoverolewithid', 'error', '', $roleid);
            }
            if (!switch_roles($thisrole, $nextrole)) {
                print_error('cannotmoverolewithid', 'error', '', $roleid);
            }
        }

        redirect($baseurl);
        break;

}

echo $OUTPUT->header();

$currenttab = 'manage';
require('managetabs.php');

$table = new html_table();
$table->colclasses = array('leftalign', 'leftalign', 'leftalign', 'leftalign');
$table->id = 'roles';
$table->attributes['class'] = 'admintable generaltable';
$table->head = array(
    get_string('role') . ' ' . $OUTPUT->help_icon('roles', 'core_role'),
    get_string('description'),
    get_string('roleshortname', 'core_role'),
    get_string('edit')
);

$stredit = get_string('edit');
$strdelete = get_string('delete');
$strmoveup = get_string('moveup');
$strmovedown = get_string('movedown');

$table->data = array();
$firstrole = reset($roles);
$lastrole = end($roles);
foreach ($roles as $role) {
        $row = array(
        '<a href="' . $defineurl . '?action=view&amp;roleid=' . $role->id . '">' . $role->localname . '</a>',
        role_get_description($role),
        s($role->shortname),
        '',
    );

        if ($role->sortorder != $firstrole->sortorder) {
        $row[3] .= get_action_icon($baseurl . '?action=moveup&amp;roleid=' . $role->id . '&amp;sesskey=' . sesskey(), 'up', $strmoveup, $strmoveup);
    } else {
        $row[3] .= get_spacer();
    }
        if ($role->sortorder != $lastrole->sortorder) {
        $row[3] .= get_action_icon($baseurl . '?action=movedown&amp;roleid=' . $role->id . '&amp;sesskey=' . sesskey(), 'down', $strmovedown, $strmovedown);
    } else {
        $row[3] .= get_spacer();
    }
        $row[3] .= get_action_icon($defineurl . '?action=edit&amp;roleid=' . $role->id,
            'edit', $stredit, get_string('editxrole', 'core_role', $role->localname));
        if (isset($undeletableroles[$role->id])) {
        $row[3] .= get_spacer();
    } else {
        $row[3] .= get_action_icon($baseurl . '?action=delete&amp;roleid=' . $role->id,
              'delete', $strdelete, get_string('deletexrole', 'core_role', $role->localname));
    }

    $table->data[] = $row;
}
echo html_writer::table($table);

echo $OUTPUT->container_start('buttons');
echo $OUTPUT->single_button(new moodle_url($defineurl, array('action' => 'add')), get_string('addrole', 'core_role'), 'get');
echo $OUTPUT->container_end();

echo $OUTPUT->footer();
die;

function get_action_icon($url, $icon, $alt, $tooltip) {
    global $OUTPUT;
    return '<a title="' . $tooltip . '" href="'. $url . '">' .
            '<img src="' . $OUTPUT->pix_url('t/' . $icon) . '" class="iconsmall" alt="' . $alt . '" /></a> ';
}
function get_spacer() {
    global $OUTPUT;
    return '<img src="' . $OUTPUT->pix_url('spacer') . '" class="iconsmall" alt="" /> ';
}
