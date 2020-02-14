<?php




defined('MOODLE_INTERNAL') || die();



define('STATS_REPORT_LOGINS',1); define('STATS_REPORT_READS',2); define('STATS_REPORT_WRITES',3); define('STATS_REPORT_ACTIVITY',4); define('STATS_REPORT_ACTIVITYBYROLE',5); 
define('STATS_REPORT_USER_ACTIVITY',7);
define('STATS_REPORT_USER_ALLACTIVITY',8);
define('STATS_REPORT_USER_LOGINS',9);
define('STATS_REPORT_USER_VIEW',10);  
define('STATS_REPORT_ACTIVE_COURSES',11);
define('STATS_REPORT_ACTIVE_COURSES_WEIGHTED',12);
define('STATS_REPORT_PARTICIPATORY_COURSES',13);
define('STATS_REPORT_PARTICIPATORY_COURSES_RW',14);

define('STATS_TIME_LASTWEEK',1);
define('STATS_TIME_LAST2WEEKS',2);
define('STATS_TIME_LAST3WEEKS',3);
define('STATS_TIME_LAST4WEEKS',4);

define('STATS_TIME_LAST2MONTHS',12);

define('STATS_TIME_LAST3MONTHS',13);
define('STATS_TIME_LAST4MONTHS',14);
define('STATS_TIME_LAST5MONTHS',15);
define('STATS_TIME_LAST6MONTHS',16);

define('STATS_TIME_LAST7MONTHS',27);
define('STATS_TIME_LAST8MONTHS',28);
define('STATS_TIME_LAST9MONTHS',29);
define('STATS_TIME_LAST10MONTHS',30);
define('STATS_TIME_LAST11MONTHS',31);
define('STATS_TIME_LASTYEAR',32);

define('STATS_MODE_GENERAL',1);
define('STATS_MODE_DETAILED',2);
define('STATS_MODE_RANKED',3); 
define('STATS_PLACEHOLDER_OUTPUT', '.');


function stats_progress($ident) {
    static $start = 0;
    static $init  = 0;

    if ($ident == 'init') {
        $init = $start = microtime(true);
        return;
    }

    $elapsed = round(microtime(true) - $start);
    $start   = microtime(true);

    if (debugging('', DEBUG_ALL)) {
        mtrace("$ident:$elapsed ", '');
    } else {
        mtrace(STATS_PLACEHOLDER_OUTPUT, '');
    }
}


function stats_run_query($sql, $parameters = array()) {
    global $DB;

    try {
        $DB->execute($sql, $parameters);
    } catch (dml_exception $e) {

       if (debugging('', DEBUG_ALL)) {
           mtrace($e->getMessage());
       }
       return false;
    }
    return true;
}


