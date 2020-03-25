<?php


namespace core\task;


class send_failed_login_notifications_task extends scheduled_task {

    
    const NOTIFY_MAXIMUM_TIME = 2592000;

    
    public function get_name() {
        return get_string('tasksendfailedloginnotifications', 'admin');
    }

    
    public function execute() {
        global $CFG, $DB;

        if (empty($CFG->notifyloginfailures)) {
            return;
        }

        $recip = get_users_from_config($CFG->notifyloginfailures, 'moodle/site:config');

                $maximumlastnotifytime = time() - self::NOTIFY_MAXIMUM_TIME;
        if (empty($CFG->lastnotifyfailure) || ($CFG->lastnotifyfailure < $maximumlastnotifytime)) {
            $CFG->lastnotifyfailure = $maximumlastnotifytime;
        }

                if (((time() - HOURSECS) < $CFG->lastnotifyfailure) || !is_array($recip) || count($recip) <= 0) {
            return;
        }

                if (empty($CFG->notifyloginthreshold)) {
            $CFG->notifyloginthreshold = 10;         }

                        $logmang = get_log_manager();
        $readers = $logmang->get_readers('\core\log\sql_internal_table_reader');
        $reader = reset($readers);
        $readername = key($readers);
        if (empty($reader) || empty($readername)) {
                        return true;
        }
        $logtable = $reader->get_internal_log_table_name();

        $sql = "SELECT ip, COUNT(*)
                  FROM {" . $logtable . "}
                 WHERE eventname = ?
                       AND timecreated > ?
               GROUP BY ip
                 HAVING COUNT(*) >= ?";
        $params = array('\core\event\user_login_failed', $CFG->lastnotifyfailure, $CFG->notifyloginthreshold);
        $rs = $DB->get_recordset_sql($sql, $params);
        foreach ($rs as $iprec) {
            if (!empty($iprec->ip)) {
                set_cache_flag('login_failure_by_ip', $iprec->ip, '1', 0);
            }
        }
        $rs->close();

                        $sql = "SELECT userid, count(*)
                  FROM {" . $logtable . "}
                 WHERE eventname = ?
                       AND timecreated > ?
              GROUP BY userid
                HAVING count(*) >= ?";
        $params = array('\core\event\user_login_failed', $CFG->lastnotifyfailure, $CFG->notifyloginthreshold);
        $rs = $DB->get_recordset_sql($sql, $params);
        foreach ($rs as $inforec) {
            if (!empty($inforec->info)) {
                set_cache_flag('login_failure_by_id', $inforec->userid, '1', 0);
            }
        }
        $rs->close();

                        $namefields = get_all_user_name_fields(true, 'u');
        $sql = "SELECT * FROM (
                        SELECT l.*, u.username, $namefields
                          FROM {" . $logtable . "} l
                          JOIN {cache_flags} cf ON l.ip = cf.name
                     LEFT JOIN {user} u         ON l.userid = u.id
                         WHERE l.eventname = ?
                               AND l.timecreated > ?
                               AND cf.flagtype = 'login_failure_by_ip'
                    UNION ALL
                        SELECT l.*, u.username, $namefields
                          FROM {" . $logtable . "} l
                          JOIN {cache_flags} cf ON l.userid = " . $DB->sql_cast_char2int('cf.name') . "
                     LEFT JOIN {user} u         ON l.userid = u.id
                         WHERE l.eventname = ?
                               AND l.timecreated > ?
                               AND cf.flagtype = 'login_failure_by_info') t
             ORDER BY t.timecreated DESC";
        $params = array('\core\event\user_login_failed', $CFG->lastnotifyfailure, '\core\event\user_login_failed', $CFG->lastnotifyfailure);

                $count = 0;
        $messages = '';
                $rs = $DB->get_recordset_sql($sql, $params);
        foreach ($rs as $log) {
            $a = new \stdClass();
            $a->time = userdate($log->timecreated);
            if (empty($log->username)) {
                                $other = unserialize($log->other);
                $a->info = empty($other['username']) ? '' : $other['username'];
                $a->name = get_string('unknownuser');
            } else {
                $a->info = $log->username;
                $a->name = fullname($log);
            }
            $a->ip = $log->ip;
            $messages .= get_string('notifyloginfailuresmessage', '', $a)."\n";
            $count++;
        }
        $rs->close();

                if ($count > 0) {
            $site = get_site();
            $subject = get_string('notifyloginfailuressubject', '', format_string($site->fullname));
                        $params = array('id' => 0, 'modid' => 'site_errors', 'chooselog' => '1', 'logreader' => $readername);
            $url = new \moodle_url('/report/log/index.php', $params);
            $body = get_string('notifyloginfailuresmessagestart', '', $CFG->wwwroot) .
                    (($CFG->lastnotifyfailure != 0) ? '('.userdate($CFG->lastnotifyfailure).')' : '')."\n\n" .
                    $messages .
                    "\n\n".get_string('notifyloginfailuresmessageend', '',  $url->out(false).' ')."\n\n";

                        mtrace('Emailing admins about '. $count .' failed login attempts');
            foreach ($recip as $admin) {
                                email_to_user($admin, \core_user::get_support_user(), $subject, $body);
            }
        }

                set_config('lastnotifyfailure', time());

                $DB->delete_records_select('cache_flags', "flagtype IN ('login_failure_by_ip', 'login_failure_by_info')");

    }
}
