<?php



require_once(__DIR__ . '/../../config.php');
require_once($CFG->dirroot . '/' . $CFG->admin . '/roles/lib.php');
require_once($CFG->libdir . '/coursecatlib.php');

define("MAX_USERS_TO_LIST_PER_ROLE", 10);

$contextid = required_param('contextid', PARAM_INT);
$contextid0 = optional_param('contextid0', 0, PARAM_INT);
$contextid1 = optional_param('contextid1', 0, PARAM_INT);
$roleid    = optional_param('roleid', 0, PARAM_INT);
$returnto  = optional_param('return', null, PARAM_ALPHANUMEXT);

$contextid = isset($contextid) ? $contextid : (isset($contextid1) ? $contextid1 : (isset($contextid0) ? $contextid0 : -1));
list($context, $course, $cm) = get_context_info_array($contextid);

$url = new moodle_url('/admin/roles/assign.php', array('contextid' => $contextid));

if ($course) {
    $isfrontpage = ($course->id == SITEID);
} else {
    $isfrontpage = false;
    if ($context->contextlevel == CONTEXT_USER) {
        $course = $DB->get_record('course', array('id'=>optional_param('courseid', SITEID, PARAM_INT)), '*', MUST_EXIST);
        $user = $DB->get_record('user', array('id'=>$context->instanceid), '*', MUST_EXIST);
        $url->param('courseid', $course->id);
        $url->param('userid', $user->id);
    } else {
        $course = $SITE;
    }
}


require_login($course, false, $cm);
require_capability('moodle/role:assign', $context);
$PAGE->set_url($url);
$PAGE->set_context($context);

$contextname = $context->get_context_name();
$courseid = $course->id;

list($assignableroles, $assigncounts, $nameswithcounts) = get_assignable_roles($context, ROLENAME_BOTH, true);
$overridableroles = get_overridable_roles($context, ROLENAME_BOTH);

if ($roleid && !isset($assignableroles[$roleid])) {
    $a = new stdClass;
    $a->roleid = $roleid;
    $a->context = $contextname;
    print_error('cannotassignrolehere', '', $context->get_url(), $a);
}

if ($roleid) {
    $a = new stdClass;
    $a->role = $assignableroles[$roleid];
    $a->context = $contextname;
    $title = get_string('assignrolenameincontext', 'core_role', $a);
} else {
    if ($isfrontpage) {
        $title = get_string('frontpageroles', 'admin');
    } else {
        $title = get_string('assignrolesin', 'core_role', $contextname);
    }
}

if ($roleid) {

        $options = array('context' => $context, 'roleid' => $roleid);

    $potentialuserselector = core_role_get_potential_user_selector($context, 'addselect', $options);
    $currentuserselector = new core_role_existing_role_holders('removeselect', $options);

        $errors = array();
    if (optional_param('add', false, PARAM_BOOL) && confirm_sesskey()) {
        $userstoassign = $potentialuserselector->get_selected_users();
        if (!empty($userstoassign)) {

            foreach ($userstoassign as $adduser) {
                $allow = true;

                if ($allow) {
                    role_assign($roleid, $adduser->id, $context->id);
                }
            }

            $potentialuserselector->invalidate_selected_users();
            $currentuserselector->invalidate_selected_users();

                        list($assignableroles, $assigncounts, $nameswithcounts) = get_assignable_roles($context, ROLENAME_BOTH, true);
        }
    }

        if (optional_param('remove', false, PARAM_BOOL) && confirm_sesskey()) {
        $userstounassign = $currentuserselector->get_selected_users();
        if (!empty($userstounassign)) {

            foreach ($userstounassign as $removeuser) {
                                role_unassign($roleid, $removeuser->id, $context->id, '');
            }

            $potentialuserselector->invalidate_selected_users();
            $currentuserselector->invalidate_selected_users();

                        list($assignableroles, $assigncounts, $nameswithcounts) = get_assignable_roles($context, ROLENAME_BOTH, true);
        }
    }
}

if (!empty($user) && ($user->id != $USER->id)) {
    $PAGE->navigation->extend_for_user($user);
    $PAGE->navbar->includesettingsbase = true;
}

$PAGE->set_pagelayout('admin');
$PAGE->set_title($title);