function stats_cron_daily($maxdays=1) {
    global $CFG, $DB;
    require_once($CFG->libdir.'/adminlib.php');

    $now = time();

    $fpcontext = context_course::instance(SITEID, MUST_EXIST);

        if (!$timestart = get_config(NULL, 'statslastdaily')) {
        $timestart = stats_get_base_daily(stats_get_start_from('daily'));
        set_config('statslastdaily', $timestart);
    }

    $nextmidnight = stats_get_next_day_start($timestart);

        if ($now < $nextmidnight) {
        return true;     }

    $timeout = empty($CFG->statsmaxruntime) ? 60*60*24 : $CFG->statsmaxruntime;

    if (!set_cron_lock('statsrunning', $now + $timeout)) {
        return false;
    }

        $DB->delete_records_select('stats_daily',      "timeend > $timestart");
    $DB->delete_records_select('stats_user_daily', "timeend > $timestart");

        $viewactions = stats_get_action_names('view');
    $postactions = stats_get_action_names('post');

    $guest           = (int)$CFG->siteguest;
    $guestrole       = (int)$CFG->guestroleid;
    $defaultfproleid = (int)$CFG->defaultfrontpageroleid;

    mtrace("Running daily statistics gathering, starting at $timestart:");
    cron_trace_time_and_memory();

    $days  = 0;
    $total = 0;
    $failed  = false;     $timeout = false;

    if (!stats_temp_table_create()) {
        $days = 1;
        $failed = true;
    }
    mtrace('Temporary tables created');

    if(!stats_temp_table_setup()) {
        $days = 1;
        $failed = true;
    }
    mtrace('Enrolments calculated');

    $totalactiveusers = $DB->count_records('user', array('deleted' => '0'));

    while (!$failed && ($now > $nextmidnight)) {
        if ($days >= $maxdays) {
            $timeout = true;
            break;
        }

        $days++;
        core_php_time_limit::raise($timeout - 200);

        if ($days > 1) {
                        set_cron_lock('statsrunning', time() + $timeout, true);
        }

        $daystart = time();

        stats_progress('init');

        if (!stats_temp_table_fill($timestart, $nextmidnight)) {
            $failed = true;
            break;
        }

                $sql = "SELECT 'x' FROM {temp_log1} l";
        $logspresent = $DB->get_records_sql($sql, null, 0, 1);

        if ($logspresent) {
                                    $DB->insert_record('temp_log1', array('userid' => 0, 'course' => SITEID, 'action' => ''));
        }

                $sql = 'SELECT COUNT(DISTINCT u.id)
                  FROM {user} u
                  JOIN {temp_log1} l ON l.userid = u.id
                 WHERE u.deleted = 0';
        $dailyactiveusers = $DB->count_records_sql($sql);

        stats_progress('0');

                        $sql = "INSERT INTO {temp_stats_user_daily}
                            (stattype, timeend, courseid, userid, statsreads)

                SELECT 'logins', $nextmidnight AS timeend, ".SITEID." AS courseid,
                        userid, COUNT(id) AS statsreads
                  FROM {temp_log1} l
                 WHERE action = 'login'
              GROUP BY userid
                HAVING COUNT(id) > 0";

        if ($logspresent && !stats_run_query($sql)) {
            $failed = true;
            break;
        }
        $DB->update_temp_table_stats();

        stats_progress('1');

        $sql = "INSERT INTO {temp_stats_daily} (stattype, timeend, courseid, roleid, stat1, stat2)

                SELECT 'logins' AS stattype, $nextmidnight AS timeend, ".SITEID." AS courseid, 0,
                       COALESCE(SUM(statsreads), 0) as stat1, COUNT('x') as stat2
                  FROM {temp_stats_user_daily}
                 WHERE stattype = 'logins' AND timeend = $nextmidnight";

        if ($logspresent && !stats_run_query($sql)) {
            $failed = true;
            break;
        }
        stats_progress('2');


                                                                        
        $sql = "INSERT INTO {temp_stats_daily} (stattype, timeend, courseid, roleid, stat1, stat2)

                SELECT 'enrolments' as stattype, $nextmidnight as timeend, courseid, roleid,
                        COUNT(DISTINCT userid) as stat1, 0 as stat2
                  FROM {temp_enroled}
              GROUP BY courseid, roleid";

        if (!stats_run_query($sql)) {
            $failed = true;
            break;
        }
        stats_progress('3');

                        $sql = "UPDATE {temp_stats_daily}
                   SET stat2 = (

                    SELECT COUNT(DISTINCT userid)
                      FROM {temp_enroled} te
                     WHERE roleid = {temp_stats_daily}.roleid
                       AND courseid = {temp_stats_daily}.courseid
                       AND EXISTS (

                        SELECT 'x'
                          FROM {temp_log1} l
                         WHERE l.course = {temp_stats_daily}.courseid
                           AND l.userid = te.userid
                                  )
                               )
                 WHERE {temp_stats_daily}.stattype = 'enrolments'
                   AND {temp_stats_daily}.timeend = $nextmidnight
                   AND {temp_stats_daily}.courseid IN (

                    SELECT DISTINCT course FROM {temp_log2})";

        if ($logspresent && !stats_run_query($sql, array('courselevel'=>CONTEXT_COURSE))) {
            $failed = true;
            break;
        }
        stats_progress('4');

                $sql = "INSERT INTO {temp_stats_daily} (stattype, timeend, courseid, roleid, stat1, stat2)

                SELECT 'enrolments', $nextmidnight AS timeend, te.courseid AS courseid, 0 AS roleid,
                       COUNT(DISTINCT userid) AS stat1, 0 AS stat2
                  FROM {temp_enroled} te
              GROUP BY courseid
                HAVING COUNT(DISTINCT userid) > 0";

        if ($logspresent && !stats_run_query($sql)) {
            $failed = true;
            break;
        }
        stats_progress('5');

                $sql = "UPDATE {temp_stats_daily}
                   SET stat2 = (

                    SELECT COUNT(DISTINCT te.userid)
                      FROM {temp_enroled} te
                     WHERE te.courseid = {temp_stats_daily}.courseid
                       AND EXISTS (

                        SELECT 'x'
                          FROM {temp_log1} l
                         WHERE l.course = {temp_stats_daily}.courseid
                           AND l.userid = te.userid
                                  )
                               )

                 WHERE {temp_stats_daily}.stattype = 'enrolments'
                   AND {temp_stats_daily}.timeend = $nextmidnight
                   AND {temp_stats_daily}.roleid = 0
                   AND {temp_stats_daily}.courseid IN (

                    SELECT l.course
                      FROM {temp_log2} l
                     WHERE l.course <> ".SITEID.")";

        if ($logspresent && !stats_run_query($sql, array())) {
            $failed = true;
            break;
        }
        stats_progress('6');

                $sql = "INSERT INTO {temp_stats_daily} (stattype, timeend, courseid, roleid, stat1, stat2)

                SELECT 'enrolments', $nextmidnight, ".SITEID.", 0, $totalactiveusers AS stat1,
                       $dailyactiveusers AS stat2" .
                $DB->sql_null_from_clause();

        if ($logspresent && !stats_run_query($sql)) {
            $failed = true;
            break;
        }
                        $DB->update_temp_table_stats();

        stats_progress('7');

                if ($defaultfproleid) {
                        $sql = "DELETE
                      FROM {temp_stats_daily}
                     WHERE stattype = 'enrolments'
                       AND courseid = ".SITEID."
                       AND roleid = $defaultfproleid
                       AND timeend = $nextmidnight";

            if ($logspresent && !stats_run_query($sql)) {
                $failed = true;
                break;
            }
            stats_progress('8');

            $sql = "INSERT INTO {temp_stats_daily} (stattype, timeend, courseid, roleid, stat1, stat2)

                    SELECT 'enrolments', $nextmidnight, ".SITEID.", $defaultfproleid,
                           $totalactiveusers AS stat1, $dailyactiveusers AS stat2" .
                    $DB->sql_null_from_clause();

            if ($logspresent && !stats_run_query($sql)) {
                $failed = true;
                break;
            }
            stats_progress('9');

        } else {
            stats_progress('x');
            stats_progress('x');
        }


                list($viewactionssql, $params1) = $DB->get_in_or_equal($viewactions, SQL_PARAMS_NAMED, 'view');
        list($postactionssql, $params2) = $DB->get_in_or_equal($postactions, SQL_PARAMS_NAMED, 'post');
        $sql = "INSERT INTO {temp_stats_user_daily} (stattype, timeend, courseid, userid, statsreads, statswrites)

                SELECT 'activity' AS stattype, $nextmidnight AS timeend, course AS courseid, userid,
                       SUM(CASE WHEN action $viewactionssql THEN 1 ELSE 0 END) AS statsreads,
                       SUM(CASE WHEN action $postactionssql THEN 1 ELSE 0 END) AS statswrites
                  FROM {temp_log1} l
              GROUP BY userid, course";

        if ($logspresent && !stats_run_query($sql, array_merge($params1, $params2))) {
            $failed = true;
            break;
        }
        stats_progress('10');


                $sql = "INSERT INTO {temp_stats_daily} (stattype, timeend, courseid, roleid, stat1, stat2)

                SELECT 'activity' AS stattype, $nextmidnight AS timeend, c.id AS courseid, 0,
                       SUM(CASE WHEN l.action $viewactionssql THEN 1 ELSE 0 END) AS stat1,
                       SUM(CASE WHEN l.action $postactionssql THEN 1 ELSE 0 END) AS stat2
                  FROM {course} c, {temp_log1} l
                 WHERE l.course = c.id
              GROUP BY c.id";

        if ($logspresent && !stats_run_query($sql, array_merge($params1, $params2))) {
            $failed = true;
            break;
        }
        stats_progress('11');


        
        $sql = "INSERT INTO {temp_stats_daily} (stattype, timeend, courseid, roleid, stat1, stat2)

                SELECT 'activity', $nextmidnight AS timeend, courseid, roleid, SUM(statsreads), SUM(statswrites)
                  FROM (

                    SELECT pl.courseid, pl.roleid, sud.statsreads, sud.statswrites
                      FROM {temp_stats_user_daily} sud, (

                        SELECT DISTINCT te.userid, te.roleid, te.courseid
                          FROM {temp_enroled} te
                         WHERE te.roleid <> $guestrole
                           AND te.userid <> $guest
                                                        ) pl

                     WHERE sud.userid = pl.userid
                       AND sud.courseid = pl.courseid
                       AND sud.timeend = $nextmidnight
                       AND sud.stattype='activity'
                       ) inline_view

              GROUP BY courseid, roleid
                HAVING SUM(statsreads) > 0 OR SUM(statswrites) > 0";

        if ($logspresent && !stats_run_query($sql, array('courselevel'=>CONTEXT_COURSE))) {
            $failed = true;
            break;
        }
        stats_progress('12');

                
        $sql = "INSERT INTO {temp_stats_daily} (stattype, timeend, courseid, roleid, stat1, stat2)

                SELECT 'activity', $nextmidnight AS timeend, courseid, $guestrole AS roleid,
                       SUM(statsreads), SUM(statswrites)
                  FROM (

                    SELECT sud.courseid, sud.statsreads, sud.statswrites
                      FROM {temp_stats_user_daily} sud
                     WHERE sud.timeend = $nextmidnight
                       AND sud.courseid <> ".SITEID."
                       AND sud.stattype='activity'
                       AND (sud.userid = $guest OR sud.userid NOT IN (

                        SELECT userid
                          FROM {temp_enroled} te
                         WHERE te.courseid = sud.courseid
                                                                     ))
                       ) inline_view

              GROUP BY courseid
                HAVING SUM(statsreads) > 0 OR SUM(statswrites) > 0";

        if ($logspresent && !stats_run_query($sql, array())) {
            $failed = true;
            break;
        }
        stats_progress('13');


                $sql = "INSERT INTO {temp_stats_daily} (stattype, timeend, courseid, roleid, stat1, stat2)

                SELECT 'activity', $nextmidnight AS timeend, courseid, roleid,
                       SUM(statsreads), SUM(statswrites)
                  FROM (
                    SELECT pl.courseid, pl.roleid, sud.statsreads, sud.statswrites
                      FROM {temp_stats_user_daily} sud, (

                        SELECT DISTINCT ra.userid, ra.roleid, c.instanceid AS courseid
                          FROM {role_assignments} ra
                          JOIN {context} c ON c.id = ra.contextid
                         WHERE ra.contextid = :fpcontext
                           AND ra.roleid <> $defaultfproleid
                           AND ra.roleid <> $guestrole
                           AND ra.userid <> $guest
                                                   ) pl
                     WHERE sud.userid = pl.userid
                       AND sud.courseid = pl.courseid
                       AND sud.timeend = $nextmidnight
                       AND sud.stattype='activity'
                       ) inline_view

              GROUP BY courseid, roleid
                HAVING SUM(statsreads) > 0 OR SUM(statswrites) > 0";

        if ($logspresent && !stats_run_query($sql, array('fpcontext'=>$fpcontext->id))) {
            $failed = true;
            break;
        }
        stats_progress('14');


                $sql = "INSERT INTO {temp_stats_daily} (stattype, timeend, courseid, roleid, stat1, stat2)

                SELECT 'activity', timeend, courseid, $defaultfproleid AS roleid,
                       SUM(statsreads), SUM(statswrites)
                  FROM (
                    SELECT sud.timeend AS timeend, sud.courseid, sud.statsreads, sud.statswrites
                      FROM {temp_stats_user_daily} sud
                     WHERE sud.timeend = :nextm
                       AND sud.courseid = :siteid
                       AND sud.stattype='activity'
                       AND sud.userid <> $guest
                       AND sud.userid <> 0
                       AND sud.userid NOT IN (

                        SELECT ra.userid
                          FROM {role_assignments} ra
                         WHERE ra.roleid <> $guestrole
                           AND ra.roleid <> $defaultfproleid
                           AND ra.contextid = :fpcontext)
                       ) inline_view

              GROUP BY timeend, courseid
                HAVING SUM(statsreads) > 0 OR SUM(statswrites) > 0";

        if ($logspresent && !stats_run_query($sql, array('fpcontext'=>$fpcontext->id, 'siteid'=>SITEID, 'nextm'=>$nextmidnight))) {
            $failed = true;
            break;
        }
        $DB->update_temp_table_stats();
        stats_progress('15');

                $sql = "INSERT INTO {temp_stats_daily} (stattype, timeend, courseid, roleid, stat1, stat2)

                SELECT stattype, timeend, courseid, $guestrole AS roleid,
                       SUM(statsreads) AS stat1, SUM(statswrites) AS stat2
                  FROM (
                    SELECT sud.stattype, sud.timeend, sud.courseid,
                           sud.statsreads, sud.statswrites
                      FROM {temp_stats_user_daily} sud
                     WHERE (sud.userid = $guest OR sud.userid = 0)
                       AND sud.timeend = $nextmidnight
                       AND sud.courseid = ".SITEID."
                       AND sud.stattype='activity'
                       ) inline_view
                 GROUP BY stattype, timeend, courseid
                 HAVING SUM(statsreads) > 0 OR SUM(statswrites) > 0";

        if ($logspresent && !stats_run_query($sql)) {
            $failed = true;
            break;
        }
        stats_progress('16');

        stats_temp_table_clean();

        stats_progress('out');

                set_config('statslastdaily', $nextmidnight);
        $elapsed = time()-$daystart;
        mtrace("  finished until $nextmidnight: ".userdate($nextmidnight)." (in $elapsed s)");
        $total += $elapsed;

        $timestart    = $nextmidnight;
        $nextmidnight = stats_get_next_day_start($nextmidnight);
    }

    stats_temp_table_drop();

    set_cron_lock('statsrunning', null);

    if ($failed) {
        $days--;
        mtrace("...error occurred, completed $days days of statistics in {$total} s.");
        return false;

    } else if ($timeout) {
        mtrace("...stopping early, reached maximum number of $maxdays days ({$total} s) - will continue next time.");
        return false;

    } else {
        mtrace("...completed $days days of statistics in {$total} s.");
        return true;
    }
}



