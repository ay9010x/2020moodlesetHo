<?php



defined('MOODLE_INTERNAL') || die();

require_once($CFG->libdir.'/tablelib.php');
require_once($CFG->libdir.'/gradelib.php');
require_once($CFG->dirroot.'/mod/assign/locallib.php');


class tool_assignmentupgrade_assignments_table extends table_sql implements renderable {

    
    private $perpage = 10;
    
    private $rownum = -1;
    
    private $output = null;
    
    public $anyupgradableassignments = false;

    
    public function __construct($perpage, $rowoffset=0) {
        global $PAGE;
        parent::__construct('tool_assignmentupgrade_assignments');
        $this->perpage = $perpage;
        $this->output = $PAGE->get_renderer('tool_assignmentupgrade');

        $this->define_baseurl(new moodle_url('/admin/tool/assignmentupgrade/listnotupgraded.php'));

        $this->anyupgradableassignments = tool_assignmentupgrade_any_upgradable_assignments();

                if ($rowoffset) {
            $this->rownum = $rowoffset - 1;
        }

        $fields = 'a.id as id,
                   a.name as name,
                   a.assignmenttype as type,
                   c.shortname as courseshortname,
                   c.id as courseid,
                   COUNT(s.id) as submissioncount';
        $from = '{assignment} a JOIN {course} c ON a.course = c.id ' .
                        ' LEFT JOIN {assignment_submissions} s ON a.id = s.assignment';

        $where = '1 = 1';
        $where .= ' GROUP BY a.id, a.name, a.assignmenttype, c.shortname, c.id ';

        $this->set_sql($fields, $from, $where, array());
        $this->set_count_sql('SELECT COUNT(*) FROM {assignment} a JOIN {course} c ON a.course = c.id', array());

        $columns = array();
        $headers = array();

        $columns[] = 'select';
        $headers[] = get_string('select', 'tool_assignmentupgrade') .
                     '<div class="selectall">' .
                     '<input type="checkbox" name="selectall" title="' . get_string('selectall') . '"/>' .
                     '</div>';
        $columns[] = 'upgradable';
        $headers[] = get_string('upgradable', 'tool_assignmentupgrade');
        $columns[] = 'id';
        $headers[] = get_string('assignmentid', 'tool_assignmentupgrade');
        $columns[] = 'courseshortname';
        $headers[] = get_string('course');
        $columns[] = 'name';
        $headers[] = get_string('name');
        $columns[] = 'type';
        $headers[] = get_string('assignmenttype', 'tool_assignmentupgrade');
        $columns[] = 'submissioncount';
        $headers[] = get_string('submissions', 'tool_assignmentupgrade');

                $this->define_columns($columns);
        $this->define_headers($headers);
        $this->no_sorting('upgradable');
        $this->no_sorting('select');
    }

    
    public function get_rows_per_page() {
        return $this->perpage;
    }

    
    public function col_name(stdClass $row) {
        $url = new moodle_url('/mod/assignment/view.php', array('a' => $row->id));
        return html_writer::link($url, $row->name);
    }


    
    public function col_upgradable(stdClass $row) {
        if ($row->upgradable) {
            $urlparams = array('id' => $row->id, 'sesskey' => sesskey());
            $url = new moodle_url('/admin/tool/assignmentupgrade/upgradesingleconfirm.php', $urlparams);
            return html_writer::link($url, get_string('supported', 'tool_assignmentupgrade'));
        } else {
            return get_string('notsupported', 'tool_assignmentupgrade');
        }
    }

    
    public function col_select(stdClass $row) {
        global $CFG;
        $version = get_config('assignment_' . $row->type, 'version');
        require_once($CFG->dirroot . '/mod/assign/locallib.php');
        if (assign::can_upgrade_assignment($row->type, $version)) {
            $row->upgradable = true;
            return '<input type="checkbox" name="selectedassignment" value="' . $row->id . '"/>';
        }
        $row->upgradable = false;
        return '';
    }

    
    protected function show_hide_link($column, $index) {
        if ($index > 0) {
            return parent::show_hide_link($column, $index);
        }
        return '';
    }
}
