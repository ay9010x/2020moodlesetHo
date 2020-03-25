<?php



defined('MOODLE_INTERNAL') || die;

if (!defined('REPORT_LOG_MAX_DISPLAY')) {
    define('REPORT_LOG_MAX_DISPLAY', 150); }

require_once(dirname(__FILE__).'/lib.php');


function report_log_print_graph($course, $userid, $type, $date=0, $logreader='') {
    $logmanager = get_log_manager();
    $readers = $logmanager->get_readers();

    if (empty($logreader)) {
        $reader = reset($readers);
    } else {
        $reader = $readers[$logreader];
    }
        if (!($reader instanceof \core\log\sql_internal_table_reader) && !($reader instanceof logstore_legacy\log\store)) {
        return array();
    }

    $url = new moodle_url('/report/log/graph.php', array('id' => $course->id, 'user' => $userid, 'type' => $type,
        'date' => $date, 'logreader' => $logreader));
    echo html_writer::empty_tag('img', array('src' => $url, 'alt' => ''));
}


function report_log_usercourse($userid, $courseid, $coursestart, $logreader = '') {
    global $DB;

    $logmanager = get_log_manager();
    $readers = $logmanager->get_readers();
    if (empty($logreader)) {
        $reader = reset($readers);
    } else {
        $reader = $readers[$logreader];
    }

        if (!($reader instanceof \core\log\sql_internal_table_reader) && !($reader instanceof logstore_legacy\log\store)) {
        return array();
    }

    $coursestart = (int)$coursestart;     if ($reader instanceof logstore_legacy\log\store) {
        $logtable = 'log';
        $timefield = 'time';
        $coursefield = 'course';
                $nonanonymous = '';
    } else {
        $logtable = $reader->get_internal_log_table_name();
        $timefield = 'timecreated';
        $coursefield = 'courseid';
        $nonanonymous = 'AND anonymous = 0';
    }

    $params = array();
    $courseselect = '';
    if ($courseid) {
        $courseselect = "AND $coursefield = :courseid";
        $params['courseid'] = $courseid;
    }
    $params['userid'] = $userid;
    return $DB->get_records_sql("SELECT FLOOR(($timefield - $coursestart)/" . DAYSECS . ") AS day, COUNT(*) AS num
                                   FROM {" . $logtable . "}
                                  WHERE userid = :userid
                                        AND $timefield > $coursestart $courseselect $nonanonymous
                               GROUP BY FLOOR(($timefield - $coursestart)/" . DAYSECS .")", $params);
}


function report_log_userday($userid, $courseid, $daystart, $logreader = '') {
    global $DB;
    $logmanager = get_log_manager();
    $readers = $logmanager->get_readers();
    if (empty($logreader)) {
        $reader = reset($readers);
    } else {
        $reader = $readers[$logreader];
    }

        if (!($reader instanceof \core\log\sql_internal_table_reader) && !($reader instanceof logstore_legacy\log\store)) {
        return array();
    }

    $daystart = (int)$daystart; 
    if ($reader instanceof logstore_legacy\log\store) {
        $logtable = 'log';
        $timefield = 'time';
        $coursefield = 'course';
                $nonanonymous = '';
    } else {
        $logtable = $reader->get_internal_log_table_name();
        $timefield = 'timecreated';
        $coursefield = 'courseid';
        $nonanonymous = 'AND anonymous = 0';
    }
    $params = array('userid' => $userid);

    $courseselect = '';
    if ($courseid) {
        $courseselect = "AND $coursefield = :courseid";
        $params['courseid'] = $courseid;
    }
    return $DB->get_records_sql("SELECT FLOOR(($timefield - $daystart)/" . HOURSECS . ") AS hour, COUNT(*) AS num
                                   FROM {" . $logtable . "}
                                  WHERE userid = :userid
                                        AND $timefield > $daystart $courseselect $nonanonymous
                               GROUP BY FLOOR(($timefield - $daystart)/" . HOURSECS . ") ", $params);
}


function report_log_print_mnet_selector_form($hostid, $course, $selecteduser=0, $selecteddate='today',
                                 $modname="", $modid=0, $modaction='', $selectedgroup=-1, $showcourses=0, $showusers=0, $logformat='showashtml') {

    global $USER, $CFG, $SITE, $DB, $OUTPUT, $SESSION;
    require_once $CFG->dirroot.'/mnet/peer.php';

    $mnet_peer = new mnet_peer();
    $mnet_peer->set_id($hostid);

    $sql = "SELECT DISTINCT course, hostid, coursename FROM {mnet_log}";
    $courses = $DB->get_records_sql($sql);
    $remotecoursecount = count($courses);

        $numcourses = $remotecoursecount + $DB->count_records('course');
    if ($numcourses < COURSE_MAX_COURSES_PER_DROPDOWN && !$showcourses) {
        $showcourses = 1;
    }

    $sitecontext = context_system::instance();

            if ($hostid == $CFG->mnet_localhost_id) {
        $context = context_course::instance($course->id);

                if ($course->groupmode == SEPARATEGROUPS and !has_capability('moodle/site:accessallgroups', $context)) {
            $selectedgroup = -1;
            $showgroups = false;
        } else if ($course->groupmode) {
            $showgroups = true;
        } else {
            $selectedgroup = 0;
            $showgroups = false;
        }

        if ($selectedgroup === -1) {
            if (isset($SESSION->currentgroup[$course->id])) {
                $selectedgroup =  $SESSION->currentgroup[$course->id];
            } else {
                $selectedgroup = groups_get_all_groups($course->id, $USER->id);
                if (is_array($selectedgroup)) {
                    $selectedgroup = array_shift(array_keys($selectedgroup));
                    $SESSION->currentgroup[$course->id] = $selectedgroup;
                } else {
                    $selectedgroup = 0;
                }
            }
        }

    } else {
        $context = $sitecontext;
    }

        $users = array();

            $limitfrom = empty($showusers) ? 0 : '';
    $limitnum  = empty($showusers) ? COURSE_MAX_USERS_PER_DROPDOWN + 1 : '';

        if ($hostid == $CFG->mnet_localhost_id && $course->id != SITEID) {
        $courseusers = get_enrolled_users($context, '', $selectedgroup, 'u.id, ' . get_all_user_name_fields(true, 'u'),
                null, $limitfrom, $limitnum);
    } else {
                $courseusers = $DB->get_records('user', array('deleted'=>0), 'lastaccess DESC', 'id, ' . get_all_user_name_fields(true),
                $limitfrom, $limitnum);
    }

    if (count($courseusers) < COURSE_MAX_USERS_PER_DROPDOWN && !$showusers) {
        $showusers = 1;
    }

    if ($showusers) {
        if ($courseusers) {
            foreach ($courseusers as $courseuser) {
                $users[$courseuser->id] = fullname($courseuser, has_capability('moodle/site:viewfullnames', $context));
            }
        }
        $users[$CFG->siteguest] = get_string('guestuser');
    }

        $sql = "select distinct
                h.id,
                h.name
            from
                {mnet_host} h,
                {mnet_log} l
            where
                h.id = l.hostid
            order by
                h.name";

    if ($hosts = $DB->get_records_sql($sql)) {
        foreach($hosts as $host) {
            $hostarray[$host->id] = $host->name;
        }
    }

    $hostarray[$CFG->mnet_localhost_id] = $SITE->fullname;
    asort($hostarray);

    $dropdown = array();

    foreach($hostarray as $hostid => $name) {
        $courses = array();
        $sites = array();
        if ($CFG->mnet_localhost_id == $hostid) {
            if (has_capability('report/log:view', $sitecontext) && $showcourses) {
                if ($ccc = $DB->get_records("course", null, "fullname","id,shortname,fullname,category")) {
                    foreach ($ccc as $cc) {
                        if ($cc->id == SITEID) {
                            $sites["$hostid/$cc->id"]   = format_string($cc->fullname).' ('.get_string('site').')';
                        } else {
                            $courses["$hostid/$cc->id"] = format_string(get_course_display_name_for_list($cc));
                        }
                    }
                }
            }
        } else {
            if (has_capability('report/log:view', $sitecontext) && $showcourses) {
                $sql = "SELECT DISTINCT course, coursename FROM {mnet_log} where hostid = ?";
                if ($ccc = $DB->get_records_sql($sql, array($hostid))) {
                    foreach ($ccc as $cc) {
                        if (1 == $cc->course) {                             $sites["$hostid/$cc->course"]   = $cc->coursename.' ('.get_string('site').')';
                        } else {
                            $courses["$hostid/$cc->course"] = $cc->coursename;
                        }
                    }
                }
            }
        }

        asort($courses);
        $dropdown[] = array($name=>($sites + $courses));
    }


    $activities = array();
    $selectedactivity = "";

    $modinfo = get_fast_modinfo($course);
    if (!empty($modinfo->cms)) {
        $section = 0;
        $thissection = array();
        foreach ($modinfo->cms as $cm) {
            if (!$cm->uservisible || !$cm->has_view()) {
                continue;
            }
            if ($cm->sectionnum > 0 and $section <> $cm->sectionnum) {
                $activities[] = $thissection;
                $thissection = array();
            }
            $section = $cm->sectionnum;
            $modname = strip_tags($cm->get_formatted_name());
            if (core_text::strlen($modname) > 55) {
                $modname = core_text::substr($modname, 0, 50)."...";
            }
            if (!$cm->visible) {
                $modname = "(".$modname.")";
            }
            $key = get_section_name($course, $cm->sectionnum);
            if (!isset($thissection[$key])) {
                $thissection[$key] = array();
            }
            $thissection[$key][$cm->id] = $modname;

            if ($cm->id == $modid) {
                $selectedactivity = "$cm->id";
            }
        }
        if (!empty($thissection)) {
            $activities[] = $thissection;
        }
    }

    if (has_capability('report/log:view', $sitecontext) && !$course->category) {
        $activities["site_errors"] = get_string("siteerrors");
        if ($modid === "site_errors") {
            $selectedactivity = "site_errors";
        }
    }

    $strftimedate = get_string("strftimedate");
    $strftimedaydate = get_string("strftimedaydate");

    asort($users);

        $actions = array(
        'view' => get_string('view'),
        'add' => get_string('add'),
        'update' => get_string('update'),
        'delete' => get_string('delete'),
        '-view' => get_string('allchanges')
    );

            
    $timenow = time(); 
        $timemidnight = $today = usergetmidnight($timenow);

        $dates = array(
        "0" => get_string('alldays'),
        "$timemidnight" => get_string("today").", ".userdate($timenow, $strftimedate)
    );

    if (!$course->startdate or ($course->startdate > $timenow)) {
        $course->startdate = $course->timecreated;
    }

    $numdates = 1;
    while ($timemidnight > $course->startdate and $numdates < 365) {
        $timemidnight = $timemidnight - 86400;
        $timenow = $timenow - 86400;
        $dates["$timemidnight"] = userdate($timenow, $strftimedaydate);
        $numdates++;
    }

    if ($selecteddate === "today") {
        $selecteddate = $today;
    }

    echo "<form class=\"logselectform\" action=\"$CFG->wwwroot/report/log/index.php\" method=\"get\">\n";
    echo "<div>\n";    echo "<input type=\"hidden\" name=\"chooselog\" value=\"1\" />\n";
    echo "<input type=\"hidden\" name=\"showusers\" value=\"$showusers\" />\n";
    echo "<input type=\"hidden\" name=\"showcourses\" value=\"$showcourses\" />\n";
    if (has_capability('report/log:view', $sitecontext) && $showcourses) {
        $cid = empty($course->id)? '1' : $course->id;
        echo html_writer::label(get_string('selectacoursesite'), 'menuhost_course', false, array('class' => 'accesshide'));
        echo html_writer::select($dropdown, "host_course", $hostid.'/'.$cid);
    } else {
        $courses = array();
        $courses[$course->id] = get_course_display_name_for_list($course) . ((empty($course->category)) ? ' ('.get_string('site').') ' : '');
        echo html_writer::label(get_string('selectacourse'), 'menuid', false, array('class' => 'accesshide'));
        echo html_writer::select($courses,"id",$course->id, false);
        if (has_capability('report/log:view', $sitecontext)) {
            $a = new stdClass();
            $a->url = "$CFG->wwwroot/report/log/index.php?chooselog=0&group=$selectedgroup&user=$selecteduser"
                ."&id=$course->id&date=$selecteddate&modid=$selectedactivity&showcourses=1&showusers=$showusers";
            print_string('logtoomanycourses','moodle',$a);
        }
    }

    if ($showgroups) {
        if ($cgroups = groups_get_all_groups($course->id)) {
            foreach ($cgroups as $cgroup) {
                $groups[$cgroup->id] = $cgroup->name;
            }
        }
        else {
            $groups = array();
        }
        echo html_writer::label(get_string('selectagroup'), 'menugroup', false, array('class' => 'accesshide'));
        echo html_writer::select($groups, "group", $selectedgroup, get_string("allgroups"));
    }

    if ($showusers) {
        echo html_writer::label(get_string('participantslist'), 'menuuser', false, array('class' => 'accesshide'));
        echo html_writer::select($users, "user", $selecteduser, get_string("allparticipants"));
    }
    else {
        $users = array();
        if (!empty($selecteduser)) {
            $user = $DB->get_record('user', array('id'=>$selecteduser));
            $users[$selecteduser] = fullname($user);
        }
        else {
            $users[0] = get_string('allparticipants');
        }
        echo html_writer::label(get_string('participantslist'), 'menuuser', false, array('class' => 'accesshide'));
        echo html_writer::select($users, "user", $selecteduser, false);
        $a = new stdClass();
        $a->url = "$CFG->wwwroot/report/log/index.php?chooselog=0&group=$selectedgroup&user=$selecteduser"
            ."&id=$course->id&date=$selecteddate&modid=$selectedactivity&showusers=1&showcourses=$showcourses";
        print_string('logtoomanyusers','moodle',$a);
    }

    echo html_writer::label(get_string('date'), 'menudate', false, array('class' => 'accesshide'));
    echo html_writer::select($dates, "date", $selecteddate, false);
    echo html_writer::label(get_string('showreports'), 'menumodid', false, array('class' => 'accesshide'));
    echo html_writer::select($activities, "modid", $selectedactivity, get_string("allactivities"));
    echo html_writer::label(get_string('actions'), 'menumodaction', false, array('class' => 'accesshide'));
    echo html_writer::select($actions, 'modaction', $modaction, get_string("allactions"));

    $logformats = array('showashtml' => get_string('displayonpage'),
                        'downloadascsv' => get_string('downloadtext'),
                        'downloadasods' => get_string('downloadods'),
                        'downloadasexcel' => get_string('downloadexcel'));
    echo html_writer::label(get_string('logsformat', 'report_log'), 'menulogformat', false, array('class' => 'accesshide'));
    echo html_writer::select($logformats, 'logformat', $logformat, false);
    echo '<input type="submit" value="'.get_string('gettheselogs').'" />';
    echo '</div>';
    echo '</form>';
}