function stats_cron_weekly() {
    global $CFG, $DB;
    require_once($CFG->libdir.'/adminlib.php');

    $now = time();

        if (!$timestart = get_config(NULL, 'statslastweekly')) {
        $timestart = stats_get_base_daily(stats_get_start_from('weekly'));
        set_config('statslastweekly', $timestart);
    }

    $nextstartweek = stats_get_next_week_start($timestart);

        if ($now < $nextstartweek) {
        return true;     }

    $timeout = empty($CFG->statsmaxruntime) ? 60*60*24 : $CFG->statsmaxruntime;

    if (!set_cron_lock('statsrunning', $now + $timeout)) {
        return false;
    }

        $DB->delete_records_select('stats_weekly',      "timeend > $timestart");
    $DB->delete_records_select('stats_user_weekly', "timeend > $timestart");

    mtrace("Running weekly statistics gathering, starting at $timestart:");
    cron_trace_time_and_memory();

    $weeks = 0;
    while ($now > $nextstartweek) {
        core_php_time_limit::raise($timeout - 200);
        $weeks++;

        if ($weeks > 1) {
                        set_cron_lock('statsrunning', time() + $timeout, true);
        }

        $stattimesql = "timeend > $timestart AND timeend <= $nextstartweek";

        $weekstart = time();
        stats_progress('init');

            $sql = "INSERT INTO {stats_user_weekly} (stattype, timeend, courseid, userid, statsreads)

                SELECT 'logins', timeend, courseid, userid, SUM(statsreads)
                  FROM (
                           SELECT $nextstartweek AS timeend, courseid, userid, statsreads
                             FROM {stats_user_daily} sd
                            WHERE stattype = 'logins' AND $stattimesql
                       ) inline_view
              GROUP BY timeend, courseid, userid
                HAVING SUM(statsreads) > 0";

        $DB->execute($sql);

        stats_progress('1');

        $sql = "INSERT INTO {stats_weekly} (stattype, timeend, courseid, roleid, stat1, stat2)

                SELECT 'logins' AS stattype, $nextstartweek AS timeend, ".SITEID." as courseid, 0,
                       COALESCE((SELECT SUM(statsreads)
                                   FROM {stats_user_weekly} s1
                                  WHERE s1.stattype = 'logins' AND timeend = $nextstartweek), 0) AS nstat1,
                       (SELECT COUNT('x')
                          FROM {stats_user_weekly} s2
                         WHERE s2.stattype = 'logins' AND timeend = $nextstartweek) AS nstat2" .
                $DB->sql_null_from_clause();

        $DB->execute($sql);

        stats_progress('2');

            $sql = "INSERT INTO {stats_weekly} (stattype, timeend, courseid, roleid, stat1, stat2)

                SELECT 'enrolments', ntimeend, courseid, roleid, " . $DB->sql_ceil('AVG(stat1)') . ", " . $DB->sql_ceil('AVG(stat2)') . "
                  FROM (
                           SELECT $nextstartweek AS ntimeend, courseid, roleid, stat1, stat2
                             FROM {stats_daily} sd
                            WHERE stattype = 'enrolments' AND $stattimesql
                       ) inline_view
              GROUP BY ntimeend, courseid, roleid";

        $DB->execute($sql);

        stats_progress('3');

            $sql = "INSERT INTO {stats_weekly} (stattype, timeend, courseid, roleid, stat1, stat2)

                SELECT 'activity', ntimeend, courseid, roleid, SUM(stat1), SUM(stat2)
                  FROM (
                           SELECT $nextstartweek AS ntimeend, courseid, roleid, stat1, stat2
                             FROM {stats_daily}
                            WHERE stattype = 'activity' AND $stattimesql
                       ) inline_view
              GROUP BY ntimeend, courseid, roleid";

        $DB->execute($sql);

        stats_progress('4');

            $sql = "INSERT INTO {stats_user_weekly} (stattype, timeend, courseid, userid, statsreads, statswrites)

                SELECT 'activity', ntimeend, courseid, userid, SUM(statsreads), SUM(statswrites)
                  FROM (
                           SELECT $nextstartweek AS ntimeend, courseid, userid, statsreads, statswrites
                             FROM {stats_user_daily}
                            WHERE stattype = 'activity' AND $stattimesql
                       ) inline_view
              GROUP BY ntimeend, courseid, userid";

        $DB->execute($sql);

        stats_progress('5');

        set_config('statslastweekly', $nextstartweek);
        $elapsed = time()-$weekstart;
        mtrace(" finished until $nextstartweek: ".userdate($nextstartweek) ." (in $elapsed s)");

        $timestart     = $nextstartweek;
        $nextstartweek = stats_get_next_week_start($nextstartweek);
    }

    set_cron_lock('statsrunning', null);
    mtrace("...completed $weeks weeks of statistics.");
    return true;
}


