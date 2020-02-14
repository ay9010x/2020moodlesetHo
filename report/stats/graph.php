<?php



require('../../config.php');
require_once($CFG->dirroot.'/report/stats/locallib.php');
require_once($CFG->dirroot.'/lib/graphlib.php');

$courseid = required_param('course', PARAM_INT);
$report   = required_param('report', PARAM_INT);
$time     = required_param('time', PARAM_INT);
$mode     = required_param('mode', PARAM_INT);
$userid   = optional_param('userid', 0, PARAM_INT);
$roleid   = optional_param('roleid',0,PARAM_INT);

$url = new moodle_url('/report/stats/graph.php', array('course'=>$courseid, 'report'=>$report, 'time'=>$time, 'mode'=>$mode, 'userid'=>$userid, 'roleid'=>$roleid));
$PAGE->set_url($url);

$course = $DB->get_record("course", array("id"=>$courseid), '*', MUST_EXIST);
$coursecontext   = context_course::instance($course->id);
$PAGE->set_context($coursecontext);

if (!empty($userid)) {
    $user = $DB->get_record('user', array('id'=>$userid, 'deleted'=>0), '*', MUST_EXIST);
    $personalcontext = context_user::instance($user->id);

    if ($USER->id != $user->id and has_capability('moodle/user:viewuseractivitiesreport', $personalcontext)
            and !is_enrolled($coursecontext, $USER) and is_enrolled($coursecontext, $user)) {
                require_login();
        $PAGE->set_course($course);
    } else {
        require_login($course);
    }

    if (!report_stats_can_access_user_report($user, $course, true)) {
        require_capability('report/stats:view', $coursecontext);
    }
} else if ($mode === STATS_MODE_DETAILED) {
    print_error('invaliduser');
} else {
    require_capability('report/stats:view', $coursecontext);
}

stats_check_uptodate($course->id);

$param = stats_get_parameters($time,$report,$course->id,$mode);

if (!empty($userid)) {
    $param->table = 'user_'.$param->table;
}

$sql = 'SELECT '.((empty($param->fieldscomplete)) ? 'id,roleid,timeend,' : '').$param->fields
.' FROM {stats_'.$param->table.'} WHERE '
.(($course->id == SITEID) ? '' : ' courseid = '.$course->id.' AND ')
 .((!empty($userid)) ? ' userid = '.$userid.' AND ' : '')
 .((!empty($roleid)) ? ' roleid = '.$roleid.' AND ' : '')
 . ((!empty($param->stattype)) ? ' stattype = \''.$param->stattype.'\' AND ' : '')
 .' timeend >= '.$param->timeafter
.' '.$param->extras
.' ORDER BY timeend DESC';

$stats = $DB->get_records_sql($sql, $param->params);

$stats = stats_fix_zeros($stats,$param->timeafter,$param->table,(!empty($param->line2)),(!empty($param->line3)));

$stats = array_reverse($stats);

$graph = new graph(750,400);

$graph->parameter['legend'] = 'outside-right';
$graph->parameter['legend_size'] = 10;
$graph->parameter['x_axis_angle'] = 90;
$graph->parameter['title'] = false; $graph->y_tick_labels = null;

if (empty($param->crosstab)) {
    foreach ($stats as $stat) {
        $graph->x_data[] = userdate($stat->timeend,get_string('strftimedate'),$CFG->timezone);
        $graph->y_data['line1'][] = $stat->line1;
        if (isset($stat->line2)) {
            $graph->y_data['line2'][] = $stat->line2;
        }
        if (isset($stat->line3)) {
            $graph->y_data['line3'][] = $stat->line3;
        }
    }
    $graph->y_order = array('line1');
    $graph->y_format['line1'] = array('colour' => 'blue','line' => 'line','legend' => $param->line1);
    if (!empty($param->line2)) {
        $graph->y_order[] = 'line2';
        $graph->y_format['line2'] = array('colour' => 'green','line' => 'line','legend' => $param->line2);
    }
    if (!empty($param->line3)) {
        $graph->y_order[] = 'line3';
        $graph->y_format['line3'] = array('colour' => 'red','line' => 'line','legend' => $param->line3);
    }
    $graph->y_tick_labels = false;

} else {
    $data = array();
    $times = array();
    $roles = array();
    $missedlines = array();
    $rolenames = role_fix_names(get_all_roles($coursecontext), $coursecontext, ROLENAME_ALIAS, true);
    foreach ($stats as $stat) {
        $data[$stat->roleid][$stat->timeend] = $stat->line1;
        if (!empty($stat->zerofixed)) {
            $missedlines[] = $stat->timeend;
        }
        if ($stat->roleid != 0) {
            if (!array_key_exists($stat->roleid,$roles)) {
                $roles[$stat->roleid] = $rolenames[$stat->roleid];
            }
        } else {
            if (!array_key_exists($stat->roleid,$roles)) {
                $roles[$stat->roleid] = get_string('all');
            }
        }
        if (!array_key_exists($stat->timeend,$times)) {
            $times[$stat->timeend] = userdate($stat->timeend,get_string('strftimedate'),$CFG->timezone);
        }
    }
    foreach (array_keys($times) as $t) {
        foreach ($data as $roleid => $stuff) {
            if (!array_key_exists($t, $stuff)) {
                $data[$roleid][$t] = 0;
            }
        }
    }

    $roleid = 0;
    krsort($roles); 
    $colors = array('green', 'blue', 'red', 'purple', 'yellow', 'olive', 'navy', 'maroon', 'gray', 'ltred', 'ltltred', 'ltgreen', 'ltltgreen', 'orange', 'ltorange', 'ltltorange', 'lime', 'ltblue', 'ltltblue', 'fuchsia', 'aqua', 'grayF0', 'grayEE', 'grayDD', 'grayCC', 'gray33', 'gray66', 'gray99');
    $colorindex = 0;

    foreach ($roles as $roleid=>$rname) {
        ksort($data[$roleid]);
        $graph->y_order[] = $roleid+1;
        if ($roleid) {
            $color = $colors[$colorindex++];
            $colorindex = $colorindex % count($colors);
        } else {
            $color = 'black';
        }
        $graph->y_format[$roleid+1] = array('colour' => $color, 'line' => 'line','legend' => $rname);
    }
    foreach (array_keys($data[$roleid]) as $time) {
        $graph->x_data[] = $times[$time];
    }
    foreach ($data as $roleid => $t) {
        foreach ($t as $time => $data) {
            $graph->y_data[$roleid+1][] = $data;
        }
    }
}

$graph->draw_stack();

