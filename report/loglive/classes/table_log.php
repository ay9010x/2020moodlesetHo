<?php



defined('MOODLE_INTERNAL') || die;
require_once($CFG->libdir . '/tablelib.php');


class report_loglive_table_log extends table_sql {

    
    protected $userfullnames = array();

    
    protected $courseshortnames = array();

    
    protected $contextname = array();

    
    protected $filterparams;

    
    public function __construct($uniqueid, $filterparams = null) {
        parent::__construct($uniqueid);

        $this->set_attribute('class', 'reportloglive generaltable generalbox');
        $this->set_attribute('aria-live', 'polite');
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
                get_string('eventrelatedfullnameuser', 'report_loglive'),
                get_string('eventcontext', 'report_loglive'),
                get_string('eventcomponent', 'report_loglive'),
                get_string('eventname'),
                get_string('description'),
                get_string('eventorigin', 'report_loglive'),
                get_string('ip_address')
                )
            ));
        $this->collapsible(false);
        $this->sortable(false);
        $this->pageable(true);
        $this->is_downloadable(false);
    }

    
    public function col_course($event) {
        if (empty($event->courseid) || empty($this->courseshortnames[$event->courseid])) {
            return '-';
        } else {
            return $this->courseshortnames[$event->courseid];
        }
    }

    
    public function col_time($event) {
        $recenttimestr = get_string('strftimerecent', 'core_langconfig');
        return userdate($event->timecreated, $recenttimestr);
    }

    
    public function col_fullnameuser($event) {
                $logextra = $event->get_logextra();

                if (!empty($logextra['realuserid'])) {
            $a = new stdClass();
            $params = array('id' => $logextra['realuserid']);
            if ($event->courseid) {
                $params['course'] = $event->courseid;
            }
            $a->realusername = html_writer::link(new moodle_url("/user/view.php", $params),
                $this->userfullnames[$logextra['realuserid']]);
            $params['id'] = $event->userid;
            $a->asusername = html_writer::link(new moodle_url("/user/view.php", $params),
                $this->userfullnames[$event->userid]);
            $username = get_string('eventloggedas', 'report_loglive', $a);
        } else if (!empty($event->userid) && !empty($this->userfullnames[$event->userid])) {
            $params = array('id' => $event->userid);
            if ($event->courseid) {
                $params['course'] = $event->courseid;
            }
            $username = html_writer::link(new moodle_url("/user/view.php", $params), $this->userfullnames[$event->userid]);
        } else {
            $username = '-';
        }
        return $username;
    }

    
    public function col_relatedfullnameuser($event) {
                if (!empty($event->relateduserid) && isset($this->userfullnames[$event->relateduserid])) {
            $params = array('id' => $event->relateduserid);
            if ($event->courseid) {
                $params['course'] = $event->courseid;
            }
            return html_writer::link(new moodle_url("/user/view.php", $params), $this->userfullnames[$event->relateduserid]);
        } else {
            return '-';
        }
    }

    
    public function col_context($event) {
                if ($event->contextid) {
                        if (isset($this->contextname[$event->contextid])) {
                return $this->contextname[$event->contextid];
            } else {
                $context = context::instance_by_id($event->contextid, IGNORE_MISSING);
                if ($context) {
                    $contextname = $context->get_context_name(true);
                    if ($url = $context->get_url()) {
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
        if ($url = $event->get_url()) {
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

        $url = new moodle_url("/iplookup/index.php?ip={$logextra['ip']}&user=$event->userid");
        return $this->action_link($url, $logextra['ip'], 'ip');
    }

    
    protected function action_link(moodle_url $url, $text, $name = 'popup') {
        global $OUTPUT;
        $link = new action_link($url, $text, new popup_action('click', $url, $name, array('height' => 440, 'width' => 700)));
        return $OUTPUT->render($link);
    }

    
    public function query_db($pagesize, $useinitialsbar = true) {

        $joins = array();
        $params = array();

                if (!empty($this->filterparams->courseid)) {
            $joins[] = "courseid = :courseid";
            $params['courseid'] = $this->filterparams->courseid;
        }

        if (!empty($this->filterparams->date)) {
            $joins[] = "timecreated > :date";
            $params['date'] = $this->filterparams->date;
        }

        if (isset($this->filterparams->anonymous)) {
            $joins[] = "anonymous = :anon";
            $params['anon'] = $this->filterparams->anonymous;
        }

        $selector = implode(' AND ', $joins);

        $total = $this->filterparams->logreader->get_events_select_count($selector, $params);
        $this->pagesize($pagesize, $total);
        $this->rawdata = $this->filterparams->logreader->get_events_select($selector, $params, $this->filterparams->orderby,
                $this->get_page_start(), $this->get_page_size());

                if ($useinitialsbar) {
            $this->initialbars($total > $pagesize);
        }

                $this->update_users_and_courses_used();
    }

    
    public function update_users_and_courses_used() {
        global $SITE, $DB;

        $this->userfullnames = array();
        $this->courseshortnames = array($SITE->id => $SITE->shortname);
        $userids = array();
        $courseids = array();
                        foreach ($this->rawdata as $event) {
            $logextra = $event->get_logextra();
            if (!empty($event->userid) && !in_array($event->userid, $userids)) {
                $userids[] = $event->userid;
            }
            if (!empty($logextra['realuserid']) && !in_array($logextra['realuserid'], $userids)) {
                $userids[] = $logextra['realuserid'];
            }
            if (!empty($event->relateduserid) && !in_array($event->relateduserid, $userids)) {
                $userids[] = $event->relateduserid;
            }

            if (!empty($event->courseid) && ($event->courseid != $SITE->id) && !in_array($event->courseid, $courseids)) {
                $courseids[] = $event->courseid;
            }
        }

                if (!empty($userids)) {
            list($usql, $uparams) = $DB->get_in_or_equal($userids);
            $users = $DB->get_records_sql("SELECT id," . get_all_user_name_fields(true) . " FROM {user} WHERE id " . $usql,
                    $uparams);
            foreach ($users as $userid => $user) {
                $this->userfullnames[$userid] = fullname($user);
            }
        }

                if (!empty($courseids)) {             list($coursesql, $courseparams) = $DB->get_in_or_equal($courseids, SQL_PARAMS_NAMED);
            $ccselect = ', ' . context_helper::get_preload_record_columns_sql('ctx');
            $ccjoin = "LEFT JOIN {context} ctx ON (ctx.instanceid = c.id AND ctx.contextlevel = :contextlevel)";
            $courseparams['contextlevel'] = CONTEXT_COURSE;
            $sql = "SELECT c.id,c.shortname $ccselect FROM {course} c
                   $ccjoin
                     WHERE c.id " . $coursesql;

            $courses = $DB->get_records_sql($sql, $courseparams);
            foreach ($courses as $courseid => $course) {
                $url = new moodle_url("/course/view.php", array('id' => $courseid));
                context_helper::preload_from_record($course);
                $context = context_course::instance($courseid, IGNORE_MISSING);
                                $this->courseshortnames[$courseid] = html_writer::link($url, format_string($course->shortname, true,
                        array('context' => $context)));
            }
        }
    }
}
