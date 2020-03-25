<?php



namespace tool_cohortroles\output;
defined('MOODLE_INTERNAL') || die();

require_once($CFG->libdir . '/tablelib.php');

use context_helper;
use context_system;
use html_writer;
use moodle_url;
use table_sql;


class cohort_role_assignments_table extends table_sql {

    
    public function __construct($uniqueid, $url) {
        global $CFG;
        parent::__construct($uniqueid);
        $context = context_system::instance();

        $this->context = $context;

        $this->rolenames = role_get_names();

                require_capability('moodle/role:manage', $context);

        $this->useridfield = 'userid';

                $this->define_table_columns();

        $this->define_baseurl($url);
                $this->define_table_configs();
    }

    
    protected function col_rolename($data) {
        return $this->rolenames[$data->roleid]->localname;
    }

    
    protected function col_cohortname($data) {
        global $OUTPUT;

        $record = (object) array(
            'id' => $data->cohortid,
            'idnumber' => $data->cohortidnumber,
            'description' => $data->cohortdescription,
            'visible' => $data->cohortvisible,
            'name' => $data->cohortname
        );
        $context = context_helper::instance_by_id($data->cohortcontextid);

        $exporter = new \tool_lp\external\cohort_summary_exporter($record, array('context' => $context));
        $cohort = $exporter->export($OUTPUT);

        $html = $OUTPUT->render_from_template('tool_cohortroles/cohort-in-list', $cohort);
        return $html;
    }

    
    protected function col_actions($data) {
        global $OUTPUT;

        $action = new \confirm_action(get_string('removecohortroleassignmentconfirm', 'tool_cohortroles'));
        $url = new moodle_url($this->baseurl);
        $url->params(array('removecohortroleassignment' => $data->id, 'sesskey' => sesskey()));
        $pix = new \pix_icon('t/delete', get_string('removecohortroleassignment', 'tool_cohortroles'));
        return $OUTPUT->action_link($url, '', $action, null, $pix);
    }

    
    protected function define_table_columns() {
        $extrafields = get_extra_user_fields($this->context);

                $cols = array(
            'cohortname' => get_string('cohort', 'cohort'),
            'rolename' => get_string('role'),
            'fullname' => get_string('name'),
        );

                foreach ($extrafields as $field) {
            if (get_string_manager()->string_exists($field, 'moodle')) {
                $cols[$field] = get_string($field);
            } else {
                $cols[$field] = $field;
            }
        }

                $cols = array_merge($cols, array('actions' => get_string('actions')));

        $this->define_columns(array_keys($cols));
        $this->define_headers(array_values($cols));
    }

    
    protected function define_table_configs() {
        $this->collapsible(false);
        $this->sortable(true, 'lastname', SORT_ASC);
        $this->pageable(true);
        $this->no_sorting('actions');
    }

    
    protected function get_sql_and_params($count = false) {
        $fields = 'uca.id, uca.cohortid, uca.userid, uca.roleid, ';
        $fields .= 'c.name as cohortname, c.idnumber as cohortidnumber, c.contextid as cohortcontextid, ';
        $fields .= 'c.visible as cohortvisible, c.description as cohortdescription, ';

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
                   FROM {tool_cohortroles} uca
                   JOIN {user} u ON u.id = uca.userid
                   JOIN {cohort} c ON c.id = uca.cohortid";
        $params = array();

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