function stats_cron_monthly() {
    global $CFG, $DB;
    require_once($CFG->libdir.'/adminlib.php');

    $now = time();

        if (!$timestart = get_config(NULL, 'statslastmonthly')) {
        $timestart = stats_get_base_monthly(stats_get_start_from('monthly'));
        set_config('statslastmonthly', $timestart);
    }

    $nextstartmonth = stats_get_next_month_start($timestart);

        if ($now < $nextstartmonth) {
        return true;     }

    $timeout = empty($CFG->statsmaxruntime) ? 60*60*24 : $CFG->statsmaxruntime;

    if (!set_cron_lock('statsrunning', $now + $timeout)) {
        return false;
    }

        $DB->delete_records_select('stats_monthly', "timeend > $timestart");
    $DB->delete_records_select('stats_user_monthly', "timeend > $timestart");

    $startmonth = stats_get_base_monthly($now);


    mtrace("Running monthly statistics gathering, starting at $timestart:");
    cron_trace_time_and_memory();

    $months = 0;
    while ($now > $nextstartmonth) {
        core_php_time_limit::raise($timeout - 200);
        $months++;

        if ($months > 1) {
                        set_cron_lock('statsrunning', time() + $timeout, true);
        }

        $stattimesql = "timeend > $timestart AND timeend <= $nextstartmonth";

        $monthstart = time();
        stats_progress('init');

            $sql = "INSERT INTO {stats_user_monthly} (stattype, timeend, courseid, userid, statsreads)

                SELECT 'logins', timeend, courseid, userid, SUM(statsreads)
                  FROM (
                           SELECT $nextstartmonth AS timeend, courseid, userid, statsreads
                             FROM {stats_user_daily} sd
                            WHERE stattype = 'logins' AND $stattimesql
                       ) inline_view
              GROUP BY timeend, courseid, userid
                HAVING SUM(statsreads) > 0";

        $DB->execute($sql);

        stats_progress('1');

        $sql = "INSERT INTO {stats_monthly} (stattype, timeend, courseid, roleid, stat1, stat2)

                SELECT 'logins' AS stattype, $nextstartmonth AS timeend, ".SITEID." as courseid, 0,
                       COALESCE((SELECT SUM(statsreads)
                                   FROM {stats_user_monthly} s1
                                  WHERE s1.stattype = 'logins' AND timeend = $nextstartmonth), 0) AS nstat1,
                       (SELECT COUNT('x')
                          FROM {stats_user_monthly} s2
                         WHERE s2.stattype = 'logins' AND timeend = $nextstartmonth) AS nstat2" .
                $DB->sql_null_from_clause();

        $DB->execute($sql);

        stats_progress('2');

            $sql = "INSERT INTO {stats_monthly} (stattype, timeend, courseid, roleid, stat1, stat2)

                SELECT 'enrolments', ntimeend, courseid, roleid, " . $DB->sql_ceil('AVG(stat1)') . ", " . $DB->sql_ceil('AVG(stat2)') . "
                  FROM (
                           SELECT $nextstartmonth AS ntimeend, courseid, roleid, stat1, stat2
                             FROM {stats_daily} sd
                            WHERE stattype = 'enrolments' AND $stattimesql
                       ) inline_view
              GROUP BY ntimeend, courseid, roleid";

        $DB->execute($sql);

        stats_progress('3');

            $sql = "INSERT INTO {stats_monthly} (stattype, timeend, courseid, roleid, stat1, stat2)

                SELECT 'activity', ntimeend, courseid, roleid, SUM(stat1), SUM(stat2)
                  FROM (
                           SELECT $nextstartmonth AS ntimeend, courseid, roleid, stat1, stat2
                             FROM {stats_daily}
                            WHERE stattype = 'activity' AND $stattimesql
                       ) inline_view
              GROUP BY ntimeend, courseid, roleid";

        $DB->execute($sql);

        stats_progress('4');

            $sql = "INSERT INTO {stats_user_monthly} (stattype, timeend, courseid, userid, statsreads, statswrites)

                SELECT 'activity', ntimeend, courseid, userid, SUM(statsreads), SUM(statswrites)
                  FROM (
                           SELECT $nextstartmonth AS ntimeend, courseid, userid, statsreads, statswrites
                             FROM {stats_user_daily}
                            WHERE stattype = 'activity' AND $stattimesql
                       ) inline_view
              GROUP BY ntimeend, courseid, userid";

        $DB->execute($sql);

        stats_progress('5');

        set_config('statslastmonthly', $nextstartmonth);
        $elapsed = time() - $monthstart;
        mtrace(" finished until $nextstartmonth: ".userdate($nextstartmonth) ." (in $elapsed s)");

        $timestart      = $nextstartmonth;
        $nextstartmonth = stats_get_next_month_start($nextstartmonth);
    }

    set_cron_lock('statsrunning', null);
    mtrace("...completed $months months of statistics.");
    return true;
}


function stats_get_start_from($str) {
    global $CFG, $DB;

        if ($timeend = $DB->get_field_sql('SELECT MAX(timeend) FROM {stats_'.$str.'}')) {
        return $timeend;
    }
        switch ($CFG->statsfirstrun) {
        case 'all':
            $manager = get_log_manager();
            $stores = $manager->get_readers();
            $firstlog = false;
            foreach ($stores as $store) {
                if ($store instanceof \core\log\sql_internal_table_reader) {
                    $logtable = $store->get_internal_log_table_name();
                    if (!$logtable) {
                        continue;
                    }
                    $first = $DB->get_field_sql("SELECT MIN(timecreated) FROM {{$logtable}}");
                    if ($first and (!$firstlog or $firstlog > $first)) {
                        $firstlog = $first;
                    }
                }
            }

            $first = $DB->get_field_sql('SELECT MIN(time) FROM {log}');
            if ($first and (!$firstlog or $firstlog > $first)) {
                $firstlog = $first;
            }

            if ($firstlog) {
                return $firstlog;
            }

        default:
            if (is_numeric($CFG->statsfirstrun)) {
                return time() - $CFG->statsfirstrun;
            }
                    case 'none':
            return strtotime('-3 day', time());
    }
}


function stats_get_base_daily($time=0) {
    if (empty($time)) {
        $time = time();
    }

    core_date::set_default_server_timezone();
    $time = strtotime(date('d-M-Y', $time));

    return $time;
}


