<?php



namespace logstore_legacy\log;

defined('MOODLE_INTERNAL') || die();

class store implements \tool_log\log\store, \core\log\sql_reader {
    use \tool_log\helper\store,
        \tool_log\helper\reader;

    public function __construct(\tool_log\log\manager $manager) {
        $this->helper_setup($manager);
    }

    
    protected static $standardtolegacyfields = array(
        'timecreated'       => 'time',
        'courseid'          => 'course',
        'contextinstanceid' => 'cmid',
        'origin'            => 'ip',
        'anonymous'         => 0,
    );

    
    const CRUD_REGEX = "/(crud).*?(<>|=|!=).*?'(.*?)'/s";

    
    protected static function replace_sql_legacy($selectwhere, array $params, $sort = '') {
                if ($selectwhere == "userid = :userid AND courseid = :courseid AND eventname = :eventname AND timecreated > :since" and
                empty($sort)) {
            $replace = "module = 'course' AND action = 'new' AND userid = :userid AND url = :url AND time > :since";
            $params += array('url' => "view.php?id={$params['courseid']}");
            return array($replace, $params, $sort);
        }

                foreach (self::$standardtolegacyfields as $from => $to) {
            $selectwhere = str_replace($from, $to, $selectwhere);
            if (!empty($sort)) {
                $sort = str_replace($from, $to, $sort);
            }
            if (isset($params[$from])) {
                $params[$to] = $params[$from];
                unset($params[$from]);
            }
        }

                $selectwhere = preg_replace_callback("/(crud).*?(<>|=|!=).*?'(.*?)'/s", 'self::replace_crud', $selectwhere);

        return array($selectwhere, $params, $sort);
    }

    public function get_events_select($selectwhere, array $params, $sort, $limitfrom, $limitnum) {
        global $DB;

        $sort = self::tweak_sort_by_id($sort);

                list($selectwhere, $params, $sort) = self::replace_sql_legacy($selectwhere, $params, $sort);

        $records = array();

        try {
                        $records = $DB->get_recordset_select('log', $selectwhere, $params, $sort, '*', $limitfrom, $limitnum);
        } catch (\moodle_exception $ex) {
            debugging("error converting legacy event data " . $ex->getMessage() . $ex->debuginfo, DEBUG_DEVELOPER);
            return array();
        }

        $events = array();

        foreach ($records as $data) {
            $events[$data->id] = $this->get_log_event($data);
        }

        $records->close();

        return $events;
    }

    
    public function get_events_select_iterator($selectwhere, array $params, $sort, $limitfrom, $limitnum) {
        global $DB;

        $sort = self::tweak_sort_by_id($sort);

                list($selectwhere, $params, $sort) = self::replace_sql_legacy($selectwhere, $params, $sort);

        try {
            $recordset = $DB->get_recordset_select('log', $selectwhere, $params, $sort, '*', $limitfrom, $limitnum);
        } catch (\moodle_exception $ex) {
            debugging("error converting legacy event data " . $ex->getMessage() . $ex->debuginfo, DEBUG_DEVELOPER);
            return new \EmptyIterator;
        }

        return new \core\dml\recordset_walk($recordset, array($this, 'get_log_event'));
    }

    
    public function get_log_event($data) {
        return \logstore_legacy\event\legacy_logged::restore_legacy($data);
    }

    public function get_events_select_count($selectwhere, array $params) {
        global $DB;

                list($selectwhere, $params) = self::replace_sql_legacy($selectwhere, $params);

        try {
            return $DB->count_records_select('log', $selectwhere, $params);
        } catch (\moodle_exception $ex) {
            debugging("error converting legacy event data " . $ex->getMessage() . $ex->debuginfo, DEBUG_DEVELOPER);
            return 0;
        }
    }

    
    public function is_logging() {
        return (bool)$this->get_config('loglegacy', true);
    }

