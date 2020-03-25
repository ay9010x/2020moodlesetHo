<?php



namespace tool_monitor\output\managerules;

defined('MOODLE_INTERNAL') || die;

require_once($CFG->libdir . '/tablelib.php');


class renderable extends \table_sql implements \renderable {

    
    public $courseid;

    
    protected $context;

    
    protected $hassystemcap;

    
    public function __construct($uniqueid, \moodle_url $url, $courseid = 0, $perpage = 100) {
        parent::__construct($uniqueid);

        $this->set_attribute('id', 'toolmonitorrules_table');
        $this->set_attribute('class', 'toolmonitor managerules generaltable generalbox');
        $this->define_columns(array('name', 'description', 'course', 'plugin', 'eventname', 'filters', 'manage'));
        $this->define_headers(array(
                get_string('rulename', 'tool_monitor'),
                get_string('description'),
                get_string('course'),
                get_string('area', 'tool_monitor'),
                get_string('event', 'tool_monitor'),
                get_string('frequency', 'tool_monitor'),
                get_string('manage', 'tool_monitor'),
            )
        );
        $this->courseid = $courseid;
        $this->pagesize = $perpage;
        $systemcontext = \context_system::instance();
        $this->context = empty($courseid) ? $systemcontext : \context_course::instance($courseid);
        $this->hassystemcap = has_capability('tool/monitor:managerules', $systemcontext);
        $this->collapsible(false);
        $this->sortable(false);
        $this->pageable(true);
        $this->is_downloadable(false);
        $this->define_baseurl($url);
    }

    
    public function col_name(\tool_monitor\rule $rule) {
        return $rule->get_name($this->context);
    }

    
    public function col_description(\tool_monitor\rule $rule) {
        return $rule->get_description($this->context);
    }

    
    public function col_course(\tool_monitor\rule $rule) {
        $coursename = $rule->get_course_name($this->context);

        $courseid = $rule->courseid;
        if (empty($courseid)) {
            return $coursename;
        } else {
            return \html_writer::link(new \moodle_url('/course/view.php', array('id' => $courseid)), $coursename);
        }
    }

    
    public function col_plugin(\tool_monitor\rule $rule) {
        return $rule->get_plugin_name();
    }

    
    public function col_eventname(\tool_monitor\rule $rule) {
        return $rule->get_event_name();
    }

    
    public function col_filters(\tool_monitor\rule $rule) {
        return $rule->get_filters_description();
    }

    
    public function col_manage(\tool_monitor\rule $rule) {
        global $OUTPUT, $CFG;

        $manage = '';

                                if ($this->hassystemcap || ($rule->courseid != 0)) {
            $editurl = new \moodle_url($CFG->wwwroot. '/admin/tool/monitor/edit.php', array('ruleid' => $rule->id,
                    'courseid' => $rule->courseid, 'sesskey' => sesskey()));
            $icon = $OUTPUT->render(new \pix_icon('t/edit', get_string('editrule', 'tool_monitor')));
            $manage .= \html_writer::link($editurl, $icon, array('class' => 'action-icon'));
        }

                $copyurl = new \moodle_url($CFG->wwwroot. '/admin/tool/monitor/managerules.php',
                array('ruleid' => $rule->id, 'action' => 'copy', 'courseid' => $this->courseid, 'sesskey' => sesskey()));
        $icon = $OUTPUT->render(new \pix_icon('t/copy', get_string('duplicaterule', 'tool_monitor')));
        $manage .= \html_writer::link($copyurl, $icon, array('class' => 'action-icon'));

        if ($this->hassystemcap || ($rule->courseid != 0)) {
            $deleteurl = new \moodle_url($CFG->wwwroot. '/admin/tool/monitor/managerules.php', array('ruleid' => $rule->id,
                    'action' => 'delete', 'courseid' => $rule->courseid, 'sesskey' => sesskey()));
            $icon = $OUTPUT->render(new \pix_icon('t/delete', get_string('deleterule', 'tool_monitor')));
            $manage .= \html_writer::link($deleteurl, $icon, array('class' => 'action-icon'));
        }

        return $manage;
    }

    
    public function query_db($pagesize, $useinitialsbar = true) {

        $total = \tool_monitor\rule_manager::count_rules_by_courseid($this->courseid);
        $this->pagesize($pagesize, $total);
        $rules = \tool_monitor\rule_manager::get_rules_by_courseid($this->courseid, $this->get_page_start(),
                $this->get_page_size());
        $this->rawdata = $rules;
                if ($useinitialsbar) {
            $this->initialbars($total > $pagesize);
        }
    }
}