function stats_get_base_weekly($time=0) {
    global $CFG;

    $time = stats_get_base_daily($time);
    $startday = $CFG->calendar_startwday;

    core_date::set_default_server_timezone();
    $thisday = date('w', $time);

    if ($thisday > $startday) {
        $time = $time - (($thisday - $startday) * 60*60*24);
    } else if ($thisday < $startday) {
        $time = $time - ((7 + $thisday - $startday) * 60*60*24);
    }
    return $time;
}


function stats_get_base_monthly($time=0) {
    if (empty($time)) {
        $time = time();
    }

    core_date::set_default_server_timezone();
    $return = strtotime(date('1-M-Y', $time));

    return $return;
}


function stats_get_next_day_start($time) {
    $next = stats_get_base_daily($time);
    $nextdate = new DateTime();
    $nextdate->setTimestamp($next);
    $nextdate->add(new DateInterval('P1D'));
    return $nextdate->getTimestamp();
}


function stats_get_next_week_start($time) {
    $next = stats_get_base_weekly($time);
    $nextdate = new DateTime();
    $nextdate->setTimestamp($next);
    $nextdate->add(new DateInterval('P1W'));
    return $nextdate->getTimestamp();
}


function stats_get_next_month_start($time) {
    $next = stats_get_base_monthly($time);
    $nextdate = new DateTime();
    $nextdate->setTimestamp($next);
    $nextdate->add(new DateInterval('P1M'));
    return $nextdate->getTimestamp();
}


function stats_clean_old() {
    global $DB;
    mtrace("Running stats cleanup tasks...");
    cron_trace_time_and_memory();
    $deletebefore =  stats_get_base_monthly();

        $deletebefore = strtotime('-3 months', $deletebefore);
    $DB->delete_records_select('stats_daily',      "timeend < $deletebefore");
    $DB->delete_records_select('stats_user_daily', "timeend < $deletebefore");

        $deletebefore = strtotime('-6 months', $deletebefore);
    $DB->delete_records_select('stats_weekly',      "timeend < $deletebefore");
    $DB->delete_records_select('stats_user_weekly', "timeend < $deletebefore");

    
    mtrace("...stats cleanup finished");
}

function stats_get_parameters($time,$report,$courseid,$mode,$roleid=0) {
    global $CFG, $DB;

    $param = new stdClass();
    $param->params = array();

    if ($time < 10) {                 $param->table = 'daily';
        $param->timeafter = strtotime("-".($time*7)." days",stats_get_base_daily());
    } elseif ($time < 20) {                 $param->table = 'weekly';
        $param->timeafter = strtotime("-".(($time - 10)*4)." weeks",stats_get_base_weekly());
    } else {                 $param->table = 'monthly';
        $param->timeafter = strtotime("-".($time - 20)." months",stats_get_base_monthly());
    }

    $param->extras = '';

    switch ($report) {
        case STATS_REPORT_LOGINS:
        $param->fields = 'timeend,sum(stat1) as line1,sum(stat2) as line2';
        $param->fieldscomplete = true;
        $param->stattype = 'logins';
        $param->line1 = get_string('statslogins');
        $param->line2 = get_string('statsuniquelogins');
        if ($courseid == SITEID) {
            $param->extras = 'GROUP BY timeend';
        }
        break;

    case STATS_REPORT_READS:
        $param->fields = $DB->sql_concat('timeend','roleid').' AS uniqueid, timeend, roleid, stat1 as line1';
        $param->fieldscomplete = true;         $param->aggregategroupby = 'roleid';
        $param->stattype = 'activity';
        $param->crosstab = true;
        $param->extras = 'GROUP BY timeend,roleid,stat1';
        if ($courseid == SITEID) {
            $param->fields = $DB->sql_concat('timeend','roleid').' AS uniqueid, timeend, roleid, sum(stat1) as line1';
            $param->extras = 'GROUP BY timeend,roleid';
        }
        break;

    case STATS_REPORT_WRITES:
        $param->fields = $DB->sql_concat('timeend','roleid').' AS uniqueid, timeend, roleid, stat2 as line1';
        $param->fieldscomplete = true;         $param->aggregategroupby = 'roleid';
        $param->stattype = 'activity';
        $param->crosstab = true;
        $param->extras = 'GROUP BY timeend,roleid,stat2';
        if ($courseid == SITEID) {
            $param->fields = $DB->sql_concat('timeend','roleid').' AS uniqueid, timeend, roleid, sum(stat2) as line1';
            $param->extras = 'GROUP BY timeend,roleid';
        }
        break;

    case STATS_REPORT_ACTIVITY:
        $param->fields = $DB->sql_concat('timeend','roleid').' AS uniqueid, timeend, roleid, sum(stat1+stat2) as line1';
        $param->fieldscomplete = true;         $param->aggregategroupby = 'roleid';
        $param->stattype = 'activity';
        $param->crosstab = true;
        $param->extras = 'GROUP BY timeend,roleid';
        if ($courseid == SITEID) {
            $param->extras = 'GROUP BY timeend,roleid';
        }
        break;

    case STATS_REPORT_ACTIVITYBYROLE;
        $param->fields = 'stat1 AS line1, stat2 AS line2';
        $param->stattype = 'activity';
        $rolename = $DB->get_field('role','name', array('id'=>$roleid));
        $param->line1 = $rolename . get_string('statsreads');
        $param->line2 = $rolename . get_string('statswrites');
        if ($courseid == SITEID) {
            $param->extras = 'GROUP BY timeend';
        }
        break;

        case STATS_REPORT_USER_ACTIVITY:
        $param->fields = 'statsreads as line1, statswrites as line2';
        $param->line1 = get_string('statsuserreads');
        $param->line2 = get_string('statsuserwrites');
        $param->stattype = 'activity';
        break;

    case STATS_REPORT_USER_ALLACTIVITY:
        $param->fields = 'statsreads+statswrites as line1';
        $param->line1 = get_string('statsuseractivity');
        $param->stattype = 'activity';
        break;

    case STATS_REPORT_USER_LOGINS:
        $param->fields = 'statsreads as line1';
        $param->line1 = get_string('statsuserlogins');
        $param->stattype = 'logins';
        break;

    case STATS_REPORT_USER_VIEW:
        $param->fields = 'statsreads as line1, statswrites as line2, statsreads+statswrites as line3';
        $param->line1 = get_string('statsuserreads');
        $param->line2 = get_string('statsuserwrites');
        $param->line3 = get_string('statsuseractivity');
        $param->stattype = 'activity';
        break;

        case STATS_REPORT_ACTIVE_COURSES:
        $param->fields = 'sum(stat1+stat2) AS line1';
        $param->stattype = 'activity';
        $param->orderby = 'line1 DESC';
        $param->line1 = get_string('useractivity');
        $param->graphline = 'line1';
        break;

    case STATS_REPORT_ACTIVE_COURSES_WEIGHTED:
        $threshold = 0;
        if (!empty($CFG->statsuserthreshold) && is_numeric($CFG->statsuserthreshold)) {
            $threshold = $CFG->statsuserthreshold;
        }
        $param->fields = '';
        $param->sql = 'SELECT activity.courseid, activity.all_activity AS line1, enrolments.highest_enrolments AS line2,
                        activity.all_activity / enrolments.highest_enrolments as line3
                       FROM (
                            SELECT courseid, sum(stat1+stat2) AS all_activity
                              FROM {stats_'.$param->table.'}
                             WHERE stattype=\'activity\' AND timeend >= '.(int)$param->timeafter.' AND roleid = 0 GROUP BY courseid
                       ) activity
                       INNER JOIN
                            (
                            SELECT courseid, max(stat1) AS highest_enrolments
                              FROM {stats_'.$param->table.'}
                             WHERE stattype=\'enrolments\' AND timeend >= '.(int)$param->timeafter.' AND stat1 > '.(int)$threshold.'
                          GROUP BY courseid
                      ) enrolments
                      ON (activity.courseid = enrolments.courseid)
                      ORDER BY line3 DESC';
        $param->line1 = get_string('useractivity');
        $param->line2 = get_string('users');
        $param->line3 = get_string('activityweighted');
        $param->graphline = 'line3';
        break;

    case STATS_REPORT_PARTICIPATORY_COURSES:
        $threshold = 0;
        if (!empty($CFG->statsuserthreshold) && is_numeric($CFG->statsuserthreshold)) {
            $threshold = $CFG->statsuserthreshold;
        }
        $param->fields = '';
        $param->sql = 'SELECT courseid, ' . $DB->sql_ceil('avg(all_enrolments)') . ' as line1, ' .
                         $DB->sql_ceil('avg(active_enrolments)') . ' as line2, avg(proportion_active) AS line3
                       FROM (
                           SELECT courseid, timeend, stat2 as active_enrolments,
                                  stat1 as all_enrolments, '.$DB->sql_cast_char2real('stat2').'/'.$DB->sql_cast_char2real('stat1').' AS proportion_active
                             FROM {stats_'.$param->table.'}
                            WHERE stattype=\'enrolments\' AND roleid = 0 AND stat1 > '.(int)$threshold.'
                       ) aq
                       WHERE timeend >= '.(int)$param->timeafter.'
                       GROUP BY courseid
                       ORDER BY line3 DESC';

        $param->line1 = get_string('users');
        $param->line2 = get_string('activeusers');
        $param->line3 = get_string('participationratio');
        $param->graphline = 'line3';
        break;

    case STATS_REPORT_PARTICIPATORY_COURSES_RW:
        $param->fields = '';
        $param->sql =  'SELECT courseid, sum(views) AS line1, sum(posts) AS line2,
                           avg(proportion_active) AS line3
                         FROM (
                           SELECT courseid, timeend, stat1 as views, stat2 AS posts,
                                  '.$DB->sql_cast_char2real('stat2').'/'.$DB->sql_cast_char2real('stat1').' as proportion_active
                             FROM {stats_'.$param->table.'}
                            WHERE stattype=\'activity\' AND roleid = 0 AND stat1 > 0
                       ) aq
                       WHERE timeend >= '.(int)$param->timeafter.'
                       GROUP BY courseid
                       ORDER BY line3 DESC';
        $param->line1 = get_string('views');
        $param->line2 = get_string('posts');
        $param->line3 = get_string('participationratio');
        $param->graphline = 'line3';
        break;
    }

    
        return $param;
}

