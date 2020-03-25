<?php



namespace tool_lp\output;
defined('MOODLE_INTERNAL') || die();

require_once($CFG->libdir . '/tablelib.php');

use html_writer;
use moodle_url;
use table_sql;
use core_competency\template;


class template_cohorts_table extends table_sql {

    
    protected $context;

    
    protected $template;

    
    public function __construct($uniqueid, \core_competency\template $template) {
        parent::__construct($uniqueid);

                 if (!$template->can_read()) {
            throw new \required_capability_exception($template->get_context(), 'moodle/competency:templateview',
                'nopermissions', '');
        }

                $this->template = $template;
        $this->context = $this->template->get_context();

                $this->define_table_columns();

                $this->define_table_configs();
    }

    
    protected function col_actions($row) {
        global $OUTPUT;

        $action = new \confirm_action(get_string('areyousure'));
        $url = new moodle_url($this->baseurl);
        $url->params(array('removecohort' => $row->id, 'sesskey' => sesskey()));
        $actionlink = $OUTPUT->action_link($url, '', $action, null, new \pix_icon('t/delete',
            get_string('stopsyncingcohort', 'tool_lp')));

        return $actionlink;

    }

    
    protected function define_table_columns() {
                $cols = array(
            'name' => get_string('name', 'cohort'),
            'idnumber' => get_string('idnumber', 'cohort'),
        );

        if ($this->template->can_manage()) {
            $cols['actions'] = get_string('actions');
        }

        $this->define_columns(array_keys($cols));
        $this->define_headers(array_values($cols));
    }

    
    protected function define_table_configs() {
        $this->collapsible(false);
        $this->sortable(true, 'name', SORT_ASC);
        $this->pageable(true);
        $this->no_sorting('actions');
    }

    
    protected function get_sql_and_params($count = false) {
        $fields = 'c.id, c.name, c.idnumber';

        if ($count) {
            $select = "COUNT(1)";
        } else {
            $select = "$fields";
        }

        $sql = "SELECT $select
                  FROM {" . \core_competency\template_cohort::TABLE . "} tc
                  JOIN {cohort} c ON c.id = tc.cohortid
                 WHERE tc.templateid = :templateid";
        $params = array('templateid' => $this->template->get_id());

                if (!$count && $sqlsort = $this->get_sql_sort()) {
            $sql .= " ORDER BY " . $sqlsort;
        }

        return array($sql, $params);
    }

    
    public function print_nothing_to_display() {
        global $OUTPUT;
        echo $this->render_reset_button();
        $this->print_initials_bar();
        echo $OUTPUT->heading(get_string('nothingtodisplay'), 4);
    }

    
    public function query_db($pagesize, $useinitialsbar = true) {
        global $DB;

        list($countsql, $countparams) = $this->get_sql_and_params(true);
        list($sql, $params) = $this->get_sql_and_params();
        $total = $DB->count_records_sql($countsql, $countparams);
        $this->pagesize($pagesize, $total);
        $this->rawdata = $DB->get_records_sql($sql, $params, $this->get_page_start(), $this->get_page_size());

                if ($useinitialsbar) {
            $this->initialbars($total > $pagesize);
        }
    }
}
