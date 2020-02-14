<?php



defined('MOODLE_INTERNAL') || die();

global $CFG;
require_once($CFG->libdir . '/tablelib.php');


class mod_feedback_responses_table extends table_sql {

    
    const PREVIEWCOLUMNSLIMIT = 10;

    
    const TABLEJOINLIMIT = 59;

    
    const ROWCHUNKSIZE = 100;

    
    protected $feedbackstructure;

    
    protected $grandtotal = null;

    
    protected $showall = false;

    
    protected $showallparamname = 'showall';

    
    protected $downloadparamname = 'download';

    
    protected $hasmorecolumns = 0;

    
    public function __construct(mod_feedback_structure $feedbackstructure) {
        $this->feedbackstructure = $feedbackstructure;

        parent::__construct('feedback-showentry-list-' . $feedbackstructure->get_cm()->instance);

        $this->showall = optional_param($this->showallparamname, 0, PARAM_BOOL);
        $this->define_baseurl(new moodle_url('/mod/feedback/show_entries.php',
            ['id' => $this->feedbackstructure->get_cm()->id]));
        if ($courseid = $this->feedbackstructure->get_courseid()) {
            $this->baseurl->param('courseid', $courseid);
        }
        if ($this->showall) {
            $this->baseurl->param($this->showallparamname, $this->showall);
        }

        $name = format_string($feedbackstructure->get_feedback()->name);
        $this->is_downloadable(true);
        $this->is_downloading(optional_param($this->downloadparamname, 0, PARAM_ALPHA),
                $name, get_string('responses', 'feedback'));
        $this->useridfield = 'userid';
        $this->init();
    }

    
    protected function init() {

        $tablecolumns = array('userpic', 'fullname');
        $tableheaders = array(get_string('userpic'), get_string('fullnameuser'));

        $extrafields = get_extra_user_fields($this->get_context());
        $ufields = user_picture::fields('u', $extrafields, $this->useridfield);
        $fields = 'c.id, c.timemodified as completed_timemodified, c.courseid, '.$ufields;
        $from = '{feedback_completed} c '
                . 'JOIN {user} u ON u.id = c.userid AND u.deleted = :notdeleted';
        $where = 'c.anonymous_response = :anon
                AND c.feedback = :instance';
        if ($this->feedbackstructure->get_courseid()) {
            $where .= ' AND c.courseid = :courseid';
        }

        if ($this->is_downloading()) {
                                    array_shift($tablecolumns);
            array_shift($tableheaders);

                        foreach ($extrafields as $field) {
                $fields .= ", u.{$field}";
                $tablecolumns[] = $field;
                $tableheaders[] = get_user_field_name($field);
            }
        }

        if ($this->feedbackstructure->get_feedback()->course == SITEID && !$this->feedbackstructure->get_courseid()) {
            $tablecolumns[] = 'courseid';
            $tableheaders[] = get_string('course');
        }

        $tablecolumns[] = 'completed_timemodified';
        $tableheaders[] = get_string('date');

        $this->define_columns($tablecolumns);
        $this->define_headers($tableheaders);

        $this->sortable(true, 'lastname', SORT_ASC);
        $this->collapsible(true);
        $this->set_attribute('id', 'showentrytable');

        $params = array();
        $params['anon'] = FEEDBACK_ANONYMOUS_NO;
        $params['instance'] = $this->feedbackstructure->get_feedback()->id;
        $params['notdeleted'] = 0;
        $params['courseid'] = $this->feedbackstructure->get_courseid();

        $group = groups_get_activity_group($this->feedbackstructure->get_cm(), true);
        if ($group) {
            $where .= ' AND c.userid IN (SELECT g.userid FROM {groups_members} g WHERE g.groupid = :group)';
            $params['group'] = $group;
        }

        $this->set_sql($fields, $from, $where, $params);
        $this->set_count_sql("SELECT COUNT(c.id) FROM $from WHERE $where", $params);
    }

    
    protected function get_context() {
        return context_module::instance($this->feedbackstructure->get_cm()->id);
    }

    
    public function other_cols($column, $row) {
        if (preg_match('/^val(\d+)$/', $column, $matches)) {
            $items = $this->feedbackstructure->get_items();
            $itemobj = feedback_get_item_class($items[$matches[1]]->typ);
            return trim($itemobj->get_printval($items[$matches[1]], (object) ['value' => $row->$column] ));
        }
        return $row->$column;
    }

    
    public function col_userpic($row) {
        global $OUTPUT;
        $user = user_picture::unalias($row, [], $this->useridfield);
        return $OUTPUT->user_picture($user, array('courseid' => $this->feedbackstructure->get_cm()->course));
    }

    
    public function col_deleteentry($row) {
        global $OUTPUT;
        $deleteentryurl = new moodle_url($this->baseurl, ['delete' => $row->id, 'sesskey' => sesskey()]);
        $deleteaction = new confirm_action(get_string('confirmdeleteentry', 'feedback'));
        return $OUTPUT->action_icon($deleteentryurl,
            new pix_icon('t/delete', get_string('delete_entry', 'feedback')), $deleteaction);
    }

    
    protected function get_link_single_entry($row) {
        return new moodle_url($this->baseurl, ['userid' => $row->{$this->useridfield}, 'showcompleted' => $row->id]);
    }

    
    public function col_completed_timemodified($student) {
        if ($this->is_downloading()) {
            return userdate($student->completed_timemodified);
        } else {
            return html_writer::link($this->get_link_single_entry($student),
                    userdate($student->completed_timemodified));
        }
    }

    
    public function col_courseid($row) {
        $courses = $this->feedbackstructure->get_completed_courses();
        $name = '';
        if (isset($courses[$row->courseid])) {
            $name = $courses[$row->courseid];
            if (!$this->is_downloading()) {
                $name = html_writer::link(course_get_url($row->courseid), $name);
            }
        }
        return $name;
    }

    
    protected function add_all_values_to_output() {
        $tablecolumns = array_keys($this->columns);
        $tableheaders = $this->headers;

        $items = $this->feedbackstructure->get_items(true);
        if (!$this->is_downloading()) {
                                    $items = array_slice($items, 0, self::PREVIEWCOLUMNSLIMIT, true);
        }

        $columnscount = 0;
        $this->hasmorecolumns = max(0, count($items) - self::TABLEJOINLIMIT);

                foreach ($items as $nr => $item) {
            if ($columnscount++ < self::TABLEJOINLIMIT) {
                                                $this->sql->fields .= ", v{$nr}.value AS val{$nr}";
                $this->sql->from .= " LEFT OUTER JOIN {feedback_value} v{$nr} " .
                    "ON v{$nr}.completed = c.id AND v{$nr}.item = :itemid{$nr}";
                $this->sql->params["itemid{$nr}"] = $item->id;
            }

            $tablecolumns[] = "val{$nr}";
            $itemobj = feedback_get_item_class($item->typ);
            $tableheaders[] = $itemobj->get_display_name($item);
        }

                if (!$this->is_downloading() && has_capability('mod/feedback:deletesubmissions', $this->get_context())) {
            $tablecolumns[] = 'deleteentry';
            $tableheaders[] = '';
        }

        $this->define_columns($tablecolumns);
        $this->define_headers($tableheaders);
    }

    
    public function query_db($pagesize, $useinitialsbar=true) {
        global $DB;
        $this->totalrows = $grandtotal = $this->get_total_responses_count();
        if (!$this->is_downloading()) {
            $this->initialbars($useinitialsbar);

            list($wsql, $wparams) = $this->get_sql_where();
            if ($wsql) {
                $this->countsql .= ' AND '.$wsql;
                $this->countparams = array_merge($this->countparams, $wparams);

                $this->sql->where .= ' AND '.$wsql;
                $this->sql->params = array_merge($this->sql->params, $wparams);

                $this->totalrows  = $DB->count_records_sql($this->countsql, $this->countparams);
            }

            if ($this->totalrows > $pagesize) {
                $this->pagesize($pagesize, $this->totalrows);
            }
        }

        if ($sort = $this->get_sql_sort()) {
            $sort = "ORDER BY $sort";
        }
        $sql = "SELECT
                {$this->sql->fields}
                FROM {$this->sql->from}
                WHERE {$this->sql->where}
                {$sort}";

        if (!$this->is_downloading()) {
            $this->rawdata = $DB->get_recordset_sql($sql, $this->sql->params, $this->get_page_start(), $this->get_page_size());
        } else {
            $this->rawdata = $DB->get_recordset_sql($sql, $this->sql->params);
        }
    }

    
    public function get_total_responses_count() {
        global $DB;
        if ($this->grandtotal === null) {
            $this->grandtotal = $DB->count_records_sql($this->countsql, $this->countparams);
        }
        return $this->grandtotal;
    }

    
    public function define_columns($columns) {
        parent::define_columns($columns);
        foreach ($this->columns as $column => $column) {
                        $this->column_class[$column] = ' ' . $column;
        }
    }

    
    public function out($pagesize, $useinitialsbar, $downloadhelpbutton='') {
        $this->add_all_values_to_output();
        parent::out($pagesize, $useinitialsbar, $downloadhelpbutton);
    }

    
    public function display() {
        global $OUTPUT;
        groups_print_activity_menu($this->feedbackstructure->get_cm(), $this->baseurl->out());
        $grandtotal = $this->get_total_responses_count();
        if (!$grandtotal) {
            echo $OUTPUT->box(get_string('nothingtodisplay'), 'generalbox nothingtodisplay');
            return;
        }

        if (count($this->feedbackstructure->get_items(true)) > self::PREVIEWCOLUMNSLIMIT) {
            echo $OUTPUT->notification(get_string('questionslimited', 'feedback', self::PREVIEWCOLUMNSLIMIT), 'info');
        }

        $this->out($this->showall ? $grandtotal : FEEDBACK_DEFAULT_PAGE_COUNT,
                $grandtotal > FEEDBACK_DEFAULT_PAGE_COUNT);

                if ($this->totalrows > FEEDBACK_DEFAULT_PAGE_COUNT) {
            if (!$this->use_pages) {
                echo html_writer::div(html_writer::link(new moodle_url($this->baseurl, [$this->showallparamname => 0]),
                        get_string('showperpage', '', FEEDBACK_DEFAULT_PAGE_COUNT)), 'showall');
            } else {
                echo html_writer::div(html_writer::link(new moodle_url($this->baseurl, [$this->showallparamname => 1]),
                        get_string('showall', '', $this->totalrows)), 'showall');
            }
        }
    }

    
    public function get_reponse_navigation_links($record) {
        $this->setup();
        $grandtotal = $this->get_total_responses_count();
        $this->query_db($grandtotal);
        $lastrow = $thisrow = $nextrow = null;
        $counter = 0;
        $page = 0;
        while ($this->rawdata->valid()) {
            $row = $this->rawdata->current();
            if ($row->id == $record->id) {
                $page = $this->showall ? 0 : floor($counter / FEEDBACK_DEFAULT_PAGE_COUNT);
                $thisrow = $row;
                $this->rawdata->next();
                $nextrow = $this->rawdata->valid() ? $this->rawdata->current() : null;
                break;
            }
            $lastrow = $row;
            $this->rawdata->next();
            $counter++;
        }
        $this->rawdata->close();
        if (!$thisrow) {
            $lastrow = null;
        }
        return [
            $lastrow ? $this->get_link_single_entry($lastrow) : null,
            new moodle_url($this->baseurl, [$this->request[TABLE_VAR_PAGE] => $page]),
            $nextrow ? $this->get_link_single_entry($nextrow) : null,
        ];
    }

    
    public function download() {
        \core\session\manager::write_close();
        $this->out($this->get_total_responses_count(), false);
        exit;
    }

    
    public function build_table() {
        if ($this->rawdata instanceof \Traversable && !$this->rawdata->valid()) {
            return;
        }
        if (!$this->rawdata) {
            return;
        }

        $columnsgroups = [];
        if ($this->hasmorecolumns) {
            $items = $this->feedbackstructure->get_items(true);
            $notretrieveditems = array_slice($items, self::TABLEJOINLIMIT, $this->hasmorecolumns, true);
            $columnsgroups = array_chunk($notretrieveditems, self::TABLEJOINLIMIT, true);
        }

        $chunk = [];
        foreach ($this->rawdata as $row) {
            if ($this->hasmorecolumns) {
                $chunk[$row->id] = $row;
                if (count($chunk) >= self::ROWCHUNKSIZE) {
                    $this->build_table_chunk($chunk, $columnsgroups);
                    $chunk = [];
                }
            } else {
                $this->add_data_keyed($this->format_row($row), $this->get_row_class($row));
            }
        }
        $this->build_table_chunk($chunk, $columnsgroups);

        $this->rawdata->close();
    }

    
    protected function build_table_chunk(&$rows, &$columnsgroups) {
        global $DB;
        if (!$rows) {
            return;
        }

        foreach ($columnsgroups as $columnsgroup) {
            $fields = 'c.id';
            $from = '{feedback_completed} c';
            $params = [];
            foreach ($columnsgroup as $nr => $item) {
                $fields .= ", v{$nr}.value AS val{$nr}";
                $from .= " LEFT OUTER JOIN {feedback_value} v{$nr} " .
                    "ON v{$nr}.completed = c.id AND v{$nr}.item = :itemid{$nr}";
                $params["itemid{$nr}"] = $item->id;
            }
            list($idsql, $idparams) = $DB->get_in_or_equal(array_keys($rows), SQL_PARAMS_NAMED);
            $sql = "SELECT $fields FROM $from WHERE c.id ".$idsql;
            $results = $DB->get_records_sql($sql, $params + $idparams);
            foreach ($results as $result) {
                foreach ($result as $key => $value) {
                    $rows[$result->id]->{$key} = $value;
                }
            }
        }

        foreach ($rows as $row) {
            $this->add_data_keyed($this->format_row($row), $this->get_row_class($row));
        }
    }

    
    public function download_buttons() {
        global $OUTPUT;

        if ($this->is_downloadable() && !$this->is_downloading()) {
            return $OUTPUT->download_dataformat_selector(get_string('downloadas', 'table'),
                    $this->baseurl->out_omit_querystring(), $this->downloadparamname, $this->baseurl->params());
        } else {
            return '';
        }
    }
}