function stats_get_view_actions() {
    return array('view','view all','history');
}

function stats_get_post_actions() {
    return array('add','delete','edit','add mod','delete mod','edit section'.'enrol','loginas','new','unenrol','update','update mod');
}

function stats_get_action_names($str) {
    global $CFG, $DB;

    $mods = $DB->get_records('modules');
    $function = 'stats_get_'.$str.'_actions';
    $actions = $function();
    foreach ($mods as $mod) {
        $file = $CFG->dirroot.'/mod/'.$mod->name.'/lib.php';
        if (!is_readable($file)) {
            continue;
        }
        require_once($file);
        $function = $mod->name.'_get_'.$str.'_actions';
        if (function_exists($function)) {
            $mod_actions = $function();
            if (is_array($mod_actions)) {
                $actions = array_merge($actions, $mod_actions);
            }
        }
    }

            $actions =  array_values(array_unique($actions));
    $c = count($actions);
    for ($n=0;$n<$c;$n++) {
        $actions[$n] = $actions[$n];
    }
    return $actions;
}

function stats_get_time_options($now,$lastweekend,$lastmonthend,$earliestday,$earliestweek,$earliestmonth) {

    $now = stats_get_base_daily(time());
            $now += 60*60*24;

    $timeoptions = array();

    if ($now - (60*60*24*7) >= $earliestday) {
        $timeoptions[STATS_TIME_LASTWEEK] = get_string('numweeks','moodle',1);
    }
    if ($now - (60*60*24*14) >= $earliestday) {
        $timeoptions[STATS_TIME_LAST2WEEKS] = get_string('numweeks','moodle',2);
    }
    if ($now - (60*60*24*21) >= $earliestday) {
        $timeoptions[STATS_TIME_LAST3WEEKS] = get_string('numweeks','moodle',3);
    }
    if ($now - (60*60*24*28) >= $earliestday) {
        $timeoptions[STATS_TIME_LAST4WEEKS] = get_string('numweeks','moodle',4);    }
    if ($lastweekend - (60*60*24*56) >= $earliestweek) {
        $timeoptions[STATS_TIME_LAST2MONTHS] = get_string('nummonths','moodle',2);
    }
    if ($lastweekend - (60*60*24*84) >= $earliestweek) {
        $timeoptions[STATS_TIME_LAST3MONTHS] = get_string('nummonths','moodle',3);
    }
    if ($lastweekend - (60*60*24*112) >= $earliestweek) {
        $timeoptions[STATS_TIME_LAST4MONTHS] = get_string('nummonths','moodle',4);
    }
    if ($lastweekend - (60*60*24*140) >= $earliestweek) {
        $timeoptions[STATS_TIME_LAST5MONTHS] = get_string('nummonths','moodle',5);
    }
    if ($lastweekend - (60*60*24*168) >= $earliestweek) {
        $timeoptions[STATS_TIME_LAST6MONTHS] = get_string('nummonths','moodle',6);     }
    if (strtotime('-7 months',$lastmonthend) >= $earliestmonth) {
        $timeoptions[STATS_TIME_LAST7MONTHS] = get_string('nummonths','moodle',7);
    }
    if (strtotime('-8 months',$lastmonthend) >= $earliestmonth) {
        $timeoptions[STATS_TIME_LAST8MONTHS] = get_string('nummonths','moodle',8);
    }
    if (strtotime('-9 months',$lastmonthend) >= $earliestmonth) {
        $timeoptions[STATS_TIME_LAST9MONTHS] = get_string('nummonths','moodle',9);
    }
    if (strtotime('-10 months',$lastmonthend) >= $earliestmonth) {
        $timeoptions[STATS_TIME_LAST10MONTHS] = get_string('nummonths','moodle',10);
    }
    if (strtotime('-11 months',$lastmonthend) >= $earliestmonth) {
        $timeoptions[STATS_TIME_LAST11MONTHS] = get_string('nummonths','moodle',11);
    }
    if (strtotime('-1 year',$lastmonthend) >= $earliestmonth) {
        $timeoptions[STATS_TIME_LASTYEAR] = get_string('lastyear');
    }

    $years = (int)date('y', $now) - (int)date('y', $earliestmonth);
    if ($years > 1) {
        for($i = 2; $i <= $years; $i++) {
            $timeoptions[$i*12+20] = get_string('numyears', 'moodle', $i);
        }
    }

    return $timeoptions;
}

