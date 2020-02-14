<?php



namespace gradereport_history\output;

defined('MOODLE_INTERNAL') || die;

require_once($CFG->libdir . '/tablelib.php');


class tablelog extends \table_sql implements \renderable {

    
    protected $courseid;

    
    protected $context;

    
    protected $filters;

    
    protected $gradeitems = array();

    
    protected $cms;

    
    protected $defaultdecimalpoints;

    
    public function __construct($uniqueid, \context_course $context, $url, $filters = array(), $download = '', $page = 0,
                                $perpage = 100) {
        global $CFG;
        parent::__construct($uniqueid);

        $this->set_attribute('class', 'gradereport_history generaltable generalbox');

                $this->context = $context;
        $this->courseid = $this->context->instanceid;
        $this->pagesize = $perpage;
        $this->page = $page;
        $this->filters = (object)$filters;
        $this->gradeitems = \grade_item::fetch_all(array('courseid' => $this->courseid));
        $this->cms = get_fast_modinfo($this->courseid);
        $this->useridfield = 'userid';
        $this->defaultdecimalpoints = grade_get_setting($this->courseid, 'decimalpoints', $CFG->grade_decimalpoints);

                $this->define_table_columns();

                $this->define_table_configs($url);

                $this->is_downloading($download, get_string('exportfilename', 'gradereport_history'));
    }

    
    protected function define_table_configs(\moodle_url $url) {

                $urlparams = (array)$this->filters;
        unset($urlparams['submitbutton']);
        unset($urlparams['userfullnames']);
        $url->params($urlparams);
        $this->define_baseurl($url);

                $this->collapsible(true);
        $this->sortable(true, 'timemodified', SORT_DESC);
        $this->pageable(true);
        $this->no_sorting('grader');
    }

    
    protected function define_table_columns() {
        $extrafields = get_extra_user_fields($this->context);

                $cols = array(
            'timemodified' => get_string('datetime', 'gradereport_history'),
            'fullname' => get_string('name')
        );

                foreach ($extrafields as $field) {
            if (get_string_manager()->string_exists($field, 'moodle')) {
                $cols[$field] = get_string($field);
            } else {
                $cols[$field] = $field;
            }
        }

                $cols = array_merge($cols, array(
            'itemname' => get_string('gradeitem', 'grades'),
            'prevgrade' => get_string('gradeold', 'gradereport_history'),
            'finalgrade' => get_string('gradenew', 'gradereport_history'),
            'grader' => get_string('grader', 'gradereport_history'),
            'source' => get_string('source', 'gradereport_history'),
            'overridden' => get_string('overridden', 'grades'),
            'locked' => get_string('locked', 'grades'),
            'excluded' => get_string('excluded', 'gradereport_history'),
            'feedback' => get_string('feedbacktext', 'gradereport_history')
            )
        );

        $this->define_columns(array_keys($cols));
        $this->define_headers(array_values($cols));
    }

    
    public function col_finalgrade(\stdClass $history) {
        if (!empty($this->gradeitems[$history->itemid])) {
            $decimalpoints = $this->gradeitems[$history->itemid]->get_decimals();
        } else {
            $decimalpoints = $this->defaultdecimalpoints;
        }

        return format_float($history->finalgrade, $decimalpoints);
    }

    
    public function col_prevgrade(\stdClass $history) {
        if (!empty($this->gradeitems[$history->itemid])) {
            $decimalpoints = $this->gradeitems[$history->itemid]->get_decimals();
        } else {
            $decimalpoints = $this->defaultdecimalpoints;
        }

        return format_float($history->prevgrade, $decimalpoints);
    }

    
    public function col_timemodified(\stdClass $history) {
        return userdate($history->timemodified);
    }

    
    public function col_itemname(\stdClass $history) {
                $itemid = $history->itemid;
        if (!empty($this->gradeitems[$itemid])) {
            if ($history->itemtype === 'mod' && !$this->is_downloading()) {
                if (!empty($this->cms->instances[$history->itemmodule][$history->iteminstance])) {
                    $cm = $this->cms->instances[$history->itemmodule][$history->iteminstance];
                    $url = new \moodle_url('/mod/' . $history->itemmodule . '/view.php', array('id' => $cm->id));
                    return \html_writer::link($url, $this->gradeitems[$itemid]->get_name());
                }
            }
            return $this->gradeitems[$itemid]->get_name();
        }
        return get_string('deleteditemid', 'gradereport_history', $history->itemid);
    }

    
    public function col_grader(\stdClass $history) {
        if (empty($history->usermodified)) {
                        return '';
        }

        $grader = new \stdClass();
        $grader = username_load_fields_from_object($grader, $history, 'grader');
        $name = fullname($grader);

        if ($this->download) {
            return $name;
        }

        $userid = $history->usermodified;
        $profileurl = new \moodle_url('/user/view.php', array('id' => $userid, 'course' => $this->courseid));

        return \html_writer::link($profileurl, $name);
    }

    
    public function col_overridden(\stdClass $history) {
        return $history->overridden ? get_string('yes') : get_string('no');
    }

    
    public function col_locked(\stdClass $history) {
        return $history->locked ? get_string('yes') : get_string('no');
    }

    
    public function col_excluded(\stdClass $history) {
        return $history->excluded ? get_string('yes') : get_string('no');
    }

    
    public function col_feedback(\stdClass $history) {
        if ($this->is_downloading()) {
            return $history->feedback;
        } else {
            return format_text($history->feedback, $history->feedbackformat, array('context' => $this->context));
        }
    }

    
    protected function get_filters_sql_and_params() {
        global $DB;

        $coursecontext = $this->context;
        $filter = 'gi.courseid = :courseid';
        $params = array(
            'courseid' => $coursecontext->instanceid,
        );

        if (!empty($this->filters->itemid)) {
            $filter .= ' AND ggh.itemid = :itemid';
            $params['itemid'] = $this->filters->itemid;
        }
        if (!empty($this->filters->userids)) {
            $list = explode(',', $this->filters->userids);
            list($insql, $plist) = $DB->get_in_or_equal($list, SQL_PARAMS_NAMED);
            $filter .= " AND ggh.userid $insql";
            $params += $plist;
        }
        if (!empty($this->filters->datefrom)) {
            $filter .= " AND ggh.timemodified >= :datefrom";
            $params += array('datefrom' => $this->filters->datefrom);
        }
        if (!empty($this->filters->datetill)) {
            $filter .= " AND ggh.timemodified <= :datetill";
            $params += array('datetill' => $this->filters->datetill);
        }
        if (!empty($this->filters->grader)) {
            $filter .= " AND ggh.usermodified = :grader";
            $params += array('grader' => $this->filters->grader);
        }

        return array($filter, $params);
    }

    
    protected function get_sql_and_params($count = false) {
        $fields = 'ggh.id, ggh.timemodified, ggh.itemid, ggh.userid, ggh.finalgrade, ggh.usermodified,
                   ggh.source, ggh.overridden, ggh.locked, ggh.excluded, ggh.feedback, ggh.feedbackformat,
                   gi.itemtype, gi.itemmodule, gi.iteminstance, gi.itemnumber, ';

                $extrafields = get_extra_user_fields($this->context);
        foreach ($extrafields as $field) {
            $fields .= 'u.' . $field . ', ';
        }
        $gradeduserfields = get_all_user_name_fields(true, 'u');
        $fields .= $gradeduserfields . ', ';
        $groupby = $fields;

                $fields .= get_all_user_name_fields(true, 'ug', '', 'grader');
        $groupby .= get_all_user_name_fields(true, 'ug');

                $revisedonly = !empty($this->filters->revisedonly);

        if ($count && !$revisedonly) {
                        $select = "COUNT(1)";
        } else {
                                    $prevgrade = "SELECT MAX(finalgrade)
                            FROM {grade_grades_history} h
                           WHERE h.itemid = ggh.itemid
                             AND h.userid = ggh.userid
                             AND h.timemodified < ggh.timemodified
                             AND NOT EXISTS (
                              SELECT 1
                                FROM {grade_grades_history} h2
                               WHERE h2.itemid = ggh.itemid
                                 AND h2.userid = ggh.userid
                                 AND h2.timemodified < ggh.timemodified
                                 AND h.timemodified < h2.timemodified)";

            $select = "$fields, ($prevgrade) AS prevgrade,
                      CASE WHEN gi.itemname IS NULL THEN gi.itemtype ELSE gi.itemname END AS itemname";
        }

        list($where, $params) = $this->get_filters_sql_and_params();

        $sql =  "SELECT $select
                   FROM {grade_grades_history} ggh
                   JOIN {grade_items} gi ON gi.id = ggh.itemid
                   JOIN {user} u ON u.id = ggh.userid
              LEFT JOIN {user} ug ON ug.id = ggh.usermodified
                  WHERE $where";

                        if ($revisedonly) {
            $allorcount = $count ? 'COUNT(1)' : '*';
            $sql = "SELECT $allorcount FROM ($sql) pg
                     WHERE pg.finalgrade != pg.prevgrade
                        OR (pg.prevgrade IS NULL AND pg.finalgrade IS NOT NULL)
                        OR (pg.prevgrade IS NOT NULL AND pg.finalgrade IS NULL)";
        }

                if (!$count && $sqlsort = $this->get_sql_sort()) {
            $sql .= " ORDER BY " . $sqlsort;
        }

        return array($sql, $params);
    }

    
    public function get_sql_sort() {
        $columns = $this->get_sort_columns();
        if (count($columns) == 1 && isset($columns['timemodified']) && $columns['timemodified'] == SORT_DESC) {
                        $columns['id'] = SORT_DESC;
            return self::construct_order_by($columns);
        }
        return parent::get_sql_sort();
    }

    
    public function query_db($pagesize, $useinitialsbar = true) {
        global $DB;

        list($countsql, $countparams) = $this->get_sql_and_params(true);
        list($sql, $params) = $this->get_sql_and_params();
        $total = $DB->count_records_sql($countsql, $countparams);
        $this->pagesize($pagesize, $total);
        if ($this->is_downloading()) {
            $histories = $DB->get_records_sql($sql, $params);
        } else {
            $histories = $DB->get_records_sql($sql, $params, $this->pagesize * $this->page, $this->pagesize);
        }
        foreach ($histories as $history) {
            $this->rawdata[] = $history;
        }
                if ($useinitialsbar) {
            $this->initialbars($total > $pagesize);
        }
    }

    
    public function get_selected_users() {
        global $DB;
        $idlist = array();
        if (!empty($this->filters->userids)) {

            $idlist = explode(',', $this->filters->userids);
            list($where, $params) = $DB->get_in_or_equal($idlist);
            return $DB->get_records_select('user', "id $where", $params);

        }
        return $idlist;
    }

}
