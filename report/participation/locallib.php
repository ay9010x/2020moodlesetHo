<?php



defined('MOODLE_INTERNAL') || die();


function report_participation_get_log_table_name() {
        $logmanager = get_log_manager();
    $readers = $logmanager->get_readers();
    $logtable = '';

        if (!empty($readers)) {
        foreach ($readers as $readerpluginname => $reader) {
                        if ($readerpluginname == 'logstore_legacy') {
                break;
            }

                        if ($reader instanceof \core\log\sql_internal_table_reader) {
                $logtable = $reader->get_internal_log_table_name();
                break;
            }
        }
    }
    return $logtable;
}


function report_participation_get_time_options($minlog) {
    $timeoptions = array();
    $now = usergetmidnight(time());

        for ($i = 1; $i < 7; $i++) {
        if (strtotime('-'.$i.' days',$now) >= $minlog) {
            $timeoptions[strtotime('-'.$i.' days',$now)] = get_string('numdays','moodle',$i);
        }
    }
        for ($i = 1; $i < 10; $i++) {
        if (strtotime('-'.$i.' weeks',$now) >= $minlog) {
            $timeoptions[strtotime('-'.$i.' weeks',$now)] = get_string('numweeks','moodle',$i);
        }
    }
        for ($i = 2; $i < 12; $i++) {
        if (strtotime('-'.$i.' months',$now) >= $minlog) {
            $timeoptions[strtotime('-'.$i.' months',$now)] = get_string('nummonths','moodle',$i);
        }
    }
        if (strtotime('-1 year',$now) >= $minlog) {
        $timeoptions[strtotime('-1 year',$now)] = get_string('lastyear');
    }
    return $timeoptions;
}


function report_participation_get_action_sql($action, $modname) {
    global $CFG, $DB;

    $crudsql = '';
    $crudparams = array();

    $viewnames = array();
    $postnames = array();
    include_once($CFG->dirroot.'/mod/' . $modname . '/lib.php');

    $viewfun = $modname.'_get_view_actions';
    $postfun = $modname.'_get_post_actions';

    if (function_exists($viewfun)) {
        $viewnames = $viewfun();
    }

    if (function_exists($postfun)) {
        $postnames = $postfun();
    }

    switch ($action) {
        case 'view':
            $actions = $viewnames;
            break;
        case 'post':
            $actions = $postnames;
            break;
        default:
                        $actions = array_merge($viewnames, $postnames);
    }

    if (!empty($actions)) {
        list($actionsql, $actionparams) = $DB->get_in_or_equal($actions, SQL_PARAMS_NAMED, 'action');
        $actionsql = " AND action $actionsql";
    }

    return array($actionsql, $actionparams);
}


function report_participation_get_crud_sql($action) {
    global $DB;

    switch ($action) {
        case 'view':
            $crud = 'r';
            break;
        case 'post':
            $crud = array('c', 'u', 'd');
            break;
        default:
            $crud = array('c', 'r', 'u', 'd');
    }

    list($crudsql, $crudparams) = $DB->get_in_or_equal($crud, SQL_PARAMS_NAMED, 'crud');
    $crudsql = " AND crud " . $crudsql;
    return array($crudsql, $crudparams);
}


function report_participation_get_action_options() {
    return array('' => get_string('allactions'),
            'view' => get_string('view'),
            'post' => get_string('post'),);
}


function report_participation_print_filter_form($course, $timefrom, $minlog, $action, $roleid, $instanceid) {
    global $DB;

    $timeoptions = report_participation_get_time_options($minlog);

    $actionoptions = report_participation_get_action_options();

        $context = context_course::instance($course->id);
    $roles = get_roles_used_in_context($context);
    $guestrole = get_guest_role();
    $roles[$guestrole->id] = $guestrole;
    $roleoptions = role_fix_names($roles, $context, ROLENAME_ALIAS, true);

    $modinfo = get_fast_modinfo($course);

    $modules = $DB->get_records_select('modules', "visible = 1", null, 'name ASC');

    $instanceoptions = array();
    foreach ($modules as $module) {
        if (empty($modinfo->instances[$module->name])) {
            continue;
        }
        $instances = array();
        foreach ($modinfo->instances[$module->name] as $cm) {
                                    if (!$cm->has_view()) {
                continue;
            }
            $instances[$cm->id] = format_string($cm->name);
        }
        if (count($instances) == 0) {
            continue;
        }
        $instanceoptions[] = array(get_string('modulenameplural', $module->name)=>$instances);
    }

    echo '<form class="participationselectform" action="index.php" method="get"><div>'."\n".
        '<input type="hidden" name="id" value="'.$course->id.'" />'."\n";
    echo '<label for="menuinstanceid">'.get_string('activitymodule').'</label>'."\n";
    echo html_writer::select($instanceoptions, 'instanceid', $instanceid);
    echo '<label for="menutimefrom">'.get_string('lookback').'</label>'."\n";
    echo html_writer::select($timeoptions,'timefrom',$timefrom);
    echo '<label for="menuroleid">'.get_string('showonly').'</label>'."\n";
    echo html_writer::select($roleoptions,'roleid',$roleid,false);
    echo '<label for="menuaction">'.get_string('showactions').'</label>'."\n";
    echo html_writer::select($actionoptions,'action',$action,false);
    echo '<input type="submit" value="'.get_string('go').'" />'."\n</div></form>\n";
}