function stats_get_report_options($courseid,$mode) {
    global $CFG, $DB;

    $reportoptions = array();

    switch ($mode) {
    case STATS_MODE_GENERAL:
        $reportoptions[STATS_REPORT_ACTIVITY] = get_string('statsreport'.STATS_REPORT_ACTIVITY);
        if ($courseid != SITEID && $context = context_course::instance($courseid)) {
            $sql = 'SELECT r.id, r.name FROM {role} r JOIN {stats_daily} s ON s.roleid = r.id WHERE s.courseid = :courseid GROUP BY r.id, r.name';
            if ($roles = $DB->get_records_sql($sql, array('courseid' => $courseid))) {
                foreach ($roles as $role) {
                    $reportoptions[STATS_REPORT_ACTIVITYBYROLE.$role->id] = get_string('statsreport'.STATS_REPORT_ACTIVITYBYROLE). ' '.$role->name;
                }
            }
        }
        $reportoptions[STATS_REPORT_READS] = get_string('statsreport'.STATS_REPORT_READS);
        $reportoptions[STATS_REPORT_WRITES] = get_string('statsreport'.STATS_REPORT_WRITES);
        if ($courseid == SITEID) {
            $reportoptions[STATS_REPORT_LOGINS] = get_string('statsreport'.STATS_REPORT_LOGINS);
        }

        break;
    case STATS_MODE_DETAILED:
        $reportoptions[STATS_REPORT_USER_ACTIVITY] = get_string('statsreport'.STATS_REPORT_USER_ACTIVITY);
        $reportoptions[STATS_REPORT_USER_ALLACTIVITY] = get_string('statsreport'.STATS_REPORT_USER_ALLACTIVITY);
        if (has_capability('report/stats:view', context_system::instance())) {
            $site = get_site();
            $reportoptions[STATS_REPORT_USER_LOGINS] = get_string('statsreport'.STATS_REPORT_USER_LOGINS);
        }
        break;
    case STATS_MODE_RANKED:
        if (has_capability('report/stats:view', context_system::instance())) {
            $reportoptions[STATS_REPORT_ACTIVE_COURSES] = get_string('statsreport'.STATS_REPORT_ACTIVE_COURSES);
            $reportoptions[STATS_REPORT_ACTIVE_COURSES_WEIGHTED] = get_string('statsreport'.STATS_REPORT_ACTIVE_COURSES_WEIGHTED);
            $reportoptions[STATS_REPORT_PARTICIPATORY_COURSES] = get_string('statsreport'.STATS_REPORT_PARTICIPATORY_COURSES);
            $reportoptions[STATS_REPORT_PARTICIPATORY_COURSES_RW] = get_string('statsreport'.STATS_REPORT_PARTICIPATORY_COURSES_RW);
        }
        break;
    }

    return $reportoptions;
}


function stats_fix_zeros($stats,$timeafter,$timestr,$line2=true,$line3=false) {

    if (empty($stats)) {
        return;
    }

    $timestr = str_replace('user_','',$timestr); 
        $fun = 'stats_get_base_'.$timestr;
    $now = $fun();

        $actualtimes = array();
    $actualtimeshour = null;
    foreach ($stats as $statid => $s) {
                                if ($timestr == 'monthly') {
            $day = date('d', $s->timeend);
            if (date('d', $s->timeend) > 1 && date('d', $s->timeend) < 29) {
                $day = 1;
            }
            if (is_null($actualtimeshour)) {
                $actualtimeshour = date('H', $s->timeend);
            }
            $s->timeend = mktime($actualtimeshour, 0, 0, date('m', $s->timeend), $day, date('Y', $s->timeend));
        }
        $stats[$statid] = $s;
        $actualtimes[] = $s->timeend;
    }

    $actualtimesvalues = array_values($actualtimes);
    $timeafter = array_pop($actualtimesvalues);

        $times = array();
    while ($timeafter < $now) {
        $times[] = $timeafter;
        if ($timestr == 'daily') {
            $timeafter = stats_get_next_day_start($timeafter);
        } else if ($timestr == 'weekly') {
            $timeafter = stats_get_next_week_start($timeafter);
        } else if ($timestr == 'monthly') {
                        $year = date('Y', $timeafter);
            $month = date('m', $timeafter);
            $day = date('d', $timeafter);
            $dayofnextmonth = $day;
            if ($day >= 29) {
                $daysinmonth = date('n', mktime(0, 0, 0, $month+1, 1, $year));
                if ($day > $daysinmonth) {
                    $dayofnextmonth = $daysinmonth;
                }
            }
            $timeafter = mktime($actualtimeshour, 0, 0, $month+1, $dayofnextmonth, $year);
        } else {
                        return $stats;
        }
    }

        foreach ($times as $count => $time) {
        if (!in_array($time,$actualtimes) && $count != count($times) -1) {
            $newobj = new StdClass;
            $newobj->timeend = $time;
            $newobj->id = 0;
            $newobj->roleid = 0;
            $newobj->line1 = 0;
            if (!empty($line2)) {
                $newobj->line2 = 0;
            }
            if (!empty($line3)) {
                $newobj->line3 = 0;
            }
            $newobj->zerofixed = true;
            $stats[] = $newobj;
        }
    }

    usort($stats,"stats_compare_times");
    return $stats;
}

function stats_compare_times($a,$b) {
   if ($a->timeend == $b->timeend) {
       return 0;
   }
   return ($a->timeend > $b->timeend) ? -1 : 1;
}

function stats_check_uptodate($courseid=0) {
    global $CFG, $DB;

    if (empty($courseid)) {
        $courseid = SITEID;
    }

    $latestday = stats_get_start_from('daily');

    if ((time() - 60*60*24*2) < $latestday) {         return NULL;
    }

    $a = new stdClass();
    $a->daysdone = $DB->get_field_sql("SELECT COUNT(DISTINCT(timeend)) FROM {stats_daily}");

        $a->dayspending = ceil((stats_get_base_daily() - $latestday)/(60*60*24));

    if ($a->dayspending == 0 && $a->daysdone != 0) {
        return NULL;     }

        return get_string('statscatchupmode','error',$a);
}