    public function dispose() {
    }

    
    public function legacy_add_to_log($courseid, $module, $action, $url, $info, $cm, $user, $ip = null, $time = null) {
                                global $DB, $CFG, $USER;
        if (!$this->is_logging()) {
            return;
        }

        if ($cm === '' || is_null($cm)) {             $cm = 0;
        }

        if ($user) {
            $userid = $user;
        } else {
            if (\core\session\manager::is_loggedinas()) {                 return;
            }
            $userid = empty($USER->id) ? '0' : $USER->id;
        }

        if (isset($CFG->logguests) and !$CFG->logguests) {
            if (!$userid or isguestuser($userid)) {
                return;
            }
        }

        $remoteaddr = (is_null($ip)) ? getremoteaddr() : $ip;

        $timenow = (is_null($time)) ? time() : $time;
        if (!empty($url)) {             $url = html_entity_decode($url, ENT_QUOTES, 'UTF-8');
        } else {
            $url = '';
        }

                                        if (\core_text::strlen($action) > 40) {
            $action = \core_text::substr($action, 0, 37) . '...';
            debugging('Warning: logged very long action', DEBUG_DEVELOPER);
        }

        if (!empty($info) && \core_text::strlen($info) > 255) {
            $info = \core_text::substr($info, 0, 252) . '...';
            debugging('Warning: logged very long info', DEBUG_DEVELOPER);
        }

                if (!empty($url) && \core_text::strlen($url) > 100) {
            $url = \core_text::substr($url, 0, 97) . '...';
            debugging('Warning: logged very long URL', DEBUG_DEVELOPER);
        }

        if (defined('MDL_PERFDB')) {
            global $PERF;
            $PERF->logwrites++;
        };

        $log = array('time' => $timenow, 'userid' => $userid, 'course' => $courseid, 'ip' => $remoteaddr,
                     'module' => $module, 'cmid' => $cm, 'action' => $action, 'url' => $url, 'info' => $info);

        try {
            $DB->insert_record_raw('log', $log, false);
        } catch (\dml_exception $e) {
            debugging('Error: Could not insert a new entry to the Moodle log. ' . $e->errorcode, DEBUG_ALL);

                        if ($CFG->supportemail and empty($CFG->noemailever)) {
                                                $site = get_site();
                $subject = 'Insert into log failed at your moodle site ' . $site->fullname;
                $message = "Insert into log table failed at " . date('l dS \of F Y h:i:s A') .
                    ".\n It is possible that your disk is full.\n\n";
                $message .= "The failed query parameters are:\n\n" . var_export($log, true);

                $lasttime = get_config('admin', 'lastloginserterrormail');
                if (empty($lasttime) || time() - $lasttime > 60 * 60 * 24) {                                         mail($CFG->supportemail, $subject, $message);
                    set_config('lastloginserterrormail', time(), 'admin');
                }
            }
        }
    }

    
    protected static function replace_crud($match) {
        $return = '';
        unset($match[0]);         foreach ($match as $m) {
                        switch ($m) {
                case 'crud' :
                    $replace = 'action';
                    break;
                case 'c' :
                    switch ($match[2]) {
                        case '=' :
                            $replace = " LIKE '%add%'";
                            break;
                        case '!=' :
                        case '<>' :
                            $replace = " NOT LIKE '%add%'";
                            break;
                        default:
                            $replace = '';
                    }
                    break;
                case 'r' :
                    switch ($match[2]) {
                        case '=' :
                            $replace = " LIKE '%view%' OR action LIKE '%report%'";
                            break;
                        case '!=' :
                        case '<>' :
                            $replace = " NOT LIKE '%view%' AND action NOT LIKE '%report%'";
                            break;
                        default:
                            $replace = '';
                    }
                    break;
                case 'u' :
                    switch ($match[2]) {
                        case '=' :
                            $replace = " LIKE '%update%'";
                            break;
                        case '!=' :
                        case '<>' :
                            $replace = " NOT LIKE '%update%'";
                            break;
                        default:
                            $replace = '';
                    }
                    break;
                case 'd' :
                    switch ($match[2]) {
                        case '=' :
                            $replace = " LIKE '%delete%'";
                            break;
                        case '!=' :
                        case '<>' :
                            $replace = " NOT LIKE '%delete%'";
                            break;
                        default:
                            $replace = '';
                    }
                    break;
                default :
                    $replace = '';
            }
            $return .= $replace;
        }
        return $return;
    }
}