switch ($context->contextlevel) {
    case CONTEXT_SYSTEM:
        require_once($CFG->libdir.'/adminlib.php');
        admin_externalpage_setup('assignroles', '', array('contextid' => $contextid, 'roleid' => $roleid));
        break;
    case CONTEXT_USER:
        $fullname = fullname($user, has_capability('moodle/site:viewfullnames', $context));
        $PAGE->set_heading($fullname);
        $showroles = 1;
        break;
    case CONTEXT_COURSECAT:
        $PAGE->set_heading($SITE->fullname);
        break;
    case CONTEXT_COURSE:
        if ($isfrontpage) {
            $PAGE->set_heading(get_string('frontpage', 'admin'));
        } else {
            $PAGE->set_heading($course->fullname);
        }
        break;
    case CONTEXT_MODULE:
        $PAGE->set_heading($context->get_context_name(false));
        $PAGE->set_cacheable(false);
        break;
    case CONTEXT_BLOCK:
        $PAGE->set_heading($PAGE->course->fullname);
        break;
}

echo $OUTPUT->header();

echo $OUTPUT->heading_with_help($title, 'assignroles', 'core_role');

if ($roleid) {
            if ($context->contextlevel == CONTEXT_SYSTEM) {
        echo $OUTPUT->notification(get_string('globalroleswarning', 'core_role'));
    }

        $assignurl = new moodle_url($PAGE->url, array('roleid'=>$roleid));
    if ($returnto !== null) {
        $assignurl->param('return', $returnto);
    }

        $displaylist = coursecat::make_categories_list('moodle/course:create');
        $categoryid= $context->instanceid;
    $currentcategory = coursecat::get($categoryid, IGNORE_MISSING);
    $parents = $currentcategory->get_parents();
    if(sizeof($parents)==2){
        $firstid = $parents[0];
        $secondid = $parents[1];
        $assignurl = new moodle_url('/admin/roles/assign.php', array('roleid'=>$roleid));
        if ($returnto !== null) {
            $assignurl->param('return', $returnto);
        }
?>
<form id="changecategoryform" method="post" action="<?php echo $assignurl ?>"><div>
  <select id="assign_role_category0" name="contextid0" class="select singleselect">
    <?php foreach ($displaylist as $key => $catename) { 
        $cate = coursecat::get($key);
        if($cate->parent == 0 ){
            $ctx = $cate->get_context();
            if($firstid == $key){ ?>
            <option selected="selected" value="<?php echo $ctx->id; ?>"><?php echo $cate->name; ?></option>    
        <?php } else { ?>
        <option value="<?php echo $ctx->id; ?>"><?php echo $catename;?></option>
    <?php } } } ?>
  </select>
  <select id="assign_role_category1" name="contextid1" class="select singleselect">
    <?php foreach ($displaylist as $key => $catename) {
        $cate = coursecat::get($key);
        if($cate->parent == $firstid ){
            $ctx = $cate->get_context();
            if($secondid == $key){ ?>
            <option selected="selected" value="<?php echo $ctx->id;?>"><?php echo $cate->name ?></option>    
        <?php } else { ?>
        <option value="<?php echo $ctx->id; ?>"><?php echo $catename;?></option>
    <?php } } } ?>
  </select> 
  <select id="assign_role_category2" name="contextid" class="select singleselect">
    <?php foreach ($displaylist as $key => $catename) {
        $cate = coursecat::get($key);
        if($cate->parent == $secondid ){
            $ctx = $cate->get_context();
            if($categoryid == $key){ ?>
            <option selected="selected" value="<?php echo $ctx->id;?>"><?php echo $displaylist[$categoryid];?></option>
        <?php } else { ?>
        <option value="<?php echo $ctx->id;?>"><?php echo $catename;?></option>
    <?php } } } ?>
  </select> 
</form>
<?php } ?>
<form id="assignform" method="post" action="<?php echo $assignurl ?>"><div>
  <input type="hidden" name="sesskey" value="<?php echo sesskey() ?>" />

  <table id="assigningrole" summary="" class="admintable roleassigntable generaltable" cellspacing="0">
    <tr>
      <td id="existingcell">
          <p><label for="removeselect"><?php print_string('extusers', 'core_role'); ?></label></p>
          <?php $currentuserselector->display() ?>
      </td>
      <td id="buttonscell">
          <div id="addcontrols">
              <input name="add" id="add" type="submit" value="<?php echo $OUTPUT->larrow().'&nbsp;'.get_string('add'); ?>" title="<?php print_string('add'); ?>" /><br />
          </div>

          <div id="removecontrols">
              <input name="remove" id="remove" type="submit" value="<?php echo get_string('remove').'&nbsp;'.$OUTPUT->rarrow(); ?>" title="<?php print_string('remove'); ?>" />
          </div>
      </td>
      <td id="potentialcell">
          <p><label for="addselect"><?php print_string('potusers', 'core_role'); ?></label></p>
          <?php $potentialuserselector->display() ?>
      </td>
    </tr>
  </table>
</div></form>

<?php
    $PAGE->requires->js_init_call('M.core_role.init_add_assign_page');

    if (!empty($errors)) {
        $msg = '<p>';
        foreach ($errors as $e) {
            $msg .= $e.'<br />';
        }
        $msg .= '</p>';
        echo $OUTPUT->box_start();
        echo $OUTPUT->notification($msg);
        echo $OUTPUT->box_end();
    }

        echo '<div class="backlink">';

    $newroleurl = new moodle_url($PAGE->url);
    if ($returnto !== null) {
        $newroleurl->param('return', $returnto);
    }
    $select = new single_select($newroleurl, 'roleid', $nameswithcounts, $roleid, null);
    $select->label = get_string('assignanotherrole', 'core_role');
    echo $OUTPUT->render($select);
    $backurl = new moodle_url('/admin/roles/assign.php', array('contextid' => $contextid));
    if ($returnto !== null) {
        $backurl->param('return', $returnto);
    }
    echo '<p><a href="' . $backurl->out() . '">' . get_string('backtoallroles', 'core_role') . '</a></p>';
    echo '</div>';

} else if (empty($assignableroles)) {
        echo $OUTPUT->heading(get_string('notabletoassignroleshere', 'core_role'), 3);

} else {
    
        if ($context->contextlevel == CONTEXT_SYSTEM) {
        echo $OUTPUT->notification(get_string('globalroleswarning', 'core_role'));
    }

        echo $OUTPUT->heading(get_string('chooseroletoassign', 'core_role'), 3);

            $roleholdernames = array();
    $strmorethanmax = get_string('morethan', 'core_role', MAX_USERS_TO_LIST_PER_ROLE);
    $showroleholders = false;
    foreach ($assignableroles as $roleid => $notused) {
        $roleusers = '';
        if (0 < $assigncounts[$roleid] && $assigncounts[$roleid] <= MAX_USERS_TO_LIST_PER_ROLE) {
            $userfields = 'u.id, u.username, ' . get_all_user_name_fields(true, 'u');
            $roleusers = get_role_users($roleid, $context, false, $userfields);
            if (!empty($roleusers)) {
                $strroleusers = array();
                foreach ($roleusers as $user) {
                    $strroleusers[] = '<a href="' . $CFG->wwwroot . '/user/view.php?id=' . $user->id . '" >' . fullname($user) . '</a>';
                }
                $roleholdernames[$roleid] = implode('<br />', $strroleusers);
                $showroleholders = true;
            }
        } else if ($assigncounts[$roleid] > MAX_USERS_TO_LIST_PER_ROLE) {
            $assignurl = new moodle_url($PAGE->url, array('roleid'=>$roleid));
            if ($returnto !== null) {
                $assignurl->param('return', $returnto);
            }
            $roleholdernames[$roleid] = '<a href="'.$assignurl.'">'.$strmorethanmax.'</a>';
        } else {
            $roleholdernames[$roleid] = '';
        }
    }

        $table = new html_table();
    $table->id = 'assignrole';
    $table->head = array(get_string('role'), get_string('description'), get_string('userswiththisrole', 'core_role'));
    $table->colclasses = array('leftalign role', 'leftalign', 'centeralign userrole');
    $table->attributes['class'] = 'admintable generaltable';
    if ($showroleholders) {
        $table->headspan = array(1, 1, 2);
        $table->colclasses[] = 'leftalign roleholder';
    }

    foreach ($assignableroles as $roleid => $rolename) {
        $description = format_string($DB->get_field('role', 'description', array('id'=>$roleid)));
        $assignurl = new moodle_url($PAGE->url, array('roleid'=>$roleid));
        if ($returnto !== null) {
            $assignurl->param('return', $returnto);
        }
        $row = array('<a href="'.$assignurl.'">'.$rolename.'</a>',
                $description, $assigncounts[$roleid]);
        if ($showroleholders) {
            $row[] = $roleholdernames[$roleid];
        }
        $table->data[] = $row;
    }

    echo html_writer::table($table);

    if ($context->contextlevel > CONTEXT_USER) {

        if ($context->contextlevel === CONTEXT_COURSECAT && $returnto === 'management') {
            $url = new moodle_url('/course/management.php', array('categoryid' => $context->instanceid));
        } else {
            $url = $context->get_url();
        }

        echo html_writer::start_tag('div', array('class'=>'backlink'));
        echo html_writer::tag('a', get_string('backto', '', $contextname), array('href' => $url));
        echo html_writer::end_tag('div');
    }
}

echo $OUTPUT->footer();
