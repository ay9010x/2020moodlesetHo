<?php



defined('MOODLE_INTERNAL') || die;


class report_log_table_log extends table_sql {

    
    private $userfullnames = array();

    
    private $contextname = array();

    
    private $filterparams;

    
    public function __construct($uniqueid, $filterparams = null) {
        parent::__construct($uniqueid);

        $this->set_attribute('class', 'reportlog generaltable generalbox');
        $this->filterparams = $filterparams;
                $cols = array();
        $headers = array();
        if (empty($filterparams->courseid)) {
            $cols = array('course');
            $headers = array(get_string('course'));
        }

        $this->define_columns(array_merge($cols, array('time', 'fullnameuser', 'relatedfullnameuser', 'context', 'component',
                'eventname', 'description', 'origin', 'ip')));
        $this->define_headers(array_merge($headers, array(
                get_string('time'),
                get_string('fullnameuser'),
                get_string('eventrelatedfullnameuser', 'report_log'),
                get_string('eventcontext', 'report_log'),
                get_string('eventcomponent', 'report_log'),
                get_string('eventname'),
                get_string('description'),
                get_string('eventorigin', 'report_log'),
                get_string('ip_address')
                )
            ));
        $this->collapsible(false);
        $this->sortable(false);
        $this->pageable(true);
    }

    
    public function col_course($event) {
        throw new coding_exception('col_course() can not be used any more, there is no such column.');
    }

    
    protected function get_user_fullname($userid) {
        global $DB;

        if (empty($userid)) {
            return false;
        }

        if (!empty($this->userfullnames[$userid])) {
            return $this->userfullnames[$userid];
        }

                if ($this->userfullnames[$userid] === false) {
            return false;
        }

                list($usql, $uparams) = $DB->get_in_or_equal($userid);
        $sql = "SELECT id," . get_all_user_name_fields(true) . " FROM {user} WHERE id " . $usql;
        if (!$user = $DB->get_records_sql($sql, $uparams)) {
            return false;
        }

        $this->userfullnames[$userid] = fullname($user);
        return $this->userfullnames[$userid];
    }

    
    public function col_time($event) {

        if (empty($this->download)) {
            $dateformat = get_string('strftimerecent', 'core_langconfig');
        } else {
            $dateformat = get_string('strftimedatetimeshort', 'core_langconfig');
        }
        return userdate($event->timecreated, $dateformat);
    }

    
    public function col_fullnameuser($event) {
                $logextra = $event->get_logextra();

                if (!empty($logextra['realuserid'])) {
            $a = new stdClass();
            if (!$a->realusername = $this->get_user_fullname($logextra['realuserid'])) {
                $a->realusername = '-';
            }
            if (!$a->asusername = $this->get_user_fullname($event->userid)) {
                $a->asusername = '-';
            }
            if (empty($this->download)) {
                $params = array('id' => $logextra['realuserid']);
                if ($event->courseid) {
                    $params['course'] = $event->courseid;
                }
                $a->realusername = html_writer::link(new moodle_url('/user/view.php', $params), $a->realusername);
                $params['id'] = $event->userid;
                $a->asusername = html_writer::link(new moodle_url('/user/view.php', $params), $a->asusername);
            }
            $username = get_string('eventloggedas', 'report_log', $a);

        } else if (!empty($event->userid) && $username = $this->get_user_fullname($event->userid)) {
            if (empty($this->download)) {
                $params = array('id' => $event->userid);
                if ($event->courseid) {
                    $params['course'] = $event->courseid;
                }
                $username = html_writer::link(new moodle_url('/user/view.php', $params), $username);
            }
        } else {
            $username = '-';
        }
        return $username;
    }

    
    public function col_relatedfullnameuser($event) {
                if (!empty($event->relateduserid) && $username = $this->get_user_fullname($event->relateduserid)) {
            if (empty($this->download)) {
                $params = array('id' => $event->relateduserid);
                if ($event->courseid) {
                    $params['course'] = $event->courseid;
                }
                $username = html_writer::link(new moodle_url('/user/view.php', $params), $username);
            }
        } else {
            $username = '-';
        }
        return $username;
    }

    
    public function col_context($event) {
                if ($event->contextid) {
                        if (isset($this->contextname[$event->contextid])) {
                return $this->contextname[$event->contextid];
            } else {
                $context = context::instance_by_id($event->contextid, IGNORE_MISSING);
                if ($context) {
                    $contextname = $context->get_context_name(true);
                    if (empty($this->download) && $url = $context->get_url()) {
                        $contextname = html_writer::link($url, $contextname);
                    }
                } else {
                    $contextname = get_string('other');
                }
            }
        } else {
            $contextname = get_string('other');
        }

        $this->contextname[$event->contextid] = $contextname;
        return $contextname;
    }

    
    public function col_component($event) {
                $componentname = $event->component;
        if (($event->component === 'core') || ($event->component === 'legacy')) {
            return  get_string('coresystem');
        } else if (get_string_manager()->string_exists('pluginname', $event->component)) {
            return get_string('pluginname', $event->component);
        } else {
            return $componentname;
        }
    }

    
    public function col_eventname($event) {
                if ($this->filterparams->logreader instanceof logstore_legacy\log\store) {
                        $eventname = $event->eventname;
        } else {
            $eventname = $event->get_name();
        }
                if (($url = $event->get_url()) && empty($this->download)) {
            $eventname = $this->action_link($url, $eventname, 'action');
        }
        return $eventname;
    }

    
    public function col_description($event) {
                return $event->get_description();
    }

    
    public function col_origin($event) {
                $logextra = $event->get_logextra();

                return $logextra['origin'];
    }

    
    public function col_ip($event) {
                $logextra = $event->get_logextra();
        $ip = $logextra['ip'];

        if (empty($this->download)) {
            $url = new moodle_url("/iplookup/index.php?ip={$ip}&user={$event->userid}");
            $ip = $this->action_link($url, $ip, 'ip');
        }
        return $ip;
    }

    
    protected function action_link(moodle_url $url, $text, $name = 'popup') {
        global $OUTPUT;
        $link = new action_link($url, $text, new popup_action('click', $url, $name, array('height' => 440, 'width' => 700)));
        return $OUTPUT->render($link);
    }

    
    public function get_legacy_crud_action($crud) {
        $legacyactionmap = array('c' => 'add', 'r' => 'view', 'u' => 'update', 'd' => 'delete');
        if (array_key_exists($crud, $legacyactionmap)) {
            return $legacyactionmap[$crud];
        } else {
                        return '-view';
        }
    }

    
    public function get_action_sql() {
        global $DB;

                if ($this->filterparams->logreader instanceof logstore_legacy\log\store) {
            $action = $this->get_legacy_crud_action($this->filterparams->action);
            $firstletter = substr($action, 0, 1);
            if ($firstletter == '-') {
                $sql = $DB->sql_like('action', ':action', false, true, true);
                $params['action'] = '%'.substr($action, 1).'%';
            } else {
                $sql = $DB->sql_like('action', ':action', false);
                $params['action'] = '%'.$action.'%';
            }
        } else if (!empty($this->filterparams->action)) {
             list($sql, $params) = $DB->get_in_or_equal(str_split($this->filterparams->action),
                    SQL_PARAMS_NAMED, 'crud');
            $sql = "crud " . $sql;
        } else {
                        list($sql, $params) = $DB->get_in_or_equal(array('c', 'r', 'u', 'd'),
                    SQL_PARAMS_NAMED, 'crud');
            $sql = "crud ".$sql;
        }
        return array($sql, $params);
    }

    
    public function get_cm_sql() {
        $joins = array();
        $params = array();

        if ($this->filterparams->logreader instanceof logstore_legacy\log\store) {
                        $joins[] = "cmid = :cmid";
            $params['cmid'] = $this->filterparams->modid;
        } else {
            $joins[] = "contextinstanceid = :contextinstanceid";
            $joins[] = "contextlevel = :contextmodule";
            $params['contextinstanceid'] = $this->filterparams->modid;
            $params['contextmodule'] = CONTEXT_MODULE;
        }

        $sql = implode(' AND ', $joins);
        return array($sql, $params);
    }

    
    public function query_db($pagesize, $useinitialsbar = true) {
        global $DB;

        $joins = array();
        $params = array();

                $useextendeddbindex = !($this->filterparams->logreader instanceof logstore_legacy\log\store)
                && !empty($this->filterparams->userid) && !empty($this->filterparams->modid);

        $groupid = 0;
        if (!empty($this->filterparams->courseid) && $this->filterparams->courseid != SITEID) {
            if (!empty($this->filterparams->groupid)) {
                $groupid = $this->filterparams->groupid;
            }

            $joins[] = "courseid = :courseid";
            $params['courseid'] = $this->filterparams->courseid;
        }

        if (!empty($this->filterparams->siteerrors)) {
            $joins[] = "( action='error' OR action='infected' OR action='failed' )";
        }

        if (!empty($this->filterparams->modid)) {
            list($actionsql, $actionparams) = $this->get_cm_sql();
            $joins[] = $actionsql;
            $params = array_merge($params, $actionparams);
        }

        if (!empty($this->filterparams->action) || $useextendeddbindex) {
            list($actionsql, $actionparams) = $this->get_action_sql();
            $joins[] = $actionsql;
            $params = array_merge($params, $actionparams);
        }

                if ($groupid and empty($this->filterparams->userid)) {
            if ($gusers = groups_get_members($groupid)) {
                $gusers = array_keys($gusers);
                $joins[] = 'userid IN (' . implode(',', $gusers) . ')';
            } else {
                $joins[] = 'userid = 0';             }
        } else if (!empty($this->filterparams->userid)) {
            $joins[] = "userid = :userid";
            $params['userid'] = $this->filterparams->userid;
        }

        if (!empty($this->filterparams->date)) {
            $joins[] = "timecreated > :date AND timecreated < :enddate";
            $params['date'] = $this->filterparams->date;
            $params['enddate'] = $this->filterparams->date + DAYSECS;         }

        if (isset($this->filterparams->edulevel) && ($this->filterparams->edulevel >= 0)) {
            $joins[] = "edulevel = :edulevel";
            $params['edulevel'] = $this->filterparams->edulevel;
        } else if ($useextendeddbindex) {
            list($edulevelsql, $edulevelparams) = $DB->get_in_or_equal(array(\core\event\base::LEVEL_OTHER,
                \core\event\base::LEVEL_PARTICIPATING, \core\event\base::LEVEL_TEACHING), SQL_PARAMS_NAMED, 'edulevel');
            $joins[] = "edulevel ".$edulevelsql;
            $params = array_merge($params, $edulevelparams);
        }

        if (!($this->filterparams->logreader instanceof logstore_legacy\log\store)) {
                        $joins[] = "anonymous = 0";
        }

        $selector = implode(' AND ', $joins);

        if (!$this->is_downloading()) {
            $total = $this->filterparams->logreader->get_events_select_count($selector, $params);
            $this->pagesize($pagesize, $total);
        } else {
            $this->pageable(false);
        }

                $this->rawdata = $this->filterparams->logreader->get_events_select_iterator($selector, $params,
            $this->filterparams->orderby, $this->get_page_start(), $this->get_page_size());

                $this->update_users_used();

                                $this->rawdata = $this->filterparams->logreader->get_events_select_iterator($selector, $params,
            $this->filterparams->orderby, $this->get_page_start(), $this->get_page_size());

                if ($useinitialsbar && !$this->is_downloading()) {
            $this->initialbars($total > $pagesize);
        }

    }

    
    public function update_users_and_courses_used() {
        throw new coding_exception('update_users_and_courses_used() can not be used any more, please use update_users_used() instead.');
    }

    
    protected function update_users_used() {
        global $DB;

        $this->userfullnames = array();
        $userids = array();

                        foreach ($this->rawdata as $event) {
            $logextra = $event->get_logextra();
            if (!empty($event->userid) && empty($userids[$event->userid])) {
                $userids[$event->userid] = $event->userid;
            }
            if (!empty($logextra['realuserid']) && empty($userids[$logextra['realuserid']])) {
                $userids[$logextra['realuserid']] = $logextra['realuserid'];
            }
            if (!empty($event->relateduserid) && empty($userids[$event->relateduserid])) {
                $userids[$event->relateduserid] = $event->relateduserid;
            }
        }
        $this->rawdata->close();

                if (!empty($userids)) {
            list($usql, $uparams) = $DB->get_in_or_equal($userids);
            $users = $DB->get_records_sql("SELECT id," . get_all_user_name_fields(true) . " FROM {user} WHERE id " . $usql,
                    $uparams);
            foreach ($users as $userid => $user) {
                $this->userfullnames[$userid] = fullname($user);
                unset($userids[$userid]);
            }

                                    foreach ($userids as $userid) {
                $this->userfullnames[$userid] = false;
            }
        }
    }
}
