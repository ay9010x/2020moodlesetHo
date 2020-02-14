<?php



namespace tool_lp\output;
defined('MOODLE_INTERNAL') || die();

require_once($CFG->libdir . '/tablelib.php');

use html_writer;
use moodle_url;
use table_sql;
use core_competency\template;


class template_plans_table extends table_sql {

    
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
        $this->useridfield = 'userid';

                $this->define_table_columns();

                $this->define_table_configs();
    }

    
    protected function col_name($row) {
        return html_writer::link(new moodle_url('/admin/tool/lp/plan.php', array('id' => $row->id)),
            format_string($row->name, true, array('context' => $this->context)));
    }

    
    protected function define_table_columns() {
        $extrafields = get_extra_user_fields($this->context);

                $cols = array(
            'name' => get_string('planname', 'tool_lp'),
            'fullname' => get_string('name')
        );

                foreach ($extrafields as $field) {
            if (get_string_manager()->string_exists($field, 'moodle')) {
                $cols[$field] = get_string($field);
            } else {
                $cols[$field] = $field;
            }
        }

                $cols = array_merge($cols, array());

        $this->define_columns(array_keys($cols));
        $this->define_headers(array_values($cols));
    }

    
    protected function define_table_configs() {
        $this->collapsible(false);
        $this->sortable(true, 'lastname', SORT_ASC);
        $this->pageable(true);
    }

    
    protected function get_sql_and_params($count = false) {
        $fields = 'p.id, p.userid, p.name, ';

                $extrafields = get_extra_user_fields($this->context);
        foreach ($extrafields as $field) {
            $fields .= 'u.' . $field . ', ';
        }
        $fields .= get_all_user_name_fields(true, 'u');

        if ($count) {
            $select = "COUNT(1)";
        } else {
            $select = "$fields";
        }

        $sql = "SELECT $select
                  FROM {" . \core_competency\plan::TABLE . "} p
                  JOIN {user} u ON u.id = p.userid
                 WHERE p.templateid = :templateid";
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