function stats_temp_table_create() {
    global $CFG, $DB;

    $dbman = $DB->get_manager(); 
    stats_temp_table_drop();

    $tables = array();

        $table = new xmldb_table('temp_stats_daily');
    $table->add_field('id', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, XMLDB_SEQUENCE, null);
    $table->add_field('courseid', XMLDB_TYPE_INTEGER, 10, null, XMLDB_NOTNULL, null, '0');
    $table->add_field('timeend', XMLDB_TYPE_INTEGER, 10, null, XMLDB_NOTNULL, null, '0');
    $table->add_field('roleid', XMLDB_TYPE_INTEGER, 10, null, XMLDB_NOTNULL, null, '0');
    $table->add_field('stattype', XMLDB_TYPE_CHAR, 20, null, XMLDB_NOTNULL, null, 'activity');
    $table->add_field('stat1', XMLDB_TYPE_INTEGER, 10, null, XMLDB_NOTNULL, null, '0');
    $table->add_field('stat2', XMLDB_TYPE_INTEGER, 10, null, XMLDB_NOTNULL, null, '0');
    $table->add_key('primary', XMLDB_KEY_PRIMARY, array('id'));
    $table->add_index('courseid', XMLDB_INDEX_NOTUNIQUE, array('courseid'));
    $table->add_index('timeend', XMLDB_INDEX_NOTUNIQUE, array('timeend'));
    $table->add_index('roleid', XMLDB_INDEX_NOTUNIQUE, array('roleid'));
    $tables['temp_stats_daily'] = $table;

    $table = new xmldb_table('temp_stats_user_daily');
    $table->add_field('id', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, XMLDB_SEQUENCE, null);
    $table->add_field('courseid', XMLDB_TYPE_INTEGER, 10, null, XMLDB_NOTNULL, null, '0');
    $table->add_field('userid', XMLDB_TYPE_INTEGER, 10, null, XMLDB_NOTNULL, null, '0');
    $table->add_field('roleid', XMLDB_TYPE_INTEGER, 10, null, XMLDB_NOTNULL, null, '0');
    $table->add_field('timeend', XMLDB_TYPE_INTEGER, 10, null, XMLDB_NOTNULL, null, '0');
    $table->add_field('statsreads', XMLDB_TYPE_INTEGER, 10, null, XMLDB_NOTNULL, null, '0');
    $table->add_field('statswrites', XMLDB_TYPE_INTEGER, 10, null, XMLDB_NOTNULL, null, '0');
    $table->add_field('stattype', XMLDB_TYPE_CHAR, 30, null, XMLDB_NOTNULL, null, null);
    $table->add_key('primary', XMLDB_KEY_PRIMARY, array('id'));
    $table->add_index('courseid', XMLDB_INDEX_NOTUNIQUE, array('courseid'));
    $table->add_index('userid', XMLDB_INDEX_NOTUNIQUE, array('userid'));
    $table->add_index('timeend', XMLDB_INDEX_NOTUNIQUE, array('timeend'));
    $table->add_index('roleid', XMLDB_INDEX_NOTUNIQUE, array('roleid'));
    $tables['temp_stats_user_daily'] = $table;

    $table = new xmldb_table('temp_enroled');
    $table->add_field('id', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, XMLDB_SEQUENCE, null);
    $table->add_field('userid', XMLDB_TYPE_INTEGER, 10, null, XMLDB_NOTNULL, null, '0');
    $table->add_field('courseid', XMLDB_TYPE_INTEGER, 10, null, XMLDB_NOTNULL, null, '0');
    $table->add_field('roleid', XMLDB_TYPE_INTEGER, 10, null, XMLDB_NOTNULL, null, null);
    $table->add_key('primary', XMLDB_KEY_PRIMARY, array('id'));
    $table->add_index('userid', XMLDB_INDEX_NOTUNIQUE, array('userid'));
    $table->add_index('courseid', XMLDB_INDEX_NOTUNIQUE, array('courseid'));
    $table->add_index('roleid', XMLDB_INDEX_NOTUNIQUE, array('roleid'));
    $tables['temp_enroled'] = $table;


    $table = new xmldb_table('temp_log1');
    $table->add_field('id', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, XMLDB_SEQUENCE, null);
    $table->add_field('userid', XMLDB_TYPE_INTEGER, 10, null, XMLDB_NOTNULL, null, '0');
    $table->add_field('course', XMLDB_TYPE_INTEGER, 10, null, XMLDB_NOTNULL, null, '0');
    $table->add_field('action', XMLDB_TYPE_CHAR, 40, null, XMLDB_NOTNULL, null, null);
    $table->add_key('primary', XMLDB_KEY_PRIMARY, array('id'));
    $table->add_index('action', XMLDB_INDEX_NOTUNIQUE, array('action'));
    $table->add_index('course', XMLDB_INDEX_NOTUNIQUE, array('course'));
    $table->add_index('user', XMLDB_INDEX_NOTUNIQUE, array('userid'));
    $table->add_index('usercourseaction', XMLDB_INDEX_NOTUNIQUE, array('userid','course','action'));
    $tables['temp_log1'] = $table;

        $tables['temp_log2'] = clone $tables['temp_log1'];
    $tables['temp_log2']->setName('temp_log2');

    try {

        foreach ($tables as $table) {
            $dbman->create_temp_table($table);
        }

    } catch (Exception $e) {
        mtrace('Temporary table creation failed: '. $e->getMessage());
        return false;
    }

    return true;
}


function stats_temp_table_drop() {
    global $DB;

    $dbman = $DB->get_manager();

    $tables = array('temp_log1', 'temp_log2', 'temp_stats_daily', 'temp_stats_user_daily', 'temp_enroled');

    foreach ($tables as $name) {

        if ($dbman->table_exists($name)) {
            $table = new xmldb_table($name);

            try {
                $dbman->drop_table($table);
            } catch (Exception $e) {
                mtrace("Error occured while dropping temporary tables!");
            }
        }
    }
}


function stats_temp_table_setup() {
    global $DB;

    $sql = "INSERT INTO {temp_enroled} (userid, courseid, roleid)

               SELECT ue.userid, e.courseid, ra.roleid
                FROM {role_assignments} ra
                JOIN {context} c ON (c.id = ra.contextid AND c.contextlevel = :courselevel)
                JOIN {enrol} e ON e.courseid = c.instanceid
                JOIN {user_enrolments} ue ON (ue.enrolid = e.id AND ue.userid = ra.userid)";

    return stats_run_query($sql, array('courselevel' => CONTEXT_COURSE));
}


function stats_temp_table_fill($timestart, $timeend) {
    global $DB;

    
    $params = array('timestart' => $timestart,
                    'timeend' => $timeend,
                    'participating' => \core\event\base::LEVEL_PARTICIPATING,
                    'teaching' => \core\event\base::LEVEL_TEACHING,
                    'loginevent1' => '\core\event\user_loggedin',
                    'loginevent2' => '\core\event\user_loggedin',
    );

    $filled = false;
    $manager = get_log_manager();
    $stores = $manager->get_readers();
    foreach ($stores as $store) {
        if ($store instanceof \core\log\sql_internal_table_reader) {
            $logtable = $store->get_internal_log_table_name();
            if (!$logtable) {
                continue;
            }

            $sql = "SELECT COUNT('x')
                      FROM {{$logtable}}
                     WHERE timecreated >= :timestart AND timecreated < :timeend";

            if (!$DB->get_field_sql($sql, $params)) {
                continue;
            }

                                    
            $sql = "INSERT INTO {temp_log1} (userid, course, action)

            SELECT userid,
                   CASE
                      WHEN courseid IS NULL THEN ".SITEID."
                      WHEN courseid = 0 THEN ".SITEID."
                      ELSE courseid
                   END,
                   CASE
                       WHEN eventname = :loginevent1 THEN 'login'
                       WHEN crud = 'r' THEN 'view'
                       ELSE 'update'
                   END
              FROM {{$logtable}}
             WHERE timecreated >= :timestart AND timecreated < :timeend
                   AND (origin = 'web' OR origin = 'ws')
                   AND (edulevel = :participating OR edulevel = :teaching OR eventname = :loginevent2)";

            $DB->execute($sql, $params);
            $filled = true;
        }
    }

    if (!$filled) {
                $sql = "INSERT INTO {temp_log1} (userid, course, action)

            SELECT userid, course, action
              FROM {log}
             WHERE time >= :timestart AND time < :timeend";

        $DB->execute($sql, $params);
    }

    $sql = 'INSERT INTO {temp_log2} (userid, course, action)

            SELECT userid, course, action FROM {temp_log1}';

    $DB->execute($sql);

        $DB->update_temp_table_stats();

    return true;
}



function stats_temp_table_clean() {
    global $DB;

    $sql = array();

    $sql['up1'] = 'INSERT INTO {stats_daily} (courseid, roleid, stattype, timeend, stat1, stat2)

                   SELECT courseid, roleid, stattype, timeend, stat1, stat2 FROM {temp_stats_daily}';

    $sql['up2'] = 'INSERT INTO {stats_user_daily}
                               (courseid, userid, roleid, timeend, statsreads, statswrites, stattype)

                   SELECT courseid, userid, roleid, timeend, statsreads, statswrites, stattype
                     FROM {temp_stats_user_daily}';

    foreach ($sql as $id => $query) {
        if (! stats_run_query($query)) {
            mtrace("Error during table cleanup!");
            return false;
        }
    }

    $tables = array('temp_log1', 'temp_log2', 'temp_stats_daily', 'temp_stats_user_daily');

    foreach ($tables as $name) {
        $DB->delete_records($name);
    }

    return true;
}
